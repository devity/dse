#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
$vars['Verbosity']=0;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="DSE Shell Text utilities";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="colorize, etc";
$vars['DSE']['DSE_DSE_VERSION']="v0.01b";
$vars['DSE']['DSE_DSE_VERSION_DATE']="2012/06/22";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

$parameters_details = array(
  array('h','help',"this message"),
  array('q','quiet',"same as --verbosity 0"),
  array('v:','verbosity:',"0=none 1=some 2=more 3=debug"),
  array('t','test-colors',"telst all color possibilites and show"),
  array('b:','background:',"set background color = arg"),
  array('f:','forground:',"set forground color = arg"),
  array('','foreground:',"set forground color = arg"),
  array('n','no-new-line',"does not end any prints with a \\n"),
  array('p:','print:',"prints arg"),
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
  	case 'v':
	case 'verbosity':
		$vars['Verbosity']=$vars['options'][$opt];
		if($vars['Verbosity']>=2) print "Verbosity set to ".$vars['Verbosity']."\n";
		break;
	case 'h':
  	case 'help':
  		dse_cli_script_header();
		print $vars['Usage'];
		break;
	case 't':
  	case 'test-colors':
		test_all_shell_colors();
		break;
	case 'n':
  	case 'no-new-line':
		$NoNewLines=TRUE;
		break;
	case 'b':
  	case 'background':
		print setBackgroundColor($vars['options'][$opt]);
		$DidSomething=TRUE;
		break;
	case 'f':
  	case 'forground':
  	case 'foreground':
		print setForgroundColor($vars['options'][$opt]);
		$DidSomething=TRUE;
		break;
	case 'p':
  	case 'print':
		if($vars['options'][$opt]) print $vars['options'][$opt];
		if(!$NoNewLines) print "\n";
		$DidSomething=TRUE;
		break;
}
exit(0);
?>
