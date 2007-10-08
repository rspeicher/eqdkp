<?php
if ( !defined('EQDKP_INC') )
{
     die('Do not access this file directly.');
}

class Input
{
    /**
     * Stores input variables after any cleaning's been done to them, to
     * prevent overhead of multiple $in->string('var') calls, for example.
     */
    var $_cache = array();
    
    function _get($key, $default = null)
    {
        $retval = $default;
        
        if ( isset($_GET[$key]) )
        {
            $retval = $_GET[$key];
        }
        
        if ( isset($_POST[$key]) )
        {
            $retval = $_POST[$key];
        }
        
        return $retval;
    }

    /**
     * A shortcut method to request an input variable. Calls the appropriate
     * type-specifc method based on the variable type of $default
     * 
     * Note that our most-used, and default type, is a string.
     */
    function get($key, $default = '')
    {
        $type = gettype($default);
        
        if ( method_exists($this, $type) )
        {
            return $this->$type($key, $default);
        }
        else
        {
            trigger_error("Input accessor method for variables of type <b>{$type}</b> doesn't exist.", E_USER_NOTICE);
            return $this->_get($key, $default);
        }
    }
    
    // ----------------------------------------------------
    // Data type methods
    // ----------------------------------------------------
    
    /**
     * Note that this method is special in that it doesn't actually return the
     * value of the input, rather the result of isset() on the input key.
     */
    function boolean($key, $default = false)
    {
        if ( isset($_GET[$key]) || isset($_POST[$key]) )
        {
            return true;
        }
        
        return false;
    }
    
    /**
     * Alias to float, see http://us2.php.net/manual/en/function.gettype.php
     */
    function double($key, $default = 0.00)
    {
        return $this->float($key, $default);
    }
    
    function float($key, $default = 0.00)
    {
        if ( isset($this->_cache[$key]) )
        {
            $retval = $this->_cache[$key];
        }
        else
        {
            $retval = floatval($this->_get($key, $default));
        }
        
        return $retval;
    }
    
    function int($key, $default = 0)
    {
        if ( isset($this->_cache[$key]) )
        {
            $retval = $this->_cache[$key];
        }
        else
        {
            $retval = intval($this->_get($key, $default));
        }
        
        return $retval;
    }
    
    /**
     * Alias to int
     */
    function integer($key, $default = 0)
    {
        return $this->int($key, $default);
    }
    
    function hash($key, $default = '')
    {
        if ( isset($this->_cache[$key]) )
        {
            $retval = $this->_cache[$key];
        }
        else
        {
            $retval = substr(preg_replace('/[^0-9A-Za-z]/', '', $this->_get($key, $default)), 0, 40);
        }
        
        return $retval;
    }
    
    function string($key, $default = '')
    {
        if ( isset($this->_cache[$key]) )
        {
            $retval = $this->_cache[$key];
        }
        else
        {
            $retval = strval($this->_get($key, $default));
            $retval = urldecode($retval);
            //$retval = preg_replace('/\s+/', ' ', $retval); // NOTE: This breaks addnews re-displaying the form, for example
            $retval = ( get_magic_quotes_gpc() ) ? stripslashes($retval) : $retval;
            
            $this->_cache[$key] = $retval;
        }
        
        return $retval;
    }
}