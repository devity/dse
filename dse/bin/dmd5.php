#!/usr/bin/php
<?

if(sizeof($argv)<2){
	print "no argument supplied. STDIN not supported. exiting.\n";
	exit(-1);
}
print md5_of_file($argv[1]);
exit(0);


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
                $m=`md5 -q $f`;
                return ($m);
        }else{
                $sw_vers=dse_which("md5sum");
                if($sw_vers){
                        $m=`md5sum $f`;
						$m=str_replace("\t"," ",$m);
                        $m=strcut($m,""," ");
                        return ($m);
                }
        }
        print "error in md5_of_file(), no md5 utility found. Supported=(md5,md5sum)";
        return -1;
}

?>