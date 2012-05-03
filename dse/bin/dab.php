#!/usr/bin/php
<?php


error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
//is_already_running();
$Verbosity=3;
$StatusOutput="";
$DidSomething=FALSE;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="DAB";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="Devity Automatic Backup";
$vars['DSE']['DAB_VERSION']="v0.01b";
$vars['DSE']['DAB_VERSION_DATE']="2012/04/30";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******


$parameters_details = array(
  array('h','help',"this message"),
  array('q','quiet',"same as -v 0"),
  array('c','clean',"cleans up (DELETES!) all backups of all files and dirs currently matched by the config file"),
  array('','version',"version info"),
  array('v:','verbosity:',"0=none 1=some 2=more 3=debug"),
  array('s','stats',"statistics"),
  array('e','edit',"does a vibk dab.conf"),
);
$parameters=dse_cli_get_paramaters_array($parameters_details);
$Usage=dse_cli_get_usage($parameters_details);



if($Verbosity>=4) {print "argv="; print_r($argv); print "\n";}

$options = getopt(implode('', array_keys($parameters)), $parameters);
$pruneargv = array();
foreach ($options as $option => $value) {
  foreach ($argv as $key => $chunk) {
    $regex = '/^'. (isset($option[1]) ? '--' : '-') . $option . '/';
    if ($chunk == $value && $argv[$key-1][0] == '-' || preg_match($regex, $chunk)) {
      array_push($pruneargv, $key);
    }
  }
}
while ($key = array_pop($pruneargv)){
	deleteFromArray($argv,$key,FALSE,TRUE);
}
if($Verbosity>=4) {
	print "argv="; print_r($argv); print "\noptions="; print_r($options); print "\n";
}


foreach (array_keys($options) as $opt) switch ($opt) {
	case 'h':
  	case 'help':
  		$ShowUsage=TRUE;
		$DidSomething=TRUE;
		break;
	case 'version':
		$ShowVersion=TRUE;
		$DidSomething=TRUE;
		break;
	case 'q':
	case 'quiet':
		$Verbosity=0;
		break;
	case 'i':
	case 'insensitivecase':
		$CaseInsensitiveFlag="'-i";
		break;
	case 's':
	case 'stats':
		$DoShowStats=TRUE;
		$DidSomething=TRUE;
		break;
	case 'c':
	case 'clean':
		$DoClean=TRUE;
		break;
	case 'v':
	case 'verbosity':
		$Verbosity=$options[$opt];
		if($Verbosity>=2) print "Verbosity set to $Verbosity\n";
		break;
	case 'e':
	case 'edit':
		print "Backing up ".$vars['DSE']['DAB_CONFIG_FILE']." and launcing in vim:\n";
		passthru("/dse/bin/vibk ".$vars['DSE']['DAB_CONFIG_FILE']." 2>&1");
		$DidSomething=TRUE;
		break;

}
if($ShowUsage){
	print $Usage;
//	exit(0);
}
if($ShowVersion){
	print "DSE Version: " . $vars['DSE']['DSE_VERSION'] . "  Release Date: " . $vars['DSE']['DSE_VERSION_DATE'] ."\n";
	print $vars['DSE']['SCRIPT_NAME']." Version: " . $vars['DSE']['DAB_VERSION'] . "  Release Date: " . $vars['DSE']['DAB_VERSION_DATE'] ."\n";
//	exit(0);
}


$BackupLocation="";


$CfgData=file_get_contents($vars['DSE']['DAB_CONFIG_FILE']);
if($CfgData==""){
	print "ERROR opening config file: ".$vars['DSE']['DAB_CONFIG_FILE']."\n";
}else{
	print "Using config file: ".$vars['DSE']['DAB_CONFIG_FILE']."\n";
}
print "\n";

