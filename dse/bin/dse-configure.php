#!/usr/bin/php
<?
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	

include_once ("dse_cli_functions.php");
include_once ("dse_config.php");


$vars[shell_colors_reset_foreground]='light_grey';
$vars[shell_colors_reset_background]='black';
$Start=time();
$Verbosity=3;

$Script=$argv[0];

$ScriptName="dse";

$parameters = array(
  'h' => 'help',
  'v' => 'listvars',
  
);
$flag_help_lines = array(
  'h' => "\thelp - this message",
  'v' => "\listvars - list configuration variables",

);

$ScriptName_str=getColoredString($ScriptName, 'yellow', 'black');
	
$Usage="   $ScriptName_str - Devity Server Environment Managment Script
       by Louy of Devity.com


".getColoredString("command line usage:","yellow","black").
getColoredString(" dse","cyan","black").
getColoredString(" <args> (options)","dark_cyan","black")."     
";
foreach($parameters as $k=>$v){
	$k2=str_replace(":","",$k);
	$v2=str_replace(":","",$v);
	$Usage.=" -${k2}, --${v2}\t".$flag_help_lines[$k]."\n";
}
$Usage.="\n\n";

$StartLoad=get_load();

$options = _getopt(implode('', array_keys($parameters)),$parameters);
$pruneargv = array();
foreach ($options as $option => $value) {
  foreach ($argv as $key => $chunk) {
    $regex = '/^'. (isset($option[1]) ? '--' : '-') . $option . '/';
    if ($chunk == $value && $argv[$key-1][0] == '-' || preg_match($regex, $chunk)) {
      array_push($pruneargv, $key);
    }
  }
}
while ($key = array_pop($pruneargv)){
	deleteFromArray($argv,$key,FALSE,TRUE);
}


$IsSubprocess=FALSE;
foreach (array_keys($options) as $opt) switch ($opt) {
	case 'h':
  	case 'help':
  		$ShowUsage=TRUE;
		$DidSomething=TRUE;
		break;
	case 'v':
  	case 'listvars':
  		$ListVars=TRUE;
		$DidSomething=TRUE;
		break;

}


if($Verbosity>=2){
	//print getColoredString("","black","black");
	print getColoredString("    ########======-----________   ", 'light_blue', 'black');
	print getColoredString($ScriptName,"yellow","black");
	print getColoredString("   ______-----======########\n", 'light_blue', 'black');
	print "  ___________ ______ ___ __ _ _   _                      \n";
	print " /                           Configuration Settings\n";
	print "|  * Script: $Script\n";
	print "|  * Verbosity: $Verbosity\n";
	print " \________________________________________________________ __ _  _   _\n";
	//print "\n";  
}

if($argv[1]=="help"){
	print "  ________ ___ __ _  _    _
 / dse Commands:     ( /dse/bin  Scripts )
|     These scripts exist as their native language extension e.g. 'bottle_top.php' and as a soft link with no extension, in this case both 'bottle_top' and 'btop'
+------ ---- --- -- - - -  -   -     -
|  atime          - return unix time int of arg1's access time
|  backup_etc     - backs up /etc
   bh             - bash history grepper for arg1
   btop           - bottle top - system bottle-neck analyzer
|  dsizeof        - returns size in bytes of arg1
   dse            - script that sets dse variables, get's status, provieds help, etc
|  fss            - find string
   grep2exe       - returns script name for a string on ps output lines
   grep2pid       - returns PID for a string on ps output lines
|  http_stress    - multi-threaded web-site stress tester
   memcache-top   - top for memcache
   mysqltuner     - mysqld config analyzer based on http://github.com/rackerhacker/MySQLTuner-perl
   pid2exe        - returns the script name for a PID
   rpms_extract   - rebuilds as near as possible a .rpm file for an installed package
|  server_backup  - backup all server config and data
   server_log_status - saves a copy of the output of over a dozen commands like ps, lsof, vmtstat, nmap, iostat, printenv, etc
   server_monitor - server health monitor that takes actions (run scripts, send emails, etc) at various configurable thresholds
   vibk           - backup arg1 then edit with vi
 
";
	$DidSomething=TRUE;
}
	
if($ShowUsage){
	print $Usage;
}



if($ListVars){
	$Body="";
	//$Body.= " PATH = ".getenv("PATH")."\n";
	
	$vars['DSE']['PATH']=getenv("PATH");

	foreach($vars['DSE'] as $k=>$v){
		if($v){
			$Body.=" $k = ".getColoredString($v,"yellow","black")."\n";
		}
	}
  	
  	print dse_output_box("Config Variables:",$Body,"yellow","grey","light_grey");

}

if($DidSomething){
	$vars[shell_colors_reset_foreground]='';	print getColoredString("","white","black","black","black");
	exit(0);
}

//if($argv[1]=="help"){
	print $argv[1];
	
	exit(0);
//}




	 

?>
