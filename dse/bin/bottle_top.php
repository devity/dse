#!/usr/bin/php
<?
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");


$vars[shell_colors_reset_foreground]='light_grey';
$Start=time();
$Verbosity=0;
$ReloadSeconds=30;
$MaxLoops=30;
$ForceHighLoadRun=FALSE;
$MaxLoadBeforeExit=5;
$Threads=3;



// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="Bottle Top";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="top-like system bottleneck analyzer";
$vars['DSE']['BTOP_VERSION']="v0.04b";
$vars['DSE']['BTOP_VERSION_DATE']="2012/04/30";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******



$parameters_details = array(
  array('h','help',"this message"),
  array('q','quiet',"same as -v 0"),
  array('f','force',"force program to run even if load is high"),
  array('','version',"version info"),
  array('s:','reload-seconds:',"seconds between screen refresh"),
  array('v:','verbosity:',"0=none 1=some 2=more 3=debug"),
  array('m:','maxloops:',"# refreshes before auto exit"),
  array('z:','maxload:',"system load level to trigger auto exit"),
);
$parameters=dse_cli_get_paramaters_array($parameters_details);
$Usage=dse_cli_get_usage($parameters_details);



$options = _getopt(implode('', array_keys($parameters)),$parameters);
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


$IsSubprocess=FALSE;
foreach (array_keys($options) as $opt) switch ($opt) {
	case 'h':
  	case 'help':
  		$ShowUsage=TRUE;
		$DidSomething=TRUE;
		break;
  	case 'version':
  		$ShowVersion=TRUE;
		$DidSomething=TRUE;
		break;
	case 'q':
	case 'quiet':
		$Verbosity=0;
		break;
	case 'f':
	case 'force':
		$ForceHighLoadRun=TRUE;
		break;
		
	case 't':
		$Threads=$options['t'];
		if($Verbosity>=2) print "# Threads set to $Threads\n";
		break;
	case 'threads':
		$Threads=$options['threads'];
		if($Verbosity>=2) print "# Threads set to $Threads\n";
		break;
	case 's':
	case 'reload-seconds':
		$ReloadSeconds=$options[$opt];
		if($Verbosity>=2) print "reload-seconds set to $ReloadSeconds\n";
		break;
	case 'm':
	case 'maxloops':
		$MaxLoops=$options[$opt];
		if($Verbosity>=2) print "maxloops set to $MaxLoops\n";
		break;
	case 'v':
	case 'verbosity':
		$Verbosity=$options[$opt];
		if($Verbosity>=2) print "Verbosity set to $Verbosity\n";
		break;
	case 'z':
	case 'maxload':
		$MaxLoadBeforeExit=$options[$opt];
		if($Verbosity>=2) print "MaxLoadBeforeExit set to $MaxLoadBeforeExit\n";
		break;



}


if($Verbosity>=2){
	//print getColoredString("","black","black");
	print getColoredString("    ########======-----________   ", 'light_blue', 'black');
	print getColoredString($vars['DSE']['SCRIPT_NAME'],"yellow","black");
	print getColoredString("   ______-----======########\n", 'light_blue', 'black');
	print "  ___________ ______ ___ __ _ _   _                      \n";
	print " /                           Configuration Settings\n";
	print "|  * Script: ".$vars['DSE']['SCRIPT_FILENAME']."\n";
	print "|  * MaxLoadBeforeExit: $MaxLoadBeforeExit\n";
	print "|  * Verbosity: $Verbosity\n";
	print "|  * reload-seconds: $ReloadSeconds\n";
	print "|  * Number of Threads: $Threads\n";
	print " \________________________________________________________ __ _  _   _\n";
	print "\n";  
}

