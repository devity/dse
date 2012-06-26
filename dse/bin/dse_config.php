<?
$vars['DSE']['DSE_VERSION']="v0.031";
$vars['DSE']['DSE_BUILD']="23";
$vars['DSE']['DSE_RELEASE']="alpha";
$vars['DSE']['DSE_MODIFIED_DATE']="2012/05/26";
/*
 *  PHP Config / Settings Variable Setup for DSE ( https://github.com/devity/dse ) 
 * 
 *   CAUTION EDDITING THIS FILE !!!   This file is really for defaults only.
 *     All settings made from the dse.conf, the intended location for server or site specific dse tweaks,
 *     will over-ride the defaults set in this php variable initializer file.
 * 
 */
$StartTime=time()+microtime(); $vars['StartTime']=$StartTime;
$StartLoad=get_load(); $vars['StartLoad']=$StartLoad;

// *********************************************************************************
// ********* dse General/Global Settings
if(!$vars['DSE']){
	$vars['DSE']=array();
}

// *********************************************************************************
// ********* set directories
if(getenv("DSE_ROOT")!=""){
	$vars['DSE']['DSE_ROOT']=getenv("DSE_ROOT");
}else{
	$vars['DSE']['DSE_ROOT']="/dse"; 
}
$vars['DSE']['DSE_BIN_DIR']=$vars['DSE']['DSE_ROOT']."/bin";
$vars['DSE']['DSE_ALIASES_DIR']=$vars['DSE']['DSE_ROOT']."/aliases";
$vars['DSE']['SYSTEM_SCRIPTS_DIR']='/scripts';

$vars['DSE']['DSE_TEMPLATES_DIR']=$vars['DSE']['DSE_ROOT']."/install/templates";

$vars['DSE']['DSE_GIT_ROOT']="/root/dse_git";



if(getenv("DSE_CONFIG_DIR")!=""){
	$vars['DSE']['DSE_CONFIG_DIR']=getenv("DSE_CONFIG_DIR");
}else{
	$vars['DSE']['DSE_CONFIG_DIR']="/etc/dse";
}
$vars['DSE']['DSE_CONFIG_FILE_GLOBAL']=$vars['DSE']['DSE_CONFIG_DIR']."/"."dse.conf";
$vars['DSE']['SERVER_CONFIG_FILE']=$vars['DSE']['DSE_CONFIG_DIR']."/"."server.conf";

if(getenv("DSE_LOG_DIR")!=""){
	$vars['DSE']['DSE_LOG_DIR']=getenv("DSE_LOG_DIR");
}else{
	$vars['DSE']['DSE_LOG_DIR']="/var/log/dse";
}
$vars['DSE']['LOG_FILE']=$vars['DSE']['DSE_LOG_DIR']."/dse.log";
$vars['DSE']['LOG_SHOW_LINES']=50;

//if(getenv("DSE_BACKUP_DIR")!=""){
	//$vars['DSE']['DSE_BACKUP_DIR']=getenv("DSE_BACKUP_DIR");
//}else{
	$vars['DSE']['DSE_BACKUP_DIR']="/backup";
//}
$vars['DSE']['DSE_BACKUP_DIR_DSE']=$vars['DSE']['DSE_BACKUP_DIR']."/dse";
$vars['DSE']['BACKUP_DIR_HTTP']=$vars['DSE']['DSE_BACKUP_DIR']."/httpd";
$vars['DSE']['BACKUP_DIR_MYSQL']=$vars['DSE']['DSE_BACKUP_DIR']."/mysql";


$vars['DSE']['NAMED_CONF_FILE']="/etc/bind/named.conf.local";
$vars['DSE']['MYSQL_CONF_FILE']="/etc/my.cnf";
$vars['DSE']['MYSQL_LOG_FILE']="/tmp/mysql_query.log";
$vars['DSE']['HTTP_CONF_FILE']="/etc/httpd/conf/httpd.conf";
$vars['DSE']['HTTP_ERROR_LOG_FILE']="/var/log/httpd/error_log";
$vars['DSE']['HTTP_REQUEST_LOG_FILE']="/var/log/httpd/access_log";

