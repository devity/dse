#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
//$vars[dse_enable_debug_code]=TRUE; $vars['Verbosity']=6;
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
$vars['Verbosity']=1;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="FGS - Find & Grep Search";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="does a grep on find results";
$vars['DSE']['SCRIPT_VERSION']="v0.01b";
$vars['DSE']['SCRIPT_VERSION_DATE']="2012/08/022";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
$vars['DSE']['SCRIPT_COMMAND_FORMAT']="";
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******


if(sizeof($argv)==1 || $argv[1]=="-h"){
	
	print "Usage: fgs FindString GrepString [Directory]\n";
	exit();
}

$FindString=$argv[1];
$GrepString=$argv[2];
if(sizeof($argv)>3){
	$d=$argv[3];
}else{
	$d="/";
}

print "Searching for: $GrepString in files w/ name containing: $FindString\n";



$find_cmd="sudo find $d -iname \"*$FindString*\" -exec grep -i -H -n \"$GrepString\" {} 2>/dev/null \\; 2>/dev/null";

$out=dse_exec($find_cmd,TRUE);

foreach(split("\n",$out) as $L){
	$L=trim($L);
	if($L){
		$Li++;
		$FileName=strcut($L,"",":");
		$FileName=str_replace($d,"",$FileName);
		$L=strcut($L,":");
		$LineNumber=strcut($L,"",":");
		$Line=strcut($L,":");
		print colorize("$Li","yellow","black");
		print colorize(": ","blue","black");
		print colorize($FileName,"cyan","black");
		print colorize("::","yellow","black");
		print colorize($LineNumber,"green","black");
		$Line=str_replace($String,colorize($String,"black","yellow"),$Line);
		print $Line ."\n";
	}
}
exit();

 
?>
