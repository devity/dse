#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
$vars['Verbosity']=1;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="DSE Load Balancer";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="basic service monitoring and load balancing";
$vars['DSE']['DSE_DSE_VERSION']="v0.01b";
$vars['DSE']['DSE_DSE_VERSION_DATE']="2012/06/23";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

global $CFG_array;
$CFG_array=array();
//$CFG_array['']=0;
//$CFG_array=dse_read_config_file($vars['DSE']['DLB_CONFIG_FILE'],$CFG_array);	

$parameters_details = array(
 
  array('h','help',"this message"),
  array('v:','verbosity:',"0=none 1=some 2=more 3=debug"),
  array('e','edit',"backs up and launches a vim of arg1 "),
  array('u','remove-duplicate-lines',"remove duplicate lines"),
  array('b','remove-blank-lines',"remove blank lines"),
  array('m:','mid:',"returns tail -1 arg1 | head -n arg2"),
  array('','number',"adds a incrementing line number to start of each line"),
);
$vars['parameters']=dse_cli_get_paramaters_array($parameters_details);
$vars['Usage']=dse_cli_get_usage($parameters_details);
$vars['argv_origional']=$argv;
dse_cli_script_start();
		
$BackupBeforeUpdate=TRUE;
foreach (array_keys($vars['options']) as $opt) switch ($opt) {
  	case 'v':
	case 'verbosity':
		$vars['Verbosity']=$vars['options'][$opt];
		dpv(2,"Verbosity set to ".$vars['Verbosity']."\n");
		break;
	case 'h':
  	case 'help':
		print $vars['Usage'];
		exit(0);
	case 'u':
	case 'remove-duplicate-lines':
		$DoRemoveDuplicateLines=TRUE;
		break;
	case 'b':
	case 'remove-blank-lines':
		$DoRemoveBlankLines=TRUE;
		break;
	case 'e':
	case 'edit':
		$DoFileEdit=TRUE;
		break;
	case 'm':
	case 'mid':
		$MidOptions=$vars['options'][$opt];
		$DoFileMid=TRUE;
		break;
	case 'number':
		$DoNumber=TRUE;
		break;
}
if(sizeof($argv)<2){
	//dpv(0,"no file argument!");
	//exit(1);
	$UseSTDIN=TRUE;
}
$File=$argv[1];
dpv(4,"parsed args file=$File");
	
if($DoNumber){
	$Message="dfi numbering lines in $File :\n";
	dpv(1,$Message);
	dse_log($Message);
	$String=dse_file_get_contents($File);
	$Line=0;
	$out=array();
	foreach(split("\n",$String) as $Line){
		$i++;
		print "$i\t$Line\n";
	}
	exit(0);
}
if($DoFileMid){
	dpv(4,"in DoFileMid");
	if(str_contains($MidOptions,"-")){
		list($Start,$End)=split("\\-",$MidOptions);
		$Count=$End-$Start;
	}elseif(str_contains($MidOptions,"+")){
		list($Start,$Count)=split("\\+",$MidOptions);
		$End=$Start+$Count;
	}
	
	dpv(4,"s=$Start e=$End c=$Count");
	$Total=file_count_lines($File);
	dpv(4,"t=$Total");
	$Message="dfi mid $MidOptions of $File - lines $Start to $End - $Count Lines. file has $Total total:\n";
	dpv(1,$Message);
	dse_log($Message);
	$TailNumber=($Total-$Start)-1;
	dpv(4,"tn=$TailNumber t=$Total start=$Start ");
	if($UseSTDIN){
		$Contents=dse_get_stdin();
		dpv(4,"Contents=$Contents");
		$ContentsArray=split("\n",$Contents);
		for($Li=1;$Li<$Start;$Li++){
			print "$Li:.: ".$ContentsArray[$Li]."\n";
		}
		
		for($Li=$End-1;$Li<=$Total;$Li++){
			print "$Li:.: ".$ContentsArray[$Li]."\n";
		}
	
	}else{
		$HeadCount=$Count+1;
		print dse_exec("tail -n $TailNumber \"$File\" | head  -n $HeadCount",$vars['Verbosity']>3);
	}
	exit(0);
}

if($DoFileEdit){
	$Message="dfi Backing up $File and launcing in vim:\n";
	dpv(1,$Message);
	dse_log($Message);
	passthru("/dse/bin/vibk \"$File\" 2>&1");
	exit(0);
}

if($DoRemoveDuplicateLines){
	$Message="dfi removing duplicate lines from $File :\n";
	dpv(1,$Message);
	dse_log($Message);
	$Contents=dse_file_get_contents($File);
	$Contents=remove_duplicate_lines($Contents);
	dse_file_set_contents($File,$Contents);
	exit(0);
}

if($DoRemoveBlankLines){
	$Message="dfi removing blank lines from $File :\n";
	dpv(1,$Message);
	dse_log($Message);
	$Contents=dse_file_get_contents($File);
	$Contents=remove_blank_lines($Contents);
	dse_file_set_contents($File,$Contents);
	exit(0);
}


dse_cli_script_header();


if($DidSomething){
	if(!$Quiet && !$DoSetEnv){
		dpv(1, getColoredString($vars['DSE']['SCRIPT_NAME']." Done. Exiting (0)","black","green"));
		$vars['shell_colors_reset_foreground']='';	print getColoredString("\n","white","black");
	}
	if(!$NoExit) exit(0);
}else{
	if(!$Quiet && !$DoSetEnv){
		dpv(1, getColoredString("Nothing to do! try --help for usage. ".$vars['DSE']['SCRIPT_NAME']." Done. Exiting (-1)","pink","black"));
		$vars['shell_colors_reset_foreground']='';	print getColoredString("\n","white","black");
	}
	if(!$NoExit) exit(-1);
}


exit(0);

 
?>
