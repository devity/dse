<?
function dse_sysstats_sdvcqwev(){
	global $vars;
	
	return array($mysql_processes_array,$mysql_processes_raw,$mysql_processes_line_array,$mysql_processes);
}	
	

function dse_print_df(){
	global $vars,$CFG_array;
	$W=cbp_get_screen_width();
	//if($W>100){
		$Seperator=" | ";
		$Wn=15;
		$Wl=$W-3*$Wn-4*strlen($Seperator);
		$Seperator=colorize($Seperator,"blue","black");
		$NameWidth=intval($Wl*(4/7));
		$FileSystemWidth=intval($Wl*(3/7));
		$FreeWidth=$Wn; $TotalWidth=$Wn; $FreeWidth=$Wn;
	//}
	
	
	
	print bar("Disk Usage: ","-","yellow","black","blue","black");
	print color_pad("Mount","yellow","black",$NameWidth,"right");
	print $Seperator;
	print color_pad("Percent Free","yellow","black",$FreeWidth,"right");
	print $Seperator;
	print color_pad("Total","yellow","black",$TotalWidth,"right	");
	print $Seperator;
	print color_pad("Free","yellow","black",$FreeWidth,"right");
	print $Seperator;
	print color_pad("File System","yellow","black",45,"left");
	print "\n";
	print bar("","-","cyan","black","blue","black");
	
	list($disks_array,$disks_detailed_array)=dse_sysstats_disks();
	foreach ($disks_detailed_array as $DiskName => $DiskInfoArray){
		print color_pad($DiskName,"cyan","black",$NameWidth,"right");
		print $Seperator;
		if($DiskInfoArray['PercentFree']<10){
			if($DiskInfoArray['Total']==0){
				print color_pad("virtual","blue","black",$FreeWidth,"right");
			}else{
				print color_pad($DiskInfoArray['PercentFree']." % free","red","black",$FreeWidth,"right");
			}
		}else{
			print color_pad($DiskInfoArray['PercentFree']." % free","green","black",$FreeWidth,"right");
		}
		print $Seperator;
		
		$f=$DiskInfoArray['Total'];
		if($f!="0" && $f=="remote"){
			$f_str=$f;
			print color_pad($f_str,"blue","black",$TotalWidth,"right	");
			print $Seperator;
			print color_pad($f_str,"blue","black",$FreeWidth,"right");
		}else{
			$f_str=dse_file_size_to_readable($f);
			print color_pad($f_str,"cyan","black",$TotalWidth,"right");
			
			print $Seperator;
			$f_str=dse_file_size_to_readable($DiskInfoArray['Free']);
			if($f<1000000){
				if($DiskInfoArray['Total']==0){
					print color_pad($f_str,"green","black",$FreeWidth,"right");
				}else{
					print color_pad($f_str,"red","black",$FreeWidth,"right");
				}
			}else{
				print color_pad($f_str,"green","black",$FreeWidth,"right");
			}
		}
		print $Seperator;
		print color_pad($DiskInfoArray['FileSystem'],"cyan","black",$FileSystemWidth,"left");
		print "\n";
	}
	print bar("","-","cyan","black","blue","black");
}
	
