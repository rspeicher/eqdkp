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
    
    function input()
    {
    }
    
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
    
    // ----------------------------------------------------
    // Accessor methods
    // ----------------------------------------------------
    
    function float($key)
    {
        if ( isset($this->_cache[$key]) )
        {
            $retval = $this->_cache[$key];
        }
        else
        {
            $retval = floatval($this->_get($key));
        }
        
        return $retval;
    }
    
    function int($key)
    {
        if ( isset($this->_cache[$key]) )
        {
            $retval = $this->_cache[$key];
        }
        else
        {
            $retval = intval($this->_get($key));
        }
        
        return $retval;
    }
    
    function md5($key)
    {
        if ( isset($this->_cache[$key]) )
        {
            $retval = $this->_cache[$key];
        }
        else
        {
            $retval = substr(preg_replace('/[^0-9A-Za-z]/', '', $this->_get($key)), 0, 32);
        }
        
        return $retval;
    }
    
    function string($key, $escape = false)
    {
        if ( isset($this->_cache[$key]) )
        {
            $retval = $this->_cache[$key];
        }
        else
        {
            $retval = strval($this->_get($key, ''));
            $retval = urldecode($retval);
            $retval = preg_replace('/\s+/', ' ', $retval);
            
            $this->_cache[$key] = $retval;
        }
        
        // $escape might change from call to call depending on our context, so don't cache that value
        return ( $escape ) ? SQL_DB::escape($retval) : $retval;
    }
}