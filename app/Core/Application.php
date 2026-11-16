<?php

declare(strict_types=1);

namespace App\Core;

use Closure;
use InvalidArgumentException;

/**
 * هسته کانتینر تزریق وابستگی (Service Container) قالب بر پایه معماری Sage
 * سبک، سریع و بدون هیچ پکیج خارجی یا Composer.
 */
final class Application
{
    private static ?self $instance = null;

    /** @var array<string, array{concrete: Closure|object|string, shared: bool}> */
    private array $bindings = [];

    /** @var array<string, mixed> */
    private array $instances = [];

    /** @var array<int, ServiceProvider> */
    private array $providers = [];

    private bool $booted = false;

    public function __construct(
        private readonly string $basePath,
        private readonly string $baseUri,
    ) {
        self::$instance = $this;
        $this->instance('app', $this);
        $this->instance(self::class, $this);
    }

    public static function getInstance(): ?self
    {
        return self::$instance;
    }

    public function basePath(string $path = ''): string
    {
        return $path === ''
            ? $this->basePath
            : $this->basePath . '/' . ltrim($path, '/');
    }

    public function baseUri(string $path = ''): string
    {
        return $path === ''
            ? $this->baseUri
            : rtrim($this->baseUri, '/') . '/' . ltrim($path, '/');
    }

    public function bind(string $abstract, object|string $concrete, bool $shared = false): void
    {
        $this->bindings[$abstract] = [
            'concrete' => $concrete,
            'shared' => $shared,
        ];
    }

    public function singleton(string $abstract, object|string $concrete): void
    {
        $this->bind($abstract, $concrete, true);
    }

    public function instance(string $abstract, mixed $instance): mixed
    {
        $this->instances[$abstract] = $instance;
        return $instance;
    }

    public function has(string $abstract): bool
    {
        return array_key_exists($abstract, $this->instances)
            || array_key_exists($abstract, $this->bindings);
    }

    public function make(string $abstract): mixed
    {
        if (array_key_exists($abstract, $this->instances)) {
            return $this->instances[$abstract];
        }

        if (isset($this->bindings[$abstract])) {
            $binding = $this->bindings[$abstract];
            $concrete = $binding['concrete'];

            $object = match (true) {
                $concrete instanceof Closure => $concrete($this),
                is_object($concrete) => $concrete,
                is_string($concrete) && class_exists($concrete) => new $concrete($this),
                default => $concrete,
            };

            if ($binding['shared']) {
                $this->instances[$abstract] = $object;
            }

            return $object;
        }

        if (class_exists($abstract)) {
            return new $abstract($this);
        }

        throw new InvalidArgumentException(sprintf('Service [%s] is not bound in container.', $abstract));
    }

    /**
     * @param class-string<ServiceProvider>|ServiceProvider $provider
     */
    public function register(ServiceProvider|string $provider): ServiceProvider
    {
        $instance = is_string($provider) ? new $provider($this) : $provider;
        $instance->register();
        $this->providers[] = $instance;

        if ($this->booted) {
            $instance->boot();
        }

        return $instance;
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        foreach ($this->providers as $provider) {
            $provider->boot();
        }

        $this->booted = true;
    }
}
