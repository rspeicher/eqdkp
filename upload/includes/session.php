<?php
/**
 * Project:     EQdkp - Open Source Points System
 * License:     http://eqdkp.com/?p=license
 * -----------------------------------------------------------------------
 * File:        session.php
 * Began:       Sat Dec  8 2007
 * Date:        $Date$
 * -----------------------------------------------------------------------
 * @author      $Author$
 * @copyright   2002-2008 The EQdkp Project Team
 * @link        http://eqdkp.com/
 * @package     eqdkp
 * @version     $Rev$
 */

if ( !defined('EQDKP_INC') )
{
    header('HTTP/1.0 404 Not Found');
    exit;
}

/**
 * Session class
 * 
 * Stores session data, user options, language values, style settings, performs
 * login and logout operations, checks user authorization.
 *
 * @package eqdkp
 */
class Session
{
    /**#@+
     * @var array
     */
    var $data  = array();
    var $lang  = array();
    var $style = array();
    /**#@-*/

    /**
     * Session IP address
     *
     * @var string
     */
    var $ip      = '';
    
    // var $browser = '';
    
    /**
     * Current session page
     *
     * @var string
     */
    var $page    = '';
    
    function session()
    {
        $this->ip = ( !empty($_SERVER['REMOTE_ADDR']) )     ? $_SERVER['REMOTE_ADDR']     : '127.0.0.1';
        $this->ip = preg_replace('/[^\d\.]/', '', $this->ip);
        
        // $this->browser = ( !empty($_SERVER['HTTP_USER_AGENT']) ) ? $_SERVER['HTTP_USER_AGENT'] : $_ENV['HTTP_USER_AGENT'];
        
        $this->page = ( !empty($_SERVER['REQUEST_URI']) ) 
            ? $_SERVER['REQUEST_URI'] 
            : $_SERVER['SCRIPT_NAME'] . (( isset($_SERVER['QUERY_STRING']) ) 
                ? '?' . $_SERVER['QUERY_STRING'] 
                : ''
            );
    }
    
    /**
     * Work around a bug(?) where the constructor apparently can't access variables
     * via global $var
     *
     * @return void
     * @access private
     */
    function _session()
    {
        global $eqdkp;
        
        $this->page = str_replace($eqdkp->config['server_path'], '', $this->page);
    }
    
    /**
     * Start or renew a session
     *
     * @return void
     */
    function start()
    {
        // Blah blah blah, 10 minutes of debugging, grumble grumble
        $this->_session();
        
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
        global $db, $in;
        
        $cookie_user = $in->get($this->_cookie_name('cuser'), ANONYMOUS);
        $cookie_auth = $in->get($this->_cookie_name('cauth'));
        
        if ( $cookie_user > 0 )
        {
            $sql = "SELECT user_id, user_name, user_email, user_alimit, user_elimit,
                        user_ilimit, user_nlimit, user_rlimit,user_style, user_lang,
                        user_lastpage, user_active
                    FROM __users
                    WHERE (`user_id` = '{$cookie_user}') 
                    AND (`user_password` = '" . $db->escape($cookie_auth) . "')
                    LIMIT 1";
            $result = $db->query($sql);
            $row = $db->fetch_record($result);
            
            if ( is_array($row) )
            {
                $this->data = array_merge($this->data, $row);
            }
            else
            {
                // Rare case where cookie data existed, but we didn't get valid
                // values, so default the user settings
                $this->data = $this->_user_defaults();
            }
        }
        else
        {
            // User doesn't exist, populate data with default values
            $this->data = $this->_user_defaults();
        }
    }
    
    /**
     * Return an array of default user data, to be merged with {@link data}
     *
     * @return array
     * @access private
     */
    function _user_defaults()
    {
        global $eqdkp;
        
        $retval = array(
            'user_id'     => ANONYMOUS,
            'user_alimit' => $eqdkp->config['default_alimit'],
            'user_elimit' => $eqdkp->config['default_elimit'],
            'user_ilimit' => $eqdkp->config['default_ilimit'],
            'user_nlimit' => $eqdkp->config['default_nlimit'],
            'user_rlimit' => $eqdkp->config['default_rlimit'],
            'user_style'  => $eqdkp->config['default_style'],
            'user_lang'   => $eqdkp->config['default_lang'],
        );
        
        return $retval;
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
        
        $sql = "SELECT session_id, session_start, session_current, session_page,
                    session_ip
                FROM __sessions AS s
                WHERE (`session_id` = '" . $db->escape($cookie_sid) . "') 
                AND (`session_ip` = '" . $db->escape($this->ip) . "')
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
            $sql = "UPDATE __users SET user_lastvisit = '{$current}' 
                    WHERE (`user_id` = '{$this->data['user_id']}')";
            $db->query($sql);
        }
        
