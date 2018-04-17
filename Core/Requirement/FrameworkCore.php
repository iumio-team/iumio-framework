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
use iumioFramework\Core\Base\Renderer\Renderer;
use iumioFramework\Core\Server\Server;
use iumioFramework\Core\Requirement\Environment\FEnv;
use iumioFramework\Core\Base\Http\HttpListener;
use iumioFramework\Core\Exception\Access\Access200;
use iumioFramework\Core\Routing\Routing;
use iumioFramework\Core\Requirement\Reflection\FrameworkReflection;
use iumioFramework\Core\Requirement\FrameworkServices\GlobalCoreService;
use iumioFramework\Core\Exception\Server\Server500;
use iumioFramework\Core\Exception\Server\Server404;
use iumioFramework\Core\Exception\Server\Server000;
use iumioFramework\Core\Base\Json\JsonListener as JL;
use iumioFramework\Core\Requirement\FrameworkServices\AppConfig;


/**
 * Class FrameworkCore
 * The Core is the heart of the iumio system.
 * It manages an environment made of app.
 * @package iumioFramework\Core\Requirement;
 * @category Framework
 * @licence  MIT License
 * @link https://framework.iumio.com
 * @author   RAFINA Dany <dany.rafina@iumio.com>
 */

abstract class FrameworkCore extends GlobalCoreService
{

    protected $apps = array();
    protected $debug;
    protected $environment;
    private static $runtime_parameters = null;

    const CORE_VERSION = '0.6.3';
    const CORE_NAME = 'APRICOTS';
    const CORE_STAGE = 'BETA';
    const CORE_BUILD = 201763;
    protected static $edition = array();

    /**
     * Constructor.
     *
     * @param string $environment The app environment
     * @param bool   $debug       Enable debug
     * @throws Server500
     */

    public function __construct(string $environment, bool $debug)
    {
        $this->environment = $environment;
        $this->debug = (bool) $debug;
        self::detectFirstInstallation();

        if ($this->debug) {
            $this->startTime = microtime(true);
        }
        $this->declareExceptionHandlers();
        self::setCore($this);
        $defClass = new \ReflectionMethod($this, '__construct');

        $defClass = $defClass->getDeclaringClass()->name;
    }

    /** Set debug mode
     * @return bool
     */
    public function isDebug()
    {
        return $this->debug;
    }


    /** Get runtime parameters request
     * @return null|array
     */
    final public static function getRuntimeParameters()
    {
        return self::$runtime_parameters;
    }

    /** Get application environment
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Check the correct permission in directory :
     * /elements
     * /apps
     * @return int Correct permissions or not
     * @throws Server500 Permissions are incorrect
     */
    public function checkPermission():int
    {
        if (!Server::checkIsExecutable(FEnv::get("framework.root")."elements/") ||
            !Server::checkIsReadable(FEnv::get("framework.root")."elements/") ||
            !Server::checkIsWritable(FEnv::get("framework.root")."elements/")) {
            throw new Server500(new \ArrayObject(array("explain" =>
                "Core Error : Folder /elements does not have correct permission",
                "solution" => "Must be read, write, executable permission")));
        }

        if (!Server::checkIsExecutable(FEnv::get("framework.root")."apps/") ||
            !Server::checkIsReadable(FEnv::get("framework.root")."apps/") ||
            !Server::checkIsWritable(FEnv::get("framework.root")."apps/")) {
            throw new Server500(new \ArrayObject(array("explain" =>
                "Core Error : Folder /apps does not have correct permission",
                "solution" => "Must be read, write, executable permission")));
        }
        return (1);
    }


    /** Detect the default app
     * @param array $apps App list
     * @return array The default app
     * @deprecated  Will remove next major release
     * @throws \Exception When does not have a default app
     */
    protected function detectDefaultApp(array $apps):array
    {
        foreach ($apps as $oneapp => $val) {
            if ($val['isdefault'] == "yes") {
                return (array("name" => $oneapp, "value" => $val));
            }
        }

        throw new Server500(new \ArrayObject(array("explain" => "No Default app is detected", "solution" =>
            "Please edit apps.json to set a default app")));
    }

