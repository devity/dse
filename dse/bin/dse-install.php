#!/usr/bin/php
<?
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
include_once ("/dse/bin/dse_config_functions.php");

dse_require_root();
$vars['Verbosity']=1;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="DSE Install Script";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="install of files and setting up settings";
$vars['DSE']['CONFIGURE_VERSION']="v0.01a";
$vars['DSE']['CONFIGURE_VERSION_DATE']="2012/05/25";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

$parameters_details = array(
  array('h','help',"this message"),
);
$vars['parameters']=dse_cli_get_paramaters_array($parameters_details);
$vars['Usage']=dse_cli_get_usage($parameters_details);
$vars['argv_origional']=$argv;
dse_cli_script_start();
		
$BackupBeforeUpdate=TRUE;
foreach (array_keys($vars['options']) as $opt) switch ($opt) {
	case 'h':
  	case 'help':
  		$ShowUsage=TRUE;
		$DidSomething=TRUE;
		break;
}

dse_cli_script_header();

if($argv[1]=="help" || $ShowUsage){
	print $vars['Usage'];
}



// ********* main script activity START ************

print "Checking: sudo Logging: ";
$SUDOLOG="/var/log/sudolog";
$v=trim(`grep logfile= /etc/sudoers &>/dev/null`);
if($v==""){
  //`sudo echo "Defaults logfile=$SUDOLOG" | sudo tee -a /etc/sudoers &>/dev/null`;
  print getColoredString(" Set to: $SUDOLOG\n","green","black");
}else{
  print getColoredString(" Already set: $v\n","blue","black");
}
/*
echo -n "Reenabeling /var/log/messages: "
echo -e "$_TODO"
#sudo vi /etc/rsyslog.d/50-default.conf
#if [ $? -eq 0 ]; then
#   echo -e "$_OK"
#else
#   echo -e "$_FATAL"
#   exit -1;
#fi


echo -n "Restarting rsyslog: "
sudo service rsyslog restart
if [ $? -eq 0 ]; then
   echo -e "$_OK"
else
   echo -e "$_FATAL"
   exit -1;
fi
*/




$PWD=trim(`pwd`);
$DefaultInstallDirectory=$vars['DSE']['DSE_ROOT'];
$dse_git_dir="$PWD/dse/dse";
{
	
	if(file_exists($DefaultInstallDirectory)){
	/*	//only ask if QuestionLevel=ALL|FULL|HIGH|10
	 	print "DSE already installed at $DefaultInstallDirectory    Reinstall? ";
		$key=strtoupper(dse_get_key());
		cbp_characters_clear(1);
		if($key=="Y"){
			if(!file_exists($dse_git_dir)){
				print getColoredString("\nNo DSE git repo found at $dse_git_dir\n","red","black");
				print "Would you like to [E]nter a path, auto-[S]earch for it, or [Q]uit? ";
				$key=strtoupper(dse_get_key());
				cbp_characters_clear(1);
				if($key=="Q"){
					script_exit_fatal();
				}elseif($key=="E"){
					print "not supported\n";
					script_exit_fatal();
				}elseif($key=="S"){
					print "not supported\n";
					script_exit_fatal();
				}else{
					print " unknown key: $key ";
				}
			}else{
				print " Reinstalling! ";
				dse_file_delete($DefaultInstallDirectory);
				dse_file_link($DefaultInstallDirectory,$dse_git_dir);
			}
		}elseif($key=="N"){
			print " Not Reinstalling. ";
		}else{
			print " unknown key: $key ";
		}
		print "\n";*/
	}else{
		print "DSE not installed at $DefaultInstallDirectory    Install? ";
		$key=strtoupper(dse_get_key());
		cbp_characters_clear(1);
		if($key=="Y"){
			if(!file_exists($dse_git_dir)){
				print getColoredString("\nNo DSE git repo found at $dse_git_dir\n","red","black");
				print "Would you like to [E]nter a path, auto-[S]earch for it, or [Q]uit? ";
				$key=strtoupper(dse_get_key());
				cbp_characters_clear(1);
				if($key=="Q"){
					script_exit_fatal();
				}elseif($key=="E"){
					print "not supported\n";
					script_exit_fatal();
				}elseif($key=="S"){
					print "not supported\n";
					script_exit_fatal();
				}else{
					print " unknown key: $key ";
				}
			}else{
				print " Installing! ";
				dse_file_link($DefaultInstallDirectory,$dse_git_dir);
			}
		}elseif($key=="N"){
			print " Not installing. ";
		}else{
			print " unknown key: $key ";
		}
		print "\n";
	}
} 



