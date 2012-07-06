<?php

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
	$r=dse_exec("echo \"USE $Database;\n REPAIR TABLE $Table EXTENDED;\" | mysql -u ".$vars['DSE']['MYSQL_USER'],FALSE,TRUE);
	return;
}


function dse_table_optimize($Database,$Table){
	global $vars; dse_trace();
	$r=dse_exec("echo \"USE $Database;\n OPTIMIZE TABLE $Table;\" | mysql -u ".$vars['DSE']['MYSQL_USER'],FALSE,FALSE);
	return;
}


function dse_database_repair_all(){
	global $vars; dse_trace();
	
}




function dse_table_check($Database,$Table){
	global $vars; dse_trace();
	//print colorize("CHECK Table $T:\n","green","black");
	$r=dse_exec("echo \"USE $Database;\n CHECK TABLE $Table EXTENDED;\" | mysql -u ".$vars['DSE']['MYSQL_USER'],FALSE,FALSE);
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
	$r=dse_exec("echo \"USE $Database;\n ANALYZE TABLE $Table;\" | mysql -u ".$vars['DSE']['MYSQL_USER'],FALSE,FALSE);
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
	$DBa=dse_database_list_array();
	foreach($DBa as $DB){
		if($DB && $DB!="information_schema"){
			
			print bar("Checking Database $DB: ","v","white","blue","black","blue");
			$Ta=dse_table_list_array($DB);
			foreach($Ta as $T){
				if($T){
					print colorize(" Checking Table ","green","black");
					print colorize($DB,"red","black",TRUE,1);
					print colorize(".","green","black");
					print colorize($T,"magenta","black",TRUE,1);
					print colorize(":  ","green","black");
					
					$PadSize=45-(strlen($DB)+strlen($T)+3);
					print pad("",$PadSize);
					
					$IsOK=TRUE;
					$ErrorMsg="";
					
					$TCa=dse_table_check($DB,$T);
					if($TCa['MsgText']!="OK"){
						$IsOK=FALSE;
						$ErrorMsg.= colorize("CHECK $DB.$T => ".$TCa['MsgText'],"white","red",TRUE,1);
					} 
					
					
					$TAa=dse_table_analyze($DB,$T);
					if($TAa['MsgText']=="Table is already up to date" || $TAa['MsgText']=="OK"){
					}else{
						$IsOK=FALSE;
						if($ErrorMsg) $ErrorMsg.="\n";
						$ErrorMsg.= colorize("ANALYZE $DB.$T => ".$TAa['MsgText'],"white","red",TRUE,1);
					}
				
					
					
					$TSa=dse_table_status_array($DB,$T);
					$Rows=$TSa['Rows'];
					$Engine=$TSa['Engine'];
					$Avg_row_length=$TSa['Avg_row_length'];
					$Size_int=$Avg_row_length*$Rows;
					
					$Engine=pad($Engine,10," ","center");
					$Engine=colorize($Engine,"yellow","black");
					
					$Rows=pad(intval($Rows/1000)."k",6," ","right");
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
					
					
					print "   $Engine    $Rows rows   ${Size}Mb   "; 
					if($IsOK){
						print colorize(" OK","green","black",TRUE,1);
					}else{
						print colorize("BAD","red","black",TRUE,1);
					}
					print "\n";
					if($ErrorMsg){
						print $ErrorMsg."\n";
					}
					
					if(!$IsOK && $DoRepair){
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