<?php

require_once __DIR__ . "/../framework/database/connect.php";
require_once __DIR__ . "/../framework/functions/default.php";
require_once __DIR__ . "/../framework/pagination/pagination.php";

$val 	= $_GET['CO_NBR'];

$searchQuery    = strtoupper($_REQUEST['s']);
// echo $searchQuery;

$orders         = (array) $_GET['ORD_BY'];

$whereClauses   = array("ORD.DEL_F = 0");
$orderClauses   = array();
$Accounting		= $_GET['ACTG'];

if (!empty($val)) 
{
	$whereClauses[] = "ORD.SHP_CO_NBR=".$val;
}

if ($_GET['END_DT'] != "") 
{
	$whereClauses[] = "DATE(ORD.ORD_DTE) <= '" . $_GET['END_DT'] . "'";
}

if ($_GET['ORD_NBR'] != "") 
{
	$whereClauses[] 	= "ORD.ORD_NBR=" . $_GET['ORD_NBR'];
}

	$whereClauses[] 	= "ORD.IVC_TYP = 'RC' ";

if ($Accounting == 0) 
{
	$whereClauses[] 	= "(ORD.IVC_TYP = 'RC')";
}

if ($Accounting == 1) 
{
	$whereClauses[] 	= "ORD.TAX_APL_ID IN ('I', 'A')";
}

if ($Accounting == 2) 
{
	$whereClauses[] 	= "((ORD.TAX_APL_ID NOT IN ('I', 'A') AND SHP.TAX_F = 1) OR (ORD.TAX_APL_ID NOT IN ('I', 'A') AND RCV.TAX_F = 1))";	
}

if ($Accounting == 3) 
{
	$whereClauses[] 	= "((ORD.TAX_APL_ID NOT IN ('I', 'A') AND SHP.TAX_F = 0 ) OR (ORD.TAX_APL_ID NOT IN ('I', 'A') AND RCV.TAX_F = 0))";
}


foreach ($orders as $field => $mode) 
{
	if (is_int($field))
	{
		$field = $mode;
		$mode  = "ASC";
	}

	switch (strtoupper($field)) 
	{
		case "UPD_TS":
			$order = "ORD.UPD_TS";
			break;
		default:
			$order = "ORD.ORD_NBR";
			break;
	}

	$orderClauses[] = $order . " " . $mode;
}

if ($searchQuery != "") 
{
	$searchQuery = explode(" ", $searchQuery);

	foreach ($searchQuery as $query) 
	{
		$query = mysql_real_escape_string(trim($query));

		if (empty($query)) 
		{
			continue;
		}

		if (strrpos($query, '%') === false) 
		{
			$query = '%' . $query . '%';
		}

		$whereClauses[] = "(
			ORD.ORD_NBR LIKE '" . $query . "'
			OR CONCAT(SHP.NAME , ' - ' , COALESCE(SHP.CO_ID,'') , ' - ' , SHP.ADDRESS , ' ' , CIT.CITY_NM) LIKE '" . $query . "'
			OR CONCAT(RCV.NAME , ' - ' , COALESCE(RCV.CO_ID,'') , ' - ' , RCV.ADDRESS , ' ' , CITS.CITY_NM) LIKE '" . $query . "'
		)";
	}
}

$whereClauses = implode(" AND ", $whereClauses);
$orderClauses = implode(", ", $orderClauses);

if ($orderClauses == "") 
{
	$orderClauses = "ORD.ORD_NBR ASC";
}

$query = "SELECT ORD.ORD_NBR,
				 ORD.ORD_DTE,
				 ORD.RCV_CO_NBR,
				 ORD.SHP_CO_NBR,
				 CONCAT(SHP.NAME , ' - ' , COALESCE(SHP.CO_ID,'') , ' - ' , SHP.ADDRESS , ' ' , CIT.CITY_NM) AS SHP_NAME,
				 CONCAT(RCV.NAME , ' - ' , COALESCE(RCV.CO_ID,'') , ' - ' , RCV.ADDRESS , ' ' , CITS.CITY_NM) AS RCV_NAME,
				 ORD.TOT_AMT,
				 ORD.TOT_REM,
				 SHP.TAX_F,
				 ORD.TAX_APL_ID,
				 SUB.CAT_SUB_DESC,
				 SUM(TOT_AMT) AS TOT_AMT,
				 SUM(PYMT_DOWN) AS PYMT_DOWN,
				 SUM(PYMT_REM) AS PYMT_REM
		  FROM RTL.RTL_STK_HEAD ORD
		  LEFT JOIN CMP.COMPANY SHP ON ORD.SHP_CO_NBR  = SHP.CO_NBR
		  LEFT JOIN RTL.CAT_SUB SUB ON SUB.CAT_SUB_NBR = ORD.CAT_SUB_NBR
		  LEFT JOIN CMP.COMPANY RCV ON ORD.RCV_CO_NBR  = RCV.CO_NBR
		  LEFT JOIN CMP.CITY CIT ON CIT.CITY_ID 	   = SHP.CITY_ID
		  LEFT JOIN CMP.CITY CITS ON CITS.CITY_ID 	   = RCV.CITY_ID
		  WHERE  " . $whereClauses . "
		  GROUP BY ORD.ORD_NBR
		  ORDER BY " . $orderClauses; 

 // echo "<pre>".$query;

$pagination = pagination($query, 100);

$results = array(
	'parameter' 	=> $_GET,
	'query'			=> $query,
	'data' 			=> array(),
	'pagination' 	=> $pagination
);

$result = mysql_query($pagination['query']);

while($row = mysql_fetch_array($result)) 
{
	$results['data'][] = $row;
	$results['total']['TOT_AMT']   += $row['TOT_AMT']; 
	$results['total']['PYMT_DOWN'] += $row['PYMT_DOWN'];
	$results['total']['PYMT_REM']  += $row['PYMT_REM'];
}

echo json_encode($results);

?>