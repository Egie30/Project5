<?php

//error_reporting(E_ALL ^ (E_DEPRECATED | E_NOTICE | E_WARNING));
require_once "/../../framework/database/connect.php";
require_once "/../../framework/functions/default.php";

//mysql_query("TRUNCATE RTL.ACCTG_GL_HEAD"); mysql_query("TRUNCATE RTL.ACCTG_GL_DET"); 

@ini_set('max_execution_time', -1);

$today = date('Y-m-d');

$_GET['personNBR'] = $_SESSION['personNBR'];

$bookNumber	= $_GET['BK_NBR'];

$query_bk 	= "SELECT BK_NBR, BEG_DTE, END_DTE, MONTH(BEG_DTE) AS BK_MONTH, YEAR(BEG_DTE) AS BK_YEAR FROM RTL.ACCTG_BK WHERE BK_NBR = ".$bookNumber." ";
$result_bk	= mysql_query($query_bk);
$row_bk		= mysql_fetch_array($result_bk);

$_GET['CO_NBR']	= $CoNbrDef;
$_GET['BK_NBR']	= $bookNumber;
$_GET['GROUP'] 	= 'MONTH';
$_GET['MONTHS']	= $row_bk['BK_MONTH'];
$_GET['YEARS']	= $row_bk['BK_YEAR'];
	
$ArrayActg	= array("1", "2", "3");


