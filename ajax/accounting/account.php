<?php
require_once __DIR__ . "/../../framework/database/connect.php";
require_once __DIR__ . "/../../framework/functions/default.php";
require_once __DIR__ . "/../../framework/pagination/pagination.php";

$searchQuery    = strtoupper($_REQUEST['s']);
$groups         = (array) $_GET['GROUP'];
$orders         = (array) $_GET['ORD_BY'];
$whereClauses   = array("SUB.DEL_NBR=0");
$tbWhere		= array("TB.DEL_NBR = 0");
$plusMode 		= $_GET['PLUS'];
$Actg			= $_GET['ACTG'];

if(!empty($_GET['BK_NBR'])) {
$tbWhere[] 		= "TB.BK_NBR= " . $_GET['BK_NBR'];
}

if($Actg != 0)  {
	$tbWhere[]		= "TB.ACTG_TYP = ".$Actg." ";
}

if ($_GET['CD_NBR'] != "") {
	if (is_array($_GET['CD_NBR'])) {
		$whereClauses[] = "ACC.CD_NBR IN (" . implode(", ", $_GET['CD_NBR']) . ")";
	} else {
		$whereClauses[] = "ACC.CD_NBR='" . $_GET['CD_NBR'] . "'";
	}
}

if ($_GET['CD_CAT_NBR'] != "") {
	if (is_array($_GET['CD_CAT_NBR'])) {
		$whereClauses[] = "CAT.CD_CAT_NBR IN (" . implode(", ", $_GET['CD_CAT_NBR']) . ")";
	} else {
		$whereClauses[] = "CAT.CD_CAT_NBR='" . $_GET['CD_CAT_NBR'] . "'";
	}
}


if ($_GET['CD_ACC_NBR'] != "") {
	if (is_array($_GET['CD_ACC_NBR'])) {
		$whereClauses[] = "ACC.CD_ACC_NBR IN (" . implode(", ", $_GET['CD_ACC_NBR']) . ")";
	} else {
		$whereClauses[] = "ACC.CD_ACC_NBR='" . $_GET['CD_ACC_NBR'] . "'";
	}
}

if ($_GET['CD_ACC_NBR'] != "") {
	if (is_array($_GET['CD_NBR'])) {
		$whereClauses[] = "ACC.CD_NBR IN (" . implode(", ", $_GET['CD_NBR']) . ")";
	} else {
		$whereClauses[] = "ACC.CD_NBR='" . $_GET['CD_NBR'] . "'";
	}
}

if ($_GET['CD_SUB_ACC_NBR'] != "") {
	if (is_array($_GET['CD_SUB_ACC_NBR'])) {
		$whereClauses[] = "SUB.CD_SUB_ACC_NBR IN (" . implode(", ", $_GET['CD_SUB_ACC_NBR']) . ")";
	} else {
		$whereClauses[] = "SUB.CD_SUB_ACC_NBR='" . $_GET['CD_SUB_ACC_NBR'] . "'";
	}
}

if ($_GET['CD_SUB_NBR'] != "") {
	if (is_array($_GET['CD_SUB_NBR'])) {
		$whereClauses[] = "SUB.CD_SUB_NBR IN (" . implode(", ", $_GET['CD_SUB_NBR']) . ")";
	} else {
		$whereClauses[] = "SUB.CD_SUB_NBR='" . $_GET['CD_SUB_NBR'] . "'";
	}
}

if ($searchQuery != "") {
	$searchQuery = explode(" ", $searchQuery);

	foreach ($searchQuery as $query) {
		$query = mysql_real_escape_string(trim($query));

		if (empty($query)) {
			continue;
		}

		if (strrpos($query, '%') === false) {
			$query = '%' . $query . '%';
		}

		$whereClauses[] = "(
			ACC.CD_ACC_NBR LIKE '" . $query . "'
			OR ACC.CD_DESC LIKE '" . $query . "'
			OR SUB.CD_SUB_ACC_NBR LIKE '" . $query . "'
			OR SUB.CD_SUB_DESC LIKE '" . $query . "'
			OR CAT.CD_CAT_NBR LIKE '" . $query . "'
			OR CAT.CD_CAT_DESC LIKE '" . $query . "'
		)";
	}
}


$tbWhere		= implode(" AND ", $tbWhere);
$whereClauses 	= implode(" AND ", $whereClauses);

