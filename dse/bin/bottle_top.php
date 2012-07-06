#!/usr/bin/php
<?
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/include/system_stat_functions.php");
include_once ("/dse/bin/dse_config.php");

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="Bottle Top";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="top-like system bottleneck analyzer";
$vars['DSE']['BTOP_VERSION']="v0.04b";
$vars['DSE']['BTOP_VERSION_DATE']="2012/04/30";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

$vars['DSE']['SCRIPT_SETTINGS']['Verbosity']=0;
$vars['Verbosity']=$vars['DSE']['SCRIPT_SETTINGS']['Verbosity'];
$vars['DSE']['SCRIPT_SETTINGS']['ForceHighLoadRun']=FALSE;
$vars['DSE']['SCRIPT_SETTINGS']['MaxLoadBeforeExit']=5;
$vars['DSE']['SCRIPT_SETTINGS']['EasyOnly']=FALSE;
$vars['DSE']['SCRIPT_SETTINGS']['ReloadSeconds']=20;
$vars['DSE']['SCRIPT_SETTINGS']['MaxLoops']=25;

$parameters_details = array(
  array('h','help',"this message"),
  array('q','quiet',"same as -v 0"),
  array('f','force',"force program to run even if load is high"),
  array('e','easy-only',"only get/show least intensive stats"),
  array('','version',"version info"),
  array('s:','reload-seconds:',"seconds between screen refresh"),
  array('v:','verbosity:',"0=none 1=some 2=more 3=debug"),
  array('m:','maxloops:',"# refreshes before auto exit"),
  array('z:','maxload:',"system load level to trigger auto exit"),
);
$vars['parameters']=dse_cli_get_paramaters_array($parameters_details);
$vars['Usage']=dse_cli_get_usage($parameters_details);
$vars['argv_origional']=$argv;
dse_cli_script_start();

foreach (array_keys($vars['options']) as $opt) switch ($opt) {
	case 'q':
	case 'quiet':
		$Quiet=TRUE;
		$vars['DSE']['SCRIPT_SETTINGS']['Verbosity']=0;
		break;
	case 'v':
	case 'verbosity':
		$vars['DSE']['SCRIPT_SETTINGS']['Verbosity']=$vars['options'][$opt];
		$vars['Verbosity']=$vars['DSE']['SCRIPT_SETTINGS']['Verbosity'];
		if($vars['DSE']['SCRIPT_SETTINGS']['Verbosity']>=2) print "Verbosity set to ".$vars['DSE']['SCRIPT_SETTINGS']['Verbosity']."\n";
		break;
	case 'h':
  	case 'help':
  		$ShowUsage=TRUE;
		$DidSomething=TRUE;
		break;
  	case 'version':
  		$ShowVersion=TRUE;
		$DidSomething=TRUE;
		break;
	case 'f':
	case 'force':
		$vars['DSE']['SCRIPT_SETTINGS']['ForceHighLoadRun']=TRUE;
		break;
	case 'e':
	case 'easy-only':
		$vars['DSE']['SCRIPT_SETTINGS']['EasyOnly']=TRUE;
		if($vars['DSE']['SCRIPT_SETTINGS']['Verbosity']>=2) print "EasyOnly set to TRUE\n";
		break;

	case 's':
	case 'reload-seconds':
		$vars['DSE']['SCRIPT_SETTINGS']['ReloadSeconds']=$vars['options'][$opt];
		if($vars['DSE']['SCRIPT_SETTINGS']['Verbosity']>=2) print "reload-seconds set to ".$vars['DSE']['SCRIPT_SETTINGS']['ReloadSeconds']."\n";
		break;
	case 'm':
	case 'maxloops':
		$vars['DSE']['SCRIPT_SETTINGS']['MaxLoops']=$vars['options'][$opt];
		if($vars['DSE']['SCRIPT_SETTINGS']['Verbosity']>=2) print "maxloops set to ".$vars['DSE']['SCRIPT_SETTINGS']['MaxLoops']."\n";
		break;
	case 'z':
	case 'maxload':
		$vars['DSE']['SCRIPT_SETTINGS']['MaxLoadBeforeExit']=$vars['options'][$opt];
		if($vars['DSE']['SCRIPT_SETTINGS']['Verbosity']>=2) print "MaxLoadBeforeExit set to ".$vars['DSE']['SCRIPT_SETTINGS']['MaxLoadBeforeExit']."\n";
		break;



}


