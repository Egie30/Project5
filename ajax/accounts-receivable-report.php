<?php

require_once __DIR__ . "/../framework/database/connect.php";
require_once __DIR__ . "/../framework/functions/default.php";
require_once __DIR__ . "/../framework/pagination/pagination.php";

$val 			= $_GET['CO_NBR'];

$searchQuery 	= strtoupper($_REQUEST['s']);
// echo $searchQuery;

$orders         = (array) $_GET['ORD_BY'];

$whereClauses   = array("ORD.DEL_NBR = 0");
$orderClauses   = array();
$Accounting		= $_GET['ACTG'];

if (!empty($val)) 
{
	$whereClauses[] 	= "ORD.BUY_CO_NBR=".$val;
}

if ($_GET['END_DT'] != "") 
{
	$whereClauses[] 	= "DATE(ORD.ORD_TS) <= '" . $_GET['END_DT'] . "'";
}

if ($_GET['ORD_NBR'] != "") 
{
	$whereClauses[] 	= "ORD.ORD_NBR=" . $_GET['ORD_NBR'];
}

	$whereClauses[] 	= "ORD.ORD_STT_ID = 'CP' ";

if ($Accounting == 0) 
{
	$whereClauses[] 	= "(ORD.ORD_STT_ID = 'CP')";
}

if ($Accounting == 1) 
{
	$whereClauses[] 	= "ORD.ACTG_TYP = 1";
}

if ($Accounting == 2) 
{
	$whereClauses[] 	= "((ORD.ACTG_TYP = 2 AND BUY.TAX_F = 1) OR (ORD.ACTG_TYP = 2  AND PRN.TAX_F = 1))";	
}

if ($Accounting == 3) 
{
	$whereClauses[] 	= "((ORD.ACTG_TYP = 3 AND BUY.TAX_F = 0 ) OR (ORD.ACTG_TYP = 3 AND PRN.TAX_F = 0))";
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
			OR CONCAT(BUY.NAME , ' - ' , COALESCE(BUY.CO_ID,'') , ' - ' , BUY.ADDRESS , ' ' , CIT.CITY_NM) LIKE '" . $query . "'
			OR CONCAT(PRN.NAME , ' - ' , COALESCE(PRN.CO_ID,'') , ' - ' , PRN.ADDRESS , ' ' , CITS.CITY_NM) LIKE '" . $query . "'
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
				 ORD.ORD_TS,
				 ORD.PRN_CO_NBR,
				 ORD.ORD_STT_ID,
				 ORD.BUY_CO_NBR,
				 CONCAT(BUY.NAME , ' - ' , COALESCE(BUY.CO_ID,'') , ' - ' , BUY.ADDRESS , ' ' , CIT.CITY_NM) AS BUY_NAME,
				 CONCAT(PRN.NAME , ' - ' , COALESCE(PRN.CO_ID,'') , ' - ' , PRN.ADDRESS , ' ' , CITS.CITY_NM) AS PRN_NAME,
				 ORD.TOT_AMT AS TOTALNOTA,
 				 PYMT.TND_AMT AS TOTALBAYAR,
				 ORD.TOT_REM AS SISA,
				 ORD.TAX_APL_ID,
				 SUM(ORD.TOT_AMT) AS ALLTOTALNOTA,
				 SUM(PYMT.TND_AMT) AS ALLTOTALBAYAR
		  FROM CMP.PRN_DIG_ORD_HEAD ORD
		  LEFT JOIN (SELECT ORD.ORD_NBR, 
				  			SUM(PYMT.TND_AMT) AS TND_AMT
				  			FROM CMP.PRN_DIG_ORD_PYMT PYMT
				  			LEFT JOIN CMP.PRN_DIG_ORD_HEAD ORD ON ORD.ORD_NBR = PYMT.ORD_NBR
				  			GROUP BY PYMT.ORD_NBR) PYMT ON PYMT.ORD_NBR = ORD.ORD_NBR
		  LEFT JOIN CMP.COMPANY BUY ON ORD.BUY_CO_NBR  = BUY.CO_NBR
		  LEFT JOIN CMP.COMPANY PRN ON ORD.PRN_CO_NBR  = PRN.CO_NBR
		  LEFT JOIN CMP.CITY CIT ON CIT.CITY_ID 	   = BUY.CITY_ID
		  LEFT JOIN CMP.CITY CITS ON CITS.CITY_ID 	   = PRN.CITY_ID
		  WHERE  " . $whereClauses . "
		  GROUP BY ORD.ORD_NBR
		  ORDER BY " . $orderClauses; 

//echo "<pre>".$query;
//exit();
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
	$results['total']['ALLTOTALNOTA']   += $row['ALLTOTALNOTA']; 
	$results['total']['ALLTOTALBAYAR'] 	+= $row['ALLTOTALBAYAR'];
	$results['total']['SISA']  			+= $row['SISA'];
}

echo json_encode($results);

?>