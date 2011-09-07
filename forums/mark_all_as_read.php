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

taken from the old code and using Retros 
READPOST mod and updated a bit to work with new forums :D
**********************************************************/

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

$dt = (time() - $readpost_expiry);

$last_posts_read_res = sql_query('SELECT t.id, t.last_post FROM topics AS t LEFT JOIN posts AS p ON p.id = t.last_post AND p.added > '.$dt);
		
while ($last_posts_read_arr = mysql_fetch_assoc($last_posts_read_res))
  {
		$members_last_posts_read_res = sql_query('SELECT id, last_post_read FROM read_posts WHERE user_id='.$CURUSER['id'].' and topic_id='.$last_posts_read_arr['id']);
		
		if (mysql_num_rows($members_last_posts_read_res) === 0)
		{
			sql_query('INSERT INTO read_posts (user_id, topic_id, last_post_read) VALUES ('.$CURUSER['id'].', '.$last_posts_read_arr['id'].', '.$last_posts_read_arr['last_post'].')');
		}
		else
			{
			$members_last_posts_read_arr = mysql_fetch_assoc($members_last_posts_read_res);
			
				if ($members_last_posts_read_arr['last_post_read'] < $last_posts_read_arr['last_post'])
				{
				sql_query('UPDATE read_posts SET last_post_read='.$last_posts_read_arr['last_post'].' WHERE id='.$members_last_posts_read_arr['id']);
				}
			}

  }

	//=== ok, all done here, send them back! \o/
	header('Location: forums.php?m=1'); 
	
die();
?>