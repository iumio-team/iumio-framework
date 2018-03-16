<?php

/**
 *
 *  * This is an iumio Framework component
 *  *
 *  * (c) RAFINA DANY <dany.rafina@iumio.com>
 *  *
 *  * iumio Framework, an iumio component [https://iumio.com]
 *  *
 *  * To get more information about licence, please check the licence file
 *
 */


namespace iumioFramework\Core\Additional\Api;
use iumioFramework\Core\Requirement\Environment\FEnv;
use iumioFramework\Core\Requirement\Environment\FrameworkEnvironment;
use iumioFramework\Core\Base\Json\JsonListener as JL;
use iumioFramework\Exception\Server\Server500;

/**
 * Class ApiAuthentificator
 * @package iumioFramework\Core\Additional\Api
 * @author RAFINA Dany <dany.rafina@iumio.com>
 * @category Framework
 * @licence  MIT License
 * @link https://framework.iumio.com
 * @author   RAFINA Dany <dany.rafina@iumio.com>
 */
class ApiAuthentificator implements ApiInterface
{
    /**
     * @var null
     */
    static private $appapi = null;
    /**
     * @return mixed
     */
    public function getApiKeys()
    {
        // TODO: Implement getApiKeys() method.
    }


    /**
     * @return int
     * @throws Server500
     */
    public static function authEnabled() {
        $j = JL::open(FEnv::get("framework.config.core.config.file"));
        if (isset($j->api_auth_for)) {
            $auth = $j->api_auth_for;

            foreach ($auth as $oneauth) {
                if (self::checkFileExistAndApiKeys($oneauth)) {

                }
            }
        }
        else {
            return (0);
        }
    }

    /**
     * @param string $appname
     * @return bool
     */
    private static function checkFileExistAndApiKeys(string $appname) {
        //if ()
        return (false);
    }

}