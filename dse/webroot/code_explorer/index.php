<?php
$DSE_ROOT="/dse";
include_once ("$DSE_ROOT/include/web_config.php");
include_once ("$DSE_ROOT/include/code_functions.php");
dse_print_page_header();

$CodeBaseDir="/dse";

$CodeInfoArray=dse_code_parse($CodeBaseDir);


$DoFileInfo=key_exists("FileInfo", $_REQUEST);

if($DoFileInfo){
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
	
	
	print "<br><b class='f10pt'>File Contents:</b><br>";
	foreach($CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['LinesParsed'] as $LineNumber=>$Line){
		print "<font color=grey>$LineNumber </font> $Line<br>";
	}
	
}else{

	print "<table width=100%><tr class='f8pt'>";
	
	print "<td valign=top><b>Files:</b><br>";
		foreach($CodeInfoArray['Files'] as $FileFullName=>$FileEntry){
			$LineCount=$FileEntry['FileCodeInfoArray']['LineCount'];
			print "<a href=/code_explorer/?FileInfo&File=$FileFullName><b class='f9pt'>$FileFullName</b></a> ($LineCount)<br>";
			// $f:$l
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
