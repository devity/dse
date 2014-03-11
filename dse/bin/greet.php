#!/usr/bin/php
<?
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/include/system_stat_functions.php");
include_once ("/dse/include/cli_fonts.php");
include_once ("/dse/bin/dse_config.php");

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="Greet";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="Login greeting w/ system stats";
$vars['DSE']['SCRIPT_VERSION']="v0.01b";
$vars['DSE']['SCRIPT_VERSION_DATE']="2014/02/10";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
$vars['DSE']['SCRIPT_COMMAND_FORMAT']="";
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

$vars['DSE']['SCRIPT_SETTINGS']['Verbosity']=0;
$vars['Verbosity']=$vars['DSE']['SCRIPT_SETTINGS']['Verbosity'];

$parameters_details = array(
  array('h','help',"this message"),
);
$vars['parameters']=dse_cli_get_paramaters_array($parameters_details);
$vars['Usage']=dse_cli_get_usage($parameters_details);
$vars['argv_origional']=$argv;
dse_cli_script_start();

foreach (array_keys($vars['options']) as $opt) switch ($opt) {	
	case 'h':
  	case 'help':
  		dse_cli_script_header();
  		print $vars['Usage'];
		exit;
}


$Hostname=str_replace("\n", "", `hostname` );
print "\n\033[1;33m";
dse_font_2_cli($Hostname);
print "\033[0;37m\nGathering System Stats...\n";

$ENDSESSION="??";
$PROCCOUNT=str_replace("\n", "", `ps -l | wc -l` ); $PROCCOUNT=str_replace("\n", "", `expr $PROCCOUNT - 4` );
$ProcsTotal=str_replace("\n", "", `ps aux | wc -l` ); $ProcsTotal=str_replace("\n", "", `expr $ProcsTotal - 4` );
$Kernel=str_replace("\n", "", `uname -r` );
$IPs=str_replace("\n", "", `ips` );
$Groups=str_replace("\n", "", `groups` );

$Who=str_replace("\n", "", `whoami` );
$Ports=str_replace("\n","",`ports`);
$SSH_IPs=str_replace("\n","",`dnetstat -c 22`);
$TotalMemory=str_replace("\n", "", `cat /proc/meminfo | grep MemTotal | awk {'print $2'}` );
$FreeMemory=str_replace("\n", "", `cat /proc/meminfo | grep MemFree | awk {'print $2'}` );
$CachedMemory=str_replace("\n", "", `cat /proc/meminfo | grep Cached | awk {'print $2'}` );
$MemUsedPercent=number_format(( ($TotalMemory-($FreeMemory+$CachedMemory))/$TotalMemory )*100,2);
$TotalMemory=intval($TotalMemory/1000);
$FreeMemory=intval($FreeMemory/1000);

//$ProcLimit=str_replace("\n", "", `ulimit -u` );
$UserSessions=str_replace("\n", "", `who | grep $Who | wc -l` );
list($UpSeconds,$IdleSeconds)=explode(" ",str_replace("\n", "", `cat /proc/uptime` ));
$LoadAvg=str_replace("\n", "", `cat /proc/loadavg` );

if($UpSeconds>60*60*24){
	$Uptime=intval($UpSeconds/(60*60*24))." days";
}elseif($UpSeconds>60*60*4){
	$Uptime=intval($UpSeconds/(60*60))." hours";
}elseif($UpSeconds>60*4){
	$Uptime=intval($UpSeconds/(60))." minutes";
}else{
	$Uptime=$UpSeconds." seconds";
}



$Msg=" 
\033[0;35m+++++++++++++++++: \033[0;37mSystem Data\033[0;35m :+++++++++++++++++++
+  \033[0;37mHostname \033[0;35m= \033[1;32m$Hostname
\033[0;35m+    \033[0;37mAddress \033[0;35m= \033[1;32m$IPs
\033[0;35m+     \033[0;37mKernel \033[0;35m= \033[1;32m$Kernel
\033[0;35m+     \033[0;37mUptime \033[0;35m= \033[1;32m$Uptime
\033[0;35m+        \033[0;37mCPU \033[0;35m= \033[1;32m$LoadAvg
\033[0;35m+     \033[0;37mMemory \033[0;35m= \033[1;32m$FreeMemory free / $TotalMemory total MB  - $MemUsedPercent% used
\033[0;35m+  \033[0;37mProcesses \033[0;35m= \033[1;32m$ProcsTotal
\033[0;35m++++++++++++++++++: \033[0;37mUser Data\033[0;35m :++++++++++++++++++++
\033[0;35m+    \033[0;37mSSH IPs \033[0;35m= \033[1;32m$SSH_IPs
\033[0;35m+      \033[0;37mPorts \033[0;35m= \033[1;32m$Ports
\033[0;35m++++++++++++++++++: \033[0;37mUser Data\033[0;35m :++++++++++++++++++++
+  \033[0;37mUser:Group \033[0;35m= \033[1;32m$Who \033[0;35m: \033[1;32m$Groups
\033[0;35m+   \033[0;37mSessions \033[0;35m= \033[1;32m$UserSessions of $ENDSESSION MAX
\033[0;35m+++++++++++++++++++++++++++++++++++++++++++++++++++\033[0;37m
";
//\033[0;35m+  \033[0;37mProcesses \033[0;35m= \033[1;32m$PROCCOUNT of $ProcLimit MAX
print $Msg;




?>