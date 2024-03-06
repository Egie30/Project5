<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

try {
	$_GET["GROUP"] 		= "BUY_CO_NBR";
	$_GET["ORD_BY"] 	= "TOT_REM";
	
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "ajax/receivables.php";

	$results = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}
//echo "<pre>";
//print_r($results);

if (count($results->data) == 0) {
	echo "<div class='searchStatus'>Data yang ingin ditampilkan tidak tersedia.</div>";
	die();
}
?>
<table id="mainTable" class="tablesorter table-freeze std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable sortable-onload-show">
	<thead>
		<tr>
			<th class="sortable" style="text-align:center;">Nota</th>
			<th class="sortable">Nama</th>
			<th class="sortable">Total</th>
			<th class="sortable">Pembayaran</th>
			<th class="sortable">Sisa</th>
		</tr>
	</thead>
	<tbody>
	<?php 
	$alt = "";
	$i	 = 0;
	foreach ($results->data as $data) {
		if (($i % 2) == 0) {
			$style	= 'background-color:#f6f6f6;';
		}else {
			$style	= 'background-color:white';
		}
		
		if($data->NAME_PPL == '' && $data->NAME_CO == ''){
			$company = "TUNAI";
		}else{
			$company = $data->BUY_CO_NBR;
		}
	?>
	<tr <?php echo $alt; ?> style ="cursor: pointer;<?php echo $style; ?>" onclick="location.href='receivables-report-detail.php?CO_NBR=<?php echo $company;?>&MONTH=<?php echo $_GET['MONTH'];?>&YEAR=<?php echo $_GET['YEAR'];?>';">
		<td style="text-align:center;"><?php echo $data->ORD_NBR_CNT;?></td>
		<td style='white-space:nowrap'><?php //echo $data->NAME_PPL;?> <?php //echo $data->NAME_CO;?> 
			<?php if($data->NAME_PPL == '' && $data->NAME_CO == ''){ echo "Tunai";}else{ echo $data->NAME_PPL." ".$data->NAME_CO;} ?>
		</td>
		<td style="text-align:right;"><?php echo number_format($data->TOT_AMT,0,'.',',');?></td>
		<td style="text-align:right;"><?php echo number_format($data->PYMT_DOWN,0,'.',',');?></td>
		<td style="text-align:right;"><?php echo number_format($data->TOT_REM,0,'.',',');?></td>
	</tr>
	<?php
	$i++;
	}
	?>
	</tbody>
	<tfoot>
		<tr style="border-top:1px solid grey">
			<td class="std" style="text-align:left;font-weight:bold;" colspan="2">Total</td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->TOT_AMT, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->PYMT_DOWN, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->TOT_REM, 0, ',', '.');?></td>
		</tr>
	</tfoot>
</table>