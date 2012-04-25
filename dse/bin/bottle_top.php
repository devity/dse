#!/usr/bin/php
<?
//error_reporting(E_ALL);
//ini_set('display_errors','On');	


$vars[shell_colors_reset_foreground]='light_grey';
$Start=time();
$Verbosity=3;
$ReloadSeconds=30;
$MaxLoops=30;
$ForceHighLoadRun=FALSE;
$MaxLoadBeforeExit=5;
$Threads=3;

$In="/dse/bin/stress_urls.txt";
$Log="/var/log/http_stress.log";
$ThreadLog="/tmp/http_stress.thread.log";


$Script=$argv[0];

$ScriptName="Bottle Top";

$parameters = array(
  'h' => 'help',
  'q' => 'quiet',
  'f' => 'force',
  's:' => 'reload-seconds:',
  'v:' => 'verbosity:',
  'm:' => 'maxloops:',
  'z:' => 'maxload:',
);
$flag_help_lines = array(
  'h' => "\thelp - this message",
  'q' => "quiet - same as -v 0",
  'f' => "force - force program to run even if load is high",
  's:' => "reload-seconds - seconds between screen refresh",
  'v:' => "\tverbosity - 0=none 1=some 2=more 3=debug",
  'm:' => "\tmaxloops - # refreshes before auto exit",
  'z:' => "\tmaxload - system load level to trigger auto exit",
);

$ScriptName_str=getColoredString($ScriptName, 'yellow', 'black');
	
$Usage="   $ScriptName_str - 'top' like system bottleneck monitor
       by Louy of Devity.com


".getColoredString("command line usage:","yellow","black").
getColoredString(" bottle_top","cyan","black").
getColoredString(" (options)","dark_cyan","black")."     
";
foreach($parameters as $k=>$v){
	$k2=str_replace(":","",$k);
	$v2=str_replace(":","",$v);
	$Usage.=" -${k2}, --${v2}\t".$flag_help_lines[$k]."\n";
}
$Usage.="\n\n";

$StartLoad=get_load();

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
		$Verbosity=$options['v'];
		if($Verbosity>=2) print "Verbosity set to $Verbosity\n";
		break;
	case 'verbosity':
		$Verbosity=$options['verbosity'];
		if($Verbosity>=2) print "Verbosity set to $Verbosity\n";
		break;
	case 'z':
		$MaxLoadBeforeExit=$options['z'];
		if($Verbosity>=2) print "MaxLoadBeforeExit set to $MaxLoadBeforeExit\n";
		break;
	case 'maxload':
		$MaxLoadBeforeExit=$options['maxload'];
		if($Verbosity>=2) print "MaxLoadBeforeExit set to $MaxLoadBeforeExit\n";
		break;



}


if($Verbosity>=2){
	//print getColoredString("","black","black");
	print getColoredString("    ########======-----________   ", 'light_blue', 'black');
	print getColoredString($ScriptName,"yellow","black");
	print getColoredString("   ______-----======########\n", 'light_blue', 'black');
	print "  ___________ ______ ___ __ _ _   _                      \n";
	print " /                           Configuration Settings\n";
	print "|  * Script: $Script\n";
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
							print getColoredString("'q' pressed ([Q]uit). Exiting $Script. \n\n", 'green', 'black');
							$vars[shell_colors_reset_foreground]='';	print getColoredString("","white","black");
							exit(0);
							break;	
						case 'h':
							cbp_screen_clear();
							sbp_cursor_postion(0,0);
							print "\n\n";
							print getColoredString("Interactive Commands for $ScriptName: \n", 'green', 'black');
							print "  h - this message\n";
							print "  s - run http_stress\n";
							//print "  u - update all\n";
							print "  q - quit/exit $Script\n";
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
		print getColoredString("high load ($Load). Exiting $Script. \n\n", 'white', 'red');
		$vars[shell_colors_reset_foreground]='';	print getColoredString("","white","black");
		exit(-2);
	}
	
	
	
	//
	

	
	update_display($keys);
	$Loops++;
}
print getColoredString("Done. Exiting $Script. \n\n", 'black', 'green');
exit(0);



function readline_timeout($sec, $def) 
{ 
    return trim(shell_exec('bash -c ' . 
        escapeshellarg('phprlto=' . 
            escapeshellarg($def) . ';' . 
            'read -n 1 -t ' . ((int)$sec) . ' phprlto;' . 
            'echo "$phprlto"'))); 
} 


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


function dse_bt_colorize($v,$t,$type="MAXIMUM",$v_str=""){
	global $vars;
	if($v_str==""){
		$v_str=$v;
	}
	if($type=="MAXIMUM"){
		if($v>=$t*3/2){
			return getColoredString($v_str, 'white', 'red');
		}elseif($v>=$t){
			return getColoredString($v_str, 'pink', 'black');
		}elseif($v>=$t*2/3){
			return getColoredString($v_str, 'yellow', 'black');
		}else{
			return getColoredString($v_str, 'green', 'black');
		}
	}elseif($type=="MINIMUM"){
		if($v<=$t/3){
			return getColoredString($v_str, 'white', 'red');
		}elseif($v<=$t){
			return getColoredString($v_str, 'pink', 'black');
		}elseif($v<=$t*2/3){
			return getColoredString($v_str, 'yellow', 'black');
		}else{
			return getColoredString($v_str, 'green', 'black');
		}
	}
	
}





function dse_log_parse_apache_La_set_Time($La){
	global $vars;
	$TimeStr=substr($La[3],1);
	$dp=split(":",$TimeStr);
	$format = '%d/%m/%Y %H:%M:%S';
	$Time=strptime($TimeStr, $format);
//	include_once ("$vars[SITE_ROOT]/include/date_str_parse.php");
	$SQLDate=date_str_to_sql_date($dp[0],"{DD}/{Mon}/{YYYY}:");
	$La[Time]=SQLDate2time($SQLDate)+$dp[1]*60*60+$dp[2]*60+$dp[3];
	return $La;
}


