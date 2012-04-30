#!/usr/bin/php
<?
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("dse_cli_functions.php");
include_once ("dse_config.php");

$RunTime=60;
$Threads=5;
$Verbosity=0;


// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="HTTP Stress Test";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="Multi-threaded HTTP Stress Tester.";
$vars['DSE']['DSE_HTTP_STRESS_VERSION']="v0.06b";
$vars['DSE']['DSE_HTTP_STRESS_VERSION_DATE']="2012/04/30";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******


$parameters_details = array(
  array('h','help',"this message"),
  array('q','quiet',"same as -v 0"),
  array('t:','threads:',"# of threads to run simultaniously"),
  array('v:','verbosity:',"0=none 1=some 2=more 3=debug"),
  array('r:','runtime:',"run time in seconds"),
  array('s','subprocess',"(non-user option) start as subprocess of multi-threaded run"),
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
  		print $Usage;
		$DidSomething=TRUE;
		break;
	case 'q':
	case 'quiet':
		$Verbosity=0;
		break;
	case 't':
		$Threads=$options['t'];
		if($Verbosity>=2) print "# Threads set to $Threads\n";
		break;
	case 'threads':
		$Threads=$options['threads'];
		if($Verbosity>=2) print "# Threads set to $Threads\n";
		break;
	case 's':
	case 'subprocess':
		$IsSubprocess=TRUE;
		$Log=$ThreadLog;
		if($Verbosity>=2) print "IsSubprocess set to TRUE\n";
		break;
	case 'v':
		$Verbosity=$options['v'];
		if($Verbosity>=2) print "Verbosity set to $Verbosity\n";
		break;
	case 'verbosity':
		$Verbosity=$options['verbosity'];
		if($Verbosity>=2) print "Verbosity set to $Verbosity\n";
		break;
	case 'r':
		$RunTime=$options['r'];
		if($Verbosity>=2) print "RunTime set to $RunTime\n";
		break;
	case 'runtime':
		$RunTime=$options['runtime'];
		if($Verbosity>=2) print "RunTime set to $RunTime\n";
		break;

}


if($DidSomething){
	exit();
}
if($Verbosity>=2){
	print "Script: ".$vars['DSE']['SCRIPT_FILENAME']."\n";
}

$End=$StartTime+$RunTime;
$StartLoad=get_load();
  

$Command="cat ".$vars['DSE']['DSE_HTTP_STRESS_INPUT_URLS_FILE'];
$URLsRaw=`$Command`;
if($URLsRaw==""){
	print "Error opening: ".$vars['DSE']['DSE_HTTP_STRESS_INPUT_URLS_FILE']."\n";
	exit(-2);
}
$URLsArray=split("\n",$URLsRaw);
$URLnum=sizeof($URLsArray);

if($Verbosity>=2){
	print "Starting Stress with $URLnum URLs.\n";
	if($Verbosity>=3) print_r($URLsArray);
	print "Threads=$Threads\n";
	print "IsSubprocess=".debug_tostring($IsSubprocess)."\n";
}


if($Threads>0 && !$IsSubprocess){
	print "Starting in multi-threaded mode. # Threads=$Threads\n";
	if(file_exists($vars['DSE']['DSE_HTTP_STRESS_THREAD_LOG_FILE'])){
		$Command ="rm ".$vars['DSE']['DSE_HTTP_STRESS_THREAD_LOG_FILE'];
		`$Command`;
		$Command ="touch ".$vars['DSE']['DSE_HTTP_STRESS_THREAD_LOG_FILE'];
		`$Command`;
	}
}else{
	print "Starting in single-threaded mode.\n";
	
}

//exit();