    /** Detect the app type
     * @param string $appname App name
     * @return string The type of app called. Possibility to return a << none >> app when appname not detected
     * @throws
     */
    final public function detectAppType(string $appname):string
    {
        $apptype = 'none';
        $appsp = self::registerApps();
        $appbs = self::registerBaseApps();

        foreach ($appsp as $one => $val) {
            if ($one == $appname) {
                return ('simple');
            }
        }

        foreach ($appbs as $one => $val) {
            if ($val['name'] == $appname) {
                return ('base');
            }
        }

        return ($apptype);
    }

    /**
     * Detect url matches

     * @param HttpListener $request
     * @param array $routes
     * @param string $baseurl Contain base url if it's a component is calling
     * @return mixed
     */
    protected function manage(HttpListener $request, array $routes, string $baseurl = "")
    {
        $controller = null;
        $baseSimilar = 0;
        $path = $request->server->get('REQUEST_URI');

        if ($path == "") {
            $path = "/";
        }

        foreach ($routes as $route) {
            if ($route['visibility'] === "disabled") {
                continue;
            }
            $mat = Routing::matches($baseurl.$route['path'], $path, $route);
            if (($mat['similar'] > $baseSimilar)) {
                $baseSimilar = $mat['similar'];
                $controller = $route;

                if (isset($controller['params']) && count($controller['params']) > 0) {
                    $pval = $this->assembly($controller['params'], $mat['result']);

                    if ($pval != false) {
                        $controller['pval'] = $pval;
                        unset($controller['params']);
                    }
                }
            }
        }
        return ($controller);
    }

    /** Merge two simple array to key/value array
     * @param array $keys array with all key
     * @param array $values value array
     * @return mixed Merged array or false
     */
    final protected function assembly(array $keys, array $values)
    {
        if (count($keys) !== count($values))
            return (false);
        return (array_combine($keys, $values));
    }


    /** getSimpleAppFormat
     * @param array $apps App list
     * @return array getSimpleAppFormat
     */
    protected function getSimpleAppFormat(array $apps):array
    {
        $narray = array();
        foreach ($apps as $oneapp => $val) {
            $e = AppConfig::getInstance($oneapp);
            if ($e->checkVisibility()) {
                array_push($narray, array("name" => $oneapp, "value" => $val));
            }
        }
        return ($narray);
    }


    /** Get real app name
     * @param string $fullAppName Full app name
     * @return string the real app name
     */
    final protected function getRealAppName(string $fullAppName):string
    {
        return (substr($fullAppName, 0, (strlen($fullAppName) - 3)));
    }


    /** Go to controller
     * @param HttpListener $request parameters
     * @return int Return as success
     * @throws Server404 Url not found
     * @throws Server500|\Exception Class or method does not exist or router failed
     */
    public function dispatching(HttpListener $request):int
    {
        self::$runtime_parameters = $request;
        $apps = $this->registerApps();
        $bapps = $this->registerBaseApps();
        $great = false;

        if ($this->isComponentCall($bapps, $request)) {
            return (1);
        }

        $values = $this->getSimpleAppFormat($apps);
        foreach ($values as $one => $def) {
            if ($great) {
                return (1);
            }

            if ($def["value"]["enabled"] == "no") {
                continue;
            }
            $rt = new Routing($def['name'], $def['value']['prefix']);
            if ($rt->routingRegister() == true) {
                $callback = $this->manage($request, $rt->routes());
                if ($callback != null) {
                    Routing::checkRouteMatchesMethod($callback, strtoupper($request->getMethod()));
                    $method = $callback['method'];
                    $controller = $callback['controller'];
                    $defname = $def['name'];
                    $master = "\\$defname\\Masters\\{$controller}Master";
                    $call = new FrameworkReflection();
                    FEnv::set("app.call", $def['name']);
                    FEnv::set("app.is_components", false);
                    if (isset($callback['pval'])) {
                        if (isset($callback['r_parameters']) && count($callback['r_parameters']) > 0) {
                            $callback['pval'] = $rt->checkParametersTypeURI(
                                $callback['pval'],
                                $callback['r_parameters'],
                                $callback["routename"]
                            );
                        }
                        $rscall = $call->__named($master, $method, $callback['pval']);
                    } else {
                        $rscall =  $call->__named($master, $method);
                    }
                    if (!($rscall instanceof Renderer)) {
                        $ac_called = FEnv::get("app.activity_called");
                        throw new Server500(new \ArrayObject(
                            array("explain" => "The activity {".$ac_called['method'].
                                "} result in object {".$ac_called['class'].
                                "} must be a Renderer : ".ucfirst(gettype($rscall))." is given", "solution" =>
                            "Return a Renderer instance in this activity")));
                    }
                    $great = true;
                    new Access200();
                    $rscall->pushRender();
                }
            } else {
                throw new Server500(new \ArrayObject(array("explain" => "Router register failed  ", "solution" =>
                    "Please check your app configuration")));
            }
        }

        if ($great == false) {
            throw new Server404(new \ArrayObject(array("solution" => "Please check your URI")));
        }

        return (1);
    }

