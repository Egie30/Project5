<?php

//error_reporting(E_ALL ^ (E_DEPRECATED | E_NOTICE | E_WARNING));
require_once "/../../framework/database/connect.php";
require_once "/../../framework/functions/default.php";

@ini_set('max_execution_time', -1);

$today = date('Y-m-d');

$_GET['personNBR'] = $_SESSION['personNBR'];

$bookNumber	= $_GET['BK_NBR'];

$query_bk 	= "SELECT BK_NBR, BEG_DTE, END_DTE, MONTH(BEG_DTE) AS BK_MONTH, YEAR(BEG_DTE) AS BK_YEAR FROM RTL.ACCTG_BK WHERE BK_NBR = ".$bookNumber." ";
$result_bk	= mysql_query($query_bk);
$row_bk		= mysql_fetch_array($result_bk);

$_GET['CO_NBR']	= $CoNbrDef;
$_GET['BK_NBR']	= $bookNumber;
$_GET['GROUP'] 	= array("DAY","PYMT_TYP");
$_GET['MONTHS']	= $row_bk['BK_MONTH'];
$_GET['YEARS']	= $row_bk['BK_YEAR'];

$_GET['RPT_TYP']	= 'PYMT';

//mysql_query("TRUNCATE RTL.ACCTG_GL_HEAD"); mysql_query("TRUNCATE RTL.ACCTG_GL_DET"); 

$ArrayActg	= array("1", "2", "3");

foreach($ArrayActg as $Actg) {

	$_GET['ACTG'] = $Actg;

	try {
		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "../prn-dig-report-pay.php";

		$resultsPayment = json_decode(ob_get_clean());
	} catch (\Exception $ex) {
		ob_end_clean();
	}
	
	//echo "<pre>";	print_r($resultsPayment);
	
$GLTypeNumber	= 19;	//CD_SUB_NBR : Pembayaran Print Digital

$query_typ	= "SELECT GL_TYP_NBR, GL_TYP, GL_DESC, CD_SUB_NBR, CD_SUB_NBR_DEB, CD_SUB_NBR_CRT FROM RTL.ACCTG_GL_TYP WHERE GL_TYP_NBR = ".$GLTypeNumber." ";
$result_typ	= mysql_query($query_typ);
$row_typ	= mysql_fetch_array($result_typ);


$GLTypeNbr		= $row_typ['GL_TYP_NBR'];
$GLType			= $row_typ['GL_TYP'];
$GLDesc			= $row_typ['GL_DESC'];
$CdSubNbr		= $row_typ['CD_SUB_NBR'];
$CdSubNbrDeb	= $row_typ['CD_SUB_NBR_DEB'];
$CdSubNbrCrt	= $row_typ['CD_SUB_NBR_CRT'];

	foreach($resultsPayment->data as $resultTransaction) {
	
	//print_r($resultTransaction);
	
	if(($resultTransaction->TOT_AMT) != 0) {
	
	
		if ($resultTransaction->PYMT_TYP == 'CSH') {	$CdSubNbrPybl	= 1; } //CD_SUB_NBR : Kas
			else { $CdSubNbrPybl	= 3; } //CD_SUB_NBR : Bank
		
		$query		= "SELECT GL_NBR FROM RTL.ACCTG_GL_HEAD WHERE REF='".$GLType."/".$resultTransaction->CSH_DTE."/".$resultTransaction->PYMT_TYP."' AND GL_TYP_NBR=".$row_typ['GL_TYP_NBR']." AND ACTG_TYP = ".$Actg." ";
		
		//echo $query;
		
		$result		= mysql_query($query);
		$row		= mysql_fetch_array($result);
		$glNumber	= $row['GL_NBR'];
			
		if(empty($row)) {
		
		$query       = "SELECT COALESCE(MAX(GL_NBR),0)+1 AS NEW_NBR FROM RTL.ACCTG_GL_HEAD WHERE DEL_NBR = 0";
		
		//echo $query."<br />";
		
		$result      = mysql_query($query);
		$row         = mysql_fetch_array($result);

		$glNumber = $row['NEW_NBR'];

		$query       = "INSERT INTO RTL.ACCTG_GL_HEAD(GL_NBR, BK_NBR, GL_DTE,  GL_DESC, REF, CRT_TS, CRT_NBR, GL_TYP_NBR, ACTG_TYP)
			VALUES (" . $glNumber . ", '" . $bookNumber . "', '" . $resultTransaction->CSH_DTE . "', 'Jurnal ".$GLDesc." (System)', '".$GLType."/" . $resultTransaction->CSH_DTE . "/".$resultTransaction->PYMT_TYP."', CURRENT_TIMESTAMP, " . $_GET['personNBR'] . ", ".$GLTypeNbr.", ".$Actg.")";
		mysql_query($query);

		//echo $query."<br />";
		
		$query     = "SELECT COALESCE(MAX(GL_DET_NBR),0) AS NEW_NBR FROM RTL.ACCTG_GL_DET";
		$result    = mysql_query($query);
		$row       = mysql_fetch_array($result);

		$glDetailNumber = $row['NEW_NBR'];
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbrPybl.", " . $resultTransaction->TOT_AMT . ", 0, " . $_GET['personNBR'] . ")";
		mysql_query($query);
		
		//echo $query."<br />";
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbrCrt.", 0, " . $resultTransaction->TOT_AMT . ", " . $_GET['personNBR'] . ")";
		mysql_query($query);
		
		//echo $query."<br />";
		}
		
		else {

		$queryDelete 	= "DELETE FROM RTL.ACCTG_GL_DET WHERE GL_NBR = ".$glNumber." ";
		$resultDelete 	= mysql_query($queryDelete);
		
		//echo $queryDelete."<br />";
		
		$query     = "SELECT COALESCE(MAX(GL_DET_NBR),0) AS NEW_NBR FROM RTL.ACCTG_GL_DET";
		$result    = mysql_query($query);
		$row       = mysql_fetch_array($result);

		$glDetailNumber = $row['NEW_NBR'];
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbrPybl.", " . $resultTransaction->TOT_AMT . ", 0, " . $_GET['personNBR'] . ")";
		mysql_query($query);
		
		//echo $query."<br />";
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbrCrt.", 0, " . $resultTransaction->TOT_AMT . ", " . $_GET['personNBR'] . ")";
		mysql_query($query);
		
		//echo $query."<br />";
		
		}
	}


unset($_GET['ACTG']);
	
}
}

//echo "Data berhasil diposting.";