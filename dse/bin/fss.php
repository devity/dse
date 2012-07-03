#!/usr/bin/php
<?

error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");

$Quiet=FALSE;
$ReturnFirstOnly=FALSE;

if(sizeof($argv)>1 && $argv[1]=="--build-cache"){
	if(sizeof($argv)>2){
		$d=str_replace("//","/",$argv[2]."/");
	}else{
		$d="/";
	}
	$CacheFile=$d.".dse-fss-cache-file";
	$ts=time();
	$Command="sudo find $d > $CacheFile";
	dse_exec($Command,TRUE);
	print "Done! ";
	print time()-$ts . " seconds. ";
	print trim(`wc -l $CacheFile`). " files found/indexed. ";
	print "\n";
	exit(0);
}

if(sizeof($argv)>1 && $argv[1]=="-q"){
	$Quiet=TRUE;
	
	if(sizeof($argv)>2 && $argv[2]=="-f"){
		$ReturnFirstOnly=TRUE;
		
		$ss=$argv[3];
		if(sizeof($argv)>4){
			$d=$argv[4];
		}else{
			$d="/";
		}
	}else{
		
		
		$ss=$argv[2];
		if(sizeof($argv)>3){
			$d=$argv[3];
		}else{
			$d="/";
		}
	}
}else{
	$ss=$argv[1];
	if(sizeof($argv)>2){
		$d=$argv[2];
	}else{
		$d="/";
	}
}
//print "hi!";
$CacheFile=$d.".dse-fss-cache-file";
	
if(file_exists($CacheFile)){
	if(!$Quiet) print " using cache file: $CacheFile\n";
	$find_cmd="sudo grep -i \"$ss\" $CacheFile 2>/dev/null";
}else{
	if(!$Quiet) print " cache file: $CacheFile not present. rebuild w/ --rebuild-cache\n";
	$find_cmd="sudo find $d -iname \"$ss\" 2>/dev/null";
}
if(!$Quiet) print "Searching for: $ss\n";
if(!$Quiet) print "Command: $find_cmd\n";

$out=trim(`$find_cmd`);
$out=str_remove_blank_lines($out);
$out=str_ireplace($ss,colorize($ss,"black","yellow"),$out);

$Li=0;
if($out){
	if($ReturnFirstOnly){
		$ra=split("\n",$out);
		foreach($ra as $L) if($L) {
			$Li++;
			print colorize($Li,"cyan","black");
			print colorize(": ","blue","black");
			print $L;
			break;
		}
	}else{
		print $out;
	}
}else{
	if(!$Quiet) print "No Matches";
}


if(!$Quiet) print "\n";

if($out){
	exit(0);
}else{
	exit(1);
}
 

?>
