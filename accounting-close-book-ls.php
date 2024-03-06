<?php
require_once "framework/database/connect.php";

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "ajax/accounting/close-book.php";

	$results = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

if (count($results->data) == 0) {
    echo "<div class='searchStatus'>Data not found</div>";
    exit;
}

?>
<table id="mainTable" class="tablesorter table-freeze std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable sortable-onload-show">
	<thead>
		<tr>
			<th class="sortable" style="width:20px;">No.</th>
			<th class="sortable">Mulai</th>
			<th class="sortable">Selesai</th>
			<th class="sortable">Aktif</th>
			<th class="sortable">Tgl Transfer</th>
			<th class="sortable" style="width:70px;">&nbsp;</th>
			<th class="sortable" style="width:110px;">&nbsp;</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($results->data as $result) { ?>
		<tr>
			<td style="text-align:center;"><?php echo $result->BK_NBR;?></td>
			<td style="text-align:center;"><?php echo $result->BEG_DTE;?></td>
			<td style="text-align:center;"><?php echo $result->END_DTE;?></td>
			<td style="text-align:center;">
				<?php 
					if($result->ACT_F == 1) { echo "Aktif"; }
						else { echo "Sudah Tutup Buku"; }
				?>
			</td>
			<td style="text-align:center;"><?php echo $result->TRF_TS;?></td>
			
			
			<td>
				<?php if($result->ACT_F == 1) { ?>
				<div style='border:none;margin:1px 1px 1px 1px;background:none;color:gray;' id='close-book<?php echo $result->BK_NBR;?>'></div>
				<div style='border:1px #cccccc solid;background-color:#FFFFFF;padding:5px;width:70px;cursor:pointer;border-radius:3px' style="cursor:pointer;" onclick="location.href='ajax/accounting/close-book-process.php?personNBR=<?php echo $_SESSION['personNBR']?>&BK_NBR=<?php echo $result->BK_NBR;?>&BK_BEGIN=<?php echo $result->BEG_DTE;?>&BK_END=<?php echo $result->END_DTE;?>';">Close Book</div>
				<?php } ?>
			</td>
			
			<td style="text-align:left;">
				
				<div style='border:none;margin:1px 1px 1px 1px;background:none;color:gray;' id='transfer<?php echo $result->BK_NBR;?>'></div>
				<div style='border:1px #cccccc solid;background-color:#FFFFFF;padding:5px;width:110px;cursor:pointer;border-radius:3px' style="cursor:pointer;" onclick="location.href='ajax/accounting/close-book-transfer.php?personNBR=<?php echo $_SESSION['personNBR']?>&BK_NBR=<?php echo $result->BK_NBR;?>&BK_BEGIN=<?php echo $result->BEG_DTE;?>&BK_END=<?php echo $result->END_DTE;?>';">Transfer Saldo Awal</div>
				
			</td>
		</tr>
	<?php } ?>
	</tbody>
</table>