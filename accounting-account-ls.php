<?php
require_once "framework/database/connect.php";

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "ajax/accounting/account.php";

	$results = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

if (count($results->data) == 0) {
    echo "<div class='searchStatus'>Data atau nomor yang dicari tidak ada didalam kumpulan data</div>";
    exit;
}

//print_r($results);
?>
<table id="mainTable" class="tablesorter table-freeze std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable sortable-onload-show">
	<thead>
		<tr>
			<th class="sortable" style="text-align:center;">No.</th>
			<th class="sortable" style="text-align:center;">Kode Rekening</th>
			<th class="sortable">Klasifikasi</th>
			<th class="sortable">Grup</th>
			<th class="sortable">Deskripsi</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($results->data as $result) { ?>
		<tr style="cursor:pointer;" onclick="location.href='accounting-account-edit.php?CD_SUB_NBR=<?php echo $result->CD_SUB_NBR;?>';">
			<td style="text-align:center;"><?php echo $result->CD_SUB_NBR;?></td>
			<td style="text-align:center;"><?php echo $result->ACC_NBR;?></td>
			<td style="text-align:left;"><?php echo $result->CD_CAT_DESC;?></td>
			<td style="text-align:left;"><?php echo $result->CD_DESC;?></td>
			<td style="text-align:left;"><?php echo $result->CD_SUB_DESC;?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>