#!/usr/bin/php
<?
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	

include_once ("dse_cli_functions.php");
include_once ("dse_config.php");



if($argv[1]=="-?"){
/*	$file=$argv[2];
	$sa=stat($file);
	print "file: $file\n";
	print_r($sa);
	print "\n";
	$Size_str=dse_file_size_to_readable($sa[9]);
	print $Size_str."\n";;*/
}else{
	$file=$argv[1];
	$sa=stat($file);
	print $sa[9];
}
?>