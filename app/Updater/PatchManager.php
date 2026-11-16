<?php

declare(strict_types=1);

namespace App\Updater;

use App\Core\Application;
use ZipArchive;

/**
 * سیستم حرفه‌ای بروزرسانی پچ‌محور (Delta Patch Updater) قالب رنگ صادقی
 * - بررسی امضای SHA-256 فایل‌ها و جلوگیری کامل از Path Traversal
 * - تهیه نسخه پشتیبان خودکار (Auto-Backup) از فایل‌های درگیر قبل از اعمال پچ
 * - بازگردانی آنی (One-Click Rollback) در صورت خطا یا به درخواست مدیر
 * - پشتیبانی از پچ آفلاین (.zip / .json) و بررسی آنلاین مانیفست (OTA)
 */
final class PatchManager
{
    private const HISTORY_OPTION = 'rangesadeghi_patch_history';
    private const REMOTE_CACHE_TRANSIENT = 'rangesadeghi_remote_patch_info';

    public function __construct(
        private readonly Application $app,
    ) {
    }

    public function currentVersion(): string
    {
        $theme = wp_get_theme(get_template());
        $ver = (string) $theme->get('Version');
        if ($ver !== '') {
            return $ver;
        }

        $styleFile = $this->app->basePath('style.css');
        if (is_file($styleFile)) {
            $head = (string) file_get_contents($styleFile, false, null, 0, 2048);
            if (preg_match('/^\s*\*\s*Version:\s*([^\r\n]+)/mi', $head, $m)) {
                return trim($m[1]);
            }
        }

        return '2.1.0';
    }

    /**
     * مسیر امن نگهداری بکاپ‌ها در uploads با محافظت در برابر دسترسی مستقیم وب
     */
    public function backupDirectory(): string
    {
        $uploads = wp_upload_dir();
        $dir = rtrim((string) ($uploads['basedir'] ?? WP_CONTENT_DIR . '/uploads'), '/') . '/rangesadeghi-backups';

        if (!is_dir($dir)) {
            wp_mkdir_p($dir);
        }

        $htaccess = $dir . '/.htaccess';
        if (!is_file($htaccess)) {
            @file_put_contents($htaccess, "Order deny,allow\nDeny from all\n");
        }
        $indexFile = $dir . '/index.php';
        if (!is_file($indexFile)) {
            @file_put_contents($indexFile, "<?php // Silence is golden.\n");
        }

        return $dir;
    }

