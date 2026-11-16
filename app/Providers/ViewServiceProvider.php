<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Blade;
use App\Core\Options;
use App\Core\ServiceProvider;
use App\Core\ViewComposer;

final class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('options', function (): Options {
            $defaultsFile = $this->app->basePath('config/defaults.php');
            $defaults = is_file($defaultsFile) ? (array) require $defaultsFile : [];
            return new Options($defaults);
        });

        $this->app->singleton('blade', function (): Blade {
            $uploads = function_exists('wp_upload_dir') ? wp_upload_dir() : ['basedir' => sys_get_temp_dir()];
            $baseCache = !empty($uploads['basedir']) ? (string) $uploads['basedir'] : sys_get_temp_dir();
            $cachePath = rtrim($baseCache, '/') . '/cache/rangesadeghi-blade';

            return new Blade(
                $this->app,
                $this->app->basePath('resources/views'),
                $cachePath,
            );
        });
    }

    public function boot(): void
    {
        /** @var Blade $blade */
        $blade = $this->app->make('blade');
        $configFile = $this->app->basePath('config/app.php');
        $config = is_file($configFile) ? (array) require $configFile : [];

        /** @var array<int, class-string<ViewComposer>> $composers */
        $composers = $config['composers'] ?? [];
        foreach ($composers as $composerClass) {
            if (class_exists($composerClass) && is_subclass_of($composerClass, ViewComposer::class)) {
                $blade->composer($composerClass::views(), $composerClass);
            }
        }
    }
}