        /* These get done by _store()
        // session_current is more than 60 seconds old
        if ( isset($this->data['session_current']) && $current - $this->data['session_current'] > 60 )
        {
            $sql = "UPDATE __sessions SET session_current = '{$current}' 
                    WHERE (`session_id` = '" . $db->escape($this->data['session_id']) . "')";
            $db->query($sql);
        }
        
        // session_page is inaccurate
        if ( isset($this->data['session_page']) && $this->page != $this->data['session_page'] )
        {
            $sql = "UPDATE __sessions SET session_page = '" . $db->escape($this->page) . "' 
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
            $db->query("INSERT INTO __sessions :params", array(
                'session_id'      => $this->data['session_id'],
                'user_id'         => $this->data['user_id'],
                'session_start'   => time(),
                'session_current' => time(),
                'session_page'    => $this->page,
                'session_ip'      => $this->ip
            ));
        }
        else
        {
            // Don't update the session record if it's less than 60 seconds old and we're on the same page as before
            if ( time() - $this->data['session_current'] > 60 || $this->page != $this->data['session_page'] )
            {
                // Update existing session record
                $sql = "UPDATE __sessions SET :params WHERE (`session_id` = '" . $db->escape($this->data['session_id']) . "')";
                $db->query($sql, array(
                    'user_id'         => $this->data['user_id'],
                    'session_current' => time(),
                    'session_page'    => $this->page,
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
            $sql = "DELETE FROM __sessions
                    WHERE (`session_current` < {$expiration})";
            $db->query($sql);
            
            $eqdkp->config_set('session_last_cleanup', time());
        }
    }
    
    ## ########################################################################
    ## User setup and permissions
    ## ########################################################################
    
    /**
     * Sets up user language and style settings
     *
     * @param $lang_set Language to set
     * @param $style Style ID to set
     */
    function setup($style = 0)
    {
        global $db, $eqdkp, $tpl;
        
        // Populate $lang with the values from the language files
        $this->_setup_language();

        // Populate $style with the values from the database
        $this->_setup_style($style);
        
        // Populate $data[auth] with the values from the database
        $this->_setup_permissions();
    }
    
    /**
     * Populate {@link $lang} with the values from the language files, based
     * on user settings
     *
     * @return void
     * @access private
     */
    function _setup_language()
    {
        global $eqdkp_root_path;
        global $eqdkp;
        
        $lang_name = '';
        $lang_path = '';
        
        // user_lang has already been set by _user_restore(), regardless of anonymity
        $lang_name = ( file_exists($eqdkp_root_path . 'language/' . $this->data['user_lang']) ) 
            ? $this->data['user_lang'] 
            : $eqdkp->config['default_lang'];

        $lang_path = "language/{$lang_name}";

        // Include the common language strings
        require_once("{$eqdkp_root_path}{$lang_path}/lang_main.php");
        
        // Administrative language strings
        if ( defined('IN_ADMIN') )
        {
            require_once("{$eqdkp_root_path}{$lang_path}/lang_admin.php");
        }
        
        // Game language strings
        $game_path = "games/{$eqdkp->config['current_game']}";
        $game_lang_name = "{$eqdkp_root_path}{$game_path}/{$lang_path}/lang_game.php";
        if (is_file($game_lang_name))
        {
            include($game_lang_name);
        }
        
        $this->lang = $lang;
        unset($lang);
    }
    
    /**
     * Populate {@link $style} with the values from the database, based
     * on user settings
     *
     * @param int $style Populate with a specific style ID, otherwise uses the user's setting
     * @return void
     * @access private
     */
    function _setup_style($style)
    {
        global $db, $tpl;
        
        $style = intval($style);
        
        $style = ( $style == 0 ) ? intval($this->data['user_style']) : $style;

        // Get database values for this style
        $sql = "SELECT s.*, c.*
                FROM __styles AS s, __style_config AS c
                WHERE (s.`style_id` = c.`style_id`)
                AND (s.`style_id` = '{$style}')";
        $result = $db->query($sql);
        if ( !($this->style = $db->fetch_record($result)) )
        {
            // If we STILL can't get style information, go back to the default
            // Fail-safe in case someone (ahem) forgets to add style config settings
            
            // NOTE: This was mostly only an issue during development before the
            // manage_styles panel was developed, but can remain here as a fail-safe
            $sql = "SELECT s.*, c.*
                    FROM __styles AS s, __style_config AS c
                    WHERE s.`style_id` = c.`style_id`
                    AND s.`style_id` = '{$eqdkp->config['default_style']}'";
            $result = $db->query($sql);
            $this->style = $db->fetch_record($result);
        }

        $tpl->set_template($this->style['template_path']);
    }
    
    /**
     * Populate {@link $data} with an 'auth' array, containing a user's 
     * permissions, or the default permissions if the user is not logged in.
     *
     * @return void
     * @access private
     */
    function _setup_permissions()
    {
        global $db;
        
        $this->data['auth'] = array();
        
        if ( $this->data['user_id'] == ANONYMOUS )
        {
            // Get the default permissions if they're not logged in
            $sql = "SELECT auth_value, auth_default AS auth_setting
                    FROM __auth_options";
        }
        else
        {
            $sql = "SELECT o.auth_value, u.auth_setting
                    FROM __auth_users AS u, __auth_options AS o
                    WHERE (u.`auth_id` = o.`auth_id`)
                    AND (u.`user_id` = '{$this->data['user_id']}')";
        }
        if ( !($result = $db->query($sql)) )
        {
            die('Could not obtain permission data');
        }
        while ( $row = $db->fetch_record($result) )
        {
            $this->data['auth'][$row['auth_value']] = $row['auth_setting'];
        }
    }
    
