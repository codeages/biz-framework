<?php

namespace Codeages\Biz\Framework\Pay\Command;

use Codeages\Biz\Framework\Context\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class PaymentTradeAddPlatformType extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('pay:payment_trade_add_platform_type')
            ->setDescription('Create a migration for the pay database table add title column')
            ->addArgument('directory', InputArgument::REQUIRED, 'Migration base directory.', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = $input->getArgument('directory');

        $this->ensureMigrationDoseNotExist($directory, 'biz_payment_trade_add_platform_type');

        $filepath = $this->generateMigrationPath($directory, 'biz_payment_trade_add_platform_type');
        file_put_contents($filepath, file_get_contents(__DIR__.'/stub/payment_trade_add_platform_type.migration.stub'));

        $output->writeln('<info>Migration created successfully!</info>');
    }
}
