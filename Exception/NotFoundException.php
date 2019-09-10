<?php

namespace VendoPHP\Exception;
use InvalidArgumentException;

class NotFoundException extends InvalidArgumentException implements ExceptionInterface
{

    const MESSAGE = 'Not found.';
}
