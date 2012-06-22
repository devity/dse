<?php
$DSE_ROOT="/dse";
include_once ("$DSE_ROOT/include/web_config.php");
dse_print_page_header();

print "<br><center><b class='f10pt'>Welcome to Devity Server Environment's Web Interface!</b></center><br><br>";


print "<br><b class='f10pt'>Sections:</b><br>";

print " * <a href=/code_explorer/>Code Explorer</a><br>";



print "<br><hr>";

print text2html(`dse -s`);
print "<br><hr>";




	$ports_open_raw=`/dse/bin/dnetstat.php -o -d" "`;
	
	
	print "<table border=1 cellpadding=1 cellspacing=0><tr class='f7pt'><td valign=top width=15%>";
	
	print "Open Ports:<br>";
	$ports_open_array_raw=split(" ",$ports_open_raw);
	foreach($ports_open_array_raw as $pol){
		$pa=split(":",$pol);
		$exe=$pa[0];
		$port=$pa[1];
		$ports_open_array[$port]=$exe;
	}
	ksort($ports_open_array);
	foreach($ports_open_array as $port=>$exe){
		print "<b>$port</b> $exe<br>";	
	}
	print "</td><td valign=top width=15%>";
	
	/*print "Connections: <br> ";
	
	foreach($ports_open_array_raw as $pol){
		$pa=split(":",$pol);
		$exe=$pa[0];
		$port=$pa[1];
		$port_connections_raw=`/dse/bin/dnetstat.php -c $port 2>&1`;
		print "<b>$port</b> $port_connections_raw<br>";
	}
	*/
	
	print "</td><td valign=top width=70%>httpd ";
	
	$vars['dpd_httpd_fullstatus__embeded']=TRUE;
//	dpd_httpd_fullstatus();	
	
	print "</td></tr></table>";
	
	

	
dse_print_page_footer();
?>
