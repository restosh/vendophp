<?php declare(strict_types=1);

namespace VendoPHP;

use VendoPHP\Exception\PermissionDeniedVar;

/**
 * Class Cache
 * @package VendoPHP
 */
class Cache
{

    /**
     * @var array
     */
    private static $cache = [];

    /**
     * @param string $key
     * @param bool $create
     * @return string
     */
    private static function getFile(string $key, bool $create = false): string
    {
        $dirCache = Env::getPath('DIR_CACHE');

        if (false !== strstr($key, '.')) {
            $arr = explode('.', $key);
            $file = array_pop($arr);
            $path = implode('/', $arr);
        } else {
            $path = '';
            $file = $key;
        }

        $path = $dirCache . '/' .$path;

        $fileCache = $path . '/' . $file . '.cache';

        if ($create) {
            if (false === file_exists($path)) {
                @mkdir($path, 0755, true);
            }

            if (false === file_exists($path)) {
                throw new PermissionDeniedVar(PermissionDeniedVar::MESSAGE);
            }

            if (false === file_exists($fileCache)) {
                @touch($fileCache);
            }

            if (false === file_exists($fileCache)) {
                throw new PermissionDeniedVar(PermissionDeniedVar::MESSAGE);
            }
        }

        return $fileCache;
    }

    /**
     * @param string $key
     * @param $value
     * @return bool|int
     */
    public static function set(string $key, $value)
    {
        $filename = self::getFile($key, true);

        if (isset(self::$cache[$key])) {
            unset(self::$cache[$key]);
        }

        return file_put_contents($filename, json_encode($value));
    }

    /**
     * @param string $key
     * @return false|mixed|string|null
     */
    public static function get(string $key)
    {
        $isDebug = Env::isCache();

        if (true === $isDebug) {
            return null;
        }

        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $filename = self::getFile($key);

        if (false === file_exists($filename)) {
            return null;
        }

        $value = json_decode(file_get_contents($filename));

        self::$cache[$key] = $value;

        return $value;
    }

    /**
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        if (isset(self::$cache[$key])) {
            return true;
        }

        $filename = self::getFile($key);
        return file_exists($filename);
    }

    /**
     * @param string $key
     * @return bool
     */
    public static function delete(string $key): bool
    {
        if (isset(self::$cache[$key])) {
            unset(self::$cache[$key]);
        }

        $filename = self::getFile($key);
        return @unlink($filename);
    }


    public static function clear(): void
    {
        $dir = Env::getPath('DIR_CACHE');

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }
    }

}