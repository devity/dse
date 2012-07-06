#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
include_once ("/dse/bin/dse_config_functions.php");
$vars['Verbosity']=0;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="DSE Service Control";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="starts/stops/resets/status of services  and control if rc.d run levels";
$vars['DSE']['DSE_DSE_VERSION']="v0.01b";
$vars['DSE']['DSE_DSE_VERSION_DATE']="2012/06/26";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

$vars['ScriptHeaderShow']=TRUE;

$parameters_details = array(
  //array('l','log-to-screen',"log to screen too"),
 // array('','log-show:',"shows tail of log ".$CFG_array['LogFile']."  argv1 lines"),
  array('h','help',"this message"),
  array('q','quiet',"same as --verbosity 0"),
  array('v:','verbosity:',"0=none 1=some 2=more 5=debug"),
  array('o','open-ports',"lists open ports"),
  array('c','color',"colorize"),
  array('r','run-levels',"show current run level settings"),
  array('s','services-set',"services-set"),
  array('l',' run-levels-set'," run-levels-set"),
 
   //array('s','status',"prints status file".$CFG_array['StatusFile']),
  //array('e','edit',"backs up and launches a vim of ".$vars['DSE']['PANIC_CONFIG_FILE']),
  //array('c','config-show',"prints contents of ".$vars['DSE']['PANIC_CONFIG_FILE']),
 );
$vars['parameters']=dse_cli_get_paramaters_array($parameters_details);
$vars['Usage']=dse_cli_get_usage($parameters_details);
$vars['Usage'].= "Test sample image with:    /dse/bin/img2txt -v5 -s fast /dse/images/penguin.jpg\n\n";
$vars['argv_origional']=$argv;

dse_cli_script_start();
	
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
		$vars['ScriptHeaderShow']=FALSE;
		$vars['Verbosity']=0;
		break;
  	case 'v':
	case 'verbosity':
		$vars['Verbosity']=$vars['options'][$opt];
		dpv(2,"Verbosity set to ".$vars['Verbosity']."\n");
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
	//case 'c':
  //	case 'config-show':
	//	print dse_file_get_contents($vars['DSE']['PANIC_CONFIG_FILE']);
		//exit(0);
		//break;
	case 'c':
  	case 'colorize':
		$Colorize=TRUE;
		break;
}

foreach (array_keys($vars['options']) as $opt) switch ($opt) {
	case 'o':
  	case 'open-ports':
		print dse_ports_open($Colorize);
		exit(0);
	case 's':
  	case 'services-set':
		passthru("sudo rcconf");
		exit(0);
	case 'l':
  	case 'run-levels-set':
		//bootup-manager
		passthru("sudo sysv-rc-conf");
		exit(0);
	case 'r':
  	case 'run-levels':
		print "Services: ".colorize($vars['DSE']['SERVICES'],"cyan")."\n";
		foreach(split(" ",$vars['DSE']['SERVICES']) as $service){
			$service_common=dse_service_name_from_common_name($service);
	
			$PortInfo="";
			foreach($service=$vars['DSE']['SERVICE_PORTS'] as $pt=>$st){
				if($service_common==dse_service_name_from_common_name($st)){
					$PortInfo.= colorize(" port ","cyan");
					$PortInfo.= colorize("$pt ","green");
					
					$IsOpen=dse_ip_port_is_open($pt);
					if($IsOpen){
						$PortInfo.= colorize(" open ","green");
					}else{
						$PortInfo.= colorize(" closed ","red");
					}
					$IsListening=dse_ip_port_is_listening("localhost",$pt);
					if($IsListening){
						$PortInfo.= colorize(" listening ","green");
					}else{
						$PortInfo.= colorize(" not-answering ","red");
					}
					

				}
			}
	
			print " $service => $service_common  $PortInfo  \n ";//.colorize($vars['DSE']['SERVICES'],"cyan")."\n";
			print_r(dse_initd_entry_get_info($service_common));
	
		}
		 //dse_initd_entry_add($Script,$ServiceName,$Rank=99);

		 
		if(dse_is_osx()){
			
		}else{
			$r=`chkconfig --list`;
			foreach(split("\n",$r) as $L){
				$service=strcut($L,""," ");
				$states=strcut($L," ");
				$states=str_replace("off",colorize("off","red"),$states);
				$states=str_replace("on",colorize("on","green"),$states);
				print colorize($service,"cyan").colorize(": ","yellow").$states;
				
				foreach($service=$vars['DSE']['SERVICE_PORTS'] as $pt=>$st){
					if(dse_service_name_from_common_name($service)==dse_service_name_from_common_name($st)){
						print colorize(" port ","cyan");
						print colorize("$pt ","green");
					}
				}
				
				print "\n";
			}
		}
		print "\n";
		exit(0);
		
}

$service_controler=dse_which("service");
		
switch($argv[2]){
	case 'start':
	case 'stop':
	case 'restart':
	case 'status':
		$service=dse_service_name_from_common_name($argv[1]);
		$c="$service_controler $service ".$argv[2];
		print "command: $c\n";
		$rr=passthru($c,$r);
		break;
}	

	
if($vars[Verbosity]>1){
	print "\n\n\n";
	dse_cli_script_header();
}

exit(0);