    /** Check if it's component is calling
     * @param array $bases Base Apps
     * @param HttpListener $request Current request
     * @return bool If component is calling
     * @throws Server500 Generate error server
     * @throws \Exception
     */
    public function isComponentCall(array $bases, HttpListener $request): bool
    {
        foreach ($bases as $def) {
            if (((FEnv::get("framework.env") == "dev") && ($def['status_dev'] == "off")) ||
                ((FEnv::get("framework.env") == "prod") && ($def['status_prod'] == "off"))) {
                continue;
            }
            if (isset($def['appclass'])) {
                if (method_exists($def['appclass'], 'off') == true) {
                    $rt = new Routing($def['name'], null, true);
                    if ($rt->routingRegister() == true) {
                        $callback = $this->manage($request, $rt->routes());
                        if ($callback != null) {
                            Routing::checkRouteMatchesMethod($callback, strtoupper($request->getMethod()));
                            $method = $callback['method'];
                            $controller = $callback['controller'];
                            $defname = $def['name'];
                            $master = "\\$defname\\Masters\\{$controller}Master";
                            try {
                                $call = new FrameworkReflection();
                                FEnv::set("app.call", $def['name']);
                                FEnv::set("app.is_components", true);
                                if (isset($callback['pval'])) {
                                    if (count($callback['r_parameters']) > 0) {
                                        $callback['pval'] = $rt->checkParametersTypeURI(
                                            $callback['pval'],
                                            $callback['r_parameters'],
                                            $callback['routename']
                                        );
                                    }
                                    $rscall = $call->__named($master, $method, $callback['pval']);
                                } else {
                                    $rscall = $call->__named($master, $method);
                                }

                                if (!($rscall instanceof Renderer)) {
                                    $ac_called = FEnv::get("app.activity_called");
                                    throw new Server500(new \ArrayObject(array("explain" =>
                                        "The activity {".$ac_called['method'].
                                        "} result in object {".$ac_called['class'].
                                        "} must be a Renderer : ".ucfirst(gettype($rscall))." is given", "solution" =>
                                        "Return a Renderer instance in this activity")));
                                }
                                $rscall->pushRender();
                                return (true);
                            } catch (\Exception $exception) {
                                throw new Server500(new \ArrayObject(array("explain" => $exception->getMessage())));
                            }
                        }
                    } else {
                        throw new Server500(new \ArrayObject(array("explain" => $def['name'] .
                            " component not contain a related router", "solution" =>
                            "Please check the if 'routingRegister' is present in your router")));
                    }
                } else {
                    throw new Server500(new \ArrayObject(array("explain" => $def['name'] .
                        " component doesn't contain 'off' method ", "solution" =>
                        "Please add off method to your component")));
                }
            } else {
                throw new Server500(new \ArrayObject(array("explain" => "Component doesn't exist ", "solution" =>
                    "Check apps.json file in base app")));
            }
        }
        return (false);
    }

    /** Get all app register on apps.json
     * @return array Apps register
     * @throws Server000
     */

    public function registerApps():array
    {
        $classes = $this->getClassFile();
        if (count((array)$classes) == 0) {
            throw new Server000(new \ArrayObject(array()));
        }
        $apps = array();
        foreach ($classes as $class => $val) {
            $val = (array)$val;
            $apps[$val['name']] =  array("appclass" => new $val['class']());
        }
        return $apps;
    }

    /** Get all app register on apps.json
     * @return array Apps register
     */

    public function registerBaseApps():array
    {
        $classes = $this->getBaseClassFile();

        $apps = array();
        foreach ($classes as $class => $val) {
            $val = (array)$val;
            $apps[$val['name']] =  array("name" => $val['name'], "appclass" => new $val['class'](),
                "base_url" => $val['base_url'], "status_dev" => $val['status_dev'],
                "status_prod" => $val['status_prod']);
        };

        return $apps;
    }

