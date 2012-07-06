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
	$r=dse_exec("echo \"USE $Database;\n CHECK TABLE $Table EXTENDED;\" | mysql -u ".$vars['DSE']['MYSQL_USER']);
	list($HeaderLine,$DataLine)=split("\n",$r);
	$Da=split("[ \t]+",$DataLine);
	list($DdT,$Op,$MsgType,$MsgText)=$Da;
	return(array("DdT"=>$DdT,"Op"=>$Op,"MsgType"=>$MsgType,"MsgText"=>$MsgText));		
}

function dse_table_analyze($Database,$Table){
	global $vars; dse_trace();
	//print colorize("CHECK Table $T:\n","green","black");
	$r=dse_exec("echo \"USE $Database;\n CHECK ANALYZE $Table;\" | mysql -u ".$vars['DSE']['MYSQL_USER']);
	/*$r=strcut($r,"\n");
	$tbr=array();
	$ra=split("\n",$r);
	foreach($ra as $re){
		list($DdT,$Op,$MsgType,$MsgText)=$De;
	}
	*/
	
	list($HeaderLine,$DataLine)=split("\n",$r);
	$Da=split("[ \t]+",$DataLine);
	list($DdT,$Op,$MsgType,$MsgText)=$Da;
	return(array("DdT"=>$DdT,"Op"=>$Op,"MsgType"=>$MsgType,"MsgText"=>$MsgText));		
}


function dse_database_check_all(){
	global $vars; dse_trace();
	$DBa=dse_database_list_array();
	foreach($DBa as $DB){
		print colorize("Database $DB:\n","cyan","black");
		$Ta=dse_table_list_array($DB);
		foreach($Ta as $T){
			/*print colorize("CHECK Table $T:\n","green","black");
			$r=dse_exec("echo \"USE $DB;\n CHECK TABLE $T EXTENDED;\" | mysql -u ".$vars['DSE']['MYSQL_USER']);
			print colorize($r,"yellow","black");
			
			print colorize("ANALYZE Table $T:\n","green","black");
			$r=dse_exec("echo \"USE $DB;\n ANALYZE TABLE $T;\" | mysql -u ".$vars['DSE']['MYSQL_USER']);
			print colorize($r,"yellow","black");*/
			$TCa=dse_table_check($DB,$T);
			if($TCa['MsgText']!="OK"){
				print "$DB.$T => ".$TCa['MsgText']."\n";
			}
			
			
			$TAa=dse_table_analyze($DB,$T);
			if($TAa['MsgText']!="Table is already up to date"){
				print "$DB.$T => ".$TAa['MsgText']."\n";
			}
			
			
		}
	}
}


?>