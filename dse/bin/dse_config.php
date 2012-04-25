<?

if(!$vars['DSE']){
	$vars['DSE']=array();
}

$vars['DSE']['DSE_ROOT']=getenv("DSE_ROOT");

$vars['DSE']['DSE_CONFIG_DIR']="/etc/dse";


$vars['DSE']['DSE_CONFIG_FILE_GLOBAL']=$vars['DSE']['DSE_CONFIG_DIR']."/"."dse.conf";

$vars['DSE']=dse_read_config_file($vars['DSE']['DSE_CONFIG_FILE_GLOBAL'],$vars['DSE'],TRUE);


$vars['DSE']['DSE_HTTP_STRESS_CONFIG_FILE']=$vars['DSE']['DSE_CONFIG_DIR']."/"."http_stress.conf";





?>