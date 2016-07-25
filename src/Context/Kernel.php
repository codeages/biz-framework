<?php

namespace Codeages\Biz\Framework\Context;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Doctrine\DBAL\DriverManager;
use Codeages\Biz\Framework\Dao\DaoProxy;

abstract class Kernel extends Container
{
    private $config;
    private $user;
    private $putted;
    private $providers;

    public function __construct($config)
    {
        $this->config = $config;
        $this->putted = array();
        $this->providers = array();

        parent::__construct();
    }

    public function boot($options = array())
    {
        foreach ($this->registerProviders() as $provider) {
            $this->register($provider);

            if ($provider instanceof MigrationProviderInterface) {
                $provider->registerMigrationDirectory($this);
            }
        }

        $this['db'] = function ($container) {

            $cfg = $this->config('database');

            return DriverManager::getConnection(array(
                'wrapperClass' => 'Codeages\Biz\Framework\Dao\Connection',
                'dbname' => $cfg['name'],
                'user' => $cfg['user'],
                'password' => $cfg['password'],
                'host' => $cfg['host'],
                'driver' => $cfg['driver'],
                'charset' => $cfg['charset'],
            ));
        };

        foreach ($this->putted as $key => $value) {
            $this[$key] = $value;
        }
    }

    public function config($name, $default = null)
    {
        if (!isset($this->config[$name])) {
            return $default;
        }

        return $this->config[$name];
    }

    public function dao($callable)
    {
        if (!method_exists($callable, '__invoke')) {
            throw new \InvalidArgumentException('Dao definition is not a Closure or invokable object.');
        }

        return function() use ($callable) {
            return new DaoProxy($this, $callable);
        };
    }

    public function setUser(CurrentUserInterface $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function put($key, $value)
    {
        if (!is_array($value)) {
            $value = array($value);
        }

        if (!isset($this->putted[$key])) {
            $this->putted[$key] = $value;
        } else {
            $this->putted[$key] = array_merge($this->putted[$key], $value);
        }

        return $this;
    }

    public function register(ServiceProviderInterface $provider, array $values = array())
    {
        $this->providers[] = $provider;

        parent::register($provider, $values);

        return $this;
    }

    abstract public function registerProviders();
}
