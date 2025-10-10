<?php

namespace Frugal\Core\Services;

final class FrugalContainer
{
    private static ?self $instance = null;

    private array $factories = [];
    private array $instances = [];

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    public function set(string $id, callable $factory): void
    {
        $this->factories[$id] = $factory;
    }

    public function get(string $id): mixed
    {
        if (!isset($this->instances[$id])) {
            if (!isset($this->factories[$id])) {
                throw new \RuntimeException("Service '$id' non enregistré.");
            }

            $this->instances[$id] = ($this->factories[$id])();
        }

        return $this->instances[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->factories[$id]) || isset($this->instances[$id]);
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
