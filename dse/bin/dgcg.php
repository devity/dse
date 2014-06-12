#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
set_time_limit(0);
ini_set("memory_limit","5000M");

include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");

$vars['Verbosity']=0;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="DSE G Code Generator";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="basic g-code file generator & manipulator";
$vars['DSE']['SCRIPT_VERSION']="v0.02b";
$vars['DSE']['SCRIPT_VERSION_DATE']="2014/06/08";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
$vars['DSE']['SCRIPT_COMMAND_FORMAT']="";
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******



$vars['DGCG']['Units']="in";
$vars['DGCG']['Tool']['Diameter']=1;
$vars['DGCG']['Tool']['Feed']=10;
$vars['DGCG']['Current']['X']=0;
$vars['DGCG']['Current']['Y']=0;
$vars['DGCG']['Current']['Z']=0;
$vars['DGCG']['Program']['Image']['Stereo']=FALSE	;
$vars['DGCG']['Program']['Image']['PixelsPerUnit']=50;
$vars['DGCG']['Program']['Image']['FileName']="dgcg_out.jpg";
$vars['DGCG']['Program']['Image']['Margin']=50;
$vars['DGCG']['Program']['Image']['Width']=1000;
$vars['DGCG']['Program']['Image']['Height']=1000;
$vars['DGCG']['Program']['Image']['ShowMoves']=TRUE;

global $CFG_array;
$CFG_array=array();
//$CFG_array['QueriesMade']=0;
//$CFG_array=dse_read_config_file($vars['DSE']['DLB_CONFIG_FILE'],$CFG_array);	

			

$parameters_details = array(
 // array('l','log-to-screen',"log to screen too"),
 // array('','log-show:',"shows tail of log ".$CFG_array['LogFile']."  argv1 lines"),
  array('h','help',"this message"),
  array('q','quiet',"same as --verbosity 0"),
  array('d','demo',"output demo gcode and image"),
  array('g','grid',"outputs a graph-paper-type grid of: arg1 x arg2 by arg3 deep"),
  array('i','grating',"outputs a grid of holes arg1 x arg2 by arg3 deep with holes arg4 diameter and spaced by arg5"),
  array('z','guage-chart',"AWG guage chart"),
  array('u:','units:',"mm, in"),
  array('f:','file:',"commands from input .dngc script file"),
  array('t:','tool-diameter:',"tool diameter in units"),
  array('v:','verbosity:',"0=none 1=some 2=more 3=debug"),
  array('o:','gcode-outfile:',"outfile to save final g-code to"),
  array('i:','image-outfile:',"outfile to save final image to"),
  
);
$vars['parameters']=dse_cli_get_paramaters_array($parameters_details);
$vars['Usage']=dse_cli_get_usage($parameters_details);
$vars['argv_origional']=$argv;
dse_cli_script_start();
		
$BackupBeforeUpdate=TRUE;
foreach (array_keys($vars['options']) as $opt) switch ($opt) {
	case 'q':
	case 'quiet':
		$Quiet=TRUE;
		$vars['Verbosity']=0;
		break;
  	case 'v':
	case 'verbosity':
		$vars['Verbosity']=$vars['options'][$opt];
		dpv(2,"Verbosity set to ".$vars['Verbosity']."\n");
		break;
  	case 'o':
	case 'gcode-outfile':
		$OutFile=$vars['options'][$opt];
		dpv(2,"Outfile set to $OutFile\n");
		break;
  	case 'i':
	case 'image-outfile':
		$vars['DGCG']['Program']['Image']['FileName']=$vars['options'][$opt];
		dpv(2,"Outfile set to $OutFile\n");
		break;
  	case 'f':
	case 'file':
		$DNGC_Filename=$vars['options'][$opt];
		dgcg_dngc_file_process($DNGC_Filename);
		exit();
		break;
  	case 'u':
	case 'units':
		$vars['DGCG']['Units']=$vars['options'][$opt];
		dpv(2,"Units set to ".$vars['DGCG']['Units']."\n");
		break;
  	case 't':
	case 'tool-diameter':
		$vars['DGCG']['Tool']['Diameter']=$vars['options'][$opt];
		dpv(2,"Tool Diameter set to ".$vars['DGCG']['Tool']['Diameter']."\n");
		break;
}

$vars['DGCG']['Tool']['Radius']=$vars['DGCG']['Tool']['Diameter']/2;
$vars['DGCG']['Tool']['PassStep']=$vars['DGCG']['Tool']['Diameter']/3;


foreach (array_keys($vars['options']) as $opt) switch ($opt) {
	case 'h':
  	case 'help':
		print $vars['Usage'];
		exit(0);
	case 'd':
  	case 'demo':
		dgcg_demo();
		exit(0);
	case 'g':
  	case 'grid':
		$Width=$argv[1];
		$Height=$argv[2];
		$Depth=$argv[3];
		dgcg_grid($Width,$Height,$Depth);
		exit(0);
	case 'i':
  	case 'grating':
		$Width=$argv[1];
		$Height=$argv[2];
		$Depth=$argv[3];
		$Diameter=$argv[4];
		$Spacing=$argv[5];
		dgcg_grating($Width,$Height,$Depth,$Diameter,$Spacing);
		exit(0);
}


