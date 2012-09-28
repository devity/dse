#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	

$vars['Verbosity']=0;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="DSE Restore Script";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="Reverts to a previous DSE install";
$vars['DSE']['SCRIPT_VERSION']="v0.01b";
$vars['DSE']['SCRIPT_VERSION_DATE']="2012/09/28";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
$vars['DSE']['SCRIPT_COMMAND_FORMAT']="";
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

if(!$vars['DSE']['DSE_ROOT']){
	if(getenv("DSE_ROOT")!=""){
		$vars['DSE']['DSE_ROOT']=getenv("DSE_ROOT");
	}else{
		$vars['DSE']['DSE_ROOT']="/dse"; 
	}
}
if(!$vars['DSE']['DSE_BIN_DIR']) $vars['DSE']['DSE_BIN_DIR']=$vars['DSE']['DSE_ROOT']."/bin";

$vars['DSE']['DSE_BACKUP_DIR_DSE']="/backup/dse";

//list backups

$DSESourceDir=dse_file_link_get_destination($vars['DSE']['DSE_ROOT'])."/";
$DSESourceDir=str_replace("dse/dse/","dse",$DSESourceDir);

//print "source=$DSESourceDir\n";

 
$Scripts=array();
print "DSE Backups in ".$vars['DSE']['DSE_BACKUP_DIR_DSE'].":\n";
$dd=dse_ls($vars['DSE']['DSE_BACKUP_DIR_DSE'],0);
//print_r($dd);
foreach($dd as $DirEntry){
//	print "\nentry="; print_r($DirEntry);
	if($DirEntry[1]!="." && $DirEntry[1]!=".."){
		$FileName=$DirEntry[1];
		$FullFileName=$vars['DSE']['DSE_BACKUP_DIR_DSE']."/".$DirEntry[1];
			
		$ScriptFileName=str_replace(".php","",$FileName);
		$DSERoot=dse_file_link_get_destination();
		
		if($DSESourceDir && $DSESourceDir!="/"){
			$Command="rm -rf $DSESourceDir";
			//print "$Command\n";
			$Command2="cp -rf $FullFileName $DSESourceDir";
			//print "$Command\n";
			
		}
				
		$Scripts[$ScriptFileName]=" * $ScriptFileName -      \n$Command\n$Command2\n";
		
	}
}
asort($Scripts);
foreach($Scripts as $ScriptFileName=>$ScriptLine){
	if($ScriptLine){
		print $ScriptLine;
	}
}



///////////////////////////////////////////////////////////
function dse_ls( $search ){ 
	global $vars;
	$Command="ls -a -1 $search";
	$r=`$Command`;
	$tbr=array();
	foreach(split("\n",$r) as $Line){
		if($Line){
			if( is_dir( "$Line" ) ){ 
	            $tbr[]=array("DIR","$Line");
	        } else { 
				$tbr[]=array("FILE","$Line");
	        } 
		}
	}
	return $tbr;
} 

function dse_file_link_get_destination($LinkFile){
	global $vars;
	
	if(!file_exists($LinkFile)) {
		//return -1;	
	}
	$DestinationFile=dse_exec("ls -la $LinkFile");
	$DestinationFile=strcut($DestinationFile,"-> ","\n");	
	if($DestinationFile!="") {
		return $DestinationFile;	
	}
	if(file_exists($LinkFile)){
		return -3;
	}
	return -2;
}

function dse_file_get_contents($filename){
	global $vars;
	return `cat $filename`;
}

function strcut($haystack,$pre,$post=""){
	global $vars; 
	global $strcut_post_haystack;
	$strcut_post_haystack="";
	if($pre=="" || !(stristr($haystack,$pre)===FALSE)){
		if($pre==""){
		}else{
			//if($haystack && $pre){
				$haystack=substr($haystack,stripos($haystack,$pre)+strlen($pre));
			//}else{
			//	$haystack=$haystack; //==""
			//}
		}	
		if( $post!='' && !(strstr($haystack,$post)===FALSE)){	
			if($post==""){
				$r=$haystack;
				$strcut_post_haystack="";
			}else{
				$r=substr($haystack,0,strpos($haystack,$post));
				if($haystack && $post){
					$strcut_post_haystack=substr($haystack,stripos($haystack,$post)+strlen($post));
				}
			}		
		}else{
			$r=$haystack;
			$strcut_post_haystack="";
		}		
	}else{		
		$r="";
	}
	return $r;
}
function dse_exec($Command,$ShowCommand=FALSE,$ShowOutput=FALSE){
	global $vars; 
	if($ShowCommand){
		print "Command: $Command\n";
	}
	$r=`$Command`;
	if($ShowOutput) {
		print $r;
		print "END Command: $Command\n";
	}
	return $r;
}


?>

