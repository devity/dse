<?php

		
function dse_database_find_string_occurances($query,$this_db,$table){
	global $vars; dse_trace();
	if($this_db && $this_db!="*"){
		$dbs=array($this_db);
	}else{
		$dbs=dse_database_list_array();
	}
	foreach($dbs as $this_db){
		if($this_db && $this_db!="information_schema"){
			$tables=dse_table_list_array($this_db);
			foreach($tables as $this_table){
				if($this_table){
					$columns=dse_column_list_array($this_db,$this_table);
					foreach($columns as $this_column){
						if($this_column){
							$r=dse_exec("echo \"USE $this_db;\n SELECT * FROM $this_table WHERE $this_column LIKE \\\"%$query%\\\";\" | mysql -u ".$vars['DSE']['MYSQL_USER']);
							$r=strcut($r,"\n");
							if(strlen($r)>0){
								$r=substr($r,0,cbp_get_screen_width()-1);
								print "$this_db:$this_table:$this_column:\n$r\n";
							}
						//$tbr=split("\n",$r);
						}
					}
				}
			}
		}
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


function dse_table_repair($Database,$Table){
	global $vars; dse_trace();
	$pid=dse_exec_bg("echo \"USE $Database;\n REPAIR TABLE $Table EXTENDED;\" | mysql -u ".$vars['DSE']['MYSQL_USER'],FALSE,TRUE);
	while(dse_pid_is_running($pid)){
		global $Tdc,$Tc;
		progress_bar("time",100," $Tdc/$Tc checked");
					
		sleep(1);
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
			$Dc++;
			$Ta=dse_table_list_array($DB);
			foreach($Ta as $T){
				if($T){
					$Tc++;
				}
			}
		}
	}
	$Ddc=0; $Tdc=0;
	foreach($DBa as $DB){
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
}


?>