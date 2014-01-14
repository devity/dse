<?php
ini_set('display_errors','On');
ini_set('display_startup_errors','On');
ini_set('log_errors','On');
error_reporting( (E_ALL & ~E_NOTICE) ^ E_DEPRECATED);
	
if($vars['Verbosity']>5) print "starting dse_cli_functions.php\n";
	
	 
	
function dse_os_summary(){
	global $vars; dse_trace();
	$tbr="";
	if(dse_is_ubuntu()){
		$contents=dse_file_get_contents("/etc/issue");
		$contents=remove_blank_lines($contents)."\n";
		$tbr.= $contents;
	}else{
		$contents=dse_file_get_contents("/etc/release");
		$contents=remove_blank_lines($contents)."\n";
		$tbr.= $contents;
	}
	
	
	if(dse_is_ubuntu()){
		$Release=dse_ubuntu_release();
		$tbr.="Ubuntu Release Name: $Release\n";
	}
	
	$tbr.= dse_exec("uname -a");
	return $tbr;
}

	
function dse_file_shrink($FileName){
	global $vars; dse_trace();
	$Base=basename($FileName);
	$Extension=dse_file_extension($FileName);
	$FileNameWOExtension=str_remove($FileName,".".$Extension);
	$BaseWOExtension=str_remove($Base,".".$Extension);
	$Dir=dirname($FileName);
	$StartSize=dse_file_get_size($FileName);
	switch($Extension){
		case 'gz':
			$r=dse_exec("gunzip $FileName",TRUE);
			$UncompressedSize=dse_file_get_size($FileNameWOExtension);
			$r=dse_exec("gzip -9 $FileNameWOExtension",TRUE);
			$EndSize=dse_file_get_size($FileName);
			break;
	}
	
	$Percent=number_format(100*($EndSize/$StartSize));
	print "$Base:  Size $StartSize => $EndSize  $Percent%\n";
	
	
}

function dse_shutdown(){
	global $vars; dse_trace();
	return dse_cli_script_shutdown();
	
}
function dse_cli_script_shutdown(){
	global $vars; dse_trace();
	//print "dse_shutdown()\n";
	$tbr="";
	if(is_array($vars[dse_Trace_Stack]) && sizeof($vars[dse_Trace_Stack])>0 ){
		print "isarray(vars[dse_Trace_Stack])=TRUE\n";
		
		$tn=0;
		foreach ($vars[dse_Trace_Stack] as $t){
			$tn++;
			
	//print "tn=$tn\n";
			$LevelsDeep=sizeof($t);
			$last=$t[sizeof($t)-1];
			$args="";
			$tt=$last['args'];
			if(is_array($last) && is_array($last['args']) && $last['args']) foreach($last['args'] as $a){
				if($args){
					$args.=", ";
				}
				if(is_object($a)){
					$n=get_class($a);
					$a="($n object)";
				}
				$args.=" [$a]";
			}
			$call=$last['function']."($args)";
			$call=dse_debug_bt2html($t,$tn);
			$tbr.= $call;
		}
		//print " Trace &nbsp; ";
	
	/*
		foreach ($vars[dpd_Trace_phpfiles] as $phpfile=>$a){
			print "File: $phpfile<br>";
			foreach($a as $phpfunction=>$phpfunction_a){
				$callcount=$phpfunction_a[0];
				$firsttime=$phpfunction_a[1];
				$lasttime=$phpfunction_a[2];
				$totaltime=$phpfunction_a[3];
				print " &nbsp; ${phpfunction}() - $callcount calls, $totaltime<br>";
			}
		}
		
	*/
		$tbr.= "\n";
		
		if($vars[dse_enable_debug_code]) {
			//print $tbr;
		}
		
		//global $argv;
		//.".".basename($argv[0])
		$DebugOutputFilename="/tmp/dse_trace__".basename($vars['DSE']['SCRIPT_FILENAME']).".".dse_date_format("NOW","FILE");
		dse_file_put_contents($DebugOutputFilename,$tbr);
		print "Debug info and trace saved in: $DebugOutputFilename\n";
	}
}

function dse_firewall_internet_hide(){
	global $vars; dse_trace();
	
	//save current policy / conf file
	
	//prompt for ips to allow in. offer the ips on ssh now
	
	//update policy and restart
}

$dse_Trace_Stack=Array();
$vars[dse_Trace_Stack]=Array();
if($vars['DSE']['OUTPUT_FORMAT']=="HTML"){
	$vars[dse_Trace_Indent_String]="&nbsp; &nbsp; + ";
}else{
	$vars[dse_Trace_Indent_String]=colorize("   + ","green","black");
}
$vars[dse_Trace_Indent_Current]=0;
$vars[dse_Trace_Count]=0;
$vars[dse_Trace_Count_Max]=100000;
function dse_trace(){
	//$tbr=debug_tostring($bt);
	global $vars,$dseTrace_Stack;
//if($vars['Verbosity']>5) print "dse_cli_functions.php: dse_trace() start\n";
   // if(!$vars[dse_enable_debug_code]) return;
	$vars[dse_Trace_Count]++;
    if( $vars[Verbosity]<3 || $vars[dse_Trace_Count]>$vars[dse_Trace_Count_Max] ) return;
	
//if($vars['Verbosity']>5) print "dse_cli_functions.php: dse_trace() calling  debug_backtrace()\n";
   	$bt=debug_backtrace();
	
//if($vars['Verbosity']>5) print "dse_cli_functions.php: dse_trace() did debug_backtrace()\n";
	if($vars[dse_enable_debug_code_markpoints_in_html]){
		$section=$vars[dse_Trace_Count];
		print "<font class='f7pt'>[<A href=#section$section>t".$vars[dse_Trace_Count]."</a>]</font>";
	}
   	//array_walk( debug_backtrace(), create_function( '$a,$b', 'print "<br /><b>". basename( $a[\'file\'] ). "</b> &nbsp; <font color=\"red\">{$a[\'line\']}</font> &nbsp; <font color=\"green\">{$a[\'function\']} ()</font> &nbsp; -- ". dirname( $a[\'file\'] ). "/";' ) ); 
	 
	 
	 
	$LevelsDeep=sizeof($bt);
	$last=$bt[sizeof($bt)-2];
	$phpfile=$last['file'];
	$phpfunction=$last['function'];
	$vars[dse_Trace_phpfile_list][]=$phpfile;
	if(!$vars[dse_Trace_phpfiles][$phpfile]){
		$vars[dse_Trace_phpfiles][$phpfile]=array();
	}
	if(!$vars[dse_Trace_phpfiles][$phpfile][$phpfunction]){
		$vars[dse_Trace_phpfiles][$phpfile][$phpfunction]=array();
		$LastFunctionCallTime=0;
		$vars[dse_Trace_phpfiles][$phpfile][$phpfunction][0]=0;
		$vars[dse_Trace_phpfiles][$phpfile][$phpfunction][1]=time_float();
	}else{
		$LastFunctionCallTime=$vars[dse_Trace_phpfiles][$phpfile][$phpfunction][1];
	}
	$vars[dse_Trace_phpfiles][$phpfile][$phpfunction][0]++;
	$vars[dse_Trace_phpfiles][$phpfile][$phpfunction][2]=time_float();
	if($LastFunctionCallTime>0){
		$FunctionRunTime=time_float()-$LastFunctionCallTime;
		$vars[dse_Trace_phpfiles][$phpfile][$phpfunction][3]+=$FunctionRunTime;
	}else{
		$vars[dse_Trace_phpfiles][$phpfile][$phpfunction][3]=0;
	}
	$vars[dse_Trace_Stack][]=$bt;
//if($vars['Verbosity']>5) print "dse_cli_functions.php: dse_trace() returning\n";
}



function dse_whereami(){
	global $vars; 
   	$bt=debug_backtrace();
   	
	print "whereami: "; 
	print_r($bt);
	print "<br>";
}

//if($vars['Verbosity']>5) print "dse_cli_functions.php: past trace func's\n";


