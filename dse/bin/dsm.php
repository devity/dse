#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");

// *************************************************************************
// *************************************************************************

$StartTime=time();

$PID=getmypid();
$RunningPID=trim(`ps ux | grep dab | grep bin/php | grep -v grep | grep -v $PID`);
if($RunningPID!=""){
	$RunningPID=str_replace("  "," ",$RunningPID);
	$RunningPID=str_replace("  "," ",$RunningPID);
	$RunningPID=str_replace("  "," ",$RunningPID);
	$RunningPID=str_replace("  "," ",$RunningPID);
	$RunningPID=str_replace("  "," ",$RunningPID);
	$RunningPID=str_replace("  "," ",$RunningPID);
	$pa=split(" ",$RunningPID);
	print "Already running as PID: $pa[1]    under user: $pa[0] \n";
	exit();
}
$CfgFile=$vars['DSE']['DSM_CONFIG_FILE'];

$Verbosity=0;
$StatusOutput="";
$DidSomething=FALSE;


$parameters = array(
  'h' => 'help',
  'c' => 'clean',
  'q' => 'quiet',
  's' => 'stats',
  'v:' => 'verbosity:',
);
$flag_help_lines = array(
  'h' => "\thelp - this message",
  'c' => "\tclean - cleans up (DELETES!) all backups of all files and dirs currently matched by the config file",
  'q' => "quiet - same as -v 0",
  's' => "stats - statistics0",
  'v:' => "\tverbosity - 0=none 1=some 2=more 3=debug",
);


$Usage="   Devity Server Monitor
       by Louy of Devity.com

command line usage: dsm (options)

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
	print "argv="; print_r($argv); print "\noptions="; print_r($options); print "\n";
}


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
	case 'i':
	case 'insensitivecase':
		$CaseInsensitiveFlag="'-i";
		break;
	case 's':
	case 'stats':
		$DoShowStats=TRUE;
		$DidSomething=TRUE;
		break;
	case 'c':
	case 'clean':
		$DoClean=TRUE;
		break;
	case 'v':
		$Verbosity=$options['v'];
		if($Verbosity>=2) print "Verbosity set to $Verbosity\n";
		break;
	case 'verbosity':
		$Verbosity=$options['verbosity'];
		if($Verbosity>=2) print "Verbosity set to $Verbosity\n";
		break;

}



$BackupLocation="";


$CfgData=file_get_contents($CfgFile);
if($CfgData==""){
	print "ERROR opening config file: $CfgFile\n";
}
$DirectoryArray=array();
foreach(split("\n",$CfgData) as $Line){
	$Line=trim($Line);
	if(($Line=="") || (!(strstr($Line,"#")===FALSE)) ){
		//print "CCC\n";
		if(strpos($Line,"#")==0){
			$Line="";
		}else{	
			$Line=substr($Line,0,strpos($Line,"#")-1);
		}
	}
	$lp=split(" ",$Line);
	//print_r($lp);
	if($lp && sizeof($lp)>=2){
		$NickName=$lp[0];
		$SSUrl=$lp[1];
		$SS=http_get($SSUrl);
		
		if($NickName=="EL"){
			$ServerName="W S 1";
		}elseif($NickName=="BD"){
			$ServerName="Batteries Direct";
		}
		
		if($SS==""){
			dse_say("$ServerName stats url down");
		}elseif(!(strstr($SS,"Can't connect")===FALSE)){
			dse_say("$ServerName D B down");
		}
		
	//	print "$NickName:  ";
		$SSa=split("\n",$SS);
		$i=0;
		//print "*******\n";
		foreach($SSa as $S){
			
			
			print " $S";
			$i++;
			
			$pa=split(":",$S);
			//print_r($pa);
	
			
		}
		
		

		//$ServerName=strcut($SS,"",":");
		$Load=strcut($SS,"Load: "," ");
		//print "Load=$Load\n";
		if($Load==""){
			dse_say("$ServerName load unavailable");
		}elseif($Load>4){
			$Load_str=number_format($Load,1);
			dse_say("$ServerName load $Load_str");
		}

		print "\n";
		
	}
}

if($DoShowStats){
	$StatusFileContents=`cat $StatusFile`;
	print "Status File: $StatusFile \n";
	print $StatusFileContents ."\n\n";
	exit;
}


exit();



 
?>
