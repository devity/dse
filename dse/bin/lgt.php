#!/usr/bin/php
<?
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");

$Lines=10;
$MinutesBack=60;
$NumberOfBytesSameLimit=13;

$shortopts  = "";
$shortopts .= "n:";  // Required value
$shortopts .= "m:";  // Required value
$shortopts .= "v:";  // Required value
$shortopts .= "t::"; // Optional value
$shortopts .= "i"; // These options do not accept values

$longopts  = array();
$options = getopt($shortopts, $longopts);

if($options['v']){
	$vars[Verbosity]=$options['v'];
}
if($options['n']){
	$Lines=$options['n'];
}
if($options['m']){
	$MinutesBack=$options['m'];
}

$CharsWide=cbp_get_screen_width()-19;

$vars['s2t_abvr']=TRUE;
$Intermingle=TRUE;

$SudoReplace="s/sudo/SUDO/g";

$TailLines=$Lines;

$LogsCombined="";

dpv(2,"Using Log Files: ".$vars['DSE']['LGT_LOG_FILES']."\n");
foreach (split(",",$vars['DSE']['LGT_LOG_FILES']) as $LogFile ){
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
						$Ago=pad(seconds_to_text(time()-$Time),6);
					}else{
						$Ago="";
					}
					
					if($Time<=0 || $Time>$StartTime){
						$L=substr($L,0,$CharsWide);
						foreach($vars['DSE']['RedWords'] as $RedWord) $L=str_ireplace($RedWord,colorize($RedWord,"red"),$L);
						foreach($vars['DSE']['GreenWords'] as $GreenWord) $L=str_ireplace($GreenWord,colorize($GreenWord,"green"),$L);
						foreach($vars['DSE']['BlueWords'] as $BlueWord) $L=str_ireplace($BlueWord,colorize($BlueWord,"blue"),$L);
						foreach($vars['DSE']['MagentaWords'] as $PurpleWord) $L=str_ireplace($PurpleWord,colorize($PurpleWord,"purple"),$L);
						foreach($vars['DSE']['YellowWords'] as $YellowWord) $L=str_ireplace($YellowWord,colorize($YellowWord,"yellow"),$L);
						foreach($vars['DSE']['CyanWords'] as $YellowWord) $L=str_ireplace($YellowWord,colorize($YellowWord,"yellow"),$L);
			
						if($Intermingle){
							if($Time){
								$Intermingled[$Time]="$LogFileNameColorized $Ago  $L\n";
							}else{
								print "$LogFileNameColorized $Ago  $L\n";;
							}
						}else{
							if((!$PrintedThisLogFileName)) { $LogsCombined.=colorize($LogFile.": ------\n","cyan"); $PrintedThisLogFileName=TRUE; }
							$LogsCombined.= "$Ago  $L\n";
						}
					}
				}
			}
		}
	}
}
if($Intermingle){
	ksort($Intermingled); foreach($Intermingled as $L) print $L;
}else{
	print $LogsCombined;
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



?>