function dgcg_dngc_file_process($DNGC_Filename){
	global $vars,$OutFile;

	
	
	if($DNGC_Filename){
		dpv(2,"Processing $DNGC_Filename\n");
		
		$Xc=0; $Yc=0; $Zc=0;
		$vars['DGCG']['Program']['Body']="";
		dgcg_program_start();
//		dgcg_home();
	
	
		$Lines=explode("\n",`cat $DNGC_Filename`);
		foreach($Lines as $L){
			$L=strcut($L,"","#");
			$L=trim($L);
			if($L){
				dpv(3,"DNGC infile line: $L\n");
				$L=strtolower($L);
				$La=explode(" ",$L);
				switch($La[0]){
					case 'go':
						if(sizeof($La)==4){
							$Xp=substr($La[1],1);
							$Yp=substr($La[2],1);
							$Zp=substr($La[3],1);
							dgcg_go($Xp,$Yp,$Zp);
						}else{
							for($Lai=0;$Lai<sizeof($La);$Lai++){
								$d=substr($La[$Lai],0,1);
								$p=substr($La[$Lai],1);
								switch($d){
									case 'x':
										dgcg_go_x($p);
										break;
									case 'y':
										dgcg_go_y($p);
										break;
									case 'z':
										dgcg_go_z($p);
										break;
								}
							}
						}
						break;
					case 'hole':
						/*$X=substr($La[1],1);
						$Y=substr($La[2],1);
						$Z=substr($La[3],1);
						$Diameter=substr($La[4],1);
						$Depth=substr($La[5],1);
						 * */
						$X=$La[1];
						$Y=$La[2];
						$Z=$La[3];
						$Diameter=$La[4];
						$Depth=$La[5];
						dgcg_hole($Y, $Y, $Z, $Diameter, $Depth);
						break;
					default:
						break;
				}
			}
		}
		
		
		dgcg_program_end();
		
		if($vars['DGCG']['Program']['Image']['FileName']){
			unlink($vars['DGCG']['Program']['Image']['FileName']);
		}
		dse_file_put_contents("/tmp/dgcg_convert_command.sh",$vars['DGCG']['Program']['convert_command']);
		$r=dse_exec("chmod a+x /tmp/dgcg_convert_command.sh",TRUE,TRUE);
		$r=dse_exec("/tmp/dgcg_convert_command.sh",TRUE,TRUE);
		if($OutFile){
			dse_file_put_contents($OutFile,$vars['DGCG']['Program']['Body']);
			print "G-code data saved to file: $OutFile\n";
		}else{
			print $vars['DGCG']['Program']['Body']."\n";
		}
		
		
	}else{
		dpv(0,"dgcg_dngc_file_process() error: no filename passed. returning.\n");
	}
}


function dgcg_grating($Width,$Height,$Depth,$Diameter,$Spacing){
	global $vars,$OutFile;
	dpv(3,"dgcg_grating($Width,$Height,$Depth,$Diameter,$Spacing);\n");	
	
	$vars['DGCG']['Program']['Body']="";
	
	dgcg_program_start();
	
	
	dgcg_home();
	
	$Rows=intval(($Height-$Spacing)/($Spacing+$Diameter));
	$Cols=intval(($Width-$Spacing)/($Spacing+$Diameter));
//	print "r=$Rows c=$Cols\n";
	dgcg_hole_grid($Width,$Height,$Rows,$Cols,$Diameter,$Depth);
	
	dgcg_home();
	
	dgcg_program_end();
	
	if($vars['DGCG']['Program']['Image']['FileName']){
		unlink($vars['DGCG']['Program']['Image']['FileName']);
	}
	dse_file_put_contents("/tmp/dgcg_convert_command.sh",$vars['DGCG']['Program']['convert_command']);
	$r=dse_exec("chmod a+x /tmp/dgcg_convert_command.sh",TRUE,TRUE);
	$r=dse_exec("/tmp/dgcg_convert_command.sh",TRUE,TRUE);
	if($OutFile){
		dse_file_put_contents($OutFile,$vars['DGCG']['Program']['Body']);
		print "G-code data saved to file: $OutFile\n";
	}else{
		print $vars['DGCG']['Program']['Body']."\n";
	}
}


function dgcg_grid($Width,$Height,$Depth){
	global $vars,$OutFile;
	
	
	$vars['DGCG']['Program']['Body']="";
	
	dgcg_program_start();
	
	
	dgcg_home();
	
//	dgcg_line($Width,0,0,$Depth);
	//dgcg_line($Width,$Height,0,$Depth);
	//dgcg_line(0,$Height,0,$Depth);
	//dgcg_line(0,0,0,$Depth);
	
	for($x=0;$x<=$Width;$x++){
		dgcg_go($x,0,0);
		dgcg_line($x,$Height,0,$Depth);
	}
	for($y=0;$y<=$Height;$y++){
		dgcg_go(0,$y,0);
		dgcg_line($Width,$y,0,$Depth);
	}
	
	dgcg_home();

	
	dgcg_home();
	
	dgcg_program_end();
	
	if($vars['DGCG']['Program']['Image']['FileName']){
		unlink($vars['DGCG']['Program']['Image']['FileName']);
	}
	dse_file_put_contents("/tmp/dgcg_convert_command.sh",$vars['DGCG']['Program']['convert_command']);
	$r=dse_exec("chmod a+x /tmp/dgcg_convert_command.sh",TRUE,TRUE);
	$r=dse_exec("/tmp/dgcg_convert_command.sh",TRUE,TRUE);
	if($OutFile){
		dse_file_put_contents($OutFile,$vars['DGCG']['Program']['Body']);
		print "G-code data saved to file: $OutFile\n";
	}else{
		print $vars['DGCG']['Program']['Body']."\n";
	}
}


function dgcg_home(){
	global $vars;
	dgcg_go(0,0,0);
}

