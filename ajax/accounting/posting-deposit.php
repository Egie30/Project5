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
$_GET['GROUP'] 	= 'ACCT';
$_GET['MONTHS']	= $row_bk['BK_MONTH'];
$_GET['YEARS']	= $row_bk['BK_YEAR'];

$BeginDate	= $row_bk['BEG_DTE'];
$EndDate	= $row_bk['END_DTE'];

	
$GLTypeNumber	= 11;	//CD_SUB_NBR : Setoran

$query_typ	= "SELECT GL_TYP_NBR, GL_TYP, GL_DESC, CD_SUB_NBR FROM RTL.ACCTG_GL_TYP WHERE GL_TYP_NBR = ".$GLTypeNumber." ";
$result_typ	= mysql_query($query_typ);
$row_typ	= mysql_fetch_array($result_typ);


$GLTypeNbr	= $row_typ['GL_TYP_NBR'];
$GLType		= $row_typ['GL_TYP'];
$GLDesc		= $row_typ['GL_DESC'];
$CdSubNbr	= $row_typ['CD_SUB_NBR'];

$CdSubBalance	= 3; 	 //CD_SUB_NBR : Bank

//mysql_query("TRUNCATE RTL.ACCTG_GL_HEAD"); mysql_query("TRUNCATE RTL.ACCTG_GL_DET"); 

$ArrayActg	= array("1", "2", "3");


foreach($ArrayActg as $Actg) {

	if($Actg == '1') { $Acct = 'PT'; }
		else if($Actg == '2') { $Acct = 'CV'; }
			else if($Actg == '3') { $Acct = 'PR'; }

	$queryData	= "SELECT 
					DATE(DEP_DTE) AS DEP_DTE,
					SUM(CSH_AMT) AS AMT
				FROM RTL.CSH_DAY 
				WHERE DEL_NBR = 0 
					AND DEP_DTE >= '".$BeginDate."'
					AND DEP_DTE <= '".$EndDate."'
					AND ACCT = '".$Acct."'
				GROUP BY DEP_DTE
				";
	
	$resultData	= mysql_query($queryData);
	

	while($rowData = mysql_fetch_array($resultData)) {
	
	//print_r($rowData);
	
	if(($rowData['AMT']) != 0) {
			
		$query		= "SELECT GL_NBR FROM RTL.ACCTG_GL_HEAD WHERE REF='".$GLType."/".$rowData['DEP_DTE']."' AND GL_TYP_NBR=".$row_typ['GL_TYP_NBR']." AND ACTG_TYP = ".$Actg." ";
		
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
			VALUES (" . $glNumber . ", '" . $bookNumber . "', '" . $rowData['DEP_DTE'] . "', 'Jurnal ".$GLDesc." (System)', '".$GLType."/" . $rowData['DEP_DTE'] . "', CURRENT_TIMESTAMP, " . $_GET['personNBR'] . ", ".$GLTypeNbr.", ".$Actg.")";
		mysql_query($query);

		//echo $query."<br />";
		
		$query     = "SELECT COALESCE(MAX(GL_DET_NBR),0) AS NEW_NBR FROM RTL.ACCTG_GL_DET";
		$result    = mysql_query($query);
		$row       = mysql_fetch_array($result);

		$glDetailNumber = $row['NEW_NBR'];
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubBalance.", " . $rowData['AMT'] . ", 0, " . $_GET['personNBR'] . ")";
		mysql_query($query);
		
		//echo $query."<br />";
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbr.", 0, " . $rowData['AMT'] . ", " . $_GET['personNBR'] . ")";
		mysql_query($query);
		
		//echo $query."<br />";
		}
		
		else {			
			
			$queryDelete 	= "DELETE FROM RTL.ACCTG_GL_DET WHERE GL_NBR = ".$glNumber." ";
			$resultDelete 	= mysql_query($queryDelete);
			
			$query     = "SELECT COALESCE(MAX(GL_DET_NBR),0) AS NEW_NBR FROM RTL.ACCTG_GL_DET";
			$result    = mysql_query($query);
			$row       = mysql_fetch_array($result);

			$glDetailNumber = $row['NEW_NBR'];
			
			$glDetailNumber++;
			$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
				VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubBalance.", " . $rowData['AMT'] . ", 0, " . $_GET['personNBR'] . ")";
			mysql_query($query);
			
			//echo $query."<br />";
			
			$glDetailNumber++;
			$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
				VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbr.", 0, " . $rowData['AMT'] . ", " . $_GET['personNBR'] . ")";
			mysql_query($query);
			
			//echo $query."<br />";
			
			
		}
	}
		
	
	}
}





echo "Data berhasil diposting.";