function dse_sysstats_power(){
	global $vars;
	$VarsToReturn="BatteryPercent,BatteryPercentStr,BatteryMaxCapacity,BatteryCurrentCapacity,BatteryVoltage,BatteryVoltageStr,BatteryCellVoltages,BatteryCycleCount"
		.",BatteryTemperature,BatteryIsCharging,BatteryFullyCharged,BatteryVoltageStr,BatteryAmperageStr,BatteryTemperatureStr"
		.",KeyboardBatteryPercentStr,KeyboardBatteryPercent,MouseBatteryPercentStr,MouseBatteryPercent,TrackpadBatteryPercentStr,TrackpadBatteryPercent"; 
	foreach(split(",",$VarsToReturn) as $v) global $$v;
	
	if(dse_is_osx()){
		$ioregl=`ioreg -l`; 
		$SystemBatteryRaw=strcut($ioregl,"<class AppleSmartBattery,","<class ");
//	print $SystemBatteryRaw;
		$BatteryCapacity=trim(strcut($SystemBatteryRaw,"MaxCapacity\" = ","\n"));
		$BatteryCurrentCapacity=trim(strcut($SystemBatteryRaw,"CurrentCapacity\" = ","\n"));
		$BatteryVoltage=trim(strcut($SystemBatteryRaw,"oltage\"=",","));
		$BatteryCellVoltages=trim(strcut($SystemBatteryRaw,"Voltage\" = ","\n"));
		$BatteryCycleCount=trim(strcut($SystemBatteryRaw,"CycleCount\" = ","\n"));
		$BatteryTemperature=trim(strcut($SystemBatteryRaw,"Temperature\" = ","\n"));
		$BatteryIsCharging=trim(strcut($SystemBatteryRaw,"IsCharging\" = ","\n"));
		$BatteryFullyCharged=trim(strcut($SystemBatteryRaw,"FullyCharged\" = ","\n"));
		$BatteryPercent=intval(100*($BatteryCurrentCapacity/$BatteryCapacity));	
		if($BatteryPercent<30) $BatteryPercentColor="red";
			elseif($BatteryPercent<70) $BatteryPercentColor="yellow";
			else $BatteryPercentColor="green";
		$BatteryPercentStr=colorize($BatteryPercent,$BatteryPercentColor)."% left";
		$BatteryVoltageStr=number_format($BatteryVoltage/1000,2)."v";
		$BatteryAmperageStr=number_format($BatteryCurrentCapacity/1000,2)."Ah";
		$BatteryTemperatureStr=number_format($BatteryTemperature/64,2)." deg C";
		$BatteryTemperature=number_format($BatteryTemperature/64 ,2);
		
$MouseBatteryRaw=strcut($ioregl,"<class BNBMouseDevice,","<class ");
		$MouseBatteryPercent=trim(strcut($MouseBatteryRaw,"BatteryPercent\" = ","\n"));
		if($MouseBatteryPercent<14) $MouseBatteryPercentColor="red";
			elseif($MouseBatteryPercent<50) $MouseBatteryPercentColor="yellow";
			else $MouseBatteryPercentColor="green";
		$MouseBatteryPercentStr=colorize($MouseBatteryPercent,$MouseBatteryPercentColor)."% left";
		
		$KeyboardBatteryRaw=strcut($ioregl,"<class AppleBluetoothHIDKeyboard,","<class ");
		$KeyboardBatteryPercent=trim(strcut($KeyboardBatteryRaw,"BatteryPercent\" = ","\n"));
		if($KeyboardBatteryPercent<14) $KeyboardBatteryPercentColor="red";
			elseif($KeyboardBatteryPercent<50) $KeyboardBatteryPercentColor="yellow";
			else $KeyboardBatteryPercentColor="green";
		$KeyboardBatteryPercentStr=colorize($KeyboardBatteryPercent,$KeyboardBatteryPercentColor)."% left";
		
		$TrackpadBatteryRaw=strcut($ioregl,"<class BNBTrackpadDevice,","<class ");
		$TrackpadBatteryPercent=trim(strcut($TrackpadBatteryRaw,"BatteryPercent\" = ","\n"));
		if($TrackpadBatteryPercent<14) $TrackpadBatteryPercentColor="red";
			elseif($TrackpadBatteryPercent<50) $TrackpadBatteryPercentColor="yellow";
			else $TrackpadBatteryPercentColor="green";
		$TrackpadBatteryPercentStr=colorize($TrackpadBatteryPercent,$TrackpadBatteryPercentColor)."% left";
		
	}
	return dse_make_array_of_vars($VarsToReturn);
}	
	
	
function dse_make_array_of_vars($var_stringCsvList_or_array){
	global $vars;
	if(!is_array($var_stringCsvList_or_array)){
		$var_stringCsvList_or_array=split(",",$var_stringCsvList_or_array);
	}
	foreach ($var_stringCsvList_or_array as $V){
		global $$V;
		$tbr[$V]=$$V;
	}
	return $tbr;
}	
	
	
function dse_sysstats_net_listening(){
	global $vars;
	if(dse_is_osx() && dse_which("lsof")){
		$str="";
		$Command="sudo lsof -iTCP -sTCP:LISTEN -P -n";
		$raw=`$Command`;
		$raw=strcut($raw,"\n");
		$raw_array=split("\n",$raw);
		$tbr_array=array();
		foreach($raw_array as $line){
			if($line){
				//print "l=$line\n";
				$lpa=split("[ ]+",$line);
				$exe=$lpa[0];
				$port=$lpa[8];$port=strcut(str_replace("::","",$port),":");
				$lpa[9]=$port;
				
				$port_already_added=FALSE;
				foreach($tbr_array as $ea){
					//print "if($ea[9]==$port)<br>";
					if($ea[9]==$port) $port_already_added=TRUE;
				}
				if(!$port_already_added){
					$str.= "$exe:$port ";
					$tbr_array[]=$lpa;
						$ports_array[]=$port;
				}
			}
		}
	//print "ports_array=";	print_r($ports_array);
		return array($tbr_array,$raw,$raw_array,$str,$ports_array);
	}elseif(dse_which("netstat")){
		$str="";
		if(file_exists("/scripts/netstat-tulpn")){
			$Command="/scripts/netstat-tulpn";
		}else{
			$Command="netstat -tulpn";
		}
		$raw=`$Command`;
	//	$raw=strcut($raw,"\n","Active UNIX domain sockets");
		$raw=strcut($raw,"\n","");
		$raw_array=split("\n",$raw);
		$tbr_array=array();
		foreach($raw_array as $line){
			if($line){
				//print "l=$line\n";
				
				$lpa=split("[ ]+",$line);
				$port=$lpa[3];$port=strcut(str_replace("::","",$port),":");
				
				$portNexe=$lpa[6];
				$portNexepa=split("/",$portNexe);
				$pid=$portNexepa[0];
				$exe=$portNexepa[1];
				if($port){
					$lpa[9]=$port;
					
					$port_already_added=FALSE;
					foreach($tbr_array as $ea){
						if($ea[9]==$port) $port_already_added=TRUE;
					}
					if(!$port_already_added){
						$str.= "$exe:$port ";
						$tbr_array[]=$lpa;
						$ports_array[]=$port;
					}
				}
			}
		}
		return array($tbr_array,$raw,$raw_array,$str,$ports_array);
	}
	return array(NULL,NULL,NULL,"no netstat found");
}	
	
