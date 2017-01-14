<?php
function dbase_numfields($identifier)
{
	return count($identifier->fields);
}

function dbase_close($identifier)
{
	return fclose($identifier->file);
}
function dbase_get_header_info($identifier)
{
	$fields = $identifier->fields;
	$datatype = array(
	"C" => "character" ,
	"N" => "number" ,
	"M" => "memo" ,
	"B" => "binary" ,
	"D" => "date" ,
	"L" => "boolean" 
	);
	$sign = array(
	"C" => "-" ,
	"N" => "" ,
	"M" => "" ,
	"B" => "" ,
	"D" => "" ,
	"L" => "" 
	);
	$format = array(
	"C" => "s" ,
	"N" => "s" ,
	"M" => "s" ,
	"B" => "s" ,
	"D" => "s" ,
	"L" => "s" 
	);

	$info = array();
	if(count($fields))
	{
		$i = 0;
		foreach($fields as $field)
		{
            $info[$i]['name'] = $field[0];
            $info[$i]['type'] = $datatype[$field[1]];
 			switch($field[1])
			{
			case "B":
			$ln = 10;
			break;
			case "C":
			$ln = $field[2];
			break;
			case "D":
			$ln = 8;
			break;
			case "N":
			$ln = $field[2];
			break;
			case "L":
			$ln = 1;
			break;
			case "M":
			$ln = 10;
			break;
			case "@":
			$ln = 8;
			break;
			case "I":
			$ln = 4;
			break;
			case "+":
			$ln = 4;
			break;
			case "F":
			$ln = $field[2];
			break;
			case "O":
			$ln = 8;
			break;
			case "G":
			$ln = 10;
			break;
			}
            $info[$i]['length'] = $ln;
            $info[$i]['precision'] = ($field[3])?$field[3]:0;
            $info[$i]['format'] = "%".$sign[$field[1]].$ln.$format[$field[1]];
			if($i==0)
			{
            $info[$i]['offset'] = 1;
			}
			else
			{
            $info[$i]['offset'] = $info[$i-1]['offset']+$info[$i-1]['length'];
			}
			$i++;
		}
	}
	return $info;
}

