<?php
/**
 *   https://09source.kicks-ass.net:8443/svn/installer09/
 *   Licence Info: GPL
 *   Copyright (C) 2010 Installer09 v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn,kidvision.
 **/
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php');
require_once(INCL_DIR.'user_functions.php');
dbconn();
loggedinorreturn();
$lang = array_merge( load_language('global'));

$xmasday= mktime(0,0,0,12,25,date("Y"));
$today = mktime(date("G"), date("i"), date("s"), date("m"), date("d"), date("Y"));
$gifts = array("upload", "bonus", "invites", "bonus2");
$randgift = array_rand($gifts);
$gift = $gifts[$randgift];
$userid = 0 + $CURUSER["id"];
if (!is_valid_id($userid))
    stderr("Error", "Invalid ID");
$open = 0 + $_GET["open"];
if ($open != 1) {
    stderr("Error", "Invalid url");
}
if (isset($open) && $open == 1) {
    if ($today >= $xmasday) {
        if ($CURUSER["gotgift"] == 'no') {
            if ($gift == "upload") {
                sql_query("UPDATE users SET invites=invites+1, uploaded=uploaded+1024*1024*1024*10, freeslots=freeslots+1, gotgift='yes' WHERE id=".sqlesc($userid)."") or sqlerr(__FILE__, __LINE__);
                $mc1->delete_value('MyUser_'.$userid);
                $mc1->delete_value('user'.$userid);
                header('Refresh: 5; url='.$TBDEV['baseurl'].'/index.php');
                stderr("Congratulations!", "<img src=\"{$TBDEV['pic_base_url']}gift.png\" style=\"float: left; padding-right:10px;\" alt=\"Xmas Gift\" title=\"Xmas Gift\" /> <h2> You just got  1 invite 10 GB upload and bonus 1 freeslot !</h2>
Thanks for your support and sharing through year ".date('Y')." ! <br /> Merry Christmas and a happy New Year from {$TBDEV['site_name']}  Crew ! Redirecting in 5..4..3..2..1");
            }
            if ($gift == "bonus") {
                sql_query("UPDATE users SET invites=invites+3,  seedbonus = seedbonus + 1750, gotgift='yes' WHERE id=".sqlesc($userid)."") or sqlerr(__FILE__, __LINE__);
                $mc1->delete_value('MyUser_'.$userid);
                $mc1->delete_value('user'.$userid);
                header('Refresh: 5; url='.$TBDEV['baseurl'].'/index.php');
                stderr("Congratulations!", "<img src=\"{$TBDEV['pic_base_url']}gift.png\" style=\"float: left; padding-right:10px;\" alt=\"Xmas Gift\" title=\"Xmas Gift\" /> <h2> You just got 3 invites 1750 karma bonus points !</h2>
Thanks for your support and sharing through year ".date('Y')." ! <br /> Merry Christmas and a happy New Year from {$TBDEV['site_name']}  Crew ! Redirecting in 5..4..3..2..1");
            }
            if ($gift == "invites") {
                sql_query("UPDATE users SET invites=invites+2, seedbonus = seedbonus + 2000,  freeslots=freeslots+3, gotgift='yes' WHERE id=".sqlesc($userid)."") or sqlerr(__FILE__, __LINE__);
                $mc1->delete_value('MyUser_'.$userid);
                $mc1->delete_value('user'.$userid);
                header('Refresh: 5; url='.$TBDEV['baseurl'].'/index.php');
                stderr("Congratulations!", "<img src=\"{$TBDEV['pic_base_url']}gift.png\" style=\"float: left; padding-right:10px;\" alt=\"Xmas Gift\" title=\"Xmas Gift\" /> <h2> You just got 2 invites and 2000 bonus points and a bonus 3 freeslots !</h2>
Thanks for your support and sharing through year ".date('Y')." ! <br /> Merry Christmas and a happy New Year from {$TBDEV['site_name']} Crew ! Redirecting in 5..4..3..2..1");
            }
            if ($gift == "bonus2") {
                sql_query("UPDATE users SET invites=invites+3,  seedbonus = seedbonus + 2500, freeslots=freeslots+5, gotgift='yes' WHERE id=".sqlesc($userid)."") or sqlerr(__FILE__, __LINE__);
                $mc1->delete_value('MyUser_'.$userid);
                $mc1->delete_value('user'.$userid);
                header('Refresh: 5; url='.$TBDEV['baseurl'].'/index.php');
                stderr("Congratulations!", "<img src=\"{$TBDEV['pic_base_url']}gift.png\" style=\"float: left; padding-right:10px;\" alt=\"Xmas Gift\" title=\"Xmas Gift\" /> <h2> You just got 3 invites 1750 karma bonus points !</h2>
Thanks for your support and sharing through year ".date('Y')." ! <br /> Merry Christmas and a happy New Year from {$TBDEV['site_name']} Crew ! Redirecting in 5..4..3..2..1");
            }
        } else {
            stderr("Sorry...", "You already got your gift !");
        }
    } else {
        stderr("Doh...", "Be patient!  You can't open your present until Christmas day ! <b>" . date("j", ($xmasday - $today)) . "</b> day(s) to go. <br /> Today:<b>" . date('l dS \of F Y h:i:s A', $today) . "</b><br />Christmas day:<b>" . date('l dS \of F Y h:i:s A', $xmasday)."</b>");
    }
    }
?>