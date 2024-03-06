<?php 
include "framework/database/connect.php";

ini_set('max_execution_time', 0);

$receiver	= mysql_connect($OLTA,"root","Pr0reliance");

echo "<pre>";


//Waktu akses data
$time 		= microtime();
$time 		= explode(' ', $time);
$time 		= $time[1] + $time[0];
$start 		= $time;

$db_nm 		= $_POST['database'];
$tbl_nm 	= $_POST['table'];
$col_nm 	= $_POST['key'];
$datastring 	= $_POST['data'];

$data 		= json_decode(urldecode($datastring),true);



$arrayColumn	= array();

if ($tbl_nm == 'PRN_DIG_ORD_HEAD') {

	$query_clm	= "SELECT 
						COLUMN_NAME 
					FROM INFORMATION_SCHEMA.COLUMNS 
					WHERE TABLE_SCHEMA = '".$db_nm."'
						AND TABLE_NAME = '".$tbl_nm."'
						AND COLUMN_NAME NOT IN ('ORD_NBR', 'ORD_NBR_PLUS', 'ORD_TS', 'DUE_TS', 'PAID_DT', 'CRT_TS', 'UPD_TS')";
						
	$result_clm	= mysql_query($query_clm, $receiver);
	while($row_clm = mysql_fetch_assoc($result_clm)) {
		$arrayColumn[]	= $row_clm['COLUMN_NAME'];
	}

	//print_r($data);

	foreach($data as $value) {

	$query	= "INSERT IGNORE INTO ".$db_nm.".".$tbl_nm." (ORD_NBR) VALUES (".$value['ORD_NBR_PLUS'].") ";
	$result	= mysql_query($query, $receiver);

	echo $query."<br />";

	$query	= "UPDATE ".$db_nm.".".$tbl_nm." 
			SET ";

	foreach($arrayColumn as $field) {
		$query.= $field." = '".$value[$field]."' ,";
	}
	$query.= " ORD_TS = '".$value['PAID_DT']."',
				DUE_TS = '".$value['PAID_DT']."',
				CRT_TS = '".$value['PAID_DT']."',
				UPD_TS = '".$value['PAID_DT']."'
			WHERE ORD_NBR = ".$value['ORD_NBR_PLUS']."
			";
	$result	= mysql_query($query, $receiver);
			
	echo $query."<br />";
	
	}
}

//==========================================================//

$arrayColumn	= array();

if ($tbl_nm == 'PRN_DIG_ORD_DET') {

	$query_clm	= "SELECT 
						COLUMN_NAME 
					FROM INFORMATION_SCHEMA.COLUMNS 
					WHERE TABLE_SCHEMA = '".$db_nm."'
						AND TABLE_NAME = '".$tbl_nm."'
						AND COLUMN_NAME NOT IN ('ORD_NBR', 'ORD_NBR_PLUS','PAID_DT', 'CRT_TS', 'UPD_TS')";
						
	$result_clm	= mysql_query($query_clm, $receiver);
	
	while($row_clm = mysql_fetch_assoc($result_clm)) {
		$arrayColumn[]	= $row_clm['COLUMN_NAME'];
	}
		
	
	foreach($data as $value) {

	$query	= "INSERT IGNORE INTO ".$db_nm.".".$tbl_nm." (ORD_DET_NBR) VALUES (".$value['ORD_DET_NBR'].") ";
	$result	= mysql_query($query, $receiver);

	echo $query."<br />";
	
	$query	= "UPDATE ".$db_nm.".".$tbl_nm." 
			SET ";

	foreach($arrayColumn as $field) {
		$query.= $field." = '".$value[$field]."' ,";
	}
	
	$query.= " ORD_NBR = '".$value['ORD_NBR_PLUS']."',
				CRT_TS = '".$value['PAID_DT']."',
				UPD_TS = '".$value['PAID_DT']."'
			WHERE ORD_DET_NBR = ".$value['ORD_DET_NBR']."
			";
	$result	= mysql_query($query, $receiver);
			
	echo $query."<br />";
	
	}
	
}

//==========================================================//

$arrayColumn	= array();