function dgcg_demo(){
	global $vars,$OutFile;
	//0=AWG 1=in 2=mm 3=turns_per_in 4=turns_per_cm 5=area_kcmil 6=area_mm2 7=ohms/km 8=ohms/kFT
	$AWGChart=array(
	array(0),
	array(1,	0.2893,	7.348,	3.46,	1.36,	83.7,	42.4,	0.4066,	0.1239),
	array(2,	0.2576,	6.544,	3.88,	1.53,	66.4,	33.6,	0.5127,	0.1563),
	array(3,	0.2294,	5.827,	4.36,	1.72,	52.6,	26.7,	0.6465,	0.1970),
	array(4,	0.2043,	5.189,	4.89,	1.93,	41.7,	21.2,	0.8152,	0.2485),
	array(5,	0.1819,	4.621,	5.50,	2.16,	33.1,	16.8,	1.028,	0.3133),
	array(6,	0.1620,	4.115,	6.17,	2.43,	26.3,	13.3,	1.296,	0.3951),
	array(7,	0.1443,	3.665,	6.93,	2.73,	20.8,	10.5,	1.634,	0.4982),
	array(8,	0.1285,	3.264,	7.78,	3.06,	16.5,	8.37,	2.061,	0.6282),
	array(9,	0.1144,	2.906,	8.74,	3.44,	13.1,	6.63,	2.599,	0.7921),
	array(10,	0.1019,	2.588,	9.81,	3.86,	10.4,	5.26,	3.277,	0.9989),
	array(11,	0.0907,	2.305,	11.0,	4.34,	8.23,	4.17,	4.132,	1.260),
	array(12,	0.0808,	2.053,	12.4,	4.87,	6.53,	3.31,	5.211,	1.588),
	array(13,	0.0720,	1.828,	13.9,	5.47,	5.18,	2.62,	6.571,	2.003),
	array(14,	0.0641,	1.628,	15.6,	6.14,	4.11,	2.08,	8.286,	2.525),
	array(15,	0.0571,	1.450,	17.5,	6.90,	3.26,	1.65,	10.45,	3.184),
	array(16,	0.0508,	1.291,	19.7,	7.75,	2.58,	1.31,	13.17,	4.016),
	array(17,	0.0453,	1.150,	22.1,	8.70,	2.05,	1.04,	16.61,	5.064),
	array(18,	0.0403,	1.024,	24.8,	9.77,	1.62,	0.823,	20.95,	6.385),
	array(19,	0.0359,	0.912,	27.9,	11.0,	1.29,	0.653,	26.42,	8.051),
	array(20,	0.0320,	0.812,	31.3,	12.3,	1.02,	0.518,	33.31,	10.15),
	array(21,	0.0285,	0.723,	35.1,	13.8,	0.810,	0.410,	42.00,	12.80),
	array(22,	0.0253,	0.644,	39.5,	15.5,	0.642,	0.326,	52.96,	16.14),
	array(23,	0.0226,	0.573,	44.3,	17.4,	0.509,	0.258,	66.79,	20.36),
	array(24,	0.0201,	0.511,	49.7,	19.6,	0.404,	0.205,	84.22,	25.67),
	array(25,	0.0179,	0.455,	55.9,	22.0,	0.320,	0.162,	106.2,	32.37),
	array(26,	0.0159,	0.405,	62.7,	24.7,	0.254,	0.129,	133.9,	40.81),
	array(27,	0.0142,	0.361,	70.4,	27.7,	0.202,	0.102,	168.9,	51.47),
	array(28,	0.0126,	0.321,	79.1,	31.1,	0.160,	0.0810,	212.9,	64.90),
	array(29,	0.0113,	0.286,	88.8,	35.0,	0.127,	0.0642,	268.5,	81.84),
	array(30,	0.0100,	0.255,	99.7,	39.3,	0.101,	0.0509,	338.6,	103.2),
	array(31,	0.00893,	0.227,	112,	44.1,	0.0797,	0.0404,	426.9,	130.1),		
	array(32,	0.00795,	0.202,	126,	49.5,	0.0632,	0.0320,	538.3,	164.1),
	array(33,	0.00708,	0.180,	141,	55.6,	0.0501,	0.0254,	678.8,	206.9),
	array(34,	0.00630,	0.160,	159,	62.4,	0.0398,	0.0201,	856.0,	260.9),	
	array(35,	0.00561,	0.143,	178,	70.1,	0.0315,	0.0160,	1079,	329.0),	
	array(36,	0.00500,	0.127,	200,	78.7,	0.0250,	0.0127,	1361,	414.8),	
	array(37,	0.00445,	0.113,	225,	88.4,	0.0198,	0.0100,	1716,	523.1),	
	array(38,	0.00397,	0.101,	252,	99.3,	0.0157,	0.00797,	2164,	659.6),	
	array(39,	0.00353,	0.0897,	283,	111,	0.0125,	0.00632,	2729,	831.8),
	array(40,	0.00314,	0.0799,	318,	125,	0.00989,	0.00501,	3441,	1049),
	);
	$Pi=3.14159;
	
	
	$vars['DGCG']['Program']['Body']="";
	
	
	dgcg_program_start();
	
	$Depth=.25;
	$Z=0;
	
	$FontHeight=.5;
	dgcg_text(.5,9,$Z,"ABCDEFGHIJKLM",$Depth,$FontHeight);
	dgcg_text(.5,8.4,$Z,"NOPQRSTUVWXYZ",$Depth,$FontHeight);
	dgcg_text(.5,7.8,$Z,"()[]{}<>\\|/!?.,;:'\"",$Depth,$FontHeight);
	dgcg_text(.5,7.2,$Z,"0123456789-=+_@#$%^&*",$Depth,$FontHeight);
	
	
	
	
	dgcg_go(.5,.5,$Z);
	dgcg_line(8,.5,$Z,$Depth);
	
	dgcg_go(.5,5.5,$Z);
	dgcg_line(8,5.5,$Z,$Depth);
	
	$Diameter=5;
	$RadiansStart=3*$Pi/2;
	$RadiansStop=5*$Pi/2;
	dgcg_arc(8,3,0,$Diameter,$RadiansStart,$RadiansStop,$Depth);
	
	
	
	dgcg_go(.5,1.25,$Z);
	dgcg_line(8,1.25,$Z,$Depth);
	
	dgcg_go(.5,4.75,$Z);
	dgcg_line(8,4.75,$Z,$Depth);
	
	$Diameter=3.5;
	$RadiansStart=3*$Pi/2;
	$RadiansStop=5*$Pi/2;
	dgcg_arc(8,3,0,$Diameter,$RadiansStart,$RadiansStop,$Depth);
	
	
	
	dgcg_go(.5,2,$Z);
	dgcg_line(8,2,$Z,$Depth);
	
	dgcg_go(.5,4,$Z);
	dgcg_line(8,4,$Z,$Depth);
	
	$Diameter=2;
	$RadiansStart=3*$Pi/2;
	$RadiansStop=5*$Pi/2;
	dgcg_arc(8,3,0,$Diameter,$RadiansStart,$RadiansStop,$Depth);
	
	
	
	dgcg_volume(11,.5,$Z,1,2.25,$Depth);
	dgcg_volume(11,.5+2.5+.5,$Z,1,2.25,$Depth);
	
	//middle
	dgcg_volume(.5,2.5,$Z,3,1,$Depth);
	dgcg_volume(.5+3+.5,2.5,$Z,3,1,$Depth);
	
	
	
	
	$vars['DGCG']['Tool']['Diameter']=.1;
	$vars['DGCG']['Tool']['Radius']=$vars['DGCG']['Tool']['Diameter']/2;
	$vars['DGCG']['Tool']['PassStep']=$vars['DGCG']['Tool']['Diameter']/3;
	
	$hx=.25;
	for($mm=1;$mm<=20;$mm++){
		dgcg_hole($hx,6.2,$Z,mm2in($mm),$Depth);
		$hx+=mm2in($mm*1.25);
	}
	
	
	
	
	
	$x=.5;
	$y=5.7;
	$Length=12.01;
	$Width=0.4;
	$Depth=0.01;
	$Units="in";
	$ShowNumbers=TRUE;
	dgcg_ruler_x($x,$y,$z,$Length,$Width,$Depth,$Units,$ShowNumbers);
	
	 
	
	
	
	
	/*
	
	
	dgcg_go(1.5,1,$Z);
	dgcg_line(1.5,3,$Z,$Depth);
	
	dgcg_go(2.5,3,$Z);
	dgcg_line(2.5,3,$Z,$Depth);
	
	$X=3;
	$Y=2;
	
	$Diameter=2;
	$RadiansStart=$Pi/2;
	$RadiansStop=3*$Pi/2;
	
	dgcg_arc($X,$Y,$Z,$Diameter,$RadiansStart,$RadiansStop,$Depth);
		
	$Diameter=1;
	dgcg_arc($X,$Y,$Z,$Diameter,$RadiansStart,$RadiansStop,$Depth);
	*/
	/*
	$Depth=1;
	$maxx=2;
	$x=0;
	$y=1;
	$z=0;
	for($a=1;$a<=40;$a++){
		$Diameter=$AWGChart[$a][1];
		$x+=$Diameter*1.5;
		if($x>$maxx){
			$x=$Diameter*1.5;
			$y+=$Diameter*1.5;
		}
		print "adding AWG $a hole, diameter: $Diameter inches at $x,$y,$z\n";
		dgcg_hole($x,$y,$z,$Diameter,$Depth);
	}
	
	
	$W=5;
	$L=5;
	$Rows=5;
	$Cols=5;
	$HoleDiameter=0.05;
	$HoleDepth=1;
	dgcg_grid($W,$L,$Rows,$Cols,$HoleDiameter,$HoleDepth);
	 
	$x=1;
	$y=1;
	$z=0;
	$Diameter=3;
	$Depth=2;
	//dgcg_hole($x,$y,$z,$Diameter,$Depth);
	
	$cx=0;
	for($h=1;$h<10;$h++){
		$cx=$cx+$h*1.1;
		$y=0;
		$z=0;
		$Diameter=$h;
		$Depth=4;
		dgcg_hole($cx,$y,$z,$Diameter,$Depth);
		
	}
	
	
	$x=.1;$y=.1;$z=0;
	$Length=6;
	$Width=0.4;
	$Depth=0.01;
	$Units="in";
	$ShowNumbers=FALSE;
	dgcg_ruler_x($x,$y,$z,$Length,$Width,$Depth,$Units,$ShowNumbers);
	
	 
	
	$X=0;
	$Y=0;
	$Z=0;
	$W=4;
	$H=5;
	$Depth=4;
	//dgcg_volume($X,$Y,$Z,$W,$H,$Depth);
	*/
	
	dgcg_program_end();
	
	if($vars['DGCG']['Program']['Image']['FileName']){
		unlink($vars['DGCG']['Program']['Image']['FileName']);
	}
	dse_file_put_contents("/tmp/dgcg_convert_command.sh",$vars['DGCG']['Program']['convert_command']);
	
	$r=dse_exec("chmod a+x /tmp/dgcg_convert_command.sh",TRUE,TRUE);
	print $r;
	
	
	$r=dse_exec("/tmp/dgcg_convert_command.sh",TRUE,TRUE);
	//$r=dse_exec($vars['DGCG']['Program']['convert_command'],TRUE,TRUE);
	print $r;
	
	
	if($OutFile){
		dse_file_put_contents($OutFile,$vars['DGCG']['Program']['Body']);
	}else{
		print $vars['DGCG']['Program']['Body']."\n";
	}
}