if($Threads>0 && !$IsSubprocess){
	//start processes
	if($Verbosity>=2) print "Starting $Threads processes.\n";
	for($i=0;$i<$Threads;$i++){
		if($Verbosity>=2) print " Starting process #".($i+1).".\n";
		$amp='&';
		$cmd=$vars['DSE']['SCRIPT_FILENAME']." -subprocess -r $RunTime -v $Verbosity >/dev/null 2>&1 $amp";
		system($cmd);
	}
	
	if($Verbosity>=2) print "Done starting processes.\n";
	
	
	
	$Done=FALSE;
	while(!$Done){
		
		sleep(2);
		
		if(file_exists($ThreadLog)){
			$ThreadsDone=intval(trim(`wc -l $ThreadLog`));
			$Done=($ThreadsDone==$Threads);
		}else{
			$ThreadsDone=0;	
		}
	
		
		cbp_screen_clear();
		sbp_cursor_postion(0,0);
		
		
		print "$ThreadsDone threads out of $Threads total reported results so far.\n";
		
		$ActualRunTime=time()-$StartTime;
		$TimeLeft=$RunTime-$ActualRunTime;
		print "Goal Run Time: $RunTime seconds\n";
		print "Actual Run Time: $ActualRunTime seconds\n";
		print "Run Time Left: $TimeLeft seconds\n";
	}
	
	cbp_screen_clear();
	sbp_cursor_postion(0,0);
	
	
	//show results
	$Totals_array=array();
	$Results=`cat $ThreadLog`;
	$Results_array=split("\n",$Results);
	$i=1;
	foreach($Results_array as $Result){
		if($Result){
			$Result_array=split(":",$Result);
			print "#$i  ";
			foreach($Result_array as $Result_part){
				$Result_part_array=split("=",$Result_part);
				$name=$Result_part_array[0];
				$value=$Result_part_array[1];
				if($name!="runstart" && $name!="runlength"){
					print "$name: $value  ";
					$Totals_array[$name]+=$value;
				}
			}
			print "\n";
			$i++;
		}
	}
	//print_r($Totals_array);
	
	
	$Loads=$Totals_array[loads];
	$TotalSize=$Totals_array[sizetotal];
	
	$EndLoad=get_load();
	$ActualRunTime=time()-$StartTime;
	
	print "Goal Run Time: $RunTime seconds\n";
	print "Actual Run Time: $ActualRunTime seconds\n";
	print "Server Load: at start: $StartLoad at end: $EndLoad\n";
	print "Page Loads: $Loads\n";
	$LoadsPerSecond=number_format($Loads/$RunTime,2);
	print "Loads per Second: $LoadsPerSecond\n";
	$AvgLoadTime=number_format($RunTime/$Loads,2);
	print "Avg Load Time: $AvgLoadTime seconds\n";
	$AvgSizeRaw=$TotalSize/$Loads;
	$AvgSize=number_format($TotalSize/$Loads,0);
	print "Avg Size: $AvgSize Bytes\n";
	$TotalSizeStr=number_format($TotalSize,0);
	print "Total Data Received: $TotalSizeStr Bytes\n";
	$Mbps=( ($TotalSize*8) / (1024*1024) ) /$RunTime;
	$MbpsStr=number_format( ($Mbps) ,3);
	print "Avg Download Rate: $MbpsStr Mb/s\n";
	$ActualRunTime=time()-$StartTime;
		
	
	$log_line="threads=$Threads:runstart=$StartTime:runlength=$RunTime:actualruntime=$ActualRunTime:loadstart=$StartLoad:loadend=$EndLoad:loads=$Loads:lps=$LoadsPerSecond:sizeavg=$AvgSizeRaw:sizetotal=$TotalSize:Mbps=$Mbps";
	$Command="echo $log_line >> ".$vars['DSE']['DSE_HTTP_STRESS_LOG_FILE'];
	print `$Command`;
	

}else{

	
	
	$Loads=0;
	$TotalSize=0;
	while(time()<$End){
		
		$URL="http://www.batteriesdirect.com/";
		$URL=trim($URLsArray[rand(0,$URLnum-1)]);
		if($URL!=""){
		$r=`wget -q -O - $URL`;
		$Size=strlen($r);
		$TotalSize+=$Size;
		$Loads++;
		$TimeLeft=($End)-time();
		cbp_screen_clear();
	        sbp_cursor_postion(0,0);
		$LastLoad=$Load;
		$Load=get_load();
		$TimeIn=time()-$StartTime;
		print "Loads: $Loads     Time Left: $TimeLeft    Loading Now: $URL  \n";
		print "Strss Run Time: $RunTime seconds\n";
		print "Server Load: at start: $StartLoad   now: $Load\n";
		print "Page Loads: $Loads\n";
		$LoadsPerSecond=number_format($Loads/$TimeIn,2);
		print "Loads per Second: $LoadsPerSecond\n";
		$AvgLoadTime=number_format($TimeIn/$Loads,2);
		print "Avg Load Time: $AvgLoadTime seconds\n";
		$AvgSize=number_format($TotalSize/$Loads,0);
		print "Avg Size: $AvgSize Bytes\n";
		$TotalSizeStr=number_format($TotalSize,0);
		print "Total Data Received: $TotalSizeStr Bytes\n";
		$MbpsStr=number_format( ( ($TotalSize*8) / (1024*1024) ) /$TimeIn,3);
		print "Avg Download Rate: $MbpsStr Mb/s\n";
	
		while( !(strstr($r,"\n")===FALSE))	$r=str_replace("\n","",$r);
		while( !(strstr($r," ")===FALSE))       $r=str_replace(" ","",$r);
		while( !(strstr($r,"\t")===FALSE))       $r=str_replace("\t","",$r);
		while( !(stristr($r,"&nbsp;")===FALSE))       $r=str_ireplace("&nbsp;","",$r);
		print "\n".substr($r,0,11000);
		}
	}
	
	cbp_screen_clear();
	sbp_cursor_postion(0,0);
	$EndLoad=get_load();
	
	print "Strss Run Time: $RunTime seconds\n";
	print "Server Load: at start: $StartLoad at end: $EndLoad\n";
	print "Page Loads: $Loads\n";
	$LoadsPerSecond=number_format($Loads/$TimeIn,2);
	print "Loads per Second: $LoadsPerSecond\n";
	$AvgLoadTime=number_format($TimeIn/$Loads,2);
	print "Avg Load Time: $AvgLoadTime secondsn";
	$AvgSizeRaw=$TotalSize/$Loads;
	$AvgSize=number_format($TotalSize/$Loads,0);
	print "Avg Size: $AvgSize Bytes\n";
	$TotalSizeStr=number_format($TotalSize,0);
	print "Total Data Received: $TotalSizeStr Bytes\n";
	$Mbps=( ($TotalSize*8) / (1024*1024) ) /$TimeIn;
	$MbpsStr=number_format( ($Mbps),3);
	print "Avg Download Rate: $MbpsStr Mb/s\n";
	$ActualRunTime=time()-$StartTime;
	
	$log_line="runstart=$StartTime:runlength=$RunTime:actualruntime=$ActualRunTime:loadstart=$StartLoad:loadend=$EndLoad:loads=$Loads:lps=$LoadsPerSecond:sizeavg=$AvgSizeRaw:sizetotal=$TotalSize:Mbps=$Mbps";
	print `echo $log_line >> $Log`;
	print `echo $log_line`;

}


if($Threads>0 && !$IsSubprocess){
	if(file_exists($ThreadLog)){
		`rm $ThreadLog`;
	}
}

exit();



?>
