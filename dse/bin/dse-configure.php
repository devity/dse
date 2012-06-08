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



dse_file_link("/usr/bin/php",trim(`which php`));

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



dse_file_set_mode($vars['DSE']['DSE_IPTHROTTLE_LOG_DIRECTORY'],"777");


if(dse_is_osx()){
	dse_file_set_owner($vars['DSE']['DSE_BIN_DIR']."/dnetstat.php","root:wheel");
}else{
	dse_file_set_owner($vars['DSE']['DSE_BIN_DIR']."/dnetstat.php","root:root");
}
dse_file_set_mode($vars['DSE']['DSE_BIN_DIR']."/dnetstat.php","4755");


//sudo logging
//echo -n "sudo Logging: "
//if ! grep logfile= /etc/sudoers &>/dev/null ; then
//  sudo echo "Defaults logfile=$SUDOLOG" | sudo tee -a /etc/sudoers &>/dev/null

//larger bash history

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


	//harden
	
	
	//install packages
//"iftop",,"git"
	$PackageNamesArray=array("vim","memstat","sysstat","yum","chkconfig","lynx-cur","postfix","perl-tk","cron-apt","dnsutils","update-inetd",
		"build-essential","aide","chkrootkit","rkhunter","logwatch","xosview","ubuntu-desktop","gnome");
	foreach($PackageNamesArray as $PackageName){
		$r=dse_package_install($PackageName);
		if($r<0){
			print getColoredString("FATAL ERROR: installing package $PackageName\n","red","black");
			print getColoredString($vars['DSE']['SCRIPT_FILENAME']."Exiting.\n","red","black");
			exit(-1);
		}
	}
	
	if($vars['DSE']['LAMP_SERVER']){
		print "Installing LAMP server:\n";
		print `sudo tasksel install lamp-server`;
	}	
	
	
	$PackageNamesArray=array("tasksel","dselect");
	if(str_contains($vars['DSE']['SERVICES'],"ssh") ){
		$PackageNamesArray[]="ssh";
	}
	if(str_contains($vars['DSE']['SERVICES'],"vpn") ){
		$PackageNamesArray[]="openvpn";
	}
	if(str_contains($vars['DSE']['SERVICES'],"dns") ){
		$PackageNamesArray[]="bind9";
	}
	if(str_contains($vars['DSE']['SERVICES'],"ftp") ){
		$PackageNamesArray[]="vsftpd";
	}
	
	foreach($PackageNamesArray as $PackageName){
		$r=dse_package_install($PackageName);
		if($r<0){
			print getColoredString("FATAL ERROR: installing package $PackageName\n","red","black");
			print getColoredString($vars['DSE']['SCRIPT_FILENAME']."Exiting.\n","red","black");
			exit(-1);
		}
	}
	
	if(str_contains($vars['DSE']['SERVICES'],"vpn") ){
		print `sudo chkconfig openvpn off`;
	}
	if(str_contains($vars['DSE']['SERVICES'],"ftp") ){
		print `sudo chkconfig vsftpd off`;
	}
	
	
	
	if(str_contains($vars['DSE']['SERVICES'],"http") ){
			
 		if($vars['DSE']['HTTP_USER']){
 			$http_user=$vars['DSE']['HTTP_USER'];
			$r=`egrep -q "^${http_user}:" /etc/passwd`;
 			if(!str_contains($r,"$http_user")){
 				print "No http user: $http_user - adding.\n";
 				`sudo adduser $http_user`;
 			}
		}	
		if($vars['DSE']['HTTP_GROUP']){
 			$http_group=$vars['DSE']['HTTP_GROUP'];
			$r=`egrep -q "^${http_group}:" /etc/group`;
 			if(!str_contains($r,"$http_group")){
 				print "No http group: $http_group - adding.\n";
 				`sudo addgroup $http_group`;
 			}
		}	
		
		
		$c="mkdir ".$vars['DSE']['HTTP_ROOT_DIR']; 		$r=`$c`;
		if($vars['DSE']['HTTP_USER'] && $vars['DSE']['HTTP_GROUP']){
			$ug=$vars['DSE']['HTTP_USER'].":".$vars['DSE']['HTTP_GROUP'];
			dse_file_set_owner($vars['DSE']['HTTP_ROOT_DIR'],$ug);
		}
		dse_file_set_mode($vars['DSE']['HTTP_ROOT_DIR'],"755");
		
		
		print `sudo chkconfig httpd on`;
		
	}
	
	
	
	if(str_contains($vars['DSE']['SERVICES'],"mysql") ){

		$c="mkdir ".$vars['DSE']['MYSQL_ROOT_DIR']; 		$r=`$c`;
		if($vars['DSE']['MYSQL_USER'] && $vars['DSE']['MYSQL_GROUP']){
			$ug=$vars['DSE']['MYSQL_USER'].":".$vars['DSE']['MYSQL_GROUP'];
			dse_file_set_owner($vars['DSE']['MYSQL_ROOT_DIR'],$ug);
		}
		dse_file_set_mode($vars['DSE']['MYSQL_ROOT_DIR'],"755");
		
		print `sudo chkconfig mysqld on`;
	}
	
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