$dse_debug_bt2html_lla=array();
function dse_debug_bt2html($t,$tn){
	global $vars;	//dse_trace();
	global $dse_debug_bt2html_lla;
	$tbr="";
	/*$LevelsDeep=sizeof($t);
	*/
	$LevelsDeep=0;
	for($i=sizeof($t)-1;$i>=0;$i--){
		$tt=$t[$i];
		
		$LevelsDeep++;
		$IndentThis="";
		for($in=0;$in<$LevelsDeep;$in++){
			$IndentThis.=$vars[dse_Trace_Indent_String];
		}
		if($dse_debug_bt2html_lla[$LevelsDeep]!=$tt){
			$part=dse_debug_bt2html_sub($tt);
			if($part){
				if($vars['DSE']['OUTPUT_FORMAT']=="HTML"){
					$tbr.="<br>";
				}else{
					$tbr.="\n";
				}
				if($i==1){
					$extra="";
					if($vars[dse_enable_debug_code_markpoints_in_html]){
						$section=$tn;
						$extra=" id=section$section ";
					}
					if($vars['DSE']['OUTPUT_FORMAT']=="HTML"){
						$tbr.="<font class='f7pt' $extra>[t$tn]</font>";
					}else{
					//	$tbr.="[t".colorize($tn,"white","red")."]";
					}
				}else{
					if($vars['DSE']['OUTPUT_FORMAT']=="HTML") {
						$tbr.=" &nbsp; &nbsp; ";
					}else {
						$tbr.="     ";
					}
				}
				$tbr.=$IndentThis.$part;
			}
		}
		$dse_debug_bt2html_lla[$LevelsDeep]=$tt;
	}
	return $tbr;
}		
function dse_debug_bt2html_sub($last){
	global $vars;	//dse_trace();
    global $dpd_debug_bt2html_lla;
    if($last['function']=="dpd_trace"){
    	return "";
    }
	$tbr="";
	$args="";
	if(is_array($last) && is_array($last['args']) && $last['args']) foreach($last['args'] as $a){
		/*	$a=str_replace("<font color=green><b>INSERT</b></font> INTO","INSERT INTO",$a);
			$a=str_replace("<font color=green><b>DELETE</b></font> FROM","DELETE FROM",$a);
			$a=str_replace("<font color=green><b>UPDATE</b></font> ","UPDATE ",$a);
		*/
		if($args){
			$args.=", ";
		}
		$size=strlen(serialize($a));
		if($size>1000){
			$a="[TOO LARGE: ${size}B]";
		}else{
			$a=debug_tostring($a);
			$a=str_replace("\n","",$a);
			if($vars['DSE']['OUTPUT_FORMAT']=="HTML") $a=str_ireplace("<br>","",$a);
		}
		if($vars['DSE']['OUTPUT_FORMAT']=="HTML"){
			$args.=" [$a]";
		}else{
			$args.=colorize(" [","yellow","black");
			$args.=colorize($a,"green","black");
			$args.=colorize("]","yellow","black");
		
		}
	}
	$file=$last['file'];
	$line_number=$last['line'];
	$file_str="$file";
	$url=dse_ide_file_open_url($file,$line_number);
	if($vars['DSE']['OUTPUT_FORMAT']=="HTML"){
		$line_number_str="<a href=$url>$line_number</a>";
	}else{
		$line_number_str=colorize($line_number,"blue","yellow");
	}
	$file_str=str_replace("/home/admin/dev-batteriesdirect_com","",$file_str);
	if($vars['DSE']['OUTPUT_FORMAT']=="HTML"){
		$tbr.="<b>".$last['function']."</b>(<font class='f7pt'>$args</font>) &nbsp; &nbsp; &nbsp; -*- <font class='f7pt'>${file_str}:$line_number_str</font>";
	}else{
		//$args=colorize($args,"yellow","blue");
		$line_number_str=colorize($line_number_str,"cyan","black");
		$file_str=colorize($file_str,"cyan","black");
		$tbr.=$last['function'];
		$tbr.="($args) -*- ${file_str}:$line_number_str";
	}
	return $tbr;
}	
			
if($vars['Verbosity']>5) print "dse_cli_functions.php: line 269\n";
function dse_ide_file_open_url($URL,$LineNumber=0){
	global $vars; dse_trace();
	//dpv(5, "dse_ide_file_open_url() unimplimented!");
	return;
}
function dse_launch_url($URL){
	global $vars; dse_trace();
	$Command=$vars['DSE']['URL_LAUNCH_COMMAND'];
	$Command=str_replace("<URL>", "\"$URL\"", $Command);
	return dse_passthru($Command,TRUE);
}
function dse_launch_code_edit($File,$LineNumber=0){
	global $vars; dse_trace();
	$Command=$vars['DSE']['CODE_EDIT_LAUNCH_COMMAND'];
	$Command=str_replace("<FILE>", "\"$File\"", $Command);
	return dse_passthru($Command,TRUE);
}
function dse_launch_vibk_edit($File,$LineNumber=0){
	global $vars; dse_trace();
	if($vars['DSE']['VIBK_EDIT_LAUNCH_COMMAND']){
		$Command=$vars['DSE']['VIBK_EDIT_LAUNCH_COMMAND'];
		$Command=str_replace("<FILE>", "\"$File\"", $Command);
	}else{
		$Command="vi \"$File\" ";
	}
	return dse_passthru($Command,TRUE);
}
		
function dse_get_stdin(){
	global $vars; dse_trace();
	$STDIN_Content="";
	$fd = fopen("php://stdin", "r"); 
	while (!feof($fd)) {
		$STDIN_Content .= fread($fd, 1024);
	}
	return $STDIN_Content;
}
 
function str_remove_blank_lines($Contents){
	global $vars; dse_trace();
	$tbr="";
	foreach(split("\n",$Contents) as $L){
		if(trim($L)!=""){
			if($tbr!="") $tbr.="\n";
			$tbr.=$L;
		}
	}
	return $tbr;
}

if($vars['Verbosity']>5) print "dse_cli_functions.php: line 320\n";
function dse_exec_esc($StringToEscape){
	global $vars; dse_trace();
	$StringToEscape=str_replace(" ", "\\ ", $StringToEscape);
	$StringToEscape=str_replace(";", "\\;", $StringToEscape);
	$StringToEscape=str_replace("&", "\\& ", $StringToEscape);
	return $StringToEscape;
}
	
function dse_exec($Command,$ShowCommand=FALSE,$ShowOutput=FALSE){
	global $vars; dse_trace();
	if($ShowCommand){
		print bar("Command: ".colorize($Command,"white","red"),"v","yellow","black","red","black");
	}
	$r=`$Command`;
	if($ShowOutput) {
		print $r;
	}
	
	if($ShowCommand){
		print bar("END Command: ".colorize($Command,"white","red"),"^","yellow","black","red","black");
	}
	return $r;
}


function dse_exec_bar($Command,$ShowCommand=TRUE,$ShowOutput=FALSE){
	global $vars; dse_trace();
    $pid = dse_exec_bg($Command,$ShowCommand,$ShowOutput);
    while( dse_pid_is_running($pid)){
    	progress_bar();
		sleep(1);
    }
	return dse_exec_bg_results($pid);
}
   	   	
function dse_exec_bg($Command,$ShowCommand=FALSE,$ShowOutput=FALSE){
	global $vars; dse_trace();
	if($ShowCommand){
		print bar("Command: ".colorize($Command,"white","red"),"=","yellow","black","red","black");
	}
	$TmpFile=dse_tmp_file();
	exec("$Command  > $TmpFile 2>&1 & echo $!" ,$op); 
	//print_r($op);
    $pid = (int)$op[0]; 
    $vars[dse_exec_bg_pid2tmp][$pid]=$TmpFile;
	return $pid;
}
function dse_exec_bg_results($pid){
	global $vars; dse_trace();
	if(dse_pid_is_running($pid)) return FALSE;
	$TmpFile=$vars[dse_exec_bg_pid2tmp][$pid];
	$TmpFileContents=dse_file_get_contents($TmpFile);
	return $TmpFileContents;
}
   
	
function dse_passthru($Command,$ShowCommand=FALSE){
	global $vars; dse_trace();
	if($ShowCommand){
		print colorize("Command: ","yellow","black");
		print colorize($Command,"blue","white");
		print "\n";	
	}
	$r=passthru($Command);
	//if($ShowOutput) print $r;
	return $r;
}

if($vars['Verbosity']>5) print "dse_cli_functions.php: line 356\n";
   
function dse_detect_os_info(){
	global $vars; dse_trace();
	
	$vars[dse_osinfo_release]=trim(dse_exec("cat /etc/*-release"));
	$vars[dse_osinfo_uname]=trim(dse_exec("uname -a"));
	if( !(strstr($vars[dse_osinfo_release],"CentOS")===FALSE) ){
		$vars[IsCentOS]=TRUE;
	}elseif( !(strstr($vars[dse_osinfo_release],"Ubuntu")===FALSE) ){
		$vars[IsUbuntu]=TRUE;
	}

}


function dse_fss($FileNameOrPartialString, $Dir=""){
	global $vars; dse_trace();
	$FileNameOrPartialString=trim($FileNameOrPartialString);
	$FileNameOrPartialString=dse_exec_esc($FileNameOrPartialString);
	$Dir=dse_exec_esc(trim($Dir));
	$Command="/dse/bin/fss -q -f $FileNameOrPartialString $Dir";
	$r=dse_exec($Command);
	return $r;
}

if($vars['Verbosity']>5) print "dse_cli_functions.php: line 383\n";
$OK=getColoredString("OK","green","black");
$Fixed=getColoredString("Fixed","green","black");
$Added=getColoredString("Added","green","black");
$Failed=getColoredString("Failed","red","black");
$NotOK=getColoredString("Not OK","red","black");
$Missing=getColoredString("Missing","red","black");
$NotChanged=getColoredString("Not Changed","orange","black");
$NotFixed=getColoredString("Not Changed","orange","black");

if($vars['Verbosity']>5) print "dse_cli_functions.php: line 392\n";
if (!function_exists("readline")) { function readline( $prompt = '' ){
	global $vars; dse_trace();
    echo $prompt;
    return rtrim( fgets( STDIN ), "\n" );
}}

	
if($vars['Verbosity']>5) print "dse_cli_functions.php: line 395\n";

