<?php
/**
 * سازگاری استاندارد سیستم دیدگاه‌های وردپرس
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

if (post_password_required()) {
    return;
}
