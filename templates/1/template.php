<?php

function stdhead($title = "", $msgalert = true, $stdhead = false) {
    global $CURUSER, $TBDEV, $lang, $free, $_NO_COMPRESS, $mc1;
    if (!$TBDEV['site_online'])
      die("Site is down for maintenance, please check back again later... thanks<br />");
    header('Content-Type: text/html; charset='.$TBDEV['char_set'].'');
    header('Content-Language content="en-us"');
    if ($title == "")
        $title = $TBDEV['site_name'] .(isset($_GET['tbv'])?" (".TBVERSION.")":'');
    else
        $title = $TBDEV['site_name'].(isset($_GET['tbv'])?" (".TBVERSION.")":''). " :: " . htmlspecialchars($title);        
    if ($CURUSER)
    {
    $TBDEV['stylesheet'] = isset($CURUSER['stylesheet']) ? "{$CURUSER['stylesheet']}.css" : $TBDEV['stylesheet'];
    }
    //==Memcached message query
    if ($TBDEV['msg_alert'] && $CURUSER)
    {
      $unread = $mc1->get_value('inbox_new_'.$CURUSER['id']);
      if ($unread === false) {
      $res = sql_query('SELECT count(id) FROM messages WHERE receiver='.$CURUSER['id'].' && unread="yes" AND location = "1"') or sqlerr(__FILE__,__LINE__);
      $arr = mysql_fetch_row($res);
      $unread = (int)$arr[0];
      $mc1->cache_value('inbox_new_'.$CURUSER['id'], $unread, $TBDEV['expires']['unread']);
    }
    }
    //==End
    /** ZZZZZZZZZZZZZZZZZZZZZZZZZZip it! **/
    if (!isset($_NO_COMPRESS))
    if (!ob_start('ob_gzhandler'))
    ob_start();
    /** include js files needed only for the page being used by pdq **/
    $js_incl = '<!-- javascript goes here or in footer -->';
    if ($stdhead['js'] != false) {
    foreach ($stdhead['js'] as $JS)
    $js_incl .= "<script type='text/javascript' src='".$TBDEV['baseurl']."/scripts/".$JS.".js'></script>";
    }
	  $htmlout="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
 	  \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
 	  <html xmlns='http://www.w3.org/1999/xhtml'>
 	  <head>
 		<meta name='generator' content='TBDev.net' />
 		<meta http-equiv='Content-Language' content='en-us' />
 		<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
 		<meta name='MSSmartTagsPreventParsing' content='TRUE' />
    <title>{$title}</title>
	  <link rel='stylesheet' href='templates/1/itunes.css' type='text/css' />
   	  <link rel='shortcut icon' href='favicon.ico' />
          <script type='text/javascript' src='./scripts/jquery.js'></script>
		<script type='text/javascript' src='./scripts/jquery.status.js'></script>
		<script type='text/javascript' src='image-resize/core-resize.js'></script>
    <script type='text/javascript'>
    /*<![CDATA[*/
    function themes() {
    window.open('take_theme.php','My themes','height=150,width=200,resizable=no,scrollbars=no,toolbar=no,menubar=no');
    }
    /*]]>*/
    </script>
    <link type='text/css' rel='stylesheet' href='image-resize/resize.css'  />
		<link rel='alternate' type='application/rss+xml' title='Latest Torrents' href='./rss.php?passkey={$CURUSER['passkey']}' />
		<link rel='stylesheet' type='text/css' href='bbcode/markitup/skins/markitup/style.css' />
	  <link rel='stylesheet' type='text/css' href='bbcode/markitup/sets/bbcode/style.css' />
		".$js_incl."
		</head> 
    <body>
    <div class='itunes'>
	  <table class='maincont'>
	  <tr><td class='haus' colspan='2'>
	  <div class='play'>
	  <div class='play1'></div>
	  <div class='play3'></div>
	  <div class='play2'>{$TBDEV['site_name']}</div>
	  </div>
	  </td></tr>";
	  $htmlout.=StatusBar();
    
    $htmlout .="
	  <tr valign='top'><td class='hlid'>";
	  if ($CURUSER) 
    {
	  $htmlout .="<ul class='nav'>
	  <li><a href='index.php'>{$lang['gl_home']}</a></li>
	  <li><a href='browse.php'>{$lang['gl_browse']}</a></li>
	  ".(isset($CURUSER) && $CURUSER['class']>=UC_UPLOADER ? "<li><a href='upload.php'>{$lang['gl_upload']}</a></li>":"")."
	  ".(isset($CURUSER) && $CURUSER['class']<=UC_VIP ? "<li><a href='uploadapp.php'>{$lang['gl_uploadapp']}</a></li>":"")."
	 
	  <li><a href='getrss.php'>{$lang['gl_getrss']}</a></li>
	  ".(isset($CURUSER)?"<li><a href='usercp.php'>{$lang['gl_profile']}</a></li>":"<li><a href='login.php'>{$lang['gl_login']}</a></li><li><a href='signup.php'>{$lang['gl_signup']}</a></li>")."
	  <li><a href='forums.php'>{$lang['gl_forums']}</a></li>
	  <li><a href='staff.php'>{$lang['gl_staff']}</a></li>
	  <li><a href='contactstaff.php'>{$lang['gl_help']}</a></li>
	  <li><a href='donate.php'>{$lang['gl_donate']}</a></li>
	  <li><a href='topten.php'>{$lang['gl_top_10']}</a></li>
	  <li><a href='catalogue.php'>{$lang['gl_catalogue']}</a></li>
	  <li><a href='viewrequests.php'>{$lang['gl_request']}</a></li>
	  ".(isset($CURUSER) && $CURUSER['class']>=UC_MODERATOR ? "<li><a href='staffpanel.php'>{$lang['gl_admin']}</a></li>":"")."
	  </ul>";
	  }
	  $htmlout .="</td><td class='sida' align='center'>";
	  
	//=== free addon start
    if ($CURUSER) { 
    if (isset($free))
    {
    foreach ($free as $fl)
    {
        switch ($fl['modifier'])
        {
            case 1:
                $mode = 'All Torrents Free';
                break;

            case 2:
                $mode = 'All Double Upload';
                break;

            case 3:
                $mode = 'All Torrents Free and Double Upload';
                break;

            default:
                $mode = 0;
        }
        
     $htmlout .= ($fl['modifier'] != 0 && $fl['expires'] > TIME_NOW ? '<table width="50%"><tr>
     <td class="colhead" colspan="3" align="center">'.$fl['title'].'<br />'.$mode.'</td>
   </tr>
   <tr>
     <td width="42" align="center">
     <img src="'.$TBDEV['baseurl'].'/pic/cat_free.gif" alt="FREE!" /></td>
     <td align="center">'.$fl['message'].' set by '.$fl['setby'].'<br />'.($fl['expires'] != 1 ? 
'Until '.get_date($fl['expires'], 'DATE').' ('.mkprettytime($fl['expires'] - time()).' to go)' : '').'</td>
     <td width="42" align="center">
     <img src="'.$TBDEV['baseurl'].'/pic/cat_free.gif" alt="FREE!" /></td>
</tr></table>
<br />' : '');
}
}
}
//=== free addon end
   //==Temp demotion
   if ($CURUSER['override_class'] != 255 && $CURUSER) // Second condition needed so that this box isn't displayed for non members/logged out members.
   {
   $htmlout .= "<table border='0' cellspacing='0' cellpadding='10' bgcolor='green'>
   <tr><td style='padding: 10px; background: green'><b><a href='./restoreclass.php'>
   <font color='white'>{$lang['gl_tempdemotion']}</font></a></b></td></tr></table><br />\n";
   }
   //==End
    if ($TBDEV['msg_alert'] && isset($unread) && !empty($unread))
    {
      $htmlout .= "<table border='0' cellspacing='0' cellpadding='10' bgcolor='red'>
                  <tr><td style='padding: 10px; background: red'>\n
                  <b><a href='./messages.php'><font color='white'>".sprintf($lang['gl_msg_alert'], $unread) . ($unread > 1 ? "s" : "") . "!</font></a></b>
                  </td></tr></table><br />\n";
    }
   //==Memcached Big red staff thingy box:
   if($TBDEV['staffmsg_alert'] && $CURUSER['class'] >= UC_MODERATOR) {
	 $answeredby = $mc1->get_value('staff_mess_');
	 if ($answeredby === false) {
	 $res1 = sql_query("SELECT count(id) FROM staffmessages WHERE answeredby = 0");
	 list($answeredby) = mysql_fetch_row($res1);
	 $mc1->cache_value('staff_mess_', $answeredby, $TBDEV['expires']['alerts']);
	 }
	 if ($answeredby > 0) {
	 $htmlout .= "<table border='0' cellspacing='0' cellpadding='10'>
   <tr><td style='padding: 10px; background: #ccc'>\n
   <b><a href='staffbox.php'>".sprintf($lang['gl_staffmsg_alert'], $answeredby). "!</a></b>
   </td></tr></table><br />";
	 }
   }
   //==End
	//==Memcached Big red report thingy box:
   if($TBDEV['report_alert'] && $CURUSER['class'] >= UC_MODERATOR) {
   $delt_with = $mc1->get_value('new_report_');
   if ($delt_with === false) {
   $res_reports = sql_query("SELECT COUNT(id) FROM reports WHERE delt_with = '0'");
   list($delt_with) = mysql_fetch_row($res_reports);
   $mc1->cache_value('new_report_', $delt_with, $TBDEV['expires']['alerts']);
   }
   if ($delt_with > 0){
   $htmlout.="<table border='0' cellspacing='0' cellpadding='10' bgcolor='red'>
   <tr><td style='padding: 10px; background: #A60A15' align='center'>
   <b>Hey $CURUSER[username]! $delt_with report" . ($delt_with > 1 ? "s" : "") . " to be dealt with<br />
   click <a href='admin.php?action=reports'>Here</a> to view reports</b></td></tr></table><br />\n";
   }
   }
   //==End
   //==Memcached Big red uploadapp thingy box:
   if($TBDEV['uploadapp_alert'] && $CURUSER['class'] >= UC_MODERATOR) {
   $newapp = $mc1->get_value('new_uploadapp_');
   if ($newapp === false) {
   $res_newapps = sql_query("SELECT count(id) FROM uploadapp WHERE status = 'pending'");
   list($newapp) = mysql_fetch_row($res_newapps);
   $mc1->cache_value('new_uploadapp_', $newapp, $TBDEV['expires']['alerts']);
   }
   if ($newapp > 0){
   $htmlout.="<table border='0' cellspacing='0' cellpadding='10' bgcolor='red'>
   <tr><td style='padding: 10px; background: #A60A15' align='center'>
   <b>Hey $CURUSER[username]! $newapp uploader application" . ($newapp > 1 ? "s" : "") . " to be dealt with<br />
   click <a href='uploadapps.php'>Here</a> to view the application</b></td></tr></table><br />\n";
   }
   }
   //==End
   // happy hour
    if ( $CURUSER ) {
    if ( happyHour( "check" ) ) {
        $htmlout.="<table border='0' cellspacing='0' cellpadding='10'  ><tr><td align='center' style=\"background:#CCCCCC;color:#222222; padding:10px\">\n
        <b>Hey its now happy hour ! " . ( ( happyCheck( "check" ) == 255 ) ? "Every torrent downloaded in the happy hour is free" : "Only <a href=\"browse.php?cat=" . happyCheck( "check" ) . "\">this category</a> is free this happy hour" ) . "<br /><font color='red'>" . happyHour( "time" ) . " </font> remaining from this happy hour!</b>";
       $htmlout.="</td></tr></table><br />\n";
    }
   }
   
   //==pdq crazyhour
	 if (isset($CURUSER)) {
   $transfer_filename  = $TBDEV['cache'].'/transfer_crazyhour.txt';
   $crazyhour_filename = $TBDEV['cache'].'/crazy_hour.txt';
   $crazyhour_cache = fopen($crazyhour_filename,'r+');
   $crazyhour_var = fread($crazyhour_cache, filesize($TBDEV['cache'].'/crazy_hour.txt'));
   fclose($crazyhour_cache);
   $cimg = '<img src=\''.$TBDEV["pic_base_url"].'cat_free.gif\' alt=\'FREE!\' />';
   if ($crazyhour_var >= TIME_NOW && $crazyhour_var < TIME_NOW + 3600) { // is crazyhour
       $htmlout .="<table width='50%'><tr><td class='colhead' colspan='3' align='center'>
       ".$TBDEV['crazy_title']." Ends in ".mkprettytime($crazyhour_var - TIME_NOW)."</td></tr>
       <tr><td width='42px' align='center' valign='middle'>". $cimg."</td>
       <td><div align='center'>". $TBDEV['crazy_message']."</div></td>
       <td width='42px' align='center' valign='middle'>".$cimg."</td></tr></table><br />";
        if (is_file($transfer_filename))
            unlink($transfer_filename);
    }
    elseif ($crazyhour_var < TIME_NOW + 3600 && !is_file($transfer_filename)) { //== crazyhour over
        $transfer_file_created = fopen($transfer_filename, 'w') or die('no perms?');
        fclose($transfer_file_created);
        $crazyhour['crazyhour_new']       = mktime(23, 59, 59, date('m'), date('d'), date('y'));
        $crazyhour['crazyhour']['var']    = mt_rand($crazyhour['crazyhour_new'], ($crazyhour['crazyhour_new'] + 86400));
        $fp = fopen($crazyhour_filename, 'w');
        fwrite($fp, $crazyhour['crazyhour']['var']);
        fclose($fp); 
        write_log('Next Crazyhour is at '.date('F j, g:i a T', $crazyhour['crazyhour'] ['var'])); 
        $htmlout .="<table cellpadding='3'><tr><td class='colhead' colspan='3' align='center'>"." Crazyhour will be ".get_date($crazyhour['crazyhour']['var'], '')."  ".mkprettytime($crazyhour['crazyhour']['var'] - TIME_NOW)." remaining till Crazyhour</td></tr></table><br />";
        }
        else // make date look prettier with countdown etc even :]
        $htmlout .="<table cellpadding='3'><tr><td class='colhead' colspan='3' align='center'>"." Crazyhour will be ".get_date($crazyhour_var, '')."  ".mkprettytime($crazyhour_var - TIME_NOW)." remaining till Crazyhour</td></tr></table><br />";
        }
	      // crazyhour end
	return $htmlout;
}
    
    function stdfoot($stdfoot = false) {
    global $querytime, $CURUSER, $TBDEV, $queries, $query_stat, $mc1, $q;
    $debug       = array(1, 10, 14 ,2);
    $debug_ids   = (SQL_DEBUG && in_array($CURUSER['id'], $debug) ? 1 : 0);
    $cachetime   = ($mc1->Time/1000);
    $seconds     = microtime(true) - $q['start'];
    $phptime     = $seconds - $querytime;
    $phptime     = $phptime - $cachetime;
    $queries     = (!empty($queries) ? $queries : 0);
    $percentphp  = number_format(($phptime / $seconds) * 100, 2);
    $percentsql  = number_format(($querytime / $seconds) * 100, 2);
    $percentmc   = number_format(($cachetime / $seconds) * 100, 2);
    $howmany     = ($queries != 1 ? 's ' : ' ');
    $serverkillers = $queries > 4 ? '<br />'.($queries/2).' Server killers ran to show you this page :) ! =[' : '=]';
    
    $MemStats = $mc1->get_value('mc_hits');
    if ($MemStats === false) {
    $MemStats = $mc1->getStats();
    $MemStats['Hits'] = (($MemStats['get_hits']/$MemStats['cmd_get'] < 0.7) ? '' : number_format(($MemStats['get_hits']/$MemStats['cmd_get'])*100, 3));
    $mc1->cache_value('mc_hits', $MemStats, 10);
    }
    
    $header = ''; 
    $header .= number_format($cachetime, 5).'s';
    $header = round($percentmc, 2).'&#37; Memcached: '.number_format($cachetime, 5).'s Hits: '.$MemStats['Hits'].'% Misses: '.(100 - $MemStats['Hits']).'% Items: '.number_format($MemStats['curr_items']);
    
    $htmlfoot='';
    if(isset($CURUSER)){
    $htmlfoot = "<br /><div class='roundedCorners' style=\"text-align:center;width:80%;border:1px solid black;padding:5px;\">
    <div style=\"text-align:left;background:transparent;height:25px;\"><span style=\"font-weight:bold;font-size:12pt;\">Query stats</span></div>The {$TBDEV['site_name']}
    Server killers generated this page in ".(round($seconds, 4))." seconds and then took a nap.<br /> 
    They had to raid the server ".$queries." time'".$howmany."using&nbsp;:&nbsp;<b>".$percentphp."</b>&nbsp;&#37;&nbsp;php&nbsp;&#38;&nbsp;<b>".$percentsql."</b>&nbsp;&#37;&nbsp;sql ".$serverkillers.".<br />".$header."</div>";
      
    if ($debug_ids) {
    if ($q['query_stat']) {
    $htmlfoot .= "<br />
	  <div class='roundedCorners' style=\"text-align:left;width:80%;border:1px solid black;padding:5px;\">
    <div style=\"background:transparent;height:25px;\"><span style=\"font-weight:bold;font-size:12pt;\">Querys</span></div>
	  <table width=\"100%\" align=\"center\" cellspacing=\"5\" cellpadding=\"5\" border=\"0\">
		<tr>
		<td class=\"colhead\" width=\"5%\"  align=\"center\">ID</td>
		<td class=\"colhead\" width=\"10%\" align=\"center\">Query Time</td>
		<td class=\"colhead\" width=\"85%\" align=\"left\">Query String</td>
		</tr>";
    foreach ($q['query_stat'] as $key => $value) {
    $htmlfoot  .= "<tr>
		<td align=\"center\">".($key + 1)."</td>
		<td align=\"center\"><b>". ($value['seconds'] > 0.01 ?
		"<font color=\"red\" title=\"You should optimize this query.\">".
    $value['seconds']."</font>" : "<font color=\"green\" title=\"Query good.\">".
	  $value['seconds']."</font>")."</b></td>
		<td align=\"left\">".htmlspecialchars($value['query'])."<br /></td>
		</tr>";	   		   
    }
    $htmlfoot .='</table></div><br />';
    }
    }
    }
    $htmlfoot .="<p align='center'>
    <!-- It's not accepted that you remove any credit here... --> 
    <a href='http://tbdev.net'><img src='./pic/tbdev_power.png' alt='TBDEV'/></a>
    <a href='http://validator.w3.org'><img src='./pic/xhtml_valid.png' alt='Xhtml valid'/></a>&nbsp;&nbsp;<a href='http://jigsaw.w3.org/css-validator/check/'><img src='./pic/css_valid.png' alt='Xhtml valid' /></a></p>
    </td></tr><tr><td class='fotur' colspan='2'>Design by TerraNova</td></tr></table>";
    /** query stats **/
    /** include js files needed only for the page being used by pdq **/
    $htmlfoot .= '<!-- javascript goes here -->';
    if ($stdfoot['js'] != false) {
    foreach ($stdfoot['js'] as $JS)
    $htmlfoot .= '<script type="text/javascript" src="'.$TBDEV['baseurl'].'/scripts/'.$JS.'.js"></script>';
    }
    $htmlfoot .= "</div></body></html>\n";
    return $htmlfoot;
    } 

    function stdmsg($heading, $text)
    {
    $htmlout = "<table class='main' width='750' border='0' cellpadding='0' cellspacing='0'>
    <tr><td class='embedded'>\n";
    if ($heading)
    $htmlout .= "<h2>$heading</h2>\n";
    $htmlout .= "<table width='100%' border='1' cellspacing='0' cellpadding='10'><tr><td class='text'>\n";
    $htmlout .= "{$text}</td></tr></table></td></tr></table>\n";
    return $htmlout;
    }

