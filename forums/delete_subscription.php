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


	//=== delete stuff from topic page
	if($topic_id > 0)
	{
	sql_query('DELETE FROM subscriptions WHERE topic_id = '.$topic_id.' AND user_id = '.$CURUSER['id']);
	
		//=== ok, all done here, send them back! \o/
		header('Location: forums.php?action=view_topic&topic_id='.$topic_id.'&s=0'); 
	
	die();	
	}


//=== delete stuff from subscriptions page stolen from pdq... thanks hun \o
if (isset($_POST['remove']))
{
   $_POST['remove'] = (isset($_POST['remove']) ? $_POST['remove'] : '');    
   $post_delete = array();    
    foreach ($_POST['remove'] as $somevar)
        $post_delete[] = intval($somevar);
        
    $post_delete = array_unique($post_delete);
    $delete_count = count($post_delete);
       
    if ($delete_count > 0)  {
        sql_query('DELETE FROM subscriptions WHERE id IN ('.implode(', ', $post_delete).') AND user_id = '.$CURUSER['id']);
    }
	else
	{
	stderr('Error', 'Nothing Deleted!');
	}
}

	//=== ok, all done here, send them back! \o/
	header('Location: forums.php?action=subscriptions'); 
	
die();
?>