    /**
     * Checks if the current user, or a specific user, is authorized to perform
     * an action.
     * 
     * <code>
     * // Check if the current user has permission to add a raid; die with an error message if not
     * $user->check_auth('a_raid_add');
     * 
     * // Check if user 2 has any administrative raid permissions; return boolean
     * $result = $user->check_auth('a_raid_', false, 2);
     * </code>
     * 
     * @param string $auth_value Permission to check
     * @param bool $die Perform message_die() if the permission is denied?
     * @param int $user_id Check a specific user ID instead of the current user
     * @return bool|void
     */
    function check_auth($auth_value, $die = true, $user_id = 0)
    {
        // To cut down the query count, store the auth settings 
        // for $user_id in a static var if we need to
        static $specific_auth = array();
        
        // Lets us know if we're looking up data for a different user_id
        // than the last one
        static $previous_user_id = 0;
        
        // Reset $specific_auth if our $previous_user_id has changed from $user_id
        if ( ($user_id > 0) && ($user_id != $previous_user_id) )
        {
            $previous_user_id = $user_id;
            $specific_auth = array();
        }
        
        // Look up a specific user if an id was provided and $specific_auth contains
        // no data, otherwise we're going to use the $this->data['auth'] array 
        // or $specific_auth
        if ( (intval($user_id) > 0) && (sizeof($specific_auth) == 0) )
        {
            global $db;
            
            $auth = array();
            $sql = "SELECT au.auth_setting, ao.auth_value
                    FROM __auth_users AS au, __auth_options AS ao
                    WHERE (au.`auth_id` = ao.`auth_id`)
                    AND (au.`user_id` = '{$user_id}')";
            $result = $db->query($sql);
            while ( $row = $db->fetch_record($result) )
            {
                $auth[$row['auth_value']] = $row['auth_setting'];
            }
            $db->free_result($result);
            $specific_auth = $auth;
        }
        elseif ( (intval($user_id) > 0) && (sizeof($specific_auth) > 0) )
        {
            $auth = $specific_auth;
        }
        else
        {
            $auth = $this->data['auth'];
        }
        
        if ( (!isset($auth)) || (!is_array($auth)) )
        {
            return ( $die ) 
                ? message_die($this->lang['noauth_default_title'], $this->lang['noauth_default_title']) 
                : false;
        }
        
        // If auth_value ends with a '_' it's checking for any permissions of that type
        $exact = ( preg_match('/_$/', $auth_value) ) ? false : true;
        
        foreach ( $auth as $value => $setting )
        {
            if ( $exact )
            {
                if ( ($value == $auth_value) && ($setting == 'Y') )
                {
                    return true;
                }
            }
            else
            {
                if ( preg_match('/^('.$auth_value.'.+)$/', $value, $match) )
                {
                    if ( $auth[$match[1]] == 'Y' )
                    {
                        return true;
                    }
                }
            }
        }
        
        $index = 'noauth_default_title';
        if ( $exact && isset($this->lang['noauth_' . $auth_value]) )
        {
            $index = 'noauth_' . $auth_value;
        }
        
        return ( $die ) 
            ? message_die($this->lang[$index], $this->lang['noauth_default_title']) 
            : false;
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
                FROM __users
                WHERE (`user_name` = '" . $db->escape($name) . "')";
        $result = $db->query($sql);
        $row = $db->fetch_record($result);
        
        if ( $row )
        {
            // Appears to be the user's first login in the new format. Check 
            // their password against the old (md5) format, if it matches, 
            // generate their new password using their unique salt value
            if ( strlen($row['user_password']) == 32 && $row['user_password'] == md5($pass) )
            {
                $salt = generate_salt();
                $db->query("UPDATE __users SET :params WHERE (`user_id` = '{$row['user_id']}')", array(
                    'user_salt'      => $salt,
                    'user_password'  => hash_password($pass, $salt),
                ));
                unset($salt);
                
                // Recurse so we can use the record we just updated
                return $this->login($name, $pass);
            }
            
            if ( $row['user_password'] == hash_password($pass, $row['user_salt']) && $row['user_active'] )
            {
                $this->set_cookie('cuser', $row['user_id'],       60 * 60 * 24 * 365);
                $this->set_cookie('cauth', $row['user_password'], 60 * 60 * 24 * 365);
            
                // Set the user_id of the current session to this user, since it
                // otherwise wouldn't be updated immediately
                $sql = "UPDATE __sessions
                        SET user_id = '" . $row['user_id'] . "'
                        WHERE (`session_id` = '" . $this->data['session_id'] . "')";
                $db->query($sql);
            
                return true;
            }
        }

        // At this point the login attempt has failed, destroy any cookies that
        // may have been set and return
        $this->logout();
            
        return false;
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
        $sql = "UPDATE __sessions
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
     * @param int $expires Expiration date
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
}