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
$TIME_NOW=time();
$DATE_TIME_NOW=trim(`date +"%y%m%d%H%M%S"`);

$dir=dirname($vars['DSE']['DSE_VIBK_BACKUP_DIRECTORY']."$file.$DATE_TIME_NOW");
`mkdir -p $dir`;

$Command ="cp $file ".$vars['DSE']['DSE_VIBK_BACKUP_DIRECTORY']."$file.$DATE_TIME_NOW";
`$Command`;

$Command="echo \"$TIME_NOW cp $file ".$vars['DSE']['DSE_VIBK_BACKUP_DIRECTORY']."$file.$DATE_TIME_NOW\" >> ".$vars['DSE']['DSE_VIBK_LOG_FILE'];
`$Command`;

print "backing up to: ".$vars['DSE']['DSE_VIBK_BACKUP_DIRECTORY']."$file.$DATE_TIME_NOW\n";
//`/usr/bin/vim $file`;
//system("/usr/bin/vim $file");
//pcntl_exec("/usr/bin/vim",array($file));
//exec("/usr/bin/vim $file");
passthru("/usr/bin/vim $file 2>&1");

if(files_are_same($file,$vars['DSE']['DSE_VIBK_BACKUP_DIRECTORY']."$file.$DATE_TIME_NOW")){
	print "No change to $file. backup at ".$vars['DSE']['DSE_VIBK_BACKUP_DIRECTORY']."$file.$DATE_TIME_NOW removed\n";
	$Command="rm -f ".$vars['DSE']['DSE_VIBK_BACKUP_DIRECTORY']."$file.$DATE_TIME_NOW";
	print `$Command`;
	
}else{
	print "$file saved. backup at ".$vars['DSE']['DSE_VIBK_BACKUP_DIRECTORY']."$file.$DATE_TIME_NOW\n";
}
exit();


 

?>
