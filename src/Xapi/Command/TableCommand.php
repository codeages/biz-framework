<?php

namespace Codeages\Biz\Framework\Xapi\Command;

use Codeages\Biz\Framework\Context\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class TableCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('xapi:table')
            ->setDescription('Create a migration for the xapi database table')
            ->addArgument('directory', InputArgument::REQUIRED, 'Migration base directory.', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = $input->getArgument('directory');

        $this->ensureMigrationDoseNotExist($directory, 'biz_xapi');

        $filepath = $this->generateMigrationPath($directory, 'biz_xapi');
        file_put_contents($filepath, file_get_contents(__DIR__.'/stub/xapi.migration.stub'));

        $output->writeln('<info>Migration created successfully!</info>');
    }
}
