<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;
use Throwable;

/**
 * موتور قالب Blade فوق‌سبک و بدون وابستگی Composer (ویژه معماری Sage)
 * کامپایل خودکار فایل‌های .blade.php در پوشه کش امن uploads همراه با پشتیبانی کامل از View Composers.
 */
final class Blade
{
    /** @var array<string, array<int, class-string<ViewComposer>|callable>> */
    private array $composers = [];

    /** @var array<string, string> */
    private array $sections = [];

    /** @var array<int, string> */
    private array $sectionStack = [];

    /** @var array<string, array<int, string>> */
    private array $pushes = [];

    /** @var array<int, string> */
    private array $pushStack = [];

    /** @var array<int, bool> */
    private array $forelseStack = [];

    public function __construct(
        private readonly Application $app,
        private readonly string $viewsPath,
        private readonly string $cachePath,
    ) {
        $this->ensureCacheDirectory();
    }

    /**
     * ثبت View Composer برای یک یا چند ویو
     *
     * @param string|array<int, string> $views
     * @param class-string<ViewComposer>|callable $composer
     */
    public function composer(string|array $views, string|callable $composer): void
    {
        foreach ((array) $views as $view) {
            $this->composers[$view][] = $composer;
        }
    }

    /**
     * رندر ویو و بازگردانی رشته HTML
     *
     * @param array<string, mixed> $data
     */
    public function render(string $view, array $data = []): string
    {
        error_log("RSDBG render " . $view);
        return $this->renderInternal($view, $data, true);
    }

    /**
     * چاپ مستقیم ویو
     *
     * @param array<string, mixed> $data
     */
    public function display(string $view, array $data = []): void
    {
        echo $this->render($view, $data);
    }

    public function exists(string $view): bool
    {
        return is_file($this->resolveViewPath($view));
    }