$vars['DSE']['USER_HOME_DIR']="~";
if(dse_is_osx()){
	$vars['DSE']['USER_BASH_PROFILE']=$vars['DSE']['USER_HOME_DIR']."/.bash_profile";
}elseif(dse_is_ubuntu()){
	$vars['DSE']['USER_BASH_PROFILE']=$vars['DSE']['USER_HOME_DIR']."/.bashrc";
}elseif(dse_is_centos()){
	$vars['DSE']['USER_BASH_PROFILE']=$vars['DSE']['USER_HOME_DIR']."/.bash_profile";
}else{
	$vars['DSE']['USER_BASH_PROFILE']=$vars['DSE']['USER_HOME_DIR']."/.bash_profile";
}

$vars['DSE']['SYSTEM_USER_FILE']='/etc/passwd';
$vars['DSE']['SYSTEM_GROUP_FILE']='/etc/group';
$vars['DSE']['SYSTEM_BASHRC_FILE']='/etc/bashrc';
$vars['DSE']['SYSTEM_PROFILE_FILE']='/etc/profile';
$vars['DSE']['SYSTEM_APT_SOURCES_LIST']='/etc/apt/sources.list';


$vars['DSE']['SYSTEM_PHP_CLI_INI_FILE']='/etc/php.ini.default';

$vars['DSE']['SYSTEM_ROOT_FILE_USER']='root';
$vars['DSE']['SYSTEM_ROOT_FILE_GROUP']='root';



if(dse_is_osx()){
	$vars['DSE']['LGT_LOG_FILES']="/var/log/system.log,/var/log/kernel.log,/var/log/windowserver.log" 
		.",/var/log/install.log,/var/log/mail.log,/var/log/ppp.log,/var/log/secure.log,/var/log/appfirewall.log";
		//,/var/log/mount.log
}elseif(dse_is_ubuntu()){
	$vars['DSE']['LGT_LOG_FILES']="/var/log/syslog,/var/log/kern.log,/var/log/daemon.log,/var/log/messages";
}elseif(dse_is_centos()){
	$vars['DSE']['LGT_LOG_FILES']="/var/log/secure,/var/log/kernel,/var/log/messages,/var/log/maillog";
	//,/var/log/dmesg
}else{
	$vars['DSE']['LGT_LOG_FILES']="/var/log/messages";
}


$vars['DSE']['LGT_LOG_FILES'].=",/var/log/vibk.log,/var/log/dse_publisher.log,/var/log/dse/dse.log";
//,/var/log/iptable_drops.log
// *********************************************************************************
$vars['DSE']['SUGGESTED']=array();
$vars['DSE']['SUGGESTED']['HISTFILESIZE']=100000;

// *********************************************************************************
// ********* Set colors and such
$vars['DSE']['USE_ANSI_COLOR']="YES";
$vars['DSE']['SHELL_FORGROUND']="white";
$vars['DSE']['SHELL_BACKGROUND']="black";
$vars['DSE']['OUTPUT_FORMAT']="TXT";

// ********* http_stress Settings
$vars['DSE']['HTTP_STRESS_CONFIG_FILE']=$vars['DSE']['DSE_CONFIG_DIR']."/"."http_stress.conf";
$vars['DSE']['HTTP_STRESS_INPUT_URLS_FILE']=$vars['DSE']['DSE_CONFIG_DIR']."/"."http_stress/urls.conf";
$vars['DSE']['HTTP_STRESS_LOG_FILE']=$vars['DSE']['DSE_LOG_DIR']."/"."http_stress.log";
$vars['DSE']['HTTP_STRESS_THREAD_LOG_FILE']="/tmp/dse_http_stress_thread.log";
$vars['DSE']['HTTP_STRESS_DEFAULT_THREADS']=5;
$vars['DSE']['HTTP_STRESS_DEFAULT_RUNLENGTH']=30;
// Set $vars['DSE']['HTTPD_LOG_FILE'] in dse.conf w/ HTTPD_LOG_FILE=

