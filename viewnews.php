<?php
/******************************
 * EQdkp
 * Copyright 2002-2003
 * Licensed under the GNU GPL.  See COPYING for full terms.
 * ------------------
 * viewnews.php
 * Began: Sat April 5 2003
 * 
 * $Id: viewnews.php 46 2007-06-19 07:29:11Z tsigo $
 * 
 ******************************/
 
define('EQDKP_INC', true);
$eqdkp_root_path = './';
include_once($eqdkp_root_path . 'common.php');
 
$total_news = $db->query_first('SELECT count(*) FROM ' . NEWS_TABLE);
$start = ( isset($_GET['start']) ) ? $_GET['start'] : 0;

$previous_date = 0;
$sql = 'SELECT n.news_id, n.news_date, n.news_headline, n.news_message, u.username
        FROM ' . NEWS_TABLE . ' n, ' . USERS_TABLE . ' u
        WHERE (n.user_id = u.user_id)
        ORDER BY news_date DESC
        LIMIT '.$start.','.$user->data['user_nlimit'];
$result = $db->query($sql);

if ( $db->num_rows($result) == 0 )
{
    message_die($user->lang['no_news']);
}

$cur_hash = hash_filename("viewnews.php");
// print"HASH::$cur_hash::<br>";

while ( $news = $db->fetch_record($result) )
{
    // Show a new date row if it's not the same as the last
    if ( date($user->style['date_notime_long'], $news['news_date']) != date($user->style['date_notime_long'], $previous_date) )
    {
        $tpl->assign_block_vars('date_row', array(
            'DATE' => date($user->style['date_notime_long'], $news['news_date']))
        );
        
        $previous_date = $news['news_date'];
    }
    
    $message = $news['news_message'];
    $message = nl2br($message);
    news_parse($message);
    $message = preg_replace('#(\&amp;){2,}#', '&amp;', $message);
    
    $tpl->assign_block_vars('date_row.news_row', array(
        'ROW_CLASS' => $eqdkp->switch_row_class(),
        'HEADLINE' => stripslashes($news['news_headline']),
        'AUTHOR' => $news['username'],
        'TIME' => date("h:ia T", $news['news_date']),
        'MESSAGE' => $message)
    );
}
$db->free_result($result);


$tpl->assign_vars(array(
    'NEWS_PAGINATION' => generate_pagination('viewnews.php' . $SID, $total_news, $user->data['user_nlimit'], $start))
);

$eqdkp->set_vars(array(
    'page_title'    => sprintf($user->lang['title_prefix'], $eqdkp->config['guildtag'], $eqdkp->config['dkp_name']),
    'template_file' => 'viewnews.html',
    'display'       => true)
);
?>
