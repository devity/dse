#!/usr/bin/php
<?
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
ini_set("memory_limit","-1");
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");

$Lines=40;
$MinutesBack=60;
$NumberOfBytesSameLimit=13;
$Intermingle=TRUE;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="Log Tail";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="tails, intermingles, and color codes system logs";
$vars['DSE']['DSE_DSE_VERSION']="v0.01b";
$vars['DSE']['DSE_DSE_VERSION_DATE']="2012/06/23";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

$shortopts  = "";
$shortopts .= "n:";  // Required value
$shortopts .= "m:";  // Required value
$shortopts .= "v:";  // Required value
$shortopts .= "t::"; // Optional value
$shortopts .= "i"; // These options do not accept values

$options = getopt($shortopts);

if($options['v']){
	$vars[Verbosity]=$options['v'];
}
if($options['n']){
	$Lines=$options['n'];
}
if($options['m']){
	$MinutesBack=$options['m'];
}
if(key_exists('i', $options)){
	$Intermingle=FALSE;
}

$CharsWide=cbp_get_screen_width()-19;

$vars['s2t_abvr']=TRUE;

if(in_array("more", $argv)){
	$Lines*=3;
	$MinutesBack*=3;
}
if(in_array("much", $argv)){
	$Lines*=3;
	$MinutesBack*=3;
}
if(in_array("allday", $argv)){
	$Lines=30000;
	$MinutesBack=60*60*24;
}

$SudoReplace="s/sudo/SUDO/g";

$TailLines=$Lines;

$LogsCombined="";

dpv(2,"Using Log Files: ".$vars['DSE']['LGT_LOG_FILES']."\n");
foreach (split(",",$vars['DSE']['LGT_LOG_FILES']) as $LogFile ){
	dpv(2,"Doing Log File: $LogFile\n");
		
	
	$LogFile=trim($LogFile);
	$LogFileNameColorized=colorize(pad(basename($LogFile),10),"cyan");
	if($LogFile && dse_file_exists($LogFile)){
		$LogContents=`tail -n $TailLines $LogFile`;
		if($LogContents){
			$LogContents=str_remove($LogContents,dse_hostname());
			
			
			$PrintedThisLogFileName=FALSE;
			foreach(split("\n",$LogContents) as $L){
				if($L){
					
					$Time=unk_time($L);
					$StartTime=time()-(60*$MinutesBack);
					if($Time>0){
						$L=str_remove($L,$vars['unk_time__CutTimeAndDateString']." ");
						$Ago=pad(seconds_to_text($vars[Time]-$Time),6);
					}else{
						$Ago="";
					}
					
					if($Time<=0 || $Time>$StartTime){
						$L=substr($L,0,$CharsWide);
						$L=colorize_words($L);
						if($Time<=0){
							dpv(0," t=$Time $L\n","red");
						}else{
							dpv(5," t=$Time $L\n");
						}
						if($Intermingle){
							if($Time){
								$Rand=rand(100,999);
								$Intermingled[$Time."0".$Rand]="$LogFileNameColorized $Ago  $L\n";
							}else{
								print "$LogFileNameColorized $Ago  $L\n";;
							}
						}else{
							if((!$PrintedThisLogFileName)) { print colorize($LogFile.": ------\n","cyan"); $PrintedThisLogFileName=TRUE; }
							print  "$Ago  $L\n";
						}
					}
				}
			}
		}
	}
}
//print "printing\n";
//print_r($Intermingled);
if($Intermingle && is_array($Intermingled)){
	ksort($Intermingled); foreach($Intermingled as $i=>$L) print $L;// print $i." ".$L;
}else{
	
}

//$LogsCombinedCommand.=") | sed $SudoReplace | grep -v NSAutoreleaseNoPool | grep -v geektool | grep -v Geeklet | grep -v Chrome ";
//| sed 's/louiss-macbook-pro-2//g' | sed 's/Louiss-MacBook-Pro-2//g' 
//| cut -c 8-1000 
//		."tail -n $TailLines /var/log/ppp.log | cut -c 5-1000 |  sed 's/2012 ://g' ; "
	//
 //	."tail -n $TailLines /var/log/krb5kdc/kdc.log.0; "
 //."tail -n $TailLines /var/log/secure.log; "
//	."tail -n $TailLines /var/log/appfirewall.log; "
//	


//print "command=$LogsCombinedCommand<br>";
//$LogsCombined=`$LogsCombinedCommand`;
 



//$LogsCombined=combine_sameprefixed_lines($LogsCombined);
$LogsCombined=remove_duplicate_lines($LogsCombined);
//$LogsCombined=combine_sameprefixed_lines($LogsCombined);

//print $LogsCombined."\n\n";

$Out=$LogsCombined;
/*
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

$Out="";
$c=0;
$LastText="";
foreach(split("\n",$LogsCombined) as $Line){
	$Out.= substr($Line,0,200)."\n";
}

$FinalLines=split("\n",$Out);

$start=0;
//if(sizeof($FinalLines)>$Lines){
	//$start=sizeof($FinalLines)-$Lines;
//}
//print "start=$start so=".sizeof($FinalLines)."\n";

for($i=$start;$i<sizeof($FinalLines);$i++){
	//print "$i: ";
	print $FinalLines[$i]."\n";
}
print "\n";


*/


dse_shutdown();

?>