dse_cli_script_header();


if($ShowUsage){
	print $vars['Usage'];
}
if($ShowVersion){
	print "DSE Version: " . $vars['DSE']['DSE_VERSION'] . "  Release Date: " . $vars['DSE']['DSE_VERSION_DATE'] ."\n";
	print $vars['DSE']['SCRIPT_NAME']." Version: " . $vars['DSE']['BTOP_VERSION'] . "  Release Date: " . $vars['DSE']['BTOP_VERSION_DATE'] ."\n";
}

if($DidSomething){
	$vars[shell_colors_reset_foreground]='';	print getColoredString("","white","black");
	exit(0);
}

$EndLoad=get_load();
  

$ActualRunTime=time()-$Start;
	
//$log_line="runstart=$Start:runlength=$RunTime:actualruntime=$ActualRunTime:loadstart=$StartLoad:loadend=$EndLoad:loads=$Loads:lps=$LoadsPerSecond:sizeavg=$AvgSizeRaw:sizetotal=$TotalSize:Mbps=$Mbps";
//print `echo $log_line >> $Log`;
//print `echo $log_line`;
global $diskstats_lasttime,$section_httpd;
	



//$fp = fopen("php://stdin","r");     //open direct input stream for reading 
//stream_set_blocking($fp,0);        //set non-blocking mode 


	
if($vars['DSE']['SCRIPT_SETTINGS']['ReloadSeconds']<1) $vars['DSE']['SCRIPT_SETTINGS']['ReloadSeconds']=1;
$DoLoop=TRUE;
$Loops=0;
while($DoLoop && ($vars['DSE']['SCRIPT_SETTINGS']['MaxLoops']==0 || $Loops<$vars['DSE']['SCRIPT_SETTINGS']['MaxLoops'])){
	if($Loops>0) {
		$SleepLeft=$vars['DSE']['SCRIPT_SETTINGS']['ReloadSeconds'];
		while($SleepLeft>0){
		
		
			sbp_cursor_postion(0,0);
		//	cbp_screen_clear();
			print getColoredString("*", 'yellow', 'black')." ".getColoredString(trim(`hostname`)."            ".trim(`date`),'cyan','black');	
			$SleepLeft_str=getColoredString($SleepLeft,"yellow","black");
			
			$Load_str=getColoredString("$Load", 'yellow', 'black');
			$Loops_str=getColoredString($Loops+1, 'yellow', 'black');
			
			if(!dse_is_osx()){
				$this_loadavg=`cat /proc/loadavg`;
				if($this_loadavg!=""){
					$this_loadavg=str_replace("  ", " ", $this_loadavg); 
					$this_loadavg=str_replace("  ", " ", $this_loadavg); 
					$this_loadavg=str_replace("  ", " ", $this_loadavg); 
					$loadaggA=split(" ",$this_loadavg);
					$load_1=number_format($loadaggA[0],2);
					$load_2=number_format($loadaggA[1],2);
					$load_3=number_format($loadaggA[2],2);
					$threads=$loadaggA[3];
					$threadsa=split("/",$threads);
					$threads_running=$threadsa[0];
					$threads_total=$threadsa[1];
					$threads_running=dse_bt_colorize($threads_running,2.001);
					$threads_str="$threads_running/$threads_total";
					$last_pid=$loadaggA[4];
					$load_1_str=dse_bt_colorize($load_1,2.001);
		 			$Load_str="Load: $load_1_str:$load_2:$load_3    Threads:$threads_str";
				}
			}else{
				$load_1=get_load();
				$load_1_str=dse_bt_colorize($load_1,2.001);
		 		$Load_str="Load: $load_1_str";
			}
			
			$str= "   Loop: $Loops_str / ".$vars['DSE']['SCRIPT_SETTINGS']['MaxLoops']."  Next: ${SleepLeft_str}s      $Load_str   \n";
			print $str;
		
			$GraphWidth=50;
			$CPUInfoArray=dse_sysstats_cpu();
			foreach($CPUInfoArray[1] as $i=>$CPUCoreInfoArray){
				$Free=intval($CPUCoreInfoArray['Idle']);
				$Used=100-$Free;
				$RedWidth=intval(($Used/100)*$GraphWidth);
				$GreenWidth=intval(($CPUCoreInfoArray['Idle']/100)*$GraphWidth);
				print colorize("$i: ","cyan","black");
				print colorize(pad("$Used%-",$RedWidth,"#","left"),"red","black",TRUE,1);
				print colorize(pad("-$Free%",$GreenWidth,"#","right"),"green","black",TRUE,1);
				if($i%2==1) print "\n";
			}
			exit();
		
		
			//sleep(1);
			$SleepLeft--;
			$keys=readline_timeout(1, '');
			if($keys){
			
				 
				 $keys_new="";
				// foreach($keys as $key){
				for($ki=0;$ki<strlen($keys);$ki++){
					$key=$keys[$ki];
				 //	print "key-press! [$key]\n";
								
			 		switch(strtolower($key)){
						case 's':
							$o=exec("http_stress > /dev/null 2>&1 &");
							sbp_cursor_postion(0,0);
							print getColoredString("http_stress started!\n", 'green', 'black');
							sleep(1);
							break;
						case 'u':
							$SleepLeft=0;
							print getColoredString("data gather started!\n", 'green', 'black');
							sleep(1);
							break;
						case '-':
							$vars['DSE']['SCRIPT_SETTINGS']['ReloadSeconds']=intval($vars['DSE']['SCRIPT_SETTINGS']['ReloadSeconds']*5/4);
							sbp_cursor_postion(0,0);
							print getColoredString("ReloadSeconds raised to ".$vars['DSE']['SCRIPT_SETTINGS']['ReloadSeconds']."\n", 'green', 'black');
							sleep(1);
							break;
						case '+':
							$vars['DSE']['SCRIPT_SETTINGS']['ReloadSeconds']=intval($vars['DSE']['SCRIPT_SETTINGS']['ReloadSeconds']*3/4);
							sbp_cursor_postion(0,0);
							print getColoredString("ReloadSeconds lowered to ".$vars['DSE']['SCRIPT_SETTINGS']['ReloadSeconds']."\n", 'green', 'black');
							sleep(1);
							break;	
						case 'q':
							cbp_screen_clear();
							sbp_cursor_postion(0,0);
							print getColoredString("'q' pressed ([Q]uit). Exiting ".$vars['DSE']['SCRIPT_FILENAME'].". \n\n", 'green', 'black');
							$vars[shell_colors_reset_foreground]='';	print getColoredString("","white","black");
							exit(0);
							break;	
						case 'h':	
						case '?':
							cbp_screen_clear();
							sbp_cursor_postion(0,0);
							print "\n\n";
							print getColoredString("Interactive Commands for ".$vars['DSE']['SCRIPT_NAME'].": \n", 'green', 'black');
							print "  [h,?] - this message\n";
							print "  q - quit/exit ".$vars['DSE']['SCRIPT_FILENAME']."\n";
							print "  s - run http_stress\n";
							print "  u - update/refresh now\n";
							print "  - - slow refresh 25%\n";
							print "  + - speed refresh 25%\n";
							sleep(2);
							break;	
						default:
							print getColoredString("Unhandled key-press! [$key]\n", 'white', 'dark_red');
							sleep(5);
							$keys_new.=$key;
							break;
				 	}
				}
				$keys=$keys_new;
			}
		}
	}
	
	
	if($Load>0)sbp_cursor_postion(0,0);
	$str=getColoredString(">> Gathering Data for Refresh <<", 'green', 'black');
	print "$str\n";

	$Load=get_load();
	if((!$vars['DSE']['SCRIPT_SETTINGS']['ForceHighLoadRun']) && $Load>$vars['DSE']['SCRIPT_SETTINGS']['MaxLoadBeforeExit']){
		print getColoredString("high load ($Load). Exiting ".$vars['DSE']['SCRIPT_FILENAME'].". \n\n", 'white', 'red');
		$vars[shell_colors_reset_foreground]='';	print getColoredString("","white","black");
		exit(-2);
	}
	
	
	
	//
	

	
	update_display($keys);
	$Loops++;
}
print getColoredString("Done. Exiting ".$vars['DSE']['SCRIPT_FILENAME'].". \n\n", 'black', 'green');
exit(0);




