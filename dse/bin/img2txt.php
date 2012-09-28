#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
$vars['Verbosity']=0;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="img2txt";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="converts many image formats to a few text/ansi/html version";
$vars['DSE']['SCRIPT_VERSION']="v0.01b";
$vars['DSE']['SCRIPT_VERSION_DATE']="2012/06/24";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
$vars['DSE']['SCRIPT_COMMAND_FORMAT']="";
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

$vars['ScriptHeaderShow']=TRUE;
$speed="normal";
$CharsWide=cbp_get_screen_width();
$Columns=intval($CharsWide/50);


$Coverage=" '`.,-+~:=co*s%@$#O08GM";
$Coverage=" '.,:;-+=*os@$08M";
$Coverage=" '.,:;-o$08M";
$Coverage="  '`.,,::;;~-+()[]{}=co*szxmwe%TO@Y#A8G0M";
$Coverage="  `..,,::;;~~--++==v*szxme(())[[]]{}}%@TOYG#A80M";
$Coverage=" .,~-+=m@OG0M";
$Coverage="M0GO@m=+-~,. ";
$Coverage=" .+mO0";
$Coverage=" .=8";
$Coverage=" .o0";
$Coverage=" .~/o0";
$Coverage=" O";
$Coverage="  ;%##NMMM";
$Coverage="   ...,,;";
$Coverage="     ....,,,:;";
$Coverage="o%nm\$xNM#";
$Coverage=" .-/OM";



$CoverageSlow="  `..,,::;;~~--++==v*szxme(())[[]]{}}%@TOYGXZ#A80NMMM";
$CoverageNormal=" .-/OM";
$CoverageFast="&";
$Coverage=$CoverageNormal;

$FVal=.96;
$parameters_details = array(
  //array('l','log-to-screen',"log to screen too"),
 // array('','log-show:',"shows tail of log ".$CFG_array['LogFile']."  argv1 lines"),
  array('h','help',"this message"),
  array('q','quiet',"same as --verbosity 0"),
  array('v:','verbosity:',"0=none 1=some 2=more 5=debug"),
  array('s:','speed:',"arg options: [slow|normal(default)|fast]"),
  array('w:','width:',"width in characters"),
  array('d:','columns:',"# of columns to display on screen if multiple files"),
  array('o','out-file',"saves to outfile <same_base_name>_<width>.ansi"),
  array('c','cache',"adds a -o samename.ansi and prints it instead if there or if whater -o file's .ansi exists'"),
 // array('f:','f-val:',"an color picking adjuctment. argv options = number 0.1 to 10"),
  //array('s','status',"prints status file".$CFG_array['StatusFile']),
  //array('e','edit',"backs up and launches a vim of ".$vars['DSE']['PANIC_CONFIG_FILE']),
  //array('c','config-show',"prints contents of ".$vars['DSE']['PANIC_CONFIG_FILE']),
 // array('d:','daemon:',"manages the checking daemon. options: [start|stop|restart|status]"),
 );
$vars['parameters']=dse_cli_get_paramaters_array($parameters_details);
$vars['Usage']=dse_cli_get_usage($parameters_details);
$vars['Usage'].= "Test sample image with:    /dse/bin/img2txt -v5 -s fast /dse/images/penguin.jpg\n\n";
$vars['argv_origional']=$argv;

dse_cli_script_start();
	
