<?php


function dse_database_stats($Show="ALL"){
	global $vars; dse_trace();

	$Command="SHOW STATUS;";
	$r=dse_exec("echo \"$Command\" | mysql -u ".$vars['DSE']['MYSQL_USER'],FALSE,FALSE);
	
	$ShowMAIN=array(
		"Uptime",
		"Queries", 
		"Questions",
		"Threads_running",
		"Connections",
		"Table_locks_immediate", "Table_locks_waited",
		"Key_blocks_unused", "Key_blocks_used", "Key_read_requests", "Key_reads", "Key_write_requests", "Key_writes",
		"Max_used_connections", 
		"Not_flushed_delayed_rows",
		"Open_files", "Open_table_definitions", "Open_tables", "Opened_files",
		"Qcache_free_blocks", "Qcache_free_memory", "Qcache_hits", "Qcache_inserts", "Qcache_not_cached", "Qcache_queries_in_cache",
			"Qcache_total_blocks", 
		
		
		);
	
	$rla=explode("\n",$r);
	foreach($rla as $l){
		$lpa=explode("\t",$l);
		//print "0=$lpa[0] 1=$lpa[1]\n";
		if($Show=="ALL"){
			print "$lpa[0]: $lpa[1]\n";	
		}elseif($Show="MAIN"){
			if(	in_array($lpa[0], $ShowMAIN) ){
				print "$lpa[0]: $lpa[1]\n";			
			}
		}
	}

}


function dse_database_service_name(){
	global $vars; dse_trace();
	return "mysql";
}


		
function dse_database_make_hotlive_copy_of_database($db=""){
	global $vars; dse_trace();
	if(!$db){
		$dbs=dse_database_list_array();
		foreach ($dbs as $db){
			if($db && !str_contains($db,"_HLBackup")){
				dse_database_make_hotlive_copy_of_database($db);
			}
		}
		return;
	}
	$db_hotlive=$db.'_HLBackup';
	
	
	print "Doing hotlivebackup of database $db to database $dh_hotlive:\n";
	
	//delete old hotlive database
	$DrowCommand="DROP DATABASE IF EXISTS $dh_hotlive";
	$r=dse_exec("echo \"$DrowCommand;\" | mysql -u ".$vars['DSE']['MYSQL_USER'],TRUE,TRUE);
	print "r=$r\n";
	
	//do hotlive copy
	$db_path='/var/lib/mysql/'.$db.'/';
	$backup_path='/var/lib/mysql/'.$db_hotlive.'/';

	$cmd="mkdir -p $backup_path";
	$r=dse_exec($cmd,TRUE,TRUE);
	print "r=$r\n";
	
	/*
	$cmd="cp -rf  $db_path/* $backup_path/.";
	$r=dse_exec($cmd,TRUE,TRUE);
	print "r=$r\n";
	*/
	$cmd="mysqlhotcopy --addtodest --user=".$vars['DSE']['MYSQL_USER']." $db $backup_path";
	$r=dse_exec($cmd,TRUE,TRUE);
	print "r=$r\n";
	
	$cmd="service ".dse_database_service_name()." restart";
	$r=dse_exec($cmd,TRUE,TRUE);
	print "r=$r\n";
	
	$cmd="mv -f  $backup_path$db/* $backup_path/.";
	$r=dse_exec($cmd,TRUE,TRUE);
	print "r=$r\n";
	$cmd="rm -rf  $backup_path$db";
	$r=dse_exec($cmd,TRUE,TRUE);
	print "r=$r\n";
	
	$cmd="chown -R mysql:mysql $backup_path";
	$r=dse_exec($cmd,TRUE,TRUE);
	print "r=$r\n";
	$cmd="chmod -R 755 $backup_path";
	$r=dse_exec($cmd,TRUE,TRUE);
	print "r=$r\n";
}


