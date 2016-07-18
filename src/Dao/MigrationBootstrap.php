<?php

namespace Codeages\Biz\Framework\Dao;

use Phpmig\Adapter;
use Dotenv\Dotenv;
use Pimple\Container;
use Doctrine\DBAL\DriverManager;

class MigrationBootstrap
{
    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    public function run()
    {
        $container = new Container();

        $container['db'] = function() {
            return DriverManager::getConnection(array(
                'driver' => getenv('DB_DRIVER'),
                'dbname' => getenv('DB_DATABASE'),
                'charset' => getenv('DB_CHARSET'),
                'host' => getenv('DB_HOST'),
                'user' =>  getenv('DB_USERNAME'),
                'password' => getenv('DB_PASSWORD'),
            ));
        };

        $container['phpmig.adapter'] = function($container) {
            return new Adapter\Doctrine\DBAL($container['db'], 'migrations');
        };

        if (isset($this->kernel['migration_directories'])) {
            $migrations = array();
            foreach ($this->kernel['migration_directories'] as $directory) {
                $migrations = array_merge($migrations, glob("{$directory}/*.php"));
            }

            $container['phpmig.migrations'] = $migrations;
        }

        return $container;
    }
}
