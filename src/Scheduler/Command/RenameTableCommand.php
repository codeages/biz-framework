<?php

namespace Codeages\Biz\Framework\Scheduler\Command;

use Codeages\Biz\Framework\Context\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class RenameTableCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('scheduler:rename_table')
            ->setDescription('Rename a migration for the scheduler database table')
            ->addArgument('directory', InputArgument::REQUIRED, 'Migration base directory.', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = $input->getArgument('directory');

        $this->ensureMigrationDoseNotExist($directory, 'biz_scheduler_rename_table');

        $filepath = $this->generateMigrationPath($directory, 'biz_scheduler_rename_table');
        file_put_contents($filepath, file_get_contents(__DIR__.'/stub/scheduler_rename_table.migration.stub'));

        $output->writeln('<info>Migration created successfully!</info>');
    }
}
