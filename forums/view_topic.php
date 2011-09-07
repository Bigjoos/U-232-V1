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

    if (!is_valid_id($topic_id))
    {
	stderr('Error', 'Bad ID.');
    }
	
	//=== get errors
	$upload_errors_size = (isset($_GET['se']) ? intval($_GET['se']) : 0);
	$upload_errors_type = (isset($_GET['ee']) ? intval($_GET['ee']) : 0);

	$child = '';
	$parent_forum_name = '';
	$parent_forum_id = '';
	$attachments='';
	$colour='';
	$post_id='';
	
//=== Get topic info
    $res = sql_query('SELECT t.id AS topic_id, t.user_id, t.topic_name, t.locked, t.last_post, t.sticky, t.status, t.views, t.poll_id, t.num_ratings, t.rating_sum, t.topic_desc, t.forum_id, 
							f.name AS forum_name, f.min_class_read, f.min_class_write, f.parent_forum 
							FROM topics AS t 
							LEFT JOIN forums AS f ON t.forum_id = f.id 
							WHERE  '.($CURUSER['class'] < UC_MODERATOR ? 't.status = \'ok\' AND' : 
							($CURUSER['class'] < $min_delete_view_class ? ' t.status != \'deleted\'  AND' : '')).' t.id ='.$topic_id);
    $arr = mysql_fetch_assoc($res);
    $status='';
	//=== stop them, they shouldn't be here lol
    if ($CURUSER['class'] < $arr['min_class_read'] || !is_valid_id($arr['topic_id']) || $CURUSER['class'] < $min_delete_view_class && $status == 'deleted' || $CURUSER['class'] < UC_MODERATOR && $status == 'recycled')
    {
	  stderr('Error', 'Bad ID.'); //=== why tell them there is a forum here...
    }
	
	//=== topic status
	$status = $arr['status'];
	
	switch ($status)
	{
		case 'ok':
		$status ='';
		$status_image = '';
		break;
		case 'recycled':
		$status = 'recycled';
		$status_image = '<img src="pic/forums/recycle_bin.gif" alt="Recycled" title="this thread is currently in the recycle-bin" />';
		break;
		case 'deleted':
		$status = 'deleted';
		$status_image = '<img src="pic/forums/delete_icon.gif" alt="Deleted" title="this thread is currently deleted" />';
		break;		
	}

//=== topics stuff
	$forum_id = $arr['forum_id'];
	$topic_owner = $arr['user_id'];
	$topic_name = htmlentities($arr['topic_name'], ENT_QUOTES);
	$topic_desc1 = htmlentities($arr['topic_desc'], ENT_QUOTES);
	$topic_owner = $arr['user_id'];
	
//=== poll stuff
$members_votes = array();
$topic_poll = '';