    /**
     * اعمال بسته پچ (.zip یا .json) روی قالب با اعتبارسنجی و بکاپ خودکار
     *
     * @return array{ok: bool, message: string, target_version?: string, changed_count?: int, backup_id?: string}
     */
    public function applyPatchFile(string $filePath, string $originalName = ''): array
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            return ['ok' => false, 'message' => 'فایل پچ قابل خواندن نیست.'];
        }

        $ext = strtolower(pathinfo($originalName !== '' ? $originalName : $filePath, PATHINFO_EXTENSION));
        if ($ext === 'json') {
            return $this->applyJsonBundle($filePath);
        }

        if ($ext !== 'zip') {
            return ['ok' => false, 'message' => 'فرمت فایل پچ باید .zip یا .json باشد.'];
        }

        if (!class_exists(ZipArchive::class)) {
            return ['ok' => false, 'message' => 'افزونه PHP ZipArchive روی هاست فعال نیست؛ از فایل پچ فرمت .json استفاده کنید یا ZipArchive را فعال نمایید.'];
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return ['ok' => false, 'message' => 'فایل فشرده پچ معتبر نیست یا آسیب دیده است.'];
        }

        $manifestRaw = $zip->getFromName('patch.json');
        if ($manifestRaw === false) {
            $zip->close();
            return ['ok' => false, 'message' => 'فایل مانیفست patch.json در بسته بروزرسانی یافت نشد.'];
        }

        $manifest = json_decode($manifestRaw, true);
        if (!is_array($manifest)) {
            $zip->close();
            return ['ok' => false, 'message' => 'ساختار patch.json نامعتبر است.'];
        }

        $validation = $this->validateManifest($manifest);
        if (!$validation['ok']) {
            $zip->close();
            return $validation;
        }

        /** @var array<string, array<string, string>> $filesSpec */
        $filesSpec = $manifest['files'] ?? [];
        $stagedPayloads = [];
        $deletions = [];

        foreach ($filesSpec as $relPath => $meta) {
            $relPath = ltrim(str_replace('\\', '/', (string) $relPath), '/');
            if (!$this->isSafeRelativePath($relPath)) {
                $zip->close();
                return ['ok' => false, 'message' => "مسیر فایل غیرمجاز در پچ شناسایی شد: {$relPath}"];
            }

            $action = (string) ($meta['action'] ?? 'upsert');
            if ($action === 'delete') {
                $deletions[] = $relPath;
                continue;
            }

            $entryPath = 'files/' . $relPath;
            $content = $zip->getFromName($entryPath);
            if ($content === false) {
                $content = $zip->getFromName($relPath);
            }
            if ($content === false) {
                $zip->close();
                return ['ok' => false, 'message' => "فایل «{$relPath}» در آرشیو پچ موجود نیست."];
            }

            if ((bool) rs_option('updater.verify_checksum', true) && !empty($meta['sha256'])) {
                $actualHash = hash('sha256', $content);
                if (!hash_equals(strtolower((string) $meta['sha256']), strtolower($actualHash))) {
                    $zip->close();
                    return ['ok' => false, 'message' => "عدم تطابق امضای امنیتی SHA-256 در فایل «{$relPath}». عملیات متوقف شد."];
                }
            }

            $stagedPayloads[$relPath] = $content;
        }

        $zip->close();

        return $this->commitStagedPatch($manifest, $stagedPayloads, $deletions);
    }

    /**
     * اعمال پچ تک‌فایلی JSON (مناسب سرورهای فاقد ZipArchive یا پچ‌های کوچک)
     *
     * @return array{ok: bool, message: string, target_version?: string, changed_count?: int, backup_id?: string}
     */
    private function applyJsonBundle(string $jsonFile): array
    {
        $raw = (string) file_get_contents($jsonFile);
        $manifest = json_decode($raw, true);
        if (!is_array($manifest)) {
            return ['ok' => false, 'message' => 'فایل JSON پچ معتبر نیست.'];
        }

        $validation = $this->validateManifest($manifest);
        if (!$validation['ok']) {
            return $validation;
        }

        $stagedPayloads = [];
        $deletions = [];

        foreach (($manifest['files'] ?? []) as $relPath => $meta) {
            $relPath = ltrim(str_replace('\\', '/', (string) $relPath), '/');
            if (!$this->isSafeRelativePath($relPath)) {
                return ['ok' => false, 'message' => "مسیر غیرمجاز در پچ: {$relPath}"];
            }

            $action = (string) ($meta['action'] ?? 'upsert');
            if ($action === 'delete') {
                $deletions[] = $relPath;
                continue;
            }

            $encoded = (string) ($meta['content_base64'] ?? '');
            $content = base64_decode($encoded, true);
            if ($content === false) {
                return ['ok' => false, 'message' => "محتوای فایل «{$relPath}» در بسته JSON قابل رمزگشایی نیست."];
            }

            if ((bool) rs_option('updater.verify_checksum', true) && !empty($meta['sha256'])) {
                if (!hash_equals(strtolower((string) $meta['sha256']), hash('sha256', $content))) {
                    return ['ok' => false, 'message' => "خطای بررسی SHA-256 در فایل «{$relPath}»."];
                }
            }

            $stagedPayloads[$relPath] = $content;
        }

        return $this->commitStagedPatch($manifest, $stagedPayloads, $deletions);
    }

    /**
     * @param array<string, mixed> $manifest
     * @param array<string, string> $stagedPayloads
     * @param array<int, string> $deletions
     * @return array{ok: bool, message: string, target_version?: string, changed_count?: int, backup_id?: string}
     */
    private function commitStagedPatch(array $manifest, array $stagedPayloads, array $deletions): array
    {
        $fromVersion = $this->currentVersion();
        $targetVersion = sanitize_text_field((string) ($manifest['target_version'] ?? $fromVersion));
        $affectedPaths = array_values(array_unique(array_merge(array_keys($stagedPayloads), $deletions, ['style.css'])));

        $backupId = '';
        if ((bool) rs_option('updater.auto_backup_before_patch', true)) {
            $backupResult = $this->createBackup($affectedPaths, $fromVersion, $targetVersion);
            if (!$backupResult['ok']) {
                return ['ok' => false, 'message' => 'خطا در ایجاد بکاپ خودکار پیش از اعمال پچ: ' . $backupResult['message']];
            }
            $backupId = (string) $backupResult['backup_id'];
        }

        try {
            foreach ($stagedPayloads as $relPath => $content) {
                $absPath = $this->app->basePath($relPath);
                $dir = dirname($absPath);
                if (!is_dir($dir) && !wp_mkdir_p($dir)) {
                    throw new \RuntimeException("امکان ساخت پوشه برای {$relPath} وجود ندارد.");
                }
                if (file_put_contents($absPath, $content, LOCK_EX) === false) {
                    throw new \RuntimeException("خطای دسترسی نوشتن روی فایل {$relPath}.");
                }
            }

            foreach ($deletions as $relPath) {
                $absPath = $this->app->basePath($relPath);
                if (is_file($absPath)) {
                    @unlink($absPath);
                }
            }

            // اگر style.css در پچ نبود، نسخه هدر قالب را به نسخه جدید ارتقا بده
            if (!isset($stagedPayloads['style.css']) && $targetVersion !== '' && $targetVersion !== $fromVersion) {
                $this->bumpStyleCssVersion($targetVersion);
            }

            $this->flushThemeCaches();
            $this->pruneOldBackups();

            $changedTotal = count($stagedPayloads) + count($deletions);
            $this->appendHistory([
                'id' => 'patch-' . gmdate('YmdHis'),
                'from_version' => $fromVersion,
                'to_version' => $targetVersion,
                'title' => sanitize_text_field((string) ($manifest['title'] ?? "بروزرسانی به نسخه {$targetVersion}")),
                'notes' => sanitize_textarea_field((string) ($manifest['notes'] ?? '')),
                'files_count' => $changedTotal,
                'backup_id' => $backupId,
                'applied_at' => current_time('mysql'),
                'status' => 'applied',
            ]);

            delete_transient(self::REMOTE_CACHE_TRANSIENT);

            return [
                'ok' => true,
                'message' => sprintf('پچ نسخه %s با موفقیت اعمال شد (%d فایل بروزرسانی شد).', $targetVersion, $changedTotal),
                'target_version' => $targetVersion,
                'changed_count' => $changedTotal,
                'backup_id' => $backupId,
            ];
        } catch (\Throwable $e) {
            if ($backupId !== '') {
                $this->rollback($backupId);
            }
            return [
                'ok' => false,
                'message' => 'بروزرسانی با خطا مواجه شد و قالب به‌صورت خودکار به وضعیت قبل بازگردانی شد: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * ایجاد بکاپ پیش از پچ برای بازگردانی ۱۰۰٪ دقیق
     *
     * @param array<int, string> $relativePaths
     * @return array{ok: bool, backup_id?: string, message: string}
     */
    public function createBackup(array $relativePaths, string $fromVersion, string $targetVersion): array
    {
        $backupId = 'backup-' . preg_replace('/[^a-zA-Z0-9._-]/', '', $fromVersion) . '-' . gmdate('Ymd-His');
        $dir = $this->backupDirectory();

        $existed = [];
        $missing = [];
        $filesData = [];

        foreach ($relativePaths as $rel) {
            $rel = ltrim(str_replace('\\', '/', $rel), '/');
            if (!$this->isSafeRelativePath($rel)) {
                continue;
            }
            $abs = $this->app->basePath($rel);
            if (is_file($abs)) {
                $existed[] = $rel;
                $filesData[$rel] = (string) file_get_contents($abs);
            } else {
                $missing[] = $rel;
            }
        }

        $meta = [
            'backup_id' => $backupId,
            'from_version' => $fromVersion,
            'target_version' => $targetVersion,
            'created_at' => current_time('mysql'),
            'existed_files' => $existed,
            'newly_created_files' => $missing,
        ];

        if (class_exists(ZipArchive::class)) {
            $zipFile = $dir . '/' . $backupId . '.zip';
            $zip = new ZipArchive();
            if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                return ['ok' => false, 'message' => 'امکان ایجاد فایل فشرده بکاپ وجود ندارد.'];
            }
            $zip->addFromString('backup-meta.json', (string) wp_json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            foreach ($filesData as $rel => $content) {
                $zip->addFromString('files/' . $rel, $content);
            }
            $zip->close();
        } else {
            // فالبک JSON در صورت عدم وجود ZipArchive روی سرور
            $encodedFiles = array_map('base64_encode', $filesData);
            $meta['inline_files_base64'] = $encodedFiles;
            $jsonFile = $dir . '/' . $backupId . '.json';
            if (file_put_contents($jsonFile, (string) wp_json_encode($meta, JSON_UNESCAPED_UNICODE)) === false) {
                return ['ok' => false, 'message' => 'امکان ذخیره فایل بکاپ وجود ندارد.'];
            }
        }

        return ['ok' => true, 'backup_id' => $backupId, 'message' => 'بکاپ ایجاد شد.'];
    }

    /**
     * بازگردانی یک‌کلیکی (Rollback) به وضعیت دقیق قبل از پچ
     *
     * @return array{ok: bool, message: string}
     */
    public function rollback(string $backupId): array
    {
        $cleanId = preg_replace('/[^a-zA-Z0-9._-]/', '', $backupId) ?: '';
        if ($cleanId === '') {
            return ['ok' => false, 'message' => 'شناسه بکاپ نامعتبر است.'];
        }

        $dir = $this->backupDirectory();
        $zipFile = $dir . '/' . $cleanId . '.zip';
        $jsonFile = $dir . '/' . $cleanId . '.json';

        if (is_file($zipFile) && class_exists(ZipArchive::class)) {
            $zip = new ZipArchive();
            if ($zip->open($zipFile) !== true) {
                return ['ok' => false, 'message' => 'امکان باز کردن آرشیو بکاپ وجود ندارد.'];
            }
            $metaRaw = $zip->getFromName('backup-meta.json');
            $meta = is_string($metaRaw) ? json_decode($metaRaw, true) : null;
            if (!is_array($meta)) {
                $zip->close();
                return ['ok' => false, 'message' => 'متادیتای بکاپ معتبر نیست.'];
            }

            foreach (($meta['existed_files'] ?? []) as $rel) {
                if (!$this->isSafeRelativePath((string) $rel)) {
                    continue;
                }
                $content = $zip->getFromName('files/' . $rel);
                if ($content !== false) {
                    $abs = $this->app->basePath((string) $rel);
                    wp_mkdir_p(dirname($abs));
                    file_put_contents($abs, $content, LOCK_EX);
                }
            }

            foreach (($meta['newly_created_files'] ?? []) as $rel) {
                if (!$this->isSafeRelativePath((string) $rel)) {
                    continue;
                }
                $abs = $this->app->basePath((string) $rel);
                if (is_file($abs)) {
                    @unlink($abs);
                }
            }

            $zip->close();
        } elseif (is_file($jsonFile)) {
            $meta = json_decode((string) file_get_contents($jsonFile), true);
            if (!is_array($meta)) {
                return ['ok' => false, 'message' => 'فایل بکاپ JSON معتبر نیست.'];
            }
            foreach (($meta['inline_files_base64'] ?? []) as $rel => $b64) {
                if (!$this->isSafeRelativePath((string) $rel)) {
                    continue;
                }
                $content = base64_decode((string) $b64, true);
                if ($content !== false) {
                    $abs = $this->app->basePath((string) $rel);
                    wp_mkdir_p(dirname($abs));
                    file_put_contents($abs, $content, LOCK_EX);
                }
            }
            foreach (($meta['newly_created_files'] ?? []) as $rel) {
                if ($this->isSafeRelativePath((string) $rel) && is_file($this->app->basePath((string) $rel))) {
                    @unlink($this->app->basePath((string) $rel));
                }
            }
        } else {
            return ['ok' => false, 'message' => 'فایل بکاپ مورد نظر یافت نشد.'];
        }

        $this->flushThemeCaches();
        $restoredVer = (string) ($meta['from_version'] ?? $this->currentVersion());

        $this->appendHistory([
            'id' => 'rollback-' . gmdate('YmdHis'),
            'from_version' => $this->currentVersion(),
            'to_version' => $restoredVer,
            'title' => "بازگردانی به نسخه {$restoredVer}",
            'notes' => "بازگردانی موفق از بکاپ {$cleanId}",
            'files_count' => count($meta['existed_files'] ?? []),
            'backup_id' => $cleanId,
            'applied_at' => current_time('mysql'),
            'status' => 'rolled_back',
        ]);

        return [
            'ok' => true,
            'message' => sprintf('قالب با موفقیت به نسخه %s بازگردانی شد.', $restoredVer),
        ];
    }

    /**
     * بررسی آنلاین سرور بروزرسانی (OTA Manifest Check)
     *
     * @return array<string, mixed>
     */
    public function checkRemoteUpdate(bool $force = false): array
    {
        if (!$force) {
            $cached = get_transient(self::REMOTE_CACHE_TRANSIENT);
            if (is_array($cached)) {
                return $cached;
            }
        }

        $manifestUrl = trim((string) rs_option('updater.manifest_url', ''));
        if ($manifestUrl === '') {
            return ['ok' => false, 'has_update' => false, 'message' => 'آدرس سرور مانیفست پچ در تنظیمات وارد نشده است.'];
        }

        $headers = ['Accept' => 'application/json'];
        $licenseKey = trim((string) rs_option('updater.license_key', ''));
        if ($licenseKey !== '') {
            $headers['X-Theme-License'] = $licenseKey;
        }

        $response = wp_remote_get($manifestUrl, [
            'timeout' => 12,
            'headers' => $headers,
        ]);

        if (is_wp_error($response)) {
            return ['ok' => false, 'has_update' => false, 'message' => 'خطا در اتصال به سرور پچ: ' . $response->get_error_message()];
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $body = json_decode((string) wp_remote_retrieve_body($response), true);
        if ($code !== 200 || !is_array($body) || empty($body['latest_version'])) {
            return ['ok' => false, 'has_update' => false, 'message' => 'پاسخ معتبری از مانیفست بروزرسانی دریافت نشد.'];
        }

        $current = $this->currentVersion();
        $latest = sanitize_text_field((string) $body['latest_version']);
        $hasUpdate = version_compare($latest, $current, '>');

        $result = [
            'ok' => true,
            'has_update' => $hasUpdate,
            'current_version' => $current,
            'latest_version' => $latest,
            'download_url' => esc_url_raw((string) ($body['patch_url'] ?? $body['package'] ?? '')),
            'sha256' => sanitize_text_field((string) ($body['sha256'] ?? '')),
            'notes' => sanitize_textarea_field((string) ($body['notes'] ?? $body['changelog'] ?? '')),
            'checked_at' => current_time('mysql'),
            'message' => $hasUpdate
                ? "نسخه جدید {$latest} آماده نصب است!"
                : "قالب شما به آخرین نسخه ({$current}) مجهز است.",
        ];

        set_transient(self::REMOTE_CACHE_TRANSIENT, $result, 6 * HOUR_IN_SECONDS);
        return $result;
    }

    /**
     * دانلود و نصب مستقیم پچ آنلاین از سرور مانیفست
     *
     * @return array{ok: bool, message: string}
     */
    public function installRemotePatch(): array
    {
        $remote = $this->checkRemoteUpdate(true);
        if (empty($remote['ok']) || empty($remote['has_update']) || empty($remote['download_url'])) {
            return ['ok' => false, 'message' => (string) ($remote['message'] ?? 'بسته بروزرسانی آنلاین یافت نشد.')];
        }

        if (!function_exists('download_url')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        $tmpFile = download_url((string) $remote['download_url'], 30);
        if (is_wp_error($tmpFile)) {
            return ['ok' => false, 'message' => 'خطا در دانلود پچ: ' . $tmpFile->get_error_message()];
        }

        if (!empty($remote['sha256']) && (bool) rs_option('updater.verify_checksum', true)) {
            $downloadedHash = hash_file('sha256', $tmpFile);
            if ($downloadedHash === false || !hash_equals(strtolower((string) $remote['sha256']), strtolower($downloadedHash))) {
                @unlink($tmpFile);
                return ['ok' => false, 'message' => 'امضای امنیتی SHA-256 پچ دانلودشده با مانیفست مطابقت ندارد.'];
            }
        }

        $urlPath = (string) parse_url((string) $remote['download_url'], PHP_URL_PATH);
        $ext = strtolower(pathinfo($urlPath, PATHINFO_EXTENSION)) ?: 'zip';
        $result = $this->applyPatchFile($tmpFile, 'remote-patch.' . $ext);
        @unlink($tmpFile);

        return $result;
    }

    /**
     * لیست بکاپ‌های موجود در سرور
     *
     * @return array<int, array<string, mixed>>
     */
    public function listBackups(): array
    {
        $dir = $this->backupDirectory();
        $entries = glob($dir . '/backup-*.{zip,json}', GLOB_BRACE) ?: [];
        rsort($entries);

        $list = [];
        foreach ($entries as $path) {
            $base = basename($path);
            $id = (string) preg_replace('/\.(zip|json)$/i', '', $base);
            $list[] = [
                'id' => $id,
                'file' => $base,
                'size_kb' => round(((int) filesize($path)) / 1024, 1),
                'created_at' => gmdate('Y-m-d H:i:s', (int) filemtime($path)),
            ];
        }

        return $list;
    }

    /**
     * تاریخچه پچ‌ها و بازگردانی‌ها
     *
     * @return array<int, array<string, mixed>>
     */
    public function history(): array
    {
        $log = get_option(self::HISTORY_OPTION, []);
        return is_array($log) ? $log : [];
    }

    public function flushThemeCaches(): void
    {
        if ($this->app->has('blade')) {
            $this->app->make('blade')->flushCache();
        }
        delete_transient('rs_frontpage_cache');
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
    }

    /**
     * @param array<string, mixed> $manifest
     * @return array{ok: bool, message: string}
     */
    private function validateManifest(array $manifest): array
    {
        $themeSlug = (string) ($manifest['theme'] ?? '');
        if ($themeSlug !== 'rangesadeghi') {
            return ['ok' => false, 'message' => 'این بسته پچ متعلق به قالب رنگ صادقی (rangesadeghi) نیست.'];
        }

        if (empty($manifest['target_version']) || !isset($manifest['files']) || !is_array($manifest['files'])) {
            return ['ok' => false, 'message' => 'فیلدهای الزامی target_version یا files در پچ وجود ندارند.'];
        }

        $current = $this->currentVersion();
        if (!empty($manifest['min_version']) && version_compare($current, (string) $manifest['min_version'], '<')) {
            return [
                'ok' => false,
                'message' => sprintf('این پچ نیازمند حداقل نسخه %s است (نسخه فعلی شما: %s).', $manifest['min_version'], $current),
            ];
        }

        return ['ok' => true, 'message' => 'معتبر'];
    }

    /**
     * جلوگیری سخت‌گیرانه از Path Traversal و فایل‌های خطرناک خارج از محدوده قالب
     */
    private function isSafeRelativePath(string $relPath): bool
    {
        if ($relPath === '' || str_contains($relPath, "\0") || str_starts_with($relPath, '/') || str_contains($relPath, '..')) {
            return false;
        }

        // عدم اجازه دستکاری پوشه‌های حساس مخفی یا فایل‌های سیستمی سرور
        $forbiddenPrefixes = ['.git/', '.github/', 'node_modules/', '.htaccess', '.user.ini', 'wp-config'];
        foreach ($forbiddenPrefixes as $prefix) {
            if (str_starts_with(strtolower($relPath), $prefix)) {
                return false;
            }
        }

        // عدم اجازه قرار دادن فایل اجرایی PHP داخل پوشه استاتیک assets/
        $ext = strtolower(pathinfo($relPath, PATHINFO_EXTENSION));
        if (str_starts_with($relPath, 'assets/') && in_array($ext, ['php', 'phtml', 'phar', 'sh'], true)) {
            return false;
        }

        $allowedExtensions = ['php', 'css', 'js', 'json', 'webp', 'png', 'jpg', 'jpeg', 'svg', 'woff2', 'woff', 'md', 'txt', 'pot', 'po', 'mo'];
        return in_array($ext, $allowedExtensions, true);
    }

    private function bumpStyleCssVersion(string $newVersion): void
    {
        $stylePath = $this->app->basePath('style.css');
        if (!is_file($stylePath) || !is_writable($stylePath)) {
            return;
        }

        $content = (string) file_get_contents($stylePath);
        $updated = preg_replace('/^(\s*\*\s*Version:\s*)([^\r\n]+)/mi', '${1}' . $newVersion, $content, 1);
        if (is_string($updated) && $updated !== $content) {
            file_put_contents($stylePath, $updated, LOCK_EX);
        }
    }

    private function pruneOldBackups(): void
    {
        $keep = max(1, (int) rs_option('updater.keep_backups_count', 5));
        $dir = $this->backupDirectory();
        $entries = glob($dir . '/backup-*.{zip,json}', GLOB_BRACE) ?: [];
        rsort($entries);

        foreach (array_slice($entries, $keep) as $oldFile) {
            @unlink($oldFile);
        }
    }

    /**
     * @param array<string, mixed> $entry
     */
    private function appendHistory(array $entry): void
    {
        $history = $this->history();
        array_unshift($history, $entry);
        update_option(self::HISTORY_OPTION, array_slice($history, 0, 40), false);
    }
}
