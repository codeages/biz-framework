<?php

namespace Codeages\Biz\Framework\Order\Command;

use Codeages\Biz\Framework\Context\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class OrderItemAndDeductAddSnapshotCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('order:order_item_and_deduct_add_snapshot')
            ->setDescription('Create a migration for the order database table biz_order_item_and_deduct_add_snapshot')
            ->addArgument('directory', InputArgument::REQUIRED, 'Migration base directory.', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = $input->getArgument('directory');

        $this->ensureMigrationDoseNotExist($directory, 'biz_order_item_and_deduct_add_snapshot');

        $filepath = $this->generateMigrationPath($directory, 'biz_order_item_and_deduct_add_snapshot');
        file_put_contents($filepath, file_get_contents(__DIR__.'/stub/order_item_and_deduct_add_snapshot.migration.stub'));

        $output->writeln('<info>Migration created successfully!</info>');
    }
}
