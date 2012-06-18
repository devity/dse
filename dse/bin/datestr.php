#!/usr/bin/php
<?
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");


$Verbosity=0;
$Format="filename";



// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="Date String";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="returns system time in a few common formats";
$vars['DSE']['DATESTR_VERSION']="v0.01b";
$vars['DSE']['DATESTR_VERSION_DATE']="2012/04/30";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******



$parameters_details = array(
  array('h','help',"this message"),
  array('f','format',"format: [filename,int]"),
  array('','version',"version info"),
);
$parameters=dse_cli_get_paramaters_array($parameters_details);
$Usage=dse_cli_get_usage($parameters_details);



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


$IsSubprocess=FALSE;
foreach (array_keys($options) as $opt) switch ($opt) {
	case 'h':
  	case 'help':
  		$ShowUsage=TRUE;
		$DidSomething=TRUE;
		break;
  	case 'version':
  		$ShowVersion=TRUE;
		$DidSomething=TRUE;
		break;
	case 'q':
	case 'quiet':
		$Verbosity=0;
		break;
	case 'f':
	case 'format':
		$Format=$options[$opt];
		break;
		


}


if($ShowUsage){
	print $Usage;
}
if($ShowVersion){
	print "DSE Version: " . $vars['DSE']['DSE_VERSION'] . "  Release Date: " . $vars['DSE']['DSE_VERSION_DATE'] ."\n";
	print $vars['DSE']['SCRIPT_NAME']." Version: " . $vars['DSE']['DATESTR_VERSION'] . "  Release Date: " . $vars['DSE']['DATESTR_VERSION_DATE'] ."\n";
}
if($argv[1]!=""){
	$Format=$argv[1];
}

//print "Format=$Format\n";

if($DidSomething){
	
}else{
	$tbr="";
	switch($Format){
		case "int":
			$tbr=time();
			break;
		case "filename":
			$tbr=date("YmdHis");
			break;
		case "yyyymmdd":
			$tbr=date("Ymd");
			break;
		default:
			
			break;
	}
	print $tbr;
}
exit(0);


?>