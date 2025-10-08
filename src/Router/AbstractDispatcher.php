<?php

namespace Frugal\Core\Router;

use Frugal\Core\Interfaces\RouterDispatcherInterface;

abstract class AbstractDispatcher implements RouterDispatcherInterface
{
    protected function getAllRoutes() : array
    {
        $allRoutes = [];
        $classMap = require ROOT_DIR . '/vendor/composer/autoload_classmap.php';
        $baseNamespace = $this->getPsr4NamespaceForSrc();

        foreach ($classMap as $class => $path) {
            if (str_starts_with($class, $baseNamespace) && is_subclass_of($class, Route::class)) {
                $allRoutes[] = new $class();
            }
        }

        return $allRoutes;
    }

    protected function getPsr4NamespaceForSrc(): ?string {
        $composerData = json_decode(file_get_contents(ROOT_DIR."/composer.json"), true);

        if (!isset($composerData['autoload']['psr-4'])) {
            return null;
        }

        foreach ($composerData['autoload']['psr-4'] as $namespace => $path) {
            if (rtrim($path, '/') === 'src') {
                return $namespace;
            }
        }

        return null;
    }
}