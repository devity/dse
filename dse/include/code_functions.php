<?php


function dse_code_parse_save($CodeInfoArray,$CodeBaseDir="/dse/bin"){
	global $vars;
	$CodeBaseDirEsc=str_replace("/",".",$CodeBaseDir);
	$cacheFile="/tmp/dse_codemanager_parse.cache$CodeBaseDirEsc";
	file_put_contents($cacheFile, serialize($CodeInfoArray)); 
	return;
}

function dse_code_parse_load($CodeBaseDir="/dse/bin"){
	global $vars;
	$CodeBaseDirEsc=str_replace("/",".",$CodeBaseDir);
	$cacheFile="/tmp/dse_codemanager_parse.cache$CodeBaseDirEsc";
	dpv(2,"dse_code_parse_load($CodeBaseDir){ cacheFile=$cacheFile");
	
	if(dse_file_exists($cacheFile)){	
		dpv(2,"found! cacheFile=$cacheFile");
		$CodeInfoArray = unserialize(file_get_contents($cacheFile)); 
	}else{
		dpv(2,"NO cacheFile=$cacheFile");
		$CodeInfoArray=dse_code_parse($CodeBaseDir);
		dse_code_parse_save($CodeInfoArray,$CodeBaseDir);
	}
	return $CodeInfoArray;
}


function dse_code_parse($CodeBaseDir="/dse/bin"){
	global $vars;
	$CodeBaseDirEsc=str_replace("/",".",$CodeBaseDir);
	$cacheFile="/tmp/dse_codemanager_parse.cache$CodeBaseDirEsc";
	dpv(2,"dse_code_parse($CodeBaseDir){ cacheFile=$cacheFile");
	
	if(dse_file_exists($cacheFile)){	
		dpv(2,"found! cacheFile=$cacheFile");
		$CodeInfoArray = unserialize(file_get_contents($cacheFile)); 
		return $CodeInfoArray;
	}
	$skip=array("phpmyadmin",".dab",".git","/templates/","/library/","phpMemcached","Zend",".xml",".jpg",".gif","png",".pdf",".js",".css",".htaccess",
		".bak",".tar",".gz",".tgz","zip",".txt",".tpl");
	$DirArray=dse_directory_to_array($CodeBaseDir);
	$CodeInfoArray=dse_code_parse_dir_array_to_code_array($DirArray);
	//print print_r($CodeInfoArray);
	//print print_r($CodeInfoArray['Files']); return;
	foreach($CodeInfoArray['Files'] as $FileFullName=>$Entry){
		$Do=TRUE;
		foreach($skip as $s){
			if(str_contains($FileFullName,$s)){
				$Do=FALSE;
			}
		}
		if($Do){
			dpv(2,"parsing $FileFullName");
			if(!dse_file_is_link($FileFullName)){
				$CodeInfoArray=dse_code_parse_file_to_array($CodeInfoArray,$FileFullName);
			}else{
				//print "$FileFullName is LINK\n";
			}
		}
	}
	dpv(2,"Pass 2 !!!!!!!!!!!!!!!!!!!! ");
	foreach($CodeInfoArray['Files'] as $FileFullName=>$Entry){
		$Do=TRUE;
		foreach($skip as $s){
			if(str_contains($FileFullName,$s)){
				$Do=FALSE;
			}
		}
		if($Do){
			//print "parsing $FileFullName pass 2\n";
			if(!dse_file_is_link($FileFullName)){
				$CodeInfoArray=dse_code_parse_file_to_array_pass2($CodeInfoArray,$FileFullName);
			}
		}
	}
	dpv(2,"DONE returning CodeInfoArray ");
	if(!dse_file_exists($cacheFile)){	
		dse_code_parse_save($CodeInfoArray,$CodeBaseDir);
	}
	return $CodeInfoArray;
}

function dse_code_return_function_declarations($CodeBaseDir){
	global $vars;
	$tbr="";
	$CodeInfoArray=dse_code_parse($CodeBaseDir);
	foreach($CodeInfoArray['Functions']['Def'] as $k=>$fde){
		$f=$fde[0];
		$l=$fde[1];
		$n=$fde[2];
		$p=$fde[3];
		$d=$fde[4];
		$tbr.= "$n ($p) $f:$l\n";
	}
	return $tbr;
}

