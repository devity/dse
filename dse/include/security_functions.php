<?


function dse_dsec_overview(){
	global $vars; dse_trace();
	
	print colorize("Ports: ","cyan","black").dse_ports_open(TRUE)."\n";

	print dse_exec("who");

	print "
	ToDo:\n
	 logwatch   \n
	 rkhunter, chkrootkit \n
	 fail2ban, snort \n
	
	";


}

?>