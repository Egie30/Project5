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
			BK.BK_NBR LIKE '" . $query . "'
			OR BK.BEG_DT LIKE '" . $query . "'
			OR BK.END_DT LIKE '" . $query . "'
		)";
	}
}

$whereClauses[] = "BK.DEL_NBR=0";

$whereClauses = implode(" AND ", $whereClauses);

$query = "SELECT BK.BK_NBR, BK.BEG_DTE, BK.END_DTE, BK.ACT_F
	FROM RTL.ACCTG_BK BK
	WHERE " . $whereClauses . " ORDER BY 1 DESC";
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
			<th class="sortable">Mulai</th>
			<th class="sortable">Selesai</th>
			<th class="sortable">Aktif</th>
		</tr>
	</thead>
	<tbody>
	<?php while ($row = mysql_fetch_array($result)) { ?>
		<tr style="cursor:pointer;" onclick="location.href='accounting-book-edit.php?BK_NBR=<?php echo $row['BK_NBR'];?>';">
			<td style="text-align:center;"><?php echo $row['BK_NBR'];?></td>
			<td style="text-align:center;"><?php echo $row['BEG_DTE'];?></td>
			<td style="text-align:center;"><?php echo $row['END_DTE'];?></td>
			<td style="text-align:center;">
				<?php 
					if($row['ACT_F'] == 1) { echo "Aktif"; }
						else { echo "Sudah Tutup Buku";}
				?>
			</td>
		</tr>
	<?php } ?>
	</tbody>
</table>