exit();

//////////////////////////////////////////////////////////////////////////


function mm2in($mm){
	return($mm/25.4);
}
	
function dgcg_3d2img($x,$y,$z,$ZScaleFactor=3){
	global $vars;
	
	$z2_adj=1*$z*$vars['DGCG']['Program']['Image']['PixelsPerUnit']/$ZScaleFactor;
	$x2=intval($x*$vars['DGCG']['Program']['Image']['PixelsPerUnit']+$vars['DGCG']['Program']['Image']['Margin']-$z2_adj);
	$y2=$vars['DGCG']['Program']['Image']['Width']-intval($y*$vars['DGCG']['Program']['Image']['PixelsPerUnit']+$vars['DGCG']['Program']['Image']['Margin']+$z2_adj);
	
	return (array($x2,$y2));
}


function dgcg_go($x,$y,$z){
	global $vars;
	$x=number_format($x,3);
	$y=number_format($y,3);
	$z=number_format($z,3);
	
	$ZScaleFactor=2.5/1;
	
	
		
	
	
	//if($vars['DGCG']['Current']['X']!=$x || $vars['DGCG']['Current']['Y']!=$y){
		if($vars['DGCG']['Current']['Z']==0){
			$color="black";
		}elseif($vars['DGCG']['Current']['Z']>0){
			$color="blue";
		}elseif($vars['DGCG']['Current']['Z']<0){
			$color="red";
		}
		if($vars['DGCG']['Current']['Z']!=$z){
			$color="grey";
		}
		
		
		list($x1,$y1)=dgcg_3d2img($vars['DGCG']['Current']['X'],$vars['DGCG']['Current']['Y'],$vars['DGCG']['Current']['Z'],$ZScaleFactor);
		list($x2,$y2)=dgcg_3d2img($x,$y,$z,$ZScaleFactor);
		if($vars['DGCG']['Program']['Image']['ShowMoves'] || $color=="red"){
			$vars['DGCG']['Program']['convert_command'].= " -draw \"stroke $color\nline $x1,$y1 $x2,$y2\"\\\n"; 
		}	
		if($vars['DGCG']['Program']['Image']['Stereo']){
			list($x1,$y1)=dgcg_3d2img($vars['DGCG']['Current']['X'],$vars['DGCG']['Current']['Y'],$vars['DGCG']['Current']['Z'],$ZScaleFactor);
			list($x2,$y2)=dgcg_3d2img($x,$y,$z,$ZScaleFactor*-1);
			$x1+=$vars['DGCG']['Program']['Image']['Width'];
			$x2+=$vars['DGCG']['Program']['Image']['Width'];
			$vars['DGCG']['Program']['convert_command'].= " -draw \"stroke $color\nline $x1,$y1 $x2,$y2\"\\\n"; 
		}
		
	//}
	$vars['DGCG']['Current']['X']=$x;
	$vars['DGCG']['Current']['Y']=$y;
	$vars['DGCG']['Current']['Z']=$z;
	$vars['DGCG']['Program']['Body'].= "G1 X$x Y$y Z$z F100000\n";
}
function dgcg_go_x($x){
	global $vars;
	dgcg_go($x,$vars['DGCG']['Current']['Y'],$vars['DGCG']['Current']['Z']);
}
function dgcg_go_y($y){
	global $vars;
	dgcg_go($vars['DGCG']['Current']['X'],$y,$vars['DGCG']['Current']['Z']);
}
function dgcg_go_z($z){
	global $vars;
	dgcg_go($vars['DGCG']['Current']['X'],$vars['DGCG']['Current']['Y'],$z);
}