function StatusBar() {
	global $CURUSER, $TBDEV, $lang, $rep_is_on, $mc1;
	if (!$CURUSER)
	return "";
	$upped = mksize($CURUSER['uploaded']);
	$downed = mksize($CURUSER['downloaded']);
  $ratio = $CURUSER['downloaded'] > 0 ? $CURUSER['uploaded'] / $CURUSER['downloaded'] : 0;
  $ratio = number_format($ratio, 2);
  $color = get_ratio_color($ratio);
  if ($color)
  $ratio = "<font color='$color'>$ratio</font>";  

  //==Memcache unread pms
	$unread1 = $mc1->get_value('inbox_new_sb_'.$CURUSER['id']);
  if ($unread1 === false) {
	$res1 = sql_query("SELECT COUNT(id) FROM messages WHERE receiver='".$CURUSER['id']."' AND unread = 'yes'") or sqlerr(__LINE__,__FILE__);
	list($unread1) = mysql_fetch_row($res1); 
  //$PMCount= (int)$PMCount;
  $unread1 = $mc1->cache_value('inbox_new_sb_'.$CURUSER['id'], $unread1, $TBDEV['expires']['unread']);
  }
	$inbox = ($unread1 == 1 ? "$unread1&nbsp;{$lang['gl_msg_singular']}" : "$unread1&nbsp;{$lang['gl_msg_plural']}");
  //==End
  //==Memcached peers
  $MyPeersCache = $mc1->get_value('MyPeers_'.$CURUSER['id']);
if ($MyPeersCache == false) {
    $seed['yes'] = $seed['no'] = 0;
    $seed['conn'] = 3;
    
      $r = sql_query("select count(id) as count, seeder, connectable FROM peers WHERE userid=".$CURUSER['id']." group by seeder") ; 
       while($a = mysql_fetch_assoc($r)) {
        $key = $a['seeder'] ? 'yes' : 'no';
        $seed[$key] = number_format(0+$a['count']);    
        $seed['conn'] = $a['connectable'] == 'no' ? 1 : 2;
    }  
   $mc1->cache_value('MyPeers_'.$CURUSER['id'], $seed, $TBDEV['expires']['MyPeers_']);
   unset($r, $a);        
} else {
    $seed = $MyPeersCache;
}
// for display connectable  1 / 2 / 3 
 if (!empty($seed['conn'])) {
       switch ($seed['conn']){ 
       case 1:
         $connectable = "<img src='{$TBDEV['pic_base_url']}notcon.png' alt='Connectable' title='Connectable' />";
       break;
       case 2:
         $connectable = "<img src='{$TBDEV['pic_base_url']}yescon.png' alt='Connectable' title='Connectable' />";
       break;
       default :
         $connectable = "N/A";
       }
    }
    else
        $connectable = 'N/A';
  /////////////// REP SYSTEM /////////////
  $member_reputation = get_reputation($CURUSER);
   ////////////// REP SYSTEM END //////////
	  if ($CURUSER['class'] < UC_VIP && $TBDEV['max_slots']) {
    $ratioq = (($CURUSER['downloaded'] > 0) ? ($CURUSER['uploaded'] / $CURUSER['downloaded']) : 1);
    if ($ratioq < 0.95) {
	  switch (true) {
		case ($ratioq < 0.5):
		$max = 2;
		break;
		case ($ratioq < 0.65):
		$max = 3;
		break;
		case ($ratioq < 0.8):
		$max = 5;
		break;
		case ($ratioq < 0.95):
		$max = 10;
		break;
		default:
	  $max = 10;
	  }
    }
    else {
    switch ($CURUSER['class']) {
		case UC_USER:
		$max = 20;
		break;
		case UC_POWER_USER:
		$max = 30;
		break;
		default:
	  $max = 99;
	  }	
    }   
    }
    else
    $max = 999;
    
	  $usrclass="";
    if ($CURUSER['override_class'] != 255) $usrclass = "&nbsp;<b>(".get_user_class_name($CURUSER['class']).")</b>&nbsp;";
    elseif($CURUSER['class'] >= UC_MODERATOR) 
    $usrclass = "&nbsp;<a href='{$TBDEV['baseurl']}/setclass.php'><b>(".get_user_class_name($CURUSER['class']).")</b></a>&nbsp;";
	  $StatusBar = '';
		$StatusBar = "<tr>".
		"<td colspan='2' style='padding: 2px;'>".
		"<div id='statusbar'>".
		"<div style='float:left;color:black;'>{$lang['gl_msg_welcome']}, 
		".format_username($CURUSER)."&nbsp;{$usrclass} 
		"."&nbsp;$member_reputation".
		"&nbsp;&nbsp;Connectable:&nbsp;$connectable".
		"&nbsp;|&nbsp;Invites:&nbsp;<a href='{$TBDEV['baseurl']}/invite.php'>{$CURUSER['invites']}</a>&nbsp;|"."
		&nbsp;Bonus:&nbsp;<a href='{$TBDEV['baseurl']}/mybonus.php'>{$CURUSER['seedbonus']}</a>&nbsp;|&nbsp;<a href='#' onclick='themes();'>[Theme]</a>&nbsp;|&nbsp;<a href='javascript:void(0)' onclick='status_showbox()'>[Update status]</a>&nbsp;|&nbsp;<a href='logout.php'>[{$lang['gl_logout']}]</a>
		<br />{$lang['gl_ratio']}:$ratio".
		"&nbsp;|&nbsp;{$lang['gl_uploaded']}:$upped".
		"&nbsp;|&nbsp;{$lang['gl_downloaded']}:$downed".
		"&nbsp;|&nbsp;{$lang['gl_act_torrents']}:&nbsp;<img alt='{$lang['gl_seed_torrents']}' title='{$lang['gl_seed_torrents']}' src='{$TBDEV['pic_base_url']}up.png' />&nbsp;{$seed['yes']}".
		"&nbsp;&nbsp;<img alt='{$lang['gl_leech_torrents']}' title='{$lang['gl_leech_torrents']}' src='{$TBDEV['pic_base_url']}dl.png' />&nbsp;".($TBDEV['max_slots'] ? "<a title='I have ".$max." Download Slots'>{$seed['no']}/".$max."</a>" : $seed['no'])."</div>".
		"<div><p style='text-align:right;'>".gmdate("l, F jS H:i ", TIME_NOW + get_time_offset()) ."<br />".
    "<a href='./messages.php'>$inbox</a></p></div>".
    "</div></td></tr>";
	return $StatusBar;
}

?>