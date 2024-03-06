<?php
require_once "framework/database/connect.php";

$searchQuery    = strtoupper($_REQUEST['s']);
$whereClauses   = array("TOT_REM > 0", "YEAR(HED.ORD_TS) > 2015");
$group			= $_GET['GROUP'];

if ($searchQuery != "") {
	$searchQuery = explode(" ", $searchQuery);

	foreach ($searchQuery as $query) {
		$query = trim($query);

		if (empty($query)) {
			continue;
		}

		if (strrpos($query, '%') === false) {
			$query = '%' . $query . '%';
		}
		$whereClauses[] = "(
			HED.ORD_NBR LIKE '" . $query . "'
			OR HED.BUY_CO_NBR LIKE '" . $query . "'
			OR COM.NAME LIKE '" . $query . "'
			OR HED.BUY_PRSN_NBR LIKE '" . $query . "'
			OR PPL.NAME LIKE '" . $query . "'
		)";
	}
}

switch (strtoupper($group)) {
	case "CO_NBR":
		$groupClause = "HED.BUY_CO_NBR";
		break;
	case "ORD_NBR":
	default:
		$groupClause = "HED.ORD_NBR";
		break;
}

$whereClauses[] = "HED.DEL_NBR=0";
$whereClauses 	= implode(" AND ", $whereClauses);

$query="SELECT 
	HED.ORD_NBR,
	COUNT(HED.ORD_NBR) AS ORD_NBR_CNT, 
	YEAR(HED.ORD_TS) AS ORD_YEAR,
	MONTH(HED.ORD_TS) AS ORD_MONTH,
	HED.BUY_CO_NBR,
	COM.NAME AS NAME_CO,
	HED.BUY_PRSN_NBR,
	PPL.NAME AS NAME_PPL,
	SUM(COALESCE(TOT_AMT,0)) AS TOT_AMT,
	SUM(COALESCE(PAY.TND_AMT,0)) AS PYMT_DOWN,
	SUM(COALESCE(TOT_AMT,0)) - SUM(COALESCE(PAY.TND_AMT,0)) AS TOT_REM 
FROM CMP.PRN_DIG_ORD_HEAD HED 
	INNER JOIN CMP.PRN_DIG_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID 
	LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR 
	LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR
	LEFT JOIN (
		SELECT 
			PYMT.ORD_NBR,
			SUM(COALESCE(PYMT.TND_AMT,0)) AS TND_AMT
		FROM CMP.PRN_DIG_ORD_PYMT PYMT
		WHERE PYMT.DEL_NBR = 0
		GROUP BY PYMT.ORD_NBR
	) PAY ON PAY.ORD_NBR = HED.ORD_NBR
WHERE " . $whereClauses . "
GROUP BY HED.BUY_CO_NBR
ORDER BY 11 DESC";

$result = mysql_query($query);
//echo "<pre>".$query."<br><br>";
if (mysql_num_rows($result) == 0) {
    echo "<div class='searchStatus'>Data or Number Not Found</div>";
    exit;
}
?>
<table id="mainTable" class="tablesorter table-freeze std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable sortable-onload-show">
	<thead>
		<tr>
			<th class="sortable" style="text-align:center;">Nota</th>
			<th class="sortable">Nama</th>
			<th class="sortable">Total</th>
			<th class="sortable">Pembayaran</th>
			<th class="sortable">Sisa</th>
		</tr>
	</thead>
	<tbody>
	<?php 
	$alt;
	while ($row = mysql_fetch_array($result)) { 
	?>
	<tr <?php echo $alt; ?> style ="cursor: pointer;" onclick="location.href='payment-receive-edit.php?BUY_CO_NBR=<?php echo $row['BUY_CO_NBR'];?>';">
		<td style="text-align:center;"><?php echo $row['ORD_NBR_CNT'];?></td>
		<td><?php echo $row['NAME_CO'];?></td>
		<td style="text-align:center;"><?php echo $row['TOT_AMT'];?></td>
		<td><?php echo $row['PYMT_DOWN'];?></td>
		<td align="right"><?php echo number_format($row['TOT_REM'],0,'.',',');?></td>
	</tr>
	<?php 
	}
	?>
	</tbody>
</table>