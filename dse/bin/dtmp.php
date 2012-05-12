#!/usr/bin/php
<?
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/include/system_stat_functions.php");
include_once ("/dse/bin/dse_config.php");
$Verbosity=0;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="DSE Temp File Name Generator";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="line and string replacer";
$vars['DSE']['BTOP_VERSION']="v0.01b";
$vars['DSE']['BTOP_VERSION_DATE']="2012/05/11";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
$vars['DSE']['SCRIPT_COMMAND_FORMAT']="";
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

$parameters_details = array(
  array('h','help',"this message"),
  array('q','quiet',"same as -v 0"),
//  array('p','pattern',"treat needle as a pattern"),
  array('','version',"version info"),
  array('v:','verbosity:',"0=none 1=some 2=more 3=debug"),
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
while ($key = array_pop($pruneargv)){ deleteFromArray($argv,$key,FALSE,TRUE); }

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
	case 'v':
	case 'verbosity':
		$Verbosity=$options[$opt];
		if($Verbosity>=2) print "Verbosity set to $Verbosity\n";
		break;
}

if($Verbosity>=2){
	print getColoredString("    ########======-----________   ", 'light_blue', 'black');
	print getColoredString($vars['DSE']['SCRIPT_NAME'],"yellow","black");
	print getColoredString("   ______-----======########\n", 'light_blue', 'black');
	print "  ___________ ______ ___ __ _ _   _                      \n";
	print " /                           Configuration Settings\n";
	print "|  * Script: ".$vars['DSE']['SCRIPT_FILENAME']."\n";
	print "|  * Verbosity: $Verbosity\n";
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

$DATE_TIME_NOW=trim(`date +"%y%m%d%H%M%S"`);
	
$TmpDir="/tmp/";
$TmpFile=$TmpDir."dse_tmp_file_${DATE_TIME_NOW}_0".rand(10000,99999);
while(file_exists($TmpFile)){
	$TmpFile=$TmpDir."dse_tmp_file_${DATE_TIME_NOW}_0".rand(10000,99999);
}
print $TmpFile;

if($Verbosity>=2) print getColoredString("Done. Exiting ".$vars['DSE']['SCRIPT_FILENAME'].". \n\n", 'black', 'green');

exit(0);


?>
