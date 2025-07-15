<?php

namespace Frugal\Core\Services;

class Bootstrap
{
    public static function loadEnv(string $filename = ROOT_DIR."/.env") : void
    {
        if(file_exists($filename)) {
            foreach (parse_ini_file($filename) as $key => $val) {
                $_ENV[$key] = $val;
                putenv("$key=$val");
            }
        }
    }

    public static function compileRoute() : Router
    {
        $compiled = [];

        $staticRoutes = require ROOT_DIR."/config/routing/static.php";
        foreach($staticRoutes as $method => $routes) {
            foreach($routes as $uri => $handler) {
                $compiled['static'][$method][$uri] = $handler;
            }
        }

        $dynamicRoutes = require ROOT_DIR."/config/routing/dynamic.php";
        foreach ($dynamicRoutes as $method => $routes) {
            foreach ($routes as $routePath => $handler) {
                $pattern = preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $routePath);
                $pattern = "#^" . $pattern . "$#";
                $compiled['dynamic'][$method][] = [
                    'pattern' => $pattern,
                    'handler' => $handler
                ];
            }
        }

        return new Router($compiled);
    }
}