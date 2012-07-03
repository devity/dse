<?php
$DSE_ROOT="/dse";
include_once ("$DSE_ROOT/include/web_config.php");
include_once ("$DSE_ROOT/include/code_functions.php");
dse_print_page_header();

$CodeBaseDir="/dse/bin";
//print "asfsdf1fcqe134<br>";

//print "214tgf3v r<br>";
$DoFileInfo=key_exists("FileInfo", $_REQUEST);
$ViewDebugOutput=key_exists("ViewDebugOutput", $_REQUEST);

//print "asfsdf1fcg432g3g235qe134<br>";
if($DoFileInfo){
	set_time_limit(0);
	$CodeInfoArray=dse_code_parse_load();//dse_code_parse($CodeBaseDir);
	
	
	foreach($CodeInfoArray as $k=>$v){
		print "$k = $v<br>";
	}
	
//print "v24gf134g134g<br>";
	$FileFullName=$_REQUEST['File'];
	print "<center><b class='f10pt'>Info on File: $File</b></center></br><br>";
	//$FileArray=$CodeInfoArray['Files'][$File]['FileCodeInfoArray']['Functions']['Def'];//
	//print debug_tostring($FileArray);
	
	print "<table width=100%><tr class='f8pt'>";
	
	
	print "<td valign=top><b class='f10pt'>Functions Declared:</b><br>";
		ksort($CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['Functions']['Def']);
		foreach($CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['Functions']['Def'] as $n=>$e){
			$p=$e[3];
			print "<b class='f9pt'>$n</b> ($p)<br>";
			// $f:$l
		}
	print "</td>";
	
	print "<td valign=top><b class='f10pt'>Functions Referenced:</b><br>";
		ksort($CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['Functions']['Used']);
		foreach($CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['Functions']['Used'] as $n=>$e){
			$l=$e[1]; $p=$e[3]; 
			$Def=$CodeInfoArray['Functions']['Def'][$n];
			//print "<b class='f9pt'>$n</b>($p) Line:$l<br>";
			//print "<a><b class='f9pt'>$n</b>($p) Line:$l</a><div>Stuff shown on hover</div><br>";
			print "<div href=\"#\" class=\"showhim\"><b class='f9pt'>$n</b>($p) Line:$l</a>";
			print "<div class=\"showme\">";
			print debug_tostring($Def);
			print "</div></div>";

			// $f:$l
		}
	print "</td>";
	
	print "</tr></table>";
	
	$PHPKeyWords=array("#!/usr/bin/php","<?php?","<?","?>","TRUE","FALSE","function","include_once","include","ini_set","error_reporting","print",
		" as ","while","foreach","for(","exit(","return","if(","else",
		"array(","break",
		"rand","intval","number_format",
		"case","switch","default","implode","array_push","array_pop","array_keys","list","implode",
		"ksort","asort","sort",
		"strlen","strtoupper","strtolower","substr","strstr","stristr","trim",
		"preg_match");
	$PHPCharactersPurple=array("(",")","[","]","{","}","=>");
	$PHPCharactersRed=array("\$","&&","==",">=","<=","!=","||","!",",");//"\"","\'","\`",
	
	print "<br><b class='f10pt'>File Contents:</b><br>";
	foreach($CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['LinesParsed'] as $LineNumber=>$Line){
		//$Line=t2h($Line);
		$Line=str_replace("/*","<span style='background:#cccce5;'><b>/*</b>",$Line);
		$Line=str_replace("*/","<b>*/</b></span>",$Line);
		if(str_contains($Line,"//")){
			$Line="<div style='display:inline-block;'>".str_replace("//","<span style='background:#cccce5;'><b>//</b>",$Line)."</span></div>";
			
		}
		foreach($PHPKeyWords as $PHPKeyWord){
			$Line=str_replace($PHPKeyWord,"<span style='color:blue;'><b>$PHPKeyWord</b></span>",$Line);
		}
		foreach($PHPCharactersPurple as $PHPCharacter){
			$Line=str_replace($PHPCharacter,"<span style='color:purple;'><b>$PHPCharacter</b></span>",$Line);
		}
		foreach($PHPCharactersRed as $PHPCharacter){
			$Line=str_replace($PHPCharacter,"<span style='color:red;'>$PHPCharacter</span>",$Line);
		}
		
		foreach($CodeInfoArray['Functions']['Def'] as $k=>$fde){
			$f=$fde[0];
			$l=$fde[1];
			$n=$fde[2];
			$p=$fde[3];
			$d=$fde[4];
			$Line=str_replace($n."(","<span style='color:teal;'><b>$n</b></span>(",$Line);
		}
			
		print "<span style='background:#335588;'><font color=white>$LineNumber </font></span> $Line<br>";
	}
	
	
	//print d2s($CodeInfoArray);
	
}elseif($ViewDebugOutput){
//print "234t23g2435<br>";
	$FileFullName=$_REQUEST['File'];
	$Size=dse_file_get_size($FileFullName);
	print "Debug of file run for: $FileFullName   $Size bytes<br>";
	
	$tbr=dse_file_get_contents($FileFullName);
	$tbr=text2html($tbr);
	$tbr=str_replace("[0;30;40m","</span></font><font color=black><span style='background:black;'>",$tbr);
	$tbr=str_replace("[0;31;40m","</span></font><font color=red><span style='background:black;'>",$tbr);
	$tbr=str_replace("[0;32;40m","</span></font><font color=lime><span style='background:black;'>",$tbr);
	$tbr=str_replace("[0;33;40m","</span></font><font color=yellow><span style='background:black;'>",$tbr);
	$tbr=str_replace("[0;34;40m","</span></font><font color=blue><span style='background:black;'>",$tbr);
	$tbr=str_replace("[0;35;40m","</span></font><font color=magenta><span style='background:black;'>",$tbr);
	$tbr=str_replace("[0;36;40m","</span></font><font color=cyan><span style='background:black;'>",$tbr);
	$tbr=str_replace("[0;37;40m","</span></font><font color=white><span style='background:black;'>",$tbr);
	
	$tbr=str_replace("[0;;m","</span></font><font color=black><span style='background:black;'>",$tbr);
	$tbr=str_replace("[0;34;43m","</span></font><font color=blue><span style='background:yellow;'>",$tbr);
	
	print "<div style='background:black;'>";
	print $tbr;
	print "</div>";
	
}else{
	
	set_time_limit(0);
	$CodeInfoArray=dse_code_parse_load();//dse_code_parse($CodeBaseDir);
	
	print "<table width=100%><tr class='f8pt'>";
	
	print "<td valign=top><b>Files:</b><br>";
	foreach($CodeInfoArray['Files'] as $FileFullName=>$FileEntry){
		$LineCount=$FileEntry['FileCodeInfoArray']['LineCount'];
		$Language=$FileEntry['FileCodeInfoArray']['Language'];
		if($Language){
			print "<a href=/code_explorer/?FileInfo&File=$FileFullName><b class='f9pt'>$FileFullName</b></a> ($LineCount)<br>";
		}
	}
	print "</td>";
	print "</td>";
	
	
	print "<td valign=top><b>Functions:</b><br>";
	//print text2html(dse_code_return_function_declarations("/dse"))."<br>";
	
		ksort($CodeInfoArray['Functions']['Def']);
		foreach($CodeInfoArray['Functions']['Def'] as $k=>$fde){
			$f=$fde[0];
			$l=$fde[1];
			$n=$fde[2];
			$p=$fde[3];
			$d=$fde[4];
			print "<b class='f9pt'>$n</b> ($p)<br>";
			// $f:$l
		}
		print "</td>";
	
	print "</tr></table>";
}



dse_print_page_footer();
?>