function date_str_to_sql_date($str,$fmt=""){
	global $vars;
	
	$str=str_replace("\\","-",$str);
	$fmt=str_replace("\\","-",$fmt);
	$stre=urlencode($str);
	$fmte=urlencode($fmt);
	//print  "in date_str_to_sql_date($stre,$fmte)<br>";
	if($fmt){
		
		$str=str_replace("Thru ","",$str);
		$str=str_replace("Until ","",$str);
		
		$str=str_replace("Thu ","",$str);
		$str=str_replace("Thur ","",$str);
		$str=str_replace("Fri ","",$str);
		$str=str_replace("Sat ","",$str);
		$str=str_replace("Sun ","",$str);
		$str=str_replace("Mon ","",$str);
		$str=str_replace("Tue ","",$str);
		$str=str_replace("Tues ","",$str);
		$str=str_replace("Wed ","",$str);
		
	//	$fmt=str_replace("{Day} ","",$fmt);
	//	$fmt=str_replace("{Dy} ","",$fmt);
		
		
		
		$fmt="$fmt ";
		$lc=0;
		while($fmt && $fmt!=" " && $lc<100){
			$lc++;
//	print "FMT=[$fmt]<br>";
			$fc=substr($fmt,0,1);
//	print "fc=$fc<br>";
			if($fc=="{"){
				$etpos=strpos($fmt,"}");
				if(!($etpos===FALSE)){
					$tag=substr($fmt,0,$etpos+1);
					$fmt=substr($fmt,$etpos+1);
				//	include_once "$vars[SITE_ROOT]/include/str_functions.php";					
					$fndcpos=strpos_nonalnum($str);
	//	print "tag=$tag fmt=$fmt str=$str etpos=$etpos fndcpos=$fndcpos<br>";
					if($fndcpos){
						$data=substr($str,0,$fndcpos);
						$str=substr($str,$fndcpos);
						//print "?=$fndcpos tag=$tag data=$data<br>";
					}else{
						$data=$str;
						$str="";
						//print "?2=$fndcpos tag=$tag data=$data<br>";
					}
		//	print "tag='$tag' data='$data'<br>";
					switch($tag){
						case "{Month}":
						case "{month}":
						case "{Mon}":
						case "{MON}":
						case "{mon}":
							$data=strtoupper($data);
						case "{MONTH}":
							$data=str_replace(".","",$data);   
							if($data=="JANUARY") $Month=1;
							if($data=="FEBRUARY") $Month=2;
							if($data=="MARCH") $Month=3;
							if($data=="APRIL") $Month=4;
							if($data=="MAY") $Month=5;
							if($data=="JUNE") $Month=6;
							if($data=="JULY") $Month=7;
							if($data=="AUGUST") $Month=8;
							if($data=="SEPTEMBER") $Month=9;
							if($data=="OCTOBER") $Month=10;
							if($data=="NOVEMBER") $Month=11;
							if($data=="DECEMBER") $Month=12;
							
							if($data=="JAN") $Month=1;
							if($data=="FEB") $Month=2;
							if($data=="MAR") $Month=3;
							if($data=="APR") $Month=4;
							if($data=="MAP") $Month=5;
							if($data=="JUN") $Month=6;
							if($data=="JUNE") $Month=6;
							if($data=="JUL") $Month=7;							
							if($data=="JULY") $Month=7;
							if($data=="AUG") $Month=8;
							if($data=="SEP") $Month=9;
							if($data=="SEPT") $Month=9;
							if($data=="OCT") $Month=10;
							if($data=="NOV") $Month=11;
							if($data=="DEC") $Month=12;
							break;
						case "{D}":
						case "{DD}":
							$Day=$data;
							break;
						case "{Dsfx}":
						case "{DDsfx}":
							$Day=intval($data);
							break;
						case "{M}":
						case "{MM}":
							$Month=$data;
							break;
						case "{YY}":
							$Year="20".$data;
							break;
						case "{YYYY}":
							$Year=$data;
							break;
						case "Day":
						case "Dy":
							break;
					}
	//	print "ymd: $Year-$Month-$Day<br>";
				}else{
					//print "error unmatched { in date format string<br>";
					$fmt="";
				}
			}else{
				$stf=$str[0];
				if($fc==$stf){
					$str=substr($str,1);
					$fmt=substr($fmt,1);
				}else{
					//print "gggggg: ";
//					include_once "$vars[SITE_ROOT]/include/str_functions.php";
					$strfan=strpos_alnum($str);
					if(!($strfan===FALSE)){
						$str=substr($str,$strfan);
					}
					$fmtfan=strpos($fmt,"{");
					if(!($fmtfan===FALSE)){
						$fmt=substr($fmt,$fmtfan);
					}
					//print "str=$str strfan=$strfan fmt=$fmt fmtfan=$fmtfan<br>";
				}
			}
		}
		
		if($Day<10){
			$Day="0".intval($Day);
		}
		if($Month<10){
			$Month="0".intval($Month);
		}
		if(!$Year){
			//if($Month>0 && $Month<5){
				//$Year="2010";
				$Year=date("Y");
			//}else{
			//	$Year="2008";
			//}
		}
		if(intval($Year)<1000 || intval($Month)<1 || intval($Day)<1){
			return "";
		}
		
		$r="$Year-$Month-$Day";
		$r=str_replace(" ","",$r);
		return $r;
	}
		
	
	if(stristr($str,"Jan"))		$Month="01";
	if(stristr($str,"Feb"))		$Month="02";
	if(stristr($str,"Mar"))		$Month="03";
	if(stristr($str,"Apr"))		$Month="04";
	if(stristr($str,"May"))		$Month="05";
	if(stristr($str,"Jun"))		$Month="06";
	if(stristr($str,"Jul"))		$Month="07";
	if(stristr($str,"Aug"))		$Month="08";
	if(stristr($str,"Sep"))		$Month="09";
	if(stristr($str,"Oct"))		$Month="10";
	if(stristr($str,"Nov"))		$Month="11";
	if(stristr($str,"Dec"))		$Month="12";

	$str=str_replace("Thu","",$str);
	
	$DayAbrPos=strpos($str,"th");
	if(!$DayAbrPos){
		$DayAbrPos=strpos($str,"st");
		if(!$DayAbrPos){
			$DayAbrPos=strpos($str,"nd");
			if(!$DayAbrPos){
				$DayAbrPos=strpos($str,"rd");
			}
		}
	}
	if($DayAbrPos){		
		$Day1=substr($str,$DayAbrPos-2,1);
		if($Day1==" "){
			$Day="0" . substr($str,$DayAbrPos-1,1);
			//print "B";
		}else{
			$Day=substr($str,$DayAbrPos-2,2);
			//print "C";
		}
	}else{
		//print "D ($str)";
	}
	
	
	$Year1=substr($str,strlen($str)-4,2);
	$Year2=substr($str,strlen($str)-3,1);
	$Year3=substr($str,strlen($str)-4,4);
	if(($Year1=="19" || $Year1=="20") && intval($Year3>1900)){
		$Year=substr($str,strlen($str)-4,4);
		$YearSize=5;
	}elseif($Year2==" "){
		$Year="20".substr($str,strlen($str)-2,2);
		$YearSize=3;
	}else{
		$Year=date("Y");
		$YearSize=0;
	}
	
	if(!$Day){
		$Day1=substr($str,(strlen($str)-$YearSize)-1,1);
		if($Day1=","){
			$Day1=substr($str,(strlen($str)-$YearSize)-3,1);
			if($Day1==" "){
				$Day="0" . substr($str,(strlen($str)-$YearSize)-2,1);
			}else{
				$Day=substr($str,(strlen($str)-$YearSize)-3,2);
			}
		}
	}
	
	print "y=$Year m=$Month d=$Day <br>";
	
	if(intval($Year)<1000 || intval($Month)<1 || intval($Day)<1){
		//print "str=$str<br>";
		//12/30 07
		//if(preg_match("/^[01]?[0-9]\/[0-9]{1,2} 0[0-9]$/",$str)){
		if(preg_match("/^[01]?[0-9]\/[0-9]{1,2} 0[0-9]$/",$str)){
			//print "matches! mm/dd yy<br>";
			$Day=strcut($str,"/"," ");
			$Month=strcut($str,"","/");
			$Year=strcut($str," ","");			
			$Year="20".$Year;
		}
		if(preg_match("/^[01]?[0-9]\/[0-9]{1,2}$/",$str)){
			//print "matches! mm/dd<br>";
			$Day=strcut($str,"/","");
			$Month=strcut($str,"","/");
			
			$Year=date("Y");
			//print "y=$Year m=$Month d=$Day <br>";
		}
		
		if($Day<10){
			$Day="0".intval($Day);
		}
		if($Month<10){
			$Month="0".intval($Month);
		}
		if(intval($Year)<1000 || intval($Month)<1 || intval($Day)<1){
			return "";
		}
	}
	
	//print "$Year-$Month-$Day ]v";
	
	return "$Year-$Month-$Day";
}


