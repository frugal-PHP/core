<?php

namespace Frugal\Core\Services;

class PluginLoader
{
    public static function getPlugins() : array
    {
        $plugins = [];
        $psr4classesAndDirectories = require ROOT_DIR.'/vendor/composer/autoload_psr4.php';
        foreach($psr4classesAndDirectories as $namespace => $paths) {
            if(strpos($namespace,'FrugalPhpPlugin') === 0) {
                $pluginConfigClass = rtrim($namespace, '\\') . '\\Plugin';
                if (class_exists($pluginConfigClass)) {
                    $plugins[] = $pluginConfigClass;
                } else {
                    echo "❌ Classe introuvable : $pluginConfigClass\n";
                }
            }
        }

        if (empty($plugins)) {
            echo "[PluginLoader] ⏭️ Aucun plugin trouvé.\n";
        }

        return $plugins;
    }
}