<?php

declare(strict_types=1);

namespace App\Core;

/**
 * پایه View Composer مشابه Sage 10
 * داده‌های موردنیاز هر قالب Blade را به صورت تمیز و تفکیک‌شده تزریق می‌کند.
 */
abstract class ViewComposer
{
    /** @var array<int, string> نام ویوها (پشتیبانی از wildcard مثل '*') */
    protected static array $views = [];

    public function __construct(
        protected readonly Application $app,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public static function views(): array
    {
        return static::$views;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    abstract public function with(array $data): array;
}
