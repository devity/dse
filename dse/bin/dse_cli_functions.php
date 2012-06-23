<?


$OK=getColoredString("OK","green","black");
$Fixed=getColoredString("Fixed","green","black");
$Added=getColoredString("Added","green","black");
$Failed=getColoredString("Failed","red","black");
$NotOK=getColoredString("Not OK","red","black");
$Missing=getColoredString("Missing","red","black");
$NotChanged=getColoredString("Not Changed","orange","black");
$NotFixed=getColoredString("Not Changed","orange","black");



function dse_log($Message,$File=""){
	global $vars;
	$Command="";
	$Message=dse_date_format()."  ".str_replace("\"","\\\"",$Message);
	if(!$File){
		if($vars['DSE']['SCRIPT_LOG_FILE']) {
			$File=$vars['DSE']['SCRIPT_LOG_FILE'];
		}else{
			$File=$vars['DSE']['LOG_FILE'];
		}
	}
	if($vars['DSE']['LOG_TO_SCREEN']) print "dse_log: $Message\n";
	if($File)	`echo "$Message" >> $File`;
}

function dse_ip_port_is_listening($IP,$Port){
	global $vars;
	$Command="nc -vz $IP $Port 2>&1";
	$r=`$Command`;
	dse_log("c=$Command r=$r");
	return(str_contains($r,"succeeded"));
}

function dse_date_format($Time="NOW",$FormatName="FULLREADABLE"){
	global $vars;
	if($Time=="NOW") $Time=time();
	if(str_contains($FormatName," ")){
		return date($FormatName,$Time);
	}
	switch($FormatName){
		case 'SYSLOG':
			$FormatString="D M j G:i:s T Y";
			break;
		case 'FULL':
		case 'FULLREADABLE':
			$FormatString="D F jS, Y, g:i.s a T";
			break;
		case 'COMPACTREADABLE':
			$FormatString="Y/m/d g:ia";
			break;
		case 'READABLE':
			$FormatString="F j, Y, g:i a";
			break;
		case 'YYYMMDD':
			$FormatString="Ymd";
			break;
		case 'SQL':
			$FormatString="Y-m-d";
			break;
			break;
		default:
			$FormatString="F j, Y, g:i a";
	}
	$r=date($FormatString,$Time);
	return $r;
}


function seconds_to_text($Seconds){
	if($Seconds<60*3){
		return "$Seconds seconds";
	}elseif($Seconds<60*60*2){
		$Minutes=intval($Seconds/60);
		return "$Minutes minutes";
	}elseif($Seconds<60*60*24*2){
		$Hours=intval($Seconds/(60*60));
		return "$Hours hours";
	}elseif($Seconds<60*60*24*30*2){
		$Days=intval($Seconds/(60*60*24));
		return "$Days days";
	}elseif($Seconds<60*60*24*30*12*2){
		$Months=intval($Seconds/(60*60*24*30));
		return "$Months months";
	}else{
		$Years=intval($Seconds/(60*60*24*30*12));
		return "$Years years";
	}			
}
	
function dse_time_span_sting_to_seconds($Str){
	global $vars;
	$Str=strtolower($Str);
	$StrParts=split(" ",$Str);
	$tbr=0;
	$Value=$StrParts[0];
	switch($StrParts[1]){
		case 'second':
		case 'seconds':
			$tbr=$Value;
			break;
		case 'minute':
		case 'minutes':
			$tbr=$Value*60;
			break;
		case 'hour':
		case 'hours':
			$tbr=$Value*60*60;
			break;
		case 'day':
		case 'days':
			$tbr=$Value*60*60*24;
			break;
		case 'week':
		case 'weeks':
			$tbr=$Value*60*60*24*7;
			break;
		case 'month':
		case 'months':
			$tbr=$Value*60*60*24*30;
			break;
		case 'year':
		case 'years':
			$tbr=$Value*60*60*365;
			break;
	}
	if($StrParts[3]){
		$Str=strcut($Str," ");
		$Str=strcut($Str," ");
		$tbr+=dse_time_span_sting_to_seconds($Str);
	}
	return $tbr;
}
function dse_popen($Command){
	global $vars;
	ob_end_flush(); 
	$handle = popen($Command, 'r');
	while (!feof($handle)) {
        echo fgets($handle);
        flush();
        ob_flush();
        flush();
	}
	pclose($handle);
}

		
function dse_ask_yn($Question){
	global $vars;
	print "$Question (Y/N): ";
	$key=strtoupper(dse_get_key());
	cbp_characters_clear(1);
	if($key=="Y"){
		//print "\n";
		return 'Y';
	}elseif($key=="N"){
		//print "\n";
		return 'N';
	}else{
		print getColoredString(" unknown key: $key. ","red","black");
		return 0;
	}
}

