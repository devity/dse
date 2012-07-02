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
$vars['DSE']['SCRIPT_NAME']="DSE Configure Script";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="setup of config files and settings";
$vars['DSE']['CONFIGURE_VERSION']="v0.03a";
$vars['DSE']['CONFIGURE_VERSION_DATE']="2012/05/25";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

$parameters_details = array(
  array('h','help',"this message"),
  array('v','listvars',"list configuration variables"),
  array('f','full',"full setup / configuration"),
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
	case 'f':
  	case 'full':
  		$FullConfig=TRUE;
		break;
}

dse_cli_script_header();

if($argv[1]=="help" || $ShowUsage){
	print $vars['Usage'];
}

// ********* main script activity START ************



dse_file_link("/sbin/service",trim(`which service`));
dse_file_link("/usr/bin/php",trim(`which php`));
dse_file_link("/bin/php",trim(`which php`));

$wget=dse_which("wget");
//print "wget=$wget\n";
if($wget){
	dse_file_link("/usr/bin/wget",$wget);
}else{
	print getColoredString("ERROR: wget not installed.\n","red","black");
}

if($vars['DSE']['HOSTNAME']){
	print pad("Setting hostname to: ".$vars['DSE']['HOSTNAME']."  ","90%",colorize("-","blue"))."\n";
	print bar("Server REBOOT required for effect!","-","blue","white","white","red")."n";
	$vars['DSE']['REBOOT_REQUIRED']=TURE;
	dse_server_set_hostname($vars['DSE']['HOSTNAME']);
}

print pad("Creating Needed Directories:   ","90%",colorize("-","blue"))."\n";

$NeededDirs=array(
 array($vars['DSE']['DSE_BACKUP_DIR'],"777",$vars['DSE']['SYSTEM_ROOT_FILE_USER:GROUP']),
 array($vars['DSE']['DSE_BACKUP_DIR']."/installs","777",$vars['DSE']['SYSTEM_ROOT_FILE_USER:GROUP']),
 array($vars['DSE']['DSE_BACKUP_DIR']."/dse","777",$vars['DSE']['SYSTEM_ROOT_FILE_USER:GROUP']),
 array($vars['DSE']['DSE_BACKUP_DIR']."/rpms","777",$vars['DSE']['SYSTEM_ROOT_FILE_USER:GROUP']),
 array($vars['DSE']['DSE_BACKUP_DIR']."/clone","777",$vars['DSE']['SYSTEM_ROOT_FILE_USER:GROUP']),
 array($vars['DSE']['DSE_BACKUP_DIR']."/server_environment","777",$vars['DSE']['SYSTEM_ROOT_FILE_USER:GROUP']),
 array($vars['DSE']['DSE_BACKUP_DIR']."/changed_files","777",$vars['DSE']['SYSTEM_ROOT_FILE_USER:GROUP']),
 array($vars['DSE']['DSE_VIBK_BACKUP_DIRECTORY'],"777",$vars['DSE']['SYSTEM_ROOT_FILE_USER:GROUP']),
 array($vars['DSE']['DSE_LOG_DIR'],"777",$vars['DSE']['SYSTEM_ROOT_FILE_USER:GROUP']),
 array($vars['DSE']['DSE_LOG_DIR']."/ip_throttle","777",$vars['DSE']['SYSTEM_ROOT_FILE_USER:GROUP']),
 array($vars['DSE']['DSE_LOG_DIR']."/dwi_apache2","777",$vars['DSE']['SYSTEM_ROOT_FILE_USER:GROUP']),
 array($vars['DSE']['DSE_CONFIG_DIR'],"777",$vars['DSE']['SYSTEM_ROOT_FILE_USER:GROUP']),
 array($vars['DSE']['SYSTEM_SCRIPTS_DIR'],"777",$vars['DSE']['SYSTEM_ROOT_FILE_USER:GROUP']),
);
if(str_contains($vars['DSE']['SERVICES'],"dns")){
	$NeededDirs[]= array("/etc/bind/local","777",$vars['DSE']['SYSTEM_ROOT_FILE_USER:GROUP']);
}


