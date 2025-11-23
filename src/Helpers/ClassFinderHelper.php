<?php

namespace Frugal\Core\Helpers;

class ClassFinderHelper
{
    protected static ?array $classMap = null;

    public static function getClassesFromNamespace(string $namespace) : array
    {
        $matchingClasses = [];
        foreach (array_keys(self::getClassmap()) as $class) {
            if (str_starts_with($class, $namespace)) {
                $matchingClasses[] = $class;
            }
        }

        return $matchingClasses;
    }

    protected static function getClassmap() : array
    {
        if (self::$classMap === null) {
            self::$classMap = require ROOT_DIR . '/vendor/composer/autoload_classmap.php';
        }

        return self::$classMap;
    }
}