foreach($ArrayActg as $Actg) {

	$_GET['ACTG'] 		= $Actg;
	$_GET['PAY_ACTG_TYP'] 	= 2;		//Biaya Tenaga Kerja Tidak Langsung untuk Laba Rugi

	try {
		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "../payroll.php";

		$results = json_decode(ob_get_clean());
	} catch (\Exception $ex) {
		ob_end_clean();
	}

//echo "<pre>";
//print_r($results);


//===============================================================//
	
$GLTypeNumber	= 4;	//CD_SUB_NBR : Gaji Karyawan RL

$query_typ	= "SELECT GL_TYP_NBR, GL_TYP, GL_DESC, CD_SUB_NBR FROM RTL.ACCTG_GL_TYP WHERE GL_TYP_NBR = ".$GLTypeNumber." ";
$result_typ	= mysql_query($query_typ);
$row_typ	= mysql_fetch_array($result_typ);


$GLTypeNbr	= $row_typ['GL_TYP_NBR'];
$GLType		= $row_typ['GL_TYP'];
$GLDesc		= $row_typ['GL_DESC'];
$CdSubNbr	= $row_typ['CD_SUB_NBR'];

$CdSubBalance	= 3; //CD_SUB_NBR : Bank


foreach($results->data as $resultTransaction) {
	
	$PayrollRL	= $resultTransaction->PAY_AMT;

	if(($PayrollRL) != 0) {
				
		$query		= "SELECT GL_NBR FROM RTL.ACCTG_GL_HEAD WHERE REF='".$GLType."/".$resultTransaction->PAY_DTE."' AND GL_TYP_NBR=".$row_typ['GL_TYP_NBR']." AND ACTG_TYP = ".$Actg." ";
		
		$result		= mysql_query($query);
		$row		= mysql_fetch_array($result);
		$glNumber	= $row['GL_NBR'];
			
		if(empty($row)) {
		
		$query       = "SELECT COALESCE(MAX(GL_NBR),0)+1 AS NEW_NBR FROM RTL.ACCTG_GL_HEAD WHERE DEL_NBR = 0";
		$result      = mysql_query($query);
		$row         = mysql_fetch_array($result);

		$glNumber = $row['NEW_NBR'];

		$query       = "INSERT INTO RTL.ACCTG_GL_HEAD(GL_NBR, BK_NBR, GL_DTE,  GL_DESC, REF, CRT_TS, CRT_NBR, GL_TYP_NBR, ACTG_TYP)
			VALUES (" . $glNumber . ", '" . $bookNumber . "', '" . $resultTransaction->PAY_DTE . "', 'Jurnal ".$GLDesc." (System)', '".$GLType."/" . $resultTransaction->PAY_DTE . "', CURRENT_TIMESTAMP, " . $_GET['personNBR'] . ", ".$GLTypeNbr.", ".$Actg.")";
		mysql_query($query);

		//echo $query."<br />";
		
		$query     = "SELECT COALESCE(MAX(GL_DET_NBR),0) AS NEW_NBR FROM RTL.ACCTG_GL_DET";
		$result    = mysql_query($query);
		$row       = mysql_fetch_array($result);

		$glDetailNumber = $row['NEW_NBR'];
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbr.", " . $PayrollRL . ", 0, " . $_GET['personNBR'] . ")";
		mysql_query($query);
		
		//echo $query."<br />";
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubBalance.", 0, " . $PayrollRL . ", " . $_GET['personNBR'] . ")";
		mysql_query($query);
		
		//echo $query."<br />";
		
		
		}
		else {
			
			$query	= "UPDATE RTL.ACCTG_GL_DET SET DEB =".$PayrollRL." WHERE GL_NBR=".$glNumber." AND CD_SUB_NBR=".$CdSubNbr." ";
			mysql_query($query);
			
			//echo $query."<br />";
			
			$query	= "UPDATE RTL.ACCTG_GL_DET SET CRT =".$PayrollRL." WHERE GL_NBR=".$glNumber." AND CD_SUB_NBR= ".$CdSubBalance." ";
			mysql_query($query);
			
			//echo $query."<br />";
			
		}
	}

	
}


//===============================================================//


	$_GET['ACTG'] 		= $Actg;
	$_GET['PAY_ACTG_TYP'] 	= 1;		//Biaya Tenaga Kerja Langsung untuk HPP

	try {
		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "../payroll.php";

		$results = json_decode(ob_get_clean());
	} catch (\Exception $ex) {
		ob_end_clean();
	}
	
	//echo "<pre>";	print_r($results);
	
$GLTypeNumber	= 5;	//CD_SUB_NBR : Gaji Karyawan HPP

$query_typ	= "SELECT GL_TYP_NBR, GL_TYP, GL_DESC, CD_SUB_NBR FROM RTL.ACCTG_GL_TYP WHERE GL_TYP_NBR = ".$GLTypeNumber." ";
$result_typ	= mysql_query($query_typ);
$row_typ	= mysql_fetch_array($result_typ);


$GLTypeNbr	= $row_typ['GL_TYP_NBR'];
$GLType		= $row_typ['GL_TYP'];
$GLDesc		= $row_typ['GL_DESC'];
$CdSubNbr	= $row_typ['CD_SUB_NBR'];

$CdSubBalance	= 3; //CD_SUB_NBR : Bank

foreach($results->data as $resultTransaction) {
	
	$PayrollHPP	 = $resultTransaction->PAY_AMT;
	
	if(($PayrollHPP) != 0) {
				
		$query		= "SELECT GL_NBR FROM RTL.ACCTG_GL_HEAD WHERE REF='".$GLType."/".$resultTransaction->PAY_DTE."' AND GL_TYP_NBR=".$row_typ['GL_TYP_NBR']." AND ACTG_TYP = ".$Actg." ";
		
		$result		= mysql_query($query);
		$row		= mysql_fetch_array($result);
		$glNumber	= $row['GL_NBR'];
			
		if(empty($row)) {
		
		$query       = "SELECT COALESCE(MAX(GL_NBR),0)+1 AS NEW_NBR FROM RTL.ACCTG_GL_HEAD WHERE DEL_NBR = 0";
		$result      = mysql_query($query);
		$row         = mysql_fetch_array($result);

		$glNumber = $row['NEW_NBR'];

		$query       = "INSERT INTO RTL.ACCTG_GL_HEAD(GL_NBR, BK_NBR, GL_DTE,  GL_DESC, REF, CRT_TS, CRT_NBR, GL_TYP_NBR, ACTG_TYP)
			VALUES (" . $glNumber . ", '" . $bookNumber . "', '" . $resultTransaction->PAY_DTE . "', 'Jurnal ".$GLDesc." (System)', '".$GLType."/" . $resultTransaction->PAY_DTE . "', CURRENT_TIMESTAMP, " . $_GET['personNBR'] . ", ".$GLTypeNbr.", ".$Actg.")";
		mysql_query($query);

		//echo $query."<br />";
		
		$query     = "SELECT COALESCE(MAX(GL_DET_NBR),0) AS NEW_NBR FROM RTL.ACCTG_GL_DET";
		$result    = mysql_query($query);
		$row       = mysql_fetch_array($result);

		$glDetailNumber = $row['NEW_NBR'];
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbr.", " . $PayrollHPP . ", 0, " . $_GET['personNBR'] . ")";
		mysql_query($query);
		
		//echo $query."<br />";
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubBalance.", 0, " . $PayrollHPP . ", " . $_GET['personNBR'] . ")";
		mysql_query($query);
		
		//echo $query."<br />";
		
		
		}
		else {
			
			$query	= "UPDATE RTL.ACCTG_GL_DET SET DEB =".$PayrollHPP." WHERE GL_NBR=".$glNumber." AND CD_SUB_NBR=".$CdSubNbr." ";
			mysql_query($query);
			
			//echo $query."<br />";
			
			$query	= "UPDATE RTL.ACCTG_GL_DET SET CRT =".$PayrollHPP." WHERE GL_NBR=".$glNumber." AND CD_SUB_NBR= ".$CdSubBalance." ";
			mysql_query($query);
			
			//echo $query."<br />";
			
		}
	}

}


unset($_GET['ACTG']);

}

echo "Data berhasil diposting.";