<?php

namespace Codeages\Biz\Framework\Order\Command;

use Codeages\Biz\Framework\Context\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class OrderAddDisplayStatusCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('order:order_add_display_status')
            ->setDescription('Create a migration for the order database table add display_status')
            ->addArgument('directory', InputArgument::REQUIRED, 'Migration base directory.', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = $input->getArgument('directory');

        $this->ensureMigrationDoseNotExist($directory, 'biz_order_add_display_status');

        $filepath = $this->generateMigrationPath($directory, 'biz_order_add_display_status');
        file_put_contents($filepath, file_get_contents(__DIR__.'/stub/order_add_display_status.migration.stub'));

        $output->writeln('<info>Migration created successfully!</info>');
    }
}
