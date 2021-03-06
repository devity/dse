#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
//$vars[dse_enable_debug_code]=TRUE; $vars['Verbosity']=6;
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
$vars['Verbosity']=0;
$vars['ScriptHeaderShow']=TRUE;
$ShowCommand=TRUE;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="GSS - Grep Search String";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="grep tailored for server admin manual usage, color, launch files, etc";
$vars['DSE']['SCRIPT_VERSION']="v0.06b";
$vars['DSE']['SCRIPT_VERSION_DATE']="2014/01/27";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
$vars['DSE']['SCRIPT_COMMAND_FORMAT']="grep_string [start_directory]";
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

 


$parameters_details = array(
   	array('h','help',"this message"),
  	array('v:','verbosity:',"0=none 1=some 2=more 3=debug"),
  	array('q','quiet',"same as --verbosity 0"),
  	array('W','html-output',"uses html color codes for output. else terminal codes."),
  	array('r','search-results',"output mode of search results"),  	
  	array('w','web-files',"only greps: .htm(l) php phtml js txt"),
 	array('g','grep',"default action; greps for arg1 in arg2"),
 	array('U','no-sudo',"runs as user, no sudo"),
 	 

 );
$vars['parameters']=dse_cli_get_paramaters_array($parameters_details);
$vars['Usage']=dse_cli_get_usage($parameters_details);
$vars['argv_origional']=$argv;
$NoSudo=FALSE;

dse_cli_script_start();

foreach (array_keys($vars['options']) as $opt) switch ($opt) {
	case 'W':
	case 'html-output':
		$vars['DSE']['OUTPUT_FORMAT']="HTML";
		break;
	case 'q':
	case 'quiet':
		$Quiet=TRUE;
		$vars['ScriptHeaderShow']=FALSE;
		$vars['Verbosity']=0;
		break;
  	case 'v':
	case 'verbosity':
		$vars['Verbosity']=$vars['options'][$opt];
		dpv(2,"Verbosity set to ".$vars['Verbosity']."\n");
		break;
  	case 'U':
	case 'no-sudo':
		$NoSudo=TRUE;
		break;
		
}
 
 

	
$DoGrep=TRUE;
foreach (array_keys($vars['options']) as $opt) switch ($opt) {
	case 'q':
	case 'quiet':
		$Quiet=TRUE;
		$vars['ScriptHeaderShow']=FALSE;
		$vars['Verbosity']=0;
		break;
  	case 'v':
	case 'verbosity':
		$vars['Verbosity']=$vars['options'][$opt];
		dpv(2,"Verbosity set to ".$vars['Verbosity']."\n");
		break;
	case 'h':
  	case 'help':
		print $vars['Usage'];
		exit(0);
	case 'r':
	case 'search-results':
		$OutputAsSearchResults=TRUE;
		$ShowCommand=FALSE;
		$vars['Verbosity']=0;
		break;
	case 'w':
	case 'web-files':
		$OnlyWebFiles=TRUE;		
		break;
}

//print "\n\n";
if(!$OutputAsSearchResults){
	dse_cli_script_header();
} 
 
foreach (array_keys($vars['options']) as $opt) switch ($opt) {
  	
  	case 'd':
	case 'daemon':
		break;
  	case 'g':
	case 'grep':
		$DoGrep=TRUE;
		break;
}




//print "Searching for: '$String' in $d\n";
if($DoGrep || (!$DidSomething)  ){
	$String=$argv[1];
	if(sizeof($argv)>2){
		$d=$argv[2];
	}else{
		$d="/";
	}
		 
		
	if(is_dir($d)){
		$IsDir=TRUE;
		$ShowFileName=TRUE;
	}else{ 
		print "File: ".colorize("$d","cyan","black")."\n";
	}	
	
	$String=str_replace('\\','\\\\',$String);
	$fileTypes="";
	if($OnlyWebFiles){
		$fileTypes.="--include=\"*.htm\" ";
		$fileTypes.="--include=\"*.html\" ";
		$fileTypes.="--include=\"*.phtml\" ";
		$fileTypes.="--include=\"*.php\" ";
		$fileTypes.="--include=\"*.inc\" ";
		$fileTypes.="--include=\"*.js\" ";
		$fileTypes.="--include=\"*.asp\" ";
		$fileTypes.="--include=\"*.txt\" ";		
	}
	$find_cmd="grep $fileTypes -i -n -R --with-filename \"$String\" $d 2>/dev/null";
	if(!$NoSudo){		
		$find_cmd="sudo ".$find_cmd;
	}
	$out=dse_exec($find_cmd,$ShowCommand||$vars[Verbosity]>=2); 
	//$out=dse_exec($find_cmd,TRUE,TRUE);
	foreach(split("\n",$out) as $L){
		$L=trim($L);
		if($L){
		//	print "L=$L \n"; 
			$Li++;
			$FileName=strcut($L,"",":");
			//$FileName=str_replace($d,"",$FileName);
			$L=strcut($L,":");
			$LineNumber=strcut($L,"",":");
			$Line=strcut($L,":");
			print colorize("$Li","yellow","black");
			if($ShowFileName){
				print colorize(": ","cyan","black");
				print colorize("$FileName","cyan","black");
			}
			print colorize("::","yellow","black");
			print colorize($LineNumber,"green","black");
			$Line=str_replace($String,colorize($String,"black","yellow"),$Line);
			
			print $Line ."\n";
		}
	}
	$DidSomething=TRUE;
}






exit(0);
?>
