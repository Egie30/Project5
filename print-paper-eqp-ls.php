<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
include "framework/security/default.php";

$security		= getSecurity($_SESSION['userID'], "Accounting");
$searchQuery    = strtoupper($_REQUEST['s']);
$whereClauses   = array("DEL_NBR = 0");

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
			PRN_PPR_EQP LIKE '" . $query . "'
			OR PRN_PPR_EQP_DESC LIKE '" . $query . "'
		)";
	}
}

$whereClauses[] = "DEL_NBR = 0";

$whereClauses = implode(" AND ", $whereClauses);

$query = "SELECT 
	PRN_PPR_EQP,
	PRN_PPR_EQP_DESC,
	PRN_PPR_EQP_COLR,
	PRN_PPR_EQP_PRC,
	PRN_PPR_EQP_OVER,
	PRN_PPR_EQP_PLAT
FROM CMP.PRN_PPR_EQP BS
WHERE " . $whereClauses . " 
GROUP BY PRN_PPR_EQP ORDER BY 1 ASC";
$result = mysql_query($query);

if (mysql_num_rows($result) == 0) {
    echo "<div class='searchStatus'>Data atau nomor yang dicari tidak ada didalam kumpulan data</div>";
    exit;
}

?>
<table id="mainTable" class="tablesorter table-freeze std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable sortable-onload-show">
	<thead>
		<tr>
			<th class="sortable">Kode</th>
			<th class="sortable">Deskripsi</th>
			<th class="sortable">Harga Cetak</th>
			<th class="sortable">Over Cetak</th>
			<th class="sortable">Harga Plat</th>
		</tr>
	</thead>
	<tbody>
	<?php while ($row = mysql_fetch_array($result)) {
		?>
		<tr style="cursor:pointer;" onclick="location.href='print-paper-eqp-edit.php?PRN_PPR_EQP=<?php echo $row['PRN_PPR_EQP'];?>';">
			<td style="text-align:left;"><?php echo $row['PRN_PPR_EQP'];?></td>
			<td style="text-align:left;"><?php echo $row['PRN_PPR_EQP_DESC'];?></td>
			<td style="text-align:left;"><?php echo number_format($row['PRN_PPR_EQP_PRC'],0);?></td>
			<td style="text-align:left;"><?php echo number_format($row['PRN_PPR_EQP_OVER'],0);?></td>
			<td style="text-align:right;"><?php echo number_format($row['PRN_PPR_EQP_PLAT'],0);?></td>
			</tr>
	<?php } ?>
	</tbody>
</table>