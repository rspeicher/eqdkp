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

$total_news = $db->query_first("SELECT count(*) FROM __news");
$start = ( isset($_GET['start']) ) ? $_GET['start'] : 0;

$previous_date = 0;
$sql = "SELECT n.news_id, n.news_date, n.news_headline, n.news_message, u.username
        FROM __news AS n, __users AS u
        WHERE (n.`user_id` = u.`user_id`)
        ORDER BY `news_date` DESC
        LIMIT {$start},{$user->data['user_nlimit']}";
$result = $db->query($sql);

if ( $db->num_rows($result) == 0 )
{
    message_die($user->lang['no_news']);
}

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

/**
* Parses a news post containing BBCode and replaces the code with HTML
*
* @param $message Text message to parse
* @param $parse_quotes Whether or not to parse quote tags
*/
function news_parse(&$message, $parse_quotes = true)
{
    global $user, $eqdkp, $SID, $pm;
    global $eqdkp_hooks;

    // Figure out which quote class to use
    $quote_class = ( $eqdkp->switch_row_class(false) == 'row1' ) ? '1' : '2';

    // Pad message with a space so we can match things at the start of the first line
    $message = ' ' . $message;
    news_make_clickable($message);
    $message = preg_replace("#(\\\){1,}(\"|\'|\&quot;|\&\#039)#", "\"", $message);

    $quote_open = '<table width="90%" border="0" cellspacing="0" cellpadding="3" align="center"><tr><td class="quote'.$quote_class.'"><b>'.$user->lang['quote'].':</b></td></tr><tr><td class="quote'.$quote_class.'">';
    $quote_close = '</td></tr></table>';

    // Patterns and replacements
    $patterns = array();
    $replacements = array();

    // [img]image_url[/img]
    $patterns[0] = "#\[img\](.*?)\[/img\]#si";
    $replacements[0] = "<img src=\"\\1\" alt=\"User-posted image\" />";

    // [url]xxxx://www.example.com[/url]
    $patterns[1] = "#\[url\]([a-z]+?://){1}([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+]+)\[/url\]#si";
    $replacements[1] = "<a href=\"\\1\\2\">\\1\\2</a>";

    // [url]www.example.com[/url]
    $patterns[2] = "#\[url\]([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+]+)\[/url\]#si";
    $replacements[2] = "<a href=\"http://\\1\">\\1</a>";

    // [url=xxxx://www.example.com]Example[/url]
    $patterns[3] = "#\[url=([a-z]+?://){1}([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+]+)\](.*?)\[/url\]#si";
    $replacements[3] = "<a href=\"\\1\\2\">\\3</a>";

    // [url=www.example.com]Example[/url]
    $patterns[4] = "#\[url=([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+]+)\](.*?)\[/url\]#si";
    $replacements[4] = "<a href=\"http://\\1\">\\2</a>";

    // [url=mailto:user@example.com]E-Mail[/url]
    $patterns[5] = "#\[url=mailto:([a-zA-Z0-9]+[\.a-zA-Z0-9_-]*@[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+)\](.*?)\[/url\]#si";
    $replacements[5] = "<a href=\"mailto:\\1\">\\2</a>";

    // [item]name[/item]
    $patterns[6] = "#\[item\](.*?)\[/item\]#si";
    $replacements[6] = "<iframe name=\"item_stats\" src=\"http://www.crusadersvalorous.org/items/index.php?linked=1&amp;news=true&amp;item=\\1\" width=\"450\" height=\"260\" scrolling=\"no\" frameborder=\"0\" marginheight=\"0\" marginwidth=\"0\"></iframe>";

    $count = sizeof($patterns);
    if ( @is_object($pm) )
    {
        $plugin_news = $pm->do_hooks('news_parse');
        foreach ( $plugin_news as $news_array )
        {
            foreach ( $news_array as $find_replace )
            {
                if ( (isset($find_replace['patterns'])) && (isset($find_replace['replacements'])) )
                {
                    $count++;
                    $patterns[$count] = $find_replace['patterns'];
                    $replacements[$count] = $find_replace['replacements'];
                }
            }
        }
    }

    $message = preg_replace($patterns, $replacements, $message);

    $message = str_replace('[b]', '<b>', $message);
    $message = str_replace('[/b]', '</b>', $message);

    $message = str_replace('[i]', '<i>', $message);
    $message = str_replace('[/i]', '</i>', $message);

    $message = str_replace('[u]', '<u>', $message);
    $message = str_replace('[/u]', '</u>', $message);
    
    $message = str_replace('[center]', '<center>', $message);
    $message = str_replace('[/center]', '</center>', $message);

    if ( $parse_quotes )
    {
        $message = str_replace('[quote]', $quote_open, $message);
        $message = str_replace('[/quote]', $quote_close, $message);
    }

    // Undo our pad
    $message = substr($message, 1);

    return $message;
}

/**
* Replace "magic URLs" of form http://xxx.example.com, www.example.com, user@example.com
*
* @param $message Message to parse
*/
function news_make_clickable(&$message)
{
    global $eqdkp;
    
    $patterns = array();
    $replacements = array();
    
    $server_protocol = 'https://';
    $server_port = ( $eqdkp->config['server_port'] != 80 ) ? ':' . trim($eqdkp->config['server_port']) . '/' : '/';

    $match = array();
    $replace = array();

    // relative urls for this board
    $match[] = '#' . $server_protocol . trim($eqdkp->config['server_name']) . $server_port . preg_replace('/^\/?(.*?)(\/)?$/', '\1', trim($eqdkp->config['server_path'])) . '/([^\t\n\r <"\']+)#i';
    $replace[] = '<!-- l --><a href="\1" target="_blank">\1</a><!-- l -->';

    // matches a xxxx://aaaaa.bbb.cccc. ...
    $match[] = '#(^|[\n ])([\w]+?://.*?[^\t\n\r<"]*)#ie';
    $replace[] = "'\\1<!-- m --><a href=\"\\2\" target=\"_blank\">' . ( ( strlen(str_replace(' ', '%20', '\\2')) > 55 ) ?substr(str_replace(' ', '%20', '\\2'), 0, 39) . ' ... ' . substr(str_replace(' ', '%20', '\\2'), -10) : str_replace(' ', '%20', '\\2') ) . '</a><!-- m -->'";

    // matches a "www.xxxx.yyyy[/zzzz]" kinda lazy URL thing
    $match[] = '#(^|[\n ])(www\.[\w\-]+\.[\w\-.\~]+(?:/[^\t\n\r<"]*)?)#ie';
    $replace[] = "'\\1<!-- w --><a href=\"http://\\2\" target=\"_blank\">' . ( ( strlen(str_replace(' ', '%20', '\\2')) > 55 ) ? substr(str_replace(' ', '%20', '\\2'), 0, 39) . ' ... ' . substr(str_replace(' ', '%20', '\\2'), -10) : str_replace(' ', '%20', '\\2') ) . '</a><!-- w -->'";

    // matches an email@domain type address at the start of a line, or after a space.
    $match[] = '#(^|[\n ])([a-z0-9\-_.]+?@[\w\-]+\.([\w\-\.]+\.)?[\w]+)#ie';
    $replace[] = "'\\1<!-- e --><a href=\"mailto:\\2\">' . ( ( strlen('\\2') > 55 ) ?substr('\\2', 0, 39) . ' ... ' . substr('\\2', -10) : '\\2' ) . '</a><!-- e -->'";

    $message = preg_replace($match, $replace, $message);
}
?>