function strpos_nonalnum($haystack, $offset=0){
	$haystack=strtoupper($haystack);
	$i=0;
	while(1){
		$c=substr($haystack,$i,1);
		if(($c===FALSE)){
	//		print "RETURN break pos_nonalnum: '$c', $i <br>";
			break;
		}
	//	print "pos_nonalnum: '$c', $i <br>";
		if( (ord($c)>=ord('A') && ord($c)<=ord('Z')) || (ord($c)>=ord('0') && ord($c)<=ord('9')) ){
		}else{
		//	print "RETURN pos_nonalnum: '$c', $i <br>";
			return $i;
		}
		$i++;		
	}
//	print "RETURN FALSE pos_nonalnum: '$c', $i <br>";
	return FALSE;	
}


function strpos_alnum($haystack, $offset=0){
	$haystack=strtoupper($haystack);
	$i=$offset;
	while(1){
		$c=substr($haystack,$i,1);
		if(($c===FALSE)){
			break;
		}
		//print "pos_alnum: '$c', $i <br>";
		if( (ord($c)>=ord('A') && ord($c)<=ord('Z')) || (ord($c)>=ord('0') && ord($c)<=ord('9')) ){
			return $i;
		}else{			
		}
		$i++;		
	}
	return FALSE;	
}



function SQLDate2time($in){
	return YYYYMMDD2time($in);
}

function YYYYMMDD2time($in){
	
	$t = split("/",$in);	
	if (count($t)!=3) {
		$t = split("-",$in);
	}
	$c=count($t);		
	if (count($t)!=3) {
		$t = split(" ",$in);
	}
	$c=count($t);	
	if (count($t)!=3) {
		return -1;
	}
//print "YYYYMMDD2time($in) split=($t[0])($t[1])($t[2])<br>";
	if (!is_numeric($t[0])) return -2;
	if (!is_numeric($t[1])) return -3;
	if (!is_numeric($t[2])) return -4;	
	if ($t[0]<1902 || $t[0]>2037) return -5;	
	if ($t[0]<1970){
		$year_offset=1970-$t[0];
		$t[0]=1970;
	}	
	$result=mktime (0,0,0, $t[1], $t[2], $t[0]);
	if($year_offset){
		$result-=$year_offset*365*24*60*60;
	}
	return $result;
}
 
		
/* ************************** FUNCTIONS FUNCTIONS FUNCTIONS FUNCTIONS ************************ */

function str_compare_count_matching_prefix_chars($a,$b){
	$al=strlen($a); $bl=strlen($b);
	//print "a=$a b=$b\n\n";
	$s=0;
	for($c=0;$c<=$al &&$c<=$bl;$c++){
		if($a[$c]==" " && $b[$c]==" "){
			$s=$c+1;
		}
		if($a[$c]!=$b[$c]){
			return $s;
		}
	}
	if($al<$bl){
		return $al;
	}else{
		return $bl;
	}
}



function remove_duplicate_lines($Lines){
	$out=array();
	foreach(split("\n",$Lines) as $Line){
		$Found=FALSE;
		for($i=0;$i<sizeof($out);$i++){
			if($out[$i]==$Line){
				$Found=TRUE;		
			}
		}
		if(!$Found){
			$out[]=$Line;
		}
	}
	$Out2="";
	foreach($out as $Line){
		if($Out2){
			$Out2.="\n";
		}
		$Out2.=$Line;
	}
	return $Out2;
}




function combine_sameprefixed_lines($LogsCombined){
	global $NumberOfBytesSameLimit;
	$Out="";
	$c=0;
	$LastText="";
	foreach(split("\n",$LogsCombined) as $Line){
		$lpa=split(" ",$Line);
		$Date="$lpa[0] $lpa[1] $lpa[2]";
		$Text=substr($Line,strlen($Date)+1);
		$NumberOfBytesSame=str_compare_count_matching_prefix_chars($Text,$LastText);
		if($Text==$LastText){
		}elseif($NumberOfBytesSame>$NumberOfBytesSameLimit){
			$LineNewPart=substr($Line,$NumberOfBytesSame);
			$Out.= ",& $LineNewPart";
			$LastLine="";
			$LastDate="";
			$LastText="";
		}elseif(!(strstr($Line,"ast message repeated")===FALSE)){
		}else{
			if($Line!=""){
				$c++;
				$Out.= "\n$Line";
				$LastLine=$Line;
				$LastDate=$Date;
				$LastText=$Text;
			}
		}
	}
	return $Out;
}





