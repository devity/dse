#!/usr/bin/php
<?
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	

print_r($_SERVER);//exit();

$vars['DSE']['DSE_ROOT']="/dse";


//if(!file_exists("/etc/dse/dse.conf")){
if(!is_dir("/dse")){
	$DSEInstalled=FALSE;
	print "DSE not installed. No /dse\n";
	$vars['DSE']['DSE_ROOT']=str_replace("/bin/dse-install","",$_SERVER['PATH_TRANSLATED']);
	print "DSE ROOT set to " . $vars['DSE']['DSE_ROOT']."\n";
	if(!file_exists("/usr/bin/php")){
		$php=trim(`sudo find /usr -type f -name php`);
		if($php){
			`ln -s $php /usr/bin/php`;
		}
	}
	print "DSE ROOT set to " . $vars['DSE']['DSE_ROOT']."\n";
	
	print $vars['DSE']['DSE_ROOT']."/bin/dse_cli_functions.php \n";
}else{
	$DSEInstalled=TRUE;
}
print "DSE ROOT set to " . $vars['DSE']['DSE_ROOT']."\n";
	




print "starting includes\n";
include_once ($vars['DSE']['DSE_ROOT']."/bin/dse_cli_functions.php");
include_once ($vars['DSE']['DSE_ROOT']."/bin/dse_config.php");
include_once ($vars['DSE']['DSE_ROOT']."/bin/dse_config_functions.php");
print "done includes\n";
print "DSE ROOT set to " . $vars['DSE']['DSE_ROOT']."\n";
	
dse_require_root();
$vars['Verbosity']=1;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="DSE Install Script";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="install of files and setting up settings";
$vars['DSE']['SCRIPT_VERSION']="v0.02b";
$vars['DSE']['SCRIPT_VERSION_DATE']="2012/09/28";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
$vars['DSE']['SCRIPT_COMMAND_FORMAT']="";
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

$parameters_details = array(
  array('v:','verbosity:',"0=none 1=some 2=more 3=debug"),
  array('h','help',"this message"),
  array('p:','is-package-installed:',"tells what version of a package is installe ot nothing if not installed"),
  array('a','list-installed-packages',"lists installed packages"),
  array('s:','show-matching-package:',"searches possible packages"),
  array('m','manage-packages',"launch dselect to manage packages"),
  array('r','list-rollbacks',"list rollbacks"),
  array('u','pakage-upgrade',"run apt/yum/fink/macport update;upgrade"),
  array('l','show-package-history',"show package change history"),
  
);
$vars['parameters']=dse_cli_get_paramaters_array($parameters_details);
$vars['Usage']=dse_cli_get_usage($parameters_details);
$vars['argv_origional']=$argv;
dse_cli_script_start();
		
$BackupBeforeUpdate=TRUE;
foreach (array_keys($vars['options']) as $opt) switch ($opt) {
  	case 'v':
	case 'verbosity':
		$vars['Verbosity']=$vars['options'][$opt];
		if($vars['Verbosity']>=2) print "Verbosity set to ".$vars['Verbosity']."\n";
		break;
}
foreach (array_keys($vars['options']) as $opt) switch ($opt) {
	case 'h':
  	case 'help':
  		$ShowUsage=TRUE;
		$DidSomething=TRUE;
		break;
	case 'p':
  	case 'is-package-installed':
		$PackageName=$vars['options'][$opt];
		if(dse_is_package_installed($PackageName)){
			print $PackageName;
		}
		exit();
	case 'a':
  	case 'list-installed-packages':
		print `dpkg --get-selections`;
		exit();
	case 's':
  	case 'show-matching-package':
		$PackageName=$vars['options'][$opt];
		if(dse_is_ubuntu()){
			print `aptitude search $PackageName`;
		}
		exit();
	case 'm':
  	case 'manage-packages':
		$PackageName=$vars['options'][$opt];
		if(dse_which("dselect")){
			passthru(dse_which("dselect"));
		}
		exit();
  	case 'list-rollbacks':
		dse_exec("up2date --list-rolbacks",TRUE,TRUE);
		exit();
	case 'l':
  	case 'show-package-history':
		$PackageLogFile="/var/log/dpkg.log";
		if(dse_file_exists($PackageLogFile)){
			print dse_exec("cat $PackageLogFile",TRUE);
		}
		$PackageLogFile="/var/log/apt/history.log";
		if(dse_file_exists($PackageLogFile)){
			print dse_exec("cat $PackageLogFile",TRUE);
		}
		exit();
	case 'u':
  	case 'pakage-upgrade':
		dse_package_run_upgrade();
		exit();
		//up2date --list-rollbacks   http://nrh-up2date.sourceforge.net/download.html
		//yum history list
		//apt-get changelog
 //dpkg-reconfigure package_name
 
 //show files of package.
 //show owner package of file
}