// ********* dlb Settings
$vars['DSE']['DLB_CONFIG_FILE']=$vars['DSE']['DSE_CONFIG_DIR']."/"."dlb.conf";

// ********* dsm Settings
$vars['DSE']['DSM_CONFIG_FILE']=$vars['DSE']['DSE_CONFIG_DIR']."/"."dsm.conf";

// ********* dab Settings
$vars['DSE']['DAB_CONFIG_FILE']=$vars['DSE']['DSE_CONFIG_DIR']."/"."dab.conf";

// ********* vibk Settings
$vars['DSE']['DSE_VIBK_BACKUP_DIRECTORY']=$vars['DSE']['DSE_BACKUP_DIR']."/vibk_changed_files";
$vars['DSE']['DSE_VIBK_LOG_FILE']=$vars['DSE']['DSE_LOG_DIR']."/vibk.log";

// ********* panic Settings
$vars['DSE']['PANIC_CONFIG_FILE']=$vars['DSE']['DSE_CONFIG_DIR']."/"."panic.conf";




// ********* IP throttle Settings
$vars['DSE']['DSE_IPTHROTTLE_WHITELIST_FILE']=$vars['DSE']['DSE_CONFIG_DIR']."/ips_whitelist.txt";
$vars['DSE']['DSE_IPTHROTTLE_DROPLIST_FILE']=$vars['DSE']['DSE_CONFIG_DIR']."/ips_droplist.txt";
$vars['DSE']['DSE_IPTHROTTLE_LOG_DIRECTORY']=$vars['DSE']['DSE_LOG_DIR']."/ip_throttle";
$vars['DSE']['DSE_IPTHROTTLE_BANNED_FILE']=$vars['DSE']['DSE_CONFIG_DIR']."/ips_banned.txt";
$vars['DSE']['DSE_IPTHROTTLE_KONT_FILE']=$vars['DSE']['DSE_CONFIG_DIR']."/ips_kont.txt";
$vars['DSE']['DSE_WEB_INTERFACE_APACHE2_FILE']=$vars['DSE']['DSE_CONFIG_DIR']."/apache2.conf";


$vars['DSE']['RedWords']=array("no such"," no ",")no ","!","ot found","is not","could not open","could not","not","false","error","illegal","warning","unexpected","empty","failure","failed","aborted","denied","problem","exhausted"
	,"invalid","segfault","crash","denied","disconnected","POSSIBLE BREAK-IN ATTEMPT","BREAK-IN","ATTEMPT","isn't","bounce","sorry"," 403 "," 404 "); 
	for($e=500;$e<540;$e++){
		$vars['DSE']['RedWords'][]=" $e ";
	}
$vars['DSE']['GreenWords']=array(" ok ","granted","uo to date","done","accepted","true","succeeded","success","freeing","cleaned up"
	,"established","disconnected by user"," 200 ","\"GET ","New connection from"); 
$vars['DSE']['BlueWords']=array("https","sftp","imaps","http","httpd","xinetd","inetd","ftp","ftpd","imap","ssh","sshd","samba","qmail","mail","smtp","mysql","mysqld"
	,"apache","crowbar","vncserver","vnc"); 
$vars['DSE']['CyanWords']=array("127.0.0.1","localhost",dse_hostname(),"port","protocol");
$vars['DSE']['MagentaWords']=array("root","permission","sudo","admin");
$vars['DSE']['YellowWords']=array("status","result","permission","login","logout","user","start","exit","stop","started","stopped","info"
	,"removed","configuration","config","version","disabled","message"); 


$vars['DSE']['ComponentsAvailable']=array("image-processing","desktop","tor","xurlrunner");
$vars['DSE']['AddComponents']=array();
$vars['DSE']['DisabledComponents']=array();

