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

namespace iumioFramework\Core\Additional\Manager;

/**
 * Class CoreManager
 * @package iumioFramework\Core\Additional\Manager\
 * @category Framework
 * @licence  MIT License
 * @link https://framework.iumio.com
 * @author   RAFINA Dany <dany.rafina@iumio.com>
 */
class CoreManager
{
    private static $currentModule = "Main";

    /** Run manager console
     * @param int $argc Argument number
     * @param array $argv Arguments
     * @throws \Exception
     */
    final public function run(int $argc, array $argv)
    {
        $ar = new ArgsManager();
        $ar->getArgs($argc, $argv);
    }

    /** Get current module
     * @return string Current module name
     */
    public static function getCurrentModule():string
    {
        return self::$currentModule;
    }

    /** Set new current module
     * @param string $currentModule The current module
     */
    public static function setCurrentModule(string $currentModule)
    {
        self::$currentModule = $currentModule;
    }
}