function dse_directory_ls( $path = '.', $level = 0 ){ 
	global $vars;
	$path.="/";  $path=str_replace("//", "/", $path);
    $ignore = array( '.', '..' ); 
    $dh = @opendir( $path ); 
	$tbr=array();
    while( false !== ( $file = readdir( $dh ) ) ){ 
        if( !in_array( $file, $ignore ) ){ 
            if( is_dir( "$path$file" ) ){ 
                $tbr[]=array("DIR",dse_directory_ls( "$path$file", ($level+1) ) ); 
            } else { 
             	$tbr[]=array("FILE","$path$file");
            } 
        } 
    } 
    closedir( $dh );
	return $tbr;
} 
function dse_ls( $search ){ 
	global $vars;
	$Command="ls -a -1 $search";
	$r=`$Command`;
	//print "Command: $Command\n$r\n";
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


function dse_pid_get_info($PID){
	global $vars;
	$PIDInfo=array();
	$PIDInfo['PID']=$PID;
	$PIDInfo['PPID']=dse_pid_get_ps_columns($PID,"ppid");
	$PIDInfo['PCPU']=dse_pid_get_ps_columns($PID,"pcpu");
	$PIDInfo['PMEM']=dse_pid_get_ps_columns($PID,"pmem");
	$PIDInfo['USER']=dse_pid_get_ps_columns($PID,"user");
	$PIDInfo['EXE']=trim(`/dse/bin/pid2exe $PID`);
	return $PIDInfo;
}

function dse_pid_get_ps_columns($PID,$o){
	global $vars;
	$PID=intval($PID);
	if(!$PID){
		return -1;
	}
	$PMEM=trim(`ps -p $PID -o $o=`);
	return $PMEM;		
}


function dse_which($prog){
	global $vars;
	$Command="which $prog 2>&1";
	$r=`$Command`;
	//print "Command=$Command r=$r\n";
	if(!(strstr($r,"no $prog in")===FALSE)){
		return "";
	}else{
		return trim($r);
	}
}

function dse_cli_script_start(){
	global $vars,$argv;
	
	if($vars['Verbosity']>2 || $vars['DSE']['SCRIPT_SETTINGS']['Verbosity']>2){
		print "options=".debug_tostring($vars['options'])."\n";
		print "argv=".debug_tostring($argv)."\n";
	}
	
	$vars['options'] = _getopt(implode('', array_keys($vars['parameters'])),$vars['parameters']);
	$pruneargv = array();
	foreach ($vars['options'] as $option => $value) {
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
		
}


function dse_cli_script_header(){
	global $vars;
	if($vars['Verbosity']>1 || $vars['DSE']['SCRIPT_SETTINGS']['Verbosity']>1){
		//print getColoredString("","black","black");
		print getColoredString("    ########======-----________   ", 'light_blue', 'black');
		print getColoredString($vars['DSE']['SCRIPT_NAME'],"yellow","black");
		print getColoredString("   ______-----======########\n", 'light_blue', 'black');
		print "  ___________ ______ ___ __ _ _   _                      \n";
		print " /                           Configuration Settings\n";
		if($vars['DSE']['SCRIPT_SETTINGS']){
			foreach($vars['DSE']['SCRIPT_SETTINGS'] as $k=>$v){
				print "|  * $k: $v\n";
			}
		}else{
			print "|  * Script: ".$vars['DSE']['SCRIPT_FILENAME']."\n";
			print "|  * Verbosity: ".$vars['Verbosity']."\n";
		}
		print " \________________________________________________________ __ _  _   _\n";
		//print "\n";  
	}
}


function dse_require_root(){
	global $vars,$argv;
	$user=trim(`whoami`);
	if($user!="root"){
		print "$argv[0] must be run as root.\n";
		exit(-101);	
	}
}

function md5_of_file($f){
        global $vars;
        $sw_vers=dse_which("md5");
        if($sw_vers){
                $m=`md5 -q $f`;
                return ($m);
        }else{
                $sw_vers=dse_which("md5sum");
                if($sw_vers){
                        $m=`md5sum $f`;
                        $m=strcut($m,""," ");
                        return ($m);
                }
        }
        print "error in md5_of_file(), no md5 utility found. Supported:(md5,md5sum)";
        return -1;
}

function files_are_same($f1,$f2){
	global $vars;
	$m1=md5_of_file($f1);
	$m2=md5_of_file($f2);
	//print "files_are_same:md5: $m1==$m2<br>";
	return ($m1==$m2);
}

function dse_file_get_size($DestinationFile){
	global $vars;
	return dse_file_get_stat_field($DestinationFile,"size");
}

function dse_file_get_mtime($DestinationFile){
	global $vars;
	return dse_file_get_stat_field($DestinationFile,"mtime");
}

function dse_file_get_stat_field($DestinationFile,$field=""){
	global $vars;
	$stat_field_names=array('dev'=>0,'ino'=>1,'mode'=>2,'nlink'=>3,'uid'=>4,'gid'=>5,'rdev'=>6,'size'=>7,'atime'=>8,'mtime'=>9,'ctime'=>10,'blksize'=>11,'blocks'=>12);
	if(!dse_file_exists($DestinationFile)){
		print "Error in dse_file_get_mode($DestinationFile,$field) - file does not exist.\n";
		return -1;
	}
	$sa=stat($DestinationFile);
	if(!$field) return $sa;
	$index_i=$stat_field_names[$field];
	if((!$index_i) || strlen($index_i)<=0){
		print "Error in dse_file_get_mode($DestinationFile,$field) - field $field unknown. Options: "; print_r($stat_field_names); print "\n";
		return -1;
	}
	return $sa[$index_i];
}

function dse_file_exists($DestinationFile){
	global $vars;
	$r=`ls -la $DestinationFile`;
	if(str_contains($r,'No such file or directory')){
		return FALSE;
	}
	return TRUE;
}

function dse_file_get_mode($DestinationFile){
	global $vars;
	$ModeInt=intval(substr(sprintf('%o', fileperms($DestinationFile)), -4));
	return $ModeInt;
}

function dse_file_get_owner($DestinationFile,$ReturnGroupAlso=TRUE){
	global $vars;
	$Owner="";
	$UserInt=fileowner($DestinationFile);
	$UserArray=dse_posix_getpwuid($UserInt);
	$UserName=$UserArray['name'];
	$Owner.=$UserName;
	if($ReturnGroupAlso){
		$GroupInt=filegroup($DestinationFile);
		$GroupArray=dse_posix_getgrgid($GroupInt);
		$GroupName=$GroupArray['name'];
		$Owner.=":".$GroupName;
	}
	return $Owner;
}
	
function dse_posix_getgrgid($gid){ 
	global $vars;
  	if (function_exists('posix_getgrgid')) { 
    	$a = posix_getgrgid($gid); 
    	return $a; 
  	} 
 	if (is_readable($vars['DSE']['SYSTEM_GROUP_FILE'])){ 
    	exec(sprintf('grep :%s: '.$vars['DSE']['SYSTEM_GROUP_FILE'].' | cut -d: -f1', (int) $gid), $o, $r); 
    	if ($r == 0) 
      		return array('name'=>trim($o['0'])); 
    	else 
    		return array('name'=>$uid); 
  	} 
  	return array('name'=>$uid); 
}

function dse_posix_getpwuid($uid){ 
	global $vars;
  	if (function_exists('posix_getpwuid')) { 
    	$a = posix_getpwuid($uid); 
    	return $a; 
  	} 
 	if (is_readable($vars['DSE']['SYSTEM_USER_FILE'])){ 
    	exec(sprintf('grep :%s: '.$vars['DSE']['SYSTEM_USER_FILE'].' | cut -d: -f1', (int) $uid), $o, $r); 
    	if ($r == 0) 
      		return array('name'=>trim($o['0'])); 
    	else 
    		return array('name'=>$uid); 
  	} 
  	return array('name'=>$uid); 
} 

function dse_file_delete($File){
	global $vars;
	if(!$File){
		return -1;
	}
	if(str_contains($File,array(" ",",","/","\\","&",">","<","|","!","`","^","?",";",":"))){
		return -2;
	}
	$r=`rm -f $File`;
	return 0;
}



function dse_file_set_mode($DestinationFile,$Mode){
	global $vars;
	if($DestinationFile && $Mode){
		$command="chmod -R $Mode $DestinationFile 2>&1";
		print `$command`;
		$CurrentPermissions=dse_file_get_mode($DestinationFile);
		if(intval($Mode)!=$CurrentPermissions){
			return -2;
		}
		return 0;
	}
	return -1;
}

function dse_file_set_owner($DestinationFile,$Owner){
	global $vars;
	if($DestinationFile && $Owner){
		$command="chown $Owner $DestinationFile";
		`$command`;
		//$CurrentPermissions=dse_file_get_mode($DestinationFile);
		//if(intval($Mode)!=$CurrentPermissions){
		//	return -2;
		//}
		return 0;
	}
	return -1;
}

function dse_directory_create($Destination,$Mode="",$Owner=""){
	global $vars;
	print "DSE dir: $Destination ";
	if(!file_exists($Destination)) {
		$command="mkdir $Destination";
		`$command`;
		print getColoredString(" creating. ","green","black");
	}
	
	if(!file_exists($Destination)) {
		print getColoredString(" ERROR: failed to create $Destination . \n","red","black");
		return -2;	
	}
	
	if($Owner){
		$command="chown -R $Owner $Destination";
		`$command`;
	}
	if($Mode){
		$command="chmod -R $Mode $Destination";
		`$command`;
	}
	print getColoredString(" OK.\n","green","black");
	return 0;
}

function dse_file_backup($file){
	global $vars;

	$DATE_TIME_NOW=trim(`date +"%y%m%d%H%M%S"`);
	$backupfilename=$vars['DSE']['DSE_VIBK_BACKUP_DIRECTORY']."$file.$DATE_TIME_NOW";
	
	
	$dir=dirname($backupfilename);
	`mkdir -p $dir`;
	
	$Command="cp $file $backupfilename";
	`$Command`;
	
	$Command="echo \"$TIME_NOW cp $file $backupfilename\" >> ".$vars['DSE']['DSE_VIBK_LOG_FILE'];
	`$Command`;
	
	if(files_are_same($file,$backupfilename)){
		return $backupfilename;
	}else{
		print "Error creating backup: $backupfilename\n";
		return "";	
	}
}


function dse_file_link($LinkFile,$DestinationFile){
	global $vars;
	print "DSE file link: $LinkFile ";
	if(!$LinkFile){
		print getColoredString("Error: No LinkFile given.\n","red","black");
		return -2;
	}
	if(!$DestinationFile){
		print getColoredString("Error: No DestinationFile given.\n","red","black");
		return -3;
	}
	if(file_exists($LinkFile)) {
		$DestinationFileCurrent=dse_file_link_get_destination($LinkFile);
		if($DestinationFileCurrent==-3){
			print getColoredString(" file exists. not a link. \n","green","black");
			return 0;	
		}else{
			if(file_exists($DestinationFileCurrent)){
				print getColoredString(" link exists. -> $DestinationFileCurrent \n","green","black");
				return 0;	
			}
		}
	}else{
		$DestinationFileCurrent=dse_file_link_get_destination($LinkFile);
		if($DestinationFileCurrent>=0){
			print getColoredString(" link broken. ","orange","black");
			dse_file_delete($LinkFile);
		}
	}
	print "linking to: $DestinationFile ";
	$Command="ln -s $DestinationFile $LinkFile";
	$r=`$Command`;	
	if(!file_exists($LinkFile)) {
		print getColoredString(" ERROR creating link. cmd: $Command   result=$r\n","red","black");
		return -1;	
	}
	print getColoredString(" Added.\n","green","black");
	return 0;
}

function dse_file_is_link($File){
	global $vars;
	$DestinationFileCurrent=`ls -la $File`;
	if(str_contains($DestinationFileCurrent,"->")){
		return TRUE;
	}
	return FALSE;
}

function dse_file_link_get_destination($LinkFile){
	global $vars;
	
	if(!file_exists($LinkFile)) {
		//return -1;	
	}
	$DestinationFile=(`ls -la $LinkFile`);
	$DestinationFile=strcut($DestinationFile,"-> ","\n");	
	if($DestinationFile!="") {
		return $DestinationFile;	
	}
	if(file_exists($LinkFile)){
		return -3;
	}
	return -2;
}
 
function is_already_running($exe="",$ExitOnTrue=TRUE,$MessageOnExit=TRUE){
	global $vars;
	if($exe==""){
		global $argv;
		$exe=$arvg[0];
	}
	$PID=getmypid();
	$RunningPID=trim(`ps ux | grep $exe | grep -v grep | grep -v $PID`);
	if($RunningPID!=""){
		$RunningPID=str_replace("  "," ",$RunningPID);
		$RunningPID=str_replace("  "," ",$RunningPID);
		$RunningPID=str_replace("  "," ",$RunningPID);
		$RunningPID=str_replace("  "," ",$RunningPID);
		$RunningPID=str_replace("  "," ",$RunningPID);
		$RunningPID=str_replace("  "," ",$RunningPID);
		$pa=split(" ",$RunningPID);
		if($ExitOnTrue){
			if($MessageOnExit) print "Already running as PID: $pa[1]    under user: $pa[0] \n";
			exit();
		}
	}
	return;	
}

function dse_say($Text,$Volume="",$Voice="Victoria"){
	global $vars;
	$CurrentVolume=`/dmp/bin/volume_get`;
	if($Volume){
		//=$Volume;
	}else{
		if($vars[say_volume]==""){
			$Volume=intval($CurrentVolume/2);
			if($vars[say_volume]<10){
				$vars[say_volume]=10;
			}
		}else{
			$Volume=$vars[say_volume];
		}
	}
	`/dmp/bin/volume_set $Volume`;
	`say --voice=$Voice $Text`;
	`/dmp/bin/volume_set $CurrentVolume`;
	
}

function dse_output_box($Title,$Body,$TitleColor="",$BorderColor="",$BodyColor="",$BGColor="",$BGBorderColor=""){
	global $vars;
	
	if(!$BGColor)			$BGColor=$vars[shell_colors_reset_background];
	if(!$BGBorderColor)		$BGBorderColor=$BGColor;
	if(!$BodyColor)		$BodyColor=$vars[shell_colors_reset_foreground];
	if(!$TitleColor)		$TitleColor=$BodyColor;
	if(!$BorderColor)		$BorderColor=$TitleColor;
	
	$tbr="";
	
	$tbr.=getColoredString("   _ __ ___ ___________/[ ", $BorderColor, $BGBorderColor);
	$tbr.=getColoredString($Title, $TitleColor, $BGBorderColor);
	$tbr.=getColoredString(" ]\____________ ___ __ _\n", $BorderColor, $BGBorderColor);
	$n=0;
	foreach(split("\n",$Body) as $L){
		if($L){
			if($n==0){
				$bc=" /";
			}else{
				$bc="| ";
			}
			$tbr.=getColoredString($bc, $BorderColor, $BGBorderColor);
			$tbr.=getColoredString(" $L\n", $BodyColor, $BGColor);
			$n++;
		}
	}	
	$tbr.=getColoredString(" \_______________________________ ______ ___ __ _  _\n", $BorderColor, $BGBorderColor);
	return $tbr;
}

function dse_file_get_size_readable($file){
	global $vars;
	return dse_file_size_to_readable(dse_file_get_size($file));
}

function dse_file_size_to_readable($size){
	global $vars;
	if($size<1024){
		return number_format($size,0)."B";
	}elseif($size<1024*1024){
		return number_format($size/1024,0)."kB";
	}elseif($size<1024*1024*1024){
		return number_format($size/(1024*1024),1)."MB";
	}elseif($size<1024*1024*1024*1024){
		return number_format($size/(1024*1024*1024),2)."GB";
	}else{
		return number_format($size,0)."B";
	}
}

function dse_file_get_contents($filename){
	global $vars;
	return `cat $filename`;
}

// returns array of Names=>Values
function dse_read_config_file($filename,$tbra=array(),$OverwriteExisting=FALSE){
	global $vars;
	$CfgData=dse_file_get_contents($filename);
	if($CfgData==""){
		print "ERROR opening config file: $filename\n";
	}
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
		//print "Line=$Line\n";
		if(str_contains($Line,"+=")){
			$Lpa=split("\\+=",$Line);
			if($Lpa[0] && $Lpa[1]){
				$tbra[$Lpa[0]].=$Lpa[1];
			}
		}else{
			$Name=strcut($Line,"","=");
			$Value=strcut($Line,"=");
			//print "nv=: $Name && $Value\n";
			if($Name && $Value){
				if(str_contains($Name,"[]")){
					$Name=str_replace("[]","",$Name);
					if( (!isset($tbra[$Name])) ){
						$tbra[$Name]=array();
					}
					$tbra[$Name][]=$Value;
				}else{
					if( (!isset($tbra[$Name])) || $OverwriteExisting){
						$tbra[$Name]=$Value;
					}
				}	
			}
		}
	}

	return $tbra;
}


function str_contains($str,$needle){
	global $vars;
	if(is_array($needle)){
		foreach($needle as $n){
			if(!(strstr($str,$n)===FALSE)) return TRUE;
		}
	}else{
		if(!(strstr($str,$needle)===FALSE)) return TRUE;
	}
	return FALSE;
}

function strcut($haystack,$pre,$post=""){
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



function date_str_to_sql_date($str,$fmt=""){
	global $vars;
	
	$str=str_replace("\\","-",$str);
	$fmt=str_replace("\\","-",$fmt);
	$stre=urlencode($str);
	$fmte=urlencode($fmt);
	//print  "in date_str_to_sql_date($stre,$fmte)<br>";
	if($fmt){
		
		$str=str_replace("Thru ","",$str);
		$str=str_replace("Until ","",$str);
		
		$str=str_replace("Thu ","",$str);
		$str=str_replace("Thur ","",$str);
		$str=str_replace("Fri ","",$str);
		$str=str_replace("Sat ","",$str);
		$str=str_replace("Sun ","",$str);
		$str=str_replace("Mon ","",$str);
		$str=str_replace("Tue ","",$str);
		$str=str_replace("Tues ","",$str);
		$str=str_replace("Wed ","",$str);
		
	//	$fmt=str_replace("{Day} ","",$fmt);
	//	$fmt=str_replace("{Dy} ","",$fmt);
		
		
		
		$fmt="$fmt ";
		$lc=0;
		while($fmt && $fmt!=" " && $lc<100){
			$lc++;
//	print "FMT=[$fmt]<br>";
			$fc=substr($fmt,0,1);
//	print "fc=$fc<br>";
			if($fc=="{"){
				$etpos=strpos($fmt,"}");
				if(!($etpos===FALSE)){
					$tag=substr($fmt,0,$etpos+1);
					$fmt=substr($fmt,$etpos+1);
				//	include_once "$vars[SITE_ROOT]/include/str_functions.php";					
					$fndcpos=strpos_nonalnum($str);
	//	print "tag=$tag fmt=$fmt str=$str etpos=$etpos fndcpos=$fndcpos<br>";
					if($fndcpos){
						$data=substr($str,0,$fndcpos);
						$str=substr($str,$fndcpos);
						//print "?=$fndcpos tag=$tag data=$data<br>";
					}else{
						$data=$str;
						$str="";
						//print "?2=$fndcpos tag=$tag data=$data<br>";
					}
		//	print "tag='$tag' data='$data'<br>";
					switch($tag){
						case "{Month}":
						case "{month}":
						case "{Mon}":
						case "{MON}":
						case "{mon}":
							$data=strtoupper($data);
						case "{MONTH}":
							$data=str_replace(".","",$data);   
							if($data=="JANUARY") $Month=1;
							if($data=="FEBRUARY") $Month=2;
							if($data=="MARCH") $Month=3;
							if($data=="APRIL") $Month=4;
							if($data=="MAY") $Month=5;
							if($data=="JUNE") $Month=6;
							if($data=="JULY") $Month=7;
							if($data=="AUGUST") $Month=8;
							if($data=="SEPTEMBER") $Month=9;
							if($data=="OCTOBER") $Month=10;
							if($data=="NOVEMBER") $Month=11;
							if($data=="DECEMBER") $Month=12;
							
							if($data=="JAN") $Month=1;
							if($data=="FEB") $Month=2;
							if($data=="MAR") $Month=3;
							if($data=="APR") $Month=4;
							if($data=="MAP") $Month=5;
							if($data=="JUN") $Month=6;
							if($data=="JUNE") $Month=6;
							if($data=="JUL") $Month=7;							
							if($data=="JULY") $Month=7;
							if($data=="AUG") $Month=8;
							if($data=="SEP") $Month=9;
							if($data=="SEPT") $Month=9;
							if($data=="OCT") $Month=10;
							if($data=="NOV") $Month=11;
							if($data=="DEC") $Month=12;
							break;
						case "{D}":
						case "{DD}":
							$Day=$data;
							break;
						case "{Dsfx}":
						case "{DDsfx}":
							$Day=intval($data);
							break;
						case "{M}":
						case "{MM}":
							$Month=$data;
							break;
						case "{YY}":
							$Year="20".$data;
							break;
						case "{YYYY}":
							$Year=$data;
							break;
						case "Day":
						case "Dy":
							break;
					}
	//	print "ymd: $Year-$Month-$Day<br>";
				}else{
					//print "error unmatched { in date format string<br>";
					$fmt="";
				}
			}else{
				$stf=$str[0];
				if($fc==$stf){
					$str=substr($str,1);
					$fmt=substr($fmt,1);
				}else{
					//print "gggggg: ";
//					include_once "$vars[SITE_ROOT]/include/str_functions.php";
					$strfan=strpos_alnum($str);
					if(!($strfan===FALSE)){
						$str=substr($str,$strfan);
					}
					$fmtfan=strpos($fmt,"{");
					if(!($fmtfan===FALSE)){
						$fmt=substr($fmt,$fmtfan);
					}
					//print "str=$str strfan=$strfan fmt=$fmt fmtfan=$fmtfan<br>";
				}
			}
		}
		
		if($Day<10){
			$Day="0".intval($Day);
		}
		if($Month<10){
			$Month="0".intval($Month);
		}
		if(!$Year){
			//if($Month>0 && $Month<5){
				//$Year="2010";
				$Year=date("Y");
			//}else{
			//	$Year="2008";
			//}
		}
		if(intval($Year)<1000 || intval($Month)<1 || intval($Day)<1){
			return "";
		}
		
		$r="$Year-$Month-$Day";
		$r=str_replace(" ","",$r);
		return $r;
	}
		
	
	if(stristr($str,"Jan"))		$Month="01";
	if(stristr($str,"Feb"))		$Month="02";
	if(stristr($str,"Mar"))		$Month="03";
	if(stristr($str,"Apr"))		$Month="04";
	if(stristr($str,"May"))		$Month="05";
	if(stristr($str,"Jun"))		$Month="06";
	if(stristr($str,"Jul"))		$Month="07";
	if(stristr($str,"Aug"))		$Month="08";
	if(stristr($str,"Sep"))		$Month="09";
	if(stristr($str,"Oct"))		$Month="10";
	if(stristr($str,"Nov"))		$Month="11";
	if(stristr($str,"Dec"))		$Month="12";

	$str=str_replace("Thu","",$str);
	
	$DayAbrPos=strpos($str,"th");
	if(!$DayAbrPos){
		$DayAbrPos=strpos($str,"st");
		if(!$DayAbrPos){
			$DayAbrPos=strpos($str,"nd");
			if(!$DayAbrPos){
				$DayAbrPos=strpos($str,"rd");
			}
		}
	}
	if($DayAbrPos){		
		$Day1=substr($str,$DayAbrPos-2,1);
		if($Day1==" "){
			$Day="0" . substr($str,$DayAbrPos-1,1);
			//print "B";
		}else{
			$Day=substr($str,$DayAbrPos-2,2);
			//print "C";
		}
	}else{
		//print "D ($str)";
	}
	
	
	$Year1=substr($str,strlen($str)-4,2);
	$Year2=substr($str,strlen($str)-3,1);
	$Year3=substr($str,strlen($str)-4,4);
	if(($Year1=="19" || $Year1=="20") && intval($Year3>1900)){
		$Year=substr($str,strlen($str)-4,4);
		$YearSize=5;
	}elseif($Year2==" "){
		$Year="20".substr($str,strlen($str)-2,2);
		$YearSize=3;
	}else{
		$Year=date("Y");
		$YearSize=0;
	}
	
	if(!$Day){
		$Day1=substr($str,(strlen($str)-$YearSize)-1,1);
		if($Day1=","){
			$Day1=substr($str,(strlen($str)-$YearSize)-3,1);
			if($Day1==" "){
				$Day="0" . substr($str,(strlen($str)-$YearSize)-2,1);
			}else{
				$Day=substr($str,(strlen($str)-$YearSize)-3,2);
			}
		}
	}
	
	print "y=$Year m=$Month d=$Day <br>";
	
	if(intval($Year)<1000 || intval($Month)<1 || intval($Day)<1){
		//print "str=$str<br>";
		//12/30 07
		//if(preg_match("/^[01]?[0-9]\/[0-9]{1,2} 0[0-9]$/",$str)){
		if(preg_match("/^[01]?[0-9]\/[0-9]{1,2} 0[0-9]$/",$str)){
			//print "matches! mm/dd yy<br>";
			$Day=strcut($str,"/"," ");
			$Month=strcut($str,"","/");
			$Year=strcut($str," ","");			
			$Year="20".$Year;
		}
		if(preg_match("/^[01]?[0-9]\/[0-9]{1,2}$/",$str)){
			//print "matches! mm/dd<br>";
			$Day=strcut($str,"/","");
			$Month=strcut($str,"","/");
			
			$Year=date("Y");
			//print "y=$Year m=$Month d=$Day <br>";
		}
		
		if($Day<10){
			$Day="0".intval($Day);
		}
		if($Month<10){
			$Month="0".intval($Month);
		}
		if(intval($Year)<1000 || intval($Month)<1 || intval($Day)<1){
			return "";
		}
	}
	
	//print "$Year-$Month-$Day ]v";
	
	return "$Year-$Month-$Day";
}


function strpos_nonalnum($haystack, $offset=0){
	$haystack=strtoupper($haystack);
	$i=0;
	while(1){
		$c=substr($haystack,$i,1);
		if(($c===FALSE)){
	//		print "RETURN break pos_nonalnum: '$c', $i <br>";
			break;
		}
	//	print "pos_nonalnum: '$c', $i <br>";
		if( (ord($c)>=ord('A') && ord($c)<=ord('Z')) || (ord($c)>=ord('0') && ord($c)<=ord('9')) ){
		}else{
		//	print "RETURN pos_nonalnum: '$c', $i <br>";
			return $i;
		}
		$i++;		
	}
//	print "RETURN FALSE pos_nonalnum: '$c', $i <br>";
	return FALSE;	
}


function strpos_alnum($haystack, $offset=0){
	$haystack=strtoupper($haystack);
	$i=$offset;
	while(1){
		$c=substr($haystack,$i,1);
		if(($c===FALSE)){
			break;
		}
		//print "pos_alnum: '$c', $i <br>";
		if( (ord($c)>=ord('A') && ord($c)<=ord('Z')) || (ord($c)>=ord('0') && ord($c)<=ord('9')) ){
			return $i;
		}else{			
		}
		$i++;		
	}
	return FALSE;	
}



function SQLDate2time($in){
	return YYYYMMDD2time($in);
}

function YYYYMMDD2time($in){
	
	$t = split("/",$in);	
	if (count($t)!=3) {
		$t = split("-",$in);
	}
	$c=count($t);		
	if (count($t)!=3) {
		$t = split(" ",$in);
	}
	$c=count($t);	
	if (count($t)!=3) {
		return -1;
	}
//print "YYYYMMDD2time($in) split=($t[0])($t[1])($t[2])<br>";
	if (!is_numeric($t[0])) return -2;
	if (!is_numeric($t[1])) return -3;
	if (!is_numeric($t[2])) return -4;	
	if ($t[0]<1902 || $t[0]>2037) return -5;	
	if ($t[0]<1970){
		$year_offset=1970-$t[0];
		$t[0]=1970;
	}	
	$result=mktime (0,0,0, $t[1], $t[2], $t[0]);
	if($year_offset){
		$result-=$year_offset*365*24*60*60;
	}
	return $result;
}
 
	
function str_compare_count_matching_prefix_chars($a,$b){
	$al=strlen($a); $bl=strlen($b);
	//print "a=$a b=$b\n\n";
	$s=0;
	for($c=0;$c<=$al &&$c<=$bl;$c++){
		if($a[$c]==" " && $b[$c]==" "){
			$s=$c+1;
		}
		if($a[$c]!=$b[$c]){
			return $s;
		}
	}
	if($al<$bl){
		return $al;
	}else{
		return $bl;
	}
}


global $debug_tostring_output_txt; 	$debug_tostring_output_txt=TRUE;
	
function debug_tostring(&$var){
	global $vars;
	global $debug_tostring_indent;
	global $debug_tostring_full_name;
	global $debug_tostring_output_txt;
	$tbr="";
	if(is_array($var)){
		//$tbr.="<div style='border:1px dotted black;margin-left:10px;'>";
	}
	//call to debug_tostring()=<br>
	if(is_array($var) && !$debug_tostring_output_txt){
		$tbr.="<table border=1 cellspacing=0 cellpadding=0><tr><td valign=top>";
	}
	$var_name=variable_name($var);
	if(!$var_name){
		$var_name="variable";
	}else{
		if((!$debug_tostring_output_txt) && (!(is_array($var)) && $debug_tostring_indent)){
			$tbr.="<b>"."$".$var_name."</b>";
		}
		$debug_tostring_full_name=$var_name;
	}
	if(is_bool($var)){
		if($var==TRUE){
			$var_str="TRUE";
		}else{
			$var_str="FALSE";
		}
		$tbr.="(boolean)=".$var_str;
	}elseif(is_float($var)){
		$tbr.="(float)=".$var;
	}elseif(is_int($var)){
		$tbr.="(integer)=".$var;
	}elseif(is_string($var)){
		
			$var=str_replace("INSERT INTO","<font color=green><b>INSERT</b></font> INTO",$var);
			$var=str_replace("DELETE FROM","<font color=green><b>DELETE</b></font> FROM",$var);
			$var=str_replace("UPDATE ","<font color=green><b>UPDATE</b></font> ",$var);
		$tbr.="(string)=\"".$var."\"";
	}elseif(is_array($var)){
		/*
		$tbr.="(array)={<br>";
		//".$var;
		$tmp_indent=$debug_tostring_indent;
		$debug_tostring_full_name_t=$debug_tostring_full_name;
		$debug_tostring_indent.="&nbsp;";
		foreach($var as $i=>$v){
			$debug_tostring_full_name=$debug_tostring_full_name_t."[".$i."]";
			$tbr.="<font style='font-size:7pt;'>"."$"."$debug_tostring_full_name</font>";
			$tbr.="".debug_tostring($var[$i]);
			
		}
		$debug_tostring_full_name=$debug_tostring_full_name_t;
		$debug_tostring_indent=$tmp_indent;
		//$tbr.="}<br>";
		*/
		if(!$debug_tostring_output_txt){
			$tbr.="</td><td valign=top>";
		}
		//		$tbr.="(array)={<br>";
		//".$var;
		$tmp_indent=$debug_tostring_indent;
		$debug_tostring_full_name_t=$debug_tostring_full_name;
		$debug_tostring_indent.="&nbsp;";
		$first=true;
		foreach($var as $i=>$v){
			if($first){
				$debug_tostring_full_name="";
				$debug_tostring_full_name.=$debug_tostring_full_name_t;
				if(!$debug_tostring_output_txt){
					$debug_tostring_full_name.="</td><td valign=top>";
				}
				$debug_tostring_full_name.="[".$i."]";
				$first=false;
			}else{
				$debug_tostring_full_name="[".$i."]";
			}
			if(!is_array($v)){
				$tbr.="$debug_tostring_full_name";
			}
			$value=debug_tostring($var[$i]);
			$full_var_name=$debug_tostring_full_name_t."[".$i."]";
			//$value="<table width=100% border=1 cellpadding=0 cellspacing=0 style='display:inline;margin:7px; border: 1px solid red;'><tr><td>$value</td><td align=right>$full_var_name</td></tr></table>";
			$tbr.="".$value;
			//<font style='font-size:7pt;'>
		}
		$debug_tostring_full_name=$debug_tostring_full_name_t;
		$debug_tostring_indent=$tmp_indent;
		//$tbr.="}<br>";
		
		
		//
		
		
	}elseif(is_int($var)){
		$tbr.="(int)=".$var;
	}elseif(is_null($var)){
		$tbr.="(null)=".$var;
	}elseif(is_resource($var)){
		$tbr.="(resource)=".$var;
	}elseif(is_scalar($var)){
		$tbr.="(scalar)=".$var;
	}elseif(is_object($var)){
		$tbr.="(object)=?"; // =".$var;
	}elseif(is_numeric($var)){
		$tbr.="(numeric)=".$var;
	}else{
		if(!$debug_tostring_output_txt){
			$tbr.="<b><font color=red>(unknown_type)</font></b>=".$var;
		}else{
			$tbr.="(unknown_type)=".$var;
		}
	}
	if(is_array($var)){
	//	$tbr.="</div>";
	}else{
		if(!$debug_tostring_output_txt){
			$tbr.="<br>";
		}else{
			//$tbr.="\n";
		}
	}
	if(is_array($var) && (!$debug_tostring_output_txt) ){
		$tbr.="</td></tr></table>";
	}
	/*
(
get_class() - Returns the name of the class of an object
function_exists() - Return TRUE if the given function has been defined
method_exists() - C
	
	function unserialize2array($data) { 
    $obj = unserialize($data); 
    if(is_array($obj)) return $obj; 
    $arr = array(); 
    foreach($obj as $k=>$v) { 
        $arr[$k] = $v; 
    } 
    unset($arr['__PHP_Incomplete_Class_Name']); 
    return $arr; 
} 
	
	
	
	*/
	return $tbr;
}	
function variable_name( &$var, $scope=false, $prefix='UNIQUE', $suffix='VARIABLE' ){
    if($scope) {
        $vals = $scope;
    } else {
        $vals = $GLOBALS;
    }
    $old = $var;
    $var = $new = $prefix.rand().$suffix;
    $vname = FALSE;
    foreach($vals as $key => $val) {
        if($val === $new) $vname = $key;
    }
    $var = $old;
    return $vname;
}




function remove_duplicate_lines($Lines){
	$out=array();
	foreach(split("\n",$Lines) as $Line){
		$Found=FALSE;
		for($i=0;$i<sizeof($out);$i++){
			if($out[$i]==$Line){
				$Found=TRUE;		
			}
		}
		if(!$Found){
			$out[]=$Line;
		}
	}
	$Out2="";
	foreach($out as $Line){
		if($Out2){
			$Out2.="\n";
		}
		$Out2.=$Line;
	}
	return $Out2;
}




function combine_sameprefixed_lines($LogsCombined){
	global $NumberOfBytesSameLimit;
	$Out="";
	$c=0;
	$LastText="";
	foreach(split("\n",$LogsCombined) as $Line){
		$lpa=split(" ",$Line);
		$Date="$lpa[0] $lpa[1] $lpa[2]";
		$Text=substr($Line,strlen($Date)+1);
		$NumberOfBytesSame=str_compare_count_matching_prefix_chars($Text,$LastText);
		if($Text==$LastText){
		}elseif($NumberOfBytesSame>$NumberOfBytesSameLimit){
			$LineNewPart=substr($Line,$NumberOfBytesSame);
			$Out.= ",& $LineNewPart";
			$LastLine="";
			$LastDate="";
			$LastText="";
		}elseif(!(strstr($Line,"ast message repeated")===FALSE)){
		}else{
			if($Line!=""){
				$c++;
				$Out.= "\n$Line";
				$LastLine=$Line;
				$LastDate=$Date;
				$LastText=$Text;
			}
		}
	}
	return $Out;
}





/*
* This function deletes the given element from a one-dimension array
* Parameters: $array:    the array (in/out)
*             $deleteIt: the value which we would like to delete
*             $useOldKeys: if it is false then the function will re-index the array (from 0, 1, ...)
*                          if it is true: the function will keep the old keys
*				$useDeleteItAsIndex: uses deleteIt for compare against array index/key instead of values
* Returns true, if this value was in the array, otherwise false (in this case the array is same as before)
*/
function deleteFromArray(&$array, $deleteIt, $useOldKeys = FALSE, $useDeleteItAsIndex=FALSE ){
    $tmpArray = array();
    $found = FALSE;
   // print "array="; print_r($array); print "\n";
    foreach($array as $key => $value)
    {
    	//print "k=$key v=$value \n";
        if($useDeleteItAsIndex){
        	$Match=($key !== $deleteIt)==TRUE;
        }else{
        	$Match=($value !== $deleteIt)==TRUE;
        }
        
        if($Match){
        	if($useOldKeys){
        	    $tmpArray[$key] = $value;
            }else{
                $tmpArray[] = $value;
            }
        }else{
            $found = TRUE;
        }
    }
    $array = $tmpArray;
    return $found;
}



function dse_is_osx(){
	global $vars;
	if(isset($vars['DSE']['IS_OSX'])) return $vars['DSE']['IS_OSX'];
	$sw_vers=dse_which("sw_vers");
	if(!$sw_vers){
		$vars['DSE']['IS_OSX']=FALSE;
	}else{
		$OSXVersion =trim(strcut(trim(`sw_vers `),"ProductName:","\n"));
		if($OSXVersion=="Mac OS X"){
			$vars['DSE']['IS_OSX']=TRUE;
		}else{
			$vars['DSE']['IS_OSX']=FALSE;
		}
	}
	return $vars['DSE']['IS_OSX'];
}
function dse_is_ubuntu(){
	global $vars;
	if(isset($vars['DSE']['IS_UBUNTU'])) return $vars['DSE']['IS_UBUNTU'];
	if(!file_exists("/etc/issue")){
		$vars['DSE']['IS_UBUNTU']=FALSE;
	}else{
		$EtcIssue=dse_file_get_contents("/etc/issue");
		if(str_contains($EtcIssue,"Ubuntu")){
			$vars['DSE']['IS_UBUNTU']=TRUE;
		}else{
			$vars['DSE']['IS_UBUNTU']=FALSE;
		}
	}
	return $vars['DSE']['IS_UBUNTU'];
}
function dse_is_centos(){
	global $vars;
	if(isset($vars['DSE']['IS_CENTOS'])) return $vars['DSE']['IS_CENTOS'];
	if(!file_exists("/etc/issue")){
		$vars['DSE']['IS_CENTOS']=FALSE;
	}else{
		$EtcIssue=dse_file_get_contents("/etc/issue");
		if(str_contains($EtcIssue,"CentOS")){
			$vars['DSE']['IS_CENTOS']=TRUE;
		}else{
			$vars['DSE']['IS_CENTOS']=FALSE;
		}
	}
	return $vars['DSE']['IS_CENTOS'];
}


function get_load(){
	global $vars;
	if(dse_is_osx()){
		$this_loadavg=`uptime 2>&1`;
		$this_loadavg=strcut($this_loadavg,"oad averages: ","\n");
		if($this_loadavg!=""){  
			$loadaggA=split('\s',$this_loadavg);
			return number_format($loadaggA[0],3);
		}
	}else{
		$this_loadavg=`cat /proc/loadavg 2>&1`;
		if($this_loadavg!=""){  
			$loadaggA=split('\t',$this_loadavg);
			return number_format($loadaggA[0],3);
		}
	}
	return -1;
}

function dse_get_key(){
	global $vars;
	$keys=$vars['dse_get_key__keys'];
	while($keys==""){
		$keys=readline_timeout(1, '');
	}
	if(strlen($keys)>1){
		$vars['dse_get_key__keys']=substr($keys,1);
		return substr($keys,0,1);
	}else{
		$vars['dse_get_key__keys']="";
		return $keys;
	}
}	
	
function readline_timeout($sec, $def){ 
    return trim(shell_exec('bash -c ' . 
        escapeshellarg('phprlto=' . 
            escapeshellarg($def) . ';' . 
            'read -n 1 -t ' . ((int)$sec) . ' phprlto;' . 
            'echo "$phprlto"'))); 
/*
$STDIN_Content="";
$fd = fopen("php://stdin", "r"); 
while (!feof($fd)) {
	$STDIN_Content .= fread($fd, 1024);
}
*/
} 


function dse_cli_get_paramaters_array($parameters_details){
	global $vars;
	$parameters=array();
	foreach($parameters_details as $p){
		$parameters[$p[0]]=$p[1];
	}
	return $parameters;
}



function dse_cli_get_usage($parameters_details){
	global $vars;

	if($vars['DSE']['SCRIPT_COMMAND_FORMAT']){
		$CommandFormat=$vars['DSE']['SCRIPT_COMMAND_FORMAT'];
	}else{
		$CommandFormat="(options)";
	}
	
	$Usage="\n   ". $vars['DSE']['SCRIPT_NAME']." - " . $vars['DSE']['SCRIPT_DESCRIPTION_BRIEF'] . "\n";
	$Usage.="       part of https://github.com/devity/dse  - by Louy of Devity.com\n\n";
	$Usage.=getColoredString("command line usage:","yellow","black");
	$Usage.=getColoredString(" ". $vars['DSE']['SCRIPT_FILENAME'],"cyan","black");
	$Usage.=getColoredString(" $CommandFormat","dark_cyan","black");
	$Usage.="\n\noptions: \n";
	foreach($parameters_details as $parameter){
		$f=$parameter[0];
		$flag=$parameter[1];
		$details=$parameter[2];
		$f=str_replace(":","",$f);
		$flag=str_replace(":","",$flag);
		
		$indent="";
		if(strlen($flag)<9){
			$indent="\t";
		}
		$indent.="\t- ";
		
		if($f!=""){
			$Usage.=" -${f}, --${flag}$indent $details\n";
		}else{
			$Usage.="     --${flag}$indent $details\n";
		}
	}
	$Usage.="\n";
	return $Usage;
}

//////////////////////////////////////////////////////////////////////////////////////////

function _getopt ( ) {

/* _getopt(): Ver. 1.3      2009/05/30
   My page: http://www.ntu.beautifulworldco.com/weblog/?p=526

Usage: _getopt ( [$flag,] $short_option [, $long_option] );

Note that another function split_para() is required, which can be found in the same
page.

_getopt() fully simulates getopt() which is described at
http://us.php.net/manual/en/function.getopt.php , including long options for PHP
version under 5.3.0. (Prior to 5.3.0, long options was only available on few systems)

Besides legacy usage of getopt(), I also added a new option to manipulate your own
argument lists instead of those from command lines. This new option can be a string
or an array such as 

$flag = "-f value_f -ab --required 9 --optional=PK --option -v test -k";
or
$flag = array ( "-f", "value_f", "-ab", "--required", "9", "--optional=PK", "--option" );

So there are four ways to work with _getopt(),

1. _getopt ( $short_option );

  it's a legacy usage, same as getopt ( $short_option ).

2. _getopt ( $short_option, $long_option );

  it's a legacy usage, same as getopt ( $short_option, $long_option ).

3. _getopt ( $flag, $short_option );

  use your own argument lists instead of command line arguments.

4. _getopt ( $flag, $short_option, $long_option );

  use your own argument lists instead of command line arguments.

*/

  if ( func_num_args() == 1 ) {
     $flag =  $flag_array = $GLOBALS['argv'];
     $short_option = func_get_arg ( 0 );
     $long_option = array ();
  } else if ( func_num_args() == 2 ) {
     if ( is_array ( func_get_arg ( 1 ) ) ) {
        $flag = $GLOBALS['argv'];
        $short_option = func_get_arg ( 0 );
        $long_option = func_get_arg ( 1 );
     } else {
        $flag = func_get_arg ( 0 );
        $short_option = func_get_arg ( 1 );
        $long_option = array ();
     }
  } else if ( func_num_args() == 3 ) {
     $flag = func_get_arg ( 0 );
     $short_option = func_get_arg ( 1 );
     $long_option = func_get_arg ( 2 );
  } else {
     exit ( "wrong options\n" );
  }

  $short_option = trim ( $short_option );

  $short_no_value = array();
  $short_required_value = array();
  $short_optional_value = array();
  $long_no_value = array();
  $long_required_value = array();
  $long_optional_value = array();
  $options = array();

  for ( $i = 0; $i < strlen ( $short_option ); ) {
     if ( $short_option{$i} != ":" ) {
        if ( $i == strlen ( $short_option ) - 1 ) {
          $short_no_value[] = $short_option{$i};
          break;
        } else if ( $short_option{$i+1} != ":" ) {
          $short_no_value[] = $short_option{$i};
          $i++;
          continue;
        } else if ( $short_option{$i+1} == ":" && $short_option{$i+2} != ":" ) {
          $short_required_value[] = $short_option{$i};
          $i += 2;
          continue;
        } else if ( $short_option{$i+1} == ":" && $short_option{$i+2} == ":" ) {
          $short_optional_value[] = $short_option{$i};
          $i += 3;
          continue;
        }
     } else {
        continue;
     }
  }

  foreach ( $long_option as $a ) {
     if ( substr( $a, -2 ) == "::" ) {
        $long_optional_value[] = substr( $a, 0, -2);
        continue;
     } else if ( substr( $a, -1 ) == ":" ) {
        $long_required_value[] = substr( $a, 0, -1 );
        continue;
     } else {
        $long_no_value[] = $a;
        continue;
     }
  }

  if ( is_array ( $flag ) )
     $flag_array = $flag;
  else {
     $flag = "- $flag";
     $flag_array = split_para( $flag );
  }

  for ( $i = 0; $i < count( $flag_array ); ) {

     if ( $i >= count ( $flag_array ) )
        break;

     if ( ! $flag_array[$i] || $flag_array[$i] == "-" ) {
        $i++;
        continue;
     }

     if ( $flag_array[$i]{0} != "-" ) {
        $i++;
        continue;

     }

     if ( substr( $flag_array[$i], 0, 2 ) == "--" ) {

        if (strpos($flag_array[$i], '=') != false) {
          list($key, $value) = explode('=', substr($flag_array[$i], 2), 2);
          if ( in_array ( $key, $long_required_value ) || in_array ( $key, $long_optional_value ) )
             $options[$key][] = $value;
          $i++;
          continue;
        }

        if (strpos($flag_array[$i], '=') == false) {
          $key = substr( $flag_array[$i], 2 );
          if ( in_array( substr( $flag_array[$i], 2 ), $long_required_value ) ) {
             $options[$key][] = $flag_array[$i+1];
             $i += 2;
             continue;
          } else if ( in_array( substr( $flag_array[$i], 2 ), $long_optional_value ) ) {
             if ( $flag_array[$i+1] != "" && $flag_array[$i+1]{0} != "-" ) {
                $options[$key][] = $flag_array[$i+1];
                $i += 2;
             } else {
                $options[$key][] = FALSE;
                $i ++;
             }
             continue;
          } else if ( in_array( substr( $flag_array[$i], 2 ), $long_no_value ) ) {
             $options[$key][] = FALSE;
             $i++;
             continue;
          } else {
             $i++;
             continue;
          }
        }

     } else if ( $flag_array[$i]{0} == "-" && $flag_array[$i]{1} != "-" ) {

        for ( $j=1; $j < strlen($flag_array[$i]); $j++ ) {
          if ( in_array( $flag_array[$i]{$j}, $short_required_value ) || in_array( $flag_array[$i]{$j}, $short_optional_value )) {

             if ( $j == strlen($flag_array[$i]) - 1  ) {
                if ( in_array( $flag_array[$i]{$j}, $short_required_value ) ) {
                  $options[$flag_array[$i]{$j}][] = $flag_array[$i+1];
                  $i += 2;
                } else if ( in_array( $flag_array[$i]{$j}, $short_optional_value ) && $flag_array[$i+1] != "" && $flag_array[$i+1]{0} != "-" ) {
                  $options[$flag_array[$i]{$j}][] = $flag_array[$i+1];
                  $i += 2;
                } else {
                  $options[$flag_array[$i]{$j}][] = FALSE;
                  $i ++;
                }
                $plus_i = 0;
                break;
             } else {
                $options[$flag_array[$i]{$j}][] = substr ( $flag_array[$i], $j + 1 );
                $i ++;
                $plus_i = 0;
                break;
             }

          } else if ( in_array ( $flag_array[$i]{$j}, $short_no_value ) ) {

             $options[$flag_array[$i]{$j}][] = FALSE;
             $plus_i = 1;
             continue;

          } else {
             $plus_i = 1;
             break;
          }
        }

        $i += $plus_i;
        continue;

     }

     $i++;
     continue;
  }

  foreach ( $options as $key => $value ) {
     if ( count ( $value ) == 1 ) {
        $options[ $key ] = $value[0];

     }

  }

  return $options;

}

function split_para ( $pattern ) {

/* split_para() version 1.0      2008/08/19
   My page: http://www.ntu.beautifulworldco.com/weblog/?p=526

This function is to parse parameters and split them into smaller pieces.
preg_split() does similar thing but in our function, besides "space", we
also take the three symbols " (double quote), '(single quote),
and \ (backslash) into consideration because things in a pair of " or '
should be grouped together.

As an example, this parameter list

-f "test 2" -ab --required "t\"est 1" --optional="te'st 3" --option -v 'test 4'

will be splited into

-f
t"est 2
-ab
--required
test 1
--optional=te'st 3
--option
-v
test 4

see the code below,

$pattern = "-f \"test 2\" -ab --required \"t\\\"est 1\" --optional=\"te'st 3\" --option -v 'test 4'";

$result = split_para( $pattern );

echo "ORIGINAL PATTERN: $pattern\n\n";

var_dump( $result );

*/

  $begin=0;
  $backslash = 0;
  $quote = "";
  $quote_mark = array();
  $result = array();

  $pattern = trim ( $pattern );

  for ( $end = 0; $end < strlen ( $pattern ) ; ) {

     if ( ! in_array ( $pattern{$end}, array ( " ", "\"", "'", "\\" ) ) ) {
        $backslash = 0;
        $end ++;
        continue;
     }

     if ( $pattern{$end} == "\\" ) {
        $backslash++;
        $end ++;
        continue;
     } else if ( $pattern{$end} == "\"" ) {
        if ( $backslash % 2 == 1 || $quote == "'" ) {
          $backslash = 0;
          $end ++;
          continue;
        }

        if ( $quote == "" ) {
          $quote_mark[] = $end - $begin;
          $quote = "\"";
        } else if ( $quote == "\"" ) {
          $quote_mark[] = $end - $begin;
          $quote = "";
        }

        $backslash = 0;
        $end ++;
        continue;
     } else if ( $pattern{$end} == "'" ) {
        if ( $backslash % 2 == 1 || $quote == "\"" ) {
          $backslash = 0;
          $end ++;
          continue;
        }

        if ( $quote == "" ) {
          $quote_mark[] = $end - $begin;
          $quote = "'";
        } else if ( $quote == "'" ) {
          $quote_mark[] = $end - $begin;
          $quote = "";
        }

        $backslash = 0;
        $end ++;
        continue;
     } else if ( $pattern{$end} == " " ) {
        if ( $quote != "" ) {
          $backslash = 0;
          $end ++;
          continue;
        } else {
          $backslash = 0;
          $cand = substr( $pattern, $begin, $end-$begin );
          for ( $j = 0; $j < strlen ( $cand ); $j ++ ) {
             if ( in_array ( $j, $quote_mark ) )
                continue;

             $cand1 .= $cand{$j};
          }
          if ( $cand1 ) {
             eval( "\$cand1 = \"$cand1\";" );
             $result[] = $cand1;
          }
          $quote_mark = array();
          $cand1 = "";
          $end ++;
          $begin = $end;
          continue;
       }
     }
  }

  $cand = substr( $pattern, $begin, $end-$begin );
  for ( $j = 0; $j < strlen ( $cand ); $j ++ ) {
     if ( in_array ( $j, $quote_mark ) )
        continue;

     $cand1 .= $cand{$j};
  }

  eval( "\$cand1 = \"$cand1\";" );

  if ( $cand1 )
     $result[] = $cand1;

  return $result;
}
////////////////////////////////////////////////////////////////////////////////////


function dse_log_parse_apache_La_set_Time($La){
	global $vars;
	$TimeStr=substr($La[3],1);
	$dp=split(":",$TimeStr);
	$format = '%d/%m/%Y %H:%M:%S';
	$Time=strptime($TimeStr, $format);
//	include_once ("$vars[SITE_ROOT]/include/date_str_parse.php");
	$SQLDate=date_str_to_sql_date($dp[0],"{DD}/{Mon}/{YYYY}:");
	$La[Time]=SQLDate2time($SQLDate)+$dp[1]*60*60+$dp[2]*60+$dp[3];
	return $La;
}  



// ************* COLOR / TERMINAL  *** COLOR / TERMINAL  *** COLOR / TERMINAL  *** COLOR / TERMINAL  *** COLOR / TERMINAL  
// ************* COLOR / TERMINAL  *** COLOR / TERMINAL  *** COLOR / TERMINAL  *** COLOR / TERMINAL  *** COLOR / TERMINAL  
// ************* COLOR / TERMINAL  *** COLOR / TERMINAL  *** COLOR / TERMINAL  *** COLOR / TERMINAL  *** COLOR / TERMINAL  
// ************* COLOR / TERMINAL  *** COLOR / TERMINAL  *** COLOR / TERMINAL  *** COLOR / TERMINAL  *** COLOR / TERMINAL  
	 
function test_all_shell_colors(){
	global $vars;
	print "\n\nForground Codes: ";
	for($p1=0;$p1<=11;$p1++){
		for($p2=0;$p2<100;$p2++){
			$foreground_color="$p1;$p2";
			$background_color="black";
			print "  ". getColoredString($foreground_color, $foreground_color, $background_color);
			foreach($vars[shell_foreground_colors] as $ColorName=>$ColorCode){
				if($ColorCode==$foreground_color){
					print "(".getColoredString($ColorName, $foreground_color, $background_color).")";
				}
			}
		}
		print "\n--------------";
	}
	print "\n\n";
	
	print "\n\nBackground Codes: ";
	for($p1=0;$p1<=510;$p1++){
		$background_color="$p1";
		$foreground_color="white";
		print "  ". getColoredString($background_color, $foreground_color, $background_color);
		foreach($vars[shell_background_colors] as $ColorName=>$ColorCode){
			if($ColorCode==$background_color){
				print "(".getColoredString($ColorName, $foreground_color, $background_color).")";
			}
		}
	}
	print "\n\n";
}

function dse_bt_colorize($v,$t,$type="MAXIMUM",$v_str=""){
	global $vars;
	if($v_str==""){
		$v_str=$v;
	}
	if($type=="MAXIMUM"){
		if($v>=$t*3/2){
			return getColoredString($v_str, 'white', 'red');
		}elseif($v>=$t){
			return getColoredString($v_str, 'pink', 'black');
		}elseif($v>=$t*2/3){
			return getColoredString($v_str, 'yellow', 'black');
		}else{
			return getColoredString($v_str, 'green', 'black');
		}
	}elseif($type=="MINIMUM"){
		if($v<=$t/3){
			return getColoredString($v_str, 'white', 'red');
		}elseif($v<=$t){
			return getColoredString($v_str, 'pink', 'black');
		}elseif($v<=$t*2/3){
			return getColoredString($v_str, 'yellow', 'black');
		}else{
			return getColoredString($v_str, 'green', 'black');
		}
	}
	
}



	$vars[shell_foreground_colors] = array();
	$vars[shell_background_colors] = array();
	 
			
	
	$vars[shell_foreground_colors]['blink'] = '0;5';
	
	
	$vars[shell_foreground_colors]['white'] = '1;37';
	$vars[shell_foreground_colors]['grey'] = '0;2';
	$vars[shell_foreground_colors]['lightest_grey'] = '1;37';
	$vars[shell_foreground_colors]['light_grey'] = '6;37';
	$vars[shell_foreground_colors]['dark_grey'] = '1;30';
	$vars[shell_foreground_colors]['black'] = '0;30';
	
	$vars[shell_foreground_colors]['blink_red'] = '5;91';
	$vars[shell_foreground_colors]['red'] = '0;31';
	$vars[shell_foreground_colors]['pink'] = '1;31';
	$vars[shell_foreground_colors]['light_red'] = '1;31';
	$vars[shell_foreground_colors]['dark_red'] = '2;91';
	
	$vars[shell_foreground_colors]['blink_green'] = '5;92';
	$vars[shell_foreground_colors]['green'] = '0;92';
	$vars[shell_foreground_colors]['bold_green'] = '1;92';
	$vars[shell_foreground_colors]['dark_green'] = '2;32';
	
	$vars[shell_foreground_colors]['blink_yellow'] = '5;93';
	$vars[shell_foreground_colors]['brown'] = '10;33';
	$vars[shell_foreground_colors]['orange'] = '10;33';
	$vars[shell_foreground_colors]['yellow'] = '0;93';
	$vars[shell_foreground_colors]['bold_yellow'] = '1;93';
	
	$vars[shell_foreground_colors]['dark_blue'] = '0;34';
	$vars[shell_foreground_colors]['blue'] = '1;34';
	$vars[shell_foreground_colors]['light_blue'] = '1;94';
	
	$vars[shell_foreground_colors]['purple'] = '0;35';
	$vars[shell_foreground_colors]['light_purple'] = '1;35';
	$vars[shell_foreground_colors]['dark_purple'] = '2;35';
	
	$vars[shell_foreground_colors]['dark_cyan'] = '0;36';
	$vars[shell_foreground_colors]['cyan'] = '1;36';
	
	 
	 
	 
	$vars[shell_background_colors]['white'] = '107';
	$vars[shell_background_colors]['grey'] = '47';
	$vars[shell_background_colors]['black'] = '40';
	$vars[shell_background_colors]['dark_red'] = '41';
	$vars[shell_background_colors]['dark_green'] = '42';
	$vars[shell_background_colors]['dark_yellow'] = '43';
	$vars[shell_background_colors]['dark_blue'] = '44';
	$vars[shell_background_colors]['dark_magenta'] = '45';
	$vars[shell_background_colors]['dark_cyan'] = '46';
	
	$vars[shell_background_colors]['red'] = '101';
	$vars[shell_background_colors]['green'] = '102';
	$vars[shell_background_colors]['yellow'] = '103';
	$vars[shell_background_colors]['orange'] = '43';
	$vars[shell_background_colors]['blue'] = '104';
	$vars[shell_background_colors]['magenta'] = '105';
	$vars[shell_background_colors]['cyan'] = '106';
	
function getColoredString($string, $foreground_color = null, $background_color = null) {
	global $vars;
	

	
	$colored_string = "";
	$colored_string .= "\033[0m";
	if(TRUE || $background_color=="black"){
	}else{
		if( (intval($background_color)<=0 || $background_color=="0") && isset($vars[shell_background_colors][$background_color])) {
			$colored_string .= "\033[" . $vars[shell_background_colors][$background_color] . "m";
		}elseif( intval($background_color)<=0 ) {
			$colored_string .= "\033[" . $vars[shell_foreground_colors]['red'] . "m";
			$colored_string .= " Unknown Shell Bckground Color: ($background_color) ";
		}else{
			$colored_string .= "\033[" . $background_color . "m";
		}
	}
	if( (intval($foreground_color)<=0 || $foreground_color=="0") && isset($vars[shell_foreground_colors][$foreground_color])) {
		$colored_string .= "\033[" . $vars[shell_foreground_colors][$foreground_color] . "m";
	}elseif( intval($foreground_color)<=0 ) {
		$colored_string .= "\033[" . $vars[shell_foreground_colors]['red'] . "m";
		$colored_string .= " Unknown Shell Foreground Color: ($foreground_color) ";
	}else{
		$colored_string .= "\033[" . $foreground_color . "m";
	}
	
	
	$colored_string .=  $string;
	if($vars[shell_colors_reset_foreground]!=""){
		$colored_string .= "\033[0m";
		$colored_string .= "\033[".$vars[shell_foreground_colors][$vars[shell_colors_reset_foreground]]."m";
		if($vars[shell_colors_reset_background]!=""){
			$colored_string .= "\033[".$vars[shell_background_colors][$vars[shell_colors_reset_background]]."m";
		}else{
			$colored_string .= "\033[".$vars[shell_background_colors]['black']."m";
		}
	}else{
		$colored_string .= "\033[0m";
	}
	return $colored_string;
}

function setBackgroundColor($background_color) {
	global $vars;
	$colored_string = "";
	//$colored_string .= "\033[0m";
	if( (intval($background_color)<=0 || $background_color=="0") && isset($vars[shell_background_colors][$background_color])) {
		$colored_string .= "\033[" . $vars[shell_background_colors][$background_color] . "m";
	}
	return $colored_string;
}
function setForgroundColor($foreground_color) {
	global $vars;
	$colored_string = "";
	//$colored_string .= "\033[0m";
	if( (intval($foreground_color)<=0 || $foreground_color=="0") && isset($vars[shell_foreground_colors][$foreground_color])) {
		$colored_string .= "\033[" . $vars[shell_foreground_colors][$foreground_color] . "m";
	}
	return $colored_string;
}
	
	
	
function getForegroundColors() {
	global $vars;
	return array_keys($vars[shell_foreground_colors]);
}
 
function getBackgroundColors() {
	global $vars;
	return array_keys($vars[shell_background_colors]);
}
	
 
 
 

function sbp_cursor_postion($L=0,$C=0){
        print "\033[${L};${C}H";
}
//function sbp_cursor_column($C=0){
  //      print "\033[;${C}H";
//}
function cbp_screen_clear(){
        print "\033[2J";
}
function cbp_cursor_left($N=1){
        print "\033[${N}D";
}
function cbp_cursor_up($N=1){
        print "\033[${N}A";
}
function cbp_characters_clear($N=1){
		print "\033[${N}D";
		for($i=0;$i<$n;$i++) print " ";
        print "\033[${N}D";
}


// ************ System Stats    ** System Stats    ** System Stats    ** System Stats    ** System Stats    
// ************ System Stats    ** System Stats    ** System Stats    ** System Stats    ** System Stats    
// ************ System Stats    ** System Stats    ** System Stats    ** System Stats    ** System Stats    
// ************ System Stats    ** System Stats    ** System Stats    ** System Stats    ** System Stats    

function dse_proc_io_get($Reset=FALSE){
	global $vars,$procIOs;
	//print "dse_proc_io_get_start_time=$vars[dse_proc_io_get_start_time] \n";
	//print "sizeof($procIOs)=".sizeof($procIOs)." \n";
	
	$ps=`sudo ps aux`;
	$time=time();
	if(sizeof($procIOs)==0) $Reset=TRUE;
	$vars[dse_proc_io_get_last_time]=$time;		
	if($Reset){
		$start_time=$time;
		$vars[dse_proc_io_get_start_time]=$start_time;
		$procIOs=array($time);
		//$ps="7487";
		foreach(split("\n", $ps) as $pse){
			//print "$pse \n";
			$pse=str_replace("  "," ",$pse);
			$pse=str_replace("  "," ",$pse);
			$pse=str_replace("  "," ",$pse);
			$psea=split(" ",$pse);
			//print_r($psea);
			if($psea[1]){
				$PIDs[]=$psea[1];
				$procIOs[$time][$psea[1]]=dse_get_proc_io_as_array($psea[1]);
				$w=dse_get_proc_io_as_array($psea[1]);
			//	print "procIOs[$time][$psea[1]]['wchar']=".$w['wchar']."; \n";
				//print "PID:$psea[1]\n";
			//	print debug_tostring($procIOs[$time][$psea[1]])."\n";
				
				
			}
		}
	}else{
		$start_time=$vars[dse_proc_io_get_start_time];
		//sleep(3);
		$ps=`sudo ps aux`;
		$time=time();
		$time_diff=$time-$start_time;
		//print "timediff=$time_diff\n";
		$procIOs[$time]=array();
		$wt=0; $rt=0;
		//$ps="7487";
		foreach(split("\n", $ps) as $pse){
			//print "PID: $pse \n";
			$pse=str_replace("  "," ",$pse);
			$pse=str_replace("  "," ",$pse);
			$pse=str_replace("  "," ",$pse);
			$psea=split(" ",$pse);
			//print_r($psea);
			if($psea[1]){ 
				$PID=$psea[1];
				$PIDs[]=$PID;

				$procIOs[$time][$PID]=dse_get_proc_io_as_array($PID);
			//	print "dw=procIOs[$time][$PID]['wchar']-procIOs[$start_time][$PID]['wchar']\n";
			//	print "dw=".$procIOs[$time][$PID]['wchar']."-".$procIOs[$start_time][$PID]['wchar']."\n";
				//print "start_time=$start_time\n";
				//print_r ($procIOs[$time][$PID]); 
				//print_r ($procIOs[$start_time][$PID]); 
				$dw=$procIOs[$time][$PID]['wchar']-$procIOs[$start_time][$PID]['wchar'];
				$dr=$procIOs[$time][$PID]['rchar']-$procIOs[$start_time][$PID]['rchar'];
				$dwb=$procIOs[$time][$PID]['read_bytes']-$procIOs[$start_time][$PID]['read_bytes'];
				$drb=$procIOs[$time][$PID]['write_bytes']-$procIOs[$start_time][$PID]['write_bytes'];
				$wt+=$dw;
				$rt+=$dr;
				$wbt+=$dwb;
				$rbt+=$drb;
				$wps=intval($dw/$time_diff);
				$rps=intval($dr/$time_diff);
				$wbps=intval($dwb/$time_diff);
				$rbps=intval($drb/$time_diff);
				$wtps=intval($wt/$time_diff);
				$rtps=intval($rt/$time_diff);
				$wbtps=intval($wbt/$time_diff);
				$rbtps=intval($rbt/$time_diff);
				
				if($wps>0){
					$exe=`echo $PID | /dse/bin/pid2exe 2>/dev/null`;
					//print "EXE: $exe PID:$psea[1] dt=$time_diff  	 dW=$dw ($wps/s)    dR=$dr ($rps/s)   dWb=$dwb ($wbps/s)    dRb=$drb ($rbps/s)  \n";
					//print debug_tostring($procIOs[$time][$PID)."\n";
				
				}
				$procIOs[$time]['TOTAL']['wchar']=$wt;
				$procIOs[$time]['TOTAL']['rchar']=$rt;
				$procIOs[$time]['TOTAL']['read_bytes']=$rbt;
				$procIOs[$time]['TOTAL']['write_bytes']=$wbt;
				//print "\n";
			}
		}
		//print "Totals:  w:$wt ($wtps/s)    r:$rt ($rtps/s)    dWb=$wbt ($wbtps/s)    dRb=$rbt ($rbtps/s) \n\n";
	
	}
	//return $procIOs;
}





function rrmdir($dir) {
	global $rrmdir_test_only;
   	if (is_dir($dir)) {
     $objects = scandir($dir);
     foreach ($objects as $object) {
       if ($object !="" && $object != "." && $object != "..") {
         if (filetype($dir."/".$object) == "dir") {
         	if($rrmdir_test_only){
         		print " rrmdir($dir/$object); \n";
         	}else{
         		rrmdir($dir."/".$object); 
         	}
         }else {
         	if($rrmdir_test_only){
         		print "unlink($dir/$object); \n";
         	}else{
         		unlink($dir."/".$object);
        	}
         }
       }
     }
     reset($objects);
     rmdir($dir);
   }
 }
 
 


function http_get($URL,$PostData=""){
	global $vars;
	return http_lynx_get($URL);
}
 
function http_lynx_get($URL){
	$URL=str_replace("\"","%34",$URL);
	$URL=str_replace("\n","",$URL);
	//return `/usr/bin/lynx -connect_timeout=10 -source "$URL"`;	
	$wget=dse_which("wget");
	if($wget){
		return `$wget -qO- "$URL"`;	
	}else{
		if(file_exists("/usr/bin/wget")){
			return `/usr/bin/wget -qO- "$URL"`;	
		}else{
			print getColoredString("ERROR: no wget\n","red","black");
			return "";
		}
	}
}
 


?>