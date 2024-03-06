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
			LST.PRSN_NBR LIKE '" . $query . "'
			OR NAME LIKE '" . $query . "'
			OR POS_DESC LIKE '" . $query . "'
		)";
	}
}

$whereClauses[] = "LST.DEL_NBR=0";

$whereClauses = implode(" AND ", $whereClauses);

$query = "SELECT LST.PRSN_NBR, NAME, POS_DESC, PYMT_DTE, CNBTN_VAL, CNBTN_PRC, SUM(COALESCE(CNBTN_PNT,0)) AS CNBTN_PNT
	FROM PAY.CNBTN_LST LST
	INNER JOIN CMP.PEOPLE PPL ON LST.PRSN_NBR = PPL.PRSN_NBR
	INNER JOIN CMP.POS_TYP TYP ON PPL.POS_TYP = TYP.POS_TYP
	WHERE " . $whereClauses . " 
	GROUP BY LST.PRSN_NBR
	ORDER BY 2 DESC";
$result = mysql_query($query);

if (mysql_num_rows($result) == 0) {
    echo "<div class='searchStatus'>Data or Number Not Found</div>";
    exit;
}
?>
<table id="mainTable" class="tablesorter table-freeze std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable sortable-onload-show">
	<thead>
		<tr>
			<th class="sortable" style="text-align:center;">No</th>
			<th class="sortable">Nama</th>
			<th class="sortable">Jabatan</th>
			<!--
			<th class="sortable">Gaji Kontribusi</th>
			<th class="sortable">Harga Reksa Dana</th>
			-->
			<th class="sortable">Total Unit</th>
		</tr>
	</thead>
	<tbody>
	<?php while ($row = mysql_fetch_array($result)) { ?>
		<tr style="cursor:pointer;" onclick="location.href='contribution-list.php?PRSN_NBR=<?php echo $row['PRSN_NBR'];?>';">
			<td style="text-align:center;"><?php echo $row['PRSN_NBR'];?></td>
			<td><?php echo $row['NAME'];?></td>
			<td><?php echo $row['POS_DESC'];?></td>
			<!--
			<td style="text-align:right;"><?php echo number_format($row['CNBTN_VAL'],0,",",".");?></td>
			<td style="text-align:right;"><?php echo number_format($row['CNBTN_PRC'],2,",",".");?></td>
			-->
			<td style="text-align:right;"><?php echo $row['CNBTN_PNT'];?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>