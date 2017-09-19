<?php

namespace Codeages\Biz\Framework\Context;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Finder\Finder;

class AbstractCommand extends Command
{
    protected $biz;

    public function __construct(Biz $biz, $name = null)
    {
        $this->biz = $biz;
        parent::__construct($name);
    }

    protected function generateMigrationPath($directory, $name)
    {
        sleep(1);

        return $directory.DIRECTORY_SEPARATOR.date('YmdHis').'_'.$name.'.php';
    }

    protected function ensureMigrationDoseNotExist($directory, $name)
    {
        $finder = new Finder();
        $finder->files()->in($directory);

        foreach ($finder as $file) {
            $path = $file->getRelativePathname();
            if (substr($path, -strlen($name) - 4, -4) == $name) {
                throw new \InvalidArgumentException("Migration `{$name}` is already exist.");
            }
        }

        return true;
    }
}
