<?php
namespace Codeages\Biz\Framework\UnitTests;

use Phpmig\Api\PhpmigApplication;
use Symfony\Component\Console\Output\NullOutput;
use Codeages\Biz\Framework\Dao\MigrationBootstrap;
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

        $config = $this->kernel->config('database');

        $this->kernel['db'] = DriverManager::getConnection(array(
            'wrapperClass' => 'Codeages\Biz\Framework\Dao\TestCaseConnection',
            'driver' => $config['driver'],
            'host' => $config['host'],
            'port' => $config['port'],
            'dbname' => $config['name'],
            'charset' => $config['charset'],
            'user' => $config['user'],
            'password' => $config['password'],
        ));

        BaseTestCase::setKernel($this->kernel);
        BaseTestCase::emptyDatabase(true);

        $migration = new MigrationBootstrap($this->kernel);
        $container = $migration->boot();

        $adapter = $container['phpmig.adapter'];
        if (!$adapter->hasSchema()) {
            $adapter->createSchema();
        }

        $app = new PhpmigApplication($container, new NullOutput());

        $app->up();
    }

}