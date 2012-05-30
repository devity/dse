#!/usr/bin/php
<?

if(sizeof($argv)<2){
	print "no argument supplied. STDIN not supported. exiting.\n";
	exit(-1);
}

if(is_dir($argv[1])){
	if($argv[1][0]!='/'){
		if($argv[1][0]=='.'){
			$argv[1]=trim(`pwd`).substr($argv[1],1);
		}else{
			$argv[1]=trim(`pwd`)."/".$argv[1];
		}
	}
	$argv[1]=$argv[1]."/";
	$argv[1]=str_replace("//", "/", $argv[1]);
	md5_of_directory($argv[1]);
}elseif(file_exists($argv[1])){
	print md5_of_file($argv[1]);
	exit(0);
}else{
	print "$argv[1] does not exist or is inaccessable. exiting.\n";
	exit(-1);
}



function md5_of_directory( $path = '.', $level = 0 ){ 
	print "$path DIRECTORY\n";
	
	$path.="/";  $path=str_replace("//", "/", $path);
	//$path_notrail=substr($path,0,strlen($path)-1);
	
    $ignore = array( '.', '..' ); 
    // Directories to ignore when listing output.
    
	//print "opendir( $path )\n";
	
    
    $dh = @opendir( $path ); 
    // Open the directory to the handle $dh 
     
    while( false !== ( $file = readdir( $dh ) ) ){ 
    // Loop through the directory 
     
        if( !in_array( $file, $ignore ) ){ 
        // Check that this file is not to be ignored 
             
            $spaces = str_repeat( '&nbsp;', ( $level * 4 ) ); 
            // Just to add spacing to the list, to better 
            // show the directory tree. 
             
            if( is_dir( "$path$file" ) ){ 
            // Its a directory, so we need to keep reading down... 
             
                //echo "<strong>$spaces $file</strong><br />"; 
                
                md5_of_directory( "$path$file", ($level+1) ); 
                // Re-call this same function but on a new directory. 
                // this is what makes function recursive. 
             
            } else { 
             	$md5=trim(md5_of_file("$path$file"));
             	print "$path$file $md5\n";
               // echo "$spaces $file<br />"; 
                // Just print out the filename 
            } 
        } 
    } 
     
    closedir( $dh ); 
    // Close the directory handle 

} 


function dse_which($prog){
	global $vars;
	$Command="which $prog 2>&1";
	$r=`$Command`;
	if(!(strstr($r,"no $prog in")===FALSE)){
		return "";
	}else{
		return trim($r);
	}
}


function md5_of_file($f){
        global $vars;
        $sw_vers=dse_which("md5");
        if($sw_vers){
                $m=`md5 -q "$f" 2>/dev/null`;
                return ($m);
        }else{
                $sw_vers=dse_which("md5sum");
                if($sw_vers){
                        $m=`md5sum "$f" 2>/dev/null`;
						$m=str_replace("\t"," ",$m);
                        $m=strcut($m,""," ");
                        return ($m);
                }
        }
        print "error in md5_of_file(), no md5 utility found. Supported=(md5,md5sum)";
        return -1;
}


function strcut($haystack,$pre,$post=""){
	global $strcut_post_haystack;
	$strcut_post_haystack="";
	if($pre=="" || !(stristr($haystack,$pre)===FALSE)){
		if($pre==""){
		}else{
			//if($haystack && $pre){
				$haystack=substr($haystack,stripos($haystack,$pre)+strlen($pre));
			//}else{
			//	$haystack=$haystack; //==""
			//}
		}	
		if( $post!='' && !(strstr($haystack,$post)===FALSE)){	
			if($post==""){
				$r=$haystack;
				$strcut_post_haystack="";
			}else{
			
			
				$r=substr($haystack,0,strpos($haystack,$post));
				if($haystack && $post){
					$strcut_post_haystack=substr($haystack,stripos($haystack,$post)+strlen($post));
				}
			}		
		}else{
			$r=$haystack;
			$strcut_post_haystack="";
		}		
	}else{		
		$r="";
	}
	return $r;
}


?>