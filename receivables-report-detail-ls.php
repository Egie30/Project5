<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

try {
	$_GET["GROUP"]	= "ORD_NBR";
	$_GET["ORD_BY"]	= "ORD_TS";
	
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "ajax/receivables.php";

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
			<th class="sortable" style="text-align:right;">No.</th>
			<?php if($_GET['EXPORT']!='XLS'){	?>
			<th class="nosort"></th>
			<?php } ?>
			<th>Judul</th>
			<th>Pemesan</th>
			<th style="width:7%;">Pesan</th>
			<th>Status</th>
			<th>Janji</th>
			<th style="width:7%;">Jadi </th>
			<th>Jatuh Tempo</th>
			<th>Jumlah</th>
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
		<td style="text-align:right;"><?php echo $data->ORD_NBR;?></td>
		<?php
		echo "<td style='text-align:left;white-space:nowrap'>";
			if(in_array($data->BUY_CO_NBR,$TopCusts)){
				echo "<div class='listable'><span class='fa fa-star listable'></span></div>";
			}				
			if($data->SPC_NTE !=""){
				echo "<div class='listable'><span class='fa fa-comment listable'></span></div>";
			}
			if($data->DL_CNT >0){
				echo "<div class='listable'><span class='fa fa-truck listable' style='margin-left:-1px'></span></div>";
			}
			if($data->PU_CNT >0){
				echo "<div class='listable'><span class='fa fa-shopping-cart listable'></span></div>";
			}
			if($data->NS_CNT >0){
				echo "<div class='listable'><span class='fa fa-flag listable'></span></div>";
			}
			if($data->IVC_PRN_CNT >0){
				echo "<div class='listable'><span class='fa fa-print listable'></span></div>";
			}
		echo "</td>";
		?>
		<td style='white-space:nowrap'><?php echo $data->ORD_TTL;?></td>
		<td style='white-space:nowrap'><?php //echo $data->NAME_PPL;?> <?php //echo $data->NAME_CO;?> 
			<?php if($data->NAME_PPL == '' && $data->NAME_CO == ''){ echo "Tunai";}else{ echo $data->NAME_PPL." ".$data->NAME_CO;} ?>
		</td>
		<td style="text-align:center;"><?php echo parseDateShort($data->ORD_TS);?></td>
		<td style="text-align:center;"><?php echo $data->ORD_STT_DESC;?></td>
		<td style='text-align:center;white-space:nowrap'>
			<?php echo parseDateShort($data->DUE_TS);?> <?php echo parseHour($data->DUE_TS);?> <?php echo parseMinute($data->DUE_TS);?>
		</td>
		<td style="text-align:center;"><?php echo parseDateShort($data->CMP_TS);?></td>
		<td style='text-align:center;white-space:nowrap'><?php echo parseDateShort($data->PAST_DUE);?></td>
		<td style="text-align:right;"><?php echo number_format($data->TOT_AMT,0,'.',',');?></td>
		<td style="text-align:right;"><?php echo number_format($data->TOT_REM,0,'.',',');?></td>
	</tr>
	<?php 
	$i++;
	}
	?>
	</tbody>
	<tfoot>
		<tr style="border-top:1px solid grey">
			<td class="std" style="text-align:left;font-weight:bold;" colspan="9">Total</td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->TOT_AMT, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->TOT_REM, 0, ',', '.');?></td>
		</tr>
	</tfoot>
</table>