/*
* This function deletes the given element from a one-dimension array
* Parameters: $array:    the array (in/out)
*             $deleteIt: the value which we would like to delete
*             $useOldKeys: if it is false then the function will re-index the array (from 0, 1, ...)
*                          if it is true: the function will keep the old keys
*				$useDeleteItAsIndex: uses deleteIt for compare against array index/key instead of values
* Returns true, if this value was in the array, otherwise false (in this case the array is same as before)
*/
function deleteFromArray(&$array, $deleteIt, $useOldKeys = FALSE, $useDeleteItAsIndex=FALSE ){
    $tmpArray = array();
    $found = FALSE;
   // print "array="; print_r($array); print "\n";
    foreach($array as $key => $value)
    {
    	//print "k=$key v=$value \n";
        if($useDeleteItAsIndex){
        	$Match=($key !== $deleteIt)==TRUE;
        }else{
        	$Match=($value !== $deleteIt)==TRUE;
        }
        
        if($Match){
        	if($useOldKeys){
        	    $tmpArray[$key] = $value;
            }else{
                $tmpArray[] = $value;
            }
        }else{
            $found = TRUE;
        }
    }
    $array = $tmpArray;
    return $found;
}





function sbp_cursor_postion($L=0,$C=0){
        print "\033[${L};${C}H";
}
//function sbp_cursor_column($C=0){
  //      print "\033[;${C}H";
//}
function cbp_screen_clear(){
        print "\033[2J";
}
function cbp_cursor_left($N=1){
        print "\033[${N}D";
}
function cbp_cursor_up($N=1){
        print "\033[${N}A";
}

function get_load(){	
	$this_loadavg=`cat /proc/loadavg`;
	if($this_loadavg!=""){  
		$loadaggA=split("	",$this_loadavg);
		return number_format($loadaggA[0],3);
	}
	return -1;
}


//////////////////////////////////////////////////////////////////////////////////////////

function _getopt ( ) {

/* _getopt(): Ver. 1.3      2009/05/30
   My page: http://www.ntu.beautifulworldco.com/weblog/?p=526

Usage: _getopt ( [$flag,] $short_option [, $long_option] );

Note that another function split_para() is required, which can be found in the same
page.

_getopt() fully simulates getopt() which is described at
http://us.php.net/manual/en/function.getopt.php , including long options for PHP
version under 5.3.0. (Prior to 5.3.0, long options was only available on few systems)

Besides legacy usage of getopt(), I also added a new option to manipulate your own
argument lists instead of those from command lines. This new option can be a string
or an array such as 

$flag = "-f value_f -ab --required 9 --optional=PK --option -v test -k";
or
$flag = array ( "-f", "value_f", "-ab", "--required", "9", "--optional=PK", "--option" );

So there are four ways to work with _getopt(),

1. _getopt ( $short_option );

  it's a legacy usage, same as getopt ( $short_option ).

2. _getopt ( $short_option, $long_option );

  it's a legacy usage, same as getopt ( $short_option, $long_option ).

3. _getopt ( $flag, $short_option );

  use your own argument lists instead of command line arguments.

4. _getopt ( $flag, $short_option, $long_option );

  use your own argument lists instead of command line arguments.

*/

  if ( func_num_args() == 1 ) {
     $flag =  $flag_array = $GLOBALS['argv'];
     $short_option = func_get_arg ( 0 );
     $long_option = array ();
  } else if ( func_num_args() == 2 ) {
     if ( is_array ( func_get_arg ( 1 ) ) ) {
        $flag = $GLOBALS['argv'];
        $short_option = func_get_arg ( 0 );
        $long_option = func_get_arg ( 1 );
     } else {
        $flag = func_get_arg ( 0 );
        $short_option = func_get_arg ( 1 );
        $long_option = array ();
     }
  } else if ( func_num_args() == 3 ) {
     $flag = func_get_arg ( 0 );
     $short_option = func_get_arg ( 1 );
     $long_option = func_get_arg ( 2 );
  } else {
     exit ( "wrong options\n" );
  }

  $short_option = trim ( $short_option );

  $short_no_value = array();
  $short_required_value = array();
  $short_optional_value = array();
  $long_no_value = array();
  $long_required_value = array();
  $long_optional_value = array();
  $options = array();

  for ( $i = 0; $i < strlen ( $short_option ); ) {
     if ( $short_option{$i} != ":" ) {
        if ( $i == strlen ( $short_option ) - 1 ) {
          $short_no_value[] = $short_option{$i};
          break;
        } else if ( $short_option{$i+1} != ":" ) {
          $short_no_value[] = $short_option{$i};
          $i++;
          continue;
        } else if ( $short_option{$i+1} == ":" && $short_option{$i+2} != ":" ) {
          $short_required_value[] = $short_option{$i};
          $i += 2;
          continue;
        } else if ( $short_option{$i+1} == ":" && $short_option{$i+2} == ":" ) {
          $short_optional_value[] = $short_option{$i};
          $i += 3;
          continue;
        }
     } else {
        continue;
     }
  }

  foreach ( $long_option as $a ) {
     if ( substr( $a, -2 ) == "::" ) {
        $long_optional_value[] = substr( $a, 0, -2);
        continue;
     } else if ( substr( $a, -1 ) == ":" ) {
        $long_required_value[] = substr( $a, 0, -1 );
        continue;
     } else {
        $long_no_value[] = $a;
        continue;
     }
  }

  if ( is_array ( $flag ) )
     $flag_array = $flag;
  else {
     $flag = "- $flag";
     $flag_array = split_para( $flag );
  }

  for ( $i = 0; $i < count( $flag_array ); ) {

     if ( $i >= count ( $flag_array ) )
        break;

     if ( ! $flag_array[$i] || $flag_array[$i] == "-" ) {
        $i++;
        continue;
     }

     if ( $flag_array[$i]{0} != "-" ) {
        $i++;
        continue;

     }

     if ( substr( $flag_array[$i], 0, 2 ) == "--" ) {

        if (strpos($flag_array[$i], '=') != false) {
          list($key, $value) = explode('=', substr($flag_array[$i], 2), 2);
          if ( in_array ( $key, $long_required_value ) || in_array ( $key, $long_optional_value ) )
             $options[$key][] = $value;
          $i++;
          continue;
        }

        if (strpos($flag_array[$i], '=') == false) {
          $key = substr( $flag_array[$i], 2 );
          if ( in_array( substr( $flag_array[$i], 2 ), $long_required_value ) ) {
             $options[$key][] = $flag_array[$i+1];
             $i += 2;
             continue;
          } else if ( in_array( substr( $flag_array[$i], 2 ), $long_optional_value ) ) {
             if ( $flag_array[$i+1] != "" && $flag_array[$i+1]{0} != "-" ) {
                $options[$key][] = $flag_array[$i+1];
                $i += 2;
             } else {
                $options[$key][] = FALSE;
                $i ++;
             }
             continue;
          } else if ( in_array( substr( $flag_array[$i], 2 ), $long_no_value ) ) {
             $options[$key][] = FALSE;
             $i++;
             continue;
          } else {
             $i++;
             continue;
          }
        }

     } else if ( $flag_array[$i]{0} == "-" && $flag_array[$i]{1} != "-" ) {

        for ( $j=1; $j < strlen($flag_array[$i]); $j++ ) {
          if ( in_array( $flag_array[$i]{$j}, $short_required_value ) || in_array( $flag_array[$i]{$j}, $short_optional_value )) {

             if ( $j == strlen($flag_array[$i]) - 1  ) {
                if ( in_array( $flag_array[$i]{$j}, $short_required_value ) ) {
                  $options[$flag_array[$i]{$j}][] = $flag_array[$i+1];
                  $i += 2;
                } else if ( in_array( $flag_array[$i]{$j}, $short_optional_value ) && $flag_array[$i+1] != "" && $flag_array[$i+1]{0} != "-" ) {
                  $options[$flag_array[$i]{$j}][] = $flag_array[$i+1];
                  $i += 2;
                } else {
                  $options[$flag_array[$i]{$j}][] = FALSE;
                  $i ++;
                }
                $plus_i = 0;
                break;
             } else {
                $options[$flag_array[$i]{$j}][] = substr ( $flag_array[$i], $j + 1 );
                $i ++;
                $plus_i = 0;
                break;
             }

          } else if ( in_array ( $flag_array[$i]{$j}, $short_no_value ) ) {

             $options[$flag_array[$i]{$j}][] = FALSE;
             $plus_i = 1;
             continue;

          } else {
             $plus_i = 1;
             break;
          }
        }

        $i += $plus_i;
        continue;

     }

     $i++;
     continue;
  }

  foreach ( $options as $key => $value ) {
     if ( count ( $value ) == 1 ) {
        $options[ $key ] = $value[0];

     }

  }

  return $options;

}

