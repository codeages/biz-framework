<?php

namespace Codeages\Biz\Framework\Pay\Command;

use Codeages\Biz\Framework\Context\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class PaymentTradeAddCreatedParams extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('pay:payment_trade_add_created_params')
            ->setDescription('Create a migration for the pay database table add title column')
            ->addArgument('directory', InputArgument::REQUIRED, 'Migration base directory.', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = $input->getArgument('directory');

        $this->ensureMigrationDoseNotExist($directory, 'biz_payment_trade_add_created_params');

        $filepath = $this->generateMigrationPath($directory, 'biz_payment_trade_add_created_params');
        file_put_contents($filepath, file_get_contents(__DIR__.'/stub/payment_trade_add_created_params.migration.stub'));

        $output->writeln('<info>Migration created successfully!</info>');
    }
}
