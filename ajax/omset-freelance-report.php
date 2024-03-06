<?php

require_once __DIR__ . "/../framework/database/connect.php";
require_once __DIR__ . "/../framework/functions/default.php";
require_once __DIR__ . "/../framework/pagination/pagination.php";

$searchQuery 	= strtoupper($_REQUEST['s']);

$Accounting	   = $_GET['ACTG'];
$companyNumber = $CoNbrDef;
$beginDate 	   = $_GET['BEG_DT'];
$endDate 	   = $_GET['END_DT'];
$days 		   = $_GET['DAYS'];
$months 	   = $_GET['MONTHS'];
$years 		   = $_GET['YEARS'];
$day 		   = $_GET['DAY'];
$month		   = $_GET['MONTH'];
$year		   = $_GET['YEAR'];
$groups 	  = (array) $_GET['GROUP'];

if (empty($_GET['BEG_DT'])) 
{
	$beginDate = date('Y-m-01');
}

if (empty($_GET['END_DT'])) 
{
	$endDate  = date('Y-m-d');
}

$whereClauses = array("OMSET.DEL_NBR = 0", "DATE(OMSET.ORD_DTE) >= (SELECT BEG_ACCTG FROM NST.PARAM_LOC)");
$groupClauses = array();

if ($Accounting == 0) 
{
	$whereClauses[] 	= "OMSET.ORD_NBR != 'NULL'";
}

if ($Accounting == 1) 
{
	$whereClauses[] 	= "OMSET.TAX_APL_ID IN ('I', 'A')";
}

if ($Accounting == 2) 
{
	$whereClauses[] 	= "OMSET.TAX_APL_ID NOT IN ('I', 'A') AND COM.TAX_F = 1";	
}

if ($Accounting == 3) 
{
	$whereClauses[] 	= "OMSET.TAX_APL_ID NOT IN ('I', 'A') AND COM.TAX_F = 0";
}	

if (count($groups) > 0) 
{
	$groupClauses = array();
	
	while(count($groups) > 0) 
	{
		$group = strtoupper(array_shift($groups));
		
		switch ($group) 
		{
			case "YEAR":
				$groupClauses[] = "YEAR(".$field.")";
				break;
			case "MONTH":
				$groupClauses[] = "YEAR(".$field."), MONTH(".$field.")";
				break;
			case "DAY":
				$groupClauses[] = "YEAR(".$field."), MONTH(".$field."), DAY(".$field.")";
				break;
			case "ORD_NBR":
				$groupClauses[] = "OMSET.ORD_NBR";
				break;
			default:
				$groupClauses[] = "OMSET.ORD_NBR";
				break;
		}
	}		

	$groupClause = implode(", ", $groupClauses);
} 
else 
{
	$groupClause = "OMSET.ORD_NBR";
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
			OMSET.ORD_NBR LIKE '" . $query . "' 
			OR (CASE WHEN OMSET.BUY_CO_NBR IS NULL THEN 'Tunai' ELSE COM.NAME END) LIKE '" . $query . "'
			OR OMSET.ORD_TTL LIKE '" . $query . "'
		)";
	}
}

$whereClauses = implode(" AND ", $whereClauses);
$groupClauses = implode(", ", $groupClauses);

$query	= "SELECT OMSET.ORD_NBR,
				  OMSET.ORD_DTE,
				  (CASE WHEN OMSET.BUY_CO_NBR IS NULL THEN 'Tunai' ELSE COM.NAME END) AS NAME,
				  OMSET.ORD_TTL,
				  SUM(COALESCE(OMSET.TOT_SUB,0)) AS REVENUE,
				  SUM(COALESCE(COST.TOT_SUB_INTERNAL,0)) AS COST_INTERNAL,
				  SUM(COALESCE(COST.TOT_SUB_EKSTERNAL,0)) AS COST_EKSTERNAL
		    FROM
		  			(SELECT DATE(HED.ORD_TS) AS ORD_DTE,
			        	HED.ORD_NBR,
			        	DET.ORD_DET_NBR,
			        	HED.ORD_TTL,
			        	HED.BUY_CO_NBR,
			        	HED.TAX_APL_ID,
			        	HED.DEL_NBR,
			        	COALESCE(SUM(CASE WHEN PRN_DIG_EQP = 'ICS' THEN TOT_SUB ELSE 0 END),0) AS TOT_SUB
					 FROM CMP.PRN_DIG_ORD_HEAD HED
					 LEFT OUTER JOIN CMP.PRN_DIG_ORD_DET DET ON HED.ORD_NBR = DET.ORD_NBR
					 LEFT OUTER JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP = TYP.PRN_DIG_TYP
					 LEFT OUTER JOIN 
					 		(SELECT ORD_DET_NBR, 
					 		 SUM(TOT_SUB) AS TOT_SUB_ADD 
					 		 FROM CMP.PRN_DIG_ORD_VAL_ADD 
					 		 GROUP BY ORD_NBR) VAL 
					 		 ON DET.ORD_DET_NBR = VAL.ORD_DET_NBR
							 WHERE (BUY_CO_NBR IS NULL OR BUY_CO_NBR NOT IN (1002,271))
							 AND DATE(ORD_TS) >= '".$beginDate."'
							 AND DATE(ORD_TS) <= '".$endDate."'
							 AND HED.DEL_NBR   = 0
							 AND DET.DEL_NBR   = 0
							 AND TYP.PRN_DIG_EQP = 'ICS'
							 GROUP BY DET.ORD_DET_NBR
					 ) OMSET
			INNER JOIN 
				(SELECT DET.ORD_NBR,
						DET.ORD_DET_NBR_REF, 
						SUM(CASE WHEN HED.CAT_SUB_NBR = 273 THEN DET.TOT_SUB ELSE 0 END) AS TOT_SUB_INTERNAL,
						SUM(CASE WHEN HED.CAT_SUB_NBR != 273 THEN DET.TOT_SUB ELSE 0 END) AS TOT_SUB_EKSTERNAL
				 FROM RTL.RTL_STK_DET DET
				 LEFT JOIN RTL.RTL_STK_HEAD HED
				    ON DET.ORD_NBR = HED.ORD_NBR
				 WHERE HED.DEL_F = 0
				    AND HED.IVC_TYP = 'RC'
				 GROUP BY DET.ORD_DET_NBR
				)COST
			    ON COST.ORD_DET_NBR_REF = OMSET.ORD_DET_NBR
			LEFT JOIN CMP.COMPANY COM ON COM.CO_NBR = OMSET.BUY_CO_NBR
			WHERE  " . $whereClauses . "
			GROUP BY OMSET.ORD_NBR
			ORDER BY OMSET.ORD_NBR
			";
// echo "<pre>".$query;

$pagination = pagination($query, 1000);

$results = array(
	'parameter' 	=> $_GET,
	'query'			=> $query,
	'data' 			=> array(),
	'pagination' 	=> $pagination
);

$result = mysql_query($pagination['query']);

while($row = mysql_fetch_array($result)) 
{
	//print_r($row);
	//echo "<br />";
	$results['data'][] = $row;
	$results['total']['REVENUE'] 		+= $row['REVENUE'];
	$results['total']['COST_INTERNAL'] 	+= $row['COST_INTERNAL'];
	$results['total']['COST_EKSTERNAL'] += $row['COST_EKSTERNAL'];
}

echo json_encode($results);

?>
