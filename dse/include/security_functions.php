<?php


function package_of_file($File){
	global $vars; dse_trace();
	$r=`dpkg -S "$File" 2>&1`;	
	if(str_contains($r,"no path found")){
		return "";
	}
	$Package=strcut($r,"",":");
	return $Package;
}


function version_of_package($Package){
	global $vars; dse_trace();
	if(!$Package){
		return "";
	}
	$r=`dpkg -s "$Package" 2>&1`;	
	if(str_contains($r,"is not installed")){
		return "";
	}
	$Version=strcut($r,"Version:","\n");
	$Version=trim($Version);
	return $Version;
}


function dse_dsec_file_hash($Path){
	global $vars; dse_trace();
	$Skips=array("/dev","/proc","/tmp","/sys");
	$SkipParts=array("udev/devices","/var/log",".sock",".bzr/",".tmp","/var/spool");
	
	//print "----------dse_dsec_file_hash($Path){\n";
	//print_r($Path);
	//$Path=$Path."/";
	//$Path=str_replace("//", "/", $Path);
	$DirArray=dse_ls($Path);
	foreach($DirArray as $DirEntry){
		list($Type,$Name)=$DirEntry;
		//print "99999999 $Type,$Name\n";
		if($Name!="." && $Name!=".."){
			$FileName=$Path."/".$Name;
			$FileName=str_replace("//", "/", $FileName);
			
			if (is_link($FileName)) {
			    $LinkDesc=readlink($FileName);
				$mtime=filemtime($FileName);
				$ctime=filectime($FileName);
				$Permissions=fileperms($FileName);					
				$FileUser=posix_getpwuid(fileowner($FileName));			$FileUser=$FileUser['name'];			
				$FileGroup=posix_getgrgid(filegroup($FileName));		$FileGroup=$FileGroup['name'];					
				print "$FileName\tLINK\t$LinkDesc\t$mtime\t$ctime\t$FileUser\t$FileGroup\t$Permissions\n";
			}else{
				
				if($Type=="FILE"){
					$Size=filesize($FileName);
					$MD5=md5_of_file($FileName);
					$mtime=filemtime($FileName);
					$ctime=filectime($FileName);
					$Permissions=fileperms($FileName);					
					$FileUser=posix_getpwuid(fileowner($FileName));			$FileUser=$FileUser['name'];			
					$FileGroup=posix_getgrgid(filegroup($FileName));		$FileGroup=$FileGroup['name'];
					$Pkg=package_of_file($FileName);
					$PkgVer=version_of_package($Pkg);
					print "$FileName\t$Size\t$mtime\t$ctime\t$FileUser\t$FileGroup\t$Permissions\t$MD5\t$Pkg\t$PkgVer\n";
				}elseif($Type=="DIR"){
				//	print "DIR!\n";
					$Do=TRUE;
					foreach($Skips as $Skip){
						if($FileName==$Skip){
							//print "if($FileName==$Skip){\n";
							$Do=FALSE;
						}		
					}
					foreach($SkipParts as $Skip){
						if(str_contains($FileName,$Skip)){
							//print "if($FileName==$Skip){\n";
							$Do=FALSE;
						}		
					}
					if($Do){
						dse_dsec_file_hash($FileName);
					}	
				}else{
					//error
				}
			}
		}
	}

}




function dse_dsec_overview(){
	global $vars; dse_trace();
	
	print colorize("Ports: ","cyan","black").dse_ports_open(TRUE)."\n";

	print "Who: ".dse_exec("who");

	print "
	ToDo:\n
	 logwatch   \n
	 rkhunter, chkrootkit \n
	 fail2ban, snort \n
	 tripwire
	 aide
	 fail2ban
	 snort
	 
	";


}

?>
