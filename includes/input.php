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
        elseif ( isset($_SESSION[$key]) )
        {
            // NOTE: This elseif is intentional. We don't want session data overwriting post.
            $retval = $_SESSION[$key];
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
    
    /**
     * Special method to get an input array and walk over it, calling the appropriate
     * filter on each entry.
     */
    function getArray($key, $type, $max_depth = 10)
    {
        $retval = array();
        
        if ( isset($this->_cache[$key]) )
        {
            $retval = $this->_cache[$key];
        }
        else
        {
            $input  = $this->_get($key, $retval);
            $retval = $this->_recurseClean($input, $type, $max_depth);
            
            $this->_cache[$key] = $retval;
        }
        
        return $retval;
    }
    
    // ----------------------------------------------------
    // Data type methods
    // ----------------------------------------------------
    
    /**
     * Note that this method is special in that it doesn't actually return the
     * value of the input, rather the result of isset() on the input key.
     */
    function boolean($key, $default = 'ignored')
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
            $retval = $this->_cleanFloat($this->_get($key, $default));
            
            $this->_cache[$key] = $retval;
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
            $retval = $this->_cleanInt($this->_get($key, $default));
            
            $this->_cache[$key] = $retval;
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
            $retval = $this->_cleanHash($this->string($key, $default));
            
            $this->_cache[$key] = $retval;
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
            $retval = $this->_cleanString($this->_get($key, $default));
            
            $this->_cache[$key] = $retval;
        }
        
        return $retval;
    }
    
    // ----------------------------------------------------
    // Data cleaning methods
    // ----------------------------------------------------
    
    function _cleanFloat($value)
    {
        $value = floatval($value);
        
        return $value;
    }
    
    function _cleanInt($value)
    {
        $value = intval($value);
        
        return $value;
    }
    
    function _cleanHash($value)
    {
        $value = substr(preg_replace('/[^0-9A-Za-z]/', '', $this->_string($value)), 0, 40);
        
        return $value;
    }
    
    function _cleanString($value)
    {
        $value = strval($value);
        $value = urldecode($value);
        $value = ( get_magic_quotes_gpc() ) ? stripslashes($value) : $value;
        
        return $value;
    }
    
    /**
     * Recursively clean an array, stopping if the number of iterations is higher
     * than $max_depth. This prevents a malicious user from submitting an array
     * with an unusually high number of dimensions, potentially overloading
     * the server.
     * 
     * @param   $array      Array to clean
     * @param   $type       The type of data in each element
     * @param   $max_depth  The maximum number of iterations to run
     * @param   $cur_depth  The current number of iterations run
     */
    function _recurseClean($array, $type, $max_depth, $cur_depth = 0)
    {
        if ( !is_array($array) )
        {
            return $array;
        }
        
        if ( $cur_depth >= $max_depth )
        {
            return $array;
        }
        
        $cleaner = '_clean' . ucwords(strtolower($type));
        foreach ( $array as $k => $v )
        {
            if ( is_array($v) )
            {
                $array[$k] = $this->_recurseClean($v, $type, $max_depth, $cur_depth++);
            }
            else
            {
                $array[$k] = $this->$cleaner($v);
            }
        }
        
        return $array;
    }
}