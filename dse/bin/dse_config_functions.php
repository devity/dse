<?

/* backup package state!
Code:
dpkg --get-selections > installed-software
And if you wanted to use the list to reinstall this software on a fresh ubuntu setup,
Code:
dpkg --set-selections < installed-software
followed by
Code:
dselect
 * 
 * 
 * /etc/apt/sources.list file on your destination system and see if there are any extra third-party repositories or repository subcategories that need to be enabled.
Once your sources.list file is settled, update your package list to make sure you get the latest version of the packages:
$ sudo apt-get update
Import the Package List
To import the package list, pipe the entire list to xargs, which then splits it into manageable chunks for the apt-get command:
$ cat package_list | xargs sudo apt-get install
 * 
*/

function dse_initd_entry_add($Script,$ServiceName,$Rank=99){
	global $vars; dse_trace();
	if(dse_is_osx()){
		$Command="sudo launchctl remove $ServiceName";
		$r=`$Command`;
		print "$Command = $r\n";
		
		$Command="sudo launchctl submit -l $ServiceName -- $Script start";
		$r=`$Command`;
		print "$Command = $r\n";
		
		$Command="sudo launchctl list $ServiceName";
		$r=`$Command`;
		print "$Command = $r\n";
		
	}else{
		$FileName=basename($Script);
		$Command="ln -s $Script ".$vars['DSE']['SERVER_INITD_DIR']."/$FileName";
		$r=`$Command`;
		print "$r\n";
		
		$StopRank=100-$Rank;
		$Command="update-rc.d $FileName defaults $Rank $StopRank";
		$r=`$Command`;
		print "$r\n";
	}
}
function dse_initd_entry_get_info($ServiceName=""){
	global $vars; dse_trace();
	if(dse_is_osx()){
		$tbr="";
		//foreach(array("RUNNING","NOT_RUNNING") as $Running){
		foreach(array("RUNNING") as $Running){
			if($Running=="RUNNING"){
				$Command="sudo launchctl list $ServiceName | grep -v '^-' | sort";
			}else{
				$Command="sudo launchctl list $ServiceName | grep '^-' | sort";
			}
			print "Command: $Command \n";
			$r=`$Command`;
			if($vars['DSE']['OUTPUT_FORMAT']=="HTML"){
				$tbr.= "<b><i>$Command</i></b><table><tr><td><b>Status</b></td><td><b>Exe Tree</b></td><td><b>PID</b></td><td><b>User</b></td><td><b>Label</b></td></tr>";//<td><b>Full Path</b></td>
			}
			foreach(split("\n",$r) as $L){
				list($PID,$RunStatus,$Label)=split("[ \t]+",$L);
				if(intval($PID)>0){
					$PIDInfo=dse_pid_get_info($PID);
					$ExeTree=dse_pid_get_exe_tree($PID,TRUE);
				}else{
					$PIDInfo=""; $ExeTree="";
				}
				if($vars['DSE']['OUTPUT_FORMAT']=="HTML"){
					$tbr.= "<tr class='f7pt'><td>$Running</td><td>$ExeTree</td><td>$PID</td><td>".$PIDInfo['USER']."</td><td>$Label</td></td>";//<td>".$PIDInfo['EXE']."</td>
				}else{
					$tbr.= "$ExeTree $PID ".$PIDInfo['USER']." $Label ".$PIDInfo['EXE']."\n";
				}
				
			}
			if($vars['DSE']['OUTPUT_FORMAT']=="HTML"){
				$tbr.= "</tr></table>";
			}
		}
		return $tbr;
	}else{
		$FileName=$ServiceName."d";
		$StopRank=100-$Rank;
		$Command="chkconfig --list $FileName | grep \"3:on \" | cut -f1 -d\" \"  ";
		$r=`$Command`;
		
		return $r;
	}
}


function dse_server_set_hostname($NewHostName){
	global $vars; dse_trace();
	if(dse_is_ubuntu()){
		$Hostname=trim(dse_exec("hostname"));
		if($NewHostName!=$Hostname){
			print colorize("Setting hostname: ","cyan","black");
			print colorize("[$Hostname]=>[$NewHostName]\n","yellow","black");
		
			dse_replace_in_file($vars['DSE']['SYSTEM_ETC_HOSTS_FILE'],$Hostname,$NewHostName);
			dse_replace_in_file($vars['DSE']['SYSTEM_HOSTNAME_FILE'],$Hostname,$NewHostName);
			
			$Command="sudo /bin/hostname $NewHostName";
			dse_exec($Command,TRUE);
		
			$Command="/etc/init.d/hostname.sh start";
			dse_exec($Command,TRUE);
			
			//print bar("Server REBOOT required for effect!","-","blue","white","white","red")."n";
			//$vars['DSE']['REBOOT_REQUIRED']=TURE;
		}
	}
}

