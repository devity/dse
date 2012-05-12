#!/usr/bin/php
<?
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");



$Verbosity=0;


// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="DSE";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="main script of Devity Server Environment";
$vars['DSE']['DSE_DSE_VERSION']="v0.04b";
$vars['DSE']['DSE_DSE_VERSION_DATE']="2012/04/30";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******


$parameters_details = array(
  array('h','help',"this message"),
  array('q','quiet',"same as --verbosity 0"),
  array('u','update',"updates dse from github"),
  array('v','update-no-backup',"does a --update w/o backing up current dse install"),
  array('e','edit',"backs up and launches a vim of ".$vars['DSE']['DSE_CONFIG_FILE_GLOBAL']),
  array('s','set-env',"set shell environment variables"),
  array('','verbosity:',"0=none 1=some 2=more 3=debug"),
);
$parameters=dse_cli_get_paramaters_array($parameters_details);
$Usage=dse_cli_get_usage($parameters_details);





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
$BackupBeforeUpdate=TRUE;
foreach (array_keys($options) as $opt) switch ($opt) {
	case 'q':
	case 'quiet':
		$Quiet=TRUE;
		$Verbosity=0;
		break;
	case 'verbosity':
		$Verbosity=$options[$opt];
		if($Verbosity>=2) print "Verbosity set to $Verbosity\n";
		break;
	case 'h':
  	case 'help':
  		$ShowUsage=TRUE;
		$DidSomething=TRUE;
		break;
	case 'u':
  	case 'update':
  		$DoUpdate=TRUE;
		$DidSomething=TRUE;
		break;
	case 'v':
  	case 'update-no-backup':
		$BackupBeforeUpdate=FALSE;
  		$DoUpdate=TRUE;
		$DidSomething=TRUE;
		break;
	case 's':
  	case 'set-env':
  		$DoSetEnv=TRUE;
		$DidSomething=TRUE;
		break;
	
	case 'e':
	case 'edit':
		print "Backing up ".$vars['DSE']['DSE_CONFIG_FILE_GLOBAL']." and launcing in vim:\n";
		passthru("/dse/bin/vibk ".$vars['DSE']['DSE_CONFIG_FILE_GLOBAL']." 2>&1");
		$DidSomething=TRUE;
		break;

}

if($DoSetEnv){
	putenv ("DSE_MYSQL_CONF_FILE=".$vars['DSE']['MYSQL_CONF_FILE']);
	exec("export DSE_MYSQL_CONF_FILE");
	print "#!/bin/bash\n";
	print "DSE_MYSQL_CONF_FILE=".$vars['DSE']['MYSQL_CONF_FILE']."\nexport DSE_MYSQL_CONF_FILE\n";
	$NoExit=TRUE;
}

$EarlyExit=FALSE;
if($argv[1]=="configure"){
	$PassArgString=""; for($PassArgString_i=1;$PassArgString_i<sizeof($argv);$PassArgString_i++) $PassArgString.=" ".$argv[$PassArgString_i];
	print `/dse/bin/dse-configure $PassArgString`;
	$EarlyExit=TRUE;
}elseif($argv[1]=="install"){
	$PassArgString=""; for($PassArgString_i=1;$PassArgString_i<sizeof($argv);$PassArgString_i++) $PassArgString.=" ".$argv[$PassArgString_i];
	print exec("dse-install $PassArgString");
	print "44444 dse-install $PassArgString\n";
	$EarlyExit=TRUE;
}
if($EarlyExit){
	print getColoredString($vars['DSE']['SCRIPT_NAME']." Done. Exiting (0)","black","green");
	$vars[shell_colors_reset_foreground]='';	print getColoredString("\n","white","black");
	exit(0);
}

if($Verbosity>1){
	//print getColoredString("","black","black");
	print getColoredString("    ########======-----________   ", 'light_blue', 'black');
	print getColoredString($vars['DSE']['SCRIPT_NAME'],"yellow","black");
	print getColoredString("   ______-----======########\n", 'light_blue', 'black');
	print "  ___________ ______ ___ __ _ _   _                      \n";
	print " /                           Configuration Settings\n";
	print "|  * Script: ".$vars['DSE']['SCRIPT_FILENAME']."\n";
	print "|  * Verbosity: $Verbosity\n";
	print " \________________________________________________________ __ _  _   _\n";
	//print "\n";  
}

//print "argv[1]=$argv[1]\n";
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
 
if($argv[1]=="configure"){
	$PassArgString=""; for($PassArgString_i=1;$PassArgString_i<sizeof($argv);$PassArgString_i++) $PassArgString.=" ".$argv[$PassArgString_i];
	print `/dse/bin/dse-configure $PassArgString`;
	$DidSomething=TRUE;
}elseif($argv[1]=="install"){
	$PassArgString=""; for($PassArgString_i=1;$PassArgString_i<sizeof($argv);$PassArgString_i++) $PassArgString.=" ".$argv[$PassArgString_i];
	print exec("dse-install $PassArgString");
	print "44444 dse-install $PassArgString\n";
	$DidSomething=TRUE;
}

	
if($ShowUsage){
	print $Usage;
}
if($DoUpdate){
	$Date_str=date("YmdGis");
	if($BackupBeforeUpdate){
		$BackupDir=$vars['DSE']['DSE_BACKUP_DIR_DSE']."/".$Date_str."/dse";
		$Command="mkdir -p ".$BackupDir;
		//print "$Command\n";
		`$Command`;
	
		if(!$Quiet) print "Backing up ".$vars['DSE']['DSE_ROOT']." to $BackupDir\n";
		$Command="cp -rf ".$vars['DSE']['DSE_ROOT']." ".$BackupDir."/.";
		//print "$Command\n";
		`$Command`;
	}else{
		if(!$Quiet) print "Skipping backing up of current dse install.\n";
	}
	$Command="/scripts/dse_git_pull 2>&1";
	$o=`$Command`;
	if(!$Quiet) print $o;
}

if($DidSomething){
	if(!$Quiet){
		print getColoredString($vars['DSE']['SCRIPT_NAME']." Done. Exiting (0)","black","green");
		$vars[shell_colors_reset_foreground]='';	print getColoredString("\n","white","black");
	}
	if(!$NoExit) exit(0);
}else{
	if(!$Quiet){
		print getColoredString("Nothing to do! try --help for usage. ".$vars['DSE']['SCRIPT_NAME']." Done. Exiting (-1)","pink","black");
		$vars[shell_colors_reset_foreground]='';	print getColoredString("\n","white","black");
	}
	if(!$NoExit) exit(-1);
}




	 

?>