function dse_sysstats_connected($Port){
	global $vars;
	/*if(FALSE && dse_which("lsof")){
		$str="";
		$Command="sudo lsof -iTCP -sTCP:LISTEN -P -n";
		$raw=`$Command`;
		$raw=strcut($raw,"\n");
		$raw_array=split("\n",$raw);
		$tbr_array=array();
		foreach($raw_array as $line){
			if($line){
				//print "l=$line\n";
				$lpa=split("[ ]+",$line);
				$exe=$lpa[0];
				$port=$lpa[8];$port=strcut(str_replace("::","",$port),":");
				$lpa[9]=$port;
				
				$port_already_added=FALSE;
				foreach($tbr_array as $ea){
					//print "if($ea[9]==$port)<br>";
					if($ea[9]==$port) $port_already_added=TRUE;
				}
				if(!$port_already_added){
					$str.= "$exe:$port ";
					$tbr_array[]=$lpa;
				}
			}
		}
		return array($tbr_array,$raw,$raw_array,$str);
	}else*/if(dse_which("netstat")){
		$str="";
		$Command="sudo netstat -n";
		$raw=`$Command`;
		$raw=strcut($raw,"\n","Active UNIX domain sockets");
		$raw_array=split("\n",$raw);
		$tbr_array=array();
		foreach($raw_array as $line){
			if($line){
				//print "l=$line\n";
				
				$lpa=split("[ ]+",$line);
				
				if(str_contains($lpa[3],"::")){
					$lpa[3]=substr($lpa[3],2);
					$lpa[3]=strcut($lpa[3],":");
				}
				$local_ipNport=$lpa[3];
				$local_ip=strcut($local_ipNport,"",":");
				$local_port=strcut($local_ipNport,":");
				$lpa[3]=array($local_ip,$local_port);
				if(str_contains($lpa[4],"::")){
					$lpa[4]=substr($lpa[4],2);
					$lpa[4]=strcut($lpa[4],":");
				}
				$foreign_ipNport=$lpa[4];
				$foreign_ip=strcut($foreign_ipNport,"",":");
				$foreign_port=strcut($foreign_ipNport,":");
				$lpa[4]=array($foreign_ip,$foreign_port);
				
				//print "local_ipNport=$local_ipNport foreign_ipNport=$foreign_ipNport $local_port==$Port lpa5=$lpa[5] l=$line\n";
				if($local_port==$Port && $lpa[5]!="LISTEN"){
					//print " (adding? $foreign_ip) ";
					$ip_already_added=FALSE;
					foreach($tbr_array as $ea){
						if($ea[4][0]==$foreign_ip) $ip_already_added=TRUE;
					}
					if(!$ip_already_added){
						//print " (unique $foreign_ip) ";
						$str.= "$foreign_ip ";
						$tbr_array[]=$lpa;
					}
					//print " (1str=$str) ";
						
				}
					//print " (2str=$str) ";
			}
		}
		
					//print " (3str=$str) ";
		return array($tbr_array,$raw,$raw_array,$str);
	}
	return NULL;
}	
function dse_sysstats_proc_interrupts(){
	global $vars;
	$section_procinterrupts="";
	$raw=`cat /proc/interrupts`;
	$time=time();
	$procInterrupts=array($time);
	foreach(split("\n", $raw) as $line){
	
	}
	//yoGNw2BA9ef
	$wt=$procInterrupts[$vars[dse_proc_interrupts_get_last_time]]['TOTAL']['wchar'];
	//$dt=$vars[dse_proc_interrupts_get_last_time]-$vars[dse_proc_interrupts_get_start_time];
	//$wtps=intval($wt/$dt);

	$section_procio= "/proc/interrupts: \n";
	return array($mysql_processes_array,$mysql_processes_raw,$mysql_processes_line_array,$section_procinterrupts);
}	
	
				
					
