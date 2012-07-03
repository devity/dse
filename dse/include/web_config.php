<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
ini_set("memory_limit","10000M");
include_once ("$DSE_ROOT/bin/dse_cli_functions.php");
include_once ("$DSE_ROOT/bin/dse_config.php");
include_once ("$DSE_ROOT/include/web_functions.php");
global $debug_tostring_output_txt; 	$debug_tostring_output_txt=FALSE;
$vars['DSE']['OUTPUT_FORMAT']="HTML";
register_shutdown_function('on_shutdown');
$vars[dse_enable_debug_code]=TRUE;

?>