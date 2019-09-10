<?php declare(strict_types=1);

namespace VendoPHP;

use VendoPHP\Controller\Project\Command\Create;
use VendoPHP\Exception\ControllerNotFound;
use VendoPHP\Exception\ServiceNotFound;

class Event
{
    const CACHE_EVENTS_FILES = 'vendophp.events';

    const BEFORE = 'beforeEvent';
    const AFTER = 'afterEvent';
    const EXCEPTION = 'exceptionEvent';

    const METHODS = [
        self::BEFORE,
        self::AFTER,
        self::EXCEPTION,
    ];

    private static $events;
    private static $current;
    private static $subscribers;
    private static $subscribersInstance;

    public static function set(string $type, string $class): void
    {

    }

    public static function has(string $eventType, string $className): bool
    {
        return isset(self::$events[$eventType][$className]);
    }

    public static function invoke(string $eventType, string $className, string $method): void
    {
        self::setCurrent($eventType);

        $reflector = new \ReflectionClass($className);
        if (false !== $reflector->getParentClass()) {
            self::invoke($eventType, $reflector->getParentClass()->getName(), $method);
        }

        if (self::has($eventType, $className)) {
            foreach (self::$events[$eventType][$className] as $priority => $events) {
                foreach ($events as $subscriberClassName => $event) {
                    if (isset($event[$method])) {

                        if (!isset(self::$subscribers[$subscriberClassName])) {
                            // startup event object
                            self::$subscribers[$subscriberClassName] = new \ReflectionClass($subscriberClassName);
                            self::$subscribersInstance[$subscriberClassName] = self::$subscribers[$subscriberClassName]->newInstanceWithoutConstructor();
                            self::$subscribers[$subscriberClassName]->getMethod($eventType)->invokeArgs(self::$subscribersInstance[$subscriberClassName], Autowire::handle(self::$subscribers[$subscriberClassName]->getMethod($eventType)->getParameters()));
                        }

                        $result = self::$subscribers[$subscriberClassName]->getMethod($method)->invoke(self::$subscribersInstance[$subscriberClassName]);

                    }
                }
            }
        }
    }

    public static function setCurrent(string $current): void
    {
        self::$current = $current;
    }

    public static function getCurrent()
    {
        return self::$current;
    }

    public static function load(): void
    {
        self::$events = Cache::get(self::CACHE_EVENTS_FILES);

        if (null === self::$events) {

            $directory = new \RecursiveDirectoryIterator(Env::getPath('DIR_EVENT'));
            $iterator = new \RecursiveIteratorIterator($directory);
            $regex = iterator_to_array(new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH));

            if (!empty($regex)) {
                foreach ($regex as $file) {
                    $eventClassName = Autowire::convertPathToNamespace($file[0], Env::getPath('DIR_EVENT'), "Event");

                    $reflector = new \ReflectionClass($eventClassName);

                    foreach (self::METHODS as $METHOD) {


                        if ($reflector->hasMethod($METHOD)) {

                            $reflectorMethod = new \ReflectionClass($eventClassName);
                            $instanceEvent = $reflectorMethod->newInstanceWithoutConstructor();
                            $listenerMethods = $reflectorMethod->getMethod($METHOD)->invokeArgs($instanceEvent, Autowire::handle($reflectorMethod->getMethod($METHOD)->getParameters()));

                            foreach ($listenerMethods as $priotity => $listenerClassName) {

                                if (!isset(self::$events[$METHOD][$listenerClassName])) {
                                    self::$events[$METHOD][$listenerClassName] = [];
                                }

                                if (!isset(self::$events[$METHOD][$listenerClassName][$priotity])) {
                                    self::$events[$METHOD][$listenerClassName][$priotity] = [];
                                }

                                $eventMethods = [];
                                if (!empty($reflectorMethod->getMethods())) {

                                    foreach ($reflectorMethod->getMethods() as $eventMethod) {
                                        if (!in_array($eventMethod->getName(), self::METHODS)) {
                                            $eventMethods[$eventMethod->getName()] = $eventMethod->getName();
                                        }
                                    }
                                }

                                if (!empty($eventMethods)) {
                                    if (!isset(self::$events[$METHOD][$listenerClassName][$priotity][$reflector->getName()])) {
                                        self::$events[$METHOD][$listenerClassName][$priotity][$reflector->getName()] = $eventMethods;
                                    } else {
                                        self::$events[$METHOD][$listenerClassName][$priotity][$reflector->getName()] = array_merge(self::$events[$METHOD][$listenerClassName][$priotity][$reflector->getName()], $eventMethods);
                                    }
                                }
                            }
                        }

                        if (isset(self::$events[$METHOD]) && !empty(self::$events[$METHOD])) {
                            ksort(self::$events[$METHOD]);
                        }
                    }
                }
                
                Cache::set(self::CACHE_EVENTS_FILES, self::$events);
            }
        }
    }

}