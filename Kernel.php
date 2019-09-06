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

        $this->loadDefaultServices();

        $this->loadConfigs();

        $this->loadCustomServices();

        $this->loadEvents();

        $this->loadHandlers();

    }

    public function handle(string $className, string $methodName)
    {
        return Autowire::resolve($className, $methodName);
    }

    private function loadEnv(): void
    {
        $dotenv = new Dotenv();
        $dotenv->load(APP_DIR . '/.env');
    }

    private function loadCustomServices(): void
    {

        DI::set('commandBus', function () {
            return (new CommandBus());
        });
    }

    private function loadDefaultServices(): void
    {
        $files = Cache::get(self::CACHE_SERVICE_FILES);

        if (null === $files) {
            $directory = new \RecursiveDirectoryIterator(Env::getPath('DIR_SERVICE'));
            $iterator = new \RecursiveIteratorIterator($directory);
            $files = (iterator_to_array(new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH)));

            Cache::set(self::CACHE_SERVICE_FILES, $files);
        }

        foreach ($files as $file) {
            require_once($file[0]);
        }
    }

    private function loadEvents(): void
    {
        Event::load();
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