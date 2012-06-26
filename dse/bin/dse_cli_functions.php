<?
ini_set('display_errors','On');
ini_set('display_startup_errors','On');
ini_set('log_errors','On');
error_reporting( (E_ALL & ~E_NOTICE) ^ E_DEPRECATED);
	

$OK=getColoredString("OK","green","black");
$Fixed=getColoredString("Fixed","green","black");
$Added=getColoredString("Added","green","black");
$Failed=getColoredString("Failed","red","black");
$NotOK=getColoredString("Not OK","red","black");
$Missing=getColoredString("Missing","red","black");
$NotChanged=getColoredString("Not Changed","orange","black");
$NotFixed=getColoredString("Not Changed","orange","black");

if (!function_exists("readline")) { function readline( $prompt = '' ){
    echo $prompt;
    return rtrim( fgets( STDIN ), "\n" );
}}

function dse_hostname(){
	global $vars;
	if($vars['DSE']['HOSTNAME']) return $vars['DSE']['HOSTNAME'];
	$tbr=trim(`hostname`);
	if(dse_is_osx()) $tbr=str_remove($tbr,".local");
	return $tbr;
}

function dse_pid_get_exe_tree($PID,$Reverse=FALSE){
	global $vars;
	$tbr="";
	if($PID<0){
		return "";
	}elseif($PID==0){
		return "0";
	}elseif($PID==1){
		$PIDInfo=dse_pid_get_info($PID);
		return $PIDInfo['EXE_FILE'];
	}else{
		$PIDInfo=dse_pid_get_info($PID);
		$Parent=dse_pid_get_exe_tree($PIDInfo['PPID']);
		if($Reverse){
			$tbr=$Parent ."->". $PIDInfo['EXE_FILE'];
		}else{
			$tbr=$PIDInfo['EXE_FILE'] ."->". $Parent;
		}
	}
	return $tbr;
}
	
function dpv($MinVerbosity,$Message){
	global $vars;
	if($vars['Verbosity']>=$MinVerbosity){
		$Message=colorize(substr($Message,0,cbp_get_screen_width()-2),"yellow")."\n";
		print $Message;
	}
}

