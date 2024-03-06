<?php

require_once "/../../framework/database/connect.php";
require_once "/../../framework/functions/default.php";

//INSERT DATA PEMBAYARAN PEMBELIAN

@ini_set('max_execution_time', -1);

$today = date('Y-m-d');

$_GET['personNBR'] = $_SESSION['personNBR'];

$bookNumber	= $_GET['BK_NBR'];

$query_bk 	= "SELECT BK_NBR, BEG_DTE, END_DTE, MONTH(BEG_DTE) AS BK_MONTH, YEAR(BEG_DTE) AS BK_YEAR FROM RTL.ACCTG_BK WHERE BK_NBR = ".$bookNumber." ";
$result_bk	= mysql_query($query_bk);
$row_bk		= mysql_fetch_array($result_bk);

//echo $query_bk;

$_GET['CO_NBR']	= $CoNbrDef;
$_GET['BK_NBR']	= $bookNumber;
$_GET['GROUP'] 	= 'DAY';
$_GET['MONTHS']	= $row_bk['BK_MONTH'];
$_GET['YEARS']	= $row_bk['BK_YEAR'];


//mysql_query("TRUNCATE RTL.ACCTG_GL_HEAD"); mysql_query("TRUNCATE RTL.ACCTG_GL_DET"); 

//#########################################################################################
	
//BEGIN OF UANG MUKA PEMBELIAN
	
	$GLTypeNumber	= 9;
	
	$query_typ	= "SELECT GL_TYP_NBR, GL_TYP, GL_DESC, CD_SUB_NBR FROM RTL.ACCTG_GL_TYP WHERE GL_TYP_NBR = ".$GLTypeNumber." ";
	$result_typ	= mysql_query($query_typ);
	$row_typ	= mysql_fetch_array($result_typ);


	$GLTypeNbrRC	= $row_typ['GL_TYP_NBR'];
	$GLTypeRC		= $row_typ['GL_TYP'];
	$GLDescRC		= $row_typ['GL_DESC'];
	$CdSubNbr		= $row_typ['CD_SUB_NBR'];
	

$_GET['CAT_TYP_NBR']	= '1,2';

$ArrayActg	= array("1", "2", "3");

foreach($ArrayActg as $Actg) {

	//echo $Actg;
	
	$_GET['ACTG'] 		= $Actg;

	$_GET['IVC_TYP']	= 'RC';
	$_GET['TYP']			= 'ACTG_DOWN';

	try {
		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "../procurement-report.php";

		$results = json_decode(ob_get_clean());
	} catch (\Exception $ex) {
		ob_end_clean();
	}

	
//===============================================================//

	
foreach($results->data as $resultTransaction) {
	
	//Insert Data Pembelian
	
	$payment	= $resultTransaction->TOT_AMT;
	
	if($resultTransaction->PYMT_TYP == "CSH") { $CdSubBalance	= 1; }  //CD_SUB_NBR : Kas
		else { $CdSubBalance	= 3; }  //CD_SUB_NBR : Bank

	
	
	if(($payment) != 0) {
				
		$query		= "SELECT GL_NBR FROM RTL.ACCTG_GL_HEAD WHERE REF='".$GLTypeRC."/".$resultTransaction->ORD_NBR."' AND GL_TYP_NBR=".$GLTypeNbrRC." AND ACTG_TYP = ".$Actg." AND DEL_NBR = 0";
		
		$result		= mysql_query($query);
		$row		= mysql_fetch_array($result);
		$glNumber	= $row['GL_NBR'];
			
		if(empty($row)) {
		
		$query       = "SELECT COALESCE(MAX(GL_NBR),0)+1 AS NEW_NBR FROM RTL.ACCTG_GL_HEAD WHERE DEL_NBR = 0";
		$result      = mysql_query($query);
		$row         = mysql_fetch_array($result);

		$glNumber = $row['NEW_NBR'];

		$query       = "INSERT INTO RTL.ACCTG_GL_HEAD(GL_NBR, BK_NBR, GL_DTE,  GL_DESC, REF, CRT_TS, CRT_NBR, GL_TYP_NBR, ACTG_TYP)
			VALUES (" . $glNumber . ", '" . $bookNumber . "', '" . $resultTransaction->ORD_DTE . "', 'Jurnal ".$GLDescRC."  (System)', '".$GLTypeRC."/" . $resultTransaction->ORD_NBR . "', CURRENT_TIMESTAMP, " . $_GET['personNBR'] . ", ".$GLTypeNbrRC.", ".$Actg.")";
		mysql_query($query);

		//echo $query."<br />";
		
		$query     = "SELECT COALESCE(MAX(GL_DET_NBR),0) AS NEW_NBR FROM RTL.ACCTG_GL_DET";
		$result    = mysql_query($query);
		$row       = mysql_fetch_array($result);

		$glDetailNumber = $row['NEW_NBR'];
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbr.", " . $payment . ", 0, " . $_GET['personNBR'] . ")";
		mysql_query($query);
		
		//echo $query."<br />";
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubBalance.", 0, " . $payment . ", " . $_GET['personNBR'] . ")";
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
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbr.", " . $payment . ", 0, " . $_GET['personNBR'] . ")";
		mysql_query($query);
		
		//echo $query."<br />";
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubBalance.", 0, " . $payment . ", " . $_GET['personNBR'] . ")";
		mysql_query($query);
		
		
		}
	}

} //END OF INSERT JURNAL PEMBAYARAN PEMBELIAN


	unset($_GET['ACTG']);
}

