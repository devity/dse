#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
//$vars[dse_enable_debug_code]=TRUE; $vars['Verbosity']=6;
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
  array('x:','code-query:',"shows status of string arg1. grep is unknown string or more info if known as a file, function, of variable name"),
  array('a','reboot',"reboots the server"),
  array('b','halt',"halts the server (turns it off! use w/ caution. if you just need to disconnect it from the work because of an attack, run: ".
  	$vars['DSE']['DSE_CONFIG_FILE_GLOBAL']."/panic   and answer the questions  )"),
);
$vars['parameters']=dse_cli_get_paramaters_array($parameters_details);
$vars['Usage']=dse_cli_get_usage($parameters_details);
$vars['argv_origional']=$argv;
dse_cli_script_start();
		
$BackupBeforeUpdate=TRUE;
foreach (array_keys($vars['options']) as $opt) switch ($opt) {
  	case 'y':
	case 'verbosity':
		$vars['Verbosity']=$vars['options'][$opt];
		if($vars['Verbosity']>=2) print "Verbosity set to ".$vars['Verbosity']."\n";
		break;
}
foreach (array_keys($vars['options']) as $opt) switch ($opt) {
	case 'q':
	case 'quiet':
		$Quiet=TRUE;
		$vars['Verbosity']=0;
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
		break;
	case 's':
  	case 'set-env':
  		$DoSetEnv=TRUE;
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
	case 'x':
  	case 'code-query':
		include_once ("/dse/include/code_functions.php");
  		$String=$vars['options'][$opt];
		if(sizeof($argv)>1 && $argv[1]){
			$LaunchNumber=$argv[1];
		}
		//dse_launch_code_edit
		
		
		foreach ($vars['DSE']['CODE_BROWSE_DIRECTORIES'] as $DoDirStr){ 
			$DoDir=strcut($DoDirStr,""," ");
			$Li=0;
			if(!$LaunchNumber) print bar("FILE NAME Results: $DoDir","-","blue","white","green","white");
			$Command=$vars['DSE']['DSE_BIN_DIR']."/fss \"$String\" ".$DoDir;
	  		$r=dse_exec($Command,$vars['Verbosity']>3);
			foreach(split("\n",$r) as $L){
				$Li++;
				if(!$LaunchNumber){
					print colorize("$Li","cyan","black");
					print colorize(": ","blue","black");
					$L=str_replace($String,colorize($String,"black","yellow"),$L);
					print "$L\n";
				}
			}
			
			if(!$LaunchNumber) print bar("STRING GREP Results: $DoDir","-","blue","white","green","white");
			$Command=$vars['DSE']['DSE_BIN_DIR']."/gss \"$String\" ".$DoDir." | grep -v .dab  ";
	  		$r=dse_exec($Command,$vars['Verbosity']>3);
			foreach(split("\n",$r) as $L){
				$L=trim($L);
				if($L){
					list($FileName,$LineNumber,$Line)=split(":",$L);
					if($FileName && dse_file_exists($FileName)){
						$Li++;
						if(!$LaunchNumber){
							print colorize("$Li","yellow","black");
							print colorize(": ","blue","black");
							
							print colorize($FileName,"cyan","black");
							print colorize("::","yellow","black");
							print colorize($LineNumber,"green","black");
							$L=str_replace($String,colorize($String,"black","yellow"),$Line);
							print "$L\n";
						}
						if($LaunchNumber==$Li){
							dse_launch_code_edit($FileName,$LineNumber);
							exit(0);
						}
					}
				}
			}
			
			if(!$LaunchNumber) print bar("code_explorer Defined Functions Results: $DoDir","-","blue","white","green","white");
			$CodeBaseDir="";
			$CodeInfoArray=dse_code_parse($CodeBaseDir);
			foreach($CodeInfoArray['Functions']['Def'] as $k=>$fde){
				if(str_icontains($fde[2],$String)){
					$f=$fde[0];
					$l=$fde[1];
					$n=$fde[2];
					$p=$fde[3];
					$d=$fde[4];
					$n=str_replace($String,colorize($String,"black","yellow"),$n);
					print " $f:$l  <b class='f9pt'>$n</b> ($p)<br>";
				}
			}
			//$CodeInfoArray['Files'][$FileFullName]
			
		}
		
		$DidSomething=TRUE;
		break;
		
	case 'reboot':
		$RebootCommand="sudo shutdown -r now";
		//print "afasfsadfqwe";
		dse_passthru($RebootCommand,TRUE);
		$t=time();
		while(TRUE){
			//print "fsadfsadfdas";
			$td=time()-$t;
			$h=cbp_get_screen_height()-10;
			print "REBOOT COMMAND ISSUED  $td seconds ago:     $RebootCommand\n";
			dse_exec("ps -aux | head -n $h",TRUE,TRUE);
			sleep(1);
		}
		break;
	case 'halt':
		$RebootCommand="sudo shutdown -h now";
		dse_passthru($RebootCommand,TRUE);
		$t=time();
		while(TRUE){
			cbp_screen_clear();
			$td=time()-$t;
			$h=cbp_get_screen_height()-3;
			print "SHUTDOWN COMMAND ISSUED  $td seconds ago:     $RebootCommand\n";
			dse_exec("ps -aux | head -n $h",TRUE,TRUE);
			sleep(1);
		}
		break;
		
}

