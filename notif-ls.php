<?php
include "framework/database/connect.php";
include "framework/functions/default.php";

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
			NT.NTFY_NBR LIKE '" . $query . "'
			OR NT.NTFY_TTL LIKE '" . $query . "'
			OR NT.NTFY_DESC LIKE '" . $query . "'
			OR NT.NTFY_TYP LIKE '" . $query . "'
			OR NT.BEG_DT LIKE '" . $query . "'
			OR NT.END_DT LIKE '" . $query . "'
		)";
	}
}

$whereClauses[] = "NT.DEL_NBR=0";

$whereClauses = implode(" AND ", $whereClauses);

$query = "SELECT NT.NTFY_NBR, NT.NTFY_TTL, NT.NTFY_DESC, NT.NTFY_TYP, NT.BEG_DT, NT.END_DT
	FROM CMP.NTFY NT
	WHERE " . $whereClauses . " ORDER BY NT.END_DT ASC";
$result=mysql_query($query);

if(mysql_num_rows($result)==0) {
    echo "<div class='searchStatus'>Data atau nomor belum ada didalam kumpulan data</div>";
    exit;
}
?>

<table id="mainTable" class="tablesorter table-freeze std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable sortable-onload-show">
	<thead>
		<tr>
			<th class="sortable" style="text-align:center;">Id</th>
			<th class="sortable">Tipe</th>
			<th class="sortable">Judul</th>
			<th class="sortable">Deskripsi</th>		
			<th class="sortable">Mulai</th>
			<th class="sortable">Selesai</th>
		</tr>
	</thead>
	<tbody>
	<?php while ($row = mysql_fetch_array($result)) { ?>
		<tr style="cursor:pointer;" onclick="location.href='notif-edit.php?NTFY_NBR=<?php echo $row['NTFY_NBR'];?>';">
			<td style="text-align:center;"><?php echo $row['NTFY_NBR'];?></td>
			<td style="text-align:left;"><?php echo $row['NTFY_TYP'];?></td>
			<td style="text-align:left;"><?php echo $row['NTFY_TTL'];?></td>
			<td style="text-align:left;"><?php echo $row['NTFY_DESC'];?></td>	
			<td style="text-align:center;"><?php echo $row['BEG_DT'];?></td>
			<td style="text-align:center;"><?php echo $row['END_DT'];?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>