#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
$vars['Verbosity']=1;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="DSE Load Balancer";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="basic service monitoring and load balancing";
$vars['DSE']['SCRIPT_VERSION']="v0.01b";
$vars['DSE']['SCRIPT_VERSION_DATE']="2012/06/23";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
$vars['DSE']['SCRIPT_COMMAND_FORMAT']="";
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

global $CFG_array;
$CFG_array=array();
$CFG_array['QueriesMade']=0;
$CFG_array['QueriesSucceeded']=0;
$CFG_array['QueriesFailed']=0;
$CFG_array=dse_read_config_file($vars['DSE']['DLB_CONFIG_FILE'],$CFG_array);	
$vars['DSE']['SCRIPT_LOG_FILE']=$CFG_array['LogFile'];	
$vars['DSE']['SCRIPT_LOG_LEVEL']=$CFG_array['LogLevel'];	
if($CFG_array['DefaultLogShowLines']) $vars['DSE']['LOG_SHOW_LINES']=$CFG_array['DefaultLogShowLines'];	else $vars['DSE']['LOG_SHOW_LINES']=25;
$RunningPID=dse_dlb_is_running();
			

$parameters_details = array(
  array('l','log-to-screen',"log to screen too"),
  array('','log-show:',"shows tail of log ".$CFG_array['LogFile']."  argv1 lines"),
  array('h','help',"this message"),
  array('q','quiet',"same as --verbosity 0"),
  array('v:','verbosity:',"0=none 1=some 2=more 3=debug"),
  array('s','status',"prints status file".$CFG_array['StatusFile']),
  array('e','edit',"backs up and launches a vim of ".$vars['DSE']['DLB_CONFIG_FILE']),
  array('c','config-show',"prints contents of ".$vars['DSE']['DLB_CONFIG_FILE']),
  array('d:','daemon:',"manages the checking daemon. options: [start|stop|restart|status]"),
  array('r:','request-from-pool:',"returns an UP node from service_pool=arg1"),
);
$vars['parameters']=dse_cli_get_paramaters_array($parameters_details);
$vars['Usage']=dse_cli_get_usage($parameters_details);
$vars['argv_origional']=$argv;
dse_cli_script_start();
		
$BackupBeforeUpdate=TRUE;
foreach (array_keys($vars['options']) as $opt) switch ($opt) {
	case 'l':
	case 'log-to-screen':
		$vars['DSE']['LOG_TO_SCREEN']=TRUE;
		dpv(2,"Logging to screen ON\n".$vars['DSE']['LOG_TO_SCREEN']);
		$vars['LOG_TO_SCREEN']=TRUE;
		break;
	case 'log-show':
		if($vars['options'][$opt]) $Lines=$vars['options'][$opt]; else $Lines=$vars['DSE']['LOG_SHOW_LINES'];
		$Command="tail -n $Lines ".$CFG_array['LogFile'];
		print `$Command`;
		exit(0);
	case 'q':
	case 'quiet':
		$Quiet=TRUE;
		$vars['Verbosity']=0;
		break;
  	case 'v':
	case 'verbosity':
		$vars['Verbosity']=$vars['options'][$opt];
		dpv(2,"Verbosity set to ".$vars['Verbosity']."\n");
		break;
	case 's':
  	case 'status':
		if($RunningPID>0){
			print "DLB Daemon is RUNNING PID=$RunningPID\n";
		}else{
			print "DLB Daemon is NOT RUNNING!\n";
		}
		dpv(1,dse_file_get_contents($CFG_array['StatusFile']));
		exit(0);
	case 'h':
  	case 'help':
		print $vars['Usage'];
		exit(0);
	case 'e':
	case 'edit':
		$Message="Backing up ".$vars['DSE']['DLB_CONFIG_FILE']." and launcing in vim:\n";
		dpv(1,$Message);
		dse_log($Message);
		passthru("/dse/bin/vibk ".$vars['DSE']['DLB_CONFIG_FILE']." 2>&1");
		exit(0);
	case 'c':
  	case 'config-show':
		print dse_file_get_contents($vars['DSE']['DLB_CONFIG_FILE']);
		exit(0);
}

