<?php
/** Minimal framework smoke test; does not replace WordPress/WooCommerce integration tests.
 * Run: php tests/smoke.php
 */
declare(strict_types=1);

define('ABSPATH', __DIR__);
function get_template_directory_uri(): string { return 'https://example.test/theme'; }
function is_admin(): bool { return false; }
function add_action(...$args): void {}
function add_filter(...$args): void {}
function get_option($name, $default = false): mixed { return $default; }
function wp_upload_dir(): array { return ['basedir' => sys_get_temp_dir()]; }
function wp_mkdir_p($dir): bool { return is_dir($dir) || mkdir($dir, 0777, true); }

require dirname(__DIR__) . '/functions.php';
$blade = rs_app('blade');
foreach ((require dirname(__DIR__) . '/config/app.php')['composers'] as $class) {
    if (!class_exists($class)) throw new RuntimeException("Composer failed to autoload: $class");
}
$source = '@forelse($a as $x)A@empty E1 @endforelse @forelse($b as $x)B@empty E2 @endforelse';
$compiled = $blade->compileString($source);
$a = [];
$b = [1];
$__env = $blade;
ob_start();
eval('?>' . $compiled);
$output = trim((string) ob_get_clean());
if ($output !== 'E1  B') throw new RuntimeException('Sequential forelse compilation failed: ' . $output);
$source = '@forelse($a as $x)A@forelse($b as $y)B@empty E2 @endforelse@empty E1 @endforelse';
$compiled = $blade->compileString($source);
$a = [1];
$b = [];
ob_start();
eval('?>' . $compiled);
$output = trim((string) ob_get_clean());
if ($output !== 'A E2') throw new RuntimeException('Nested forelse compilation failed: ' . $output);
echo "Bootstrap, autoload and Blade forelse smoke tests passed.\n";