if ($arr['poll_id'] > 0)
{
	//=== get the poll info
	$res_poll = sql_query('SELECT * FROM forum_poll WHERE id = '.$arr['poll_id']);
	$arr_poll  = mysql_fetch_assoc($res_poll );
	
	//=== get the stuff for just staff
	if ($CURUSER['class'] >= UC_MODERATOR)
	{
	$res_poll_voted = sql_query('SELECT DISTINCT fpv.user_id, fpv.ip, fpv.added, 
											u.id, u.username, u.class, u.donor, u.suspended, u.chatpost, u.leechwarn, u.pirate, u.king, u.warned, u.avatar_rights, u.enabled
											FROM forum_poll_votes AS fpv
											LEFT JOIN users AS u ON u.id = fpv.user_id
											WHERE u.id > 0 AND poll_id = '.$arr['poll_id']);

				$who_voted = (mysql_num_rows($res_poll_voted) > 0 ? '<hr />' : 'no votes yet');
				while ($arr_poll_voted = mysql_fetch_assoc($res_poll_voted))
				{
			 	$who_voted .= print_user_stuff($arr_poll_voted);
			  }
	      }
	
	//=== see if they voted yet
	$res_did_they_vote_yet = sql_query('SELECT `option` FROM `forum_poll_votes` WHERE `poll_id` = '.$arr['poll_id'].' AND `user_id` = '.$CURUSER['id']);
	
	$voted = 0;
	$members_vote = 1000;
	
	if (mysql_num_rows($res_did_they_vote_yet) > 0) 
	{
	$voted = 1;
		while($members_vote = mysql_fetch_assoc($res_did_they_vote_yet))
		{
		//$members_votes[]=$members_vote['option'];
		$members_votes[]=$members_vote;
		} 
	}

	$change_vote = ($arr_poll['change_vote'] === 'no'  ? 0 : 1);
	$poll_open = (($arr_poll['poll_closed'] === 'yes' || $arr_poll['poll_starts'] > time() || $arr_poll['poll_ends'] < time()) ? 0 : 1);
	
	$poll_options = unserialize($arr_poll['poll_answers']); 
	$multi_options = $arr_poll['multi_options'];
	$total_votes = mysql_num_rows($res_did_they_vote_yet);
	
	$res_non_votes = sql_query('SELECT COUNT(id) FROM `forum_poll_votes` WHERE `option` > 20 AND `poll_id` = '.$arr['poll_id']);
	$arr_non_votes  = mysql_fetch_row($res_non_votes);
	$num_non_votes = $arr_non_votes[0];
	$total_non_votes = ($num_non_votes > 0 ? ' [ '.number_format($num_non_votes).' member'.($num_non_votes == 1 ? '' : 's').' just wanted to see the results ]' : '');
	
	//=== if they voted show them the resaults, if not, let them vote
	$topic_poll .= (($voted === 1 || $poll_open === 0) ? '<br /><br />' : '
	<form action="forums.php?action=poll" method="post" name="poll">
	<fieldset class="poll_select">
	<input type="hidden" name="topic_id" value="'.$topic_id.'" />
	<input type="hidden" name="action_2" value="poll_vote" />').'
	<table border="0" cellspacing="5" cellpadding="5" style="max-width:80%;" align="center">
	<tr>
	<td class="forum_head_dark" colspan="2" align="left"><img src="pic/forums/poll.gif" alt="Poll" title="Poll" />  <span style="font-weight: bold;">Poll
	'.($arr_poll['poll_closed'] === 'yes' ? 'closed</span>' : 			
	($arr_poll['poll_starts'] > time() ? 'starts:</span> '.get_date($arr_poll['poll_starts'],'') : 
	($arr_poll['poll_ends'] == 1356048000 ? '</span>'  : ($arr_poll['poll_ends'] > time() ? ' ends:</span> '.get_date($arr_poll['poll_ends'],'',0,1) : '</span>')))).'</td>
	<td class="forum_head_dark" colspan="3" align="right">'.($CURUSER['class'] < UC_MODERATOR ? '' : 
	'<a href="forums.php?action=poll&amp;action_2=poll_edit&amp;topic_id='.$topic_id.'" class="altlink"><img src="pic/forums/modify.gif" alt="modify" title="Modify" width="20px" /> edit</a>  
	<a href="forums.php?action=poll&amp;action_2=poll_reset&amp;topic_id='.$topic_id.'" class="altlink"><img src="pic/forums/stop_watch.png" alt="stop watch" title="Stop Watch" width="20px" /> reset</a> 
	'.(($arr_poll['poll_ends'] > time() || $arr_poll['poll_closed'] === 'no') ? 
	'<a href="forums.php?action=poll&amp;action_2=poll_close&amp;topic_id='.$topic_id.'" class="altlink"><img src="pic/forums/clock.png" alt="clock" title="Clock" width="20px" /> close</a>' :
	'<a href="forums.php?action=poll&amp;action_2=poll_open&amp;topic_id='.$topic_id.'" class="altlink"><img src="pic/forums/clock.png" alt="clock" title="Clock" width="20px" /> start</a>').'
	<a href="forums.php?action=poll&amp;action_2=poll_delete&amp;topic_id='.$topic_id.'" class="altlink"><img src="pic/forums/delete.gif" alt="delete" title="Delete" width="20px" /> delete</a>').'</td>
	</tr>
	<tr>
	<td class="three" width="5px" align="center"><img src="pic/forums/poll_question.png" alt="poll question" title="Poll Question" width="25px" /></td>
	<td class="three" align="left" valign="top" colspan="4"><br />'.format_comment($arr_poll['question']).'<br /><br /></td>
	</tr>
	<tr>
	<td class="three" colspan="5" align="center">
	'.(($voted === 1 || $poll_open === 0) ? '' : '<p>you may select up to <span style="font-weight: bold;">'.$multi_options.' </span>option'.($multi_options == 1 ? '' : 's').'.</p>').'</td>
	</tr>';
		
		$number_of_options = $arr_poll['number_of_options'];
		$math_image = '';
		$math_text = '';
		$colour='';
		//$members_votes=0;
		for($i = 0; $i < $number_of_options; $i++)
		{
		//=== change colors
		$colour= (++$colour)%2;
		$class = ($colour == 0 ? 'two' : 'one');
		//=== if they have voted
		if ($voted === 1)
		{
		//=== do the math for the votes
		$math_res = sql_query('SELECT COUNT(id) FROM `forum_poll_votes` WHERE poll_id = '.$arr['poll_id'].' AND `option` = '.$i);
		$math_row = mysql_fetch_row($math_res);
		$vote_count = $math_row[0];
		$math = (round($vote_count / $total_votes * 100)); 
		$math_text = $math .'% with '.$vote_count.' vote'.($vote_count == 1 ? '' : 's');
		$math_image = '<table border="0" width="200px">
			<tr>
				<td style="padding: 0px; background-image: url(pic/forums/vote_img_bg.gif); background-repeat: repeat-x">
				<img src="pic/forums/vote_img.gif" width="'.$math.'%" height="8" alt="'.$math_text.'" title="'.$math_text.'"  /></td>
			</tr>
				</table>';
		}

		$topic_poll .= '<tr>
			<td class="'.$class.'" width="5px" align="center">'.(($voted === 1 || $poll_open === 0) ? '<span style="font-weight: bold;">'.($i + 1).'.</span>' :
			($multi_options == 1 ? '<input type="radio" name="vote" value="'.$i.'" />' : 
			'<input type="checkbox" name="vote[]" id="vote'.$i.'" value="'.$i.'" />')).'</td>
			<td class="'.$class.'" align="left" valign="middle">'.format_comment($poll_options[$i]).'</td>
			<td class="'.$class.'" align="left">'.$math_image.'</td>
			<td class="'.$class.'" align="center"><span style="white-space:nowrap;">'.$math_text.'</span></td>
			<td class="'.$class.'" align="center">'. (in_array($i, $members_votes) ? '<img src="pic/forums/check.gif" width="20px" alt="check"  title="Check" /> <span style="font-weight: bold;">Your vote!</span>' : '').'</td>
		</tr>';	
		}
		
	$class = ($class == 'one' ? 'two' : 'one');
	$topic_poll .= (($change_vote === 1 && $voted === 1) ? '<tr><td class="three" colspan="5" align="center">
			<a href="forums.php?action=poll&amp;action_2=reset_vote&amp;topic_id='.$topic_id.'" class="altlink"><img src="pic/forums/stop_watch.png" alt="stop watch" title="Stop Watch" width="20px" /> Reset Your Vote!</a> 
			</td>
		</tr>' : '').($voted === 1 ? '<tr>
			<td class="three" colspan="5" align="center">Total votes: '.number_format($total_votes.$total_non_votes).($CURUSER['class'] < UC_MODERATOR ? '' : 
			'<br />
			<a class="altlink"  title="List voters" id="toggle_voters" style="font-weight:bold;cursor:pointer;">List voters</a>
			<div id="voters" style="display:none">'.$who_voted.'</div>').'</td>
	</tr></table><br />' : ($poll_open === 0 ? '' : '<tr>
			<td class="'.$class.'" width="5px" align="center">'.($multi_options == 1 ? '<input type="radio" name="vote" value="666" />' : 
			'<input type="checkbox" name="vote[]" id="vote666" value="666" />').'</td>
			<td class="'.$class.'" align="left" valign="middle" colspan="4"><span style="font-weight: bold;">I just want to see the results!</span></td>
		</tr>')
			.(($voted === 1 || $poll_open === 0) ? '</table><br />' : '<tr><td class="three" colspan="5" align="center">
			<input type="submit" name="button" class="button" value="Vote!" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" /></td>
		</tr></table></fieldset></form>'));

}
		if (isset($_GET['search']))
		{
		$search = htmlspecialchars($_GET['search']);
		$topic_name = highlightWords($topic_name, $search);
		} 

	$forum_desc = ($arr['topic_desc'] !== '' ? '<span style="font-weight: bold;">'.htmlentities($arr['topic_desc'], ENT_QUOTES).'</span><br /><br />' : '');
	$locked = ($arr['locked'] === 'yes' ? 'yes' : 'no');
	$sticky = ($arr['sticky'] === 'yes' ? 'yes' : 'no');
	$views = number_format($arr['views']);

	//=== forums stuff
	$forum_name = htmlentities($arr['forum_name'], ENT_QUOTES);

	//=== staff options
	$staff_tools = '';
	$staff_link = '';
	if ($CURUSER['class'] >= UC_MODERATOR)
	{
	$staff_link = '<a class="altlink"  title="Staff Tools" id="tool_open" style="font-weight:bold;cursor:pointer;">Staff Tools</a>';
	} 
	
   //=== not yet added rate topic \o/
   //if ($arr['num_ratings'] !== 0)
   //$rating =  ROUND($arr['rating_sum'] / $arr['num_ratings'], 1);      

		//=== see if member is subscribed to topic
		$res_subscriptions = sql_query('SELECT id FROM subscriptions WHERE topic_id='.$topic_id.' AND user_id='.$CURUSER['id']);
		$row_subscriptions = mysql_fetch_row($res_subscriptions);
		$subscriptions = ($row_subscriptions[0]  > 0 ? ' <a class="altlink" href="forums.php?action=delete_subscription&amp;topic_id='.$topic_id.'"> 
		<img src="pic/forums/unsubscribe.gif" alt="+" width="12" /> Unsubscribe from this topic</a>' : 
		'<a class="altlink" href="forums.php?action=add_subscription&amp;forum_id='.$forum_id.'&amp;topic_id='.$topic_id.'">
		<img src="pic/forums/subscribe.gif" alt="+" width="12" title="Subscribe" /> Subscribe to this topic</a>');

		//=== who is here 
		sql_query('DELETE FROM now_viewing WHERE user_id ='.$CURUSER['id']);
		sql_query('INSERT INTO now_viewing (user_id, forum_id, topic_id, added) VALUES('.$CURUSER['id'].', '.$forum_id.', '.$topic_id.', '.TIME_NOW.')');
				
	 //=== now_viewing
	$now_viewing_res = sql_query('SELECT n_v.user_id, u.id, u.username, u.suspended, u.class, u.donor, u.warned, u.enabled, u.chatpost, u.leechwarn, u.pirate, u.king 
							FROM now_viewing AS n_v LEFT JOIN users AS u ON n_v.user_id = u.id WHERE topic_id = '.$topic_id);

				//=== let's see whos lookng in here...
				$now_viewing = '';
				while ($now_viewing_arr = mysql_fetch_assoc($now_viewing_res))
				{
					if ($now_viewing !== '')
			  	$now_viewing .= ', ';
			 		$now_viewing .= ' '.print_user_stuff($now_viewing_arr);
			  	}

					if ($now_viewing !== '')
					{
					$now_viewing = 'Currently viewing this topic: '.$now_viewing;
					}
						
	
		//=== Update views column
		sql_query('UPDATE topics SET views = views + 1 WHERE id='.$topic_id);

	//=== must get count for pager... mini query
	$res_count = sql_query('SELECT COUNT(id) AS count FROM posts 
					WHERE '.($CURUSER['class'] < UC_MODERATOR ? 'status = \'ok\' AND' : ($CURUSER['class'] < $min_delete_view_class ? 'status != \'deleted\' AND' : '')).' topic_id='.$topic_id);
	$arr_count = mysql_fetch_row($res_count);
	$count = $arr_count[0];

	  //=== get stuff for the pager
	$page = isset($_GET['page']) ? intval($_GET['page']) : 0;
	$perpage = isset($_GET['perpage']) ? intval($_GET['perpage']) : 20;
	
	$subscription_on_off = (isset($_GET['s'])  ? ($_GET['s'] == 1 ? '<br /><div style="font-weight: bold;">Subscribed to topic <img src="pic/forums/subscribe.gif" alt=" " width="25"></div>' : '<br /><div style="font-weight: bold;">Unsubscribed from topic <img src="pic/forums/unsubscribe.gif" alt=" " width="25"></div>') : '');
  list($menu, $LIMIT) = pager_new($count, $perpage, $page, 'forums.php?action=view_topic&amp;topic_id='.$topic_id.(isset($_GET['perpage']) ? '&amp;perpage='.$perpage : ''));  

	$res = sql_query('SELECT p.id AS post_id, p.topic_id, p.user_id, p.added, p.body, p.edited_by, p.edit_date, p.icon, p.post_title, p.bbcode, p.post_history, p.edit_reason, p.ip, p.status AS post_status,
				u.seedbonus, u.id, u.username, u.class, u.donor, u.chatpost, u.leechwarn, u.pirate, u.king, u.warned, u.enabled, u.email, u.google_talk, u.website, u.icq, u.msn, u.aim, u.yahoo, u.last_access, u.show_email, 
				u.paranoia, u.hit_and_run_total, u.avatar, u.suspended, u.offensive_avatar, u.avatar_rights, u.title, u.uploaded, u.downloaded, u.signature 
				FROM posts AS p LEFT JOIN users AS u ON p.user_id = u.id WHERE 
				'.($CURUSER['class'] < UC_MODERATOR ? 'p.status = \'ok\' AND' : ($CURUSER['class'] < $min_delete_view_class ? 'p.status != \'deleted\' AND' : '')).' topic_id='.$topic_id.'  
				ORDER BY p.id ASC '.$LIMIT);
				
	//=== make sure they can reply here
	$may_post = ($CURUSER['class'] >= $arr['min_class_write'] && $CURUSER['forum_post'] == 'yes' && $CURUSER['suspended'] == 'no');
	
	//=== reply button
	$locked_or_reply_button = ($locked === 'yes' ? '<span style="font-weight: bold; font-size: x-small;"><img src="pic/forums/thread_locked.gif" alt="locked" title ="Locked" width="22" />  
						This topic is locked, you may not post in this thread.</span>' : 
						($CURUSER['forum_post'] == 'no' ? '<span style="font-weight: bold; font-size: x-small;">Your posting rights have been removed. You may not post.</span>' :
						'<a href="forums.php?action=post_reply&amp;topic_id='.$topic_id.'">
						<input type="submit" class="button" value="Add Reply" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" /></a>'));


	if ($arr['parent_forum'] > 0)
	{
		//=== now we need the parent forums stuff
		$parent_forum_res = sql_query('SELECT name AS parent_forum_name FROM forums WHERE id='.$arr['parent_forum']);
		$parent_forum_arr = mysql_fetch_row($parent_forum_res);
		
		$child = ($arr['parent_forum'] > 0 ? '<span style="font-size: x-small;"> [ child-board ]</span>' : '');
		$parent_forum_name = '<img src="pic/arrow_next.gif" alt="&#9658;" title="&#9658;" /> 
		<a class="altlink" href="forums.php?action=view_forum&amp;forum_id='.$parent_forum_id.'">'.htmlentities($parent_forum_arr[0], ENT_QUOTES).'</a>';
	}

	//=== top and bottom stuff
	$the_top_and_bottom =  '<tr><td class="three" width="33%" align="left" valign="middle">&nbsp;&nbsp;'.$subscriptions.'</td>
		<td class="three" width="33%" align="center">'.(($count > $perpage) ? $menu : '').'</td>
		<td class="three" align="right">'. ($may_post ? $locked_or_reply_button : 
		'<span style="font-weight: bold; font-size: x-small;">
		You are not permitted to post in this thread.</span>').'</td></tr>';

$location_bar = '<a name="top"></a><span style="font-weight: bold; font-size: large;">'.$status_image.' <a class="altlink" href="index.php">'.$TBDEV['site_name'].'</a>  <img src="pic/arrow_next.gif" alt="&#9658;"  title="&#9658;" /> 
			<a class="altlink" href="forums.php">Forums</a> '.$parent_forum_name.' 
			<img src="pic/arrow_next.gif" alt="&#9658;" /> 
			<a class="altlink" href="forums.php?action=view_forum&amp;forum_id='.$forum_id.'">'.$forum_name.$child.'</a>
			<img src="pic/arrow_next.gif" alt="&#9658;" /> 
			<a class="altlink" href="forums.php?action=view_topic&amp;topic_id='.$topic_id.'">'.$topic_name.'</a> '.$status_image.'</span><br />'.$forum_desc.'
			<span style="text-align: center;">'.$mini_menu.(($topic_owner == $CURUSER['id'] && $arr['poll_id'] == 0 || $CURUSER['class'] >= UC_MODERATOR && $arr['poll_id'] == 0) ? 
			'  | <a href="forums.php?action=poll&amp;action_2=poll_add&amp;topic_id='.$topic_id.'" class="altlink">Add Poll</a> ' : '').'</span><br /><br />';
			
	$HTMLOUT .= ($upload_errors_size > 0 ? ($upload_errors_size === 1 ? '<div style="text-align: center;">One file was not uploaded. The maximum file size allowed is. '.mksize($max_file_size).'.</div>' :
		'<div style="text-align: center;">'.$upload_errors_size.' file were not uploaded. The maximum file size allowed is. '.mksize($max_file_size).'.</div>') : '').
		($upload_errors_type > 0 ? ($upload_errors_type === 1 ? '<div style="text-align: center;">One file was not uploaded. The accepted formats are zip and rar.</div>' :
		'<div style="text-align: center;">'.$upload_errors_type.' files were not uploaded. The accepted formats are zip and rar.</div>') : '').	$location_bar.$topic_poll.'<br />'.$subscription_on_off .'<br />
		'.($CURUSER['class'] < UC_MODERATOR ? '' : '<form action="forums.php?action=staff_actions" method="post" name="checkme" onsubmit="return ValidateForm(this,\'post_to_mess_with\')" enctype="multipart/form-data">').(isset($_GET['count']) ? '
		<div style="text-align: center;">'.intval($_GET['count']).' PMs Sent</div>'  : '').'
		<table border="0" cellspacing="5" cellpadding="10" width="90%">
		'.$the_top_and_bottom.'
		<tr><td class="forum_head_dark" align="left" width="100"> <img src="pic/forums/topic_normal.gif" alt="normal" title="Topic Normal" />&nbsp;&nbsp;Author</td>
		<td class="forum_head_dark" align="left" colspan="2">&nbsp;&nbsp;Topic: '.$topic_name.'  [ Read '.$views.' times ] </td></tr>
		<tr><td class="three" align="left" colspan="3">'.$now_viewing.'</td></tr>';
		
		
		
		//=== lets start the loop \o/
		while ($arr = mysql_fetch_assoc($res))
		{
		//=== change colors
		$colour = (++$colour)%2;
		$class = ($colour == 0 ? 'one' : 'two');
		$class_alt = ($colour == 0 ? 'two' : 'one');
		$post_icon = ($arr['icon'] !== '' ? '<img src="pic/smilies/'.htmlspecialchars($arr['icon']).'.gif" alt="icon" /> ' : '<img src="pic/forums/topic_normal.gif" alt="icon" title="icon" /> ');
		$post_title = ($arr['post_title'] !== '' ? ' <span style="font-weight: bold; font-size: x-small;">'.htmlentities($arr['post_title'], ENT_QUOTES).'</span>' : '');
		
		$edited_by = '';
		if ($arr['edit_date'] > 0)
		{
		$res_edited = sql_query('SELECT username FROM users WHERE id='.$arr['edited_by']);
		$arr_edited = mysql_fetch_assoc($res_edited);
		
		$edited_by = '<br /><br /><br /><span style="font-weight: bold; font-size: x-small;">Last edited by <a class="altlink" href="member_details.php?id='.$arr['edited_by'].'">'.$arr_edited['username'].'</a>
				 at '.get_date($arr['edit_date'],'').' GMT '.($arr['edit_reason'] !== '' ? ' [ Reason: '.htmlspecialchars($arr['edit_reason']).' ]</span><span style="font-weight: bold; font-size: x-small;">' : '').'
				 '.(($CURUSER['class'] >= UC_MODERATOR && $arr['post_history'] !== '') ? 
				 ' <a class="altlink" href="forums.php?action=view_post_history&amp;post_id='.$arr['post_id'].'&amp;forum_id='.$forum_id.'&amp;topic_id='.$topic_id.'">read post history</a></span><br />' : '');
		}
		
	
		//==== highlight for search
		$body = ($arr['bbcode'] == 'yes' ? format_comment($arr['body']) : format_comment_no_bbcode($arr['body']));
		if (isset($_GET['search']))
		{
		$body = highlightWords($body, $search);
		$post_title = highlightWords($post_title, $search);
		} 
		
			$post_id = $arr['post_id'];
			
			//=== if there are attachments, let's get them!
			$attachments_res = sql_query('SELECT id, file_name, extension, size FROM attachments WHERE post_id ='.$post_id.' AND user_id = '.$arr['id']);
			if (mysql_num_rows($attachments_res) > 0)
			{
				$attachments = '<table align="center" width="100%" border="0" cellspacing="0" cellpadding="5"><tr>
										<td class="'.$class.'" align="left"><span style="font-weight: bold;">Attachments:</span><hr />';
					while ($attachments_arr = mysql_fetch_assoc($attachments_res))
					{
					$attachments .= '<span style="white-space:nowrap;">'.($attachments_arr['extension'] === 'zip' ? ' <img src="pic/forums/zip.gif" alt="zip" title="Zip" width="18" style="vertical-align: middle;" /> ' :
					' <img src="pic/forums/rar.gif" alt="rar" title="Rar" width="18" /> ').' 
					<a class="altlink" href="forums.php?action=download_attachment&amp;id='.$attachments_arr['id'].'" title="Download Attachment" target="_blank">
					'.htmlspecialchars($attachments_arr['file_name']).'</a> <span style="font-weight: bold; font-size: xx-small;">['.mksize($attachments_arr['size']).']</span>&nbsp;&nbsp;</span>';
					}
				$attachments .= '</td></tr></table>';
			}
			
			//=== signature stuff
			$signature = ($CURUSER['signatures'] == 'no' ? '' : ($arr['signature'] == '' ? '' : 
							'<table align="center" width="100%" border="0" cellspacing="0" cellpadding="5"><tr><td class="'.$class.'" align="left"><hr />'.format_comment($arr['signature']).'</td></tr></table>'));
							
	//=== post status
	$post_status = $arr['post_status'];
	
	switch ($post_status)
	{
		case 'ok':
		$post_status = $class;
		break;
		case 'recycled':
		$post_status = 'recycled';
		break;
		case 'deleted':
		$post_status = 'deleted';
		break;		
	}
		
			
			$HTMLOUT .='<tr><td class="'.$class.'" align="left" valign="top" colspan="3"><table border="0" cellspacing="5" cellpadding="10" width="100%"><tr><td class="forum_head" align="left" width="100" valign="middle">
			<span style="white-space:nowrap;"><a name="'.$post_id.'"></a>
			'.($CURUSER['class'] >= UC_MODERATOR ? '<input type="checkbox" name="post_to_mess_with[]" value="'.$post_id.'" />' : '').'
			<a href="javascript:window.alert(\'Direct link to this post:\n '.$TBDEV['baseurl'].'/forums.php?action=view_topic&amp;topic_id='.$topic_id.'&amp;page='.$page.'#'.$post_id.'\');">
			<img src="pic/forums/link.gif" alt="Direct link to this post" title="Direct link to this post" width="12px" /></a>
			<span style="font-weight: bold;">'.htmlspecialchars($arr['username']).'</span>
			'.(($arr['paranoia'] >= 2 && $CURUSER['class'] < UC_MODERATOR) ? '<img src="pic/smilies/tinfoilhat.gif" alt="I wear a tin-foil hat!" title="I wear a tin-foil hat!" />' : 
			get_user_ratio_image(($arr['downloaded'] ? $arr['uploaded'] / $arr['downloaded'] : 0), 0)).'</span></td>
			<td class="forum_head" align="left" valign="middle"><span style="white-space:nowrap;">'.$post_icon.$post_title.'&nbsp;&nbsp;&nbsp;&nbsp; posted on: '.get_date($arr['added'],'').' ['.get_date($arr['added'],'',0,1).']</span></td>
			<td class="forum_head" align="right" valign="middle"><span style="white-space:nowrap;"> 
			<a class="altlink" href="forums.php?action=post_reply&amp;topic_id='.$topic_id.'&amp;quote_post='.$post_id.'&amp;key='.$arr['added'].'"><img src="pic/forums/quote.gif" alt="::" /> Quote</a>
			'.(($CURUSER['class'] >= UC_MODERATOR || $CURUSER['id'] == $arr['id']) ? ' <a class="altlink" href="forums.php?action=edit_post&amp;post_id='.$post_id.'&amp;topic_id='.$topic_id.'&amp;page='.$page.'"><img src="pic/forums/modify.gif" alt="::" /> Modify</a> 
			 <a class="altlink" href="forums.php?action=delete_post&amp;post_id='.$post_id.'&amp;topic_id='.$topic_id.'"><img src="pic/forums/delete.gif" alt="::" title="Delete" /> Remove</a>' : '').'
			 <!--<a class="altlink" href="forums.php?action=report_post&amp;topic_id='.$topic_id.'&amp;post_id='.$post_id.'"><img src="pic/forums/report.gif" alt="Report" title="Report" width="22" /> Report</a>-->
			 <a href="'.$INSTALLER09['baseurl'].'/report.php?type=Post&amp;id='.$post_id.'&amp;id_2='.$topic_id.'"><img src="pic/forums/report.gif" alt="Report" title="Report" width="22" /> Report</a>
			 <a href="forums.php?action=view_topic&amp;topic_id='.$topic_id.'&amp;page='.$page.'#top"><img src="pic/forums/up.gif" alt="top" title="Top" /></a> 
			 <a href="forums.php?action=view_topic&amp;topic_id='.$topic_id.'&amp;page='.$page.'#bottom"><img src="pic/forums/down.gif" alt="bottom" title="Bottom" /></a> 
			</span></td>
			</tr>	
			
			<tr><td class="'.$class_alt.'" align="center" valign="top">'.avatar_stuff($arr).'<br />
			'.print_user_stuff($arr).($arr['title'] == '' ? '' : '<br /><span style=" font-size: xx-small;">['.htmlspecialchars($arr['title']).']</span>').'<br />
			<span style="font-weight: bold;">'.get_user_class_name($arr['class']).'</span><br />
			'.($arr['last_access'] > (TIME_NOW - 300) ? ' <img src="pic/online.gif" alt="::" /> Online' : ' <img src="pic/offline.gif" border="0" alt="::" /> Offline').'<br />
			Karma: '.number_format($arr['seedbonus']).'<br /><br />
			'.
			($arr['google_talk'] !== '' ? ' <a href="http://talkgadget.google.com/talkgadget/popout?member='.htmlspecialchars($arr['google_talk']).'" target="_blank"><img src="pic/forums/google_talk.gif" alt="google_talk" title="click for google talk gadget"  /></a> ' : '').
			($arr['icq'] !== '' ? ' <a href="http://people.icq.com/people/&amp;uin='.htmlspecialchars($arr['icq']).'" title="click to open icq page" target="_blank"><img src="pic/forums/icq.gif" alt="icq" title="icq" /></a> ' : '').
			($arr['msn'] !== '' ? ' <a href="http://members.msn.com/'.htmlspecialchars($arr['msn']).'" target="_blank" title="click to see msn details"><img src="pic/forums/msn.gif" alt="msn" title="msn" /></a> ' : '').
			($arr['aim'] !== '' ? ' <a href="http://aim.search.aol.com/aol/search?s_it=searchbox.webhome&amp;q='.htmlspecialchars($arr['aim']).'" target="_blank" ><img src="pic/forums/aim.gif" alt="AIM" title="click to search on aim... you will need to have an AIM account!" /></a> ' : '').
			($arr['yahoo'] !== '' ? ' <a href="http://webmessenger.yahoo.com/?im='.htmlspecialchars($arr['yahoo']).'" target="_blank" title="click to open yahoo"><img src="pic/forums/yahoo.gif" alt="yahoo" title="yahoo"/></a> ' : '').'<br /><br />'.
			($arr['website'] !== '' ? ' <a href="'.htmlspecialchars($arr['website']).'" target="_blank" title="click to go to website"><img src="pic/forums/website.gif" alt="website" title="website" /></a> ' : '').
			($arr['show_email'] == 'yes' ? ' <a href="mailto:'.htmlspecialchars($arr['email']).'"  title="click to email" target="_blank"><img src="pic/email.gif" alt="email" title="email" width="25" /> </a>' : '').'<br /><br />
			'.($CURUSER['class'] >= UC_MODERATOR ? '   
			<ul class="makeMenu">
				<li>'.htmlspecialchars($arr['ip']).'
					<ul>
					<li><a href="https://ws.arin.net/whois/?queryinput='.htmlspecialchars($arr['ip']).'" title="whois to find ISP info" target="_blank">IP whois</a></li>
					<li><a href="http://www.infosniper.net/index.php?ip_address='.htmlspecialchars($arr['ip']).'" title="IP to map using InfoSniper!" target="_blank">IP to Map</a></li>
				</ul>
				</li>
			</ul>' : '').'
			</td>
			<td class="'.$post_status.'" align="left" valign="top" colspan="2">'.$body.$edited_by.'</td></tr>
			
			<tr><td class="'.$class_alt.'" width="100"></td><td class="'.$class.'" align="left" valign="top" colspan="2">'.$signature.'</td></tr>
			<tr><td class="'.$class_alt.'" width="100"></td><td class="'.$class.'" align="left" valign="top" colspan="2">'.$attachments.'</td></tr>
			
			<tr><td class="'.$class_alt.'" align="right" valign="middle" colspan="3">'.(($arr['paranoia'] >= 1 && $CURUSER['class'] < UC_MODERATOR) ? '' : '
			<span style="color: green;"><img src="pic/up.png" alt="uploaded" title="uploaded" /> '.mksize($arr['uploaded']).'</span>&nbsp;&nbsp;  
			<span style="color: red;"><img src="pic/dl.png" alt="downloaded" title="downloaded" /> '.mksize($arr['downloaded']).'</span>&nbsp;&nbsp;').
			(($arr['paranoia'] >= 2 && $CURUSER['class'] < UC_MODERATOR) ? '' : 'Ratio: '.member_ratio($arr['uploaded'], $arr['downloaded']).'&nbsp;&nbsp;
			'.($arr['hit_and_run_total'] == 0 ? '<img src="pic/no_hit_and_runs2.gif" width="22" alt="'.htmlspecialchars($arr['username']).' has never hit & ran!" title="'.htmlspecialchars($arr['username']).' has never hit & ran!" />' : '').'
			&nbsp;&nbsp;&nbsp;&nbsp;').'
			<a class="altlink" href="sendmessage.php?receiver='.$arr['id'].'"><img src="pic/forums/send_pm.png" alt="pm" title="Send Pm" width="18" />Send Message</a></td></tr></table></td></tr>';
		$attachments='';
		} //=== end while loop 
    
		//=== update the last post read by CURUSER 
		sql_query('DELETE FROM `read_posts` WHERE user_id ='.$CURUSER['id'].' AND `topic_id` = '.$topic_id);
		sql_query('INSERT INTO `read_posts` (`user_id` ,`topic_id` ,`last_post_read`) VALUES ('.$CURUSER['id'].', '.$topic_id.', '.$post_id.')');
				
				
	//=== set up jquery show hide here 
	$HTMLOUT .= $the_top_and_bottom.'</table>
	
	<span style="text-align: center;">'.$location_bar.'</span><a name="bottom"></a>
	<br />
	'.($CURUSER['class'] >= UC_MODERATOR ? '<img src="pic/forums/tools.png" alt="tools" title="Staff Tools" width="22" /> '.$staff_link.' <img src="pic/forums/tools.png" alt=" " width="22" /><br /><br />

	<div id="tools" style="display:none">
    	<br />
    	
    	<table border="0" cellspacing="5" cellpadding="5" width="800" align="center">
		<tr>
	  <td class="forum_head_dark" colspan="4" align="center">Staff Tools</td>
		</tr>
		<tr>
			<td class="two" align="left" colspan="3">
			<input type="hidden" name="topic_id" value="'.$topic_id.'" />
			<input type="hidden" name="forum_id" value="'.$forum_id.'" />
      <table border="0" cellspacing="2" cellpadding="2" width="100%" align="center">
		<tr>
			<td class="two" align="center" valign="middle" width="18"><img src="pic/forums/recycle_bin.gif" alt="recycle" title="Recycle" width="22" /></td>
			<td class="two" align="left" valign="middle">
				<input type="radio" name="action_2" value="send_to_recycle_bin" />Send to Recycle Bin  <br />
				<input type="radio" name="action_2" value="remove_from_recycle_bin" />Remove from Recycle Bin 
			</td>
			<td class="two" align="center" valign="middle" width="18"><img src="pic/forums/delete.gif" alt="delete" title="Delete" /></td>
			<td class="two" align="left" valign="middle">
				<input type="radio" name="action_2" value="delete_posts" />Delete
			'. ($CURUSER['class'] < $min_delete_view_class ? '' : '<br /><input type="radio" name="action_2" value="un_delete_posts" /><span style="font-weight:bold;color:red;">*</span>Un-Delete').'
			</td>
			<td class="two" align="center" valign="middle" width="18"><img src="pic/forums/merge.gif" alt="merge" title="Merge" /></td>
			<td class="two" align="left" valign="middle">
			<input type="radio" name="action_2" value="merge_posts" />Merge With<br />
	<input type="radio" name="action_2" value="append_posts" />Append To
	</td>
	<td class="two" align="left" valign="middle">
	Topic:<input type="text" size="2" name="new_topic" value="'.$topic_id.'" /></td>
	</tr>
  </table>
  <table border="0" cellspacing="2" cellpadding="2" width="100%" align="center">
  <tr>
	<td class="two" align="center" valign="middle" width="18"><img src="pic/forums/split.gif" alt="split" title="Split" width="18" /></td>
	<td class="two" align="left" valign="middle">
	<input type="radio" name="action_2" value="split_topic" />Split Topic
	</td>
	<td class="two" align="left" valign="middle">
	New Topic Name:<input type="text" size="20" maxlength="120" name="new_topic_name" value="'.($topic_name !== '' ? $topic_name : '').'" /> [required]<br />
	New Topic Desc:<input type="text" size="20" maxlength="120" name="new_topic_desc" value="" />
	</td>
	<td class="two" align="center" valign="middle" width="18"><img src="pic/forums/send_pm.png" alt="pm" title="Pm" width="18" /></td>
	<td class="two" align="center" valign="middle">
	<a class="altlink"  title="Send PM to Selected Members - click" id="pm_open" style="font-weight:bold;cursor:pointer;">Send PM </a><br />[click]
 </td>
 </tr>
 </table>
      <div id="pm" style="display:none"><br />
      <table border="0" cellspacing="2" cellpadding="2" width="100%" align="center">
	    <tr>
	    <td class="forum_head_dark" align="left" colspan="2">Send Pm to Selected Members</td>
	    </tr>
	    <tr>
			<td class="three" align="right" valign="top">
			<span style="font-weight: bold;">Subject:</span>
			</td>
			<td class="three" align="left" valign="top">
			<input type="text" size="20" maxlength="120" class="text_default" name="subject" value="" />
			<input type="radio" name="action_2" value="send_pm" /> <span style="font-weight: bold;">Select to send.</span> 
			</td>
		  </tr>
		  <tr>
			<td class="three" align="right" valign="top">
			<span style="font-weight: bold;">Message:</span>
			</td>
			<td class="three" align="left" valign="top">
			<textarea cols="30" rows="4" name="message" class="text_area_small"></textarea>
			</td>
		   </tr>
		   <tr>
			<td class="three" align="right" valign="top">
			<span style="font-weight: bold;">From:</span>
			</td>
			<td class="three" align="left" valign="top">
			<input type="radio" name="pm_from" value="0" /> System  
			<input type="radio" name="pm_from" value="1" /> '.print_user_stuff($CURUSER).'
			</td>
		  </tr>
	   </table></div><hr /></td>
    	<td class="two" align="center">
			<a class="altlink" href="javascript:SetChecked(1,\'post_to_mess_with[]\')" title="Select all posts and use the following options"> Select All</a> <br />
			<a class="altlink" href="javascript:SetChecked(0,\'post_to_mess_with[]\')" title="Un-select all posts">Un-Select All</a><br />
			<input type="submit" name="button" class="button" value="With Selected" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" />
			</form>
			</td>
		  </tr>    
		  <tr>
			<td class="two" align="center" width="28" valign="top"><img src="pic/forums/pinned.gif" alt="pinned" title="Pinned" /></td>
			<td class="two" align="right" valign="top"><span style="font-weight: bold;white-space:nowrap;">Pin Topic:</span></td>
			<td class="two" align="left" valign="top">
			<form action="forums.php?action=staff_actions" method="post">
			<input type="hidden" name="action_2" value="set_pinned" />
			<input type="hidden" name="topic_id" value="'.$topic_id.'" />
			<input type="radio" name="pinned" value="yes" '.($sticky === 'yes' ? 'checked="checked"' : '').' /> Yes  
			<input type="radio" name="pinned" value="no" '.($sticky === 'no' ? 'checked="checked"' : '').' /> No
		  </td>
			<td class="two" align="center">
			<input type="submit" name="button" class="button" value="Set Pinned" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" />
		  </td>
		  </form>
		  </tr>	  
		  <tr>
			<td class="two" align="center" width="28" valign="top"><img src="pic/forums/thread_locked.gif" alt="locked" title="Locked" width="22" /></td>
			<td class="two" align="right" valign="top"><span style="font-weight: bold;white-space:nowrap;">Lock Topic:</span></td>
			<td class="two" align="left" valign="top">
			<form action="forums.php?action=staff_actions" method="post" name="locked">
			<input type="hidden" name="action_2" value="set_locked" />
			<input type="hidden" name="topic_id" value="'.$topic_id.'" />
			<input type="radio" name="locked" value="yes" '.($locked === 'yes' ? 'checked="checked"' : '').' />Yes  
			<input type="radio" name="locked" value="no" '.($locked === 'no' ? 'checked="checked"' : '').' /> No
			</td>
			<td class="two" align="center">
			<input type="submit" name="button" class="button" value="Lock Topic" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" />
			</td>	
		  </form>
		  </tr>
		  <tr>
			<td class="two" align="center" width="28" valign="top"><img src="pic/forums/move.gif" alt="move" title="Move" width="22" /></td>
			<td class="two" align="right" valign="top"><span style="font-weight: bold;white-space:nowrap;">Move Topic:</span></td>
			<td class="two" align="left" valign="top">
			<form action="forums.php?action=staff_actions" method="post">
			<input type="hidden" name="action_2" value="move_topic" />
			<input type="hidden" name="topic_id" value="'.$topic_id.'" />
			<select name="forum_id">
			'.insert_quick_jump_menu($forum_id, $staff = true).'</select>
		
			</td>
			<td class="two" align="center">
			<input type="submit" name="button" class="button" value="Move Topic" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" />
			</td>	
		  </form>
		  </tr>	
		  <tr>
			<td class="two" align="center" width="28" valign="top"><img src="pic/forums/modify.gif" alt="modify" title="Modify" /></td>
			<td class="two" align="right" valign="top"><span style="font-weight: bold;white-space:nowrap;">Rename Topic:</span></td>
			<td class="two" align="left" valign="top">
			<form action="forums.php?action=staff_actions" method="post">
			<input type="hidden" name="action_2" value="rename_topic" />
			<input type="hidden" name="topic_id" value="'.$topic_id.'" />
			<input type="text" size="40" maxlength="120" name="new_topic_name" value="'.($topic_name !== '' ? $topic_name : '').'" />
			</td>
			<td class="two" align="center">
			<input type="submit" name="button" class="button" value="Rename Topic" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" />
			</td>
		  </form>
		  </tr>	
		  <tr>
			<td class="two" align="center" width="28" valign="top"><img src="pic/forums/modify.gif" alt="modify" title="Modify" /></td>
			<td class="two" align="right" valign="top"><span style="font-weight: bold;white-space:nowrap;">Change Topic Desc:</span></td>
			<td class="two" align="left" valign="top">
			<form action="forums.php?action=staff_actions" method="post">
			<input type="hidden" name="action_2" value="change_topic_desc" />
			<input type="hidden" name="topic_id" value="'.$topic_id.'" />
			<input type="text" size="40" maxlength="120" name="new_topic_desc" value="'.($topic_desc1 !== '' ? $topic_desc1 : '').'" />	
			</td>
			<td class="two" align="center">
			<input type="submit" name="button" class="button" value="Change Desc" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" />
			</td>			
		  </form>
		  </tr>	
		  <tr>
			<td class="two" align="center" width="28" valign="top"><img src="pic/forums/merge.gif" alt="merge" title="Merge" /></td>
			<td class="two" align="right" valign="top"><span style="font-weight: bold;white-space:nowrap;">Merge Topic:</span></td>
			<td class="two" align="left" valign="top">With topic # 
			<form action="forums.php?action=staff_actions" method="post">
			<input type="hidden" name="action_2" value="merge_topic" />
			<input type="hidden" name="topic_id" value="'.$topic_id.'" />
			<input type="text" size="4" name="topic_to_merge_with" value="'.$topic_id.'" /><br />
			Enter the destination  Topic Id to merge into<br />
			Topic ID can be found in the address bar above... the topic id for this thread is: '.$topic_id.'<br />
			[This option will mix the two topics together, keeping dates and post numbers preserved.]	
			</td>
			<td class="two" align="center">
			<input type="submit" name="button" class="button" value="Merge Topic" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" />
			</td>
		  </form>
		  </tr>	
		  <tr>
			<td class="two" align="center" width="28" valign="top"><img src="pic/forums/merge.gif" alt="merge" title="Merge" /></td>
			<td class="two" align="right" valign="top"><span style="font-weight: bold;white-space:nowrap;">Append Topic:</span></td>
			<td class="two" align="left" valign="top">With topic # 
			<form action="forums.php?action=staff_actions" method="post">
			<input type="hidden" name="action_2" value="append_topic" />
			<input type="hidden" name="topic_id" value="'.$topic_id.'" />
			<input type="text" size="4" name="topic_to_append_into" value="'.$topic_id.'" /><br />
			Enter the destination  Topic Id to append to.<br />
			Topic ID can be found in the address bar above... the topic id for this thread is: '.$topic_id.'<br />
			[This option will append this topic to the end of the new topic. The dates will be preserved, but the posts will be added after the last post in the appended to thread.]
			</td>
			<td class="two" align="center">
			<input type="submit" name="button" class="button" value="Append Topic" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" />			
			</td>		
		  </form>
		  </tr>	
		  <tr>
			<td class="two" align="center" width="28" valign="top"><img src="pic/forums/recycle_bin.gif" alt="recycle" title="Recycle" width="22" /></td>
			<td class="two" align="right" valign="top"><span style="font-weight: bold;white-space:nowrap;">Move to Recycle Bin:</span></td>
			<td class="two" align="left" valign="top">
			<form action="forums.php?action=staff_actions" method="post">
			<input type="hidden" name="action_2" value="move_to_recycle_bin" />
			<input type="hidden" name="topic_id" value="'.$topic_id.'" />
			<input type="hidden" name="forum_id" value="'.$forum_id.'" />
			<input type="radio" name="status" value="yes" '.($status === 'recycled' ? 'checked="checked"' : '').' /> Yes  
			<input type="radio" name="status" value="no" '.($status !== 'recycled' ? 'checked="checked"' : '').' /> No<br />
			This option will send this thread to the hidden recycle bin for other staff to view it.<br />
			All subscriptions to this thread will be deleted!
			</td>
			<td class="two" align="center">
			<input type="submit" name="button" class="button" value="Recycle It" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" />
			</td>			
		  </form>
		  </tr>	
		  <tr>
			<td class="two" align="center" width="28"><img src="pic/forums/delete.gif" alt="delete" title="Delete" /></td>
			<td class="two" align="right"><span style="font-weight: bold;white-space:nowrap;">Delete Topic:</span></td>
			<td class="two" align="left">Are you really sure you want to delete this topic, and not just move it or merge it?</td>
			<td class="two" align="center">
			<form action="forums.php?action=staff_actions" method="post">
			<input type="hidden" name="action_2" value="delete_topic" />
			<input type="hidden" name="topic_id" value="'.$topic_id.'" />
			<input type="submit" name="button" class="button" value="Delete Topic" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" />
			</form>
			</td>
		  </tr>			
			'.($CURUSER['class'] < $min_delete_view_class ? '' : 	'
		  <tr>
			<td class="two" align="center" width="28"><img src="pic/forums/delete.gif" alt="delete" title="Delete" /></td>
			<td class="two" align="right"><span style="font-weight: bold;white-space:nowrap;"><span style="font-weight:bold;color:red;">*</span>Un-Delete Topic:</span></td>
			<td class="two" align="left"></td>
			<td class="two" align="center">
			<form action="forums.php?action=staff_actions" method="post">
			<input type="hidden" name="action_2" value="un_delete_topic" />
			<input type="hidden" name="topic_id" value="'.$topic_id.'" />
			<input type="submit" name="button" class="button" value="Un-Delete Topic" onmouseover="this.className=\'button_hover\'" onmouseout="this.className=\'button\'" />
			</form>
			</td>
		  </tr>
		  <tr>
			<td class="two" align="center" colspan="4"><span style="font-weight:bold;color:red;">*</span>
			only <span style="font-weight:bold;">'.get_user_class_name($min_delete_view_class).'</span> and above can see these options!</td>
		</tr>').'
		</table></div></form>' : '');
		$HTMLOUT .='<script type="text/javascript" src="scripts/check_selected.js"></script>
	  
	  <script src="scripts/jquery.trilemma.js" type="text/javascript"></script>
    <script type="text/javascript">
    /*<![CDATA[*/
    $(function(){
	  jQuery(\'.poll_select\').trilemma({max:'.$multi_options.',disablelabels:true});
    });
    /*]]>*/
    </script>
    <script type="text/javascript">
    /*<![CDATA[*/
    $(document).ready(function()	{
    //=== show hide staff tools
    $("#tool_open").click(function() {
    $("#tools").slideToggle("slow", function() {
    });});
    //=== show hide voters
    $("#toggle_voters").click(function() {
    $("#voters").slideToggle("slow", function() {
    });});});
    //=== show hide send PM
    $("#pm_open").click(function() {
    $("#pm").slideToggle("slow", function() {
    });});
    /*]]>*/
    </script>';
//print_r($members_votes);
?>