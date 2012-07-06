#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
include_once ("/dse/include/system_stat_functions.php");
$vars['Verbosity']=1;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="DSE Panic Script";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="basic emergency care - free disk, restart services, reboot";
$vars['DSE']['DSE_DSE_VERSION']="v0.01b";
$vars['DSE']['DSE_DSE_VERSION_DATE']="2012/06/24";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

$vars['ScriptHeaderShow']=TRUE;

global $CFG_array;
$CFG_array=array();
$CFG_array['OutFile']="panic_results.txt";
$CFG_array=dse_read_config_file($vars['DSE']['PANIC_CONFIG_FILE'],$CFG_array);	

print getColoredString("Saving copy of results to: ".$CFG_array['OutFile']."\n");
dse_file_put_contents($CFG_array['OutFile'],"DSE Panic Run Started at ".dse_date_format()."\n");


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
  array('z','internet-hide',"updates iptables to drop everything incoming except what you are prompted for and enter"),
 //close firewall to world! except...
 
 );
$vars['parameters']=dse_cli_get_paramaters_array($parameters_details);
$vars['Usage']=dse_cli_get_usage($parameters_details);
$vars['argv_origional']=$argv;

dse_cli_script_start();
print "\n\n\n";
dse_cli_script_header();
	
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
	case 'z':
  	case 'internet-hide':
		dse_firewall_internet_hide();
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




