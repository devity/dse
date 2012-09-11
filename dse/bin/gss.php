#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
//$vars[dse_enable_debug_code]=TRUE; $vars['Verbosity']=6;
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
$vars['Verbosity']=1;
$ShowCommand=FALSE;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="GSS - Grep Search String";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="grep tailored for server admin manual usage, color, launch files, etc";
$vars['DSE']['DSE_DSE_VERSION']="v0.05b";
$vars['DSE']['DSE_DSE_VERSION_DATE']="2012/07/07";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

$String=$argv[1];
if(sizeof($argv)>2){
	$d=$argv[2];
}else{
	$d="/";
}

//print "Searching for: '$String' in $d\n";

$find_cmd="sudo grep -i -n -R \"$String\" $d 2>/dev/null";
$out=dse_exec($find_cmd,$ShowCommand);
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
