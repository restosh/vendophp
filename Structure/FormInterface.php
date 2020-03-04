<?php declare(strict_types=1);

namespace VendoPHP\Structure;

interface FormInterface
{

    public function isValid();
    public function getErrors();
}