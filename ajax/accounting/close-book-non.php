<?php
require_once "../../framework/database/connect.php";
@ini_set('max_execution_time', -1);
//$bookNumber		= $_GET['BK_NBR'];
//$_GET['BK_NBR']	= $bookNumber;

try {
    
    ob_start();
    include __DIR__ . DIRECTORY_SEPARATOR . "balance-report.php";

    $results = json_decode(ob_get_clean());
} catch (\Exception $ex) {
    ob_end_clean();
}

$query 			= "SELECT COALESCE(MAX(TB_NBR),0)+1 AS NEW_NBR FROM ACCTG_TB";
$result 		= mysql_query($query);
$row 			= mysql_fetch_array($result);
$balanceNumber 	= $row['NEW_NBR'];

$query_book 	= "SELECT 
					BK_NBR, 
					BEG_DTE, 
					MONTH(BEG_DTE) AS BK_MONTH, 
					YEAR(BEG_DTE) AS BK_YEAR, 
					END_DTE 
				FROM ACCTG_BK 
				WHERE ACT_F = 1 ORDER BY BEG_DTE ASC LIMIT 1";
$result_book	= mysql_query($query_book);
$row_book		= mysql_fetch_array($result_book);

//$book			= $row_book['BK_NBR']; 

$book			= $_GET['BK_NBR']; 
$year			= $row_book['BK_YEAR'];

$query			= "SELECT 
				BK_NBR 
				FROM ACCTG_BK 
				WHERE ACT_F = 1 AND BEG_DTE = (SELECT (BEG_DTE+INTERVAL 1 MONTH) AS BEG_DTE FROM ACCTG_BK WHERE BK_NBR = ".$book.")";
$result			= mysql_query($query);
$row			= mysql_fetch_array($result);

$bookNumber		= $row['BK_NBR'];

//$bookNumber		= 2;

$RptTypNumber	= 1;

