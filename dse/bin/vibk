#!/usr/bin/php
<?
ini_set('display_errors','On');	
error_reporting(E_ALL & ~E_NOTICE);

$backup_dir="/backup/changed_files";
$log_file="/var/log/vibk.log";

$Script=$argv[0];
$Script=trim(`which $Script`);
print "Script: $Script\n";
$file=$argv[1];
if($file==basename($file)){
	$file=trim(`pwd`)."/".$file;	
}
$TIME_NOW=time();
$DATE_TIME_NOW=trim(`date +"%y%m%d%H%M%S"`);
$dir=dirname("$backup_dir$file.$DATE_TIME_NOW");
`mkdir -p $dir`;
`cp $file $backup_dir$file.$DATE_TIME_NOW`;
`echo "$TIME_NOW cp $file $backup_dir$file.$DATE_TIME_NOW" >> $log_file`;
print "backing up to: $backup_dir$file.$DATE_TIME_NOW\n";
//system("vi $file");
pcntl_exec("/usr/bin/vim",array($file));
//exec("/usr/bin/vim",array($file));
//passtru("/usr/bin/vim",array($file));

print "$file saved. backup at $backup_dir$file.$DATE_TIME_NOW\n";
exit();

$RunTime=60;
$Start=time();;
$Verbosity=3;
$ReloadSeconds=5;
$MaxLoops=0;

$In="/dse/bin/stress_urls.txt";
$Log="/var/log/http_stress.log";
$ThreadLog="/tmp/http_stress.thread.log";


$Script=$argv[0];


$parameters = array(
  'h' => 'help',
  't:' => 'threads',
  'q' => 'quiet',
  's:' => 'reload-seconds:',
  'v:' => 'verbosity:',
  'r:' => 'runtime:',
  'm:' => 'maxloops:',
);
$flag_help_lines = array(
  'h' => "\thelp - this message",
  't:' => "\tthreads - # of threads to run simultaniously",
  'q' => "quiet - same as -v 0",
  's:' => "reload-seconds - seconds between screen refresh",
  'v:' => "\tverbosity - 0=none 1=some 2=more 3=debug",
  'r:' => "\truntime - run time in seconds",
  'm:' => "\tmaxloops - # refreshes before auto exit",
);


$Usage="   Devity Bottle Top Program - 'top' like system bottleneck monitor
       by Louy of Devity.com

command line usage: bottle_top (options)

";
foreach($parameters as $k=>$v){
	$k2=str_replace(":","",$k);
	$v2=str_replace(":","",$v);
	$Usage.=" -${k2}, --${v2}\t".$flag_help_lines[$k]."\n";
}


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
  		print $Usage;
		$DidSomething=TRUE;
		break;
	case 'q':
	case 'quiet':
		$Verbosity=0;
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
	case 'r':
		$RunTime=$options['r'];
		if($Verbosity>=2) print "RunTime set to $RunTime\n";
		break;
	case 'runtime':
		$RunTime=$options['runtime'];
		if($Verbosity>=2) print "RunTime set to $RunTime\n";
		break;

}


if($DidSomething){
	exit();
}
if($Verbosity>=2){
	print "Script: $Script\n";
}


$EndLoad=get_load();
  

$ActualRunTime=time()-$Start;
	
//$log_line="runstart=$Start:runlength=$RunTime:actualruntime=$ActualRunTime:loadstart=$StartLoad:loadend=$EndLoad:loads=$Loads:lps=$LoadsPerSecond:sizeavg=$AvgSizeRaw:sizetotal=$TotalSize:Mbps=$Mbps";
//print `echo $log_line >> $Log`;
//print `echo $log_line`;
global $diskstats_lasttime,$section_httpd;
	
$DoLoop=TRUE;
$Loops=0;
while($DoLoop && ($MaxLoops==0 || $Loops<$MaxLoops)){
	update_display();
	sleep($ReloadSeconds);
	$Loops++;
}


exit();