foreach($NeededDirs as $DirArray){
	if($DirArray[0]){
		$Dir=$DirArray[0];
		$Mode=$DirArray[1];
		$Owner=$DirArray[2];
		print "DSE Directory $Dir: ";
		
		if(!is_dir($Dir)){
			print "$Missing. Create? ";
			$A=dse_ask_yn();
			if($A=='Y'){
				dse_directory_create($Dir,$Mode,$Owner);
				if(is_dir($Dir)){
					print $OK;	
				}else{
					print $Failed;	
				}
			}
		}else{
			print "Exists. ";
			if($Mode!=dse_file_get_mode($Dir) && "2".$Mode!=dse_file_get_mode($Dir) ){
				print "Mode $NotOK =".dse_file_get_mode($Dir)." ";
				$A=dse_ask_yn("Set to $Mode?");
				if($A=='Y'){
					if(dse_file_set_mode($Dir,$Mode)==0){
						print "$Fixed. ";
					}else{
						print "$Failed. ";
					}
				}else{
					print "$NotFixed";
				}
			}
			if($Owner!=dse_file_get_owner($Dir)){
				print "Owner $NotOK =".dse_file_get_owner($Dir)."  ";
				$A=dse_ask_yn("Set to $Owner?");
				if($A=='Y'){
					
					if(dse_file_set_owner($Dir,$Owner)==0){
						print "$Fixed. ";
					}else{
						print "$Failed. ";
					}
				}else{
					print "$NotFixed";
				}
			}
			if($Mode==dse_file_get_mode($Dir) && $Owner==dse_file_get_owner($Dir)){
				print "$OK";
			}
		}
		print "\n";
	}
}




print pad("Installing cfg files from Templates: ".colorize($PackageName,"cyan")."...   ","90%",colorize("-","blue"))."\n";
	
$DSE_Git_pull_script="/scripts/dse_git_pull";
$TemplateFile=$vars['DSE']['DSE_TEMPLATES_DIR'] . "/scripts/" . "dse_git_pull";
dse_configure_file_install_from_template($DSE_Git_pull_script,$TemplateFile,"4775","root:root");

$TemplateFile=$vars['DSE']['DSE_TEMPLATES_DIR'] . "/etc/dse/" . "dse.conf";
dse_configure_file_install_from_template($vars['DSE']['DSE_CONFIG_FILE_GLOBAL'],$TemplateFile,"664","root:root");

$TemplateFile=$vars['DSE']['DSE_TEMPLATES_DIR'] . "/etc/dse/" . "ips_whitelist.txt";
dse_configure_file_install_from_template($vars['DSE']['DSE_IPTHROTTLE_WHITELIST_FILE'],$TemplateFile,"664","root:root");

$TemplateFile=$vars['DSE']['DSE_TEMPLATES_DIR'] . "/etc/dse/" . "ips_droplist.txt";
dse_configure_file_install_from_template($vars['DSE']['DSE_IPTHROTTLE_DROPLIST_FILE'],$TemplateFile,"664","root:root");


if(str_contains($vars['DSE']['SERVICES'],"dwi") && dse_is_package_installed("apache2") ){
	if(!dse_file_exists($vars['DSE']['DSE_WEB_INTERFACE_APACHE2_FILE'])){
		
 		
		dse_file_link("/usr/mime.types",dse_fss("mime.types"));
		print "No ".$vars['DSE']['DSE_WEB_INTERFACE_APACHE2_FILE']."   using template.\n";
		$t=dse_fss("mod_headers.so");
		print "t=$t =dse_fss(\"mod_headers.so\")\n";
		$Apache2ModuleDirectory=dirname($t);
		$Apache2ModuleDirectory=str_remove($Apache2ModuleDirectory,"/mod_headers.so");
		$TemplateFile=$vars['DSE']['DSE_TEMPLATES_DIR'] . "/etc/dse/" . "apache2.conf";
		dse_configure_file_install_from_template($vars['DSE']['DSE_WEB_INTERFACE_APACHE2_FILE'],$TemplateFile,"664","root:root");
		if($Apache2ModuleDirectory){
			print  "found Apache2ModuleDirectory=$Apache2ModuleDirectory   replacing.\n";
			dse_file_replace_str($vars['DSE']['DSE_WEB_INTERFACE_APACHE2_FILE'],"libexec/apache2",$Apache2ModuleDirectory);
		}
	}
}


