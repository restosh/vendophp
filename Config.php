<?php

namespace VendoPHP;

use Symfony\Component\Yaml\Yaml;

/**
 * Class DI
 * @package VendoPHP
 */
class Config
{

    const CACHE_PARAMETERS_NAME = 'vendophp.parameters';
    /**
     * @var array
     */
    private static $parameters = [];

    public static function set(string $name, $value)
    {
        self::$parameters[$name] = $value;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public static function get(string $name)
    {

        if (self::has($name)) {
            return self::$parameters[$name];
        }

        return null;
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function has(string $name)
    {
        return isset(self::$parameters[$name]);
    }

    public static function load(): void
    {

        self::$parameters = Cache::get(self::CACHE_PARAMETERS_NAME);

        if (null === self::$parameters) {

            $directory = new \RecursiveDirectoryIterator(Env::getPath('DIR_CONFIG'));
            $iterator = new \RecursiveIteratorIterator($directory);
            $regex = iterator_to_array(new \RegexIterator($iterator, '/^.+\.yaml/i', \RecursiveRegexIterator::GET_MATCH));

            if (!empty($regex)) {
                foreach ($regex as $file) {
                    $params = Yaml::parseFile($file[0]);
                    self::flatten($params);
                }
            }

            Cache::set(self::CACHE_PARAMETERS_NAME, self::$parameters);
        }
    }

    private static function flatten(array $array, $key = '')
    {
        foreach ($array as $name => $value) {

            $index = (empty($key) ? '' : $key . '.') . $name;

            if (is_array($value) && null === $value[0]) {
                self::flatten($value, $index);
            } else {
                self::$parameters[$index] = $value;
            }
        }
    }


}
