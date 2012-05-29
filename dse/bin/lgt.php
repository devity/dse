#!/usr/bin/php
<?
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");


$shortopts  = "";
$shortopts .= "n:";  // Required value
$shortopts .= "t::"; // Optional value
$shortopts .= "abc"; // These options do not accept values

$longopts  = array(
    "required:",     // Required value
    "optional::",    // Optional value
    "option",        // No value
    "opt"           // No value
);
$options = getopt($shortopts, $longopts);

if($options['n']){
	$Lines=$options['n'];
}

if(!$Lines){
	$Lines=50;
}
$NumberOfBytesSameLimit=13;


$SudoReplace="s/sudo/SUDO/g";

$TailLines=$Lines*10;

$LogsCombinedCommand="(";
foreach (split(",",$vars['DSE']['LGT_LOG_FILES']) as $LogFile ){
	print "Adding Log File: $LogFile\n";
	if($LogsCombinedCommand!="("){
		$LogsCombinedCommand.="; ";
	}
	$LogsCombinedCommand.="tail -n $TailLines $LogFile ";
}
$LogsCombinedCommand.=") | sort | sed $SudoReplace | grep -v NSAutoreleaseNoPool | grep -v geektool | grep -v Geeklet | grep -v Chrome ";
//| sed 's/louiss-macbook-pro-2//g' | sed 's/Louiss-MacBook-Pro-2//g' 
//| cut -c 8-1000 
//		."tail -n $TailLines /var/log/ppp.log | cut -c 5-1000 |  sed 's/2012 ://g' ; "
	//
 //	."tail -n $TailLines /var/log/krb5kdc/kdc.log.0; "
 //."tail -n $TailLines /var/log/secure.log; "
//	."tail -n $TailLines /var/log/appfirewall.log; "
//	


//print "command=$LogsCombinedCommand<br>";
$LogsCombined=`$LogsCombinedCommand`;
 



$LogsCombined=combine_sameprefixed_lines($LogsCombined);
$LogsCombined=remove_duplicate_lines($LogsCombined);
//$LogsCombined=combine_sameprefixed_lines($LogsCombined);

//print $LogsCombined."\n\n";


$Out="";
$c=0;
$LastText="";
foreach(split("\n",$LogsCombined) as $Line){
	$lpa=split(" ",$Line);
	$Date="$lpa[0] $lpa[1] $lpa[2]";
	//print "Date=$Date\n";
	$Text=substr($Line,strlen($Date)+1);
	$NumberOfBytesSame=str_compare_count_matching_prefix_chars($Text,$LastText);
	//print "nob=$NumberOfBytesSame t=$Text\n";
	
	if($Text==$LastText){
	//	$Out.="dupe\n";
	}elseif($NumberOfBytesSame>$NumberOfBytesSameLimit){
		$LineNewPart=substr($Line,$NumberOfBytesSame);
		$Out.= ",& $LineNewPart";
	//	print "\n$Line";
	}elseif(!(strstr($Line,"ast message repeated")===FALSE)){
		//$Out.="dupe\n";
	}else{
		if($Line!=""){
			$c++;
			$Out.= "\n$Line";
			$LastLine=$Line;
			$LastDate=$Date;
			$LastText=$Text;
		}
	}
}


$FinalLines=split("\n",$Out);

$start=0;
if(sizeof($FinalLines)>$Lines){
	$start=sizeof($FinalLines)-$Lines;
}
//print "start=$start so=".sizeof($FinalLines)."\n";

for($i=$start;$i<sizeof($FinalLines);$i++){
	//print "$i: ";
	print $FinalLines[$i]."\n";
}
print "\n";






?>