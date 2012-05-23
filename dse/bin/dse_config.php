<?
$vars['DSE']['DSE_VERSION']="v0.03";
$vars['DSE']['DSE_VERSION_STATUS']="alpha";
$vars['DSE']['DSE_VERSION_DATE']="2012/05/22";
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

if(getenv("DSE_LOG_DIR")!=""){
	$vars['DSE']['DSE_LOG_DIR']=getenv("DSE_LOG_DIR");
}else{
	$vars['DSE']['DSE_LOG_DIR']="/var/log/dse";
}

if(getenv("DSE_BACKUP_DIR")!=""){
	$vars['DSE']['DSE_BACKUP_DIR']=getenv("DSE_BACKUP_DIR");
}else{
	$vars['DSE']['DSE_BACKUP_DIR']="/backup";
}
$vars['DSE']['DSE_BACKUP_DIR_DSE']=$vars['DSE']['DSE_BACKUP_DIR']."/dse";


$vars['DSE']['MYSQL_CONF_FILE']="/etc/my.cnf";
$vars['DSE']['MYSQL_LOG_FILE']="/tmp/mysql_query.log";


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





// *********************************************************************************
// *********************************************************************************
// *********************************************************************************
// ********* Now, we WANT to overwrite these program defaults with the dse.conf file!
// ********* Now, we WANT to overwrite these program defaults with the dse.conf file!
// ********* Now, we WANT to overwrite these program defaults with the dse.conf file!
// *********************************************************************************
$vars['DSE']['DSE_CONFIG_FILE_GLOBAL']=$vars['DSE']['DSE_CONFIG_DIR']."/"."dse.conf";
$vars['DSE']=dse_read_config_file($vars['DSE']['DSE_CONFIG_FILE_GLOBAL'],$vars['DSE'],TRUE);

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******


putenv ("DSE_GIT_ROOT=".$vars['DSE']['DSE_GIT_ROOT']);
putenv ("DSE_MYSQL_CONF_FILE=".$vars['DSE']['MYSQL_CONF_FILE']);
putenv ("DSE_MYSQL_LOG_FILE=".$vars['DSE']['MYSQL_LOG_FILE']);

// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******


?>