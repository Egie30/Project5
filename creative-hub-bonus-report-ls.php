<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/pagination/pagination.php";
require_once "framework/security/default.php";

$security		= getSecurity($_SESSION['userID'],"Executive");

try{
	$_GET["GROUP"]	= "ORD_NBR";
	
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "ajax/creative-hub-bonus.php";

	$results = json_decode(ob_get_clean());
}catch(\Exception $ex) {
	ob_end_clean();
}

//echo "<pre>";
//print_r($results);

if (count($results->data) == 0) {
	echo "<div class='searchStatus'>Data yang ingin ditampilkan tidak tersedia.</div>";
	exit;
}

?>
<table id="mainTable" class="table-accounting tablesorter table-freeze std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable sortable-onload-show">
	<thead>
		<tr style="text-align:center">
			<th style="width:5%" class="sortable" style="text-align:centers;">No Nota</th>
			<th style="text-align:center;">Judul</th>
            <th style="text-align:center;">Pemesan</th>
            <th style="width:7%;text-align:center;">Tgl Pesan</th>
            <th style="width:7%;text-align:center;">Status</th>
            <th style="width:7%;text-align:center;">Tgl Selesai</th>
			<th style="width:7%;text-align:center;">Jumlah</th>
            <th style="text-align:center;">Sisa</th>
            <th style="text-align:center;">Bonus</th>
            <th style="text-align:center;">Sales</th>
		</tr>
	</thead>
	<tbody>
		<?php 
			foreach ($results->data as $result){
		?>
		<tr style="cursor:pointer;" onclick="location.href='creativehub-edit.php?ORD_NBR=<?php echo $result->ORD_NBR;?>';">
			<td class="std" style="text-align:right;"><?php echo $result->ORD_NBR;?></td>
			<td class="std" style="text-align:left;"><?php echo $result->ORD_TTL;?></td>
			<td class="std" style="text-align:left;"><?php echo $result->BUY_DESC;?></td>
			<td class="std" style="text-align:center;"><?php echo $result->ORD_DTE;?></td>
			<td class="std" style="text-align:center;"><?php echo $result->ORD_STT_DESC;?></td>
			<td class="std" style="text-align:center;"><?php echo $result->CMP_DTE;?></td>
			<td class="std" style="text-align:right;"><?php echo number_format($result->TOT_AMT, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;"><?php echo number_format($result->TOT_REM, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;"><?php echo number_format($result->TOT_BNS, 0, ',', '.');?></td>
			<td class="std" style="text-align:left;"><?php echo dispNameScreen(shortName($result->NAME));?></td>
		</tr>
		<?php } ?>
	</tbody>
	<tfoot>
		<tr style="border-top:1px solid grey;">	
			<td class="std" style="text-align:right;font-weight:bold;" colspan="6">Total:</td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->TOT_AMT, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->TOT_REM, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->TOT_BNS, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;">&nbsp;</td>
		</tr>
	</tfoot>
</table>

<table style="width:30%" class="table-accounting">
	<thead>
		<tr style="text-align:center">
			<th class="sortable" style="text-align:centers;">Nama</th>
			<th class="sortable">Bonus</th>
		</tr>
	</thead>
	<tbody>
		<?php 
			try{
				$_GET["GROUP"]	= "SLS_PRSN_NBR";
				
				ob_start();
				include __DIR__ . DIRECTORY_SEPARATOR . "ajax/creative-hub-bonus.php";

				$results = json_decode(ob_get_clean());
			}catch(\Exception $ex) {
				ob_end_clean();
			}
			$alt="";
			foreach ($results->data as $result){
		?>
		<tr style="cursor:pointer;" <?php echo $alt; ?>>
			<td class="std" style="text-align:left;"><?php echo dispNameScreen(shortName($result->NAME));?></td>
			<td class="std" style="text-align:right;"><?php echo number_format($result->TOT_BNS, 0, ',', '.');?></td>
			
		</tr>
		<?php } ?>
	</tbody>
	<tfoot>
		<tr style="border-top:1px solid grey;">	
			<td class="std" style="font-weight:bold;">Total:</td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->TOT_BNS, 0, ',', '.');?></td>
		</tr>
		<?php if ($security <= 6) { ?>
		<tr style="border-top:1px solid grey;">	
			<td class="std" style="font-weight:bold;">Komisi SPV</td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->TOT_BNS_SPV, 0, ',', '.');?></td>
		</tr>
		<?php } ?>
	</tfoot>
</table>
<?php buildPagination($results->pagination, "creative-hub-bonus-report-ls.php"); ?>