function dgcg_hole($x,$y,$z,$Diameter,$Depth){
	global $vars;
	dpv(4,"dgcg_hole($x,$y,$z,$Diameter,$Depth){\n");
	$Pi=3.14159;
	$AngleIncrement=$Pi/20;
	dgcg_go($x,$y,$z);
	dgcg_go($x,$y,$z-$Depth);
	$CurrentHoleRadius=$vars['DGCG']['Tool']['Diameter'];
	$Angle=0;
	while($CurrentHoleRadius<$Diameter/2){
		$Angle+=$AngleIncrement;
		$CurrentHoleRadius=($Angle/(2*$Pi))*$vars['DGCG']['Tool']['PassStep'];
		$cx=$x+(cos($Angle)*$CurrentHoleRadius);
		$cy=$y+(sin($Angle)*$CurrentHoleRadius);
		dgcg_go($cx,$cy,$z-$Depth);
	}
	dgcg_go($x,$y,$z);
}


function dgcg_line($x,$y,$z,$Depth){
	global $vars;
	dgcg_go_z( $vars['DGCG']['Current']['Z']-$Depth);
	dgcg_go($x,$y,$z-$Depth);
	dgcg_go($x,$y,$z);
}


function dgcg_volume($X,$Y,$Z,$W,$H,$Depth){
	global $vars;
	//dgcg_go($X+$vars['DGCG']['Tool']['Radius'],$Y+$vars['DGCG']['Tool']['Radius'],$Z);
	dgcg_go_z( $Z);
	for($xi=$X+$vars['DGCG']['Tool']['Radius'] ; $xi<($X+$W)-$vars['DGCG']['Tool']['Radius'] ; $xi+=$vars['DGCG']['Tool']['PassStep']){
		dgcg_go_x( $xi);
		dgcg_go_y( $Y+$vars['DGCG']['Tool']['Radius']);
		dgcg_go_z( $Z-$Depth);
		dgcg_go_y( ($Y+$H)-$vars['DGCG']['Tool']['Radius']);
		dgcg_go_z( $Z);
	}
}


function dgcg_arc($X,$Y,$Z,$Diameter,$RadiansStart,$RadiansStop,$Depth){
	global $vars;
	dgcg_oval_arc($X,$Y,$Z,$Diameter,$Diameter,$RadiansStart,$RadiansStop,$Depth);
}


function dgcg_oval_arc($X,$Y,$Z,$DiameterX,$DiameterY,$RadiansStart,$RadiansStop,$Depth){
	global $vars;
	dgcg_go($X,$Y,$Z);
	dgcg_go_z( $Z);
	$Pi=3.14159;
	$RadianIncrement=$Pi/20;
	for($Radians=$RadiansStart ; $Radians<$RadiansStop ; $Radians+=$RadianIncrement){
		$cx=$X+cos($Radians)*$DiameterX/2;
		$cy=$Y+sin($Radians)*$DiameterY/2;
		dgcg_go($cx,$cy,$Z-$Depth);
	}
	$cx=$X+cos($RadiansStop)*$DiameterX/2;
	$cy=$Y+sin($RadiansStop)*$DiameterY/2;
	dgcg_go($cx,$cy,$Z-$Depth);
	dgcg_go($cx,$cy,$Z);
}


function dgcg_hole_grid($W,$L,$Rows,$Cols,$HoleDiameter,$HoleDepth,$Clearence=0){
	global $vars;
	$Wgap=$W/($Cols+1);
	$Lgap=$L/($Rows+1);
	for($r=0;$r<$Rows;$r++){
		for($c=0;$c<$Cols;$c++){
			$x=$r*$Lgap;
			$y=$c*$Wgap;
			$z=$Clearence;
			dgcg_hole($x,$y,$z,$HoleDiameter,$HoleDepth);
			//dgcg_go($x,$y,$z);
			$z=-1*$HoleDepth;
			//dgcg_go($x,$y,$z);
			$z=$Clearence;
			//dgcg_go($x,$y,$z);
		}
	}
}





