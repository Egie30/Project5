<?php

require_once "/../../framework/database/connect.php";
require_once "/../../framework/functions/default.php";



//mysql_query("TRUNCATE RTL.ACCTG_GL_HEAD"); mysql_query("TRUNCATE RTL.ACCTG_GL_DET"); 

@ini_set('max_execution_time', -1);

$today = date('Y-m-d');

$_GET['personNBR'] = $_SESSION['personNBR'];

$bookNumber	= $_GET['BK_NBR'];

$ArrayActg	= array("1", "2", "3");

$query_bk	= "SELECT BK_NBR, 
				(BEG_DTE - INTERVAL 1 DAY) AS BEGIN, 
				BEG_DTE AS BEG_DT,
				END_DTE AS END_DT,
				MONTH(BEG_DTE) AS BK_MONTH,
				YEAR(BEG_DTE) AS BK_YEAR,
				MONTH(BEG_DTE - INTERVAL 1 MONTH) AS BK_MONTH_BEG,
				YEAR(BEG_DTE - INTERVAL 1 MONTH) AS BK_YEAR_BEG
			FROM RTL.ACCTG_BK WHERE BK_NBR = ".$bookNumber." ";
$result_bk 	= mysql_query($query_bk);
$row_bk		= mysql_fetch_array($result_bk);

