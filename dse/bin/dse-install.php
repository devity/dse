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
		print "\n";
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

$PackageNamesArray=array("wget");
$OSXPackageNamesArray=array("lynx");
$NotOSXPackageNamesArray=array("perl","vim","memstat","iftop","sysstat","chkconfig","lynx-cur");
	
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