function dgcg_ruler_x($x,$y,$z,$Length,$Width,$Depth,$Units="mm",$ShowNumbers=FALSE){
	global $vars;
	$cx=$x;
	dgcg_go($cx,$y,$z);
	$Offset=0;
	while($cx<=($x+$Length)-$vars['DGCG']['Tool']['Radius']){
		dgcg_go_z( $z);
		dgcg_go_x($cx);
		dgcg_go_y($y+$vars['DGCG']['Tool']['Radius']);
		dgcg_go_z( $z-$Depth);
		$Len=$Width/3;
		if($Units=="mm"){
			if($Offset%5==0){
				$Len=$Width*(2/5);
			}
			if($Offset%10==0){
				$Len=$Width;
			}
		}else{
			if($Offset%5==0){
				$Len=$Width*(2/5);
			}
			if($Offset%10==0){
				$Len=$Width;
			}
		}
		dgcg_go_y($y+$Len-$vars['DGCG']['Tool']['Radius']);
		dgcg_go_y($y+$vars['DGCG']['Tool']['Radius']);
		dgcg_go_z( $z);
		
		if($Units=="mm"){
			$cx++;
		}else{
			$cx+=.1; 	
		}
		$Offset++;
	}
	
}





function dgcg_border_rectangular($X,$Y,$Z,$W,$H,$BorderThickness,$BorderDepth){
	global $vars;
}



function dgcg_image2gcode($File,$UnitsPerPixel,$Depth,$Normalize=FALSE){
	global $vars;
/*
	$ia=image2array

	if normalize, do so
	
	scale image to 1 pixel per tool diameter

	
		scale pixel to depth
*/
}



function dgcg_load_file($Filename){
	global $vars;
	$gfile_array=array();

	return($gfile_array);
}



function dgcg_save_file($Filename,$gfile_array){
	global $vars;
	

}



function dgcg_scale($gfile_array,$Factor){
	global $vars;

	return($gfile_array);
}



function dgcg_program_start(){
	global $vars;
	$vars['DGCG']['Program']['Body']="";
	
	if($vars['DGCG']['Units']=="mm"){
		$vars['DGCG']['Program']['Body'].= "G21 G00 Z1\n"; //mm=G21  in=G20
	}else{
		$vars['DGCG']['Program']['Body'].= "G20 G00 Z1\n"; //mm=G21  in=G20
	}
	$vars['DGCG']['Program']['Body'].= "G80 G90 G94\n"; //(set absolute distance mode)
	$vars['DGCG']['Program']['Body'].= "G64 P1.0\n"; 
	$vars['DGCG']['Program']['Body'].= "F100000\n"; 
	$vars['DGCG']['Program']['convert_command']="";
	
}

function dgcg_program_end(){
	global $vars;
	
	
	if($vars['DGCG']['Program']['Image']['Stereo']){
		$vars['DGCG']['Program']['convert_command']= "convert -size "
			.($vars['DGCG']['Program']['Image']['Width']*2)."x".$vars['DGCG']['Program']['Image']['Height']
			." xc:lightblue ".$vars['DGCG']['Program']['convert_command']; 
	}else{
		$vars['DGCG']['Program']['convert_command']= "convert -size "
			.$vars['DGCG']['Program']['Image']['Width']."x".$vars['DGCG']['Program']['Image']['Height']
			." xc:lightblue ".$vars['DGCG']['Program']['convert_command']; 
	}
	$vars['DGCG']['Program']['convert_command'].=" ".$vars['DGCG']['Program']['Image']['FileName'];
	
	
	$vars['DGCG']['Program']['Body'].="M2\n";// (end program)
}