if (count($groups) > 0) {
	$groupClauses = array();
	
	while(count($groups) > 0) {
		$group = strtoupper(array_shift($groups));
		
		switch ($group) {
			case "ACC_NBR":
				$groupClauses[] = "ACC.CD_ACC_NBR, SUB.CD_SUB_ACC_NBR";
				break;
			case "CD_NBR":
				$groupClauses[] = "ACC.CD_NBR";
				break;
			case "CD_ACC_NBR":
				$groupClauses[] = "ACC.CD_ACC_NBR";
				break;
			case "CD_SUB_ACC_NBR":
				$groupClauses[] = "ACC.CD_SUB_ACC_NBR";
				break;
			case "CD_CAT_NBR":
				$groupClauses[] = "CAT.CD_CAT_NBR";
				break;
			default:
				$groupClauses[] = "SUB.CD_SUB_NBR";
				break;
		}
	}
		
	$groupClause = implode(", ", $groupClauses);
} else {
	$groupClause = "ACC.CD_NBR, SUB.CD_SUB_NBR";
}

$orderClauses = array();


foreach ($orders as $field => $mode) {
	if (is_int($field)) {
		$field = $mode;
		$mode = "ASC";
	}

	switch (strtoupper($field)) {
		case "ACC_NBR":
			$order = "ACC.CD_ACC_NBR, SUB.CD_SUB_ACC_NBR";
			break;
		case "CD_NBR":
			$order = "ACC.CD_NBR";
			break;
		case "CD_ACC_NBR":
			$order = "ACC.CD_ACC_NBR";
			break;
		case "CD_SUB_ACC_NBR":
			$order = "SUB.CD_SUB_ACC_NBR";
			break;
		case "CD_SUB_NBR":
			$order = "SUB.CD_SUB_NBR";
			break;
		case "CD_CAT_NBR":
			$order = "CAT.CD_CAT_NBR";
			break;
		default:
			$order = "ACC.CD_NBR, SUB.CD_SUB_NBR";
			break;
	}

	$orderClauses[] = $order . " " . $mode;
}

$orderClauses = implode(", ", $orderClauses);

if (empty($orderClauses)) {
	$orderClauses = $groupClause;
}

$query = "SELECT SUB.CD_SUB_NBR,
       SUB.CD_SUB_ACC_NBR,
       SUB.CD_SUB_DESC,
       ACC.CD_NBR,
       ACC.CD_ACC_NBR,
       ACC.CD_DESC,
       CAT.CD_CAT_NBR,
       CAT.CD_CAT_DESC,
       CONCAT(CAT.CD_CAT_NBR, '-', ACC.CD_ACC_NBR, SUB.CD_SUB_ACC_NBR) AS ACC_NBR,
       CONCAT(CAT.CD_CAT_DESC, ' - ', ACC.CD_DESC, ' :: ', SUB.CD_SUB_DESC) AS ACC_DESC,
       CONCAT(ACC.CD_ACC_NBR, '-', SUB.CD_SUB_ACC_NBR) AS ACC_SHORT_NBR,
       CONCAT(ACC.CD_DESC, ' :: ', SUB.CD_SUB_DESC) AS ACC_SHORT_DESC,
       COALESCE(BALANCE.DEB, 0) AS DEB,
       COALESCE(BALANCE.CRT, 0) AS CRT
FROM RTL.ACCTG_CD_SUB SUB
LEFT OUTER JOIN RTL.ACCTG_CD ACC ON ACC.CD_NBR=SUB.CD_NBR
LEFT OUTER JOIN RTL.ACCTG_CD_CAT CAT ON CAT.CD_CAT_NBR=ACC.CD_CAT_NBR
LEFT OUTER JOIN
  (SELECT 
		TB.TB_NBR,
		TB.CD_SUB_NBR,
		SUM(TB.DEB) AS DEB,
		SUM(TB.CRT) AS CRT
   FROM RTL.ACCTG_TB TB WHERE ".$tbWhere."
   GROUP BY TB.CD_SUB_NBR) BALANCE ON BALANCE.CD_SUB_NBR=SUB.CD_SUB_NBR
WHERE " . $whereClauses . "
  GROUP BY " . $groupClause . "
ORDER BY " . $orderClauses." ";

//echo "<pre>".$query;

$pagination = pagination($query, 1000);

$results = array(
	'parameter' => $_GET,
	'data' => array(),
	'pagination' => $pagination,
	'total' => array()
);
$result = mysql_query($pagination['query']);

while($row = mysql_fetch_assoc($result)) {
	$results['data'][] = $row;
	$results['total']['BALANCE_DEB'] += $row['DEB'];
	$results['total']['BALANCE_CRT'] += $row['CRT'];
}

echo json_encode($results);