<?php

function dse_dwi_overview(){
	global $vars;
	
	print "<table border=1 cellpdding=3 cellspacing=0><tr class='f7pt'>
	
	<td valign=top><b class='f10pt'>System Stats</b><br>
	".dse_dwi_overview_section_sysstats()."
	</td>
	
	<td valign=top><b class='f10pt'>File Manager</b><br>
	
	</td>
	
	
	<td valign=top><b class='f10pt'>Code Manager</b><br>
	
	</td>
	
	</tr><tr class='f7pt'>
	
	
	<td valign=top><b class='f10pt'>Open & Connections</b><br>
	".dse_dwi_overview_section_openports()."
	</td>	
	
	<td valign=top><b class='f10pt'>Load Balancer</b><br>
	".dse_dwi_overview_section_dlb()."
	</td>
	
	
	<td valign=top><b class='f10pt'>Services chkconfig</b><br>
	".dse_dwi_overview_section_initd()."
	
	</td>
	
	
	<td valign=top><b class='f10pt'>iptables -nvL</b><br>
	".dse_dwi_overview_section_firewall()."
	</td>
	
	
	</tr></table>";
	//print text2html(`dse -s`);
}


function dse_dwi_overview_section_openports(){
	global $vars;
	$tbr="";
	$ports_open_raw=`/dse/bin/dnetstat.php -o -d" "`;
	//$tbr.= "Open Ports:<br>";
	$ports_open_array_raw=split(" ",$ports_open_raw);
	foreach($ports_open_array_raw as $pol){
		$pa=split(":",$pol);
		$exe=$pa[0];
		$port=$pa[1];
		$ports_open_array[$port]=$exe;
	}
	ksort($ports_open_array);
	foreach($ports_open_array as $port=>$exe){
		if($port){
			$tbr.= "<b>$port</b> $exe<br>";	
		}
	}
	/*print "Connections: <br> ";
	print "</td><td valign=top width=15%>";
	foreach($ports_open_array_raw as $pol){
		$pa=split(":",$pol);
		$exe=$pa[0];
		$port=$pa[1];
		$port_connections_raw=`/dse/bin/dnetstat.php -c $port 2>&1`;
		print "<b>$port</b> $port_connections_raw<br>";
	}
	*/
	return $tbr;
}
function dse_dwi_overview_section_sysstats(){
	global $vars;
	$tbr="";
	$tbr.=text2html(`uptime`);
	$tbr.=text2html(`iostat`);
	$tbr.=text2html(`vmstat`);
	$tbr.=text2html(`who`);
	$tbr.=text2html(`ps -aux`);
	return $tbr;
}
function dse_dwi_overview_section_dlb(){
	global $vars;
	$tbr="";
	$tbr.=text2html(`/dse/bin/dlb --status`);
	return $tbr;
}
function dse_dwi_overview_section_initd(){
	global $vars;
	include_once ("/dse/bin/dse_config_functions.php");
	$tbr="";
	$tbr.=dse_initd_entry_get_info();
	$tbr.="<hr>";
	if(dse_is_osx()){
		$tbr.=text2html(`daemonic dump`);
		//$tbr.=text2html(`sudo launchctl list`);
	}else{
		$tbr.=text2html(`chkconfig --list`);
	}
	return $tbr;
}

function dse_dwi_overview_section_firewall(){
	global $vars;
	$tbr="";
	if(dse_is_osx()){
		$tbr.=text2html(`sudo ipfw list`);
	}else{
		$tbr.=text2html(`sudo iptables -nvL`);
	}
	return $tbr;
}
?>
	
	
	