<?php
namespace Codeages\Biz\Framework\UnitTests;

use Phpmig\Api\PhpmigApplication;
use Symfony\Component\Console\Output\NullOutput;
use Codeages\Biz\Framework\Dao\MigrationBootstrap;
use Codeages\Biz\Targetlog\TargetlogKernel;
use Doctrine\DBAL\DriverManager;

class UnitTestsBootstrap
{
    protected $kernle;

    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    public function boot()
    {
        $this->kernel->boot();

        $this->kernel['db'] = DriverManager::getConnection(array(
            'wrapperClass' => 'Codeages\Biz\Framework\Dao\TestCaseConnection',
            'driver' => getenv('DB_DRIVER'),
            'dbname' => getenv('DB_DATABASE'),
            'charset' => getenv('DB_CHARSET'),
            'host' => getenv('DB_HOST'),
            'user' =>  getenv('DB_USERNAME'),
            'password' => getenv('DB_PASSWORD'),
        ));

        BaseTestCase::setKernel($this->kernel);

        $migration = new MigrationBootstrap($this->kernel, __DIR__);
        $container = $migration->run();

        $adapter = $container['phpmig.adapter'];
        if (!$adapter->hasSchema()) {
            $adapter->createSchema();
        }

        $app = new PhpmigApplication($container, new NullOutput());

        $app->up();
    }

}