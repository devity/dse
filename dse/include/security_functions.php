<?




function dse_dsec_file_hash($Path){
	global $vars; dse_trace();
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
			if($Type=="FILE"){
				$Size=filesize($FileName);
				$mtime=filemtime($FileName);
				$ctime=filectime($FileName);
				$Permissions=fileperms($FileName);
				$MD5=md5_of_file($FileName);
				$FileUser=posix_getpwuid(fileowner($FileName));			$FileUser=$FileUser['name'];			
				$FileGroup=posix_getgrgid(filegroup($FileName));		$FileGroup=$FileGroup['name'];
				print "$FileName\t$Size\t$mtime\t$ctime\t$FileUser\t$FileGroup\t$Permissions\t$MD5\n";
			}elseif($Type=="DIR"){
			//	print "DIR!\n";
				dse_dsec_file_hash($FileName);	
			}else{
				//error
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