if($vars['Verbosity']>4){
	$vars[dse_enable_debug_code]=TRUE;
}
dse_cli_script_header();

if($argv[1]=="help" || $ShowUsage){
	print $vars['Usage'];
}
/*
`rm -rf /tmp/bootinfoscript-061.tar.gz`;
`wget -qO- http://downloads.sourceforge.net/project/bootinfoscript/bootinfoscript/0.61/bootinfoscript-061.tar.gz > /tmp/bootinfoscript-061.tar.gz 2>/dev/null`;

`rm -rf /tmp/bootinfoscript-061.tar`;
print `gunzip /tmp/bootinfoscript-061.tar.gz`;

`rm -rf /tmp/bootinfoscript`;
print `tar xvf /tmp/bootinfoscript-061.tar`;
*/





//exit(0);

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



dse_apt_uu();

$PWD=trim(`pwd`);
$DefaultInstallDirectory=$vars['DSE']['DSE_ROOT'];
$dse_git_dir="$PWD/dse/dse";

	
	if(file_exists("/dse")){
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
		print "DSE not installed at /dse    Install? ";
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
				dse_file_link("/dse",$dse_git_dir);
				
				print " Running DSE Configure first...\n";
				dse_passthru("sudo /dse/bin/dse-configure");
			}
		}elseif($key=="N"){
			print " Not installing. ";
		}else{
			print " unknown key: $key ";
		}
		print "\n";
	}


/*
localepurge: to purge unneeded translations
readahead: to accelerate boot sequence

 * 
 * */
//,"postfix","cron-apt","","","","",""
//"","","","","",""
//"","",""

$PackageNamesArray=array();
$OSXPackageNamesArray=array();
$NotOSXPackageNamesArray=array();



$NotOSXPackageNamesArray[]="vim";
$PackageNamesArray[]="apt-get";
$PackageNamesArray[]="install";


//curl -kL http://xrl.us/perlbrewinstall | bash
//echo source ~/perl5/perlbrew/etc/bashrc >> $HOME/.bashrc


$NotOSXPackageNamesArray[]="perl";
$NotOSXPackageNamesArray[]="perl-tk";
$PackageNamesArray[]="php5";
$PackageNamesArray[]="php5-curl";
$PackageNamesArray[]="python";
$PackageNamesArray[]="g++"; 
$PackageNamesArray[]="build-essential";
$PackageNamesArray[]="yasm";

$PackageNamesArray[]="libssl-dev";
$PackageNamesArray[]="libxrender-dev";
$PackageNamesArray[]="qt4-dev-tools";
$PackageNamesArray[]="libqt4-dev";
$PackageNamesArray[]="libqt4-webkit";




$PackageNamesArray[]="tofrodos";
$PackageNamesArray[]="wget";
$PackageNamesArray[]="curl";
$OSXPackageNamesArray[]="lynx";
$NotOSXPackageNamesArray[]="lynx-cur";

$PackageNamesArray[]="rsync";
//$PackageNamesArray[]="bnz";
$PackageNamesArray[]="bzr"; 
$PackageNamesArray[]="subversion";
$PackageNamesArray[]="git-core";

$PackageNamesArray[]="openssl";



$PackageNamesArray[]="pcregrep";
$PackageNamesArray[]="nmap";

$PackageNamesArray[]="tcpdump";

// ip to location
$PackageNamesArray[]="libgeoip1";
$PackageNamesArray[]="geoip-bin";
$PackageNamesArray[]="geoip-database";
//wget -N http://geolite.maxmind.com/download/geoip/database/GeoLiteCity.dat.gz
//gunzip GeoLiteCity.dat.gz
//mkdir /usr/local/share/GeoIP/
//mv GeoLiteCity.dat /usr/local/share/GeoIP/



