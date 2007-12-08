<?php
/*
  TODO: Based on this file, the following changes have to be made to 1.4:
  
  Database (and any code that uses these)
    * REPLACE INTO __config VALUES ('auth_salt', '<SOME RANDOM STRING GENERATED AT UPGRADE>')
    * Rename __users.username to __users.user_name
    * Add __users.user_salt
    * Rename __sessions.session_user_id to __sessions.user_id
    * Might not need cookie_path and cookie_domain anymore
    
  Code
    * Change User::Encrypt calls to Session::HashPassword
*/

## ############################################################################
## Create a simulated EQdkp environment, since we're overriding some Session stuff
## ############################################################################

define('EQDKP_INC', true);
error_reporting (E_ALL);

$eqdkp_root_path = './../upload/';

if ( !is_file($eqdkp_root_path . 'config.php') )
{
    die('Error: could not locate configuration file.');
}

require_once($eqdkp_root_path . 'config.php');

// Constants
define('EQDKP_VERSION', '1.4.0');
define('NO_CACHE', true);

define('DEBUG', 2);

// User Levels
define('ANONYMOUS', -1);

// Backwards compatibility for pre-1.4
$dbms = ( !isset($dbms) && isset($dbtype) ) ? $dbtype : $dbms;

require($eqdkp_root_path . 'includes/functions.php');
// require($eqdkp_root_path . 'includes/functions_paths.php');
require($eqdkp_root_path . 'includes/db/' . $dbms . '.php');
require($eqdkp_root_path . 'includes/eqdkp.php');
// require($eqdkp_root_path . 'includes/session.php');
// require($eqdkp_root_path . 'includes/class_template.php');
// require($eqdkp_root_path . 'includes/eqdkp_plugins.php');
require($eqdkp_root_path . 'includes/input.php');
// require($eqdkp_root_path . 'games/game_manager.php');

// $tpl  = new Template;
$in   = new Input();
// $user = new User;
$db   = new $sql_db();

// Connect to the database
$db->sql_connect($dbhost, $dbname, $dbuser, $dbpass, false);

// Initialize the eqdkp module
$eqdkp = new EQdkp($eqdkp_root_path);

## ############################################################################
## Class Definitions
## ############################################################################

class Session
{
    var $data    = array();
    var $ip      = '';
    // var $browser = '';
    var $page    = '';
    
    function session()
    {
        $this->ip = ( !empty($_SERVER['REMOTE_ADDR']) )     ? $_SERVER['REMOTE_ADDR']     : '127.0.0.1';
        $this->ip = preg_replace('/[^\d\.]/', '', $this->ip);
        
        // $this->browser = ( !empty($_SERVER['HTTP_USER_AGENT']) ) ? $_SERVER['HTTP_USER_AGENT'] : $_ENV['HTTP_USER_AGENT'];
    }
    
    /**
     * Start or renew a session
     *
     * @return void
     */
    function start()
    {
        // Grab data from __users, given an ID and password hash from cookies
        $this->_user_restore();
        
        // Grab data from __sessions, given a SID from a cookie
        $this->_session_restore();
    
        // Update user_lastvisit, session_current and session_page, if necessary
        $this->_update_stats();
        
        // Insert or update the session row
        $this->_store();

        // Remove dead sessions
        $this->_cleanup();
    }
    
    /**
     * Restore user data for this session via cookie data
     *
     * @return void
     * @access private
     */
    function _user_restore()
    {
        global $db, $eqdkp, $in;
        
        $cookie_user = $in->get($this->_cookie_name('cuser'), ANONYMOUS);
        $cookie_auth = $in->get($this->_cookie_name('cauth'));
        
        if ( $cookie_user > 0 )
        {
            $sql = "SELECT user_id, user_name, user_email, user_alimit, user_elimit,
                        user_ilimit, user_nlimit, user_rlimit,user_style, user_lang,
                        user_lastpage, user_active
                    FROM __users2
                    WHERE (
                        (`user_id` = '{$cookie_user}') AND
                        (`user_password` = '" . $db->escape($cookie_auth) . "')
                    )
                    LIMIT 1";
            $result = $db->query($sql);
            $row = $db->fetch_record($result);
            
            if ( is_array($row) )
            {
                $this->data = array_merge($this->data, $row);
            }
        }
        else
        {
            // User doesn't exist, populate data with default values
            $this->data = array_merge($this->data, array(
                'user_id'     => ANONYMOUS,
                'user_alimit' => $eqdkp->config['default_alimit'],
                'user_elimit' => $eqdkp->config['default_elimit'],
                'user_ilimit' => $eqdkp->config['default_ilimit'],
                'user_nlimit' => $eqdkp->config['default_nlimit'],
                'user_rlimit' => $eqdkp->config['default_rlimit'],
                'user_style'  => $eqdkp->config['default_style'],
                'user_lang'   => $eqdkp->config['default_lang'],
            ));
        }
    }
    
