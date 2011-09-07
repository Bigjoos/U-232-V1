<?php
/**
 *   https://09source.kicks-ass.net:8443/svn/installer09/
 *   Licence Info: GPL
 *   Copyright (C) 2010 Installer09 v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn,kidvision.
 **/
/**********************************************************
New 2010 forums that don't suck for TB based sites....

Beta Thurs Sept 9th 2010 v0.5

Powered by Bunnies!!!
***************************************************************/

if (!defined('BUNNY_FORUMS')) 
{
	$HTMLOUT .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
        <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
        <title>ERROR</title>
        </head><body>
        <h1 style="text-align:center;">ERROR</h1>
        <p style="text-align:center;">How did you get here? silly rabbit Trix are for kids!.</p>
        </body></html>';
	PRINT $HTMLOUT;
	exit();
}

   $topic_id = (isset($_GET['topic_id']) ? intval($_GET['topic_id']) :  (isset($_POST['topic_id']) ? intval($_POST['topic_id']) :  0));
   $forum_id = (isset($_GET['forum_id']) ? intval($_GET['forum_id']) :  (isset($_POST['forum_id']) ? intval($_POST['forum_id']) :  0));
   

	//=== first see if they are being norty...
	$norty_res = sql_query('SELECT min_class_read FROM forums WHERE id = '.$forum_id);
	$norty_arr = mysql_fetch_row($norty_res);

    if (!is_valid_id($topic_id) || $norty_arr[0] > $CURUSER['class'] || !is_valid_id($forum_id))
    {
	stderr('Error', 'Bad ID.');
    }
	
	//=== see if they are subscribed already
	$res = sql_query('SELECT id FROM subscriptions WHERE user_id = '.$CURUSER['id'].' AND topic_id = '.$topic_id);
	$arr = mysql_fetch_row($res);    

	if ($arr[0] > 0)
	{
	stderr('Error', 'You are already subscribed to this topic!');
	}

	//=== ok, that the hell, let's add it \o/
	sql_query('INSERT INTO `subscriptions` (`user_id`, `topic_id`) VALUES ('.$CURUSER['id'].', '.$topic_id.')');
	
	header('Location: forums.php?action=view_topic&topic_id='.$topic_id.'&s=1'); 
	
die();
?>