foreach($vars['DSE']['AddPkg'] as $Pkg){
	$PackageNamesArray[]=$Pkg;
}


$NotOSXPackageNamesArray[]="memstat";
$NotOSXPackageNamesArray[]="iftop";
$NotOSXPackageNamesArray[]="htop";
$NotOSXPackageNamesArray[]="hwinfo";
$NotOSXPackageNamesArray[]="lftp";
$NotOSXPackageNamesArray[]="sysstat";
$NotOSXPackageNamesArray[]="iftop";
$NotOSXPackageNamesArray[]="xosview";
//http://bootinfoscript.sourceforge.net
//$NotOSXPackageNamesArray[]="mytop";





$NotOSXPackageNamesArray[]="util-linux";
//$NotOSXPackageNamesArray[]="filefrog";
$NotOSXPackageNamesArray[]="loop-aes-utils";
//$NotOSXPackageNamesArray[]="gawk";



$PackageNamesArray[]="openssh-client";
if(dse_is_centos()){
	$PackageNamesArray[]="jwhois";
}elseif(dse_is_osx()){
}else{
	$PackageNamesArray[]="whois";
}
$NotOSXPackageNamesArray[]="dnsutils";
$NotOSXPackageNamesArray[]="arp-scan";
$NotOSXPackageNamesArray[]="nmap";
$NotOSXPackageNamesArray[]="traceroute";
$NotOSXPackageNamesArray[]="ifupdown-extra";

if(dse_is_ubuntu()){
	$PackageNamesArray[]="dpkg-repack";
	//$PackageNamesArray[]="dnet-progs";
	$PackageNamesArray[]="yum";
	$PackageNamesArray[]="alien";
	$PackageNamesArray[]="dpkg-dev";
	$PackageNamesArray[]="debhelper";
	$PackageNamesArray[]="aptitude";
}
$NotOSXPackageNamesArray[]="rpmrebuild";
$NotOSXPackageNamesArray[]="dselect";
$NotOSXPackageNamesArray[]="sysv-rc-conf";

$NotOSXPackageNamesArray[]="chkconfig";


$PackageNamesArray[]="etckeeper";
$PackageNamesArray[]="backintime-common";
if(dse_is_ubuntu()) $PackageNamesArray[]="apt-btrfs-snapshot";

  

/* reddit
 * 
gettext
make
optipng
jpegoptim

memcached
postgresql
postgresql-client
rabbitmq-server
cassandra
haproxy
 * */

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

//	$NotOSXPackageNamesArray[]="xinit";



$ComponentName="performance";
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
		$PackageNamesArray[]="strace";
		$PackageNamesArray[]="hardinfo";
		$PackageNamesArray[]="dmidecode";
		$PackageNamesArray[]="hdparm";
		$NotOSXPackageNamesArray[]="lshw";
		$PackageNamesArray[]="lm-sensors";
		$NotOSXPackageNamesArray[]="hddtemp";
		if(str_contains($vars['DSE']['SERVICES'],"mysql")){
			if(dse_is_ubuntu()){
				$NotOSXPackageNamesArray[]="mysql-workbench-gpl";
			}else{
				$NotOSXPackageNamesArray[]="mysql-bench";
			}
			
			$NotOSXPackageNamesArray[]="mytop";
		}
		if(str_contains($vars['DSE']['SERVICES'],"http")){
			$NotOSXPackageNamesArray[]="apache2-utils";
		}
		
		//$PackageNamesArray[]="munin";
		$NotOSXPackageNamesArray[]="mpstat";
		$PackageNamesArray[]="phoronix";
		$PackageNamesArray[]="cpuburn";
		$PackageNamesArray[]="lmbench";
		$PackageNamesArray[]="dbench";
		$PackageNamesArray[]="netperf"; //http://www.netperf.org/netperf/NetperfPage.html
		$PackageNamesArray[]="x86info"; 		//http://codemonkey.org.uk/projects/x86info/
		$PackageNamesArray[]="smartmontools"; //http://sourceforge.net/apps/trac/smartmontools/wiki/FAQ
		$PackageNamesArray[]="bonnie"; //http://www.textuality.com/bonnie/intro.html
		$NotOSXPackageNamesArray[]="bonnie++"; //http://www.textuality.com/bonnie/intro.html
		//http://www.tux.org/~mayer/linux/bmark.html
		//OpenBenchmarking.org.
		//http://www.stresslinux.org/sl/downloads
		//http://www.iozone.org/
	}
}
$ComponentName="network-analysis";
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
		$PackageNamesArray[]="rrdtool";
		$NotOSXPackageNamesArray[]="rrdtoool-perl";
		$NotOSXPackageNamesArray[]="nmap";
		$NotOSXPackageNamesArray[]="ntop";
		$NotOSXPackageNamesArray[]="nmon";
		$NotOSXPackageNamesArray[]="dsniff";
		$NotOSXPackageNamesArray[]="satan";
		$NotOSXPackageNamesArray[]="saint";
		//rrdtool rrdtoool-perl   http://oss.oetiker.ch/rrdtool/doc/index.en.html
	}
}

