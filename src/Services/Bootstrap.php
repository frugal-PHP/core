<?php

namespace Frugal\Core\Services;

use Wilaak\Http\RadixRouter;

class Bootstrap
{
    public static array $compiledRoutes = [];
    public static array $commands = [];

    public static function loadEnv(string $filename = ROOT_DIR."/.env") : void
    {
        if(file_exists($filename)) {
            foreach (parse_ini_file($filename) as $key => $val) {
                $_ENV[$key] = $val;
                putenv("$key=$val");
            }
        }
    }

    public static function addCommand(string $commandTitle, string $commandClassName)
    {
        self::$commands[$commandTitle] = $commandClassName;
    }

    public static function compileRoute(
        string $routingFile, 
        RadixRouter $router
    ) : void
    {
        $routes = require $routingFile;
        foreach($routes as $method => $data) {
            foreach($data as $uri => $handler) {
                $router->add($method, $uri, $handler);
            }
        }
    }

    public static function autoloadPlugins() : void
    {
        foreach(PluginLoader::getPlugins() as $plugin) {
            $plugin::init();
        }
    }
}