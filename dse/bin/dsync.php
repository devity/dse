#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
include_once ("/dse/include/security_functions.php");
$vars['Verbosity']=1;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="DSE File Sync";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="sync that handles large # of files well, w/ recover";
$vars['DSE']['SCRIPT_VERSION']="v0.01b";
$vars['DSE']['SCRIPT_VERSION_DATE']="2013/01/29";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

global $CFG_array;
$CFG_array=array();
//$CFG_array['QueriesMade']=0;
//$CFG_array=dse_read_config_file($vars['DSE']['DLB_CONFIG_FILE'],$CFG_array);	

			
$parameters_details = array(
  array('h','help',"this message"),
  array('q','quiet',"same as --verbosity 0"),
  array('v:','verbosity:',"0=none 1=some 2=more 3=debug"),
);

$vars['parameters']=dse_cli_get_paramaters_array($parameters_details);
$vars['Usage']=dse_cli_get_usage($parameters_details);
$vars['Usage']=" usage: ".$vars['DSE']['SCRIPT_FILENAME']. " (args) source destination\n source and destination format are user@host:/path/file or local full path filename\n" .$vars['Usage'];
$vars['argv_origional']=$argv;
dse_cli_script_start();
foreach (array_keys($vars['options']) as $opt) switch ($opt) {
	case 'h':
  	case 'help':
		$DidSomething=TRUE;
		print $vars['Usage'];
		exit(0);
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
		exit(0);
}

dse_cli_script_header();


if(!$DidSomething){
	$Source=$argv[1];
	$Dest=$argv[2];
	if(!$Source || !$Dest){
		print "command syntax error: no src and destination given.\n";
		dpv(1, getColoredString("command syntax error: no src and destination given.\n"
			."Try --help for usage. ".$vars['DSE']['SCRIPT_NAME']." Done. Exiting (-1)","pink","black"));
		if(!$NoExit) exit(-1);
	}else{
		print "DSE dsync syncing: \n";
		print "        source: $Source\n";
		print "   destination: $Dest\n";
		
		include_once ("/dse/include/file_functions.php");
		dse_dsync_do($Source,$Dest);
	}
}

dpv(1, getColoredString($vars['DSE']['SCRIPT_NAME']." Done. Exiting (0)","black","green"));
$vars['shell_colors_reset_foreground']='';	print getColoredString("\n","white","black");
if(!$NoExit) exit(0);
// --------------------------------------------------------------------------------
// **********************************************************************************

?>
