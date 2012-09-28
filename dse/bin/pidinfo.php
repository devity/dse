#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
include_once ("/dse/include/system_stat_functions.php");
$vars['Verbosity']=1;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="PID Info";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="Gets info about a process by it's PID'";
$vars['DSE']['SCRIPT_VERSION']="v0.01b";
$vars['DSE']['SCRIPT_VERSION_DATE']="2012/07/11";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
$vars['DSE']['SCRIPT_COMMAND_FORMAT']="(options) PID";
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

$parameters_details = array(
  array('h','help',"this message"),
  array('v:','verbosity:',"0=none 1=some 2=more 3=debug"),
  array('f','family-tree',"shows info for parent and all grandparent processes"),
  array('e','exe-family-tree',"returns string like: init>grandparent>parent>PIDsEXE"),
  array('c','cpu-trace',"shows info on processes hitting the cpu"),
  //throttle / nice   http://www.willnolan.com/cputhrottle/cputhrottle.html
);
$vars['parameters']=dse_cli_get_paramaters_array($parameters_details);
$vars['Usage']=dse_cli_get_usage($parameters_details);
$vars['argv_origional']=$argv;
dse_cli_script_start();
		
$BackupBeforeUpdate=TRUE;
foreach (array_keys($vars['options']) as $opt) {switch ($opt) {
	case 'h':
  	case 'help':
  		$ShowUsage=TRUE;
		$DidSomething=TRUE;
		break;
  	case 'v':
	case 'verbosity':
		$vars['Verbosity']=$vars['options'][$opt];
		if($vars['Verbosity']>=2) print "Verbosity set to ".$vars['Verbosity']."\n";
		break;
}}

foreach (array_keys($vars['options']) as $opt) { switch ($opt) {
	case 'f':
  	case 'family-tree':
		if(sizeof($argv)==1){
			print "no PID argument given. exiting.\n";
			exit(-1);
		}else{
			$PID=$argv[1];
		}
		$PIDInfo=dse_pid_get_info($PID);
  		$ShowFamilyTree=TRUE;
		$DidSomething=TRUE;
		break;
	case 'e':
  	case 'exe-family-tree':
		if(sizeof($argv)==1){
			print "no PID argument given. exiting.\n";
			exit(-1);
		}else{
			$PID=$argv[1];
		}
		$PIDInfo=dse_pid_get_info($PID);
  		$ShowEXEFamilyTree=TRUE;
		$DidSomething=TRUE;
		break;
	case 'c':
  	case 'cpu_trace':
  		$ShowCPUTrace=TRUE;
		$DidSomething=TRUE;
		break;
}
}
	
if($ShowUsage){
	print $vars['Usage'];
	exit(0);
}


dse_cli_script_header();



if($ShowFamilyTree && $PIDInfo['PID']>0 ){
	$Command="/dse/bin/pidinfo -f ".$PIDInfo['PPID'];
	$parent=`$Command`;
	print $parent;
}
if($ShowCPUTrace){
	print dse_sysstats_cpu_trace();
}

if($ShowEXEFamilyTree && $PIDInfo['PID']>0 ){
	if($PIDInfo['PPID']=="0"){
		$parent="0";
	}else{
		$Command="/dse/bin/pidinfo -e ".$PIDInfo['PPID'];
		$parent=trim(`$Command`);
	}
	
	if($parent!="") print $parent." -> ";
	print $PIDInfo['PID'].":";
	print $PIDInfo['EXE_FILE'];	//print " ".$Command."\n";
	
}
if($ShowEXEFamilyTree ){
	exit(0);
}
print "EXE: ".$PIDInfo['EXE']."\n";
print "PID: ".$PIDInfo['PID']."\n";
print "PPID: ".$PIDInfo['PPID']."\n";
print "PCPU: ".$PIDInfo['PCPU']."\n";
print "PMEM: ".$PIDInfo['PMEM']."\n";
print "USER: ".$PIDInfo['USER']."\n";

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
