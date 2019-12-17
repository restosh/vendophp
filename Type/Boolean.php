<?php declare(strict_types=1);

namespace VendoPHP\Type;

use VendoPHP\Exception\InvalidArgumentsException;

class Boolean
{


    public function set($value)
    {
        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);

        return $value;
    }

}