foreach (array_keys($vars['options']) as $opt) switch ($opt) {
  	case 'r':
	case 'request-from-pool':
		$CFG_array['QueriesMade']++;
		if($RunningPID<=0){
			dse_log("query pool $NodesPool NO-DAEMON");	
			print "NO-DAEMON";
			$CFG_array['QueriesFailed']++;
			exit(-1);
		}
		$NodesPool=$vars['options'][$opt];
		print dse_dlb_get_up_node($NodesPool);
		exit(0);
  	case 'd':
	case 'daemon':
		$CFG_array=dse_read_config_file($vars['DSE']['DLB_CONFIG_FILE']);
		
		switch($vars['options'][$opt]){
			case 'restart':
				if($RunningPID>0){
					$r=`kill $RunningPID 2>&1`;
					dpv(1,"Killing process PID $RunningPID\n");
					dse_log("DLB stop. Killing process PID $RunningPID");
				}
				dse_dlb_daemon($CFG_array);
				$DidSomething=TRUE;
				break;	
			case 'start':
				if($RunningPID>0){
					dpv(1,"DLB Already Running as PID $RunningPID!\n");
				}else{
					dse_dlb_daemon($CFG_array);
				}
				$DidSomething=TRUE;
				break;	
			case 'stop':
				if($RunningPID>0){
					$r=`kill $RunningPID 2>&1`;
					dpv(1, "Killing process PID $RunningPID\n");
					dse_log("DLB stop. Killing process PID $RunningPID");
				}else{
					dpv(1, "DLB Not Running!\n");
				}
				$DidSomething=TRUE;
				break;	
			case 'status':
				if($RunningPID>0){
					dpv(0, "DLB Running as PID $RunningPID!\n");
					dpv(1,print "Status File: ".$CFG_array['StatusFile']."  ---------------------___________\n");
					print dse_file_get_contents($CFG_array['StatusFile'])."\n";
				}else{
					print "DLB Not Running!\n";
				}
				$DidSomething=TRUE;
				break;	
		}
		break;
}



dse_cli_script_header();


if($DidSomething){
	if(!$Quiet && !$DoSetEnv){
		dpv(1, getColoredString($vars['DSE']['SCRIPT_NAME']." Done. Exiting (0)","black","green"));
		$vars['shell_colors_reset_foreground']='';	print getColoredString("\n","white","black");
	}
	if(!$NoExit) exit(0);
}else{
	if(!$Quiet && !$DoSetEnv){
		dpv(1, getColoredString("Nothing to do! try --help for usage. ".$vars['DSE']['SCRIPT_NAME']." Done. Exiting (-1)","pink","black"));
		$vars['shell_colors_reset_foreground']='';	print getColoredString("\n","white","black");
	}
	if(!$NoExit) exit(-1);
}


exit(0);
// --------------------------------------------------------------------------------
// **********************************************************************************

function dse_dlb_is_running(){
	global $vars,$CFG_array;
	if(!file_exists($CFG_array['PIDFile'])){
		return "NO-PID-FILE";
	}
	$RunningPID=intval(trim(dse_file_get_contents($CFG_array['PIDFile'])));
	if(!$RunningPID){
		return "NO-PID-IN-PID-FILE";
	}
	$OwnerUser=dse_pid_get_ps_columns($RunningPID,"user");
	if(!$OwnerUser){
		return "PID-NOT-RUNNING";
	}
	return $RunningPID;
}

