<?php
/**
 *   http://btdev.net:1337/svn/test/Installer09_Beta
 *   Licence Info: GPL
 *   Copyright (C) 2010 BTDev Installer v.1
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn.
 **/
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php');
require_once(INCL_DIR.'user_functions.php');
require_once INCL_DIR.'bbcode_functions.php';
require_once ROOT_DIR.'polls.php';
dbconn(true);

loggedinorreturn();

    $stdfoot = array(/** include js **/'js' => array('shout','java_klappe'));

    $lang = array_merge( load_language('global'), load_language('index') );
    //$lang = ;
    
    $HTMLOUT = '';
    /** latestuser index **/
    $latestuser_cache = $mc1->get_value('latestuser');
    if ($latestuser_cache === false) {
    $latestuser_cache = mysql_fetch_assoc(@sql_query('SELECT id, username FROM users WHERE status="confirmed" ORDER BY id DESC LIMIT 1'))/* or sqlerr(__FILE__, __LINE__)*/;
    $latestuser_cache['id']  = (int)$latestuser_cache['id']; // so is stored as an integer
    /** OOP **/
    $mc1->cache_value('latestuser', $latestuser_cache, $TBDEV['expires']['latestuser']);
    }
    $latestuser = '<div class="roundedCorners" style="text-align:center;width:80%;border:1px solid black;padding:5px;"><span id="latestuser" style="text-align:center;">
      Welcome to our newest member, 
      <b><a href="userdetails.php?id='.$latestuser_cache['id'].'">'.$latestuser_cache['username'].'</a></b>!
      </span></div><br />';
 
   //==Stats Begin
    $stats_cache = $mc1->get_value('site_stats_');
    if ($stats_cache === false) {
    $stats_cache = mysql_fetch_assoc(sql_query("SELECT *, seeders + leechers AS peers, seeders / leechers AS ratio, unconnectables / (seeders + leechers) AS ratiounconn FROM stats WHERE id = '1' LIMIT 1"))/* or sqlerr(__FILE__, __LINE__)*/;
    $seeders = (int) $stats_cache['seeders'];
    $leechers = (int) $stats_cache['leechers'];
    $registered = (int) $stats_cache['regusers'];
    $unverified = (int) $stats_cache['unconusers'];
    $torrents =  (int) $stats_cache['torrents'];
    $torrentstoday = (int) $stats_cache['torrentstoday'];
    $ratiounconn = (int) $stats_cache['ratiounconn'];
    $unconnectables = (int) $stats_cache['unconnectables'];
    $ratio = $stats_cache['ratio'];
    $peers = (int) $stats_cache['peers'];
    $numactive = (int) $stats_cache['numactive'];
    $donors = (int) $stats_cache['donors'];
    $forumposts = (int) $stats_cache['forumposts'];
    $forumtopics = (int) $stats_cache['forumtopics'];
    $mc1->cache_value('site_stats_', $stats_cache, $TBDEV['expires']['site_stats']);
    }
    //==End
   
   $browser = $_SERVER['HTTP_USER_AGENT'];
   if(preg_match("/MSIE/i",$browser))//browser is IE
   {
   $HTMLOUT .="<div align='center'><img border='0' src='{$TBDEV['pic_base_url']}warned.png' />
   <font size='+2'><b>Warning!</b></font><img border='0' src='{$TBDEV['pic_base_url']}warned.png' />
   <br />It appears as though you are running Internet Explorer, this site was <b>NOT</b> intended to be viewed with internet explorer and chances are it will not look right and may not even function correctly.
   {$TBDEV['site_name']} suggests that you <a href='http://browsehappy.com'><b>browse happy</b></a> and consider switching to one of the many better alternatives.
   <br /><a href='http://www.mozilla.com/firefox'><img border='0' alt='Get Firefox!' title='Get Firefox!' src='{$TBDEV['pic_base_url']}getfirefox.gif' /></a>
   <br /><strong>Get a SAFER browser !</strong></div>";
   }
   
   //==MemCached latest user
   $HTMLOUT .= $latestuser;
   
   // Announcement Code...
   $ann_subject = trim($CURUSER['curr_ann_subject']);
   $ann_body = trim($CURUSER['curr_ann_body']);
   if ((!empty($ann_subject)) AND (!empty($ann_body)))
   {
   $HTMLOUT .= "<div class='roundedCorners' style='text-align:left;width:80%;border:1px solid black;padding:5px;'>
   <div style='background:transparent;height:25px;'><span style='font-weight:bold;font-size:12pt;'>{$lang['index_announce']}</span></div><br />
   <table width='100%' border='1' cellspacing='0' cellpadding='5'>
   <tr><td bgcolor='transparent'><b><font color='red'>Announcement&nbsp;: 
   ".htmlspecialchars($ann_subject)."</font></b></td></tr>
   <tr><td style='padding: 10px; background:lightgrey'>
   ".format_comment($ann_body)."
   <br /><hr /><br />
   Click <a href='{$TBDEV['baseurl']}/clear_announcement.php'>
   <i><b>here</b></i></a> to clear this announcement.</td></tr></table></div><br />\n";
   }
   
   // === shoutbox 09
   if ($CURUSER['show_shout'] === "yes") {
   $commandbutton = '';
   $refreshbutton = '';
   $smilebutton = '';
   $custombutton = '';
   if(get_smile() != '0')
   $custombutton .="<span style='float:right;'><a href=\"javascript:PopCustomSmiles('shbox','shbox_text')\">{$lang['index_shoutbox_csmilies']}</a></span>";
   if ($CURUSER['class'] >= UC_STAFF){
   $commandbutton = "<span style='float:right;'><a href=\"javascript:popUp('shoutbox_commands.php')\">{$lang['index_shoutbox_commands']}</a></span>\n";}
   $refreshbutton = "<span style='float:right;'><a href='shoutbox.php' target='sbox'>{$lang['index_shoutbox_refresh']}</a></span>\n";
   $smilebutton = "<span style='float:right;'><a href=\"javascript:PopMoreSmiles('shbox','shbox_text')\">{$lang['index_shoutbox_smilies']}</a></span>\n";
   $HTMLOUT .= "<form action='shoutbox.php' method='get' target='sbox' name='shbox' onsubmit='mysubmit()'>
   <div class='roundedCorners' style='text-align:left;width:80%;border:1px solid black;padding:5px;'>
	 <div style='background:transparent;height:25px;'><span style='font-weight:bold;font-size:12pt;'>{$lang['index_shout']}</span></div>
	 <br /><b>{$lang['index_shoutbox']}</b>&nbsp;[&nbsp;<a href='{$TBDEV['baseurl']}/shoutbox.php?show_shout=1&amp;show=no'><b>{$lang['index_shoutbox_close']}</b></a>&nbsp;]";
   if ($CURUSER['class'] >= UC_STAFF){
   $HTMLOUT .= "[&nbsp;<a href='{$TBDEV['baseurl']}/admin.php?action=shistory'><b>{$lang['index_shoutbox_history']}</b></a>&nbsp;]";
   }
   $HTMLOUT .= "<iframe src='{$TBDEV['baseurl']}/shoutbox.php' width='100%' height='200' frameborder='0' name='sbox' marginwidth='0' marginheight='0'></iframe>
   <br/>
   <br/>
	 <div align='center'>
   <b>{$lang['index_shoutbox_shout']}</b>
   <input type='text' maxlength='680' name='shbox_text' size='1' style='width:500px;' />
   <input class='button' type='submit' value='{$lang['index_shoutbox_send']}' />
   <input type='hidden' name='sent' value='yes' />
   <br />
	 <a href=\"javascript:SmileIT(':-)','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/smile1.gif' alt='Smile' title='Smile' /></a> 
   <a href=\"javascript:SmileIT(':smile:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/smile2.gif' alt='Smiling' title='Smiling' /></a> 
   <a href=\"javascript:SmileIT(':-D','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/grin.gif' alt='Grin' title='Grin' /></a> 
   <a href=\"javascript:SmileIT(':lol:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/laugh.gif' alt='Laughing' title='Laughing' /></a> 
   <a href=\"javascript:SmileIT(':w00t:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/w00t.gif' alt='W00t' title='W00t' /></a> 
   <a href=\"javascript:SmileIT(':blum:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/blum.gif' alt='Rasp' title='Rasp' /></a> 
   <a href=\"javascript:SmileIT(';-)','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/wink.gif' alt='Wink' title='Wink' /></a> 
   <a href=\"javascript:SmileIT(':devil:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/devil.gif' alt='Devil' title='Devil' /></a> 
   <a href=\"javascript:SmileIT(':yawn:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/yawn.gif' alt='Yawn' title='Yawn' /></a> 
   <a href=\"javascript:SmileIT(':-/','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/confused.gif' alt='Confused' title='Confused' /></a> 
   <a href=\"javascript:SmileIT(':o)','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/clown.gif' alt='Clown' title='Clown' /></a> 
   <a href=\"javascript:SmileIT(':innocent:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/innocent.gif' alt='Innocent' title='innocent' /></a> 
   <a href=\"javascript:SmileIT(':whistle:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/whistle.gif' alt='Whistle' title='Whistle' /></a> 
   <a href=\"javascript:SmileIT(':unsure:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/unsure.gif' alt='Unsure' title='Unsure' /></a> 
   <a href=\"javascript:SmileIT(':blush:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/blush.gif' alt='Blush' title='Blush' /></a> 
   <a href=\"javascript:SmileIT(':hmm:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/hmm.gif' alt='Hmm' title='Hmm' /></a> 
   <a href=\"javascript:SmileIT(':hmmm:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/hmmm.gif' alt='Hmmm' title='Hmmm' /></a> 
   <a href=\"javascript:SmileIT(':huh:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/huh.gif' alt='Huh' title='Huh' /></a> 
   <a href=\"javascript:SmileIT(':look:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/look.gif' alt='Look' title='Look' /></a> 
   <a href=\"javascript:SmileIT(':rolleyes:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/rolleyes.gif' alt='Roll Eyes' title='Roll Eyes' /></a> 
   <a href=\"javascript:SmileIT(':kiss:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/kiss.gif' alt='Kiss' title='Kiss' /></a> 
   <a href=\"javascript:SmileIT(':blink:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/blink.gif' alt='Blink' title='Blink' /></a> 
   <a href=\"javascript:SmileIT(':baby:','shbox','shbox_text')\"><img border='0' src='{$TBDEV['pic_base_url']}smilies/baby.gif' alt='Baby' title='Baby' /></a><br/>
	 </div>
	 <div style='background:transparent;height:25px;'><span style='font-weight:bold;font-size:8pt;'>{$refreshbutton}</span></div>
   <div style='background:transparent;height:25px;'><span style='font-weight:bold;font-size:8pt;'>{$commandbutton}</span></div>
   <div style='background:transparent;height:25px;'><span style='font-weight:bold;font-size:8pt;'>{$smilebutton}</span></div>
   <div style='background:transparent;height:25px;'><span style='font-weight:bold;font-size:8pt;float:right'>{$custombutton}</span></div>
	 </div>
   </form><br />\n";
   }
   if ($CURUSER['show_shout'] === "no") {
   $HTMLOUT .="<div class='roundedCorners' style='text-align:left;width:80%;border:1px solid black;padding:5px;'><div style='background:transparent;height:25px;'><b>{$lang['index_shoutbox']}&nbsp;</b>[&nbsp;<a href='{$TBDEV['baseurl']}/shoutbox.php?show_shout=1&amp;show=yes'><b>{$lang['index_shoutbox_open']}&nbsp;]</b></a></div></div><br />";
   }
   //==end 09 shoutbox
   
    //==09 Cached News
    $news2  = '';
    $adminbutton = '';
    if ($CURUSER['class'] >= UC_MODERATOR){
    $adminbutton = "<span style='float:right;'><a href='admin.php?action=news'>News page</a></span>\n";
    }
    $HTMLOUT.="<div class='roundedCorners' style='text-align:left;width:80%;border:1px solid black;padding:5px;'>
    <div style='background:transparent;height:25px;'><span style='font-weight:bold;font-size:12pt;'>{$lang['news_title']}</span>{$adminbutton}</div>";
    
    $prefix = 'ChangeMe';
    $news = $mc1->get_value('latest_news_');
    if($news === false ) {
    $res = sql_query("SELECT ".$prefix.".id, ".$prefix.".userid, ".$prefix.".added, ".$prefix.".title, ".$prefix.".body, ".$prefix.".sticky, u.username FROM news AS ".$prefix." LEFT JOIN users AS u ON u.id = ".$prefix.".userid WHERE ".$prefix.".added + ( 3600 *24 *45 ) > ".time()." ORDER BY sticky, ".$prefix.".added DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);
    while ($array = mysql_fetch_assoc($res) ) 
    $news[] = $array;
    $mc1->cache_value('latest_news_', $news, 0);
    }
    $news_flag = 0;
    if ($news)
    {
    foreach ($news as $array)
    {
    $button='';
    if ($CURUSER['class'] >= UC_MODERATOR)
    {
    $hash = md5('the@@saltto66??' . $array['id']. 'add' . '@##mu55y==');
    $button = "<br /><div style='float:right;'><a href='admin.php?action=news&amp;mode=edit&amp;newsid={$array['id']}&amp;returnto=".urlencode($_SERVER['PHP_SELF'])."'><img src='{$TBDEV['pic_base_url']}button_edit2.gif' border='0' alt=\"Edit news\"  title=\"Edit news\" /></a>&nbsp;<a href='admin.php?action=news&amp;mode=delete&amp;newsid={$array['id']}&amp;h=$hash&amp;returnto=".urlencode($_SERVER['PHP_SELF'])."'><img src='{$TBDEV['pic_base_url']}del.png' border='0' alt=\"Delete news\" title=\"Delete news\" /></a></div>";
    }
    $HTMLOUT .= "<div style='background:transparent;height:20px;'><span style='font-weight:bold;font-size:10pt;'>";
    if ($news_flag < 2) {
    $HTMLOUT .="<a href=\"javascript: klappe_news('a".$array['id']."')\"><img border=\"0\" src='pic/plus.gif' id=\"pica".$array['id']."\" alt=\"Show/Hide\" />" . " - " .get_date( $array['added'],'DATE') . " - " ."{$array['title']}</a></span>{$button}</div>";
    $HTMLOUT .="<div id=\"ka".$array['id']."\" style=\"display:".($array["sticky"] == "yes" ? "" : "none").";margin-left:30px;margin-top:10px;\"> ".format_comment($array["body"],0)." </div><br /> ";

    $news_flag = ($news_flag + 1);
    }
    else {
    $HTMLOUT .="<a href=\"javascript: klappe_news('a".$array['id']."')\"><img border=\"0\" src='pic/plus.gif' id=\"pica".$array['id']."\" alt=\"Show/Hide\" />" . " - " .get_date( $array['added'],'DATE') . " - " ."{$array['title']}</a></span>{$button}</div>";
    $HTMLOUT .="<div id=\"ka".$array['id']."\" style=\"display:".($array["sticky"] == "yes" ? "" : "none").";margin-left:30px;margin-top:10px;\"> ".format_comment($array["body"],0)." </div><br /> ";

    }
    $HTMLOUT .= "<div style='margin-top:10px;padding:5px;'></div><hr />\n";
    }
    $HTMLOUT .= "</div><br />\n";
    }
    if (empty($news))
    $HTMLOUT .= "Currently No News</div><br />\n";
    //==End
   
        //== Latest forum posts [set limit from config]
	      $HTMLOUT .= "<div class='roundedCorners' style='text-align:left;width:80%;border:1px solid black;padding:5px;'>
	      <div style='background:transparent;height:25px;'><span style='font-weight:bold;font-size:12pt;'>{$lang['latestposts_title']}</span></div><br />";
        $page = 1;
        $num = 0;
        $topics = $mc1->get_value('last_posts_');
        if($topics === false ) {
        $topicres = sql_query("SELECT t.id, t.user_id, t.topic_name, t.locked, t.forum_id, t.last_post, t.sticky, t.views, f.min_class_read, f.name ".
        ", (SELECT COUNT(id) FROM posts WHERE topic_id=t.id) AS p_count ".
        ", p.user_id AS puser_id, p.added ".
        ", u.id AS uid, u.username ".
        ", u2.username AS u2_username ".
        "FROM topics AS t ".
        "LEFT JOIN forums AS f ON f.id = t.forum_id ".
        "LEFT JOIN posts AS p ON p.id=(SELECT MAX(id) FROM posts WHERE topic_id = t.id) ".
        "LEFT JOIN users AS u ON u.id=p.user_id ".
        "LEFT JOIN users AS u2 ON u2.id=t.user_id ".
        "WHERE f.min_class_read <= ".$CURUSER['class']." ".
        "ORDER BY t.last_post DESC LIMIT {$TBDEV['latest_posts_limit']}") or sqlerr(__FILE__, __LINE__);
        while($topic = mysql_fetch_assoc($topicres))
        $topics[] = $topic;
        $mc1->cache_value('last_posts_', $topics, $TBDEV['expires']['latestposts']);
        }
        if (count($topics) > 0) {
        $HTMLOUT .= "<table width='100%' cellspacing='0' cellpadding='5'><tr>
        <td align='left' class='colhead'>{$lang['latestposts_topic_title']}</td>
        <td align='center' class='colhead'>{$lang['latestposts_replies']}</td>
        <td align='center' class='colhead'>{$lang['latestposts_views']}</td>
        <td align='center' class='colhead'>{$lang['latestposts_last_post']}</td></tr>";
        if ($topics)
        {
        foreach($topics as $topicarr) {
	      $topicid = 0+$topicarr['id'];
	      $topic_userid = 0+$topicarr['user_id'];
 	      $perpage = $CURUSER['postsperpage'];;
 	      if (!$perpage)
 	      $perpage = 24;
 	      $posts = 0+$topicarr['p_count'];
 	      $replies = max(0, $posts - 1);
      	$first = ($page * $perpage) - $perpage + 1;
      	$last = $first + $perpage - 1;
 	      if ($last > $num)
 	      $last = $num;
 	      $pages = ceil($posts / $perpage);
 	      $menu = '';
 	      for ($i = 1; $i <= $pages; $i++) {
 	      if($i == 1 && $i != $pages){
 	      $menu .= "[ ";
 	      }
 	      if ($pages > 1){
 	      $menu .= "<a href='/forums.php?action=view_topic&amp;topic_id=$topicid&amp;page=$i'>$i</a>\n";
 	      }
 	      if ($i < $pages) {
 	      $menu .= "|\n";
 	      }
 	      if($i == $pages && $i > 1){
 	      $menu .= "]";
 	      }
 	      }

 	      $added = get_date($topicarr['added'],'',0,1);
 	      $username = "".(!empty($topicarr['username']) ? "<a href='/userdetails.php?id=".(int)$topicarr['puser_id']."'><b>".htmlspecialchars($topicarr['username'])."</b></a>" : "<i>Unknown[$topic_userid]</i>")."";
	      $author = (!empty($topicarr['u2_username']) ? "<a href='/userdetails.php?id=$topic_userid'><b>".htmlspecialchars($topicarr['u2_username'])."</b></a>" : ($topic_userid == '0' ? "<i>System</i>" : "<i>Unknown[$topic_userid]</i>"));
	      $staffimg = ($topicarr['min_class_read'] >= UC_STAFF ? "<img src='".$TBDEV['pic_base_url']."staff.png' border='0' alt='Staff forum' title='Staff Forum' />" : '');
	      $stickyimg = ($topicarr['sticky'] == 'yes' ? "<img src='".$TBDEV['pic_base_url']."sticky.gif' border='0' alt='Sticky' title='Sticky Topic' />&nbsp;&nbsp;" : '');
	      $lockedimg = ($topicarr['locked'] == 'yes' ? "<img src='".$TBDEV['pic_base_url']."forumicons/locked.gif' border='0' alt='Locked' title='Locked Topic' />&nbsp;" : '');
        $topic_name = $lockedimg.$stickyimg."<a href='/forums.php?action=view_topic&amp;topic_id=$topicid&amp;page=last#".(int)$topicarr['last_post']."'><b>" . htmlspecialchars($topicarr['topic_name']) . "</b></a>&nbsp;&nbsp;$staffimg&nbsp;&nbsp;$menu<br /><font class='small'>in <a href='forums.php?action=view_forum&amp;forum_id=".(int)$topicarr['forum_id']."'>".htmlspecialchars($topicarr['name'])."</a>&nbsp;by&nbsp;$author&nbsp;&nbsp;($added)</font>";
        $HTMLOUT .="<tr><td>{$topic_name}</td><td align='center'>{$replies}</td><td align='center'>".number_format($topicarr['views'])."</td><td align='center'>{$username}</td></tr>";
        }
        $HTMLOUT .= "</table></div><br />\n";
        } else {
        //if there are no posts...
        if (empty($topics))
        $HTMLOUT .= "<tr><td colspan='4'>{$lang['latestposts_no_posts']}</td></tr></table></div><br />\n";
        }
        }
        //end latest forum posts
        
        //==Installer 09 stats
        $HTMLOUT .="<div class='roundedCorners' style='text-align:left;width:80%;border:1px solid black;padding:5px;'>
        <div style='background:transparent;height:25px;'><span style='font-weight:bold;font-size:12pt;'>{$lang['index_stats_title']}</span></div><br />
        <!--<a href=\"javascript: klappe_news('a3')\"><img border=\"0\" src=\"pic/plus.gif\" id=\"pica3\" alt=\"[Hide/Show]\" /></a><div id=\"ka3\" style=\"display: none;\">-->    
        <table width='100%' border='1' cellspacing='0' cellpadding='10'><tr><td align='center'>
        <table class='main' border='1' cellspacing='0' cellpadding='5'>
        <tr>
	      <td class='rowhead'>{$lang['index_stats_regged']}</td><td align='right'>{$stats_cache['regusers']}/{$TBDEV['maxusers']}</td>
	      <td class='rowhead'>{$lang['index_stats_online']}</td><td align='right'>{$stats_cache['numactive']}</td>
        </tr>
        <tr>
	      <td class='rowhead'>{$lang['index_stats_uncon']}</td><td align='right'>{$stats_cache['unconusers']}</td>
	      <td class='rowhead'>{$lang['index_stats_donor']}</td><td align='right'>{$stats_cache['donors']}</td>
        </tr>
        <tr>
	      <td colspan='4'> </td>
        </tr>
        <tr>
	      <td class='rowhead'>{$lang['index_stats_topics']}</td><td align='right'>{$stats_cache['forumtopics']}</td>
	      <td class='rowhead'>{$lang['index_stats_torrents']}</td><td align='right'>{$stats_cache['torrents']}</td>
        </tr>
        <tr>
        <td class='rowhead'>{$lang['index_stats_posts']}</td><td align='right'>{$stats_cache['forumposts']}</td>
	      <td class='rowhead'>{$lang['index_stats_newtor']}</td><td align='right'>{$stats_cache['torrentstoday']}</td>
        </tr>
        <tr>
        <td colspan='4'> </td>
        </tr>
        <tr>
	      <td class='rowhead'>{$lang['index_stats_peers']}</td><td align='right'>{$stats_cache['peers']}</td>
	      <td class='rowhead'>{$lang['index_stats_unconpeer']}</td><td align='right'>{$stats_cache['unconnectables']}</td>
        </tr>
        <tr>
	      <td class='rowhead'>{$lang['index_stats_seeders']}</td><td align='right'>{$stats_cache['seeders']}</td>
	      <td class='rowhead' align='right'><b>{$lang['index_stats_unconratio']}</b></td><td align='right'><b>".round($stats_cache['ratiounconn'] * 100)."</b></td>
        </tr>
        <tr>
	      <td class='rowhead'>{$lang['index_stats_leechers']}</td><td align='right'>{$stats_cache['leechers']}</td>
	      <td class='rowhead'>{$lang['index_stats_slratio']}</td><td align='right'>".round($stats_cache['ratio'] * 100)."</td>
        </tr></table></td></tr></table></div><br /><!--</div>-->";
        //==End 09 stats
      
     //==Start activeusers - pdq
     $keys['activeusers']    = 'activeusers';
     $active_users_cache = $mc1->get_value($keys['activeusers']);
     if ($active_users_cache === false) {
     $dt = $_SERVER['REQUEST_TIME'] - 180;                       
     $activeusers = '';
     $active_users_cache = array();
     $res = sql_query('SELECT id, username, class, donor, title, warned, enabled, chatpost, leechwarn, pirate, king '.
              'FROM users WHERE last_access >= '.$dt.' '.
              'ORDER BY username ASC') or sqlerr(__FILE__, __LINE__);
     $actcount = mysql_num_rows($res);
     while ($arr = mysql_fetch_assoc($res)) {
      if ($activeusers) 
      $activeusers .= ",\n";
      $activeusers .= '<b>'.format_username($arr).'</b>';
     }
     $active_users_cache['activeusers'] = $activeusers;
     $active_users_cache['actcount']    = $actcount;
     $mc1->cache_value($keys['activeusers'] , $active_users_cache, $TBDEV['expires']['activeusers']);
     }
     if (!$active_users_cache['activeusers'])
     $active_users_cache['activeusers'] = 'There have been no active users in the last 15 minutes.';
     $active_users = '<div class="roundedCorners" style="text-align:left;width:80%;border:1px solid black;padding:5px;">
     <div style="background:transparent;height:25px;"><span style="font-weight:bold;font-size:12pt;"><a href="#active_users">'.$lang['index_active'].'</a>&nbsp;('.$active_users_cache['actcount'].')</span></div><br />
     <table width="100%" border="1" cellspacing="0" cellpadding="10">
     <tr><td class="text">'.$active_users_cache['activeusers'].'</td></tr>
     </table></div><br />';
     $HTMLOUT .= $active_users;
     //== end activeusers

     //== Last24 start - pdq
     $keys['last24'] = 'last24';
     $last24_cache = $mc1->get_value($keys['last24']);
     if ($last24_cache === false) {
     $last24_cache  = array();
     $time24 = $_SERVER['REQUEST_TIME'] - 86400;
     $activeusers24 = '';
     $arr = mysql_fetch_assoc(sql_query('SELECT * FROM avps WHERE arg = "last24"'));
     $res = sql_query('SELECT id, username, class, donor, title, warned, enabled, chatpost, leechwarn, pirate, king '.
                  'FROM users WHERE last_access >= '.$time24.' '.
                  'ORDER BY username ASC') or sqlerr(__FILE__, __LINE__);
     $totalonline24 = mysql_num_rows($res);
     $_ss24 = ($totalonline24 != 1 ? 's' : '');
     $last24record  = get_date($arr['value_u'], ''); 
     $last24 = $arr['value_i'];
     if ($totalonline24 > $last24) {
      $last24 = $totalonline24;
      $period = $_SERVER['REQUEST_TIME'];
      mysql_query('UPDATE avps SET value_s = 0, '.
                  'value_i = '.$last24.', '.
                  'value_u = '.$period.' '.
                  'WHERE arg = "last24"') or sqlerr(__FILE__, __LINE__);
     }
     while ($arr = mysql_fetch_assoc($res)) {
     if ($activeusers24) 
     $activeusers24 .= ",\n";
     $activeusers24 .= '<b>'.format_username($arr).'</b>';
     }
     $last24_cache['activeusers24'] = $activeusers24;
     $last24_cache['totalonline24'] = number_format($totalonline24);
     $last24_cache['last24record'] = $last24record;
     $last24_cache['last24'] = number_format($last24);
     $last24_cache['ss24'] = $_ss24;
     $mc1->cache_value($keys['last24'], $last24_cache, $TBDEV['expires']['last24']);
     }
     if (!$last24_cache['activeusers24'])
     $last24_cache['activeusers24'] = 'There&nbsp;have&nbsp;been&nbsp;no&nbsp;active&nbsp;users&nbsp;in&nbsp;the&nbsp;last&nbsp;15&nbsp;minutes.';
     $last_24 = '<div class=\'roundedCorners\' style=\'text-align:left;width:80%;border:1px solid black;padding:5px;\'>
     <div style=\'background:transparent;height:25px;\'><span style=\'font-weight:bold;font-size:12pt;\'>'.$lang['index_active24'].'<small>&nbsp;-&nbsp;List&nbsp;updated&nbsp;hourly</small></span></div><br />
     <table width="100%" border="0" cellspacing="0" cellpadding="5">
     <tr><td class="text">
     <p align="center"><b>'.$last24_cache['totalonline24'].' Member'.$last24_cache['ss24'].' visited during the last 24 hours</b></p>
     <p align="center">'.$last24_cache['activeusers24'].'</p>
     <p align="center"><b>Most ever visited in 24 hours was '.$last24_cache['last24'].' Member'.$last24_cache['ss24'].' on '.
     $last24_cache['last24record'].'</b></p>
     </td></tr></table></div><br />';
     $HTMLOUT .= $last_24;
     //== last24 end
    
     //==Poll
    $HTMLOUT .= parse_poll();

    
    //== 09 Donation progress
    $progress='';
    $totalfunds_cache = $mc1->get_value('totalfunds_');
    if ($totalfunds_cache === false) {
    $totalfunds_cache =  mysql_fetch_assoc(sql_query("SELECT sum(cash) as total_funds FROM funds"))/* or sqlerr(__FILE__, __LINE__)*/;
    $totalfunds_cache["total_funds"] = (int)$totalfunds_cache["total_funds"];
    $mc1->cache_value('totalfunds_', $totalfunds_cache, $TBDEV['expires']['total_funds']);
    }
    $funds_so_far = (int)$totalfunds_cache["total_funds"];
    $totalneeded = 50;    //=== set this to your monthly wanted amount
    $funds_difference = $totalneeded - $funds_so_far;
    $Progress_so_far = number_format($funds_so_far / $totalneeded * 100, 1);
    if($Progress_so_far >= 100)
    $Progress_so_far = 100;
    $HTMLOUT .="<div class='roundedCorners' style='text-align:left;width:80%;border:1px solid black;padding:5px;'>
    <div style='background:transparent;height:25px;'><span style='font-weight:bold;font-size:12pt;'>{$lang['index_donations']}</span></div><br /><div align='center'><a href='{$TBDEV['baseurl']}/donate.php'>
    <img border='0' src='{$TBDEV['pic_base_url']}makedonation.gif' alt='Donate' title='Donate'  /></a><br /><br />
    <table align='center' width='140' style='height: 20%;' border='2'><tr>
    <td bgcolor='transparent' align='center' valign='middle' width='$Progress_so_far%'>$Progress_so_far%</td><td bgcolor='grey' align='center' valign='middle'></td></tr></table></div></div><br />";
    //end
    

    /*
    $xmasday= mktime(0,0,0,12,25,date("Y"));
    $today = mktime(date("G"), date("i"), date("s"), date("m"),date("d"),date("Y"));
    if ($CURUSER["gotgift"] == 'no' && $today <> $xmasday) {
    $HTMLOUT .="<div class='roundedCorners' style='text-align:left;width:80%;border:1px solid black;padding:5px;'>
    <div style='background:transparent;height:25px;'><span style='font-weight:bold;font-size:12pt;'>Xmas Gift</span></div><br /><div align='center'><a href='{$TBDEV['baseurl']}/gift.php?open=1'><img src='{$TBDEV['pic_base_url']}gift.png' style='float: center;border-style: none;' alt='Xmas Gift' title='Xmas Gift' /></a><br /><br /><br /><br /></div></div><br />";
    }
    */
    //==Disclaimer
    $HTMLOUT .= "<div class='roundedCorners' style='text-align:left;width:80%;border:1px solid black;padding:5px;'>
    <div style='background:transparent;height:25px;'><span style='font-weight:bold;font-size:12pt;'>{$lang['index_disclaimer']}</span></div><br />";
    $HTMLOUT .= sprintf("<p><font class='small'>{$lang['foot_disclaimer']}</font></p>", $TBDEV['site_name']);
    $HTMLOUT .= "</div>";

///////////////////////////// FINAL OUTPUT //////////////////////
print stdhead('Home') . $HTMLOUT . stdfoot($stdfoot);
?>