<?php

namespace Codeages\Biz\Framework\Session\Command;

use Codeages\Biz\Framework\Context\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class AddFieldsCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('session:online_add_fields')
            ->setDescription('add fields for the online database table')
            ->addArgument('directory', InputArgument::REQUIRED, 'Migration base directory.', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = $input->getArgument('directory');

        $this->ensureMigrationDoseNotExist($directory, 'biz_online_add_fields');

        $filepath = $this->generateMigrationPath($directory, 'biz_online_add_fields');
        file_put_contents($filepath, file_get_contents(__DIR__.'/stub/online_add_fields.migration.stub'));

        $output->writeln('<info>Migration created successfully!</info>');
    }
}
