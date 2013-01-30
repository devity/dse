#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
$vars['Verbosity']=6;
dpv(6,"trace:head");
// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="DSE File Merger";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="finds duplicate files, syncs directories";
$vars['DSE']['SCRIPT_VERSION']="v0.01b";
$vars['DSE']['SCRIPT_VERSION_DATE']="2013/01/24";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
$vars['DSE']['SCRIPT_COMMAND_FORMAT']="[source] [destination]";
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

$parameters_details = array(
 // array('h','help',"this message"),
  array('q','quiet',"same as --verbosity 0"),
  array('v:','verbosity:',"0=none 1=some 2=more 3=debug"),
  array('h','help',"this message"),
  array('s','silent-success',"dont report any matches, only missing"),
  array('c','compare-existance',"compare esistance of [d] for each [s]"),
  array('a','compare-hash',"compare [s] and [d] by hash and size"),
);
$vars['parameters']=dse_cli_get_paramaters_array($parameters_details);
$vars['Usage']=dse_cli_get_usage($parameters_details);
$vars['argv_origional']=$argv;
dse_cli_script_start();
$BackupBeforeUpdate=TRUE;

dpv(6,"trace:atop_foreach");
$vars['fmerge']['silent-success']=FALSE;

foreach (array_keys($vars['options']) as $opt) switch ($opt) {
  	case 'v':
	case 'verbosity':
		$vars['Verbosity']=$vars['options'][$opt];
		if($vars['Verbosity']>=2) print "Verbosity set to ".$vars['Verbosity']."\n";
		break;
}

dse_cli_script_header();
foreach (array_keys($vars['options']) as $opt) switch ($opt) {
	case 'q':
	case 'quiet':
		$Quiet=TRUE;
		$vars['Verbosity']=0;
		break;	
	case 'h':
  	case 'help':
  		$ShowUsage=TRUE;
		$DidSomething=TRUE;
		break;
	case 's':
	case 'silent-success':
		$vars['fmerge']['silent-success']=TRUE;
		break;	
}

if($vars['Verbosity']>4){
	$vars[dse_enable_debug_code]=TRUE;
}
foreach (array_keys($vars['options']) as $opt) switch ($opt) {
	case 'c':
  	case 'compare-existance':
		$DidSomething=TRUE;
		$s=$argv[1];
		$d=$argv[2];
		
		$directory_array=dse_directory_to_array2($s,2);
		//print_r($directory_array);
		
		dse_fmerge_process_directory_array($s,$d,$directory_array,"compare-existance");
		
		break;
		
		
		
	case 'a':
  	case 'compare-hash':
		$DidSomething=TRUE;
		$s=$argv[1];
		$d=$argv[2];
		
		$directory_array=dse_directory_to_array2($s,2);
		//print_r($directory_array);
		
		dse_fmerge_process_directory_array($s,$d,$directory_array,"compare-hash");
		
		break;
	
}





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

function dse_fmerge_process_directory_array( $s, $d, $da, $action ){
	global $vars;
	foreach($da as $de){
		$name=$de[1];
		$fullname=$de[2];

		if($de[0]=="FILE"){
			
			switch($action){
				case "compare-existance":
					$ed=str_replace($s,$d,$fullname); 
					if(dse_file_exists2($ed)){
						if(!$vars['fmerge']['silent-success']){
							print "$fullname ";
							print "EXISTS: $ed";
							print "\n";
						}
					}else{
						print "$fullname ";
						print "MISSING: $ed";
						print "\n";
					}
					break;
				case "compare-hash":
					$ed=str_replace($s,$d,$fullname); 
					
					$hs=md5_of_file2($fullname).dse_file_get_stat_field2($fullname,"size");
					$hd=md5_of_file2($ed).dse_file_get_stat_field2($ed,"size");
					
					if(dse_file_exists2($ed)){
						if($hs==$hd){
							if(!$vars['fmerge']['silent-success']){
								//print "$fullname ";
								//print "MATCH: $ed";
								//print "\n";
							}
						}else{
							print "$fullname ";
							print "DIFFERENT: $ed";
							print "\n";
						}
					}else{
						print "$fullname ";
						print "MISSING: $ed";
						print "\n";
					}
					break;
			}
		}elseif($de[0]=="DIR"){
			$ed=str_replace($s,$d,$fullname); 
			if(dse_file_exists2($ed)){
				if(is_array($de[3])){
					dse_fmerge_process_directory_array( $s, $d, $de[3], $action );
				}else{
					//empty directory
				}
			}else{
				print "$fullname ";
				print "MISSING: $ed";
				print "\n";
			}
		}else{
			print "unexpected. ds[0]=".$de[0]." error: 4tgf34gv34gv31tttttt\n";
		}

	}
	
}
		

