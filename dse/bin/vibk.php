#!/usr/bin/php
<?
ini_set('display_errors','On');	
error_reporting(E_ALL & ~E_NOTICE);

$backup_dir="/backup/changed_files";
$log_file="/var/log/vibk.log";

$Script=$argv[0];
$Script=trim(`which $Script`);
print "Script: $Script\n";
$file=$argv[1];
if($file==basename($file)){
	$file=trim(`pwd`)."/".$file;	
}
$TIME_NOW=time();
$DATE_TIME_NOW=trim(`date +"%y%m%d%H%M%S"`);
$dir=dirname("$backup_dir$file.$DATE_TIME_NOW");
`mkdir -p $dir`;
`cp $file $backup_dir$file.$DATE_TIME_NOW`;
`echo "$TIME_NOW cp $file $backup_dir$file.$DATE_TIME_NOW" >> $log_file`;
print "backing up to: $backup_dir$file.$DATE_TIME_NOW\n";
//system("vi $file");
//pcntl_exec("/usr/bin/vim",array($file));
exec("/usr/bin/vim",array($file));
//passtru("/usr/bin/vim",array($file));

print "$file saved. backup at $backup_dir$file.$DATE_TIME_NOW\n";
exit();


 

?>
