#!/usr/bin/php
<?
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");

$field_names=array('dev','ino','mode','nlink','uid','gid','rdev','size','atime','mtime','ctime','blksize','blocks');

if($argv[1]=="-h" || $argv[1]=="--help"){
	print "Usage:\n fstat [, seperated fields] [file]\n fields:";
	foreach($field_names as $n=>$field_name){
		print " $field_name";
	}
	print "\n";
	exit(0);
}


$fields=split(",",$argv[1]);
$file=$argv[2];
if(!file_exists($file)){
	print "$file does not exist. exiting.\n";
	exit(-1);
}
$sa=stat($file);

foreach($fields as $field){
	foreach($field_names as $n=>$field_name){
		if($field_name==$field){
			if($DidOne){
				print " ";
			}
			print $sa[$n];
			$DidOne=TRUE;
		}
	}
}


	exit(0);

?>