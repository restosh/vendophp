<?php declare(strict_types=1);

namespace VendoPHP;

use VendoPHP\Exception\MissingVarEnv;

class Env
{
    /**
     * @param string $name
     * @param bool $default
     * @return string|null
     */
    public static function get(string $name, $default = false): ?string
    {
        $value = getenv($name);

        if (!empty($value)) {
            return $value;
        }

        if (false !== $default) {
            return $default;
        }

        return null;
    }

    /**
     * @return bool
     */
    public static function isDebug(): bool
    {
        return filter_var(self::get('IS_DEBUG'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return bool
     */
    public static function isCache(): bool
    {
        return filter_var(self::get('IS_CACHE'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param string $name
     * @return string|null
     */
    public static function getPath(string $name): ?string
    {
        $value = self::get($name);

        if (empty($value)) {
            throw new MissingVarEnv(sprintf(MissingVarEnv::MESSAGE, $name));
        }

        if (false === file_exists(APP_DIR . $value)) {
            throw new MissingVarEnv(sprintf(MissingVarEnv::MESSAGE_DIR, $name));
        }

        return APP_DIR . $value;
    }


}