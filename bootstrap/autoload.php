<?php

declare(strict_types=1);

/**
 * اتولودر استاندارد PSR-4 بدون نیاز به Composer (معماری Sage)
 * فضای نام App\ را مستقیماً به پوشه app/ نگاشت می‌کند.
 */

if (!defined('ABSPATH')) {
    exit;
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    $length = strlen($prefix);

    if (strncmp($prefix, $class, $length) !== 0) {
        return;
    }

    $relative = substr($class, $length);
    $file = dirname(__DIR__) . '/app/' . str_replace('\\', '/', $relative) . '.php';

    if (is_file($file)) {
        require_once $file;
    }
});

require_once dirname(__DIR__) . '/app/helpers.php';
