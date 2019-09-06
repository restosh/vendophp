<?php declare(strict_types=1);

namespace VendoPHP;

use VendoPHP\Exception\PermissionDeniedVar;

/**
 * Class Autowire
 * @package VendoPHP
 */
class Autowire
{


    /**
     * @param string $class
     * @param string|null $method
     * @return mixed|object
     * @throws \ReflectionException
     */
    public static function resolve(string $class, string $method = null)
    {

        $reflector = new \ReflectionClass($class);

        // EVENTS
        // dump($reflector->getParentClass());

        $constructor = $reflector->getConstructor();

        Event::invoke(Event::BEFORE, $class, '__construct');

        $instance = (empty($constructor) ? $reflector->newInstance() : $reflector->newInstanceArgs(self::handle($reflector->getConstructor()->getParameters())));

        Event::invoke(Event::AFTER, $class, '__construct');

        if (null !== $method) {
            $reflectionMethod = new \ReflectionMethod($class, $method);

            Event::invoke(Event::BEFORE, $class, $method);

            try {
                return $reflectionMethod->invokeArgs($instance, self::handle($reflectionMethod->getParameters()));

            } catch (\Exception $exception) {
                Event::invoke(Event::EXCEPTION, $class, $method);
            }
            
            Event::invoke(Event::AFTER, $class, $method);
        }

        return $instance;
    }

    /**
     * @param array $parameters
     * @return array
     * @throws \Exception
     */
    public static function handle(array $parameters): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            if (DI::has($parameter->getName())) {
                $dependencies[] = DI::get($parameter->getName());
            } else {
                $class = '\\' . $parameter->getClass()->getName();
                $dependencies[] = self::resolve($class);
            }
        }

        return $dependencies;
    }

    /**
     * @param string $path
     * @param string $search
     * @param string $replace
     * @return string
     */
    public static function convertPathToNamespace(string $path, string $search, string $replace): string
    {

        $pos = strrpos($path, $search);

        if (false !== $pos) {
            $className = substr($path, $pos);
        }

        return str_replace([$search, '.php', '/'], [$replace, '', '\\'], $className);
    }

}