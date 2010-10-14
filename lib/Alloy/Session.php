<?php
namespace Alloy;

/**
 * Session Class
 * Handles and stores user session data
 * 
 * @package Alloy
 * @link http://alloyframework.com/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Session
{
    /**
     * Constructor Fuction
     * Begins new user session
     */
    public function __construct()
    {
        if(!session_id()) {
            session_start();
        }
    }
    
    
    /**
     * Get a set session variable
     */
    public function get($var)
    {
        if(isset($_SESSION[$var])) {
            return $_SESSION[$var];
        } else {
            return null;
        }
    }
    
    
    /**
     * Set session variable
     */
    public function set($var, $value)
    {
        $_SESSION[$var] = $value;
        return true;
    }
    
    
    /**
     * Automatic getter/setter functions
     */
    public function __get($var) { return $this->get($var);	}
    public function __set($var, $value) { return $this->set($var, $value); }
    
    
    /**
     * Get session id
     */
    public function id()
    {
        return session_id();
    }
    
    
    /**
     * Destroy current session and all session data
     */
    public function destroy($var = null)
    {
        // Destroy single session variable
        if(!is_null($var)) {
            if(isset($_SESSION[$var])) {
                $_SESSION[$var] = null;
                unset($_SESSION[$var]);
            } else {
                return false;
            }
            return true;
        }
        else
        {
            // Destroy entire session
            session_destroy();
            return true;
        }
    }
    
    
    /**
     * Close session and write all data to file
     */
    public function close()
    {
        session_write_close();
        return true;
    }
    
    
    /**
     * Destructor function - ensure all session data is saved
     */
    public function __destruct()
    {
        $this->close();
    }
}