function dgcg_text($X,$Y,$Z,$Text,$Depth,$FontHeight,$FontWidthRatio=.7){
	global $vars;
	$FontWidth=$FontHeight*$FontWidthRatio;
	$FontSpacing=$FontWidth/3;
	
	$Pi=3.14159;
	$vars['DGCG']['CharacterMap']['A']=array(
		array("Line",0,0,.5,1),
		array("Line",1,0,.5,1),
		array("Line",.3,.5,.7,.5),
		);
	$vars['DGCG']['CharacterMap']['B']=array(
		array("Line",0,0,0,1),
		array("Line",0,0,.65,0),
		array("Line",0,.5,.65,.5),
		array("Line",0,1,.65,1),
		array("Arc",.65,.25,.5,3*$Pi/2,5*$Pi/2),
		array("Arc",.65,.75,.5,3*$Pi/2,5*$Pi/2),
		);
	$vars['DGCG']['CharacterMap']['C']=array(
		array("OvalArc",.8,.5,.9,1,$Pi/2,3*$Pi/2),
		array("Line",.8,0,1,0),
		array("Line",.8,1,1,1),
		);
	$vars['DGCG']['CharacterMap']['D']=array(
		array("Line",0,0,0,1),
		array("OvalArc",.2,.5,1,1,3*$Pi/2,5*$Pi/2),
		array("Line",0,0,.2,0),
		array("Line",0,1,.2,1),
		);
	$vars['DGCG']['CharacterMap']['E']=array(
		array("Line",0,0,0,1),
		array("Line",0,0,1,0),
		array("Line",0,.5,1,.5),
		array("Line",0,1,1,1),
		);
	$vars['DGCG']['CharacterMap']['F']=array(
		array("Line",0,0,0,1),
		array("Line",0,.6,1,.6),
		array("Line",0,1,1,1),
		);
	$vars['DGCG']['CharacterMap']['G']=array(
		array("OvalArc",1,.5,1.25,1,$Pi/2,3*$Pi/2),
		array("Line",1,0,1,.2),
		array("Line",1,.2,.8,.2),
		);
	$vars['DGCG']['CharacterMap']['H']=array(
		array("Line",0,0,0,1),
		array("Line",0,.5,1,.5),
		array("Line",1,0,1,1),
		);
	$vars['DGCG']['CharacterMap']['I']=array(
		array("Line",.5,0,.5,1),
		array("Line",.2,0,.8,0),
		array("Line",.2,1,.8,1),
		);
	$vars['DGCG']['CharacterMap']['J']=array(
		array("Line",.8,.3,.8,1),
		array("Line",.7,1,1,1),
		array("Arc",.4,.3,.6,2*$Pi/2,4*$Pi/2),
		);
	$vars['DGCG']['CharacterMap']['K']=array(
		array("Line",0,0,0,1),
		array("Line",0,.5,1,1),
		array("Line",0,.5,1,0),
		);
	$vars['DGCG']['CharacterMap']['L']=array(
		array("Line",0,1,0,0),
		array("Line",0,0,1,0),
		);
	$vars['DGCG']['CharacterMap']['M']=array(
		array("Line",0,0,0,1),
		array("Line",0,1,.5,0),
		array("Line",.5,0,1,1),
		array("Line",1,1,1,0),
		);
	$vars['DGCG']['CharacterMap']['N']=array(
		array("Line",0,0,0,1),
		array("Line",0,1,1,0),
		array("Line",1,0,1,1),
		);
	$vars['DGCG']['CharacterMap']['O']=array(
		array("OvalArc",.5,.5,.8,1,0,2*$Pi),
		);
	$vars['DGCG']['CharacterMap']['P']=array(
		array("Line",0,0,0,1),
		array("Line",0,.5,.5,.5),
		array("Line",0,1,.5,1),
		array("Arc",.5,.75,.5,3*$Pi/2,5*$Pi/2),
		);
	$vars['DGCG']['CharacterMap']['Q']=array(
		array("OvalArc",.5,.5,.8,1,0,2*$Pi),
		array("Line",.6,.4,1,0),
		);
	$vars['DGCG']['CharacterMap']['R']=array(
		array("Line",0,0,0,1),
		array("Line",0,.5,.5,.5),
		array("Line",0,1,.5,1),
		array("Arc",.5,.75,.5,3*$Pi/2,5*$Pi/2),
		array("Line",.3,.55,1,0),
		);
	$vars['DGCG']['CharacterMap']['S']=array(
		array("Line",0,0,.5,0),
		array("OvalArc",.5,.25,.8,.5,3*$Pi/2,5*$Pi/2),
		array("OvalArc",.5,.75,.8,.5,$Pi/2,3*$Pi/2),
		array("Line",.5,1,1,1),
		);
	$vars['DGCG']['CharacterMap']['T']=array(
		array("Line",.5,0,.5,1),
		array("Line",0,1,1,1),
		);
	$vars['DGCG']['CharacterMap']['U']=array(
		array("OvalArc",.5,.3,.6,.6,2*$Pi/2,4*$Pi/2),
		array("Line",0,1,0,.3),
		array("Line",1,1,1,.3),
		);
	$vars['DGCG']['CharacterMap']['V']=array(
		array("Line",0,1,.5,0),
		array("Line",.5,0,1,1),
		);
	$vars['DGCG']['CharacterMap']['W']=array(
		array("Line",0,1,.25,0),
		array("Line",.25,0,.5,1),
		array("Line",.5,1,.75,0),
		array("Line",.75,0,1,1),
		);
	$vars['DGCG']['CharacterMap']['X']=array(
		array("Line",0,1,1,0),
		array("Line",0,0,1,1),
		);
	$vars['DGCG']['CharacterMap']['Y']=array(
		array("Line",0,1,.5,.5),
		array("Line",.5,.5,1,1),
		array("Line",.5,.5,.5,0),
		);
	$vars['DGCG']['CharacterMap']['Z']=array(
		array("Line",0,1,1,1),
		array("Line",1,1,0,0),
		array("Line",0,0,1,0),
		);
		
		
		
	$vars['DGCG']['CharacterMap']['0']=array(
		array("OvalArc",.5,.5,.7,1,0,2*$Pi),
		);
	$vars['DGCG']['CharacterMap']['1']=array(
		array("Line",.5,0,.5,1),
		array("Line",.25,0,.75,0),
		array("Line",.25,.85,.5,1),
		);
	$vars['DGCG']['CharacterMap']['2']=array(
		//array("Line",0,1,.5,1),
		array("OvalArc",.5,.6,.7,.8,4*$Pi/2,6*$Pi/2),
		array("Line",0,0,1,.6),
		array("Line",0,0,1,0),
		);
	$vars['DGCG']['CharacterMap']['3']=array(
		array("Line",0,0,.5,0),
		array("OvalArc",.5,.25,.5,.5,3*$Pi/2,5*$Pi/2),
		array("Line",0,.5,.5,.5),
		array("OvalArc",.5,.75,.5,.5,3*$Pi/2,5*$Pi/2),
		array("Line",0,1,.5,1),
		);
	$vars['DGCG']['CharacterMap']['4']=array(
		array("Line",.8,0,.8,1),
		array("Line",.8,1,0,.5),
		array("Line",0,.5,1,.5),
		);
	$vars['DGCG']['CharacterMap']['5']=array(
		array("Line",1,1,0,1),
		array("Line",0,1,0,.6),
		array("Line",0,.6,.45,.6),
		array("OvalArc",.5,.3,.6,.6,3*$Pi/2,5*$Pi/2),
		array("Line",0,0,.45,0),
		);
	$vars['DGCG']['CharacterMap']['6']=array(
		array("OvalArc",.5,.3,.7,.5,0,2*$Pi),
		array("OvalArc",.7,.5,1,1,$Pi/2,(2.1)*$Pi/2),
		);
	$vars['DGCG']['CharacterMap']['7']=array(
		array("Line",0,1,1,1),
		array("Line",1,1,0,0),
		);
	$vars['DGCG']['CharacterMap']['8']=array(
		array("OvalArc",.5,.25,.7,.5,0,2*$Pi),
		array("OvalArc",.5,.75,.6,.5,0,2*$Pi),
		);
	$vars['DGCG']['CharacterMap']['9']=array(
		array("OvalArc",.5,.75,.7,.5,0,2*$Pi),
		array("Line",1,.8,.85,0),
		);
		
		
		
		
		
	$vars['DGCG']['CharacterMap']['-']=array(
		array("Line",.1,.5,.9,.5),
		);
	$vars['DGCG']['CharacterMap']['_']=array(
		array("Line",0,.05,1,.05),
		);
	$vars['DGCG']['CharacterMap']['|']=array(
		array("Line",.5,0,.5,1),
		);
	$vars['DGCG']['CharacterMap']['+']=array(
		array("Line",.15,.5,.85,.5),
		array("Line",.5,.2,.5,.8),
		);
	$vars['DGCG']['CharacterMap']['=']=array(
		array("Line",.1,.35,.9,.35),
		array("Line",.1,.65,.9,.65),
		);
	$vars['DGCG']['CharacterMap']['\\']=array(
		array("Line",.1,1,.9,0),
		);
	$vars['DGCG']['CharacterMap']['/']=array(
		array("Line",.1,0,.9,1),
		);
	$vars['DGCG']['CharacterMap']['^']=array(
		array("Line",.1,.5,.5,1),
		array("Line",.5,1,.9,.5),
		);
	$vars['DGCG']['CharacterMap']['#']=array(
		array("Line",.1,.35,.9,.35),
		array("Line",.1,.6,.9,.6),
		array("Line",.1,.1,.6,.9),
		array("Line",.4,.1,.9,.9),
		);
	$vars['DGCG']['CharacterMap']['%']=array(
		array("Line",.1,.15,.9,.85),
		array("OvalArc",.2,.8,.2,.2,0,2*$Pi),
		array("OvalArc",.8,.2,.2,.2,0,2*$Pi),
		);
	$vars['DGCG']['CharacterMap']['*']=array(
		array("Line",.15,.5,.85,.5),
		array("Line",.75,.2,.25,.8),
		array("Line",.25,.2,.75,.8),
		);
		
		
	$vars['DGCG']['CharacterMap']['!']=array(
		array("Line",.2,.3,.2,1),
		array("Line",.2,0,.2,.1),
		);
	$vars['DGCG']['CharacterMap']['.']=array(
		array("Line",.2,0,.2,.1),
		);
	$vars['DGCG']['CharacterMap'][',']=array(
		array("OvalArc",.1,.1,.1,.1,3*$Pi/2,5*$Pi/2),
		);
	$vars['DGCG']['CharacterMap']['?']=array(
		array("OvalArc",.5,.75,.6,.6,3*$Pi/2,6*$Pi/2),
		array("Line",.5,.25,.5,.45),
		array("Line",.5,0,.5,.1),
		);
	$vars['DGCG']['CharacterMap']['"']=array(
		array("Line",.8,.8,.9,1),
		array("Line",.6,.8,.7,1),
		);
	$vars['DGCG']['CharacterMap']['\'']=array(
		array("Line",.6,.8,.7,1),
		);
	$vars['DGCG']['CharacterMap'][':']=array(
		array("Line",.2,.1,.2,.2),
		array("Line",.2,.8,.2,.9),
		);
	$vars['DGCG']['CharacterMap'][';']=array(
		array("Line",.2,.8,.2,.9),
		array("OvalArc",.1,.1,.1,.1,3*$Pi/2,5*$Pi/2),
		);
		
		
		
		
		
	$vars['DGCG']['CharacterMap']['[']=array(
		array("Line",.8,1,.1,1),
		array("Line",.1,1,.1,0),
		array("Line",.1,0,.8,0),
		);	
	$vars['DGCG']['CharacterMap'][']']=array(
		array("Line",.1,1,.8,1),
		array("Line",.8,1,.8,0),
		array("Line",.8,0,.1,0),
		);
	$vars['DGCG']['CharacterMap']['(']=array(
		array("OvalArc",.8,.5,.5,1,$Pi/2,3*$Pi/2),
		);
	$vars['DGCG']['CharacterMap'][')']=array(
		array("OvalArc",.8,.5,.5,1,3*$Pi/2,5*$Pi/2),
		);
	$vars['DGCG']['CharacterMap']['<']=array(
		array("Line",1,1,0,.5),
		array("Line",0,.5,1,0),
		);
	$vars['DGCG']['CharacterMap']['>']=array(
		array("Line",0,1,1,.5),
		array("Line",1,.5,0,0),
		);
		
		
		
		
		
		
		
	
	print "adding text: $Text\n";
	for($ci=0;$ci<strlen($Text);$ci++){
		$L=$Text[$ci];
		if( $vars['DGCG']['CharacterMap'][$L]){
			
			print "adding letter: $L\n";
			dgcg_letter($X,$Y,$Z,$L,$Depth,$FontHeight,$FontWidthRatio);
		}else{
			
		}
		$X+=$FontSpacing+$FontWidth;
	}
	


}

