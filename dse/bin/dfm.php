#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
include_once ("/dse/include/system_stat_functions.php");
$vars['Verbosity']=1;
ini_set("memory_limit","-1");

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="DSE File Manipulator";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="basic service monitoring and load balancing";
$vars['DSE']['SCRIPT_VERSION']="v0.01b";
$vars['DSE']['SCRIPT_VERSION_DATE']="2012/06/23";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
$vars['DSE']['SCRIPT_COMMAND_FORMAT']="";
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

global $CFG_array;
$CFG_array=array();
//$CFG_array['']=0;
//$CFG_array=dse_read_config_file($vars['DSE']['DLB_CONFIG_FILE'],$CFG_array);	

$parameters_details = array(
 
 //abcdef
  array('c','compare-directories',"compare-directories arg1 to arg2 or if no arg2, pwd"),
  array('h','help',"this message"),
  array('v:','verbosity:',"0=none 1=some 2=more 3=debug"),
  array('e','edit',"backs up and launches a vim of arg1 "),
  array('u','remove-duplicate-lines',"remove duplicate lines"),
  array('b','remove-blank-lines',"remove blank lines"),
  array('m:','mid:',"returns tail|head. format: --mid start-end file  or --mid start+count file"),
  array('q:','line-with-string:',"returns line number of line in file arg2 w string arg1"),
  array('n','number',"adds a incrementing line number to start of each line"),
  array('l','find-large-files',"finds larges files in arg1"),
  array('y','empty',"empties file arg1"),
  array('w','launch-url',"launch url arg1"),
  array('z','launch-vibk-edit',"launch vibk edit of file arg1"),
  array('x','launch-code-edit',"launch code edit of file arg1"),
  array('s','shrink',"try to compress file arg1 more"),
 // array('','tree',"show dir as a tree"),
  array('i','ls',"a colorfull and more info version of ls"),
  array('d','df',"colorized version of df"),
  array('a','dir-sizes',"get dir sizes in ls"),
  array('g','get',"gets file arg2 from user@host arg1"),
  array('r','sync',"rsyncs to file arg3 from file arg2 on user@host arg1"),
  array('f:','run-command-batch:',"takes list of commands in fine arg1 and DELETES and executes one at a time"),
  
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
}

if($vars['Verbosity']>4){
	$vars[dse_enable_debug_code]=TRUE;
}

dse_cli_script_header();



foreach (array_keys($vars['options']) as $opt) switch ($opt) {
	case 'a':
	case 'dir-sizes':
		$vars['dse_dfm_do_dir_sizes']=TRUE;
		break;
}
		
