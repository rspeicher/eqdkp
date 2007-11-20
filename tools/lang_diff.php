<?php
/*
 ------------------------------------------------------------------------------
 This file will find both missing and extraneous language file entries, using
 English as the baseline.
 ------------------------------------------------------------------------------
*/

$nl = ( isset($argv) ) ? "\n" : "<br />";

define('EQDKP_INC', true); // Shut the language files up

$lang_path  = dirname(__FILE__) . '/../upload/language/';
$languages  = array('chinese', 'german', 'french');

// First find our baseline using the English files
$baseline = include_lang('english');

// Now get the other languages
$lang = array();
foreach ( $languages as $language )
{
    $lang[$language] = array(
        'missing' => null,
        'extra'   => null,
        'base'    => include_lang($language)
    );
    
    $lang[$language]['missing'] = array_diff($baseline, $lang[$language]['base']);
    $lang[$language]['extra']   = array_diff($lang[$language]['base'], $baseline);
    
    $header = sprintf("%s - (%d missing, %d extra) ", ucfirst($language), count($lang[$language]['missing']), count($lang[$language]['extra']));
    echo str_pad($header, 80, '-') . $nl;
    echo implode(', ', $lang[$language]['missing']) . $nl . $nl;
}

## ############################################################################
## Functions
## ############################################################################

function include_lang($language)
{
    global $lang_path;
    
    unset($lang, $retval);
    
    $retval = array();
    
    @include_once("{$lang_path}/{$language}/lang_admin.php");
    @include_once("{$lang_path}/{$language}/lang_install.php");
    @include_once("{$lang_path}/{$language}/lang_main.php");
    
    // Fucked again by the lack of array_diff_key! Create an array of just keys
    $retval = array_keys($lang);
    
    return $retval;
}