function dse_server_configure_file_load(){
	global $vars; dse_trace();
	global $strcut_post_haystack;
	dpv(0,"dse_server_configure_file_load");
	$ConfigDirectory=$vars['DSE']['DSE_CONFIG_DIR'];
	$ConfigFileContents=file_get_contents($vars['DSE']['SERVER_CONFIG_FILE']);
	
	if($ConfigFileContents==""){
	    print "ERROR: cant open or empty file: ".$vars['DSE']['SERVER_CONFIG_FILE']."\n";
		return -1;
	}
	
	$ConfigFileContents_new="";
	foreach(split("\n",$ConfigFileContents) as $Line){
		if(!(strstr($Line,"#")===FALSE)){
			//print "CCC\n";
			if(strpos($Line,"#")==0){
				$Line="";
			}else{	
				$ConfigFileContents_new.=substr($Line,0,strpos($Line,"#")-1)."\n";
			}
		}else{
			$ConfigFileContents_new.=$Line."\n";
		}
	}
	$ConfigFileContents=$ConfigFileContents_new;
	dpv(0,"ConfigFileContents=$ConfigFileContents");
	
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
	
	
		
	$DefineCommand="FIREWALL ";
	$Loops=0;
	while( (!( strstr($ProcessedFileContents,$DefineCommand)=== FALSE)) && ($Loops<100)){
	        $Loops++;
	        $DefineCommandAction=strcut($ProcessedFileContents,$DefineCommand,"\n");
	        $Pre=strcut($ProcessedFileContents,"",$DefineCommand);
	        $Post=substr($strcut_post_haystack,strlen($DefineCommandAction)+1);
	        $ProcessedFileContents=$Pre."".$Post;
	        $FirewallCommand=strcut($DefineCommandAction,""," ");
	        $FirewallCommandParamaters=strcut($DefineCommandAction," ");
			//$Defines[$Holder]=$HolderValue;
	       	if($FirewallCommand=="OPEN"){
	       		foreach(split(",",$FirewallCommandParamaters) as $Port){
	       			if($Port){
	       				$FirewallOpen[]=$Port;
	       			}
	       		}
	       	}elseif($FirewallCommand=="ALLOW"){
	       		foreach(split(",",$FirewallCommandParamaters) as $IP){
	       			if($IP){
	       				$FirewallAllow[]=$IP;
	       			}
	       		}
	       	}		
	
	}
	
	
	
	dpv(0,"looking for domains");
	//print "Defines="; print_r($Defines); print "\n";
	//print "Sets="; print_r($Sets); print "\n";
	
	//print "\n\n\n\n\n\n\nProcessed: $ProcessedFileContents\n\n\n\n\n\n\n";
	
	$Command="DOMAIN";

	$vars['DSE']['SERVER_CONF']=array();
	$vars['DSE']['SERVER_CONF']['FirewallPortsOpen']=$FirewallOpen;
	$vars['DSE']['SERVER_CONF']['FirewallAllowIPs']=$FirewallAllow;
	$vars['DSE']['SERVER_CONF']['Domains']=array();
	$vars['DSE']['SERVER_CONF']['Webroots']=array();
	$vars['DSE']['SERVER_CONF']['Hosts']=array();
	$vars['DSE']['SERVER_CONF']['Sets']=$Sets;
	$vars['DSE']['SERVER_CONF']['Defines']=$Defines;
	
	$Loops=0;
	while( (!( strstr($ProcessedFileContents,$Command)=== FALSE)) && ($Loops<100)){
	        $Loops++;
	        $DomainTag=strcut($ProcessedFileContents,$Command." ","END ".$Command);
			dpv(0,"1 DomainTag=$DomainTag");
	        $Pre=strcut($ProcessedFileContents,"",$Command." ");
	        $Post=strcut($ProcessedFileContents,"END ".$Command);
	        $ProcessedFileContents=$Pre."".$Post;
			$Domain=strcut($DomainTag,"","\n");
			$DomainTag=strcut($DomainTag,"\n");
			dpv(0,"2 Domain=$Domain DomainTag=$DomainTag");
			$DomainTags[$Domain]=$DomainTag;
			$vars['DSE']['SERVER_CONF']['Domains'][]=$Domain;
			$vars['DSE']['SERVER_CONF']['Webroots'][$Domain]=array();
			$vars['DSE']['SERVER_CONF']['Hosts'][$Domain]=array();
	       	foreach(split("\n",$DomainTag) as $Line){
	       		$Line=trim($Line);
	       		if($Line){
	       			$Lpa=split(" ",$Line);
					print_r($Lpa);
					$Protocol=$Lpa[0];
					switch($Protocol){
						case "HTTP":
							$Hosts=$Lpa[1];
							if($Lpa[3]){
								$IP=$Lpa[2];
								$Webroot=$Lpa[3];
								foreach(split(",",$Hosts) as $Host){
									$vars['DSE']['SERVER_CONF']['Hosts'][$Domain][$Host]=$IP;
								}
							}else{
								$Webroot=$Lpa[2];
							}
							$vars['DSE']['SERVER_CONF']['Webroots'][$Domain][$Hosts]=$Webroot;
							print "added ['Webroots'][$Domain][$Hosts]=$Webroot\n";
							
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
	global $vars; dse_trace();
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
	global $vars; dse_trace();
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
	global $vars; dse_trace();
	//if(strlen($Mode)==4){
	//	$ExpectedMode=substr($Mode,1,3);
	//}else{
		$ExpectedMode=$Mode;
	//}
	print "DSE template: ";
	print colorize("$TemplateFile ","cyan");
	if(file_exists($DestinationFile)){
		$CurrentPermissions=dse_file_get_mode($DestinationFile);
		if(intval($ExpectedMode)!=$CurrentPermissions){
			print "$DestinationFile permissions wrong. Expected $ExpectedMode, found $CurrentPermissions. Fix? ";
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
	global $vars; dse_trace();
	print "DSE installing file: ";
	print colorize("$Template ","cyan");
	
	if(str_contains($Template,"/*")){
		$Template_test=strcut($Template,"","/*");
	}else{
		$Template_test=$Template;
	}
	if(!file_exists($Template_test)) {
		print getColoredString(" ERROR: Template missing. \n","red","black");
		return -1;	
	}
	$command="cp -rf $Template $Destination";
	//$command="rsync -rR --size-only --partial $Template $Destination";
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



function dse_install_yum(){
	global $vars; dse_trace();
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

function dse_apt_uu(){
	global $vars; dse_trace();
	$Installer=dse_get_installer_name();
	passthru("sudo $Installer update");
	passthru("sudo $Installer upgrade");
}
				
function dse_get_installer_name(){
	global $vars; dse_trace();
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
	}elseif(dse_is_centos()){
		$yum=dse_which("yum");
		if($yum){
			$Installer="yum";
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
	return $Installer;
}
				
function dse_file_get_extension($filename){
	global $vars; dse_trace();
	$ext = end(explode('.', $filename));
	return $ext;
}	
		
function dse_is_package_installed($Package){
	global $vars; dse_trace();
	//if(dse_is_ubuntu()){
		$Command="dpkg --get-selections 2>/dev/null";
		//print colorize("downloading file..\n","red");
		//print "Command: $Command\n";
		$r=`$Command`;
		$r=str_replace("\t"," ",$r);
		foreach(split("\n",$r) as $L){
			$TP=trim(strcut($L,""," "));
		//	print "[$Package==$TP] \n";
			if($Package==$TP) {
			//	print colorize("!!!! $Package=$TP","green");
				return TRUE;
			}
		}
	//}
	return FALSE;
}



function dse_install_file_from_url($URL){
	global $vars; dse_trace();
	$DownloadsLocation="/backup/installs";
	$ar=parse_url($URL);
	//print_r($ar);
	$DirAndFile=$ar[path];
	$FileName=basename($DirAndFile);
	$LocalFullFileName=$DownloadsLocation."/".$FileName;
	$FileExtension=dse_file_get_extension($FileName);
	$FileWithoutExtension=str_remove($FileName,".".$FileExtension);
	$ThisDownloadsLocation=$DownloadsLocation."/".$FileWithoutExtension;
	print bar("INSTALLING from URL: $URL  ","<","blue","white","green","white")."n";
	
	print colorize("Creating Directory: $ThisDownloadsLocation\n","green");
	`mkdir $ThisDownloadsLocation`;
		
	if(!dse_file_exists($LocalFullFileName)){
		$Command="wget -qO- \"$URL\" > $LocalFullFileName 2>/dev/null";
		print colorize("downloading file..\n","red");
		print "Command: $Command\n";
		`$Command`;
	}
	
	if(!dse_file_exists($LocalFullFileName)){
		print colorize("error, $LocalFullFileName not there. downlaod problem?\n","red");
		return;
	}
	
	switch($FileExtension){
		case 'rpm':
			chdir(dirname($LocalFullFileName));
			$Command="alien $LocalFullFileName";
			print "Command: $Command\n";
			$r=`$Command`;
			if(!str_contains($r,"generated")){
				print colorize("error, alien didnt make a deb as expected\n","red");
				break;
			}
			$DebFileName=strcut($r,""," ");
			//$LocalFullFileNameDeb=str_replace(".rpm",".deb",$LocalFullFileName);
			print colorize("$DebFileName generated!\n","green");
				
			$Command="sudo dpkg -i $DebFileName";
			print "Command: $Command\n";
			passthru($Command);
			
			break;
		case 'deb':
			$Command="sudo dpkg -i $LocalFullFileName";
			print "Command: $Command\n";
			passthru($Command);
			break;
		case 'gz':
			$LocalFullUncompressedFileName=str_remove($LocalFullFileName,".gz");
			
			$Command="sudo rm -rf $LocalFullUncompressedFileName";
			print "Command: $Command\n";
			passthru($Command);
			
			$Command="sudo gunzip $LocalFullUncompressedFileName";
			print "Command: $Command\n";
			passthru($Command);
			
			//$Command="sudo rm -rf $LocalFullUncompressedFileName";
		//	print "Command: $Command\n";
			//passthru($Command);
			$UncompressedFileExtension=dse_file_get_extension($LocalFullUncompressedFileName);
			
			if($UncompressedFileExtension=="tar"){
				$Command="sudo tar xvf $LocalFullUncompressedFileName";
				print "Command: $Command\n";
				$r=dse_exec($Command);
				return $r;
			}
			return "";
			break;
		case 'tgz':
			$LocalFullUncompressedFileName=str_remove($LocalFullFileName,".tgz");
			
			//$Command="sudo rm -rf $LocalFullUncompressedFileName";
			//print "Command: $Command\n";
			//passthru($Command);
			
			//$Command="sudo gunzip $LocalFullUncompressedFileName";
			//print "Command: $Command\n";
			//passthru($Command);
			
			//$Command="sudo rm -rf $LocalFullUncompressedFileName";
		//	print "Command: $Command\n";
			//passthru($Command);
		//	$UncompressedFileExtension=dse_file_get_extension($LocalFullUncompressedFileName);
			
			//if($UncompressedFileExtension=="tar"){
				$Command="sudo tar xvf $LocalFullFileName";
				print "Command: $Command\n";
				passthru($Command);
			//}
			
			break;
	}
	
	
	return;
	
	//if(!is_dir("")){
			
				//chdir("/tmp");
				//$Command="svn export http://simile.mit.edu/repository/crowbar/trunk/";
				//print "Command: $Command\n";
				//`$Command`;
				
				//`mkdir /root/crowbar`;
				//`mv /tmp/trunk /root/crowbar/.`;
				//$Command="sudo dpkg -i /tmp/xulrunner-2.0_2.0%2Bnobinonly-0ubuntu1_i386.deb";
				//print "Command: $Command\n";
				//passthru($Command);
				
				//$Command="xulrunner --install-app /root/crowbar/trunk/xulapp";
				//print "Command: $Command\n";
				//`$Command`;

				//print colorize("xulrunner installed! run with: ","green","white").colorize("xulrunner /root/crowbar/trunk/xulapp/application.ini\n","blue","white");
		//	}
	/*
`rm -rf /tmp/bootinfoscript-061.tar.gz`;
`wget -qO- http://downloads.sourceforge.net/project/bootinfoscript/bootinfoscript/0.61/bootinfoscript-061.tar.gz > /tmp/bootinfoscript-061.tar.gz 2>/dev/null`;

`rm -rf /tmp/bootinfoscript-061.tar`;
print `gunzip /tmp/bootinfoscript-061.tar.gz`;

`rm -rf /tmp/bootinfoscript`;
print `tar xvf /tmp/bootinfoscript-061.tar`;

	*/
}		
				
					
function dse_package_install($PackageName,$Remove=FALSE){
	global $vars; dse_trace();
	if(!$PackageName){
		return;
	}
	$PackageNameUpper=strtoupper($PackageName);
	if(dse_is_package_installed($PackageName)){
		print pad("Package Present: ".colorize($PackageNameUpper,"cyan")." ...   ","90%",colorize("-","green"))."\n";
		return;
	}
	
	print pad("Installing Package: ".colorize($PackageNameUpper,"cyan")." ...   ","90%",colorize("-","blue"))."\n";
	
	//$Installer=dse_get_installer_name();
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
	}elseif(dse_is_centos()){
		$yum=dse_which("yum");
		if($yum){
			$Installer="yum";
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
	
	
	if($Remove){
		$Action="remove";
	}else{
		$Action="install";
	}
	
	
	$vars['DSE']['dse_package_install__use_passthru']=TRUE;
  	print "Package $PackageName ";
	if(!$PackageName){
    	print getColoredString(" ERROR: PackageName missing. \n","red","black");
		return -1;
	}
	if($Installer=='yum'){
		$Command="sudo yum -y $Action $PackageName 2>&1";
		print " Running: $Command\n";
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
		$Command="sudo $aptget -y $Action $PackageName 2>&1";
		print " Running: $Command\n";
		if($vars['DSE']['dse_package_install__use_passthru']){
			passthru($Command);
			//dse_popen($Command);
		}else{
			//$r=`$Command`;
			$r=dse_popen($Command);
			 print "cmd: $Command   r=".$r."\n";
			if(str_contains($r,"will be installed")){
				print getColoredString(" Installed.\n","green","black");
				return 0;
		  	}elseif(str_contains($r,"is already ")){
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
		print " Running: $Command\n";
		if($vars['DSE']['dse_package_install__use_passthru']){
			passthru($Command);
		}else{
			$r=`$Command`;
			if(!str_contains($r,"s not installed") ){
				print getColoredString(" Already Installed.\n","green","black");
				return 0;
			}
			
			$Command="sudo fink -yv $Action $PackageName 2>&1";
			$r=passthru($Command);
			return (0);
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
	if(!dse_is_package_installed($PackageName)){
		print dep("Package Failed to Install: ".colorize($PackageNameUpper,"cyan"));
		return;
	}
	
}

function dse_package_run_upgrade(){
	global $vars; dse_trace();
	print pad("Updating Packages: ...   ","90%",colorize("-","blue"))."\n";
	
	//$Installer=dse_get_installer_name();
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
	}elseif(dse_is_centos()){
		$yum=dse_which("yum");
		if($yum){
			$Installer="yum";
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
	
	
	if($Remove){
		$Action="remove";
	}else{
		$Action="install";
	}
	
	
	$vars['DSE']['dse_package_install__use_passthru']=TRUE;
  	
	if($Installer=='yum'){
		$Command="sudo yum update 2>&1";
		dse_passthru($Command,TRUE);
		$Command="sudo yum -y upgrade 2>&1";
		dse_passthru($Command,TRUE);
	}elseif($Installer=='apt-get'){
		$Command="sudo $aptget update 2>&1";
		dse_passthru($Command,TRUE);
		$Command="sudo $aptget -y upgrade 2>&1";
		dse_passthru($Command,TRUE);
	}elseif($Installer=='fink'){
		$Command="dpkg update 2>&1";
		dse_passthru($Command,TRUE);
		$Command="dpkg -y upgrade 2>&1";
		dse_passthru($Command,TRUE);
	}else{
		print getColoredString(" ERROR: no supported package installer found \n","red","black");
		   
	}
	/* else if($Installer=='port'){
		$Command="sudo port self-update 2>&1";
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
	global $vars; dse_trace();
	
	/*:INPUT ACCEPT [9019:1653587]
:FORWARD ACCEPT [0:0]
:OUTPUT ACCEPT [6900:1127927]
:fail2ban-ssh - [0:0]
-A INPUT -p tcp -m multiport --dports 22 -j fail2ban-ssh 
-A fail2ban-ssh -j RETURN 
COMMIT*/
		
	$TemplateContents="
*filter
:INPUT ACCEPT [0:0]
:FORWARD ACCEPT [0:0]
:OUTPUT ACCEPT [0:0]

-A INPUT -i lo -j ACCEPT 

-A INPUT -p tcp -m state --state RELATED,ESTABLISHED -j ACCEPT 
-A OUTPUT -p tcp -m state --state NEW,RELATED,ESTABLISHED -j ACCEPT 

-A INPUT -p udp -m udp --sport 53 --dport 1024:65535 -j ACCEPT
-A OUTPUT -p udp -m udp --sport 1024:65535 --dport 53 -j ACCEPT 

";
//-A OUTPUT -o eth0 -p udp -m udp --sport 1024:65535 --dport 25 -j ACCEPT 

	foreach($vars['DSE']['SERVER_CONF']['FirewallAllowIPs'] as $IP){
		$TemplateContents.="-A INPUT -s $IP -p tcp -m tcp -j ACCEPT\n";
	}
	
	$TemplateContents.="\n";
	foreach($vars['DSE']['SERVER_CONF']['FirewallPortsOpen'] as $Port){
		if(intval($Port)<=0){
			$Port=dse_port_number($Port);
			$PortName=dse_port_name($Port);
		}
		//if($Port!=53){
			$TemplateContents.="# allow service $PortName\n";
			$TemplateContents.="-A INPUT -p tcp -m tcp --dport $Port -m state --state NEW,ESTABLISHED -j ACCEPT \n";
			$TemplateContents.="-A OUTPUT -p tcp -m tcp --sport $Port -m state --state ESTABLISHED -j ACCEPT \n\n";
		//}
	}
	
	$TemplateContents.="\n";
	
	
	$TemplateContents.="-A INPUT -j DROP\n";
	$TemplateContents.="-A OUTPUT -j DROP\n";
	
	$TemplateContents.="\n";
	$TemplateContents.="COMMIT\n";
	
	$TemplateContents.="\n";
	
	$SaveFile="/etc/iptables_rules";
	$SaveFile="/tmp/iptables_rules";
	dse_file_put_contents($SaveFile,$TemplateContents);
	
	print colorize("iptables rules:  saved to $SaveFile \n","blue","yellow");
	print $TemplateContents;
	
	//$Str="/sbin/iptables-restore < /etc/iptables_rules";
	//dse_file_add_line_if_not("/etc/rc.local",$Str,2);
	
	//dse_exec("/sbin/iptables-restore < /etc/iptables_rules 2>&1");
	//dse_exec("/sbin/iptables -nvL 2>&1",FALSE,TRUE);

}

function dse_configure_create_named_conf(){
	global $vars; dse_trace();
	
	foreach($vars['DSE']['SERVER_CONF']['Domains'] as $Domain){
		if($Domain) {
			print "Domain: $Domain\n";	
			foreach($vars['DSE']['SERVER_CONF']['Hosts'][$Domain] as $Host=>$IP){
				print " Host: $Host.$Domain => $IP\n";
			}	
		}
	}
	 
	 
	
	dse_service_stop("named");
print "adding /etc/bind/local/$Domain to named conf\n";
	$named_conf_local="";
	foreach($vars['DSE']['SERVER_CONF']['Domains'] as $Domain){
		$Domain=strtolower($Domain);
		$named_conf_local.= "zone \"$Domain\"{ type master; file \"/etc/bind/local/$Domain\"; };\n";	
	}
	
	$NS1=$vars['DSE']['SERVER_CONF']['Sets']['NameServer1'];
	$NS2=$vars['DSE']['SERVER_CONF']['Sets']['NameServer2'];
	
	foreach($vars['DSE']['SERVER_CONF']['Domains'] as $Domain){
		$domain=strtolower($Domain);
		print "$domain *****\n";
		$zone="\$TTL	300

@		IN	SOA	$domain.	louis.louismarquette.com. (
			2003042204 ; serial
			28800 ; refresh
			14400 ; retry
			3600000 ; expire
			86400 ; default_ttl
			)
@               IN      NS      $NS1.
@               IN      NS      $NS2.
                IN      MX      10 smtp.devity.com.
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
		// print_r($vars['DSE']['SERVER_CONF']['Hosts'][$Domain]);
        foreach($vars['DSE']['SERVER_CONF']['Hosts'][$Domain] as $Host=>$IP){
        	$Host=strtolower($Host);
			if($Host=="_blank") $Host="@";
			$zone.= "$Host	IN	A	$IP\n";
			print  "$Host	IN	A	$IP\n";
			
		}
		$zone_file="/etc/bind/local/$domain";
		print "Saving file $zone_file\n";
		dse_file_put_contents($zone_file, $zone);
		dse_file_set_owner($zone_file,"root:bind");
		dse_file_set_mode($zone_file,"644");
	
	}
	//print "named_conf_local=\n$named_conf_local\n";
	
	
	file_put_contents($vars['DSE']['NAMED_CONF_FILE'], $named_conf_local);
	dse_file_set_owner($vars['DSE']['NAMED_CONF_FILE'],"root:bind");
	dse_file_set_mode($vars['DSE']['NAMED_CONF_FILE'],"644");
		
	dse_service_start("named");
}

function dse_configure_create_httpd_conf(){
	global $vars; dse_trace();
	
	foreach($vars['DSE']['SERVER_CONF']['Domains'] as $Domain){
		print "Domain: $Domain\n";	
		foreach($vars['DSE']['SERVER_CONF']['Hosts'][$Domain] as $Host=>$IP){
			print " Host: $Host.$Domain => $IP\n";
		}	
	}
	 
	 
	
	dse_service_stop("httpd");

	$named_conf_local="";
	foreach($vars['DSE']['SERVER_CONF']['Domains'] as $Domain){
		$Domain=strtolower($Domain);
		$named_conf_local.= "zone \"$Domain\"{ type master; file \"/etc/bind/local/$Domain\"; };\n";	
	}
	
	$NS1=$vars['DSE']['SERVER_CONF']['Sets']['NameServer1'];
	$NS2=$vars['DSE']['SERVER_CONF']['Sets']['NameServer2'];
	
	$i=1;
	if(sizeof($vars['DSE']['SERVER_CONF']['Domains'])>0){
		foreach($vars['DSE']['SERVER_CONF']['Domains'] as $Domain){
			//if($i>4) break;
			$domain=strtolower($Domain);
			$DocRoot=$vars['DSE']['HTTP_ROOT_DIR'];
			print "$domain *****\n";
			
			foreach ($vars['DSE']['SERVER_CONF']['Webroots'][$Domain] as $Hosts=>$Webroot){
				//if($i>4) break;
				foreach(split(",",$Hosts) as $Host){
					//if($i>4) break;
					$Extra="";
					$ServerAlias="$Host.$Domain";
					if($Host=="_blank") $ServerAlias="$Domain";
					$ServerName="$Host.$domain";
					if($Host=="_blank") $ServerName="$domain";
					$IP=$vars['DSE']['SERVER_CONF']['Hosts'][$Domain][$Host];
					$File404="$DocRoot/$Webroot/404.php";
					if(dse_file_exists($File404)){
						$Extra.=" ErrorDocument   404     /404.php\n";
					}
					$site="
	<VirtualHost *:80>
	 ServerName $ServerName
	 ServerAlias $ServerAlias
	 DocumentRoot $DocRoot/$Webroot
	 ErrorLog /var/log/apache2/error.log
	 CustomLog /var/log/apache2/access.log combined
	$Extra
	</VirtualHost>
	";
					$site_file="/etc/apache2/sites-available/$Host.$domain";
					if($Host=="_blank") $site_file="/etc/apache2/sites-available/$domain";
					print "Saving file $site_file $site\n";
					file_put_contents($site_file, $site);
					dse_file_set_owner($site_file,"root:root");
					dse_file_set_mode($site_file,"644");
					
					//$site_file_link="/etc/apache2/sites-enabled/$i.$Host.$domain";
					//dse_file_link($site_file_link,$site_file);
				//	dse_file_set_owner($site_file_link,"root:root");
					//dse_file_set_mode($site_file_link,"777");
					
					
					if($Host=="_blank") {
						$r=`a2ensite $domain`;
					}else{
						$r=`a2ensite $Host.$domain`;
					}
					print $r;
				
					//if($i>4) break;
					$i++;
				}
				//if($i>4) break;
			}
		//if($i>4) break;
		}
	}
	dse_service_start("httpd");
}



function dse_configure_install_packages(){
	global $vars; dse_trace();

//"iftop",,"git","gnome","ubuntu-desktop"
	$PackageNamesArray=array("vim","memstat","sysstat","yum","chkconfig","lynx-cur","perl-tk","cron-apt","dnsutils","update-inetd",
		"build-essential","rpm-build","aide","chkrootkit","rkhunter","logwatch","xosview");
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
	global $vars; dse_trace();
	
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
	global $vars; dse_trace();
	
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

function dse_service_restart($service){
	global $vars; dse_trace();
	dse_service_stop($service);
	dse_service_start($service);
}
function dse_service_stop($service){
	global $vars; dse_trace();
	$service=dse_service_name_from_common_name($service);
	print "Stopping service $service: ";
	$c="/sbin/service $service stop";
	$r=`$c`;
	print "Stopped.\n";
}
function dse_service_start($service){
	global $vars; dse_trace();
	$service=dse_service_name_from_common_name($service);
	print "Starting service $service: ";
	$c="/sbin/service $service start";
	$r=`$c`;
	print "Stopped.\n";
}
	
	
function dse_configure_http_setup(){
	global $vars; dse_trace();
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
	global $vars; dse_trace();
	print "dse_configure_mysql_setup():\n";
}


function dse_get_cfg_file_value($File,$VarName){
	global $vars; dse_trace();
	$CacheName="dse_get_cfg_file_value($File,$VarName)";
	if($vars[$CacheName]) return $vars[$CacheName][$VarName];
	if(is_array($vars[$CacheName])) return NULL;
	$CommentCharacter="#";
	if(str_contains($File,"php.ini")){
		$CommentCharacter=";";
	}
	$Raw=dse_file_get_contents($File);
	foreach(split("\n",$Raw) as $L){
		if($L=trim(strcut($L,"",$CommentCharacter))){
			list($Name,$Value)=split("=",$L);
			$Name=trim($Name); $Value=trim($Value);
			$vars[$CacheName][$Name]=$Value;
		}
	}
	if($vars[$CacheName]) return $vars[$CacheName][$VarName];
	return NULL;
}


function dse_write_daemon_script($INITD_SCRIPT_ARRAY){
	global $vars; dse_trace();
	$InitdFile=$vars['DSE']['SYSTEM_SCRIPTS_DIR']."/".$INITD_SCRIPT_ARRAY['ServiceName']."d";
	
	$tbr="#!/bin/bash

RUNNING=`".$INITD_SCRIPT_ARRAY['VarIsRunning']."`
STAT=`".$INITD_SCRIPT_ARRAY['VarStatus']."`
NETSTAT=`".$INITD_SCRIPT_ARRAY['VarNetstat']."`

IS_RUNNING=AlreadyRunning
NO_RUNNING=NotRunning

LINES=`printf -v f \"%22s\" ; printf \"%s\n\" \"\${f// /-}\"`
case \"$1\" in
  start)
        if [ -n \"\$RUNNING\" ] ; then
            echo -e \"\$LINES\n\$IS_RUNNING\"
            echo -e \"\$NETSTAT\n\$LINES\"
        else
        	echo -e \"\$LINES\nStarting ".$INITD_SCRIPT_ARRAY['ServiceName']."d\n\$LINES\"
			".$INITD_SCRIPT_ARRAY['ActionStart']."
        fi
        ;;

  status)
        if [ -n \"\$RUNNING\" ] ; then
            echo -e \"\$LINES\n\$IS_RUNNING\"
            echo -e \"\$NETSTAT\n\$LINES\"
        else
            echo -e \"\$LINES\n\$NO_RUNNING\n\$LINES\"
        fi
        ;;

   stop)
        if [ -n \"\$RUNNING\" ] ; then
            echo -e \"\$LINES\nStopping ".$INITD_SCRIPT_ARRAY['ServiceName']."d\n\$LINES\"
			".$INITD_SCRIPT_ARRAY['ActionStop']."
        else
            echo -e \"\$LINES\n\$NO_RUNNING\n\$LINES\"
        fi
        ;;

   restart)
        echo -e \"\$LINES\nRestarting ".$INITD_SCRIPT_ARRAY['ServiceName']."d\n\$LINES\"
        $0 stop
        $0 start
        ;;
        
        *)
        echo -e \"\$LINES\nPost Fix Needed: {start | stop | status | restart}\n\$LINES\"
        exit 1
esac
exit 0
";
	if(dse_file_exists($InitdFile)){
		$OldContents=dse_file_get_contents($InitdFile);
		if($OldContents!=$tbr){
			$A=dse_ask_yn("$InitdFile Exists and different. Overwrite?");
			if($A!='Y'){
				return;
			}
			unlink($InitdFile);
		}
	}
	print "Writing $InitdFile\n";
	dse_file_put_contents($InitdFile,$tbr);
	dse_file_set_mode($InitdFile,"775");
}



function dse_backup_mysqld() {
	global $vars; dse_trace();
	dse_detect_os_info();
	
	print bar("Backing up MYSQL ","-","blue","white","white","blue")."n";
	
	dse_exec("/dse/aliases/cdf",FALSE,TRUE);
	print "MySQL Backup Directory: ".$vars['DSE']['BACKUP_DIR_MYSQL']." ";
	if(!is_dir($vars['DSE']['BACKUP_DIR_MYSQL'])){
		print " $Missing. Create? ";
		$A=dse_ask_yn();
		if($A=='Y'){
			dse_directory_create($vars['DSE']['BACKUP_DIR_MYSQL'],"777","root:root");
		}else{
			print "\n  Can't backup w/o backup dir. Exiting.\n";
			exit(-1);	
		}
	}else{
		print $OK;
	}
	print "\n";
	
	print " Saving Copy of mysqld Data: ";
	$DATE_TIME_NOW=trim(`date +"%y%m%d%H%M%S"`);
 	$file=$vars['DSE']['BACKUP_DIR_MYSQL']."/mysqldump".$DATE_TIME_NOW.".sql";
	
	/*$Command="mysqldump --all-databases --user=".$vars['DSE']['MYSQL_USER']." --add-drop-database --comments --debug-info --disable-keys "
		."--dump-date --force --quick --routines --verbose --result-file=$file";
	$pid=dse_exec_bg($Command,TRUE);
	while(dse_pid_is_running($pid)){
		progress_bar();
		sleep(1);
	}
	
	$pid=dse_exec_bg("gzip $file",TRUE);
	while(dse_pid_is_running($pid)){
		progress_bar();
		sleep(1);
	}*/
	
	//--all-databases
	$Command="mysqldump --all-databases --user=".$vars['DSE']['MYSQL_USER']." --add-drop-database --comments --debug-info --disable-keys "
		."--dump-date --force --quick --routines --verbose  > $file"; //| gzip -1 --stdout
	$pid=dse_exec_bg($Command,TRUE);
	while(dse_pid_is_running($pid)){
		$Size=dse_exec("/dse/bin/dsizeof $file");
		progress_bar("time",60," $Size B ");
		sleep(1);
	}
	
	
	
	
	
	//`mysqlhotcopy-all-databases`;


	print " $_OK MySQL backup saved at: $file\n";
	
	dse_exec("/dse/aliases/cdf",FALSE,TRUE);
}
   
   


function dse_backup_httpd() {
	global $vars; dse_trace();
	dse_detect_os_info();
	print bar("Backing up HTTP ","-","blue","white","white","blue")."n";
	
	print "httpd Backup Directory: ".$vars['DSE']['BACKUP_DIR_HTTP']." ";
	if(!is_dir($vars['DSE']['BACKUP_DIR_HTTP'])){
		print " $Missing. Create? ";
		$A=dse_ask_yn();
		if($A=='Y'){
			dse_directory_create($vars['DSE']['BACKUP_DIR_HTTP'],"777","root:root");
		}else{
			print "\n  Can't backup w/o backup dir. Exiting.\n";
			exit(-1);	
		}
	}else{
		print $OK;
	}
	print "\n";
	
	$web_data_dir=$vars['DSE']['HTTP_ROOT_DIR'];
	$dse_server_httpd_backup_directory=$vars['DSE']['BACKUP_DIR_HTTP'];
	
	print " Saving Copy of httpd Data: ";
	
   	$DATE_TIME_NOW=dse_dat_format("NOW","FILE");
   	if(!file_exists($dse_server_httpd_backup_directory)){
   		print "Backup directory $dse_server_httpd_backup_directory missing - fatal error. exiting.\n";
   		exit(1);
   	}
	
	$dir=$dse_server_httpd_backup_directory . "/" . $DATE_TIME_NOW;
	`mkdir $dir`;   
   
   	$web_conf_dir="/etc/httpd";
   	if(!is_dir($web_conf_dir)){
   		$web_conf_dir="/etc/apache2";
	   	if(!is_dir($web_conf_dir)){
	   		$web_conf_dir="";
	   	}
   	}
   
   	if($web_conf_dir){
		$Command="cp -rf $web_conf_dir ${dir}/.";
		print "Command: $Command\n";
		`$Command`;
	}
	//foreach($web_data_dirs as $web_data_dir){
		$Command="rsync -r $web_data_dir ${dir}/.";
		print "Command: $Command\n";
		`$Command`;
	//}

	print "$_OK  saved in  ${dir}\n";
}
   
   


function dse_backup_server_environment() {
	global $vars; dse_trace();
	dse_detect_os_info();
	//if(!$dir){
		$dse_server_environment_backup_directory=$vars['DSE']['DSE_BACKUP_DIR']."/server_environment";
		
		print "Saving Image of Environment Variables in: $dse_server_environment_backup_directory\n";
		
	   	$DATE_TIME_NOW=trim(`date +"%y%m%d%H%M%S"`);
	   	$dir=$dse_server_environment_backup_directory . "/" . $DATE_TIME_NOW;
	   	dse_exec("mkdir ${dir}",TRUE,TRUE);
	   	if(!file_exists($dse_server_environment_backup_directory)){
	   		print "Backup directory $dse_server_environment_backup_directory missing - fatal error. exiting.\n";
	   		exit(1);
	   	}
	 
	//}

    $pid=dse_exec_bg("mount 2>&1 > ${dir}/mount.out",TRUE);
	while(dse_pid_is_running($pid)){ progress_bar();sleep(1);}
	
    $pid=dse_exec_bg("ps aux 2>&1 > ${dir}/ps-aux.out",TRUE);
	while(dse_pid_is_running($pid)){ progress_bar();sleep(1);}
	
   	$pid=dse_exec_bg("ps axjf 2>&1 > ${dir}/ps-axjf.out",TRUE);
	while(dse_pid_is_running($pid)){ progress_bar();sleep(1);}
	
   //	$pid=dse_exec_bg("ps AFl &> ${dir}/ps-AFl.out",TRUE);
//	while(dse_pid_is_running($pid)){ progress_bar();sleep(1);}
	
   	$pid=dse_exec_bg("netstat -pn -l -A inet 2>&1 > ${dir}/netstat-pn-l-Ainet.out",TRUE);
	while(dse_pid_is_running($pid)){ progress_bar();sleep(1);}
	
   	$pid=dse_exec_bg("lsof -i | grep LISTEN 2>&1 > ${dir}/lsof-i.out",TRUE);
	while(dse_pid_is_running($pid)){ progress_bar();sleep(1);}
	
   	$pid=dse_exec_bg("nmap -v -sS localhost 2>&1 > ${dir}/nmap-v-sSlocalhost.out",TRUE);
	while(dse_pid_is_running($pid)){ progress_bar();sleep(1);}
	
    $pid=dse_exec_bg("/dse/bin/dnetstat -o 2>&1 > ${dir}/dnetstat-o.out",TRUE);
	while(dse_pid_is_running($pid)){ progress_bar();sleep(1);}
	
    $pid=dse_exec_bg("/dse/bin/dnetstat -a 2>&1 > ${dir}/dnetstat-a.out",TRUE);
	while(dse_pid_is_running($pid)){ progress_bar();sleep(1);}
	
    $pid=dse_exec_bg("iptables -nvL 2>&1 > ${dir}/iptables-nvl.out",TRUE);
	while(dse_pid_is_running($pid)){ progress_bar();sleep(1);}
	
	
   	$pid=dse_exec_bg("printenv 2>&1 > ${dir}/printenv.out",TRUE);
	while(dse_pid_is_running($pid)){ progress_bar();sleep(1);}
	
   	$pid=dse_exec_bg("df 2>&1 > ${dir}/df.out",TRUE);
	while(dse_pid_is_running($pid)){ progress_bar();sleep(1);}
	
  // 	dse_exec("memstat &> ${dir}/memstat.out");
  
   	if(dse_is_osx() || dse_is_ubuntu()){
   		$pid=dse_exec_bg("dpkg --get-selections 2>&1 > ${dir}/dpkg--get-selections.out",TRUE);
		while(dse_pid_is_running($pid)){ progress_bar();sleep(1);}
	
   	}
   	if(dse_is_centos()){
   		$pid=dse_exec_bg("rpm -qa 2>&1 > ${dir}/rpm-qa.out",TRUE);
		while(dse_pid_is_running($pid)){ progress_bar();sleep(1);}
	
	}
	if(!dse_is_osx()){
		$pid=dse_exec_bg("cat /etc/*-release 2>&1 > ${dir}/cat-etc-release.out",TRUE);
		while(dse_pid_is_running($pid)){ progress_bar();sleep(1);}
	
		$pid=dse_exec_bg("cat /etc/issue 2>&1 > ${dir}/cat-etc-issue.out",TRUE);
		while(dse_pid_is_running($pid)){ progress_bar();sleep(1);}
	
		$pid=dse_exec_bg("uname -a 2>&1 > ${dir}/uname-a.out",TRUE);
		while(dse_pid_is_running($pid)){ progress_bar();sleep(1);}
	
	}
	
	print "$_OK  saved in  ${dir}\n";
	return $dir;
}


   
	
function dse_build_clone_server_script(){
	global $vars; dse_trace();
	$clone_directory=$vars['DSE']['DSE_BACKUP_DIR']."/clone";
	
	print bar("Starting to build clone generation script in: $clone_directory","-","blue","white","blue","white")."\n";
	print "/\n";
	 
	

	
	
   	if(!is_dir($clone_directory)){
   		dse_mkdir($clone_directory);
   	}
   	dse_exec("rm -rf ${clone_directory}/*");
   	if(!is_dir($clone_directory)){
   		print "Clone directory $clone_directory missing and uncreatable - fatal error. exiting.\n";
   		exit(1);
   	}
	

	print bar("Starting backup of server environment in: $clone_directory/server_environment_inspection_output","-","blue","white","green","white")."\n";
	$dir=dse_backup_server_environment();
	if(is_dir($dir)){
		dse_exec("cp -rf $dir ${clone_directory}/server_environment_inspection_output",TRUE);
	}else{
		print "error! dse_backup_server_environment() did not return a path to env dump\n";
	}
	
	
	print bar("Starting backup of rpms in: $clone_directory/rpms","-","blue","white","green","white")."\n";
	dse_rpms_extract();
	dse_exec("cp -rf ".$vars['DSE']['DSE_BACKUP_DIR']."/rpms ${clone_directory}/rpms");
	$dpkg_selections=$clone_directory."/server_environment_inspection_output/dpkg--get-selections.out";
	`cp -rf $dpkg_selections $clone_directory/rpms/.`;

	
	print bar("Starting backup of /etc in: $clone_directory/etc/*","-","blue","white","green","white")."\n";
	if(!file_exists($clone_directory."/etc")){
		//dse_exec("mkdir ".$clone_directory."/etc");
	}
	dse_exec("cp -rf /etc ".$clone_directory."/.",TRUE);
	
	
	print bar("Starting backup of .bash history: $clone_directory/etc/*","-","blue","white","green","white")."\n";
	
	
	print bar("Starting backup of logs in: $clone_directory/logs/*","-","blue","white","green","white")."\n";
	if(!file_exists($clone_directory."/logs")){
		dse_mkdir($clone_directory."/logs");
	}
	dse_exec("cp -rf /var/log/sudo* ".$clone_directory."/logs/.",TRUE);
	
	
	
	print bar("Starting backup of users' home directories: $clone_directory/home/*","-","blue","white","green","white")."\n";
	if(!file_exists($clone_directory."/home")){
		dse_mkdir($clone_directory."/home");
	}
	if(dse_is_osx()){
		$UserDirs=dse_ls("/Users");
	}else{
		$UserDirs=dse_ls("/home");
	}	
	foreach($UserDirs as $UserDirArray){
		list($Type,$FileName)=$UserDirArray;
		$bn=basename($FileName);
		if($bn[0]!='.'){
			$UserHomeDir="/home/".$bn;
			$UserHomeDirBackup=$clone_directory.$UserHomeDir;
			print "Backing up user $FileName's home dir $UserHomeDir to $UserHomeDirBackup\n";
			$Command="cp -rf $UserHomeDir $clone_directory/home/.";
			dse_exec($Command,TRUE);
		}
	}
	
	print bar("Starting backup of root's home directory: $clone_directory/home/root","-","blue","white","green","white")."\n";
	
		$bn="root";
		if($bn[0]!='.'){
			$UserHomeDir="/root";
			$UserHomeDirBackup=$clone_directory.$UserHomeDir;
			print "Backing up user $FileName's home dir $UserHomeDir to $UserHomeDirBackup\n";
			$Command="cp -rf $UserHomeDir $clone_directory/home/.";
			dse_exec($Command,TRUE);
		}
		
		
	
	
	$SystemLSOutputFile=$clone_directory."/ls_of_all_files.txt";
	print bar("Capturing list of all system files and owner,mode,size  in: $SystemLSOutputFile","-","blue","white","green","white")."\n";
	//dse_exec("sudo find / -type d -exec ls -lad {}  2>/dev/null \;  > $SystemLSOutputFile 2>/dev/null",TRUE);
	
	
	print bar("Done Saving/Capturing.  Creating Re-Create / Build Clone Scripts...","-","blue","white","green","white")."\n";
	
	$RestoreScript="#!/bin/php
<"."?php

echo \"***************************** Starting DSE Clone Restore Script *************************************\"

echo \"***************************** Run Level Check *************************************\"

echo \"***************************** Stopping Services *************************************\"



echo \"***************************** Restoring RPMs *************************************\"
dpkg < ./server_environment_inspection_output/dpkg--get-selections.out
apt-get update
apt-get upgrade


echo \"***************************** Restoring /etc *************************************\"




echo \"***************************** Restoring /etc *************************************\"


echo \"***************************** Restoring /etc *************************************\"


echo \"***************************** Restoring /etc *************************************\"




echo \"***************************** Restoring users and groups *************************************\"


echo \"***************************** Restoring file permissions *************************************\"




echo \"***************************** Starting Services *************************************\"


?".">";


$clone_directory=$vars['DSE']['DSE_BACKUP_DIR']."/clone";
	
	$OutFile=$vars['DSE']['DSE_BACKUP_DIR']."/clone.tgz";
	$Command="tar --atime-preserve --preserve-order --preserve-permissions -czf $OutFile $clone_directory";
	dse_exec($Command,TRUE);
	
	print bar("Clone Backup Done! $OutFile","-","blue","white","green","white")."\n";
	print bar("Clone Backup Done! $OutFile","-","black","green","black","green")."\n";
	
	
}


function dse_rpms_extract(){
	global $vars; dse_trace();
	print "Rebuilding rpms in: ".$vars['DSE']['DSE_BACKUP_DIR']."/rpms/\n";
	$rpms=`rpm -qa`;
	foreach(split("\n",$rpms) as $rpm){
		if($rpm){
			$exists=trim(dse_exec("find ".$vars['DSE']['DSE_BACKUP_DIR']."/rpms -iname ${rpm}*"));
			if( strstr($exists,$rpm)===FALSE ){
				print "extracting $rpm\n";
				print dse_exec("rpmrebuild -n -b -d ".$vars['DSE']['DSE_BACKUP_DIR']."/rpms/ $rpm");
			}else{
				print "$exists exists.\n";
			}
		}
	}
}

?>