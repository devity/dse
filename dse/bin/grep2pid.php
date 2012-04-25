#!/usr/bin/php
<?
ini_set('display_errors','On');	
error_reporting(E_ALL & ~E_NOTICE);
$Verbosity=0;


$Script=$argv[0];
$search_str=$argv[1];


										
$dollar='$';
$cmd="ps aux | grep \"$search_str\" | grep -v grep | awk '{ print ${dollar}2 }'";


if($Verbosity>=2){
	print "Script: $Script\n";
	print "Command line: $Script $search_str\n";
	print_r($argv)."\n";
	print "cmd: $cmd\n";
}


$PID=trim(`$cmd`);


if($Verbosity>=2){
	print "PID: ";
}
$PID=str_replace("\n"," ",$PID);
print trim($PID);



?>