if($vars['Verbosity']>4){
	$vars[dse_enable_debug_code]=TRUE;
}
foreach (array_keys($vars['options']) as $opt) switch ($opt) {
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
}

foreach (array_keys($vars['options']) as $opt) switch ($opt) {
  	case 'c':
  	case 'configure':
		$r=passthru($vars['DSE']['DSE_BIN_DIR']."/dse-configure --verbosity ".$vars['Verbosity']);
		if($r<0){
			if(!$Quiet && !$DoSetEnv){
				print getColoredString($vars['DSE']['SCRIPT_NAME']." FATAL ERROR. Exiting (0)","black","green");
				$vars[shell_colors_reset_foreground]='';	print getColoredString("\n","white","black");
			}
			exit(-1);
		}
		$DidSomething=TRUE;
		break;
}

foreach (array_keys($vars['options']) as $opt) switch ($opt) {
  	case 'i':
  	case 'install':
		$r=passthru($vars['DSE']['DSE_BIN_DIR']."/dse-install --verbosity ".$vars['Verbosity']);
		if($r<0){
			if(!$Quiet && !$DoSetEnv){
				print getColoredString($vars['DSE']['SCRIPT_NAME']." FATAL ERROR. Exiting (0)","black","green");
				$vars[shell_colors_reset_foreground]='';	print getColoredString("\n","white","black");
			}
			exit(-1);
		}
		$DidSomething=TRUE;
		break;
}

if($argv[1]=="push"){
	$Command="git_ul dse";
	if(sizeof($argv)>2 && $argv[2]){
		$Command .= " " . $argv[2];
	}else{
		$Command .= " no_message";
	}
	exit(passthru($Command));
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
   backup_etc     - backs up /etc
   bh             - bash history grepper for arg1
   btop           - bottle top - system bottle-neck analyzer
   chom           - chmod + chown
   code_explorer  - code manager
   dab            - deity automatic backup program
   detestr        - returns system time in a few common formats
   dbp            - backup and patch
   ddb            - database manager
   dfm            - file modifier / manager
   dlb            - load balancer / service status monitor
   dmd5           - file md5 checksum generator
   dnetstat       - network info
   dreplace       - replaces arg2 with arg3 in file arg1
   dsc            - service/daemon controler
   dsizeof        - returns size in bytes of arg1
   dse            - script that sets dse variables, get's status, provides 
                     help, etc
   dse-install    - installs dse on server
   dse-configure  - runs setup of config and variables and environment
   dse_daemon     - answers select DSE commands/requests from remotely
   dse_set-env    - sets dse vars for shell. use: . /dse/bin/dse_set-env
   dsm            - devity server monitor - watches load, processes, hd, etc.
   dss            - system stats
   dst            - shell text utilities: color, cursor
   dtmp           - returns a valid, unique full /tmp/ filename
   fss            - find string
   fstat          - file info
   grep2exe       - returns script name for a string on ps output lines
   grep2pid       - returns PID for a string on ps output lines
   gss            - grep for string
   http_stress    - multi-threaded web-site stress tester
   img2txt        - almost any format img to ansi or html converter
   ip_throttle    - sort-of load balancer, ip banner
   lgt            - returns the intermingled tails of a few /var/log/(s)
   memcache-top   - top for memcache
   mysqltuner     - mysqld config analyzer based on:
                     http://github.com/rackerhacker/MySQLTuner-perl
   panic          - automated emergency script. free's disk, restarts daemons,
                     restore old config's, reboot.
   pid2exe        - returns the script name for a PID
   pidinfo        - various info on a PID
   publish        - dev->prd publisher
   rpms_extract   - rebuilds as near as possible a .rpm file for an 
                     installed package
   server_backup  - backup all server config and data
   server_log_status - saves a copy of the output of over a dozen commands 
                        like ps, lsof, vmtstat, nmap, iostat, printenv, etc
   server_monitor - server health monitor that takes actions (run scripts, 
                     send emails, etc) at various configurable thresholds
   vibk           - backup arg1 then edit with vi
|
 
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


dse_shutdown();

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
