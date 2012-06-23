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
$vars['DSE']['DSE_DSE_VERSION']="v0.01b";
$vars['DSE']['DSE_DSE_VERSION_DATE']="2012/06/23";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

$CFG_array=dse_read_config_file($vars['DSE']['DLB_CONFIG_FILE']);	
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
  array('d:','daemon:',"manages the checking daemon. options: [start|stop|status]"),
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
		print "Logging to screen ON\n".$vars['DSE']['LOG_TO_SCREEN'];
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
		if($vars['Verbosity']>=2) print "Verbosity set to ".$vars['Verbosity']."\n";
		break;
	case 's':
  	case 'status':
		print dse_file_get_contents($CFG_array['StatusFile']);
		exit(0);
	case 'h':
  	case 'help':
		print $vars['Usage'];
		exit(0);
	case 'e':
	case 'edit':
		print "Backing up ".$vars['DSE']['DLB_CONFIG_FILE']." and launcing in vim:\n";
		passthru("/dse/bin/vibk ".$vars['DSE']['DLB_CONFIG_FILE']." 2>&1");
		exit(0);
}

foreach (array_keys($vars['options']) as $opt) switch ($opt) {
  	case 'r':
	case 'request-from-pool':
		if(!$RunningPID){
			dse_log("query pool $NodesPool NO-DAEMON");	
			print "NO-DAEMON";
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
				if($RunningPID){
					$r=`kill $RunningPID 2>&1`;
					print "Killing process PID $RunningPID\n";
					dse_log("DLB stop. Killing process PID $RunningPID");
				}
				dse_dlb_daemon($CFG_array);
				break;	
			case 'start':
				if($RunningPID){
					print "DLB Already Running as PID $RunningPID!\n";
				}else{
					dse_dlb_daemon($CFG_array);
				}
				break;	
			case 'stop':
				if($RunningPID){
					$r=`kill $RunningPID 2>&1`;
					print "Killing process PID $RunningPID\n";
					dse_log("DLB stop. Killing process PID $RunningPID");
				}else{
					print "DLB Not Running!\n";
				}
				break;	
			case 'status':
				if($RunningPID){
					print "DLB Not Running!\n";
				}else{
					print "DLB Running as PID $RunningPID!\n";
				}
				print dse_file_get_contents($CFG_array['PIDFile'])."\n";
				break;	
		}
		break;
}



dse_cli_script_header();