foreach (array_keys($vars['options']) as $opt) switch ($opt) {
	case 'd':
	case 'columns':
		$Columns=$vars['options'][$opt];
		break;
	case 'w':
	case 'width':
		$CharsWide=$vars['options'][$opt];
		break;
	case 'f':
	case 'f-val':
		$FVal=$vars['options'][$opt];
		break;
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
	case 'h':
  	case 'help':
		print $vars['Usage'];
		//exit(0);
		break;
	case 'c':
  	case 'cache':
		$vars['img2txt__use_cache']=TRUE;
		//exit(0);
		break;
	case 's':
  	case 'speed':
		if($vars['options'][$opt]){
			$Speed=$vars['options'][$opt];
			dpv(2,"Speed set to $Speed\n");
		}else{
			dpv(0,"Speed option missing argument! Ignoring. Using speed $Speed\n");
		}
		switch($Speed){
			case 'normal':
				$Coverage=$CoverageNormal;
				break;
			case 'fast':
				$Coverage=$CoverageFast;
				break;
			case 'slow':
				$Coverage=$CoverageSlow;
				break;
		}
		break;
	case 'o':
  	case 'out-file':
		if($vars['options'][$opt]){
			$OutFile=$vars['options'][$opt];
		}
		$DoOutFiles=TRUE;
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


	
	
	
	
if($vars[Verbosity]>1){
	print "\n\n\n";
	dse_cli_script_header();
}
$PP=img2txt_build_possibles_map();
 
$NumFiles=sizeof($argv)-1;
if($NumFiles==1){
	$InFile=$argv[1];
	$Extension=strcut(basename($InFile),".");
	$FileBaseName=str_replace(".$Extension","", basename($InFile));
	$CacheFile="$FileBaseName"."_$CharsWide.ansi";
	if($DoOutFile || $vars['img2txt__use_cache']){
		$OutFile=$CacheFile;
	}else{
		$OutFile="";
	}
	dpv(1,"Input File: $InFile\n");
	print img2txt_process_file($InFile,$OutFile,$CharsWide,$FVal);
}elseif($NumFiles>0){
	if($NumFiles<$Columns){
		$Columns=$NumFiles;
	}
	dpv(5,"Columns=$Columns\nNumFiles=$NumFiles\n");
	$CharsWide=intval($CharsWide/$Columns);
	$Spacer="";
	for($i=0;$i<$CharsWide;$i++) $Spacer.=" ";
	$bc=getColoredString("","white","black");
	for($fi=1;$fi<sizeof($argv);$fi++){
		dpv(5,"for loop for(fi=1;fi<sizeof(argv);$fi++){\n");
		
		$InFile=$argv[$fi];
		$Extension=strcut(basename($InFile),".");
		$FileBaseName=str_replace(".$Extension","", basename($InFile));
		$CacheFile="$FileBaseName"."_$CharsWide.ansi";
		if($DoOutFile || $vars['img2txt__use_cache']){
			$OutFile=$CacheFile;
		}else{
			$OutFile="";
		}
		
		$PreLines="";
		if($vars[Verbosity]>=1){
			if(strlen("Input File: $InFile")<=$CharsWide){
				$PreLines.= pad("Input File: $InFile",$CharsWide)."\n";
			}else{
				$PreLines.= pad($InFile,$CharsWide)."\n";
			}	
		}
		
		$row[($fi-1)%$Columns]=split("\n",$PreLines . img2txt_process_file($InFile,$OutFile,$CharsWide,$FVal));
		dpv(5,"got img2txt_process_file\n");
		
		if(($fi-1)%$Columns==$Columns-1){
			dpv(5,"pringing row\n");
			$Tallest=0;	for($ti=0;$ti<$Columns;$ti++) if(sizeof($row[$ti])>$Tallest) $Tallest=sizeof($row[$ti]);
			for($r=0;$r<$Tallest-1;$r++){
				for($i=0;$i<$Columns;$i++){
					if($row[$i][$r]!="" && strlen($row[$i][$r])>10){
						print $row[$i][$r];
					}else{
						print $Spacer;
					}
				}
				print "\n";
			}
			for($i=0;$i<$Columns;$i++){
				$row[$i]="";
			}	
		}
	}
	dpv(5,"post pringing row\n");
	$Tallest=0;	for($ti=0;$ti<$Columns;$ti++) if(sizeof($row[$ti])>$Tallest) $Tallest=sizeof($row[$ti]);
	for($r=0;$r<$Tallest-1;$r++){
		for($i=0;$i<$Columns;$i++){
			if($row[$i][$r]!="" && strlen($row[$i][$r])>10){
				print $row[$i][$r];
			}else{
				print $Spacer;
			}
		}
		print "\n";
	}
}
exit(0);

function img2txt_rbg_pair_distance($rbg1,$rbg2){
	global $vars,$Coverage;
	list($r1,$g1,$b1)=$rbg1;
	list($r2,$g2,$b2)=$rbg2;
	$str="($r1)($g1)($b1)($r2)($g2)($b2)";
//	if(isset($vars[rgb_dist_cache][$str])){
//		return $vars[rgb_dist_cache][$str];
//	}
	$Distance=sqrt( ($r1-$r2)*($r1-$r2)+($g1-$g2)*($g1-$g2)+($b1-$b2)*($b1-$b2) );
	///$vars[rgb_dist_cache][$str]=$Distance;
	return $Distance;
}

function img2txt_process_file($InFile,$OutFile,$CharsWide,$FVal){
	global $vars,$PP,$Coverage;
	dpv(5,"starting img2txt_process_file($InFile,$OutFile,$CharsWide,$FVal)\n");
	if($vars['img2txt__use_cache'] && dse_file_exists($OutFile)){
		dpv(2,"Found cache $OutFile printing it.\n");
		return `cat $OutFile`;
	}
	foreach($PP as $P){
		list($r,$g,$b,$Char,$ForgroundName,$BackgroundName,$ForgroundColorParts,$BackgroundColorParts)=$P;
		//print "$r,$g,$b \n";
	}
	$Command="identify -format \"%w x %h\" $InFile";
	$r=`$Command`;
	//print "Command: $Command\n $r\n";
	list($W,$H)=split(" x ",$r);
	$W=trim($W);
	if(!$W){
		dpv(0,"error. imagemagick's identify did not give width. corrupt or unknown format?\n");
		return("");
	}
	$H=trim($H);
	//print "$InFile $W x $H\n";
	$Scale=$CharsWide/$W;
	//print "Scale: $Scale\n";
	$Wn=intval($Scale*$W);
	$Hn=intval(($Scale*$H)*(40/100));
	//print "out characters $Wn x $Hn\n";
	
	
	$Command="convert -sample ${Wn}x${Hn}! $InFile out.txt";
	$r=`$Command`;
	//print "Command: $Command\n $r\n";
	
	$raw=dse_file_get_contents("out.txt");
	`rm out.txt`;
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
	
	//print "high_x=$high_x high_y=$high_y \n";
	//print_r($rbg_array);
	$tbr="";
	foreach($rbg_array as $x=>$row){
		foreach($row as $y=>$p){
			//print "\n x=$x y=$y\n";
			$out= img2txt_pixel2printable($p,$x,$y,$FVal);
			$tbr.=$out;
			//print $out;
		}
		//print "\n";
		$tbr.="\n";
	}
	//print getColoredString("","white","black","0",TRUE);
	$tbr.=getColoredString("","white","black","0",TRUE);
	if($OutFile){
		dse_file_put_contents($OutFile,$tbr);
		if($vars[Verbosity]>=1){
			dpv(2,"Wrote Output File: $OutFile\n");
		}
	}
	dpv(5,"exiting img2txt_process_file($InFile,$OutFile,$CharsWide,$FVal)\n");
	return $tbr;
}

function img2txt_find_best_match_in_map($rgb){
	global $vars,$PP,$Coverage;
	list($r,$g,$b)=$rgb;
	$str="($r)($g)($b)";
	if(is_array($vars[rgb_best_cache][$str])){
	//	print_r($vars[rgb_best_cache]);
		return ( $vars[rgb_best_cache][$str]);
	}
	$LowestDistance=1000000;
	$BestMatch="";
	foreach($PP as $P){
		list($r,$g,$b,$Char,$ForgroundName,$BackgroundName,$ForgroundColorParts,$BackgroundColorParts)=$P;
		$Distance=img2txt_rbg_pair_distance($rgb,array($r,$g,$b));
		if($Distance<$LowestDistance){
			$LowestDistance=$Distance;
		//	print "\n $rbg,array($r,$g,$b) D=$Distance < Dl= $LowestDistance \n";
			$BestMatch=array($Char,$ForgroundName,$BackgroundName);
		}
	}
	//print "bm="; print_r($BestMatch); print "\n";
	$vars[rgb_best_cache][$str]=$BestMatch;
	return $BestMatch;
}

function img2txt_build_possibles_map(){
	global $vars,$Coverage;
	
	dpv(5,"starting img2txt_build_possibles_map()\n");
	$CoverageSize=strlen($Coverage);
	$Colors=array("black"=>array(0,0,0),"white"=>array(255,255,255),
	"red"=>array(255,0,0),"green"=>array(0,255,0),"blue"=>array(0,0,255)
		,"yellow"=>array(255,255,0),"cyan"=>array(0,255,255),"magenta"=>array(255,0,255));
	//print_r($Colors);
	$CoveragePercentMax=70;
	$PP=array();
	foreach($Colors as $BackgroundName=>$BackgroundColorParts){
		//print "safsdaf";
		foreach($Colors as $ForgroundName=>$ForgroundColorParts){
			for($Ci=0;$Ci<$CoverageSize;$Ci++){
				//print $Ci;
				$LetterCoveragePercent=$CoveragePercentMax*($Ci/$CoverageSize);
				$BackgroundVisablePercent=(100-$LetterCoveragePercent)/100;
				$LetterCoveragePercent=$LetterCoveragePercent/100;
				$r=intval(($Colors[$BackgroundName][0]*$BackgroundVisablePercent+$Colors[$ForgroundName][0]*$LetterCoveragePercent)/1);
				$g=intval(($Colors[$BackgroundName][1]*$BackgroundVisablePercent+$Colors[$ForgroundName][1]*$LetterCoveragePercent)/1);
				$b=intval(($Colors[$BackgroundName][2]*$BackgroundVisablePercent+$Colors[$ForgroundName][2]*$LetterCoveragePercent)/1);
				$Char=$Coverage[$Ci];
				//print $Char;
				$PP[]=array($r,$g,$b,$Char,$ForgroundName,$BackgroundName,$ForgroundColorParts,$BackgroundColorParts);
				if(rand(0,300)==3){
					//print getColoredString($Char,$ForgroundName,$BackgroundName);
				//	print "sllllll=".$Colors[$BackgroundName][0];
					// print "\n ==>$LetterCoveragePercent/$BackgroundVisablePercent rgb=$r,$g,$b char=$Char,$ForgroundName,$BackgroundName,$ForgroundColorParts,$BackgroundColorParts\n";
			
				}
			}
		}
	}

	dpv(5,"exiting img2txt_build_possibles_map()\n");
	return($PP);
	  
}
function img2txt_pixel2printable($p,$x,$y,$FVal){
	global $vars,$PP,$Coverage;
	if(!is_array($p)){
		return "";
	}
	
	$r=img2txt_find_best_match_in_map($p);
	list($Char,$ForgroundColor,$BackgroundColor)=$r;
	//print getColoredString("r=$r\n","white", "black");
//	print "   list($Char,$ForgroundColor,$BackgroundColor)= \n"; 
	return getColoredString("$Char",$ForgroundColor,$BackgroundColor);
	/*
	$Coverage=" '`.,-+~:=co*s%@$#O08GM";
	$CoverageSize=strlen($Coverage);
	$Coverage.="00"; //pad for buffer overrun
	list($r,$g,$b)=$p;
	$Brightness=(($r+$g+$b)/(256*3));
	$Brightness=number_format($Brightness,2);
	

	$BrightnessCoverage=intval($Brightness*$CoverageSize);
	if($BrightnessCoverage<0) $BrightnessCoverage=0;
	if($BrightnessCoverage>$CoverageSize-1) $BrightnessCoverage=$CoverageSize-1;
	
	$Char=$Coverage[$BrightnessCoverage];
	$NChar=$Coverage[$CoverageSize-$BrightnessCoverage];
	
	$debug_x=10;
	$debug_y=2;
	
	$color="";
//	$f=.3; $f_inc=.01;
	//$f=.95; $f_inc=.001;
	$f=$FVal	; $f_inc=$f/100;
	$L=0;
	$cpp=70;
	while( (!$color) && $f>0){
		$L++;
			
		if($g>$r/$f && $b>$r/$f && rand(0,99)<$cpp+10){
			if(abs($g-$b)<$b/10){
				//$color="cyan";
			}elseif($g>$b*.8){
				if($g>$r*.8){
					$color="green";
				}else{
					$color="yellow";
				}
			}else{
				$color="blue";
			}
		}elseif($g>$r/$f && $b>$r/$f && rand(0,99)<$cpp+6){
			$color="cyan";
		}elseif($r>$b/$f && $g>($b)/$f && rand(0,99)<$cpp+3){
			if($g>$r*.8){
				$color="green";
			}else{
				$color="yellow";
			}
		}elseif($r>$g/$f && $r>$b/$f && rand(0,99)<$cpp){
			$color="red";
		}elseif($g>$r/$f && $g>$b/$f && rand(0,99)<$cpp-3){
			$color="green";
		}elseif($b>$r/$f && $b>$g/$f && rand(0,99)<$cpp-6){
			$color="blue";
		}elseif($r>$g/$f && $b>$g/$f && rand(0,99)<$cpp-10){
			$color="purple";
		}
		$f-=$f_inc;
		if($x==$debug_x && $y==$debug_y){
			$color="red"; $Char="[O]";
			//print "\nif($x==$debug_x && $y==$debug_y){";
		}
		if($x-1==$debug_x && $y-1==$debug_y){
			// $Char.="\nL=$L f=$f \{$r,$g,$b:$Brightness ($r>$b/$f && $g>$b/$f) ($r>".$b/$f." && $g>".$b/$f.") C=$color}";
			 //$color="yellow";
		}
		//print "\nif($x==$debug_x && $y==$debug_y){";
	}
//$Char=" $Brightness";
	if($Brightness<.4){
		$Type=2;
	}elseif($Brightness>.8){
		$Type=1;
	}else{
		$Type=0;
	}
//	$Char=$Type;
	if($color){
		if($Type==1){
			$Char=getColoredString("$Char","black",$color,$Type);
		}else{
			$Char=getColoredString("$Char",$color,"black",$Type);
		}
	}else{
		$Char=getColoredString("$Char","white","black");
	}
	return $Char;*/
}





