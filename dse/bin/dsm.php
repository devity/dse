#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");

// *************************************************************************
// *************************************************************************


$StartTime=microtime(TRUE);

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
  'e' => 'edit',
);
$flag_help_lines = array(
  'h' => "\thelp - this message",
  'c' => "\tclean - cleans up (DELETES!) all backups of all files and dirs currently matched by the config file",
  'q' => "quiet - same as -v 0",
  's' => "stats - statistics0",
  'v:' => "\tverbosity - 0=none 1=some 2=more 3=debug",
  'e' => "edit backs up and launches a vim of dsm.conf",
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
	case 'e':
	case 'edit':
		passthru("vibk $CfgFile");
		$DidSomething=TRUE;
		breastartk;
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
	$DoMonitorRun=TRUE;
}
dpv(5,"done parsing arguments");


if($DoMonitorRun){
	$BackupLocation="";
	
	//$wget=`which wget`; print "wget=$wget \n"; $path=getenv("PATH"); print "path=$path\n";
	
	dpv(4,"reading/parsing config file: ".$CfgFile);
	$vars[dsm_cfg]=dse_read_config_file($CfgFile);
	
	
	//print "$CfgFile array()=\n";
	//print_r($vars[dsm_cfg]); 
	
	$Hostname=dse_hostname();
	$ServerLoad=get_load();
	
	foreach($vars[dsm_cfg][Monitors] as $MonitorLine){
		print "DoinMonitorLine: $MonitorLine\n";
		list($MonitorType,$MonitorSource,$MonitorThresholds,$MonitorActions)=explode(" ",$MonitorLine);
		switch(strtolower($MonitorType)){
			case 'ssurl';
				$SourceHtml=http_get($MonitorSource);
				if($SourceHtml==""){
					$AlertMsg="ALERT! ssurl fail $MonitorSource";
					print " $AlertMsg\n";
					dsm_log($AlertMsg);
					dsm_do_alert_actions(
						"ALERT! ssurl fail",
						"$MonitorSource",
						$MonitorActions);
				}else{
					$SourceSSArray=unserialize($SourceHtml);
					$MonitorHostname=$SourceSSArray['hostname'];
					//print "SourceSSArray="; print_r($SourceSSArray);print "\n"; 
					$DoThisMonitorAlert=FALSE;
					foreach(explode(",",$MonitorThresholds) as $MonitorThreshold){
						//print " MonitorThreshold=$MonitorThreshold\n";
						if(str_contains($MonitorThreshold,">")){
							list($MonitorVarName,$MonitorVarThreshold)=explode(">",$MonitorThreshold);
							if(array_key_exists($MonitorVarName, $SourceSSArray)){
								if($SourceSSArray[$MonitorVarName]>$MonitorVarThreshold){
									$AlertMsg="ALERT! $MonitorHostname:$MonitorVarName ".$SourceSSArray[$MonitorVarName]." > $MonitorVarThreshold";
									print " $AlertMsg\n";
									dsm_log($AlertMsg);
									dsm_do_alert_actions(
										"$MonitorHostname:$MonitorVarName = ".$SourceSSArray[$MonitorVarName],
										"Thresh >".$MonitorVarThreshold,
										$MonitorActions);
								}else{
									print " OK $MonitorHostname:$MonitorVarName ".$SourceSSArray[$MonitorVarName]."  <= $MonitorVarThreshold\n";
								}
							}
						}elseif(str_contains($MonitorThreshold,"<")){
							list($MonitorVarName,$MonitorVarThreshold)=explode("<",$MonitorThreshold);
							if(array_key_exists($MonitorVarName, $SourceSSArray)){
								if($SourceSSArray[$MonitorVarName]<$MonitorVarThreshold){
									$AlertMsg="ALERT! $MonitorHostname:$MonitorVarName ".$SourceSSArray[$MonitorVarName]." < $MonitorVarThreshold";
									print " $AlertMsg\n";
									dsm_log($AlertMsg);								
									dsm_do_alert_actions(
										"$MonitorHostname:$MonitorVarName = ".$SourceSSArray[$MonitorVarName],
										"Thresh <".$MonitorVarThreshold,
										$MonitorActions);
								}else{
									print " OK $MonitorHostname:$MonitorVarName ".$SourceSSArray[$MonitorVarName]."  >= $MonitorVarThreshold\n";
								}
							}
						}
					}
				} 
				break;
		}
	}
	$RunTime=microtime(TRUE)-$StartTime;
	
	dsm_log("Run Done. ".number_format($RunTime,2)." seconds.");
}

