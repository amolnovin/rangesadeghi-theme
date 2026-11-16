<?php

declare(strict_types=1);

return [
    'providers' => [
        App\Providers\ViewServiceProvider::class,
        App\Providers\ThemeServiceProvider::class,
        App\Providers\AssetsServiceProvider::class,
        App\Providers\WooCommerceServiceProvider::class,
        App\Providers\SeoSchemaServiceProvider::class,
        App\Providers\PerformanceServiceProvider::class,
        App\Providers\UpdaterServiceProvider::class,
        App\Providers\AdminServiceProvider::class,
    ],
    'composers' => [
        App\View\Composers\AppComposer::class,
        App\View\Composers\FrontPageComposer::class,
        App\View\Composers\ShopComposer::class,
        App\View\Composers\SingleProductComposer::class,
        App\View\Composers\BlogComposer::class,
        App\View\Composers\CheckoutCartComposer::class,
    ],
];
