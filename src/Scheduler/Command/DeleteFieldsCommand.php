<?php

namespace Codeages\Biz\Framework\Scheduler\Command;

use Codeages\Biz\Framework\Context\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class DeleteFieldsCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('scheduler:delete_fields')
            ->setDescription('Delete fields for the scheduler database table')
            ->addArgument('directory', InputArgument::REQUIRED, 'Migration base directory.', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = $input->getArgument('directory');

        $this->ensureMigrationDoseNotExist($directory, 'biz_scheduler_delete_fields');

        $filepath = $this->generateMigrationPath($directory, 'biz_scheduler_delete_fields');
        file_put_contents($filepath, file_get_contents(__DIR__.'/stub/scheduler_delete_fields.migration.stub'));

        $output->writeln('<info>Migration created successfully!</info>');
    }
}