function dse_database_find_string_occurances($query,$db,$table){
	global $vars; dse_trace();
	if($db && $db!="*"){
		$dbs=array($db);
	}else{
		$dbs=dse_database_list_array();
	}
	foreach($dbs as $this_db){
		if($this_db && $this_db!="information_schema"){
			$tables=dse_table_list_array($this_db);
			foreach($tables as $this_table){
				if($this_table && $this_table!="FilePublishes"){
					$columns=dse_column_list_array($this_db,$this_table);
					foreach($columns as $this_column){
						if($this_column){
							$r=dse_exec("echo \"USE $this_db;\n SELECT * FROM $this_table WHERE $this_column LIKE \\\"%$query%\\\";\" | mysql -u ".$vars['DSE']['MYSQL_USER']);
							//$r=strcut($r,"\n");
							if(strlen($r)>0){
								print colorize($this_db,"blue");
								print colorize(":","yellow");
								print colorize($this_table,"cyan");
								print colorize(":","yellow");
								print colorize($this_column,"red");
								print colorize(":\n","yellow");
								$ra=split("\n",$r);
								foreach($ra as $rl){
									if($rl){
										$rl=substr($rl,0,cbp_get_screen_width()-1);	
										$rl=str_replace($query,colorize($query,"green"),$rl);
										$rl=str_replace("\t",colorize("|","blue"),$rl);
										print "$rl\n";
									}
								}
								print "\n";
							}
						//$tbr=split("\n",$r);
						}
					}
				}
			}
		}
	}
}


function dse_database_compare($db1,$db2){
	global $vars; dse_trace();
	$Same=TRUE;
	$db1_tables=dse_table_list_array($db1);
	$db2_tables=dse_table_list_array($db2);
	$db1_tc=sizeof($db1_tables);
	$db2_tc=sizeof($db2_tables);
	print "DB1: $db1   Tables: $db1_tc\n";
	print "DB2: $db2   Tables: $db2_tc\n";
	
	foreach($db1_tables as $this_table){
		if($this_table){
			if(!in_array($this_table,$db2_tables)){
				print "$db2 has no table $this_table\n";
				$Same=FALSE;
			}else{
				$db1_columns=dse_column_list_array($db1,$this_table);
				$db2_columns=dse_column_list_array($db2,$this_table);
				foreach($db1_columns as $this_column){
					if($this_column){
						if(!in_array($this_column,$db2_columns)){
							print "$db2.$this_table has no column $this_column\n";
							$Same=FALSE;
						}
					}
				}
				foreach($db2_columns as $this_column){
					if($this_column){
						if(!in_array($this_column,$db1_columns)){
							print "$db1.$this_table has no column $this_column\n";
							$Same=FALSE;
						}
					}
				}
			}
		}
	}
	foreach($db2_tables as $this_table){
		if($this_table){
			if(!in_array($this_table,$db1_tables)){
				print "$db1 has no table $this_table\n";
				$Same=FALSE;
			}	
		}
	}
	if($Same){
		print colorize("Database Schemas Match!\n","green","black");
	}else{
		print colorize("Database Schemas DIFFER!\n","red","black");
	}
	
}

function dse_database_list_array(){
	global $vars; dse_trace();
	$r=dse_exec("echo \"SHOW DATABASES;\" | mysql -u ".$vars['DSE']['MYSQL_USER']);
	$r=strcut($r,"\n");
	$tbr=split("\n",$r);
	$tbr=remove_duplicate_array_lines($tbr);
	return $tbr;
}

function dse_table_list_array($Database){
	global $vars; dse_trace();
	$r=dse_exec("echo \"USE $Database;\n SHOW TABLES;\" | mysql -u ".$vars['DSE']['MYSQL_USER']);
	$r=strcut($r,"\n");
	$tbr=split("\n",$r);
	$tbr=remove_duplicate_array_lines($tbr);
	return $tbr;
}

function dse_column_list_array($Database,$Table){
	global $vars; dse_trace();
	$r=dse_exec("echo \"USE $Database;\n DESCRIBE $Table;\" | mysql -u ".$vars['DSE']['MYSQL_USER']);

	$t=split("\n",$r);
	for($i=1;$i<sizeof($t);$i++){
		$ValuesA=split("\t",$t[$i]);
		$tbr[]=$ValuesA[0];
	}
	//$r=strcut($r,"\n");
	//$tbr=split("\n",$r);
	//$tbr=remove_duplicate_array_lines($tbr);
	return $tbr;
}

function dse_table_status_array($Database,$Table){
	global $vars; dse_trace();
	$r=dse_exec("echo \"USE $Database;\n SHOW TABLE STATUS WHERE Name='$Table' ;\" | mysql -u ".$vars['DSE']['MYSQL_USER']);
	//$r=strcut($r,"\n");
	$tbr=split("\n",$r);
	$NamesA=split("\t",$tbr[0]);
	$ValuesA=split("\t",$tbr[1]);
	$tbra=array();
	foreach($NamesA as $i=>$N){
		$V=$ValuesA[$i];
		$tbra[$N]=$V;
	}
	//print_r($tbra);
	//print "\n";
	//$tbr=remove_duplicate_array_lines($tbr);
	return $tbra;
}