function update_display(){
	//global $c,$t,$tt,$st,$Key,$FoundKeys,$file_scan_last,$file_keys_found,$i1,$i2,$i3,$i4,$ScanContinue,$NoDiplayYet;
	global $vars,$Loops;
	global $diskstats_lasttime,$section_httpd;
	
	//first update data before clear so no slow print / flickering
		//get mysql process info
		$section_mysql_processes="";
		$sql_query="SHOW FULL PROCESSLIST";
		$mysql_processes_raw=`sudo echo "$sql_query" | mysql -u localroot`;
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

		$section_mysql_stats.="Queries:$Queries  Qps:$Qps_str  Slow:$Slow_queries %$Slow_percent  LastCost:$Last_query_cost \n";
		$section_mysql_stats.="Updates:$Handler_update  Delete:$Handler_delete  Write:$Handler_write\n";
		$section_mysql_stats.="Innodb bppf:$Innodb_buffer_pool_pages_free \n";
		$section_mysql_stats.="Qcache free_blocks:$Qcache_free_blocks  total_blocks:$Qcache_total_blocks free_memory:${Qcache_free_memory_str}MB\n";
		$section_mysql_stats.="Open: Files:$Open_files  Tables:$Open_tables  \n";
		$section_mysql_stats.="Key_blocks_unused:$Key_blocks_unused   Key_blocks_used:$Key_blocks_used   \n";
		$section_mysql_stats.="Select_range:$Select_range   Select_scan:$Select_scan   Sort_scan:$Sort_scan  \n";
	//	$section_mysql_stats.=" \n";
	//	$section_mysql_stats.=" \n";
		$LastQueries=$Queries;
		$LastRunTime=time();
		
			//"Created_tmp_disk_tables", "Created_tmp_tables", 
			//"", "", "Key_buffer_fraction_%", 
		//	"", "", 
			//"Table_locks_immediate", "Table_locks_waited", 
		
	//exit();
	
		$section_memory="";
		$section_cpu="";
		$unit_size=1024*1024;
		$o=`vmstat -S M 1 2`;
		$oa=split("\n",$o);
		$o=$oa[3];
//		print "o=$o\n";    
		$o=str_replace("  ", " ", $o);
		$o=str_replace("  ", " ", $o);
		$o=str_replace("  ", " ", $o);
		$o=str_replace("  ", " ", $o);
		$oa=split(" ",$o);
		//print debug_tostring($oa );
		$MFree=$oa[4];
		$MTotal=4096;
		$MUsed=$MTotal-$MFree;
		$MFreePercent=number_format(($MFree/$MTotal)*100,2);
		$MUsedPercent=number_format(($MUsed/$MTotal)*100,2);
		
		
		$MUsedPercent_str=dse_bt_colorize($MUsedPercent,60);

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
			$load_1=number_format($loadaggA[0],3);
			$load_2=number_format($loadaggA[1],3);
			$load_3=number_format($loadaggA[2],3);
			$load_4=number_format($loadaggA[3],3);
			$load_5=number_format($loadaggA[4],3);
		}
		
		
		$load_1_str=dse_bt_colorize($load_1,1);

		$section_cpu.= "$load_1_str:$load_2:$load_3 $load_4/$load_5  CPU %:   Sys:$CpuSys_str%   User:$CpuSys_str%   Idle:$CpuIdle_str% ";
		$section_memory.= "Mem Used Percent:$MUsedPercent_str%   Used: ${MUsed}MB   Free:${MFree}MB ";
		
		
		$section_processes="";
		$section_processes.=`ps auxf | sort -nr -k 4 | head -25`;		
	
	/*
		$Start
		$DateStr=date("d/M/Y:H:i",$Start);
		$DateStr=substr($DateStr,0,strlen($DateStr)-1);
		print "grep DateStr=$DateStr\n";
		print "grep \"$DateStr\" $LogFileName > $TmpFileName\n";
		`grep $DateStr $LogFileName > $TmpFileName`;
		*/
	if(($Loops%5)==0){
		$LogFileName="/home/httpd/batteriesdirect.com/stats/batteriesdirect.com-custom_log";
		$TmpFileName="/tmp/".rand(1111111,99999999);
	
		
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
		$section_httpd.="HTTPD Requests/Minute:$PRpm_str   Avg: ${$AvgGenTime_str}s  Span:${SpanSeconds}s";
	}
