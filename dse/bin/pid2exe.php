#!/usr/bin/php
<?
ini_set('display_errors','On');	
error_reporting(E_ALL & ~E_NOTICE);
$Verbosity=3;


$Script=$argv[0];
$search_str=$argv[1];

/*
function ReadStdin($prompt, $valid_inputs, $default = '') { 
    while(!isset($input) || (is_array($valid_inputs) && !in_array($input, $valid_inputs)) || ($valid_inputs == 'is_file' && !is_file($input))) { 
        echo $prompt; 
        $input = strtolower(trim(fgets(STDIN))); 
        if(empty($input) && !empty($default)) { 
            $input = $default; 
        } 
    } 
    return $input; 
} 

stream_set_blocking(STDIN, 0);
$stdin = fgetcsv(STDIN);
 * *
 */
$stdin=fgets(STDIN);
$stdin=trim($stdin);				
										
$dollar='$';
$cmd="ps -p $stdin | grep \"$stdin\" | grep -v grep | awk '{ print ${dollar}4 }'";


if($Verbosity>=2){
	//print "Script: $Script\n";
	//print "Command line: $Script $search_str\n";
	//print_r($argv)."\n";
	//print "cmd: $cmd\n";
	//print "STDIN=$stdin\n";
}


$PID=trim(`$cmd`);


if($Verbosity>=2){
	//print "PID: ";
}
print "$PID";



?>