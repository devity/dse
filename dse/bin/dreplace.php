#!/usr/bin/php
<?
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/include/system_stat_functions.php");
include_once ("/dse/bin/dse_config.php");
$Verbosity=0;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="DSE Replace";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="line and string replacer";
$vars['DSE']['BTOP_VERSION']="v0.01b";
$vars['DSE']['BTOP_VERSION_DATE']="2012/05/11";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
$vars['DSE']['SCRIPT_COMMAND_FORMAT']="(options) input_file needle replace";
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

$parameters_details = array(
  array('h','help',"this message"),
  array('q','quiet',"same as -v 0"),
  array('p','pattern',"treat needle as a pattern"),
  array('s','save',"overwrite argv[1]"),
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
	case 's':
	case 'save':
		$DoSaveOverwrite=TRUE;
	case 'p':
	case 'pattern':
		$TreatNeedleAsPattern=TRUE;
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


// *** GET SETTINGS ***
$Filename=$argv[1];
$Old=$argv[2];
$New=$argv[3];
if($Verbosity>=2) print "Opening $Filename\n";
if($Verbosity>=2) print "Replacing: $Old\nWith: $New\n";

// *** GET INPUT ***
$raw=file_get_contents($Filename);
$raw_a=split("\n",$raw);

// *** DO REPLACE ***
$Out="";
foreach($raw_a as $n=>$line){
	if($Out!="") $Out.="\n";
	if($TreatNeedleAsPattern){
		$line=preg_replace("/$Old/",$New,$line);
		if($Verbosity>=3) print "preg_replace(\"/$Old/\",$New,$line);\n";
	}else{
		$line=str_replace($Old,$New,$line);
		if($Verbosity>=3) print "str_replace($Old,$New,$line);\n";
	}	
	$Out.=$line;
}

// *** OUTPUT ***
if($DoSaveOverwrite){
	$backupfilename=dse_file_backup($Filename);
	if($Verbosity>=2) print "backing up to: $backupfilename\n";
	file_put_contents($Filename,$Out);
	if($Verbosity>=2) print "Saving to/Overwriting $Filename\n";
}else{
	print $Out;
}

if($Verbosity>=2) print getColoredString("Done. Exiting ".$vars['DSE']['SCRIPT_FILENAME'].". \n\n", 'black', 'green');

exit(0);


?>