function split_para ( $pattern ) {

/* split_para() version 1.0      2008/08/19
   My page: http://www.ntu.beautifulworldco.com/weblog/?p=526

This function is to parse parameters and split them into smaller pieces.
preg_split() does similar thing but in our function, besides "space", we
also take the three symbols " (double quote), '(single quote),
and \ (backslash) into consideration because things in a pair of " or '
should be grouped together.

As an example, this parameter list

-f "test 2" -ab --required "t\"est 1" --optional="te'st 3" --option -v 'test 4'

will be splited into

-f
t"est 2
-ab
--required
test 1
--optional=te'st 3
--option
-v
test 4

see the code below,

$pattern = "-f \"test 2\" -ab --required \"t\\\"est 1\" --optional=\"te'st 3\" --option -v 'test 4'";

$result = split_para( $pattern );

echo "ORIGINAL PATTERN: $pattern\n\n";

var_dump( $result );

*/

  $begin=0;
  $backslash = 0;
  $quote = "";
  $quote_mark = array();
  $result = array();

  $pattern = trim ( $pattern );

  for ( $end = 0; $end < strlen ( $pattern ) ; ) {

     if ( ! in_array ( $pattern{$end}, array ( " ", "\"", "'", "\\" ) ) ) {
        $backslash = 0;
        $end ++;
        continue;
     }

     if ( $pattern{$end} == "\\" ) {
        $backslash++;
        $end ++;
        continue;
     } else if ( $pattern{$end} == "\"" ) {
        if ( $backslash % 2 == 1 || $quote == "'" ) {
          $backslash = 0;
          $end ++;
          continue;
        }

        if ( $quote == "" ) {
          $quote_mark[] = $end - $begin;
          $quote = "\"";
        } else if ( $quote == "\"" ) {
          $quote_mark[] = $end - $begin;
          $quote = "";
        }

        $backslash = 0;
        $end ++;
        continue;
     } else if ( $pattern{$end} == "'" ) {
        if ( $backslash % 2 == 1 || $quote == "\"" ) {
          $backslash = 0;
          $end ++;
          continue;
        }

        if ( $quote == "" ) {
          $quote_mark[] = $end - $begin;
          $quote = "'";
        } else if ( $quote == "'" ) {
          $quote_mark[] = $end - $begin;
          $quote = "";
        }

        $backslash = 0;
        $end ++;
        continue;
     } else if ( $pattern{$end} == " " ) {
        if ( $quote != "" ) {
          $backslash = 0;
          $end ++;
          continue;
        } else {
          $backslash = 0;
          $cand = substr( $pattern, $begin, $end-$begin );
          for ( $j = 0; $j < strlen ( $cand ); $j ++ ) {
             if ( in_array ( $j, $quote_mark ) )
                continue;

             $cand1 .= $cand{$j};
          }
          if ( $cand1 ) {
             eval( "\$cand1 = \"$cand1\";" );
             $result[] = $cand1;
          }
          $quote_mark = array();
          $cand1 = "";
          $end ++;
          $begin = $end;
          continue;
       }
     }
  }

  $cand = substr( $pattern, $begin, $end-$begin );
  for ( $j = 0; $j < strlen ( $cand ); $j ++ ) {
     if ( in_array ( $j, $quote_mark ) )
        continue;

     $cand1 .= $cand{$j};
  }

  eval( "\$cand1 = \"$cand1\";" );

  if ( $cand1 )
     $result[] = $cand1;

  return $result;
}
////////////////////////////////////////////////////////////////////////////////////