function dse_database_repair($Database="",$Table=""){
	global $vars; dse_trace();
	if($Database && $Table){
		dse_table_repair($Database,$Table);
	}elseif($Database){
		$Tables=dse_table_list_array($Database);
		foreach($Tables as $Table){
			dse_table_repair($Database,$Table);
		}
	}else{
		$Databases=dse_database_list_array($Database);
		foreach($Databases as $Database){
			$Tables=dse_table_list_array($Database);
			foreach($Tables as $Table){
				dse_table_repair($Database,$Table);
			}
		}	
	}
}

function dse_table_repair($Database,$Table){
	global $vars; dse_trace();
	print "Repairing: $Database.$Table\n";
	$pid=dse_exec_bg("echo \"USE $Database;\\n REPAIR TABLE $Table EXTENDED;\" | mysql -u ".$vars['DSE']['MYSQL_USER'],TRUE,TRUE);
	while(dse_pid_is_running($pid)){
		global $Tdc,$Tc;
		progress_bar("time",100," $Tdc/$Tc checked");
					
		sleep(1);
	}
			
	return;
	$TAa=dse_table_analyze($DB,$T);
	if($TAa['MsgText']=="Table is already up to date" || $TAa['MsgText']=="OK"){
	}else{
		$IsOK=FALSE;
		dse_exec("service mysql stop");
		$Command="mysqlcheck -r $Database $Table -u ".$vars['DSE']['MYSQL_USER'];
		$r=dse_exec($Command,TRUE,TRUE);
		dse_exec("service mysql start");
	}
						
	return;
}

function dse_table_optimize($Database,$Table){
	global $vars; dse_trace();
	$pid=dse_exec_bg("echo \"USE $Database;\n OPTIMIZE TABLE $Table;\" | mysql -u ".$vars['DSE']['MYSQL_USER'],FALSE,FALSE);
	while(dse_pid_is_running($pid)){
		global $Tdc,$Tc;
		progress_bar("time",100," $Tdc/$Tc checked");
		sleep(1);
	}
	return;
}


function dse_table_check($Database,$Table){
	global $vars; dse_trace();
	//print colorize("CHECK Table $T:\n","green","black");
	$pid=dse_exec_bg("echo \"USE $Database;\n CHECK TABLE $Table EXTENDED;\" | mysql -u ".$vars['DSE']['MYSQL_USER'],FALSE,FALSE);
	//print "pid=$pid\n";
	while(dse_pid_is_running($pid)){
		//print " pid=$pid running\n";
		global $Tdc,$Tc;
		progress_bar("time",100," $Tdc/$Tc checked");
		sleep(1);
	}
	$r=dse_exec_bg_results($pid);
	list($HeaderLine,$DataLine)=split("\n",$r);
	$DataLine=whitespace_minimize($DataLine);
	$DdT=strcut($DataLine,""," ");
	$DataLine=strcut($DataLine," ");
	$Op=strcut($DataLine,""," ");
	$DataLine=strcut($DataLine," ");
	$MsgType=strcut($DataLine,""," ");
	$DataLine=strcut($DataLine," ");
	$MsgText=$DataLine;
	return(array("DdT"=>$DdT,"Op"=>$Op,"MsgType"=>$MsgType,"MsgText"=>$MsgText));		
}

function dse_table_analyze($Database,$Table){
	global $vars; dse_trace();
	//print colorize("CHECK Table $T:\n","green","black");
	$pid=dse_exec_bg("echo \"USE $Database;\n ANALYZE TABLE $Table;\" | mysql -u ".$vars['DSE']['MYSQL_USER'],FALSE,FALSE);
	//print "A pid=$pid\n";
	while(dse_pid_is_running($pid)){
		//print " A pid=$pid running\n";
		global $Tdc,$Tc;
		progress_bar("time",100," $Tdc/$Tc checked");
		sleep(1);
	}
	$r=dse_exec_bg_results($pid);
	list($HeaderLine,$DataLine)=split("\n",$r);
	$DataLine=whitespace_minimize($DataLine);
	$DdT=strcut($DataLine,""," ");
	$DataLine=strcut($DataLine," ");
	$Op=strcut($DataLine,""," ");
	$DataLine=strcut($DataLine," ");
	$MsgType=strcut($DataLine,""," ");
	$DataLine=strcut($DataLine," ");
	$MsgText=$DataLine;
	return(array("DdT"=>$DdT,"Op"=>$Op,"MsgType"=>$MsgType,"MsgText"=>$MsgText));		
}


