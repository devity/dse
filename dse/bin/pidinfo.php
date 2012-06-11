#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
$vars['Verbosity']=1;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="PID Info";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="Gets info about a process by it's PID'";
$vars['DSE']['DSE_DSE_VERSION']="v0.01b";
$vars['DSE']['DSE_DSE_VERSION_DATE']="2012/07/11";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

$parameters_details = array(
  array('h','help',"this message"),
  array('f','family-tree',"shows infor for parent and all grandparent processes"),
);
$vars['parameters']=dse_cli_get_paramaters_array($parameters_details);
$vars['Usage']=dse_cli_get_usage($parameters_details);
$vars['argv_origional']=$argv;
dse_cli_script_start();
		
$BackupBeforeUpdate=TRUE;
foreach (array_keys($vars['options']) as $opt) switch ($opt) {
	case 'h':
  	case 'help':
  		$ShowUsage=TRUE;
		$DidSomething=TRUE;
		break;
	case 'f':
  	case 'family-tree':
  		$ShowFamilyTree=TRUE;
		$DidSomething=TRUE;
		break;
}

	
if($ShowUsage){
	print $vars['Usage'];
	exit(0);
}

if(sizeof($argv)==1){
	print "no PID argument given. exiting.\n";
	exit(-1);
}else{
	$PID=$argv[1];
}
dse_cli_script_header();


$PIDInfo=dse_pid_get_info($PID);
if($ShowFamilyTree){
	$Command="/dse/bin/pidinfo -f ".$PIDInfo['PPID'];
	$parent=`$Command`;
	print $parent;
}


print "EXE: ".$PIDInfo['EXE']."\n";
print "PID: ".$PIDInfo['PID']."\n";
print "PPID: ".$PIDInfo['PPID']."\n";
print "PCPU: ".$PIDInfo['PCPU']."\n";
print "PMEM: ".$PIDInfo['PMEM']."\n";

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
