<?php



function dse_show_w_hover($Show,$Hide){
	global $vars;
	return "<span href=\"#\" class=\"showhim\">$Show<div class=\"showme\" style='border:2px solid orange;background:#FFF3C3;'>$Hide</div></span>";
}
function dse_print_page_footer(){
	global $vars;
	
	if(!$vars[SkipFooter]){
		print "<BR><BR><BR><div style='background:#eeeef6;border-top:1px dotted black;'>"
			."<table width=100% border=0 cellpadding=0 cellspacing=0><tr style='font-size:6pt;color:#888888;'>"
			."<td align=right>DSE for download at: 
			<a href=https://github.com/devity/dse target=_blank>https://github.com/devity/dse</a>
			- developed by <a href=http://www.devity.com target=_blank>Devity.com</a> 
			</td></tr></table> 
		</div></BODY></HTML>";
	}
}

function dse_print_page_header(){
	global $vars; dpd_trace();
	
	$page_bg_color="#F4F4FC";
	$Title="DSE Web Interface";
	
	print "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">
<HTML><HEAD><title>$Title</title>
<STYLE TYPE=\"text/css\"> 
body { font-size: 9pt; }
body, th, td, ol, ul, li { font-style: normal; font-family: verdana, arial, helvetica, sans-serif; color: #00237B;	}
body { background: $page_bg_color; margin: 0; padding: 0; 	}

a:link,a:active { color : #0000ff; } 
a:visited { color : #7401FF; }
a:hover	{ text-decoration: underline; color : #FF00FF; }
ul {margin-top: 2; margin-bottom: 5; margin-right: 2;}
.collapsible { display: none; border: dashed 1px silver; padding: 5px; }
ins {color:green;background:#ddffdd;border:1px solid green;}
del {color:red;background:#ffdddd;border:1px solid red;}

.f4pt {font-size: 4pt;}
.f5pt {font-size: 5pt;}
.f6pt {font-size: 6pt;}
.f7pt {font-size: 7pt;}
.f8pt {font-size: 8pt;}
.f9pt {font-size: 9pt;}
.f10pt {font-size: 10pt;}
.f11pt {font-size: 11pt;}
.f12pt {font-size: 12pt;}
.f13pt {font-size: 13pt;}
.f14pt {font-size: 14pt;}
.f15pt {font-size: 15pt;}
.f16pt {font-size: 16pt;}
.f17pt {font-size: 17pt;}
.f18pt {font-size: 18pt;}
.f19pt {font-size: 19pt;}
.f20pt {font-size: 20pt;}
.f21pt {font-size: 21pt;}
.f22pt {font-size: 22pt;}
.f23pt {font-size: 23pt;}
.f24pt {font-size: 24pt;}
.f25pt {font-size: 25pt;}
.small { font-size: 8pt; }

.showme{ 
display: none;
}
.showhim:hover .showme{
display : block;
}
</STYLE>";
?>
<script type="text/javascript">



function ShowHideLayer(boxID) {
	var box = document.getElementById("box"+boxID);
	var boxbtn = document.getElementById("btn"+boxID);	
	if(box.style.display == "none" || box.style.display=="") {
		box.style.display = "block";
 		boxbtn.src = "/images/collapse2.gif";
	}else{
		box.style.display = "none";
		boxbtn.src = "/images/expand2.gif";
	}
}
function ShowLayer(boxID) {
	var box = document.getElementById("box"+boxID);
	var boxbtn = document.getElementById("btn"+boxID);	
	box.style.display = "block";
 	boxbtn.src = "/images/collapse2.gif";
}
function HideLayer(boxID) {
	var box = document.getElementById("box"+boxID);
	var boxbtn = document.getElementById("btn"+boxID);	
	box.style.display = "none";
	boxbtn.src = "/images/expand2.gif";
}
function ToggleCollapsibleMinLayer(boxID) {
	var box = document.getElementById("box"+boxID);
	var boxbtn = document.getElementById("btn"+boxID);	
	if(box.style.display == "none" || box.style.display=="") {
		box.style.display = "block";
 		boxbtn.src = "/images/collapse.gif";
	}else{
		box.style.display = "none";
		boxbtn.src = "/images/expand.gif";
	}
}
</script>
<?
 
if($vars[ServerLoad]>3){
	$LoadColor="red";
}elseif($vars[ServerLoad]>1){
	$LoadColor="orange";
}else{
	$LoadColor="green";
}
print "</HEAD><BODY bgcolor='$page_bg_color'>";
if(!$vars[SkipHeader]){
	
	$pid=getmypid();
	
	$process_info="pid:$pid";
	
	
	if(str_contains($vars['SITE_ROOT'], "dev-")){
		$Codebase="DEV";
	}else{
		$Codebase="PRD";
	}
	$process_info.=" &nbsp; code:$Codebase";
	
	print "<div style='background:#bbbbbf;border-bottom:1px dotted black;'><table width=100% border=0 cellpadding=0 cellspacing=0><tr>"
			."<td style='font-size:8pt;'><b style='font-size:10pt;'><a href=/>$Title</a></b> &nbsp;:&nbsp; "
			
		//	."  <a href=/dse_admin/apc/apc.php>APC</a>,&nbsp; "
			
			
			."  &nbsp; "
			
			."  &nbsp; "
			. "</td><td align=center class='f5pt'>"
				
			//."  <a href=/dse_admin/utils/code.php?PageType=ShowUnPublished>Publisher</a>  "
			."  &nbsp; "
			
			. "</td><td align=center class='f5pt'>$process_info"
			
			. "</td><td align=center><a href=/dse_admin/utils/ss.php style='font-size:8pt;'>Load: <font color=$LoadColor><b>$vars[ServerLoad]</b></font></a></td>"

			."<td align=right style='font-size:7pt;color:#444449;'>$vars[UserEmail] <a href=/user/logout>logout</a></td></tr></table></div>";
}
}


function on_shutdown(){
	global $vars,$_SERVER,$HTTP_POST_VARS;	
	dpd_trace();
	
	session_write_close();

	$vars[PageShutdownTime]=time()+microtime(); 		
	if($vars[PostGenerationDatabaseCommands]){
		foreach($vars[PostGenerationDatabaseCommands] as $Command){
			do_db_command($Command);
		}
	}				
	if($vars[DeleteRequestHistoryLog]){		
		//do_db_command("DELETE FROM PageRequestHistory  WHERE ID='$vars[PageRequestHistoryID]'");
		$vars[PR_Insert]="";
	}elseif( (!$vars[NoEndPRHUpdate]) && (!$vars[SkipPageRequestHistoryLog]) && $vars[PR_Insert] ){
		if($vars[IsAdminHoldTillEnd]) $vars[IsAdmin]=TRUE;
			
		$vars[PageEndTime]=time()+microtime(); 
		$PageRunTime=$vars[PageEndTime]-$vars[PageStartTime];
		$runtime=$PageRunTime;	
		$cldbruntime=$vars[Database_CraftLister]->TotalRunTime;
		$qpdbruntime=$vars[Database]->TotalRunTime;
		$dbruntime=$cldbruntime+$qpdbruntime;	
	
		$TGT=intval($runtime*1000);
		$DBT=intval($dbruntime*1000);
		if(!$vars[IsAPICall]){
			if(($vars[IsAdmin] || $vars[RequestTrustLevel]<=1) && $vars[UserNumber]<=0){
				$vars[PageRequestHistoryExtraClause].=", LoggedIn='ADMIN', UserNumber='0'";		
			}else{
				if($vars[LoggedIn]){
					$vars[PageRequestHistoryExtraClause].=", LoggedIn='YES', UserNumber='$vars[UserNumber]'";		
				}else{
					$vars[PageRequestHistoryExtraClause].=", LoggedIn='NO', UserNumber='0'";		
				}
			}
		}
		
		if($vars[SentLoginForm] || ( $vars[RequireLogin] && (!$vars[LoggedIn]) ))	{			
			$vars[PageRequestResult]="LS";
		}	
		//print "PageRequestResult=$vars[PageRequestResult]<br>";
			
		if(!$vars[HeaderStartTime]){
			$vars[HeaderStartTime]=$vars[ConfigEndTime];
		}
		if(!$vars[HeaderEndTime]){
			$vars[HeaderEndTime]=$vars[ConfigEndTime];
		}
		if(!$vars[FooterStartTime]){
			$vars[FooterStartTime]=$vars[PageShutdownTime];
		}
		if(!$vars[PageShutdownTime]){
			$vars[PageShutdownTime]=$vars[PageShutdownTime];
		}
		/*
		$t1=number_format($vars[ConfigEndTime]-$vars[PageStartTime],3,".","");
		$t2=number_format($vars[HeaderStartTime]-$vars[ConfigEndTime],3,".","");
		$t3=number_format($vars[HeaderEndTime]-$vars[HeaderStartTime],3,".","");
		$t4=number_format($vars[FooterStartTime]-$vars[HeaderEndTime],3,".","");
		$t5=number_format($vars[PageShutdownTime]-$vars[FooterStartTime],3,".","");
		$tpts="$t1;$t2;$t3;$t4;$t5";
		//$vars[PageRequestHistoryExtraClause].=", TPts='$tpts' ";		
		//print ".TPts=$tpts<br>"; 
		*/
		//Cfg / PreHdr / Headr / Body / ShtDwn		
		if($vars[RequestTrustLevel]) $vars[PageRequestHistoryExtraClause].=", TrustLevel='$vars[RequestTrustLevel]' ";		
		if($vars[RequestIsAttack]) {
			$vars[PageRequestHistoryExtraClause].=", AttackLogID='$vars[RequestAttackLogID]' ";		
		}
		if($vars[PageRequestResult]){			
			$vars[PageRequestHistoryExtraClause].=", Result='$vars[PageRequestResult]' ";
		}	
		if( $vars[Config][LoadLevel]>3 && ($vars[PageRequestResult]=="IPB" || $vars[PageRequestResult]=="PLT" || $vars[RequestIsAttack])){
			//dont wast time logging to prh table
		}else{
			//if(!$vars[IsAdmin]){
				do_db_command("$vars[PR_Insert], TotalGenTime='$TGT', DBTime='$DBT' $vars[PageRequestHistoryExtraClause]");
			//}
		}
		//print "extra=$vars[PageRequestHistoryExtraClause]<br>";
	}	
	if($vars[PrintableFormat]){
		return;
	}	

	if(  ($vars[SkipFooter])  ){
		exit();
	}
	print "<div id='debugerrors' class='non_print' style='background:#ddddef;' >";	
		
	$my_pid = getmypid();
	$PageRunTime=number_format($vars[PageEndTime]-$vars[PageStartTime],2);
	$runtime=$PageRunTime;	
	$qpdbruntime=number_format($vars[Database]->TotalRunTime,2);
	$dbruntime=$cldbruntime+$qpdbruntime;	
	

	if(TRUE || $vars['Debug']>=1 || $vars[DBFailedCommands]>0){
		$MemoryRaw=trim(`ps -eo%mem,rss,pid | grep $my_pid`);
		$MemoryArray=split(" ",$MemoryRaw);
		$MemoryPercent=$MemoryArray[0];
		$MemorykB=$MemoryArray[1];
		$PID=$MemoryArray[2];
		//if($vars[DBFailedCommands]>0){
		//	print "<div style='z-index: 10; position: absolute; left: 40; top: 25; background: white;'><font color=red class='f15pt'><b>$vars[DBFailedCommands] Failed DB Queries</b></font></div>";
		//}
		print "<i>Page Info:</i> &nbsp;		 ";
		//	<b>RunTime:</b> ${runtime}s 	<b>DB:</b> ". $dbruntime ."s &nbsp; &nbsp;
		
		print return_collapsible_min_area_start(FALSE);
		print "<b>Memory Usage:</b>  $MemoryPercent%  @ ${MemorykB}kB in PID $PID<br>";
		$dat = getrusage();
		$sec=$dat[ru_utime.tv_sec];
		$usec=$dat[ru_utime.tv_usec];
		print "<b>Usage:</b> <br>
			&nbsp;&nbsp;&nbsp;  swaps: $dat[ru_nswap] <br>
			&nbsp;&nbsp;&nbsp;  faults: $dat[ru_majflt] <br>
			&nbsp;&nbsp;&nbsp;  time: $sec.$usec seconds <br>
		<br>"; 
		
			
		
		
		//print "<div id='debug' style='display:none;' class='non_print'>";
		
		print"<br><br>";	
		print return_collapsible_min_area_end();
		print "Times &nbsp; ";	
		if($vars[DBFailedCommands]>0){
		//if($vars[Database]->DatabaseHistoryErrors){
			print "<div style='background:#efdddd;border-right:1px solid red;border-bottom:3px solid red; padding:2px; position: fixed;  top:0px; left:0px; width:200px;'>";
			//print return_collapsible_min_area_start(TRUE);
			print "<b class='f15pt'> <font color=red>DB Error(s) ! </font> </b> ";
			print "<br><font class='f7pt'>" . $vars[Database]->DatabaseHistoryErrors ."</font><br>";
			//print return_collapsible_min_area_end();
			//print "Errors &nbsp; ";
			print "</div>";
			
			print return_collapsible_min_area_start(FALSE);
			print "<b class='f15pt'> <font color=red>DB Error(s) ! </font> </b> ";
			print "<br><font class='f7pt'>" . $vars[Database]->DatabaseHistoryErrors ."</font><br>";
			print return_collapsible_min_area_end();
			print "Errors &nbsp; ";
		}
		
		if($vars[Database]->DatabaseHistory){
			print return_collapsible_min_area_start(FALSE);
			print "
				<b>QP Main DB User:</b> " . $vars[Database]->Hostname . "<br>
				<b>QP Inserts DB Used:</b> " . $vars[Database]->HostnameInserts . "<br>			
				DB History:<br>" . $vars[Database]->DatabaseHistory ."<br>";
			print return_collapsible_min_area_end();
			print "DB &nbsp; ";
		}
		
	
	//	include_once "$vars[SITE_ROOT]/admin/scripts/webserver_file_manager_functions.php";
	
		//SCRIPT_FILENAME
		//PHP_SELF
		$Dir=dirname($_SERVER[PHP_SELF]);
		//print "Web File Manager: <a href=/admin/scripts/webserver_file_manager.php?PageType=FileManager&Dir=/usr/local/webroot/dev-craftlister_com$Dir>$Dir</a><br>";
		//print "<b>Web File Manager links for this File and subdirectories:</b><br> <a class='f14pt' href=/admin/scripts/webserver_file_manager.php?PageType=FileManager&Dir=/usr/local/webroot/dev-craftlister_com/>/</a>";
		print return_collapsible_min_area_start(FALSE);
		foreach(split("/",$Dir) as $p){
			if($p){
				$tDir.="/$p";
				print " /<a href=/admin/scripts/webserver_file_manager.php?PageType=FileManager&Dir=/usr/local/webroot/dev-craftlister_com$tDir/>$p</a>";
			}
		}
	//	print "<br><b>Web File Manager links for Included Files:</b><br>";
		$included_files = get_included_files();		
		foreach($included_files as $filename) {
			$tDir="";
			//print "&nbsp;&nbsp;&nbsp; $filename<br>";
			//$filename=substr($filename,strpos($filename,"webroot")+strlen("webroot"));
			$basefilename=basename($filename);
			foreach(split("/",$filename) as $p){
				if($p){
					$tDir.="/$p";
					if($p==$basefilename){
						$p="<b>$p</b>";
					}
					print " /<a href=/admin/scripts/webserver_file_manager.php?PageType=FileManager&Dir=/usr/local/webroot$tDir/>$p</a>";
					if(str_contains($p,".php") || str_contains($p,".inc") || str_contains($p,".txt") || str_contains($p,".htaccess")){
						$url=dpd_ide_file_open_url($filename);
						print "<a href=$url>&crarr;</a>";
					}
				}				
			}
			$filename=str_replace("/dev-craftlister_com","",$filename);
			$filename=str_replace("/prd-craftlister_com","",$filename);
			print "&nbsp; <a class='f7pt' href=/admin/scripts/webserver_file_manager.php?PageType=FileHistory&Action=FileHistory&FileName=$filename>history</a>";
			print "<br>";
		}
		//	print "<br>";		
		//print "<br>Web Publish: <a href=/admin/scripts/webserver_file_manager.php><font color='#8888ff'>$Status</font></a> <br>";
		//print "<br><hr>";	
		//print "<b class='f12pt'>Page Validation:</b> <a href=http://validator.w3.org/check/referer>html &#10004;</a> &nbsp;";
		//print "<a href=http://jigsaw.w3.org/css-validator/check/referer>css &#10004;</a> <br>";	
		print return_collapsible_min_area_end();
		print " Files &nbsp; ";
	
	}
	
	//print "[1]";

	if(FALSE && $vars['Debug']>=2){	
		print return_collapsible_min_area_start(FALSE);
		print " Database History: <br> " . $vars[Database]->DatabaseHistory . " <br><br>	";
		print return_collapsible_min_area_end();
		print " DB &nbsp; ";
	}
	if($vars['Debug']>=2){	
		if( $_SERVER[HTTPS] == "on" ){
			print return_collapsible_min_area_start(FALSE);
			print "<pre>";
			print "\$_SERVER=";		
			print debug_tostring($_SERVER);
			print "<br>\$_REQUEST=";		
			print debug_tostring($_REQUEST);
			print "<br>\$HTTP_POST_VARS=";		
			print debug_tostring($HTTP_POST_VARS);
			//print "<br>\$vars=";		
			//print debug_tostring($vars);
			print "</pre>";
		//	phpinfo();
			print return_collapsible_min_area_end();
			print " Variables &nbsp; ";
		}else{
			print " &nbsp; \$vars=(goto https) &nbsp; ";
		}
	}
	
	
	
	
	if($vars[dpd_Trace_Stack] && $vars['Debug']>=1){
			
	
		print return_collapsible_min_area_start(FALSE);
		$tn=0;
		foreach ($vars[dpd_Trace_Stack] as $t){
			$tn++;
			$LevelsDeep=sizeof($t);
			$last=$t[sizeof($t)-1];
			$args="";
			$tt=$last['args'];
			if(is_array($last) && is_array($last['args']) && $last['args']) foreach($last['args'] as $a){
				if($args){
					$args.=", ";
				}
				if(is_object($a)){
					$n=get_class($a);
					$a="($n object)";
				}
				$args.=" [$a]";
			}
			$call=$last['function']."($args)";
			$call=dpd_debug_bt2html($t,$tn);
			print $call;
		}
		print return_collapsible_min_area_end();
		print " Trace &nbsp; ";
	
	
		print return_collapsible_min_area_start(FALSE);
		foreach ($vars[dpd_Trace_phpfiles] as $phpfile=>$a){
			print "File: $phpfile<br>";
			foreach($a as $phpfunction=>$phpfunction_a){
				$callcount=$phpfunction_a[0];
				$firsttime=$phpfunction_a[1];
				$lasttime=$phpfunction_a[2];
				$totaltime=$phpfunction_a[3];
				print " &nbsp; ${phpfunction}() - $callcount calls, $totaltime<br>";
			}
		}
		print return_collapsible_min_area_end();
		print " Functions &nbsp; ";
	
	
		
	}
	
	print "<br><i>Server Info: &nbsp; </i> ";
	
	
	
	$Contents=`cat /etc/dse/dse.conf`;
	if($Contents){
		print return_collapsible_min_area_start(FALSE);
		print text2html($Contents);
		print return_collapsible_min_area_end();
		print " dse.conf &nbsp; ";	
	}	
	
	$Contents=`cat /etc/dse/apache2.conf`;
	if($Contents){
		print return_collapsible_min_area_start(FALSE);
		print text2html($Contents);
		print return_collapsible_min_area_end();
		print " dse httpd.conf &nbsp; ";	
	}	
	
	$Contents=`cat /etc/dse/server.conf`;
	if($Contents){
		print return_collapsible_min_area_start(FALSE);
		print text2html($Contents);
		print return_collapsible_min_area_end();
		print " server.conf &nbsp; ";	
	}	
	
	$Contents=`cat /etc/httpd.conf`;
	if($Contents){
		print return_collapsible_min_area_start(FALSE);
		print text2html($Contents);
		print return_collapsible_min_area_end();
		print " httpd.conf &nbsp; ";	
	}	
	
	$Contents=`/scripts/iptables-nvL 2>&1`;
	if($Contents){
		print return_collapsible_min_area_start(FALSE);
		print text2html($Contents);
		print return_collapsible_min_area_end();
		print " iptables -nvL &nbsp; ";	
	}	
	
	
	
	
//	print "[3]";
	//print "vars['Debug']=".$vars['Debug']."<br>";
	//print "vars[dpd_Trace_Stack]=".debug_tostring($vars[dpd_Trace_Stack])."<br>";
	if($vars['Debug']>1){	
	//	print "<br>";
	}
	//`rm -rf $vars[TMP_INFO_FILE]`;
	//print "</div>";
	
	//if($vars[PageRequestHistoryID] && $vars[Config][LoadLevel]<7){
	//if($vars[Config][LoadLevel]<7){
	
	//v}
	print "</div>";
}
		
		

function collapsible_area($HTML,$Show){
	global $vars;	dpd_trace();
	print return_collapsible_area($HTML,$Show);
}		
function return_collapsible_area($HTML,$Show){
	global $vars;	dpd_trace();
	$ID=$vars[collapsible_area_next_ID];
	if($ID<=0){
		$ID=1;
	}
	$vars[collapsible_area_next_ID]=$ID+1;	
	return return_collapsible_area_start($Show).$HTML.return_collapsible_area_end();
}	
function return_collapsible_area_start($Show){
	global $vars;	dpd_trace();
	$ID=$vars[collapsible_area_next_ID];
	if($ID<=0){
		$ID=1;
	}
	//
	if($Show){ 
		$display=" style='display:block;'";
		$img="/images/collapse2.gif";
	}else{
		$display=" class=\"collapsible\"  ' ";
		$img="/images/expand2.gif";
	}
	$vars[collapsible_area_next_ID]=$ID+1;	
	return "<a href=\"javascript:;\" onclick=\"ShowHideLayer($ID);\">
<img src=\"$img\" alt=\"Click to Toggle Expand\" name=\"btn$ID\" width=\"85\" height=\"9\" border=\"0\" id=\"btn$ID\" />
</a><div id=\"box$ID\" style=\"border:1px dashed ".$vars[ThemeArray][T]."; background:".$vars[ThemeArray][CB].";\" $display>";
}
function return_collapsible_area_end(){
	global $vars;	dpd_trace();
	return "</div>";	
}


function collapsible_min_area($HTML,$Show){
	global $vars;	dpd_trace();
	print return_collapsible_min_area($HTML,$Show);
}		
function return_collapsible_min_area($HTML,$Show){
	global $vars;	dpd_trace();
	$ID=$vars[collapsible_area_next_ID];
	if($ID<=0){
		$ID=1;
	}
	$vars[collapsible_area_next_ID]=$ID+1;	
	return return_collapsible_min_area_start($Show).$HTML.return_collapsible_min_area_end();
}	
function return_collapsible_min_area_start($Show){
	global $vars;	dpd_trace();
	$ID=$vars[collapsible_area_next_ID];
	if($ID<=0){
		$ID=1;
	}
	
	if($Show){ 
		$display="style='display:block;' ";
		$img="/images/collapse.gif";
	}else{
		$display=" class=\"collapsible\"  ";
		$img="/images/expand.gif";
	}
	$vars[collapsible_area_next_ID]=$ID+1;	
	return "<a href=\"javascript:;\" onclick=\"ToggleCollapsibleMinLayer($ID);\">
	<img src=\"$img\" alt=\"Click to Toggle Expand\" name=\"btn$ID\" width=\"9\" height=\"9\" border=\"0\" id=\"btn$ID\" />
	</a><span id=\"box$ID\" style=\"border:1px dashed ".$vars[ThemeArray][T]."; background:".$vars[ThemeArray][CB].";\" $display>";
}
function return_collapsible_min_area_end(){
	global $vars;	dpd_trace();
	return "</span>";	
}


function start_feature_box($title,$width){	
	global $vars;	dpd_trace();
	print return_start_feature_box($title,$width);
}


function return_start_feature_box($title,$width){	
	global $vars;	dpd_trace();
	$tbr="";
	if($vars[SKIP_FEATURE_BOX])	return;	
	if($vars[BOX_TYPE]=="Menu"){
		start_menu_box($title,$width);
		return;	
	}	
	$rawwidth=$width;
	if(!$width)	$width=500;	
	$tbr.= "<table width=$width border=0 cellpadding=0 cellspacing=0 >";
	if($title){
		$leading_corner="<td align=left valign=top>&nbsp;</td>";
		$tbr.= "<tr><td align=left><table border=0 cellpadding=0 cellspacing=0 bgcolor='$vars[color_box_feature_title_background]'>
<tr>$leading_corner<td valign=bottom><font color='$vars[color_box_feature_title_text]' class='f13pt'><b>&nbsp;$title&nbsp;</B></font></td>
<td align=right valign=top>&nbsp;</td></tr></table></td></tr>";
	}		
	$tbr.= "<tr><td align=center><div style=\"border: solid 1px $vars[color_box_feature_border];padding:4px;background-color:$vars[color_box_feature_background]\"><table><tr><td>";
	return $tbr;
}

function end_feature_box(){	
	global $vars;	dpd_trace();
	print return_end_feature_box();
}

function return_end_feature_box(){	
	global $vars;	dpd_trace();
	$tbr="";
	if($vars[SKIP_FEATURE_BOX])	return;
	if($vars[BOX_TYPE]=="Menu"){
		end_menu_box($title,$width);
		return;	
	}	
	$tbr.= "</td></tr></table></div></td></tr></table>";	
	return $tbr;
}



function text2html($text){
	//this function is depricated
	return ConvertTextToHTML($text);		
}

function ConvertTextToHTML($text){
    global $vars;
    if(!$vars[QP_AutoLinkOff]){   
    	$text=AutoLink($text);	    
    }    
	
	$text=str_replace("<","&lt;",$text);
	$text=str_replace("&lt;bullet>","&nbsp;&nbsp; $vars[bullet] &nbsp;&nbsp;",$text);
	$text=str_replace("&lt;tab>","&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$text);
	$text=str_replace("\n","<br>",$text);
	$text=str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$text);
	$text=str_replace("&lt;br&gt;","<br>",$text);
	$text=str_replace("\n\n","\n",$text);
	$text=str_replace("  ","&nbsp;&nbsp;",$text);
	$text=str_replace(":)","<img alt=smile src=/images/icons/smile_smile.gif>",$text);
	$text=str_replace(";)","<img alt=wink src=/images/icons/smile_wink.gif>",$text);
	$text=str_replace(":-)","<img alt=smile src=/images/icons/smile_smile.gif>",$text);
	$text=str_replace(";-)","<img alt=wink src=/images/icons/smile_wink.gif>",$text);
	$text=str_replace(":-(","<img alt=frown src=/images/icons/smile_frown.gif>",$text);
	$text=str_replace(";-(","<img alt=frown src=/images/icons/smile_frown.gif>",$text);
	return $text;        		
}
    
function AutoLink($return) { 
   //$return = eregi_replace( "([[:alnum:]]+)://([^[:space:]]*)([[:alnum:]#?/&=])",  "<a href=\"\\1://\\2\\3\" target=\"_blank\">\\2\\3</a>", $return);
   //$return = eregi_replace( "(([a-z0-9_]|\\-|\\.)+@([^[:space:]]*)([[:alnum:]-]))",  "<a href=\"mailto:\\1\" target=\"_new\">\\1</a>", $return);    
   // $return = eregi_replace( "([ ]|\n)([[:alnum:]]+)://([^[:space:]]*)([[:alnum:]#?/&=])",  " <a href=\"\\2://\\3\\4\" target=\"_blank\" target=\"_new\">\\3\\4</a>", $return);
	$return=preg_replace("/([ ]|\n|^)www./i","http://www.",$return);	
	//$return = preg_replace( "/(?<!<a href=\")((http|ftp)+(s)?:\/\/[^<>\s]+)/i", " <a target=_new href=\"$0\">$0</a>",$return); 
	//$return = preg_replace( "/(?<!=\")((http|ftp)+(s)?:\/\/[^<>\s]+)/i", " <a target=_new href=\"$0\">$0</a>",$return); 
	
	$return = preg_replace( "/(?<![=\"\'])((http|ftp)+(s)?:\/\/[^<>\s]+)/i", " <a target=_new href=\"$0\">$0</a>",$return); 
	$return = preg_replace( "/([ ]|\n|^)([a-zA-Z0-9_-]+[.](com|net|org))/i", "\\1<a target=_new href=\"http://www.\\2\">\\2</a>",$return);
    $return = preg_replace("/([ ]|\n|^)([a-zA-Z0-9_-]+@[a-zA-Z0-9_.-]+[a-zA-Z0-9]+)/","\\1<a href=mailto:\\2>\\2</a>",$return); 
	return $return;
}


$dpd_Trace_Stack=Array();
$vars[dpd_Trace_Stack]=Array();
$vars[dpd_Trace_Indent_String]="&nbsp; &nbsp; + ";
$vars[dpd_Trace_Indent_Current]=0;
$vars[dpd_Trace_Count]=0;
$vars[dpd_Trace_Count_Max]=3000;
function dpd_trace($str=""){
	//$tbr=debug_tostring($bt);
	global $vars,$dpd_Trace_Stack;
    if(!$vars[dpd_enable_debug_code]) return;
	$vars[dpd_Trace_Count]++;
    if( $vars[dpd_Trace_Count]>$vars[dpd_Trace_Count_Max] ) return;
	
   	$bt=debug_backtrace();
	if($vars[dpd_enable_debug_code_markpoints_in_html]){
		$section=$vars[dpd_Trace_Count];
		print "<font class='f7pt'>[<A href=#section$section>t".$vars[dpd_Trace_Count]."</a>]</font>";
	}
   	//array_walk( debug_backtrace(), create_function( '$a,$b', 'print "<br /><b>". basename( $a[\'file\'] ). "</b> &nbsp; <font color=\"red\">{$a[\'line\']}</font> &nbsp; <font color=\"green\">{$a[\'function\']} ()</font> &nbsp; -- ". dirname( $a[\'file\'] ). "/";' ) ); 
	 
	 
	 
	$LevelsDeep=sizeof($bt);
	$last=$bt[sizeof($bt)-2];
	$phpfile=$last['file'];
	$phpfunction=$last['function'];
	$vars[dpd_Trace_phpfile_list][]=$phpfile;
	if(!$vars[dpd_Trace_phpfiles][$phpfile]){
		$vars[dpd_Trace_phpfiles][$phpfile]=array();
	}
	if(!$vars[dpd_Trace_phpfiles][$phpfile][$phpfunction]){
		$vars[dpd_Trace_phpfiles][$phpfile][$phpfunction]=array();
		$LastFunctionCallTime=0;
		$vars[dpd_Trace_phpfiles][$phpfile][$phpfunction][0]=0;
		$vars[dpd_Trace_phpfiles][$phpfile][$phpfunction][1]=time_float();
	}else{
		$LastFunctionCallTime=$vars[dpd_Trace_phpfiles][$phpfile][$phpfunction][1];
	}
	$vars[dpd_Trace_phpfiles][$phpfile][$phpfunction][0]++;
	$vars[dpd_Trace_phpfiles][$phpfile][$phpfunction][2]=time_float();
	if($LastFunctionCallTime>0){
		$FunctionRunTime=time_float()-$LastFunctionCallTime;
		$vars[dpd_Trace_phpfiles][$phpfile][$phpfunction][3]+=$FunctionRunTime;
	}else{
		$vars[dpd_Trace_phpfiles][$phpfile][$phpfunction][3]=0;
	}
	$vars[dpd_Trace_Stack][]=$bt;
}



function dpd_whereami(){
	global $vars;
   	$bt=debug_backtrace();
   	
	print "whereami: "; 
	print_r($bt);
	print "<br>";
}

function dpd_ide_file_open_url($SiteFileName,$Line=""){
	global $vars;
	$sfn=$SiteFileName;
	//$bd_ide_file_open_url_debug=TRUE;
	$protocol="openineclipse://"; 
	if(!(strstr($SiteFileName,"dse_publish_archive")==FALSE)){
		if ($bd_ide_file_open_url_debug) print "bd_ide_file_open_url_debug: if.1<br>";
		//$SiteFileName=str_replace("/home/admin","",$SiteFileName);
		$LocalFileName="/Volumes/bd_admin/$SiteFileName";
		$LocalFileName=str_replace("Volumes/bd_admin//home/admin","Volumes/bd_admin",$LocalFileName);
		//javascript:document.location='openineclipse://open?url=file:///Volumes/bd_admin//home/admin/dse_publish_archive/home/admin/batteriesdirect.com/dse_admin/utils/code_functions.php.20120414122414&line=';
		
		if ($bd_ide_file_open_url_debug) print "bd_ide_file_open_url_debug: if.b<br>";
	}elseif(!(strstr($SiteFileName,"/home/marqul")==FALSE)){
		if ($bd_ide_file_open_url_debug) print "bd_ide_file_open_url_debug: if.a<br>";
		$SiteFileName=str_replace("/home/marqul","",$SiteFileName);
		$LocalFileName="/Volumes/bd_marqul/$SiteFileName";
		
		$LocalFileName="/Volumes/bd_admin/$SiteFileName";
		if ($bd_ide_file_open_url_debug) print "bd_ide_file_open_url_debug: if.b<br>";
	}elseif(!(strstr($SiteFileName,"/home/admin")==FALSE)){
		$SiteFileName=str_replace("/home/admin","",$SiteFileName);
		$SiteFileName=str_replace("//","/",$SiteFileName);
		$LocalFileName="/Volumes/bd_admin/$SiteFileName";
		$LocalFileName=str_replace("//","/",$LocalFileName);
		if ($bd_ide_file_open_url_debug) print "bd_ide_file_open_url_debug: if.c<br>";
		
//	}elseif(!(strstr($SiteFileName,"/home/admin/dev-batteriesdirect_com/")==FALSE)){
//		$SiteFileName=str_replace("/home/admin/","",$SiteFileName);
	}else{
		if ($bd_ide_file_open_url_debug) print "bd_ide_file_open_url_debug: if.d<br>";
		//$SiteFileName="dev-batteriesdirect_com".$SiteFileName;
		$LocalFileName="$SiteFileName";
	}
	

	
	
	if ($bd_ide_file_open_url_debug) print "bd_ide_file_open_url_debug: lfn=$LocalFileName sfn=$sfn<br>";
	return "javascript:document.location='${protocol}open?url=file://${LocalFileName}&line=$Line';";
	//openineclipse://open?url=file:///etc/hosts&line=
}	 

?>