    /** Return app declaration file
     * @return \stdClass File result
     * @throws
     */
    final protected function getClassFile():\stdClass
    {
        $a = json_decode(file_get_contents(FEnv::get("framework.config.core.apps.file")));
        return ($a == null ? new \stdClass() : $a);
    }

    /** Return base app declaration file
     * @return \stdClass File result
     * @throws
     */
    final protected function getBaseClassFile():\stdClass
    {
        $a = json_decode(file_get_contents(FEnv::get("framework.baseapps.apps.file")));
        return ($a == null ? new \stdClass() : $a);
    }

    /** Get info about iumio framework
     * @param string $infoname info name
     * @return string info result
     * @throws Server500 Error generate
     */
    final public static function getInfo(string $infoname):string
    {
        $rs = 'none';
        $edition = self::getEditionInfo();
        switch ($infoname) {
            case 'CORE_VERSION':
                $rs = self::CORE_VERSION;
                break;
            case 'CORE_BUILD':
                $rs = self::CORE_BUILD;
                break;
            case 'CORE_STAGE':
                $rs = self::CORE_STAGE;
                break;
            case 'CORE_NAME':
                $rs = self::CORE_NAME;
                break;
            case 'EDITION_BUILD':
                $rs = $edition->edition_build;
                break;
            case 'EDITION_VERSION':
                $rs = $edition->edition_version;
                break;
            case 'EDITION_STAGE':
                $rs = $edition->edition_stage;
                break;
            case 'EDITION_SHORTNAME':
                $rs = $edition->edition_shortname;
                break;
            case 'EDITION_FULLNAME':
                $rs = $edition->edition_fullname;
                break;
            case 'EDITION_U3I':
                $rs = $edition->u3i;
                break;
            case 'LOCATION':
                $rs =  realpath(__DIR__.DIRECTORY_SEPARATOR.'../../../../../');
                break;
        }
        return ($rs);
    }

    /** Get info about current server
     * @param string $infoname info name
     * @return string info result
     * @throws Server500 Error generate
     */
    final public static function getServerInfo(string $infoname):string
    {
        $rs = 'none';
        switch ($infoname) {
            case 'PHP_VERSION':
                $rs = phpversion();
                break;
            case 'SERVER_NAME':
                $rs = $_SERVER['SERVER_NAME'];
                break;
            default:
                try {
                    $rs = $_SERVER[$infoname];
                } catch (\Exception $e) {
                    throw new Server500(new \ArrayObject(array("explain" =>
                        "Core Error: The server info $infoname does not exist", "solution" => "Check your keyword")));
                }

                break;
        }
        return ($rs);
    }


    /** Get edition info linked with Framework Core
     * @return \stdClass edition infos
     * @throws Server500
     */
    final public static function getEditionInfo():\stdClass {
        $file = JL::open(FEnv::get("framework.config.core.config.file"));
        JL::close(FEnv::get("framework.config.core.config.file"));
        self::$edition = $file;
        return ($file);
    }

    /** Detect if it is a first install
     * @return int The success or failure
     * @throws Server500 File installer.php not exists
     */
    final private function detectFirstInstallation():int
    {
        $file = JL::open(FEnv::get("framework.config.core.config.file"));
        if (!isset($file->installation) || ($file->installation == NULL)) {
            if (file_exists(FEnv::get("framework.root").'public/setup/setup.php')) {
                header('Location: '.FEnv::get("host.current").'/setup/setup.php');
                exit(1);
            } else {

                throw new \RuntimeException("(Setup components does not exist in web directory => Please download".
                    "the setup components on iumio Framework Website to fix this error and put him in web directory)");
            }
        }
        return (0);
    }

    /**
     * Declare the new method dedicated to exception
     */
    final private function declareExceptionHandlers()
    {
        set_error_handler(
            'iumioFramework\Core\Exception\Tools\ToolsExceptions::errorHandler',
            E_ALL
        );

        set_exception_handler('iumioFramework\Core\Exception\Tools\ToolsExceptions::exceptionHandler');
        register_shutdown_function('iumioFramework\Core\Exception\Tools\ToolsExceptions::shutdownFunctionHandler');
    }
}
