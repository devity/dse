<?


function dse_server_configure_file_load(){
	global $vars,$strcut_post_haystack;
	$ConfigDirectory=$vars['DSE']['DSE_CONFIG_DIR'];
	$ConfigFileContents=file_get_contents($vars['DSE']['SERVER_CONFIG_FILE']);
	
	if($ConfigFileContents==""){
	    print "FATAL ERROR: cant open or empty file: $FullFileName\n";
		return -1;
	}
	
	$IncludeCommand="INCLUDE ";
	$Loops=0;
	while( (!( strstr($ConfigFileContents,$IncludeCommand)=== FALSE)) && ($Loops<100)){
	        $Loops++;
	        $ConfigFileIncludeName=strcut($ConfigFileContents,$IncludeCommand,"\n");
	        $ConfigFileContentsPreInclude=strcut($ConfigFileContents,"",$IncludeCommand);
	        $ConfigFileContentsPostInclude=substr($strcut_post_haystack,strlen($ConfigFileIncludeName)+1);
	        $ConfigFileIncludeFullFileName=$ConfigDirectory . "/" . $ConfigFileIncludeName;
	        $ConfigFileIncludeContents=file_get_contents($ConfigFileIncludeFullFileName);
	        $ConfigFileContents=$ConfigFileContentsPreInclude."\n#START OF INC: $ConfigFileIncludeFullFileName\n".$ConfigFileIncludeContents."\n#END
	 OF INC: $ConfigFileIncludeFullFileName\n".$ConfigFileContentsPostInclude;
	}
	
	$IncludeCommand="SET ";
	$Loops=0;
	while( (!( strstr($ConfigFileContents,$IncludeCommand)=== FALSE)) && ($Loops<100)){
	        $Loops++;
	        $NeV=strcut($ConfigFileContents,$IncludeCommand,"\n"); 
	        $Holder=strcut($NeV,""," ");
	        $HolderValue=strcut($NeV," ");
			$Sets[$Holder]=$HolderValue;
	        $ConfigFileContentsPreInclude=strcut($ConfigFileContents,"",$IncludeCommand);
	        $ConfigFileContentsPostInclude=substr($strcut_post_haystack,strlen($NeV)+1);
	        $ConfigFileContents=$ConfigFileContentsPreInclude.$ConfigFileContentsPostInclude;
	}
	
	
	$ProcessedFileContents=$ConfigFileContents;
	
	
	//print "\n\n\n\n\n\n\nProcessed: $ProcessedFileContents\n\n\n\n\n\n\n";
	
	
	$DefineCommand="DEFINE ";
	$Loops=0;
	while( (!( strstr($ProcessedFileContents,$DefineCommand)=== FALSE)) && ($Loops<100)){
	        $Loops++;
	        $DefineCommandAction=strcut($ProcessedFileContents,$DefineCommand,"\n");
	//print "DefineAction: $DefineCommandAction \n";
	        $Pre=strcut($ProcessedFileContents,"",$DefineCommand);
	        $Post=substr($strcut_post_haystack,strlen($DefineCommandAction)+1);
	        $ProcessedFileContents=$Pre."".$Post;
	        $Holder=strcut($DefineCommandAction,""," ");
	        $HolderValue=strcut($DefineCommandAction," ");
	//print "h=$Holder v=$HolderValue\n";
			$Defines[$Holder]=$HolderValue;
	        $ProcessedFileContents=str_replace($Holder,$HolderValue,$ProcessedFileContents);
	
	
	}
	
	//print "Defines="; print_r($Defines); print "\n";
	//print "Sets="; print_r($Sets); print "\n";
	
	//print "\n\n\n\n\n\n\nProcessed: $ProcessedFileContents\n\n\n\n\n\n\n";
	
	$Command="DOMAIN";

	$vars['DSE']['SERVER_CONF']=array();
	$vars['DSE']['SERVER_CONF']['Domains']=array();
	$vars['DSE']['SERVER_CONF']['Webroots']=array();
	$vars['DSE']['SERVER_CONF']['Hosts']=array();
	$vars['DSE']['SERVER_CONF']['Sets']=$Sets;
	$vars['DSE']['SERVER_CONF']['Defines']=$Defines;
	
	$Loops=0;
	while( (!( strstr($ProcessedFileContents,$Command)=== FALSE)) && ($Loops<100)){
	        $Loops++;
	        $DomainTag=strcut($ProcessedFileContents,$Command." ","END ".$Command);
	        $Pre=strcut($ProcessedFileContents,"",$Command." ");
	        $Post=strcut($ProcessedFileContents,"END ".$Command);
	        $ProcessedFileContents=$Pre."".$Post;
			$Domain=strcut($DomainTag,"","\n");
			$DomainTag=strcut($DomainTag,"\n");
			$DomainTags[$Domain]=$DomainTag;
			$vars['DSE']['SERVER_CONF']['Domains'][]=$Domain;
			$vars['DSE']['SERVER_CONF']['Webroots'][$Domain]=array();
			$vars['DSE']['SERVER_CONF']['Hosts'][$Domain]=array();
	       	foreach(split("\n",$DomainTag) as $Line){
	       		$Line=trim($Line);
	       		if($Line){
	       			$Lpa=split(" ",$Line);
					$Protocol=$Lpa[0];
					switch($Protocol){
						case "HTTP":
							$Hosts=$Lpa[1];
							$IP=$Lpa[2];
							$Webroot=$Lpa[3];
							$vars['DSE']['SERVER_CONF']['Webroots'][$Domain][$Hosts]=$Webroot;
							foreach(split(",",$Hosts) as $Host){
								$vars['DSE']['SERVER_CONF']['Hosts'][$Domain][$Host]=$IP;
							}
							break;
						case "HOST":
						case "HOSTS":
							$Hosts=$Lpa[1];
							$IP=$Lpa[2];
							foreach(split(",",$Hosts) as $Host){
								$vars['DSE']['SERVER_CONF']['Hosts'][$Domain][$Host]=$IP;
							}
							break;
						
					}
				}
				
	       	}
	
	}
	print "Domains="; print_r($DomainTags); print "\n";
	
}



