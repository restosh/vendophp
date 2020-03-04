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

    const PRIORITY_HIGHT = 0;
    const PRIORITY_MIDDLE = 50;
    const PRIORITY_LOW = 100;

    const METHODS = [
        self::BEFORE,
        self::AFTER,
        self::EXCEPTION,
    ];

    private static $events = [
        self::BEFORE => [],
        self::AFTER => [],
        self::EXCEPTION => [],
    ];

    private static $current;
    private static $subscribers;
    private static $subscribersInstance;

    public static function add(
        string $type,
        string $subscriberClass,
        string $eventClass,
        int $priority = self::PRIORITY_LOW
    ): void
    {
        if (!isset(self::$events[$type][$subscriberClass])) {
            self::$events[$type][$subscriberClass] = [];
        }

        if (!isset(self::$events[$type][$subscriberClass][$priority])) {
            self::$events[$type][$subscriberClass][$priority] = [];
        }

        self::$events[$type][$subscriberClass][$priority][$eventClass] = $eventClass;
    }

    public static function has(string $eventType, string $className): bool
    {
        return isset(self::$events[$eventType][$className]);
    }

    public static function invoke(string $eventType, string $className, $params = null): void
    {
        self::setCurrent($eventType);

        $reflector = new \ReflectionClass($className);
        if (false !== $reflector->getParentClass()) {
            self::invoke($eventType, $reflector->getParentClass()->getName(), $params);
        }

        if (self::has($eventType, $className)) {
            foreach (self::$events[$eventType][$className] as $priority => $events) {
                foreach ($events as $subscriberClassName => $event) {

                    if (!isset(self::$subscribers[$subscriberClassName])) {
                        self::$subscribers[$subscriberClassName] = Autowire::resolve($subscriberClassName);
                    }

                    self::$subscribers[$subscriberClassName]->{$eventType}($params);
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

    /*
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

                var_dump(self::$events);
                die();

                Cache::set(self::CACHE_EVENTS_FILES, self::$events);
            }
        }
    }
*/
}