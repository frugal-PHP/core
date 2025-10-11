<?php

namespace Frugal\Core\Plugins;

use Frugal\Core\Services\Bootstrap;

abstract class AbstractPlugin
{
    protected const PLUGIN_NAME="unknown";
    public const PLUGIN_ROOT_PATH=__DIR__."/../";

    public function init() : void
    {
        echo "⚙️ Chargement de ".static::PLUGIN_NAME."\n";
        static::registerServices();
    }

    protected function loadCommands(array $commands) : void
    {
        Bootstrap::$commands += $commands;
        echo "  ✅ Commandes ajoutées\n";
    }

    protected function addStorage(string $storageName) : void
    {
        $storageDir = getenv('STORAGE_DIR').$storageName;
        if (!file_exists($storageDir)) {
            mkdir($storageDir, 0777, true);
        }
    }

    protected function checkEnvironmentVariables(array $envVars) : void
    {
        foreach ($envVars as $var) {
            if (getenv($var) === false) {
                echo "  ❌ variable d'environnement $var manquante.\n";
            }
        }
    }

    protected function registerServices(): void
    {
    }
}
