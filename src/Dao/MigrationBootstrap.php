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

    public function boot()
    {
        $container = new Container();

        $config = $this->kernel->config('database');

        $container['db'] = function() use ($config) {
            return DriverManager::getConnection(array(
                'driver' => $config['driver'],
                'host' => $config['host'],
                'port' => $config['port'],
                'dbname' => $config['name'],
                'charset' => $config['charset'],
                'user' => $config['user'],
                'password' => $config['password'],
            ));
        };

        // see: http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/cookbook/mysql-enums.html
        $container['db']->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

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