function debug_tostring(&$var){
	global $vars;
	global $debug_tostring_indent;
	global $debug_tostring_full_name;
	global $debug_tostring_output_txt;
	$tbr="";
	if(is_array($var)){
		//$tbr.="<div style='border:1px dotted black;margin-left:10px;'>";
	}
	//call to debug_tostring()=<br>
	if(is_array($var) && !$debug_tostring_output_txt){
		$tbr.="<table border=1 cellspacing=0 cellpadding=0><tr><td valign=top>";
	}
	$var_name=variable_name($var);
	if(!$var_name){
		$var_name="variable";
	}else{
		if((!$debug_tostring_output_txt) && (!(is_array($var)) && $debug_tostring_indent)){
			$tbr.="<b>"."$".$var_name."</b>";
		}
		$debug_tostring_full_name=$var_name;
	}
	if(is_bool($var)){
		if($var==TRUE){
			$var_str="TRUE";
		}else{
			$var_str="FALSE";
		}
		$tbr.="(boolean)=".$var_str;
	}elseif(is_float($var)){
		$tbr.="(float)=".$var;
	}elseif(is_int($var)){
		$tbr.="(integer)=".$var;
	}elseif(is_string($var)){
		
			$var=str_replace("INSERT INTO","<font color=green><b>INSERT</b></font> INTO",$var);
			$var=str_replace("DELETE FROM","<font color=green><b>DELETE</b></font> FROM",$var);
			$var=str_replace("UPDATE ","<font color=green><b>UPDATE</b></font> ",$var);
		$tbr.="(string)=\"".$var."\"";
	}elseif(is_array($var)){
		/*
		$tbr.="(array)={<br>";
		//".$var;
		$tmp_indent=$debug_tostring_indent;
		$debug_tostring_full_name_t=$debug_tostring_full_name;
		$debug_tostring_indent.="&nbsp;";
		foreach($var as $i=>$v){
			$debug_tostring_full_name=$debug_tostring_full_name_t."[".$i."]";
			$tbr.="<font style='font-size:7pt;'>"."$"."$debug_tostring_full_name</font>";
			$tbr.="".debug_tostring($var[$i]);
			
		}
		$debug_tostring_full_name=$debug_tostring_full_name_t;
		$debug_tostring_indent=$tmp_indent;
		//$tbr.="}<br>";
		*/
		if(!$debug_tostring_output_txt){
			$tbr.="</td><td valign=top>";
		}
		//		$tbr.="(array)={<br>";
		//".$var;
		$tmp_indent=$debug_tostring_indent;
		$debug_tostring_full_name_t=$debug_tostring_full_name;
		$debug_tostring_indent.="&nbsp;";
		$first=true;
		foreach($var as $i=>$v){
			if($first){
				$debug_tostring_full_name="";
				$debug_tostring_full_name.=$debug_tostring_full_name_t;
				if(!$debug_tostring_output_txt){
					$debug_tostring_full_name.="</td><td valign=top>";
				}
				$debug_tostring_full_name.="[".$i."]";
				$first=false;
			}else{
				$debug_tostring_full_name="[".$i."]";
			}
			if(!is_array($v)){
				$tbr.="$debug_tostring_full_name";
			}
			$value=debug_tostring($var[$i]);
			$full_var_name=$debug_tostring_full_name_t."[".$i."]";
			//$value="<table width=100% border=1 cellpadding=0 cellspacing=0 style='display:inline;margin:7px; border: 1px solid red;'><tr><td>$value</td><td align=right>$full_var_name</td></tr></table>";
			$tbr.="".$value;
			//<font style='font-size:7pt;'>
		}
		$debug_tostring_full_name=$debug_tostring_full_name_t;
		$debug_tostring_indent=$tmp_indent;
		//$tbr.="}<br>";
		
		
		//
		
		
	}elseif(is_int($var)){
		$tbr.="(int)=".$var;
	}elseif(is_null($var)){
		$tbr.="(null)=".$var;
	}elseif(is_resource($var)){
		$tbr.="(resource)=".$var;
	}elseif(is_scalar($var)){
		$tbr.="(scalar)=".$var;
	}elseif(is_object($var)){
		$tbr.="(object)=?"; // =".$var;
	}elseif(is_numeric($var)){
		$tbr.="(numeric)=".$var;
	}else{
		if(!$debug_tostring_output_txt){
			$tbr.="<b><font color=red>(unknown_type)</font></b>=".$var;
		}else{
			$tbr.="(unknown_type)=".$var;
		}
	}
	if(is_array($var)){
	//	$tbr.="</div>";
	}else{
		if(!$debug_tostring_output_txt){
			$tbr.="<br>";
		}else{
			//$tbr.="\n";
		}
	}
	if(is_array($var) && (!$debug_tostring_output_txt) ){
		$tbr.="</td></tr></table>";
	}
	/*
(
get_class() - Returns the name of the class of an object
function_exists() - Return TRUE if the given function has been defined
method_exists() - C
	
	function unserialize2array($data) { 
    $obj = unserialize($data); 
    if(is_array($obj)) return $obj; 
    $arr = array(); 
    foreach($obj as $k=>$v) { 
        $arr[$k] = $v; 
    } 
    unset($arr['__PHP_Incomplete_Class_Name']); 
    return $arr; 
} 
	
	
	
	*/
	return $tbr;
}	
function variable_name( &$var, $scope=false, $prefix='UNIQUE', $suffix='VARIABLE' ){
    if($scope) {
        $vals = $scope;
    } else {
        $vals = $GLOBALS;
    }
    $old = $var;
    $var = $new = $prefix.rand().$suffix;
    $vname = FALSE;
    foreach($vals as $key => $val) {
        if($val === $new) $vname = $key;
    }
    $var = $old;
    return $vname;
}





//$colors = new Colors();
//echo $colors->getColoredString("Testing Colors class, this is blue string on light grey background.", "blue", "light_grey") . "\n";
	 
function test_all_shell_colors(){
	/*
	print "\n\nForground Codes: ";
	for($p1=0;$p1<=11;$p1++){
		for($p2=0;$p2<100;$p2++){
			$foreground_color="$p1;$p2";
			$background_color="black";
			print "". getColoredString($foreground_color, $foreground_color, $background_color)."  ";
		}
		print "\n--------------";
	}
	print "\n\n";
	*/
	print "\n\nBackground Codes: ";
	for($p1=0;$p1<=110;$p1++){
		$background_color="$p1";
		$foreground_color="white";
		print "". getColoredString($background_color, $foreground_color, $background_color)."  ";
	}
	print "\n\n";
}

