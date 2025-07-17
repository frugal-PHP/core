<?php

namespace Frugal\Core\Services;

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

    public static function compileRoute(string $staticFile, string $dynamicFile) : void
    {
        $staticRoutes = require $staticFile;
        foreach($staticRoutes as $method => $routes) {
            foreach($routes as $uri => $handler) {
                self::$compiledRoutes['static'][$method][$uri] = $handler;
            }
        }

        $dynamicRoutes = require $dynamicFile;
        foreach ($dynamicRoutes as $method => $routes) {
            foreach ($routes as $routePath => $handler) {
                $pattern = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $routePath);
                $pattern = "#^" . $pattern . "$#";
                self::$compiledRoutes['dynamic'][$method][] = [
                    'pattern' => $pattern,
                    'handler' => $handler
                ];
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