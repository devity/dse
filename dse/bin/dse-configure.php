#!/usr/bin/php
<?
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
dse_require_root();
$Verbosity=3;

$Script=$argv[0];
$ScriptName="dse";
$parameters = array(
  'h' => 'help',
  'v' => 'listvars',
  
);
$flag_help_lines = array(
  'h' => "\thelp - this message",
  'v' => "\listvars - list configuration variables",

);
$ScriptName_str=getColoredString($ScriptName, 'yellow', 'black');
$Usage="   $ScriptName_str - Devity Server Environment Managment Script
       by Louy of Devity.com


".getColoredString("command line usage:","yellow","black").
getColoredString(" dse","cyan","black").
getColoredString(" <args> (options)","dark_cyan","black")."     
";
foreach($parameters as $k=>$v){
	$k2=str_replace(":","",$k);
	$v2=str_replace(":","",$v);
	$Usage.=" -${k2}, --${v2}\t".$flag_help_lines[$k]."\n";
}
$Usage.="\n\n";


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
}


if($Verbosity>=2){
	//print getColoredString("","black","black");
	print getColoredString("    ########======-----________   ", 'light_blue', 'black');
	print getColoredString($ScriptName,"yellow","black");
	print getColoredString("   ______-----======########\n", 'light_blue', 'black');
	print "  ___________ ______ ___ __ _ _   _                      \n";
	print " /                           Configuration Settings\n";
	print "|  * Script: $Script\n";
	print "|  * Verbosity: $Verbosity\n";
	print " \________________________________________________________ __ _  _   _\n";
	//print "\n";  
}

if($argv[1]=="help" || $ShowUsage){
	print $Usage;
}



$DSE_Git_pull_script="/scripts/dse_git_pull";
if(file_exists($DSE_Git_pull_script)){
	print "DSE git pull script installed at $DSE_Git_pull_script   \n ";
}else{
	
	print "DSE git pull script missing. Install to $DSE_Git_pull_script ? ";
	$key=strtoupper(dse_get_key());
	if($key=="Y"){
		print " Installing! ";
		$Template=$vars['DSE']['DSE_TEMPLATES_DIR'] . "/" . "dse_git_pull";
		$error_no=dse_install_file($Template,$DSE_Git_pull_script,"4775","root:root");
		if($error_no){
			print "Fatal error. Exiting.";
			exit -1;
		}
	}elseif($key=="N"){
		print " Not Installing. ";
	}else{
		print " unknown key: $key ";
	}
	print "\n";
}


if($DidSomething){
	$vars[shell_colors_reset_foreground]='';	print getColoredString("","white","black","black","black");
	exit(0);
}

exit(0);





	 

?>
