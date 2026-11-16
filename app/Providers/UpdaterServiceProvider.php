<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\ServiceProvider;
use App\Updater\PatchManager;

final class UpdaterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('patcher', fn(): PatchManager => new PatchManager($this->app));
    }

    public function boot(): void
    {
        // اتصال به اعلان بروزرسانی بومی وردپرس در صورت فعال بودن بررسی خودکار
        if ((bool) rs_option('updater.check_wp_updates', true)) {
            add_filter('pre_set_site_transient_update_themes', [$this, 'injectThemeUpdateNotification']);
        }
    }

    public function injectThemeUpdateNotification(mixed $transient): mixed
    {
        if (!is_object($transient) || empty($transient->checked)) {
            return $transient;
        }

        /** @var PatchManager $patcher */
        $patcher = $this->app->make('patcher');
        $remote = $patcher->checkRemoteUpdate(false);

        if (!empty($remote['ok']) && !empty($remote['has_update'])) {
            $slug = get_template();
            $transient->response[$slug] = [
                'theme' => $slug,
                'new_version' => (string) $remote['latest_version'],
                'url' => admin_url('admin.php?page=rangesadeghi-settings#tab-updater'),
                // A delta archive is not a complete theme ZIP. Never hand it to
                // WordPress's theme upgrader, which replaces the whole directory.
                'package' => '',
            ];
        }

        return $transient;
    }
}
