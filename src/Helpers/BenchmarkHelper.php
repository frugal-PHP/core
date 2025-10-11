<?php

namespace Frugal\Core\Helpers;

class BenchmarkHelper
{
    private float $start;

    public function __construct()
    {
        $this->start = microtime(true);
    }

    public function log(string $label): void
    {
        $duration = (microtime(true) - $this->start) * 1000;
        echo "⏱ $label : " . round($duration, 3) . " ms\n";
    }
}
