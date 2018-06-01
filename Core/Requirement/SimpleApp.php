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

namespace iumioFramework\Core\Requirement;

/**
 * Class SimpleApp
 * @package iumioFramework\Core\Requirement
 * @category Framework
 * @licence  MIT License
 * @link https://framework.iumio.com
 * @author   RAFINA Dany <dany.rafina@iumio.com>
 */

class SimpleApp extends App
{

    /**
     * SimpleApp constructor.
     * @param string|null $appname The app name or null
     */
    public function __construct(string $appname = null)
    {
    }

    /** Save an App
     */
    public function save()
    {
        // TODO: Implement save() method.
    }

    /** Delete an app
     */
    public function remove()
    {
        // TODO: Implement remove() method.
    }
}
