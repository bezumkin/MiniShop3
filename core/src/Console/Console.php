<?php

namespace ModxPro\MiniShop3\Console;

use ModxPro\MiniShop3\MiniShop3;
use ModxPro\MiniShop3\Console\Command\Install;
use ModxPro\MiniShop3\Console\Command\Remove;
use MODX\Revolution\modX;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\ListCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Console extends Application
{
    protected modX $modx;

    public function __construct(modX $modx)
    {
        parent::__construct(MiniShop3::NAME);
        $this->modx = $modx;
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        if ($this->getCommandName($input) === 'install' && !$this->modx->services->has('pdoTools')) {
            try {
                $cli = new \ModxPro\PdoTools\Console\Console($this->modx);
                $output->writeln('<info>Trying to install modx-pro/pdotools...</info>');
                $cli->doRun($input, $output);
                $output->writeln('<info>Done! Continue to install ' . MiniShop3::NAME . '</info>');
            } catch (\Throwable $e) {
                $output->writeln('<error>Could not load pdoTools service</error>');
                $output->writeln('<info>Please run "composer exec pdotools install"</info>');
                exit;
            }
        }

        return parent::doRun($input, $output);
    }

    protected function getDefaultCommands(): array
    {
        return [
            new ListCommand(),
            new Install($this->modx),
            new Remove($this->modx),
        ];
    }
}
