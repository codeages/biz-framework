<?php

namespace Codeages\Biz\Framework\Context;


use Codeages\Biz\Framework\Event\EventDispatcher;
use Pimple\Container;
use Doctrine\DBAL\DriverManager;
use Pimple\ServiceProviderInterface;
use Codeages\Biz\Framework\Dao\DaoProxy;

abstract class Kernel extends Container
{
    private $config;
    private $user;
    private $putted;
    private $providers;

    public function __construct($config)
    {
        $this->config    = $config;
        $this->putted    = array();
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

        $this['db'] = function ($kernel) {
            $cfg = $kernel->config('database');

            return DriverManager::getConnection(array(
                'wrapperClass' => 'Codeages\Biz\Framework\Dao\Connection',
                'dbname'       => $cfg['name'],
                'user'         => $cfg['user'],
                'password'     => $cfg['password'],
                'host'         => $cfg['host'],
                'driver'       => $cfg['driver'],
                'charset'      => $cfg['charset']
            ));
        };

        $this['EventDispatcher'] = function ($kernel){
            $dispatcher = EventDispatcher::getInstance();
            $dispatcher->setKernel($kernel);
            return $dispatcher;
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

        return function ($kernel) use ($callable) {
            return new DaoProxy($kernel, $callable);
        };
    }

    public function setUser(CurrentUserInterface $user)
    {
        $this->user = $user;
        return $this;
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

    public function get($key)
    {
        return $this->offsetGet($key);
    }

    public function register(ServiceProviderInterface $provider, array $values = array())
    {
        $this->providers[] = $provider;

        return parent::register($provider, $values);
    }

    abstract public function registerProviders();
}
