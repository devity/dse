<?
function dse_sysstats_sdvcqwev(){
	global $vars; dse_trace();
	
	return array($mysql_processes_array,$mysql_processes_raw,$mysql_processes_line_array,$mysql_processes);
}	
	


function dse_color_ls($FileArg){
	global $vars; dse_trace();
	global $CFG_array;
	$W=cbp_get_screen_width()-1;
	
	if($W>100){
		$ShowFullPermissions=TRUE;
	}
	
	$NameWidth=30;
	if($W>100){
		$NameWidth=30;
		$ShowPath=TRUE;
	}
	
	
	
	$TypeWidth=4;
	$SizeWidth=8;
	$BlockSizeWidth=8;
	$OwnerWidth=16;
	if($ShowFullPermissions){
		$PermissionsWidth=16;
	}else{
		$PermissionsWidth=5;
	}
	$TimesWidth=8;
	$NeededWidth=$TypeWidth+$SizeWidth+$BlockSizeWidth+$OwnerWidth+$PermissionsWidth+$TimesWidth*3+(3*8);
	if($NeededWidth+$NameWidth<$W){
		$NameWidth=$W-($NeededWidth+2);
	}
	dpv(4,"w=$W nw=$NeededWidth NameWidth=$NameWidth");
	$Seperator=colorize(" | ","blue","black",TRUE,1);
	
	
	$Dir=dirname(($FileArg."/asdf"));
	$t=time();
	$vars['s2t_abvr']=TRUE;
	
	
		
	
	print bar("ls of $FileArg ","-","yellow","black","blue","black");
	print color_pad("File Name","yellow","black",$NameWidth,"right");
	print $Seperator;
	print color_pad("Type","yellow","black",$TypeWidth,"right");
	print $Seperator;
	print color_pad("Size","yellow","black",$SizeWidth,"right");
	print $Seperator;
	print color_pad("Block Size","yellow","black",$BlockSizeWidth,"right");
	print $Seperator;
	print color_pad("Owner","yellow","black",$OwnerWidth,"center");
	print $Seperator;
	print color_pad("Permissions","yellow","black",$PermissionsWidth,"right");
	print $Seperator;
	print color_pad("Modified","yellow","black",$TimesWidth,"right");
	print $Seperator;
	print color_pad("Accessed","yellow","black",$TimesWidth,"right");
	print $Seperator;
	print color_pad("Created","yellow","black",$TimesWidth,"right");
	print "\n";
	print bar("","-","cyan","black","blue","black");
	$ls_Array=dse_ls($FileArg);
	foreach ($ls_Array as $DiskName => $lsEntry){
		list($Type,$FileName)=$lsEntry;
		$FullFileName=$Dir."/".$FileName;
		if($FileName=="."){
			$FullFileName=substr($FullFileName,0,strlen($FullFileName)-2);
		}
		if($FileName==".."){
			$FullFileName=substr($FullFileName,0,strlen($FullFileName)-2);
		}
		$sa=dse_file_get_stat_array($FullFileName);
		//print_r($sa);
		$uid=$sa['uid'];
		$gid=$sa['gid'];
		$Size=$sa['size'];
		$aTime=$sa['atime'];
		$mTime=$sa['mtime'];
		$cTime=$sa['ctime'];
		$BlockSize=$sa['blksize']*$sa['blocks'];
		$Owner_str=dse_gid_name($gid).":".dse_uid_name($uid);
		dpv(5,"getting sizes");
		if($Type=="DIR"){
			if($vars['dse_dfm_do_dir_sizes']){
				if($FileName=="." || $FileName==".."){
					$Size_str="-";
					$BlockSize_str="-";
				}else{
					$Size_str=trim(dse_exec("/dse/bin/dsizeof -r \"$FullFileName\"",$vars['Verbosity']>4));
					$BlockSize_str=trim(dse_exec("/dse/bin/dsizeof -br \"$FullFileName\"",$vars['Verbosity']>4));
				}
			}else{
				$Size_str="-";
				$BlockSize_str="-";
			}
		}else{
			$Size_str=dse_file_size_to_readable($Size);
			$BlockSize_str="$sa[11]*$sa[12]";
			$BlockSize_str=dse_file_size_to_readable($BlockSize);
		}
		$asa=dse_file_get_alt_stat_array($FullFileName);
		//print_r($asa);
		if($ShowFullPermissions){
			$Permissions_str=$asa['perms']['human'] . "  " . $asa['perms']['octal2'];
		}else{
			$Permissions_str=$asa['perms']['octal2'];
		}
		$mTime_str=seconds_to_text($t-$mTime);
		$aTime_str=seconds_to_text($t-$aTime);
		$cTime_str=seconds_to_text($t-$cTime);
	//	print "cTime=$cTime\n";
		if($ShowPath){
			$sl=strlen("$Dir/$FileName");
			$sl3=strlen("/Users/louis/Desktop/Shared/work/dse/dse_git/dse/aliases/empty");
			$NameOverRun=$sl-$NameWidth;
			dpv(5,"NameOverRun=$NameOverRun  sl=$sl  sl2=$sl3  =strlen(\"$Dir/$FileName\")-$NameWidth;");
		
			if($NameOverRun>=0){
				$Dir_str=str_tail($Dir,$NameWidth-(strlen($FileName)+1)-3);
				$FileName_str=colorize("...","white","black").colorize($Dir_str,"red","black")."/".colorize($FileName,"yellow","black");	
			}else{
				$FileName_str=colorize($Dir,"red","black")."/".colorize($FileName,"yellow","black");
				$FileName_str.=colorize(pad("",-1*$NameOverRun,"-"),"blue","black");
			}
			
			
		}else{
			$FileName_str=colorize($FileName,"cyan","black");
			$FileName_str.=colorize(pad(" ",$NameWidth-strlen($FileName),"-"),"blue","black");
		}
		if(dse_file_is_link($FullFileName)){
			$D=dse_file_link_get_destination($FullFileName);
			$Type="LINK";
		}
		print $FileName_str;
		print $Seperator;
		if($Type!="DIR" && str_contains($asa['perms']['human'],"x")){
			print color_pad($Type,"black","green",$TypeWidth,"right");
		}else{
			print color_pad($Type,"cyan","black",$TypeWidth,"right");
		}
		print $Seperator;
		print color_pad($Size_str,"green","black",$SizeWidth,"right");
		print $Seperator;
		print color_pad($BlockSize_str,"red","black",$BlockSizeWidth,"right");
		print $Seperator;
		print color_pad($Owner_str,"magenta","black",$OwnerWidth,"right");
		print $Seperator;
		print color_pad($Permissions_str,"yellow","black",$PermissionsWidth,"right");
		print $Seperator;
		print color_pad($mTime_str,"red","black",$TimesWidth,"right");
		print $Seperator;
		print color_pad($aTime_str,"green","black",$TimesWidth,"right");
		print $Seperator;
		print color_pad($cTime_str,"green","black",$TimesWidth,"right");
		
				
		print "\n";
	}
	print bar("","-","cyan","black","blue","black");
}
	