function dgcg_letter($X,$Y,$Z,$Letter,$Depth,$FontHeight,$FontWidthRatio=.7){
	global $vars;
	$FontWidth=$FontHeight*$FontWidthRatio;
	$FontSpacing=$FontWidth/3;
	
	print_r($vars['DGCG']['CharacterMap'][$Letter]);
 	foreach($vars['DGCG']['CharacterMap'][$Letter] as $Segment){
 		$SegmentType=$Segment[0];
		switch($SegmentType){
			case 'Line':
				$x1=$X+$Segment[1]*$FontWidth;
				$y1=$Y+$Segment[2]*$FontHeight;
				$x2=$X+$Segment[3]*$FontWidth;
				$y2=$Y+$Segment[4]*$FontHeight;
				dgcg_go($x1,$y1,$Z);
				dgcg_line($x2,$y2,$Z,$Depth);
				break;
			case 'Arc':
				$x1=$X+$Segment[1]*$FontWidth;
				$y1=$Y+$Segment[2]*$FontHeight;
				$Diameter=$Segment[3]*$FontHeight;
				$RadiansStart=$Segment[4];
				$RadiansStop=$Segment[5];
				dgcg_arc($x1,$y1,$Z,$Diameter,$RadiansStart,$RadiansStop,$Depth);
				break;
			case 'OvalArc':
				$x1=$X+$Segment[1]*$FontWidth;
				$y1=$Y+$Segment[2]*$FontHeight;
				$DiameterX=$Segment[3]*$FontHeight;
				$DiameterY=$Segment[4]*$FontHeight;
				$RadiansStart=$Segment[5];
				$RadiansStop=$Segment[6];
				dgcg_oval_arc($x1,$y1,$Z,$DiameterX,$DiameterY,$RadiansStart,$RadiansStop,$Depth);
				break;
				
		}
 	}
		
	


}
?>
