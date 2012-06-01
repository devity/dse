#!/usr/bin/php
<?

if(sizeof($argv)<3){
	print "no arguments supplied. STDIN not supported. usage: chom username:group:permissions file\nexiting.\n";
	exit(-1);
}
$pa=split(":",$argv[1]);
$u=$pa[0];
$g=$pa[1];
$p=$pa[2];
$f=$argv[2];
$r=`chown $u:$g $f`;
print $r;
$r=`chmod $p $f`;
print $r;

?>