<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

$Type			= $_GET['TYP'];
$_GET['CO_NBR']	= $CoNbrDef;
$Actg			= $_GET['ACTG'];
$beginDate		= $_GET['BEG_DT'];
$endDate		= $_GET['END_DT'];
$PaymentType	= $_GET['PYMT_TYP'];

try {

	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "ajax/procurement-report.php";

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

echo '<h3>'.$title.'</h3>';
			
?>

<table class="table-accounting tablesorter">
	<thead>
		<tr style="text-align:center">
			<?php 
			if($_GET['GROUP'] == "CAT_SUB_NBR") { 
				echo '<th class="sortable">Sub Departemen</th>'; 
				echo '<th class="sortable">Tipe</th>';
				}
				else { 
					echo '<th class="sortable">Nomor Nota</th>'; 
					echo '<th class="sortable">Tanggal Nota</th>'; 
					echo '<th class="sortable">Tanggal Terima</th>'; 
					echo '<th class="sortable">No Faktur Pajak</th>'; 
					echo '<th class="sortable">Tgl Faktur Pajak</th>'; 
					if($Type == 'ACTG') {
						echo '<th class="sortable">Tanggal Lunas</th>'; 
					}
					echo '<th class="sortable">Pengirim</th>';
					echo '<th class="sortable">Penerima</th>';
					echo '<th class="sortable">Sub Departemen</th>';
					echo '<th class="sortable">Tipe</th>';
					if(($locked==0) && ($_COOKIE["LOCK"] != "LOCK")) {
						echo '<th class="sortable">Rekening</th>';
					}
				}

				echo "<th class='sortable'>Subtotal Nota</th>"; 
				echo "<th class='sortable'>PPN</th>";
				echo "<th class='sortable'>Total Nota</th>";
				
				echo '<th class="sortable">Sisa</th>';
				
				if($Type == 'ACTG') {
				echo '<th class="sortable">Keterangan</th>';
				echo '<th class="sortable">Tgl Rekening Koran</th>';
				}
			?>		
		</tr>
	</thead>
	<tbody>
		<?php
		$i = $_GET['page'] > 1 ? ($_GET['page'] - 1) * $_GET['LIMIT'] : 0;
		$i++;

		foreach ($results->data as $data) {
			
			if($_GET['GROUP'] == "CAT_SUB_NBR") { 
						$link = "location.href='".$file."?ACTG=".$Actg."&BEG_DT=".$beginDate."&END_DT=".$endDate."&CAT_SUB_NBR=".$data->CAT_SUB_NBR."&CAT_TYP_NBR=".$data->CAT_TYP_NBR."&TYP=".$Type."&IVC_TYP=RC&GROUP=ORD_NBR'";
					}
					else {
						
						$link="style='text-align:center;white-space:nowrap;cursor:pointer;' onclick=".chr(34)."location.href='retail-stock-edit.php?ORD_NBR=".$data->ORD_NBR."';".chr(34)." ";
						
						$linkBank="style='cursor:pointer;' onclick=".chr(34)."location.href='bank-statement-edit.php?BNK_STMT_TYP=DB&ORD_NBR=".$data->ORD_NBR."&ORD_NBR=".$data->ORD_NBR."&PYMT_TYP=".$data->PYMT_TYP."&ORD_TTL=".$data->ORD_TTL."';".chr(34)." ";
					}
					
			?>
			
			<tr>
				
				<?php 
				if($_GET['GROUP'] == "CAT_SUB_NBR") {	
					echo '<td class="std" style="text-align:left;white-space:nowrap">'.$data->CAT_SUB_DESC.'</td>';	
					echo '<td class="std" style="text-align:left">'.$data->CAT_TYP.'</td>';
				} else {
						echo '<td class="std" '.$link.'>'.$data->ORD_NBR.'</td>';
						echo '<td class="std" style="text-align:left;white-space:nowrap">'.$data->ORD_DTE.'</td>';
						echo '<td class="std" style="text-align:left;white-space:nowrap">'.$data->DL_DTE.'</td>';
						echo '<td class="std" style="text-align:left;white-space:nowrap">'.$data->TAX_IVC_NBR.'</td>';
						echo '<td class="std" style="text-align:left;white-space:nowrap">'.$data->TAX_IVC_DTE.'</td>';
						
						if($Type == 'ACTG') {
							echo '<td class="std" style="text-align:left;white-space:nowrap">'.$data->PAID_DTE.'</td>';
						}
						echo '<td class="std" style="text-align:left;white-space:nowrap">'.$data->SHIPPER.'</td>';	
						echo '<td class="std" style="text-align:left;white-space:nowrap">'.$data->RECEIVER.'</td>';	
						echo '<td class="std" style="text-align:left;white-space:nowrap">'.$data->CAT_SUB_DESC.'</td>';
						echo '<td class="std" style="text-align:left;white-space:nowrap">'.$data->CAT_TYP.'</td>';
						if(($locked==0) && ($_COOKIE["LOCK"] != "LOCK")) {
							echo '<td class="std" style="text-align:center;white-space:nowrap">'.$data->ACTG_TYP.'</td>';
						}	
					}
				?>
				
				<td class="std" style="text-align:right"><?php echo number_format($data->SUBTOTAL, 0, ',', '.');?></td>
				<td class="std" style="text-align:right"><?php echo number_format($data->TAX_AMT, 0, ',', '.');?></td>
				<td class="std" style="text-align:right"><?php echo number_format($data->TOT_AMT, 0, ',', '.');?></td>
				<td class="std" style="text-align:right"><?php echo number_format($data->TOT_REM, 0, ',', '.');?></td>
				
				<?php if($Type == 'ACTG') {
							echo '<td class="std" style="text-align:left;white-space:nowrap">'.$data->PYMT_DESC.'</td>';
							echo '<td class="std" '.$linkBank.'>'.$data->BNK_STMT_DTE.'</td>';
						} ?>
				
			</td>
			</tr>
			<?php
			$i++;
		}
		?>
	</tbody>
	<tfoot>
		<tr style="border-top:1px solid grey">	

			<?php 
				if($_GET['GROUP'] == 'ORD_NBR') 
					{ 
						if($Type == 'ACTG') {
							$span = 9;
						}
						else {
							if(($locked==0) && ($_COOKIE["LOCK"] != "LOCK")) {
								$span = 8;
							} else {
								$span = 7;
							}
						}
					} 
				else { 
					if($Type == 'ACTG') {
						$span = 2;
					}
					else {
						$span = 2;
					}
				} 
				
			?>	
			
			<td class="std" style="text-align:right;font-weight:bold;" colspan="<?php echo $span; ?>">Total:</td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->SUBTOTAL, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->TAX_AMT, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->TOT_AMT, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->TOT_REM, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"></td>
		</tr>
	</tfoot>
	</table>


<?php buildPagination($results->pagination, "procurement-tax-report-ls.php"); ?>
