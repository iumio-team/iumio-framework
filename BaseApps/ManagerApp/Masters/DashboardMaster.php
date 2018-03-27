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

namespace ManagerApp\Masters;

use iumioFramework\Core\Base\Renderer\Renderer;
use iumioFramework\Core\Requirement\Environment\FEnv;
use iumioFramework\Core\Exception\Server\AbstractServer;
use iumioFramework\Core\Exception\Server\Server500;
use iumioFramework\Core\Masters\MasterCore;
use iumioFramework\Core\Base\Json\JsonListener as JL;

/**
 * Class DashboardMaster
 * @package iumioFramework\Core\Manager
 * @category Framework
 * @licence  MIT License
 * @link https://framework.iumio.com
 * @author   RAFINA Dany <dany.rafina@iumio.com>
 */

class DashboardMaster extends MasterCore
{
    /**
     * Start FGM dashboard
     * @return Renderer
     * @throws \Exception
     */
    public function indexActivity()
    {
        $file = JL::open(FEnv::get("framework.config.core.config.file"));
        $date =  new \DateTime($file->installation->date);
        $file->installation = $date->format('Y/m/d');

        return($this->render("index", array("env" => strtolower(FEnv::get("framework.env")),
            "selected" => "dashboard", "fi" => $file, 
            'https' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on',
            "loader_msg" => "Framework Graphic Manager - Dashboard")));
    }

    /** Get the last debug logs (limited by 10)
     * @return Renderer JSON response log list
     * @throws Server500
     * @throws \Exception
     */
    public function getlastlogActivity():Renderer
    {
        $last = array_values((AbstractServer::getLogs("", 10)));
        $lastn = array();
        for ($i = 0; $i < count($last); $i++) {
            $last[$i]['log_url'] = $this->generateRoute(
                "iumio_manager_logs_manager_get_one",
                array("uidie" => $last[$i]['uidie'], "env" => strtolower(FEnv::get("framework.env")))
            );
            $last[$i]['time'] =  strtotime($last[$i]['time']);
            array_push($lastn, $last[$i]);
        }

        return ((new Renderer())->jsonRenderer(array("code" => 200, "results" => $lastn)));
    }


    /**
     * Get default App
     * @return Renderer JSON response log list
     * @throws Server500
     */
    public function getDefaultAppActivity():Renderer
    {
        $default = array();
        $file = (array) JL::open(FEnv::get("framework.config.core.apps.file"));
        foreach ($file as $one) {
            if ($one->isdefault == "yes") {
                $default = $one;
                break;
            }
        }
        return ((new Renderer())->jsonRenderer(array("code" => 200, "results" => $default)));
    }

    /**
     * Get the framework statistics
     * @return Renderer JSON response log list
     * @throws Server500
     */
    public function getFrameworkStatisticsActivity():Renderer
    {

        $appmaster = $this->getMaster('Apps');
        $appstats = $appmaster->getStatisticsApp();

        // ROUTING STATS IS TOO LONG - CHECK IT TO OPTIMIZE
        $routiningmaster = $this->getMaster('Routing');
        $routingstats = $routiningmaster->getStatisticsRouting();
        
        $dbmaster = $this->getMaster('Databases');
        $dbstats = $dbmaster->getStatisticsDatabases();


        $logsmaster = $this->getMaster('Logs');
        $logsstats = $logsmaster->getStatisticsLogs();

        $servicemaster = $this->getMaster('Services');
        $servicestats = $servicemaster->getStatisticsServices();



        return ((new Renderer())->jsonRenderer(array("code" => 200, "results" => array("apps" => $appstats,
            "routes" => $routingstats, "dbs" => $dbstats, "logs" => $logsstats, "services" => $servicestats))));
    }
}
