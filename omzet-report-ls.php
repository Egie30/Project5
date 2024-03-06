<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

$Type			= $_GET['TYP'];
$_GET['CO_NBR']	= $CoNbrDef;
$Actg			= $_GET['ACTG'];

$file = "omzet-report.php";
		
	try {
		
		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "ajax/omzet-report.php";

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
		<tr style="text-align:center">
			<?php 
				echo '<th class="sortable">'.$header.'</th>';
				if($_GET['GROUP'] == 'ORD_NBR') { 
					echo '<th class="sortable">Pemesan</th>';
				}
				echo '<th class="sortable">Total</th>';
				
				if($_GET['GROUP'] == 'ORD_NBR') { 
					echo '<th class="sortable">Ket.</th>';
				}
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
				$link = "location.href='".$file."?ACTG=0&YEARS=".$data->CSH_YEAR."&GROUP=MONTH&CO_NBR=".$_GET['CO_NBR']."&TYP=".$Type."'";
				}
				else if($_GET['GROUP'] == "MONTH") { 
					$link = "location.href='".$file."?ACTG=".$Actg."&YEARS=".$data->CSH_YEAR."&MONTHS=".$data->CSH_MONTH."&GROUP=DAY&CO_NBR=".$_GET['CO_NBR']."&TYP=".$Type."'";
				}
					else if($_GET['GROUP'] == "DAY") { 
						$link = "location.href='".$file."?ACTG=".$Actg."&YEARS=".$data->CSH_YEAR."&MONTHS=".$data->CSH_MONTH."&DAYS=".$data->CSH_DAY."&GROUP=ORD_NBR&CO_NBR=".$_GET['CO_NBR']."&TYP=".$Type."'";
					}
					else {
						$link = "location.href='print-digital-edit.php?ACTG=".$Actg."&YEARS=".$data->CSH_YEAR."&MONTHS=".$data->CSH_MONTH."&DAYS=".$data->CSH_DAY."&ORD_NBR=".$data->ORD_NBR."&CO_NBR=".$_GET['CO_NBR']."'";
					}
			?>
			
			<tr style="cursor:pointer" onclick="<?php echo $link; ?>">
				<td class="std" style="text-align:left;white-space:nowrap">
				<?php 
				if($_GET['GROUP'] == "YEAR") {	echo $data->CSH_YEAR;	}
					else if($_GET['GROUP'] == "MONTH") { echo $data->CSH_MONTHNAME;	}
						else if($_GET['GROUP'] == "DAY") { echo $data->CSH_DTE;	}
							else { echo $data->ORD_NBR; }
					
				?>
				</td>
				<?php if($_GET['GROUP'] == 'ORD_NBR') { ?>
					<td class="std" style="text-align:left"><?php echo $data->BUY_NAME;?></td>
				<?php } ?>
				<td class="std" style="text-align:right"><?php echo number_format($data->TOT_AMT, 0, ',', '.');?></td>
				<?php if($_GET['GROUP'] == 'ORD_NBR') { ?>
					<td class="std" style="text-align:left"><?php echo $data->PYMT_TYP;?></td>
				<?php } ?>								
			</td>
			</tr>
			<?php
			$i++;
		}
		?>
	</tbody>
	<tfoot>
		<tr style="border-top:1px solid grey">
			<?php if($_GET['GROUP'] == 'ORD_NBR') { $span = 2; } else { $span = 1; }?>
			<td class="std" style="text-align:right;font-weight:bold;" colspan="<?php echo $span; ?>">Total:</td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->TOT_AMT, 0, ',', '.');?></td>
		</tr>
	</tfoot>
	</table>


<?php buildPagination($results->pagination, "omzet-report-ls.php"); ?>