$ComponentName="hardening";
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
		$PackageNamesArray[]="fail2ban";
		//$PackageNamesArray[]="rkhunter";
		$PackageNamesArray[]="chkrootkit";
		$PackageNamesArray[]="logwatch";
		$PackageNamesArray[]="tripwire";
		//$PackageNamesArray[]="aide";
		
		$PackageNamesArray[]="libcrypt-ssleay-perl"; 
		//acidbase
		//sudo apt-get install oinkmaster
		//snort-mysql
		//http://code.google.com/p/pulledpork/
		//svn checkout http://pulledpork.googlecode.com/svn/trunk/ pulledpork-read-only
		//svn checkout http://snort-stat.googlecode.com/svn/trunk/ snort-stat-read-only
		
	}
}

$ComponentName="network-monitoring";
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
	
		$PackageNamesArray[]="snort"; 
		
		//svn checkout http://snort-stat.googlecode.com/svn/trunk/ snort-stat-read-only
		
	}
}

if(str_contains($vars['DSE']['SERVICES'],"cacti")){
	$PackageNamesArray[]="cacti";
	$PackageNamesArray[]="rrdtool";
}
if(str_contains($vars['DSE']['SERVICES'],"ntop")){
	$PackageNamesArray[]="ntop";
	$PackageNamesArray[]="graphviz";
	
}

if(str_contains($vars['DSE']['SERVICES'],"dns")){
	$PackageNamesArray[]="bind9";
}
if(str_contains($vars['DSE']['SERVICES'],"ssh")){
	$PackageNamesArray[]="openssh-server";
}
if(str_contains($vars['DSE']['SERVICES'],"smtp")){
	$PackageNamesArray[]="postfix";
	$PackageNamesArray[]="postfix-mysql";
	$PackageNamesArray[]="libsasl2-modules";
	$PackageNamesArray[]="libsasl2-modules-sql";
	$PackageNamesArray[]="libgsasl7";
	$PackageNamesArray[]="libauthen-sasl-cyrus-perl";
	$PackageNamesArray[]="sasl2-bin";
	$PackageNamesArray[]="libpam-mysql";
	$PackageNamesArray[]="clamav-base";
	$PackageNamesArray[]="libclamav6";
	$PackageNamesArray[]="clamav-daemon";
	$PackageNamesArray[]="clamav-freshclam";
	$PackageNamesArray[]="amavisd-new";
	$PackageNamesArray[]="spamassassin";
	$PackageNamesArray[]="spamc";
	$PackageNamesArray[]="postgrey";
	$PackageNamesArray[]="squirrelmail";
	$PackageNamesArray[]="squirrelmail-locales";
	$PackageNamesArray[]="php-pear";
	$PackageNamesArray[]="php5-cli";
	$PackageNamesArray[]="courier-base";
	$PackageNamesArray[]="squirrelmail";
	$PackageNamesArray[]="courier-authdaemon";
	$PackageNamesArray[]="courier-authlib-mysql";
	$PackageNamesArray[]="courier-imap";
	$PackageNamesArray[]="courier-imap-ssl";
	$PackageNamesArray[]="courier-ssl";
}

 
if(str_contains($vars['DSE']['SERVICES'],"http")){
	$PackageNamesArray[]="php5-dev";
	$PackageNamesArray[]="php5-geoip";
	
	/*
	 * wget http://geolite.maxmind.com/download/geoip/database/GeoLiteCity.dat.gz
gunzip GeoLiteCity.dat.gz
sudo mkdir -v /usr/share/GeoIP
sudo mv -v GeoLiteCity.dat /usr/share/GeoIP/GeoIPCity.dat
	 * */
	
	//$PackageNamesArray[]="libgeoip-dev";
	//sudo pecl install geoip  // extension=geoip.so>php.ini
	
}
	


