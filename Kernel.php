<?php declare(strict_types=1);

namespace VendoPHP;

use Composer\Autoload\ClassLoader;
use Symfony\Component\Dotenv\Dotenv;
use VendoPHP\Service\CommandBus;

/**
 * Class Kernel
 * @package VendoPHP
 */
class Kernel
{

    const CACHE_SERVICE_FILES = 'vendophp.services';

    /**
     * Kernel constructor.
     */
    public function __construct()
    {
        $this->loadEnv();

        $this->loadConfigs();

        $this->loadServices();

        $this->loadHandlers();

    }

    public function handle(string $className, string $methodName)
    {
        return Autowire::resolve($className, $methodName);
    }

    public function handleQueues(){

    }

    public static function loadEnv(): void
    {
        $dotenv = new Dotenv();
        $dotenv->load(APP_DIR . '/.env');
    }

    public static function loadServices($services = []): void
    {
        $files = DI::get('cache')->get(self::CACHE_SERVICE_FILES);

        if (null === $files) {
            $directory = new \RecursiveDirectoryIterator(Env::getPath('DIR_SERVICE'));
            $iterator = new \RecursiveIteratorIterator($directory);
            $files = (iterator_to_array(new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH)));
            DI::get('cache')->set(self::CACHE_SERVICE_FILES, $files);
        }

        foreach ($files as $file) {
            require_once($file[0]);
        }
    }

    private function loadHandlers(): void
    {
        CommandBus::load();
    }


    private function loadConfigs(): void
    {
        Config::load();
    }

}