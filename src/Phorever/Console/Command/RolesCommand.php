<?php

namespace Phorever\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class RolesCommand extends ConfigBasedCommand
{
    protected function configure()
    {
        $this->setName("roles")
            ->setDescription("Lists all unique roles defined")
            ->setDefinition(array(
            ))
            ->setHelp(<<<EOT
The <info>roles</info> command lists all unique roles defined in the configuration file
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $roles = array();
        foreach ($this->config['processes'] as $process) {
            $roles = array_merge($roles, (array)$process['roles']);
        }

        sort($roles);

        $output->writeln("The currently configured roles are:");
        foreach ($roles as $role) {
            $output->writeln("    <info>$role</info>");
        }
    }
}
