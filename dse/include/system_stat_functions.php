<?
function dse_sysstats_sdvcqwev(){
	global $vars;
	
	return array($mysql_processes_array,$mysql_processes_raw,$mysql_processes_line_array,$mysql_processes);
}	
	
	
function dse_sysstats_net_listening(){
	global $vars;
	if(FALSE && dse_which("lsof")){
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
	}elseif(dse_which("netstat")){
		$str="";
		$Command="sudo netstat -na";
		$raw=`$Command`;
		$raw=strcut($raw,"\n","Active UNIX domain sockets");
		$raw_array=split("\n",$raw);
		$tbr_array=array();
		foreach($raw_array as $line){
			if($line){
				//print "l=$line\n";
				
				$lpa=split("[ ]+",$line);
				if(str_contains($lpa[3],"::")){
					$lpa[3]=strcut($lpa[3],"::",":");
				}
				
				$exe="";
				$port=strcut($lpa[3],":");
				if($port){
					$lpa[9]=$port;
					
					$port_already_added=FALSE;
					foreach($tbr_array as $ea){
						if($ea[9]==$port) $port_already_added=TRUE;
					}
					if(!$port_already_added){
						$str.= "$exe:$port ";
						$tbr_array[]=$lpa;
					}
				}
			}
		}
		return array($tbr_array,$raw,$raw_array,$str);
	}
	return NULL;
}	
	
function dse_sysstats_connected($Port){
	global $vars;
	if(FALSE && dse_which("lsof")){
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
	}elseif(dse_which("netstat")){
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
				if(str_contains($lpa[4],"::")){
					$lpa[4]=substr($lpa[4],2);
					$lpa[4]=strcut($lpa[4],":",":");
				}
				$lpa[4]=strcut($lpa[4],"",":");
				$ip=$lpa[4];
				
				if($lpa[5]!="LISTEN"){
					$ip_already_added=FALSE;
					foreach($tbr_array as $ea){
						if($ea[4]==$ip) $ip_already_added=TRUE;
					}
					if(!$ip_already_added){
						$str.= "$ip ";
						$tbr_array[]=$lpa;
					}
				}
			}
		}
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



?>