function dse_sysstats_proc_io(){
	global $vars;
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
	return array($procIOs,$section_procio);
}	
			
function dse_sysstats_files_open(){
	global $vars;
	$section_files_open="";
	$lsof_raw=`sudo lsof`;
	$lsof_a=split("\n",$lsof_raw);
	$open_files=sizeof($lsof_a);
	$open_files_str=dse_bt_colorize($open_files,4000);
	$section_files_open.="lsof open files: $open_files_str   ";
	/*global $lsof_last;
	 if($lsof_last){
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
		
	}
	 $lsof_last=$lsof;*/
	return array($open_files,$lsof_raw,$section_files_open);
}

function dse_sysstats_mysql_processlist(){
	global $vars;

	$mysql_processes="";
	$sql_query="SHOW FULL PROCESSLIST";
	$mysql_processes_raw=`echo "$sql_query" | mysql -u localroot | grep -v PROCESSLIST | grep -v Sleep`;
	$mysql_processes_line_array=split("\n",$mysql_processes_raw);
	$mysql_processes_array=array();
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
			$tsa[8]=$Command;				
							
			$mysql_processes_array[]=$tsa;
			$mysql_processes.= "$User $DB $State $Command $Info\n";
		}
	}
	
	return array($mysql_processes_array,$mysql_processes_raw,$mysql_processes_line_array,$mysql_processes);
}


function dse_sysstats_mysql_status(){
	global $vars;

	$mysql_status_array=array();
	
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
		"Threads_cached","Threads_connected","Threads_created",
		);
		
