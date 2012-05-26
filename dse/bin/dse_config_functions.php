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
	if(!file_exists($Template)) {
		print getColoredString(" ERROR: Template missing. \n","red","black");
		return -1;	
	}
	$command="cp -rf $Template $Destination";
	`$command`;
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
	
  	print "install package $PackageName ";
	if(!$PackageName){
    	print getColoredString(" ERROR: PackageName missing. \n","red","black");
		return -1;
	}
	if($Installer=='yum'){
		$Command="sudo yum -y install $PackageName 2>&1";
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
	}elseif($Installer=='apt-get'){
		$Command="sudo apt-get -y install $PackageName 2>&1";
		$r=`$Command`;
		// print "cmd: $Command   r=".$r."\n";
		if(str_contains($r," already the newest versi")){
			print getColoredString(" Already Installed.\n","green","black");
			return 0;
	  	}elseif(str_contains($r,"ldn't find pack")){
	  		print getColoredString(" Unknown Package Name: $PackageName!\n","red","black");
			return 1;
	  	}else{
		    print getColoredString(" ERROR w/ cmd: $Command\n","red","black");
			return -1;
		}
	}elseif($Installer=='fink'){
		
		$Command="dpkg -L $PackageName 2>&1";
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


?>