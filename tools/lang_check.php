<?php
/*
 NOTICE -----------------------------------------------------------------------
 Because certain language keys are never used directly, but rather created 
 dynamically - 'noauth_u_event_list', for example - this list of unused keys 
 can't be entirely accurate. But it's a good starting point.
 ------------------------------------------------------------------------------
*/

define('EQDKP_INC', true); // Shut the language files up

$lang_path = dirname(__FILE__) . '/../upload/language/english/';
$lang_files = array('lang_main.php', 'lang_admin.php', 'lang_install.php');
foreach ( $lang_files as $file )
{
    include_once($lang_path . $file);
}

if ( !is_array($lang) || count($lang) == 0 )
{
    die('Invalid language array, something went wrong.');
}

$used   = array(); // Stores language indexes that actually get used somewhere in EQdkp
$counts = array(); // Counts the number of times each index is used
$ignore = array('.', '..', '.svn', '.htaccess', 'index.html', 'language', 'templates');

$base_path = dirname(__FILE__) . '/../upload/';

read_folder($base_path);

$used = array_unique($used);
reset($used);

$unused = array_diff($lang, $used);
echo count($unused) . " unused language keys:\n";
print_r($unused);

## ############################################################################
## Functions
## ############################################################################

function read_folder($folder)
{
    global $ignore;
    
    $folder = preg_replace('/\/$/', '', $folder);
    
    if ( $dir = opendir($folder) )
    {
        while ( $path = readdir($dir) )
        {
            if ( !in_array(basename($path), $ignore) && is_dir($folder . '/' . $path) )
            {
                read_folder($folder . '/' . $path);
            }
            elseif ( !in_array(basename($path), $ignore) && is_file($folder . '/' . $path) )
            {
                read_file($folder . '/' . $path);
            }
        }
    }
}

function read_file($path)
{
    global $used, $counts, $lang;
    
    if ( !preg_match('/\.php$/', $path) )
    {
        return;
    }
    
    $file = file_get_contents($path);
    
    preg_match_all('/lang\[\'([\w]+)\'\]/', $file, $matches, PREG_SET_ORDER);
    
    if ( count($matches) > 0 )
    {
        foreach ( $matches as $match )
        {
            $lang_key = $match[1];
            $used[$lang_key] = $lang[$lang_key]; // If we're running on PHP5.1 we could use array_diff_key, but we're not
            
            if ( isset($counts[$lang_key]) )
            {
                $counts[$lang_key] += 1;
            }
            else
            {
                $counts[$lang_key] = 1;
            }
        }
    }
}