function dse_database_check($DB,$DoRepair=TRUE,$DoOptimize=TRUE){
	global $vars; dse_trace();
	global $Tdc,$Tc;
	$DBa=dse_database_list_array();
	$W=cbp_get_screen_width();
	$TableNameWidth=intval($W*(2/5));
	if($TableNameWidth<55){
		$TableNameWidth=55;
	}
	$Dc=0; $Tc=0;
	if($DB && $DB!="information_schema"){
		$Dc++;
		$Ta=dse_table_list_array($DB);
		foreach($Ta as $T){
			if($T){
				$Tc++;
			}
		}
	}

	$Ddc=0; $Tdc=0;
	if($DB && $DB!="information_schema"){
		$Ddc++;
		print bar("Checking Database $DB: ","v","white","blue","cyan","blue");
		$Ta=dse_table_list_array($DB);
		foreach($Ta as $T){
			if($T){
				$TablesChecked++;
				$Tdc++;
				progress_bar("time",100," $Tdc/$Tc checked");
				print colorize(" $DB","red","black",TRUE,1);
				print colorize(".","green","black");
				print colorize($T,"magenta","black",TRUE,1);
				
				$PadSize=$TableNameWidth-(strlen($DB)+strlen($T)+2);
				print pad("",$PadSize);
				
				$IsOK=TRUE;
				$ErrorMsg="";
			
				
				
				$TSa=dse_table_status_array($DB,$T);
				$Rows=$TSa['Rows'];
				$Engine=$TSa['Engine'];
				$Avg_row_length=$TSa['Avg_row_length'];
				$Size_int=$Avg_row_length*$Rows;
				
				$Engine=pad($Engine,10," ","center");
				$Engine=colorize($Engine,"yellow","black");
				
				$Rows=pad(intval($Rows/1000),6," ","right");
				if(     $TSa['Rows']>1500000){
					$Rows=colorize($Rows,"red","black",TRUE,1);
				}elseif($TSa['Rows']> 700000){
					$Rows=colorize($Rows,"yellow","black",TRUE,1);
				}elseif($TSa['Rows']> 100000){
					$Rows=colorize($Rows,"green","black",TRUE,1);
				}else{
					$Rows=colorize($Rows,"blue","black",TRUE,1);
				}
				
				$Size=pad(intval($Size_int/1000000),6," ","right");
				$Size=colorize($Size,"green","black",TRUE,1);
				if(     $Size_int>100000000){
					$Size=colorize($Size,"red","black",TRUE,1);
				}elseif($Size_int> 10000000){
					$Size=colorize($Size,"yellow","black",TRUE,1);
				}elseif($Size_int>  1000000){
					$Size=colorize($Size,"green","black",TRUE,1);
				}else{
					$Size=colorize($Size,"blue","black",TRUE,1);
				}
				
				
				if($TSa['Engine']!="CSV"){
					progress_bar("time",100," $Tdc/$Tc checked");
					$TCa=dse_table_check($DB,$T);
					if($TCa['MsgText']!="OK"){
						$IsOK=FALSE;
						$ErrorMsg.= colorize("CHECK $DB.$T => ".$TCa['MsgText'],"white","red",TRUE,1);
					} 
					
					
					progress_bar("time",100," $Tdc/$Tc checked");
				
					$TAa=dse_table_analyze($DB,$T);
					if($TAa['MsgText']=="Table is already up to date" || $TAa['MsgText']=="OK"){
					}else{
						$IsOK=FALSE;
						if($ErrorMsg) $ErrorMsg.="\n";
						$ErrorMsg.= colorize("ANALYZE $DB.$T => ".$TAa['MsgText'],"white","red",TRUE,1);
					}
				
					
				}
				
				print " $Engine {$Rows}k rows ${Size} MB    "; 
				
				if($TSa['Engine']=="CSV"){
					print colorize("  ???  ","red","yellow",TRUE,1);
				}else{
					if($IsOK){
						print colorize("  OK!  ","white","green",TRUE,1);
					}else{
						print colorize("  BAD  ","white","red",TRUE,1);
					}
				}	
				
				print "\n";
				
				
				if($ErrorMsg){
					print $ErrorMsg."\n";
				}
				
				if(!$IsOK && $DoRepair){
					progress_bar("time",100," $Tdc/$Tc checked");
				
					dse_table_repair($DB,$T);
				}
				if($DoOptimize){
					dse_table_optimize($DB,$T);
				}
			}
		}
	}
	
}


