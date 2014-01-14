#!/usr/bin/php
<?php
$PFile="/tmp/primes.txt";
$n=8;

for($ni=1;$ni<50;$ni++){
$ts=time();
$n=ngpp($ni);
$p=nip($n);
$tr=time()-$ts;
print "[$tr s] ";
if($p){
	print "$n is PRIME\n";
	file_put_contents($PFile,"$n\n",FILE_APPEND);
}else{

	print "$n is NOT prime\n";
}
}

function ngpp($tp){
	//$tp=rand(1, 118);
	$c="(2^$tp)-1";
	$n=nbc($c);
	print "(2^$tp)-1=$n\n";
	return $n;

}




function nip($n){
	$f=2;
	//$fm=nbc(" ($n ^ .5)+1");
	$fm=nbc(" ($n /2)+1");
	while(nil($f,$fm)){
		$d=nbc("$n / $f");	
		$nr=nbc("$d * $f");
//		print "nr==n   $nr==$n\n";
		if(strstr($nr,$n)){
			print " ( $f x $d = $nr == $n ) ";
			return FALSE;
		}
		$f=nbc("$f + 1");
	}
	return TRUE;
}

function nil($a,$b){
	$diff=nbc("$a - $b");
//	print "nil($a,$b)=$diff";
	if(strstr($diff,"-")){
//		print "TRUE\n";
		return TRUE;
	}else{
//		print "FALSE\n";
		return FALSE;
	}
}


function nbc($bc){
	$bcc="echo '$bc' | bc";
//	print "$bcc = ";

	$tbr=`$bcc`;
	$tbr=trim($tbr);
//	print "$tbr\n";
	return $tbr;
}





?>
