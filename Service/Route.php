<?php declare(strict_types=1);

namespace VendoPHP\Service;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target("ALL")
 */
class Route
{

    /**
     * @var null|string
     */
    public $name;

    /**
     * @var string|array
     */
    public $url;

    /**
     * @var null|array
     */
    public $methods = [];

    /**
     * @var null|string
     */
    public $className;

    /**
     * @var null|string
     */
    public $methodName;

    /**
     * @var null|string
     */
    public $jsonSchema;

    /**
     * @var null|array
     */
    public $roles;

    /**
     * @var null|array
     */
    public $rules;


    /**
     * Route constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {

        if (isset($data['name']) && !empty($data['name'])) {
            $this->setName($data['name']);
        }

        if (isset($data['value']) && !empty($data['value'])) {
            $this->setUrl($data['value']);
        }

        if (isset($data['url']) && !empty($data['url'])) {
            $this->setUrl($data['url']);
        }

        if (isset($data['methods']) && !empty($data['methods'])) {
            $this->setMethods($data['methods']);
        }

        if (isset($data['schema']) && !empty($data['schema'])) {
            $this->setJsonSchema($data['schema']);
        }

        if (isset($data['roles']) && !empty($data['roles'])) {
            $this->setRoles($data['roles']);
        }

        if (isset($data['rules']) && !empty($data['rules'])) {
            $this->setRules($data['rules']);
        }
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return Route
     */
    public function setName(?string $name): Route
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|array
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string|array|null $url
     * @return Route
     */
    public function setUrl($url): Route
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getMethods(): ?array
    {
        return $this->methods;
    }

    /**
     * @param array|null $methods
     * @return Route
     */
    public function setMethods(?array $methods): Route
    {
        $this->methods = $methods;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getClassName(): ?string
    {
        return $this->className;
    }

    /**
     * @param string|null $className
     * @return Route
     */
    public function setClassName(?string $className): Route
    {
        $this->className = $className;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMethodName(): ?string
    {
        return $this->methodName;
    }

    /**
     * @param string|null $methodName
     * @return Route
     */
    public function setMethodName(?string $methodName): Route
    {
        $this->methodName = $methodName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getJsonSchema(): ?string
    {
        return $this->jsonSchema;
    }

    /**
     * @param string|null $jsonSchema
     * @return Route
     */
    public function setJsonSchema(?string $jsonSchema): Route
    {
        $this->jsonSchema = $jsonSchema;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getRoles(): ?array
    {
        return $this->roles;
    }

    /**
     * @param array|null $roles
     * @return Route
     */
    public function setRoles(?array $roles): Route
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getRules(): ?array
    {
        return $this->rules;
    }

    /**
     * @param array|null $roles
     * @return Route
     */
    public function setRules(?array $rules): Route
    {
        $this->rules = $rules;
        return $this;
    }
}