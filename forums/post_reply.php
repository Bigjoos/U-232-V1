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
  $page = $colour = $extension_error = $size_error = '';
	$topic_id = (isset($_GET['topic_id']) ? intval($_GET['topic_id']) :  (isset($_POST['topic_id']) ? intval($_POST['topic_id']) :  0));
   
    if (!is_valid_id($topic_id))
    {
	stderr('Error', 'Bad ID.');
    }	

      $res = sql_query('SELECT t.topic_name, t.locked, f.min_class_read, f.min_class_write, f.id AS real_forum_id, s.id AS subscribed_id 
								FROM topics AS t LEFT JOIN forums AS f ON t.forum_id = f.id LEFT JOIN subscriptions AS s ON s.topic_id = t.id 
								WHERE '.($CURUSER['class'] < UC_MODERATOR ? 't.status = \'ok\' AND' : 
								($CURUSER['class'] < $min_delete_view_class ? 't.status != \'deleted\'  AND' : '')).' t.id='.$topic_id);
      $arr = mysql_fetch_assoc($res);
    
    		//=== stop them, they shouldn't be here lol
    		if ($arr['locked'] == 'yes')
		{
		stderr('Error', 'This topic is locked.');
		}
		if ($CURUSER['class'] < $arr['min_class_read'] || $CURUSER['class'] < $arr['min_class_write'])
		{
		stderr('Error', 'Bad ID.');
		}
		if ($CURUSER['forum_post'] == 'no' || $CURUSER['suspended'] == 'yes')
		{
		stderr('Error', 'Your posting rights have been suspended.');
		}

	$quote = (isset($_GET['quote_post']) ? intval($_GET['quote_post']) :  0);
	$key = (isset($_GET['key']) ? intval($_GET['key']) :  0);
	$body = (isset($_POST['body']) ? $_POST['body'] : '');
	$post_title = strip_tags((isset($_POST['post_title']) ? $_POST['post_title'] : ''));
	$icon = htmlspecialchars((isset($_POST['icon']) ? $_POST['icon'] : ''));
	$bb_code = (isset($_POST['bb_code']) && $_POST['bb_code'] == 'no' ? 'no' : 'yes');
	$subscribe = ((isset($_POST['subscribe']) && $_POST['subscribe'] == 'yes') ? 'yes' : ((!isset($_POST['subscribe']) && $arr['subscribed_id'] > 0) ? 'yes' : 'no'));
  $topic_name = htmlspecialchars($arr['topic_name']);
      //== if it's a quote
      if ($quote !== 0 && $body == '')
      {
      $res_quote = sql_query('SELECT p.body, u.username FROM posts AS p LEFT JOIN users AS u ON p.user_id = u.id WHERE p.id='.$quote);
      $arr_quote = mysql_fetch_array($res_quote);
      //=== if member exists, then add username, and then link back to post that was quoted with date :-D
      $quoted_member = ($arr_quote['username'] == '' ? 'Lost member' : htmlspecialchars($arr_quote['username']));
      $body = '[quote='.$quoted_member.($quote > 0 ? ' | post='.$quote : '').($key > 0 ? ' | key='.$key : '').']'.htmlspecialchars($arr_quote['body']).'[/quote]';
      }       
      
      
      if (isset($_POST['button']) && $_POST['button'] == 'Post')
      {
      
      //=== make sure they are posting something
      if($body === '')
      {
      stderr('Error', 'No body text.');
      }
      
      $ip = ($CURUSER['ip'] == '' ? htmlspecialchars($_SERVER['REMOTE_ADDR']) : $CURUSER['ip']);
      
      sql_query('INSERT INTO `posts` (`topic_id`, `user_id`, `added`, `body`, `icon`, `post_title`, `bbcode`, `ip`) VALUES ('.$topic_id.', '.$CURUSER['id'].', '.TIME_NOW.', '.sqlesc($body).', '.sqlesc($icon).', '.sqlesc($post_title).', '.sqlesc($bb_code).', '.sqlesc($ip).')');
      $post_id = mysql_insert_id();
      $mc1->delete_value('last_posts_'.$CURUSER['class']);
      $mc1->delete_value('forum_posts_'.$CURUSER['id']);
      sql_query('UPDATE topics SET last_post='.$post_id.', post_count = post_count + 1 WHERE id='.$topic_id);
      
      sql_query('UPDATE `forums` SET post_count = post_count +1 WHERE id ='.$arr['real_forum_id']);
	    if($TBDEV['forums_seedbonus_on'] == 1){
 	    sql_query("UPDATE users SET seedbonus = seedbonus+2.0 WHERE id = ".sqlesc($CURUSER['id'])."") or sqlerr(__FILE__, __LINE__);
			}
	    if($TBDEV['forums_autoshout_on'] == 1){ 
      $message = $CURUSER['username'] . " replied to topic [url={$TBDEV['baseurl']}/forums.php?action=view_topic&topic_id=$topic_id&page=last]{$topic_name}[/url]"; 	
 	    //////remember to edit the ids to your staffforum ids :)
 	    if (!in_array($forum_id, array("18","23","24","25"))) {
 	    autoshout($message);
 	    }
			}
	    if ($subscribe == 'yes' && $arr['subscribed_id'] < 1)
      {
      sql_query('INSERT INTO `subscriptions` (`user_id`, `topic_id`) VALUES ('.$CURUSER['id'].', '.$topic_id.')');
      }
      elseif ($subscribe == 'no' && $arr['subscribed_id'] > 0)
      {
      sql_query('DELETE FROM `subscriptions` WHERE `user_id`= '.$CURUSER['id'].' AND  `topic_id` = '.$topic_id);
      }
      
      //=== stuff for file uploads
if ($CURUSER['class'] >= $min_upload_class)
{
	while(list($key,$name) = each($_FILES['attachment']['name']))
	{
		if(!empty($name))
		{ 
		$size =  intval($_FILES['attachment']['size'][$key]); 
		$type =  $_FILES['attachment']['type'][$key]; 
		//=== make sure file is kosher
		$accepted_file_types = array('application/zip', 'application/x-zip','application/rar', 'application/x-rar');
		$extension_error = $size_error = 0; 
		//=== allowed file types (2 checks) but still can't really trust it   
    $the_file_extension = strrpos($name, '.'); 
    $file_extension = strtolower(substr($name, $the_file_extension)); //===  make sure the name is only alphanumeric or _ or -   
    $name = preg_replace('#[^a-zA-Z0-9_-]#', '', $name); // hell, it could even be 0_0 if it wanted to! 
    switch(true)
				{
				case($size > $max_file_size);
				$size_error = ($size_error  + 1);
				break;
				case(!in_array($file_extension, $accepted_file_extension) && $accepted_file_extension == false):
				$extension_error = ($extension_error  + 1);
				break;
				case($accepted_file_extension === 0):
				$extension_error = ($extension_error  + 1);
				break;
				case(!in_array($type, $accepted_file_types)):
				$extension_error = ($extension_error  + 1);
				break;
				default:
			//=== woohoo passed all our silly tests but just to be sure, let's mess it up a bit ;)
			//=== get rid of the file extension
			$name = substr($name, 0, -strlen($file_extension));
			$upload_to  = $upload_folder.$name.'(id-'.$post_id.')'.$file_extension; 
			//===plop it into the DB all safe and snuggly			
			 sql_query('INSERT INTO `attachments` (`post_id`, `user_id`, `file`, `file_name`, `added`, `extension`, `size`) VALUES 
( '.$post_id.', '.$CURUSER['id'].', '.sqlesc($name.'(id-'.$post_id.')'.$file_extension).', '.sqlesc($name).', '.TIME_NOW.', '.($file_extension === '.zip' ? '\'zip\'' : '\'rar\'').', '.$size.')');
				copy($_FILES['attachment']['tmp_name'][$key], $upload_to ); 
				chmod($upload_to, 0777);      
				}
		}
	}	
} //=== end attachment stuff
 
      header('Location: forums.php?action=view_topic&topic_id='.$topic_id.($extension_error === 0 ? '' :  '&ee='.$extension_error).($size_error === 0 ? '' : '&se='.$size_error).'#'.$post_id); 
      die();
      }
 
  $page=0;
	$HTMLOUT .= '<table class="main" width="750px" border="0" cellspacing="0" cellpadding="0">
   	 <tr><td class="embedded" align="center">
	<h1 style="text-align: center;">Reply in topic "<a class="altlink" href="forums.php?action=view_topic&amp;topic_id='.$topic_id.'">'.htmlentities($arr['topic_name'], ENT_QUOTES).'</a>"</h1>
	 '.(isset($_POST['button']) && $_POST['button'] == 'Preview' ? '
	<table width="80%" border="0" cellspacing="5" cellpadding="5" align="center">
	<tr><td class="forum_head" colspan="2"><span style="font-weight: bold;">Preview</span></td></tr>
	<tr><td width="80" valign="top" class="one">'.avatar_stuff($CURUSER).'</td>
	<td valign="top" align="left" class="two">'.($bb_code == 'yes'  ? format_comment($body) : format_comment_no_bbcode($body)).'</td>
	</tr></table><br /><br />' : '').'
	<form method="post" action="forums.php?action=post_reply&amp;topic_id='.$topic_id.'&amp;page='.$page.'" enctype="multipart/form-data">
	<table align="center" width="80%" border="0" cellspacing="0" cellpadding="5">
	<tr><td align="left" class="forum_head_dark" colspan="2">Compose</td></tr>
	<tr><td align="right" class="two"><span style="white-space:nowrap; font-weight: bold;">Post Icon:</span></td>
	<td align="left" class="two">
	<table>
	<tr>
	<td class="two" align="center" valign="middle"><img src="pic/smilies/smile1.gif" alt="Smile" title="Smile" /></td>
	<td class="two" align="center" valign="middle"><img src="pic/smilies/grin.gif" alt="Grin" title="Grin" /></td>
	<td class="two" align="center" valign="middle"><img src="pic/smilies/tongue.gif" alt="Tongue" title="Tongue" /></td>
	<td class="two" align="center" valign="middle"><img src="pic/smilies/cry.gif" alt="Cry" title="Cry" /></td>
	<td class="two" align="center" valign="middle"><img src="pic/smilies/wink.gif" alt="Wink" title="Wink" /></td>
	<td class="two" align="center" valign="middle"><img src="pic/smilies/rolleyes.gif" alt="Roll eyes" title="Roll eyes" /></td>
	<td class="two" align="center" valign="middle"><img src="pic/smilies/blink.gif" alt="Blink" title="Blink" /></td>
	<td class="two" align="center" valign="middle"><img src="pic/smilies/bow.gif" alt="Bow" title="Bow" /></td>
	<td class="two" align="center" valign="middle"><img src="pic/smilies/clap2.gif" alt="Clap" title="Clap" /></td>
	<td class="two" align="center" valign="middle"><img src="pic/smilies/hmmm.gif" alt="Hmm" title="Hmm" /></td>
	<td class="two" align="center" valign="middle"><img src="pic/smilies/devil.gif" alt="Devil" title="Devil" /></td>
	<td class="two" align="center" valign="middle"><img src="pic/smilies/angry.gif" alt="Angry" title="Angry" /></td>
	<td class="two" align="center" valign="middle"><img src="pic/smilies/shit.gif" alt="Shit" title="Shit" /></td>
	<td class="two" align="center" valign="middle"><img src="pic/smilies/sick.gif" alt="Sick" title="Sick" /></td>
	<td class="two" align="center" valign="middle"><img src="pic/smilies/tease.gif" alt="Tease" title="Tease" /></td>
	<td class="two" align="center" valign="middle"><img src="pic/smilies/love.gif" alt="Love" title="Love" /></td>
	<td class="two" align="center" valign="middle"><img src="pic/smilies/ohmy.gif" alt="Oh my" title="Oh my" /></td>
	<td class="two" align="center" valign="middle"><img src="pic/smilies/yikes.gif" alt="Yikes" title="Yikes" /></td>
	<td class="two" align="center" valign="middle"><img src="pic/smilies/spider.gif" alt="Spider" title="Spider" /></td>
	<td class="two" align="center" valign="middle"><img src="pic/smilies/wall.gif" alt="wall" title="Wall" /></td>
	<td class="two" align="center" valign="middle"><img src="pic/smilies/idea.gif" alt="Idea" title="Idea" /></td>
	<td class="two" align="center" valign="middle"><img src="pic/smilies/question.gif" alt="Question" title="Question" /></td>
	</tr>

	<tr>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="smile1"'.($icon == 'smile1' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="grin"'.($icon == 'grin' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="tongue"'.($icon == 'tongue' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="cry"'.($icon == 'cry' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="wink"'.($icon == 'wink' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="rolleyes"'.($icon == 'rolleyes' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="blink"'.($icon == 'blink' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="bow"'.($icon == 'bow' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="clap2"'.($icon == 'clap2' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="hmmm"'.($icon == 'hmmm' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="devil"'.($icon == 'devil' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="angry"'.($icon == 'angry' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="shit"'.($icon == 'shit' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="sick"'.($icon == 'sick' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="tease"'.($icon == 'tease' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="love"'.($icon == 'love' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="ohmy"'.($icon == 'ohmy' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="yikes"'.($icon == 'yikes' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="spider"'.($icon == 'spider' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="wall"'.($icon == 'wall' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="idea"'.($icon == 'idea' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="question"'.($icon == 'question' ? ' checked="checked"' : '').' /></td>
	</tr>
	</table>
	</td></tr>	
	<tr><td align="right" class="two"><span style="white-space:nowrap; font-weight: bold;">Post Title:</span></td>
	<td align="left" class="two"><input type="text" maxlength="120" name="post_title" value="'.$post_title.'" class="text_default" /> [ optional ]</td></tr>
	<tr><td align="right" class="two"><span style="white-space:nowrap; font-weight: bold;">BBcode:</span></td>
	<td align="left" class="two">
	<input type="radio" name="bb_code" value="yes"'.($bb_code == 'yes' ? ' checked="checked"' : '').' /> yes enable BBcode in post 
	<input type="radio" name="bb_code" value="no"'.($bb_code == 'no' ? ' checked="checked"' : '').' /> no disable BBcode in post 
	</td></tr>
		
	<tr><td align="right" valign="top" class="two"><span style="white-space:nowrap; font-weight: bold;">Body:</span></td>
	<td align="left" class="two">'.BBcode($body).$more_options.'
	</td></tr>
	<tr><td align="center" colspan="2" class="two"><img src="pic/forums/subscribe.gif" alt="+" title="+" /> Subscribe to this thread 
	<input type="radio" name="subscribe" value="yes"'.($subscribe == 'yes' ? ' checked="checked"' : '').' />yes 
	<input type="radio" name="subscribe" value="no"'.($subscribe == 'no' ? ' checked="checked"' : '').' />no 
	<input type="submit" name="button" class="button" value="Preview" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" />
	<input type="submit" name="button" class="button_tiny" value="Post" onmouseover="this.className=\'button_tiny_hover\'" onmouseout="this.className=\'button_tiny\'" />
	</td></tr>
	</table></form>';
	
//=== get last ten posts
      $res_posts = sql_query('SELECT p.id AS post_id, p.user_id, p.added, p.body, p.icon, p.post_title, p.bbcode,
				u.id, u.username, u.class, u.donor, u.suspended, u.chatpost, u.leechwarn, u.pirate, u.king, u.warned, u.enabled, u.avatar, u.offensive_avatar, u.avatar_rights 
				FROM posts AS p LEFT JOIN users AS u ON p.user_id = u.id 
				WHERE '.($CURUSER['class'] < UC_MODERATOR ? 'p.status = \'ok\' AND' : 
				($CURUSER['class'] < $min_delete_view_class ? 'p.status != \'deleted\' AND' : '')).' topic_id='.$topic_id.' ORDER BY p.id DESC LIMIT 0, 10');	
				
	$HTMLOUT .= '
	<span style="text-align: center;font-size: large;">last ten posts in reverse order</span>
	<table border="0" cellspacing="5" cellpadding="10" width="90%" align="center">';
		$colour='';
		//=== lets start the loop \o/
		while ($arr = mysql_fetch_assoc($res_posts))
		{
		//=== change colors
		$colour = (++$colour)%2;
		$class = ($colour == 0 ? 'one' : 'two');
		$class_alt = ($colour == 0 ? 'two' : 'one');
						
			$HTMLOUT .='<tr><td class="forum_head" align="left" width="100" valign="middle">#
			<span style="font-weight: bold;">'.htmlspecialchars($arr['username']).'</span></td>
			<td class="forum_head" align="left" valign="middle"><span style="white-space:nowrap;"> posted on: '.get_date($arr['added'],'').' ['.get_date($arr['added'],'',0,1).']</span></td></tr>
			<tr><td class="'.$class_alt.'" align="center" width="100" valign="top">'.avatar_stuff($arr).'<br />
			'.print_user_stuff($arr).'</td>
			<td class="'.$class.'" align="left" valign="top" colspan="2">'.($arr['bbcode'] == 'yes' ? format_comment($arr['body']) : format_comment_no_bbcode($arr['body'])).'</td></tr>';
			
		} //=== end while loop 
		
		$HTMLOUT .='</table></td></tr></table><br /><br />';
     
?>