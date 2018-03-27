<?php

/*
 * This is an iumio Framework component
 *
 * (c) RAFINA DANY <dany.rafina@iumio.com>
 *
 * iumio Framework, an iumio component [https://iumio.com]
 *
 * To get more information about licence, please check the licence file
 */


namespace FServiceApp\Masters;

use iumioFramework\Core\Requirement\Environment\FEnv;
use iumioFramework\Core\Exception\Server\Server500;
use iumioFramework\Masters\MasterCore;
use iumioFramework\Base\Renderer\Renderer;

/**
 * Class ServiceMaster
 * @package FServiceApp\Masters
 * @category Framework
 * @licence  MIT License
 * @link https://framework.iumio.com
 * @author   RAFINA Dany <dany.rafina@iumio.com>
 */

class ServiceMaster extends MasterCore
{
    /**
     * Get current app
     * @throws Server500
     */
    public function getCAppActivity()
    {
        return ((new Renderer())->jsonRenderer(array("code" => 200, "app" => FEnv::get("app.call"))));
    }
}
