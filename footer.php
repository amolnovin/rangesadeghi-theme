<?php
/**
 * فایل سازگاری با افزونه‌هایی که مستقیماً get_footer() را فراخوانی می‌کنند.
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$appData = rs_app()->make(\App\View\Composers\AppComposer::class)->with([]);
echo rs_view('partials.footer', $appData);
wp_footer();
?>
</body>
</html>