foreach (array_keys($vars['options']) as $opt) switch ($opt) {
	case 'h':
  	case 'help':
		print $vars['Usage'];
		$DidSomething=TRUE;
		exit(0);
	case 'u':
	case 'remove-duplicate-lines':
		$DoRemoveDuplicateLines=TRUE;
		$DidSomething=TRUE;
		break;
	case 'b':
	case 'remove-blank-lines':
		dpv(3,"DoRemoveBlankLines=TRUE");
		$DoRemoveBlankLines=TRUE;
		$DidSomething=TRUE;
		break;
	case 'e':
	case 'edit':
		$DoFileEdit=TRUE;
		$DidSomething=TRUE;
		break;
	case 'm':
	case 'mid':
		$MidOptions=$vars['options'][$opt];
		$DoFileMid=TRUE;
		$DidSomething=TRUE;
		break;
	case 'q':
	case 'line-with-string':
		$String=$vars['options'][$opt];
		$DoLineWithString=TRUE;
		$DidSomething=TRUE;
		break;
	case 'number':
		$DoNumber=TRUE;
		$DidSomething=TRUE;
		break;
	case 'find-large-files':
		$RootDir=$argv[1];
		$Limit=$argv[2];
		$DoLargeFileFind=TRUE;
		$DidSomething=TRUE;
		break;
	case 'empty':
		$DoFileEmpty=TRUE;
		$DidSomething=TRUE;
		break;
	case 'launch-url':
		$URL=$argv[1];
		exit(dse_launch_url($URL));
	case 'launch-vibk-edit':
		$File=$argv[1];
		exit (dse_launch_vibk_edit($File));
	case 'launch-code-edit':
		$File=$argv[1];
		exit(dse_launch_code_edit($File));
	case 'g':
	case 'get':
		//print_r($vars[DSE][USERHOST]); exit();
		$UserHost=$argv[1];
		if(str_contains($UserHost,"@")){
			$User=strcut($UserHost,"","@");
			$Host=strcut($UserHost,"@");
		}else{
			print "invalid user@host in arg1. exiting.\n";
			exit(1);
		}
		$File=$argv[2];
		$Dir=dirname($File);
		$FileName=basename($File);
		if($Dir=="" || $File==$FileName){
			$Dir=getcwd();
			$File=$Dir."/".$FileName;
		}
		print colorize("Getting File $File\n","green","black",TRUE,1);
			
		if(dse_file_exists($File)){
			print colorize("$File Already Exists!\n","white","red",TRUE,1);
			$A=dse_ask_yn("Delete first?");
			if($A=='Y'){
				$backupfilename=dse_file_backup($File);
				print "backup saved at: $backupfilename\n";
				dse_file_delete($File);
			}else{
				print "Cant get. File Exists. Exiting.\n";
				exit(1);
			}
		}
		$Command="scp $User@$Host:$File $File";
		print "C=$Command\n";
		dse_passthru($Command,TRUE);
		if(dse_file_exists($File)){
			print colorize("Success!\n","white","green",TRUE,1);
		}else{
			print colorize("Error!\n","white","red",TRUE,1);
		}
		exit(0);
	case 'r':
	case 'sync': //rsyncs to file arg3 from file arg2 on user@host arg1
		//print_r($vars[DSE][USERHOST]); exit();
		
		//if(sizeof($argv)==4){
			$UserHost=$argv[1];
			if(str_contains($UserHost,"@")){
				$User=strcut($UserHost,"","@");
				$Host=strcut($UserHost,"@");
			}else{
				print "invalid user@host in arg1. exiting.\n";
				exit(1);
			}
			$SourceFile=$argv[2];
			if($argv[3]){
				$LocalFile=$argv[3];
			}else{
				$LocalFile=$SourceFile;
			}
		
		
		$SourceDir=dirname($SourceFile);
		$SourceFileName=basename($SourceFile);
		if($SourceDir=="" || $SourceFile==$SourceFileName){
			$SourceDir=getcwd();
			$SourceFile=$SourceDir."/".$SourceFileName;
		}
		print colorize("Getting File $SourceFile\n","green","black",TRUE,1);
			
		
		$Command="rsync --progress --partial --recursive --size-only $User@$Host:$SourceFile $LocalFile";
		print "C=$Command\n"; //--dry-run 
		dse_passthru($Command,TRUE);
		/*if(dse_file_exists($File)){
			print colorize("Success!\n","white","green",TRUE,1);
		}else{
			print colorize("Error!\n","white","red",TRUE,1);
		}*/
		exit(0);
	case 'c':
	case 'compare-directories':
		$Dir1=$argv[1];
		dpv(4, "doing compare-directories");
		if(sizeof($argv)>2){
			$Dir2=$argv[2];
		}else{
			$Dir2=getcwd();
		}
		$Dir1=dse_directory_strip_trail($Dir1);
		$Dir2=dse_directory_strip_trail($Dir2);
		
		//diff -qr /home/wordjack/wordjack/PHP /home/wordjack/wordjack_git/scraper/PHP
		//diff -Naur DIR1 DIR2 
		$Command="rsync -rnvc $Dir1/* $Dir2/.  2>/dev/null | grep -v \".git\" | grep -v \"skipping non-regular file\" "
			." | grep -v \"ding incremental file list\" | grep -v \"bytes/sec\" | grep -v \"speedup is\" ";
		$Command="rsync -rnvc $Dir1/ $Dir2  2>/dev/null | grep -v \".git\" | grep -v \"skipping non-regular file\" "
			." | grep -v \"ding incremental file list\" | grep -v \"bytes/sec\" | grep -v \"speedup is\" | grep -v \"/dse/\" "
			." | grep -v \"CONFS/\" | grep -v \"DSE/\"  | grep -v \"/output/\"  ";
		$r=dse_exec($Command,TRUE);
		foreach(split("\n",$r) as $L){
			$L=trim($L);
			if($L){
				//print colorize(pad("L=".$L." ",100),"white","magenta");
				
				
				$F1=$Dir1."/".$L;
				$F2=$Dir2."/".$L;
				//print colorize(pad(" aa",70),"blue","yellow");
				$F1_sa==array();
				//print colorize(pad("bb ",70),"blue","yellow");
				$F2_sa=array();
				
				//print colorize(pad("cc ",70),"blue","yellow");
				$F1_size="?";
				$F2_size="?";
				
				$F1_md5="?";
				$F2_md5="?";
				 
				dpv(4,"F1=$F1 F21=$F2");
				print colorize(pad($L." ",70),"blue","white");
				
				
				if(dse_file_exists($F1)){
					$F1_sa=dse_file_get_stat_array($F1);
					$F1_size=$F1_sa[7];
					dpv(5,"dse_file_exists($F1)=TRUE");
				
					if(is_dir($F1)){
						$F1_md5="dir";
					}else{
						$F1_md5=md5_of_file($F1);
					}
					print colorize(pad($F1_size,12," ","right")."b  ","white","blue");
					print colorize(pad($F1_md5,12," ","right")."#  ","white","blue");
				}else{
					print colorize(pad("missing",26," ","center")."  ","black","yellow");
				}
				
				
				
				//print "ff321rfwc\n";
				if(dse_file_exists($F2)){
					$F2_sa=dse_file_get_stat_array($F2);
					$F2_size=$F2_sa[7];
					//	print "wrwqerw\n";
					if(is_dir($F2)){
						$F2_md5="dir";
					}else{
						$F2_md5=md5_of_file($F2);
					//	print "334r234t43f\n";
					}
					dpv(5,"dse_file_exists($F2)=TRUE");
					if($F2_size==$F1_size && $F1_md5==$F2_md5){
						print colorize(" => ","white","green");
					}else{
						print colorize(" => ","white","red");
					}
				
					
					if($F2_size==$F1_size){
						print colorize(pad($F2_size,12," ","right")."b  ","white","green");
					}elseif($F2_size>$F1_size){
						print colorize(pad($F2_size,12," ","right")."b  ","white","magenta");
					}elseif($F2_size<$F1_size){
						print colorize(pad($F2_size,12," ","right")."b  ","white","red");
					}
					if($F1_md5==$F2_md5){
						print colorize(pad($F2_md5,12," ","right")."#  ","white","green");
					}else{
						print colorize(pad($F2_md5,12," ","right")."#  ","white","red");
					}
				}else{	
				//print "v2344\n";
					print colorize(" => ","white","red");
					print colorize(pad("missing",26," ","center")."  ","black","yellow");
				}
				//print "252345dfgs\n";
				print "\n";
			}
		}
		$DidSomething=TRUE;
		break;
			
	case 'v':
	case 'verbosity':
		break;
	case 's':
	case 'shrink':
		$FileName=$argv[1];
		dse_file_shrink($FileName);
		exit(0);
	case 'd':
  	case 'df':
		dse_print_df();
		exit(0);
	case 'i':
  	case 'ls':
		if(sizeof($argv)>1){
			dse_color_ls($argv[1]);
		}else{
			$CWD=getcwd();
			dse_color_ls($CWD);
		}
		exit(0);
	case 'a':
	case 'dir-sizes':
		exit(0);
	case 'f':
	case 'run-command-batch':
		if(sizeof($argv)>1){
			$CommandBatchFile=$vars['options'][$opt];
			
			$CommandFormat=$argv[1];
			print "			dse_run_command_batch($CommandBatchFile,$CommandFormat);\n";
			dse_run_command_batch($CommandBatchFile,$CommandFormat);
		}else{
			print "no arg1 command-batch-file given to run\n";
			exit(1);
		}
		break;
	default:
		dep("unknown option passed to dfm: opt='$opt'");
		break;
		//list by most recent changed fiels in dir
		
}

