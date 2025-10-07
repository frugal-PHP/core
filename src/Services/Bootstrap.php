<?php

namespace Frugal\Core\Services;

use RuntimeException;

class Bootstrap
{
    public static array $compiledRoutes = [];
    public static array $commands = [];

    public static function loadEnv(?string $filename = null) : void
    {
        $filename = $filename ?? ROOT_DIR."/.env";
        if(file_exists($filename)) {
            foreach (parse_ini_file($filename) as $key => $val) {
                $_ENV[$key] = $val;
                putenv("$key=$val");
            }
        } else {
            throw new RuntimeException("Can not load .env : ".$filename);
        }
    }

    public static function addCommand(string $commandTitle, string $commandClassName)
    {
        self::$commands[$commandTitle] = $commandClassName;
    }

    public static function autoloadPlugins() : void
    {
        foreach(PluginLoader::getPlugins() as $plugin) {
            $plugin::init();
        }
    }

    public static function env(string $varName) : mixed
    {
        return $_ENV[$varName];
    }
}