function dse_code_parse_file_to_array($CodeInfoArray,$FileFullName){
	global $vars;
	dpv(2,"dse_code_parse_file_to_array($CodeInfoArray,$FileFullName){");
	$CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']=array();
	$CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['FileFullName']=$FileFullName;
	$CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['Size']=dse_file_get_size($FileFullName);
	if(is_dir($FileFullName)){
		$CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['Contents']="DIR";
	}else{	
		$CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['Contents']=dse_file_get_contents($FileFullName);
	}
	$CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['Functions']=array("Def"=>array(),"Used"=>array());
	$CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['Variables']=array("Def"=>array(),"Used"=>array());
	$Language="UNKNOWN";
	$cont=substr($CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['Contents'],0,20);
	//print "$FileFullName cont=$cont\n";
	if(str_contains($FileFullName,".php")){
		$Language="PHP";
	}elseif(str_contains($CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['Contents'],"<?php")){
		$Language="PHP";
	}elseif(str_contains($CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['Contents'],"#!/usr/bin/php")){
		$Language="PHP";
	}elseif(str_contains($FileFullName,".sh")){
		$Language="SH";
	}elseif(str_contains($CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['Contents'],"#!/bin/sh")){
		$Language="SH";
	}
	$CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['Language']=$Language;
	$CodeInfoArray=dse_code_parse_contents_to_array($CodeInfoArray,$FileFullName);
	dpv(2,"leaving dse_code_parse_file_to_array($CodeInfoArray,$FileFullName){");
	return $CodeInfoArray;
}

function dse_code_parse_file_to_array_pass2($CodeInfoArray,$FileFullName){
	global $vars;
	$CodeInfoArray=dse_code_parse_contents_to_array_pass2($CodeInfoArray,$FileFullName);
	return $CodeInfoArray;
}

function dse_code_parse_contents_to_array($CodeInfoArray,$FileFullName){
	global $vars;
	switch($CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['Language']){
		case "PHP":
			return dse_code_parse_PHP_contents_to_array($CodeInfoArray,$FileFullName);
			break;
		case "SH":
			return dse_code_parse_SH_contents_to_array($CodeInfoArray,$FileFullName);
			break;
	}
	return $CodeInfoArray;
}
function dse_code_parse_contents_to_array_pass2($CodeInfoArray,$FileFullName){
	global $vars;
	switch($CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['Language']){
		case "PHP":
			return dse_code_parse_PHP_contents_to_array_pass2($CodeInfoArray,$FileFullName);
			break;
		case "SH":
			return dse_code_parse_SH_contents_to_array_pass2($CodeInfoArray,$FileFullName);
			break;
	}
	return $CodeInfoArray;
}

function dse_code_parse_PHP_contents_to_array($CodeInfoArray,$FileFullName){
	global $vars;
	dpv(2,"dse_code_parse_PHP_contents_to_array($FileFullName){");
	
	$CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['Lines']=split("\n",$CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['Contents']);
	$CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['LineCount']=sizeof($CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['Lines']);
	//print "  Lines: ".$CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['LineCount']."\n";
	$LineNumber=0;
	foreach($CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['Lines'] as $Line){
		$LineNumber++;
		if(str_contains($Line,"function ")){
			//print "func! $Line\n";
			dpv(2," func! $Line\n");
			$FunctionDeclaration=trim(strcut($Line,"function ","{"));
			$FunctionName=trim(strcut($FunctionDeclaration,"","("));
			$FunctionParamaters=trim(strcut($FunctionDeclaration,"(",")"));
		//	print $FileFullName . " FunctionDeclaration=$FunctionDeclaration\n";
			$CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['Functions']['Def']["$FunctionName"]=array($FileFullName,$LineNumber,$FunctionName,$FunctionParamaters,$FunctionDeclaration);
			//print "CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['Functions']['Def'][$FunctionName]=array($FileFullName,$LineNumber,$FunctionName,$FunctionParamaters,$FunctionDeclaration);<br>";
			$CodeInfoArray['Functions']['Def']["$FunctionName"]=array($FileFullName,$LineNumber,$FunctionName,$FunctionParamaters,$FunctionDeclaration);
			//print " set CodeInfoArray['Functions']['Def'][]=array($FileFullName,$LineNumber,$FunctionName,$FunctionParamaters,$FunctionDeclaration); \n";
			
			$CodeInfoArray['Functions']['Code']["$LastFunctionName"]=$LastFunctionBody;
			$LastFunctionBody="";
			$LastFunctionName=$FunctionName;
		}else{
			$LastFunctionBody.=$Line."\n";
		}
	}
	return $CodeInfoArray;
}
function dse_code_parse_PHP_contents_to_array_pass2($CodeInfoArray,$FileFullName){
	global $vars;
	
	include_once ($vars['DSE']['DSE_ROOT']."/include/web_functions.php");
	$LineNumber=0;
	$CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['LinesParsed']=array();
	foreach($CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['Lines'] as $Line){
		$LineNumber++;
		$NewLine=$Line;
		$NewLine=t2h($NewLine);
		foreach ($CodeInfoArray['Functions']['Def'] as $FunctionName=>$FuncArray){
			if(str_contains($Line,"$FunctionName(") || str_contains($Line,"$FunctionName (")){
				$FunctionParamaters=strcut(strcut($Line,"$FunctionName"),"(",")");
				$CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['Functions']['Used']["$FunctionName"]=array($FileFullName,$LineNumber,$FunctionName,$FunctionParamaters);
				$CodeInfoArray['Functions']['Used']["$FunctionName"]=array($FileFullName,$LineNumber,$FunctionName,$FunctionParamaters);
			
				$Hide=dse_code_return_function_info_html($CodeInfoArray,$FunctionName);
				$FunctionNameNew=dse_show_w_hover($FunctionName,$Hide);
				
				$NewLine=str_replace("$FunctionName","<font color=green><b>$FunctionNameNew</b></font>",$NewLine);
			}
		}
		$CodeInfoArray['Files'][$FileFullName]['FileCodeInfoArray']['LinesParsed'][$LineNumber]=$NewLine;
	}
	return $CodeInfoArray;
}