function dse_replace_in_file($File,$Needle,$Replacement){
	global $vars; dse_trace();
	if(!dse_file_exists($File)) return FALSE;
	$tmp=dse_exec("/dse/bin/dtmp");
	$MD5=md5_of_file($File);
	$Command="/dse/bin/dreplace $File \"$Needle\" \"$Replacement\" > $tmp";
	dse_exec($Command);	
	if(!dse_file_exists($tmp)) return FALSE;
	$MD52=md5_of_file($tmp);
	if($MD5!=$MD52){
		dse_exec("mv -f $tmp $File 2>&1");
		return TRUE;
	}
	return FALSE;
}

			
function progress_bar($Percent="time",$Width=80,$Note=""){
	global $vars; dse_trace();
	global $Rainbow,$RainbowSize;
	//print "progress_bar()\n";
	if($RainbowSize<1){
		$Rainbow[]=colorize(" ","red","red");
		$Rainbow[]=colorize(" ","red","red");
		$Rainbow[]=colorize("-","black","red");
		$Rainbow[]=colorize("%","black","red");
		$Rainbow[]=colorize("%","red","black");
		$Rainbow[]=colorize("&","red","black");
		$Rainbow[]=colorize("M","red","black");
		$Rainbow[]=colorize(" ","black","black");
		$Rainbow[]=colorize("-","blue","black");
		$Rainbow[]=colorize("+","green","black");
		$Rainbow[]=colorize("#","cyan","black");
		$Rainbow[]=colorize("+","green","black");
		$Rainbow[]=colorize("-","white","black");
		$Rainbow[]=colorize(" ","black","black");
		$Rainbow[]=colorize("M","red","black");
		$Rainbow[]=colorize("&","red","black");
		$Rainbow[]=colorize("%","black","red");
		$Rainbow[]=colorize("-","black","red");
		$Rainbow[]=colorize(" ","red","red");
		$Rainbow[]=colorize(" ","red","red");
		$Rainbow[]=colorize(" ","red","red");
		$Rainbow[]=colorize(".","yellow","red");
		$Rainbow[]=colorize(".","yellow","red");
		$Rainbow[]=colorize("-","yellow","red");
		$Rainbow[]=colorize("-","yellow","red");
		$Rainbow[]=colorize("=","yellow","red");
		$Rainbow[]=colorize("%","yellow","red");
		$Rainbow[]=colorize("M","yellow","red");
		$Rainbow[]=colorize("M","red","yellow");
		$Rainbow[]=colorize("%","red","yellow");	
		$Rainbow[]=colorize("%","white","yellow");
		$Rainbow[]=colorize("M","yellow","yellow");
		$Rainbow[]=colorize("M","white","yellow");
		$Rainbow[]=colorize("M","yellow","yellow");
		$Rainbow[]=colorize("%","white","yellow");
		
		$Rainbow[]=colorize("M","yellow","yellow");
		$Rainbow[]=colorize("N","green","yellow");
		$Rainbow[]=colorize("=","green","yellow");
		$Rainbow[]=colorize("*","green","green");
		$Rainbow[]=colorize("[","cyan","green");
		$Rainbow[]=colorize("+","cyan","cyan");
		$Rainbow[]=colorize("]","blue","cyan");
		$Rainbow[]=colorize("%","cyan","blue");
		$Rainbow[]=colorize("M","white","blue");
		$Rainbow[]=colorize("M","blue","white");
		$Rainbow[]=colorize("+","white","white");
		$Rainbow[]=colorize("+","magenta","magenta");
		
		$Rainbow[]=colorize("&","white","red");
		$Rainbow[]=colorize("%","white","red");
		$Rainbow[]=colorize("=","white","red");
		$Rainbow[]=colorize("-","white","red");
		$Rainbow[]=colorize(".","white","red");
		$Rainbow[]=colorize(" ","red","red");
		$RainbowSize=sizeof($Rainbow);
	}
	$vars[pr_bar__rainbow]+=3;
	$ri=$vars[pr_bar__rainbow]%$RainbowSize;
	$Needed=$Width;
	$RainbowBar="";
	for($i=0;$i<$Needed;$i++){
		$RainbowBar.=$Rainbow[($i+$ri)%$RainbowSize];
	}
	
	if($Percent=="reset"){
		$vars[pr_bar__start_time]=time();
	}elseif($Percent=="time"){
		if($vars[pr_bar__start_time]){
			$tt=time()-$vars[pr_bar__start_time];
		}else{
			$tt=0;
			$vars[pr_bar__start_time]=time();
		}
		$RunTimeStr=" ".seconds_to_text($tt)."  ";
		$Percent=50;
	}
	if($Note) $RunTimeStr.=" | ".$Note;
	$GreenPortion=pad($RunTimeStr,intval($Width*($Percent/100))," ");
	$GreenPortion2=$RainbowBar;//pad("",intval($Width*($Percent/100)),"*");
	$RedPortion=pad("",intval($Width*((100-$Percent)/100)),"*");
	cbp_cursor_save();
	sbp_cursor_postion(0,cbp_get_screen_width()-$Width);
	print colorize($GreenPortion,"white","black",TRUE,1);
	//if($vars[pr_bar__last]==TRUE){
		//print colorize($RedPortion,"red","black");
	//}else{
		
		$Needed=intval($Width*((100-$Percent)/100));
		$RainbowBar="";
		for($i=0;$i<$Needed;$i++){
			$RainbowBar.=$Rainbow[($i+$ri)%$RainbowSize];
		}
		print $RainbowBar;
		//print colorize($RedPortion,"blue","black");
	//}
	sbp_cursor_postion(2,cbp_get_screen_width()-$Width);
	print colorize($GreenPortion2,"green");
	if($vars[pr_bar__last]==TRUE){
	//	print colorize($RedPortion,"red");
	}else{
		//print colorize($RedPortion,"magenta");
	}
	$vars[pr_bar__last]=!$vars[pr_bar__last];
	cbp_cursor_restore();
}
function dse_hostname(){
	global $vars; dse_trace();
	if($vars['DSE']['HOSTNAME']) return $vars['DSE']['HOSTNAME'];
	$tbr=trim(`hostname`);
	if(dse_is_osx()) $tbr=str_remove($tbr,".local");
	return $tbr;
}

function dse_pid_get_exe_tree($PID,$Reverse=FALSE){
	global $vars; dse_trace();
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
	global $vars; dse_trace();
	//if(str_icontains($Message,"error ") || str_icontains($Message,"error: ")){
	//	dep($Message);
	//}else{
		if($vars['Verbosity']>=$MinVerbosity){
			print colorize("Dbg$MinVerbosity: ","black","white");
			print colorize($Message,"yellow","black")."\n";
		}
	//}
}
function dep($ErrorMessage,$Log=TRUE){
	global $vars; dse_trace();
	if($vars['Verbosity']>=0){
		print colorize("depERROR:","white","red")
			.colorize(" ".$ErrorMessage,"magenta","black")."\n";
	}
	$PWD=getcwd();
	if($Log){
		dse_log("ERROR ".$vars['DSE']['SCRIPT_FILENAME']."-".$vars['DSE']['SCRIPT_VERSION']." PWD=$PWD ".$ErrorMessage);	
	}
}

