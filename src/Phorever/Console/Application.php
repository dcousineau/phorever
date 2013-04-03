<?php
namespace Phorever\Console;

use Phorever\Phorever;
use Phorever\Console\Command;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    public function getDefaultCommands() {
        $commands = parent::getDefaultCommands();

        $commands[] = new Command\RolesCommand();
        $commands[] = new Command\StartCommand();
        $commands[] = new Command\StopCommand();
        $commands[] = new Command\StatusCommand();

        return $commands;
    }

    public function getLongVersion() {
        $bigtext = <<<'BIGTEXT'
 /$$$$$$$  /$$
| $$__  $$| $$
| $$  \ $$| $$$$$$$   /$$$$$$   /$$$$$$   /$$$$$$  /$$    /$$ /$$$$$$   /$$$$$$
| $$$$$$$/| $$__  $$ /$$__  $$ /$$__  $$ /$$__  $$|  $$  /$$//$$__  $$ /$$__  $$
| $$____/ | $$  \ $$| $$  \ $$| $$  \__/| $$$$$$$$ \  $$/$$/| $$$$$$$$| $$  \__/
| $$      | $$  | $$| $$  | $$| $$      | $$_____/  \  $$$/ | $$_____/| $$
| $$      | $$  | $$|  $$$$$$/| $$      |  $$$$$$$   \  $/  |  $$$$$$$| $$
|__/      |__/  |__/ \______/ |__/       \_______/    \_/    \_______/|__/
BIGTEXT;

        return "<comment>$bigtext</comment>\n\n" . parent::getLongVersion();
    }
}