if(str_contains($vars['DSE']['SERVICES'],"mysql")){
	$PackageNamesArray[]="mysql-server";
	$PackageNamesArray[]="php5-mysql";
	$PackageNamesArray[]="mysql-admin";
	$PackageNamesArray[]="php5-mcrypt";
	$PackageNamesArray[]="phpmyadmin";
	$PackageNamesArray[]="mysql-admin";
	$PackageNamesArray[]="libdbd-mysql";
	$PackageNamesArray[]="libdbd-mysql-perl";
	$PackageNamesArray[]="libzip1";
	$PackageNamesArray[]="python-paramiko";
	$PackageNamesArray[]="python-pysqlite2";
	$PackageNamesArray[]="libctemplate0";
	$PackageNamesArray[]="libgtkmm-2.4-1c2a";
	$PackageNamesArray[]="mysql-workbench";
	$PackageNamesArray[]="mysql-workbench-gpl"; //http://www.mysql.com/downloads/workbench/#downloads
	$PackageNamesArray[]="mysql-gui-tools-common";
	$PackageNamesArray[]="mysql-query-browser";
		
}
$PackageNamesArray[]="mysql-client";
$PackageNamesArray[]="libmysqlclient16";
$PackageNamesArray[]="libmysqlclient16-dev";
		
		

$ComponentName="flyback";
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
		if(!dse_is_osx()){
			$NotOSXPackageNamesArray[]="python-glade2";
			$NotOSXPackageNamesArray[]="python-gnome2";
			$NotOSXPackageNamesArray[]="python-sqlite";
			$NotOSXPackageNamesArray[]="python-gconf";
			if(!dse_is_package_installed("flyback")){
				$URL="http://flyback.googlecode.com/files/flyback_0.4.0.tar.gz";
				$r=dse_install_file_from_url($URL);
			}
		}
	}
}
$ComponentName="synergy";
if(!in_array($ComponentName, $vars['DSE']['DisabledComponents'])){
	if(!in_array($ComponentName, $vars['DSE']['AddComponents'])){
		$Component=colorize($ComponentName,"cyan");
		$A=dse_ask_yn("Install Component $Component?");
		print "\n";
		if($A=='Y'){
			$vars['DSE']['AddComponents'][]=$ComponentName;
			$DidReplace=dse_replace_in_file($vars['DSE']['DSE_CONFIG_FILE_GLOBAL'],"# ComponentsAvailable[]=$ComponentName","AddComponents[]=$ComponentName");
		}else{
			$DidReplace=dse_replace_in_file($vars['DSE']['DSE_CONFIG_FILE_GLOBAL'],"# ComponentsAvailable[]=$ComponentName","DisabledComponents[]=$ComponentName");
		}
		if(!$DidReplace){
			dse_file_add_line_if_not($vars['DSE']['DSE_CONFIG_FILE_GLOBAL'],"AddComponents[]=$ComponentName");
		}
	}
	if(in_array($ComponentName, $vars['DSE']['AddComponents'])){
		if(!dse_is_osx()){
			$NotOSXPackageNamesArray[]="libqt4-gui";
			$NotOSXPackageNamesArray[]="libqt4-network";
			//$URL="http://downloads.sourceforge.net/project/synergy2/Binaries/1.3.1/synergy-1.3.1-1.i386.rpm";
			if(!dse_is_package_installed("synergy")){
				$URL="http://synergy.googlecode.com/files/synergy-1.3.1-Linux-i386.rpm";
				dse_install_file_from_url($URL);
			}
		}
	}
}



