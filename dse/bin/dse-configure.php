#!/usr/bin/php
<?
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
include_once ("/dse/bin/dse_config_functions.php");
dse_require_root();
$vars['Verbosity']=1;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="DSE Configure Script";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="setup of config files and settings";
$vars['DSE']['SCRIPT_VERSION']="v0.04b";
$vars['DSE']['SCRIPT_VERSION_DATE']="2012/09/28";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
$vars['DSE']['SCRIPT_COMMAND_FORMAT']="";
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

$parameters_details = array(
  array('h','help',"this message"),
  array('y:','verbosity:',"0=none 1=some 2=more 3=debug"),
  array('v','listvars',"list configuration variables"),
  array('f','full',"full setup / configuration"),
  array('s','services',"(re)runs configuration of services"),
  array('H','http',"(re)runs configuration of http(s)"),
);
$vars['parameters']=dse_cli_get_paramaters_array($parameters_details);
$vars['Usage']=dse_cli_get_usage($parameters_details);
$vars['argv_origional']=$argv;
dse_cli_script_start();
		
$DSEConfig=TRUE;
$BackupBeforeUpdate=TRUE;
foreach (array_keys($vars['options']) as $opt) switch ($opt) {
  	case 'y':
	case 'verbosity':
		$vars['Verbosity']=$vars['options'][$opt];
		if($vars['Verbosity']>=2) print "Verbosity set to ".$vars['Verbosity']."\n";
		break;
}
foreach (array_keys($vars['options']) as $opt) switch ($opt) {
	case 'h':
  	case 'help':
  		$ShowUsage=TRUE;
		$DidSomething=TRUE;
		$DSEConfig=FALSE;
		break;
	case 'f':
  	case 'full':
		$DSEConfig=TRUE;
  		$FullConfig=TRUE;
		$DidSomething=TRUE;
		break;		
	case 's':
  	case 'services':
		$DSEConfig=FALSE;
  		$ServicesConfig=TRUE;
		$DidSomething=TRUE;
		break;
			
	case 'H':
  	case 'http':
		dse_configure_create_httpd_conf();
		$DidSomething=TRUE;
		break;
}

if($vars['Verbosity']>4){
	$vars[dse_enable_debug_code]=TRUE;
}
dse_cli_script_header();

if($argv[1]=="help" || $ShowUsage){
	print $vars['Usage'];
	exit();
}



// ********* main script activity START ************
if($DSEConfig){
	dse_do_dse_cfg();
}

if($FullConfig || $ServicesConfig){
	dse_server_configure_file_load();	
	dse_do_services_cfg();
}
	

// ********* main script activity END ************


print getColoredString($vars['DSE']['SCRIPT_FILENAME']." Done!\n","green","black","black","black");
dse_shutdown();

if($DidSomething){
	$vars[shell_colors_reset_foreground]='';	print getColoredString("","white","black","black","black");
	exit(0);
}

exit(0);



	



	 

?>
