#!/usr/bin/php
<?
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
$vars['Verbosity']=1;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="DSE IP Throttler";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="cli level IP throttling";
$vars['DSE']['SCRIPT_VERSION']="v0.01b";
$vars['DSE']['SCRIPT_VERSION_DATE']="2012/05/28";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

$parameters_details = array(
  array('h','help',"this message"),
  array('q','quiet',"same as --verbosity 0"),
  array('y','verbosity:',"0=none 1=some 2=more 3=debug"),
  array('i','info',"info about <IP>"),
  array('l','log',"log request from <IP>"),
  array('b','block',"add block for request from <IP>"),
  array('c','count',"return count of requests from <IP> recently"),
);
$vars['parameters']=dse_cli_get_paramaters_array($parameters_details);
$vars['Usage']=dse_cli_get_usage($parameters_details);
$vars['argv_origional']=$argv;
dse_cli_script_start();
		
		
		//$vars['DSE']['DSE_IPTHROTTLE_LOG_DIRECTORY']
		
$t1=time();
$TimeStr1=date("YmdGi",$t1);
$LogFile1=$vars['DSE']['DSE_IPTHROTTLE_LOG_DIRECTORY']."/".$TimeStr1.".log";
$t2=$t1-60;
$TimeStr2=date("YmdGi",$t2);
$LogFile2=$vars['DSE']['DSE_IPTHROTTLE_LOG_DIRECTORY']."/".$TimeStr2.".log";
		
$BackupBeforeUpdate=TRUE;
foreach (array_keys($vars['options']) as $opt) switch ($opt) {
	case 'q':
	case 'quiet':
		$Quiet=TRUE;
		$vars['Verbosity']=0;
		break;
  	case 'y':
	case 'verbosity':
		$vars['Verbosity']=$vars['options'][$opt];
		if($vars['Verbosity']>=2) print "Verbosity set to ".$vars['Verbosity']."\n";
		break;
	case 'h':
  	case 'help':
  		$ShowUsage=TRUE;
		$DidSomething=TRUE;
		break;
		
	case 'b':
	case 'block':
		if(sizeof($argv)==1){
			print "No IP given. Exiting.\n";
			exit(-1);
		}
		$IP=$argv[1];
		if(!file_exists($vars['DSE']['DSE_IPTHROTTLE_DROPLIST_FILE'])){
			print "Droplist file (".$vars['DSE']['DSE_IPTHROTTLE_DROPLIST_FILE'].") missing. Exiting.\n";
			exit(-3);
		}
		$c="grep $IP ".$vars['DSE']['DSE_IPTHROTTLE_DROPLIST_FILE'];
		$r=`$c`;
		if($c=="$IP\n"){
			print "$IP Already Blocked.\n";
			exit(0);
		}
		$c="echo \"$IP\" >> ".$vars['DSE']['DSE_IPTHROTTLE_DROPLIST_FILE'];
		$r=`$c`;
		print "$IP Blocked.\n";
		exit(0);
		break;
	case 'l':
	case 'log':
		if(sizeof($argv)==1){
			print "No IP given. Exiting.\n";
			exit(-1);
		}
		$IP=$argv[1];
		$c="echo \"$IP\" >> $LogFile1";
		$r=`$c`;
		//exit(0);
		//break;
	case 'c':
	case 'count':
		if(sizeof($argv)==1){
			print "No IP given. Exiting.\n";
			exit(-1);
		}
		$IP=$argv[1];
		$c="grep \"^$IP\$\" $LogFile1 2>/dev/null | wc -l";
		$r=trim(`$c`);
		$c="grep \"^$IP\$\" $LogFile2 2>/dev/null | wc -l";
		$r2=trim(`$c`)+$r;
		print "$r2\n";
		exit(0);
		break;
	case 'i':
	case 'info':
		if(sizeof($argv)==1){
			print "No IP given. Exiting.\n";
			exit(-1);
		}
		$IP=$argv[1];
		$Status="UNKNOWN";
		if(!file_exists($vars['DSE']['DSE_IPTHROTTLE_WHITELIST_FILE'])){
			print "Whitelist file (".$vars['DSE']['DSE_IPTHROTTLE_WHITELIST_FILE'].") missing. Exiting.\n";
			exit(-2);
		}
		if(!file_exists($vars['DSE']['DSE_IPTHROTTLE_DROPLIST_FILE'])){
			print "Droplist file (".$vars['DSE']['DSE_IPTHROTTLE_DROPLIST_FILE'].") missing. Exiting.\n";
			exit(-3);
		}
		$c="grep $IP ".$vars['DSE']['DSE_IPTHROTTLE_WHITELIST_FILE'];
		$r=`$c`;
		if($c=="$IP\n"){
			$Status="WHITELIST";
		}else{
			$c="grep $IP ".$vars['DSE']['DSE_IPTHROTTLE_DROPLIST_FILE'];
			$r=`$c`;
			if($c=="$IP\n"){
				$Status="DROPLIST";
			}	
			
		}
		print "$IP $Status\n";
		exit(0);
		break;

}

if($DoSetEnv){

	print "#!/bin/bash\n";
	print "export DSE_GIT_ROOT=".$vars['DSE']['DSE_GIT_ROOT']."\n";
	print "export DSE_MYSQL_CONF_FILE=".$vars['DSE']['MYSQL_CONF_FILE']."\n";
	print "export DSE_MYSQL_LOG_FILE=".$vars['DSE']['MYSQL_LOG_FILE']."\n";
	
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

dse_cli_script_header();

$vars['Usage'].= "";
	
 
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
	print $vars['Usage'];
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
	$DSE_GIT_ROOT=getenv("DSE_GIT_ROOT");
	if($DSE_GIT_ROOT){
		if(file_exists($DSE_GIT_ROOT)){
			$Command="/scripts/dse_git_pull 2>&1";
			$o=`$Command`;
			if(!$Quiet) print $o;
		}else{
			print "ERROR: DSE_GIT_ROOT=$DSE_GIT_ROOT does not exist.\n";
			exit -1;
		}
	}else{
		print "ERROR: DSE_GIT_ROOT unset.\n";
		exit -1;
	}
}

if($DidSomething){
	if(!$Quiet && !$DoSetEnv){
		print getColoredString($vars['DSE']['SCRIPT_NAME']." Done. Exiting (0)","black","green");
		$vars[shell_colors_reset_foreground]='';	print getColoredString("\n","white","black");
	}
	if(!$NoExit) exit(0);
}else{
	if(!$Quiet && !$DoSetEnv){
		print getColoredString("Nothing to do! try --help for usage. ".$vars['DSE']['SCRIPT_NAME']." Done. Exiting (-1)","pink","black");
		$vars[shell_colors_reset_foreground]='';	print getColoredString("\n","white","black");
	}
	if(!$NoExit) exit(-1);
}




	 

?>
