<?php
require_once "../../framework/database/connect.php";
@ini_set('max_execution_time', -1);
$bookNumber		= $_GET['BK_NBR'];
$beginDate		= $_GET['BK_BEGIN'];

$date	= date_create($beginDate);
$year	= date_format($date,"Y");




$query		= "SELECT BK_NBR FROM RTL.ACCTG_BK WHERE MONTH(BEG_DTE) = MONTH('".$beginDate."' + INTERVAL 1 MONTH)
				AND YEAR(BEG_DTE) = YEAR('".$beginDate."' + INTERVAL 1 MONTH)
				";

$result		= mysql_query($query);
$row		= mysql_fetch_array($result);


if(empty($row)) {

	$query	= "INSERT INTO RTL.ACCTG_BK (BK_NBR, BEG_DTE, END_DTE, ACT_F, CRT_TS, CRT_NBR) 

				VALUES (" . ($bookNumber+1) . ", (SELECT '".$beginDate."' + INTERVAL 1 MONTH), (SELECT DATE_FORMAT(LAST_DAY('".$beginDate."' + INTERVAL 1 MONTH),'%Y-%m-%d')), 1, CURRENT_TIMESTAMP, " . $_SESSION['personNBR'] . ") ";

	$result = mysql_query($query);
	
}


$book	= $bookNumber+1;

$query		= "SELECT YEAR(BEG_DTE) AS YEAR_NEW FROM RTL.ACCTG_BK WHERE BK_NBR = ".$book." ";
$result		= mysql_query($query);
$row		= mysql_fetch_array($result);

$year_new	= $row['YEAR_NEW'];



$query_delete	= "DELETE FROM RTL.ACCTG_TB WHERE BK_NBR = ".$book." ";
mysql_query($query_delete);

$query_delete	= "DELETE FROM RTL.ACCTG_RPT WHERE BK_NBR = ".$bookNumber." ";
mysql_query($query_delete);


$RptTypNumber	= 1;

$ArrayActg	= array("1", "2", "3");

