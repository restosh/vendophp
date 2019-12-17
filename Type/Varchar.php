<?php declare(strict_types=1);

namespace VendoPHP\Type;

use VendoPHP\Exception\InvalidArgumentsException;

class Varchar
{


    public function set($value)
    {
        $value = strtolower(trim(filter_var($value, FILTER_SANITIZE_STRING)));

        if (strlen($value) < 4) {
            throw new InvalidArgumentsException(['login' => sprintf('%s %d %s.', __("invalid-min"), 4, __("invalid-chars", 4))]);
        }

        if (strlen($value) > 64) {
            throw new InvalidArgumentsException(['login' => sprintf('%s %d %s.', __("invalid-max"), 64, __("invalid-chars", 64))]);
        }

        return $value;
    }

}