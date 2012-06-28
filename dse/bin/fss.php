#!/usr/bin/php
<?

if(sizeof($argv)>1 && $argv[1]=="--build-cache"){
	if(sizeof($argv)>2){
		$d=str_replace("//","/",$argv[2]."/");
	}else{
		$d="/";
	}
	$CacheFile=$d.".dse-fss-cache-file";
	$ts=time();
	`find $d > $CacheFile`;
	print "Done! ";
	print time()-$ts . " seconds. ";
	print trim(`wc -l $CacheFile`). " files found/indexed. ";
	print "\n";
	exit(0);
}

if(sizeof($argv)>1 && $argv[1]=="-q"){
	$Quiet=TRUE;
	$ss=$argv[2];
	if(sizeof($argv)>3){
		$d=$argv[3];
	}else{
		$d="/";
	}
}else{
	$ss=$argv[1];
	if(sizeof($argv)>2){
		$d=$argv[2];
	}else{
		$d="/";
	}
}



$find_cmd="sudo find $d -iname $ss 2>/dev/null";

if(!$Quiet) print "Searching for: $ss\n";
if(!$Quiet) print "Command: $find_cmd\n";

$out=trim(`$find_cmd`);
$out=str_remove_blank_lines($out);
print $out;

if(!$Quiet) print "\n";

if($out){
	exit(0);
}else{
	exit(1);
}
 
 
function str_remove_blank_lines($Contents){
	$tbr="";
	foreach(split("\n",$Contents) as $L){
		if(trim($L)!=""){
			if($tbr!="") $tbr.="\n";
			$tbr.=$L;
		}
	}
	return $tbr;
}
 
?>
