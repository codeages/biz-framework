<?php

namespace Codeages\Biz\Framework\Order\Command;

use Codeages\Biz\Framework\Context\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class OrderAddRefundDeadlineCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('order:order_add_refund_deadline')
            ->setDescription('Create a migration for the order database table add refund_deadline')
            ->addArgument('directory', InputArgument::REQUIRED, 'Migration base directory.', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = $input->getArgument('directory');

        $this->ensureMigrationDoseNotExist($directory, 'order_add_refund_deadline');

        $filepath = $this->generateMigrationPath($directory, 'order_add_refund_deadline');
        file_put_contents($filepath, file_get_contents(__DIR__.'/stub/order_add_refund_deadline.migration.stub'));

        $output->writeln('<info>Migration created successfully!</info>');
    }
}
