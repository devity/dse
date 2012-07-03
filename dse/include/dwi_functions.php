<?php

function dse_dwi_overview(){
	global $vars;
	
	print "<table border=1 cellpdding=3 cellspacing=0><tr class='f7pt'>
	
	<td valign=top><b class='f10pt'>System Stats</b><br>
	".dse_dwi_overview_section_sysstats()."
	</td>
	
	<td valign=top><b class='f10pt'>Disks</b><br>
	".dse_dwi_overview_section_disks()."
	</td>
	
	<td valign=top><b class='f10pt'>File Manager</b><br>
	
	</td>
	
	
	<td valign=top><b class='f10pt'>Code Manager</b><br>
	 <a href=/code_explorer/>Code Explorer</a>
	Recent Traces:<br>";
	
	$dse_ls_out=dse_ls("/tmp/dse_trace__*");
	//print d2s($dse_ls_out);
	foreach($dse_ls_out as $dse_ls_out_row){
		list($Type,$File)=$dse_ls_out_row;
		$Script=strcut($File,"__",".2012");
		$RunTime=strcut($File,$Script.".");
		$File_esc=urldecode($File);
		$Lines=dse_exec("wc -l $File");
		print "$RunTime $Script  <a href=/code_explorer/index.php?ViewDebugOutput=TRUE&File=$File_esc target=_blank>view</a> ($Lines)<br>";
	}
	
	print " </td>
	
	</tr><tr class='f7pt'>
	
	
	<td valign=top><b class='f10pt'>Open & Connections</b><br>
	".dse_dwi_overview_section_openports()."
	</td>	
	
	<td valign=top><b class='f10pt'>Load Balancer</b><br>
	".dse_dwi_overview_section_dlb()."
	</td>
	
	
	<td valign=top><b class='f10pt'>Security</b><br>
	".dse_dwi_overview_section_security()."
	</td>
	
	
	<td valign=top><b class='f10pt'>Backup</b><br>
	".dse_dwi_overview_section_backup()."
	</td>
	
	
	</tr>
	<tr class='f7pt'>
	
	
	<td valign=top><b class='f10pt'>Monitoring</b><br>
	".dse_dwi_overview_section_monitoring()."
	</td>
	
	
	<td valign=top><b class='f10pt'></b><br>
	". text2html(dse_exec("ps -aux")) ."
	</td>
	
	
	<td valign=top><b class='f10pt'>Services chkconfig</b><br>
	".dse_dwi_overview_section_initd()."
	</td>
	
	
	<td valign=top><b class='f10pt'>iptables -nvL</b><br>
	".dse_dwi_overview_section_firewall()."
	</td>
	
	</tr>
	<tr class='f7pt'>
	
	
	<td valign=top><b class='f10pt'>DNS</b><br>
	status<br>
	sites <br>
	# requests<br>
	</td>
	
	
	<td valign=top><b class='f10pt'>Apache</b><br>
	sites dos/roots<br>
	processes<br>
	log, error and access
	</td>
	
	
	<td valign=top><b class='f10pt'></b><br>
	</td>
	
	
	<td valign=top><b class='f10pt'></b><br>
	
	</td>
	
	
	</tr>
	
	</table>";
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




function dse_dwi_overview_section_disks(){
	global $vars;
	$tbr="";
	$tbr.=text2html(dse_exec("df -h"));
	return $tbr;
}


function dse_dwi_overview_section_sysstats(){
	global $vars;
	$tbr="";
	$tbr.=text2html(dse_exec("uptime"));
	$tbr.=text2html(dse_exec("iostat"));
	$tbr.=text2html(dse_exec("vmstat"));
	$tbr.=text2html(dse_exec("who"));
	return $tbr;
}


function dse_dwi_overview_section_security(){
	global $vars;
	$tbr="";
	$Installed="<font color=green><b>INSTALLED</b></font>";
	$NotInstalled="<font color=red><b>NOT INSTALLED</b></font>";
	$Running="<font color=green><b>RUNNING</b></font>";
	$NotRunning="<font color=red><b>NOT RUNNING</b></font>";
//	$tbr.=text2html(`uptime`);


	$tbr.="<a href=http://www.fail2ban.org/wiki/index.php/Main_Page>fail2ban</a>: ";
	$r=dse_exec("/dse/bin/grep2pid fail2ban");
	if($r){
		$tbr.= $Running;
	}else{
		$tbr.= $NotRunning;
	}
	$tbr.="<br>";

	$tbr.="<a href=http://sourceforge.net/projects/logwatch/files/ target=_blank>logwatch</a>: ";
	$r=dse_which("logwatch");
	if($r){
		$tbr.= $Installed;
	}else{
		$tbr.= $NotInstalled;
	}
	$tbr.="<br>";

	$tbr.="<a href=http://aide.sourceforge.net/ target=_blank>aide</a>: ";
	$r=dse_which("aide");
	if($r){
		$tbr.= $Installed;
	}else{
		$tbr.= $NotInstalled;
	}
	$tbr.="<br>";

	$tbr.="<a href=http://rkhunter.sourceforge.net/ target=_blank>rkhunter</a>: ";
	$r=dse_which("rkhunter");
	if($r){
		$tbr.= $Installed;
	}else{
		$tbr.= $NotInstalled;
	}
	$tbr.="<br>";

	$tbr.="<a href=http://www.chkrootkit.org/ target=_blank>chkrootkit</a>: ";
	$r=dse_which("chkrootkit");
	if($r){
		$tbr.= $Installed;
	}else{
		$tbr.= $NotInstalled;
	}
	$tbr.="<br>";
	
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
		$tbr.=text2html(dse_exec("chkconfig --list"));
		$tbr=str_replace(":off",":<font color=red>OFF</font>",$tbr);
		$tbr=str_replace(":on",":<font color=green>ON</font>",$tbr);
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


function dse_dwi_overview_section_monitoring(){
	global $vars;
	$tbr="";
	$Installed="<font color=green><b>INSTALLED</b></font>";
	$NotInstalled="<font color=red><b>NOT INSTALLED</b></font>";
	$Running="<font color=green><b>RUNNING</b></font>";
	$NotRunning="<font color=red><b>NOT RUNNING</b></font>";
	
	
	$tbr.="<a href=http://www.ntop.org/ target=_blank>ntop</a>: ";
	$r=dse_which("ntop");
	if($r){
		$tbr.= $Installed;
		$tbr.=" <a href=http://localhost:3000/ target=_blank>view</a> ";
	}else{
		$tbr.= $NotInstalled;
	}
	$tbr.="<br>";
	
	
	$tbr.="<a href=http://www.cacti.net/ target=_blank>cacti</a>: ";
	$r=dse_which("cacti");
	if($r){
		$tbr.= $Installed;
		$tbr.=" <a href=http://localhost/cacti target=_blank>view</a> ";
	}else{
		$tbr.= $NotInstalled;
	}
	$tbr.="<br>";
	
	$PHPMyAdminInstalled=FALSE;
	if(dse_is_osx()){
		if(dse_file_exists("/opt/local/var/macports/sources/rsync.macports.org/release/ports/www/phpmyadmin")){
			$PHPMyAdminInstalled=TRUE;
		}
	}else{
		$r=dse_exec("/dse/bin/fss phpmyadmin");
		if($r) $PHPMyAdminInstalled=TRUE;
	}
	$tbr.="<a href=http://www.phpmyadmin.net/home_page/index.php target=_blank>phpmyadmin</a>: ";
	if($PHPMyAdminInstalled){
		$tbr.= $Installed;
		$tbr.=" <a href=http://localhost/phpmyadmin target=_blank>view</a> ";
	}else{
		$tbr.= $NotInstalled;
	}
	$tbr.="<br>";
	
	
	return $tbr;
}


function dse_dwi_overview_section_backup(){
	global $vars;
	$tbr="";
	$Installed="<font color=green><b>INSTALLED</b></font>";
	$NotInstalled="<font color=red><b>NOT INSTALLED</b></font>";
	$Running="<font color=green><b>RUNNING</b></font>";
	$NotRunning="<font color=red><b>NOT RUNNING</b></font>";
	
	
	$tbr.="<a href=http://joeyh.name/code/etckeeper/ target=_blank>etckeeper</a>: ";
	$r=dse_which("etckeeper");
	if($r){
		$tbr.= $Installed;
	}else{
		$tbr.= $NotInstalled;
	}
	$tbr.="<br>";
	

	
	
	return $tbr;
}


?>
	
	
	