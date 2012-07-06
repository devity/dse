#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
include_once ("/dse/include/security_functions.php");
$vars['Verbosity']=1;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="DSE Security";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="basic security manager";
$vars['DSE']['DSE_DSE_VERSION']="v0.01b";
$vars['DSE']['DSE_DSE_VERSION_DATE']="2012/07/06";
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
  array('s','status',"prints security status/overview "),
  array('d:','port-scan-detect-versions:',"does an nmap -A -T4 on arg1 "),
);

$vars['parameters']=dse_cli_get_paramaters_array($parameters_details);
$vars['Usage']=dse_cli_get_usage($parameters_details);
$vars['argv_origional']=$argv;
dse_cli_script_start();
foreach (array_keys($vars['options']) as $opt) switch ($opt) {
	case 'h':
  	case 'help':
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
	case 's':
  	case 'status':
		dse_dsec_overview();
		exit(0);
}

foreach (array_keys($vars['options']) as $opt) switch ($opt) {
  	case 'd':
	case 'port-scan-detect-versions':
		$Host=$vars['options'][$opt];
		if(!$Host){
			print "No host arg1 supplied, using localhost\n";
			$Host="localhost";
		}
		//dpv(2,"Verbosity set to ".$vars['Verbosity']."\n");
		
		
		$Command="nmap -A -T4 $Host | grep open | grep tcp";
		$r=dse_exec($Command,TRUE,TRUE);
		
		
		$Command="nmap -sS -O $Host | grep open | grep tcp";
		$r=dse_exec($Command,TRUE,TRUE);
		
	
		
		break;
}

dse_cli_script_header();


if($DidSomething){
	if(!$Quiet && !$DoSetEnv){
		//dpv(1, getColoredString($vars['DSE']['SCRIPT_NAME']." Done. Exiting (0)","black","green"));
		//$vars['shell_colors_reset_foreground']='';	print getColoredString("\n","white","black");
	}
	if(!$NoExit) exit(0);
}else{
	
	if(!$Quiet && !$DoSetEnv){
		//dpv(1, getColoredString("Nothing to do! try --help for usage. ".$vars['DSE']['SCRIPT_NAME']." Done. Exiting (-1)","pink","black"));
		//$vars['shell_colors_reset_foreground']='';	print getColoredString("\n","white","black");
	}
	dse_dsec_overview();
	if(!$NoExit) exit(-1);
}


exit(0);
// --------------------------------------------------------------------------------
// **********************************************************************************

?>
