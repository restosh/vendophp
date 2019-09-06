<?php declare(strict_types=1);

namespace VendoPHP\Service;

use Doctrine\Common\Annotations\AnnotationReader;
use VendoPHP\Autowire;
use VendoPHP\Cache;
use VendoPHP\DI;
use VendoPHP\Env;
use VendoPHP\Exception\NotFoundException;

class Routing
{

    const CACHE_ROUTING_FILES = 'vendophp.routing';

    private static $routes = [];
    private static $routesNameMap = [];

    /**
     * @var null | string
     */
    private $className = null;

    /**
     * @var null | string
     */
    private $methodName = null;


    public function __construct()
    {
        $this->load();

        $this->dispatch();

    }

    public static function url(string $name): ?string
    {
        if (isset(self::$routesNameMap[$name])) {
            return self::$routesNameMap[$name];
        }

    }

    private function load(): void
    {
        $annotationReader = new AnnotationReader();
        $routeInit = new Route([]);

        self::$routes = Cache::get(self::CACHE_ROUTING_FILES);

        if (null === self::$routes) {

            $directory = new \RecursiveDirectoryIterator(Env::getPath('DIR_CONTROLLER'));
            $iterator = new \RecursiveIteratorIterator($directory);
            $regex = iterator_to_array(new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH));

            if (!empty($regex)) {
                foreach ($regex as $file) {
                    $controllerClassName = Autowire::convertPathToNamespace($file[0], Env::getPath('DIR_CONTROLLER'), "Controller");

                    $reflectionClass = new \ReflectionClass($controllerClassName);

                    foreach ($reflectionClass->getMethods() as $method) {
                        $route = $annotationReader->getMethodAnnotation($method, Route::class);
                        if ($route instanceof Route) {
                            $route->setClassName($controllerClassName);
                            $route->setMethodName($method->getName());

                            $key = $route->getUrl();

                            if (null !== $route->getName()) {
                                self::$routesNameMap[$route->getName()] = $key;
                            }

                            self::$routes[$key] = (array)$route;
                        }
                    }
                }
            }

            Cache::set(self::CACHE_ROUTING_FILES, self::$routes);
        }

    }

    private function dispatch()
    {
        $url = (isset($_GET['_url']) ? $_GET['_url'] : '/');

        if (isset(self::$routes[$url])) {
            $this->setClassName(self::$routes[$url]['className']);
            $this->setMethodName(self::$routes[$url]['methodName']);

            return true;
        }

        throw new NotFoundException(NotFoundException::MESSAGE);

    }

    /**
     * @param string|null $className
     * @return Routing
     */
    public function setClassName(?string $className): Routing
    {
        $this->className = $className;
        return $this;
    }

    /**
     * @param string|null $methodName
     * @return Routing
     */
    public function setMethodName(?string $methodName): Routing
    {
        $this->methodName = $methodName;
        return $this;
    }


    public function getClassName(): string
    {
        return $this->className;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public static function redirect($name)
    {
        $url = self::url($name);
        header("Location: " . $url);
       // die();
    }

}