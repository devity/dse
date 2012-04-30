#!/usr/bin/php
<?

$rpms=`rpm -qa`;
foreach(split("\n",$rpms) as $rpm){

	$exists=trim(`find /backup/rpms -iname ${rpm}*`);
	if( strstr($exists,$rpm)===FALSE ){
		print "extracting $rpm\n";
		print `rpmrebuild -n -b -d /backup/rpms/ $rpm`;
	}else{
		print "$exists exists.\n";
	}

}

exit(0);


$DidSomething=FALSE;
$BHF="~/.bash_history";
$DefaultLinesBackToLimitWithTail=10000;
$CaseInsensitiveFlag="";
$RunCommands=FALSE;
$RunInBackground=FALSE;
$Verbosity=3;

$parameters = array(
  'h' => 'help',
  'r' => 'run',
  'i' => 'insensitivecase',
  'b' => 'background',
  'q' => 'quiet',
  's' => 'stdinin',
  'l:' => 'lines:',
  'v:' => 'verbosity:',
);
$flag_help_lines = array(
  'h' => "\tthis message",
  'r' => "\trun the matching commands",
  'i' => "search the bash history using case insensitivity",
  'b' => "if running, run in background",
  'q' => "quiet - same as -v 0",
  's' => "\tstdinin -  read from stdin",
  'l:' => "\tlines - number of lines to tail from history for search",
  'v:' => "verbosity - 0=none 1=some 2=more 3=debug",
);


$Usage="   Devity bash history utility (finder, repeater, ...)
       by Louy of Devity.com

usage: bh (options) search_string

";

foreach($parameters as $k=>$v){
	$k2=str_replace(":","",$k);
	$v2=str_replace(":","",$v);
	$Usage.=" -${k2}, --${v2}\t".$flag_help_lines[$k]."\n";
}


if($Verbosity>=3) print_r($argv);

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
	print "pt3: "; print_r($argv);print_r($options);
}

if($options['h'] || $options['help']){
	print $Usage;
	$DidSomething=TRUE;
}
if($options['q'] || $options['quiet']){
	$Verbosity=0;
}
if($options['s'] || $options['stdinin']){
	$UseSTDIN=TRUE;
}
if($options['b'] || $options['background']){
	$RunInBackground=TRUE;
}
if(isset($options['r']) || isset($options['run'])){
	$RunCommands=TRUE;
	if($Verbosity>=2)  print "RunCommands set to TRUE - matched commands will be re-exected!!!!! Do test runs!!!\n";
}
if($options['i'] || $options['insensitivecase']){
	print $Usage;
	$CaseInsensitiveFlag="'-i";
}
if($options['l']){
	$DefaultLinesBackToLimitWithTail=$options['l'];
	if($Verbosity>=2) print "DefaultLinesBackToLimitWithTail set to $DefaultLinesBackToLimitWithTail\n";
}elseif($options['lines']){
	$DefaultLinesBackToLimitWithTail=$options['lines'];
	if($Verbosity>=2) print "DefaultLinesBackToLimitWithTail set to $DefaultLinesBackToLimitWithTail\n";
}
if($options['v']){
	$Verbosity=$options['v'];
	if($Verbosity>=2) print "Verbosity set to $Verbosity\n";
}elseif($options['verbosity']){
	$Verbosity=$options['verbosity'];
	if($Verbosity>=2) print "Verbosity set to $Verbosity\n";
}

$MatchingUniqueCommands=0;


$STDIN_Content="";
if($UseSTDIN){
	$fd = fopen("php://stdin", "r"); 
	while (!feof($fd)) {
		$STDIN_Content .= fread($fd, 1024);
	}
}


if($argv[1] || $STDIN_Content){
	if($Verbosity>=2) print "Searching for commands in history containing: \"$argv[1]\"\n";
	$GrepString=$argv[1];
	
	$GrepStringEscaped=str_replace("\"","\\\"",$GrepString);
	$GrepPhrase=" grep $CaseInsensitiveFlag \"$GrepStringEscaped\" ";
	
	$BH="";
	if($STDIN_Content){
		print "using stdin:\n";
		$StreamSTDINContentString=$STDIN_Content;
		$cmd = "tail -n $DefaultLinesBackToLimitWithTail | $GrepPhrase ";
		$descriptorspec = array(   0 => array("pipe", "r"),   1 => array("pipe", "w"));
		$process = proc_open($cmd, $descriptorspec, $pipes);
		if (is_resource($process)) {
 		   fwrite($pipes[0], $StreamSTDINContentString);
 		   fclose($pipes[0]);
 		   $BH = stream_get_contents($pipes[1]);
 		   fclose($pipes[1]);
 		   $return_value = proc_close($process);
		}
	}else{
		$BH_acquire_command="cat ~/.bash_history | tail -n $DefaultLinesBackToLimitWithTail | $GrepPhrase ";
		if($Verbosity>=3) print "BH_acquire_command=$BH_acquire_command\n";
		$BH=`$BH_acquire_command`;
		//$BH=passthru($BH_acquire_command);
	}
	
	
	if($Verbosity>=3) print "BH=$BH\n";
	
	$BH=remove_duplicate_lines($BH);
	
	if($Verbosity>=3) print "duplicates removed BH=$BH\n";
	
	
	foreach(split("\n",$BH) as $Line){
		if($Line){
			$MatchingUniqueCommands++;
			print "$Line";
			if($RunCommands){
				print " ->RUNNING: ";
				if(RunInBackground){
					$Line.=" &";
				}
				passthru($Line);
				//exec('nohup php process.php > process.out 2> process.err < /dev/null &');


			}
		}
		print "\n";
	}
	
	
	$DidSomething=TRUE;
}







if(!$DidSomething){
	print "bh: error! incorrect usage / command arguments\n";
	print $Usage;
}
exit();

/* ************************** FUNCTIONS FUNCTIONS FUNCTIONS FUNCTIONS ************************ */

function str_compare_count_matching_prefix_chars($a,$b){
	$al=strlen($a); $bl=strlen($b);
	//print "a=$a b=$b\n\n";
	$s=0;
	for($c=0;$c<=$al &&$c<=$bl;$c++){
		if($a[$c]==" " && $b[$c]==" "){
			$s=$c+1;
		}
		if($a[$c]!=$b[$c]){
			return $s;
		}
	}
	if($al<$bl){
		return $al;
	}else{
		return $bl;
	}
}



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




function combine_sameprefixed_lines($LogsCombined){
	global $NumberOfBytesSameLimit;
	$Out="";
	$c=0;
	$LastText="";
	foreach(split("\n",$LogsCombined) as $Line){
		$lpa=split(" ",$Line);
		$Date="$lpa[0] $lpa[1] $lpa[2]";
		$Text=substr($Line,strlen($Date)+1);
		$NumberOfBytesSame=str_compare_count_matching_prefix_chars($Text,$LastText);
		if($Text==$LastText){
		}elseif($NumberOfBytesSame>$NumberOfBytesSameLimit){
			$LineNewPart=substr($Line,$NumberOfBytesSame);
			$Out.= ",& $LineNewPart";
			$LastLine="";
			$LastDate="";
			$LastText="";
		}elseif(!(strstr($Line,"ast message repeated")===FALSE)){
		}else{
			if($Line!=""){
				$c++;
				$Out.= "\n$Line";
				$LastLine=$Line;
				$LastDate=$Date;
				$LastText=$Text;
			}
		}
	}
	return $Out;
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