function md5_of_file2($f){
	global $vars; dse_trace();
        $sw_vers=dse_which("md5");
		$f=str_replace("\"","\\\"",$f);
        if($sw_vers){
                $m=`md5 -q "$f" 2>/dev/null`;
                return ($m);
        }else{
                $sw_vers=dse_which("md5sum");
                if($sw_vers){
                        $m=`md5sum "$f" 2>/dev/null`;
                        $m=strcut($m,""," ");
                        return ($m);
                }
        }
        print "error in md5_of_file(), no md5 utility found. Supported:(md5,md5sum)";
        return -1;
}

function dse_file_exists2($DestinationFile){
	global $vars; dse_trace();
	$DestinationFile=str_replace("\"","\\\"",$DestinationFile);
	$r=`ls -la "$DestinationFile" 2>&1`;
	if(str_contains($r,'No such file or directory')){
		return FALSE;
	}
	return TRUE;
}

		


function dse_file_get_stat_field2($DestinationFile,$field=""){
	global $vars; dse_trace();
	$stat_field_names=array('dev'=>0,'ino'=>1,'mode'=>2,'nlink'=>3,'uid'=>4,'gid'=>5,'rdev'=>6,'size'=>7,'atime'=>8,'mtime'=>9,'ctime'=>10,'blksize'=>11,'blocks'=>12);
	if(!dse_file_exists2($DestinationFile)){
		dpv(4,  "Error in dse_file_get_mode($DestinationFile,$field) - file does not exist.");
		return -1;
	}
	$sa=@stat($DestinationFile);
	if(!$sa) return NULL;
	if(!$field) return $sa;
	$index_i=$stat_field_names[$field];
	if((!$index_i) || strlen($index_i)<=0){
		print "Error in dse_file_get_mode($DestinationFile,$field) - field $field unknown. Options: "; print_r($stat_field_names); print "\n";
		return -1;
	}
	return $sa[$index_i];
}

function dse_directory_to_array2( $path = '.', $max_level=100, $level = 0 ){
	global $vars;
	dpv(2,"Starting dse_directory_to_array($path, $max_level, $level){");
	$tbr=array();
	$path.="/";  $path=str_replace("//", "/", $path);
    $ignore = array( '.', '..' ); 
    $ignore_partial = array( 'crafters/files','events/files', 'ratings/files', '/Zend/', '/images', '/phpMemcachedAdmin', '/ZFDebug', '/cache', '/thumbnail' ); 
    $dh = @opendir( $path ); 
	$FileAnyTypeCount=0;
    while( $dh && false !== ( $file = readdir( $dh ) ) ){ 
      //  if( !in_array( $ignore, $file ) ){
      	if($file!="." && $file!=".."){
      		$fullfilename=$path.$file;
		//	print "importing $fullfilename\n";
        	$ignore=FALSE;
        	/*foreach($ignore_partial as $ignore_try){
        		dpv(5,"ignore_try: str_icontains($file,$ignore_try)");
        		if(str_icontains($fullfilename,$ignore_try)){
        			$ignore=TRUE;
        		}
        	}*/
			if(!$ignore){
				$FileAnyTypeCount++;
	            if( is_dir( $fullfilename ) ){
	            	if($level<=$max_level){
		            	$tbr[]=array(
		            		"DIR",
		            		$file,
		            		$fullfilename, 
	    	        		dse_directory_to_array2( $fullfilename, $max_level, ($level+1) )
						 );
					}else{
						$tbr[]=array("DIR",$file,$fullfilename, NULL);
					}
	            } else { 
	               $tbr[]=array("FILE",$file,$fullfilename, NULL ); 
	            } 
			}
        } 
    } 
     
    if($dh){
    	closedir( $dh ); 
	}
	if($FileAnyTypeCount>25){
		$FileAnyTypeCount=colorize($FileAnyTypeCount,"white","red");
	}
	dpv(2,"} Done dse_directory_to_array($path, $max_level, $level). Found $FileAnyTypeCount sub-entries.");
	return $tbr;
} 




?>