function getColoredString($string, $foreground_color = null, $background_color = null) {
	global $vars;
	
	$vars[shell_foreground_colors] = array();
	$vars[shell_background_colors] = array();
	 
			
	
	$vars[shell_foreground_colors]['blink'] = '0;5';
	
	
	$vars[shell_foreground_colors]['white'] = '1;37';
	$vars[shell_foreground_colors]['grey'] = '0;2';
	$vars[shell_foreground_colors]['lightest_grey'] = '1;37';
	$vars[shell_foreground_colors]['light_grey'] = '9;37';
	$vars[shell_foreground_colors]['dark_grey'] = '1;30';
	$vars[shell_foreground_colors]['black'] = '0;30';
	
	$vars[shell_foreground_colors]['blink_red'] = '5;91';
	$vars[shell_foreground_colors]['red'] = '0;31';
	$vars[shell_foreground_colors]['pink'] = '1;31';
	$vars[shell_foreground_colors]['light_red'] = '1;31';
	$vars[shell_foreground_colors]['dark_red'] = '2;91';
	
	$vars[shell_foreground_colors]['blink_green'] = '5;92';
	$vars[shell_foreground_colors]['green'] = '0;92';
	$vars[shell_foreground_colors]['bold_green'] = '1;92';
	$vars[shell_foreground_colors]['dark_green'] = '2;92';
	
	$vars[shell_foreground_colors]['blink_yellow'] = '5;93';
	$vars[shell_foreground_colors]['brown'] = '10;33';
	$vars[shell_foreground_colors]['yellow'] = '0;93';
	$vars[shell_foreground_colors]['bold_yellow'] = '1;93';
	
	$vars[shell_foreground_colors]['dark_blue'] = '0;34';
	$vars[shell_foreground_colors]['blue'] = '1;34';
	$vars[shell_foreground_colors]['light_blue'] = '1;94';
	
	$vars[shell_foreground_colors]['purple'] = '0;35';
	$vars[shell_foreground_colors]['light_purple'] = '1;35';
	
	$vars[shell_foreground_colors]['dark_cyan'] = '0;36';
	$vars[shell_foreground_colors]['cyan'] = '1;36';
	
	 
	 
	 
	$vars[shell_background_colors]['white'] = '107';
	$vars[shell_background_colors]['grey'] = '47';
	$vars[shell_background_colors]['black'] = '40';
	$vars[shell_background_colors]['dark_red'] = '41';
	$vars[shell_background_colors]['dark_green'] = '42';
	$vars[shell_background_colors]['dark_yellow'] = '43';
	$vars[shell_background_colors]['dark_blue'] = '44';
	$vars[shell_background_colors]['dark_magenta'] = '45';
	$vars[shell_background_colors]['dark_cyan'] = '46';
	
	$vars[shell_background_colors]['red'] = '101';
	$vars[shell_background_colors]['green'] = '102';
	$vars[shell_background_colors]['yellow'] = '103';
	$vars[shell_background_colors]['blue'] = '104';
	$vars[shell_background_colors]['magenta'] = '105';
	$vars[shell_background_colors]['cyan'] = '106';
		
	
	$colored_string = "";
	$colored_string .= "\033[0m";
	if( intval($foreground_color)<=0 && isset($vars[shell_foreground_colors][$foreground_color])) {
		$colored_string .= "\033[" . $vars[shell_foreground_colors][$foreground_color] . "m";
	}elseif( intval($foreground_color)<=0 ) {
		$colored_string .= "\033[" . $vars[shell_foreground_colors]['red'] . "m";
		$colored_string .= " Unknown Shell Foreground Color: ($foreground_color) ";
	}else{
		$colored_string .= "\033[" . $foreground_color . "m";
	}
	if( intval($background_color)<=0 && isset($vars[shell_background_colors][$background_color])) {
		$colored_string .= "\033[" . $vars[shell_background_colors][$background_color] . "m";
	}elseif( intval($background_color)<=0 ) {
		$colored_string .= "\033[" . $vars[shell_foreground_colors]['red'] . "m";
		$colored_string .= " Unknown Shell Bckground Color: ($background_color) ";
	}else{
		$colored_string .= "\033[" . $background_color . "m";
	}
	$colored_string .=  $string;
	if($vars[shell_colors_reset_foreground]!=""){
		$colored_string .= "\033[0m";
		$colored_string .= "\033[".$vars[shell_foreground_colors][$vars[shell_colors_reset_foreground]]."m";
		if($vars[shell_colors_reset_background]!=""){
			$colored_string .= "\033[".$vars[shell_background_colors][$vars[shell_colors_reset_background]]."m";
		}else{
			$colored_string .= "\033[".$vars[shell_background_colors]['black']."m";
		}
	}else{
		$colored_string .= "\033[0m";
	}
	return $colored_string;
}
 
		// Returns all foreground color names
function getForegroundColors() {
	global $vars;
	return array_keys($vars[shell_foreground_colors]);
}
 
		// Returns all background color names
function getBackgroundColors() {
	global $vars;
	return array_keys($vars[shell_background_colors]);
}
	
 
 
 


function dse_proc_io_get($Reset=FALSE){
	global $vars,$procIOs;
	//print "dse_proc_io_get_start_time=$vars[dse_proc_io_get_start_time] \n";
	//print "sizeof($procIOs)=".sizeof($procIOs)." \n";
	
	$ps=`sudo ps aux`;
	$time=time();
	if(sizeof($procIOs)==0) $Reset=TRUE;
	$vars[dse_proc_io_get_last_time]=$time;		
	if($Reset){
		$start_time=$time;
		$vars[dse_proc_io_get_start_time]=$start_time;
		$procIOs=array($time);
		//$ps="7487";
		foreach(split("\n", $ps) as $pse){
			//print "$pse \n";
			$pse=str_replace("  "," ",$pse);
			$pse=str_replace("  "," ",$pse);
			$pse=str_replace("  "," ",$pse);
			$psea=split(" ",$pse);
			//print_r($psea);
			if($psea[1]){
				$PIDs[]=$psea[1];
				$procIOs[$time][$psea[1]]=dse_get_proc_io_as_array($psea[1]);
				$w=dse_get_proc_io_as_array($psea[1]);
			//	print "procIOs[$time][$psea[1]]['wchar']=".$w['wchar']."; \n";
				//print "PID:$psea[1]\n";
			//	print debug_tostring($procIOs[$time][$psea[1]])."\n";
				
				
			}
		}
	}else{
		$start_time=$vars[dse_proc_io_get_start_time];
		//sleep(3);
		$ps=`sudo ps aux`;
		$time=time();
		$time_diff=$time-$start_time;
		//print "timediff=$time_diff\n";
		$procIOs[$time]=array();
		$wt=0; $rt=0;
		//$ps="7487";
		foreach(split("\n", $ps) as $pse){
			//print "PID: $pse \n";
			$pse=str_replace("  "," ",$pse);
			$pse=str_replace("  "," ",$pse);
			$pse=str_replace("  "," ",$pse);
			$psea=split(" ",$pse);
			//print_r($psea);
			if($psea[1]){ 
				$PID=$psea[1];
				$PIDs[]=$PID;

				$procIOs[$time][$PID]=dse_get_proc_io_as_array($PID);
			//	print "dw=procIOs[$time][$PID]['wchar']-procIOs[$start_time][$PID]['wchar']\n";
			//	print "dw=".$procIOs[$time][$PID]['wchar']."-".$procIOs[$start_time][$PID]['wchar']."\n";
				//print "start_time=$start_time\n";
				//print_r ($procIOs[$time][$PID]); 
				//print_r ($procIOs[$start_time][$PID]); 
				$dw=$procIOs[$time][$PID]['wchar']-$procIOs[$start_time][$PID]['wchar'];
				$dr=$procIOs[$time][$PID]['rchar']-$procIOs[$start_time][$PID]['rchar'];
				$dwb=$procIOs[$time][$PID]['read_bytes']-$procIOs[$start_time][$PID]['read_bytes'];
				$drb=$procIOs[$time][$PID]['write_bytes']-$procIOs[$start_time][$PID]['write_bytes'];
				$wt+=$dw;
				$rt+=$dr;
				$wbt+=$dwb;
				$rbt+=$drb;
				$wps=intval($dw/$time_diff);
				$rps=intval($dr/$time_diff);
				$wbps=intval($dwb/$time_diff);
				$rbps=intval($drb/$time_diff);
				$wtps=intval($wt/$time_diff);
				$rtps=intval($rt/$time_diff);
				$wbtps=intval($wbt/$time_diff);
				$rbtps=intval($rbt/$time_diff);
				
				if($wps>0){
					$exe=`echo $PID | /dse/bin/pid2exe 2>/dev/null`;
					//print "EXE: $exe PID:$psea[1] dt=$time_diff  	 dW=$dw ($wps/s)    dR=$dr ($rps/s)   dWb=$dwb ($wbps/s)    dRb=$drb ($rbps/s)  \n";
					//print debug_tostring($procIOs[$time][$PID)."\n";
				
				}
				$procIOs[$time]['TOTAL']['wchar']=$wt;
				$procIOs[$time]['TOTAL']['rchar']=$rt;
				$procIOs[$time]['TOTAL']['read_bytes']=$rbt;
				$procIOs[$time]['TOTAL']['write_bytes']=$wbt;
				//print "\n";
			}
		}
		//print "Totals:  w:$wt ($wtps/s)    r:$rt ($rtps/s)    dWb=$wbt ($wbtps/s)    dRb=$rbt ($rbtps/s) \n\n";
	
	}
	//return $procIOs;
}



