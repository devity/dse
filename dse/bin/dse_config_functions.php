<?


function dse_configure_file_link($LinkFile,$DestinationFile){
	global $vars;
	print "DSE file link: $LinkFile =>  ";
	if(file_exists($LinkFile)){
		print getColoredString(" Exists! \n","green","black");
		return 0;
	}else{
		print getColoredString(" Missing: \n","red","black");
		print "   link to: $DestinationFile ? ";
		$key=strtoupper(dse_get_key());
		cbp_characters_clear(1);
		if($key=="Y"){
			print getColoredString(" Linking! ","green","black");
			$error_no=dse_file_link($LinkFile,$DestinationFile);
			if($error_no){
				print getColoredString("Fatal error. Exiting.\n","red","black");
				return -1;
			}
		}elseif($key=="N"){
			print getColoredString(" Skipping. \n","orange","black");
			return 0;
		}else{
			print getColoredString(" unknown key: $key \n","red","black");
			return -2;
		}
	}
}


function dse_configure_file_install_from_template($DestinationFile,$TemplateFile,$Mode,$Owner){
	global $vars;
	print "DSE template: $TemplateFile ";
	if(file_exists($DestinationFile)){
		$CurrentPermissions=dse_file_get_mode($DestinationFile);
		if(intval($Mode)!=$CurrentPermissions){
			print "$DestinationFile permissions wrong. Expected $Mode, found $CurrentPermissions. Fix? ";
			$key=strtoupper(dse_get_key());
			cbp_characters_clear(1);
			if($key=="Y"){
				print getColoredString(" Fixing! ","green","black");
				$error_no=dse_file_set_mode($DestinationFile,$Mode);
				if($error_no){
					print getColoredString("Fatal error. Exiting.\n","red","black");
					return -1;
				}
			}elseif($key=="N"){
				print getColoredString(" Not Fixing.\n","orange","black");
				return 0;
			}else{
				print getColoredString(" unknown key: $key\n","red","black");
				return -2;
			}
			print "\n";
			
		}
		print getColoredString(" Installed","green","black");
		print " at $DestinationFile\n";
	}else{
		print getColoredString(" File missing.","red","black");
		print " Install to $DestinationFile ? ";
		$key=strtoupper(dse_get_key());
		cbp_characters_clear(1);
		if($key=="Y"){
			print getColoredString(" Installing! ","green","black");
			$error_no=dse_file_install($TemplateFile,$DestinationFile,$Mode,$Owner);
			if($error_no){
				print getColoredString("Fatal error. Exiting.\n","red","black");
				return -1;
			}
		}elseif($key=="N"){
			print getColoredString(" Not Fixing.\n","orange","black");
			return 0;
		}else{
			print getColoredString(" unknown key: $key\n","red","black");
			return -2;
		}
		print "\n";
	}
	return -100;
}