dse_file_set_mode($vars['DSE']['DSE_IPTHROTTLE_LOG_DIRECTORY'],"777");


dse_file_set_mode("/var/log","777");
dse_file_set_mode("/var","777");


if(dse_is_osx()){
	dse_file_set_owner($vars['DSE']['DSE_BIN_DIR']."/dnetstat.php","root:wheel");
}else{
	dse_file_set_owner($vars['DSE']['DSE_BIN_DIR']."/dnetstat.php","root:root");
}
dse_file_set_mode($vars['DSE']['DSE_BIN_DIR']."/dnetstat.php","4755");


print bar("Enabeling yum/rpm restore points: ","-","blue","white","green","white")."n";
dse_file_add_line_if_not($vars['DSE']['SYSTEM_YUM_CONF_FILE'],"tsflags=repackage");
dse_file_add_line_if_not($vars['DSE']['SYSTEM_RPM_MACROS_FILE'],"%_repackage_all_erasures 1");

//sudo logging
//echo -n "sudo Logging: "
//if ! grep logfile= /etc/sudoers &>/dev/null ; then
//  sudo echo "Defaults logfile=$SUDOLOG" | sudo tee -a /etc/sudoers &>/dev/null
//larger bash history
print "Checking current \$PATH: \n";
$PATH=getenv("PATH");
if(!str_contains($PATH,$vars['DSE']['DSE_BIN_DIR'])){
	print " Cant find ".$vars['DSE']['DSE_BIN_DIR']." in PATH: $PATH\n";

	
	
	print "Checking system profile PATH: ";
	if(!dse_file_exists($vars['DSE']['SYSTEM_PROFILE_FILE'])){
		print "$Failed to verify. No ".$vars['DSE']['SYSTEM_PROFILE_FILE']."\n";
	}else{
		$Command="grep \"PATH=\" ".$vars['DSE']['SYSTEM_PROFILE_FILE'];
		$PATH=strcut(trim(`$Command`),"=");
		if(!str_contains($PATH,$vars['DSE']['DSE_BIN_DIR'])){
			print "Cant find ".$vars['DSE']['DSE_BIN_DIR']." in PATH: $PATH\n";
			$A=dse_ask_yn(" Update to PATH?");
			if($A=='Y'){
				//$Command="echo \"PATH=\$PATH:".$vars['DSE']['DSE_BIN_DIR'].":".$vars['DSE']['DSE_ALIASES_DIR'].":"
				//	.$vars['DSE']['SYSTEM_SCRIPTS_DIR']."\nexport PATH\" >> ".$vars['DSE']['SYSTEM_PROFILE_FILE'];
				$Command="/dse/bin/dbp --line-append ".$vars['DSE']['SYSTEM_PROFILE_FILE']." \"PATH=\$PATH:".$vars['DSE']['DSE_BIN_DIR'].":".$vars['DSE']['DSE_ALIASES_DIR'].":"
					.$vars['DSE']['SYSTEM_SCRIPTS_DIR']."\nexport PATH\"";
				$r=`$Command`;
				print "$Updated\n";
			}else{
				print "$NotChanged\n";
			}
				
		}else{
			print "$OK Path= $PATH\n";
			
			
			print "Checking user's .profile PATH: ";
			if(!dse_file_exists($vars['DSE']['USER_BASH_PROFILE'])){
				print "$Failed to verify. No ".$vars['DSE']['USER_BASH_PROFILE']."\n";
			}else{
				$Command="grep \"PATH=\" ".$vars['DSE']['USER_BASH_PROFILE'];
				$PATH=strcut(trim(`$Command`),"=");
				if(!str_contains($PATH,$vars['DSE']['DSE_BIN_DIR'])){
					print "Cant find ".$vars['DSE']['DSE_BIN_DIR']." in PATH: $PATH\n";
					$A=dse_ask_yn(" Update to PATH?");
					if($A=='Y'){
						//$Command="echo \"PATH=\$PATH:".$vars['DSE']['DSE_BIN_DIR'].":".$vars['DSE']['DSE_ALIASES_DIR'].":".$vars['DSE']['SYSTEM_SCRIPTS_DIR']
						//  ."\nexport PATH\" >> ".$vars['DSE']['USER_BASH_PROFILE'];
						$Command="/dse/bin/dbp --line-append ".$vars['DSE']['USER_BASH_PROFILE']." \"PATH=\$PATH:".$vars['DSE']['DSE_BIN_DIR'].":".$vars['DSE']['DSE_ALIASES_DIR'].":"
							.$vars['DSE']['SYSTEM_SCRIPTS_DIR']."\nexport PATH\"";
						$r=`$Command`;
						print "$Updated\n";
					}else{
						print "$NotChanged\n";
					}
						
				}else{
					print "$OK Path= $PATH\n";
				}
			}
	
			
			
		}
	}
	
	
}else{
	print "$OK = $PATH\n";
}

