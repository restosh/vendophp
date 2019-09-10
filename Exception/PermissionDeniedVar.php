<?php

namespace VendoPHP\Exception;
use InvalidArgumentException;

class PermissionDeniedVar extends InvalidArgumentException implements ExceptionInterface
{

    const MESSAGE = 'Permission denied to write to "var" directory.';
}
