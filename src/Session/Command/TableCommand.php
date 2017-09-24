<?php

namespace Codeages\Biz\Framework\Session\Command;

use Codeages\Biz\Framework\Context\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class TableCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('session:table')
            ->setDescription('Create a migration for the session database table')
            ->addArgument('directory', InputArgument::REQUIRED, 'Migration base directory.', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = $input->getArgument('directory');

        $this->ensureMigrationDoseNotExist($directory, 'biz_session_and_online');

        $filepath = $this->generateMigrationPath($directory, 'biz_session_and_online');
        file_put_contents($filepath, file_get_contents(__DIR__.'/stub/session_and_online.migration.stub'));

        $output->writeln('<info>Migration created successfully!</info>');
    }
}