function dse_dlb_config_parse(){
	global $vars,$CFG_array;
	$DLB_array=array();
	$DLB_array['Services']=$CFG_array['Services'];
	foreach(split(" ",$CFG_array['Services']) as $Service){
		if($Service){
			$DLB_array[$Service]=array();
			$DLB_array[$Service]['Name']=$Service;
			$DLB_array[$Service]['NodesRaw']=array();
			$DLB_array[$Service]['Nodes']=array();
			$DLB_array[$Service]['NodeStatus']=array();
			$DLB_array[$Service]['NodeCountTotal']=0;
			$DLB_array[$Service]['NodeCountUP']=0;
			$DLB_array[$Service]['NodeCountDown']=0;
			$VarName="Service.$Service.Node";
			if(is_array($CFG_array[$VarName])){
				foreach($CFG_array[$VarName] as $Node){
					if($Node){
						$NodeA=split(" ",$Node);
						dpv(2, " adding $Service node: $Node\n");
						$DLB_array[$Service]['NodesRaw'][]=$Node;
						if($NodeA[1]){
							$DLB_array[$Service]['Nodes'][$NodeA[1]]=$NodeA;
							$DLB_array[$Service]['NodeStatus'][$NodeA[1]]="UNKNOWN";
						}else{
							$DLB_array[$Service]['Nodes'][]=$NodeA;
							$DLB_array[$Service]['NodeStatus'][]="UNKNOWN";
						}
						$DLB_array[$Service]['NodeCountTotal']++;
						}
				}
			}
			if(sizeof($DLB_array[$Service]['Nodes'])==0){
				$Warning="Warning service_pool $Service Listed in ConfigFile.Services but no has Nodes!";
				dpv(1, $Warning."\n");
				dse_log($Warning);
			}
		}
	}
	return $DLB_array;
}
	 
	 
function dse_dlb_daemon(){
	global $vars,$CFG_array;
	
	$PID=getmypid();
	file_put_contents($CFG_array['PIDFile'],$PID);
	dpv(4, "file_put_contents($CFG_array[PidFile],$PID);\n");
	
	dpv(2, "Running as DLB Daemon...\n");
	dse_log("DLB Daemon Starting. PID=$PID Log=".$CFG_array['LogFile']." Status=".$CFG_array['StatusFile']);
	$DLB_array=dse_dlb_config_parse($CFG_array);
	//print_r($DLB_array);
	
	$CheckSeconds=dse_time_span_sting_to_seconds($CFG_array['DefaultUpCheckFrequency']);
	dpv(3, " Checking every $CheckSeconds seconds\n");
	
	$DoLoop=TRUE;
	while($DoLoop){
		$DLB_array=dse_dlb_services_check($DLB_array);
		if($CFG_array['RandomizeCheckFrequency']) {
			$ThisSleep=rand($CheckSeconds/2,$CheckSeconds*2);
		}else{
			$ThisSleep=$CheckSeconds;
		}
		sleep($ThisSleep);
	}
}



function dse_dlb_services_check($DLB_array){
	global $vars,$CFG_array;
	dpv(3, " Starting Services Checking...\n"); $CheckTimeSeconds=time();
	dse_log("Starting Services Checking...");
	$NodesChecked=0; $NodesCheckedOK=0; $NodesCheckedBad=0;
	foreach(split(" ",$CFG_array['Services']) as $Service){
		if($Service){
			if(sizeof($DLB_array[$Service]['Nodes'])>0){
				
				dpv(4, "  Checking $Service Nodes:\n");
				
				//$DLB_array[$Service][Name]
				//$DLB_array[$Service][NodesRaw]
				//$DLB_array[$Service][Nodes]
				$ServiceNodesChecked=0; $ServiceNodesCheckedOK=0; $ServiceNodesCheckedBad=0;
				$ServiceHasNodeUP=FALSE;
				$DLB_array[$Service]['NodeCountUP']=0; $DLB_array[$Service]['NodeCountDown']=0;
				foreach($DLB_array[$Service]['Nodes'] as $k=>$Node){
					
					list($NodeIP,$NodePort)=split(":",$Node[0]);
					$NodeName=$Node[1];
					dpv(4, "   Checking Node: $NodeIP Port $NodePort -  $NodeName... ");
					$Listening=dse_ip_port_is_listening($NodeIP,$NodePort);
					if($Listening){
						dpv(4, "Listening");
						$NodesCheckedOK++; $ServiceNodesCheckedOK++;
						$ServiceHasNodeUP=TRUE;
						$DLB_array[$Service]['NodeCountUP']++;
						$DLB_array[$Service]['NodeStatus'][$k]="UP";
					}else{
						dpv(4, "ERROR - NOT Listening!");
						dse_log("ERROR - node DOWN: $Service $NodeIP:$NodePort $NodeName");	
						$NodesCheckedBad++; $ServiceNodesCheckedBad++;
						$DLB_array[$Service]['NodeCountDown']++;
						$DLB_array[$Service]['NodeStatus'][$k]="DOWN";
					}
					dpv(4, "\n");
					$NodesChecked++; $ServiceNodesChecked++;
				}
				if(!$ServiceHasNodeUP){
					dpv(2, "FATAL ALERT! $Service has no nodes UP !\n");
				}
			}
		}
	}
	$CheckTimeSeconds=time()-$CheckTimeSeconds;
	$ResultsSummaryLine="Done Checking. $CheckTimeSeconds seconds, $NodesChecked nodes checked, $NodesCheckedOK OK, $NodesCheckedBad NOT.";
	dpv(1, " $ResultsSummaryLine\n");	
	dse_log("$ResultsSummaryLine");	
	dse_dlb_status_file_generate($DLB_array);
	return $DLB_array;		
}	
	

