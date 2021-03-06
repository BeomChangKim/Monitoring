<?php

/* 
 * Copyright (C) 2014 Christophe
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once('include.php');

// Define action scripts.
define('ACTION_LOGIN', 'login');
define('ACTION_LOGOUT', 'logout');

define('ACTION_CONTROLLER_ADD', 'addcontroller');
define('ACTION_CONTROLLER_EDIT', 'editcontroller');
define('ACTION_CONTROLLER_REMOVE', 'removecontroller');

define('ACTION_USER_ADD', 'adduser');
define('ACTION_USER_EDIT', 'edituser');
define('ACTION_USER_REMOVE', 'removeuser');
define('ACTION_PROFILE_EDIT', 'editprofile');

define('ACTION_ALARM_ADD', 'addalarm');
define('ACTION_ALARM_EDIT', 'editalarm');
define('ACTION_ALARM_REMOVE', 'removealarm');

define('ACTION_ALERT_EDIT', 'editalert');

define('ACTION_ROLE_ADD', 'addrole');
define('ACTION_ROLE_EDIT', 'editrole');
define('ACTION_ROLE_REMOVE', 'removerole');

define('ACTION_PROJECT_ADD', 'addproject');
define('ACTION_PROJECT_EDIT', 'editproject');
define('ACTION_PROJECT_REMOVE', 'removeproject');

define('ACTION_USERROLE_ADD', 'adduserrole');
define('ACTION_USERROLE_DELETE', 'removeuserrole');

define('ACTION_ADMIN_CONFIG', 'adminconfig');

define('ACTION_NONE', 'nothing');

class ActionScript
{
    // database object
    var $pdo = null;
    // error messages
    var $error = null;

    /**
    * class constructor
    */
    function __construct()
    {
        // instantiate the pdo object
        try
        {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_AUTH_DBNAME . "";
            $this->pdo =  new PDO($dsn,DB_AUTH_USERNAME, DB_AUTH_PASSWORD);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        } catch (PDOException $e)
        {
            
        }
        
        // Check if logged.
        session_start();
        if (isset($_SESSION))
            $connected = true;
    }
    
    function executeAction($action, $request)
    {
        if (isset($_REQUEST["origin"]))
            $origin = $_REQUEST["origin"];
        
        $request = $this->sanitizeRequest($_REQUEST);
        
        try
        {
            switch ($action)
            {
                case ACTION_LOGIN:
                    $as = new AuthProcess($this->pdo);
                    $as->login($request["u"], $request["p"]);
                    exit(0);
                    break;
                case ACTION_LOGOUT:
                    $as = new AuthProcess($this->pdo);
                    $as->logout();
                    exit(0);
                    break;
                case ACTION_CONTROLLER_ADD:
                    $cp = new ControllerProcess($this->pdo);
                    $others = $this->__clearReservedParams($request);
                    $cp->add($request["name"], $request["descr"], $request["state"], $request["strict"], $request["type"], $request["alarm_id"], $others);
                    exit(0);
                    break;
                case ACTION_CONTROLLER_EDIT:
                    $cp = new ControllerProcess($this->pdo);
                    $others = $this->__clearReservedParams($request);
                    $cp->edit($request["id"], $request["name"], $request["descr"], $request["state"], $request["strict"], $request["type"], $request["alarm_id"], $others);
                    exit(0);
                    break;
                case ACTION_ALARM_ADD:
                    $cp = new AlarmProcess($this->pdo);
                    $cp->add($request["name"], $request["type"], $request["email"], $request["sms"]);
                    exit(0);
                    break;
                case ACTION_ALARM_EDIT:
                    $cp = new AlarmProcess($this->pdo);
                    $cp->edit($request["id"], $request["name"], $request["type"], $request["email"], $request["sms"]);
                    exit(0);
                    break;
                case ACTION_ALERT_EDIT:
                    $ap = new AlertProcess($this->pdo);
                    $ap->intervention($request["id"], $request["mode"], $_SESSION["user_id"]);
                    exit(0);
                    break;
                case ACTION_PROFILE_EDIT:
                    $up = new UserProcess($this->pdo);
                    $up->editprofile($request["username"], $request["email"], $request["email-active"], 
                            $request["phone"], $request["phone-active"], $request["current-password"], 
                            $request["new-password"], $request["retape-password"]);
                    exit(0);
                    break;
                case ACTION_USER_ADD:
                    $up = new UserProcess($this->pdo);
                    $up->add($request["id"], $request["username"], $request["email"], $request["email-active"], 
                            $request["phone"], $request["phone-active"], $request["password"]);
                    exit(0);
                    break;
                case ACTION_USER_EDIT:
                    $up = new UserProcess($this->pdo);
                    $up->edit($request["id"], $request["username"], $request["email"], $request["email-active"], 
                            $request["phone"], $request["phone-active"], $request["current-password"], 
                            $request["new-password"], $request["retape-password"]);
                    break;
                case ACTION_USER_REMOVE:
                    $up = new UserProcess($this->pdo);
                    $up->remove($request["id"]);
                    break;
                case ACTION_USER_REMOVE:
                    $up = new UserProcess($this->pdo);
                    $up->remove($request["id"]);
                    exit(0);
                    break;
                case ACTION_ROLE_ADD:
                    $rp = new RoleProcess($this->pdo);
                    $others = $this->__preparePermissions($request);
                    $rp->add($request["name"], $others);
                    exit(0);
                    break;
                case ACTION_ROLE_EDIT:
                    $rp = new RoleProcess($this->pdo);
                    $others = $this->__preparePermissions($request);
                    $rp->edit($request["id"], $request["name"], $others);
                    exit(0);
                    break;
                case ACTION_ROLE_REMOVE:
                    $rp = new RoleProcess($this-pdo);
                    $others = $this->__preparePermissions($request);
                    $rp->remove($request["id"], $request["next_role_id"]);
                    exit(0);
                    break;
                case ACTION_PROJECT_ADD:
                    $pp = new ProjectProcess($this->pdo);
                    $pp->add($request["name"], $request["locked"], $request["visible"]);
                    exit(0);
                    break;
                case ACTION_PROJECT_EDIT:
                    $pp = new ProjectProcess($this->pdo);
                    $pp->edit($request["id"], $request["name"], $request["locked"], $request["visible"]);
                    exit(0);
                    break;
                case ACTION_PROJECT_REMOVE:
                    $pp = new ProjectProcess($this->pdo);
                    $pp->remove($request["id"]);
                    exit(0);
                    break;
                case ACTION_USERROLE_ADD:
                    $up = new UserProcess($this->pdo);
                    $up->addrole($request["userid"], $request["proj"], $request["selector-role"]);
                    exit(0);
                    break;
                case ACTION_USERROLE_DELETE:
                    $up = new UserProcess($this->pdo);
                    $up->removerole($request["user_id"], $request["proj"], $request["role_id"]);
                    exit(0);
                    break;
                case ACTION_ADMIN_CONFIG:
                    $ac = new AdminProcess($this->pdo);
                    $ac->setup($request["db-host"], $request["db-username"], $request["db-password"], $request["db-name"], 
                            $request["control-threads"], $request["control-interval"]);
                    exit(0);
                    break;
                case ACTION_NONE:
                default:
                    throw new Exception("Requête inconnue");
            }
            
            if (isset($origin))
            {
                header('Location: ' . $origin);
                exit(0);
            }
            else
            {
                header('Location: ' . $_COOKIE["last-page"]);
                exit(0);
            }
        }
        catch (Exception $e)
        {
            if (isset($origin))
            {
                header('Location: ' . $origin);
                $_SESSION["message"] = array("type" => "danger", "title" => "Erreur", "descr" => $e->getMessage());
                exit(0);
            }
            else
            {
                header('Location: ' . $_COOKIE["last-page"]);
                $_SESSION["message"] = array("type" => "danger", "title" => "Erreur", "descr" => $e->getMessage());
                exit(0);
            }
        }
    }
    
    function sanitizeRequest($array)
    {
        $newArray = array();
        foreach ($array as $key => $value)
        {
            //$sanitized = sql_sanitize($value);
            $newArray[$key] = $value;
        }
        unset($key);
        unset($value);
        
        return $newArray;
    }
    
    private function __clearReservedParams($arr)
    {
        $finalArray = array();
        $forbiddenArrays = array("v", "id", "a", "name", "descr", "state", "strict", "alarm_id", "type", "origin");
        
        $keys = array_keys($arr);
        for ($i=0; $i < count($keys); $i++)
        {
            if (!in_array($keys[$i], $forbiddenArrays))
                $finalArray[$keys[$i]] = $arr[$keys[$i]];
        }
        
        return $finalArray;
    }
    
    private function __preparePermissions($arr)
    {
        $newArr = array();
        $keys = array_keys($arr);
        for ($i=0; $i < count($keys); $i++)
        {
            $key = $keys[$i];
            
            if (startsWith($key, "perm_"))
            {
                $key = str_replace("_", ".", $key);
                array_push($newArr, $key);
            }
        }
        
        return $newArr;
    }
}

?>