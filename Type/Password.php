<?php declare(strict_types=1);

namespace VendoPHP\Type;

use VendoPHP\Exception\InvalidArgumentException;
use VendoPHP\Exception\InvalidArgumentsException;

class Password
{


    public function set($value)
    {
        if (strlen($value) < 8) {
            throw new InvalidArgumentException(sprintf('%s %d %s.', __("invalid-min"), 8, __("invalid-chars", 8)));
        }

        if (strlen($value) > 64) {
            throw new InvalidArgumentException(sprintf('%s %d %s.', __("invalid-max"), 64, __("invalid-chars", 64)));
        }

        return $value;
    }

}