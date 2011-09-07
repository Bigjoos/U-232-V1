<?php
function db_test() {
  global $root, $TBDEV;
  $out = '<fieldset><legend>Database</legend>';
  require_once($root.'include/config.php');
  if(@mysql_connect($TBDEV['mysql_host'],$TBDEV['mysql_user'],$TBDEV['mysql_pass'],$TBDEV['mysql_db'])) {
   $out .= '<div class="readable">Connection to database was made</div>';
   if(mysql_select_db($TBDEV['mysql_db'])) {
     $out .= '<div class="readable">Data base exists, data can be imported</div>';
     $out .= '<form action="index.php" method="post"><div class="info" style="text-align:center;"><input type="hidden" name="do" value="db_insert" /><input type="submit" value="Import database" /></div></form>';
   }
   else
    $out .= '<div class="notreadable">There was an error while selecting the database<br/>'.mysql_error().'</div><div class="info" style="text-align:center"><input type="button" value="Reload" onclick="window.location.reload()"/></div>';
  } else 
   $out .= '<div class="notreadable">There was an error while connection to the database<br/>'.mysql_error().'</div><div class="info" style="text-align:center"><input type="button" value="Reload" onclick="window.location.reload()"/></div>';
  $out .= '</fieldset>';
  
  print($out);
}
function db_insert() {
 global $root, $TBDEV;
 $out = '<fieldset><legend>Database</legend>';
 require_once($root.'include/config.php');
 $q = sprintf('/usr/bin/mysql -h %s -u %s -p%s %s < %sinstall/extra/install.sql',$TBDEV['mysql_host'],$TBDEV['mysql_user'],$TBDEV['mysql_pass'],$TBDEV['mysql_db'],$root);
 //$q = sprintf('c:\AppServ\MySQL\bin\mysql -h %s -u %s -p%s %s < %sinstall/extra/install.sql',$TBDEV['mysql_host'],$TBDEV['mysql_user'],$TBDEV['mysql_pass'],$TBDEV['mysql_db'],$root);
  exec($q,$o);

 if(!count($o)) {
  $out .= '<div class="readable">Database was imported</div><div class="info" style="text-align:center"><input type="button" value="Finish" onclick="window.location.href=\'?step=3\'"/></div>';
  file_put_contents('step2.lock',1);
 }
 else
  $out .= '<div class="notreadable">There was an error while importing the database<br/>'.$o.'</div>';
  $out .= '</fieldset>';
 print($out);
}

?>
