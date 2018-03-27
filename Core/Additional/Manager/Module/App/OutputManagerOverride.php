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

namespace iumioFramework\Core\Additional\Manager\Module\App;

use iumioFramework\Core\Additional\Manager\Display\OutputManager;

/**
 * Class OutputManagerOverride
 * @package iumioFramework\Core\Additional\Manager\Module\App
 * @category Framework
 * @licence  MIT License
 * @link https://framework.iumio.com
 * @author   RAFINA Dany <dany.rafina@iumio.com>
 */
class OutputManagerOverride extends OutputManager
{

    /** display Success Message
     * @param string $message Message to display
     * @param string $exit Exit script
     */
    public static function outputAsSuccess(string $message, string $exit = "yes")
    {
        $colors = self::getManagerColorInstance();
        echo "\n\n".$colors->getColoredString($message, "black", "green");
        if ($exit == "yes") {
            exit();
        }
    }

    /** display As Normal Message
     * @param string $message Message to display
     * @param string $exit Exit script
     */
    public static function outputAsNormal(string $message, string $exit = "yes")
    {
        $colors = self::getManagerColorInstance();
        echo "\n\n".$colors->getColoredString($message, "black", "green", false);
        if ($exit == "yes") {
            exit();
        }
    }

    /** display for read line Message
     * @param string $message Message to display
     * @param string $exit Exit script
     */
    public static function outputAsReadLine(string $message, string $exit = "yes")
    {
        $colors = self::getManagerColorInstance();
        echo "\n".$colors->getColoredStringReadLine($message, "yellow", "transparent");
        if ($exit == "yes") {
            exit();
        }
    }

    /** display Notice Message
     * @param string $message Message to display
     * @param string $exit Exit script
     */
    final public static function outputAsNotice(string $message, string $exit = "yes")
    {
        $colors = self::getManagerColorInstance();
        echo "\n\n".$colors->getColoredString($message, "black", "yellow");
        if ($exit == "yes") {
            exit();
        }
    }

    /** display Error Message
     * @param string $message Message to display
     * @param string $exit Exit script
     */
    final public static function outputAsError(string $message, string $exit = "yes")
    {
        $colors = self::getManagerColorInstance();
        echo "\n\n".$colors->getColoredString($message, "white", "red");
        if ($exit == "yes") {
            exit();
        }
    }

    /** display for end Success Message
     * @param string $message Message to display
     * @param string $exit Exit script
     */
    public static function outputAsEndSuccess(string $message, string $exit = "yes")
    {
        $colors = self::getManagerColorInstance();
        self::clear();
        echo $colors->getColoredString($message, "black", "green");
        if ($exit == "yes") {
            exit();
        }
    }
}