if($DidSomething){
	if(!$Quiet && !$DoSetEnv){
		print getColoredString($vars['DSE']['SCRIPT_NAME']." Done. Exiting (0)","black","green");
		$vars['shell_colors_reset_foreground']='';	print getColoredString("\n","white","black");
	}
	if(!$NoExit) exit(0);
}else{
	if(!$Quiet && !$DoSetEnv){
		print getColoredString("Nothing to do! try --help for usage. ".$vars['DSE']['SCRIPT_NAME']." Done. Exiting (-1)","pink","black");
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
			foreach($CFG_array[$VarName] as $Node){
				if($Node){
					$NodeA=split(" ",$Node);
					print " adding $Service node: $Node\n";
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
			if(sizeof($DLB_array[$Service]['Nodes'])==0){
				print "Warning service_pool $Service Listed in ConfigFile.Services but no has Nodes!\n";
			}
		}
	}
	return $DLB_array;
}
	 
	 
function dse_dlb_daemon(){
	global $vars,$CFG_array;
	
	$PID=getmypid();
	file_put_contents($CFG_array['PIDFile'],$PID);
	print "file_put_contents($CFG_array[PidFile],$PID);\n";
	
	print "Running as DLB Daemon...\n";
	dse_log("DLB Daemon Starting. PID=$PID Log=".$CFG_array['LogFile']." Status=".$CFG_array['StatusFile']);
	$DLB_array=dse_dlb_config_parse($CFG_array);
	//print_r($DLB_array);
	
	$CheckSeconds=dse_time_span_sting_to_seconds($CFG_array['DefaultUpCheckFrequency']);
	print " Checking every $CheckSeconds seconds\n";
	
	$DoLoop=TRUE;
	while($DoLoop){
		$DLB_array=dse_dlb_services_check($DLB_array);
		sleep($CheckSeconds);
	}
}



function dse_dlb_services_check($DLB_array){
	global $vars,$CFG_array;
	print " Starting Services Checking...\n"; $CheckTimeSeconds=time();
	dse_log("DLB Starting Services Checking...");
	$NodesChecked=0; $NodesCheckedOK=0; $NodesCheckedBad=0;
	foreach(split(" ",$CFG_array['Services']) as $Service){
		if($Service){
			if(sizeof($DLB_array[$Service]['Nodes'])>0){
				
				print "  Checking $Service Nodes:\n";
				
				//$DLB_array[$Service][Name]
				//$DLB_array[$Service][NodesRaw]
				//$DLB_array[$Service][Nodes]
				$ServiceNodesChecked=0; $ServiceNodesCheckedOK=0; $ServiceNodesCheckedBad=0;
				$ServiceHasNodeUP=FALSE;
				$DLB_array[$Service]['NodeCountUP']=0; $DLB_array[$Service]['NodeCountDown']=0;
				foreach($DLB_array[$Service]['Nodes'] as $k=>$Node){
					
					list($NodeIP,$NodePort)=split(":",$Node[0]);
					$NodeName=$Node[1];
					print "   Checking Node: $NodeIP Port $NodePort -  $NodeName... ";
					$Listening=dse_ip_port_is_listening($NodeIP,$NodePort);
					if($Listening){
						print "Listening";
						$NodesCheckedOK++; $ServiceNodesCheckedOK++;
						$ServiceHasNodeUP=TRUE;
						$DLB_array[$Service]['NodeCountUP']++;
						$DLB_array[$Service]['NodeStatus'][$k]="UP";
					}else{
						print "ERROR - NOT Listening!";
						$NodesCheckedBad++; $ServiceNodesCheckedBad++;
						$DLB_array[$Service]['NodeCountDown']++;
						$DLB_array[$Service]['NodeStatus'][$k]="DOWN";
					}
					print "\n";
					$NodesChecked++; $ServiceNodesChecked++;
				}
				if(!$ServiceHasNodeUP){
					print "ALERT! $Service has no nodes UP !\n";
				}
			}
		}
	}
	$CheckTimeSeconds=time()-$CheckTimeSeconds;
	$ResultsSummaryLine="Done Checking. $CheckTimeSeconds seconds, $NodesChecked nodes checked, $NodesCheckedOK OK, $NodesCheckedBad not.";
	print " $ResultsSummaryLine\n";	
	dse_log("DLB $ResultsSummaryLine");	
	dse_dlb_status_file_generate($DLB_array);
	return $DLB_array;		
}	
	

function dse_dlb_status_file_generate($DLB_array){
	global $vars,$CFG_array;
	global $vars;
	$TimeStr=dse_date_format();
	$PID=getmypid();
	print " Generating DLB Status File: ".$CFG_array['StatusFile']."...\n-----------------file start---------------\n"; 
	$tbr="";
	$tbr.="# dlb status file. for info: ".$vars['DSE']['SCRIPT_FILENAME']." --help\n";
	$tbr.="# last updated: $TimeStr    PID=$PID\n";
	foreach(split(" ",$CFG_array['Services']) as $Service){
		if($Service){
			foreach($DLB_array[$Service]['Nodes'] as $k=>$Node){
				list($NodeIP,$NodePort)=split(":",$Node[0]);
				$NodeName=$Node[1];
				if($DLB_array[$Service]['NodeStatus'][$k]=="UP"){
					$tbr.= "$Service $NodeIP:$NodePort #$NodeName\n";
				}else{
					$tbr.= "#DOWN!#:$Service $NodeIP:$NodePort #$NodeName\n";
				}
			}
		}
	}
	print "$tbr";
	print "-----------------file end---------------\n"; 
	file_put_contents($CFG_array['StatusFile'],$tbr);
	dse_log("status file ".$CFG_array['StatusFile']." updated");	
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
			$Lpa=split(" ",$Line);;
			list($ThisNodesPool,$NodeIP,$NodePort,$NodeName)=$Lpa;
			if($NodesPool==$ThisNodesPool)	$AvailableNodes[]=$Lpa;
		}
	}
	$UpCount=sizeof($AvailableNodes);
	if($UpCount<=0){
		dse_log("query pool $NodesPool ALL-DOWN");	
		return "ALL-DOWN";
	}
	$NodeToUseIndex=rand(0,$UpCount-1);
	list($ThisNodesPool,$NodeIPPort)=$AvailableNodes[$NodeToUseIndex];
	dse_log("query pool $NodesPool SUCCESS. $UpCount UP. Returning $NodeIPPort $NodeName");
	return $NodeIPPort;		
}	
	 

?>