//#########################################################################################
	
unset($_GET['TYP']);

//BEGIN OF PELUNASAN PEMBELIAN
	
	$GLTypeNumber	= 10;
	
	$query_typ	= "SELECT GL_TYP_NBR, GL_TYP, GL_DESC, CD_SUB_NBR FROM RTL.ACCTG_GL_TYP WHERE GL_TYP_NBR = ".$GLTypeNumber." ";
	$result_typ	= mysql_query($query_typ);
	$row_typ	= mysql_fetch_array($result_typ);


	$GLTypeNbrRC	= $row_typ['GL_TYP_NBR'];
	$GLTypeRC		= $row_typ['GL_TYP'];
	$GLDescRC		= $row_typ['GL_DESC'];
	$CdSubNbr		= $row_typ['CD_SUB_NBR'];


$_GET['CAT_TYP_NBR']	= '1,2';

$ArrayActg	= array("1", "2", "3");

foreach($ArrayActg as $Actg) {

	//echo $Actg;
	
	$_GET['ACTG'] 		= $Actg;

	$_GET['IVC_TYP']	= 'RC';
	$_GET['TYP']		= 'ACTG_REM';

	try {
		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "../procurement-report.php";

		$results = json_decode(ob_get_clean());
	} catch (\Exception $ex) {
		ob_end_clean();
	}

	
//===============================================================//

	
foreach($results->data as $resultTransaction) {
	
	//Insert Data Pembelian
	
	$payment	= $resultTransaction->TOT_AMT;
	
	if($resultTransaction->PYMT_TYP == "CSH") { $CdSubBalance	= 1; }  //CD_SUB_NBR : Kas
		else { $CdSubBalance	= 3; }  //CD_SUB_NBR : Bank

	
	
	if(($payment) != 0) {
				
		$query		= "SELECT GL_NBR FROM RTL.ACCTG_GL_HEAD WHERE REF='".$GLTypeRC."/".$resultTransaction->ORD_NBR."' AND GL_TYP_NBR=".$GLTypeNbrRC." AND ACTG_TYP = ".$Actg." AND DEL_NBR = 0";
		
		$result		= mysql_query($query);
		$row		= mysql_fetch_array($result);
		$glNumber	= $row['GL_NBR'];
			
		if(empty($row)) {
		
		$query       = "SELECT COALESCE(MAX(GL_NBR),0)+1 AS NEW_NBR FROM RTL.ACCTG_GL_HEAD WHERE DEL_NBR = 0";
		$result      = mysql_query($query);
		$row         = mysql_fetch_array($result);

		$glNumber = $row['NEW_NBR'];

		$query       = "INSERT INTO RTL.ACCTG_GL_HEAD(GL_NBR, BK_NBR, GL_DTE,  GL_DESC, REF, CRT_TS, CRT_NBR, GL_TYP_NBR, ACTG_TYP)
			VALUES (" . $glNumber . ", '" . $bookNumber . "', '" . $resultTransaction->ORD_DTE . "', 'Jurnal ".$GLDescRC."  (System)', '".$GLTypeRC."/" . $resultTransaction->ORD_NBR . "', CURRENT_TIMESTAMP, " . $_GET['personNBR'] . ", ".$GLTypeNbrRC.", ".$Actg.")";
		mysql_query($query);

		//echo $query."<br />";
		
		$query     = "SELECT COALESCE(MAX(GL_DET_NBR),0) AS NEW_NBR FROM RTL.ACCTG_GL_DET";
		$result    = mysql_query($query);
		$row       = mysql_fetch_array($result);

		$glDetailNumber = $row['NEW_NBR'];
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbr.", " . $payment . ", 0, " . $_GET['personNBR'] . ")";
		mysql_query($query);
		
		//echo $query."<br />";
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubBalance.", 0, " . $payment . ", " . $_GET['personNBR'] . ")";
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
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbr.", " . $payment . ", 0, " . $_GET['personNBR'] . ")";
		mysql_query($query);
		
		//echo $query."<br />";
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubBalance.", 0, " . $payment . ", " . $_GET['personNBR'] . ")";
		mysql_query($query);
		
		
		}
	}

} //END OF INSERT JURNAL PEMBAYARAN PEMBELIAN (PELUNASAN)

	unset($_GET['ACTG']);
}
	//===============================================================//



//echo "Data berhasil diposting.";