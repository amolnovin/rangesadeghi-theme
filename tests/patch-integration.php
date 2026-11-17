<?php
/** Run with wp eval-file tests/patch-integration.php after constructing fixtures in RS_PATCH_TEST_DIR. */
declare(strict_types=1);

$root = getenv('RS_PATCH_TEST_DIR');
$source = getenv('RS_PATCH_SOURCE_DIR');
if (!$root || !is_dir($root) || !$source || !is_dir($source)) throw new RuntimeException('Patch test fixture or source directory is missing.');
$files = ['style.css', 'app/Support/ProductPresenter.php', 'app/Updater/PatchManager.php', 'app/Providers/AdminServiceProvider.php'];
$before = [];
foreach ($files as $file) {
    $before[$file] = file_get_contents($root . '/theme/' . $file);
    if (!is_string($before[$file])) throw new RuntimeException('Missing baseline fixture ' . $file);
}
$blade = rs_app('blade');
$options = rs_app('options');
$app = new App\Core\Application($root . '/theme', '');
$app->instance('blade', $blade);
$app->instance('options', $options);
$manager = new App\Updater\PatchManager($app);
foreach (['zip', 'json'] as $extension) {
    $patch = $root . '/rangesadeghi-patch-2.2.1.' . $extension;
    $result = $manager->applyPatchFile($patch);
    if (empty($result['ok'])) throw new RuntimeException("Failed to apply $extension patch: " . ($result['message'] ?? 'unknown'));
    if (($result['target_version'] ?? '') !== '2.2.1' || ($result['changed_count'] ?? 0) !== count($files)) {
        throw new RuntimeException("Unexpected $extension patch result");
    }
    foreach ($files as $file) {
        $expected = file_get_contents($source . '/' . $file);
        if (file_get_contents($root . '/theme/' . $file) !== $expected) throw new RuntimeException("Wrong patched content: $file");
    }
    $rollback = $manager->rollback((string) ($result['backup_id'] ?? ''));
    if (empty($rollback['ok'])) throw new RuntimeException('Rollback failed: ' . ($rollback['message'] ?? 'unknown'));
    foreach ($files as $file) {
        if (file_get_contents($root . '/theme/' . $file) !== $before[$file]) throw new RuntimeException("Rollback failed for $file");
    }
}
echo "ZIP and JSON patch application and rollback passed.\n";
