#!/usr/bin/php
<?php
$load_alert_level=3;
$dse_alert_contact_filename="/etc/dse_alert_contacts";
$dse_alert_sent_lock_file="/tmp/dse_alert_sent_pending_clearing";
$dse_from_email="marqul@gmail.com";
$StartTime=time();


if($argv[1]=="-c"){
	`rm -f $dse_alert_sent_lock_file`;
}

print "Starting.\n";

$instance_count=trim(`ps aux | grep server_monitor | grep -v grep | wc -l`);
if($instance_count>1){
	print "already running. exiting.\n";
	exit (0);
}
 

$l=dse_get_server_load();
if($l>$load_alert_level){
	sleep(60);
	$l=dse_get_server_load();
	if($l>$load_alert_level){
		$s="BD High Load @ $l";
		$lf=`cat /proc/loadavg`;	
		$b="server load at $lf ";
		dse_alert_contact("admin",$s,$b);
	}
}

function dse_get_server_load(){
	global $vars;
	$this_loadavg=`cat /proc/loadavg`;
	if($this_loadavg!=""){  
	        $loadaggA=split("       ",$this_loadavg);
	        return number_format($loadaggA[0],3);
	}else{  
	        print "cant open for read: /proc/loadavg<br>";
	        exit(); 
	}
}     

function dse_alert_contact($t,$s,$b){
	global $vars;
	global $dse_alert_contact_filename,$dse_from_email,$dse_alert_sent_lock_file;

	$lock=trim(`cat $dse_alert_sent_lock_file`);
	if($lock!=""){
		$lock_age=time()-$lock;
		if($lock_age>60*60*3){
			`rm -f $dse_alert_sent_lock_file`;
		}else{	
			print "alert pending clearing\n";
			exit(1);
		}
	}
	$t=time();
	`echo -n $time > $dse_alert_sent_lock_file`;
	`chmod 777 $dse_alert_sent_lock_file`;

	
	$contacts_raw=`cat $dse_alert_contact_filename`;
	$contacts_array=split("\n",$contacts_raw);
	foreach($contacts_array as $contact){
		$contact_part_array=split(" ",$contact);
		$contact_email=$contact_part_array[0];
		$contact_type=$contact_part_array[1];
		if($contact_type==$t){
			dse_text_mail($contact_email,$dse_from_email,$s,$b);
			print "emailing $contact_email $s\n";
		}
	}
}



function dse_text_mail($to,$from,$subject,$txtbody){
	global $vars;
	$headers  = "";		
	$headers .= "From: $from\n";		
	//$headers .= "Errors-To: $from\n";
	$headers .= "MIME-Version: 1.0\n";
	$headers .= "X-Mailer: Website\n";
	$headers .= "X-Mailer-Version: 23155614\n";	
	$headers .= "Content-Type: text/plain; charset=iso-8859-1\n";
	$headers .= "Content-Transfer-Encoding: 8bit\n\n";
	$headers .= "$txtbody\n";
	mail($to, $subject, "", $headers);
}
	






?>