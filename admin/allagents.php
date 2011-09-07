<?php
/**
 *   https://09source.kicks-ass.net:8443/svn/installer09/
 *   Licence Info: GPL
 *   Copyright (C) 2010 Installer09 v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn,kidvision.
 **/
 
	if ( ! defined( 'IN_TBDEV_ADMIN' ) )
  {
	$HTMLOUT='';
	$HTMLOUT .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
		\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
		<html xmlns='http://www.w3.org/1999/xhtml'>
		<head>
		<title>Error!</title>
		</head>
		<body>
	<div style='font-size:33px;color:white;background-color:red;text-align:center;'>Incorrect access<br />You cannot access this file directly.</div>
	</body></html>";
	print $HTMLOUT;
	exit();
  }
  require_once(INCL_DIR.'user_functions.php');
  $HTMLOUT='';
  $lang = array_merge( $lang, load_language('ad_bans') );
	require_once(INCL_DIR.'class_check.php');
  class_check(UC_MODERATOR);
	//if ($CURUSER['class'] < UC_MODERATOR)
	//stderr("Error", "Permission denied.");
	$res = sql_query("SELECT agent, peer_id FROM peers GROUP BY agent") or sqlerr();
	$HTMLOUT .="<table align='center' border='3' cellspacing='0' cellpadding='5'>
	<tr><td class='colhead'>Client</td><td class='colhead'>Peer ID</td></tr>";
	while($arr = mysql_fetch_assoc($res))
	{
	$HTMLOUT .="<tr><td align='left'>".htmlspecialchars($arr["agent"])."</td><td align='left'>".htmlspecialchars($arr["peer_id"])."</td></tr>\n";
	}
  $HTMLOUT .="</table>\n";
print stdhead("All Clients") . $HTMLOUT . stdfoot();
?>