    /**
     * Restore session data for this session via a session identifier from a cookie
     *
     * @return void
     * @access private
     */
    function _session_restore()
    {
        global $db, $in;
        
        $cookie_sid  = $in->hash($this->_cookie_name('csid'));
        
        $sql = "SELECT session_id, session_start, session_current, session_page
                FROM __sessions2 AS s
                WHERE (
                    (`session_id` = '" . $db->escape($cookie_sid) . "') AND
                    (`session_ip` = '" . $db->escape($this->ip) . "')
                )
                LIMIT 1";
        $result = $db->query($sql);
        $row = $db->fetch_record($result);
        
        if ( is_array($row) )
        {
            $this->data = array_merge($this->data, $row);
        }
        
        if ( !empty($this->data['session_id']) )
        {
            // Pre-existing session, do we need to do anything?
        }
        else
        {
            // Session doesn't exist yet, create a unique session ID
            $this->data = array_merge($this->data, array(
                'session_id' => md5(uniqid(rand(), true)),
            ));
        }
    }
    
    function _update_stats()
    {
        global $db;
        
        $current = time();
        
        // user_lastvisit is more than 60 seconds old
        if ( isset($this->data['user_lastvisit']) && $current - $this->data['user_lastvisit'] > 60 )
        {
            $sql = "UPDATE __users2 SET user_lastvisit = '{$current}' 
                    WHERE (`user_id` = '{$this->data['user_id']}')";
            $db->query($sql);
        }
        
        /* These get done by _store()
        // session_current is more than 60 seconds old
        if ( isset($this->data['session_current']) && $current - $this->data['session_current'] > 60 )
        {
            $sql = "UPDATE __sessions2 SET session_current = '{$current}' 
                    WHERE (`session_id` = '" . $db->escape($this->data['session_id']) . "')";
            $db->query($sql);
        }
        
        // session_page is inaccurate
        if ( isset($this->data['session_page']) && $this->page != $this->data['session_page'] )
        {
            $sql = "UPDATE __sessions2 SET session_page = '" . $db->escape($this->page) . "' 
                    WHERE (`session_id` = '" . $db->escape($this->data['session_id']) . "')";
            $db->query($sql);
        }
        */
    }
    
    /**
     * Insert or update a session row in the database based on the current object state
     *
     * @return void
     * @access private
     */
    function _store()
    {
        global $db, $eqdkp;
        
        if ( empty($this->data['session_start']) )
        {
            // Insert a new session record
            $db->query("INSERT INTO __sessions2 :params", array(
                'session_id'      => $this->data['session_id'],
                'user_id'         => $this->data['user_id'],
                'session_start'   => time(),
                'session_current' => time(),
                'session_page'    => '',
                'session_ip'      => $this->ip
            ));
        }
        else
        {
            // Don't update the session record if it's less than 60 seconds old and we're on the same page as before
            if ( time() - $this->data['session_current'] > 60 || $this->page != $this->data['session_page'] )
            {
                // Update existing session record
                $sql = "UPDATE __sessions2 SET :params WHERE (`session_id` = '" . $db->escape($this->data['session_id']) . "')";
                $db->query($sql, array(
                    'user_id'         => $this->data['user_id'],
                    'session_current' => time(),
                    'session_page'    => '',
                    'session_ip'      => $this->ip
                ));
            }
        }
        
        // Store the session ID in a cookie that expires with the session_length
        $this->set_cookie('csid', $this->data['session_id'], $eqdkp->config['session_length']);
    }
    
    /**
     * Remove expired sessions from the database
     *
     * @return void
     * @access private
     */
    function _cleanup()
    {
        global $db, $eqdkp;
        
        $expiration = time() - $eqdkp->config['session_length'];
        
        if ( $expiration > $eqdkp->config['session_last_cleanup'] )
        {
            $sql = "DELETE FROM __sessions2
                    WHERE (`session_current` < {$expiration})";
            $db->query($sql);
            
            $eqdkp->config_set('session_last_cleanup', time());
        }
    }
    
    ## ########################################################################
    ## Login/Logout
    ## ########################################################################
    
