#!/usr/bin/php
<?php
$StartTime=time();

print "Starting.\n";


ini_set('display_errors','On');	
error_reporting(E_ALL & ~E_NOTICE);

$dse_server_backup_directory="/backup";
$dse_server_environment_backup_directory=$dse_server_backup_directory."/server_environment";
$dse_server_httpd_backup_directory=$dse_server_backup_directory."/httpd";

$web_conf_dir="/etc/httpd";
$web_data_dirs=array();
$web_data_dirs[]="/home/admin/batteriesdirect.com";
$web_data_dirs[]="/var/www";
 

dse_backup_server_environment();
dse_backup_httpd();
dse_backup_mysqld();


print "Done.\n";
exit(0);


function dse_backup_mysqld() {
	global $vars,$dse_server_httpd_backup_directory,$web_conf_dir,$web_data_dirs;
	dse_detect_os_info();
	
	print "Saving Copy of mysqld Data: ";
	$DATE_TIME_NOW=trim(`date +"%y%m%d%H%M%S"`);
 	$file="/backup/mysql/mysqldump".$DATE_TIME_NOW.".sql";
	$Command="mysqldump --all-databases --user=localroot --add-drop-database --comments --debug-info --disable-keys --dump-date --force --quick --routines --verbose --result-file=$file";
	print "Command: $Command\n";
	`$Command`;
	`gzip $file`;
	`mysqlhotcopy-all-databases`;


	print "$_OK  saved in  ${dir}\n";
}
   
   


function dse_backup_httpd() {
	global $vars; //,$dse_server_httpd_backup_directory,$web_conf_dir,$web_data_dirs;
	dse_detect_os_info();
	$web_data_dir=$vars['DSE']['BACKUP_HTTP_ROOT'];
	print "Saving Copy of httpd Data: ";
	
   	$DATE_TIME_NOW=trim(`date +"%y%m%d%H%M%S"`);
   	$dir=$dse_server_httpd_backup_directory . "/" . $DATE_TIME_NOW;
   	if(!file_exists($dse_server_httpd_backup_directory)){
   		print "Backup directory $dse_server_httpd_backup_directory missing - fatal error. exiting.\n";
   		exit(1);
   	}

	`mkdir $dir`;   
   
   	$web_conf_dir="/etc/httpd";
   	if(!is_dir($web_conf_dir)){
   		$web_conf_dir="/etc/apache2";
	   	if(!is_dir($web_conf_dir)){
	   		$web_conf_dir="";
	   	}
   	}
   
   	if($web_conf_dir){
		$Command="cp -rf $web_conf_dir ${dir}/.";
		print "Command: $Command\n";
		`$Command`;
	}
	//foreach($web_data_dirs as $web_data_dir){
		$Command="cp -rf $web_data_dir ${dir}/.";
		print "Command: $Command\n";
		`$Command`;
	//}

	print "$_OK  saved in  ${dir}\n";
}
   
   


function dse_backup_server_environment() {
	global $vars,$dse_server_environment_backup_directory;
	dse_detect_os_info();
	
	print "Saving Image of Environment Variables: ";
	
   	$DATE_TIME_NOW=trim(`date +"%y%m%d%H%M%S"`);
   	$dir=$dse_server_environment_backup_directory . "/" . $DATE_TIME_NOW;
   	`mkdir ${dir}`;
   	if(!file_exists($dse_server_environment_backup_directory)){
   		print "Backup directory $dse_server_environment_backup_directory missing - fatal error. exiting.\n";
   		exit(1);
   	}


   	`ps aux &> ${dir}/ps-aux.out`;
   	`ps axjf &> ${dir}/ps-axjf.out`;
   	`ps AFl &> ${dir}/ps-AFl.out`;
   	`netstat -pn -l -A inet &> ${dir}/netstat-pn-l-Ainet.out`;
   	`lsof -i | grep LISTEN &> ${dir}/lsof-i.out`;
   	`nmap -v -sS localhost &> ${dir}/nmap-v-sSlocalhost.out`;
   	`printenv &> ${dir}/printenv.out`;
   	`df &> ${dir}/df.out`;
  // 	`memstat &> ${dir}/memstat.out`;
  
   	if($vars[IsUbuntu]){
   		`dpkg --get-selections &> ${dir}/dpkg--get-selections.out`;
   	}
   	if($vars[IsCentOS]){
   		`rpm -qa &> ${dir}/rpm-qa.out`;
	}
	
	`cat /etc/*-release &> ${dir}/cat-etc-release.out`;
	`uname -a &> ${dir}/uname-a.out`;
	
	

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