dse_file_add_line_if_not($vars['DSE']['SYSTEM_YUM_CONF_FILE'],"tsflags=repackage");
dse_file_add_line_if_not($vars['DSE']['SYSTEM_RPM_UP2DATE_FILE'],"%_repackage_all_erasures 1");

 
//larger bash history
print "Checking HISTFILESIZE: \n";
if(!dse_file_exists($vars['DSE']['USER_BASH_PROFILE'])){
	print "$Failed to verify. No ".$vars['DSE']['USER_BASH_PROFILE']."\n";
}else{
	$Command="grep HISTFILESIZE ".$vars['DSE']['USER_BASH_PROFILE'];
	$HISTFILESIZE=strcut(trim(`$Command`),"=");
	if($HISTFILESIZE==""){
		print "Cant find HISTFILESIZE in ".$vars['DSE']['USER_BASH_PROFILE']."\n";
		$A=dse_ask_yn(" Add HISTFILESIZE=".$vars['DSE']['SUGGESTED']['HISTFILESIZE']." ?");
		if($A=='Y'){
			$Command="echo \"\nHISTFILESIZE=".$vars['DSE']['SUGGESTED']['HISTFILESIZE']."\" >> ".$vars['DSE']['USER_BASH_PROFILE'];
			$r=`$Command`;
			print "$Added\n";
		}else{
			print "$NotChanged\n";
		}
			
	}else{
		if($HISTFILESIZE<$vars['DSE']['SUGGESTED']['HISTFILESIZE']){
			print "HISTFILESIZE $NotOK. Smaller ( = $HISTFILESIZE ) than recommended ( ".$vars['DSE']['SUGGESTED']['HISTFILESIZE']." ). \n";
			$A=dse_ask_yn(" Increase HISTFILESIZE to ".$vars['DSE']['SUGGESTED']['HISTFILESIZE']." ?");
			if($A=='Y'){
				$Command="/dse/bin/dreplace -v 2 -s -p ".$vars['DSE']['USER_BASH_PROFILE']." \"^HISTFILESIZE=[0-9]+$\" \"HISTFILESIZE=".$vars['DSE']['SUGGESTED']['HISTFILESIZE']."\"";
				$r=`$Command`;
				print "$OK\n";
			}else{
				print "$NotChanged\n";
			}
		}else{
			print "HISTFILESIZE size $OK = $HISTFILESIZE\n";
		}
	}
}

//multi-terminal real-time bash history
$code="
#start http://stackoverflow.com/questions/103944/real-time-history-export-amongst-bash-terminal-windows

	export HISTSIZE=".$vars['DSE']['SUGGESTED']['HISTFILESIZE']."
	history() {
	  _bash_history_sync
	  builtin history \"$@\"
	}
	
	_bash_history_sync() {
	  builtin history -a        
	  HISTFILESIZE=$HISTSIZE     
	  builtin history -c         
	  builtin history -r         
	}
	
	PROMPT_COMMAND=_bash_history_sync
	
#end http://stackoverflow.com/questions/103944/real-time-history-export-amongst-bash-terminal-windows
	
";
	
$Command="grep \"stackoverflow.com/questions/103944/real-time-history-export-amongst-bash-terminal-windows\" ".$vars['DSE']['SYSTEM_BASHRC_FILE'];
$r=trim(`$Command`);
//print "$r\n";
print "Realtime cross-shell bash history: ";

