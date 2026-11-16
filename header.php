<?php
/**
 * فایل سازگاری با افزونه‌هایی که مستقیماً get_header() را فراخوانی می‌کنند.
 * قالب اصلی از موتور Blade در resources/views/layouts/app.blade.php استفاده می‌کند.
 */

declare(strict_types=1);

defined('ABSPATH') || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?> dir="rtl">
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php
echo rs_view('partials.svg-sprite');
$appData = rs_app()->make(\App\View\Composers\AppComposer::class)->with([]);
echo rs_view('partials.header', $appData);
