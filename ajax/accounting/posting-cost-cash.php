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
$_GET['GROUP'] 	= 'EXP_TYP';
$_GET['MONTHS']	= $row_bk['BK_MONTH'];
$_GET['YEARS']	= $row_bk['BK_YEAR'];
	
$ArrayActg	= array("2");


foreach($ArrayActg as $Actg) {

	$_GET['ACTG'] = $Actg;

	try {
		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "../cost-cash.php";

		$results = json_decode(ob_get_clean());
	} catch (\Exception $ex) {
		ob_end_clean();
	}

//echo "<pre>"; 
//print_r($results->data);
	
$GLTypeNumber	= 3;	//CD_SUB_NBR : Petty Cash atau biaya rumah tangga

$query_typ	= "SELECT GL_TYP_NBR, GL_TYP, GL_DESC, CD_SUB_NBR FROM RTL.ACCTG_GL_TYP WHERE GL_TYP_NBR = ".$GLTypeNumber." ";
$result_typ	= mysql_query($query_typ);
$row_typ	= mysql_fetch_array($result_typ);


$GLTypeNbr	= $row_typ['GL_TYP_NBR'];
$GLType		= $row_typ['GL_TYP'];
$GLDesc		= $row_typ['GL_DESC'];
$CdSubNbr	= $row_typ['CD_SUB_NBR'];

//$CdSubBalance	= 1; //CD_SUB_NBR : Kas Utama

foreach($results->data as $resultTransaction) {
	
	$CdSubNbrDeb	= $resultTransaction->CD_SUB_NBR_DEB;
	$CdSubNbrCrt	= $resultTransaction->CD_SUB_NBR_CRT;
	
	//echo $resultTransaction->EXP_TYP;
	
	if(($resultTransaction->TOT_SUB) != 0) {
				
		$query		= "SELECT GL_NBR FROM RTL.ACCTG_GL_HEAD WHERE REF='".$GLType."/".$resultTransaction->EXP_TYP."' AND GL_TYP_NBR=".$row_typ['GL_TYP_NBR']." AND ACTG_TYP = ".$Actg." ";
		
		$result		= mysql_query($query);
		$row		= mysql_fetch_array($result);
		$glNumber	= $row['GL_NBR'];
			
		if(empty($row)) {
		
		$query       = "SELECT COALESCE(MAX(GL_NBR),0)+1 AS NEW_NBR FROM RTL.ACCTG_GL_HEAD WHERE DEL_NBR = 0";
		$result      = mysql_query($query);
		$row         = mysql_fetch_array($result);

		$glNumber = $row['NEW_NBR'];

		$query       = "INSERT INTO RTL.ACCTG_GL_HEAD(GL_NBR, BK_NBR, GL_DTE,  GL_DESC, REF, CRT_TS, CRT_NBR, GL_TYP_NBR, ACTG_TYP)
			VALUES (" . $glNumber . ", '" . $bookNumber . "', '" . $resultTransaction->EXP_DTE . "', '".$GLDesc." Journal (System)', '".$GLType."/" . $resultTransaction->EXP_TYP . "', CURRENT_TIMESTAMP, " . $_GET['personNBR'] . ", ".$GLTypeNbr.", ".$Actg.")";
		mysql_query($query);

		//echo $query."<br />";
		
		$query     = "SELECT COALESCE(MAX(GL_DET_NBR),0) AS NEW_NBR FROM RTL.ACCTG_GL_DET";
		$result    = mysql_query($query);
		$row       = mysql_fetch_array($result);

		$glDetailNumber = $row['NEW_NBR'];
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbrDeb.", " . $resultTransaction->TOT_SUB . ", 0, " . $_GET['personNBR'] . ")";
		mysql_query($query);
		
		//echo $query."<br />";
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbrCrt.", 0, " . $resultTransaction->TOT_SUB . ", " . $_GET['personNBR'] . ")";
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
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbrDeb.", " . $resultTransaction->TOT_SUB . ", 0, " . $_GET['personNBR'] . ")";
		mysql_query($query);
		
		//echo $query."<br />";
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbrCrt.", 0, " . $resultTransaction->TOT_SUB . ", " . $_GET['personNBR'] . ")";
		mysql_query($query);
		
		//echo $query."<br />";
		
		}
	}


unset($_GET['ACTG']);
	
}
}

echo "Data berhasil diposting.";