<?php

namespace VendoPHP\Exception;

class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface
{

    const MESSAGE = 'Invalid value';

}
