<?php

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
     * @var string
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
     * Route constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {

        if (isset($data['name']) && !empty($data['name'])){
            $this->setName($data['name']);
        }

        if (isset($data['value']) && !empty($data['value'])){
            $this->setUrl($data['value']);
        }

        if (isset($data['url']) && !empty($data['url'])){
            $this->setUrl($data['url']);
        }

        if (isset($data['methods']) && !empty($data['methods'])) {
            $this->setMethods($data['methods']);
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
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     * @return Route
     */
    public function setUrl(string $url): Route
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





}