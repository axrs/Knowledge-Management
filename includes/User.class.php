<?php
/**
 * UserManagement Class
 * Author: Alexander Scott
 * Date: April 2012
 * Revision:
 * 20120409 - Initial Release
 *
 * Referenced and modified from adapted from Nick Papantas (nikolas@webdigity.com)
 * site: http://phpUserClass.com/
 *
 * Nick Papantas, accessed 04 April 2012, <http://phpUserClass.com/>.
 *
 * @version $Id: access.class.php,v 0.93 2008/05/02 10:54:32 $
 * @copyright Copyright (c) 2007 Nick Papanotas (http://www.webdigity.com)
 * @author Nick Papanotas <nikolas@webdigity.com>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 *
 */
class UserManager
{
    private $_session = "";
    private $_notifySession="GlynnTuckerKnowledgeBaseNotify";

    private $_cookieExpiry = 3600;
    private $_cookieDomain = "";
    private $_cookieName = "GlynnTuckerKnowledgeBase";
    private $_userData = array();
    private $_databaseHandle = null;
    private $_userID;
    private $_table = "user";
    private $_idField = "userName";

    /**Constructs a user management object
     * @param PDO $database Handle
     * @param int $cookieExpiry number of seconds to keep the user logged in
     */
    function __construct($database, $cookieExpiry = 3600)
    {
        //Setup the object
        $this->_databaseHandle = $database;
        $this->_cookieDomain = $this->_cookieDomain == '' ? $_SERVER['HTTP_HOST'] : $this->_cookieDomain;
        $this->_cookieExpiry = $cookieExpiry;

        //Start the session
        if (!isset($_SESSION)) session_start();

        //If a user session exists, load the session from memory
        if (!empty($_SESSION[$this->_session])) $this->load($_SESSION[$this->_session]);

        //If a browser cookie exists, load the cookie
        if (isset($_COOKIE[$this->_cookieName]) && !$this->is_loaded()) {
            print 'browserCookie!';
            $u = unserialize(base64_decode($_COOKIE[$this->_cookieName]));
            $this->login($u['username'], $u['password']);
        }

        //Otherwise no session
    }

    /**Attempts to login a user
     * @param string $loginID User id as stored in database
     * @param string $password User password (plain not MD5)
     * @param bool $remember Remember the user?
     * @param bool $load Load the user?
     * @return bool True if login successful
     */
    function login($loginID, $password, $remember = false, $load = true)
    {
        if (empty($loginID) || empty($password)) return false;

        //Create a MD5 Hash of the password
        $originalPassword = $password;
        $password = md5($password);

        //Test the user against the database:
        $result = $this->_databaseHandle->prepare("SELECT * FROM `$this->_table` WHERE `$this->_idField`= '$loginID' AND `userPassword` = '$password' LIMIT 1;");
        $result->execute();
        $result = $result->fetch(PDO::FETCH_ASSOC);
        //Verify the results
        if (count($result) <= 1) return false;

        //If load user to object
        if ($load) {
            $this->_userData = $result;
            $_SESSION[$this->_session] = $this->_userData['userName'];
            //If remember, set cookie
            if ($remember) {
                print 'setting cookie';
                $cookie = base64_encode(serialize(array('username' => $loginID, 'password' => $originalPassword)));
                setcookie($this->_cookieName, $cookie, time() + $this->_cookieExpiry, '/', $this->_cookieDomain);
            }
        }
        return true;
    }

    /**Logs out the user from the current session
     */
    function logout()
    {
        setcookie($this->_cookieName, '', 0, '/', $this->_cookieDomain);
        $_SESSION[$this->_session] = array();
        $this->_userData = array();
    }

    /**Gets the object as an associative array
     * @return array User details
     */
    function getArray()
    {
        return $this->_userData;
    }

    /**Determines if the resource has a specified property
     * @param string $prop Resource property
     * @return bool True if the property exists
     */
    function is($prop)
    {
        return $this->get_property($prop) == 1 ? true : false;
    }

