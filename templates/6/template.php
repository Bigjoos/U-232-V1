<?php

function stdhead($title = "", $msgalert = true, $stdhead = false) {
    global $CURUSER, $TBDEV, $lang, $free, $_NO_COMPRESS, $querytime, $query_stat, $q, $mc1;

    if (!$TBDEV['site_online'])
      die("We are making updates, please check back again later... thanks<br />");
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
   
    if ($TBDEV['msg_alert'] && $msgalert && $CURUSER)
    {
      $unread = $mc1->get_value('inbox_new_'.$CURUSER["id"]);
      if ($unread === false) {
      $res = sql_query("SELECT count(*) FROM messages WHERE receiver=" . $CURUSER["id"] . " && unread='yes'") or sqlerr(__FILE__,__LINE__);
      $arr = mysql_fetch_row($res);
      $unread = (int)$arr[0];
      $mc1->cache_value('inbox_new_'.$CURUSER["id"], $unread, $TBDEV['expires']['unread']);
    }
    }
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
    $htmlout = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
    \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
    <html xmlns='http://www.w3.org/1999/xhtml'>
    <head>
    <meta name='generator' content='TBDev.net' />
    <meta http-equiv='Content-Language' content='en-us' />
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
    <meta name='MSSmartTagsPreventParsing' content='TRUE' />
    <title>{$title}</title>
    <link rel='alternate' type='application/rss+xml' title='Latest Torrents' href='./rss.php?passkey={$CURUSER['passkey']}' />
    <link rel='stylesheet' href='templates/6/6.css' type='text/css' />
    <link rel='stylesheet' href='templates/6/bbcode.css' type='text/css' />
    <link rel='shortcut icon' href='favicon.ico' />
    <script type='text/javascript' src='scripts/jquery.js'></script>
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
    <link rel='stylesheet' type='text/css' href='bbcode/markitup/skins/markitup/style.css' />
	  <link rel='stylesheet' type='text/css' href='bbcode/markitup/sets/bbcode/style.css' />
    ".$js_incl."
    </head>
    <body>";
    if ($CURUSER) 
    { 
    $htmlout .= "
    <div id='container'>
    <div id='logo'><!-- Css - Change logo @ css --></div>
    <div id='userstats'>";
    $htmlout .= StatusBar();
    $htmlout .="
    </div>
    <div id='globalmenu'>
    <ul id='menu2'>
    <li class='home'><a href='./index.php'><b>home</b></a></li>
    <li class='browse'><a href='./browse.php'><b>browse</b></a></li>
    <li class='forums'><a href='./forums.php'><b>forums</b></a></li>
    <li class='faq'><a href='./faq.php'><b>faq</b></a></li>
    <li class='rules'><a href='./rules.php'><b>rules</b></a></li>
    <li class='staff'><a href='./staff.php'><b>support</b></a></li>
    <li class='logout'><a href='./logout.php'><b>logout</b></a></li>
    </ul>
    </div>
    <div id='infomenu'>";
    $htmlout .= "
    &nbsp;&nbsp;<form method='post' action='browse.php'>
    <input type='text' name='search' size='30' value='' />
    <input type='submit' value='GO' class='btn1' />
    </form>
    </div>
    <div class='clear'></div>
    <div id='globalundermenu'>
    <img src='templates/6/gfx/small-ar.png' alt=''/><a href='./topten.php'>top10</a>&nbsp;&nbsp;
    <img src='templates/6/gfx/small-ar.png' alt=''/><a href='./chat.php'>chat</a>&nbsp;&nbsp;
    <img src='templates/6/gfx/small-ar.png' alt=''/><a href='./bet.php'>betting</a>&nbsp;&nbsp;
    <img src='templates/6/gfx/small-ar.png' alt=''/><a href='./bitbucket.php'>bitbucket</a>&nbsp;&nbsp;
    <img src='templates/6/gfx/small-ar.png' alt=''/><a href='./getrss.php'>get rss</a>&nbsp;&nbsp;
    <img src='templates/6/gfx/small-ar.png' alt=''/><a href='./donate.php'>donate</a>&nbsp;&nbsp;
    <img src='templates/6/gfx/small-ar.png' alt=''/><a href='javascript:void(0)' onclick='status_showbox()'>Update status</a>&nbsp;&nbsp;
    <img src='templates/6/gfx/small-ar.png' alt=''/><a href='#' onclick='themes();'>Theme</a>&nbsp;&nbsp;
    ";
    
    if( $CURUSER['class'] >= UC_UPLOADER )
 	{
    $htmlout .= "<img src='templates/6/gfx/small-ar.png' alt=''/><a href='./upload.php'>upload</a>&nbsp;&nbsp;";
    }
    else {$htmlout .= "<img src='templates/6/gfx/small-ar.png' alt=''/><a href='./uploadapp.php'>uploader-application</a>&nbsp;&nbsp;";}
    $htmlout .= "<img src='templates/6/gfx/small-ar.png' alt=''/><a href='./usercp.php'>profile</a>&nbsp;&nbsp;";
    if( $CURUSER['class'] >= UC_MODERATOR )
 	  {    
    $htmlout .= "<img src='templates/6/gfx/small-ar.png' alt=''/><a href='./staffpanel.php'>staffpanel</a>&nbsp;&nbsp;";
    }
    $htmlout .= "</div>";  
    } 

    $htmlout .="
    <table class='mainouter' width='900px' align='center' border='0' cellspacing='0' cellpadding='0'>
    <tr><td align='center' class='outer' style='padding-top: 20px; padding-bottom: 5px'>";
    //=== free addon start
if ($CURUSER && isset($free)) {
    foreach ($free as $fl) {
        switch ($fl['modifier']) {
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
        
    $htmlout .= ($fl['modifier'] != 0 && $fl['expires'] > TIME_NOW ? '<img src="'.$TBDEV['baseurl'].'/pic/cat_free.gif" alt="FREE!" title="'.$fl['title'].'&nbsp;|&nbsp;'.$mode.'
        '.$fl['message'].'&nbsp;|&nbsp;set by '.$fl['setby'].'&nbsp;|&nbsp;'.($fl['expires'] != 1 ? 
    'Until '.get_date($fl['expires'], 'DATE').' ('.mkprettytime($fl['expires'] - time()).' to go)' : '').'
        
    <br />' : '');
    }
}
   // happy hour
   if ( $CURUSER ) {
    if ( happyHour( "check" ) ) {
        $htmlout.="<table border='0' cellspacing='0' cellpadding='10'  ><tr><td align='center' style=\"background:#CCCCCC;color:#222222; padding:10px\">\n
        <b>Hey its now happy hour ! " . ( ( happyCheck( "check" ) == 255 ) ? "Every torrent downloaded in the happy hour is free" : "Only <a href=\"browse.php?cat=" . happyCheck( "check" ) . "\">this category</a> is free this happy hour" ) . "<br /><font color='red'>" . happyHour( "time" ) . " </font> remaining from this happy hour!</b>";
       $htmlout.="</td></tr></table><br />\n";
    }
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
   //==Temp demotion
   if ($CURUSER['override_class'] != 255 && $CURUSER) // Second condition needed so that this box isn't displayed for non members/logged out members.
   {
   $htmlout .= "<b><a href='./restoreclass.php'>
   <img src='templates/6/gfx/tool.png' alt='Your running under lower class, restore here' title='Your running under lower class, restore here' /><font color='white'></font></a></b>&nbsp;&nbsp;&nbsp;";
   }
   //==End
   if ($TBDEV['msg_alert'] && isset($unread) && !empty($unread))
   {
   $htmlout .= "<b><a href='./messages.php'><img src='templates/6/gfx/new-mail.png' alt='You got mail' title='You got mail' /></a></b>";
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
    } // stdhead

function stdfoot($stdfoot = false) {
global $querytime, $CURUSER, $TBDEV, $q, $queries, $query_stat, $mc1;
    
    $debug       = array(1, 10, 14);
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
    
    $htmlfoot = "
    </td></tr></table></div>
    <div id='footer'>
    <table width='900px' align='center' id='footer-tb'><tr>
	  <td id='footer-top'>
    <a href='http://tbdev.net'><img src='templates/6/gfx/footer/tbdev.jpg' alt='Tbdev.net powered' title='Tbdev.net powered' style='opacity:0.4;filter:alpha(opacity=40)' onmouseover='this.style.opacity=1;this.filters.alpha.opacity=100' onmouseout='this.style.opacity=0.4;this.filters.alpha.opacity=40'/></a>&nbsp;&nbsp;&nbsp;&nbsp;
    <a href='http://validator.w3.org'><img src='templates/6/gfx/footer/xhtml.jpg' alt='Xhtml valid' title='Xhtml valid' style='opacity:0.4;filter:alpha(opacity=40)' onmouseover='this.style.opacity=1;this.filters.alpha.opacity=100' onmouseout='this.style.opacity=0.4;this.filters.alpha.opacity=40'/></a>&nbsp;&nbsp;&nbsp;&nbsp;
    <a href='http://kidvision.me'><img src='templates/6/gfx/footer/design.jpg' alt='Designed by kidvision' title='Designed by kidvision' style='opacity:0.4;filter:alpha(opacity=40)' onmouseover='this.style.opacity=1;this.filters.alpha.opacity=100' onmouseout='this.style.opacity=0.4;this.filters.alpha.opacity=40'/></a>
    </td></tr>   
    <tr><td class='footer-t'>
    Copyright 2010 || All Rights Reserved || By {$TBDEV['site_name']}<br />
    <br />{$TBDEV['site_name']} generated this page in ".(round($seconds, 4))." seconds.<br /> 
    They had to raid the server ".$queries." time'".$howmany."using&nbsp;:&nbsp;<b>".$percentphp."</b>&nbsp;&#37;&nbsp;php&nbsp;&#38;&nbsp;<b>".$percentsql."</b>&nbsp;&#37;&nbsp;sql ".$serverkillers.".<br />".$header."";
    
    if ($debug_ids) {
    if ($q['query_stat']) {
    $htmlfoot .= "<br /><br />
     <div class='userheader'><img src='templates/6/gfx/big-ar.png' alt=''/>Querys</div><br /> 
	  <table width=\"900px\" align=\"center\" cellspacing=\"5\" cellpadding=\"5\" border=\"0\">
		<tr>
		<td class=\"colhead2\" width=\"5%\"  align=\"center\">ID</td>
		<td class=\"colhead2\" width=\"10%\" align=\"center\">Query Time</td>
		<td class=\"colhead2\" width=\"85%\" align=\"left\">Query String</td>
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
    $htmlfoot .="</table><br />";
    }
    }
   
    $htmlfoot .="</td></tr></table></div>\n";
    /** query stats **/
    /** include js files needed only for the page being used by pdq **/
    $htmlfoot .= '<!-- javascript goes here -->';
    if ($stdfoot['js'] != false) {
    foreach ($stdfoot['js'] as $JS)
    $htmlfoot .= '<script type="text/javascript" src="'.$TBDEV['baseurl'].'/scripts/'.$JS.'.js"></script>';
    }
    $htmlfoot .= "</body></html>\n";
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
	$res1 = sql_query("SELECT COUNT(*) FROM messages WHERE receiver='".$CURUSER['id']."' AND unread = 'yes'") or sqlerr(__LINE__,__FILE__);
	list($PMCount) = mysql_fetch_row($res1); 
  $PMCount= (int)$PMCount;
  $unread1 = $mc1->cache_value('inbox_new_sb_'.$CURUSER['id'], $PMCount, $TBDEV['expires']['unread']);
  }
	$inbox = ($unread1 == 1 ? "$unread1&nbsp;{$lang['gl_msg_singular']}" : "$unread1&nbsp;{$lang['gl_msg_plural']}");
  /**Memcached Seed/Leech counts and Connectable **/
  $MyPeersCache = $mc1->get_value('MyPeers_'.$CURUSER['id']);
  if ($MyPeersCache == false) {
    $seed['yes'] = $seed['no'] = 0;
    $seed['conn'] = 3;
    
      $r = sql_query("select count(*) as count, seeder, connectable FROM peers WHERE userid=".
        $CURUSER['id']." group by seeder") ; 
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
$conn = $seed['conn'];
	
    /////////////// REP SYSTEM /////////////
    $member_reputation = get_reputation($CURUSER);
    ////////////// REP SYSTEM END //////////
    $usrclass="";
    if ($CURUSER['override_class'] != 255) $usrclass = "&nbsp;<b>(".get_user_class_name($CURUSER['class']).")</b>&nbsp;";
    else
    if ($CURUSER['class'] >= UC_STAFF)
    $usrclass = "&nbsp;<a href='./setclass.php'><b>(".get_user_class_name($CURUSER['class']).")</b></a>&nbsp;";
	  $StatusBar = '';
		$StatusBar = 
		"".
		"<div style='float:left; width:500px;'>
        {$lang['gl_msg_welcome']}, 
		".format_username($CURUSER)."&nbsp;{$usrclass}&nbsp;&nbsp;|&nbsp;&nbsp;
        Ratio:&nbsp$ratio"."&nbsp;&nbsp;|&nbsp;&nbsp;Upload:&nbsp;$upped"."<br />
        Bonus:&nbsp;<a href='./mybonus.php'>{$CURUSER['seedbonus']}</a>&nbsp;&nbsp;|&nbsp;&nbsp;Invites:&nbsp;<a href='./invite.php'>{$CURUSER['invites']}</a>&nbsp;&nbsp;|&nbsp;&nbsp;
		Mailbox: <a href='./messages.php'>$inbox</a></div>
        <div>".
    "</div>";
    return $StatusBar;
    }
?>