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
    const CACHE_ROUTING_MAP_FILES = 'vendophp.routing_map';

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

    public static function url(string $name, $args = []): ?string
    {
        if (isset(self::$routesNameMap[$name])) {
            $url = self::$routesNameMap[$name];

            if (!empty($args)) {
                foreach ($args as $name => $arg) {
                    $url = str_replace(':' . $name, $arg, $url);
                }
            }

            return $url;
        }

        return '/';
    }

    private function load(): void
    {
        $annotationReader = new AnnotationReader();
        $routeInit = new Route([]);

        self::$routes = Cache::get(self::CACHE_ROUTING_FILES);
        self::$routesNameMap = Cache::get(self::CACHE_ROUTING_MAP_FILES);

        if (null === self::$routes || null === self::$routesNameMap) {

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

                            if (null !== $route->getName()) {
                                $key = $route->getUrl();

                                if (is_array($key)) {
                                    foreach ($key as $keyRoute) {
                                        self::$routesNameMap[$route->getName()] = $keyRoute;
                                        self::$routes[$keyRoute] = (array)$route;
                                    }
                                } else {
                                    self::$routesNameMap[$route->getName()] = $key;
                                    self::$routes[$key] = (array)$route;
                                }
                            }
                        }
                    }
                }

                Cache::set(self::CACHE_ROUTING_MAP_FILES, self::$routesNameMap);
                Cache::set(self::CACHE_ROUTING_FILES, self::$routes);
            }
        }

    }

    private function dispatch()
    {
        $url = (isset($_GET['_url']) ? $_GET['_url'] : '/');

        if (!isset(self::$routes[$url])) {
            $url = self::searchMap($url);
        }

        if (isset(self::$routes[$url])) {
            if (!empty(self::$routes[$url]['methods'])) {
                if (!in_array($this->getRequestMethod(), self::$routes[$url]['methods'])) {
                    throw new NotFoundException(NotFoundException::MESSAGE);
                }
            }

            $this->setClassName(self::$routes[$url]['className']);
            $this->setMethodName(self::$routes[$url]['methodName']);

            return true;
        }

        throw new NotFoundException(NotFoundException::MESSAGE);

    }

    private static function searchMap(string $url): ?string
    {
        foreach (self::$routes as $route => $value) {
            if (substr_count($route, '/') === substr_count($url, '/')) {
                $crumbsRoute = explode('/', $route);
                $crumbsUrl = explode('/', $url);

                if ($crumbsRoute[1] === $crumbsUrl[1]) {
                    foreach ($crumbsRoute as $key => $item) {
                        if (isset($crumbsUrl[$key]) && substr($item, 0, 1) === ':') {
                            unset($crumbsRoute[$key]);
                            unset($crumbsUrl[$key]);
                        }
                    }

                    if (implode('/', $crumbsRoute) === implode('/', $crumbsUrl)) {
                        return $route;
                    }
                }
            }
        }

        return $url;
    }

    public static function getParams(string $url): ?array
    {
        $parse = parse_url($url);
        $url = $parse['path'];

        foreach (self::$routes as $route => $value) {

            if (substr_count($route, '/') === substr_count($url, '/')) {
                $params = [];
                $crumbsRoute = explode('/', $route);
                $crumbsUrl = explode('/', $url);

                if ($crumbsRoute[1] === $crumbsUrl[1]) {

                    foreach ($crumbsRoute as $key => $item) {
                        if (isset($crumbsUrl[$key]) && substr($item, 0, 1) === ':') {
                            $params[substr($crumbsRoute[$key], 1)] = trim(strtolower(strip_tags($crumbsUrl[$key])));
                            unset($crumbsRoute[$key]);
                            unset($crumbsUrl[$key]);
                        }
                    }

                    if (implode('/', $crumbsRoute) === implode('/', $crumbsUrl)) {
                        return $params;
                    }
                }
            }
        }

        return null;
    }

    public static function getParam(string $url, string $name): ?string
    {
        $params = self::getParams($url);
        if (isset($params[$name])) {
            return $params[$name];
        }

        return null;
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

    public function getRequestMethod(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    public static function redirect($name, $args = [])
    {
        $url = self::url($name, $args);

        if (DI::has('response')) {
            DI::get('response')->isRedirect($url);
            DI::get('response')->sendHeaders();
        }

        header("Location: " . $url);
        exit;
        // die();
    }

}