function dse_database_check_table($Database="",$Table="",$DoRepair=TRUE,$DoOptimize=TRUE){
	global $vars; dse_trace();


	if(!$Database || !$Table){
		if($Database){
			$Tables=dse_table_list_array($Database);
			foreach($Tables as $Table){
				dse_database_check_table($Database,$Table,$DoRepair,$DoOptimize);
			}
			
		}else{
			$Databases=dse_database_list_array($Database);
			foreach($Databases as $Database){
				$Tables=dse_table_list_array($Database);
				foreach($Tables as $Table){
					dse_database_check_table($Database,$Table,$DoRepair,$DoOptimize);
				}
			}	
			
		}
		return;
	}


	$W=cbp_get_screen_width();
	$TableNameWidth=intval($W*(2/5));
	if($TableNameWidth<55){
		$TableNameWidth=55;
	}


	print colorize(" $DB","red","black",TRUE,1);
	print colorize(".","green","black");
	print colorize($Database.".".$Table,"magenta","black",TRUE,1);
	
	$PadSize=$TableNameWidth-(strlen($Database)+strlen($Table)+2);
	print pad("",$PadSize);
	
	$IsOK=TRUE;
	$ErrorMsg="";

	
	
	$TSa=dse_table_status_array($Database,$Table);
	$Rows=$TSa['Rows'];
	$Engine=$TSa['Engine'];
	$Avg_row_length=$TSa['Avg_row_length'];
	$Size_int=$Avg_row_length*$Rows;
	
	$Engine=pad($Engine,10," ","center");
	$Engine=colorize($Engine,"yellow","black");
	
	$Rows=pad(intval($Rows/1000),6," ","right");
	if(     $TSa['Rows']>1500000){
		$Rows=colorize($Rows,"red","black",TRUE,1);
	}elseif($TSa['Rows']> 700000){
		$Rows=colorize($Rows,"yellow","black",TRUE,1);
	}elseif($TSa['Rows']> 100000){
		$Rows=colorize($Rows,"green","black",TRUE,1);
	}else{
		$Rows=colorize($Rows,"blue","black",TRUE,1);
	}
	
	$Size=pad(intval($Size_int/1000000),6," ","right");
	$Size=colorize($Size,"green","black",TRUE,1);
	if(     $Size_int>100000000){
		$Size=colorize($Size,"red","black",TRUE,1);
	}elseif($Size_int> 10000000){
		$Size=colorize($Size,"yellow","black",TRUE,1);
	}elseif($Size_int>  1000000){
		$Size=colorize($Size,"green","black",TRUE,1);
	}else{
		$Size=colorize($Size,"blue","black",TRUE,1);
	}
	
	
	if($TSa['Engine']!="CSV"){
		progress_bar("time",100," checking");
		$TCa=dse_table_check($Database,$Table);
		if($TCa['MsgText']!="OK"){
			$IsOK=FALSE;
			$ErrorMsg.= colorize("CHECK $DB.$T => ".$TCa['MsgText'],"white","red",TRUE,1);
		} 
		
		
		progress_bar("time",100," analyzing");
	
		$TAa=dse_table_analyze($Database,$Table);
		if($TAa['MsgText']=="Table is already up to date" || $TAa['MsgText']=="OK"){
		}else{
			$IsOK=FALSE;
			if($ErrorMsg) $ErrorMsg.="\n";
			$ErrorMsg.= colorize("ANALYZE $DB.$T => ".$TAa['MsgText'],"white","red",TRUE,1);
		}
	
		
	}
	
	print " $Engine {$Rows}k rows ${Size} MB    "; 
	
	if($TSa['Engine']=="CSV"){
		print colorize("  ???  ","red","yellow",TRUE,1);
	}else{
		if($IsOK){
			print colorize("  OK!  ","white","green",TRUE,1);
		}else{
			print colorize("  BAD  ","white","red",TRUE,1);
		}
	}	
	
	print "\n";
	
	
	if($ErrorMsg){
		print $ErrorMsg."\n";
	}
	
	if(!$IsOK && $DoRepair){
		progress_bar("time",100," repairing");
		
		dse_table_repair($Database,$Table);
		
		if($DoOptimize){
			dse_table_optimize($Database,$Table);
		}
	}
}

function dse_database_check_all($DoRepair=TRUE,$DoOptimize=TRUE){
	global $vars; dse_trace();
	global $Tdc,$Tc;
	$DBa=dse_database_list_array();
	$W=cbp_get_screen_width();
	$TableNameWidth=intval($W*(2/5));
	if($TableNameWidth<55){
		$TableNameWidth=55;
	}
	$Dc=0; $Tc=0;
	foreach($DBa as $DB){
		if($DB && $DB!="information_schema"){
			dse_database_check($DB, $DoRepair,$DoOptimize);
		}
	}
}

?>