if(!str_contains($r,"stackoverflow")){
	print "Not activated in ".$vars['DSE']['SYSTEM_BASHRC_FILE'];
	$A=dse_ask_yn(" Add?");
	if($A=='Y'){
		$Command="echo \"\n$code\" >> ".$vars['DSE']['SYSTEM_BASHRC_FILE'];
		$r=`$Command`;
		print " $OK $Added\n";
	}else{
		print " $NotChanged\n";
	}
}else{
	print "$OK\n";
}

//PATH
/*echo -n "Putting dse Scripts in Path: "
if ! sudo grep /dse/scripts /etc/environment &>/dev/null ; then
   #sudo echo -e "export PATH=\$PATH:/dse/scripts" | sudo tee -a /etc/environment > /dev/null
   CURRENT_PATH=`sudo grep PATH /etc/environment 2>/dev/null | sed -e 's/"$//'`
   NEW_PATH="$CURRENT_PATH:/dse/scripts"
   dse_replace_lines_grep_matched /etc/environment PATH= $NEW_PATH*/


 
 /*echo -n "Disabling telnet: "
sudo /usr/sbin/update-inetd --disable telnet*/

/*echo -n "Verifying kernel is a package: "
sudo dpkg -S `readlink -f /vmlinuz` &>/dev/null
if [ $? -eq 0 ]; then
   echo -e "$_OK"
else
   echo -e "$_FATAL"
   exit -1;
fi*/

if(dse_is_ubuntu()){
	if(in_array("desktop", $vars['DSE']['AddComponents'])
	 && dse_is_package_installed("xorg") ){
		$DesktopPowerPolicyFile="/usr/share/polkit-1/actions/org.freedesktop.upower.policy";
		if(!dse_file_exists($DesktopPowerPolicyFile)){
			$DesktopPowerPolicyFile=dse_fss("org.freedesktop.upower.policy");
		}
		if(dse_file_exists($DesktopPowerPolicyFile)){
			dse_file_replace_str($DesktopPowerPolicyFile,"<allow_active>yes</allow_active>","<allow_active>no</allow_active>");
		}
		
		dse_exec("sudo gsettings set org.gnome.settings-daemon.plugins.power active false");
	}
}

if(dse_file_exists($vars['DSE']['SYSTEM_PHP_CLI_INI_FILE'])){
	$display_errors=dse_get_cfg_file_value($vars['DSE']['SYSTEM_PHP_CLI_INI_FILE'],"display_errors");
	$display_startup_errors=dse_get_cfg_file_value($vars['DSE']['SYSTEM_PHP_CLI_INI_FILE'],"display_startup_errors");
	$log_errors=dse_get_cfg_file_value($vars['DSE']['SYSTEM_PHP_CLI_INI_FILE'],"log_errors");
	$error_reporting=dse_get_cfg_file_value($vars['DSE']['SYSTEM_PHP_CLI_INI_FILE'],"error_reporting");
	print "PHP error display/logging: ";
	if( $display_errors!="On" || $display_startup_errors!="On" || $log_errors!="On" || $error_reporting!="(E_ALL & ~E_NOTICE) ^ E_DEPRECATED" ){
		print "Not dse optimal for debugging. $NotOK.\n";
		$A=dse_ask_yn(" Fix?");
		if($A=='Y'){
			$Command="/dse/bin/dreplace -s -p ".$vars['DSE']['SYSTEM_PHP_CLI_INI_FILE']." \"^display_errors.*$\" \"display_errors = On\"";
			$r=`$Command | grep display_errors`;
			$Command="/dse/bin/dreplace -s -p ".$vars['DSE']['SYSTEM_PHP_CLI_INI_FILE']." \"^display_startup_errors.*$\" \"display_startup_errors = On\"";
			$r=`$Command | grep display_errors`;
			$Command="/dse/bin/dreplace -s -p ".$vars['DSE']['SYSTEM_PHP_CLI_INI_FILE']." \"^log_errors.*$\" \"log_errors = On\"";
			$r=`$Command | grep display_errors`;
			$Command="/dse/bin/dreplace -s -p ".$vars['DSE']['SYSTEM_PHP_CLI_INI_FILE']." \"^error_reporting.*$\" \"error_reporting = (E_ALL & ~E_NOTICE) ^ E_DEPRECATED\"";
			$r=`$Command | grep display_errors`;
			//print $r;
			print "$OK\n";
		}else{
			print "$NotChanged\n";
		}
		print "$OK\n";
	}
}

