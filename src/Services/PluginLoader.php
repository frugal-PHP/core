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
                $pluginConfigClass = rtrim($namespace, '\\') . '\\PluginConfig';
                if (class_exists($pluginConfigClass)) {
                    echo "⚙️  Chargement de ".$pluginConfigClass::PLUGIN_NAME."\n";
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

    public static function loadRoutes(array $pluginInformations)
    {
        $pluginRouteFiles = $pluginInformations['configClass']::pluginRouteFiles();
        if(!isset($pluginRouteFiles['static']) || $pluginRouteFiles['static'] === "" ||
           !isset($pluginRouteFiles['dynamic']) || $pluginRouteFiles['dynamic'] === ""
        ) {
            echo "🚫 Routes non définies ou invalides\n";
            return;
        }

        $staticPath = $pluginInformations['path'] . $pluginRouteFiles['static'];
        $dynamicPath = $pluginInformations['path'] . $pluginRouteFiles['dynamic'];

        if (!file_exists($staticPath) || !file_exists($dynamicPath)) {
            echo "🚫 Fichier(s) de route introuvable(s)\n";
            return;
        }

        Bootstrap::compileRoute(
            staticFile: $staticPath,
            dynamicFile: $dynamicPath
        );
    }

    public static function loadCommands(array $pluginInformations) : void
    {
        $pluginCommands = $pluginInformations['configClass']::pluginRouteCommands();
        Bootstrap::$commands += $pluginCommands;
    }
}