<?php

namespace Frugal\Core\Plugins;

use Frugal\Core\Services\Bootstrap;

abstract class AbstractPlugin
{
    protected const PLUGIN_NAME="unknown";

    public static function init() : void
    {
        echo "⚙️  Chargement de ".static::PLUGIN_NAME."\n";
        self::registerServices();
    }

    protected static function loadRoutes(?string $staticPath = null, ?string $dynamicPath = null)
    {
        Bootstrap::compileRoute(
            staticFile: $staticPath,
            dynamicFile: $dynamicPath
        );
    }

    protected static function loadCommands(array $commands) : void
    {
        Bootstrap::$commands += $commands;
        echo "  ✅ Commandes ajoutées\n";
    }

    protected static function addStorage(string $storageName) : void
    {
        $storageDir = getenv('STORAGE_DIR').$storageName;
        if(!file_exists($storageDir)) {
            mkdir($storageDir, 0777, true);
        }
    }

    protected static function checkEnvironmentVariables(array $envVars) : void
    {
        foreach($envVars as $var) {
            if(getenv($var) === false) {
                echo "  ❌ variable d'environnement $var manquante.\n";       
            }
        }
    }

    protected static function registerServices(): void
    {
    }
}