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


$parameters_details = array(
  array('h','help',"this message"),
  array('q','quiet',"same as --verbosity 0"),
  array('l','list-backups',"lists when backups were made of file"),
  array('y','verbosity:',"0=none 1=some 2=more 3=debug"),
);
$vars['parameters']=dse_cli_get_paramaters_array($parameters_details);
$vars['Usage']=dse_cli_get_usage($parameters_details);
$vars['argv_origional']=$argv;
dse_cli_script_start();
		
$BackupBeforeUpdate=TRUE;
foreach (array_keys($vars['options']) as $opt) switch ($opt) {
	case 'q':
	case 'quiet':
		$Quiet=TRUE;
		$vars['Verbosity']=0;
		break;
  	case 'y':
	case 'verbosity':
		$vars['Verbosity']=$vars['options'][$opt];
		if($vars['Verbosity']>=2) print "Verbosity set to ".$vars['Verbosity']."\n";
		break;
	case 'h':
  	case 'help':
  		$ShowUsage=TRUE;
		$DidSomething=TRUE;
		break;
	case 'l':
	case 'list-backups':
  		$ListBackups=TRUE;
		$DidSomething=TRUE;
		break;}


dse_cli_script_header();



	
if($ShowUsage){
	print $vars['Usage'];
}

$file=$argv[1];
if($file==basename($file)){
	$file=trim(`pwd`)."/".$file;	
}

if($ListBackups){
	$backupfilename=$vars['DSE']['DSE_VIBK_BACKUP_DIRECTORY']."$file.*";
	$dir=dse_ls($backupfilename);
//	print_r($dir);
	foreach($dir as $f){
		$name=$f[1];
		$time_str=strcut($name,$file.".");
		$name_orig=strcut($name,"",".$time");
		//$time_str=date("D M j G:i:s T Y", $time);
		print "--------- $time_str $name\n";
		if($last_file){
			print `diff -wBEdy $last_file $name`;
		}
		$last_file=$name;
		
	}
	print "current $name_orig\n";
}else{
	
	$backupfilename=dse_file_backup($file);
	print "backing up to: $backupfilename\n";
	
	$vim="/usr/bin/vim";
	if(!file_exists($vim)){
		$vim="/usr/bin/vi";
		if(!file_exists($vim)){
			$vim=trim(`which vi`);
			if(!file_exists($vim)){
				print "ERROR no vi present.\n";
				exit(-102);
			}
		}
	}
	
	passthru("$vim $file 2>&1");
	
	if(files_are_same($file,$backupfilename)){
		print "No change to $file. backup at $backupfilename removed\n";
		$Command="rm -f $backupfilename";
		print `$Command`;
		
	}else{
		print "$file saved. backup at $backupfilename\n";
	}
}
exit();




?>
