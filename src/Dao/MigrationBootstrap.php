<?php

namespace Codeages\Biz\Framework\Dao;

use Phpmig\Adapter;
use Dotenv\Dotenv;
use Pimple\Container;

class MigrationBootstrap
{
    public function __construct($kernel, $rootDirectory)
    {
        $this->kernel = $kernel;
        $this->rootDirectory = $rootDirectory;
    }

    public function run()
    {
        $dotenv = new Dotenv($this->rootDirectory);
        $dotenv->load();

        $container = new Container();

        $container['db'] = function() {
            return Doctrine\DBAL\DriverManager::getConnection(array(
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
