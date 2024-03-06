<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

$Type				= $_GET['TYP'];
$_GET['CO_NBR']		= $CoNbrDef;
$Actg				= $_GET['ACTG'];


if ($Type == 'ACTG') { $file = "prn-dig-report-accounting.php"; }
	else if ($Type == 'FULL') { $file = "prn-dig-report-full.php"; }
		else if ($Type == 'PAY') { $file = "prn-dig-report-pay.php"; }
			else if ($Type == 'ORD' || $Type == 'TAX_IVC') { $file = "prn-dig-report-order.php"; }
				else if ($Type == 'TAX') { $file = "prn-dig-report-tax.php"; }
		
	try {
		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "ajax/".$file."";

		$results = json_decode(ob_get_clean());
	} catch (\Exception $ex) {
		ob_end_clean();
	}
	//echo "<pre>".$query;exit();
if (count($results->data) == 0) {
	echo "<div class='searchStatus'>Data yang ingin ditampilkan tidak tersedia.</div>";
	die();
}

			
?>


<table class="table-accounting tablesorter">
	<thead>
		<tr style="text-align:center">
			
			<th class='sortable'>Nomor Nota</th>
			<th class='sortable'>Tgl Nota</th>
			<?php 
				if($Type == 'PAY') {	echo "<th class='sortable'>Tgl Pembayaran</th>";	}
					else {	echo "<th class='sortable'>Tgl Selesai</th>"; }
			?>
			<th class="sortable">No Faktur Pajak</th>
			<th class="sortable">Tgl Faktur Pajak</th>
			<th class='sortable'>Customer</th>
			<th class='sortable'>Judul Nota</th>
			<?php 
				if($Type == 'PAY') {	
					echo "<th class='sortable'>Total Pembayaran</th>";	
					echo "<th class='sortable'>Ket.</th>";	
					echo "<th class='sortable'>Tgl Rek. Koran</th>"; 
					echo "<th class='sortable'></th>"; 
					}
					else {	
					echo "<th class='sortable'>Subtotal Nota</th>"; 
					echo "<th class='sortable'>PPN</th>";
					echo "<th class='sortable'>Total Nota</th>";
					echo "<th class='sortable'>Ket.</th>";
					}
			
			?>
			
		</tr>
	</thead>
	<tbody>
		<?php
		$i = $_GET['page'] > 1 ? ($_GET['page'] - 1) * $_GET['LIMIT'] : 0;
		$i++;

		foreach ($results->data as $data) {
		
		$linkInvoice="style='cursor:pointer;' onclick=".chr(34)."location.href='print-digital-edit.php?ORGN=".$data->ORGN."&ORD_NBR=".$data->ORD_NBR."';".chr(34)." ";
		
		$linkBank="style='cursor:pointer;' onclick=".chr(34)."location.href='bank-statement-edit.php?BNK_STMT_TYP=CR&PYMT_NBR=".$data->PYMT_NBR."&ORD_NBR=".$data->ORD_NBR."&PYMT_TYP=".$data->PYMT_TYP."&ORD_TTL=".$data->ORD_TTL."&BNK_STMT_NBR=".$data->BNK_STMT_NBR."';".chr(34)." ";
			?>
			
			<tr >
			<?php
				echo "<td ".$linkInvoice.">".$data->ORD_NBR."</td>";
				echo "<td>".$data->ORD_DTE."</td>";
				echo "<td>".$data->CSH_DTE."</td>";	
				echo "<td>".$data->TAX_IVC_NBR."</td>";	
				echo "<td>".$data->TAX_IVC_DTE."</td>";
				echo "<td>".$data->BUY_NAME."</td>";
				echo "<td>".$data->ORD_TTL."</td>";

				if($data->BNK_STMT_DTE == null) { $bankStatementDate = "<td></td>"; } 
					else { $bankStatementDate = "<td ".$linkBank.">".$bankStatementDate."</td>"; }
				
				if($Type == 'PAY') {
					echo "<td style='text-align:right'>".number_format($data->TOT_AMT, 0, ',', '.')."</td>";
					echo "<td>".$data->PYMT_TYP."</td>";
					echo $bankStatementDate;
					$span = 5;
					
				}
				else
				{
					echo "<td style='text-align:right'>".number_format($data->SUBTOTAL, 0, ',', '.')."</td>";
					echo "<td style='text-align:right'>".number_format($data->TAX_AMT, 0, ',', '.')."</td>";
					echo "<td style='text-align:right'>".number_format($data->TOT_AMT, 0, ',', '.')."</td>";
					echo "<td>".$data->TAX_APL_DESC."</td>";
					
					$span = 5;
				}
				
				
				
				?>
			</tr>
			<?php
			$i++;
		}
		?>
	</tbody>
	<tfoot>
		<tr style="border-top:1px solid grey">
			<td class="std" style="text-align:right;font-weight:bold;" colspan=<?php echo $span; ?>>Total:</td>
			<?php 
			if($Type != 'PAY') {
			echo '<td class="std" style="text-align:right;font-weight:bold;">'.number_format($results->total->SUBTOTAL, 0, ',', '.').'</td>';
			echo '<td class="std" style="text-align:right;font-weight:bold;">'.number_format($results->total->TAX_AMT, 0, ',', '.').'</td>';
			}
			echo '<td class="std" style="text-align:right;font-weight:bold;">'.number_format($results->total->TOT_AMT, 0, ',', '.').'</td>';
			?>
		</tr>
	</tfoot>
	</table>


<?php buildPagination($results->pagination, "prn-dig-report-archive-ls.php"); ?>
