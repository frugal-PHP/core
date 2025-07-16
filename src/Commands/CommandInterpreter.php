<?php

namespace Frugal\Core\Commands;

use Frugal\Core\Services\Bootstrap;
use Minicli\App;

class CommandInterpreter
{
    public static function run()
    {
        $app = new App();
        foreach(Bootstrap::$commands as $title => $command) {
            $app->registerCommand($title, [$command, 'run']);
        }

        $argv = $_SERVER['argv'];
        $app->runCommand($argv);

        return 0;
    }
}