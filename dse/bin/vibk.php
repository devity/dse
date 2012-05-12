#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="VIBK";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="invokes vim after making backup";
$vars['DSE']['VIBK_VERSION']="v0.02b";
$vars['DSE']['VIBK_VERSION_DATE']="2012/04/30";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

print "Script: ".$vars['DSE']['SCRIPT_FILENAME']."\n";

$file=$argv[1];
if($file==basename($file)){
	$file=trim(`pwd`)."/".$file;	
}

$backupfilename=dse_file_backup($file);
print "backing up to: $backupfilename\n";

passthru("/usr/bin/vim $file 2>&1");

if(files_are_same($file,$backupfilename)){
	print "No change to $file. backup at $backupfilename removed\n";
	$Command="rm -f $backupfilename";
	print `$Command`;
	
}else{
	print "$file saved. backup at $backupfilename\n";
}
exit();




?>
