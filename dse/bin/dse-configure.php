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



$DSE_Git_pull_script="/scripts/dse_git_pull";
$TemplateFile=$vars['DSE']['DSE_TEMPLATES_DIR'] . "/scripts/" . "dse_git_pull";
dse_configure_file_install_from_template($DSE_Git_pull_script,$TemplateFile,"4775","root:root");

$TemplateFile=$vars['DSE']['DSE_TEMPLATES_DIR'] . "/etc/dse/" . "dse.conf";
dse_configure_file_install_from_template($vars['DSE']['DSE_CONFIG_FILE_GLOBAL'],$TemplateFile,"664","root:root");

$TemplateFile=$vars['DSE']['DSE_TEMPLATES_DIR'] . "/etc/dse/" . "ips_whitelist.txt";
dse_configure_file_install_from_template($vars['DSE']['DSE_IPTHROTTLE_WHITELIST_FILE'],$TemplateFile,"664","root:root");

$TemplateFile=$vars['DSE']['DSE_TEMPLATES_DIR'] . "/etc/dse/" . "ips_droplist.txt";
dse_configure_file_install_from_template($vars['DSE']['DSE_IPTHROTTLE_DROPLIST_FILE'],$TemplateFile,"664","root:root");

$TemplateFile=$vars['DSE']['DSE_TEMPLATES_DIR'] . "/etc/dse/" . "apache2.conf";
dse_configure_file_install_from_template($vars['DSE']['DSE_WEB_INTERFACE_APACHE2_FILE'],$TemplateFile,"664","root:root");



dse_file_set_mode($vars['DSE']['DSE_IPTHROTTLE_LOG_DIRECTORY'],"777");


if(dse_is_osx()){
	dse_file_set_owner($vars['DSE']['DSE_BIN_DIR']."/dnetstat.php","root:wheel");
}else{
	dse_file_set_owner($vars['DSE']['DSE_BIN_DIR']."/dnetstat.php","root:root");
}
dse_file_set_mode($vars['DSE']['DSE_BIN_DIR']."/dnetstat.php","4755");



$NeededDirs=array(
 array($vars['DSE']['DSE_BACKUP_DIR'],"777",$vars['DSE']['SYSTEM_ROOT_FILE_USER:GROUP']),
 array($vars['DSE']['DSE_LOG_DIR'],"777",$vars['DSE']['SYSTEM_ROOT_FILE_USER:GROUP']),
 array($vars['DSE']['DSE_LOG_DIR']."/ip_throttle","777",$vars['DSE']['SYSTEM_ROOT_FILE_USER:GROUP']),
 array($vars['DSE']['DSE_CONFIG_DIR'],"777",$vars['DSE']['SYSTEM_ROOT_FILE_USER:GROUP']),
 array($vars['DSE']['SYSTEM_SCRIPTS_DIR'],"777",$vars['DSE']['SYSTEM_ROOT_FILE_USER:GROUP']),
);
if(str_contains($vars['DSE']['SERVICES'],"dns")){
	$NeededDirs[]= array("/etc/bind/local","777",$vars['DSE']['SYSTEM_ROOT_FILE_USER:GROUP']);
}


foreach($NeededDirs as $DirArray){
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
		if($Mode!=dse_file_get_mode($Dir)){
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
print "Realtime cross-shell bash history: ";
	
$Command="grep \"stackoverflow.com/questions/103944/real-time-history-export-amongst-bash-terminal-windows\" ".$vars['DSE']['SYSTEM_BASHRC_FILE'];
$r=trim(`$Command`);
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

if($FullConfig){
	if(str_contains($vars['DSE']['SERVICES'],"http") && str_contains($vars['DSE']['SERVICES'],"mysql")){
		$vars['DSE']['LAMP_SERVER']=TRUE;
	}

	dse_server_configure_file_load();
	dse_configure_create_named_conf();
	dse_configure_create_httpd_conf();
	
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
