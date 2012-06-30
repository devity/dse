<?php
ini_set('display_errors','On');
ini_set('display_startup_errors','On');
ini_set('log_errors','On');
error_reporting( (E_ALL & ~E_NOTICE) ^ E_DEPRECATED);
$DSE_ROOT="/dse";
include_once ("$DSE_ROOT/include/web_config.php");
include_once ("$DSE_ROOT/include/dwi_functions.php");
dse_print_page_header();

print "<br><center><b class='f10pt'>Welcome to Devity Server Environment's Web Interface!</b></center><br><br>";


print "<br><b class='f10pt'>Sections:</b><br>";

print " * <a href=/code_explorer/>Code Explorer</a><br>";



print "<br><hr>";

$PageType=$_REQUEST['PageType'];
switch($PageType){
	case 'pidinfo':
		$PID=$_REQUEST['PageType'];
		print dse_pid_get_info_str($PID,TRUE);
		break;
	case 'Overview':
	default:
		dse_dwi_overview();
		break;
}



	
dse_print_page_footer();
?>