//"lynx-cur","postfix","cron-apt","perl-tk","xosview","dselect","openvpn",""
//"memstat","iftop","sysstat","chkconfig","mytop",""
//"dnsutils","bind9","vsftpd","tasksel",""

$PackageNamesArray=array();
$OSXPackageNamesArray=array();
$NotOSXPackageNamesArray=array();

foreach($vars['DSE']['DisabledComponents'] as $ComponentName){
	$Component=colorize($ComponentName,"cyan");
	print "Component $Component ".colorize("Disabled - no install","red")."\n";
}
foreach($vars['DSE']['AddComponents'] as $ComponentName){
	$Component=colorize($ComponentName,"cyan");
	print "Component $Component ".colorize("Enabled - marked for install","green")."\n";
}
//foreach($vars['DSE']['ComponentsAvailable'] as $ComponentName){
//	$Component=colorize($ComponentName,"cyan");
	//print "$Component ".colorize("Available - no choice yet","yellow")."\n";
//}


$ComponentName="image-processing";
if(!in_array($ComponentName, $vars['DSE']['DisabledComponents'])){
	if(!in_array($ComponentName, $vars['DSE']['AddComponents'])){
		$Component=colorize($ComponentName,"cyan");
		$A=dse_ask_yn("Install Component $Component?");
		print "\n";
		if($A=='Y'){
			$vars['DSE']['AddComponents'][]=$ComponentName;
			dse_replace_in_file($vars['DSE']['DSE_CONFIG_FILE_GLOBAL'],"# ComponentsAvailable[]=$ComponentName","AddComponents[]=$ComponentName");
		}else{
			dse_replace_in_file($vars['DSE']['DSE_CONFIG_FILE_GLOBAL'],"# ComponentsAvailable[]=$ComponentName","DisabledComponents[]=$ComponentName");
		}
	}
	if(in_array($ComponentName, $vars['DSE']['AddComponents'])){
		$OSXPackageNamesArray[]="imagemagick";
		$NotOSXPackageNamesArray[]="imagemagick";
		$NotOSXPackageNamesArray[]="libmagickcore-dev";
	}
}

$ComponentName="desktop";
if(!in_array($ComponentName, $vars['DSE']['DisabledComponents'])){
	if(!in_array($ComponentName, $vars['DSE']['AddComponents'])){
		$Component=colorize($ComponentName,"cyan");
		$A=dse_ask_yn("Install Component $Component?");
		print "\n";
		if($A=='Y'){
			$vars['DSE']['AddComponents'][]=$ComponentName;
			dse_replace_in_file($vars['DSE']['DSE_CONFIG_FILE_GLOBAL'],"# ComponentsAvailable[]=$ComponentName","AddComponents[]=$ComponentName");
		}else{
			dse_replace_in_file($vars['DSE']['DSE_CONFIG_FILE_GLOBAL'],"# ComponentsAvailable[]=$ComponentName","DisabledComponents[]=$ComponentName");
		}
	}
	if(in_array("desktop", $vars['DSE']['AddComponents'])){
		$NotOSXPackageNamesArray[]="xubuntu-desktop";
	}
}


