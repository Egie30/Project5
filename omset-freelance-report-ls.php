<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

try {
	$_GET["GROUP"]	= "ORD_NBR";
	$_GET["ORD_BY"]	= "ORD_TS";
	
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "ajax/creative-hub-bonus.php";

	$results = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

if (count($results->data) == 0) {
	echo "<div class='searchStatus'>Data yang ingin ditampilkan tidak tersedia.</div>";
	die();
}
?>
<table id="mainTable" class="tablesorter table-freeze std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable sortable-onload-show">
	<thead>
		<tr>
			style="text-align:center">
			<th style="width:10%" class="sortable" style="text-align:centers;">No Nota</th>
			<th>Judul</th>
            <th>Pemesan</th>
            <th style="width:7%;">Pesan</th>
            <th style="width:7%;">Status</th>
			<th style="width:7%;">Jumlah</th>
            <th>Sisa</th>
		</tr>
	</thead>
	<tbody>
	<?php 
	$alt;
	$i = 0;
	foreach ($results->data as $data) {
		if (($i % 2) == 0) {
			$style	= 'background-color:#f6f6f6;';
		}
		else {
			$style	= 'background-color:white';
		}
	?>
	<tr <?php echo $alt; ?> style ="cursor: pointer;<?php echo $style; ?>" onclick="location.href='print-digital-edit.php?ORD_NBR=<?php echo $data->ORD_NBR;?>';">
		<td class="std" style="text-align:right;"><?php echo $result->ORD_NBR;?></td>
		<td class="std" style="text-align:left;"><?php echo $result->ORD_TTL;?></td>
		<td class="std" style="text-align:left;"><?php echo $result->BUY_CO_NAME;?></td>
		<td class="std" style="text-align:left;"><?php echo $result->ORD_DTE;?></td>
		<td class="std" style="text-align:right;"><?php echo number_format($result->ORD_STT_DESC, 0, ',', '.');?></td>
		<td class="std" style="text-align:right;"><?php echo number_format($result->TOT_AMT, 0, ',', '.');?></td>
		<td class="std" style="text-align:right;"><?php echo number_format($result->TOT_REM, 0, ',', '.');?></td>
	</tr>
	<?php 
	$i++;
	}
	?>
	</tbody>
	<tfoot>
		<tr style="border-top:1px solid grey">
			<td class="std" style="text-align:right;font-weight:bold;" colspan="5">Total:</td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->TOT_AMT, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->TOT_REM, 0, ',', '.');?></td>
		</tr>
	</tfoot>
</table>