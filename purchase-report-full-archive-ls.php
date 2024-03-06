<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

$IvcTyp		= $_GET['IVC_TYP'];
$Type		= $_GET['TYP'];
$CatSubType	= $_GET['CAT_SUB_TYP'];
$PayType	= $_GET['PAY_TYP'];

try {
	
	ob_start();
	
	if($CatSubType == 'COST') {
		include __DIR__ . DIRECTORY_SEPARATOR . "ajax/purchase-report-full.php";
	}
	else {
		include __DIR__ . DIRECTORY_SEPARATOR . "ajax/purchase.php";
	}

	$results = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

//echo "<pre>"; print_r($results);

if (count($results->data) == 0) {
	echo "<div class='searchStatus'>Data yang ingin ditampilkan tidak tersedia.</div>";
	die();
}

if($_GET['GROUP'] == "YEAR") {	$header = 'Tahun';	}
	else if($_GET['GROUP'] == "MONTH") { $header = 'Bulan';	$title = 'Tahun '.$_GET['YEARS'];}
		else if($_GET['GROUP'] == "DAY") { $header = 'Tanggal';	$title = 'Bulan '.$_GET['MONTHS'].' Tahun '.$_GET['YEARS']; }
			else { $header = 'Nomor Nota'; $title = 'Tanggal '.$_GET['DAYS'].' Bulan '.$_GET['MONTHS'].' Tahun '.$_GET['YEARS']; }

echo '<h3>'.$title.'</h3>';
			
?>


<table class="table-accounting tablesorter">
	<thead>
		<tr>
			<?php 
				echo '<th class="sortable">Nomor</th>';		
				echo '<th class="sortable">'.$header.'</th>';
				
				if($_GET['GROUP'] == 'ORD_NBR') {
					echo '<th class="sortable">Pengirim</th>';
					echo '<th class="sortable">Penerima</th>';
				}
				echo '<th class="sortable" style="text-align:center">Total Nota Lunas</th>';
			?>
			
			
		</tr>
	</thead>
	<tbody>
		<?php
		$i = $_GET['page'] > 1 ? ($_GET['page'] - 1) * $_GET['LIMIT'] : 0;
		$i++;

		foreach ($results->data as $data) {
			$group = "";
			if($_GET['GROUP'] == "YEAR") { 
				$link = "location.href='purchase-report-full-archive.php?ACTG=0&YEARS=".$data->ORD_YEAR."&GROUP=MONTH&IVC_TYP=".$IvcTyp."&CO_NBR=".$_GET['CO_NBR']."&EXP_TYP=".$_GET['EXP_TYP']."&TYP=".$Type."&CAT_SUB_TYP=".$CatSubType."&PAY_TYP=".$PayType."' ";
				}
				else if($_GET['GROUP'] == "MONTH") { 
					$link = "location.href='purchase-report-full-archive.php?ACTG=0&YEARS=".$data->ORD_YEAR."&MONTHS=".$data->ORD_MONTH."&GROUP=DAY&IVC_TYP=".$IvcTyp."&CO_NBR=".$_GET['CO_NBR']."&EXP_TYP=".$_GET['EXP_TYP']."&TYP=".$Type."&CAT_SUB_TYP=".$CatSubType."&PAY_TYP=".$PayType."' ";
				}
					else if($_GET['GROUP'] == "DAY") { 
						$link = "location.href='purchase-report-full-archive.php?ACTG=0&YEARS=".$data->ORD_YEAR."&MONTHS=".$data->ORD_MONTH."&DAYS=".$data->ORD_DAY."&GROUP=ORD_NBR&IVC_TYP=".$IvcTyp."&CO_NBR=".$_GET['CO_NBR']."&EXP_TYP=".$_GET['EXP_TYP']."&TYP=".$Type."&CAT_SUB_TYP=".$CatSubType."&PAY_TYP=".$PayType."' ";
					}
					else {
						$link = "location.href='retail-stock-edit.php?ACTG=0&YEARS=".$data->ORD_YEAR."&MONTHS=".$data->ORD_MONTH."DAYS=".$data->ORD_DAY."&ORD_NBR=".$data->ORD_NBR."&IVC_TYP=".$IvcTyp."&CO_NBR=".$_GET['CO_NBR']."&EXP_TYP=".$_GET['EXP_TYP']."&TYP=".$Type."&CAT_SUB_TYP=".$CatSubType."&PAY_TYP=".$PayType."' ";
					}
			?>
			
			<tr style="cursor:pointer" onclick="<?php echo $link; ?>">
				<td class="std" style="text-align:center"><?php echo $i;?></td>
				<td class="std" style="text-align:left;white-space:nowrap">
				<?php 
				if($_GET['GROUP'] == "YEAR") {	echo $data->ORD_YEAR;	}
					else if($_GET['GROUP'] == "MONTH") { echo $data->ORD_MONTHNAME;	}
						else if($_GET['GROUP'] == "DAY") { echo $data->ORD_DTE;	}
							else { echo $data->ORD_NBR; }
				
				?>
				</td>
				<?php 
				if($_GET['GROUP'] == 'ORD_NBR') { ?>
					<td class="std" style="text-align:left"><?php echo $data->SPL_NAME;?></td>
					<td class="std" style="text-align:left"><?php echo $data->RCV_NAME;?></td>
				<?php } ?>
			
					<td class="std" style="text-align:right"><?php echo number_format($data->TND_AMT, 0, ',', '.');?></td>
			</td>
			</tr>
			<?php
			$i++;
		}
		?>
	</tbody>
	<tfoot>
		<tr style="border-top:1px solid grey">
			<?php if($_GET['GROUP'] == 'ORD_NBR') { $span = 4; } else { $span = 2; }?>
			
			<td class="std" style="text-align:right;font-weight:bold;" colspan="<?php echo $span; ?>">Total:</td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->TND_AMT, 0, ',', '.');?></td>
		</tr>
	</tfoot>
	</table>


<?php buildPagination($results->pagination, "purchase-report-full-archive-ls.php"); ?>
