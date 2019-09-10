<?php

namespace VendoPHP;

use VendoPHP\Exception\ServiceNotFound;

/**
 * Class DI
 * @package VendoPHP
 */
class DI
{

    /**
     * @var array
     */
    private static $services = [];

    /**
     * @var array
     */
    private static $instances = [];

    /**
     * @param string $name
     * @param $function
     */
    public static function set(string $name, $function): void
    {
        if (!isset(self::$services[$name])) {
            self::$services[$name] = $function;
        }
    }


    public static function show()
    {
        //var_dump(self::$services);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return (isset(self::$instances[$name]) || self::$services[$name]);
    }

    /**
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    public function get(string $name)
    {
        if (isset(self::$instances[$name])) {
            return self::$instances[$name];
        }

        if (isset(self::$services[$name])) {
            return self::register($name, self::$services[$name]);
        }

        throw new ServiceNotFound(ServiceNotFound::Message);
    }

    /**
     * @param $name
     * @param $function
     * @return mixed
     */
    public static function register($name, $function)
    {

        self::$instances[$name] = $function();

        return self::$instances[$name];
    }
    
}