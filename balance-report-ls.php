<?php

require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

$bookNumber	= $_GET['BK_NBR'];
$Actg		= $_GET['ACTG'];

if($_SESSION['PLUS_MODE'] == 1) {
	$_GET['PLUS'] 	= 1;
}

try {
    
    ob_start();
    include __DIR__ . DIRECTORY_SEPARATOR . "ajax/accounting/balance-report-akun.php";

    $results = json_decode(ob_get_clean());
} catch (\Exception $ex) {
    ob_end_clean();
}


?>


<table class="table-accounting">
	<thead>
		<tr>
			<th class="nosort" colspan="4" style="text-align:center">Aktiva</th>
			<th class="nosort" colspan="4" style="text-align:center">Pasiva</th>
		</tr>
		<tr>
			<th class="nosort">Tipe</th>
			<th class="nosort">Kode</th>
			<th class="nosort">Deskripsi</th>
			<th class="nosort">Total</th>
			<th class="nosort">Tipe</th>
			<th class="nosort">Kode</th>
			<th class="nosort">Deskripsi</th>
			<th class="nosort">Total</th>
		</tr>
	</thead>
	<tbody>
	<tr>
		<td colspan="4" style="vertical-align:top">
			<table>
				<?php
				if(isset($results->activa)) {
				foreach($results->activa as $key=>$report) {				
					echo "<tr><td style='font-weight:bold;width:20%' colspan='4' >".$key."</td></td></tr>";
					foreach($report as $data=>$value) {
						if($value->BALANCE != ''){
							echo "<tr>";
								echo "<td style='width:5%'></td>";	
								echo "<td>".$value->ACC_NBR."</td>";
								echo "<td>".$value->CD_SUB_DESC."</td>";
								echo "<td style='text-align:right'>".number_format($value->BALANCE,0,',','.')."</td>";
							echo "</tr>";
						}
					}
				}
				}
				?>
			</table>
		</td>
		<td colspan="4" style="vertical-align:top">
			<table>
			<?php
			if(isset($results->passiva)) {
			foreach($results->passiva as $key=>$report) {				
					echo "<tr><td style='font-weight:bold;width:20%' colspan='4' >".$key."</td></td></tr>";
					foreach($report as $data=>$value) {
						if($value->BALANCE != ''){
							echo "<tr>";
								echo "<td style='width:5%'></td>";	
								echo "<td>".$value->ACC_NBR."</td>";
								echo "<td>".$value->CD_SUB_DESC."</td>";
								echo "<td style='text-align:right'>".number_format($value->BALANCE,0,',','.')."</td>";
							echo "</tr>";
						}
					}
				}
			}
			?>
				<tr>
					<td style="font-weight:bold;" colspan="3">LABA RUGI</td>
					<td style='text-align:right'><?php echo number_format($results->total->PROFIT_LOSS,0,',','.'); ?></td>
				</tr>
			</table>
		</td>
	</tr>
	
	</tbody>
	<tfoot>
		<tr>
			<td class="std" style="text-align:left"></td>
			<td class="std" style="text-align:right;font-weight:bold;border-bottom:2px solid black" colspan="3">+</td>
			<td class="std" style="text-align:left"></td>
			<td class="std" style="text-align:right;font-weight:bold;border-bottom:2px solid black" colspan="3">+</td>
		</tr>
		<tr >
		<td class="std" style="font-weight:bold;" colspan="3">Total Aktiva</td>
		<td class="std" style="font-weight:bold;text-align:right;"> <?php echo number_format($results->total->ACTIVA,0,',','.'); ?></td>
		<td class="std" style="font-weight:bold;" colspan="3">Total Pasiva</td>
		<td class="std" style="font-weight:bold;text-align:right;"> <?php echo number_format($results->total->PASSIVA_NETT,0,',','.'); ?></td>
		</tr>
	</tfoot>
</table>