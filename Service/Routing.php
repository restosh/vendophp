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
    const CACHE_ROUTING_METHOD_MAP_FILES = 'vendophp.routing_method_map';


    private static $method;

    private static $routesNameMap = [];
    private static $routesMethodMap = [];

    /**
     * @var null | string
     */
    private $className = null;

    /**
     * @var null | string
     */
    private $methodName = null;

    /**
     * @var null | string
     */
    private $jsonSchema = null;

    /**
     * @var null | array
     */
    private $roles = [];

    /**
     * @var null | array
     */
    private $rules = [];


    public function __construct()
    {
        self::$method = $_SERVER['REQUEST_METHOD'];

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

    public static function fullUrl(string $name, $args = []): ?string
    {
        return Env::get('SERVICE_URL', '/') . self::url($name, $args);
    }

    private function load(): void
    {
        $annotationReader = new AnnotationReader();
        $routeInit = new Route([]);

        self::$routesNameMap = DI::get('cache')->get(self::CACHE_ROUTING_MAP_FILES);
        self::$routesMethodMap = DI::get('cache')->get(self::CACHE_ROUTING_METHOD_MAP_FILES);

        if (null === self::$routesMethodMap || null === self::$routesNameMap) {

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
                                        $this->registerRouter($keyRoute, $route);
                                    }
                                } else {
                                    $this->registerRouter($key, $route);
                                }
                            }
                        }
                    }
                }

                DI::get('cache')->set(self::CACHE_ROUTING_MAP_FILES, self::$routesNameMap);
                DI::get('cache')->set(self::CACHE_ROUTING_METHOD_MAP_FILES, self::$routesMethodMap);
            }
        }
    }

    private function registerRouter($key, $route)
    {
        self::$routesNameMap[$route->getName()] = $key;

        foreach ($route->getMethods() as $method) {
            self::$routesMethodMap[$method][$key] = (array)$route;
        }
    }

    private function dispatch()
    {
        $url = (isset($_GET['_url']) ? $_GET['_url'] : '/');

        if (!isset(self::$routesMethodMap[self::$method])) {
            throw new NotFoundException(NotFoundException::MESSAGE);
        }

        $findUrl = self::searchMap($url, self::$routesMethodMap[self::$method]);

        if (!isset(self::$routesMethodMap[self::$method][$findUrl])) {
            throw new NotFoundException(NotFoundException::MESSAGE);
        }

        $route = self::$routesMethodMap[self::$method][$findUrl];

        $this->setClassName($route['className']);
        $this->setMethodName($route['methodName']);
        $this->setJsonSchema($route['jsonSchema']);
        $this->setRoles($route['roles']);
        $this->setRules($route['rules']);

        return true;
    }


    private static function searchMap(string $url, &$routes): ?string
    {
        foreach ($routes as $route => $value) {
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

        foreach (self::$routesMethodMap[self::$method] as $route => $value) {

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

    public static function getUrlParam(string $name): ?string
    {
        $params = self::getParams($_SERVER['REQUEST_URI']);
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

    /**
     * @param string|null $methodName
     * @return Routing
     */
    public function setJsonSchema(?string $jsonSchema): Routing
    {
        $this->jsonSchema = $jsonSchema;
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

    public function getJsonSchema(): ?string
    {
        return $this->jsonSchema;
    }

    public function setRoles($roles): Routing
    {
        $this->roles = $roles;
        return $this;
    }

    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function getRules(): ?array
    {
        return $this->rules;
    }

    public function setRules($rules): Routing
    {
        $this->rules = $rules;
        return $this;
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