if($DidSomething) {
	print getColoredString("\-__-","green").getColoredString("panic Done with acting on arguments.","blue").getColoredString("______--------/\n","green");
	$A=dse_ask_choice(array(
		"A"=>"run panic now in fully-automatic/best-guess mode? ( same as 'panic' )",
		"I"=>"run panic now in interactive mode? ( same as 'panic --help-me' )",
		"Q"=>"Quit / Exit",
	));
	if($A=='A'){
		dse_panic();
		dse_panic_offer_interactive();
		$DidSomething=TRUE;
	}elseif($A=='I'){
		dse_panic(TRUE);
		$DidSomething=TRUE;
	}if($A=='Q'){
		exit(0);
	}
}else{
	dse_panic();
	dse_panic_offer_interactive();
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

function dse_panic_offer_interactive(){
	global $vars,$CFG_array;
	
	print "\n\n";
	print getColoredString(pad("  Automatic Run Done !  ",cbp_get_screen_width(),"*","center"),'bold_green');
	print getColoredString(pad("However, this skips things you need to be asked about.",cbp_get_screen_width()," ","center"),'cyan');
	
	$A=dse_ask_yn(
		colorize(pad("Do a more thourough, interactive run now?",cbp_get_screen_width()," ","center"),"white","red",TRUE,5)
		,'N',68*10);	
	if($A=='Y'){
		dse_panic(TRUE);
	}
}

function dse_panic($Interactive=FALSE){
	global $vars,$CFG_array;
	dse_panic_system_stats();
	
	//print "CFG_array="; print_r($CFG_array); print "\n";
	dse_panic_hd($Interactive);
	dse_panic_services($Interactive);
	dse_panic_processes($Interactive);
	return;
}

function dse_panic_system_stats(){
	global $vars,$CFG_array;
	
	print bar("System Status / Overview: ","-","blue","white","white","blue");
		
		
	print colorize("Disk: ");
	$DiskIsLow=dse_is_disk_low();
	if($DiskIsLow){
		print colorize(" Disk space LOW! ","white","red",TRUE,5);
	}else{
		print colorize(" OK! ","white","green",TRUE,1);
	}
	print "\n";
	
	print colorize("Ram: ");
	print "\n";
	
	print colorize("CPU: ");
	
	if(dse_which("mpstat")){
		$CPUInfoArray=dse_sysstats_cpu();
		$Idle=0; $IdlePossible=0;
		$CPUBars="";
		//print_r($CPUInfoArray);
		foreach($CPUInfoArray[1] as $i=>$CPUCoreInfoArray){
			$Idle+=$CPUCoreInfoArray['Idle'];
			$Free=intval($CPUCoreInfoArray['Idle']);
			$IdlePossible+=100;
			$User=intval($CPUCoreInfoArray['User']);
			$Sys=100-($Free+$User);
			$Used=100-$Free;
			$RedWidth=intval(($Sys/100)*$GraphWidth);
			$MagentaWidth=intval(($User/100)*$GraphWidth);
			$GreenWidth=$GraphWidth-($RedWidth+$MagentaWidth);
			$CPUBars.= colorize("CPU$i: ","cyan","black",TRUE,0);
			if($Used>60){
				$CPUBars.= colorize("$Used% ","red","black",TRUE,1);
			}elseif($Used>30){
				$CPUBars.= colorize("$Used% ","yellow","black",TRUE,1);
			}elseif($Used>10){
				$CPUBars.= colorize("$Used% ","green","black",TRUE,1);
			}else{
				$CPUBars.= colorize("$Used% ","cyan","black",TRUE,1);
			}
			$CPUBars.= pad("",3-strlen($Used));
			$CPUBars.= colorize(pad("SYS",$RedWidth,"#","left"),"red","black",TRUE,0);
			$CPUBars.= colorize(pad("USER",$MagentaWidth,"#","center"),"magenta","black",TRUE,0);
			$CPUBars.= colorize(pad("IDLE",$GreenWidth,"#","right"),"green","black",TRUE,0);
			//print "r=$RedWidth g=$GreenWidth ";
			if($i%2==1) {
				$CPUBars.= "\n";
			}else{
				$CPUBars.= "  ";
			}
		}
		if($IdlePossible>0){
			$IdleAverage=intval(($Idle/$IdlePossible)*100);
		}else{
			$IdleAverage=0;
		}
		if($IdleAverage<20){
			print colorize(" CPU use VERY HIGH Avg:$IdleAverage% Idle ","white","red",TRUE,5);
		}elseif($IdleAverage<40){
			print colorize(" CPU use HIGH Avg:$IdleAverage% Idle ","white","red",TRUE,1);
		}else{
			
			print colorize(" OK! Avg:$IdleAverage% Idle ","white","green",TRUE,1);
		}
		print "\n$CPUBars";
	}
		$Load=get_load();
		if($Load>10){
			print colorize(" Load VERY HIGH ( $Load ) ","white","red",TRUE,5);
		}elseif($Load>5){
			print colorize(" Load HIGH ( $Load ) ","white","red",TRUE,1);
		}else{
			print colorize(" Load OK! ($Load ) ","white","green",TRUE,1);
		} 
	
	print "\n";
	
	
	
	print colorize("Processes: ");
	$r=trim(dse_exec("ps aux | wc -l"));
	if($r>250){
		print colorize(" Very High - $r ","white","red",TRUE,5);
	}elseif($r>200){
		print colorize(" High - $r ","white","red",TRUE,1);
	}else{
		print colorize(" Low - $r ","white","green",TRUE,1);
	}
	print "\n";
	
	
	print colorize("Net: ");
	$r=dse_exec("traceroute yahoo.com 2>&1",TRUE);
	if(str_contains($r,"unknown host")){
		print colorize(" DNS Down ","white","red",TRUE,5);
	}elseif(str_contains($r,"!H")){
		print colorize(" yahoo.com Unreachable ","white","red",TRUE,5);
	}else{
		
		
		$Gateway=dse_get_gateway();
		if(!$Gateway){
			print colorize(" No Gateway Found ","white","red",TRUE,5);
		}else{
			/*$r=dse_exec("traceroute $Gateway 2>&1",TRUE);
			if(str_contains($r,"unknown host")){
				print colorize(" Gateway ($Gateway) Unreachable ","white","red",TRUE,5);
			}elseif(str_contains($r,"!H")){
				print colorize(" Gateway ($Gateway) Unreachable ","white","red",TRUE,5);
			}else{
				print colorize(" Appears OK! ","white","green",TRUE,1);
			}
			 */
			$r=dse_exec("ping -c1 $Gateway 2>&1",TRUE);
			if(str_contains($r,", 0.0% packet loss")){
				print colorize(" Appears OK! ","white","green",TRUE,1);
			}elseif(str_contains($r," 100.0% packet loss")){
				print colorize(" Gateway ($Gateway) No PING reply ","white","red",TRUE,5);
			}else{
				print colorize(" Appears OK! ","white","green",TRUE,1);
			}
		}
	}
	print "\n";
	
	
	
	print colorize("Services: ");
	print "\n";
	
	
	print colorize("Ports: ");
	print dse_ports_open(TRUE);
	print "\n";
	
	
	
	print colorize("Hacked: ");
	print "\n";
	
	print colorize("Attacks: ");
	print "\n";
	
	
	
	$A=dse_ask_yn(colorize("Continue?","white","red",TRUE,5),'Y',20);	
	if($A=='N'){
		print "\nExiting.\n";
		exit();
	}
	
	return;
}



function dse_panic_hd($Interactive=FALSE){
	global $vars,$CFG_array;
	$H=cbp_get_screen_height();
	$W=cbp_get_screen_width();
	print getColoredString(pad(" Section:  Hard Drive / Disk Space ",cbp_get_screen_width(),"-","center"),"green");

	print getColoredString("Starting Disk Stats:\n","cyan");
	dse_print_df();
	print "\n";
	
	//delete $CFG_array[RemoveFiles[]]
	if(is_array($CFG_array['RemovableFiles'])){
		foreach($CFG_array['RemovableFiles'] as $k=>$File){
			print "Deleting $File... ";
			$Command="rm -rfv $File 2>&1";
			print getColoredString($Command,"orange");
			$r=trim(`$Command`);
			if($r) print getColoredString("$r","grey");
			print "\n";
		}
	}
	//delete logs
	//delete cookes/sessions
	//delete tmp
	//delete caches
	//clearn apt/yum
	//delete unneeded packages
	//gzip as much as possible in /backup and /var/log
	
	//a.out *.out
	//nohup.out
	//core dumps
	
	//look for large uncompressed info
	//find redundant files
	print getColoredString("Final Disk Stats:\n","cyan");
	dse_print_df();
	print "\n";
	
	
	$FNW=$W-20;
	if($FNW>120) $FNW=120;
	
	//look for large files recently
	
	$DiskIsLow=dse_is_disk_low();
	if($DiskIsLow){
		print colorize("Disk space is LOW on at least one drive!\n","white","red",TRUE,5);
	}else{
		print colorize("Disk space is OK on all drives!\n","white","green",TRUE,1);
	}
	
	if($Interactive){
		
		$A=dse_ask_yn(colorize("Search for large files?","white","red",TRUE,5),'N',60);	
		if($A=='Y'){
			
			
			print bar("Searching for LARGE files... ","-","blue","white","white","blue");
		
			//print "aaaa";
			`rm /tmp/ls.out`;
			$c="find / -type f -size +25000k -exec echo {} >>/tmp/ls.out \; 2>/dev/null &";
			print `$c`;
			//print "bbbb";
			$FindPID=`/dse/bin/grep2pid "find"`;
			print "PID=$FindPID\n";
			$t=time();		
			progress_bar("reset");
			$lss_last=0;
			$CachedFileSizes=array();
			while(dse_pid_is_running($FindPID)>0){
				//print "t$asf"; $asf++;
				sleep(1);
				 
				$BigFound=0;
				if(time()%4==1){
					cbp_screen_clear();
					sbp_cursor_postion(0,0);
					$ls=dse_file_get_contents("/tmp/ls.out");
					$lsa=split("\n",$ls);
					$lss=sizeof($lsa);
					if($lss>0 ){
						$lss--;
						for($i=0;$i<$lss  ;$i++){
							if($CachedFileSizes[$lsa[$i]]){
								$SizeStr=$CachedFileSizes[$lsa[$i]];
							}else{
								$SizeStr=dse_exec("/dse/bin/dsizeof ".$lsa[$i]);
								$CachedFileSizes[$lsa[$i]]=$SizeStr;
							}
							$SizeStr=intval($SizeStr/1000000);
							if($SizeStr>0){
								//print "lss=$lss  ";
								if($i<$H-3){
									print colorize(pad($lsa[$i],$FNW),"yellow","black")."   ";
									print colorize(pad($SizeStr,8," ","right"),"red","black",TRUE,1);
									print colorize(" MB\n","green","black",TRUE,1);
								}
								$BigFound++;
							}
							//$lss_last=$i;
						}
					}
					
					/*cbp_cursor_save();
					sbp_cursor_postion(3,cbp_get_screen_width()-80);
					print colorize(pad(" + Found $BigFound large files",80,"-"),"black","green");
					cbp_cursor_restore();*/
				}
				
				//print pad(" current: PID=$FindPID ",cbp_get_screen_width(),"-","center");
				//print "\n$ls";
				//$lsa_last=$lsa; $lss_last=$lss;
				
				progress_bar("time",80," Found $lss files ");
			}
			$A=dse_ask_yn(colorize("Offer to DELETE found large files?","white","red",TRUE,5),'N',60);	
			if($A=='Y'){
				for($i=0;$i<$lss  ;$i++){
					if($CachedFileSizes[$lsa[$i]]){
						$SizeStr=$CachedFileSizes[$lsa[$i]];
					}else{
						$SizeStr=dse_exec("/dse/bin/dsizeof ".$lsa[$i]);
						$CachedFileSizes[$lsa[$i]]=$SizeStr;
					}
					$SizeStr=intval($SizeStr/1000000);
					if($lsa[$i] && $SizeStr>0){
						$ProcessingFile++;
						print pad("$ProcessingFile of $BigFiles",15);
						
						print colorize(pad($lsa[$i],$FNW),"yellow","black")."   ";
						print colorize(pad($SizeStr,8," ","right"),"red","black",TRUE,1);
						print colorize(" MB   ","green","black",TRUE,1);
						$A=dse_ask_yn(colorize("DELETE?","white","red",TRUE,5),'N',60);	
						if($A=='Y'){
							dse_file_delete($lsa[$i]);
						}
						print "\n";
					
					}
					//$lss_last=$i;
				}
			}
			
			
			$LargeFileRootDir="/";
			//look for largest fiels
			$LargeFileCommands=array(
				//"find ROOT_DIR -type f -size +1000000k -exec ls -l {} \; 2>/dev/null ",
				"du -a ROOT_DIR 2>/dev/null | sort -n -r", //du -am / 2>/dev/null | sort -n -r | head -n 200
				//"for i in G M K; do du -a / 2>/dev/null | grep [0-9]$i | sort -nr -k 1; done | head -n 11",
				//"find ROOT_DIR -type f -print0| xargs -0 ls -s | sort -rn | awk ‘{size=$1/1024; printf(\"%dMb %s\n\", size,$2);}’ | head -200",
				//"sudo find / -type f -print0 2>/dev/null | xargs -0 ls -s | sort -rn | awk '{size=$1/1024; printf(\"%dMb %s\n\", size,$2);}' | head",
				
			);
			
			$Command="/dse/bin/dfm --empty ".$CFG_array['OutFile'];
			dse_exec($Command,TRUE,TRUE);
			foreach($LargeFileCommands as $Command){
				$CommandReal=str_replace("ROOT_DIR",$LargeFileRootDir,$Command);
				$A=dse_ask_choice(array(
					"Y"=>"Yes",
					"N"=>"No",
					"C"=>"Change Root Directory for start of large file search",
					"Q"=>"Quit / Exit",
				),"Run $CommandReal ?");
				if($A=='Y'){
					$StartTime=time()+microtime();
					print getColoredString("Running File Large Files Command:  $CommandReal \n","cyan");
					$r=dse_exec("$CommandReal");  $EndTime=time()+microtime(); $RunTime=number_format($EndTime-$StartTime,2);
					print getColoredString("File Large Files:  $CommandReal (runtime $RunTime s)\n","cyan");
					$r_show=substr($r,0,2000);
					print getColoredString($r_show,"yellow");
					dse_file_append_contents($CFG_array['OutFile'],"File Large Files:  $CommandReal (runtime $RunTime s)\n");
					dse_file_append_contents($CFG_array['OutFile'],$r);
				}elseif($A=='Q'){
					exit(0);
				}elseif($A=='C'){
					$LargeFileRootDir=dse_ask_entry("Enter New Root Directory:");
					if($LargeFileRootDir){
						
						$CommandReal=str_replace("ROOT_DIR",$LargeFileRootDir,$Command);
						print getColoredString("Running File Large Files Command:  $CommandReal \n","cyan");
						$StartTime=time()+microtime();
						$r=dse_exec("$CommandReal"); $EndTime=time()+microtime(); $RunTime=number_format($EndTime-$StartTime,2);
						print getColoredString("Command Done:  $CommandReal (runtime: $RunTime s)\n","cyan");
						$r_show=substr($r,0,2000);
						print getColoredString($r_show,"yellow");
						dse_file_append_contents($CFG_array['OutFile'],"File Large Files:  $CommandReal (runtime $RunTime s)\n");
						dse_file_append_contents($CFG_array['OutFile'],$r);
					}else{
						$LargeFileRootDir="/";
					}
				}
			}
		}
	}
	
	
}


function dse_panic_services($Interactive=FALSE){
	global $vars,$CFG_array;
	print bar("Section:  Services / Daemons... ","-","blue","white","white","blue");
		
	print bar("Verifying Expected Ports are Listening.... ","-","blue","white","white","blue");
		//print `df -h`;

	if($Interactive){
		
	}
	
}


function dse_panic_processes($Interactive=FALSE){
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
