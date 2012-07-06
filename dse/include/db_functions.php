<?php

function dse_database_list_array(){
	global $vars; dse_trace();
	$r=dse_exec("echo \"SHOW DATABASES;\" | mysql -u ".$vars['DSE']['MYSQL_USER']);
	return split("\n",$r);
}

function dse_table_list_array($Database){
	global $vars; dse_trace();
	$r=dse_exec("echo \"USE $Database;\n SHOW TABLES;\" | mysql -u ".$vars['DSE']['MYSQL_USER']);
	return split("\n",$r);
}


function dse_database_repair_all(){
	global $vars; dse_trace();
	
}

function dse_database_check_all(){
	global $vars; dse_trace();
	$DBa=dse_database_list_array();
	foreach($DBa as $DB){
		print colorize("Database $DB:\n","cyan","black");
		$Ta=dse_table_list_array($DB);
		foreach($Ta as $T){
			print colorize("CHECK Table $T:\n","green","black");
			$r=dse_exec("echo \"USE $DB;\n CHECK TABLE $T EXTENDED;\" | mysql -u ".$vars['DSE']['MYSQL_USER']);
			print colorize($r,"yellow","black");
			
			print colorize("ANALYZE Table $T:\n","green","black");
			$r=dse_exec("echo \"USE $DB;\n ANALYZE TABLE $T;\" | mysql -u ".$vars['DSE']['MYSQL_USER']);
			print colorize($r,"yellow","black");
		}
	}
}


?>