function dbase_numrecords($identifier)
{
	fseek($identifier->file,4,SEEK_SET);
	$recordstr = unpack("L", fread($identifier->file, 4));
	fseek($identifier->file,0,SEEK_END);
	return $recordstr[1];
}
function dbase_delete_record($identifier, $record_number=1)
{
	$recordoffset = $identifier->recordoffset;
	$recordlength = $identifier->recordlength;
	$pos = $recordoffset + (($record_number-1) * $recordlength);
	fseek($identifier->file,$pos,SEEK_SET);
	$ret = fwrite($identifier->file, "1");
	fseek($identifier->file,0,SEEK_END);
	return $ret;
}
function dbase_get_record($identifier, $record_number=1)
{
	$recordoffset = $identifier->recordoffset;
	$recordlength = $identifier->recordlength;
	$pos = $recordoffset + (($record_number-1) * $recordlength);
	fseek($identifier->file,$pos,SEEK_SET);
	$isdeleted = trim(fread($identifier->file, 1))?1:0;

	// read record
	$fields = $identifier->fields;
	$record = array();
	if(count($fields))
	{
		$i = 0;
		foreach($fields as $field)
		{
			$fn .= str_pad($field[0], 11, pack("C", 0), STR_PAD_RIGHT); // Field name 
			$fn .= $field[1]; // Field type
			$fn .= pack("L", 0); // Displacement of field in record
			switch($field[1])
			{
			case "B":
			$ln = 10;
			break;
			case "C":
			$ln = $field[2];
			break;
			case "D":
			$ln = 8;
			break;
			case "N":
			$ln = $field[2];
			break;
			case "L":
			$ln = 1;
			break;
			case "M":
			$ln = 10;
			break;
			case "@":
			$ln = 8;
			break;
			case "I":
			$ln = 4;
			break;
			case "+":
			$ln = 4;
			break;
			case "F":
			$ln = $field[2];
			break;
			case "O":
			$ln = 8;
			break;
			case "G":
			$ln = 10;
			break;
			}
			$record[$i] = fread($identifier->file, $ln);
			$i++;
		}
	}
	return $record;
} 
function dbase_get_record_with_names($identifier, $record_number=1)
{
	$recordoffset = $identifier->recordoffset;
	$recordlength = $identifier->recordlength;
	$pos = $recordoffset + (($record_number-1) * $recordlength);
	fseek($identifier->file,$pos,SEEK_SET);
	$isdeleted = trim(fread($identifier->file, 1))?1:0;

	// read record
	$fields = $identifier->fields;
	$record = array();
	if(count($fields))
	{
		$i = 0;
		$fn = "";
		foreach($fields as $field)
		{
			$fn .= str_pad($field[0], 11, pack("C", 0), STR_PAD_RIGHT); // Field name 
			$fn .= $field[1]; // Field type
			$fn .= pack("L", 0); // Displacement of field in record
			switch($field[1])
			{
			case "B":
			$ln = 10;
			break;
			case "C":
			$ln = $field[2];
			break;
			case "D":
			$ln = 8;
			break;
			case "N":
			$ln = $field[2];
			break;
			case "L":
			$ln = 1;
			break;
			case "M":
			$ln = 10;
			break;
			case "@":
			$ln = 8;
			break;
			case "I":
			$ln = 4;
			break;
			case "+":
			$ln = 4;
			break;
			case "F":
			$ln = $field[2];
			break;
			case "O":
			$ln = 8;
			break;
			case "G":
			$ln = 10;
			break;
			}
			$record[$field[0]] = fread($identifier->file, $ln);
			$i++;
		}
	}
	$record['deleted']=$isdeleted;
	return $record;
} 
function dbase_open($filename, $mode)
{
	$modearr = array("r", "w", "r+");
	if($mode == 1)
	{
		return FALSE;
	}
	$file = fopen($filename, $modearr[$mode]);
	if(!$file)
	{
		return FALSE;
	}
	fseek($file,8,SEEK_SET);
	$recordoffsets = unpack("S",fread($file, 2));
	$recordoffset = $recordoffsets[1];
	$recordlengths = unpack("S",fread($file, 2));
	$recordlength = $recordlengths[1];

	$identifier = new StdClass();
	$fields = array();
	$i = 0;
	fseek($file,32,SEEK_SET);
	while(1)
	{
		$fieldname = fread($file,11);
		if(ord($fieldname[0]) == 0x0D)
		{
			fseek($file,10,SEEK_CUR);
			break;
		}
		$fieldtype = fread($file,1);
		$fielddisplacement = fread($file,4);
		$fieldlengtha = unpack("C", fread($file,1));
		$fieldlength = $fieldlengtha[1];
		$numdecimala = unpack("C", fread($file,1));
		$numdecimal = $numdecimala[1];
		$field = array(trim($fieldname),$fieldtype,$fieldlength,$numdecimal);
		switch($field[1])
		{
		case "B":
		$ln = 10;
		break;
		case "C":
		$ln = $field[2];
		break;
		case "D":
		$ln = 8;
		break;
		case "N":
		$ln = $field[2];
		break;
		case "L":
		$ln = 1;
		break;
		case "M":
		$ln = 10;
		break;
		case "@":
		$ln = 8;
		break;
		case "I":
		$ln = 4;
		break;
		case "+":
		$ln = 4;
		break;
		case "F":
		$ln = $field[2];
		break;
		case "O":
		$ln = 8;
		break;
		case "G":
		$ln = 10;
		break;
		}
		$field[2] = $ln;
		$fieldflags = fread($file,1);
		$autoincrement = unpack("L", fread($file,4));
		$autoincrementstep = unpack("C", fread($file,1));
		$reserved1 = unpack("L", fread($file,4));
		$reserved2 = unpack("L", fread($file,4));
		$fields[$i] = $field;
		
		$i++;
	}
	$identifier->fields = $fields;
	$identifier->filename = $filename;
	$identifier->file = $file;
	$identifier->recordlength = $recordlength;
	$identifier->recordoffset = $recordoffset;
	
	return $identifier;
}


