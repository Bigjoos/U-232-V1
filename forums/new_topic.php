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
	$HTMLOUT .='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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
  $extension_error = $size_error = '';
  $forum_id = (isset($_GET['forum_id']) ? intval($_GET['forum_id']) :  (isset($_POST['forum_id']) ? intval($_POST['forum_id']) :  0));
  if (!is_valid_id($forum_id))
  {
	stderr('Error', 'Bad ID.');
  }
      
	//=== stop suspended users from posting 
	if ($CURUSER['forum_post'] == 'no' || $CURUSER['suspended'] == 'yes')
	{
	stderr('Error', 'Your posting rights have been suspended.');
  }		

  //=== topic stuff
  $topic_name = strip_tags(isset($_POST['topic_name']) ? $_POST['topic_name'] : '');
  $topic_desc = strip_tags(isset($_POST['topic_desc']) ? $_POST['topic_desc'] : '');
	//=== post stuff
  $post_title = strip_tags(isset($_POST['post_title']) ? $_POST['post_title'] : '');
  $icon = htmlspecialchars(isset($_POST['icon']) ? $_POST['icon'] : '');
	$body = (isset($_POST['body']) ? $_POST['body'] : '');
  $ip = htmlspecialchars($CURUSER['ip'] == '' ? $_SERVER['REMOTE_ADDR'] : $CURUSER['ip']); 
  $bb_code = (isset($_POST['bb_code']) && $_POST['bb_code'] == 'no' ? 'no' : 'yes');

	//=== poll stuff
	$poll_question = strip_tags(isset($_POST['poll_question']) ? trim($_POST['poll_question']) : '');
	$poll_answers = strip_tags(isset($_POST['poll_answers']) ? trim($_POST['poll_answers']) : '');
	$poll_ends = (isset($_POST['poll_ends']) ? (($_POST['poll_ends'] > 168) ? 1356048000 : (time() + $_POST['poll_ends'] * 86400)) : ''); 
	$poll_starts = (isset($_POST['poll_starts']) ? (($_POST['poll_starts'] === 0) ? time() : (time() + $_POST['poll_starts'] * 86400)) : ''); 
	
	
	$poll_starts = ($poll_starts > ($poll_ends + 1) ? time() : $poll_starts);
	$change_vote = ((isset($_POST['change_vote']) && $_POST['change_vote'] === 'yes') ? 'yes' : 'no');
	
	$subscribe = (isset($_POST['subscribe']) && $_POST['subscribe'] === 'yes' ? 'yes' : 'no'); 

	if (isset($_POST['button']) && $_POST['button'] == 'Post')
	{
	
	//=== make sure they are posting something
	if($body === '')
	{
	stderr('Error', 'No body text.');
	}
	if($topic_name === '')
	{
	stderr('Error', 'No Topic name!');
	}
	
	//=== if no poll give a dummy id
	$poll_id = 0;
	//=== stuff for polls
	if ($poll_answers !== '')
	{
	//=== make it an array with a max of 20 options
	$break_down_poll_options = explode("\n", $poll_answers); 
	//=== be sure there are no blank options
	for($i = 0; $i < count($break_down_poll_options); $i++){
	if (strlen($break_down_poll_options[$i]) < 2)
	{
	stderr('Error', 'No blank lines in the poll, each option should be on it\'s own line, one line, one option.');
	}
	}
		
		if ($i > 20 || $i < 2)
		{
		stderr('Error', 'There is a minimum of 2 options, and a maximun of 20 options. you have entered '.$i.'.');
		}

	$multi_options = ((isset($_POST['multi_options']) && $_POST['multi_options'] <= $i) ? intval($_POST['multi_options']) : 1);
	
	//=== serialize it and slap it in the DB FFS!
	$poll_options = serialize($break_down_poll_options); 

      sql_query('INSERT INTO `forum_poll` (`user_id` ,`question` ,`poll_answers` ,`number_of_options` ,`poll_starts` ,`poll_ends` ,`change_vote` ,`multi_options`)
					VALUES ('.$CURUSER['id'].', '.sqlesc($poll_question).', '.sqlesc($poll_options).', '.$i.', '.$poll_starts.', '.$poll_ends.', \''.$change_vote.'\', '.$multi_options.')');
	$poll_id = mysql_insert_id();
	}
	  
      sql_query('INSERT INTO topics (`id`, `user_id`, `topic_name`, `forum_id`, `topic_desc`, `poll_id`) VALUES (NULL, '.$CURUSER['id'].', '.sqlesc($topic_name).', '.$forum_id.', '.sqlesc($topic_desc).', '.$poll_id.')');
      $topic_id = mysql_insert_id();
	  
      sql_query('INSERT INTO `posts` ( `topic_id` , `user_id` , `added` , `body` , `icon` , `post_title` , `bbcode` , `ip` ) VALUES 
      		('.$topic_id.', '.$CURUSER['id'].', '.TIME_NOW.', '.sqlesc($body).', '.sqlesc($icon).',  '.sqlesc($post_title).', '.sqlesc($bb_code).',  '.sqlesc($ip).')');
      $post_id = mysql_insert_id();
      $mc1->delete_value('last_posts_'.$CURUSER['class']);
      $mc1->delete_value('forum_posts_'.$CURUSER['id']);
      sql_query('UPDATE `topics` SET last_post = '.$post_id.', first_post =  '.$post_id.', post_count = 1 WHERE id='.$topic_id);
      
      sql_query('UPDATE `forums` SET post_count = post_count +1, topic_count = topic_count + 1 WHERE id ='.$forum_id);
	    
	    if($TBDEV['forums_autoshout_on'] == 1){
	    $message = $CURUSER['username'] . " Created a new topic [url={$TBDEV['baseurl']}/forums.php?action=view_topic&topic_id=$topic_id&page=last]{$topic_name}[/url]";
	    //////remember to edit the ids to your staffforum ids :)
	    if (!in_array($forum_id, array("18","23","24","25"))) {
  	  autoshout($message);
	    }
	    }
      if($TBDEV['forums_seedbonus_on'] == 1){
	    sql_query("UPDATE users SET seedbonus = seedbonus+3.0 WHERE id =  ". sqlesc($CURUSER['id']."")) or sqlerr(__FILE__, __LINE__);
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
		
		  if ($subscribe == 'yes')
      {
       sql_query('INSERT INTO `subscriptions` (`user_id`, `topic_id`) VALUES ('.$CURUSER['id'].', '.$topic_id.')');
     
      }
  
      
	header('Location: forums.php?action=view_topic&topic_id='.$topic_id.($extension_error !== 0 ? '&ee='.$extension_error  : '').($size_error !== 0 ? '&se='.$size_error  : ''));   
      die();
      }

	$res = sql_query('SELECT name FROM forums WHERE id='.$forum_id); 
	$arr = mysql_fetch_assoc($res);
	$section_name = htmlentities($arr['name'], ENT_QUOTES);

	$HTMLOUT .= '<table align="center" class="main" width="750px" border="0" cellspacing="0" cellpadding="0">
    	<tr><td class="embedded" align="center">
    	<h1 style="text-align: center;">New topic in "<a class="altlink" href="forums.php?action=view_forum&amp;forum_id='.$forum_id.'">'.$section_name.'</a>"</h1>
	<form method="post" action="forums.php?action=new_topic&amp;forum_id='.$forum_id.'" enctype="multipart/form-data">
	
	'.(isset($_POST['button']) && $_POST['button'] == 'Preview' ? '<br />
	<table align="center" width="80%" border="0" cellspacing="5" cellpadding="5">
	<tr><td class="forum_head" colspan="2"><span style="color: black; font-weight: bold;">Preview</span></td></tr>
	<tr><td width="80" valign="top" class="one">'.avatar_stuff($CURUSER).'</td>
	<td valign="top" align="left" class="two">'.($bb_code === 'yes'  ? format_comment($body) : format_comment_no_bbcode($body)).'</td>
	</tr></table><br />' : '').'
	<table align="center" width="80%" border="0" cellspacing="0" cellpadding="5">
	<tr><td align="left" class="forum_head_dark" colspan="2">Compose</td></tr>
	<tr><td align="right" class="two"><span style="white-space:nowrap; font-weight: bold;">Topic Icon:</span></td>
	<td align="left" class="two" >
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
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="smile1"'.($icon === 'smile1' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="grin"'.($icon === 'grin' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="tongue"'.($icon === 'tongue' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="cry"'.($icon === 'cry' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="wink"'.($icon === 'wink' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="rolleyes"'.($icon === 'rolleyes' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="blink"'.($icon === 'blink' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="bow"'.($icon === 'bow' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="clap2"'.($icon === 'clap2' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="hmmm"'.($icon === 'hmmm' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="devil"'.($icon === 'devil' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="angry"'.($icon === 'angry' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="shit"'.($icon === 'shit' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="sick"'.($icon === 'sick' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="tease"'.($icon === 'tease' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="love"'.($icon === 'love' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="ohmy"'.($icon === 'ohmy' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="yikes"'.($icon === 'yikes' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="spider"'.($icon === 'spider' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="wall"'.($icon === 'wall' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="idea"'.($icon === 'idea' ? ' checked="checked"' : '').' /></td>
	<td class="two" align="center" valign="middle"><input type="radio" name="icon" value="question"'.($icon === 'question' ? ' checked="checked"' : '').' /></td>
	</tr>
	</table>
	</td></tr>	
	<tr><td align="right" class="two" ><span style="white-space:nowrap; font-weight: bold;">Topic Name:</span></td>
	<td align="left" class="two" ><input type="text" size="80"  name="topic_name" value="'.trim(strip_tags($topic_name)).'" class="text_default" /></td></tr>
	<tr><td align="right" class="two" ><span style="white-space:nowrap; font-weight: bold;">Description:</span></td>
	<td align="left" class="two" ><input type="text" size="80" maxlength="120" name="topic_desc" value="'.trim(strip_tags($topic_desc)).'" class="text_default" /> [ optional ]</td></tr>
	<tr><td align="right" class="two" ><span style="white-space:nowrap; font-weight: bold;">Post Title:</span></td>
	<td align="left" class="two" ><input type="text" size="80" maxlength="120" name="post_title" value="'.trim(strip_tags($post_title)).'" class="text_default" /> [ optional ]</td></tr>
	<tr><td align="right" class="two" ><span style="white-space:nowrap; font-weight: bold;">BBcode:</span></td>
	<td align="left" class="two" >
	<input type="radio" name="bb_code" value="yes"'.($bb_code === 'yes' ? ' checked="checked"' : '').' /> yes enable BBcode in post 
	<input type="radio" name="bb_code" value="no"'.($bb_code === 'no' ? ' checked="checked"' : '').' /> no disable BBcode in post 
	</td></tr>
		
	<tr><td align="right" valign="top" class="two" ><span style="white-space:nowrap; font-weight: bold;">Body:</span></td>
	<td align="left" class="two" >'.BBcode($body).$more_options.'</td></tr>
	<tr><td align="center" colspan="2" class="two" ><img src="pic/forums/subscribe.gif" alt="+" title="+" /> Subscribe to this thread 
	<input type="radio" name="subscribe" value="yes"'.($subscribe === 'yes' ? ' checked="checked"' : '').' />yes 
	<input type="radio" name="subscribe" value="no"'.($subscribe === 'no' ? ' checked="checked"' : '').' />no 
	<input type="submit" name="button" class="button" value="Preview" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" />
	<input type="submit" name="button" class="button_tiny" value="Post" onmouseover="this.className=\'button_tiny_hover\'" onmouseout="this.className=\'button_tiny\'" />
	</td></tr>
	</table></form>
	</td></tr></table><br />';
     
?>