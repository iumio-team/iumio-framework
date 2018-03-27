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

namespace iumioFramework\Core\Base\Database;

use iumioFramework\Core\Requirement\Environment\FEnv;
use iumioFramework\Core\Exception\Server\Server500;
use iumioFramework\Core\Base\Json\JsonListener as JL;

/**
 * Class DatabaseAccess
 * @package iumioFramework\Core\Base\Database
 * @category Framework
 * @licence  MIT License
 * @link https://framework.iumio.com
 * @author   RAFINA Dany <dany.rafina@iumio.com>
 */

class DatabaseAccess
{
    private static $co_instance = null;

    /**
     * DatabaseAccess constructor.
     * @param string $databaseName
     * @throws Server500
     */
    private function __construct(string $databaseName)
    {
        try {
            $file = JL::open(FEnv::get("framework.config.db.file"));
            if (count((array) $file) == 0) {
                throw new Server500(new \ArrayObject(array("explain" => "Database file is empty", "solution" =>
                    "Add database informations in databases.json")));
            }
            if ($databaseName == "#none") {
                $dns = $file->default->db_driver.":dbname=".$file->default->db_name.";host=".$file->default->db_host;
                $user = $file->default->db_user;
                $passwd = $file->default->db_password;
                self::$co_instance = new \PDO($dns, $user, $passwd);
            } else {
                $dns = $file->$databaseName->db_driver.
                    ":dbname=".$file->$databaseName->db_name.";host=".$file->$databaseName->db_host;
                $user = $file->$databaseName->db_user;
                $passwd = $file->$databaseName->db_password;
                self::$co_instance = new \PDO($dns, $user, $passwd);
            }
        } catch (\Exception $e) {
            throw new Server500(new \ArrayObject(array("explain" => $e->getMessage(),
                "solution" => "Check your database configuration!")));
        }
    }


    /** Get PDO instance
     * @param string $databaseName database name
     * @return \PDO Pdo instance
     */
    final public static function getDbInstance(string $databaseName = '#none'):\PDO
    {
        if (self::$co_instance == null) {
            new self($databaseName);
        }
        return (self::$co_instance);
    }
}