// *********************************************************************************
// *********************************************************************************
// *********************************************************************************
// ********* Now, we WANT to overwrite these program defaults with the dse.conf file!
// ********* Now, we WANT to overwrite these program defaults with the dse.conf file!
// ********* Now, we WANT to overwrite these program defaults with the dse.conf file!
// *********************************************************************************
$vars['DSE']=dse_read_config_file($vars['DSE']['DSE_CONFIG_FILE_GLOBAL'],$vars['DSE'],TRUE);



$vars['DSE']['SERVICE_NICKNAMES']=array();
if(str_contains($vars['DSE']['SERVICES'],"http")){
	if(dse_which("apache2")) $httpd="apache2";
		elseif(dse_which("apache")) $httpd="apache";
		elseif(dse_which("httpd")) $httpd="httpd";
		elseif(dse_which("http")) $httpd="http";
		else $httpd="httpd";
	$vars['DSE']['SERVICE_NICKNAMES']["apache2"]=$httpd;
	$vars['DSE']['SERVICE_NICKNAMES']["http"]=$httpd;
	$vars['DSE']['SERVICE_NICKNAMES']["httpd"]=$httpd;
	$vars['DSE']['SERVICE_NICKNAMES']["apache"]=$httpd;
	$vars['DSE']['SERVICE_NICKNAMES']["web"]=$httpd;
	$vars['DSE']['SERVICE_NICKNAMES']["www"]=$httpd;
}
if(str_contains($vars['DSE']['SERVICES'],"mysql")){
	if(dse_which("mysqld")) $mysqld="mysqld";
		elseif(dse_which("mysql")) $mysqld="mysql";
		else $mysqld="mysql";
	$vars['DSE']['SERVICE_NICKNAMES']["mysqld"]=$mysqld;
	$vars['DSE']['SERVICE_NICKNAMES']["mysql"]=$mysqld;
	$vars['DSE']['SERVICE_NICKNAMES']["db"]=$mysqld;
}
if(str_contains($vars['DSE']['SERVICES'],"dns")){
	if(dse_which("named")) $dnsd="named";
		elseif(dse_which("bind")) $dnsd="bind";
		elseif(dse_which("bind9")) $dnsd="bind9";
		else $dnsd="named";
	$vars['DSE']['SERVICE_NICKNAMES']["dns"]=$dnsd;
	$vars['DSE']['SERVICE_NICKNAMES']["named"]=$dnsd;
	$vars['DSE']['SERVICE_NICKNAMES']["bind"]=$dnsd;
	$vars['DSE']['SERVICE_NICKNAMES']["bind9"]=$dnsd;
}

//$vars['DSE']['SERVICE_PORTS'][1]="dns";

$vars['DSE']['SERVICE_PORTS'][22]="ssh";
$vars['DSE']['SERVICE_PORTS'][80]="http";
$vars['DSE']['SERVICE_PORTS'][443]="https";
$vars['DSE']['SERVICE_PORTS'][5900]="vnc";
$vars['DSE']['SERVICE_PORTS'][6000]="vnc_2";
$vars['DSE']['SERVICE_PORTS'][7907]="dwi";
$vars['DSE']['SERVICE_PORTS'][9980]="aptana";
$vars['DSE']['SERVICE_PORTS'][10000]="crowbar";
//polipo
//tor


// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******

$vars['DSE']['SYSTEM_ROOT_FILE_USER:GROUP']=$vars['DSE']['SYSTEM_ROOT_FILE_USER'] . ":" .$vars['DSE']['SYSTEM_ROOT_FILE_GROUP'];


putenv ("DSE_GIT_ROOT=".$vars['DSE']['DSE_GIT_ROOT']);
putenv ("DSE_MYSQL_CONF_FILE=".$vars['DSE']['MYSQL_CONF_FILE']);
putenv ("DSE_MYSQL_LOG_FILE=".$vars['DSE']['MYSQL_LOG_FILE']);




// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******


?>