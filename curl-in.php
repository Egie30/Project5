<?php
//mysql_connect('localhost', 'root', 'Pr0reliance');
//mysql_select_db('CMP');
include "framework/database/connect.php";
$query  = "SELECT  TBL_NM,DB_NM FROM NST.SYNC_LIST WHERE SYNC_DIR_TYP IN ('U')";
echo $query."<br/>";
//$query .= " AND TBL_NM = 'PRN_DIG_ORD_DET'";
$result = mysql_query($query);
while($row = mysql_fetch_array($result)){
	echo "POS DATA TABEL ".$row['TBL_NM']."<br/>";
	postData($row['DB_NM'],$row['TBL_NM']);
}
$query = "UPDATE CDW.UPD_LAST SET CURL_SYNC_NA = NOW()";
mysql_query($query);

function maxLastDate(){
	$query = "SELECT CURL_SYNC_NA FROM CDW.UPD_LAST";
	$result = mysql_query($query);
	$row = mysql_fetch_array($result);
	$upd_last = $row['CURL_SYNC_NA'];
	return $upd_last;
}

function postData($db_nm,$tbl_nm){
	echo "MILIKNYA TABEL ".$tbl_nm."<br/>";
	$query	= "SELECT COUNT(*) AS JUMLAH FROM ".$db_nm.".".$tbl_nm." WHERE UPD_TS>='".maxLastDate()."'";
	//$query  = "SELECT UPDATE_TIME AS UPD_TS FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='".$db_nm."' AND TABLE_NAME='".$tbl_nm."'";
	echo $query."<br/>";
	$result = mysql_query($query);
	$rowUpd = mysql_fetch_array($result);
	echo "JUMLAHNYA ".$rowUpd['JUMLAH']."<br/>";
	if ($rowUpd['JUMLAH'] > 0){
		$query 	= "SELECT COLUMN_NAME, IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='".$db_nm."' AND table_name = '".$tbl_nm."'";
		$result = mysql_query($query);
		$where  = "";
		while($rowTab = mysql_fetch_array($result)){
			if ($rowTab['COLUMN_NAME']=='UPD_TS'){
				$where .= " WHERE UPD_TS >'" .maxLastDate()."'";
			}elseif($rowTab['COLUMN_NAME']=='CRT_TS' && $rowTab['COLUMN_NAME']=='UPD_TS'){
				$where .= " WHERE CRT_TS >'" .maxLastDate()."'";
			}
		}
		
		$query 	= "SELECT *, (SELECT CO_NBR_DEF FROM NST.PARAM_LOC) AS OWN_CO_NBR  FROM ".$db_nm.".".$tbl_nm;
		$query .= $where;
		echo $query;
		
		$result = mysql_query($query);
		echo "<pre>";
		while($row =mysql_fetch_assoc($result)){
			$data[]=$row;
			//print_r($row);
		}
		
		print_r(count($data));
		$data_string = urlencode(json_encode($data));
		CurlData($db_nm,$tbl_nm,$data_string);
		
	}
}

function CurlData($database, $table, $DataCurl){
	$url 	= "http://nestor.asia/champion/coba.php";
	$ch		= curl_init();
	
	$ch=curl_init($url);
	
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, array('database'=>$database,'table'=>$table,'data'=>$DataCurl));

	$output = curl_exec($ch);
	if ($output === FALSE) {
		echo "<span style='color:red'>cURL Error: " . curl_error($ch)."<br/><br/></span>";
	} else {
		var_dump($output);
		echo "<br/><br/>";
	}
	curl_close($ch);
}
?>