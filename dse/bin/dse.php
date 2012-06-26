#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
$vars['Verbosity']=1;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="DSE";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="main script of Devity Server Environment";
$vars['DSE']['DSE_DSE_VERSION']="v0.04b";
$vars['DSE']['DSE_DSE_VERSION_DATE']="2012/04/30";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

$parameters_details = array(
  array('l','log-show:',"shows tail of log ".$vars['DSE']['LOG_FILE']),
  array('h','help',"this message"),
  array('q','quiet',"same as --verbosity 0"),
  array('u','update',"updates dse from github"),
  array('u','upgrade',"same as --update"),
  array('v','update-no-backup',"does a --update w/o backing up current dse install"),
  array('e','edit',"backs up and launches a vim of ".$vars['DSE']['DSE_CONFIG_FILE_GLOBAL']),
  array('w','config-show',"prints ".$vars['DSE']['DSE_CONFIG_FILE_GLOBAL']),
  array('','config-show',"prints contents of ".$vars['DSE']['DSE_CONFIG_FILE_GLOBAL']),
  array('i','install',"launches dse-install"),
  array('c','configure',"launches dse-configure"),
  array('s','set-env',"set shell environment variables"),
  array('y','verbosity:',"0=none 1=some 2=more 3=debug"),
  array('z:','status:',"shows status on all or arg1. options: [initd]"),
);
$vars['parameters']=dse_cli_get_paramaters_array($parameters_details);
$vars['Usage']=dse_cli_get_usage($parameters_details);
$vars['argv_origional']=$argv;
dse_cli_script_start();
		
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
	case 'l':
	case 'log-show':
		if($vars['options'][$opt]) $Lines=$vars['options'][$opt]; else $Lines=$vars['DSE']['LOG_SHOW_LINES'];
		$Command="tail -n $Lines ".$vars['DSE']['LOG_FILE'];
		//print "$Command\n";
		print `$Command`;
		$DidSomething=TRUE;
		break;
	case 'e':
	case 'edit':
		print "Backing up ".$vars['DSE']['DSE_CONFIG_FILE_GLOBAL']." and launcing in vim:\n";
		passthru("/dse/bin/vibk ".$vars['DSE']['DSE_CONFIG_FILE_GLOBAL']." 2>&1");
		$DidSomething=TRUE;
		break;
	case 'w':
  	case 'config-show':
		print dse_file_get_contents($vars['DSE']['DSE_CONFIG_FILE_GLOBAL']);
		$DidSomething=TRUE;
	case 'u':
  	case 'update':
  	case 'upgrade':
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
	
  	case 'i':
  	case 'install':
		$r=passthru($vars['DSE']['DSE_BIN_DIR']."/dse-install");
		if($r<0){
			if(!$Quiet && !$DoSetEnv){
				print getColoredString($vars['DSE']['SCRIPT_NAME']." FATAL ERROR. Exiting (0)","black","green");
				$vars[shell_colors_reset_foreground]='';	print getColoredString("\n","white","black");
			}
			exit(-1);
		}
		$DidSomething=TRUE;
		break;
  	case 'c':
  	case 'configure':
		$r=passthru($vars['DSE']['DSE_BIN_DIR']."/dse-configure");
		if($r<0){
			if(!$Quiet && !$DoSetEnv){
				print getColoredString($vars['DSE']['SCRIPT_NAME']." FATAL ERROR. Exiting (0)","black","green");
				$vars[shell_colors_reset_foreground]='';	print getColoredString("\n","white","black");
			}
			exit(-1);
		}
		$DidSomething=TRUE;
		break;
	
	case 'z':
  	case 'status':
		include_once ("/dse/bin/dse_config_functions.php");
  		if($vars['options'][$opt]){
  			switch($vars['options'][$opt]){
				case 'initd':
					$r=dse_initd_entry_get_info();
					print $r;
					break;
  			}
  		}
		$DidSomething=TRUE;
		break;

}

if($DoSetEnv){

	print "#!/bin/bash\n";
	print "export DSE_GIT_ROOT=".$vars['DSE']['DSE_GIT_ROOT']."\n";
	print "export DSE_MYSQL_CONF_FILE=".$vars['DSE']['MYSQL_CONF_FILE']."\n";
	print "export DSE_MYSQL_LOG_FILE=".$vars['DSE']['MYSQL_LOG_FILE']."\n";
	print "export DSE_HTTP_CONF_FILE=".$vars['DSE']['HTTP_CONF_FILE']."\n";
	print "export DSE_HTTP_ERROR_LOG_FILE=".$vars['DSE']['HTTP_ERROR_LOG_FILE']."\n";
	print "export DSE_HTTP_REQUEST_LOG_FILE=".$vars['DSE']['HTTP_REQUEST_LOG_FILE']."\n";
	
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

$vars['Usage'].= "\n  ________ ___ __ _  _    _
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
	//print `/dse/bin/img2txt /dse/images/logo.gif`;
	print `cat /dse/images/logo.ascii`;
	print $vars['Usage'];
}
if($DoUpdate){
	
	$Date_str=@date("YmdGis");
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
	/*fink selfupdate
fink selfupdate-rsync
fink index -f
fink selfupdate*/
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