function dse_print_df(){
	global $vars; dse_trace();
	global $CFG_array;
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
			if($DiskInfoArray['Total']==0){
				print color_pad("virtual","blue","black",$FreeWidth,"right");
			}else{
				$f_str=dse_file_size_to_readable($f);
				print color_pad($f_str,"cyan","black",$FreeWidth,"right");
			}
			print $Seperator;
			$f_str=dse_file_size_to_readable($DiskInfoArray['Free']);
			if($f<1000000){
				if($DiskInfoArray['Total']==0){
					print color_pad("virtual","blue","black",$FreeWidth,"right");
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
	global $vars; dse_trace();
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
	global $vars; dse_trace();
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
	global $vars; dse_trace();
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
	global $vars; dse_trace();
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
	global $vars; dse_trace();
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
	global $vars; dse_trace();
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
	global $vars; dse_trace();
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
	global $vars; dse_trace();

	$mysql_processes="";
	$sql_query="SHOW FULL PROCESSLIST";
	$Command="echo \"$sql_query\" | mysql -u ".$vars['DSE']['MYSQL_USER']." | grep -v PROCESSLIST | grep -v Sleep";
	$mysql_processes_raw=dse_exec($Command);
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
	global $vars; dse_trace();

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
	$Command="echo \"$sql_query\" | mysql -u ".$vars['DSE']['MYSQL_USER'];
	$mysql_status_raw=dse_exec($Command);
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
	global $vars; dse_trace();
	global $procIOs;
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


function dse_sysstats_httpd_fullstatus(){
	global $vars;
		
	
	global $whitelist_ips_array;
	global $ips_attack_active_array; 	
	global $ips_attack_recent_array;	
	global $ips_attack_ever_array;
	global $ips_banned_array;
	global $HighlightIP;
	//if(sizeof($whitelist_ips_array)==0) dam_ip_throttle_load_ip_lists();
	
//	if(!$vars['dpd_httpd_fullstatus__embeded'])	start_feature_box("dpd_httpd_fullstatus()","100%");

	//`/dse/aliases/dse_httpd_fullstatus_on`;
	
	if(dse_which("apachectl")){
		$Command="apachectl fullstatus";
	}elseif(dse_which("/etc/init.d/httpd")){
		$Command="/etc/init.d/httpd fullstatus";
	}
	$Results=dse_exec($Command);
	if(!$vars['dpd_httpd_fullstatus__embeded'])	print "<b>Command:</b> <i>".$Command ."</i><br><br>";
	$Lines=split("\n",$Results);
	$num_lines=sizeof($Lines);
	
	$RequestsSection=FALSE;
	for( $l=15;$l<$num_lines;$l++){
		$Line=trim($Lines[$l]);
		
		$i++;  
		//print "<hr>$l: $Line";
		//$pt=split("    ",$Line);
		//print "$pr[1]<br>";
		
		if(!(strstr($Line,"-----------------")===FALSE)){
			$RequestsSection=FALSE;
			$EndSection=TRUE;
		}
		if($RequestsSection){
			$Requests[]=$Line;	
		}
		
		$lt=trim($Line);
		if(!(strstr($Line,"Total accesses:")===FALSE)){
			$Accesses=strcut($Line,"Total accesses: "," ");
		}
		if(!(strstr($Line,"Total Traffic:")===FALSE)){
			$TotalTraffic=strcut($Line,"Total Traffic: "," ");
		}
		if(!(strstr($Line,"Server uptime: ")===FALSE)){
			$UptimeStr=strcut($Line,"Server uptime: ");
		}
		if(!(strstr($Line,"CPU Usage: ")===FALSE)){
			$CPU=strcut($Line,"CPU Usage: "," CLU Load");
		}
		if(!(strstr($Line,"requests/sec")===FALSE)){
			$rps=strcut($Line,""," requests/sec");
		}
		if(!(strstr($Line,"requests currentl")===FALSE)){
			$Processing=strcut($Line,""," requests currentl");
		} 
		if(!(strstr($Line,".....")===FALSE)){
			if(!$Workers){
				$Workers=$Line;
			}
		}
		if(!(strstr($Line,"VHost")===FALSE)){
			$RequestsSection=TRUE;
		}
		if(!(strstr($Line,"al retrieves since starting: ")===FALSE)){
			$HitMiss=strcut($Line,"etrieves since starting: "," requests currentl");
		}
		
		
		$Lpa=split(" ",$Line);
		if($Lpa[0]=="Srv") break;
	}
	
	for( $l=$l;$l<$num_lines;$l++){
		$Line=trim($Lines[$l]);
		$Lpa=split(" ",$Line);
		$PID=$Lpa[1];
		if(intval($PID)>0){
		
			$LastLine=str_replace("  ", " ", $LastLine);
			$Lpa=split(" ",$LastLine);
			if($Lpa[14]!="NULL"){
				$URL=$Lpa[13]."/".$Lpa[15];
				$PID=$Lpa[1];
				$Mode=$Lpa[5];
				$CPU=$Lpa[6];
				$SS=$Lpa[7];
				$Req=$Lpa[8];
				$Con=$Lpa[9];
				//print_r($Lpa);
				print "PID: $PID  cpu:$CPU s   ss:$SS s  m:$Mode req:$Req ms   con:$Con kb   $URL \n";
			}
			$LastLine="";
		}
		$LastLine.=" ".$Line;
	}
	
	$lc=sizeof($Requests);
	$tl=$lc;
	for($l=0;$l<$lc;$l++){
		$lt=$Requests[$l];
		$n=trim(strcut($lt,"","-"));
		if(intval($n)==0&&$n!="0"){
			$Requests[$l-1].=$Requests[$l];
			$Requests[$l]="";
			$tl--;
		}
	//	print "l=$l n=$n <br>";
	}
	
	// /prefork.c
	/*$httpd_conf_file="/etc/httpd/conf/httpd.conf";
	$wmsraw=`cat $httpd_conf_file`;
	if(dpd_httpd_is_prefork_or_worker()=="worker"){
		$wmsa=strcut($wmsraw,"<IfModule worker.c>","</IfModule>");
	}elseif(dpd_httpd_is_prefork_or_worker()=="prefork"){
		$wmsa=strcut($wmsraw,"<IfModule prefork.c>","</IfModule>");
	}else{
		$wmsa="";
	}
	*/
	if($wmsa==""){
		$MaxClients="unknown";
	}else{
		foreach(split("\n",$wmsa) as $s){
			$p=split("[ ]+",$s);
			if($p[0]=="MaxClients"){
				$MaxClients=$p[1];
			}
		}
	}
	
	
	
	//if(!$vars['dpd_httpd_fullstatus__embeded'])	print "<hr>";
	if($Accesses) print "Accesses: $Accesses   ";
	print "Up:$UptimeStr   ";
	if($rps) print "rps:$rps    ";
	print "Processing:$Processing    ";
	print "$HitMiss  &nbsp;&nbsp; ";
	if($CPU) print "CPU: $CPU   ";
	if($TotalTraffic) print " TotalTraffic: $TotalTraffic  ";
	print "Workers: $Workers  &nbsp;&nbsp; ";
	if($Requests) {
		print "Clients: $tl/$MaxClients   ";
	}else{
		print "Mac Clients: $MaxClients   ";
	}
	//print debug_tostring($Requests);
//	if(!$vars['dpd_httpd_fullstatus__embeded'])	print "<hr>";
	if($Requests){
		//print "<table><tr class='f10pt'><td>Request</td><td>Client</td><td>SS</td><td>CPU</td><td>PID</td><td>M</td></tr>";
		$n=0;
		 foreach($Requests as $l){
		
			if($l){
				$n++;
				$lpa=split("[ ]+",$l);
				$Srv=$lpa[0];
				$PID=$lpa[1];
				$Acc=$lpa[2];
				$M=$lpa[3];
				$CPU=$lpa[4];
				$SS=$lpa[5];
				$Req=$lpa[6];
				$Conn=$lpa[7];
				$Child=$lpa[8];
				$Slot=$lpa[9];
				$Client=$lpa[10];
				$VHost=$lpa[11];
				$RequestMethod=$lpa[12];
				$Request=$lpa[13];
				     
				//print "S=$Srv M=$M CPU=$CPU Cl=$Client VH=$VHost RM=$RequestMethod  R=$Request <br>";
				if((stristr($VHost,"development")===FALSE)){
					$VHost="www.".$VHost;
				}                                                                
				        
				$BanURL="/dse_admin/utils/debug.php?PageType=IPTablesAddBannedIP&IPToBan=$Client";
				//$IpInfoLink=bd_dam_get_ip_link($Client);
				$IpInfoLink=$Client;
				print "<tr class='f7pt'><td class='f10pt'>$n: <a href=https://$VHost$Request target=_blank>$Request</a></td>
				<td>
							$IpInfoLink
					 &nbsp;<a href=$BanURL target=_blank><font color=red><b>Ban</b></font></a>
					 	</td>
				<td>$SS</td><td>$CPU - $Acc - $Srv</td><td>$PID</td>
				<td>$M</td></tr>";
				
			}
		}
		
		//print "</table>";
		}
	//if(!$vars['dpd_httpd_fullstatus__embeded'])	end_feature_box();
	//print "<br>";	
}
		

?>