#============ INSERT REPORT "NERACA" =================#

	foreach($results->activa as $key=>$report) {
		foreach($report as $data=>$value) {
			if($value->BALANCE != ''){
				$balanceNumber++;
				
				$CdSubNbr		= $value->CD_SUB_NBR;
				$CdNbr			= $value->CD_NBR;
				$CdCategoryNbr	= $value->CD_CAT_NBR;
				$TotalAmount 	= $value->BALANCE;
							
				#============ QUERY SELECT REPORT NUMBER =================#
				$queryNumber = "SELECT COALESCE(MAX(RPT_NBR),0) + 1 AS NEW_NBR FROM ACCTG_RPT";
				$resultNumber = mysql_query($queryNumber);
				$rowNumber = mysql_fetch_array($resultNumber);

				$reportNumber = $rowNumber['NEW_NBR'];
							
				$query       = "INSERT INTO ACCTG_RPT (RPT_NBR, BK_NBR, RPT_TYP_NBR, CD_SUB_NBR, CD_NBR, CD_CAT_NBR, TOT_AMT, CRT_TS, CRT_NBR) VALUES (" . $reportNumber . ", " . $book . ", ".$RptTypNumber.", " . $CdSubNbr . ", " . $CdNbr . ", " . $CdCategoryNbr . ", " . $TotalAmount . ", CURRENT_TIMESTAMP, " . $_GET['personNBR'] . ")";
				mysql_query($query);	
							
				//INSERT SALDO AWAL
				$query_tb = "INSERT INTO ACCTG_TB(TB_NBR, BK_NBR, CD_SUB_NBR, DEB, CRT, CRT_TS, CRT_NBR)
					VALUES (" . $balanceNumber . ", " . $bookNumber . ", " . $CdSubNbr . ", " . $TotalAmount . ", 0, CURRENT_TIMESTAMP, " . $_GET['personNBR'] . " )";
				$result = mysql_query($query_tb);
				
				//echo $query_tb."<br \>";
			}
		}
	}
			
			
	foreach($results->passiva as $key=>$report) {
		foreach($report as $data=>$value) {
			if($value->BALANCE != ''){
				$balanceNumber++;
					
				$CdSubNbr		= $value->CD_SUB_NBR;
				$CdNbr			= $value->CD_NBR;
				$CdCategoryNbr	= $value->CD_CAT_NBR;
				$TotalAmount 	= $value->BALANCE;
							
				#============ QUERY SELECT REPORT NUMBER =================#

				$queryNumber = "SELECT COALESCE(MAX(RPT_NBR),0) + 1 AS NEW_NBR FROM ACCTG_RPT";
				$resultNumber = mysql_query($queryNumber);
				$rowNumber = mysql_fetch_array($resultNumber);

				$reportNumber = $rowNumber['NEW_NBR'];
							
				$query       = "INSERT INTO ACCTG_RPT (RPT_NBR, BK_NBR, RPT_TYP_NBR, CD_SUB_NBR, CD_NBR, CD_CAT_NBR, TOT_AMT, CRT_TS, CRT_NBR, TAX_F) VALUES (" . $reportNumber . ", " . $book . ", ".$RptTypNumber.", " . $CdSubNbr . ", " . $CdNbr . ", " . $CdCategoryNbr . ", " . $TotalAmount . ", CURRENT_TIMESTAMP, " . $_GET['personNBR'] . ", 0)";
				mysql_query($query);				
							
				//INSERT SALDO AWAL
				$query_tb = "INSERT INTO ACCTG_TB(TB_NBR, BK_NBR, CD_SUB_NBR, DEB, CRT, CRT_TS, CRT_NBR, TAX_F)
						VALUES (" . $balanceNumber . ", " . $bookNumber . ", " . $CdSubNbr . ", 0, " . $TotalAmount . ", CURRENT_TIMESTAMP, " . $_GET['personNBR'] . ", 0)";
				$result = mysql_query($query_tb);
				
				//echo $query_tb."<br \>";
			}
		}
	}

	//INSERT LABA RUGI
	$CdNbr 			= 16; 
	$CdCategoryNbr 	= 3;
	if(date('Y') == $year) { 
		$CdSubNbr = 93;
	} else { 
		$CdSubNbr = 54;
	}
					
	$Profit 		= $results->profit;
	$balanceNumber ++;
					
	#============ QUERY SELECT REPORT NUMBER =================#
	$queryNumber = "SELECT COALESCE(MAX(RPT_NBR),0) + 1 AS NEW_NBR FROM ACCTG_RPT";
	$resultNumber = mysql_query($queryNumber);
	$rowNumber = mysql_fetch_array($resultNumber);

	$reportNumber = $rowNumber['NEW_NBR'];
					
	$query       = "INSERT INTO ACCTG_RPT (RPT_NBR, BK_NBR, RPT_TYP_NBR, CD_SUB_NBR, CD_NBR, CD_CAT_NBR, TOT_AMT, CRT_TS, CRT_NBR) VALUES (" . $reportNumber . ", " . $book . ", ".$RptTypNumber.", " . $CdSubNbr . ", " . $CdNbr . ", " . $CdCategoryNbr . ", " . $Profit . ", CURRENT_TIMESTAMP, " . $_GET['personNBR'] . ")";
	mysql_query($query);
							
	$query_tb = "INSERT INTO ACCTG_TB(TB_NBR, BK_NBR, CD_SUB_NBR, DEB, CRT, CRT_TS, CRT_NBR, TAX_F)
			VALUES (" . $balanceNumber . ", " . $bookNumber . ", " . $CdSubNbr . ", 0, " . $Profit . ", CURRENT_TIMESTAMP, " . $_GET['personNBR'] . ", 0)";
	$result = mysql_query($query_tb);
	
	//echo $query_tb."<br \>";
	
	$query		= "UPDATE ACCTG_BK SET TRF_F = 0 WHERE BK_NBR = ".$_GET['BK_NBR'];
	$result		= mysql_query($query);
	
header('Location:../../accounting-close-book.php');
?>
