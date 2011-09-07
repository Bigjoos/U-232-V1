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
	print $HTMLOUT;
	exit();
}

   $topic_id = (isset($_GET['topic_id']) ? intval($_GET['topic_id']) :  (isset($_POST['topic_id']) ? intval($_POST['topic_id']) :  0));
   $last_post = (isset($_GET['last_post']) ? intval($_GET['last_post']) :  (isset($_POST['last_post']) ? intval($_POST['last_post']) :  0));   

	$check_it = sql_query('SELECT id, last_post_read FROM read_posts WHERE user_id='.$CURUSER['id'].' and topic_id='.$topic_id);
	$check_it_arr = mysql_fetch_assoc($check_it);


	//===  update read posts
	
	if($check_it_arr['last_post_read'] > 0)
	{
	sql_query('UPDATE read_posts SET last_post_read = '.$last_post.' WHERE topic_id = '.$topic_id.' AND user_id = '.$CURUSER['id']);
	}
	else
	{
	sql_query('INSERT INTO read_posts (`user_id` ,`topic_id` ,`last_post_read`) VALUES ('.$CURUSER['id'].', '.$topic_id .', '.$last_post.')');	
	}
	
	//=== ok, all done here, send them back! \o/
	header('Location: forums.php?action=view_unread_posts'); 
die();
?>