    /**
     * Attempt to 'log in' a user given a name and plaintext password
     *
     * @param string $name User name
     * @param string $pass Password before encryption
     * @return bool
     */
    function login($name, $pass)
    {
        global $db, $eqdkp;
        
        $sql = "SELECT user_id, user_name, user_password, user_salt, user_active
                FROM __users2
                WHERE (`user_name` = '" . $db->escape($name) . "')";
        $result = $db->query($sql);
        $row = $db->fetch_record($result);
        
        if ( $row && $row['user_password'] == Session::HashPassword($pass, $row['user_salt']) && $row['user_active'] )
        {
            $this->set_cookie('cuser', $row['user_id'],       60 * 60 * 24 * 365);
            $this->set_cookie('cauth', $row['user_password'], 60 * 60 * 24 * 365);
            
            // Set the user_id of the current session to this user, since it
            // otherwise wouldn't be updated immediately
            $sql = "UPDATE __sessions2
                    SET user_id = '" . $row['user_id'] . "'
                    WHERE (`session_id` = '" . $this->data['session_id'] . "')";
            $db->query($sql);
            
            return true;
        }
        else
        {
            $this->logout();
            
            return false;
        }
    }
    
    /**
     * Sets the user's cookies to blank values, effectively logging them out
     *
     * @return void
     */
    function logout()
    {
        global $db;
        
        $this->set_cookie('cuser', ANONYMOUS, 0);
        $this->set_cookie('cauth', '', 0);
        
        // Set the user_id of the current session to anonymous, since it
        // otherwise wouldn't be updated immediately
        $sql = "UPDATE __sessions2
                SET user_id = '" . ANONYMOUS . "'
                WHERE (`session_id` = '" . $this->data['session_id'] . "')";
        $db->query($sql);
    }
    
    ## ########################################################################
    ## Cookie Handling
    ## ########################################################################
    
    /**
     * Format a cookie name
     *
     * @param string $name 
     * @return void
     * @access private
     */
    function _cookie_name($name)
    {
        global $eqdkp;
        return "{$eqdkp->config['cookie_name']}_{$name}";
    }
    
    /**
     * Set a cookie
     * 
     * $name automatically gets EQdkp's cookie prefix added to it:
     *     'cauth' becomes 'eqdkp_cauth'
     * 
     * $expires automatically adds time() to its value if it would be a date in the past:
     *     set_cookie('cauth', '', (60 * 60 * 24 * 365));
     *   is equivalent to
     *     set_cookie('cauth', '', time() + (60 * 60 * 24 * 365));
     *   with one exception:
     *     set_cookie('cauth', '', 0);
     *   makes the cookie expire
     *
     * @param string $name Cookie name
     * @param string $val Cookie value
     * @param string $expires Expiration date
     * @return void
     */
    function set_cookie($name, $val, $expires)
    {
        global $eqdkp;
        
        $current = time();
        $expires = ( $expires != 0 && $expires < $current ) 
            ? $current + $expires 
            : $expires;
        
        setcookie($this->_cookie_name($name), $val, $expires);
    }
    
    ## ########################################################################
    ## Static methods
    ## ########################################################################
    
    /**
     * Static function to abstract password encryption
     *
     * @param string $string String to encrypt
     * @param string $salt Salt value
     * @return string
     * @static
     */
    function HashPassword($string, $salt = '')
    {
        global $eqdkp;

        return sha1("{$string}_{$eqdkp->config['auth_salt']}_{$salt}");
    }
}

## ############################################################################
## Action! Note that almost none of this will be used in the real world
## ############################################################################

$user = new Session();
$user->start();

if ( $in->exists('logout') )
{
    $user->logout();
    header("Location: new_auth.php");
}
elseif ( $in->exists('login') )
{
    $user->logout();
    if ( $user->login('Kamien', 'kamien') )
    {
        header("Location: new_auth.php");
    }
    else
    {
        echo "Login failed!";
    }
}

$username = ( isset($user->data['user_name']) ) ? $user->data['user_name'] : 'Guest';

echo "Welcome, {$username}! ";
echo '<a href="new_auth.php?login">Login</a> | <a href="new_auth.php?logout">Logout</a><br/>';

echo '<pre>';
echo "User -------------------------------------------------------------------\n";
print_r($user->data);
echo "Cookies ----------------------------------------------------------------\n";
print_r($_COOKIE);
echo "Queries ----------------------------------------------------------------\n";
foreach ( $db->queries as $query )
{
    echo '    ' . preg_replace('/\s+/', ' ', $query) . "\n";
}
// print_r($db->queries);
echo '</pre>';