//cnf
//"thread_cache_size",


	//foreach($MysqlStatusVars as $var_name){
	//	$$var_name="";
	//}
	$section_mysql_stats="";
	$sql_query="SHOW STATUS ";
	$mysql_status_raw=`echo "$sql_query" | mysql -u localroot`;
	$mysql_status_line_array=split("\n",$mysql_status_raw);
	foreach($mysql_status_line_array as $k=>$mysql_status_line){
		$tsa=split("\t",$mysql_status_line);
		foreach($MysqlStatusVars as $var_name){
			if($tsa[0]==$var_name){
				//$$var_name=$tsa[1];
				$mysql_status_array[$var_name]=$tsa[1];
			}
		}
	}
	if($mysql_status_array[Queries]){
		$mysql_status_array[Slow_percent]=number_format(($mysql_status_array[Slow_queries]/$mysql_status_array[Queries])*10,2);
	}else{
		$mysql_status_array[Slow_percent]=0;
	}

	$mysql_status_array[Qps]=($mysql_status_array[Queries]-$vars[dse_sysstats_mysql_status__last_queries])/(time()-$vars[dse_sysstats_mysql_status__last_run_time]);
	$mysql_status_array[Qps_str]=number_format($mysql_status_array[Qps],2);
	$mysql_status_array[Qps_str]=dse_bt_colorize($mysql_status_array[Qps_str],100);
	
	//$Qcache_free_blocks_str=dse_bt_colorize($Qcache_free_blocks,10,"MINIMUM");
	//$Qcache_total_blocks_str=dse_bt_colorize($Qcache_total_blocks,20000);
	$mysql_status_array[Qcache_free_memory_str]=dse_bt_colorize(number_format($Qcache_free_memory/(1024*1024),1),150001000/(1024*1024),"MINIMUM");

	$section_mysql_stats.="Qps:$mysql_status_array[Qps_str]  Slow:$mysql_status_array[Slow_queries] %$mysql_status_array[Slow_percent] ";// LastCost:$Last_query_cost \n";
	$section_mysql_stats.="Updates: $Handler_update  Delete: $Handler_delete  Write: $Handler_write\n";
//	$section_mysql_stats.="Innodb bppf:$Innodb_buffer_pool_pages_free \n";
	$section_mysql_stats.="Qcache free_blocks:$mysql_status_array[Qcache_free_blocks]  total_blocks:$mysql_status_array[Qcache_total_blocks] free_memory:$mysql_status_array[Qcache_free_memory_str]MB\n";
	$section_mysql_stats.="Open: Files: $mysql_status_array[Open_files]  Tables: $mysql_status_array[Open_tables]  \n";
	$section_mysql_stats.="Threads: connected: $mysql_status_array[Threads_connected]  created: $mysql_status_array[Threads_created]    ";
	$section_mysql_stats.="Cached: $mysql_status_array[Threads_cached] \n";
	//$section_mysql_stats.="Key_blocks_unused:$Key_blocks_unused   Key_blocks_used:$Key_blocks_used   \n";
	//$section_mysql_stats.="Select_range:$Select_range   Select_scan:$Select_scan   Sort_scan:$Sort_scan  \n";

	$vars[dse_sysstats_mysql_status__last_queries]=$mysql_status_array[Queries];
	$vars[dse_sysstats_mysql_status__last_run_time]=time();
	return array($mysql_status_array,$mysql_status_raw,$mysql_status_line_array,$section_mysql_stats);
}



function dse_proc_io(){
	global $vars,$procIOs;
	$ps=`ps aux`;
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
		//	print "procIOs[$time][$psea[1]]['wchar']=".$w['wchar']."; \n";
			//print "PID:$psea[1]\n";
		//	print debug_tostring($procIOs[$time][$psea[1]])."\n";
			
			
		}
	}
	
	
	while(TRUE){
		sleep(3);
		$ps=`ps aux`;
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

	
function dse_sysstats_disks(){
	global $vars;
	$disks_array=array();
	$disks_detailed_array=array();
	$DiskUse="";
	$df=`df -k | grep -v Mounted`;
	foreach (split("\n",$df) as $line){
		if(trim($line)){
			$line=str_replace("map ","map->",$line);
			$line=preg_replace("/[ ]+/"," ",$line);
			$fields=split(" ",$line);	
			if($fields[5]){
				//print_r($fields);
				if($fields[1]==1048576000){
					$Total="remote";
				}else{
					$Total=$fields[1]*1024;
				}
				$fields[4]=100-str_replace("%","",$fields[4]);	
				$disks_array[$fields[5]]=$fields[4];	
				$FileSystem=trim($fields[0]);
				$disks_detailed_array[$fields[5]]=array("Name"=>$fields[5],"PercentFree"=>$fields[4],"FileSystem"=>$FileSystem,"Total"=>$Total,
					"Free"=>$fields[3]*1024,"Used"=>$fields[2]*1024);
			}
		}
	}
	return array($disks_array,$disks_detailed_array);
}	



?>