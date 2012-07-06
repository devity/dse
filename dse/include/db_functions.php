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


function dse_database_repair_all(){
	global $vars; dse_trace();
	
}




function dse_table_check($Database,$Table){
	global $vars; dse_trace();
	//print colorize("CHECK Table $T:\n","green","black");
	$r=dse_exec("echo \"USE $Database;\n CHECK TABLE $Table EXTENDED;\" | mysql -u ".$vars['DSE']['MYSQL_USER'],FALSE,FALSE);
	list($HeaderLine,$DataLine)=split("\n",$r);
	$Da=split("[ \t]+",$DataLine);
	list($DdT,$Op,$MsgType,$MsgText)=$Da;
	return(array("DdT"=>$DdT,"Op"=>$Op,"MsgType"=>$MsgType,"MsgText"=>$MsgText));		
}

function dse_table_analyze($Database,$Table){
	global $vars; dse_trace();
	//print colorize("CHECK Table $T:\n","green","black");
	$r=dse_exec("echo \"USE $Database;\n ANALYZE TABLE $Table;\" | mysql -u ".$vars['DSE']['MYSQL_USER'],FALSE,FALSE);
	/*$r=strcut($r,"\n");
	$tbr=array();
	$ra=split("\n",$r);
	foreach($ra as $re){
		list($DdT,$Op,$MsgType,$MsgText)=$De;
	}
	*/
	
	list($HeaderLine,$DataLine)=split("\n",$r);
	$DataLine=whitespace_minimize($DataLine);
	//$Da=split("[ \t]+",$DataLine);
	$Da=$DataLine;
	$DdT=strcut($Da,""," ");
	$Da=strcut($Da," ");
	$Op=strcut($Da,""," ");
	$Da=strcut($Da," ");
	$MsgType=strcut($Da,""," ");
	$Da=strcut($Da," ");
	$MsgText=$Da;
	//list($DdT,$Op,$MsgType,$MsgText)=$Da;
	return(array("DdT"=>$DdT,"Op"=>$Op,"MsgType"=>$MsgType,"MsgText"=>$MsgText));		
}


function dse_database_check_all(){
	global $vars; dse_trace();
	$DBa=dse_database_list_array();
	foreach($DBa as $DB){
		if($DB){
			print colorize("Checking Database ","green","black");
			print colorize($DB,"red","black",TRUE,1);
			print colorize("...\n","green","black");
			$Ta=dse_table_list_array($DB);
			foreach($Ta as $T){
				if($T){
					print colorize(" Checking Table ","green","black");
					print colorize($DB,"red","black",TRUE,1);
					print colorize(".","green","black");
					print colorize($T,"magenta","black",TRUE,1);
					print colorize(":  ","green","black");
					
					$IsOK=TRUE;
					
					$TCa=dse_table_check($DB,$T);
					if($TCa['MsgText']!="OK"){
						$IsOK=FALSE;
						print "CHECK $DB.$T => ".$TCa['MsgText']."\n";
					} 
					
					
					$TAa=dse_table_analyze($DB,$T);
					$IsOK=FALSE;
					if($TAa['MsgText']!="Table is already up to date"){
						print "ANALYZE $DB.$T => ".$TAa['MsgText']."\n";
					}
				
					if($IsOK){
						print colorize("OK\n","green","black",TRUE,1);
					}
				}
			}
		}
	}
}


?>