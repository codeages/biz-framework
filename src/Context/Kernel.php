<?php

namespace Codeages\Biz\Framework\Context;

use Pimple\Container;
use Doctrine\DBAL\DriverManager;
use Pimple\ServiceProviderInterface;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Dao\DaoProxy;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class Kernel extends Container
{
    protected $config;
    protected $user;
    protected $putted;
    protected $providers;

    public function __construct($config)
    {
        $this->config    = $config;
        $this->putted    = array();
        $this->providers = array();
        $this->user = null;

        parent::__construct();
    }

    public function boot($options = array())
    {
        $this['event_dispatcher'] = function ($kernel) {
            return new EventDispatcher();
        };

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

    public function user()
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

    /**
     * @return EventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this['event_dispatcher'];
    }

    /**
     * @param  string       $eventName
     * @param  string|Event $event
     * @param  array        $arguments
     * @return Event
     */
    public function dispatch($eventName, $event, array $arguments = array())
    {
        if (!$event instanceof Event) {
            $event = new Event($event, $arguments);
        }

        return $this->getEventDispatcher()->dispatch($eventName, $event);
    }

    public function addEventSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->getEventDispatcher()->addSubscriber($subscriber);
        return $this;
    }

    public function addEventSubscribers(array $subscribers)
    {
        foreach ($subscribers as $subscriber) {
            if (!$subscriber instanceof EventSubscriberInterface) {
                throw new \RuntimeException('subscriber type error');
            }

            $this->getEventDispatcher()->addSubscriber($subscriber);
        }

        return $this;
    }

    public function register(ServiceProviderInterface $provider, array $values = array())
    {
        $this->providers[] = $provider;

        return parent::register($provider, $values);
    }

    abstract public function registerProviders();
}
