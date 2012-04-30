#!/usr/bin/php
<?



$ss=$argv[1];
print "Searching for: $ss\n";
$find_cmd="sudo find / -iname $ss 2>/dev/null";
print "Command: $find_cmd\n";
$find_out=`$find_cmd`;

print $find_out ."\n";

exit();

$Verbosity=0;


$parameters = array(
  'r' => 'human',
);
$flag_help_lines = array(
  'r' => "\thuman - human readable",
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
	print "$size";
}











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