function dse_code_parse_SH_contents_to_array($CodeInfoArray,$FileFullName){
	global $vars;
	$tbr=array();
	return $CodeInfoArray;
}
function dse_code_parse_SH_contents_to_array_pass2($CodeInfoArray,$FileFullName){
	global $vars;
	$tbr=array();
	return $CodeInfoArray;
}


function dse_code_parse_dir_array_to_code_array($DirArray,$CodeInfoArray=array()){
	global $vars;
	//print "dse_code_parse_dir_array_to_code_array()\n";
	if(sizeof($CodeInfoArray)==0){
		$CodeInfoArray['Functions']=array("Def"=>array(),"Used"=>array());
		$CodeInfoArray['Variables']=array("Def"=>array(),"Used"=>array());
		$CodeInfoArray['Files']=array();
	}
	
	$skip=array("phpmyadmin",".dab","/templates/");
	
	foreach($DirArray as $Entry){
	//	print " Entry $Entry[2]\n";
		$Do=TRUE;
		foreach($skip as $s){
			if(str_contains($Entry[1],$s)) $Do=FALSE;	
		}
		if($Do){
			if($Entry[0]=="DIR"){
				$CodeInfoArray=dse_code_parse_dir_array_to_code_array($Entry[3],$CodeInfoArray);
			}elseif($Entry[0]=="FILE"){
			}
		}
		$CodeInfoArray['Files'][$Entry[2]]=array($Entry[0],$Entry[1],$Entry[2]);
		//print "		CodeInfoArray['Files'][$Entry[2]]=array($Entry[0],$Entry[1],$Entry[2]);\n";
	}
	return $CodeInfoArray;
}

function dse_code_return_function_info_html($CodeInfoArray,$FunctionName){
	global $vars;
	$FunctionArray=$CodeInfoArray['Functions']['Def'][$FunctionName];
	$Code=$CodeInfoArray['Functions']['Code'][$FunctionName];
			
	$FileFullName=$FunctionArray[0];
	$Line=$FunctionArray[1];
	$FunctionName=$FunctionArray[2];
	$FunctionParamaters=$FunctionArray[3];
	$FileLink= "<a href=/code_explorer/?FileInfo&File=$FileFullName target=_blank>$FileFullName</a>";
		
	$CodeEsc=t2h($Code);
	return "File: $FileLink Line: $Line<br>
<b>$FunctionName</b>($FunctionParamaters){<br>
<br>
$CodeEsc

";
}

function dse_directory_to_array( $path = '.', $level = 0 ){
	global $vars;
	$tbr=array();
	$path.="/";  $path=str_replace("//", "/", $path);
    $ignore = array( '.', '..' ); 
    $dh = @opendir( $path ); 
    while( false !== ( $file = readdir( $dh ) ) ){ 
     
        if( !in_array( $file, $ignore ) ){ 
             
            $spaces = str_repeat( '&nbsp;', ( $level * 4 ) ); 
            if( is_dir( "$path$file" ) ){
            	$tbr[]=array("DIR",$file,"$path$file", 
            		dse_directory_to_array( "$path$file", ($level+1) )
					 ); 
            } else { 
               $tbr[]=array("FILE",$file,"$path$file", NULL ); 
            } 
        } 
    } 
     
    closedir( $dh ); 
	return $tbr;
} 

?>