if ($tbl_nm == 'PRN_DIG_ORD_PYMT') {

	$query_clm	= "SELECT 
						COLUMN_NAME 
					FROM INFORMATION_SCHEMA.COLUMNS 
					WHERE TABLE_SCHEMA = '".$db_nm."'
						AND TABLE_NAME = '".$tbl_nm."'
						AND COLUMN_NAME NOT IN ('ORD_NBR', 'ORD_NBR_PLUS','PAID_DT', 'CRT_TS')";
						
	$result_clm	= mysql_query($query_clm, $receiver);
	
	while($row_clm = mysql_fetch_assoc($result_clm)) {
		$arrayColumn[]	= $row_clm['COLUMN_NAME'];
	}
		
	
	foreach($data as $value) {

	$query	= "INSERT IGNORE INTO ".$db_nm.".".$tbl_nm." (PYMT_NBR) VALUES (".$value['PYMT_NBR'].") ";
	$result	= mysql_query($query, $receiver);

	echo $query."<br />";
	
	$query	= "UPDATE ".$db_nm.".".$tbl_nm." 
			SET ";

	foreach($arrayColumn as $field) {
		$query.= $field." = '".$value[$field]."' ,";
	}
	
	$query.= " ORD_NBR = '".$value['ORD_NBR_PLUS']."',
				CRT_TS = '".$value['PAID_DT']."'
			WHERE PYMT_NBR = ".$value['PYMT_NBR']."
			";
	$result	= mysql_query($query, $receiver);
			
	echo $query."<br />";
	
	}
	
}

//==========================================================//

$arrayColumn	= array();

if ($tbl_nm == 'CSH_REG') {

	$query_clm	= "SELECT 
						COLUMN_NAME 
					FROM INFORMATION_SCHEMA.COLUMNS 
					WHERE TABLE_SCHEMA = '".$db_nm."'
						AND TABLE_NAME = '".$tbl_nm."'
						AND COLUMN_NAME NOT IN ('RTL_BRC', 'CRT_TS')";
						
	$result_clm	= mysql_query($query_clm, $receiver);
	
	while($row_clm = mysql_fetch_assoc($result_clm)) {
		$arrayColumn[]	= $row_clm['COLUMN_NAME'];
	}
		
	//print_r($data);
	
	foreach($data as $value) {

	$query	= "INSERT IGNORE INTO ".$db_nm.".".$tbl_nm." (REG_NBR) VALUES (".$value['REG_NBR'].") ";
	$result	= mysql_query($query, $receiver);

	echo $query."<br />";
	
	$query	= "UPDATE ".$db_nm.".".$tbl_nm." 
			SET ";

	foreach($arrayColumn as $field) {
		$query.= $field." = '".$value[$field]."' ,";
	}
	
	$query.= " RTL_BRC = '".$value['ORD_NBR_PLUS']."',
				CRT_TS = '".$value['PAID_DT']."'
			WHERE REG_NBR = ".$value['REG_NBR']."
			";
	$result	= mysql_query($query, $receiver);
			
	echo $query."<br />";
	
	}
	
}

//==========================================================//

/*

$table_cmp = array("PAYROLL", "EXPENSE", "UTILITY");

if (in_array($tbl_nm, $table_cmp))
{
	$array_col	= explode(",",$col_nm);
		
	$query_clm	= "SELECT 
						COLUMN_NAME 
					FROM INFORMATION_SCHEMA.COLUMNS 
					WHERE TABLE_SCHEMA = '".$db_nm."'
						AND TABLE_NAME = '".$tbl_nm."' ";
						
	$result_clm	= mysql_query($query_clm, $receiver);
	
	while($row_clm = mysql_fetch_assoc($result_clm)) {
		$arrayColumn[]	= $row_clm['COLUMN_NAME'];
	}
	
	print_r($data);
	
	for
	
	foreach($data->data as $value) {

	echo "lalala"; 
	echo $data->PYMT_DTE;
	
	$primary_value	= "";
	$where_value	= "";

	for($i=0; $i < count($array_col); $i++) {
			
		$primary_value.= $value[$array_col[$i]].",";
		
		$where_value.= $array_col[$i]."=".$value[$array_col[$i]].",";
	}
		
	$primary_value	= rtrim($primary_value,",");
	
	

	$query	= "INSERT IGNORE INTO ".$db_nm.".".$tbl_nm." (".$col_nm.") 
				VALUES (".$primary_value.") ";
				
				
	$result	= mysql_query($query, $receiver);

	echo $query."<br />";
	
	$query	= "UPDATE ".$db_nm.".".$tbl_nm." 
			SET ";

	foreach($arrayColumn as $field) {
		$query.= $field." = '".$value[$field]."' ,";
	}
	
	
	$query	= rtrim($query,",");
	
	$query.= " WHERE ".$where_value." ";
	
	$result	= mysql_query($query, $receiver);
			
	echo $query."<br />";
	
	}
	
}

*/



?>