function dse_file_install($Template,$Destination,$Mode="",$Owner=""){
	global $vars;
	print "DSE file: $Template ";
	if(str_contains($Template,"/*")){
		$Template_test=strcut($Template,"","/*");
	}else{
		$Template_test=$Template;
	}
	if(!file_exists($Template_test)) {
		print getColoredString(" ERROR: Template missing. \n","red","black");
		return -1;	
	}
	//$command="cp -rf $Template $Destination";
	$command="rsync --partial $Template $Destination";
	print "\n command: $command\n";
	passthru($command);
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
	print getColoredString(" Installed.\n","green","black");
	return 0;
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


function dse_install_yum(){
	global $vars;
	if($vars['DSE']['YUM_INSTALL__FAILED']==TRUE) return -1;
	print getColoredString(" Installing yum... ","blue","black");
	
	$yum=`which apt-get`;
	if(!(strstr($yum,"no apt-get in")===FALSE)){
		print getColoredString(" Using apt-get... ","green","black");
		$Command="sudo apt-get -yV install yum 2>&1";
		$r=`$command`;
		print getColoredString(" Installed.\n","green","black");
		return 0;
	}
	//fink selfupdate-rsync
	
	//fink update-all
	
	/*
	$installer=`which port`;
	//print "which port=$installer\n";
	if( ($installer!="") && (!(strstr($installer,"/port")===FALSE))  ){
		print getColoredString(" Using MacPort's: port... ","green","black");
		$Command="sudo port -vp install yum 2>&1";
		$r=passthru($Command);
		$Command2="sudo port -vp upgrade outdated 2>&1";
		$r2=passthru($Command2);
		$Command3="sudo port -vp activate yum 2>&1";
		$r3=passthru($Command3);
		print getColoredString(" Installed. cmd: $Command \n r=$r\n","green","black");
		return 0;
	}
	*/
	
	print getColoredString(" ERROR: no usable package installer in PATH. \n","red","black");
	
	if(dse_is_osx()){
		//wget https://distfiles.macports.org/MacPorts/MacPorts-2.1.1-10.6-SnowLeopard.pkg
		print " Install http://http://www.finkproject.org/ manually and rerun this install.\n";
	}else{
		print " Please install one of the following: yum, or apt-get \n";
	}
	$vars['DSE']['YUM_INSTALL__FAILED']=TRUE;
	return -1;	
}
//sudo apt-get -yv update
//sudp apt-get -yv upgrade
//sudo port -v selfupdate

function dse_package_install($PackageName){
	global $vars;
	
	$Installer="";
	
	if(dse_is_osx()){
		/*$port=`which port`;
		if(($port) && ((strstr($port,"no port in")===FALSE)) ){
			$Installer="port";
		}
		
		$brew=`which brew`;
		if(($brew) && ((strstr($brew,"no brew in")===FALSE)) ){
			$Installer="brew";
		}*/
		$fink=dse_which("fink");
		if($fink){
			$Installer="fink";
		}
	}
	if(!$Installer){
		$aptget=dse_which("apt-get");
		if($aptget){
			$Installer="apt-get";
		}
	}
	if(!$Installer){
		$yum=dse_which("yum");
		if(!$yum){
			dse_install_yum();
			$yum=dse_which("yum");
			if($yum){
				$Installer="yum";
			}
		}else{
			$Installer="yum";
		}
	}
	
	if($Installer){
		print getColoredString("$Installer ","purple","black");
	}else{
		print getColoredString("FATAL ERROR: No Compatible Installer Found missing.\n","red","black");
		return -1;
	}
	
  	print "Package $PackageName ";
	if(!$PackageName){
    	print getColoredString(" ERROR: PackageName missing. \n","red","black");
		return -1;
	}
	if($Installer=='yum'){
		$Command="sudo yum -y install $PackageName 2>&1";
		if($vars['DSE']['dse_package_install__use_passthru']){
			passthru($Command);
		}else{
			$r=`$Command`;
		//	 print "cmd: $Command   r=".$r."\n";
			if(str_contains($r,"already installed")){
				print getColoredString(" Already Installed.\n","green","black");
				return 0;
		  	}elseif(str_contains($r,"Installed:")){
				print getColoredString(" Installed!\n","green","black");
				return 0;
		  	}else{
			    print getColoredString(" ERROR w/ cmd: $Command\n","red","black");
				return -1;
			}
		}
	}elseif($Installer=='apt-get'){
		$Command="sudo apt-get -y install $PackageName 2>&1";
		if($vars['DSE']['dse_package_install__use_passthru']){
			passthru($Command);
		}else{
			$r=`$Command`;
			// print "cmd: $Command   r=".$r."\n";
			if(str_contains($r,"will be installed")){
				print getColoredString(" Installed.\n","green","black");
				return 0;
		  	}elseif(str_contains($r," already the newest versi")){
				print getColoredString(" Already Installed.\n","green","black");
				return 0;
		  	}elseif(str_contains($r,"ldn't find pack")){
		  		print getColoredString(" Unknown Package Name: $PackageName!\n","red","black");
				return 1;
		  	}else{
			    print getColoredString(" ERROR w/ cmd: $Command\n$r\n","red","black");
				return -1;
			}
		}
	}elseif($Installer=='fink'){
		
		$Command="dpkg -L $PackageName 2>&1";
		if($vars['DSE']['dse_package_install__use_passthru']){
			passthru($Command);
		}else{
			$r=`$Command`;
			if(!str_contains($r,"s not installed") ){
				print getColoredString(" Already Installed.\n","green","black");
				return 0;
			}
			
			$Command="sudo fink -yv install $PackageName 2>&1";
			$r=passthru($Command);
			// print "cmd: $Command   r=".$r."\n";
			if(str_contains($r,"Failed")){
				print getColoredString(" Install Failed!\n","red","black");
				return -1;
		  	}elseif(str_contains($r,"Installed:")){
				print getColoredString(" Installed!\n","green","black");
				return 0;
		  	}elseif(str_contains($r,"o package found fo")){
				print getColoredString(" Unkown Package Name: $PackageName!\n","red","black");
				return -1;
		  	}else{
			    print getColoredString(" ERROR w/ cmd: $Command\n","red","black");
				return -1;
			}
		}
	}else{
		print getColoredString(" ERROR: no supported package installer found \n","red","black");
		   
	}
	/* else if($Installer=='port'){
		$Command="sudo port -pv install $PackageName 2>&1";
		$r=`$Command`;
		 print "cmd: $Command   r=".$r."\n";
		if(!(strstr($r,"already installed")===FALSE)){
			print getColoredString(" Already Installed.\n","green","black");
			return 0;
	  	}elseif(!(strstr($r,"Installed:")===FALSE)){
			print getColoredString(" Installed!\n","green","black");
			return 0;
	  	}else{
		    print getColoredString(" ERROR w/ cmd: $Command\n","red","black");
		   // print "r=".$r."\n";
			return -1;
		}
	}elseif($Installer=='brew'){
		$Command="sudo -u louis /usr/local/bin/brew install $PackageName 2>&1";
		$r=`$Command`;
		// print "cmd: $Command   r=".$r."\n";
		if(!(strstr($r,"already installed")===FALSE)){
			print getColoredString(" Already Installed.\n","green","black");
			return 0;
	  	}elseif(!(strstr($r,"Installed:")===FALSE)){
			print getColoredString(" Installed!\n","green","black");
			return 0;
	  	}elseif(!(strstr($r,"No available formula for")===FALSE)){
			print getColoredString(" Unkown Package name: $PackageName!\n","red","black");
			return 0;
	  	}else{
		    print getColoredString(" ERROR w/ cmd: $Command\n","red","black");
		    print "r=".$r."\n";
			return -1;
		}
	} */
}


function dse_configure_iptables_init(){
	global $vars;
	
}


function dse_configure_install_packages(){
	global $vars;

//"iftop",,"git","gnome"
	$PackageNamesArray=array("vim","memstat","sysstat","yum","chkconfig","lynx-cur","perl-tk","cron-apt","dnsutils","update-inetd",
		"build-essential","rpm-build","aide","chkrootkit","rkhunter","logwatch","xosview","ubuntu-desktop");
	foreach($PackageNamesArray as $PackageName){
		$r=dse_package_install($PackageName);
		if($r<0){
			print getColoredString("FATAL ERROR: installing package $PackageName\n","red","black");
			print getColoredString($vars['DSE']['SCRIPT_FILENAME']."Exiting.\n","red","black");
			exit(-1);
		}
	}
	
	
	$vars['DSE']['dse_package_install__use_passthru']=TRUE;
	$PackageNamesArray=array("postfix");
	foreach($PackageNamesArray as $PackageName){
		$r=dse_package_install($PackageName);
		if($r<0){
			print getColoredString("FATAL ERROR: installing package $PackageName\n","red","black");
			print getColoredString($vars['DSE']['SCRIPT_FILENAME']."Exiting.\n","red","black");
			exit(-1);
		}
	}
	$vars['DSE']['dse_package_install__use_passthru']=FALSE;
	
	
	
	if($vars['DSE']['LAMP_SERVER']){
		print "Installing LAMP server:\n";
		passthru("sudo tasksel install lamp-server");
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
}



function dse_configure_services_init(){
	global $vars;
	
	if(str_contains($vars['DSE']['SERVICES'],"http") ){
		print `sudo chkconfig httpd on`;	
	}
	if(str_contains($vars['DSE']['SERVICES'],"mysql") ){
		print `sudo chkconfig mysqld on`;
	}
	
	if(str_contains($vars['DSE']['SERVICES'],"http") ){
		dse_service_stop("http");
		dse_configure_http_setup();
		dse_service_start("http");
	}
	if(str_contains($vars['DSE']['SERVICES'],"mysql") ){	
		dse_service_stop("mysql");
		dse_configure_mysql_setup();
		dse_service_start("mysql");
	}
}


function dse_configure_directories_create(){
	global $vars;
	
	if(str_contains($vars['DSE']['SERVICES'],"http") ){
 		if($vars['DSE']['HTTP_USER']){
 			$http_user=$vars['DSE']['HTTP_USER'];
			$r=`egrep -q "^${http_user}:" /etc/passwd`;
 			if(!str_contains($r,"$http_user")){
 				print "No http user: $http_user - adding.\n";
 				passthru("sudo adduser $http_user");
 			}
		}	
		if($vars['DSE']['HTTP_GROUP']){
 			$http_group=$vars['DSE']['HTTP_GROUP'];
			$r=`egrep -q "^${http_group}:" /etc/group`;
 			if(!str_contains($r,"$http_group")){
 				print "No http group: $http_group - adding.\n";
 				passthru("sudo addgroup $http_group");
 			}
		}	
		if($vars['DSE']['HTTP_USER'] && $vars['DSE']['HTTP_GROUP']){
			$ug=$vars['DSE']['HTTP_USER'].":".$vars['DSE']['HTTP_GROUP'];
		}else{
			$ug="";
		}
		dse_directory_create($vars['DSE']['HTTP_ROOT_DIR'],"755",$ug);
	}
	
	
	if(str_contains($vars['DSE']['SERVICES'],"mysql") ){
		if($vars['DSE']['MYSQL_USER'] && $vars['DSE']['MYSQL_GROUP']){
			$ug=$vars['DSE']['MYSQL_USER'].":".$vars['DSE']['MYSQL_GROUP'];
		}else{
			$ug="";
		}
		dse_directory_create($vars['DSE']['MYSQL_ROOT_DIR'],"755",$ug);
	}
}

function dse_service_stop($service){
	global $vars;
	print "Stopping service $service: ";
	switch($service){
		case "http":
			$service="apache2";
			break;
		case "mysql":
			$service="mysqld";
			break;
	}
	$c="/sbin/service $service stop";
	$r=`$c`;
	print "Stopped.\n";
}
function dse_service_start($service){
	global $vars;
	print "Starting service $service: ";
	switch($service){
		case "http":
			$service="apache2";
			break;
		case "mysql":
			$service="mysqld";
			break;
	}
	$c="/sbin/service $service start";
	$r=`$c`;
	print "Stopped.\n";
}
	
	
function dse_configure_http_setup(){
	global $vars;
	print "dse_configure_http_setup():\n";
	if($vars['DSE']['INSTALL_SOURCE_DIR']){	
		$http_source=$vars['DSE']['INSTALL_SOURCE_DIR'];//."/http";
		$http_webroot=$http_source."/webroot";
		if(file_exists($http_webroot)){
			print "Installing webroot files.. ";
			dse_file_install($http_webroot."/*",$vars['DSE']['HTTP_ROOT_DIR']."/.");
			print "\n";
		}
	}
}
function dse_configure_mysql_setup(){
	global $vars;
	print "dse_configure_mysql_setup():\n";
	
	
}



?>