    /**
     * پاک‌سازی کامل فایل‌های کامپایل‌شده Blade
     */
    public function clearCache(): int
    {
        if (!is_dir($this->cachePath)) {
            return 0;
        }

        $deleted = 0;
        $files = glob($this->cachePath . '/*.php') ?: [];
        foreach ($files as $file) {
            if (is_file($file) && @unlink($file)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * آمار کش ویوها برای پنل سرعت
     *
     * @return array{count: int, bytes: int, path: string}
     */
    public function cacheStats(): array
    {
        $files = is_dir($this->cachePath) ? (glob($this->cachePath . '/*.php') ?: []) : [];
        $bytes = 0;
        foreach ($files as $file) {
            $bytes += (int) @filesize($file);
        }

        return [
            'count' => count($files),
            'bytes' => $bytes,
            'path' => $this->cachePath,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function renderPartial(string $view, array $data = []): string
    {
        return $this->renderInternal($view, $data, false);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function renderPartialIf(string $view, array $data = []): string
    {
        return $this->exists($view) ? $this->renderInternal($view, $data, false) : '';
    }

    public function startSection(string $name, ?string $inlineContent = null): void
    {
        if ($inlineContent !== null) {
            $this->sections[$name] = $inlineContent;
            return;
        }

        $this->sectionStack[] = $name;
        ob_start();
    }

    public function stopSection(): void
    {
        if ($this->sectionStack === []) {
            return;
        }

        $name = array_pop($this->sectionStack);
        $this->sections[$name] = (string) ob_get_clean();
        error_log('RSDBG stop ' . $name . ' bytes=' . strlen($this->sections[$name]));
    }

    public function yieldContent(string $name, string $default = ''): string
    {
        error_log('RSDBG yield ' . $name . ' bytes=' . strlen($this->sections[$name] ?? ''));
        return $this->sections[$name] ?? $default;
    }

    public function hasSection(string $name): bool
    {
        return isset($this->sections[$name]) && $this->sections[$name] !== '';
    }

    public function startPush(string $name): void
    {
        $this->pushStack[] = $name;
        ob_start();
    }

    public function stopPush(): void
    {
        if ($this->pushStack === []) {
            return;
        }

        $name = array_pop($this->pushStack);
        $this->pushes[$name][] = (string) ob_get_clean();
    }

    public function yieldStack(string $name): string
    {
        return isset($this->pushes[$name]) ? implode("\n", $this->pushes[$name]) : '';
    }

    public function escape(mixed $value): string
    {
        if ($value === null || $value === false) {
            return '';
        }
        if (is_scalar($value) || (is_object($value) && method_exists($value, '__toString'))) {
            return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
        }
        return '';
    }

    /**
     * کامپایل رشته قالب Blade به کد PHP استاندارد (قابل تست مستقل)
     */
    public function compileString(string $source): string
    {
        $this->forelseStack = [];

        // ۱) حذف کامنت‌های Blade {{-- ... --}}
        $source = (string) preg_replace('/\{\{--.*?--\}\}/s', '', $source);

        // ۲) استخراج @extends('layout') اگر وجود دارد
        $parentLayout = null;
        if (preg_match('/^\s*@extends\(\s*[\'"]([^\'"]+)[\'"]\s*\)/m', $source, $match)) {
            $parentLayout = $match[1];
            $source = (string) preg_replace('/^\s*@extends\(\s*[\'"][^\'"]+[\'"]\s*\)\s*/m', '', $source, 1);
        }

        // ۳) بلوک‌های @php ... @endphp
        $source = (string) preg_replace('/@php\b(.*?)@endphp\b/s', '<?php $1 ?>', $source);

        // ۴) دایرکتیوهای ساختار و سکشن‌ها
        $source = (string) preg_replace_callback(
            '/@section\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*(.+?)\s*\)/',
            static fn(array $m): string => sprintf('<?php $__env->startSection(%s, (string) (%s)); ?>', var_export($m[1], true), $m[2]),
            $source
        );
        $source = (string) preg_replace_callback(
            '/@section\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
            static fn(array $m): string => sprintf('<?php $__env->startSection(%s); ?>', var_export($m[1], true)),
            $source
        );
        $source = str_replace(['@endsection', '@stop'], '<?php $__env->stopSection(); ?>', $source);

        $source = (string) preg_replace_callback(
            '/@yield\(\s*[\'"]([^\'"]+)[\'"](?:\s*,\s*(.+?))?\s*\)/',
            static fn(array $m): string => sprintf(
                '<?php echo $__env->yieldContent(%s, %s); ?>',
                var_export($m[1], true),
                $m[2] ?? "''"
            ),
            $source
        );

        $source = (string) preg_replace_callback(
            '/@hasSection\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
            static fn(array $m): string => sprintf('<?php if ($__env->hasSection(%s)): ?>', var_export($m[1], true)),
            $source
        );

        // ۵) @push / @endpush / @stack
        $source = (string) preg_replace_callback(
            '/@push\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
            static fn(array $m): string => sprintf('<?php $__env->startPush(%s); ?>', var_export($m[1], true)),
            $source
        );
        $source = str_replace('@endpush', '<?php $__env->stopPush(); ?>', $source);
        $source = (string) preg_replace_callback(
            '/@stack\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
            static fn(array $m): string => sprintf('<?php echo $__env->yieldStack(%s); ?>', var_export($m[1], true)),
            $source
        );

        // ۶) @include و @includeIf با پشتیبانی از پرانتزهای تودرتو
        $source = $this->compileBalancedDirective($source, 'includeIf', static function (string $expr): string {
            [$viewExpr, $dataExpr] = self::splitTwoArgs($expr);
            return sprintf(
                '<?php echo $__env->renderPartialIf(%s, array_merge($__data, %s)); ?>',
                $viewExpr,
                $dataExpr ?? '[]'
            );
        });
        $source = $this->compileBalancedDirective($source, 'include', static function (string $expr): string {
            [$viewExpr, $dataExpr] = self::splitTwoArgs($expr);
            return sprintf(
                '<?php echo $__env->renderPartial(%s, array_merge($__data, %s)); ?>',
                $viewExpr,
                $dataExpr ?? '[]'
            );
        });

        // Compile each forelse/empty/endforelse in source order, including nesting.
        $source = $this->compileForelse($source);

        // ۸) شرط‌ها و حلقه‌های دارای پرانتز متوازن
        $balancedMap = [
            'if' => static fn(string $e): string => "<?php if ({$e}): ?>",
            'elseif' => static fn(string $e): string => "<?php elseif ({$e}): ?>",
            'unless' => static fn(string $e): string => "<?php if (!({$e})): ?>",
            'isset' => static fn(string $e): string => "<?php if (isset({$e})): ?>",
            'empty' => static fn(string $e): string => "<?php if (empty({$e})): ?>",
            'foreach' => static fn(string $e): string => "<?php foreach ({$e}): ?>",
            'for' => static fn(string $e): string => "<?php for ({$e}): ?>",
            'while' => static fn(string $e): string => "<?php while ({$e}): ?>",
            'checked' => static fn(string $e): string => "<?php if ({$e}) echo 'checked'; ?>",
            'selected' => static fn(string $e): string => "<?php if ({$e}) echo 'selected'; ?>",
            'disabled' => static fn(string $e): string => "<?php if ({$e}) echo 'disabled'; ?>",
            'readonly' => static fn(string $e): string => "<?php if ({$e}) echo 'readonly'; ?>",
        ];

        foreach ($balancedMap as $directive => $transformer) {
            $source = $this->compileBalancedDirective($source, $directive, $transformer);
        }

        // ۹) دایرکتیوهای ساده بدون آرگومان و وردپرسی
        $simpleReplacements = [
            '@else' => '<?php else: ?>',
            '@endif' => '<?php endif; ?>',
            '@endunless' => '<?php endif; ?>',
            '@endisset' => '<?php endif; ?>',
            '@endempty' => '<?php endif; ?>',
            '@endforeach' => '<?php endforeach; ?>',
            '@endfor' => '<?php endfor; ?>',
            '@endwhile' => '<?php endwhile; ?>',
            '@auth' => '<?php if (function_exists("is_user_logged_in") && is_user_logged_in()): ?>',
            '@endauth' => '<?php endif; ?>',
            '@guest' => '<?php if (!function_exists("is_user_logged_in") || !is_user_logged_in()): ?>',
            '@endguest' => '<?php endif; ?>',
            '@wphead' => '<?php if (function_exists("wp_head")) wp_head(); ?>',
            '@wpfooter' => '<?php if (function_exists("wp_footer")) wp_footer(); ?>',
            '@wpbodyopen' => '<?php if (function_exists("wp_body_open")) wp_body_open(); ?>',
        ];

        foreach ($simpleReplacements as $needle => $php) {
            $source = (string) preg_replace('/' . preg_quote($needle, '/') . '\b/', $php, $source);
        }

        // ۱۰) عبارات خروجی خام {!! $raw !!} و امن {{ $escaped }}
        $source = (string) preg_replace('/\{!!\s*(.+?)\s*!!\}/s', '<?php echo $1; ?>', $source);
        $source = (string) preg_replace('/\{\{\s*(.+?)\s*\}\}/s', '<?php echo $__env->escape($1); ?>', $source);

        // ۱۱) اگر @extends داشت، خروجی والد را در انتها رندر کن
        if ($parentLayout !== null) {
            $source .= sprintf(
                "\n<?php echo \$__env->renderPartial(%s, \$__data); ?>",
                var_export($parentLayout, true)
            );
        }

        return $source;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function renderInternal(string $view, array $data, bool $resetState): string
    {
        if ($resetState) {
            $this->sections = [];
            $this->sectionStack = [];
            $this->pushes = [];
            $this->pushStack = [];
        }

        $viewPath = $this->resolveViewPath($view);
        if (!is_file($viewPath)) {
            throw new RuntimeException(sprintf('Blade view [%s] not found at [%s].', $view, $viewPath));
        }

        $data = $this->applyComposers($view, $data);
        $compiledPath = $this->getCompiledPath($view, $viewPath);

        return $this->evaluateView($compiledPath, $viewPath, $data);
    }

    private function resolveViewPath(string $view): string
    {
        $normalized = str_replace('.', '/', trim($view));
        return rtrim($this->viewsPath, '/') . '/' . $normalized . '.blade.php';
    }

    private function getCompiledPath(string $view, string $viewPath): ?string
    {
        if (!is_dir($this->cachePath) || !is_writable($this->cachePath)) {
            return null;
        }

        $hash = sha1($view . '|' . $viewPath);
        $compiledFile = rtrim($this->cachePath, '/') . '/' . $hash . '.php';

        if (!is_file($compiledFile) || (int) @filemtime($compiledFile) < (int) @filemtime($viewPath)) {
            $compiled = $this->compileString((string) file_get_contents($viewPath));
            @file_put_contents($compiledFile, $compiled, LOCK_EX);
        }

        return is_file($compiledFile) ? $compiledFile : null;
    }

    /**
     * @param array<string, mixed> $__data
     */
    private function evaluateView(?string $__compiledPath, string $__viewPath, array $__data): string
    {
        $__env = $this;
        extract($__data, EXTR_SKIP);

        $level = ob_get_level();
        ob_start();

        try {
            if ($__compiledPath !== null) {
                include $__compiledPath;
            } else {
                $__code = $this->compileString((string) file_get_contents($__viewPath));
                eval('?>' . $__code);
            }
        } catch (Throwable $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
            throw $e;
        }

        return (string) ob_get_clean();
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function applyComposers(string $view, array $data): array
    {
        foreach ($this->composers as $pattern => $callbacks) {
            if ($pattern !== '*' && $pattern !== $view && !fnmatch($pattern, $view)) {
                continue;
            }

            foreach ($callbacks as $composer) {
                if (is_string($composer) && class_exists($composer)) {
                    /** @var ViewComposer $instance */
                    $instance = $this->app->make($composer);
                    $data = array_merge($data, $instance->with($data));
                } elseif (is_callable($composer)) {
                    $extra = $composer($data, $view);
                    if (is_array($extra)) {
                        $data = array_merge($data, $extra);
                    }
                }
            }
        }

        return $data;
    }

    private function compileForelse(string $source): string
    {
        $stack = [];
        $nextId = 0;
        $offset = 0;
        while (preg_match('/@forelse\s*\(|@empty(?!\s*\()\b|@endforelse\b/', $source, $match, PREG_OFFSET_CAPTURE, $offset)) {
            $token = $match[0][0];
            $start = $match[0][1];
            $length = strlen($token);
            if (str_starts_with($token, '@forelse')) {
                $open = $start + $length - 1;
                $close = $this->findMatchingParen($source, $open);
                if ($close === null) {
                    throw new RuntimeException('Unclosed @forelse expression.');
                }
                $expr = substr($source, $open + 1, $close - $open - 1);
                if (!preg_match('/^(.+?)\s+as\s+(.+)$/s', $expr, $parts)) {
                    throw new RuntimeException('Invalid @forelse expression: ' . $expr);
                }
                $id = ++$nextId;
                $stack[] = $id;
                $replacement = sprintf('<?php $__empty_%1$d = true; foreach ((%2$s) ?: [] as %3$s): $__empty_%1$d = false; ?>', $id, trim($parts[1]), trim($parts[2]));
                $length = $close - $start + 1;
            } elseif ($token === '@empty') {
                if ($stack === []) throw new RuntimeException('Unexpected @empty.');
                $replacement = sprintf('<?php endforeach; if ($__empty_%d): ?>', end($stack));
            } else {
                if ($stack === []) throw new RuntimeException('Unexpected @endforelse.');
                array_pop($stack);
                $replacement = '<?php endif; ?>';
            }
            $source = substr_replace($source, $replacement, $start, $length);
            $offset = $start + strlen($replacement);
        }
        if ($stack !== []) throw new RuntimeException('Unclosed @forelse.');
        return $source;
    }

    private function compileBalancedDirective(string $source, string $directive, callable $callback): string
    {
        $pattern = '/@' . preg_quote($directive, '/') . '\s*\(/';
        $offset = 0;

        while (preg_match($pattern, $source, $match, PREG_OFFSET_CAPTURE, $offset)) {
            $start = (int) $match[0][1];
            $openParenPos = $start + strlen($match[0][0]) - 1;
            $closeParenPos = $this->findMatchingParen($source, $openParenPos);

            if ($closeParenPos === null) {
                $offset = $openParenPos + 1;
                continue;
            }

            $inner = substr($source, $openParenPos + 1, $closeParenPos - $openParenPos - 1);
            $replacement = (string) $callback($inner);

            $source = substr($source, 0, $start) . $replacement . substr($source, $closeParenPos + 1);
            $offset = $start + strlen($replacement);
        }

        return $source;
    }

    private function findMatchingParen(string $source, int $openPos): ?int
    {
        $len = strlen($source);
        $depth = 0;
        $inSingle = false;
        $inDouble = false;

        for ($i = $openPos; $i < $len; $i++) {
            $ch = $source[$i];
            $prev = $i > 0 ? $source[$i - 1] : '';

            if ($ch === "'" && !$inDouble && $prev !== '\\') {
                $inSingle = !$inSingle;
                continue;
            }
            if ($ch === '"' && !$inSingle && $prev !== '\\') {
                $inDouble = !$inDouble;
                continue;
            }
            if ($inSingle || $inDouble) {
                continue;
            }

            if ($ch === '(') {
                $depth++;
            } elseif ($ch === ')') {
                $depth--;
                if ($depth === 0) {
                    return $i;
                }
            }
        }

        return null;
    }

    /**
     * @return array{0: string, 1: ?string}
     */
    private static function splitTwoArgs(string $expr): array
    {
        $len = strlen($expr);
        $depth = 0;
        $inSingle = false;
        $inDouble = false;

        for ($i = 0; $i < $len; $i++) {
            $ch = $expr[$i];
            $prev = $i > 0 ? $expr[$i - 1] : '';

            if ($ch === "'" && !$inDouble && $prev !== '\\') {
                $inSingle = !$inSingle;
            } elseif ($ch === '"' && !$inSingle && $prev !== '\\') {
                $inDouble = !$inDouble;
            } elseif (!$inSingle && !$inDouble) {
                if (in_array($ch, ['(', '[', '{'], true)) {
                    $depth++;
                } elseif (in_array($ch, [')', ']', '}'], true)) {
                    $depth--;
                } elseif ($ch === ',' && $depth === 0) {
                    return [trim(substr($expr, 0, $i)), trim(substr($expr, $i + 1))];
                }
            }
        }

        return [trim($expr), null];
    }

    private function ensureCacheDirectory(): void
    {
        if (!is_dir($this->cachePath)) {
            @mkdir($this->cachePath, 0755, true);
        }

        $htaccess = rtrim($this->cachePath, '/') . '/.htaccess';
        if (is_dir($this->cachePath) && !is_file($htaccess)) {
            @file_put_contents($htaccess, "Deny from all\n");
            @file_put_contents(rtrim($this->cachePath, '/') . '/index.php', "<?php // Silence is golden.\n");
        }
    }
}