foreach($ArrayActg as $Actg) {

$_GET['ACTG'] = $Actg;
	
try {
    
    ob_start();
    include __DIR__ . DIRECTORY_SEPARATOR . "balance-report-akun.php";

    $results = json_decode(ob_get_clean());
} catch (\Exception $ex) {
    ob_end_clean();
}


#============ INSERT REPORT "NERACA" =================#



$query_tb 		= "SELECT COALESCE(MAX(TB_NBR),0)+1 AS NEW_NBR FROM RTL.ACCTG_TB";
$result_tb		= mysql_query($query_tb);
$row_tb			= mysql_fetch_array($result_tb);


$balanceNumber 	= $row_tb['NEW_NBR'];


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
							
				$query       = "INSERT INTO RTL.ACCTG_RPT (RPT_NBR, BK_NBR, RPT_TYP_NBR, CD_SUB_NBR, CD_NBR, CD_CAT_NBR, TOT_AMT, CRT_TS, CRT_NBR) VALUES (" . $reportNumber . ", " . $bookNumber . ", ".$RptTypNumber.", " . $CdSubNbr . ", " . $CdNbr . ", " . $CdCategoryNbr . ", " . $TotalAmount . ", CURRENT_TIMESTAMP, " . $_GET['personNBR'] . ")";
				mysql_query($query);	
							
				//INSERT SALDO AWAL
				$query_tb = "INSERT INTO RTL.ACCTG_TB(TB_NBR, BK_NBR, CD_SUB_NBR, DEB, CRT, CRT_TS, CRT_NBR, ACTG_TYP)
					VALUES (" . $balanceNumber . ", " . $book . ", " . $CdSubNbr . ", " . $TotalAmount . ", 0, CURRENT_TIMESTAMP, " . $_GET['personNBR'] . " , ".$Actg.")";
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
							
				$query       = "INSERT INTO RTL.ACCTG_RPT (RPT_NBR, BK_NBR, RPT_TYP_NBR, CD_SUB_NBR, CD_NBR, CD_CAT_NBR, TOT_AMT, CRT_TS, CRT_NBR, TAX_F) VALUES (" . $reportNumber . ", " . $bookNumber . ", ".$RptTypNumber.", " . $CdSubNbr . ", " . $CdNbr . ", " . $CdCategoryNbr . ", " . $TotalAmount . ", CURRENT_TIMESTAMP, " . $_GET['personNBR'] . ", 0)";
				mysql_query($query);				
							
				//INSERT SALDO AWAL
				$query_tb = "INSERT INTO RTL.ACCTG_TB(TB_NBR, BK_NBR, CD_SUB_NBR, DEB, CRT, CRT_TS, CRT_NBR, TAX_F, ACTG_TYP)
						VALUES (" . $balanceNumber . ", " . $book . ", " . $CdSubNbr . ", 0, " . $TotalAmount . ", CURRENT_TIMESTAMP, " . $_GET['personNBR'] . ", 0, ".$Actg.")";
				$result = mysql_query($query_tb);
				
				//echo $query_tb."<br \>";
			}
		}
	}

	$GLTypeNumber	= 26;	//CD_SUB_NBR : Penjualan

	$query_typ	= "SELECT GL_TYP_NBR, GL_TYP, GL_DESC, CD_SUB_NBR FROM RTL.ACCTG_GL_TYP WHERE GL_TYP_NBR = ".$GLTypeNumber." ";
	$result_typ	= mysql_query($query_typ);
	$row_typ	= mysql_fetch_array($result_typ);

	$GLTypeNbr				= $row_typ['GL_TYP_NBR'];
	$GLType					= $row_typ['GL_TYP'];
	$GLDesc					= $row_typ['GL_DESC'];
	$LabaBerjalan			= $row_typ['CD_SUB_NBR'];

	
	
	$GLTypeNumber	= 27;	//CD_SUB_NBR : Penjualan

	$query_typ	= "SELECT GL_TYP_NBR, GL_TYP, GL_DESC, CD_SUB_NBR FROM RTL.ACCTG_GL_TYP WHERE GL_TYP_NBR = ".$GLTypeNumber." ";
	$result_typ	= mysql_query($query_typ);
	$row_typ	= mysql_fetch_array($result_typ);

	$GLTypeNbr				= $row_typ['GL_TYP_NBR'];
	$GLType					= $row_typ['GL_TYP'];
	$GLDesc					= $row_typ['GL_DESC'];
	$LabaDitahan			= $row_typ['CD_SUB_NBR'];
	
	
	//INSERT LABA RUGI
	//$CdNbr 			= 16; 
	//$CdCategoryNbr 	= 3;
	
	if($year == $year_new) { 
		$CdSubNbr = $LabaBerjalan;
	} else { 
		$CdSubNbr = $LabaDitahan;
	}
					
	$Profit 		= $results->total->PROFIT_LOSS;
	$balanceNumber ++;
					
	#============ QUERY SELECT REPORT NUMBER =================#
	$queryNumber = "SELECT COALESCE(MAX(RPT_NBR),0) + 1 AS NEW_NBR FROM ACCTG_RPT";
	$resultNumber = mysql_query($queryNumber);
	$rowNumber = mysql_fetch_array($resultNumber);

	$reportNumber = $rowNumber['NEW_NBR'];
					
	$query       = "INSERT INTO RTL.ACCTG_RPT (RPT_NBR, BK_NBR, RPT_TYP_NBR, CD_SUB_NBR, CD_NBR, CD_CAT_NBR, TOT_AMT, CRT_TS, CRT_NBR) VALUES (" . $reportNumber . ", " . $bookNumber . ", ".$RptTypNumber.", " . $CdSubNbr . ", " . $CdNbr . ", " . $CdCategoryNbr . ", " . $Profit . ", CURRENT_TIMESTAMP, " . $_GET['personNBR'] . ")";
	//mysql_query($query);
							
	$query_tb = "INSERT INTO RTL.ACCTG_TB(TB_NBR, BK_NBR, CD_SUB_NBR, DEB, CRT, CRT_TS, CRT_NBR, ACTG_TYP)
			VALUES (" . $balanceNumber . ", " . $book . ", " . $CdSubNbr . ", 0, " . $Profit . ", CURRENT_TIMESTAMP, " . $_GET['personNBR'] . ", ".$Actg.")";
	$result = mysql_query($query_tb);
	
	echo $query_tb."<br \>";
	
	$query		= "UPDATE RTL.ACCTG_BK SET TRF_TS = CURRENT_TIMESTAMP WHERE BK_NBR = ".$bookNumber." ";
	$result		= mysql_query($query);
	
//header('Location:../../accounting-close-book.php');

}

?>
