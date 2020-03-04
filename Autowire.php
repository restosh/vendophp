<?php declare(strict_types=1);

namespace VendoPHP;

use Doctrine\Common\Persistence\ObjectRepository;
use VendoPHP\Exception\PermissionDeniedVar;
use VendoPHP\Service\ValidationObject;
use VendoPHP\Structure\ControllerInterface;

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
        try {
            $reflector = new \ReflectionClass($class);

            $constructor = $reflector->getConstructor();

            $instance = (empty($constructor) ? $reflector->newInstance() : $reflector->newInstanceArgs(self::handle($reflector->getConstructor()->getParameters())));

            if (null !== $method || $reflector->implementsInterface(ControllerInterface::class)) {
                $reflectionMethod = new \ReflectionMethod($class, $method);

                Event::invoke(Event::BEFORE, $class);

                return $reflectionMethod->invokeArgs($instance, self::handle($reflectionMethod->getParameters()));


                Event::invoke(Event::AFTER, $class);
            }

        } catch (\Exception $exception) {
            Event::invoke(Event::EXCEPTION, $class, $exception);
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