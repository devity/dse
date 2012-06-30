#!/usr/bin/php
<?php
$StartTime=time();

print "Starting.\n";


ini_set('display_errors','On');	
error_reporting(E_ALL & ~E_NOTICE);

$dse_server_stats_log_directory="/var/log/dse_server_stats";



dse_log_server_stats();

print "Done.\n";
exit(0);



function dse_log_server_stats() {
	global $vars,$dse_server_stats_log_directory;
	dse_detect_os_info();
	
	print "Saving Image of Environment Variables: ";
	
   	$DATE_TIME_NOW=trim(`date +"%y%m%d%H%M%S"`);
   	$dir=$dse_server_stats_log_directory . "/" . $DATE_TIME_NOW;
   	if(!file_exists($dse_server_stats_log_directory)){
   		print "Log directory $dse_server_stats_log_directory missing - fatal error. exiting.\n";
   		exit(1);
   	}
   	`mkdir ${dir}`;


   	`mpstat -P ALL &> ${dir}/mpstat-P-ALL.out`;
   	
   	`ps aux &> ${dir}/ps-aux.out`;
   	`ps axjf &> ${dir}/ps-axjf.out`;
   	`ps AFl &> ${dir}/ps-AFl.out`;
   	
   	`netstat -pn -l -A inet &> ${dir}/netstat-pn-l-Ainet.out`;
   	`lsof -i | grep LISTEN &> ${dir}/lsof-i.out`;
   	`nmap -v -sS localhost &> ${dir}/nmap-v-sSlocalhost.out`;
   	`printenv &> ${dir}/printenv.out`;
   	`df &> ${dir}/df.out`;
   	
   	
  // 	`memstat &> ${dir}/memstat.out`;
  
  	`vmstat &> ${dir}/vmstat.out`;
  	`vmstat -a &> ${dir}/vmstat-a.out`;
  	`vmstat -d &> ${dir}/vmstat-d.out`;
  	`vmstat -s &> ${dir}/vmstat-s.out`;
  	`vmstat -m &> ${dir}/vmstat-m.out`;
  	
  	`iostat &> ${dir}/iostat.out`;
  	`iostat-x &> ${dir}/iostat-x.out`;
  	`iostat &> ${dir}/iostat.out`;
  	`iostat &> ${dir}/iostat.out`;
  
	

	print "$_OK  saved in  ${dir}\n";
}
   
   




   
function dse_detect_os_info(){
	global $vars;
	
	$vars[dse_osinfo_release]=trim(`cat /etc/*-release`);
	$vars[dse_osinfo_uname]=trim(`uname -a`);
	if( !(strstr($vars[dse_osinfo_release],"CentOS")===FALSE) ){
		$vars[IsCentOS]=TRUE;
	}elseif( !(strstr($vars[dse_osinfo_release],"Ubuntu")===FALSE) ){
		$vars[IsUbuntu]=TRUE;
	}

}

?>
