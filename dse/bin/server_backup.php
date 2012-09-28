#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
include_once ("/dse/bin/dse_config_functions.php");
$vars['Verbosity']=1;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="server-backup";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="makes a backup of web data, mysql, conf's, etc";
$vars['DSE']['SCRIPT_VERSION']="v0.01b";
$vars['DSE']['SCRIPT_VERSION_DATE']="2012/09/28";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
$vars['DSE']['SCRIPT_COMMAND_FORMAT']="";
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******


$parameters_details = array(
  array('l','log-show:',"shows tail of log ".$vars['DSE']['LOG_FILE']),
  array('h','help',"this message"),
  array('q','quiet',"same as --verbosity 0"),
  array('e','edit',"backs up and launches a vim of ".$vars['DSE']['DSE_CONFIG_FILE_GLOBAL']),
  array('w','config-show',"prints ".$vars['DSE']['DSE_CONFIG_FILE_GLOBAL']),
  array('','config-show',"prints contents of ".$vars['DSE']['DSE_CONFIG_FILE_GLOBAL']),
  array('y:','verbosity:',"0=none 1=some 2=more 3=debug"),
  array('z:','status:',"shows status on all or arg1. options: [initd]"),
  array('c','clone',"build a recreate / clone script"),
  array('a','http',"backup webroot and http confs"),
  array('m','mysql',"backup mysql data and confs"),
  array('v','env',"backup environment info"),
  //interact w etckeeper
  //rpm rebuild stored restore points
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
		break;
	case 'z':
  	case 'status':
		$DidSomething=TRUE;
		break;
	case 'c':
  	case 'clone':
		dse_build_clone_server_script();
		exit(0);
		$DidSomething=TRUE;
		break;
	case 'a':
  	case 'http':
		dse_backup_httpd();
		exit(0);
		$DidSomething=TRUE;
		break;
	case 'v':
  	case 'env':
		dse_backup_server_environment();
		exit(0);
		$DidSomething=TRUE;
		break;
	case 'm':
  	case 'mysql':
		dse_backup_mysqld();
		exit(0);
		$DidSomething=TRUE;
		break;
}



print "Starting Backup:\n";


print "Backup Directory: ".$vars['DSE']['DSE_BACKUP_DIR']." ";
if(!is_dir($vars['DSE']['DSE_BACKUP_DIR'])){
	print " $Missing. Create? ";
	$A=dse_ask_yn();
	if($A=='Y'){
		dse_directory_create($vars['DSE']['DSE_BACKUP_DIR'],"777","root:root");
	}else{
		print "\n  Can't backup w/o backup dir. Exiting.\n";
		exit(-1);	
	}
}else{
	print $OK;
}
print "\n";




//dse_backup_server_environment();
//dse_backup_mysqld();
//dse_backup_httpd();


print "Done.\n";
exit(0);

?>