$DirectoryArray=array();
foreach(split("\n",$CfgData) as $Line){
	if(!(strstr($Line,"#")===FALSE)){
		//print "CCC\n";
		if(strpos($Line,"#")==0){
			$Line="";
		}else{	
			$Line=substr($Line,0,strpos($Line,"#")-1);
		}
	}
	//print "L=$Line\n";
	if(!(strstr($Line,"ExcludedExtensions=")===FALSE)){
		//print "ttttt\n";
		$ExcludedExtensionsList=substr($Line,strlen("ExcludedExtensions="));
		$ExcludedExtensionsArray=split(",",$ExcludedExtensionsList);
	}elseif(!(strstr($Line,"ExcludedDirectories=")===FALSE)){
		$ExcludedDirectoriesList=substr($Line,strlen("ExcludedDirectories="));
		$ExcludedDirectoriesArray=split(",",$ExcludedDirectoriesList);
	}elseif(!(strstr($Line,"ExcludedDirectoriesPartial=")===FALSE)){
		$ExcludedDirectoriesPartialList=substr($Line,strlen("ExcludedDirectoriesPartial="));
		$ExcludedDirectoriesPartialArray=split(",",$ExcludedDirectoriesPartialList);
	}elseif(!(strstr($Line,"LogFile=")===FALSE)){
		$LogFile=substr($Line,strlen("LogFile="));
	}elseif(!(strstr($Line,"StatusFile=")===FALSE)){
		$StatusFile=substr($Line,strlen("StatusFile="));
	}elseif(!(strstr($Line,"BackupLocationRoot=")===FALSE)){
		$BackupLocationRoot=substr($Line,strlen("BackupLocationRoot="));
	}elseif($Line!=""){
		$DirectoryArray[]=$Line;
	}
}

if($DoShowStats){
	$StatusFileContents=`cat $StatusFile`;
	print "Status File: $StatusFile \n";
	print $StatusFileContents ."\n\n";
	exit;
}

if($DidSomething){
	exit(0);
}


$BackupDirectoryName=".ddab";

$ExcludedDirectoriesArray[]=$BackupDirectoryName;

print "Exclude Extensions: $ExcludedExtensionsList\n";
print "Excluded Directories: $ExcludedDirectoriesList\n";
print "\n";
$BytesNeededTotal=0;
foreach ($DirectoryArray as $Dir){
	$BytesNeededTotal_tmp=$BytesNeededTotal;
	$BytesNeededTotal=0;
	ddab_recursive_do_dir($Dir);
	global $BytesNeededTotal;
	$BytesNeededTotal+=$BytesNeededTotal_tmp;
}

if($DoClean){
	$BytesCleaned_str=number_format($BytesCleanedTotal/1000000,1)."MB";
	print "Space Free'd: $BytesCleaned_str \n";
}
	
$BytesNeededTotal_str=number_format($BytesNeededTotal,0);
print "BytesNeededTotal=$BytesNeededTotal_str\n";
if($BytesNeededTotal>0){
	ddab_log("Total Size: $BytesNeededTotal_str Bytes\n");
}

if($StatusFile){
	$LastRun_str=@date("Y/m/d H:i.s");
	$StatusOutput.="Last Run: $LastRun_str\n";
	print "Last Run: $LastRun_str\n";
	
	$EndTime=time();
	$RunTime=$EndTime-$StartTime;
	$RunTime_str="$RunTime seconds";
	$StatusOutput.="Run Time: $RunTime_str\n";
	print "Run Time: $RunTime_str\n";
	
	print "Status written to: $StatusFile\n";
	file_put_contents($StatusFile, $StatusOutput);
}
if($LogFile){
	print "Log file at: $LogFile\n";
}

exit();


//////////////////////////////////////////////////////////////////////////////////////////////

function ddab_log($line){
	global $LogFile;
	$fh = fopen($LogFile, 'a') or die("can't open log file for append: $LogFile");
	$d=@date("Y/m/d H:i.s");
	fwrite($fh, $d." - ".$line."\n");
	fclose($fh);
}

$BackupLocationsCleanedArray=array();

