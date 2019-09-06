<?php declare(strict_types=1);

namespace VendoPHP\Service;

use VendoPHP\Autowire;
use VendoPHP\Env;
use VendoPHP\Cache;
use VendoPHP\DI;

/**
 * Class CommandBus
 * @package VendoPHP
 */
class CommandBus
{

    const CACHE_HANDLERS_NAME = 'vendophp.handlers';
    const INVOKE_METHOD_NAME = 'invoke';

    /**
     * @var array
     */
    private static $handlers = [];

    /**
     * @var array
     */
    private $commandQueue = [];

    /**
     * @param $command
     */
    public function dispatch($command): void
    {
        $this->commandQueue[] = $command;

        while ($command = array_shift($this->commandQueue)) {
            $this->invokeHandler($command);
        }
    }

    public static function load(): void
    {
        self::$handlers = Cache::get(self::CACHE_HANDLERS_NAME);

        if (empty(self::$handlers)) {
            $dirHandler = Env::getPath('DIR_HANDLER');

            $directory = new \RecursiveDirectoryIterator($dirHandler);
            $iterator = new \RecursiveIteratorIterator($directory);
            $files = iterator_to_array(new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH));

            if (!empty($files)) {
                foreach ($files as $file) {
                    $handlerClass = Autowire::convertPathToNamespace($file[0], $dirHandler, 'Handler');

                    $reflector = new \ReflectionClass($handlerClass);

                    if ($reflector->hasMethod(self::INVOKE_METHOD_NAME)) {

                        $parameters = $reflector->getMethod(self::INVOKE_METHOD_NAME)->getParameters();

                        if (!empty($parameters)) {
                            foreach ($parameters as $parameter) {

                                $method = $parameter->getClass()->getName();

                                if (!isset(self::$handlers[$method])) {
                                    self::$handlers[$method] = [];
                                }

                                self::$handlers[$method][$handlerClass] = true;
                            }
                        }
                    }
                }
            }

            Cache::set(self::CACHE_HANDLERS_NAME, self::$handlers);
        }
    }

    /**
     * @param $message
     * @throws \ReflectionException
     */
    private function invokeHandler($message)
    {
        $className = get_class($message);

        if (isset(self::$handlers[$className]) && !empty(self::$handlers[$className])) {

            foreach (self::$handlers[$className] as $handlerClassName => $isObject) {
                $object = Autowire::resolve($handlerClassName);
                $object->invoke($message);
            }
        }
    }
}