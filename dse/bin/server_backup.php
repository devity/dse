#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
$vars['Verbosity']=1;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="server-backup";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="makes a backup of web data, mysql, conf's, etc";
$vars['DSE']['DSE_DSE_VERSION']="v0.01b";
$vars['DSE']['DSE_DSE_VERSION_DATE']="2012/06/16";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******


$parameters_details = array(
  array('l','log-show:',"shows tail of log ".$vars['DSE']['LOG_FILE']),
  array('h','help',"this message"),
  array('q','quiet',"same as --verbosity 0"),
  array('e','edit',"backs up and launches a vim of ".$vars['DSE']['DSE_CONFIG_FILE_GLOBAL']),
  array('w','config-show',"prints ".$vars['DSE']['DSE_CONFIG_FILE_GLOBAL']),
  array('','config-show',"prints contents of ".$vars['DSE']['DSE_CONFIG_FILE_GLOBAL']),
  array('y','verbosity:',"0=none 1=some 2=more 3=debug"),
  array('z:','status:',"shows status on all or arg1. options: [initd]"),
  array('c','clone',"build a recreate / clone script"),
);
$vars['parameters']=dse_cli_get_paramaters_array($parameters_details);
$vars['Usage']=dse_cli_get_usage($parameters_details);
$vars['argv_origional']=$argv;
dse_cli_script_start();
		
$BackupBeforeUpdate=TRUE;
foreach (array_keys($vars['options']) as $opt) switch ($opt) {
	case 'q':
	case 'quiet':
		$Quiet=TRUE;
		$vars['Verbosity']=0;
		break;
  	case 'y':
	case 'verbosity':
		$vars['Verbosity']=$vars['options'][$opt];
		if($vars['Verbosity']>=2) print "Verbosity set to ".$vars['Verbosity']."\n";
		break;
	case 'h':
  	case 'help':
  		$ShowUsage=TRUE;
		$DidSomething=TRUE;
		break;
	case 'l':
	case 'log-show':
		if($vars['options'][$opt]) $Lines=$vars['options'][$opt]; else $Lines=$vars['DSE']['LOG_SHOW_LINES'];
		$Command="tail -n $Lines ".$vars['DSE']['LOG_FILE'];
		//print "$Command\n";
		print `$Command`;
		$DidSomething=TRUE;
		break;
	case 'e':
	case 'edit':
		print "Backing up ".$vars['DSE']['DSE_CONFIG_FILE_GLOBAL']." and launcing in vim:\n";
		passthru("/dse/bin/vibk ".$vars['DSE']['DSE_CONFIG_FILE_GLOBAL']." 2>&1");
		$DidSomething=TRUE;
		break;
	case 'w':
  	case 'config-show':
		print dse_file_get_contents($vars['DSE']['DSE_CONFIG_FILE_GLOBAL']);
		$DidSomething=TRUE;
		break;
	case 'z':
  	case 'status':
		$DidSomething=TRUE;
		break;
	case 'c':
  	case 'clone':
		$DidSomething=TRUE;
		break;
		
}



print "Starting Backup:\n";


print "Backup Directory: ".$vars['DSE']['DSE_BACKUP_DIR']." ";
if(!is_dir($vars['DSE']['DSE_BACKUP_DIR'])){
	print " $Missing. Create? ";
	$A=dse_ask_yn();
	if($A=='Y'){
		dse_directory_create($vars['DSE']['DSE_BACKUP_DIR'],"777","root:root");
	}else{
		print "\n  Can't backup w/o backup dir. Exiting.\n";
		exit(-1);	
	}
}else{
	print $OK;
}
print "\n";




dse_backup_server_environment();
dse_backup_httpd();
dse_backup_mysqld();


print "Done.\n";
exit(0);


function dse_backup_mysqld() {
	global $vars;
	dse_detect_os_info();
	
	print "MySQL Backup Directory: ".$vars['DSE']['BACKUP_DIR_MYSQL']." ";
	if(!is_dir($vars['DSE']['BACKUP_DIR_MYSQL'])){
		print " $Missing. Create? ";
		$A=dse_ask_yn();
		if($A=='Y'){
			dse_directory_create($vars['DSE']['BACKUP_DIR_MYSQL'],"777","root:root");
		}else{
			print "\n  Can't backup w/o backup dir. Exiting.\n";
			exit(-1);	
		}
	}else{
		print $OK;
	}
	print "\n";
	
	print " Saving Copy of mysqld Data: ";
	$DATE_TIME_NOW=trim(`date +"%y%m%d%H%M%S"`);
 	$file=$vars['DSE']['BACKUP_DIR_MYSQL']."/mysqldump".$DATE_TIME_NOW.".sql";
	$Command="mysqldump --all-databases --user=localroot --add-drop-database --comments --debug-info --disable-keys --dump-date --force --quick --routines --verbose --result-file=$file";
	print " Command: $Command\n";
	`$Command`;
	`gzip $file`;
	//`mysqlhotcopy-all-databases`;


	print " $_OK MySQL backup saved in  ${dir}\n";
}
   
   


function dse_backup_httpd() {
	global $vars; 
	dse_detect_os_info();

	print "httpd Backup Directory: ".$vars['DSE']['BACKUP_DIR_HTTP']." ";
	if(!is_dir($vars['DSE']['BACKUP_DIR_HTTP'])){
		print " $Missing. Create? ";
		$A=dse_ask_yn();
		if($A=='Y'){
			dse_directory_create($vars['DSE']['BACKUP_DIR_HTTP'],"777","root:root");
		}else{
			print "\n  Can't backup w/o backup dir. Exiting.\n";
			exit(-1);	
		}
	}else{
		print $OK;
	}
	print "\n";
	
	$web_data_dir=$vars['DSE']['BACKUP_HTTP_ROOT'];
	$dse_server_httpd_backup_directory=$vars['DSE']['BACKUP_DIR_HTTP'];
	
	print " Saving Copy of httpd Data: ";
	
   	$DATE_TIME_NOW=trim(`date +"%y%m%d%H%M%S"`);
   	if(!file_exists($dse_server_httpd_backup_directory)){
   		print "Backup directory $dse_server_httpd_backup_directory missing - fatal error. exiting.\n";
   		exit(1);
   	}
	
	$dir=$dse_server_httpd_backup_directory . "/" . $DATE_TIME_NOW;
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
	global $vars;
	dse_detect_os_info();
	
	$dse_server_environment_backup_directory=$vars['DSE']['DSE_BACKUP_DIR']."/server_environment";
	
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



