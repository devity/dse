#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
$vars['Verbosity']=1;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="DSE Backup and Patch";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="makes backup of then applies patch/edit to a file";
$vars['DSE']['DSE_DSE_VERSION']="v0.01b";
$vars['DSE']['DSE_DSE_VERSION_DATE']="2012/06/22";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

$parameters_details = array(
  array('h','help',"this message"),
  array('q','quiet',"same as --verbosity 0"),
  array('v:','verbosity:',"0=none 1=some 2=more 3=debug"),
  array('','line-append:',"appends \\n then argv[2] to file arg[1]"),
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
  	case 'v':
	case 'verbosity':
		$vars['Verbosity']=$vars['options'][$opt];
		if($vars['Verbosity']>=2) print "Verbosity set to ".$vars['Verbosity']."\n";
		break;
	case 'h':
  	case 'help':
  		$ShowUsage=TRUE;
		$DidSomething=TRUE;
		break;
	case 'line-append':
		$file=$vars['options'][$opt];
		$line=$argv[1];
		if(!$file || !$file){
			print "arg missing for switch --$opt. exiting\n";
			exit(-1);
		}
		$backupfilename=dse_file_backup($file);
		print "backup saved at: $backupfilename\n";
		$Command="echo \"\\n$line\" >> $file ";
		$r=`$Command`;
		print "line appended.\n";
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
