#!/usr/bin/php
<?

$ss=$argv[1];
if(sizeof($argv)>2){
	$d=$argv[2];
}else{
	$d="/";
}

print "Searching for: $ss\n";

$find_cmd="sudo find $d -iname $ss 2>/dev/null";
print "Command: $find_cmd\n";
$out=`$find_cmd`;
print $out ."\n";

exit();

 
?>