function dse_proc_io(){
	global $vars,$procIOs;
	$ps=`sudo ps aux`;
	$time=time();
	$start_time=$time;
	$procIOs=array($time);
	//$ps="7487";
	foreach(split("\n", $ps) as $pse){
		//print "$pse \n";
		$pse=str_replace("  "," ",$pse);
		$pse=str_replace("  "," ",$pse);
		$pse=str_replace("  "," ",$pse);
		$psea=split(" ",$pse);
		//print_r($psea);
		if($psea[1]){
			$PIDs[]=$psea[1];
			$procIOs[$time][$psea[1]]=dse_get_proc_io_as_array($psea[1]);
			$w=dse_get_proc_io_as_array($psea[1]);
			print "procIOs[$time][$psea[1]]['wchar']=".$w['wchar']."; \n";
			//print "PID:$psea[1]\n";
		//	print debug_tostring($procIOs[$time][$psea[1]])."\n";
			
			
		}
	}
	
	
	while(TRUE){
		sleep(3);
		$ps=`sudo ps aux`;
		$time=time();
		$time_diff=$time-$start_time;
		//print "timediff=$time_diff\n";
		$procIOs[$time]=array();
		$wt=0; $rt=0;
		//$ps="7487";
		foreach(split("\n", $ps) as $pse){
			//print "PID: $pse \n";
			$pse=str_replace("  "," ",$pse);
			$pse=str_replace("  "," ",$pse);
			$pse=str_replace("  "," ",$pse);
			$psea=split(" ",$pse);
			//print_r($psea);
			if($psea[1]){ 
				$PID=$psea[1];
				$PIDs[]=$PID;

				$procIOs[$time][$PID]=dse_get_proc_io_as_array($PID);
			//	print "dw=procIOs[$time][$PID]['wchar']-procIOs[$start_time][$PID]['wchar']\n";
			//	print "dw=".$procIOs[$time][$PID]['wchar']."-".$procIOs[$start_time][$PID]['wchar']."\n";
				//print "start_time=$start_time\n";
				//print_r ($procIOs[$time][$PID]); 
				//print_r ($procIOs[$start_time][$PID]); 
				$dw=$procIOs[$time][$PID]['wchar']-$procIOs[$start_time][$PID]['wchar'];
				$dr=$procIOs[$time][$PID]['rchar']-$procIOs[$start_time][$PID]['rchar'];
				$dwb=$procIOs[$time][$PID]['read_bytes']-$procIOs[$start_time][$PID]['read_bytes'];
				$drb=$procIOs[$time][$PID]['write_bytes']-$procIOs[$start_time][$PID]['write_bytes'];
				$wt+=$dw;
				$rt+=$dr;
				$wbt+=$dwb;
				$rbt+=$drb;
				$wps=intval($dw/$time_diff);
				$rps=intval($dr/$time_diff);
				$wbps=intval($dwb/$time_diff);
				$rbps=intval($drb/$time_diff);
				$wtps=intval($wt/$time_diff);
				$rtps=intval($rt/$time_diff);
				$wbtps=intval($wbt/$time_diff);
				$rbtps=intval($rbt/$time_diff);
				
				if($wps>0){
					$exe=`echo $PID | /dse/bin/pid2exe 2>/dev/null`;
					print "EXE: $exe PID:$psea[1] dt=$time_diff  	 dW=$dw ($wps/s)    dR=$dr ($rps/s)   dWb=$dwb ($wbps/s)    dRb=$drb ($rbps/s)  \n";
					//print debug_tostring($procIOs[$time][$PID)."\n";
				
				}
				//print "\n";
			}
		}
		print "Totals:  w:$wt ($wtps/s)    r:$rt ($rtps/s)    dWb=$wbt ($wbtps/s)    dRb=$rbt ($rbtps/s) \n\n";
	
	}
	
}


function dse_get_proc_io_as_array($PID){
	global $vars;
	$tbr=array();
	$o=`cat /proc/$PID/io 2>/dev/null`;
	foreach(split("\n", $o) as $oe){
		$oep=split(" ",$oe);
		if($oep[0]=="rchar:"){
			$tbr['rchar']=$oep[1];
		}elseif($oep[0]=="wchar:"){
			$tbr['wchar']=$oep[1];
		}elseif($oep[0]=="syscr:"){
			$tbr['syscr']=$oep[1];
		}elseif($oep[0]=="syscw:"){
			$tbr['syscw']=$oep[1];
		}elseif($oep[0]=="read_bytes:"){
			$tbr['read_bytes']=$oep[1];
		}elseif($oep[0]=="write_bytes:"){
			$tbr['write_bytes']=$oep[1];
		}
			
	}
	return $tbr;
}

	 

?>
