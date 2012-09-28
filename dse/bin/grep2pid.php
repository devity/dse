#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
//$vars[dse_enable_debug_code]=TRUE; $vars['Verbosity']=6;
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
$vars['Verbosity']=0;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="Grep 2 PID";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="Returns the PID(s) of processes matching a grep of argv1";
$vars['DSE']['SCRIPT_VERSION']="v0.02b";
$vars['DSE']['SCRIPT_VERSION_DATE']="2012/09/28";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
$vars['DSE']['SCRIPT_COMMAND_FORMAT']="grep_string";
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

$parameters_details = array(
  array('h','help',"this message"),
  array('q','quiet',"same as --verbosity 0"),
  array('v:','verbosity:',"0=none 1=some 2=more 3=debug"),
 

);
$vars['parameters']=dse_cli_get_paramaters_array($parameters_details);
$vars['Usage']=dse_cli_get_usage($parameters_details);
$vars['argv_origional']=$argv;
dse_cli_script_start();
		
$BackupBeforeUpdate=TRUE;
foreach (array_keys($vars['options']) as $opt) switch ($opt) {
  	case 'v':
	case 'verbosity':
		$vars['Verbosity']=$vars['options'][$opt];
		if($vars['Verbosity']>=2) print "Verbosity set to ".$vars['Verbosity']."\n";
		break;
}

dse_cli_script_header();
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
}

if($vars['Verbosity']>4){
	$vars[dse_enable_debug_code]=TRUE;
}
foreach (array_keys($vars['options']) as $opt) switch ($opt) {
	case 'd':
  	case 'update-documentation':
  		$DoUpdateDocumentation=TRUE;
		$DidSomething=TRUE;
		break;
	
}







if(sizeof($argv)>1){
	$search_str=$argv[1];
}else{
	$search_str=fgets(STDIN);
}

	
if($search_str){
	$DidSomething=TRUE;
				
	$dollar='$';
	$cmd="ps aux | grep \"$search_str\" | grep -v grep | awk '{ print ${dollar}2 }'";
	$PID=trim(`$cmd`);
	
	
	if($vars['Verbosity']>=2){
		print "PID: ";
	}
	$PID=str_replace("\n"," ",$PID);
	print trim($PID);
}






dse_cli_script_shutdown();


if($DidSomething){
	if(!$Quiet && !$DoSetEnv && $vars['Verbosity']>0){
		print getColoredString($vars['DSE']['SCRIPT_NAME']." Done. Exiting (0)","black","green");
		$vars[shell_colors_reset_foreground]='';	print getColoredString("\n","white","black");
	}
	if(!$NoExit) exit(0);
}else{
	if(!$Quiet && !$DoSetEnv && $vars['Verbosity']>0){
		print getColoredString("Nothing to do! try --help for usage. ".$vars['DSE']['SCRIPT_NAME']." Done. Exiting (-1)","pink","black");
		$vars[shell_colors_reset_foreground]='';	print getColoredString("\n","white","black");
	}
	if(!$NoExit) exit(-1);
}
?>