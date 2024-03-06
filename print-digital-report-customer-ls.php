<?php
require_once "framework/database/connect.php";

$searchQuery    = strtoupper($_REQUEST['s']);
$whereClauses   = array("HED.BUY_CO_NBR != ''");

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
			OR COM.NAME  LIKE '" . $query . "'
			OR PPL.NAME LIKE '" . $query . "'
		)";
	}
}

$whereClauses[] = "HED.DEL_NBR=0";

$whereClauses = implode(" AND ", $whereClauses);

$query = "SELECT 
	MONTHNAME(HED.ORD_TS) AS CSH_MONTHNAME,
	HED.ORD_NBR,
	COALESCE(HED.ORD_TTL, 'Cetakan') AS ORD_TTL,
	COALESCE(SUM(HED.TOT_AMT), 0) AS TOT_AMT,
	COALESCE(SUM(HED.TOT_REM), 0) AS TOT_REM,
	HED.BUY_CO_NBR,
	COM.NAME AS BUY_NAME
FROM CMP.PRN_DIG_ORD_HEAD HED
	LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR = COM.CO_NBR
WHERE " . $whereClauses . "
GROUP BY HED.BUY_CO_NBR";
/*
$query = "SELECT 
	MONTHNAME(HED.ORD_TS) AS CSH_MONTHNAME,
	HED.ORD_NBR,
	COALESCE(HED.ORD_TTL, 'Cetakan') AS ORD_TTL,
	COALESCE(SUM(HED.TOT_AMT), 0) AS TOT_AMT,
	COALESCE(SUM(HED.TOT_REM), 0) AS TOT_REM,
	HED.BUY_CO_NBR,
	(CASE 
		WHEN HED.BUY_CO_NBR != '' THEN COM.NAME 
		WHEN HED.BUY_PRSN_NBR != '' THEN PPL.NAME
		ELSE 'Tunai' END 
	) AS BUY_NAME
FROM CMP.PRN_DIG_ORD_HEAD HED
	LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR = COM.CO_NBR
	LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR = PPL.PRSN_NBR
WHERE " . $whereClauses . "
GROUP BY HED.BUY_CO_NBR";
*/
$result = mysql_query($query);

if (mysql_num_rows($result) == 0) {
    echo "<div class='searchStatus'>Data atau nomor yang dicari tidak ada didalam kumpulan data</div>";
    exit;
}
?>
<table id="mainTable" class="tablesorter table-freeze std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable sortable-onload-show">
	<thead>
		<tr>
			<th class="sortable" style="text-align:center;">No</th>
			<th class="sortable">Name</th>
			<th class="sortable">Total</th>
			<th class="sortable">Sisa</th>
		</tr>
	</thead>
	<tbody>
	<?php while ($row = mysql_fetch_array($result)) { ?>
		<tr style="cursor:pointer;" onclick="location.href='print-digital-report-customer-detail.php?BUY_CO_NBR=<?php echo $row['BUY_CO_NBR'];?>';">
			<td style="text-align:left;"><?php echo $row['BUY_CO_NBR'];?></td>
			<td style="text-align:left;"><?php echo $row['BUY_NAME'];?></td>
			<td style="text-align:right;"><?php echo number_format($row['TOT_AMT'],0,',','.');?></td>
			<td style="text-align:right;"><?php echo number_format($row['TOT_REM'],0,',','.');?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>