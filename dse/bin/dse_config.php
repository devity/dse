<?
$StartTime=time()+microtime();
$StartLoad=get_load();

if(!$vars['DSE']){
	$vars['DSE']=array();
}

$vars['DSE']['DSE_ROOT']="/dse"; //=getenv("DSE_ROOT");
$vars['DSE']['DSE_BIN']=$vars['DSE']['DSE_ROOT']."/bin";

$vars['DSE']['DSE_CONFIG_DIR']="/etc/dse";

$vars['DSE']['DSE_LOG_DIR']="/var/log/dse";

$vars['DSE']['DSE_BACKUP_DIR']="/backup";
$vars['DSE']['DSE_BACKUP_DIR_DSE']=$vars['DSE']['DSE_BACKUP_DIR']."/dse";

$vars['DSE']['DSE_HTTP_STRESS_CONFIG_FILE']=$vars['DSE']['DSE_CONFIG_DIR']."/"."http_stress.conf";
$vars['DSE']['DSE_HTTP_STRESS_INPUT_URLS_FILE']=$vars['DSE']['DSE_CONFIG_DIR']."/"."http_stress/urls.conf";
$vars['DSE']['DSE_HTTP_STRESS_LOG_FILE']=$vars['DSE']['DSE_LOG_DIR']."/"."http_stress.log";
$vars['DSE']['DSE_HTTP_STRESS_THREAD_LOG_FILE']="/tmp/dse_http_stress_thread.log";

$vars['DSE']['DSE_DSM_CONFIG_FILE']=$vars['DSE']['DSE_CONFIG_DIR']."/"."dsm.cfg";

$vars['DSE']['DSE_CONFIG_FILE_GLOBAL']=$vars['DSE']['DSE_CONFIG_DIR']."/"."dse.conf";
$vars['DSE']=dse_read_config_file($vars['DSE']['DSE_CONFIG_FILE_GLOBAL'],$vars['DSE'],TRUE);

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['DSE_VERSION']="v0.01b";
$vars['DSE']['DSE_VERSION_DATE']="2012/04/30";
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

$vars[shell_colors_reset_foreground]='light_grey';
$vars[shell_colors_reset_background]='black';

?>