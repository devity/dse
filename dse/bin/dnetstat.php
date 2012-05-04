#!/usr/bin/php
<?
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/include/system_stat_functions.php");
include_once ("/dse/bin/dse_config.php");


$vars[shell_colors_reset_foreground]='light_grey';
$Start=time();
$Verbosity=0;
$ReloadSeconds=30;
$MaxLoops=30;
$ForceHighLoadRun=FALSE;
$MaxLoadBeforeExit=5;
$Threads=3;



// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="DSE Net Stat";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="Network Status and Stats";
$vars['DSE']['BTOP_VERSION']="v0.041";
$vars['DSE']['BTOP_VERSION_DATE']="2012/05/04";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******



$parameters_details = array(
  array('h','help',"this message"),
  array('q','quiet',"same as -v 0"),
  array('','version',"version info"),
  array('v:','verbosity:',"0=none 1=some 2=more 3=debug"),
  array('o','open',"List OPEN ports"),
  array('x','xip',"Return External IP Address"),
);
$parameters=dse_cli_get_paramaters_array($parameters_details);
$Usage=dse_cli_get_usage($parameters_details);



$options = _getopt(implode('', array_keys($parameters)),$parameters);
$pruneargv = array();
foreach ($options as $option => $value) {
  foreach ($argv as $key => $chunk) {
    $regex = '/^'. (isset($option[1]) ? '--' : '-') . $option . '/';
    if ($chunk == $value && $argv[$key-1][0] == '-' || preg_match($regex, $chunk)) {
      array_push($pruneargv, $key);
    }
  }
}
while ($key = array_pop($pruneargv)){
	deleteFromArray($argv,$key,FALSE,TRUE);
}


$IsSubprocess=FALSE;
foreach (array_keys($options) as $opt) switch ($opt) {
	case 'h':
  	case 'help':
  		$ShowUsage=TRUE;
		$DidSomething=TRUE;
		break;
  	case 'version':
  		$ShowVersion=TRUE;
		$DidSomething=TRUE;
		break;
	case 'q':
	case 'quiet':
		$Verbosity=0;
		break;
	case 'o':
	case 'open':
		$ShowOpen=TRUE;
		break;
	case 'x':
	case 'xip':
		$ShowIP=TRUE;
		break;
	case 'v':
	case 'verbosity':
		$Verbosity=$options[$opt];
		if($Verbosity>=2) print "Verbosity set to $Verbosity\n";
		break;
	case 'z':
	case 'maxload':
		$MaxLoadBeforeExit=$options[$opt];
		if($Verbosity>=2) print "MaxLoadBeforeExit set to $MaxLoadBeforeExit\n";
		break;



}


if($Verbosity>=2){
	//print getColoredString("","black","black");
	print getColoredString("    ########======-----________   ", 'light_blue', 'black');
	print getColoredString($vars['DSE']['SCRIPT_NAME'],"yellow","black");
	print getColoredString("   ______-----======########\n", 'light_blue', 'black');
	print "  ___________ ______ ___ __ _ _   _                      \n";
	print " /                           Configuration Settings\n";
	print "|  * Script: ".$vars['DSE']['SCRIPT_FILENAME']."\n";
	print "|  * MaxLoadBeforeExit: $MaxLoadBeforeExit\n";
	print "|  * Verbosity: $Verbosity\n";
	print "|  * reload-seconds: $ReloadSeconds\n";
	print "|  * Number of Threads: $Threads\n";
	print " \________________________________________________________ __ _  _   _\n";
	print "\n";  
}

if($ShowUsage){
	print $Usage;
}
if($ShowVersion){
	print "DSE Version: " . $vars['DSE']['DSE_VERSION'] . "  Release Date: " . $vars['DSE']['DSE_VERSION_DATE'] ."\n";
	print $vars['DSE']['SCRIPT_NAME']." Version: " . $vars['DSE']['BTOP_VERSION'] . "  Release Date: " . $vars['DSE']['BTOP_VERSION_DATE'] ."\n";
}

if($DidSomething){
	$vars[shell_colors_reset_foreground]='';	print getColoredString("","white","black");
	exit(0);
}
if($ShowOpen){
	$dse_sysstats_net_listening_array=dse_sysstats_net_listening();
	$section_net_listening=$dse_sysstats_net_listening_array[3];
	print $section_net_listening."\n";	
}

if($ShowIP){
	$ext_info = `curl --silent http://checkip.dyndns.org | grep -Eo '([0-9]{1,3}\.){3}[0-9]{1,3}'`;
	if($ext_info) { 
	    print $ext_info;
	}
}
$EndLoad=get_load();  
$ActualRunTime=time()-$Start;
	

			
			
//print getColoredString("Done. Exiting ".$vars['DSE']['SCRIPT_FILENAME'].". \n\n", 'black', 'green');
exit(0);

