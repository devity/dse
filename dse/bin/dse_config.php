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
$StartTime=time()+microtime();
$StartLoad=get_load();

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

$vars['DSE']['SYSTEM_SCRIPTS_DIR']='/scripts';

$vars['DSE']['SYSTEM_USER_FILE']='/etc/passwd';
$vars['DSE']['SYSTEM_GROUP_FILE']='/etc/group';
$vars['DSE']['SYSTEM_BASHRC_FILE']='/etc/bashrc';

$vars['DSE']['SYSTEM_ROOT_FILE_USER']='root';
$vars['DSE']['SYSTEM_ROOT_FILE_GROUP']='root';

// *********************************************************************************
$vars['DSE']['SUGGESTED']=array();
$vars['DSE']['SUGGESTED']['HISTFILESIZE']=100000;

// *********************************************************************************
// ********* Set colors and such
$vars['DSE']['USE_ANSI_COLOR']="YES";
$vars['DSE']['shell_colors_reset_foreground']="white";
$vars['DSE']['shell_colors_reset_background']="black";

// ********* http_stress Settings
$vars['DSE']['HTTP_STRESS_CONFIG_FILE']=$vars['DSE']['DSE_CONFIG_DIR']."/"."http_stress.conf";
$vars['DSE']['HTTP_STRESS_INPUT_URLS_FILE']=$vars['DSE']['DSE_CONFIG_DIR']."/"."http_stress/urls.conf";
$vars['DSE']['HTTP_STRESS_LOG_FILE']=$vars['DSE']['DSE_LOG_DIR']."/"."http_stress.log";
$vars['DSE']['HTTP_STRESS_THREAD_LOG_FILE']="/tmp/dse_http_stress_thread.log";
$vars['DSE']['HTTP_STRESS_DEFAULT_THREADS']=5;
$vars['DSE']['HTTP_STRESS_DEFAULT_RUNLENGTH']=30;
// Set $vars['DSE']['HTTPD_LOG_FILE'] in dse.conf w/ HTTPD_LOG_FILE=

// ********* dsm Settings
$vars['DSE']['DSM_CONFIG_FILE']=$vars['DSE']['DSE_CONFIG_DIR']."/"."dsm.conf";

// ********* dab Settings
$vars['DSE']['DAB_CONFIG_FILE']=$vars['DSE']['DSE_CONFIG_DIR']."/"."dab.conf";

// ********* vibk Settings
$vars['DSE']['DSE_VIBK_BACKUP_DIRECTORY']=$vars['DSE']['DSE_BACKUP_DIR']."/vibk_changed_files";
$vars['DSE']['DSE_VIBK_LOG_FILE']=$vars['DSE']['DSE_LOG_DIR']."/vibk.log";

// ********* IP throttle Settings
$vars['DSE']['DSE_IPTHROTTLE_WHITELIST_FILE']=$vars['DSE']['DSE_CONFIG_DIR']."/ips_whitelist.txt";
$vars['DSE']['DSE_IPTHROTTLE_DROPLIST_FILE']=$vars['DSE']['DSE_CONFIG_DIR']."/ips_droplist.txt";
$vars['DSE']['DSE_IPTHROTTLE_LOG_DIRECTORY']=$vars['DSE']['DSE_LOG_DIR']."/ip_throttle";
$vars['DSE']['DSE_IPTHROTTLE_BANNED_FILE']=$vars['DSE']['DSE_CONFIG_DIR']."/ips_banned.txt";
$vars['DSE']['DSE_IPTHROTTLE_KONT_FILE']=$vars['DSE']['DSE_CONFIG_DIR']."/ips_kont.txt";




// *********************************************************************************
// *********************************************************************************
// *********************************************************************************
// ********* Now, we WANT to overwrite these program defaults with the dse.conf file!
// ********* Now, we WANT to overwrite these program defaults with the dse.conf file!
// ********* Now, we WANT to overwrite these program defaults with the dse.conf file!
// *********************************************************************************
$vars['DSE']=dse_read_config_file($vars['DSE']['DSE_CONFIG_FILE_GLOBAL'],$vars['DSE'],TRUE);

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******

$vars['DSE']['SYSTEM_ROOT_FILE_USER:GROUP']=$vars['DSE']['SYSTEM_ROOT_FILE_USER'] . ":" .$vars['DSE']['SYSTEM_ROOT_FILE_GROUP'];


putenv ("DSE_GIT_ROOT=".$vars['DSE']['DSE_GIT_ROOT']);
putenv ("DSE_MYSQL_CONF_FILE=".$vars['DSE']['MYSQL_CONF_FILE']);
putenv ("DSE_MYSQL_LOG_FILE=".$vars['DSE']['MYSQL_LOG_FILE']);




// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******


?>