$ComponentName="tor";
if(!in_array($ComponentName, $vars['DSE']['DisabledComponents'])){
	if(!in_array($ComponentName, $vars['DSE']['AddComponents'])){
		$Component=colorize($ComponentName,"cyan");
		$A=dse_ask_yn("Install Component $Component?");
		print "\n";
		if($A=='Y'){
			$vars['DSE']['AddComponents'][]=$ComponentName;
			
			$release=dse_ubuntu_release();
			switch($release){
				case 'karmic':
				case 'maverick':
				case 'lucid':
					dse_file_add_line_if_not($vars['DSE']['SYSTEM_APT_SOURCES_LIST'],"deb http://deb.torproject.org/torproject.org $release main");
					dse_file_add_line_if_not($vars['DSE']['SYSTEM_APT_SOURCES_LIST'],"deb-src http://deb.torproject.org/torproject.org $release main");
					
					passthru("gpg --keyserver keys.gnupg.net --recv 886DDD89");
					passthru("gpg --export A3C4F0F979CAA22CDBA8F512EE8CBC9E886DDD89 | sudo apt-key add -");
					dse_apt_uu();
					
					break;
				case 'natty':
					dse_file_add_line_if_not($vars['DSE']['SYSTEM_APT_SOURCES_LIST'],"deb http://deb.torproject.org/torproject.org $release main");
					break;
				default:
					print colorize("Unknown Linux Release. Can't setup torproject.org rpm repository.\n","white","red");
					break;
			}
			
			dse_replace_in_file($vars['DSE']['DSE_CONFIG_FILE_GLOBAL'],"# ComponentsAvailable[]=$ComponentName","AddComponents[]=$ComponentName");
		}else{
			dse_replace_in_file($vars['DSE']['DSE_CONFIG_FILE_GLOBAL'],"# ComponentsAvailable[]=$ComponentName","DisabledComponents[]=$ComponentName");
		}
	}
	if(in_array("desktop", $vars['DSE']['AddComponents'])){
		$NotOSXPackageNamesArray[]="tor";
		$NotOSXPackageNamesArray[]="tor-geoipdb";
		$NotOSXPackageNamesArray[]="vidalia";
		$NotOSXPackageNamesArray[]="polipo";
		
	}
}



$NotOSXPackageNamesArray[]="vim";
$NotOSXPackageNamesArray[]="perl";
$PackageNamesArray[]="wget";
$PackageNamesArray[]="curl";
$OSXPackageNamesArray[]="lynx";
$NotOSXPackageNamesArray[]="lynx-cur";
$PackageNamesArray[]="bc";
$NotOSXPackageNamesArray[]="memstat";
$NotOSXPackageNamesArray[]="iftop";
$NotOSXPackageNamesArray[]="sysstat";
$NotOSXPackageNamesArray[]="chkconfig";

if(dse_is_centos()){
	$PackageNamesArray[]="jwhois";
}elseif(dse_is_osx()){
}else{
	$PackageNamesArray[]="whois";
}

if(dse_is_ubuntu()){
	$PackageNamesArray[]="dnet-progs";
}
	
if(dse_is_osx()){
	foreach($OSXPackageNamesArray as $p) $PackageNamesArray[]=$p;
}else{
	foreach($NotOSXPackageNamesArray as $p) $PackageNamesArray[]=$p;
}
	
	
foreach($PackageNamesArray as $PackageName){
	$r=dse_package_install($PackageName);
	if($r<0){
		print getColoredString("FATAL ERROR: installing package $PackageName\n","red","black");
		print getColoredString($vars['DSE']['SCRIPT_FILENAME']."Exiting.\n","red","black");
		exit(-1);
	}
}





// ********* main script activity END ************

print getColoredString($vars['DSE']['SCRIPT_FILENAME']." Done!\n","green","black","black","black");

if($DidSomething){
	$vars[shell_colors_reset_foreground]='';	print getColoredString("","white","black");
	exit(0);
}

//if($argv[1]=="help"){
//	print $argv[1];
	
	exit(0);
//}



function script_exit_fatal(){
	global $vars;
	print getColoredString($vars['DSE']['SCRIPT_FILENAME']." Fatal Error. Exiting!\n","red","black");
	$vars[shell_colors_reset_foreground]='';	print getColoredString("","white","black");
	exit(0);
}
	 

?>
