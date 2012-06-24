#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
$vars['Verbosity']=1;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="img2txt";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="converts many image formats to a few text/ansi/html version";
$vars['DSE']['DSE_DSE_VERSION']="v0.01b";
$vars['DSE']['DSE_DSE_VERSION_DATE']="2012/06/24";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

$vars['ScriptHeaderShow']=TRUE;

$parameters_details = array(
  //array('l','log-to-screen',"log to screen too"),
 // array('','log-show:',"shows tail of log ".$CFG_array['LogFile']."  argv1 lines"),
  array('h','help',"this message"),
  array('q','quiet',"same as --verbosity 0"),
  array('v:','verbosity:',"0=none 1=some 2=more 3=debug"),
  //array('s','status',"prints status file".$CFG_array['StatusFile']),
  //array('e','edit',"backs up and launches a vim of ".$vars['DSE']['PANIC_CONFIG_FILE']),
  //array('c','config-show',"prints contents of ".$vars['DSE']['PANIC_CONFIG_FILE']),
 // array('d:','daemon:',"manages the checking daemon. options: [start|stop|restart|status]"),
 );
$vars['parameters']=dse_cli_get_paramaters_array($parameters_details);
$vars['Usage']=dse_cli_get_usage($parameters_details);
$vars['argv_origional']=$argv;

dse_cli_script_start();
print "\n\n\n";
dse_cli_script_header();
	
foreach (array_keys($vars['options']) as $opt) switch ($opt) {
	case 'l':
	case 'log-to-screen':
		$vars['DSE']['LOG_TO_SCREEN']=TRUE;
		dpv(2,"Logging to screen ON\n".$vars['DSE']['LOG_TO_SCREEN']);
		$vars['LOG_TO_SCREEN']=TRUE;
		break;
	case 'log-show':
		if($vars['options'][$opt]) $Lines=$vars['options'][$opt]; else $Lines=$vars['DSE']['LOG_SHOW_LINES'];
		$Command="tail -n $Lines ".$CFG_array['LogFile'];
		print `$Command`;
		//exit(0);
		break;
	case 'q':
	case 'quiet':
		$Quiet=TRUE;
		$vars['ScriptHeaderShow']=FALSE;
		$vars['Verbosity']=0;
		break;
  	case 'v':
	case 'verbosity':
		$vars['Verbosity']=$vars['options'][$opt];
		dpv(2,"Verbosity set to ".$vars['Verbosity']."\n");
		break;
	case 's':
  	case 'status':
		if($RunningPID>0){
			print "DLB Daemon is RUNNING PID=$RunningPID\n";
		}else{
			print "DLB Daemon is NOT RUNNING!\n";
		}
		dpv(1,dse_file_get_contents($CFG_array['StatusFile']));
		//exit(0);
		break;
	case 'h':
  	case 'help':
		print $vars['Usage'];
		//exit(0);
		break;
	case 'e':
	case 'edit':
		$Message="Backing up ".$vars['DSE']['PANIC_CONFIG_FILE']." and launcing in vim:\n";
		dpv(1,$Message);
		dse_log($Message);
		passthru("/dse/bin/vibk ".$vars['DSE']['PANIC_CONFIG_FILE']." 2>&1");
		//exit(0);
		break;
	case 'c':
  	case 'config-show':
		print dse_file_get_contents($vars['DSE']['PANIC_CONFIG_FILE']);
		//exit(0);
		break;
}

foreach (array_keys($vars['options']) as $opt) switch ($opt) {
  	
  	case 'd':
	case 'daemon':
		$CFG_array=dse_read_config_file($vars['DSE']['PANIC_CONFIG_FILE']);
		
		switch($vars['options'][$opt]){
			case 'restart':
				if($RunningPID>0){
					$r=`kill $RunningPID 2>&1`;
					dpv(1,"Killing process PID $RunningPID\n");
					dse_log("DLB stop. Killing process PID $RunningPID");
				}
				dse_dlb_daemon($CFG_array);
				$DidSomething=TRUE;
				break;	
			case 'start':
				if($RunningPID>0){
					dpv(1,"DLB Already Running as PID $RunningPID!\n");
				}else{
					dse_dlb_daemon($CFG_array);
				}
				$DidSomething=TRUE;
				break;	
			case 'stop':
				if($RunningPID>0){
					$r=`kill $RunningPID 2>&1`;
					dpv(1, "Killing process PID $RunningPID\n");
					dse_log("DLB stop. Killing process PID $RunningPID");
				}else{
					dpv(1, "DLB Not Running!\n");
				}
				$DidSomething=TRUE;
				break;	
			case 'status':
				if($RunningPID>0){
					dpv(0, "DLB Running as PID $RunningPID!\n");
					dpv(1,print "Status File: ".$CFG_array['StatusFile']."  ---------------------___________\n");
					print dse_file_get_contents($CFG_array['StatusFile'])."\n";
				}else{
					print "DLB Not Running!\n";
				}
				$DidSomething=TRUE;
				break;	
		}
		break;
}