function dse_spread_config_to_all_servers(){
       /* $WebServerHostName=str_replace("\r\n","",`hostname`);
        $WebServerHostName=strtoupper(substr($WebServerHostName,0,strpos($WebServerHostName,".")));
        $WebServerNumber=str_replace("WS","",$WebServerHostName);

        if($WebServerNumber!=1){
                `cp -f /usr/local/webroot/ServerLevelConfig.txt /webroots/ws1root/ServerLevelConfig.txt`;
        }
        if($WebServerNumber!=3){
                `cp -f /usr/local/webroot/ServerLevelConfig.txt /webroots/ws3root/ServerLevelConfig.txt`;
        }
        if($WebServerNumber!=4){
                `cp -f /usr/local/webroot/ServerLevelConfig.txt /webroots/ws4root/ServerLevelConfig.txt`;
        }*/
}



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
	$command="rsync -rR --size-only --partial $Template $Destination";
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

function dse_configure_create_named_conf(){
	global $vars;
	
	foreach($vars['DSE']['SERVER_CONF']['Domains'] as $Domain){
		print "Domain: $Domain\n";	
		foreach($vars['DSE']['SERVER_CONF']['Hosts'][$Domain] as $Host=>$IP){
			print " Host: $Host.$Domain => $IP\n";
		}	
	}
	 
	 
	
	dse_service_stop("named");

	$named_conf_local="";
	foreach($vars['DSE']['SERVER_CONF']['Domains'] as $Domain){
		$Domain=strtolower($Domain);
		$named_conf_local.= "zone \"$Domain\"{ type master; file \"/etc/bind/local/$Domain\"; };\n";	
	}
	
	$NS1=$vars['DSE']['SERVER_CONF']['Sets']['NameServer1'];
	$NS2=$vars['DSE']['SERVER_CONF']['Sets']['NameServer2'];
	
	foreach($vars['DSE']['SERVER_CONF']['Domains'] as $Domain){
		$Domain=strtolower($Domain);
		$zone="\$TTL	300

@		IN	SOA	$Domain.	louis.louismarquette.com. (
			2003042204 ; serial
			28800 ; refresh
			14400 ; retry
			3600000 ; expire
			86400 ; default_ttl
			)
@               IN      NS      $NS1.
@               IN      NS      $NS2.
";
		/*if(array_key_exists("_blank",$vars['DSE']['SERVER_CONF']['Hosts'][$Domain])){
			$IP=$vars['DSE']['SERVER_CONF']['Hosts'][$Domain]['_blank'];
			$zone.= "@		IN	A	$IP\n";
		}*/
		/*
		 *     500     IN      MX      10 craftlister.com.s8a1.psmtp.com.
            500     IN      MX      20 craftlister.com.s8a2.psmtp.com.
            500     IN      MX      30 craftlister.com.s8b1.psmtp.com.
            500     IN      MX      40 craftlister.com.s8b2.psmtp.com.
		 */
		 print_r($vars['DSE']['SERVER_CONF']['Hosts'][$Domain]);
        foreach($vars['DSE']['SERVER_CONF']['Hosts'][$Domain] as $Host=>$IP){
        	$Host=strtolower($Host);
			if($Host=="_blank") $Host="@";
			$zone.= "$Host	IN	A	$IP\n";
			print  "$Host	IN	A	$IP\n";
			
		}
		$zone_file="/etc/bind/local/$Domain";
		file_put_contents($zone_file, $zone);
		dse_file_set_owner($zone_file,"root:bind");
		dse_file_set_mode($zone_file,"644");
	
	}
	//print "named_conf_local=\n$named_conf_local\n";
	
	
	file_put_contents($vars['DSE']['NAMED_CONF_FILE'], $named_conf_local);
	dse_file_set_owner($vars['DSE']['NAMED_CONF_FILE'],"root:bind");
	dse_file_set_mode($vars['DSE']['NAMED_CONF_FILE'],"644");
		
	dse_service_start("named");
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

function dse_service_name_from_common_name($service){
	global $vars;
	switch($service){
		case "http":
			$service="apache2";
			break;
		case "mysql":
			$service="mysqld";
			break;
		case "named":
			$service="bind9";
			break;
	}
	return $service;
}
function dse_service_stop($service){
	global $vars;
	$service=dse_service_name_from_common_name($service);
	print "Stopping service $service: ";
	$c="/sbin/service $service stop";
	$r=`$c`;
	print "Stopped.\n";
}
function dse_service_start($service){
	global $vars;
	$service=dse_service_name_from_common_name($service);
	print "Starting service $service: ";
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