    /**Gets a specified property
     * @param string $property Property key
     * @return string Property value
     */
    function get_property($property)
    {
        if (empty($this->_userData)) return "";
        if (!isset($this->_userData[$property])) return "";
        return $this->_userData[$property];
    }
    /**Gets the user activation state
     * @return bool True if active
     */
    function is_active()
    {
        return $this->_userData['userIsActive'] == 1;
    }

    /**Gets the current object state
     * @return bool True if a user is loaded
     */
    function is_loaded()
    {
        return count($this->_userData) >0;
        return empty($this->_userID) ? false : true;
    }

    /**Registers a new user with the database
     * @static
     * @param $data
     * @return int
     */
    function register($data)
    {
        //Invalid data, exit
        if (!is_array($data)) return 0;
        //Not unique login, exit
        if (!$this->verifyUniqueUsername($data['userName'])) return 0;

        //Generate password hash for storage
        $data['userPassword'] = md5($data['userPassword']);
        //Generate SQL
        $keys = "";
        $values = "";
        foreach ($data as $key => $value) {
            $keys .= "`$key`,";
            $values .= (is_numeric($value)) ? "$value," : "'$value',";
        }
        $keys = trim($keys, ',');
        $values = trim($values, ',');

        $sql = sprintf("INSERT INTO `%s` (%s) VALUES (%s)", $this->_table, $keys, $values);
        //Run database execution
        return $this->_databaseHandle->exec($sql);
    }

    /**Loads a user into the object
     * @param string $userID Login username
     * @return bool True if load successful
     */
    function load($userID)
    {
        $result = $this->_databaseHandle->prepare("SELECT * FROM `$this->_table` WHERE `$this->_idField` = '" . $userID . "' LIMIT 1");
        $result->execute();
        $result = $result->fetch(PDO::FETCH_ASSOC);
        if (count($result) == 0) return false;
        $this->_userData = $result;
        $this->_userID = $userID;
        $_SESSION[$this->_session] = $this->_userID;
        return true;
    }

    /**Verity's if a specified login is unique
     * @param string $loginID Login ID
     * @return bool True if unique
     */
    function verifyUniqueUsername($loginID)
    {
        /**REFERENCED FROM PHP.NET REGARDING PDO ROW COUNT
         * For most databases, PDOStatement::rowCount() does not return the number of rows affected by a SELECT statement.
         * Instead, use PDO::query() to issue a SELECT COUNT(*) statement with the same predicates as your intended SELECT
         * statement, then use PDOStatement::fetchColumn() to retrieve the number of rows that will be returned.
         * Your application can then perform the correct action.
         *
         * Source:http://php.net/manual/en/pdostatement.rowcount.php
         */
        $loginID = strtolower($loginID);
        $sql = "SELECT count(*) FROM `$this->_table` WHERE `$this->_idField`='$loginID';";
        $results = $this->_databaseHandle->prepare($sql);
        $results->execute();
        if (!$results->fetchColumn()) {
            return true;
        }
        return false;
    }

    /**Indicates if there is a notification message to display to the user
     * @return bool True if the property exists
     */
    function has_notification()
    {
        return isset($_SESSION[$this->_notifySession]);
    }

    function add_notification($message, $success=true)
    {
        if (!isset($_SESSION[$this->_notifySession])) $_SESSION[$this->_notifySession] = array();
        $_SESSION[$this->_notifySession][$message]=$success;
    }

    function displayNotifications(){
        if (!isset($_SESSION[$this->_notifySession])) return;
        foreach ($_SESSION[$this->_notifySession] as $message=>$success){
            echo sprintf('<div class="box twentyfour columns %s">', ($success)? "success" : "warning");
            echo sprintf('<h1>%s</h1><div class="content"><p>%s</p></div>',($success)? "Success" : "Warning",$message);
            echo sprintf('</div>');
        }
        unset($_SESSION[$this->_notifySession]);
    }
}