#!/usr/bin/php
<?
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");


$Verbosity=0;

$parameters = array(
  'h' => 'help',
  'v' => 'verbose',
  't:' => 'type:',
);
$flag_help_lines = array(
  'h' => "\thelp - this message",
  'v' => "\tverbose - more info",
  't:' => "\ttype - obj type",
);

$Usage="   Devity find utility     by Louy of Devity.com

This program will find an object ( file or a directory ).

command line usage:    fi (options) partial_object_name

";
foreach($parameters as $k=>$v){
	$k2=str_replace(":","",$k);
	$v2=str_replace(":","",$v);
	$Usage.=" -${k2}, --${v2}\t".$flag_help_lines[$k]."\n";
}

if($Verbosity>=3) {print "argv="; print_r($argv); print "\n";}

$options = getopt(implode('', array_keys($parameters)), $parameters);
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

if($options['v'] || $options['verbose']){
	$Verbosity=3;
}

if($Verbosity>=3) {
	print "argv="; print_r($argv); print "\n";
	print "pruneargv="; print_r($pruneargv); print "\n";
	print "options="; print_r($options); print "\n";
}


$Options="";
foreach (array_keys($options) as $opt) switch ($opt) {
	case 'r':
	case 'human':
  		//$OutputHumanReadable=TRUE;
		//$Options.=" -h";
		break;
	
	case 'h':
  	case 'help':
  		print $Usage;
		$DidSomething=TRUE;
		break;
}


if($options['t']){
	if($options['t']=="p"){
		$SearchProcesses=TRUE;
	}else{
		$SearchProcesses=FALSE;
	}
}elseif($options['type']){
	if($options['type']=="p"){
		$SearchProcesses=TRUE;
	}else{
		$SearchProcesses=FALSE;
	}
}




$obj="";
if(sizeof($argv)==1){
	$obj=".";
}else{
	$obj=$argv[1];
	$Options.=" -iname \"$obj\"";
}

$root_dir="/";
if($SearchProcesses){
	$command="ps aux | grep $obj ";
}else{
	$command="find $root_dir $Options -exec {} \; 2>/dev/null ";
}

if($Verbosity>=2) { print "Command: $command\n"; }
$tbr=`$command`;

print $tbr;
exit(0);








/*
* This function deletes the given element from a one-dimension array
* Parameters: $array:    the array (in/out)
*             $deleteIt: the value which we would like to delete
*             $useOldKeys: if it is false then the function will re-index the array (from 0, 1, ...)
*                          if it is true: the function will keep the old keys
*				$useDeleteItAsIndex: uses deleteIt for compare against array index/key instead of values
* Returns true, if this value was in the array, otherwise false (in this case the array is same as before)
*/
function deleteFromArray(&$array, $deleteIt, $useOldKeys = FALSE, $useDeleteItAsIndex=FALSE ){
    $tmpArray = array();
    $found = FALSE;
   // print "array="; print_r($array); print "\n";
    foreach($array as $key => $value)
    {
    	//print "k=$key v=$value \n";
        if($useDeleteItAsIndex){
        	$Match=($key !== $deleteIt)==TRUE;
        }else{
        	$Match=($value !== $deleteIt)==TRUE;
        }
        
        if($Match){
        	if($useOldKeys){
        	    $tmpArray[$key] = $value;
            }else{
                $tmpArray[] = $value;
            }
        }else{
            $found = TRUE;
        }
    }
    $array = $tmpArray;
    return $found;
}
 
 
 
 
?>