$ComponentName="crowbar";
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
		$NotOSXPackageNamesArray[]="xulrunner-2.0";
		$NotOSXPackageNamesArray[]="xulrunner-2.0-dev";
		$NotOSXPackageNamesArray[]="xulrunner-2.0-gnome-support";
		$NotOSXPackageNamesArray[]="xulrunner-dev";
		$NotOSXPackageNamesArray[]="gdm";
		$NotOSXPackageNamesArray[]="libsvn1";
		$NotOSXPackageNamesArray[]="subversion";
		if(dse_is_ubuntu()){
			$Command="sudo add-apt-repository ppa:ubuntu-mozilla-daily/ppa";
			print "Command: $Command\n";
			//passthru($Command);
			print "\n";
			
			if(!is_dir("/root/crowbar/trunk")){
			
				chdir("/tmp");
				$Command="svn export http://simile.mit.edu/repository/crowbar/trunk/";
				print "Command: $Command\n";
				`$Command`;
				
				`mkdir /root/crowbar`;
				`mv /tmp/trunk /root/crowbar/.`;
				//$Command="sudo dpkg -i /tmp/xulrunner-2.0_2.0%2Bnobinonly-0ubuntu1_i386.deb";
				//print "Command: $Command\n";
				//passthru($Command);
				
				$Command="xulrunner --install-app /root/crowbar/trunk/xulapp";
				print "Command: $Command\n";
				`$Command`;

				print colorize("xulrunner installed! run with: ","green","white").colorize("xulrunner /root/crowbar/trunk/xulapp/application.ini\n","blue","white");
			}
		}
	}
}

$ComponentName="xurlrunner";
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
		$NotOSXPackageNamesArray[]="xulrunner-2.0";
		$NotOSXPackageNamesArray[]="xulrunner-2.0-dev";
		$NotOSXPackageNamesArray[]="xulrunner-2.0-gnome-support";
		$NotOSXPackageNamesArray[]="xulrunner-dev";
		$NotOSXPackageNamesArray[]="gdm";
		if(dse_is_ubuntu()){
			$Command="sudo add-apt-repository ppa:ubuntu-mozilla-daily/ppa";
			print "Command: $Command\n";
			//passthru($Command);
			print "\n";
			
			
			`wget -qO- http://launchpadlibrarian.net/70321863/xulrunner-1.9.2_1.9.2.17%2Bbuild3%2Bnobinonly-0ubuntu1_i386.deb > /tmp/xulrunner-1.9.2_1.9.2.17%2Bbuild3%2Bnobinonly-0ubuntu1_i386.deb 2>/dev/null`;
			$Command="sudo dpkg -i /tmp/xulrunner-1.9.2_1.9.2.17%2Bbuild3%2Bnobinonly-0ubuntu1_i386.deb";
			print "Command: $Command\n";
			
			/*
			`wget -qO- http://launchpadlibrarian.net/67954580/xulrunner-2.0-mozjs_2.0%2Bnobinonly-0ubuntu1_i386.deb > /tmp/xulrunner-2.0-mozjs_2.0%2Bnobinonly-0ubuntu1_i386.deb 2>/dev/null`;
			$Command="sudo dpkg -i /tmp/xulrunner-2.0-mozjs_2.0%2Bnobinonly-0ubuntu1_i386.deb";
			print "Command: $Command\n";
			
			`wget -qO- http://launchpadlibrarian.net/67954579/xulrunner-2.0_2.0%2Bnobinonly-0ubuntu1_i386.deb > /tmp/xulrunner-2.0_2.0%2Bnobinonly-0ubuntu1_i386.deb 2>/dev/null`;
			$Command="sudo dpkg -i /tmp/xulrunner-2.0_2.0%2Bnobinonly-0ubuntu1_i386.deb";
			print "Command: $Command\n";
			//passthru($Command);
			*/
		}
	}
}

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
		$PackageNamesArray[]="graphviz";
		$NotOSXPackageNamesArray[]="xorg";
		$NotOSXPackageNamesArray[]="xfonts-cyrillic";
		$NotOSXPackageNamesArray[]="xfonts-75dpi";
		$NotOSXPackageNamesArray[]="xfonts-100dpi";
		$NotOSXPackageNamesArray[]="xfonts-base";
		$NotOSXPackageNamesArray[]="gsfonts-x11";
		$NotOSXPackageNamesArray[]="ttf-mscorefonts-installer";
		$NotOSXPackageNamesArray[]="ttf-larabie-uncommon";
		$NotOSXPackageNamesArray[]="ttf-larabie-straight";
		$NotOSXPackageNamesArray[]="ttf-larabie-deco";
		$NotOSXPackageNamesArray[]="sun-java6-fonts";
		$NotOSXPackageNamesArray[]="ttf-sjfonts";
		$NotOSXPackageNamesArray[]="ttf-georgewilliams";
		$NotOSXPackageNamesArray[]="ttf-dustin";
		$NotOSXPackageNamesArray[]="ttf-gentium";
		
		$URL="https://wkhtmltopdf.googlecode.com/files/wkhtmltopdf-0.9.9-static-amd64.tar.bz2";
		$r=dse_install_file_from_url($URL);
	
		$URL="https://wkhtmltopdf.googlecode.com/files/wkhtmltoimage-0.11.0_rc1-static-amd64.tar.bz2";
		$r=dse_install_file_from_url($URL);
	
		//$URL="svn co https://cutycapt.svn.sourceforge.net/svnroot/cutycapt";
		//$r=dse_install_file_from_url($URL);
				
		
	}
}