print "Creating dwi init.d script.\n";
$INITD_SCRIPT_ARRAY=array();
$INITD_SCRIPT_ARRAY['ServiceName']="dwi";
$INITD_SCRIPT_ARRAY['ActionStart']="sudo apachectl -f /etc/dse/apache2.conf";
$INITD_SCRIPT_ARRAY['ActionStop']="sudo sudo kill `grep2pid httpd`";
$INITD_SCRIPT_ARRAY['VarIsRunning']="grep2pid httpd";
$INITD_SCRIPT_ARRAY['VarStatus']="ps aux | egrep httpd";
if(dse_is_osx()){
	$INITD_SCRIPT_ARRAY['VarNetstat']="netstat -ta | egrep 7907";
}else{
	$INITD_SCRIPT_ARRAY['VarNetstat']="netstat -tap | egrep 7907";
}
dse_write_daemon_script($INITD_SCRIPT_ARRAY);		


if($FullConfig){
	if(str_contains($vars['DSE']['SERVICES'],"http") && str_contains($vars['DSE']['SERVICES'],"mysql")){
		$vars['DSE']['LAMP_SERVER']=TRUE;
	}

	dse_server_configure_file_load();
	print "Services to be Setup/Configured: ".$vars['DSE']['SERVICES']."\n";
	
	if(str_contains($vars['DSE']['SERVICES'],"dns")) dse_configure_create_named_conf();
	if(str_contains($vars['DSE']['SERVICES'],"http")) dse_configure_create_httpd_conf();
	
	if(str_contains($vars['DSE']['SERVICES'],"desktop")){
		print "Installing 'desktop' packages:\n";
		$vars['DSE']['DESKTOP']=TRUE;
		
		$PackageNamesArray=array("gnome");
		if(dse_is_ubuntu()){
			$PackageNamesArray[]="ubuntu-desktop";
		}
		foreach($PackageNamesArray as $PackageName){
			$r=dse_package_install($PackageName);
			if($r<0){
				print getColoredString("FATAL ERROR: installing package $PackageName\n","red","black");
				print getColoredString($vars['DSE']['SCRIPT_FILENAME']."Exiting.\n","red","black");
				exit(-1);
			}
		}
	}
		
		
		
	
	if(str_contains($vars['DSE']['SERVICES'],"vncserver")){
		print "Creating vncserver init.d script.\n";
		$StartFileName=$vars['DSE']['SYSTEM_SCRIPTS_DIR']."/vncserver_start";
		$StartFileContents="#!/bin/sh
export DISPLAY=:2
#vncserver -kill $DISPLAY
vncserver $DISPLAY -geometry 1500x800 -depth 16
gnome-session --display=$DISPLAY &
";
		dse_put_file_contents($StartFileName,$StartFileContents);
		$StartFileName=$vars['DSE']['SYSTEM_SCRIPTS_DIR']."/vncserver_stop";
		$StartFileContents="#!/bin/sh
export DISPLAY=:2
vncserver -kill $DISPLAY
";
		
		$vncserverUser=$vars['DSE']['VNCSERVER_USER'];
		$INITD_SCRIPT_ARRAY=array();
		$INITD_SCRIPT_ARRAY['ServiceName']=$vars['DSE']['SERVICES'];
		$INITD_SCRIPT_ARRAY['ActionStart']="sleep 1; sudo -u $vncserverUser -H -s \"/scripts/vncserver_start\"";
		$INITD_SCRIPT_ARRAY['ActionStop']="sudo -u $vncserverUser -H -s \"/scripts/vncserver_stop\"";
		$INITD_SCRIPT_ARRAY['VarStatus']="sudo -u $vncserverUser -H -s \"ps aux | egrep xorg11\"";
		if(dse_is_osx()){
			$INITD_SCRIPT_ARRAY['VarNetstat']="netstat -ta | egrep 5902";
		}else{
			$INITD_SCRIPT_ARRAY['VarNetstat']="netstat -tap | egrep 5902";
		}
		$INITD_SCRIPT_ARRAY['VarIsRunning']=$INITD_SCRIPT_ARRAY['VarNetstat'];
		dse_write_daemon_script($INITD_SCRIPT_ARRAY);
		$InitdFile=$vars['DSE']['SYSTEM_SCRIPTS_DIR']."/".$INITD_SCRIPT_ARRAY['ServiceName']."d";
		dse_initd_entry_add($InitdFile,$INITD_SCRIPT_ARRAY['ServiceName']."d",85);
		dse_service_restart($INITD_SCRIPT_ARRAY['ServiceName']."d");
		print `/dse/bin/dsc -oc`;
	}
	if(str_contains($vars['DSE']['SERVICES'],"crowbar")){
		print "Creating crowbar init.d script.\n";
		$StartFileName=$vars['DSE']['SYSTEM_SCRIPTS_DIR']."/crowbar_start";
		$StartFileContents="#!/bin/sh
export DISPLAY=:1
xulrunner /root/crowbar/trunk/xulapp/application.ini &
";
		dse_put_file_contents($StartFileName,$StartFileContents);
		$crowbarUser=$vars['DSE']['CROWBAR_USER'];
		$INITD_SCRIPT_ARRAY=array();
		$INITD_SCRIPT_ARRAY['ServiceName']=$vars['DSE']['SERVICES'];
		$INITD_SCRIPT_ARRAY['ActionStart']="sleep 1; sudo -u $crowbarUser -H -s \"/scripts/crowbar_start\"";
		$INITD_SCRIPT_ARRAY['ActionStop']="sudo -u $crowbarUser -H -s \"killall -9 xulrunner\"";
		$INITD_SCRIPT_ARRAY['VarIsRunning']="sudo -u $crowbarUser -H -s \"ps aux | egrep xulrunner\"";
		$INITD_SCRIPT_ARRAY['VarStatus']="sudo -u $crowbarUser -H -s \"ps aux | egrep xulrunner\"";
		if(dse_is_osx()){
			$INITD_SCRIPT_ARRAY['VarNetstat']="netstat -ta | egrep 10000";
		}else{
			$INITD_SCRIPT_ARRAY['VarNetstat']="netstat -tap | egrep 10000";
		}
		dse_write_daemon_script($INITD_SCRIPT_ARRAY);
		$InitdFile=$vars['DSE']['SYSTEM_SCRIPTS_DIR']."/".$INITD_SCRIPT_ARRAY['ServiceName']."d";
		dse_initd_entry_add($InitdFile,$INITD_SCRIPT_ARRAY['ServiceName']."d",85);
		dse_service_restart($INITD_SCRIPT_ARRAY['ServiceName']."d");
		print `/dse/bin/dsc -oc`;
	}
	if(str_contains($vars['DSE']['SERVICES'],"dlb")){
		print "Creating dlb init.d script.\n";
		$INITD_SCRIPT_ARRAY=array();
		$INITD_SCRIPT_ARRAY['ServiceName']=$vars['DSE']['SERVICES'];
		$INITD_SCRIPT_ARRAY['ActionStart']="sudo /dse/bin/dlb -d start";
		$INITD_SCRIPT_ARRAY['ActionStop']="sudo /dse/bin/dlb -d stop";
		$INITD_SCRIPT_ARRAY['VarIsRunning']="sudo /dse/bin/dlb -d status | grep 'Running as'";
		$INITD_SCRIPT_ARRAY['VarStatus']="sudo /dse/bin/dlb -d status";
		$INITD_SCRIPT_ARRAY['VarNetstat']="echo 'does not listen, no open ports.'";
		dse_write_daemon_script($INITD_SCRIPT_ARRAY);
		$InitdFile=$vars['DSE']['SYSTEM_SCRIPTS_DIR']."/".$INITD_SCRIPT_ARRAY['ServiceName']."d";
		dse_initd_entry_add($InitdFile,$INITD_SCRIPT_ARRAY['ServiceName']."d",91);
		dse_service_restart($INITD_SCRIPT_ARRAY['ServiceName']."d");
		print `/dse/bin/dsc -oc`;
	}
	if(str_contains($vars['DSE']['SERVICES'],"dwi")){
		print "Creating dwi init.d script.\n";
		$INITD_SCRIPT_ARRAY=array();
		$INITD_SCRIPT_ARRAY['ServiceName']=$vars['DSE']['SERVICES'];
		$INITD_SCRIPT_ARRAY['ActionStart']="sudo apachectl -f /etc/dse/apache2.conf";
		$INITD_SCRIPT_ARRAY['ActionStop']="sudo kill `/dse/bin/grep2pid \"/etc/dse/apache2.conf\"`";
		if(dse_is_osx()){
			$INITD_SCRIPT_ARRAY['VarIsRunning']="netstat -ta | egrep 7907";
		}else{
			$INITD_SCRIPT_ARRAY['VarIsRunning']="netstat -tap | egrep 7907";
		}
		$INITD_SCRIPT_ARRAY['VarStatus']="sudo /dse/bin/dwi -d status";
		if(dse_is_osx()){
			$INITD_SCRIPT_ARRAY['VarNetstat']="netstat -ta | egrep 7907";
		}else{
			$INITD_SCRIPT_ARRAY['VarNetstat']="netstat -tap | egrep 7907";
		}
		dse_write_daemon_script($INITD_SCRIPT_ARRAY);
		$InitdFile=$vars['DSE']['SYSTEM_SCRIPTS_DIR']."/".$INITD_SCRIPT_ARRAY['ServiceName']."d";
		dse_initd_entry_add($InitdFile,$INITD_SCRIPT_ARRAY['ServiceName']."d",91);
		dse_service_restart($INITD_SCRIPT_ARRAY['ServiceName']."d");
		print `/dse/bin/dsc -oc`;
	}

	
	exit();

	//harden
	
	dse_configure_iptables_init();
	
	dse_configure_install_packages();
	
	dse_configure_directories_create();
	
	dse_configure_services_init();
	
	
	/*
#dse_install_package ntop
	#dse_install_package graphviz
	#dse_install_package mailx
	#dse_install_package denyhosts
	#dse_install_package bastille
	#dse_install_package unhide
	#dse_install_package harden
	#dse_install_package snort
	#dse_install_package logcheck
	#dse_install_package integrit
	#dse_install_package tripwire
	#dse_install_package tiger
	#dse_install_package nmap
	
	#dse_install_package vnstat
	#sudo vnstat -u -i eth0
	
	#dse_install_package sysv-rc-conf
	#echo -n "Disabling rsync: "
	#sudo sysv-rc-conf rsync off
	#echo -e "$_OK"
	
	*/

/*



sudo apt-get -yqq update &>/dev/null
if [ $? -eq 0 ]; then
   echo -e "$_OK"
else
   echo -e "$_FATAL"
   exit -1;
fi
echo -n "Running apt-get upgrade: "
sudo apt-get -yqq upgrade &>/dev/null
if [ $? -eq 0 ]; then
   echo -e "$_OK"
else
   echo -e "$_FATAL"
   exit -1;
fi*/
/*
#echo -n "Running apt-get dist-upgrade: "
#sudo apt-get -yqq dist-upgrade &>/dev/null
#if [ $? -eq 0 ]; then
#   echo -e "$_OK"
#else
#   echo -e "$_FATAL"
#   exit -1;
#fi


echo -n "Creating Directory: $DSE_WEBROOT: "
if [ -d $DSE_WEBROOT ]; then
   echo -e "$_OK Exists"
else
   sudo mkdir $DSE_WEBROOT &>/dev/null
   if [ -d $DSE_WEBROOT ]; then
      echo -e "$_OK Created"
   else
      echo -e "$_FATAL"
      exit -1;
   fi
fi
*/


	
}


// ********* main script activity END ************


print getColoredString($vars['DSE']['SCRIPT_FILENAME']." Done!\n","green","black","black","black");

if($DidSomething){
	$vars[shell_colors_reset_foreground]='';	print getColoredString("","white","black","black","black");
	exit(0);
}

exit(0);



	



	 

?>