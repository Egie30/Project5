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
$_GET['GROUP'] 	= 'UTL_NBR';
$_GET['MONTHS']	= $row_bk['BK_MONTH'];
$_GET['YEARS']	= $row_bk['BK_YEAR'];
	
$ArrayActg	= array("2"); //semua beban pengeluaran rutin di tanggung rekening 2

//mysql_query("TRUNCATE RTL.ACCTG_GL_HEAD"); mysql_query("TRUNCATE RTL.ACCTG_GL_DET"); 


foreach($ArrayActg as $Actg) {

	$_GET['ACTG'] = $Actg;

	try {
		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "../cost-routine.php";

		$results = json_decode(ob_get_clean());
	} catch (\Exception $ex) {
		ob_end_clean();
	}
	
//echo '<pre>'; print_r($results);


foreach($results->data as $resultTransaction) {
		
		
	$GLTypeNumber	= 17;	//Pengeluaran Rutin untuk HPP

	$query_typ	= "SELECT GL_TYP_NBR, GL_TYP, GL_DESC, CD_SUB_NBR FROM RTL.ACCTG_GL_TYP WHERE GL_TYP_NBR = ".$GLTypeNumber." ";
	$result_typ	= mysql_query($query_typ);
	$row_typ	= mysql_fetch_array($result_typ);


	$GLTypeNbr	= $row_typ['GL_TYP_NBR'];
	$GLType		= $row_typ['GL_TYP'];
	$GLDesc		= $row_typ['GL_DESC'];

	$CdSubNbrDeb	= $resultTransaction->CD_SUB_NBR_DEB;
	$CdSubNbrCrt	= $resultTransaction->CD_SUB_NBR_CRT;
	
	if(($resultTransaction->TOT_SUB) != 0) {
		
		$RoutineHPP 	= $resultTransaction->TOT_SUB * 0.9;
		
		$query		= "SELECT GL_NBR FROM RTL.ACCTG_GL_HEAD WHERE REF='".$GLType."/".$resultTransaction->UTL_NBR."' AND GL_TYP_NBR=".$row_typ['GL_TYP_NBR']." AND ACTG_TYP = ".$Actg." AND DEL_NBR = 0";
		
		$result		= mysql_query($query);
		$row		= mysql_fetch_array($result);
		$glNumber	= $row['GL_NBR'];
		
		////echo $query."<br />";
		
		if(empty($row)) {
		
		$query       = "SELECT COALESCE(MAX(GL_NBR),0)+1 AS NEW_NBR FROM RTL.ACCTG_GL_HEAD WHERE DEL_NBR = 0";
		$result      = mysql_query($query);
		$row         = mysql_fetch_array($result);

		$glNumber = $row['NEW_NBR'];

		$query       = "INSERT INTO RTL.ACCTG_GL_HEAD(GL_NBR, BK_NBR, GL_DTE,  GL_DESC, REF, CRT_TS, CRT_NBR, GL_TYP_NBR, ACTG_TYP)
			VALUES (" . $glNumber . ", '" . $bookNumber . "', '" . $resultTransaction->UTL_DTE . "', '".$GLDesc." Journal (System)', '".$GLType."/" . $resultTransaction->UTL_NBR . "', CURRENT_TIMESTAMP, " . $_GET['personNBR'] . ", ".$GLTypeNbr.", ".$Actg.")";
		mysql_query($query);

		//echo $query."<br />";
		
		$query     = "SELECT COALESCE(MAX(GL_DET_NBR),0) AS NEW_NBR FROM RTL.ACCTG_GL_DET";
		$result    = mysql_query($query);
		$row       = mysql_fetch_array($result);

		$glDetailNumber = $row['NEW_NBR'];
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbrDeb.", " . $RoutineHPP . ", 0, " . $_GET['personNBR'] . ")";
		mysql_query($query);
		
		////echo $query."<br />";
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbrCrt.", 0, " . $RoutineHPP . ", " . $_GET['personNBR'] . ")";
		mysql_query($query);
		
		////echo $query."<br />";
		
		
		}
		
		else {

		$queryDelete 	= "DELETE FROM RTL.ACCTG_GL_DET WHERE GL_NBR = ".$glNumber." ";
		$resultDelete 	= mysql_query($queryDelete);
		
		////echo $queryDelete."<br />";
		
		$query     = "SELECT COALESCE(MAX(GL_DET_NBR),0) AS NEW_NBR FROM RTL.ACCTG_GL_DET";
		$result    = mysql_query($query);
		$row       = mysql_fetch_array($result);

		$glDetailNumber = $row['NEW_NBR'];
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbrDeb.", " . $RoutineHPP . ", 0, " . $_GET['personNBR'] . ")";
		mysql_query($query);
		
		////echo $query."<br />";
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbrCrt.", 0, " . $RoutineHPP . ", " . $_GET['personNBR'] . ")";
		mysql_query($query);
		
		////echo $query."<br />";
		
		}
		

		
					
		$GLTypeNumber	= 18;	//Pengeluaran Rutin untuk Rugi Laba

		$query_typ	= "SELECT GL_TYP_NBR, GL_TYP, GL_DESC, CD_SUB_NBR FROM RTL.ACCTG_GL_TYP WHERE GL_TYP_NBR = ".$GLTypeNumber."";
		$result_typ	= mysql_query($query_typ);
		$row_typ	= mysql_fetch_array($result_typ);


		$GLTypeNbr	= $row_typ['GL_TYP_NBR'];
		$GLType		= $row_typ['GL_TYP'];
		$GLDesc		= $row_typ['GL_DESC'];
		//$CdSubNbr	= $row_typ['CD_SUB_NBR'];

				$RoutineRL	= $resultTransaction->TOT_SUB * 0.1;
				
		$query		= "SELECT GL_NBR FROM RTL.ACCTG_GL_HEAD WHERE REF='".$GLType."/".$resultTransaction->UTL_NBR."' AND GL_TYP_NBR=".$row_typ['GL_TYP_NBR']." AND ACTG_TYP = ".$Actg." AND DEL_NBR = 0";
		
		$result		= mysql_query($query);
		$row		= mysql_fetch_array($result);
		$glNumber	= $row['GL_NBR'];
			
		if(empty($row)) {
		
		$query       = "SELECT COALESCE(MAX(GL_NBR),0)+1 AS NEW_NBR FROM RTL.ACCTG_GL_HEAD WHERE DEL_NBR = 0";
		$result      = mysql_query($query);
		$row         = mysql_fetch_array($result);

		$glNumber = $row['NEW_NBR'];

		$query       = "INSERT INTO RTL.ACCTG_GL_HEAD(GL_NBR, BK_NBR, GL_DTE,  GL_DESC, REF, CRT_TS, CRT_NBR, GL_TYP_NBR, ACTG_TYP)
			VALUES (" . $glNumber . ", '" . $bookNumber . "', '" . $resultTransaction->UTL_DTE . "', '".$GLDesc." Journal (System)', '".$GLType."/" . $resultTransaction->UTL_NBR . "', CURRENT_TIMESTAMP, " . $_GET['personNBR'] . ", ".$GLTypeNbr.", ".$Actg.")";
		mysql_query($query);

		////echo $query."<br />";
		
		$query     = "SELECT COALESCE(MAX(GL_DET_NBR),0) AS NEW_NBR FROM RTL.ACCTG_GL_DET";
		$result    = mysql_query($query);
		$row       = mysql_fetch_array($result);

		$glDetailNumber = $row['NEW_NBR'];
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbrDeb.", " . $RoutineRL . ", 0, " . $_GET['personNBR'] . ")";
		mysql_query($query);
		
		////echo $query."<br />";
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbrCrt.", 0, " . $RoutineRL . ", " . $_GET['personNBR'] . ")";
		mysql_query($query);
		
		////echo $query."<br />";
		
		
		}else {

		$queryDelete 	= "DELETE FROM RTL.ACCTG_GL_DET WHERE GL_NBR = ".$glNumber." ";
		$resultDelete 	= mysql_query($queryDelete);
		
		////echo $queryDelete."<br />";
		
		$query     = "SELECT COALESCE(MAX(GL_DET_NBR),0) AS NEW_NBR FROM RTL.ACCTG_GL_DET";
		$result    = mysql_query($query);
		$row       = mysql_fetch_array($result);

		$glDetailNumber = $row['NEW_NBR'];
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbrDeb.", " . $RoutineRL . ", 0, " . $_GET['personNBR'] . ")";
		mysql_query($query);
		
		////echo $query."<br />";
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbrCrt.", 0, " . $RoutineRL . ", " . $_GET['personNBR'] . ")";
		mysql_query($query);
		
		////echo $query."<br />";
		
		}
	
	}

	}
	
	
unset($_GET['ACTG']);
}

	


echo "Data berhasil diposting.";