dpv(4,"parsed args");
	
	
if($DoLargeFileFind){
	if(!$RootDir) $RootDir="/";
	if(!$Limit) $Limit=100;
	$r=dse_exec("du -am $RootDir 2>/dev/null | sort -n -r | head -n $Limit",TRUE,TRUE);
	//du -am / 2>/dev/null | sort -n -r 
	$BlockSize=1024*1024;
	
	//dse_passthru("find $RootDir -type f -size +1000000k -exec ls -l {} \; 2>/dev/null ",TRUE);
	//exit(0);
}
		
		
		if(sizeof($argv)<2){
	//dpv(0,"no file argument!");
	//exit(1);
	$UseSTDIN=TRUE;
}
$File=$argv[1];
dpv(4,"file=$File");
			
if($DoFileEmpty){
	dpv(4,"in DoFileEmpty");
	dse_file_put_contents($File,"");
//	exit(0);
}
			
if($DoNumber){
	$File=$argv[1];
	$Message="dfi numbering lines in $File :\n";
	dpv(1,$Message);
	dse_log($Message);
	$FileContents=dse_file_get_contents($File);
	$Line=0;
	foreach(split("\n",$FileContents) as $Line){
		$i++;
		print "$i\t$Line\n";
	}
	//exit(0);
}
if($DoLineWithString){
	$File=$argv[1];
	$Command="fgrep -n \"$String\" $File";
	$r=dse_exec($Command);
	$Line=0;
	$tbr="";
	foreach(split("\n",$r) as $Line){
		$LineNumber=strcut($Line,"",":");
		if($LineNumber){
			if($tbr)$tbr.=",";
			$tbr.= $LineNumber;
		}
	}
	print $tbr;
	//exit(0);
}

