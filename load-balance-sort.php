<?php
include "framework/database/connect.php";

ini_set('max_execution_time', 0);

$arrayOrderNbr	= array();

$_GET['ACTG']		= 0;
$_GET['GROUP'] 		= 'ORD_NBR';

$_GET['PAID_DT']	= date('Y-m-d',strtotime("-2 days"));

//$_GET['PAID_DT']	= '2017-01-01';

//echo $_GET['PAID_DT'];

try {
		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "ajax/prn-dig-report-full.php";

		$resultsPrintFull = json_decode(ob_get_clean());
	} catch (\Exception $ex) {
		ob_end_clean();
	}
	
	
echo "<pre>"; 
print_r($resultsPrintFull->data);

//echo $OrdNumberPlus;


foreach($resultsPrintFull->data as $data) {
	
	$OrdNumber		= $data->ORD_NBR;
	$PaidDate		= $data->MAX_CRT_TS;
		
	$query_upd	= "UPDATE CMP.PRN_DIG_ORD_HEAD SET PAID_DT = '".$PaidDate."' WHERE ORD_NBR = ".$OrdNumber." AND (ORD_NBR_PLUS IS NULL OR ORD_NBR_PLUS = 0)";
	
	//$query_upd	= "UPDATE CMP.PRN_DIG_ORD_HEAD SET PAID_DT = '".$PaidDate."' WHERE ORD_NBR = ".$OrdNumber." ";
	
	$result_upd	= mysql_query($query_upd);
	
	echo $query_upd."<br /><br />";
	
	/*
	$query_upd	= "UPDATE CMP.PRN_DIG_ORD_DET SET PAID_DT = '".$PaidDate."' WHERE ORD_NBR = ".$OrdNumber." AND (ORD_NBR_PLUS IS NULL OR ORD_NBR_PLUS = 0)";
	$result_upd	= mysql_query($query_upd);
	
	//echo $query_upd."<br />";
			
	$query_upd	= "UPDATE CMP.PRN_DIG_ORD_PYMT SET PAID_DT = '".$PaidDate."' WHERE ORD_NBR = ".$OrdNumber." AND (ORD_NBR_PLUS IS NULL OR ORD_NBR_PLUS = 0)";
	$result_upd	= mysql_query($query_upd);
	*/
}

unset($_GET['ACTG']);

$_GET['ACTG'] 		= 2;
$_GET['GROUP'] 		= 'ORD_NBR';

$query	= "SELECT COALESCE(MAX(ORD_NBR_PLUS),0) AS ORD_NBR_PLUS FROM CMP.PRN_DIG_ORD_HEAD";
$result	= mysql_query($query);
$row 	= mysql_fetch_array($result);

$OrdNumberPlus	= $row['ORD_NBR_PLUS'];

try {
		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "ajax/prn-dig-report-full.php";

		$resultsPrintFull = json_decode(ob_get_clean());
	} catch (\Exception $ex) {
		ob_end_clean();
	}
	
	
echo "<pre>"; 
//print_r($resultsPrintFull->data);

//echo $OrdNumberPlus;

foreach($resultsPrintFull->data as $data) {
	
	$OrdNumber		= $data->ORD_NBR;
	$PaidDate		= $data->MAX_CRT_TS;
	$arrayOrderNbr[]= $data->ORD_NBR;
	
	//echo $query_upd."<br />";
	
	//$OrdNumberPlus++;
	
}

//print_r($arrayOrderNbr);

$OrderNbr = implode(", ", $arrayOrderNbr);


$query	= "UPDATE CMP.PRN_DIG_ORD_HEAD HED SET ACTG_TYP = 2 WHERE HED.ACTG_TYP IS NULL AND ORD_NBR IN (".$OrderNbr.")";
$result	= mysql_query($query);

echo $query."<br /><br />";

$query	= "UPDATE CMP.PRN_DIG_ORD_HEAD HED SET ACTG_TYP = 1 WHERE HED.ACTG_TYP IS NULL AND HED.TAX_APL_ID IN ('I', 'A') AND ORD_NBR NOT IN (".$OrderNbr.") ";
$result	= mysql_query($query);

echo $query."<br /><br />";

$query	= "UPDATE CMP.PRN_DIG_ORD_HEAD HED SET ACTG_TYP = 3 WHERE HED.ACTG_TYP IS NULL AND ORD_NBR NOT IN (".$OrderNbr.") AND HED.TAX_APL_ID NOT IN ('I', 'A')";
$result	= mysql_query($query);

echo $query."<br /><br />";

$query	= "set @count:= ".$OrdNumberPlus." ";
mysql_query($query);

$query = "UPDATE PRN_DIG_ORD_HEAD SET ORD_NBR_PLUS=@count:=@count+1 WHERE ACTG_TYP = 2 AND ORD_NBR_PLUS IS NULL ORDER BY PAID_DT";
$result= mysql_query($query);

echo $query;



?>