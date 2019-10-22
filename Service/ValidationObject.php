<?php declare(strict_types=1);

namespace VendoPHP\Service;

use VendoPHP\Exception\InvalidArgumentsException;

class ValidationObject
{


    public static function constructor($object, array $params = []): array
    {
        $errors = [];

        $reflector = new \ReflectionClass($object);
        $parameters = $reflector->getConstructor()->getParameters();

        foreach ($parameters as $parameter) {
            $method = 'set'.ucfirst($parameter->getName());

            try {
                $object->$method($params[$parameter->getPosition()]);
            }catch (\Exception $exception){
                $errors[$parameter->getName()] = $exception->getMessage();
            }
        }

        if(!empty($errors)){
            throw new InvalidArgumentsException($errors, InvalidArgumentsException::MESSAGE);
        }

        return $errors;

    }

}