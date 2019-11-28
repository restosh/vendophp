<?php declare(strict_types=1);

namespace VendoPHP\Exception;

final class InvalidArgumentsException extends \Exception
{
    const MESSAGE = 'Invalid values';

    private $errors = [];

    public function __construct($errors, $message = null, $code = 0, Exception $previous = null)
    {
        $this->errors = $errors;

        if(null === $message){
            $message = self::MESSAGE;
        }

        parent::__construct($message, $code, $previous);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