//	exit();


	if(($Loops%5)==0){
		
		
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
			$sda1_percent=dse_bt_colorize($sda1_percent,10);
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
			$sda2_percent=dse_bt_colorize($sda2_percent,10);
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
	

	print getColoredString(trim(`hostname`),'light_blue')."        ".trim(`date`)."         ";	
	print $section_cpu;
	print "\n";
	print $section_memory;
	print "\n";print "\n";
	
	print $section_disk;
	print "\n";
	
	print $section_httpd;
	print "\n";print "\n";
	
		
	
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


function dse_bt_colorize($v,$t,$type="MAXIMUM"){
	global $vars;
	if($type=="MAXIMUM"){
		if($v>$t){
			return getColoredString($v, 'pink', 'black');
		}else{
			return getColoredString($v, 'green', 'black');
		}
	}elseif($type=="MINIMUM"){
		if($v<$t){
			return getColoredString($v, 'pink', 'black');
		}else{
			return getColoredString($v, 'green', 'black');
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
//echo $colors->getColoredString("Testing Colors class, this is blue string on light gray background.", "blue", "light_gray") . "\n";
	 

function getColoredString($string, $foreground_color = null, $background_color = null) {
	global $vars;
	
	$vars[shell_foreground_colors] = array();
	$vars[shell_background_colors] = array();
	 
			
	$vars[shell_foreground_colors]['black'] = '0;30';
	$vars[shell_foreground_colors]['dark_gray'] = '1;30';
	$vars[shell_foreground_colors]['blue'] = '0;34';
	$vars[shell_foreground_colors]['light_blue'] = '1;34';
	$vars[shell_foreground_colors]['green'] = '0;32';
	$vars[shell_foreground_colors]['light_green'] = '1;32';
	$vars[shell_foreground_colors]['cyan'] = '0;36';
	$vars[shell_foreground_colors]['light_cyan'] = '1;36';
	$vars[shell_foreground_colors]['red'] = '0;31';
	$vars[shell_foreground_colors]['light_red'] = '1;31';
	$vars[shell_foreground_colors]['pink'] = '1;31';
	$vars[shell_foreground_colors]['purple'] = '0;35';
	$vars[shell_foreground_colors]['light_purple'] = '1;35';
	$vars[shell_foreground_colors]['brown'] = '0;33';
	$vars[shell_foreground_colors]['yellow'] = '1;33';
	$vars[shell_foreground_colors]['light_gray'] = '0;37';
	$vars[shell_foreground_colors]['white'] = '1;37';
	 
	$vars[shell_background_colors]['black'] = '40';
	$vars[shell_background_colors]['red'] = '41';
	$vars[shell_background_colors]['green'] = '42';
	$vars[shell_background_colors]['yellow'] = '43';
	$vars[shell_background_colors]['blue'] = '44';
	$vars[shell_background_colors]['magenta'] = '45';
	$vars[shell_background_colors]['cyan'] = '46';
	$vars[shell_background_colors]['light_gray'] = '47';
	$vars[shell_background_colors]['white'] = '47';
		
	
	//print "so.bg=".sizeof($vars[shell_background_colors])."\n";
	$colored_string = "";
	if (isset($vars[shell_foreground_colors][$foreground_color])) {
	//	print " [fg=$foreground_color] ";
		$colored_string .= "\033[" . $vars[shell_foreground_colors][$foreground_color] . "m";
	}
	if (isset($vars[shell_background_colors][$background_color])) {
		//print " [bg=$background_color] ";
		$colored_string .= "\033[" . $vars[shell_background_colors][$background_color] . "m";
	}
	$colored_string .=  $string . "\033[0m";
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
	
 

?>