if($DoFileMid){
	$File=$argv[1];
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
	//exit(0);
}

if($DoFileEdit){
	$File=$argv[1];
	$Message="dfi Backing up $File and launcing in vim:\n";
	dpv(1,$Message);
	dse_log($Message);
	passthru("/dse/bin/vibk \"$File\" 2>&1");
	//exit(0);
}

if($DoRemoveDuplicateLines){
	$File=$argv[1];
	$Message="dfi 2 removing duplicate lines from $File :\n";
	//print "sdfsadf34f3ca\n";
	dpv(1,$Message);
	//print "sdfsadf34f3ca\n";
	dse_log($Message);
	//print "sdfsadf34f3ca\n";
	$Contents=dse_file_get_contents($File);
	//print "sdfsadf34f3ca\n";
	$Contents=remove_duplicate_lines($Contents);
	$sl=strlen($Contents);
	//print "sl=$sl\n";
	dse_file_put_contents($File,$Contents);
//	exit(0);
}

if($DoRemoveBlankLines){
	//	dpv(3,"doing DoRemoveBlankLines");
	$File=$argv[1];
	$Message="dfi removing blank lines from $File :\n";
	dpv(1,$Message);
	dse_log($Message);
	$Contents=dse_file_get_contents($File);
	$sl=strlen($Contents);
	//print "sl=$sl\n";
	$Contents=remove_blank_lines($Contents);
	$sl=strlen($Contents);
	//print "sl=$sl\n";
	dse_file_put_contents($File,$Contents);
//	exit(0);
}


dse_shutdown();

/*
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
*/

exit(0);




function dse_run_command_batch($CommandBatchFile,$CommandFormat=""){
	global $vars; dse_trace();
	print " dse_run_command_batch($CommandBatchFile,$CommandFormat)\n";
	$TimeStart=time();
	$CommandBatchSize=trim(dse_exec("wc -l \"$CommandBatchFile\""));
	$CommandBatchSizeInitial=$CommandBatchSize;
	while($CommandBatchSize>0){
		$NumberDone++;
	//	print "Number of Commands Left to run: $CommandBatchSize\n";
		$Command=dse_file_strip_last_line($CommandBatchFile,TRUE);
	//	print "Command=$Command\n";	
		$Command=trim($Command);
		$Command=str_replace("/backup/webroot/prd_min_craftlister_com","",$Command);
		if($CommandFormat){
			$Command=str_replace("REPLACE",$Command,$CommandFormat);
		}else{
			
		}
		
		$TimeRunning=time()-$TimeStart;
		$TimeRunningStr=seconds_to_text($TimeRunning);
		
		$TimeTotalExpected=intval($TimeRunning*($CommandBatchSize/$NumberDone));
		
		$TimeLeft=$TimeTotalExpected-$TimeRunning;
		$TimeLeftStr=seconds_to_text($TimeLeft);
		
		$CommandBatchSize=trim(dse_exec("wc -l \"$CommandBatchFile\""));
		$PercentDone=number_format(($CommandBatchSize/$CommandBatchSizeInitial)*100,1);
		print "#Left: $CommandBatchSize  $PercentDone%  $TimeRunningStr  $TimeLeftStr  Cmd: $Command\n";
		$r=dse_exec($Command,FALSE,FALSE);
	}
}

 

function dse_file_strip_last_line($FileName,$SaveFile=FALSE){
	global $vars; dse_trace();
	$LastLine=dse_exec("tail -n1 \"$FileName\"");
	if($SaveFile){
		dse_exec("sed -ie '\$d' \"$FileName\"");
	}
	return $LastLine;
}

 
 
 
 
 
 
?>