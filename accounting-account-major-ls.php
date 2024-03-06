
<?php
require_once "framework/database/connect.php";

$searchQuery    = strtoupper($_REQUEST['s']);
$whereClauses   = array();

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
			ACC.CD_ACC_NBR LIKE '" . $query . "'
			OR CAT.CD_CAT_DESC LIKE '" . $query . "'
			OR ACC.CD_DESC LIKE '" . $query . "'
		)";
	}
}

$whereClauses[] = "ACC.DEL_NBR=0";

$whereClauses = implode(" AND ", $whereClauses);

$query = "SELECT ACC.CD_NBR, ACC.CD_ACC_NBR, ACC.CD_DESC, CAT.CD_CAT_NBR, CAT.CD_CAT_DESC,
		CONCAT(CAT.CD_CAT_NBR, '-', ACC.CD_ACC_NBR) AS ACC_NBR,
		CONCAT(CAT.CD_CAT_DESC, ' - ', ACC.CD_DESC) AS ACC_DESC
	FROM RTL.ACCTG_CD ACC
		INNER JOIN RTL.ACCTG_CD_CAT CAT ON CAT.CD_CAT_NBR=ACC.CD_CAT_NBR
	WHERE " . $whereClauses . "
	ORDER BY CAT.CD_CAT_NBR, ACC.CD_NBR ASC";
$result = mysql_query($query);

//echo $query;

if (mysql_num_rows($result) == 0) {
    echo "<div class='searchStatus'>Data atau nomor yang dicari tidak ada didalam kumpulan data</div>";
    exit;
}
?>
<table id="mainTable" class="tablesorter table-freeze std-row-alt sortable-onload-5-6r rowstyle-alt colstyle-alt no-arrow searchTable sortable-onload-show">
	<thead>
		<tr>
			<th class="sortable">Kode</th>
			<th class="sortable">Klasifikasi</th>
			<th class="sortable">Deskripsi</th>
		</tr>
	</thead>
	<tbody>
	<?php while ($row = mysql_fetch_array($result)) { ?>
		<tr style="cursor:pointer;" onclick="location.href='accounting-account-major-edit.php?CD_NBR=<?php echo $row['CD_NBR'];?>';">
			<td style="text-align:left;"><?php echo $row['ACC_NBR'];?></td>
			<td style="text-align:left;"><?php echo $row['CD_CAT_DESC'];?></td>
			<td style="text-align:left;"><?php echo $row['CD_DESC'];?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>