$ComponentName="desktop";
if(!in_array($ComponentName, $vars['DSE']['DisabledComponents'])){
	if(in_array($ComponentName, $vars['DSE']['AddComponents'])){
		if(dse_is_ubuntu()){
			
		
			$DebSourcesArray[]="http://deb.opera.com/opera/ stable non-free";
			$AptKeyURLsArray[]="http://deb.opera.com/archive.key";
			$PackageNamesArray[]="debian-archive-keyring";
			$PackageNamesArray[]="opera";
			
			$PackageNamesArray[]="chromium-browser";
			
			$PackageNamesArray[]="compizconfig-settings-manager";
			$PackageNamesArray[]="compiz-plugins-extra";
			
			$AddAptRepositoryArray[]="ppa:tualatrix/ppa";	
			$PackageNamesArray[]="ubuntu-tweak";
			
			$PackageNamesArray[]="psensor";
			$PackageNamesArray[]="hddtemp";
			$PackageNamesArray[]="lm-sensors";
			
			$PackageNamesArray[]="dconf-tools";
			$PackageNamesArray[]="gnome-tweak-tool";
			$PackageNamesArray[]="gnome-shell";
			$PackageNamesArray[]="lo-menubar";

			$PackageNamesArray[]="totem-mozilla";
			$PackageNamesArray[]="icedax";
			$PackageNamesArray[]="tagtool";
			$PackageNamesArray[]="easytag";
			$PackageNamesArray[]="id3tool";
			$PackageNamesArray[]="lame";
			$PackageNamesArray[]="nautilus-script-audio-convert";
			
			$PackageNamesArray[]="gstreamer0.10-plugins-ugly";
			$PackageNamesArray[]="gstreamer0.10-ffmpeg";
			$PackageNamesArray[]="libxine1-ffmpeg";
			$PackageNamesArray[]="gxine";
			$PackageNamesArray[]="mencoder";
			$PackageNamesArray[]="libmad0";
			$PackageNamesArray[]="mpg321";
			$PackageNamesArray[]="flashplugin-installer"; //flashplugin-nonfree 
			$PackageNamesArray[]="libdvdread4";
			$PackageNamesArray[]="vlc";
			$NotOSXPackageNamesArray[]="ubuntu-restricted-extras";
			$PackageNamesArray[]="ffmpeg";
			$PackageNamesArray[]="winff";
			$PackageNamesArray[]="libavcodec-extra-53";
			$PackageNamesArray[]="libavbin0";
			
			$PackageNamesArray[]="soundconverter";
			
			$PackageNamesArray[]="youtube-dl";
			
			$PackageNamesArray[]="p7zip-rar";
			$PackageNamesArray[]="p7zip-full";
			$PackageNamesArray[]="unace";
			$PackageNamesArray[]="unrar";
			$PackageNamesArray[]="zip";
			$PackageNamesArray[]="unzip";
			$PackageNamesArray[]="sharutils";
			$PackageNamesArray[]="rar";
			$PackageNamesArray[]="uudeview";
			$PackageNamesArray[]="mpack";
			$PackageNamesArray[]="arj";
			$PackageNamesArray[]="lha";
			$PackageNamesArray[]="cabextract";
			$PackageNamesArray[]="file-roller";
			
			
			
			$NotOSXPackageNamesArray[]="gnome-specimen";


			//$NotOSXPackageNamesArray[]="ubuntu-desktop";
			$NotOSXPackageNamesArray[]="lightdm"; //$NotOSXPackageNamesArray[]="gdm";
			$NotOSXPackageNamesArray[]="x-window-system-core";
			
			$NotOSXPackageNamesArray[]="lubuntu-desktop";
			$NotOSXPackageNamesArray[]="lxde";
			$NotOSXPackageNamesArray[]="lxdm";
			
			$NotOSXPackageNamesArray[]="firefox";
			$NotOSXPackageNamesArray[]="dillo";
			$NotOSXPackageNamesArray[]="epiphany-webkit";
/*			$NotOSXPackageNamesArray[]="idesk";
			$NotOSXPackageNamesArray[]="thunar";
			$NotOSXPackageNamesArray[]="xfe";
			$NotOSXPackageNamesArray[]="rox-filer";
			$NotOSXPackageNamesArray[]="nautilus";*/
			$NotOSXPackageNamesArray[]="pcmanfm";
			$NotOSXPackageNamesArray[]="synaptic";
			$NotOSXPackageNamesArray[]="x11apps";
			$NotOSXPackageNamesArray[]="x11-xfs-utils";
			$NotOSXPackageNamesArray[]="libfs6";
			$NotOSXPackageNamesArray[]="x11-session-utils";
			//$NotOSXPackageNamesArray[]="gnome-power-manage";
			$NotOSXPackageNamesArray[]="modemmanager";
			$NotOSXPackageNamesArray[]="powermgmt-base";
			
			$NotOSXPackageNamesArray[]="backintime-gnome";
			
		/*
			$NotOSXPackageRemoveNamesArray[]="gnome-power-manage";
			$NotOSXPackageRemoveNamesArray[]="modemmanager";
			$NotOSXPackageRemoveNamesArray[]="powermgmt-base";
sudo update-rc.d -f cups remove
sudo update-rc.d -f modem-manager remove
sudo update-rc.d -f bluetooth remove
sudo update-rc.d -f ondemand remove
*/
			if(dse_is_package_installed("lxde")){
				$Command="sudo /usr/lib/lightdm/lightdm-set-defaults -s LXDE";
				dse_exec($Command,TRUE);
			}		
			
		}
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



if(dse_is_osx()){
	foreach($OSXPackageNamesArray as $p) $PackageNamesArray[]=$p;
}else{
	foreach($NotOSXPackageNamesArray as $p) $PackageNamesArray[]=$p;
}


foreach($DebSourcesArray as $DebSource){
	$Command="sudo deb $DebSource";
	dse_exec($Command,TRUE,TRUE);
}
foreach($AptKeyURLsArray as $AptKeyURL){
	$Command="wget -qO - $AptKeyURL | sudo apt-key add -";
	dse_exec($Command,TRUE,TRUE);
}

foreach($AddAptRepositoryArray as $AddAptRepository){
	$Command="sudo add-apt-repository $AddAptRepository";
	dse_exec($Command,TRUE,TRUE);
}

			
			
			
	
	
foreach($PackageNamesArray as $PackageName){
	$r=dse_package_install($PackageName);
	if($r<0){
		print getColoredString("FATAL ERROR: installing package $PackageName\n","red","black");
		print getColoredString($vars['DSE']['SCRIPT_FILENAME']."Exiting.\n","red","black");
		exit(-1);
	}
}

if(!dse_is_osx()){
	if(is_array($NotOSXPackageRemoveNamesArray)){
		foreach($NotOSXPackageRemoveNamesArray as $PackageName){
			$r=dse_package_remove($PackageName);
			/*if($r<0){
				print getColoredString("FATAL ERROR: removing package $PackageName\n","red","black");
				print getColoredString($vars['DSE']['SCRIPT_FILENAME']."Exiting.\n","red","black");
				exit(-1);
			}*/
		}
	}
}



if(in_array("desktop", $vars['DSE']['AddComponents'])){
	if(dse_is_ubuntu()){
		passthru("sudo update-rc.d -f cups remove");
		passthru("sudo update-rc.d -f modem-manager remove");
		passthru("sudo update-rc.d -f bluetooth remove");
		passthru("sudo update-rc.d -f ondemand remove");
	}
}


if(in_array("desktop", $vars['DSE']['AddComponents'])){
	if(dse_is_ubuntu()){
		passthru("sudo fc-cache -f -v");
	}
}



dse_apt_uu();

// ********* main script activity END ************

print getColoredString($vars['DSE']['SCRIPT_FILENAME']." Done!\n","green","black","black","black");

dse_shutdown();

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