/*
if($vars[dsm_cfg][LoadMax] && $ServerLoad>$vars[dsm_cfg][LoadMax]){
	
	dsm_log("Load: $ServerLoad FAIL, Restarting services");
	dsm_log("Load: $ServerLoad FAIL, Restarting services","alert");
	foreach($vars[dsm_cfg][ServicesToRestart] as $ServiceName){
		if($ServiceName){
			dse_exec("service $ServiceName stop",TRUE,TRUE);
		}
	}

	$MaxTime=time()+50;
	while($ServerLoad>$vars[dsm_cfg][LoadMax]){
		print "Server Load at $ServerLoad - Sleeping until under ".$vars[dsm_cfg][LoadMax]."\n";
		sleep(5);
		if(time()>$MaxTime){
			print "Overtme! Exiting - next pass will restore...\n";
			exit();
		}
		$ServerLoad=get_load();
	}

	foreach($vars[dsm_cfg][ServicesToRestart] as $ServiceName){
		if($ServiceName){
			dse_exec("service $ServiceName start",TRUE,TRUE);
		}
	}
}else{
	print "Server Load: OK - $ServerLoad - max=".$vars[dsm_cfg][LoadMax]."\n";
	dsm_log("Load: $ServerLoad OK");
}

if($vars[dsm_cfg][LocalhostTestURL]){
	print "Testing: ".$vars[dsm_cfg][LocalhostTestURL]." ";
	$html=http_get($vars[dsm_cfg][LocalhostTestURL]);

	if($html!="OK"){
		print " FAIL, Restarting services\n";
		dsm_log("TestURL: FAIL, Restarting services");
		dsm_log("TestURL: FAIL, Restarting services","alert");
		foreach($vars[dsm_cfg][ServicesToRestart] as $ServiceName){
			if($ServiceName){
				dse_exec("service $ServiceName stop",TRUE,TRUE);
				dse_exec("service $ServiceName start",TRUE,TRUE);
			//	dse_exec("service $ServiceName restart",TRUE,TRUE);
			}
		}
		print " Getting headers...\n";
		$headers=http_headers($vars[dsm_cfg][LocalhostTestURL]);
		if(str_contains($headers,"Internal Server Error")){			//do a code check
			$Message="  Internal Server Error!\n $headers \n  Starting a code parse check..";
			print "$Message\n";
			dsm_log("$Message");
			dsm_log("$Message","alert");
			dse_passthru("code_explorer -v 0 -c ".$vars[dsm_cfg][LocalhostTestWebroot],TRUE);
		}else{
			print "  NO server error in headers. html returned=\n$html\n";
		}
	}else{
		print " OK Returned!\n";
	}
	
		
}
 */
 
/*
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
*/
dpv(5,"done reading/parsing config");
if(FALSE && $DoShowStats){
	dse_exec('uptime',FALSE,TRUE);
	dse_exec('cdf',FALSE,TRUE);
	dse_exec('vmstat 1 5',FALSE,TRUE);
	dse_exec('who',FALSE,TRUE);
	$StatusFileContents=`cat $StatusFile`;
	dpv(3, "Status File: $StatusFile");
	print $StatusFileContents ;
dpv(5,"exiting");
	exit;
}


dpv(5,"exiting");
exit();


function dsm_log($Line,$Type=""){
	global $vars;
	$LogFile=$vars[dsm_cfg][LogFile];
	if($Type){
		$LogFile.=".".$Type;
	}
	$TimeStr=dse_date_format();
	$c="echo \"$TimeStr $Line\" >> $LogFile ";
	dse_exec($c);
}
 
 
function dsm_do_alert_actions($Headline,$Msg,$Actions){
	global $vars;
	
	foreach(explode(",",$Actions) as $Action){
		$Action=trim($Action);		
		if($Action){
			print "Doing Action: $Action\n";
			$filename="/tmp/dsm.alert.cache.".sanitize_file_name("$Headline $Msg $Actions");
			if(!file_exists($filename)){			
				if(str_contains($Action,"@")){
					$Subject=$Headline;
					$Body=$Msg;
					mail($Action,$Subject,$Body);
					file_put_contents($filename,time());
					print "	mail($Action,$Subject,$Body); $filename\n";
				}
			}else{
				print "	ALREADY mail($Action,$Subject,$Body); delete $filename to clear\n";
			}
		}
	}
}
 
function sanitize_file_name( $raw ) {
    $escaped = preg_replace('/[^A-Za-z0-9_\-]/', '_', $raw);
	return $escaped;
}
?>