function dse_dlb_status_file_generate($DLB_array){
	global $vars,$CFG_array;
	global $vars;
	$TimeStr=dse_date_format();
	$PID=getmypid();
	
	$NowTime=time()+microtime();
	$RunningSeconds=intval($NowTime-$vars['StartTime']);
	$RunningTimeStr=seconds_to_text($RunningSeconds);
	
	if($RunningSeconds){
		$QPS=number_format($CFG_array['QueriesMade']/$RunningSeconds,2);
	}else{
		$QPS=0;
	}
	
	//if($vars['Verbosity']>=$MinVerbosity){
	dpv(2, " Generating DLB Status File: ".$CFG_array['StatusFile']."...\n-----------------file start---------------\n");
	$tbr="";
	$tbr.="# dlb status file. for info: ".$vars['DSE']['SCRIPT_FILENAME']." --help\n";
	$tbr.="# dlb status last updated: $TimeStr    PID=$PID   Running for $RunningTimeStr\n";
//	$tbr.="# dlb stats: Queries=$CFG_array[QueriesMade] OK=$CFG_array[QueriesSucceeded] FAIL=$CFG_array[QueriesFailed] QPS=$QPS\n";
	
	
	$NodesTotal=0; $NodesUP=0; $NodesDown=0;
	foreach(split(" ",$CFG_array['Services']) as $Service){
		if($Service){
			foreach($DLB_array[$Service]['Nodes'] as $k=>$Node){
				list($NodeIP,$NodePort)=split(":",$Node[0]); $NodesTotal++;
				$NodeName=$Node[1];
				if($DLB_array[$Service]['NodeStatus'][$k]=="UP"){
					$tbr.= "$Service $NodeIP:$NodePort #$NodeName\n"; $NodesUP++;
				}else{
					$tbr.= "#DOWN!#:$Service $NodeIP:$NodePort #$NodeName\n"; $NodesDown++;
				}
			}
		}
	}
	dpv(2, "$tbr");
	dpv(2, "-----------------file end---------------\n"); 
	file_put_contents($CFG_array['StatusFile'],$tbr);
	dse_log("status file ".$CFG_array['StatusFile']." updated:");	
	// Queries=$CFG_array[QueriesMade] OK=$CFG_array[QueriesSucceeded] FAIL=".$CFG_array['QueriesFailed']." QPS=$QPS 
	dse_log("running $RunningTimeStr "
	 ." Nodes=$NodesTotal UP=$NodesUP Down=$NodesDown");	
	return $tbr;		
}	
	 

function dse_dlb_get_up_node($NodesPool){
	global $vars,$CFG_array;
	//dse_log("dse_dlb_get_up_node(NodesPool=$NodesPool");
	$raw=dse_file_get_contents($CFG_array['StatusFile']);
	$AvailableNodes=array();
	foreach(split("\n",$raw) as $Line){
		$Line=trim(strcut($Line,"","#"));
		if($Line){
			$Lpa=split(" ",$Line);
			list($ThisNodesPool,$NodeIP,$NodePort,$NodeName)=$Lpa;
			if($NodesPool==$ThisNodesPool)	$AvailableNodes[]=$Lpa;
		}
	}
	$UpCount=sizeof($AvailableNodes);
	if($UpCount<=0){
		dse_log("query pool $NodesPool ALL-DOWN");	
		$CFG_array['QueriesFailed']++;
		return "ALL-DOWN";
	}
	$NodeToUseIndex=rand(0,$UpCount-1);
	list($ThisNodesPool,$NodeIPPort)=$AvailableNodes[$NodeToUseIndex];
	dse_log("query pool $NodesPool SUCCESS. $UpCount UP. Returning $NodeIPPort $NodeName");
	$CFG_array['QueriesSucceeded']++;
	return $NodeIPPort;		
}	
	 

?>
