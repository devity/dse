#!/usr/bin/php
<?
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");


$Verbosity=0;
$UniqueOutput=TRUE;

$Script=$argv[0];

if(sizeof($argv)>1){
	$search_str=$argv[1];
}else{
	$search_str=fgets(STDIN);
}

$dollar='$';

$exes="";
$pids=`./grep2pid.php $search_str`;
foreach(split(" ",$pids) as $PID){
	if($PID){
		$cmd="ls -ald --color=never /proc/$PID/exe | awk '{ print ".$dollar."11 }'";
		
		$PATH=trim(`$cmd`);
		
		if($Verbosity>=3) print "sub.cmd: $cmd\n";
		$exes.="$PATH\n";
	}
}

if($Verbosity>=2){
	print "Script: $Script\n";
	print "Command line: $Script $search_str\n";
	print_r($argv)."\n";
	print "cmd: $cmd\n";
	print "pids: $pids\n";
}


$PID=trim(`$cmd`);

if($UniqueOutput){
	$exes=remove_duplicate_lines($exes);	
}
if($Verbosity>=2){
	print "exes: ";
}
print "$exes";




function remove_duplicate_lines($Lines){
	$out=array();
	foreach(split("\n",$Lines) as $Line){
		$Found=FALSE;
		for($i=0;$i<sizeof($out);$i++){
			if($out[$i]==$Line){
				$Found=TRUE;		
			}
		}
		if(!$Found){
			$out[]=$Line;
		}
	}
	$Out2="";
	foreach($out as $Line){
		if($Out2){
			$Out2.="\n";
		}
		$Out2.=$Line;
	}
	return $Out2;
}



?>