$CharsWide=200;
$InFile=$argv[1];
if($argv[2]){
	$OutFile=$argv[2];
}else{
	$OutFile="out.ansi";
}
$Command="identify -format \"%w x %h\" $InFile";
$r=`$Command`;
print "Command: $Command\n $r\n";
list($W,$H)=split(" x ",$r);
$W=trim($W);
$H=trim($H);
print "$InFile $W x $H\n";
$Scale=$CharsWide/$W;
print "Scale: $Scale\n";
$Wn=intval($Scale*$W);
$Hn=intval(($Scale*$H)*(5/9));
print "out characters $Wn x $Hn\n";


$Command="convert -sample ${Wn}x${Hn}! $InFile out.txt";
$r=`$Command`;
print "Command: $Command\n $r\n";

$raw=dse_file_get_contents("out.txt");
//print $raw;
$raw=str_replace("rgba","rgb",$raw);
$last_x=0;
$high_x=0;
$high_y=0;
foreach(split("\n",$raw) as $L){
	$x=strcut($L,"",",");
	$y=strcut($L,",",":");
	$rgb=strcut($L,"rgb(",")");
	//if($x!=$last_x){
		$rbg_array[$y][$x]=split(",",$rgb);
	//}
	//print "$rgb ";
	if($x>$high_x) $high_x=$x;
	if($y>$high_y) $high_y=$y;
}

print "high_x=$high_x high_y=$high_y \n";
//print_r($rbg_array);

foreach($rbg_array as $row){
	foreach($row as $p){
		print img2txt_pixel2printable($p);
	}
	print "\n";
}



//$Command="convert -sample ${Wn}x${Hn} $InFile out.txt";
//$r=`$Command`;
//print "Command: $Command\n $r\n";

if($DidSomething){
	if(!$Quiet && !$DoSetEnv){
		dpv(1, getColoredString($vars['DSE']['SCRIPT_NAME']." Done. Exiting (0)","black","green"));
		$vars['shell_colors_reset_foreground']='';	print getColoredString("\n","white","black");
	}
	if(!$NoExit) exit(0);
}else{
	if(!$Quiet && !$DoSetEnv){
		dpv(1, getColoredString("Nothing to do! try --help for usage. ".$vars['DSE']['SCRIPT_NAME']." Done. Exiting (-1)","pink","black"));
		$vars['shell_colors_reset_foreground']='';	print getColoredString("\n","white","black");
	}
	if(!$NoExit) exit(-1);
}


exit(0);


function img2txt_pixel2printable($p){
	global $vars;
	if(!is_array($p)){
		return "";
	}
	$Coverage=" '+~:=o*%O@#$000";
	$CoverageSize=strlen($Coverage)-2;
	
	list($r,$g,$b)=$p;
	$Brightness=(($r+$g+$b)/(256*3));
	
	/* not working
	$BrighnessRandFactor=.011;
	if(TRUE){
		if(rand(0,1)==1) $PN=-1; else $PN=1;
		$Adjust=$PN*rand(0,$BrighnessRandFactor*$Brightness*100);	
		//if(rand(0,200)==2) print "\n	$Adjust=$PN*rand(0,$BrighnessRandFactor*$Brightness*100*100); adjust=$Adjust	\n";
		//print "Brightness=$Brightness adjust=$Adjust ";
		$Brightness+=$Adjust;
	}*/

	$BrightnessCoverage=intval($Brightness*$CoverageSize);
	if($BrightnessCoverage<0) $BrightnessCoverage=0;
	if($BrightnessCoverage>$CoverageSize-1) $BrightnessCoverage=$CoverageSize-1;
	
	$Char=$Coverage[$BrightnessCoverage];
	
	$color="";
	$f=.3; $f_inc=.01;
	while( (!$color) && $f>0){
		//if(rand(0,300)==1) print "\n if($r>$g/$f && $r>$b/$f)";
		if($r>$g/$f && $r>$b/$f){
			$color="red";
		}elseif($g>$r/$f && $g>$b/$f){
			$color="green";
		}elseif($b>$r/$f && $b>$g/$f){
			$color="blue";
		}elseif($r>$b/$f && $g>$b/$f){
			$color="yellow";
		}
		$f-=$f_inc;
	}
	if($color){
		$Char=getColoredString("$Char",$color,"black");
	}else{
		$Char=getColoredString("$Char","white","black");
	}
	return $Char;
}





