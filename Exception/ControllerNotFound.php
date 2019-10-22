<?php declare(strict_types=1);

namespace VendoPHP\Exception;
use InvalidArgumentException;

class ControllerNotFound extends InvalidArgumentException implements ExceptionInterface
{

    const MESSAGE = 'Controller not found.';
}
