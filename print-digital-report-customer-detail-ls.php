<?php
require_once "framework/database/connect.php";

$searchQuery    = strtoupper($_REQUEST['s']);
$whereClauses   = array("HED.DEL_NBR = 0");

if ($_GET['ORD_STT_ID'] != '') {
	$whereClauses[]	= "HED.ORD_STT_ID = '".$_GET['ORD_STT_ID']."' ";
}

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
			OR COM.NAME LIKE '" . $query . "'
			OR PPL.NAME LIKE '" . $query . "'
			OR HED.ORD_TTL LIKE '" . $query . "'
		)";
	}
}

$whereClauses[] = "HED.BUY_CO_NBR = '". $_GET['BUY_CO_NBR'] ."' ";

$whereClauses = implode(" AND ", $whereClauses);

$query = " SELECT 
	HED.ORD_NBR,
	DATE(HED.ORD_TS) AS ORD_DTE,
	DATE(HED.ORD_TS) AS CSH_DTE,
	COALESCE(HED.ORD_TTL, 'Cetakan') AS ORD_TTL,
	HED.ORD_STT_ID,
	STT.ORD_STT_DESC,
	COALESCE(SUM(HED.TOT_AMT), 0) AS TOT_AMT,
	COALESCE(SUM(HED.TOT_REM), 0) AS TOT_REM,
	HED.BUY_CO_NBR,
	(CASE 
		WHEN HED.BUY_CO_NBR != '' THEN COM.NAME 
		WHEN HED.BUY_PRSN_NBR != '' THEN PPL.NAME
		ELSE 'Tunai' END 
	) AS BUY_NAME
FROM CMP.PRN_DIG_ORD_HEAD HED
	INNER JOIN CMP.PRN_DIG_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID 
	LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR = COM.CO_NBR
	LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR = PPL.PRSN_NBR
WHERE " . $whereClauses . " 
GROUP BY HED.ORD_NBR
ORDER BY HED.ORD_NBR DESC";
$result = mysql_query($query);
//echo "<pre>".$query;
if (mysql_num_rows($result) == 0) {
    echo "<div class='searchStatus'>Data atau nomor yang dicari tidak ada didalam kumpulan data</div>";
    exit;
}
?>
<table id="mainTable" class="tablesorter table-freeze std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable sortable-onload-show">
	<thead>
		<tr style="text-align:center">
			<th class='sortable'>Nomor Nota</th>
			<th class='sortable'>Tgl Nota</th>
			<th class='sortable'>Tgl Selesai</th>
			<th class='sortable'>Status</th>
			<th class='sortable'>Customer</th>
			<th class='sortable'>Judul Nota</th>
			<th class='sortable'>Total Nota</th>	
			<th class='sortable'>Sisa</th>	
		</tr>
	</thead>
	<tbody>
	<?php while ($row = mysql_fetch_array($result)) { ?>
		<tr style="cursor:pointer;" onclick="location.href='print-digital-edit.php?ORD_NBR=<?php echo $row['ORD_NBR'];?>';">
			<td><?php echo $row['ORD_NBR'];?></td>
			<td style="text-align:center;"><?php echo $row['ORD_DTE'];?></td>
			<td style="text-align:center;"><?php echo $row['CSH_DTE'];?></td>
			<td><?php echo $row['ORD_STT_DESC'];?></td>
			<td style="text-align:left;"><?php echo $row['BUY_NAME'];?></td>
			<td style="text-align:left;"><?php echo $row['ORD_TTL'];?></td>
			<td style="text-align:right;"><?php echo number_format($row['TOT_AMT'],0,',','.');?></td>
			<td style="text-align:right;"><?php echo number_format($row['TOT_REM'],0,',','.');?></td>
		</tr>
	<?php 
		$subtotal 		+= $row['SUBTOTAL'];
		$totalAmount	+= $row['TOT_AMT'];
		$totalRemain	+= $row['TOT_REM'];
		} 
	?>
	</tbody>
	<tfoot>
		<tr>
			<td class="std" style="font-weight:bold;" colspan="6">Total:</td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($totalAmount, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($totalRemain, 0, ',', '.');?></td>
		</tr>
	</tfoot>
</table>