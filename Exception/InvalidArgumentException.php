<?php declare(strict_types=1);

namespace VendoPHP\Exception;

class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface
{

    const MESSAGE = 'Invalid value';

}
