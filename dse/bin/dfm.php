#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
$vars['Verbosity']=1;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="DSE File Manipulator";
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
 
  array('c','compare-directories',"compare-directories arg1 to arg2 or if no arg2, pwd"),
  array('h','help',"this message"),
  array('v:','verbosity:',"0=none 1=some 2=more 3=debug"),
  array('e','edit',"backs up and launches a vim of arg1 "),
  array('u','remove-duplicate-lines',"remove duplicate lines"),
  array('b','remove-blank-lines',"remove blank lines"),
  array('m:','mid:',"returns tail -1 arg1 | head -n arg2"),
  array('','number',"adds a incrementing line number to start of each line"),
  array('','find-large-files',"finds larges files in arg1"),
  array('','empty',"empties file arg1"),
  array('','launch-url',"launch url arg1"),
  array('','launch-vibk-edit',"launch vibk edit of file arg1"),
  array('','launch-code-edit',"launch code edit of file arg1"),
 // array('','tree',"show dir as a tree"),
  array('','ls',"a colorfull and more info version of ls"),
  
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

if($vars['Verbosity']>3){
	$vars[dse_enable_debug_code]=TRUE;
}

dse_cli_script_header();

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
						print colorize(" => ","white","red");
					}else{
						print colorize(" => ","white","green");
					}
				
					
					if($F2_size==$F1_size){
						print colorize(pad($F2_size,12," ","right")."b  ","white","blue");
					}elseif($F2_size>$F1_size){
						print colorize(pad($F2_size,12," ","right")."b  ","white","green");
					}elseif($F2_size<$F1_size){
						print colorize(pad($F2_size,12," ","right")."b  ","white","red");
					}
					if($F1_md5==$F2_md5){
						print colorize(pad($F2_md5,12," ","right")."#  ","white","cyan");
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
	exit(0);
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
	dse_file_set_contents($File,"");
	exit(0);
}
			
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


dse_shutdown();


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
