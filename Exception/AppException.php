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
        try {
            // POważny bład

            DI::get('logger')->error($message, (!empty($previous) ? [$previous->getFile() . ": " . $previous->getLine()]));
        } catch (\Exception $exception) {
           die($exception->getMessage());
        }

        parent::__construct(self::MESSAGE, $code, $previous);
    }

}
