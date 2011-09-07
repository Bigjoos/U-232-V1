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

if (!defined('BUNNY_FORUMS') || $CURUSER['class'] < UC_MODERATOR) 
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

	$post_id = (isset($_GET['post_id']) ? intval($_GET['post_id']) :  (isset($_POST['post_id']) ? intval($_POST['post_id']) :  0));
	$forum_id = (isset($_GET['forum_id']) ? intval($_GET['forum_id']) :  (isset($_POST['forum_id']) ? intval($_POST['forum_id']) :  0));
	$topic_id = (isset($_GET['topic_id']) ? intval($_GET['topic_id']) :  (isset($_POST['topic_id']) ? intval($_POST['topic_id']) :  0));
	
    if  (!is_valid_id($post_id) || !is_valid_id($forum_id) || !is_valid_id($topic_id))
    {
	stderr('Error', 'Bad ID.');
    }

      $res = sql_query('SELECT p.added, p.body, p.edited_by, p.user_id AS poster_id, p.edit_date, p.post_title, p.icon, p.post_history, p.bbcode, t.topic_name AS topic_name, 
      				f.name AS forum_name, u.id, u.username, u.suspended, u.class, u.donor, u.chatpost, u.leechwarn, u.pirate, u.king, u.warned, u.enabled, u.avatar, u.avatar_rights, u.offensive_avatar 
      				FROM posts AS p LEFT JOIN topics AS t ON p.topic_id = t.id LEFT JOIN forums AS f ON t.forum_id = f.id LEFT JOIN users AS u ON p.user_id = u.id 
					WHERE '.($CURUSER['class'] < UC_MODERATOR ? 'p.status = \'ok\' AND t.status = \'ok\' AND' : 
					($CURUSER['class'] < $min_delete_view_class ? 'p.status != \'deleted\' AND t.status != \'deleted\'  AND' : '')).' p.id = '.$post_id);
      $arr = mysql_fetch_array($res);	
      
      $res_edited = sql_query('SELECT id, username, class, donor, suspended, chatpost, leechwarn, pirate, king, avatar_rights, warned, enabled, avatar, offensive_avatar FROM users WHERE id = '.$arr['edited_by']);
      $arr_edited = mysql_fetch_array($res_edited);
      
      $icon = htmlspecialchars($arr['icon']);
      $post_title = htmlentities($arr['post_title'], ENT_QUOTES);
	
	
	$location_bar = '<h1><a class="altlink" href="forums.php">Forums</a> <img src="pic/arrow_next.gif" alt="&#9658;" /> 
			<a class="altlink" href="forums.php?action=view_forum&amp;forum_id='.$forum_id.'">'.htmlentities($arr['forum_name'], ENT_QUOTES).'</a>
			<img src="pic/arrow_next.gif" alt="&#9658;" /> 
			<a class="altlink" href="forums.php?action=view_topic&amp;topic_id='.$topic_id.'">'.htmlentities($arr['topic_name'], ENT_QUOTES).'</a></h1>
			<span style="text-align: center;">'.$mini_menu.'</span><br /><br />';

	$HTMLOUT .= $location_bar;
	
	$HTMLOUT .= '<h1>'.htmlspecialchars($arr['username']).'\'s Final Edited Post. last edited by: '.print_user_stuff($arr_edited).'</h1>
		<table border="0" cellspacing="5" cellpadding="10" width="90%">
		<tr>
			<td class="forum_head" align="left" width="120px" valign="middle">
			<span style="white-space:nowrap;">#'.$post_id.'</span>
			<span style="font-weight: bold;">'.htmlspecialchars($arr['username']).'</span></td>
			<td class="forum_head" align="left" width="120px" valign="middle">
			<span style="white-space:nowrap;"> posted on: '.get_date($arr['added'],'').' ['.get_date($arr['added'],'',0,1).'] GMT
			'.($post_title !== '' ? '&nbsp;&nbsp;&nbsp;&nbsp; Title: <span style="font-weight: bold;">'.$post_title.'</span>' : '').($icon !== '' ? ' <img src="pic/smilies/'.$icon.'.gif" alt="Post Icon" />' : '').'</span>
			</td>
			<td class="two" align="center" width="120px" valign="top">'.avatar_stuff($arr).'<br />'.print_user_stuff($arr).'</td>
			<td class="one" align="left" valign="top" colspan="2">'.($arr['bbcode'] == 'yes' ? format_comment($arr['body']) : format_comment_no_bbcode($arr['body'])).'</td>
		</tr>
		</table><br /><h1>Post History</h1>[ All Post Edits by Date Desc. ]<br /><br />'.$arr['post_history'].'<br />'.$location_bar ;

?>