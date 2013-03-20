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
$StatusFile="/var/log/dse/dsm.status";

$vars['Verbosity']=0;
$StatusOutput="";
$DidSomething=FALSE;


$parameters = array(
  'h' => 'help',
  'c' => 'clean',
  'q' => 'quiet',
  's' => 'stats',
  'v:' => 'verbosity:',
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




if($vars['Verbosity']>=3) {print "argv="; print_r($argv); print "\n";}

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
if($vars['Verbosity']>=3) {
	print "argv="; print_r($argv); print "\noptions="; print_r($options); print "\n";
}


dpv(4," parsing arguments");
foreach (array_keys($options) as $opt){
	
dpv(5," parsing argument: ".$opt);
 switch ($opt) {
	case 'h':
  	case 'help':
  		print $Usage;
		$DidSomething=TRUE;
		break;
	case 'q':
	case 'quiet':
		$vars['Verbosity']=0;
		break;
	case 'c':
	case 'clean':
		$DoClean=TRUE;
		$DidSomething=TRUE;
		break;
	case 'v':
	case 'verbosity':
		$vars['Verbosity']=$options[$opt];
		if($vars['Verbosity']>=2) print "Verbosity set to $vars[Verbosity]\n";
		break;
	case 's':
	case 'stats':
		$DoShowStats=TRUE;
		$DidSomething=TRUE;
		break;
 }
}
if(!$DidSomething){
	$DoShowStats=TRUE;
}
dpv(5,"done parsing arguments");


$BackupLocation="";

//$wget=`which wget`; print "wget=$wget \n"; $path=getenv("PATH"); print "path=$path\n";

dpv(4,"reading/parsing config file: ".$CfgFile);
$CfgData=file_get_contents($CfgFile);
if($CfgData==""){
	print "ERROR opening config file: $CfgFile\n";
}
$tbr="";
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
		if($SS==""){
			if(dse_which("wget")){
				dse_say("no w get");
			}else{
				dse_say("Network down");
			}
			//exit(-1);
		}else{
			if($NickName=="EL"){
				$ServerName="W S 1";
			}elseif($NickName=="BD"){
				$ServerName="Batteries Direct";
			}
			
			
			
			$tbr.= "$NickName:  ";
			$SSa=split("\n",$SS);
			$i=0;
			//print "*******\n";
			foreach($SSa as $S){
				$tbr.= " $S";
				$i++;
				$pa=split(":",$S);
				//print_r($pa);
			}
	
			//$ServerName=strcut($SS,"",":");
			$Load=strcut($SS,"Load: "," ");
			//print "Load=$Load\n";
			if($SS==""){
				dse_say("$ServerName stats url down");
			}elseif(!(strstr($SS,"Can't connect")===FALSE)){
				dse_say("$ServerName D B down");
			}elseif(!(strstr($SS,"Server load too high.")===FALSE)){
				$Load=strcut($SS,"Load="," ");
				$Load_str=number_format($Load,1);
				dse_say("$ServerName load $Load_str");
			}elseif($Load==""){
				dse_say("$ServerName load unavailable");
			}elseif($Load>4){
				$Load_str=number_format($Load,1);
				dse_say("$ServerName load $Load_str");
			}
	
			$tbr.= "\n";
		}
	}
	//print $tbr;
}
dse_file_put_contents($StatusFile,$tbr);

dpv(5,"done reading/parsing config");
if($DoShowStats){
	$StatusFileContents=`cat $StatusFile`;
	dpv(3, "Status File: $StatusFile");
	print $StatusFileContents ;
dpv(5,"exiting");
	exit;
}


dpv(5,"exiting");
exit();



 
?>
