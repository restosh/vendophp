<?php declare(strict_types=1);

namespace VendoPHP\Exception;

final class InvalidArgumentsException extends \Exception
{
    const MESSAGE = 'Invalid values';

    private $errors = [];

    public function __construct($errors, $message, $code = 0, Exception $previous = null)
    {
        $this->errors = $errors;
        
        parent::__construct($message, $code, $previous);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
