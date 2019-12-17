<?php declare(strict_types=1);

namespace VendoPHP\Service;

use Entity\User;
use VendoPHP\Autowire;
use VendoPHP\Env;
use VendoPHP\Cache;
use VendoPHP\DI;
use VendoPHP\Exception\AppException;
use VendoPHP\Exception\InvalidArgumentException;

/**
 * Class Auth
 * @package VendoPHP
 */
class Auth
{

    private static $user = null;

    public function __construct()
    {
        $this->session = DI::get('session');
    }

    public function login(User $user)
    {
        if (false === password_verify($loginMessage->getPasswd(), $loginMessage->getUser()->getPasswd())) {
            throw new AppException(__('incorrect-password'));
        }

        if (false === $user->getIsActive()) {
            throw new AppException(__('login.user.is_not_active'));
        }

        DI::get('session')->set('userId', $user->getId());
        self::$user = $user;
    }

    public function isLogin()
    {
        return DI::get('session')->has('userId');
    }

    public static function getUser()
    {
        if (DI::get('session')->has('userId') && null === self::$user) {
            self::$user = DI::get('entityManager')->getRepository(User::class)->find(DI::get('session')->get('userId'));
        }

        return self::$user;
    }

    public static function getUserId()
    {
        return self::getUser()->getId();
    }

    public static function getAccountId()
    {
        return self::getUser()->getAccount()->getId();
    }


}