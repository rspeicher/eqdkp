<?php
class Input
{
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
        return floatval($this->_get($key));
    }
    
    function int($key)
    {
        return intval($this->_get($key));
    }
    
    function md5($key)
    {
        $retval = $this->_get($key);
        
        return substr(preg_replace('/[^0-9A-Za-z]/', '', $retval), 0, 32);
    }
    
    function string($key, $escape = false)
    {
        $retval = strval($this->_get($key, ''));
        $retval = urldecode($retval);
        $retval = preg_replace('/\s+/', ' ', $retval);
        
        return ( $escape ) ? SQL_DB::escape($retval) : $retval;
    }
}