function dse_log($Message,$File=""){
	global $vars;
	$Command="";
	$Message=dse_date_format()."  ".str_replace("\"","\\\"",$Message);
	if(!$File){
		if($vars['DSE']['SCRIPT_LOG_FILE']) {
			if($vars['DSE']['SCRIPT_LOG_FILE']=="NO") {
				return ;	
			}
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
	//dse_log("c=$Command r=$r");
	return(str_contains($r,"succeeded"));
}

function dse_date_format($Time="NOW",$FormatName="FULLREADABLE"){
	global $vars;
	if($Time=="NOW") $Time=time();
	if(str_contains($FormatName," ")){
		return @date($FormatName,$Time);
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
	$r=@date($FormatString,$Time);
	return $r;
}


function seconds_to_text($Seconds){
	global $vars;
	if($Seconds<60*3){
		if($vars['s2t_abvr'])return "$Seconds secs";
		return "$Seconds seconds";
	}elseif($Seconds<60*60*2){
		$Minutes=intval($Seconds/60);
		if($vars['s2t_abvr'])return "$Minutes mins";
		return "$Minutes minutes";
	}elseif($Seconds<60*60*24*2){
		$Hours=intval($Seconds/(60*60));
		if($vars['s2t_abvr'])return "$Hours hrs";
		return "$Hours hours";
	}elseif($Seconds<60*60*24*30*2){
		$Days=intval($Seconds/(60*60*24));
		if($vars['s2t_abvr'])return "$Days dys";
		return "$Days days";
	}elseif($Seconds<60*60*24*30*12*2){
		$Months=intval($Seconds/(60*60*24*30));
		if($vars['s2t_abvr'])return "$Months mon";
		return "$Months months";
	}else{
		$Years=intval($Seconds/(60*60*24*30*12));
		if($vars['s2t_abvr'])return "$Years yr";
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
	$tbr="";
	while (!feof($handle)) {
        $tbr.= fgets($handle);
        flush();
        ob_flush();
        flush();
	}
	pclose($handle);
	return $tbr;
}
		
		
function dse_ask_yn($Question,$Default="",$Timeout=""){
	global $vars;
	print getColoredString("$Question ","red");
	print getColoredString(" (","purple");
	print getColoredString("Y","yellow");
	print getColoredString("/","purple");
	print getColoredString("N","yellow");
	print getColoredString(") ","purple");
	print getColoredString("  Choice? ","blink_red");
	$key=strtoupper(dse_get_key($Timeout,$Default));
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
function dse_ask_entry($Question="Enter Response:",$Default="",$Timeout=""){
	global $vars;
	print getColoredString("$Question\n","red");
	return readline();
/*	$tbr="";
	while(TRUE){
		$K=dse_get_key($Timeout,"\n");
		if($K=="\n") return $tbr;
		$tbr.=$K;
	}*/
}
	
function dse_ask_choice($Options,$Question="Select an option:",$Default="",$Timeout=""){
	global $vars;
	print getColoredString("$Question\n","red");
	$Keys="";
	$Os=sizeof($Options);$Oi=0;
	foreach($Options as $K=>$O){  $Oi++;
		if($Keys){
			if($Oi==$Os){
				$Keys.=", or ".$K;
			}else{
				$Keys.=", ".$K;
			}
		}else{
			$Keys.=$K;
		}
		print getColoredString("  $K","yellow").getColoredString(" ) ","purple").getColoredString("   $O\n","green");
	}
	print "                    Press ".getColoredString("$Keys","yellow").getColoredString("  Choice? ","blink_red");
	$key=strtoupper(dse_get_key($Timeout,$Default));
	$Oi=0;
	foreach($Options as $K=>$O){  $Oi++;
		if($key==$K){
			print "\n";
			return $K;
		}
	}
	print getColoredString(" Invalid Option. You pressed '$key'. \n","red","black");
	return dse_ask_choice($Question,$Options,$Default,$Timeout);
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
	
	$PIDInfo['EXE_FILE']=$PIDInfo['EXE'];
	while(str_contains($PIDInfo['EXE_FILE'],"/")){
		$PIDInfo['EXE_FILE']=strcut($PIDInfo['EXE_FILE'],"/");
	}
	return $PIDInfo;
}
function dse_pid_get_info_str($PID,$Recursive=FALSE,$Reverse=FALSE){
	global $vars;
	$PIDInfo=dse_pid_get_info($PID);
	$tbr="";
	if($vars['DSE']['OUTPUT_FORMAT']=="HTML"){
		print "<b>PID:</b> ".$PIDInfo['PID']."<br>";
		print "<b>EXE:</b> ".$PIDInfo['EXE']."<br>";
		print "<b>USER:</b> ".$PIDInfo['USER']."<br>";
		
		print "<b>CPU:</b> ".$PIDInfo['PCPU']."<br>";
		print "<b>MEM:</b> ".$PIDInfo['PMEM']."<br>";
		
		print "<b>Parent PID:</b> ".$PIDInfo['PPID']."<br>";
	}
	if($Recursive){
		$Parent=dse_pid_get_info_str($PIDInfo['PPID'],$Recursive);
		if($Reverse){
			$tbr=$tbr."<br>".$Parent;
		}else{
			$tbr=$Parent."<br>".$tbr;
		}
	}
	return $tbr;
}

function dse_pid_get_ps_columns($PID,$o){
	global $vars;
	$PID=intval($PID);
	if(!$PID){
		return -1;
	}
	$Command="ps -p $PID -o $o=";
	$Value=trim(`$Command`);
	$Message="dse_pid_get_ps_columns($PID,$o) [$Command] returning $Value";
	//print "$Message\n";
	dpv(5,$Message);
	return $Value;		
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
	if($vars['ScriptHeaderShow'] || $vars['Verbosity']>1 || $vars['DSE']['SCRIPT_SETTINGS']['Verbosity']>1){
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
		$A=dse_ask_yn(" sudo -u root for this script?");
		if($A=='Y'){
			$Command="";
			foreach($argv as $i=>$t){
				if($Command) $Command.=" ";
				if($i>0) $t="\"$t\"";
				$Command.=$t;
			}
			$Command=str_replace("\"","\\\"",$Command);
			$Command="sudo -u root -H -s \"$Command\"";
			passthru($Command,$v);
			exit($v);
		}
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
	$r=`ls -la $DestinationFile 2>&1`;
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
function dse_file_append_contents($filename,$Str){
	global $vars;
	return dse_file_put_contents($filename,dse_file_get_contents($filename).$Str);
}
function dse_file_put_contents($filename,$Str){
	global $vars;
	return file_put_contents($filename,$Str);
}

// returns array of Names=>Values
function dse_read_config_file($filename,$tbra=array(),$OverwriteExisting=FALSE){
	global $vars;
	$CfgData=@dse_file_get_contents($filename);
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

function str_remove($String,$toRemove){
	global $vars;// print "\n str_remove(tr=$toRemove\n";
	return str_replace($toRemove,"",$String);
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

function pad($String,$Length,$PadChar=" ",$Justification="left"){
	global $vars;
	$CurrentLength=strlen($String);
	//print "pad($String,$Length,$PadChar) CurrentLength=$CurrentLength\n";
	if($CurrentLength>=$Length) return substr($String,0,$Length);
	for($i=$CurrentLength;$i<$Length;$i++){
		switch($Justification){
			case 'right':
				$String=$PadChar.$String;
				break;
			case 'left':
				$String=$String.$String.$String;
				break;
			case 'center':
				if($i<$Length-1){
					$String=$String.$String.$String;
					$i++;
				}else{
					$String.=$PadChar;
				}
				break;
		}
	}
	return $String;
}
	
function unk_time($TimeAndDateString){
	global $vars;
	$TimeAndDateString=trim($TimeAndDateString);
	$TimeAndDateString=str_replace("  "," ",$TimeAndDateString);
	dpv(5,substr("unk_time($TimeAndDateString)",0,cbp_get_screen_width()-2)."\n");
	
	$format=""; $prefix=""; $vars['unk_time__CutTimeAndDateString']="";
	$months=array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
	//foreach($months as $n=>$month) str_replace($month,$n+1,$TimeAndDateString);
	if( preg_match ("/1[0-9]{9} /" , $TimeAndDateString, $matches) >0 ){
		dpv(5,"preg_match 1\n");
		$vars['unk_time__CutTimeAndDateString']=substr($TimeAndDateString,0,10);
		return intval($vars['unk_time__CutTimeAndDateString']);
	}elseif( preg_match ("/^[a-zA-Z]{3} [0-9]{1} [0-9]{2}:[0-9]{2}:[0-9]{2}/" , $TimeAndDateString, $matches) >0 ){
		dpv(5,"preg_match 2\n");
		$len=14; $format = '%b %d %H:%M:%S';}	// Jun  4 08:16:02
	elseif( preg_match ("/^[a-zA-Z]{3} [0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}/" , $TimeAndDateString, $matches) >0 ){
		dpv(5,"preg_match 3\n");
		$len=15; $format = '%b %d %H:%M:%S';}	// Jun  14 08:16:02
	elseif( preg_match ("/^[0-9]{2}\/[0-9]{2}\/[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}/" , $TimeAndDateString, $matches) >0 ){
		dpv(5,"preg_match 4\n");
		$len=17; $format = '%d/%m/%Y %H:%M:%S';}
	elseif( preg_match ("/^[0-9]{2}\/[0-9]{2}\/[0-9]{4} [0-9]{2}:[0-9]{2}:[0-9]{2}/" , $TimeAndDateString, $matches) >0 ){
		dpv(5,"preg_match 5\n");
		$len=18; $format = '%d/%m/%Y %H:%M:%S';}
	
	elseif( preg_match ("/^[a-zA-Z]{9} [0-9]{1,2}, [0-9]{4} [0-9]{2}:[0-9]{2}/" , $TimeAndDateString, $matches) >0 ){
		dpv(5,"preg_match 6\n");
		$len=18; $format = '%B $d, %Y, %H:%M';} //April 9, 2012, 11:33 
	
	elseif( preg_match ("/^[a-zA-Z]{3} [a-zA-Z]{0,9} [0-9]{1,2} [0-9]{2}:[0-9]{2}:[0-9]{2} [0-9]{4}/" , $TimeAndDateString, $matches) >0 ){
		dpv(5,"preg_match 7\n");
		$len=25; $format = '%a %B %d %H:%M:%S %Y';}//Fri Jun  8 03:52:48 2012 
	
	elseif( preg_match ("/^[a-zA-Z]{3} [a-zA-Z]{0,9} [0-9]{1,2}[a-z]{0,2}, [0-9]{4}, [0-9]{1,2}:[0-9]{2}.[0-9]{2} [a-zA-Z]{2} [a-zA-Z]{3}/" , $TimeAndDateString, $matches) >0 ){
			dpv(5,"preg_match 8 \n");
			$TimeAndDateString=str_replace("th, ",", ",$TimeAndDateString);
		$TimeAndDateString=str_replace("st, ",", ",$TimeAndDateString);
		$TimeAndDateString=str_replace("rd, ",", ",$TimeAndDateString);
		$TimeAndDateString=strcut($TimeAndDateString,""," EDT");
		 $len=52; $format = '%a %B %d, %Y, %h:%M.%S %P';
	}//	Sun June 24th, 2012, 5:46.48 am EDT
	elseif( preg_match ("/^[a-zA-Z]{3} [a-zA-Z]{0,9} [0-9]{1,2}, [0-9]{4}, [0-9]{1,2}:[0-9]{2}.[0-9]{2} [a-zA-Z]{2}/" , $TimeAndDateString, $matches) >0 ){
		dpv(5,"preg_match 8b \n");
			$TimeAndDateString=str_replace("th, ",", ",$TimeAndDateString);
		$TimeAndDateString=str_replace("st, ",", ",$TimeAndDateString);
		$TimeAndDateString=str_replace("rd, ",", ",$TimeAndDateString);
		$len=52; $format = '%a %B %d, %Y, %H:%M.%S ';
	}//	Sun June 24, 2012, 5:28.48 am
	elseif( preg_match ("/^[a-zA-Z]{3} [a-zA-Z]{0,9} [0-9]{1,2}[a-z]{0,2}, [0-9]{4}, [0-9]{1,2}:[0-9]{2} [a-zA-Z]{2} [a-zA-Z]{3}/" , $TimeAndDateString, $matches) >0 ){
		dpv(5,"preg_match 9 \n");
		$TimeAndDateString=str_replace("th, ",", ",$TimeAndDateString);
		$TimeAndDateString=str_replace("st, ",", ",$TimeAndDateString);
		$TimeAndDateString=str_replace("rd, ",", ",$TimeAndDateString);
		$TimeAndDateString=strcut($TimeAndDateString,""," EDT");
		$len=52; $format = '%a %B %d, %Y, %H:%M %P';
	}// Sat June 23rd, 2012, 12:49 pm EDT 
	elseif( preg_match ("/^[a-zA-Z]{0,9} [0-9]{1,2}[a-z]{0,2}, [0-9]{4}, [0-9]{1,2}:[0-9]{2} [a-zA-Z]{2}: /" , $TimeAndDateString, $matches) >0 ){
			dpv(5,"preg_match 10 \n");
		
		$TimeAndDateString=strcut($TimeAndDateString,"",": ");
		$len=52; $format = '%B %d, %Y, %H:%M';
	}// April 9, 2012, 12:32 pm:

  	elseif( preg_match ("/^[a-zA-Z]{0,3} [0-9]{1,2} [0-9]{1,2}:[0-9]{2}:[0-9]{2} /" , $TimeAndDateString, $matches) >0 ){
		dpv(5,"preg_match 10b \n");
		$TimeAndDateString=strcut($TimeAndDateString,"",": ");
		$len=52; $format = '%b %d %H:%M:%S';
	}//Jun 26 02:08:54
	
	elseif( str_contains ( $TimeAndDateString, " - - [") >0 ){
		$len=0;
		dpv(5,"preg_match 11 - $TimeAndDateString\n");
	//	$TimeAndDateString=strcut($TimeAndDateString,"["," ");
		$TimeAndDateString=substr(strcut($TimeAndDateString,"- - "," "),1);
		dpv(5,"preg_match 11b - $TimeAndDateString\n");
		$format = '%d/%b/%Y:%H:%M:%S';
	}
		
	//dpv(5,colorize(" found format=$format\n","green"));
	
	
	//print_r($matches);
	//print "res=".$matches[0].", $format\n";
	//if($format && $matches[0] ) return strptime($matches[0], $format);
	if(!$TimeAndDateString){
		dpv(0,colorize("strptime($TimeAndDateString, $format) no final TimeAndDateString. empty!\n","red"));
	}
	if($format &&  $TimeAndDateString){
		if($len) $TimeAndDateString=substr($TimeAndDateString,0,$len);
		$vars['unk_time__CutTimeAndDateString']=$TimeAndDateString;
		$dateTime=strptime($TimeAndDateString, $format);
		if($dateTime){
			dpv(5,colorize("strptime($TimeAndDateString, $format) dateTime=$dateTime\n","cyan"));
		}else{
			dpv(0,colorize("strptime($TimeAndDateString, $format) dateTime=$dateTime\n","red"));
		}
	
		if(!$dateTime['tm_year']) $dateTime['tm_year']=112;
		$t=@mktime($dateTime['tm_hour'], $dateTime['tm_min'], $dateTime['tm_sec'], $dateTime['tm_mon']+1, $dateTime['tm_mday'], $dateTime['tm_year']+1900);
	//	print "\nunk_time=$TimeAndDateString  fmt=$format   t=$t\n";
		return $t;
	}
	
	//$format = '%a %B $d, %Y, %H:%M %P';
	//$dateTime=strptime($TimeAndDateString, $format);
	//print "******* $dateTime **********\n";
	
	return -1;
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


function dse_is_redhat(){
	global $vars;
	if(isset($vars['DSE']['IS_REDHAT'])) return $vars['DSE']['IS_REDHAT'];
	if(!file_exists("/etc/issue")){
		$vars['DSE']['IS_REDHAT']=FALSE;
	}else{
		$EtcIssue=dse_file_get_contents("/etc/issue");
		if(str_contains($EtcIssue,"Red Hat")){
			$vars['DSE']['IS_REDHAT']=TRUE;
		}else{
			$vars['DSE']['IS_REDHAT']=FALSE;
		}
	}
	return $vars['DSE']['IS_REDHAT'];
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

function dse_get_key($Timeout="",$Default=""){
	global $vars;
	$keys=$vars['dse_get_key__keys'];
	$Start=time();
	while($keys==""){
		$keys=readline_timeout(1, '');
		$Left=($Start+$Timeout)-time()	;
		if($Timeout && $Left<0){
			$Msg="( Using Default '$Default' )";
			$MsgColor=getColoredString($Msg,"blink_green");
			$Len=strlen($Msg);
			print $MsgColor;
			cbp_cursor_left($Len);
			cbp_characters_clear($Len+1);
			return $Default;
		}elseif($Timeout){
			$Msg="( Default '$Default' in $Left )";
			$MsgColor=getColoredString($Msg,"blink_green");
			$Len=strlen($Msg);
			print $MsgColor;
			cbp_cursor_left($Len);
		}
	}
	if($Timeout){
		cbp_characters_clear($Len+1);
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
	print "\n\nAnsi Color Codes: ";
	//$background_color="black";
	/*foreach($vars[shell_foreground_colors] as $ColorName=>$foreground_color){
		print "(ForgroundNames[".getColoredString($ColorName, $foreground_color, $background_color)."])";
	}
	foreach($vars[shell_background_colors] as $ColorName=>$background_color){
		print "(BackgroundNames[".getColoredString($ColorName, $foreground_color, $background_color)."])";
	}*/
	getColoredString($string, $forground_color = null, $background_color = null, $type=null, $ResetColorsAfter=TRUE);
	
	
	shell_colors_print_keys();
	for($type=0;$type<=6;$type++){
		if(!($type>2 && $type<4)){
			for($forground_color=30;$forground_color<=37;$forground_color++){
				for($background_color=40;$background_color<=47;$background_color++){
					$ColorName="$type;$forground_color;$background_color";
					print "([".getColoredString($ColorName, $forground_color,$background_color,FALSE,$type)."])";
					//print "$ColorName  ";	
				}
			}
		}
	//print "\n--------------";
	}
	print "\n\n";
	/*
	 * 
	 * 
	 * shell_colors_print_keys();
	for($p1=0;$p1<=8;$p1++){
		if(!($p1>2 && $p1<4))
		for($p2=30;$p2<57;$p2++){
		for($p3=30;$p3<57;$p3++){
			$forground_color="$p1;$p2;$p3";
			print "  ". getColoredString($forground_color, $forground_color);
			foreach($vars[shell_forground_colors] as $ColorName=>$ColorCode){
				if($ColorCode==$forground_color){
					print "(fg[".getColoredString($ColorName, $forground_color)."])";
				}
			}
		}}
		//print "\n--------------";
	}
	 * for($p1=0;$p1<=11;$p1++){
		for($p2=0;$p2<100;$p2++){
			$forground_color="$p1;$p2";
			print "  ". getColoredString($foreground_color, $forground_color);
			foreach($vars[shell_forground_colors] as $ColorName=>$ColorCode){
				if($ColorCode==$forground_color){
					print "(fg[".getColoredString($ColorName, $forground_color)."])";
				}
			}
		}
		//print "\n--------------";
	}
	 * Set Attribute Mode	<ESC>[{attr1};...;{attrn}m
Sets multiple display attribute settings. The following lists standard attributes:
0	Reset all attributes
1	Bright
2	Dim
4	Underscore	
5	Blink
7	Reverse
8	Hidden

	Foreground Colours
30	Black
31	Red
32	Green
33	Yellow
34	Blue
35	Magenta
36	Cyan
37	White

	Background Colours
40	Black
41	Red
42	Green
43	Yellow
44	Blue
45	Magenta
46	Cyan
47	White
	print "\n\nBackground Codes: ";
	$forground_color="white";
	foreach($vars[shell_background_colors] as $ColorName=>$background_color){
		print "(bg[".getColoredString($ColorName, $forground_color, $background_color)."])";
	}
	for($p1=0;$p1<=510;$p1++){
		$background_color="$p1";
		print "  ". getColoredString($background_color, $forground_color, $background_color);
		foreach($vars[shell_background_colors] as $ColorName=>$ColorCode){
			if($ColorCode==$background_color){
				print "(bg[".getColoredString($ColorName, $forground_color, $background_color)."])";
			}
		}
	}
	print "\n\n";*/
}

function shell_colors_print_keys(){
	global $vars;

	print "\n\nForground Names: ";
	$background_color="40";//"black";
	foreach($vars[shell_foreground_colors] as $ColorName=>$forground_color){
		print " ".getColoredString($ColorName, $forground_color, $background_color)." ";
	}
	
	print "\n\nBackground Names: ";
	$forground_color="37";//""white";
	foreach($vars[shell_background_colors] as $ColorName=>$background_color){
		print " ".getColoredString($ColorName, $forground_color, $background_color)." ";
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


$vars[shell_foreground_colors]['black'] = '30';
$vars[shell_foreground_colors]['red'] = '31';
$vars[shell_foreground_colors]['green'] = '32';
$vars[shell_foreground_colors]['yellow'] = '33';
$vars[shell_foreground_colors]['blue'] = '34';
$vars[shell_foreground_colors]['magenta'] = '35';
$vars[shell_foreground_colors]['cyan'] = '36';
$vars[shell_foreground_colors]['white'] = '37';
		

	$vars[shell_foreground_colors]['grey'] = $vars[shell_foreground_colors]['white'];
	$vars[shell_foreground_colors]['lightest_grey'] = $vars[shell_foreground_colors]['white'];
	$vars[shell_foreground_colors]['light_grey'] = $vars[shell_foreground_colors]['white'];
	$vars[shell_foreground_colors]['dark_grey'] = $vars[shell_foreground_colors]['white'];
	$vars[shell_foreground_colors]['blink_red'] = $vars[shell_foreground_colors]['red'];
	$vars[shell_foreground_colors]['dark_red'] = $vars[shell_foreground_colors]['red'];
	$vars[shell_foreground_colors]['blink_green'] = $vars[shell_foreground_colors]['green'];
	$vars[shell_foreground_colors]['bold_green'] = $vars[shell_foreground_colors]['green'];
	$vars[shell_foreground_colors]['dark_green'] = $vars[shell_foreground_colors]['green'];
	$vars[shell_foreground_colors]['blink_yellow'] = $vars[shell_foreground_colors]['yellow'];
	$vars[shell_foreground_colors]['brown'] = $vars[shell_foreground_colors]['yellow'];
	$vars[shell_foreground_colors]['orange'] = $vars[shell_foreground_colors]['yellow'];
	$vars[shell_foreground_colors]['bold_yellow'] = $vars[shell_foreground_colors]['yellow'];
	$vars[shell_foreground_colors]['dark_cyan'] = $vars[shell_foreground_colors]['cyan'];
	$vars[shell_foreground_colors]['blink_cyan'] = $vars[shell_foreground_colors]['cyan'];
	$vars[shell_foreground_colors]['dark_blue'] = $vars[shell_foreground_colors]['cyan'];
	$vars[shell_foreground_colors]['blink_blue'] = $vars[shell_foreground_colors]['blue'];
	$vars[shell_foreground_colors]['light_blue'] = $vars[shell_foreground_colors]['blue'];
	$vars[shell_foreground_colors]['purple'] = $vars[shell_foreground_colors]['magenta'];
	$vars[shell_foreground_colors]['blink_purple'] = $vars[shell_foreground_colors]['magenta'];
	$vars[shell_foreground_colors]['light_purple'] = $vars[shell_foreground_colors]['magenta'];
	$vars[shell_foreground_colors]['dark_purple'] = $vars[shell_foreground_colors]['magenta'];
	/*
	$vars[shell_foreground_colors]['blink'] = '0;5';
	$vars[shell_foreground_colors]['white'] = '1;37';
	$vars[shell_foreground_colors]['grey'] = '0;2';
	$vars[shell_foreground_colors]['lightest_grey'] = '1;37';
	$vars[shell_foreground_colors]['light_grey'] = '6;37';
	$vars[shell_foreground_colors]['dark_grey'] = '2;40';
	$vars[shell_foreground_colors]['black'] = '0;30';
	$vars[shell_foreground_colors]['blink_red'] = '5;91';
	$vars[shell_foreground_colors]['dark_red'] = '0;31';
	$vars[shell_foreground_colors]['red'] = '0;91';
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
	$vars[shell_foreground_colors]['blink_blue'] = '5;94';
	$vars[shell_foreground_colors]['light_blue'] = '1;94';
	$vars[shell_foreground_colors]['purple'] = '0;35';
	$vars[shell_foreground_colors]['blink_purple'] = '5;95';
	$vars[shell_foreground_colors]['light_purple'] = '1;35';
	$vars[shell_foreground_colors]['dark_purple'] = '2;35';
	$vars[shell_foreground_colors]['dark_cyan'] = '0;36';
	$vars[shell_foreground_colors]['blink_cyan'] = '5;96';
	$vars[shell_foreground_colors]['cyan'] = '1;36';
	if(dse_is_osx()||dse_is_redhat()){

		$vars[shell_background_colors]['black'] = '1;1';
		$vars[shell_background_colors]['white'] = '7;40';
		$vars[shell_background_colors]['grey'] = '7;90';
		$vars[shell_background_colors]['bold_white'] = '1;7';
		$vars[shell_background_colors]['dark_red'] = '7;31'; //'11;41';
		$vars[shell_background_colors]['red2'] = '7;91';
		$vars[shell_background_colors]['red'] = '3;41';
		$vars[shell_background_colors]['magenta'] = '3;45';
		$vars[shell_background_colors]['dark_magenta'] = '11;45';
		$vars[shell_background_colors]['orange'] = '2;41';
		$vars[shell_background_colors]['dark_yellow'] = '11;43';
		$vars[shell_background_colors]['yellow'] = '7;93';
		$vars[shell_background_colors]['green'] = '3;42';
		$vars[shell_background_colors]['dark_green'] = '11;42';
		$vars[shell_background_colors]['dark_teal'] = '11;46';
		$vars[shell_background_colors]['cyan'] = '3;46';
		$vars[shell_background_colors]['blue'] = '3;44';
	}else{
		*/
		$vars[shell_background_colors]['black'] = '40';
		$vars[shell_background_colors]['red'] = '41';
		$vars[shell_background_colors]['green'] = '42';
		$vars[shell_background_colors]['yellow'] = '43';
		$vars[shell_background_colors]['blue'] = '44';
		$vars[shell_background_colors]['magenta'] = '45';
		$vars[shell_background_colors]['cyan'] = '46';
		$vars[shell_background_colors]['white'] = '47';
		
		$vars[shell_background_colors]['grey'] = $vars[shell_background_colors]['white'];
		$vars[shell_background_colors]['bold_white'] = $vars[shell_background_colors]['white'];
		$vars[shell_background_colors]['dark_red'] = $vars[shell_background_colors]['red']; //'11;41';
		$vars[shell_background_colors]['dark_magenta'] = $vars[shell_background_colors]['magenta'];
		$vars[shell_background_colors]['orange'] = $vars[shell_background_colors]['yellow'];
		$vars[shell_background_colors]['dark_yellow'] = $vars[shell_background_colors]['yellow'];
		$vars[shell_background_colors]['darkcyan'] = $vars[shell_background_colors]['cyan'];
		$vars[shell_background_colors]['dark_green'] = $vars[shell_background_colors]['green'];
		$vars[shell_background_colors]['dark_blue'] = $vars[shell_background_colors]['blue'];
		/*
		$vars[shell_background_colors]['grey'] = '47';
		
		$vars[shell_background_colors]['red'] = '101';
		$vars[shell_background_colors]['green'] = '102';
		$vars[shell_background_colors]['yellow'] = '103';
		$vars[shell_background_colors]['orange'] = '43';
		$vars[shell_background_colors]['blue'] = '104';
		$vars[shell_background_colors]['magenta'] = '105';
		$vars[shell_background_colors]['cyan'] = '106';*/
	//}
	
	
function colorize_words($L) {
	global $vars;
	foreach($vars['DSE']['RedWords'] as $RedWord) $L=str_ireplace($RedWord,colorize($RedWord,"red"),$L);
	foreach($vars['DSE']['GreenWords'] as $GreenWord) $L=str_ireplace($GreenWord,colorize($GreenWord,"green"),$L);
	foreach($vars['DSE']['BlueWords'] as $BlueWord) $L=str_ireplace($BlueWord,colorize($BlueWord,"blue"),$L);
	foreach($vars['DSE']['MagentaWords'] as $PurpleWord) $L=str_ireplace($PurpleWord,colorize($PurpleWord,"purple"),$L);
	foreach($vars['DSE']['YellowWords'] as $YellowWord) $L=str_ireplace($YellowWord,colorize($YellowWord,"yellow"),$L);
	foreach($vars['DSE']['CyanWords'] as $CyanWord) $L=str_ireplace($CyanWord,colorize($CyanWord,"cyan"),$L);
	return $L;			
}
function colorize($string, $forground_color = null, $background_color = null, $ResetColorsAfter=TRUE, $type=null) {
	global $vars;
	return getColoredString($string, $forground_color, $background_color, $ResetColorsAfter, $type);
}

function getColoredString($string, $forground_color = null, $background_color = null, $ResetColorsAfter=TRUE, $type=null) {
	global $vars;
	
	//print "\n\ngetColoredString($string, $forground_color = null, $background_color = null, $ResetColorsAfter=TRUE, $type=null) \n\n";
	if($forground_color!=null && $forground_color!=""){
		$vars[dst_lst__foreground_color]=$forground_color;
	}else{
		$forground_color=$vars[dst_lst__foreground_color];
	}
	if($background_color!=null && $background_color!=""){
		$vars[dst_lst__background_color]=$background_color;
	}else{
		$background_color=$vars[dst_lst__background_color];
	}
	if($type!=null && $type!=""){
		$vars[dst_lst__type]=$type;
	}else{
		$type=$vars[dst_lst__type];
	}
	//print "intval($background_color)==$background_color ";
	if(intval($background_color)>0){
		//print "===are===";
		$background_color_code=$background_color;
	}else{
		if(isset($vars[shell_background_colors][$background_color])){
			$background_color_code=$vars[shell_background_colors][$background_color];
		}else {
			$background_color_code=$vars[shell_background_colors][red];
		}
	}
	if(intval($forground_color)>0){
		$forground_color_code=$forground_color;
	}else{
		if(isset($vars[shell_foreground_colors][$forground_color])){
			$forground_color_code=$vars[shell_foreground_colors][$forground_color];
		}else {
			$forground_color_code=$vars[shell_foreground_colors][red];
		}
	}

	if($type==null || $type==""){
		$type="0";
	}
	if($forground_color_code==null || $forground_color_code==""){
		$forground_color_code="37";
	}
	if($background_color_code==null || $background_color_code==""){
		$background_color_code="40";
	}
//	$colored_string  = "\033[0m";
	$colored_string="";
	$colored_string .= "\033[$type;$forground_color_code;$background_color_code"."m";
	$colored_string .=  $string;//"      ".$string."==".$type.";".$forground_color_code.";".$background_color_code."m";
	
	
//	print "=+== $type;$forground_color_code;$background_color_code   ++++++\n\n\n";

	if($ResetColorsAfter){
		if(!$vars['DSE']['SHELL_FORGROUND']) $vars['DSE']['SHELL_FORGROUND']="white";
		if(!$vars['DSE']['SHELL_BACKGROUND']) $vars['DSE']['SHELL_BACKGROUND']="black";
		$colored_string .= "\033[0";
		if(FALSE && intval($vars['DSE']['SHELL_FORGROUND'])==$vars['DSE']['SHELL_FORGROUND']){
			$colored_string .= ";".$vars['DSE']['SHELL_FORGROUND'];
		}else{
			if(isset($vars[shell_foreground_colors][$vars['DSE']['SHELL_FORGROUND']])){
				$colored_string .= ";".$vars[shell_foreground_colors][$vars['DSE']['SHELL_FORGROUND']];
			}else {
				$colored_string .= ";".$vars[shell_foreground_colors][white];
			}
		}
		
		if(FALSE && intval($vars['DSE']['SHELL_BACKGROUND'])==$vars['DSE']['SHELL_BACKGROUND']){
			$colored_string .= ";".$vars['DSE']['SHELL_BACKGROUND'];
		}else{
			if(isset($vars[shell_background_colors][$vars['DSE']['SHELL_BACKGROUND']])){
				$colored_string .= ";".$vars[shell_background_colors][$vars['DSE']['SHELL_BACKGROUND']];
			}else {
				$colored_string .= ";".$vars[shell_background_colors][red];
			}
		}
		$colored_string .= "m";
	}
 
	return $colored_string;
}

function setBackgroundColor($background_color) {
	global $vars;
	return getColoredString("", null, $background_color, FALSE);
}
function setForgroundColor($forground_color) {
	global $vars;
	return getColoredString("", $forground_color, null, FALSE);
}
	
	
	
function getForegroundColors() {
	global $vars;
	return array_keys($vars[shell_foreground_colors]);
}
 
function getBackgroundColors() {
	global $vars;
	return array_keys($vars[shell_background_colors]);
}
	
 
 
function cbp_get_screen_width(){
    global $vars;
	return trim(`stty size | cut -d" " -f2`);
}
function cbp_get_screen_height(){
    global $vars;
	return trim(`stty size | cut -d" " -f1`);
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

/*
 * Save Cursor & Attrs	<ESC>7
Save current cursor position.
Restore Cursor & Attrs	<ESC>8
 * Scroll Screen		<ESC>[r
Enable scrolling for entire display.
Scroll Screen		<ESC>[{start};{end}r
Enable scrolling from row {start} to row {end}.
Scroll Down		<ESC>D
Scroll display down one line.
Scroll Up		<ESC>M
 * 
 * Erase End of Line	<ESC>[K
Erases from the current cursor position to the end of the current line.
Erase Start of Line	<ESC>[1K
Erases from the current cursor position to the start of the current line.
Erase Line		<ESC>[2K
Erases the entire current line.
Erase Down		<ESC>[J
Erases the screen from the current line down to the bottom of the screen.
Erase Up		<ESC>[1J
Erases the screen from the current line up to the top of the screen.
Erase Screen		<ESC>[2J
 * */
 
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