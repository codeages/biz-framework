<?php

namespace Codeages\Biz\Framework\Pay\Command;

use Codeages\Biz\Framework\Context\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class TableCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('pay:table')
            ->setDescription('Create a migration for the pay database table')
            ->addArgument('directory', InputArgument::REQUIRED, 'Migration base directory.', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directory = $input->getArgument('directory');

        $migrations = array(
            'pay',
            'payment_trade_add_created_params',
            'payment_trade_add_platform_type',
            'cashflow_delete_user_type',
            'cashflow_add_title',
            'cashflow_add_buyer_id',
            'user_cashflow_add_action'
        );

        foreach ($migrations as $migration) {
            $this->copyNextMigration($directory, $migration);
        }

        $output->writeln('<info>Migration created successfully!</info>');
    }

    protected function copyNextMigration($directory, $next)
    {
        if (!$this->existMigration($directory, $next)) {
            $this->generateMigration($directory, 'biz_'.$next, __DIR__."/stub/{$next}.migration.stub");
        }
    }
}
