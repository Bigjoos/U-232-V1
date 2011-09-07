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
require_once INCL_DIR.'html_functions.php';
dbconn();

loggedinorreturn();
    
    $lang = array_merge( load_language('global'), load_language('staff') );
    
    $htmlout = '';
    
    $query = mysql_query("SELECT users.id, username, email, support, supportfor, last_access, class, title, country, status, countries.flagpic, countries.name FROM users LEFT  JOIN countries ON countries.id = users.country WHERE class >=".UC_MODERATOR." AND status='confirmed' ORDER BY username") or sqlerr();

    while($arr2 = mysql_fetch_assoc($query)) {
      
    /*	if($arr2["class"] == UC_VIP)
        $vips[] =  $arr2;
    */	
      if($arr2["class"] == UC_MODERATOR)
        $mods[] =  $arr2;
        
      if($arr2["class"] == UC_ADMINISTRATOR)
        $admins[] =  $arr2;
        
      if($arr2["class"] == UC_SYSOP)
        $sysops[] =  $arr2;
      
     if($arr2['support'] == 'yes' && $arr2['class'] < UC_MODERATOR)
        $fls[] =  $arr2;
      }
    /*
    print_r($sysops);
    print("<br />");
    print_r($admins);
    print("<br />");
    print_r($mods);
    print("<br />");
    print(count($mods));
    */
    
    function DoStaff($staff, $staffclass, $cols = 2) 
    {
      global $TBDEV, $lang;
      
      $dt = time() - 180;
      $htmlout = '';
      
      if($staff===false) 
      {
        $htmlout .= "<br /><table algin='center' width='75%' border='1' cellpadding='3'>";
        $htmlout .= "<tr><td class=''><h2>{$staffclass}</h2></td></tr>";
        $htmlout .= "<tr><td>{$lang['text_none']}</td></tr></table>";
        return;
      }
      $counter = count($staff);
        
      $rows = ceil($counter/$cols);
      $cols = ($counter < $cols) ? $counter : $cols;
      //echo "<br />" . $cols . "   " . $rows;
      $r = 0;
      $htmlout .= "<br /><table width='75%' border='1' cellpadding='3'>";
      $htmlout .= "<tr><td class='colhead' colspan='{$counter}'><h2>{$staffclass}</h2></td></tr>";
      
      for($ia = 0; $ia < $rows; $ia++)
      {

            $htmlout .= "<tr>";
            for($i = 0; $i < $cols; $i++)
            {
              if( isset($staff[$r]) )  
              {
                $htmlout .= "<td class=''><a href='userdetails.php?id={$staff[$r]['id']}'><b>".$staff[$r]["username"]."</b></a>".
          "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img style='vertical-align: middle;' src='{$TBDEV['pic_base_url']}staff".
          ($staff[$r]['last_access']>$dt?"/user_online.gif":"/user_offline.gif" )."' border='0' alt='' />
          "."<a href='{$TBDEV['baseurl']}/sendmessage.php?receiver={$staff[$r]['id']}'>".
          "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img style='vertical-align: middle;' src='{$TBDEV['pic_base_url']}pm.gif' border='0' title=\"{$lang['alt_pm']}\" alt='' /></a>".
          "<a href='{$TBDEV['baseurl']}/email-gateway.php?id={$staff[$r]['id']}'>".
          "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img style='vertical-align: middle;' src='{$TBDEV['pic_base_url']}pm.gif' border='0' alt='{$staff[$r]['username']}' title=\"{$lang['alt_sm']}\" /></a>".
          "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img style='vertical-align: middle;' src='{$TBDEV['pic_base_url']}flag/{$staff[$r]['flagpic']}' border='0' alt='{$staff[$r]['name']}' /></td>";
          $r++;
              }
              else
              {
                $htmlout .= "<td>&nbsp;</td>";
              }
            }
            $htmlout .= "</tr>";
        
      }
      $htmlout .= "</table>";
    /*
    print("</table>");
    print("<br /><table border=1><tr>");
    for ($i = 0; $i <= count($staff)-1; $i++) {
        print("<td>{$staff[$i]["username"]}</td>");
        }
        print("</tr></table>");
    */
    
 
      return $htmlout;
   
    }

    


    $htmlout .= "<h1>{$lang['text_staff']}</h1>";

    $htmlout .= DoStaff($sysops, "{$lang['header_sysops']}");
    $htmlout .= isset($admins) ? DoStaff($admins, "{$lang['header_admins']}") : DoStaff($admins=false, "{$lang['header_admins']}");
    $htmlout .= isset($mods) ? DoStaff($mods, "{$lang['header_mods']}") : DoStaff($mods=false, "{$lang['header_mods']}");
    $htmlout .= isset($fls) ? DoStaff($fls, "{$lang['header_fls']}<br /><br />{$lang['staff_asup']}") : DoStaff($fls=false, "{$lang['header_fls']}");
    //$htmlout .= isset($vips) ? DoStaff($vips, "{$lang['header_vips']}") : DoStaff($vips=false, "{$lang['header_vips']}");


    print stdhead("{$lang['stdhead_staff']}") . $htmlout . stdfoot();

?>