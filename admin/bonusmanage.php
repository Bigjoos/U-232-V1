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
require_once(INCL_DIR.'html_functions.php');

$lang = array_merge( $lang, load_language('bonusmanager'));

$HTMLOUT="";
 
require_once(INCL_DIR.'class_check.php');
class_check(UC_ADMINISTRATOR);

	  $res = sql_query("SELECT * FROM bonus") or sqlerr(__FILE__, __LINE__);
    if($_SERVER["REQUEST_METHOD"] == "POST")
    {
	  if(isset($_POST["id"]) || isset($_POST["points"]) || isset($_POST["pointspool"]) || isset($_POST["minpoints"]) || isset($_POST["description"]) || isset($_POST["enabled"])){
		$id = 0 + $_POST["id"];
		$points = 0 + $_POST["bonuspoints"];
		$pointspool = 0 + $_POST["pointspool"];
		$minpoints = 0 + $_POST["minpoints"];
		$descr = 	htmlspecialchars($_POST["description"]);
		$enabled = "yes";
		if(isset($_POST["enabled"]) == ''){
		$enabled = "no";
		}
		
		$sql = sql_query("UPDATE bonus SET points = '$points', pointspool='$pointspool', minpoints='$minpoints', enabled = '$enabled', description = '$descr' WHERE id = '$id'");
	  if($sql){
    header("Location: {$TBDEV['baseurl']}/admin.php?action=bonusmanage");
    } else {
    stderr($lang['bonusmanager_oops'], "{$lang['bonusmanager_sql']}");
    
    
    }
  }
}
while($arr = mysql_fetch_assoc($res)) {
    $HTMLOUT .="<form name='bonusmanage' method='post' action='staffpanel.php?tool=bonusmanage&amp;action=bonusmanage'>
	  <div class='roundedCorners' style='text-align:left;width:80%;border:1px solid black;padding:5px;'>
    <div style='background:#890537;height:25px;'><span style='font-weight:bold;font-size:12pt;'>{$lang['bonusmanager_bm']}</span></div>
	  <table width='100%' border='2' cellpadding='8'>
	  <tr>
		<td style='background:#890537;height:25px;'>{$lang['bonusmanager_id']}</td>
		<td style='background:#890537;height:25px;'>{$lang['bonusmanager_enabled']}</td>
		<td style='background:#890537;height:25px;'>{$lang['bonusmanager_bonus']}</td>
		<td style='background:#890537;height:25px;'>{$lang['bonusmanager_points']}</td>
		<td style='background:#890537;height:25px;'>{$lang['bonusmanager_pointspool']}</td>
		<td style='background:#890537;height:25px;'>{$lang['bonusmanager_minpoints']}</td>
		<td style='background:#890537;height:25px;'>{$lang['bonusmanager_description']}</td>
	  <td style='background:#890537;height:25px;'>{$lang['bonusmanager_type']}</td>
		<td style='background:#890537;height:25px;'>{$lang['bonusmanager_quantity']}</td>
		<td style='background:#890537;height:25px;'>{$lang['bonusmanager_action']}</td></tr> 
	  <tr><td>
		<input name='id' type='hidden' value='{$arr["id"]}' />$arr[id]</td>
		<td><input name='enabled' type='checkbox'".($arr["enabled"] == "yes" ? " checked='checked'" : ""). " /></td>
		<td>{$arr["bonusname"]}</td>
		<td><input type='text' name='bonuspoints' value='{$arr["points"]}' size='4' /></td>
		<td><input type='text' name='pointspool' value='{$arr["pointspool"]}' size='4' /></td>
		<td><input type='text' name='minpoints' value='{$arr["minpoints"]}' size='4' /></td>
		<td><textarea name='description' rows='4' cols='10'>{$arr["description"]}</textarea></td>
		<td>{$arr["art"]}</td>
		<td>". (($arr["art"] == "traffic" || $arr["art"] == "traffic2" || $arr["art"] == "gift_1" || $arr["art"] == "gift_2") ? ($arr["menge"] / 1024 / 1024 / 1024) . " GB" : $arr["menge"]) ."</td>
		<td align='center'><input type='submit' value='{$lang['bonusmanager_submit']}' /></td>
		</tr></table></div></form>";
		}
		

print stdhead('Bonus Manager') . $HTMLOUT . stdfoot();
?>