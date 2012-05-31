#!/usr/bin/php
<?
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");

$Log="/var/log/publish.log";
$Date_str=date("F j, Y, g:i a");


if(sizeof($argv)>1){
	$cmd=$argv[1];
	switch($cmd){
		case "--last":
			$num=$argv[2];
			$c="grep Publishing: $Log | tail -n $num";
			$r=`$c`;
			foreach(split("\n",$r) as $line){
				$time_str=strcut($line,"","Publishing:");
				$line=strcut($line,"Publishing: cp -pf ");
				$lpa=split(" ",$line);
				$s=$lpa[0]; $d=$lpa[1];
				print "$time_str   $s => $d\n";
			}
			break;
		
	}
	exit (0);	
}

//print `echo $Date_str Run Started >> $Log`;

$rsync_out=`sudo rsync -rnv /home/admin/dse_publish_pending/home/admin/batteriesdirect.com /home/admin/. `;
$published=0;
foreach(split("\n",$rsync_out) as $file){
	$file="/home/admin/".$file;
	if($file!="" && !(strstr($file,"batteriesdirect")===FALSE) ){
		$pending_file="/home/admin/dse_publish_pending".$file;
		if(file_exists($pending_file)){
			if(is_dir($pending_file)){
				print " Creating Dir: $file\n";
				`sudo mkdir -p $file`;
				`sudo chown admin:apache $file`;
				`sudo chmod 775 $file`;
			}else{
				$Date_str=date("F j, Y, g:i a");
				$log_line="$Date_str Publishing: cp -pf $pending_file $file";
				
				print "Publishing: cp -pf $pending_file $file\n";
				`sudo cp -pf $pending_file $file`;
				`sudo chown admin:apache $file`;
				`sudo rm -rf $pending_file`;
				$published++;
				
				print `sudo echo $log_line >> $Log`;
				
			}
		}
	}
}




$rsync_out=`sudo rsync -rnv /home/admin/dse_publish_pending/home/admin/dev-batteriesdirect_com /home/admin/. `;
$published=0;
foreach(split("\n",$rsync_out) as $file){
	$file="/home/admin/".$file;
	if($file!="" && !(strstr($file,"batteriesdirect")===FALSE) ){
		$pending_file="/home/admin/dse_publish_pending".$file;
		if(file_exists($pending_file)){
			if(is_dir($pending_file)){
				print " Creating Dir: $file\n";
				`sudo mkdir -p $file`;
				`sudo chown admin:apache $file`;
				`sudo chmod 775 $file`;
			}else{
				$Date_str=date("F j, Y, g:i a");
				$log_line="$Date_str Publishing: cp -pf $pending_file $file";
				
				print "Publishing: cp -pf $pending_file $file\n";
				`sudo cp -pf $pending_file $file`;
				`sudo chown admin:apache $file`;
				`sudo rm -rf $pending_file`;
				$published++;
				
				print `sudo echo $log_line >> $Log`;
				
			}
		}
	}
}











print "Done.\n";

if($published>0){
	`service httpd restart`;
	`service httpd restart`;
}
exit(1);






$logfile="/var/log/dse_publisher.log";
$rsync_out=`sudo rsync -rnv /home/marqul/dev-batteriesdirect_com /home/admin/. | grep dev-batteriesdirect_com`;
$archive_dir="/user/admin/dse_publish_archive";


if($argv[1]=="--dry-run"){
	print $rsync_out;
	file_put_contents("/tmp/publish.rsync.out",$rsync_out);
	exit();
}

//print $rsync_out;

$PrintedLogIntroLine=FALSE;

foreach(split("\n",$rsync_out) as $file){
	if($file!="" && !(strstr($file,"dev-batteriesdirect_com/")===FALSE) ){
		$Date=date("F j, Y, g:i a");
		$Bytes=`sudo wc -c /home/marqul/$file`;
		$Bytes=str_replace("/home/marqul/$file","",$Bytes);
		$Bytes=str_replace(" ","",$Bytes);
		$Bytes=trim($Bytes);
		print "Publishing $file\n";
	//	$file_is_not_new=file_exists("/home/admin/$file");
	//	if(!$file_is_not_new){
			$filetime_int=filemtime("/home/admin/$file");
			if($filetime_int>0){	
				$filetime=date("Ymdhis",$filetime_int);
			}else{
				$filetime="new";
			}
	//	}else{
	//		$filetime="new";
	//	}
		$new_dir=dirname("$archive_dir/$file");
		`sudo mkdir -p $new_dir`;
		$cp_out=`sudo cp -p /home/admin/$file $archive_dir/$file.$filetime`;
		$new_dir=dirname("/home/admin/$file");
		`sudo mkdir -p $new_dir`;
		$cp_out=`sudo cp -p /home/marqul/$file /home/admin/$file`;
		//print "$cp_out\n";
		$chown_out=`sudo chown -R admin:apache /home/admin/$file`;
		//print "$chwon_out\n";
		if(!$PrintedLogIntroLine){
			`echo "$Date: Start Publish" >> $logfile`;
			$PrintedLogIntroLine=TRUE;
		}
		`echo "$Date: $file $Bytes $filetime" >> $logfile`;
	}
}


?>
