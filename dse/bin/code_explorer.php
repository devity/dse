#!/usr/bin/php
<?php
error_reporting(E_ALL && ~E_NOTICE);
ini_set('display_errors','On');	
include_once ("/dse/bin/dse_cli_functions.php");
include_once ("/dse/bin/dse_config.php");
include_once ("/dse/include/web_functions.php");
include_once ("/dse/include/code_functions.php");
$vars['Verbosity']=1;
ini_set("memory_limit","-1");


// ********* DO NOT CHANGE below here ********** DO NOT CHANGE below here ********** DO NOT CHANGE below here ******
$vars['DSE']['SCRIPT_NAME']="DSE";
$vars['DSE']['SCRIPT_DESCRIPTION_BRIEF']="main script of Devity Server Environment";
$vars['DSE']['DSE_DSE_VERSION']="v0.05b";
$vars['DSE']['DSE_DSE_VERSION_DATE']="2012/09/11";
$vars['DSE']['SCRIPT_FILENAME']=$argv[0];
// ********* DO NOT CHANGE above here ********** DO NOT CHANGE above here ********** DO NOT CHANGE above here ******

$parameters_details = array(
  array('h','help',"this message"),
  array('q','quiet',"same as --verbosity 0"),
  array('v:','verbosity:',"0=none 1=some 2=more 3=debug"),
 // array('p:','parse:',"parses code-base at argv[1]"),
  array('f:','function-declarations:',"shows functions declared in code-base at argv[1]"),
  array('u:','file-info:',"code-base at argv[1] file-info on file = argv[2]"),
  array('o:','overview:',"overview of code-base at argv[1]"),
  array('g','grep',"does a grep for argv[1] in code-base at argv[2]"),
  array('c:','check-for-errors:',"does a syntax check, etc of code-base at argv[1]"),
  array('d','compare',"does a diff code-base names argv[1] and argv[2]"),
  
);
$vars['parameters']=dse_cli_get_paramaters_array($parameters_details);
$vars['Usage']=dse_cli_get_usage($parameters_details);
$vars['argv_origional']=$argv;
dse_cli_script_start();
		
$BackupBeforeUpdate=TRUE;
foreach (array_keys($vars['options']) as $opt) switch ($opt) {
	case 'q':
	case 'quiet':
		$Quiet=TRUE;
		$vars['Verbosity']=0;
		break;
  	case 'v':
	case 'verbosity':
		$vars['Verbosity']=$vars['options'][$opt];
		if($vars['Verbosity']>=2) print "Verbosity set to ".$vars['Verbosity']."\n";
		break;
	case 'h':
  	case 'help':
  		$ShowUsage=TRUE;
		$DidSomething=TRUE;
		break;
}

dse_cli_script_header();

