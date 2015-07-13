<?php


function dse_mail_stats(){
	global $vars;
	
	
	$c="nc -w 1 localhost 25 </dev/null";
	$r=dse_exec($c);
	if($r){
		print "SMTP PORT 25: OK (listening) - answered with: $r";
	}else{
		print "NO LISTNER ON SMTP PORT 25!!!! \n";
	}
	
	
	
	$c="ps -e -o pid,ppid,fname | perl -lane '
    if (\$F[1] != 1) {
        ++\$c{\$F[1]}->{\$F[2]};
    } elsif (\$F[2] eq \"master\") {
        push(@masters, \$F[0]);
    }
    END {
        foreach $master (@masters) {
            print \"=== master: \$master ===\";
            foreach \$agent (keys %{\$c{\$master}}) {
                printf \"\\t%-5d %s\\n\", \$c{$master}->{\$agent}, \$agent;
            }
        }
   }
'";
	/*$r=dse_exec($c);
	if($r){
		print "Mail Processes: - $r";
	}else{
		
	}
	  */
	 
	
	$c="ps -e -o pid,ppid,fname,size,vsize,pcpu";
	$r=dse_exec($c);
	if($r){
		$ra=explode("\n",$r);
		foreach($ra as $l){
			//print "l=$l\n";
			$l=str_replace("\t", " ", $l);	while(str_contains($l,"  ")) $l=str_replace("  ", " ", $l);
			list($pid,$ppid,$exe)=explode(" ",$l);
			if($exe=="master"){
				$mpid=$pid;
			}
			//print "$pid,$ppid,$exe \n";
		}
		if($mpid){
			$mailprocesses=array();
			print "Mail Master Process PID:  $mpid\n";
			foreach($ra as $l){
				$l=str_replace("\t", " ", $l);	while(str_contains($l,"  ")) $l=str_replace("  ", " ", $l);
				list($pid,$ppid,$exe,$size,$vsize,$pcpu)=explode(" ",$l);
				if($ppid==$mpid){
					if(!array_key_exists($exe, $mailprocesses)){
						$mailprocesses[$exe]=array();
					}
					$mailprocesses[$exe][$pid]=array("size"=>$size,"vsize"=>$vsize,"pcpu"=>$pcpu);					
				}
			}
			$pidc=1;
			//$PIDInfo=dse_pid_get_info($mpid);
			$PCPUt=$PIDInfo['PCPU'];
			$PMEMt=$PIDInfo['PMEM'];		
			foreach($mailprocesses as $exe =>$pids){
				$PMEM=0; $PCPU=0; $size=0; $vsize=0;
				print "  - $exe: ";
				foreach($pids as $pid=>$pa){
					$pidc++;
					//$PIDInfo=dse_pid_get_info($pid);
					//$PCPU+=$PIDInfo['PCPU']; $PCPUt+=$PIDInfo['PCPU'];
					$PCPU+=$pa['pcpu']; $PCPUt+=$pa['pcpu'];
					//$PMEM+=$PIDInfo['PMEM']; $PMEMt+=$PIDInfo['PMEM'];
					$size+=$pa['size']; $sizet+=$pa['size'];
					$vsize+=$pa['vsize']; $vsizet+=$pa['vsize'];		
					print " $pid";
				}
				print " (cpu $PCPU   mem $PMEM   size: $size   vsize $vsize ) \n";
			}
			print "  ---- All processes summary: ( processes: $pidc   cpu: $PCPUt    mem: $PMEMt     size: $sizet   vsize $vsizet ) \n";
		}
		
	}else{
		print "No postfix processes running. \n";
	}
	  
	 
	
	
	print "------- Queue Sizes: ---------------\n";
	$counts=array();
    $queues=array("maildrop","hold","incoming","active","deferred"); 
	foreach($queues as $queue){
		$c="/usr/sbin/qshape -s $queue | grep TOTAL";
		$r=dse_exec($c);
		$r=str_replace("\t", " ", $r);
		while(str_contains($r,"  ")) $r=str_replace("  ", " ", $r);
		$counts[$queue]=strcut($r,"TOTAL "," ");
		//print "$c\n$r\n";
		print "  - $queue: ".$counts[$queue]."\n";
	}
	
	
	//postqueue -p
	
	
}

?>