<?php
require_once "../../framework/database/connect.php";
@ini_set('max_execution_time', -1);

$bookNumber		= $_GET['BK_NBR'];

$query		= "UPDATE RTL.ACCTG_BK SET ACT_F = 0 WHERE BK_NBR = ".$bookNumber." ";$result		= mysql_query($query);$beginDate	= $_GET['BK_BEGIN'];
$query		= "SELECT BK_NBR FROM RTL.ACCTG_BK WHERE MONTH(BEG_DTE) = MONTH('".$beginDate."' + INTERVAL 1 MONTH)				AND YEAR(BEG_DTE) = YEAR('".$beginDate."' + INTERVAL 1 MONTH)				";
$result		= mysql_query($query);$row		= mysql_fetch_array($result);
if(empty($row)) {
	$query	= "INSERT INTO RTL.ACCTG_BK (BK_NBR, BEG_DTE, END_DTE, ACT_F, CRT_TS, CRT_NBR) 
				VALUES (" . ($bookNumber+1) . ", (SELECT '".$beginDate."' + INTERVAL 1 MONTH), (SELECT DATE_FORMAT(LAST_DAY('".$beginDate."' + INTERVAL 1 MONTH),'%Y-%m-%d')), 1, CURRENT_TIMESTAMP, " . $_SESSION['personNBR'] . ") ";
	$result = mysql_query($query);
}

header('Location:../../accounting-close-book.php');

?>