function dbase_create($filename, $fields)
{
	$recordoffsets = ((count($fields) + 1) * 32)+1; // record offsets
	if(!isset($recordoffsets)) $recordoffsets = 0;
	$recordlength = 0;
	if(count($fields))
	{
		foreach($fields as $field)
		{
			switch($field[1])
			{
			case "B":
			$ln = 10;
			break;
			case "C":
			$ln = $field[2];
			break;
			case "D":
			$ln = 8;
			break;
			case "N":
			$ln = $field[2];
			break;
			case "L":
			$ln = 1;
			break;
			case "M":
			$ln = 10;
			break;
			case "@":
			$ln = 8;
			break;
			case "I":
			$ln = 4;
			break;
			case "+":
			$ln = 4;
			break;
			case "F":
			$ln = $field[2];
			break;
			case "O":
			$ln = 8;
			break;
			case "G":
			$ln = 10;
			break;
			}
			$recordlength += $ln;
		}
		$recordlength += 1;
	}
	$header =  pack("C", 0x03); // file type
	$header .= pack("C", date("Y")-1900); // last update YY
	$header .= pack("C", date("m")); // last update MM
	$header .= pack("C", date("d")); // last update DD
	$header .= pack("L", 0); // Number of records in file (0 on create)
	$header .= pack("S", $recordoffsets); // Position of first data record
	$header .= pack("S", $recordlength); // Length of one data record, including delete flag
	$header .= pack("L", 0); // 12 to 15 Reserved
	$header .= pack("L", 0); // 16 to 19 Reserved
	$header .= pack("L", 0); // 20 to 23 Reserved
	$header .= pack("L", 0); // 24 to 27 Reserved
	$header .= pack("C", 0); // Table flags
	$header .= pack("C", 0); // Code page mark
	$header .= pack("S", 0); // Reserved
	$fn = "";
	if(count($fields))
	{
		foreach($fields as $field)
		{
			$fn .= str_pad($field[0], 11, pack("C", 0), STR_PAD_RIGHT); // Field name 
			$fn .= $field[1]; // Field type
			$fn .= pack("L", 0); // Displacement of field in record
			if(!isset($field[3])) $field[3] = 0;
			switch($field[1])
			{
			case "B":
			$ln = 10;
			break;
			case "C":
			$ln = $field[2];
			break;
			case "D":
			$ln = 8;
			break;
			case "N":
			$ln = $field[2];
			break;
			case "L":
			$ln = 1;
			break;
			case "M":
			$ln = 10;
			break;
			case "@":
			$ln = 8;
			break;
			case "I":
			$ln = 4;
			break;
			case "+":
			$ln = 4;
			break;
			case "F":
			$ln = $field[2];
			break;
			case "O":
			$ln = 8;
			break;
			case "G":
			$ln = 10;
			break;
			}
			$fn .= pack("C", $ln); //Length of field (in bytes)
			$fn .= pack("C", $field[3]); //Number of decimal places
			$fn .= pack("C", 0x00);
			$fn .= pack("L", 0x00);
			$fn .= pack("C", 0x00);
			$fn .= pack("L", 0x00);
			$fn .= pack("L", 0x00);
		}
	}
	$header .= $fn;
	$header .= pack("C",0xD); // Header record terminator (0x0D)
	$file = fopen($filename, "w+");
	$identifier = new StdClass();
	$identifier->fields = $fields;
	$identifier->filename = $filename;
	$identifier->file = $file;
	$identifier->recordlength = $recordlength;
	$identifier->recordoffset = $recordoffsets;
	
	fwrite($identifier->file, $header);
	return $identifier;
}
function dbase_add_record($identifier, $data)
{
	fseek($identifier->file,4,SEEK_SET);
	$recordss = unpack("L",fread($identifier->file, 4));
	$records = $recordss[1];
	fseek($identifier->file,0,SEEK_END);
	$fields = $identifier->fields;
	fwrite($identifier->file, " ", 1);
	if(count($fields))
	{
		foreach($fields as $key=>$field)
		{
		 	if($field[1]=="C")
			{
				$data2write = str_pad($data[$key], $field[2]);
				$ln = $field[2];                      
			}
		 	else if($field[1]=="D")
			{
				$data2write = str_pad($data[$key], 8, " ", STR_PAD_RIGHT);
				$ln = 8;                      
			}                      
		 	else if($field[1]=="M")
			{
				$data2write = str_pad($data[$key], 10, " ", STR_PAD_LEFT);                      
		 		$ln = 10;
			}
			else if($field[1]=="L")
			{
				$data2write = $data[$key];
				$ln = 1;
			}                      
		 	else if($field[1]=="N")
			{
				$fmt = "%".round($field[2]).".".round($field[3])."f";
				$data2write = str_pad(sprintf($fmt,$data[$key]), $field[2], " ", STR_PAD_LEFT);
				$ln = $field[2];                      
			}
			fwrite($identifier->file, $data2write, $ln);
		}
	}
	$records++;
	fseek($identifier->file,1,SEEK_SET);
	fwrite($identifier->file, pack("C",date("Y")-1900), 1);
	fwrite($identifier->file, pack("C",date("m")), 1);
	fwrite($identifier->file, pack("C",date("d")), 1);
	fwrite($identifier->file, pack("L",$records), 4);
	fseek($identifier->file,0,SEEK_END);
}

function dbase_replace_record($identifier, $data, $record_number=1)
{
	$recordoffset = $identifier->recordoffset;
	$recordlength = $identifier->recordlength;
	$pos = $recordoffset + (($record_number-1) * $recordlength);
	fseek($identifier->file,$pos,SEEK_SET);
	fwrite($identifier->file, " ", 1);
	if(count($fields))
	{
		foreach($fields as $key=>$field)
		{
		 	if($field[1]=="C")
			{
				$data2write = str_pad($data[$key], $field[2]);
				$ln = $field[2];                      
			}
		 	else if($field[1]=="D")
			{
				$data2write = str_pad($data[$key], 8, " ", STR_PAD_RIGHT);
				$ln = 8;                      
			}                      
		 	else if($field[1]=="M")
			{
				$data2write = str_pad($data[$key], 10, " ", STR_PAD_LEFT);                      
		 		$ln = 10;
			}
			else if($field[1]=="L")
			{
				$data2write = $data[$key];
				$ln = 1;
			}                      
		 	else if($field[1]=="N")
			{
				$fmt = "%".round($field[2]).".".round($field[3])."f";
				$data2write = str_pad(sprintf($fmt,$data[$key]), $field[2], " ", STR_PAD_LEFT);
				$ln = $field[2];                      
			}                      
			fwrite($identifier->file, $data2write, $ln);
		}
	}
	$records++;
	fseek($identifier->file,1,SEEK_SET);
	fwrite($identifier->file, pack("C",date("Y")-1900), 1);
	fwrite($identifier->file, pack("C",date("m")), 1);
	fwrite($identifier->file, pack("C",date("d")), 1);
	fwrite($identifier->file, pack("L",$records), 4);
	fseek($identifier->file,0,SEEK_END);
}
?>