<?php

namespace Frugal\Core\Services;

class PluginLoader
{
    public static function getPlugins() : array
    {
        $plugins = [];
        $psr4classesAndDirectories = require getenv('ROOT_DIR').'/vendor/composer/autoload_psr4.php';
        foreach($psr4classesAndDirectories as $namespace => $paths) {
            if(strpos($namespace,'FrugalPhpPlugin') === 0) {
                $pluginConfigClass = rtrim($namespace, '\\') . '\\PluginConfig';
                if (class_exists($pluginConfigClass)) {
                    $plugins[] = array(
                        'configClass' => $pluginConfigClass,
                        'path' => current($paths)
                    );
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