foreach($ArrayActg as $Actg) {

	$_GET['ACTG'] = $Actg;


	try {
		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "hpp.php";

		$results = json_decode(ob_get_clean());
	} catch (\Exception $ex) {
		ob_end_clean();
	}
	
	//echo "<pre>"; print_r($results);
	
	//==================== INSERT HPP BAHAN BAKU ===================//
			
	
	if(($results->data->HPP_MAIN) != 0) {
		
		$HPP_Main	= $results->data->HPP_MAIN;

		$GLTypeNumber	= 6;	//CD_SUB_NBR : Persediaan Bahan Baku

		$query_typ	= "SELECT GL_TYP_NBR, GL_TYP, GL_DESC, CD_SUB_NBR FROM RTL.ACCTG_GL_TYP WHERE GL_TYP_NBR = ".$GLTypeNumber." ";
		$result_typ	= mysql_query($query_typ);
		$row_typ	= mysql_fetch_array($result_typ);

		$CdSubBalance	= $row_typ['CD_SUB_NBR'];
		
		$GLTypeNumber	= 24;	//CD_SUB_NBR : HPP Bahan Baku

		$query_typ	= "SELECT GL_TYP_NBR, GL_TYP, GL_DESC, CD_SUB_NBR FROM RTL.ACCTG_GL_TYP WHERE GL_TYP_NBR = ".$GLTypeNumber." ";
		$result_typ	= mysql_query($query_typ);
		$row_typ	= mysql_fetch_array($result_typ);


		$GLTypeNbr	= $row_typ['GL_TYP_NBR'];
		$GLType		= $row_typ['GL_TYP'];
		$GLDesc		= $row_typ['GL_DESC'];
		$CdSubNbr	= $row_typ['CD_SUB_NBR'];
				
		$query		= "SELECT GL_NBR FROM RTL.ACCTG_GL_HEAD WHERE REF='".$GLType."/".$row_bk['END_DT']."' AND GL_TYP_NBR=".$GLTypeNumber." AND ACTG_TYP = ".$Actg." AND DEL_NBR = 0";
		
		//echo $query."<br />";
		
		$result		= mysql_query($query);
		$row		= mysql_fetch_array($result);
		$glNumber	= $row['GL_NBR'];
		
		if(empty($row)) {
		
		$query       = "SELECT COALESCE(MAX(GL_NBR),0)+1 AS NEW_NBR FROM RTL.ACCTG_GL_HEAD WHERE DEL_NBR = 0";
		$result      = mysql_query($query);
		$row         = mysql_fetch_array($result);

		$glNumber = $row['NEW_NBR'];

		$query       = "INSERT INTO RTL.ACCTG_GL_HEAD(GL_NBR, BK_NBR, GL_DTE,  GL_DESC, REF, CRT_TS, CRT_NBR, GL_TYP_NBR, ACTG_TYP)
			VALUES (" . $glNumber . ", '" . $bookNumber . "', '" . $row_bk['END_DT'] . "', '".$GLDesc." Journal (System)', '".$GLType."/" . $row_bk['END_DT'] . "', CURRENT_TIMESTAMP, " . $_GET['personNBR'] . ", ".$GLTypeNbr.", ".$Actg.")";
		mysql_query($query);

		//echo $query."<br />";
		
		$query     = "SELECT COALESCE(MAX(GL_DET_NBR),0) AS NEW_NBR FROM RTL.ACCTG_GL_DET";
		$result    = mysql_query($query);
		$row       = mysql_fetch_array($result);

		$glDetailNumber = $row['NEW_NBR'];
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbr.", " . $HPP_Main . ", 0, " . $_GET['personNBR'] . ")";
		mysql_query($query);
		
		//echo $query."<br />";
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubBalance.", 0, " . $HPP_Main . ", " . $_GET['personNBR'] . ")";
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
				VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbr.", " . $HPP_Main . ", 0, " . $_GET['personNBR'] . ")";
			mysql_query($query);
			
			//echo $query."<br />";
			
			$glDetailNumber++;
			$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
				VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubBalance.", 0, " . $HPP_Main . ", " . $_GET['personNBR'] . ")";
			mysql_query($query);
			
			//echo $query."<br />";
			
		}
	}


	
	//==================== INSERT HPP BAHAN PEMBANTU ===================//
	
	if(($results->data->HPP_SUB) != 0) {
		
		$HPP_Sub	= $results->data->HPP_SUB;

		

		$GLTypeNumber	= 13;	//CD_SUB_NBR : Persediaan Bahan Baku

		$query_typ	= "SELECT GL_TYP_NBR, GL_TYP, GL_DESC, CD_SUB_NBR FROM RTL.ACCTG_GL_TYP WHERE GL_TYP_NBR = ".$GLTypeNumber." ";
		$result_typ	= mysql_query($query_typ);
		$row_typ	= mysql_fetch_array($result_typ);

		$CdSubBalance	= $row_typ['CD_SUB_NBR'];

		$GLTypeNumber	= 25;	//CD_SUB_NBR : HPP Bahan Baku

		$query_typ	= "SELECT GL_TYP_NBR, GL_TYP, GL_DESC, CD_SUB_NBR FROM RTL.ACCTG_GL_TYP WHERE GL_TYP_NBR = ".$GLTypeNumber." ";
		$result_typ	= mysql_query($query_typ);
		$row_typ	= mysql_fetch_array($result_typ);


		$GLTypeNbr	= $row_typ['GL_TYP_NBR'];
		$GLType		= $row_typ['GL_TYP'];
		$GLDesc		= $row_typ['GL_DESC'];
		$CdSubNbr	= $row_typ['CD_SUB_NBR'];
		
		$query		= "SELECT GL_NBR FROM RTL.ACCTG_GL_HEAD WHERE REF='".$GLType."/".$row_bk['END_DT']."' AND GL_TYP_NBR=".$row_typ['GL_TYP_NBR']." AND ACTG_TYP = ".$Actg." AND DEL_NBR = 0";
		
		//echo $query."<br />";
		
		$result		= mysql_query($query);
		$row		= mysql_fetch_array($result);
		$glNumber	= $row['GL_NBR'];
			
		if(empty($row)) {
		
		$query       = "SELECT COALESCE(MAX(GL_NBR),0)+1 AS NEW_NBR FROM RTL.ACCTG_GL_HEAD WHERE DEL_NBR = 0";
		$result      = mysql_query($query);
		$row         = mysql_fetch_array($result);

		$glNumber = $row['NEW_NBR'];

		$query       = "INSERT INTO RTL.ACCTG_GL_HEAD(GL_NBR, BK_NBR, GL_DTE,  GL_DESC, REF, CRT_TS, CRT_NBR, GL_TYP_NBR, ACTG_TYP)
			VALUES (" . $glNumber . ", '" . $bookNumber . "', '" . $row_bk['END_DT'] . "', '".$GLDesc." Journal (System)', '".$GLType."/" . $row_bk['END_DT'] . "', CURRENT_TIMESTAMP, " . $_GET['personNBR'] . ", ".$GLTypeNbr.", ".$Actg.")";
		mysql_query($query);

		//echo $query."<br />";
		
		$query     = "SELECT COALESCE(MAX(GL_DET_NBR),0) AS NEW_NBR FROM RTL.ACCTG_GL_DET";
		$result    = mysql_query($query);
		$row       = mysql_fetch_array($result);

		$glDetailNumber = $row['NEW_NBR'];
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbr.", " . $HPP_Sub . ", 0, " . $_GET['personNBR'] . ")";
		mysql_query($query);
		
		//echo $query."<br />";
		
		$glDetailNumber++;
		$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
			VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubBalance.", 0, " . $HPP_Sub . ", " . $_GET['personNBR'] . ")";
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
				VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubNbr.", " . $HPP_Sub . ", 0, " . $_GET['personNBR'] . ")";
			mysql_query($query);
			
			//echo $query."<br />";
			
			$glDetailNumber++;
			$query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, CD_SUB_NBR, DEB, CRT, UPD_NBR)
				VALUES (" . $glDetailNumber . ", " . $glNumber . ", ".$CdSubBalance.", 0, " . $HPP_Sub . ", " . $_GET['personNBR'] . ")";
			mysql_query($query);
			
			//echo $query."<br />";
			
			
		}
	}



unset($_GET['ACTG']);
}

echo "Data berhasil diposting.";