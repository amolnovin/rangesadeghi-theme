<?php

declare(strict_types=1);

defined('ABSPATH') || exit;

require_once __DIR__ . '/autoload.php';

$app = new \App\Core\Application(dirname(__DIR__), get_template_directory_uri());
$config = require dirname(__DIR__) . '/config/app.php';
foreach ($config['providers'] as $provider) {
    $app->register($provider);
}
$app->boot();

return $app;
