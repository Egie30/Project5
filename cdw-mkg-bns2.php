<?php
include "framework/database/connect.php";

//Mengambil configurasi tanggal payroll bedasarkan tanggal kemarin
$query = "SELECT PAY_CONFIG_NBR, PAY_BEG_DTE, PAY_END_DTE FROM PAY.PAY_CONFIG_DTE WHERE PAY_CONFIG_NBR = 43";
//$query = "SELECT PAY_CONFIG_NBR, PAY_BEG_DTE, PAY_END_DTE FROM PAY.PAY_CONFIG_DTE WHERE PAY_BEG_DTE <= CURRENT_DATE AND PAY_END_DTE >= CURRENT_DATE";
$result = mysql_query($query);
$row    = mysql_fetch_array($result);
$PayConfigNbr = $row['PAY_CONFIG_NBR'];
$PayBegDte    = $row['PAY_BEG_DTE'];
$PayEndDte    = $row['PAY_END_DTE'];

//Untuk menghapus data CDW yang memiliki PAY_CONFIG_NBR hari kemarin
$query  = "DELETE FROM CDW.MKG_BNS WHERE PRN_CO_NBR = ". $CoNbrDef ." AND PAY_CONFIG_NBR = ".$PayConfigNbr;
$result=mysql_query($query);

$_GET['PAY_CONFIG_NBR'] = $PayConfigNbr;
$_GET['PAY_BEG_DTE'] 	= $PayBegDte;
$_GET['PAY_END_DTE'] 	= $PayEndDte;
		
try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "cdw-mkg-bns-ajax.php";

	$results = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}
echo "<pre>";
print_r($results);

foreach ($results->data as $data) {
	$sql = "INSERT INTO CDW.MKG_BNS(
		PYMT_NBR,
		ORD_NBR,
		ORD_TS,
		REF_NBR,
		ORD_TTL,
		ORD_STT_ID,
		ORD_STT_DESC,
		ACCT_EXEC_NBR,
		BUY_CO_NBR,
		BUY_CO_NAME,
		DUE_DTE,
		BILL_DTE,
		PRN_CO_NBR,
		PRN_CO_NAME,
		TOT_AMT,
		TOT_REM,
		PYMT_TYP,
		TND_AMT,
		BNK_CO_NBR,
		VAL_NBR,
		DEPN_PCT,
		BNS_AMT,
		CRT_NBR,
		CRT_TS,
		UPD_NBR,
		UPD_TS,
		PAY_CONFIG_NBR,
		ACT_F
	)VALUES (
		'".$data->PYMT_NBR."',
		'".$data->ORD_NBR."',
		'".$data->ORD_TS."',
		'".$data->REF_NBR."',
		'".$data->ORD_TTL."', 
		'".$data->ORD_STT_ID."', 
		'".$data->ORD_STT_DESC."', 
		'".$data->ACCT_EXEC_NBR."', 
		'".$data->BUY_CO_NBR."', 
		'".$data->BUY_CO_NAME."', 
		'".$data->DUE_DTE."', 
		'".$data->BILL_DTE."', 
		'".$data->PRN_CO_NBR."', 
		'".$data->PRN_CO_NAME."', 
		'".$data->TOT_AMT."', 
		'".$data->TOT_REM."', 
		'".$data->PYMT_TYP."', 
		'".$data->TND_AMT."', 
		'".$data->BNK_CO_NBR."', 
		'".$data->VAL_NBR."', 
		'".$data->DEPN_PCT."', 
		'".$data->BNS_AMT."', 
		'".$data->CRT_NBR."', 
		'".$data->CRT_TS."', 
		'".$data->UPD_NBR."', 
		'".$data->UPD_TS."', 
		'".$data->PAY_CONFIG_NBR."', 
		'".$data->ACT_F."'	
	)";
	echo $sql."<br><br>";
	//$result = mysql_query($sql,$cloud);
	//$sql 	= str_replace($CDW,"CDW",$sql);
	$result = mysql_query($sql);
}
?>