#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
include_once ("/dse/include/db_functions.php");
$vars['Verbosity']=0;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="DSE DataBase Manager";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="basic managing / status of DB";
$vars['DSE']['DSE_DSE_VERSION']="v0.01b";
$vars['DSE']['DSE_DSE_VERSION_DATE']="2012/07/05";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

global $CFG_array;
$CFG_array=array();
//$CFG_array['QueriesMade']=0;
//$CFG_array=dse_read_config_file($vars['DSE']['DLB_CONFIG_FILE'],$CFG_array);	

			

$parameters_details = array(
 // array('l','log-to-screen',"log to screen too"),
 // array('','log-show:',"shows tail of log ".$CFG_array['LogFile']."  argv1 lines"),
  array('h','help',"this message"),
  array('q','quiet',"same as --verbosity 0"),
  array('v:','verbosity:',"0=none 1=some 2=more 3=debug"),
  array('w','html',"html output format"),
  array('s','status',"prints status file".$CFG_array['StatusFile']),
  array('f:','find:',"searches for arg1 in all db and tables or db arg2 and table arg3"),
  array('d','list-databases',"prints list of databases: SHOW DATABASES; command"),
  array('t:','list-tables:',"prints list of tables in database arg1: USE arg1; SHOW TABLES; command"),
  array('r','repair-all',"repairs all tables in all db's"),
  array('c','check-all',"check all tables in all db's"),
 // array('e','edit',"backs up and launches a vim of ".$vars['DSE']['DLB_CONFIG_FILE']),
//  array('c','config-show',"prints contents of ".$vars['DSE']['DLB_CONFIG_FILE']),
 // array('d:','daemon:',"manages the checking daemon. options: [start|stop|restart|status]"),
//  array('r:','request-from-pool:',"returns an UP node from service_pool=arg1"),
);
$vars['parameters']=dse_cli_get_paramaters_array($parameters_details);
$vars['Usage']=dse_cli_get_usage($parameters_details);
$vars['argv_origional']=$argv;
dse_cli_script_start();
		
$BackupBeforeUpdate=TRUE;
foreach (array_keys($vars['options']) as $opt) switch ($opt) {
	/*case 'l':
	case 'log-to-screen':
		$vars['DSE']['LOG_TO_SCREEN']=TRUE;
		dpv(2,"Logging to screen ON\n".$vars['DSE']['LOG_TO_SCREEN']);
		$vars['LOG_TO_SCREEN']=TRUE;
		break;
	case 'log-show':
		if($vars['options'][$opt]) $Lines=$vars['options'][$opt]; else $Lines=$vars['DSE']['LOG_SHOW_LINES'];
		$Command="tail -n $Lines ".$CFG_array['LogFile'];
		print `$Command`;
		exit(0);*/
	case 'q':
	case 'quiet':
		$Quiet=TRUE;
		$vars['Verbosity']=0;
		break;
  	case 'v':
	case 'verbosity':
		$vars['Verbosity']=$vars['options'][$opt];
		dpv(2,"Verbosity set to ".$vars['Verbosity']."\n");
		break;
  	case 'w':
	case 'html':
		$vars['DSE']['OUTPUT_FORMAT']="HTML";
		dpv(3,"Output format set to HTML\n");
		break;
}


foreach (array_keys($vars['options']) as $opt) switch ($opt) {
  	case 'f':
	case 'find':
		$query=$vars['options'][$opt];
		if(sizeof($argv)>1){
			$db=$argv[1];
		}else{
			$db="*";
		}
		if(sizeof($argv)>2){
			$table=$argv[2];
		}else{
			$table="*";
		}
		dpv(2,"Searching db $db, table $table for $query\n");
		dse_database_find_string_occurances($query,$db,$table);
		break;
	case 's':
  	case 'status':
		if($RunningPID>0){
			print "DLB Daemon is RUNNING PID=$RunningPID\n";
		}else{
			print "DLB Daemon is NOT RUNNING!\n";
		}
		dpv(1,dse_file_get_contents($CFG_array['StatusFile']));
		exit(0);
	case 'd':
  	case 'list-databases':
		$a=dse_database_list_array();
		foreach($a as $d){
			if($d) print "$d\n";
		}
		exit(0);
	case 't':
  	case 'list-tables':
		if(!$vars['options'][$opt]){
			print "no arg1\n";
			exit(1);
		}
		$Database=$vars['options'][$opt];
		$a=dse_table_list_array($Database);
		foreach($a as $t){
			if($t) print "$t\n";
		}
		exit(0);
	case 'r':
  	case 'repair-all':
		dse_database_check_all();
	//	dse_database_repair_all();
		exit(0);
	case 'c':
  	case 'check-all':
		dse_database_check_all();
		exit(0);
		
		
	case 'h':
  	case 'help':
		print $vars['Usage'];
		exit(0);
	/*case 'e':
	case 'edit':
		$Message="Backing up ".$vars['DSE']['DLB_CONFIG_FILE']." and launcing in vim:\n";
		dpv(1,$Message);
		dse_log($Message);
		passthru("/dse/bin/vibk ".$vars['DSE']['DLB_CONFIG_FILE']." 2>&1");
		exit(0);
	case 'c':
  	case 'config-show':
		print dse_file_get_contents($vars['DSE']['DLB_CONFIG_FILE']);
		exit(0);*/
}


dse_cli_script_header();


if($DidSomething){
	if(!$Quiet && !$DoSetEnv){
		dpv(1, getColoredString($vars['DSE']['SCRIPT_NAME']." Done. Exiting (0)","black","green"));
		$vars['shell_colors_reset_foreground']='';	print getColoredString("\n","white","black");
	}
	if(!$NoExit) exit(0);
}else{
	if(!$Quiet && !$DoSetEnv){
		dpv(1, getColoredString("Nothing to do! try --help for usage. ".$vars['DSE']['SCRIPT_NAME']." Done. Exiting (-1)","pink","black"));
		$vars['shell_colors_reset_foreground']='';	print getColoredString("\n","white","black");
	}
	if(!$NoExit) exit(-1);
}


exit(0);
// --------------------------------------------------------------------------------
// **********************************************************************************

	 

?>
