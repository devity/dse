#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="Denote";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="knowledge engine / idea jotpad / query database";
$vars['DSE']['VIBK_VERSION']="v0.01b";
$vars['DSE']['VIBK_VERSION_DATE']="2014/06/10";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
$vars['DSE']['SCRIPT_COMMAND_FORMAT']="[options]";
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

print "Script: ".$vars['DSE']['SCRIPT_FILENAME']."\n";

$parameters_details = array(
  array('h','help',"this message"),
  array('q','quiet',"same as --verbosity 0"),
  array('l','list-backups',"lists when backups were made of file"),
  array('y:','verbosity:',"0=none 1=some 2=more 3=debug"),  
);
$vars['parameters']=dse_cli_get_paramaters_array($parameters_details);
$vars['Usage']=dse_cli_get_usage($parameters_details);
$vars['argv_origional']=$argv;
dse_cli_script_start();
		
$ShowDiff=FALSE;
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
		break;
	
}

$args="";
foreach($argv as $i=>$arg){
	if($i>0){
		if($args){
			$args.=" ";
		}
		$args.=$arg;
	}
}

if($argv[1]=="a"){
	$args="";
	foreach($argv as $i=>$arg){
		if($i>1){
			if($args){
				$args.=" ";
			}
			$args.=$arg;
		}
	}
	$data=$args."\n";
	$filename="/backup/denote.txt";
	file_put_contents($filename, $data, FILE_APPEND);
	print "Added: $args\n";	
	exit();
}elseif($argv[1]=="s"){
	$args="";
	foreach($argv as $i=>$arg){
		if($i>1){
			if($args){
				$args.=" ";
			}
			$args.=$arg;
		}
	}
	$data=$args;
	$data=str_replace("\"", "", $data);
	$filename="/backup/denote.txt";
	$command="grep \"$data\" \"$filename\"";
	$r=dse_exec($command,FALSE,FALSE);
	$data_green=colorize($data,"green","black");
	$r=str_replace($data, $data_green, $r);	
	print "Search Results:\n$r\n";	
	exit();
}
//print "args=$args\n";

//dse_cli_script_header	();



	
if($ShowUsage){
	print $vars['Usage'];
	exit();
}





exit();




?>
