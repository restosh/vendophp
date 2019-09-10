<?php

namespace VendoPHP\Exception;
use InvalidArgumentException;

class MissingVarEnv extends InvalidArgumentException implements ExceptionInterface
{

    const MESSAGE = 'Missing variable %s in .env';

    const MESSAGE_DIR = 'Missing dir by variable %s in .env';

}
