<?php

require_once "/../../framework/database/connect.php";
require_once "/../../framework/functions/default.php";


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
$_GET['GROUP'] 	= 'ORD_NBR';
$_GET['MONTHS']	= $row_bk['BK_MONTH'];
$_GET['YEARS']	= $row_bk['BK_YEAR'];

//$_GET['TYP']			= 'ACTG';
$_GET['CAT_TYP_NBR']	= '1,2';

$ArrayActg	= array("1","2","3");

mysql_query("TRUNCATE RTL.ACCTG_GL_HEAD"); mysql_query("TRUNCATE RTL.ACCTG_GL_DET"); 

foreach($ArrayActg as $Actg) {

	//echo $Actg;
	
	$_GET['ACTG'] 		= $Actg;

	$_GET['IVC_TYP']	= 'RC';
	
	try {
		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "../procurement-report.php";

		$results = json_decode(ob_get_clean());
	} catch (\Exception $ex) {
		ob_end_clean();
	}


$GLTypeNumber	= 6;	//CD_SUB_NBR : Pembelian

	$query_typ	= "SELECT GL_TYP_NBR, GL_TYP, GL_DESC, CD_SUB_NBR FROM RTL.ACCTG_GL_TYP WHERE GL_TYP_NBR = ".$GLTypeNumber." ";
	$result_typ	= mysql_query($query_typ);
	$row_typ	= mysql_fetch_array($result_typ);


	$GLTypeNbrRC	= $row_typ['GL_TYP_NBR'];
	$GLTypeRC		= $row_typ['GL_TYP'];
	$GLDescRC		= $row_typ['GL_DESC'];
	//$CdSubNbrRC		= $row_typ['CD_SUB_NBR'];

$GLTypeNumber	= 9;	//CD_SUB_NBR : Hutang Dagang

	$query_typ	= "SELECT GL_TYP_NBR, GL_TYP, GL_DESC, CD_SUB_NBR FROM RTL.ACCTG_GL_TYP WHERE GL_TYP_NBR = ".$GLTypeNumber." ";
	$result_typ	= mysql_query($query_typ);
	$row_typ	= mysql_fetch_array($result_typ);

	$GLTypeNbrPybl	= $row_typ['GL_TYP_NBR'];
	$GLTypePybl		= $row_typ['GL_TYP'];
	$GLDescPybl		= $row_typ['GL_DESC'];
	$CdSubNbrPybl	= $row_typ['CD_SUB_NBR'];

$GLTypeNumber	= 7;	//CD_SUB_NBR : Retur Pembelian

	$query_typ	= "SELECT GL_TYP_NBR, GL_TYP, GL_DESC, CD_SUB_NBR FROM RTL.ACCTG_GL_TYP WHERE GL_TYP_NBR = ".$GLTypeNumber." ";
	$result_typ	= mysql_query($query_typ);
	$row_typ	= mysql_fetch_array($result_typ);


	$GLTypeNbrRT	= $row_typ['GL_TYP_NBR'];
	$GLTypeRT		= $row_typ['GL_TYP'];
	$GLDescRT		= $row_typ['GL_DESC'];
	$CdSubNbrRT		= $row_typ['CD_SUB_NBR'];


	
//===============================================================//

	
foreach($results->data as $resultTransaction) {
	
	//Insert Data Pembelian
	
	//echo $resultTransaction->TAX_APL_ID;
	
	$Purchase	= $resultTransaction->TOT_AMT;
	
	if($resultTransaction->CAT_TYP_NBR == 1) {	$GLTypeNumber = 6; $GLTypePPN = 20;}
	if($resultTransaction->CAT_TYP_NBR == 2) {	$GLTypeNumber = 13; $GLTypePPN = 22;}
	
	$query_typ	= "SELECT GL_TYP_NBR, GL_TYP, GL_DESC, CD_SUB_NBR FROM RTL.ACCTG_GL_TYP WHERE GL_TYP_NBR = ".$GLTypeNumber." ";
	$result_typ	= mysql_query($query_typ);
	$row_typ	= mysql_fetch_array($result_typ);


	$GLTypeNbrRC	= $row_typ['GL_TYP_NBR'];
	$GLTypeRC		= $row_typ['GL_TYP'];
	$GLDescRC		= $row_typ['GL_DESC'];
	$CdSubNbrRC		= $row_typ['CD_SUB_NBR'];
	
	
	$query_ppn	= "SELECT CD_SUB_NBR FROM RTL.ACCTG_GL_TYP WHERE GL_TYP_NBR = ".$GLTypePPN.""; //PPN Masukan
	$result_ppn	= mysql_query($query_ppn);
	$row_ppn	= mysql_fetch_array($result_ppn);

	$CdSubPPN	= $row_ppn['CD_SUB_NBR'];
		 
	if (($Purchase) != 0) {
		
		if ($resultTransaction->ACTG_TYP == 1) {
			$total 		= $resultTransaction->SUBTOTAL;
		}
		else {
			$total 		= $resultTransaction->TOT_AMT;
		}
		
		
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
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbrRC.", " . $total . ", 0, " . $_GET['personNBR'] . ")";
		mysql_query($query);
		
		//echo $query."<br />";
		
		if (($resultTransaction->TAX_APL_ID != 'E') && ($resultTransaction->ACTG_TYP == 1)) {
						
				$glDetailNumber++;
				$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
					VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubPPN.", " . $resultTransaction->TAX_AMT . ", 0, " . $_GET['personNBR'] . ")";
				
				//echo $query;
				
				mysql_query($query);
			}
			
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbrPybl.", 0, " . $Purchase . ", " . $_GET['personNBR'] . ")";
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
				VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbrRC.", " . $total . ", 0, " . $_GET['personNBR'] . ")";
			mysql_query($query);
			
			//echo $query."<br />";
			
			if (($resultTransaction->TAX_APL_ID != 'E') && ($resultTransaction->ACTG_TYP == 1)) {
							
					$glDetailNumber++;
					$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
						VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubPPN.", " . $resultTransaction->TAX_AMT . ", 0, " . $_GET['personNBR'] . ")";
					
					//echo $query;
					
					mysql_query($query);
				}
				
			$glDetailNumber++;
			$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
				VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbrPybl.", 0, " . $Purchase . ", " . $_GET['personNBR'] . ")";
			mysql_query($query);
			
			//echo $query."<br />";
			
		}
		
		
	}

	} //END OF INSERT JURNAL PEMBELIAN

	//===============================================================//

	
	
	
	
	
unset($_GET['ACTG']);

}

include __DIR__ . DIRECTORY_SEPARATOR . "posting-purchase-payment.php";

echo "Data berhasil diposting.";