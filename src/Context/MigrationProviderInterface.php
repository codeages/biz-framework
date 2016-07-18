<?php

namespace Codeages\Biz\Framework\Context;

use Pimple\Container;

interface MigrationProviderInterface
{
    public function registerMigrationDirectory(Kernel $contaier);
}