function ddab_recursive_do_dir($Dir){
	global $ExcludedDirectoriesArray,$ExcludedExtensionsArray,$ExcludedDirectoriesPartialArray;
	global $BackupDirectoryName, $BytesNeededTotal, $LogFile, $StatusFile, $StatusOutput;
	global $BackupLocationRoot;
	global $DoClean,$BytesCleanedTotal;
	global $BackupLocationsCleanedArray;
	$warn_size_limit=1000*1000;
	
	//print "ddab_recursive_do_dir($Dir)\n";
	$ls=`ls -1a $Dir`;
	foreach(split("\n",$ls) as $filename){
		//print "$filename \n";
		
		if($filename && $filename!="." && $filename!=".."){
			$filename_parts=pathinfo($filename);
			//print_r($filename_parts);
			$filename_extension=$filename_parts['extension'];
			$full_filename=$Dir."/".$filename;
			$full_filename=str_replace("//", "/", $full_filename);
			$do=TRUE;
			if(is_dir($full_filename)){
				foreach($ExcludedDirectoriesArray as $xDir){
					if($xDir!="" && $xDir==$filename){
						$do=FALSE; 
						if($xDir!=".ddab"){
							$msg=" ******SKIPPED DIRECTORY: $filename/\n";
							$StatusOutput.=$msg; print $msg;
						}
					}
				}
				foreach($ExcludedDirectoriesPartialArray as $xDir){
					if($xDir!="" && !(strstr($filename,$xDir)===FALSE)){
						$do=FALSE; 
						$msg=" ******SKIPPED DIRECTORY: $filename/\n";
						$StatusOutput.=$msg; print $msg;
					}
				}
			}
			foreach($ExcludedExtensionsArray as $xExt){
				if($xExt!="" && $xExt==$filename_extension){
					$do=FALSE;
					$size=filesize($full_filename);
					$size_str=number_format($size);
					$msg= " ******SKIPPED FILE: $filename ($size_str B)\n";
					$StatusOutput.=$msg; print $msg;
				}
			}
					
			
			if($do){
				if(is_dir($full_filename)){
					$full_filename.="/";
				}else{
					
				}
				
				if(is_dir($full_filename)){
					print "$full_filename \n"; 
					$BytesNeededTotal_tmp=$BytesNeededTotal;
					$BytesNeededTotal=0;
					ddab_recursive_do_dir($full_filename);
					global $BytesNeededTotal;
					$BytesNeededTotal+=$BytesNeededTotal_tmp;
				}else{
					print "$full_filename "; 
					if($BackupLocationRoot){
						$BackupLocation=$BackupLocationRoot."/".$Dir."/".$BackupDirectoryName."/";
					}else{
						$BackupLocation=$Dir."/".$BackupDirectoryName."/";					
					}			
					$BackupLocation=str_replace("//", "/", $BackupLocation);
					$BackupFile=$BackupLocation."/".$filename;
					$BackupFile=str_replace("//", "/", $BackupFile);
					
					if($DoClean){
						if(!$BackupLocationsCleanedArray[$BackupLocation]){
							$Command="dsizeof \"$BackupLocation\"";
							$BytesCleaned=`$Command`;
							$BackupLocationsCleanedArray[$BackupLocation]=$BytesCleaned;
							$BytesCleanedTotal+=$BytesCleaned;
							//print "$Command => $BytesCleaned\n";
						//	global $rrmdir_test_only;	$rrmdir_test_only=TRUE;
							rrmdir($BackupLocation);
						}else{
							
						}
					}else{
						if(!file_exists($BackupLocation)){
							//print "%%%%%% $BackupLocation \n";
							mkdir($BackupLocation,0777,TRUE);
						}
						if(!file_exists($BackupFile)){
							$Command="cp -fp \"$full_filename\" \"$BackupFile\"";
							print `$Command`;
							$BytesNeededTotal+=filesize($full_filename);
							if(filesize($full_filename)>$warn_size_limit){
								$msg= " ************* BIG file: $full_filename  =".filesize($full_filename)." Bytes\n";
								$StatusOutput.=$msg; print $msg;
							}
							ddab_log("NEW FILE: $full_filename");
								print " - NEW ****** ";
						}else{
							if(filesize($full_filename)!=filesize($BackupFile) 
							
							){
							//|| filemtime($full_filename)!=filemtime($BackupFile)
								$BytesNeededTotal+=filesize($full_filename);
								$mtime=filemtime($BackupFile);
								$full_filename_parts=pathinfo($full_filename);
								$full_filename_extension=$full_filename_parts['extension'];
								$BackupFile_archive=$BackupFile.".".@date("Ymd-His").".".$full_filename_extension;
								$Command="cp -fp \"$BackupFile\" \"$BackupFile_archive\"";
								print "Cmd=$Command \n";
								print `$Command`;
								$Command="cp -fp \"$full_filename\" \"$BackupFile\"";
								print "Cmd=$Command \n";
								print `$Command`;
								ddab_log("UPDATED FILE: $full_filename");
								print " - UPDATED ******";
							}else{
								print " - OK ";
							}	
						}
					}
					print "\n";
				}
			}
		}
//$BytesNeededTotal_str=$BytesNeededTotal;//number_format($BytesNeededTotal,0);
//print "BytesNeededTotal=$BytesNeededTotal_str\n";

	}
}


 
?>