function dse_log($Message,$File=""){
	global $vars; dse_trace();
	$Command="";
	$Message=dse_date_format("NOW","SYSLOG")."  ".str_replace("\"","\\\"",$Message);
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

if($vars['Verbosity']>5) print "dse_cli_functions.php: line 584
\n";
function dse_ip_port_is_open($Port){
	global $vars; dse_trace();
	$Command="/dse/bin/dnetstat -o -d\"\n\" | grep \":$Port \" ";
	$r=trim(`$Command`);
	//dse_log("c=$Command r=$r");
	return($r!="");
}
function dse_ip_port_is_listening($IP,$Port){
	global $vars; dse_trace();
	$Command="nc -vz $IP $Port 2>&1";
	$r=`$Command`;
	//dse_log("c=$Command r=$r");
	return(str_contains($r,"succeeded"));
}

function dse_date_format($Time="NOW",$FormatName="FULLREADABLE"){
	global $vars; dse_trace();
	if($Time=="NOW") $Time=time();
	if(str_contains($FormatName," ")){
		return @date($FormatName,$Time);
	}
	switch($FormatName){
		case 'FILE':
			$FormatString="YmdGis";
			break;
		case 'SYSLOG':
			$FormatString="D M j G:i:s T Y";
			$FormatString="M j G:i:s";
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
	global $vars; dse_trace();
	if($Seconds<60*3){
		if($vars['s2t_abvr'])return "$Seconds sec";
		return "$Seconds seconds";
	}elseif($Seconds<60*60*2){
		$Minutes=intval($Seconds/60);
		if($vars['s2t_abvr'])return "$Minutes min";
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
		if($vars['s2t_abvr'])return "$Years yrs";
		return "$Years years";
	}			
}
	
function dse_time_span_sting_to_seconds($Str){
	global $vars; dse_trace();
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
	global $vars; dse_trace();
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
		
		
function dse_ask_yn($Question=" Press Y or N ",$Default="",$Timeout=""){
	global $vars; dse_trace();
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
	global $vars; dse_trace();
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
	global $vars; dse_trace();
	if(!is_array($Options)){
		$TreatOptionsAsString=TRUE;
		$Options=str_split($Options);
	}
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
		if($key===$K){
			print "\n";
			if($TreatOptionsAsString){
				return $Options[intval($K)];
			}else{
				return $K;
			}
		}
	}
	print getColoredString(" Invalid Option. You pressed '$key'. \n","red","black");
	if($TreatOptionsAsString){
		return $Options[intval(dse_ask_choice($Options,$Question,$Default,$Timeout))];
	}else{
		return dse_ask_choice($Options,$Question,$Default,$Timeout);
	}
	
}
function dse_ask_char_choice($OptionKeys,$Question="Select an option:",$Default="",$Timeout=""){
	global $vars; dse_trace();
	$OptionKeysArray=str_split($OptionKeys);
	
	print getColoredString("$Question\n","red");
	$Keys=$OptionKeys;
	
	$Os=sizeof($Options);$Oi=0;
	/*foreach($OptionKeysArray as $i=>$K){  $Oi++;
		
		print getColoredString("  $K","yellow").getColoredString(" ) ","purple").getColoredString("   $O\n","green");
	}*/
	print getColoredString("         Options: $OptionKeys            Choice? ","blink_red");
	$key=strtoupper(dse_get_key($Timeout,$Default));
	$Oi=0;
	foreach($OptionKeysArray as $K=>$O){  $Oi++;
		if($key===$O){
			print "\n";
			return $O;
		}
	}
	print getColoredString(" Invalid Option. You pressed '$key'. Valid keys: $OptionKeys\n","red","black");
	return dse_ask_char_choice($OptionKeys,$Question,$Default,$Timeout);
}
if($vars['Verbosity']>5) print "dse_cli_functions.php: line 882\n";
	
function dse_directory_strip_trail( $path ){ 
	global $vars; dse_trace();
	if(!$path){
		return "";
	}
	$path.="/";
	$path=str_replace("//","/",$path);
	
	//$LastChar=$path[strlen($path)-1];	
	//if($LastChar=="/"){
		return substr($path,0,strlen($path)-1);
	//}
	return $path;
}


function dse_directory_ls( $path = '.', $level = 0 ){ 
	global $vars; dse_trace();
	dpv(3,"dse_directory_ls($path, $level){");
	$path.="/";  $path=dse_directory_strip_trail($path);
    $ignore = array( '.', '..' ); 
    $dh = @opendir( $path ); 
	$tbr=array();
    while( false !== ( $file = readdir( $dh ) ) ){
    	//print "readdir returned file=$file\n";
    	if($file){
    		//print "p=$path f=$file\n"; 
	        if( !in_array( $file, $ignore ) ){ 
	            if( is_dir( "$path$file" ) ){
	            //	print "calling dse_directory_ls( $path $file\n"; 
	                $tbr[]=array("DIR",dse_directory_ls( "$path$file", ($level+1) ) ); 
	            } else { 
	             	$tbr[]=array("FILE","$path$file");
	            } 
			}
        } 
    } 
    closedir( $dh );
	dpv(4,"} DONE dse_directory_ls($path, $level)");
	return $tbr;
} 
function dse_ls( $search ){ 
	global $vars; dse_trace();
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

function dse_directory_to_array( $path = '.', $max_level=100, $level = 0 ){
	global $vars;
	dpv(2,"Starting dse_directory_to_array($path, $max_level, $level){");
	$tbr=array();
	$path.="/";  $path=str_replace("//", "/", $path);
    $ignore = array( '.', '..' ); 
    $ignore_partial = array( 'crafters/files','events/files', 'ratings/files', '/Zend/', '/images', '/phpMemcachedAdmin', '/ZFDebug', '/cache', '/thumbnail' ); 
    $dh = @opendir( $path ); 
	$FileAnyTypeCount=0;
    while( $dh && false !== ( $file = readdir( $dh ) ) ){ 
      //  if( !in_array( $ignore, $file ) ){
      	if($file!="." && $file!=".."){
      		$fullfilename=$path.$file;
        	$ignore=FALSE;
        	foreach($ignore_partial as $ignore_try){
        		dpv(5,"ignore_try: str_icontains($file,$ignore_try)");
        		if(str_icontains($fullfilename,$ignore_try)){
        			$ignore=TRUE;
        		}
        	}
			if(!$ignore){
				$FileAnyTypeCount++;
	            if( is_dir( $fullfilename ) ){
	            	if($level<=$max_level){
		            	$tbr[]=array(
		            		"DIR",
		            		$file,
		            		$fullfilename, 
	    	        		dse_directory_to_array( $fullfilename, $max_level, ($level+1) )
						 );
					}else{
						$tbr[]=array("DIR",$file,$fullfilename, NULL);
					}
	            } else { 
	               $tbr[]=array("FILE",$file,$fullfilename, NULL ); 
	            } 
			}
        } 
    } 
     
    closedir( $dh ); 
	if($FileAnyTypeCount>25){
		$FileAnyTypeCount=colorize($FileAnyTypeCount,"white","red");
	}
	dpv(2,"} Done dse_directory_to_array($path, $max_level, $level). Found $FileAnyTypeCount sub-entries.");
	return $tbr;
} 



function dse_pid_list(){
	//print " dse_pid_is_running($PID)\n";
	global $vars; dse_trace();
	$PIDList=array();
	$raw=dse_exec("sudo ps aux");
	foreach (split("\n",$raw) as $line) {
		$line=trim($line);
		$lpa=split("[ \t]+",$line);
		//print_r($lpa);
		if($lpa[1]>0){
			$PIDList[$lpa[1]]=array('exe'=>$lpa[10],'mem'=>$lpa[3],'cpu'=>$lpa[2]);
		}
	}
	//print_r($PIDList);
	return ($PIDList);
}


function dse_pid_is_running($PID){
	//print " dse_pid_is_running($PID)\n";
	global $vars; dse_trace();
	$PIDInfo=dse_pid_get_info($PID);
	//print_r($PIDInfo);
	return ($PIDInfo['PPID']>0);
}

function dse_pid_get_info($PID){
	global $vars; dse_trace();
	if(!$PID){
		return null;
	}
	//print "panic A\n";
	$PIDInfo=array();
	$PIDInfo['PID']=$PID;
	$PIDInfo['PPID']=dse_pid_get_ps_columns($PID,"ppid");
	$PIDInfo['PCPU']=dse_pid_get_ps_columns($PID,"pcpu");
	$PIDInfo['PMEM']=dse_pid_get_ps_columns($PID,"pmem");
	$PIDInfo['USER']=dse_pid_get_ps_columns($PID,"user");
	$PIDInfo['EXE']=trim(`/dse/bin/pid2exe $PID`);
	
	$PIDInfo['EXE_FILE']=$PIDInfo['EXE'];
	$T=0;
	//print "panic b\n";
	while($T<10 && str_contains($PIDInfo['EXE_FILE'],"/")){
		$T++;
	//print "panic c\n";
		$PIDInfo['EXE_FILE']=strcut($PIDInfo['EXE_FILE'],"/");
	}
//	print "panic d\n";
	return $PIDInfo;
}
function dse_pid_get_info_str($PID,$Recursive=FALSE,$Reverse=FALSE){
	global $vars; dse_trace();
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
	global $vars; dse_trace();
	//print "dse_pid_get_ps_columns a dse_pid_get_ps_columns($PID,$o)\n";
	$PID=intval($PID);
	if(!$PID){
		return -1;
	}
	$Command="ps -p $PID -o $o=";
	//print "dse_pid_get_ps_columns b\n";
	$Value=trim(`$Command`);
	//print "dse_pid_get_ps_columns b\n";
	$Message="dse_pid_get_ps_columns($PID,$o) [$Command] returning $Value";
	//print "$Message\n";
	dpv(5,$Message);
	return $Value;		
}


function dse_which($prog){
	global $vars; dse_trace();
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
	global $vars; dse_trace();
	global $argv;
	
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
	global $vars; dse_trace();
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
		}
		//else{
			print "|  * Script: ".$vars['DSE']['SCRIPT_FILENAME']."\n";
			if($vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']){
				print "|  ".$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']."\n";
			}
			print "|  * verbosity: ".$vars['DSE']['Verbosity']."\n";
			print "|  * Script Version: ".$vars['DSE']['SCRIPT_VERSION']."\n";
			print "|  * Script Release Date: ".$vars['DSE']['SCRIPT_VERSION_DATE']."\n";
		//}
		print " \________________________________________________________ __ _  _   _\n";
		//print "\n";  


	}
}


function dse_require_root(){
	global $vars; dse_trace();
	global $argv;
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
	global $vars; dse_trace();
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
	global $vars; dse_trace();
	$m1=md5_of_file($f1);
	$m2=md5_of_file($f2);
	//print "files_are_same:md5: $m1==$m2<br>";
	return ($m1==$m2);
}

function dse_file_get_size($DestinationFile){
	global $vars;
	return dse_file_get_stat_field($DestinationFile,"size");
}

function dse_file_extension($File){
	global $vars; dse_trace();
	$File=basename($File);
	$Extension=strcut($File,".");
	if(str_contains($Extension,".")) return dse_file_extension($Extension);
	return $Extension;
}

function dse_file_get_mtime($DestinationFile){
	global $vars;
	return dse_file_get_stat_field($DestinationFile,"mtime");
}

function dse_file_get_stat_array($DestinationFile){
	global $vars; dse_trace();
	dpv(5, "dse_file_get_stat_array($DestinationFile)");
//	$stat_field_names=array('dev'=>0,'ino'=>1,'mode'=>2,'nlink'=>3,'uid'=>4,'gid'=>5,'rdev'=>6,'size'=>7,'atime'=>8,'mtime'=>9,'ctime'=>10,'blksize'=>11,'blocks'=>12);
	//if(!dse_file_exists($DestinationFile)){
	if(!file_exists($DestinationFile)){
		print "\n";
		dpv(4, "Error in dse_file_get_stat_array($DestinationFile) - file does not exist.");
		return -1;
	}
	$sa=stat($DestinationFile);
	return $sa;
}

function dse_file_get_alt_stat_array($file) {
	global $vars; dse_trace();
 clearstatcache();
 $ss=@stat($file);
 if(!$ss) return false; //Couldnt stat file
 
 $ts=array(
  0140000=>'ssocket',
  0120000=>'llink',
  0100000=>'-file',
  0060000=>'bblock',
  0040000=>'ddir',
  0020000=>'cchar',
  0010000=>'pfifo'
 );
 
 $p=$ss['mode'];
 $t=decoct($ss['mode'] & 0170000); // File Encoding Bit
 
 $str =(array_key_exists(octdec($t),$ts))?$ts[octdec($t)]{0}:'u';
 $str.=(($p&0x0100)?'r':'-').(($p&0x0080)?'w':'-');
 $str.=(($p&0x0040)?(($p&0x0800)?'s':'x'):(($p&0x0800)?'S':'-'));
 $str.=(($p&0x0020)?'r':'-').(($p&0x0010)?'w':'-');
 $str.=(($p&0x0008)?(($p&0x0400)?'s':'x'):(($p&0x0400)?'S':'-'));
 $str.=(($p&0x0004)?'r':'-').(($p&0x0002)?'w':'-');
 $str.=(($p&0x0001)?(($p&0x0200)?'t':'x'):(($p&0x0200)?'T':'-'));
 
 $s=array(
 'perms'=>array(
  'umask'=>sprintf("%04o",@umask()),
  'human'=>$str,
  'octal1'=>sprintf("%o", ($ss['mode'] & 000777)),
  'octal2'=>sprintf("0%o", 0777 & $p),
  'decimal'=>sprintf("%04o", $p),
  'fileperms'=>@fileperms($file),
  'mode1'=>$p,
  'mode2'=>$ss['mode']),
 
 'owner'=>array(
  'fileowner'=>$ss['uid'],
  'filegroup'=>$ss['gid'],
  'owner'=>
  (function_exists('posix_getpwuid'))?
  @posix_getpwuid($ss['uid']):'',
  'group'=>
  (function_exists('posix_getgrgid'))?
  @posix_getgrgid($ss['gid']):''
  ),
 
 'file'=>array(
  'filename'=>$file,
  'realpath'=>(@realpath($file) != $file) ? @realpath($file) : '',
  'dirname'=>@dirname($file),
  'basename'=>@basename($file)
  ),

 'filetype'=>array(
  'type'=>substr($ts[octdec($t)],1),
  'type_octal'=>sprintf("%07o", octdec($t)),
  'is_file'=>@is_file($file),
  'is_dir'=>@is_dir($file),
  'is_link'=>@is_link($file),
  'is_readable'=> @is_readable($file),
  'is_writable'=> @is_writable($file)
  ),
  
 'device'=>array(
  'device'=>$ss['dev'], //Device
  'device_number'=>$ss['rdev'], //Device number, if device.
  'inode'=>$ss['ino'], //File serial number
  'link_count'=>$ss['nlink'], //link count
  'link_to'=>($s['type']=='link') ? @readlink($file) : ''
  ),
 
 'size'=>array(
  'size'=>$ss['size'], //Size of file, in bytes.
  'blocks'=>$ss['blocks'], //Number 512-byte blocks allocated
  'block_size'=> $ss['blksize'] //Optimal block size for I/O.
  ), 
 
 'time'=>array(
  'mtime'=>$ss['mtime'], //Time of last modification
  'atime'=>$ss['atime'], //Time of last access.
  'ctime'=>$ss['ctime'], //Time of last status change
  'accessed'=>@date('Y M D H:i:s',$ss['atime']),
  'modified'=>@date('Y M D H:i:s',$ss['mtime']),
  'created'=>@date('Y M D H:i:s',$ss['ctime'])
  ),
 );
 
 clearstatcache();
 return $s;
}


function dse_file_get_stat_field($DestinationFile,$field=""){
	global $vars; dse_trace();
	$stat_field_names=array('dev'=>0,'ino'=>1,'mode'=>2,'nlink'=>3,'uid'=>4,'gid'=>5,'rdev'=>6,'size'=>7,'atime'=>8,'mtime'=>9,'ctime'=>10,'blksize'=>11,'blocks'=>12);
	if(!dse_file_exists($DestinationFile)){
		dpv(4,  "Error in dse_file_get_mode($DestinationFile,$field) - file does not exist.");
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
	global $vars; dse_trace();
	$r=`ls -la $DestinationFile 2>&1`;
	if(str_contains($r,'No such file or directory')){
		return FALSE;
	}
	return TRUE;
}

function dse_file_get_mode($DestinationFile){
	global $vars; dse_trace();
	$ModeInt=intval(substr(sprintf('%o', fileperms($DestinationFile)), -4));
	return $ModeInt;
}

function dse_file_get_owner($DestinationFile,$ReturnGroupAlso=TRUE){
	global $vars; dse_trace();
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
	
function dse_gid_name($gid){ 
	global $vars; dse_trace();
	if($gid){
		$a=dse_posix_getgrgid($gid);
		if($a) return $a['name'];
	}
	return $gid;
}
function dse_uid_name($uid){ 
	global $vars; dse_trace();
	if($uid){
		$a=dse_posix_getpwuid($uid);
		if($a) return $a['name'];
	}
	return $uid;
}


function dse_posix_getgrgid($gid){ 
	global $vars; dse_trace();
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
	global $vars; dse_trace();
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
	global $vars; dse_trace();
	if(!$File){
		return -1;
	}
	if(str_contains($File,array(" ",",","/","\\","&",">","<","|","!","`","^","?",";",":"))){
		return -2;
	}
	$r=`rm -f $File`;
	return 0;
}



function dse_file_set_mode($DestinationFile,$Mode,$Recursive=FALSE){
	global $vars; dse_trace();
	if($DestinationFile && $Mode){
		if($Recursive){
			$command="chmod -R $Mode $DestinationFile 2>&1";
		}else{
			$command="chmod $Mode $DestinationFile 2>&1";
		}
		print `$command`;
		$CurrentPermissions=dse_file_get_mode($DestinationFile);
		if(intval($Mode)!=$CurrentPermissions){
			return -2;
		}
		return 0;
	}
	return -1;
}

function dse_file_set_owner($DestinationFile,$Owner,$Recursive=FALSE){
	global $vars; dse_trace();
	if($DestinationFile && $Owner){
		if($Recursive){
			$command="chown -R $Owner $DestinationFile";
		}else{
			$command="chown $Owner $DestinationFile";
		}
		`$command`;
		//$CurrentPermissions=dse_file_get_mode($DestinationFile);
		//if(intval($Mode)!=$CurrentPermissions){
		//	return -2;
		//}
		return 0;
	}
	return -1;
}

function dse_mkdir($Destination,$Mode="",$Owner=""){
	global $vars; dse_trace();
	return dse_directory_create($Destination,$Mode,$Owner);
}

function dse_directory_create($Destination,$Mode="",$Owner=""){
	global $vars; dse_trace();
	print "dse_directory_create($Destination,$Mode,$Owner);\n";
	if(!file_exists($Destination)) {
		print " creating\n";
		$command="mkdir -p $Destination";
		dse_exec($command,TRUE);
	}
	
	if(!file_exists($Destination)) {
		print getColoredString(" ERROR: failed to create $Destination . \n","red","black");
		return -2;	
	}
	
	if($Owner){
		$command="chown -R $Owner $Destination";
		print " doing $command\n";
		`$command`;
	}
	if($Mode){
		$command="chmod -R $Mode $Destination";
		print " doing $command\n";
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
	global $vars; dse_trace();
	print "DSE file link: $LinkFile => $DestinationFile ";
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
		/*$DestinationFileCurrent=dse_file_link_get_destination($LinkFile);
		if($DestinationFileCurrent>=0){
			print getColoredString(" link broken. ","orange","black");
			dse_file_delete($LinkFile);
		}*/
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
	global $vars; dse_trace();
	$DestinationFileCurrent=dse_exec("ls -la \"$File\"");
	if(str_contains($DestinationFileCurrent,"->")){
		return TRUE;
	}
	return FALSE;
}
function dse_file_mv($S,$D){
	global $vars; dse_trace();
	if(!dse_file_exists($S)) return FALSE;
	if(dse_file_exists($D)) {
		dse_file_delete($D);
	}
	dse_exec("mv -rf \"$S\" \"$D\" ");
}

function dse_file_link_get_destination($LinkFile){
	global $vars; dse_trace();
	
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
 
function is_already_running($exe="",$ExitOnTrue=TRUE,$MessageOnExit=TRUE){
	global $vars; dse_trace();
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
	global $vars; dse_trace();
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
	global $vars; dse_trace();
	
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
	global $vars; dse_trace();
	return dse_file_size_to_readable(dse_file_get_size($file));
}

function dse_file_size_to_readable($size){
	global $vars;
	if($size<1024){
		return number_format($size,0)." B";
	}elseif($size<1024*1024){
		return number_format($size/1024,0)." k";
	}elseif($size<1024*1024*1024){
		return number_format($size/(1024*1024),0)." M";
	}elseif($size<1024*1024*1024*1024){
		return number_format($size/(1024*1024*1024),1)." G";
	}else{
		return number_format($size,0)." B";
	}
}

function dse_file_get_contents($filename){
	global $vars; dse_trace();
	return `cat $filename`;
}
function dse_file_append_contents($filename,$Str){
	global $vars; dse_trace();
	return dse_file_put_contents($filename,dse_file_get_contents($filename).$Str);
}
function dse_file_add_line_if_not($filename,$Str,$LineNumber=0,$ShowCommand=FALSE){
	global $vars; dse_trace();
	if($ShowCommand){
		print colorize("dse_file_add_line_if_not(","yellow","black");
		print colorize($filename,"magenta","black");
		print colorize(",","yellow","black");
		print colorize($Str,"green","black");
		print colorize(")","yellow","black");
		print "\n";	
	}
	$Now=dse_file_get_contents($filename);
	if(!str_contains($Now,$Str)){
		if($LineNumber==0){
			return dse_file_put_contents($filename,$Now."\n".$Str);
		}else{
			$tbr="";
			foreach(split("\n",$Now) as $Li=>$L){
				if($Li==$LineNumber){
					$Added=TRUE;
					$tbr.="$Str\n";
				}
				$tbr.="$L\n";
			}
			if(!$Added){
				$tbr.="$Str\n";
			}
			return dse_file_put_contents($filename,$tbr);
		}
	}
}

function dse_file_insert_line($filename,$Str,$LineNumber=0,$ShowCommand=FALSE){
	global $vars; dse_trace();
	$tmp=dse_exec("/dse/bin/dtmp");
	$Lines=dse_exec("wc -l $filename");
	if($LineNumber==0){
		dse_exec("echo -n \"\" > $tmp");
	}else{
		$Head=$LineNumber-1;
		dse_exec("head -n $Head $filename > $tmp");
	}
	dse_exec("echo \"$Str\" >> $tmp");
	$Tail=$Lines-$LineNumber;
	dse_exec("tail -n $Tail $filename >> $tmp");
	dse_file_mv($tmp,$filename);
}

function dse_file_replace_str($File,$Needle,$Replacement){
	global $vars; dse_trace();
	dse_replace_in_file($File,$Needle,$Replacement);
}

function dse_file_put_contents($filename,$Str){
	global $vars; dse_trace();
	return file_put_contents($filename,$Str);
}

// returns array of Names=>Values
function dse_read_config_file($filename,$tbra=array(),$OverwriteExisting=FALSE){
	global $vars; dse_trace();
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
				}elseif(str_contains($Name,"[")){
					$NameBase=strcut($Name,"","[");
					$NameIndex=strcut($Name,"[","]");
					if( (!isset($tbra[$NameBase])) ){
						$tbra[$NameBase]=array();
					} 
					$tbra[$NameBase][$NameIndex]=$Value;
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


function dse_tmp_file(){
	global $vars; dse_trace();
	$DATE_TIME_NOW=trim(`date +"%y%m%d%H%M%S"`);
		
	$TmpDir="/tmp/";
	$TmpFile=$TmpDir."dse_tmp_file_${DATE_TIME_NOW}_0".rand(10000,99999);
	while(file_exists($TmpFile)){
		$TmpFile=$TmpDir."dse_tmp_file_${DATE_TIME_NOW}_0".rand(10000,99999);
	}
	return $TmpFile;
}

function str_icontains($str,$needle){
	global $vars; dse_trace();
	return str_contains($str,$needle,TRUE);;
}
function str_contains($str,$needle,$CaseInSensitive=FALSE){
	global $vars; dse_trace();
	if(is_array($needle)){
		foreach($needle as $n){
			if($CaseInSensitive){
				if(!(stristr($str,$n)===FALSE)) return TRUE;
			}else{
				if(!(strstr($str,$n)===FALSE)) return TRUE;
			}
		}
	}else{
		if($CaseInSensitive){
			if(!(stristr($str,$needle)===FALSE)) return TRUE;
		}else{
			if(!(strstr($str,$needle)===FALSE)) return TRUE;
		}
	}
	return FALSE;
}

function str_remove($String,$toRemove){
	global $vars; dse_trace();
	return str_replace($toRemove,"",$String);
}

function str_head($String,$N){
	global $vars; dse_trace();
	return substr($String,0,$N);
}

function str_tail($String,$N){
	global $vars; dse_trace();
	dpv(6,"str_tail($String,$N)");
	global $vars;
	$sl=strlen($String);
	$pos=$sl-$N;
	$tbr=substr($String,$pos);
	
	dpv(6,"sl=$sl pos=$pos tbr=$tbr");
	return $tbr;
}

function str_igrep($String,$Grep){
	global $vars; dse_trace();
	$tbr=array();
	foreach(split("\n",$String) as $L){
		if(str_icontains($L,$Grep)){
			$tbr[]=$L;
		}
	}	
	return array2string($tbr);
}

function array2string($LineArray){
	global $vars; dse_trace();
	$tbr="";
	foreach($LineArray as $L){
		if($tbr) $tbr.="\n";
		$tbr.=$L;
	}	
	return $tbr;
}

function strcut($haystack,$pre,$post=""){
	global $vars; dse_trace();
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
	
function bar($String,$Type,$fg,$bg,$bfg="",$bbg=""){
	global $vars; dse_trace();
	if($bfg==""){
		$bfg=$fg;
	}
	if($bbg==""){
		$bbg=$bg;
	}
	$HeaderColorCodeCount=substr_count ($String , "[");
	$HeaderText=$String;
	if(strlen($HeaderText)*2<cbp_get_screen_width()*(2/3)){
		$BarWidth=cbp_get_screen_width()-strlen($HeaderText)*2-($HeaderColorCodeCount*5)*2-7;
		print colorize($HeaderText."  ",$fg,$bg,TRUE,1);
		print colorize(pad("",$BarWidth,$Type),$bfg,$bbg,TRUE,1);
		print colorize("  ".$HeaderText,$fg,$bg,TRUE,1);
	}else{
		$BarWidth=cbp_get_screen_width()-strlen($HeaderText)-($HeaderColorCodeCount*5)-7;
		print colorize($HeaderText."  ",$fg,$bg,TRUE,1);
		print colorize(pad("",$BarWidth,$Type),$bfg,$bbg,TRUE,1);
	}
	print "\n";
}


function pad($String,$Length,$PadChar=" ",$Justification="left"){
	global $vars; dse_trace();
	if($vars['Verbosity']>5)$inString=$String;
	if(str_contains($Length,"%")){
		$ScreenWidth=cbp_get_screen_width();
		$Length=str_remove($Length,"%");
		$Length=intval($ScreenWidth*($Length/100));
	}
	$tbr="";
	$tbr_len=0;
	while($tbr_len<$Length && strlen($String)>0){
		$l=substr($String,0,1);
		$String=substr($String,1);
		if($l=='\033'){
			$tbr.=$l;
			while($l!='m' && strlen($String)>0){
				$l=substr($String,0,1);
				$String=substr($String,1);
				$tbr.=$l;
			}
		}else{
			$tbr_len++;
			$tbr.=$l;
		}
	}
	$String=$tbr;
	
	$CurrentLength=$tbr_len;
	$sl=strlen($String);
	//print "\n pad($String,$Length,$PadChar)  sl=$sl CurrentLength=$CurrentLength\n";
	
//	print "pad($String,$Length,$PadChar) ScreenWidth=$ScreenWidth CurrentLength=$CurrentLength\n";
	if($CurrentLength>=$Length) return substr($String,0,$Length);
	for($i=$CurrentLength;$i<$Length;$i++){
		switch($Justification){
			case 'left':
				$Added++;
				$String=$String.$PadChar;
				break;
			case 'center':
				if($i<$Length-1){
					if($i%2==1){
						$Added+=2;
						$String=$PadChar.$String.$PadChar;
					}
				}else{
					$Added++;
					$String.=$PadChar;
				}
				break;
			case '':
			case 'right':
			default:
				$Added++;
				$String=$PadChar.$String;
				break;
		}
	}
	while(strlen($String)<$Length){
		$String.=$PadChar; 
	}
	//print "Added=$Added \n";
	dpv(6," pad($inString,$Length,$PadChar,$Justification) = [$String] ");
	return $String;
}
	
function unk_time($TimeAndDateString){
	global $vars; dse_trace();
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
	
	elseif( preg_match ("/^\\[[a-zA-Z]{3} [a-zA-Z]{0,9} [0-9]{1,2} [0-9]{2}:[0-9]{2}:[0-9]{2} [0-9]{4}/" , $TimeAndDateString, $matches) >0 ){
		dpv(5,"preg_match 7b\n");
		$TimeAndDateString=substr($TimeAndDateString,1);
		$len=25; $format = '%a %B %d %H:%M:%S %Y';}//[Thu Jul 05 22:49:49 2012] 
		
	elseif( preg_match ("/^[a-zA-Z]{3} [a-zA-Z]{0,9} [0-9]{1,2}[a-z]{0,2}, [0-9]{4}, [0-9]{1,2}:[0-9]{2}.[0-9]{2} [a-zA-Z]{2} [a-zA-Z]{3}/" , $TimeAndDateString, $matches) >0 ){
			dpv(5,"preg_match 8 \n");
			$TimeAndDateString=str_replace("th, ",", ",$TimeAndDateString);
		$TimeAndDateString=str_replace("st, ",", ",$TimeAndDateString);
		$TimeAndDateString=str_replace("rd, ",", ",$TimeAndDateString);
		if(str_contains($TimeAndDateString,"EDT")){
			$TimeAndDateString=strcut($TimeAndDateString,""," EDT");
		}elseif(str_contains($TimeAndDateString,"PDT")){
			$TimeAndDateString=strcut($TimeAndDateString,""," PDT");
		}else{
			$TimeAndDateString=strcut($TimeAndDateString,"","DT");
		}
		$len=52; $format = '%a %B %d, %Y, %l:%M.%S %P';
				 
		list($DayName,$MonthName,$Date,$Year,$Hour,$Minute,$Second,$AMPM) = sscanf($TimeAndDateString, "%s %s %d, %d, %d:%d.%d %s");
		//print "d=$DayName,m=$MonthName,dt=$Date,y=$Year,h=$Hour,m=$Minute,s=$Second,ampm=$AMPM\n";
		if($AMPM=="PM" || $AMPM=="pm") $Hour+=12;
			if(stristr($MonthName,"Jan"))		$Month="1";
			if(stristr($MonthName,"Feb"))		$Month="2";
			if(stristr($MonthName,"Mar"))		$Month="3";
			if(stristr($MonthName,"Apr"))		$Month="4";
			if(stristr($MonthName,"May"))		$Month="5";
			if(stristr($MonthName,"Jun"))		$Month="6";
			if(stristr($MonthName,"Jul"))		$Month="7";
			if(stristr($MonthName,"Aug"))		$Month="8";
			if(stristr($MonthName,"Sep"))		$Month="9";
			if(stristr($MonthName,"Oct"))		$Month="10";
			if(stristr($MonthName,"Nov"))		$Month="11";
			if(stristr($MonthName,"Dec"))		$Month="12";
			if(stristr($MonthName,"January"))		$Month="1";
			if(stristr($MonthName,"Febuary"))		$Month="2";
			if(stristr($MonthName,"March"))		$Month="3";
			if(stristr($MonthName,"April"))		$Month="4";
			if(stristr($MonthName,"May"))		$Month="5";
			if(stristr($MonthName,"June"))		$Month="6";
			if(stristr($MonthName,"July"))		$Month="7";
			if(stristr($MonthName,"August"))		$Month="8";
			if(stristr($MonthName,"September"))		$Month="9";
			if(stristr($MonthName,"October"))		$Month="10";
			if(stristr($MonthName,"November"))		$Month="11";
			if(stristr($MonthName,"December"))		$Month="12";
		$t=@mktime($Hour, $Minute, $Second, $Month, $Date, $Year);
		//	print "\nunk_time=$TimeAndDateString  fmt=$format   t=$t\n";
		return $t;
	
	
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
	
	elseif( preg_match ("/^[0-9]{6} [0-9]{1,2}:[0-9]{2}:[0-9]{2} /" , $TimeAndDateString, $matches) >0 ){
		dpv(5,"preg_match 10c \n");
		$TimeAndDateString=strcut($TimeAndDateString,"",": ");
		$len=15; $format = '%y%m%d %H:%M:%S';
	}//120705 21:14:43
	
	elseif( str_contains ( $TimeAndDateString, " - - [") >0 ){
		$len=0;
		dpv(5,"preg_match 11 - $TimeAndDateString\n");
	//	$TimeAndDateString=strcut($TimeAndDateString,"["," ");
		$TimeAndDateString=substr(strcut($TimeAndDateString,"- - "," "),1);
		dpv(5,"preg_match 11b - $TimeAndDateString\n");
		$format = '%d/%b/%Y:%H:%M:%S';
	}
		
	dpv(5,colorize(" found format=$format\n","green"));
	
	
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
		// "$dateTime=strptime($TimeAndDateString, $format);\n";
		if($dateTime){
			dpv(5,colorize("strptime($TimeAndDateString, $format) dateTime=$dateTime\n","cyan"));
		}else{
			dpv(0,colorize("strptime($TimeAndDateString, $format) dateTime=$dateTime\n","red"));
		}
//	print_r($dateTime);
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

if($vars['Verbosity']>5) print "dse_cli_functions.php: line 1885\n";

function date_str_to_sql_date($str,$fmt=""){
	global $vars; dse_trace();
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
	global $vars; dse_trace();
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
	global $vars; dse_trace();
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


function time_float(){
	global $vars; //dse_trace();
	return (time()+microtime());
}

function readable_date($Date){
	global $vars;
	return readable_date_range($Date,$Date);
}
	
	
function readable_date_range($Start,$End){
	global $vars;
	
	/*$STime=SQLDate2time($Start);
	$ETime=SQLDate2time($End);
	$SMonth=date("n",$STime);
	$EMonth=date("n",$ETime);
	$SDay=date("j",$STime);
	$EDay=date("j",$ETime);
	$SYear=date("y",$STime);
	$EYear=date("y",$ETime);	
	$TYear=date("y");
	*/
	$SDay=$Start[8].$Start[9];
	$SMonth=$Start[5].$Start[6];
	$SYear=$Start[2].$Start[3];
	$SYearC=$Start[0].$Start[1];
	$EDay=$End[8].$End[9];
	$EMonth=$End[5].$End[6];
	$EYear=$End[2].$End[3];
	$EYearC=$End[0].$End[1];
	$TYear=date("y");
	
	//$YSsuf="&nbsp;<font size=-1>$SYearC</font>$SYear";		
	//$YEsuf="&nbsp;<font size=-1>$EYearC</font>$EYear";
	$YSsuf="&nbsp;$SYearC$SYear";		
	$YEsuf="&nbsp;$EYearC$EYear";
	
	if($SMonth==$EMonth){
		if($SYear!=$TYear){
			//$YEsuf="&nbsp;$SYear";
		}else{
			$YEsuf="";
		}
		if($SDay==$EDay){
			return "$SMonth/${SDay}$YEsuf";
		}else{
			return "$SMonth/${SDay}-$SMonth/${EDay}$YEsuf";
		}
	}else{
		//if($SYear!=$EYear || $TYear!=$SYear || $TYear!=$EYear){
		if($SYear==$EYear ){
			$YSsuf="";
		}
		if($EYear==$TYear ){
			$YEsuf="";
		}
		return "$SMonth/${SDay}${YSsuf}-$EMonth/${EDay}$YEsuf";
	}	
}

function DateAddDays($OldDate,$Days){
	if($OldDate){
		$OldTime=YYYYMMDD2time($OldDate);
		if($OldTime){
			$NewTime=$OldTime+60*60*24*$Days;
			$NewDate=date("Y-m-d", $NewTime);
		}
	}
	return $NewDate;
}

function MMDDYYYY2time($in){	
	$t = split("/",$in);
	if (count($t)!=3) $t = split("-",$in);
	if (count($t)!=3) $t = split(" ",$in);
	if (count($t)!=3) return -1;
	if (!is_numeric($t[0])) return -2;
	if (!is_numeric($t[1])) return -3;
	if (!is_numeric($t[2])) return -4;	
	if($t[2]<100 && $t[2]>0){
		$t[2]+=2000;
	}	
	if ($t[2]<1902 || $t[2]>2037) return -5;
	if ($t[2]<1970){
		$year_offset=1970-$t[2];
		$t[2]=1970;
	}	
	$result=mktime (0,0,0, $t[0], $t[1], $t[2]);
	if($year_offset){
		$result-=$year_offset*365*24*60*60;
	}
	return $result;
}

function DDMMYYYY2time($in){	
	$t = split("/",$in);
	if (count($t)!=3) $t = split("-",$in);
	if (count($t)!=3) $t = split(" ",$in);
	if (count($t)!=3) return -1;
	if (!is_numeric($t[0])) return -2;
	if (!is_numeric($t[1])) return -3;
	if (!is_numeric($t[2])) return -4;	
	if ($t[2]<1902 || $t[2]>2037) return -5;
	if ($t[2]<1970){
		$year_offset=1970-$t[2];
		$t[2]=1970;
	}	
	$result=mktime (0,0,0, $t[1], $t[2], $t[0]);
	if($year_offset){
		$result-=$year_offset*365*24*60*60;
	}
	return $result;
}


function time2SQLDate($in){
	return date("Y-m-d",$in);
}

function SQLDate2time($in){
	return YYYYMMDD2time($in);
}

function YYYYMMDD2time($in){
	global $vars; dse_trace();
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
	global $vars; dse_trace();
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
	return d2s($var);
}
	
function v2s(&$var){
	return d2s($var);
}
	
function d2s(&$var){
	global $vars; dse_trace();
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
	global $vars; dse_trace();
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




function remove_duplicate_lines($String){
	global $vars; dse_trace();
	$StringArray=split("\n",$String);
	$out=remove_duplicate_array_lines($StringArray);
	$Out2="";
	foreach($out as $Line){
		if($Out2){
			$Out2.="\n";
		}
		$Out2.=$Line;
	}
	return $Out2;
}


function remove_duplicate_array_lines($StringArray){
	global $vars; dse_trace();
	$out=array();
	foreach($StringArray as $Line){
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
	return $out;
}



function remove_blank_lines($String){
	global $vars; dse_trace();
	$out=array();
	foreach(split("\n",$String) as $Line){
		if(trim($Line)!=""){
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

function whitespace_minimize($String){
	global $vars; dse_trace();
	$String=trim(str_replace("\t"," ",$String));
	while(str_contains($String,"  ")){
		$String=trim(str_replace("  "," ",$String));
	}
	return $String;
}




function file_count_lines($File){
	global $vars; dse_trace();
	return (trim(dse_exec("wc -l $File",$vars['Verbosity']>4)));
}

function combine_sameprefixed_lines($LogsCombined){
	global $vars; dse_trace();
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
	global $vars; dse_trace();
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
	global $vars; dse_trace();
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
	global $vars; dse_trace();
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
	// hwprefs os_class   => Snow Leopard     hwprefs os_type => Mac OS X 10.6.8 (10K549)
	
	
}
function dse_is_ubuntu(){
	global $vars; dse_trace();
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
function dse_ubuntu_release(){
	global $vars; dse_trace();
	if(isset($vars['DSE']['UBUNTU_RELEASE'])) return $vars['DSE']['UBUNTU_RELEASE'];
	if(!file_exists("/etc/issue")){
		$vars['DSE']['IS_UBUNTU']=FALSE;
	}else{
		$EtcIssue=dse_file_get_contents("/etc/issue");
		if(str_contains($EtcIssue,"Ubuntu 9.10.")){
			$vars['DSE']['UBUNTU_RELEASE']="karmic";
		}elseif(str_contains($EtcIssue,"Ubuntu 10.04.")){
			$vars['DSE']['UBUNTU_RELEASE']="lucid";
		}elseif(str_contains($EtcIssue,"Ubuntu 10.10.")){
			$vars['DSE']['UBUNTU_RELEASE']="maverick";
		}elseif(str_contains($EtcIssue,"Ubuntu 11.04.")){
			$vars['DSE']['UBUNTU_RELEASE']="natty";
		}else{
			$vars['DSE']['UBUNTU_RELEASE']="unkown";
		}
	}
	return $vars['DSE']['UBUNTU_RELEASE'];
}
function dse_is_centos(){
	global $vars; dse_trace();
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
	global $vars; dse_trace();
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
	global $vars; dse_trace();
	$parameters=array();
	foreach($parameters_details as $p){
		$parameters[$p[0]]=$p[1];
	}
	return $parameters;
}

function dse_cli_get_parameters_readable_brief($parameters_details){
	global $vars; dse_trace();
	$tbr="";
	foreach($parameters_details as $p){
		if($tbr)$tbr.=" ";
		$p[1]=str_replace(":","+arg",$p[1]);
		$tbr.="--".$p[1];
	}
	return $tbr;
}



function dse_cli_get_usage($parameters_details){
	global $vars; dse_trace();

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
	global $vars; dse_trace();

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
	global $vars; dse_trace();

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
	global $vars; dse_trace();
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
	global $vars; dse_trace();
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
	global $vars; dse_trace();
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
	global $vars; //dse_trace();
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
	global $vars; //dse_trace();
	foreach($vars['DSE']['RedWords'] as $RedWord) $L=str_ireplace($RedWord,colorize($RedWord,"red"),$L);
	foreach($vars['DSE']['GreenWords'] as $GreenWord) $L=str_ireplace($GreenWord,colorize($GreenWord,"green"),$L);
	foreach($vars['DSE']['BlueWords'] as $BlueWord) $L=str_ireplace($BlueWord,colorize($BlueWord,"blue"),$L);
	foreach($vars['DSE']['MagentaWords'] as $PurpleWord) $L=str_ireplace($PurpleWord,colorize($PurpleWord,"purple"),$L);
	foreach($vars['DSE']['YellowWords'] as $YellowWord) $L=str_ireplace($YellowWord,colorize($YellowWord,"yellow"),$L);
	foreach($vars['DSE']['CyanWords'] as $CyanWord) $L=str_ireplace($CyanWord,colorize($CyanWord,"cyan"),$L);
	return $L;			
}
function color_pad($string, $forground_color, $background_color, $PadSize, $Align="left") {
	global $vars; //dse_trace();
	return getColoredString(pad($string,$PadSize," ",$Align), $forground_color, $background_color);
}

function colorize($string, $forground_color = null, $background_color = null, $ResetColorsAfter=TRUE, $type=null) {
	global $vars; //dse_trace();
	//print " colorize(string, $forground_color , $background_color , $ResetColorsAfter, $type) \n";
	return getColoredString($string, $forground_color, $background_color, $ResetColorsAfter, $type);
}

function getColoredString($string, $forground_color = null, $background_color = null, $ResetColorsAfter=TRUE, $type=null) {
	global $vars; 
	
	
	if($vars['DSE']['OUTPUT_FORMAT']=="HTML"){
		$tbr="";
		if($background_color){
			$tbr.="<span style='background-color:$background_color;'>";
		}
		$tbr.="<span style='color:$forground_color;'>$string</span>";
		if($background_color){
			$tbr.="</span>";
		}
		return $tbr;
	}
	
	
//if($vars['Verbosity']>5) print "dse_cli_functions.php: getColoredString pre trace\n";
//dse_trace();

//if($vars['Verbosity']>5) print "dse_cli_functions.php: getColoredString post trace\n";
	//print "getColoredString(string, $forground_color, $background_color, $ResetColorsAfter, $type) {\n";
	////print "\n\ngetColoredString($string, $forground_color = null, $background_color = null, $ResetColorsAfter=TRUE, $type=null) \n\n";
	/*if($forground_color!=null && $forground_color!=""){
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
	 */
	//print "intval($background_color)==$background_color ";
	if($forground_color==""){
		$forground_color=$vars['DSE']['SHELL_FORGROUND'];
	}

	if($background_color==""){
		$background_color=$vars['DSE']['SHELL_BACKGROUND'];
	}
	
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
	$colored_string  ="";
	$colored_string .= "\033[$type;$forground_color_code;$background_color_code"."m";
	$colored_string .=  $string;//"      ".$string."==".$type.";".$forground_color_code.";".$background_color_code."m";
	
	
//	print "=+== $type;$forground_color_code;$background_color_code   ++++++\n\n\n";
	//$colored_string .= "\033[0m";
		
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
	global $vars; dse_trace();
	return getColoredString("", null, $background_color, FALSE);
}
function setForgroundColor($forground_color) {
	global $vars; dse_trace();
	return getColoredString("", $forground_color, null, FALSE);
}
	
	
	
function getForegroundColors() {
	global $vars; dse_trace();
	return array_keys($vars[shell_foreground_colors]);
}
 
function getBackgroundColors() {
	global $vars; dse_trace();
	return array_keys($vars[shell_background_colors]);
}
	
 
 
function cbp_get_screen_width(){
	global $vars; dse_trace();
	$Command="stty size | cut -d\" \" -f2";
	return trim(dse_exec($Command));
}
function cbp_get_screen_height(){
	global $vars; dse_trace();
	$Command="stty size | cut -d\" \" -f1";
	return trim(dse_exec($Command));
}
 

function sbp_cursor_postion($L=0,$C=0){
	global $vars; dse_trace();
    print "\033[${L};${C}H";
}
//function sbp_cursor_column($C=0){
  //      print "\033[;${C}H";
//}
/*
function cbp_cursor_save(){
	global $vars; dse_trace();
    //print "\0337";
    $vars[cbp_cursor_save__position]=dse_exec("/dse/aliases/cursor-get-position",FALSE,FALSE);
	print "\ncursor pos= $vars[cbp_cursor_save__position]\n";
}
function cbp_cursor_restore(){
	global $vars; dse_trace();
    //print "\0337";
    if($vars[cbp_cursor_save__position]){
    	list($row,$col)=split(" ",$vars[cbp_cursor_save__position]);
		sbp_cursor_postion($row,$col);
		$vars[cbp_cursor_save__position]="";
    }
}*/
function cbp_cursor_save(){
	global $vars; dse_trace();
    print "\0337";
}
function cbp_cursor_restore(){
	global $vars; dse_trace();
    print "\0338";
}
function cbp_screen_clear(){
	global $vars; dse_trace();
    print "\033[2J";
}
function cbp_cursor_left($N=1){
	global $vars; dse_trace();
    print "\033[${N}D";
}
function cbp_cursor_up($N=1){
	global $vars; dse_trace();
    print "\033[${N}A";
}
function cbp_characters_clear($N=1){
	global $vars; dse_trace();
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
	global $vars; dse_trace();
	global $procIOs;
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
	global $vars; dse_trace();
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
	global $vars; dse_trace();
	return http_lynx_get($URL);
}
 
function http_lynx_get($URL){
	global $vars; dse_trace();
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
function http_headers($URL){
	global $vars; dse_trace();
	$URL=str_replace("\"","%34",$URL);
	$URL=str_replace("\n","",$URL);
	$command="/usr/bin/lynx -connect_timeout=10 -head -source \"$URL\"";
	return dse_exec($command);	
	
}
 

function dse_service_name_from_common_name($service){
	global $vars; dse_trace();
	if(@key_exists($service, $vars['DSE']['SERVICE_NICKNAMES'])){
		return $vars['DSE']['SERVICE_NICKNAMES'][$service];
	}else{
		print_r($vars['DSE']['SERVICE_NICKNAMES']); exit();
	}
	return $service;
}

function dse_service_action($service,$action){
	global $vars; dse_trace();
	$service=dse_service_name_from_common_name($service);
	$Command="service $service_common $action";
	$r=dse_exec($Command);
	return TRUE;
}
			
			
			
function dse_port_number($port_name){
	global $vars; dse_trace();
	foreach($vars['DSE']['SERVICE_PORTS'] as $Port=>$Name){
		if($Name==$port_name) return $Port;
	}
	return $port_name;
}
function dse_port_name($port_number){
	global $vars; dse_trace();
	foreach($vars['DSE']['SERVICE_PORTS'] as $Port=>$Name){
		if($Port==$port_number) return $Name;
	}
	return $port_number;
}


function dse_get_gateway(){
	global $vars; dse_trace();
	$Gateway="";
	if(dse_is_osx()){
		$r=dse_exec("netstat -nr | grep default");
		$ra=split("[ \t]+",$r);
		$Gateway=$ra[1];
	}else{
		$r=dse_exec("netstat -nr | grep 0.0.0.0");
		$ra=split("[ \t]+",$r);
		if($ra[0]=="0.0.0.0"){
			$Gateway=$ra[1];
		}
	}
	return $Gateway;
}

function dse_ports_open($Colorize=FALSE){
	global $vars; dse_trace(); dpv(6,"dse_ports_open($Colorize){");
	$tbr="";
	$r=dse_exec("/dse/bin/dnetstat -o");
	foreach(split(" ",$r) as $ep){
		list($exe,$p)=split(":",$ep);
		if(trim($exe) && trim($p)){
			if($tbr) $tbr.=" ";
			$PortName=dse_port_name($p);
			if($Colorize){
				if($exe!=$PortName){
					if(intval($PortName)>0){
						$tbr.= colorize($exe,"cyan","black").colorize(":","yellow","black").colorize($PortName,"green","black",TRUE,1);
					}else{
						$tbr.= colorize($PortName,"green","black",TRUE,1);
					}
				}else{
					$tbr.= colorize($PortName,"green","black",TRUE,1);
				}
			}else{
				$tbr.= "$exe:$PortName";
			}
		}
	}
	return $tbr;
}
function dse_ports_connected($Colorize=FALSE){
	global $vars; dse_trace(); dpv(6,"dse_ports_connected($Colorize){");
	$tbr="";
	$r=dse_exec("/dse/bin/dnetstat -c");
	dpv(6,"r=$r\n\n");
	foreach(split("\n",$r) as $port_ips){
		dpv(6,"\nforeach( this port_ips=$port_ips)");
		list($port,$ips)=split(":: ",$port_ips);
		dpv(6,"split -> port=$port ips=$ips");
		if(trim($port) && trim($ips)){
			if($tbr) $tbr.=" ";
			$PortName=dse_port_name($port);
			if($Colorize){
				/*if($exe!=$PortName){
					if(intval($PortName)>0){
						$tbr.= colorize($PortName,"cyan","black").colorize(": ","yellow","black").colorize($PortName,"green","black",TRUE,1);
					}else{
						$tbr.= colorize($PortName,"green","black",TRUE,1);
					}
				}else{*/
				$tbr.= colorize($PortName,"green","black",TRUE,1) ." ". colorize($ips,"grey","black",TRUE,1);
			}else{
				$tbr.= "$PortName: $ips";
			}
		}
		dpv(6,"tbr.= \"$PortName: $ips\";");
	}
	return $tbr;
}


if($vars['Verbosity']>5) print "dse_cli_functions.php: Done!\n";


/*
 * 
 //calander
#!/usr/bin/perl

use POSIX qw/strftime/;
use integer;

$date = strftime "%e",localtime;
$date =~ s/\s//;
@cal = `cal`;
$cal[0] = (strftime "%B %e %Y", localtime)."\n";
$cal[0] =~ s/\s\s/ /;
$il = " " x ((length($cal[1]) - length($cal[0]))/2);
$cal[0] =~ s/^/ $il/; 
print $cal[0];
for ($i = 1; $i <=6; $i++) {
        $cal[$i] =~ s/^(.*)\n$/ $1/;
        $cal[$i] =~ s/(^|\s)$date(\s|$)/\[$date\]/;
        print $cal[$i]."\n";
}
 
 */
 
?>
