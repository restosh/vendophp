<?php declare(strict_types=1);

namespace VendoPHP\Exception;
use InvalidArgumentException;
use Throwable;
use VendoPHP\DI;

class AppException extends \Exception
{

    const MESSAGE = 'Internal service error';

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        DI::get('logger')->error($message, [$previous->getFile().": ".$previous->getLine()]);
        
        parent::__construct(self::MESSAGE, $code, $previous);
    }

}
