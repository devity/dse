#!/usr/bin/php
<?php

$rpms=`rpm -qa`;
foreach(split("\n",$rpms) as $rpm){

	$exists=trim(`find /backup/rpms -iname ${rpm}*`);
	if( strstr($exists,$rpm)===FALSE ){
		print "extracting $rpm\n";
		print `rpmrebuild -n -b -d /backup/rpms/ $rpm`;
	}else{
		print "$exists exists.\n";
	}

}

exit(0);

?>
