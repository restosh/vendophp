<?php

namespace VendoPHP\Exception;
use InvalidArgumentException;

class ServiceNotFound extends InvalidArgumentException implements ExceptionInterface
{

    const MESSAGE = 'Service not found.';
}