if($ShowUsage){
	print $Usage;
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


	
if($ReloadSeconds<1) $ReloadSeconds=1;
$DoLoop=TRUE;
$Loops=0;
while($DoLoop && ($MaxLoops==0 || $Loops<$MaxLoops)){
	if($Loops>0) {
		$SleepLeft=$ReloadSeconds;
		while($SleepLeft>0){
		
		sbp_cursor_postion(0,0);
		print getColoredString("*", 'yellow', 'black')." ".getColoredString(trim(`hostname`)."            ".trim(`date`),'cyan','black');	
		$SleepLeft_str=getColoredString($SleepLeft,"yellow","black");
		
		$Load_str=getColoredString("$Load", 'yellow', 'black');
		$Loops_str=getColoredString($Loops+1, 'yellow', 'black');
		
		
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
		
		
		$str= "   Loop: $Loops_str / $MaxLoops  Next: ${SleepLeft_str}s      $Load_str   \n";
		print $str;
		
	
			//sleep(1);
			$SleepLeft--;
			/*
			$keys="";
			while (($buf = fgetc($fp, 4096)) != false) {  //fgets is required if we want to handle escape sequenced keys 
		    	$keys .= $buf; 
		  	} 
			 * */
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
						case '-':
							$ReloadSeconds=intval($ReloadSeconds*5/4);
							sbp_cursor_postion(0,0);
							print getColoredString("ReloadSeconds raised to $ReloadSeconds\n", 'green', 'black');
							sleep(1);
							break;
						case '+':
							$ReloadSeconds=intval($ReloadSeconds*3/4);
							sbp_cursor_postion(0,0);
							print getColoredString("ReloadSeconds lowered to $ReloadSeconds\n", 'green', 'black');
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
							cbp_screen_clear();
							sbp_cursor_postion(0,0);
							print "\n\n";
							print getColoredString("Interactive Commands for ".$vars['DSE']['SCRIPT_NAME'].": \n", 'green', 'black');
							print "  h - this message\n";
							print "  s - run http_stress\n";
							//print "  u - update all\n";
							print "  q - quit/exit ".$vars['DSE']['SCRIPT_FILENAME']."\n";
							print "  - - slow refresh 25%\n";
							print "  + - speed refresh 25%\n";
							//print "  h - this message\n";
							
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
	if((!$ForceHighLoadRun) && $Load>$MaxLoadBeforeExit){
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
	
	//first update data before clear so no slow print / flickering
		//get mysql process info
		$section_mysql_processes="";
		$sql_query="SHOW FULL PROCESSLIST";
		$mysql_processes_raw=`sudo echo "$sql_query" | mysql -u localroot | grep -v PROCESSLIST | grep -v Sleep`;
		$mysql_processes_line_array=split("\n",$mysql_processes_raw);
		foreach($mysql_processes_line_array as $k=>$mysql_processes_line){
			
			$tsa=split("\t",$mysql_processes_line);
			if(intval($tsa[0])>0){
			//	$ssa=split(" ",$mysql_processes_line);
				//print "ssa="; print_r($ssa); print "\n";
				//print "tsa="; print_r($tsa); print "\n";
				$ID=$tsa[0];
				$User=$tsa[1];
				$Host=$tsa[2];
				$DB=$tsa[3];
				$Command=$tsa[4];
				$Time=$tsa[5];
				$State=$tsa[6];
				$Info=$tsa[7];
				$Command=substr($Command,0,100);
                                $Info=substr($Info,0,100);

				$section_mysql_processes.= "$User $DB $State $Command $Info\n";
			}
		}
		
		
	
		//mysql stats
		$MysqlStatusVars=array(
			"Queries", "Slow_queries","Last_query_cost",
			"Handler_update", "Handler_write", "Handler_delete", 
			"Select_range", "Select_scan", "Sort_scan", 
			"Innodb_buffer_pool_pages_free",
			"Qcache_free_blocks", "Qcache_total_blocks", "Qcache_free_memory", 
			"Created_tmp_disk_tables", "Created_tmp_tables", 
			"Key_blocks_unused", "Key_blocks_used", "Key_buffer_fraction_%", 
			"Open_files", "Open_tables", 
			"Table_locks_immediate", "Table_locks_waited", 
			);
		foreach($MysqlStatusVars as $var_name){
			$$var_name="";
		}
		$section_mysql_stats="";
		$sql_query="SHOW STATUS ";
		$mysql_status_raw=`sudo echo "$sql_query" | mysql -u localroot`;
		$mysql_status_line_array=split("\n",$mysql_status_raw);
		foreach($mysql_status_line_array as $k=>$mysql_status_line){
			//	$ssa=split(" ",$mysql_processes_line);
				//print "ssa="; print_r($ssa); print "\n";
				$tsa=split("\t",$mysql_status_line);
				//print "tsa="; print_r($tsa); print "\n";
				
				foreach($MysqlStatusVars as $var_name){
					if($tsa[0]==$var_name){
						$$var_name=$tsa[1];
						//$section_mysql_stats.="$var_name=$tsa[1];   -- ";
					}
				}
		
						
		}
		if($Queries){
			$Slow_percent=number_format(($Slow_queries/$Queries)*10,2);
		}else{
			$Slow_percent=0;
		}
		
		global $LastQueries, $LastRunTime;
		
		$Qps=($Queries-$LastQueries)/(time()-$LastRunTime);
		//$section_mysql_stats.= "Qps=($Queries-$LastQueries)/(time()-$LastRunTime);\n";


		$Qps_str=number_format($Qps,2);
		$Qps_str=dse_bt_colorize($Qps_str,100);
		
		
		
		//$Qcache_free_blocks_str=dse_bt_colorize($Qcache_free_blocks,10,"MINIMUM");
		//$Qcache_total_blocks_str=dse_bt_colorize($Qcache_total_blocks,20000);
		$Qcache_free_memory_str=dse_bt_colorize(number_format($Qcache_free_memory/(1024*1024),1),150001000/(1024*1024),"MINIMUM");

		$section_mysql_stats.="Queries :$Queries  Qps:$Qps_str  Slow:$Slow_queries %$Slow_percent ";// LastCost:$Last_query_cost \n";
		$section_mysql_stats.="Updates: $Handler_update  Delete: $Handler_delete  Write: $Handler_write\n";
	//	$section_mysql_stats.="Innodb bppf:$Innodb_buffer_pool_pages_free \n";
		$section_mysql_stats.="Qcache free_blocks:$Qcache_free_blocks  total_blocks:$Qcache_total_blocks free_memory:${Qcache_free_memory_str}MB\n";
		$section_mysql_stats.="Open: Files: $Open_files  Tables: $Open_tables  \n";
		//$section_mysql_stats.="Key_blocks_unused:$Key_blocks_unused   Key_blocks_used:$Key_blocks_used   \n";
		//$section_mysql_stats.="Select_range:$Select_range   Select_scan:$Select_scan   Sort_scan:$Sort_scan  \n";
	
		$LastQueries=$Queries;
		$LastRunTime=time();
		
			//"Created_tmp_disk_tables", "Created_tmp_tables", 
			//"", "", "Key_buffer_fraction_%", 
		//	"", "", 
			//"Table_locks_immediate", "Table_locks_waited", 
		
	//exit();
	
	
	
		global $section_files_open;
		
		if(($Loops%5)==0 ){
	//	include_once 'Text/Diff.php'; 
	//	include_once 'Text/Diff/Renderer.php'; 
	//	include_once 'Text/Diff/Renderer/inline.php'; 
			global $lsof_last;
			$section_files_open="";
			$lsof=`sudo lsof`;
			$lsof_a=split("\n",$lsof);
			$open_files=sizeof($lsof_a);
			$open_files_str=dse_bt_colorize($open_files,4000);
			$section_files_open.="lsof open files: $open_files_str      ";
			/*if($lsof_last){
				$lsof_last_a=split("\n",$lsof_last);
				
				
				$r=(array_diff($lsof_a, $lsof_last_a));
				foreach($r as $e){
					$ea=split("\n",$e);
					foreach($ea as $ep){	
						$section_files_open.= $ep."\n";
					}			
				}
				//$diff = &new Text_Diff($lsof,$lsof_last);
				//$renderer = &new Text_Diff_Renderer_inline();		
				//$section_files_open.= $renderer->render($diff);
				
			}*/
			$lsof_last=$lsof;
		}
		
		// ********************************************************i*********************************************************
		// ******************************************   /proc/$PID/io  /proc/$PID/io   *************************************
		// *****************************************************************************************************************
		global $section_procio;
		if(($Loops%20)==0 ){
		//	dse_proc_io_get(TRUE);
		}
		if(($Loops%5)==0 ){
			$section_procio="";
			global $procIOs;
			dse_proc_io_get();
			$wt=$procIOs[$vars[dse_proc_io_get_last_time]]['TOTAL']['wchar'];
			$rt=$procIOs[$vars[dse_proc_io_get_last_time]]['TOTAL']['rchar'];
			$dt=$vars[dse_proc_io_get_last_time]-$vars[dse_proc_io_get_start_time];
			$wtps=intval($wt/$dt);
			$rtps=intval($rt/$dt);
			$wt=number_format($wt/1024,0)."kB";
			$rt=number_format($rt/1024,0)."kB";
			$wtps_str=number_format($wtps/1024,0)."kB";
			$rtps_str=number_format($rtps/1024,0)."kB";
			$rtps_str=dse_bt_colorize($rtps/1024,8000,"MAXIMUM",$rtps_str);
			$wtps_str=dse_bt_colorize($wtps/1024,3000,"MAXIMUM",$wtps_str);
			
			$section_procio= "/proc/io: w: $wtps_str/s  r: $rtps_str/s\n";// dWb=$wbt ($wbtps/s)    dRb=$rbt ($rbtps/s) \n\n";
		}
			
		// *****************************************************************************************************************
		// *********************************************** MEMORY MEMORY MEMORY MEMORY *************************************
		// *****************************************************************************************************************
	
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
		
		//print $section_memory."\n";
		//exit;
		// *****************************************************************************************************************
		// *********************************************** CPU CPU CPU CPU CPU**********************************************
		// *****************************************************************************************************************
		

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
		
		
		$section_processes="";
		$section_processes.=`ps aux | sort -nr -k 3 | grep -v COMMAND | head -20`;		
	
	/*
		$Start
		$DateStr=date("d/M/Y:H:i",$Start);
		$DateStr=substr($DateStr,0,strlen($DateStr)-1);
		print "grep DateStr=$DateStr\n";
		print "grep \"$DateStr\" $LogFileName > $TmpFileName\n";
		`grep $DateStr $LogFileName > $TmpFileName`;
		*/
	if(($Loops%5)==0 ){
		$LogFileName="/home/httpd/batteriesdirect.com/stats/batteriesdirect.com-custom_log";
		$TmpFileName="/tmp/dse_log.tmp.".rand(1111111,99999999);
	
		
		$section_httpd="";
		`tail -n 500 $LogFileName > $TmpFileName`;
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
		`rm -f $TmpFileName`;
		
		
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
		$PRpm_str=dse_bt_colorize($PRpm,90);
		$section_httpd.="HTTPD Requests/Minute:$PRpm_str   Avg: ${AvgGenTime_str}s  Span:${SpanSeconds}s \n";//TotalGenTime:$TotalGenTime";
		

		
	}

	global $section_disk;
	if($Loops<=2 || ($Loops%5)==0 ){
		
		
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

	
	print $section_cpu;
	//print $section_memory;
	print $section_files_open;

	print $section_procio;
	
	print $section_disk;
	print "\n";
	
	print $section_httpd;
	
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
