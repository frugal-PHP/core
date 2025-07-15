<?php

namespace Frugal\Core\Commands;

use Minicli\App;

class CommandInterpreter
{
    public static function run()
    {
        $app = new App();
        $app->registerCommands([]);

        $argv = $_SERVER['argv'];
        $app->runCommand($argv);

        return 0;
    }
}