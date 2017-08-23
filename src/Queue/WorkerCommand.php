<?php
namespace Codeages\Biz\Framework\Queue;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WorkerCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('queue:worker');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // ...
    }
}