function update_display($keys=""){
	//global $c,$t,$tt,$st,$Key,$FoundKeys,$file_scan_last,$file_keys_found,$i1,$i2,$i3,$i4,$ScanContinue,$NoDiplayYet;
	global $vars,$Loops;
	global $diskstats_lasttime,$section_httpd;
	
	if($keys){
		 print "keys=$keys\n"; 
		 sleep(10);
		 
		 foreach($keys as $key){
		 		switch(strtolower($key)){
					case 'q':
						break;	
		 		}
		 }
		 	
	}
	
		if($vars['Verbosity']>3) print "dse_sysstats_mysql_processlist()\n";
		$dse_sysstats_mysql_processlist_array=dse_sysstats_mysql_processlist();
		$section_mysql_processes= $dse_sysstats_mysql_processlist_array[3];
		
	
		if($vars['Verbosity']>3) print "dse_sysstats_mysql_status()\n";
		$dse_sysstats_mysql_status_array=dse_sysstats_mysql_status();
		$section_mysql_stats=$dse_sysstats_mysql_status_array[3];
		
	
		/*global $section_files_open;
		if( (!$vars['DSE']['SCRIPT_SETTINGS']['EasyOnly']) && ($Loops%5)==0 ){
			print "dse_sysstats_files_open()\n";
			$dse_sysstats_files_open_array=dse_sysstats_files_open();
			$section_files_open=$dse_sysstats_files_open_array[2];
		}*/
		
		/*
		global $section_procio;
		if( (!$vars['DSE']['SCRIPT_SETTINGS']['EasyOnly']) && ($Loops%5)==0 ){
			print "dse_sysstats_proc_io()\n";
			$dse_sysstats_proc_io_array=dse_sysstats_proc_io();
			$section_procio=$dse_sysstats_proc_io_array[1];
		}*/
			
		global $section_net_listening;
		if(($Loops%5)==0 ){
			if($vars['Verbosity']>3) print "dse_sysstats_net_listening()\n";
			//$dse_sysstats_net_listening_array=dse_sysstats_net_listening();
			//$section_net_listening="Ports Listening: ".$dse_sysstats_net_listening_array[3];
			$section_net_listening=colorize("Ports Listening: ","cyan","black").dse_exec("/dse/bin/dsc -oc");
		}	
			
			
		// *****************************************************************************************************************
		// *********************************************** MEMORY MEMORY MEMORY MEMORY *************************************
		// *****************************************************************************************************************
	
	/*
		if($vars['Verbosity']>3) print "section_memory()\n";
		$section_memory="";
		$section_cpu="";
		$unit_size=1024*1024;
		$o=`vmstat -a -S M 1 2`;
		$o=str_replace("  ", " ", $o);
		$o=str_replace("  ", " ", $o);
		$o=str_replace("  ", " ", $o);
		$oda=str_replace("  ", " ", $o);
		$odaa=split("\n",$oda);
		$oda=$odaa[3];
		$odaa=split(" ",$oda);
		
		
		$o=`vmstat -S M 1 2`;
		$o=str_replace("  ", " ", $o);
		$o=str_replace("  ", " ", $o);
		$o=str_replace("  ", " ", $o);
		$o=str_replace("  ", " ", $o);
		$oa=split("\n",$o);
		$o=$oa[3];
		$oa=split(" ",$o);
		
		$fo=`free -m`;
		$fo=str_replace("  ", " ", $fo);
		$fo=str_replace("  ", " ", $fo);
		$fo=str_replace("  ", " ", $fo);
		$fo=str_replace("  ", " ", $fo);
		$foa=split("\n",$fo);
		$fo=$foa[1];
		$fo1a=split(" ",$fo);
		$fo=$foa[2];
		$fo2a=split(" ",$fo);
		$fo=$foa[3];
		$fo3a=split(" ",$fo);
		
		$free_Mem_Total=$fo1a[1];
		$free_Mem_Used=$fo1a[2];
		$free_Mem_Free=$fo1a[3];
		$free_Mem_Shared=$fo1a[4];
		$free_Mem_Buffers=$fo1a[5];
		$free_Mem_Cached=$fo1a[6];
		$free_BC_Total=$fo2a[1];
		$free_BC_Used=$fo2a[2];
		$free_BC_Free=$fo2a[3];
		$free_Swap_Total=$fo3a[1];
		$free_Swap_Used=$fo3a[2];
		$free_Swap_Free=$fo3a[3];
		
		$Mem=array();
		$Mem[TotalPhysical]=$free_Mem_Total;
		$Mem[TotalAvailable]=$free_BC_Free;
		$Mem[TotalFeee]=$free_Mem_Free;
		$Mem[TotalUsed]=$Mem[TotalPhysical]-$Mem[TotalAvailable];
		$Mem[Swap]=$free_Swap_Used;
		
	//	print debug_tostring($odaa );
		//print "<br>odaa[5]=".$odaa[5]."\n";
		$Mr=$oa[1];
		$Mb=$oa[2];
		$MSwap=$oa[3];
		$MFree=$oa[4];
		$MBuff=$oa[5];
		$MCache=$oa[6];
		$MInactive=$odaa[5];
		$MActive=$odaa[6];
		$Ssi=$oa[7];
		$Sso=$oa[8];
		$IObi=$oa[9];
		$IObo=$oa[10];
		$SYin=$oa[11];
		$SYcs=$oa[12];
		$MTotal=4096;
		$MUsed=$MTotal-$MFree;
		
		
		
		$MAvailablePercent=number_format(($Mem[TotalAvailable]/$Mem[TotalPhysical])*100,2);
		$MAvailablePercent_str=dse_bt_colorize($MAvailablePercent,60);
		
		$MUsedPercent=number_format(($Mem[TotalUsed]/$Mem[TotalPhysical])*100,2);
		$MUsedPercent_str=dse_bt_colorize($MUsedPercent,50);
		
		$MFreePercent=number_format(($Mem[TotalFeee]/$Mem[TotalPhysical])*100,2);
		$MFreePercent_str=dse_bt_colorize($MFreePercent,20,"MINIMUM");
		
		$Mr_str=dse_bt_colorize($Mr,2);
		$Mb_str=dse_bt_colorize($Mb,2);
		$Mem[Swap]=dse_bt_colorize($Mem[Swap],257);
		
		
		//."	MInactive=$MInactive MActive=$MActive MFree=$MFree MBuff=$MBuff MCache=$MCache MSwap=$MSwap \n
		//free_BC_Used=$free_BC_Used free_BC_Free=$free_BC_Free
		*/
		
		//print $section_memory."\n";
		//exit;
		// *****************************************************************************************************************
		// *********************************************** CPU CPU CPU CPU CPU**********************************************
		// *****************************************************************************************************************
	/*	

		if($vars['Verbosity']>3) print "section_cpu()\n";
		$CpuIdle=$oa[15];
		$CpuUser=$oa[13];
		$CpuSys=$oa[14];
		$CpuUsePercent=$CpuSys+$CpuUser;
		
		$CpuUser_str=dse_bt_colorize($CpuUser,40);
		$CpuSys_str=dse_bt_colorize($CpuSys,40);
		$CpuIdle_str=dse_bt_colorize($CpuIdle,40,"MINIMUM");
		
		
		$this_loadavg=`cat /proc/loadavg`;
		if($this_loadavg!=""){
			$this_loadavg=str_replace("  ", " ", $this_loadavg); 
			$this_loadavg=str_replace("  ", " ", $this_loadavg); 
			$this_loadavg=str_replace("  ", " ", $this_loadavg); 
			$loadaggA=split(" ",$this_loadavg);
			$load_1=number_format($loadaggA[0],2);
			$load_2=number_format($loadaggA[1],2);
			$load_3=number_format($loadaggA[2],2);
			$threads=$loadaggA[3];
			$threadsa=split("/",$threads);
			$threads_running=$threadsa[0];
			$threads_total=$threadsa[1];
			$threads_running=dse_bt_colorize($threads_running,2.001);
			$threads_str="$threads_running/$threads_total";
			$last_pid=$loadaggA[4];
		}
		//Load: $load_1_str:$load_2:$load_3    Threads:$threads_str  
		//$load_1_str=dse_bt_colorize($load_1,2.001);

		$section_cpu.= " Sys:$CpuSys_str%  User:$CpuSys_str%  Idle:$CpuIdle_str%  r:$Mr_str b:$Mb_str  ";
		
		
		$section_cpu.= "Used: $Mem[TotalUsed]MB ($MUsedPercent_str%)".
		   "  Avail: $Mem[TotalAvailable]MB".
		   "  Free: $Mem[TotalFeee]MB ($MFreePercent_str%)".
		   "  SwapUsed: $Mem[Swap]MB".
		   "\n";
		
	*/	
		$section_processes="";
		$section_processes.=colorize("System Processes: \n","cyan","black") . `ps aux | sort -nr -k 3 | grep -v COMMAND | head -20`;		
	
	/*
		$Start
		$DateStr=date("d/M/Y:H:i",$Start);
		$DateStr=substr($DateStr,0,strlen($DateStr)-1);
		print "grep DateStr=$DateStr\n";
		print "grep \"$DateStr\" $LogFileName > $TmpFileName\n";
		`grep $DateStr $LogFileName > $TmpFileName`;
		*/
	if( (!$vars['DSE']['SCRIPT_SETTINGS']['EasyOnly'])  ){//&& ($Loops%5)==0
		if($vars['Verbosity']>3) print "section_httpd_log()\n";
		
		$LogFileName=$vars['DSE']['HTTP_LOG_FILE'];
		$TmpFileName=dse_exec("/dse/bin/dtmp");
	
		
		$section_httpd="";
		dse_exec("tail -n 800 $LogFileName > $TmpFileName", $vars['Verbosity']>4 );
	//	exit();
		$log_file_handle = fopen($TmpFileName, "r");
		$vars[dse_lpa_log_line_full_array]=array();
		$LinesProcessed=0;
		$MaxLinesToProcess=400;
		while ( ($La = fgetcsv( $log_file_handle, 0, " ", '"') ) !== FALSE) {
			$La=dse_log_parse_apache_La_set_Time($La);
	        $vars[dse_lpa_log_line_full_array][]=$La;
			$LinesProcessed++;
			if($MaxLinesToProcess>0 && $LinesProcessed>$MaxLinesToProcess){
				break;
			}
	    }
	    fclose($log_file_handle);
		dse_exec("rm -f $TmpFileName");
		
		
		//$section_httpd.= "Got Log Data. $LinesProcessed Lines. Processing.\n";
		$s=time()-60*7;
		$e=$s+60*3;
		$l=0; $f=99999999999;
		$Requests=0; $TotalGenTime=0;
		foreach($vars[dse_lpa_log_line_full_array] as $La){
			//if($La[Time]>=$s && $la[Time]<=$e){
				if($La[Time]>$l) $l=$La[Time];
				if($La[Time]<$f) $f=$La[Time];
				$Requests++;
				$TotalGenTime+=$La[12];
				
				$DateStr=date("d/M/Y:H:i",$La[Time]);
				//print "d=$DateStr \n";
			//}
		}
		$SpanSeconds=$l-$f;
		$PRpm=intval($Requests/($SpanSeconds/60));
		$AvgGenTime=number_format(($TotalGenTime/1000/1000)/$Requests,2);
		$AvgGenTime_str=dse_bt_colorize($AvgGenTime,1.5);
		$PRpm_str=dse_bt_colorize($PRpm,150);
		$PRps=$PRpm*60;
		$PRps_str=dse_bt_colorize($PRps,150*60);
		$section_httpd.=colorize("HTTPD:  ","cyan","black"). "Requests:  $PRpm_str/min   $PRps_str/s    Avg: ${AvgGenTime_str}s  \n";//TotalGenTime:$TotalGenTime";
		//Span:${SpanSeconds}s 

		
	}

	global $section_disk;
	if( (!$vars['DSE']['SCRIPT_SETTINGS']['EasyOnly']) &&  ($Loops<=2 || ($Loops%5)==0 ) ){
		if($vars['Verbosity']>3) print "section_disk()\n";
		
		
		global $sda1_last,$sda2_last,$diskstats_lasttime;
		$section_disk="";
		$diskstats_sda1=`cat /proc/diskstats | grep sda1`; 
		$diskstats_sda1=str_replace("  "," ",$diskstats_sda1);
		$diskstats_sda1=str_replace("  "," ",$diskstats_sda1);
		$diskstats_sda1=str_replace("  "," ",$diskstats_sda1);
		$diskstats_sda2=`cat /proc/diskstats | grep sda2`; 
		$diskstats_sda2=str_replace("  "," ",$diskstats_sda2);
		$diskstats_sda2=str_replace("  "," ",$diskstats_sda2);
		$diskstats_sda2=str_replace("  "," ",$diskstats_sda2);
		
		$diskstats_run_span=(microtime(true)-$diskstats_lasttime)*1000;
		
		$sda1=split(" ",$diskstats_sda1);
		if($sda1_last){
			$sda1_reads=$sda1[3]-$sda1_last[3];
			$sda1_sectors_written=$sda1[9]-$sda1_last[9];
			$sda1_read_ms=$sda1[6]-$sda1_last[6];
			$sda1_write_ms=$sda1[10]-$sda1_last[10];
			$sda1_io_ms=$sda1[11]-$sda1_last[11];
			$sda1_percent=number_format( (($sda1_read_ms+$sda1_write_ms+$sda1_io_ms)/$diskstats_run_span)*100,2);
			$sda1_percent=dse_bt_colorize($sda1_percent,30);
			$section_disk.="sda1: reads:$sda1_reads   sect.wri:$sda1_sectors_written    R/W/IO:$sda1_read_ms/$sda1_write_ms/$sda1_io_ms ms    Use:$sda1_percent%\n";
		}
		$sda1_last=$sda1;
		
		$sda2=split(" ",$diskstats_sda2);
		if($sda2_last){
			$sda2_reads=$sda2[3]-$sda2_last[3];
			$sda2_sectors_written=$sda2[9]-$sda2_last[9];
			$sda2_read_ms=$sda2[6]-$sda2_last[6];
			$sda2_write_ms=$sda2[10]-$sda2_last[10];
			$sda2_io_ms=$sda2[11]-$sda2_last[11];
			$sda2_percent=number_format( (($sda2_read_ms+$sda2_write_ms+$sda2_io_ms)/$diskstats_run_span)*100,2);
			$sda2_percent=dse_bt_colorize($sda2_percent,30);
			$section_disk.="sda2: reads:$sda2_reads   sect.wri:$sda2_sectors_written    R/W/IO:$sda2_read_ms/$sda2_write_ms/$sda2_io_ms ms    Use:$sda2_percent%\n";
		
		}
		//$section_disk.="diskstats_run_span=$diskstats_run_span \n";
		
		$sda2_last=$sda2;
	
		$diskstats_lasttime=microtime(true);
	}

////////////// *****************************************************************************************************
////////////// *****************************************************************************************************
////////////// *****************************************************************************************************
////////////// *****************************************************************************************************


	//print it!
	cbp_screen_clear();
	sbp_cursor_postion(0,0);
	
	
	//$vars[shell_colors_skip_reset]=TRUE;
	//print getColoredString("", 'grey', 'black');
	//$vars[shell_colors_skip_reset]=FALSE;
	
	print "\n";
	print "\n";
	print "\n";
	print "\n";

	
	print $section_cpu;
	//print $section_memory;
	print $section_files_open;

	
	print $section_procio;
	
	print $section_disk;
	print "\n";
	
	print $section_net_listening;
	print "\n";
	
	print $section_httpd;
	
	dse_sysstats_httpd_fullstatus();
	print "\n";
	
	print colorize("MYSQL: ","cyan","black");
	print $section_mysql_stats;
	print "\n";
	//print "Slow_queries:$Slow_queries ";
//	print "Last_query_cost:$Last_query_cost ";
	//foreach($MysqlStatusVars as $var_name){
	//		$val=$$var_name;
	//		print " $var_name=$val  -- ";
	//}
	
	print $section_mysql_processes;
	print "\n";
	
	print $section_processes;
	print "\n";
	/*
	$r=rand(0,500);
	if($NoDiplayYet){
		$NoDiplayYet=FALSE;
	}
	if($r==0){
		cbp_screen_clear();
		sbp_cursor_postion(0,0);
			$rt=time()-$st;
		$tps=number_format(($t/$tt)*100,2);
		$rtt=$rt/($t/$tt);
		$rts=number_format($rt/60,1)." Minutes";
		$rtts=number_format($rtt/60,1)." Minutes";
		$rtl=$rtt-$rt;
		$rtls=number_format($rtl/60,1)." Minutes";
		print "Scanning all possible Keys.... via: `smc -rk <Key>`";
		if($ScanContinue){
			print "    Continuing Prior Scan";
		}
		print "\n";
		print "  Time     Running: $rts  /  Left: $rtls  /  Total Expected: $rtts   \n";
		
	}
	
	sbp_cursor_postion(5,0);
	print "  Testing Key: $Key    ";
	
	if($r==0){
		print "    - Tried $t of $tt ($tps %)     \n";
		print "\n";
		print "Keys Found: $FoundKeys";
		file_put_contents($file_scan_last,"$i1 $i2 $i3 $i4 $c $t $rt");
		file_put_contents($file_keys_found,"$FoundKeys");
		//`echo "$i1 $i2 $i3 $i4" > $file_scan_last`;
		//`echo "$FoundKeys" > $file_keys_found`;
	}*/
}





		
?>
