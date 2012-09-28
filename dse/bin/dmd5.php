#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
$vars['Verbosity']=0;

// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="DSE MD5";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="md5 checksum generator";
$vars['DSE']['SCRIPT_VERSION']="v0.01b";
$vars['DSE']['SCRIPT_VERSION_DATE']="2012/09/28";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
$vars['DSE']['SCRIPT_COMMAND_FORMAT']="[file_name|dir_name]";
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

$parameters_details = array(
 // array('h','help',"this message"),
  array('q','quiet',"same as --verbosity 0"),
  array('v:','verbosity:',"0=none 1=some 2=more 3=debug"),
);
$vars['parameters']=dse_cli_get_paramaters_array($parameters_details);
$vars['Usage']=dse_cli_get_usage($parameters_details);
$vars['argv_origional']=$argv;
dse_cli_script_start();
$BackupBeforeUpdate=TRUE;
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
}

if($vars['Verbosity']>4){
	$vars[dse_enable_debug_code]=TRUE;
}
foreach (array_keys($vars['options']) as $opt) switch ($opt) {
	case 'd':
  	case 'update-documentation':
  		$DoUpdateDocumentation=TRUE;
		$DidSomething=TRUE;
		break;
	
}





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
	dse_shutdown();
	exit(0);
}elseif(file_exists($argv[1])){
	print md5_of_file($argv[1]);
	dse_shutdown();
	exit(0);
}else{
	print "$argv[1] does not exist or is inaccessable. exiting.\n";
	exit(-1);
	dse_shutdown();
}



///////////////////////////////////////////////////////////////

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



?>