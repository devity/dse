#!/usr/bin/php
<?
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
$vars['Verbosity']=0;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="DSE FSTAT";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="Returns a file's FSTAT info";
$vars['DSE']['SCRIPT_VERSION']="v0.02b";
$vars['DSE']['SCRIPT_VERSION_DATE']="2012/09/28";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
$vars['DSE']['SCRIPT_COMMAND_FORMAT']="file_name|dir_name [fields,fields,...]";
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******



$field_names=array('dev','ino','mode','nlink','uid','gid','rdev','size','atime','mtime','ctime','blksize','blocks');

if($argv[1]=="-h" || $argv[1]=="--help"){
	print "Usage:\n fstat file [, seperated fields]\n fields:";
	foreach($field_names as $n=>$field_name){
		print " $field_name";
	}
	print "\n";
	exit(0);
}


$fields=array("ALL");
if($argv[2]){
	$fields=split(",",$argv[2]);
}
$file=$argv[1];
if(!file_exists($file)){
	print "$file does not exist. exiting.\n";
	exit(-1);
}
$sa=stat($file);

foreach($fields as $field){
	if($field){
		foreach($field_names as $n=>$field_name){
			if($field_name==$field){
				if($DidOne){
					print " ";
				}
				print $sa[$n];
				$DidOne=TRUE;
			}elseif($field=="ALL"){
				if($DidOne){
					print " ";
				}
				print $field_name."=".$sa[$n];
				$DidOne=TRUE;
			}
		}
	}
}

	exit(0);

?>