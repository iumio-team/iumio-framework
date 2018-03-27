<?php

/**
 **
 ** This is an iumio Framework component
 **
 ** (c) RAFINA DANY <dany.rafina@iumio.com>
 **
 ** iumio Framework, an iumio component [https://iumio.com]
 **
 ** To get more information about licence, please check the licence file
 **
 **/

namespace iumioFramework\Core\Routing\Listener;

/**
 * Interface Listener
 * @package iumioFramework\Core\Routing\Listener
 * @category Framework
 * @licence  MIT License
 * @link https://framework.iumio.com
 * @author   RAFINA Dany <dany.rafina@iumio.com>
 */

interface Listener
{

    /**
     * @return int
     */
    public function open():int;

    /**
     * @return array
     */
    public function render():array;

    /**
     * @param $oneRouter
     * @return int
     */
    public function close($oneRouter):int;

    /**
     * @return int
     */
    public function listingRouters():int;
}
