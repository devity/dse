#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
$vars['Verbosity']=1;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="DSE System Stats";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="basic system monitoring and info";
$vars['DSE']['DSE_DSE_VERSION']="v0.01b";
$vars['DSE']['DSE_DSE_VERSION_DATE']="2012/06/29";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

global $CFG_array;
$CFG_array=array();
//$CFG_array['']=0;
//$CFG_array=dse_read_config_file($vars['DSE']['DLB_CONFIG_FILE'],$CFG_array);	

			

$parameters_details = array(
 
  array('h','help',"this message"),
  array('v:','verbosity:',"0=none 1=some 2=more 3=debug"),
  array('a','all',"print all available system stats"),
  array('p','prompt',"print brief info for use in shell prompt w: export PS1=\"[\$(/dse/bin/dss --prompt)]  \w:\$ \"
  "),
);
$vars['parameters']=dse_cli_get_paramaters_array($parameters_details);
$vars['Usage']=dse_cli_get_usage($parameters_details);
$vars['argv_origional']=$argv;
dse_cli_script_start();
		
$BackupBeforeUpdate=TRUE;
foreach (array_keys($vars['options']) as $opt) switch ($opt) {
	
  	case 'v':
	case 'verbosity':
		$vars['Verbosity']=$vars['options'][$opt];
		dpv(2,"Verbosity set to ".$vars['Verbosity']."\n");
		break;
  	
	case 'h':
  	case 'help':
		print $vars['Usage'];
		exit(0);
		
	case 'p':
  	case 'prompt':
		$Load=get_load();
		$Load=number_format($Load,1);
		print "L$Load";
		exit(0);
		
		
	case 'a':
  	case 'all':
		
		dse_exec("hddtemp /dev/sda",FALSE,TRUE);	
		dse_exec("hddtemp /dev/sdb",FALSE,TRUE);	
//		lshw -short -C disk
		
		exit(0);
	
}


$Command=$argv[1];
switch($Command){
	case 'get':
		$StatName=$argv[2];
		switch($StatName){
			case 'hddtemp':
				$Identifier=$argv[3];
				$r=dse_exec("hddtemp /dev/$Identifier");
				$TempA=split(": ",$r);
				$Temp=$TempA[2];
				$Temp=substr($Temp,0,strlen($Temp)-3);
				print $Temp;
				exit(0);
		}
		break;
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

	 

?>
