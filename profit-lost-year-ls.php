<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

$years		= $_GET['YEARS'];
$RLType		= $_GET['RL_TYP'];

$RevenueReal	= 0;
$CostReal		= 0;
$ProfitLossReal	= 0;

$RevenueStock	= 0;
$CostStock		= 0;
$ProfitLossStock= 0;


?>
<br />

<table class="table-accounting">
	<thead>
		<tr>
			<th class="sortable" rowspan="2" style="text-align:center;">No.</th>
			<th class="sortable" rowspan="2" style="text-align:center;">Bulan</th>
			<th class="sortable" colspan="3" style="text-align:center;border-left:1px solid black;border-right:1px solid black;">Projection</th>
			<th class="sortable" colspan="3" style="text-align:center;border-left:1px solid black;border-right:1px solid black;">Accounting</th>
		</tr>
		<tr>
			<th class="sortable" style="text-align:center;border-left:1px solid black;">Omset</th>
			<th class="sortable" style="text-align:center;">Pengeluaran</th>
			<th class="sortable" style="text-align:center;">Laba Rugi</th>
			
			<th class="sortable" style="text-align:center;border-left:1px solid black;">Omset</th>
			<th class="sortable" style="text-align:center;">Pengeluaran</th>
			<th class="sortable" style="text-align:center;border-right:1px solid black;">Laba Rugi</th>
		</tr>
	</thead>
	<tbody>
		
	<?php
	
	try {
			$_GET['GROUP'] 	= 'MONTH';
			
			ob_start();
			include __DIR__ . DIRECTORY_SEPARATOR . "ajax/prn-dig-report-order.php";

			$resultsPrintOrder = json_decode(ob_get_clean());
		} catch (\Exception $ex) {
			ob_end_clean();
		}
			
	try {
			$_GET['GROUP'] 	= 'MONTH';
			
			ob_start();
			include __DIR__ . DIRECTORY_SEPARATOR . "ajax/prn-dig-report-full.php";

			$resultsPrintFull = json_decode(ob_get_clean());
		} catch (\Exception $ex) {
			ob_end_clean();
		}
		
	try {
			$_GET['GROUP'] 	= 'MONTH';
			
			ob_start();
			include __DIR__ . DIRECTORY_SEPARATOR . "ajax/cost-cash.php";

			$resultsCostCash = json_decode(ob_get_clean());
		} catch (\Exception $ex) {
			ob_end_clean();
		}
	
	try {
			$_GET['GROUP'] 	= 'MONTH';
			
			ob_start();
			include __DIR__ . DIRECTORY_SEPARATOR . "ajax/cost-routine.php";

			$resultsCostRoutine = json_decode(ob_get_clean());
		} catch (\Exception $ex) {
			ob_end_clean();
		}
		
	try {
			$_GET['GROUP'] 	= 'MONTH';
			
			ob_start();
			include __DIR__ . DIRECTORY_SEPARATOR . "ajax/payroll.php";

			$resultsPayroll = json_decode(ob_get_clean());
		} catch (\Exception $ex) {
			ob_end_clean();
		}
			
	try {
		
		$_GET['GROUP'] 			= 'MONTH';
		$_GET['CAT_SUB_TYP']	= 'CLICK';
		$_GET['IVC_TYP']		= 'RC';
		$_GET['TYP']			= 'PRN_DIG';
		$_GET['CAT_SUB_NBR'] 	= 202;
		
		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "ajax/purchase-report.php";

		$resultsClick = json_decode(ob_get_clean());
	} catch (\Exception $ex) {
		ob_end_clean();
	}
	
	unset($_GET['CAT_SUB_TYP']);
	unset($_GET['TYP']);
	unset($_GET['CAT_SUB_NBR']);

	$_GET['GROUP'] 		= "MONTH";
	$_GET['TYP']		= 'RL';
	$_GET['IVC_TYP']	= 'RC';

	try {
		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "../cost-profit-lost.php";

		$resultsCost = json_decode(ob_get_clean());
	} catch (\Exception $ex) {
		ob_end_clean();
	}
	
	try {

	$_GET['GROUP'] 		= "MONTH";

	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "ajax/store-inventory-monthly.php";

	$resultsHpp = json_decode(ob_get_clean());
	} catch (\Exception $ex) {
		ob_end_clean();
	}
	
	unset($_GET['CAT_SUB_TYP']);
	
	try {
		
		$_GET['GROUP'] 			= 'MONTH';
		$_GET['TYP']			= 'PRN_DIG';
		
		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "ajax/purchase-report.php";

		$resultsPurchase = json_decode(ob_get_clean());
	} catch (\Exception $ex) {
		ob_end_clean();
	}

	for($i=0; $i<=11; $i++) {
		
		$PrintOrder	 	= $resultsPrintOrder->data[$i]->TOT_AMT;
		$PrintFull	 	= $resultsPrintFull->data[$i]->TOT_AMT;
		$CostCash		= $resultsCostCash->data[$i]->TOT_SUB;
		$CostRoutine	= $resultsCostRoutine->data[$i]->TOT_SUB;
		$Payroll		= $resultsPayroll->data[$i]->PAY_AMT;
		$Click			= $resultsClick->data[$i]->RCV_TOT_SUB;
		$Cost			= $resultsCost->data[$i]->RCV_TOT_SUB;
		$Hpp			= $resultsHpp->data[$i]->MOV_TOTAL;
		$Purchase		= $resultsPurchase->data[$i]->RCV_TOT_SUB - $resultsPurchase->data[$i]->RTR_TOT_SUB;
		
		$CostPurchase	= $CostCash + $CostRoutine + $Payroll + $Click + $Cost + $Purchase;
		$CostHpp		= $CostCash + $CostRoutine + $Payroll + $Click + $Cost + $Hpp;
		
		echo '<tr>';
		echo '<td>'.($i+1).'</td>';
		echo '<td>'.$resultsPrintOrder->data[$i]->ACT_MO_NAME.'</td>';
		echo '<td style="text-align:right">'.number_format($PrintOrder,0,',','.').'</td>';
		echo '<td style="text-align:right">'.number_format($CostPurchase,0,',','.').'</td>';
		echo '<td style="text-align:right">'.number_format(($PrintOrder - $CostPurchase),0,',','.').'</td>';
		echo '<td style="text-align:right">'.number_format($PrintFull,0,',','.').'</td>';
		echo '<td style="text-align:right">'.number_format($CostHpp,0,',','.').'</td>';
		echo '<td style="text-align:right">'.number_format(($PrintFull - $CostHpp),0,',','.').'</td>';
		
		echo '</tr>';
		
		$TotalPrintOrder	+= $PrintOrder;
		$TotalPrintFull		+= $PrintFull;
		$TotalCostPurchase	+= $CostPurchase;
		$TotalCostHpp		+= $CostHpp;
	
	}
	
?>
	</tbody>

	<tfoot>
		<tr>
			<td class="std" style="text-align:right;font-weight:bold;border-bottom:2px solid black" colspan="8"></td>
		</tr>
		
		<tr>
		<td class="std" style="text-align:center;font-weight:bold;" colspan="2">Total</td>
		<td class="std" style="text-align:right;font-weight:bold;">Rp. <?php echo number_format($TotalPrintOrder,0,',','.');?></td>
		<td class="std" style="text-align:right;font-weight:bold;">Rp. <?php echo number_format($TotalCostPurchase,0,',','.');?></td>
		<td class="std" style="text-align:right;font-weight:bold;">Rp. <?php echo number_format(($TotalPrintOrder - $TotalCostPurchase),0,',','.');?></td>
		
		<td class="std" style="text-align:right;font-weight:bold;">Rp. <?php echo number_format($TotalPrintFull,0,',','.');?></td>
		<td class="std" style="text-align:right;font-weight:bold;">Rp. <?php echo number_format($TotalCostHpp,0,',','.');?></td>
		<td class="std" style="text-align:right;font-weight:bold;">Rp. <?php echo number_format(($TotalPrintFull - $TotalCostHpp),0,',','.');?></td>
		</tr>
	</tfoot>
	
</table>