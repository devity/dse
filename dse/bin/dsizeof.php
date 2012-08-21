#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");



$Verbosity=0;


$parameters = array(
  'r' => 'human',
  'b' => 'block-size',
);
$flag_help_lines = array(
  'r' => "\thuman - human readable",
  'b' => "\block-size - return total space used on disks by used blocks",
);


$Usage="   Devity sizeof utility 
       by Louy of Devity.com

This program will return the size of an object ( file or a directory ).

command line usage: dsizeof (options) object

";
foreach($parameters as $k=>$v){
	$k2=str_replace(":","",$k);
	$v2=str_replace(":","",$v);
	$Usage.=" -${k2}, --${v2}\t".$flag_help_lines[$k]."\n";
}

if($Verbosity>=3) {print "argv="; print_r($argv); print "\n";}

$options = _getopt(implode('', array_keys($parameters)),$parameters);
$pruneargv = array();
foreach ($options as $option => $value) {
  foreach ($argv as $key => $chunk) {
    $regex = '/^'. (isset($option[1]) ? '--' : '-') . $option . '/';
    if ($chunk == $value && $argv[$key-1][0] == '-' || preg_match($regex, $chunk)) {
      array_push($pruneargv, $key);
    }
  }
}
while ($key = array_pop($pruneargv)){
	deleteFromArray($argv,$key,FALSE,TRUE);
}
if($Verbosity>=3) {
	print "argv="; print_r($argv); print "\n";
	print "pruneargv="; print_r($pruneargv); print "\n";
	print "options="; print_r($options); print "\n";
}


$Options="-c";
foreach (array_keys($options) as $opt) switch ($opt) {
	case 'r':
  	case 'human':
  		$OutputHumanReadable=TRUE;
		$Options.="h";
		break;
	case 'b':
  	case 'block-size':
  		$ReturnBlockSize=TRUE;
		break;
}

if(!$OutputHumanReadable){
	$Options.="k";
}

$obj="";
if(sizeof($argv)==1){
	$obj=".";
}else{
	$obj=$argv[1];
}

$command="du $Options $obj 2>&1";
$res=`$command`;
//print "res=$res\n";
if(!(strstr($res,"No such file")===FALSE)){
	print "No such object.\n";
	exit(1);
}

foreach(split("\n",$res) as $L){
	if(!(strstr($L,"total")===FALSE)){
		$size=$L;
	}
}

$size=str_replace("\ttotal","",$size);

if(!$OutputHumanReadable){
	$BLOCKSIZE=1024;
	$Bytes=$size*$BLOCKSIZE;
	print "$Bytes";
}else{
	//$Size_str=dse_file_size_to_readable($Bytes);
	print "$size\n";
}

 
 
 
?>