foreach (array_keys($vars['options']) as $opt) switch ($opt) {
  	case 'd':
  	case 'compare':
  		$CodeBaseName1=$argv[1];
  		$CodeBaseName2=$argv[2];
		$CodeBaseDir1=$vars['DSE']['CODE_BROWSE_NAMES'][$CodeBaseName1];
		$CodeBaseDir2=$vars['DSE']['CODE_BROWSE_NAMES'][$CodeBaseName2];
		$Command="rsync --dry-run --verbose --recursive --checksum $CodeBaseDir1/ $CodeBaseDir2/ | grep -v dse | grep -v '.gz' | grep -v 'output' ";
		dse_exec($Command,TRUE,TRUE);
		$DidSomething=TRUE;
		break;
  	case 'p':
  	case 'parse':
		dse_code_parse($vars['options'][$opt]);
		$DidSomething=TRUE;
		break;
  	case 'f':
  	case 'function-declarations':
		print dse_code_return_function_declarations($vars['options'][$opt]);
		$DidSomething=TRUE;
		break;
  	case 'c':
  	case 'check-for-errors':
		dse_code_check($vars['options'][$opt]);
		$DidSomething=TRUE;
		break;
	
	case 'u':
	case 'file-info':
		$DidSomething=TRUE;
		{
			$CodeBaseDir=$vars['options'][$opt];
			$CodeInfoArray=dse_code_parse($CodeBaseDir);

		
			$FileFullName=$argv[1];
			print "<center><b class='f10pt'>Info on File: $FileFullName</b></center></br><br>";
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
	/*

	*/


			
		}
		break;
	case 'o':
	case 'overview':
		$DidSomething=TRUE;
		{
			//print "v=$vars[Verbosity]\n";
			if($vars['options'][$opt]){
				$CodeBaseDir=$vars['options'][$opt];
			}else{
				$CodeBaseDir="/dse/bin";
			}
			$CodeInfoArray=dse_code_parse($CodeBaseDir);
			print "<table width=100%><tr class='f8pt'>";
			
			print "<td valign=top><b>Files:</b><br>";
				foreach($CodeInfoArray['Files'] as $FileFullName=>$FileEntry){
					if($FileEntry['FileCodeInfoArray']['LineCount']){
						$LineCount=$FileEntry['FileCodeInfoArray']['LineCount'];
						print "<a href=/code_explorer/?FileInfo&File=$FileFullName><b class='f9pt'>$FileFullName</b></a> ($LineCount)<br>";
						// $f:$l
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
		break;
	case 'g':
	case 'grep':
		$DidSomething=TRUE;
		{
			$GrepString=$argv[1];
			//print "v=$vars[Verbosity]\n";
			if($argv[2]){
				$CodeBaseDir=$argv[2];
			}else{
				$CodeBaseDir="/dse/bin";
			}
			
			dpv(1,"Searching for '$GrepString' in $CodeBaseDir");
			$CodeInfoArray=dse_code_parse($CodeBaseDir,FALSE);
			//print "<table width=100%><tr class='f8pt'>";
			
			
			ksort($CodeInfoArray['Files']);
			foreach($CodeInfoArray['Files'] as $k=>$cefa){
				//$f=$cefa[0];
					
				//print "($p)\n";
//				print "Files[$k]=".debug_tostring($cefa)."\n";
				//print "k=$k\n";
				if(str_icontains($cefa['FileCodeInfoArray']['Contents'],$GrepString)){
//		print "!!! k=$k\n";
				
				
				
				//print "Files[$k]=".print_r($cefa,TRUE)."\n";
					print dse_exec("/dse/bin/gss --search-results \"$GrepString\" \"$k\"");
					//print "\n";
				}else{
					dpv(3, "Not Found in $k");
				}
			}
			
		}
		break;
		
		
			
}



	
 
if($ShowUsage){
	print $vars['Usage'];
}
if($DoUpdate){
	$Date_str=date("YmdGis");
	if($BackupBeforeUpdate){
		$BackupDir=$vars['DSE']['DSE_BACKUP_DIR_DSE']."/".$Date_str."/dse";
		$Command="mkdir -p ".$BackupDir;
		//print "$Command\n";
		`$Command`;
	
		if(!$Quiet) print "Backing up ".$vars['DSE']['DSE_ROOT']." to $BackupDir\n";
		$Command="cp -rf ".$vars['DSE']['DSE_ROOT']." ".$BackupDir."/.";
		//print "$Command\n";
		`$Command`;
	}else{
		if(!$Quiet) print "Skipping backing up of current dse install.\n";
	}
	$DSE_GIT_ROOT=getenv("DSE_GIT_ROOT");
	if($DSE_GIT_ROOT){
		if(file_exists($DSE_GIT_ROOT)){
			$Command="/scripts/dse_git_pull 2>&1";
			$o=`$Command`;
			if(!$Quiet) print $o;
		}else{
			print "ERROR: DSE_GIT_ROOT=$DSE_GIT_ROOT does not exist.\n";
			exit -1;
		}
	}else{
		print "ERROR: DSE_GIT_ROOT unset.\n";
		exit -1;
	}
}

if($DidSomething){
	if(!$Quiet && !$DoSetEnv){
		print getColoredString($vars['DSE']['SCRIPT_NAME']." Done. Exiting (0)","black","green");
		$vars[shell_colors_reset_foreground]='';	print getColoredString("\n","white","black");
	}
	if(!$NoExit) exit(0);
}else{
	if(!$Quiet && !$DoSetEnv){
		print getColoredString("Nothing to do! try --help for usage. ".$vars['DSE']['SCRIPT_NAME']." Done. Exiting (-1)","pink","black");
		$vars[shell_colors_reset_foreground]='';	print getColoredString("\n","white","black");
	}
	if(!$NoExit) exit(-1);
}

/*
 * dot -Tpng schema.gv -o schema.png
 * 
digraph g {
graph [ rankdir = "LR" ];
node [ fontsize = "16" shape = "ellipse" ];
edge [ ];
"node_accounts" [ label = "<f0> Accounts| <f_un> UserNumber" shape = "record" ]; 
"node_crafters" [ label = "<f0> Crafters| <f_cn> CrafterNumber| <f_un> UserNumber| " shape = "record" ];
"node_events" [ label = "<f0> Events|<f_en>EventNumber|<f_pn> PromoterNumber" shape = "record" ];
"node_promoters" [ label = "<f0> Promoters| <f_pn> PromoterNumber| <f_un> UserNumber" shape = "record" ];
"node_comments" [ label = "<f0> Comments| <f_cn> CrafterNumber| <f_en> EventNumber| Rating| Comment" shape = "record" ];

"node_accounts": f_un -> "node_crafters":f_un [ id = 0 ];
"node_accounts":f_un -> "node_promoters":f_un [ id = 3 ];
"node_crafters":f_cn -> "node_comments":f_cn [ id = 4 ];
"node_promoters":f_pn -> "node_events":f_pn [ id = 5 ];
"node_events":f_en -> "node_comments":f_en [ id = 6 ];
}
 * 
 */


	 

?>
