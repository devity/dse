#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
$vars['Verbosity']=1;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="DSE Panic Script";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="basic emergency care - free disk, restart services, reboot";
$vars['DSE']['DSE_DSE_VERSION']="v0.01b";
$vars['DSE']['DSE_DSE_VERSION_DATE']="2012/06/24";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******


$parameters_details = array(
  array('l','log-to-screen',"log to screen too"),
 // array('','log-show:',"shows tail of log ".$CFG_array['LogFile']."  argv1 lines"),
  array('h','help',"this message"),
  array('q','quiet',"same as --verbosity 0"),
  array('v:','verbosity:',"0=none 1=some 2=more 3=debug"),
  //array('s','status',"prints status file".$CFG_array['StatusFile']),
  array('e','edit',"backs up and launches a vim of ".$vars['DSE']['PANIC_CONFIG_FILE']),
  array('c','config-show',"prints contents of ".$vars['DSE']['PANIC_CONFIG_FILE']),
 // array('d:','daemon:',"manages the checking daemon. options: [start|stop|restart|status]"),
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
		//exit(0);
		break;
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
		//exit(0);
		break;
	case 'h':
  	case 'help':
		print $vars['Usage'];
		//exit(0);
		break;
	case 'e':
	case 'edit':
		$Message="Backing up ".$vars['DSE']['PANIC_CONFIG_FILE']." and launcing in vim:\n";
		dpv(1,$Message);
		dse_log($Message);
		passthru("/dse/bin/vibk ".$vars['DSE']['PANIC_CONFIG_FILE']." 2>&1");
		//exit(0);
		break;
	case 'c':
  	case 'config-show':
		print dse_file_get_contents($vars['DSE']['PANIC_CONFIG_FILE']);
		//exit(0);
		break;
}

foreach (array_keys($vars['options']) as $opt) switch ($opt) {
  	
  	case 'd':
	case 'daemon':
		$CFG_array=dse_read_config_file($vars['DSE']['PANIC_CONFIG_FILE']);
		
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

if(!$DidSomething){
	print getColoredString("\-__-","green").getColoredString("panic Done with acting on arguments.","blue").getColoredString("______--------/\n","green");
	$A=dse_ask_choice(array(
		"A"=>"run panic now in fully-automatic/best-guess mode? ( same as 'panic' )",
		"I"=>"run panic now in interactive mode? ( same as 'panic --help-me' )",
		"Q"=>"Quit / Exit",
	));
	if($A=='A'){
		dse_panic();
		$DidSomething=TRUE;
	}elseif($A=='I'){
		dse_panic(TRUE);
		$DidSomething=TRUE;
	}if($A=='Q'){
		exit(0);
	}
}else{
	dse_panic();
	$DidSomething=TRUE;
}



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

function dse_panic($Interactive=FALSE){
	global $vars,$CFG_array;
	
	
	return;
}


function dse_panic_hd($Interactive=FALSE){
	global $vars,$CFG_array;
	//delete logs
	//delete cookes/sessions
	//delete tmp
	//delete caches
	//clearn apt/yum
	//delete unneeded packages
	//gzip as much as possible in /backup and /var/log
	//look for large files recently
	//loge for largest fiels
	//look for large uncompressed info
	//find redundant files
	return;
}


function dse_panic_services($Interactive=FALSE){
	global $vars,$CFG_array;
	
	
	return;
}


function dse_panic_config_parse(){
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
	 
	 
function dse_panic_daemon(){
	global $vars,$CFG_array;
	
	$PID=getmypid();
	file_put_contents($CFG_array['PIDFile'],$PID);
	dpv(4, "file_put_contents($CFG_array[PidFile],$PID);\n");
	
	dpv(2, "Running as panic Daemon...\n");
	dse_log("panic Daemon Starting. PID=$PID Log=".$CFG_array['LogFile']." Status=".$CFG_array['StatusFile']);
	$DLB_array=dse_panic_config_parse($CFG_array);
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



function dse_panic_status_file_generate($DLB_array){
	global $vars,$CFG_array;
	global $vars;
	/*$TimeStr=dse_date_format();
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
	return $tbr;		*/
}	
	 
	 

?>
