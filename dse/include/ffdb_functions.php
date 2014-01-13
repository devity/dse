<?php

$vars['DSE']['Database']="try1";
$vars['DSE']['ffbd_root_path']="/tmp";

function dse_ffdb_select_by_field($Table,$FieldToMatch,$ValueToMatch){
	global $vars; dse_trace();
	$tbr=array();
	
	$Database=$vars['DSE']['Database'];
	$DataFile=$vars['DSE']['ffbd_root_path']."/".$Database."/".$Table.".data";
	
	$Fields=dse_ffdb_load_def($Table);
	foreach($Fields as $Field=>$FieldInfo){
		$FieldNamesByNumberInt++;
		$FieldNamesByNumber[$FieldNamesByNumberInt]=$Field;
	}
	$Data=file_get_contents($DataFile);
	foreach(explode("\n",$Data) as $DataLine){
		$FieldNumber=0;
		foreach(explode(",",$DataLine) as $FieldValue){
			$FieldNumber++;
			$FieldName=$FieldNamesByNumber[$FieldNumber];
			if($FieldName==$FieldToMatch){
				if($FieldValue==$ValueToMatch){
					$tbr[]=$DataLine;
				}
			}
		}
	}
	return $tbr;
}



function dse_ffdb_insert($Table,$Values){
	global $vars; dse_trace();
	$Fields=dse_ffdb_load_def($Table);
	
	$Database=$vars['DSE']['Database'];
	$DataFile=$vars['DSE']['ffbd_root_path']."/".$Database."/".$Table.".data";
	//if(!file_exists($DataFile)){
		//print "ERROR: $DataFile does not exist.\n";
		//return NULL;
	//}	
	$data="";
	foreach($Fields as $Field=>$FieldInfo){
		$Value="";		
		if(array_key_exists($Field, $Values)){
			$Value=dse_ffdb_escape($Values[$Field]);
		}
		if($data){
			$data.=",";
		}
		$data.=$Value;		
	}
	if(file_exists($DataFile)){
		$data="\n".$data;
	}
	file_put_contents($DataFile, $data, FILE_APPEND);
	
}


function dse_ffdb_load_def($Table){
	global $vars; dse_trace();
	
	$Database=$vars['DSE']['Database'];
	$DefFile=$vars['DSE']['ffbd_root_path']."/".$Database."/".$Table.".def";
	if(!file_exists($DefFile)){
		print "ERROR: $DefFile does not exist.\n";
		return NULL;
	}
	
	$Def=file_get_contents($DefFile);
	foreach(explode("\n",$Def) as $Line){
		list($Name,$Type,$Options)=explode(",",$Line);
		$Fields[$Name]=array($Type,$Options);
	}
	return $Fields;
}


function dse_ffdb_escape($Value){
	$Value=str_replace(",", "[[ffbb_comma]]", $Value);
	return $Value;
}


?>