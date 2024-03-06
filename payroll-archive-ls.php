<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

$PayActgType	= $_GET['PAY_ACTG_TYP'];
//$_GET['CO_NBR']	= $CoNbrDef;

	try {
		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "ajax/payroll.php";

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
			else { $header = 'Karyawan'; $title = 'Tanggal '.$_GET['DAYS'].' Bulan '.$_GET['MONTHS'].' Tahun '.$_GET['YEARS']; }

echo '<h3>'.$title.'</h3>';
			
?>


<table class="table-accounting tablesorter">
	<thead>
		<tr>
			<?php 
				echo '<th class="sortable">Nomor</th>';		
				echo '<th class="sortable">'.$header.'</th>';
				echo '<th class="sortable">Gaji Diterima</th>';
				echo '<th class="sortable">Cicilan</th>';
				echo '<th class="sortable">Pencairan Kas Bon</th>';
				echo '<th class="sortable">Gaji Terlapor</th>';
			?>
			
		</tr>
	</thead>
	<tbody>
		<?php
		$i = $_GET['page'] > 1 ? ($_GET['page'] - 1) * $_GET['LIMIT'] : 0;
		$i++;

		foreach ($results->data as $data) {
			if($_GET['GROUP'] == "MONTH") {
					$link = "location.href='payroll-archive.php?ACTG=".$Actg."&PYMT_DTE=".$data->PAY_DTE."&GROUP=PRSN_NBR&CO_NBR=".$_GET['CO_NBR']."&TYP=".$Type."&PAY_ACTG_TYP=".$PayActgType."'";
				}
				else {
						$link = "location.href='payroll-edit.php?ACTG=".$Actg."&PRSN_NBR=".$data->PRSN_NBR."&PYMT_DTE=".$data->PAY_DTE."&CO_NBR=".$_GET['CO_NBR']."'";
					}
				
			?>
			
			<tr style="cursor:pointer" onclick="<?php echo $link; ?>">
				<td class="std" style="text-align:center"><?php echo $i;?></td>
				<td class="std" style="text-align:left;white-space:nowrap">
				<?php 
				if($_GET['GROUP'] == "YEAR") {	echo $data->PAY_YEAR;	}
					else if($_GET['GROUP'] == "MONTH") { echo $data->PAY_MONTHNAME;	}
						else if($_GET['GROUP'] == "DAY") { echo $data->PAY_DTE;	}
							else { echo $data->NAME; }
				
				$total 	= $data->PAY_AMT + $data->DEBT_MO - $data->DSBRS_CRDT;
				
				?>
				
				</td>
				<td class="std" style="text-align:right"><?php echo number_format($data->PAY_AMT, 0, ',', '.');?></td>
				<td class="std" style="text-align:right"><?php echo number_format($data->DEBT_MO, 0, ',', '.');?></td>
				<td class="std" style="text-align:right"><?php echo number_format($data->DSBRS_CRDT, 0, ',', '.');?></td>
				<td class="std" style="text-align:right"><?php echo number_format($total, 0, ',', '.');?></td>
								
			</td>
			</tr>
			<?php
			$i++;
			
			$total_terlapor	+= $total;
		}
		?>
	</tbody>
	<tfoot>
		<tr style="border-top:1px solid grey">
			<td class="std" style="text-align:right;font-weight:bold;" colspan="2">Total:</td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->PAY_AMT, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->DEBT_MO, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->DSBRS_CRDT, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($total_terlapor, 0, ',', '.');?></td>
		</tr>
	</tfoot>
	</table>


<?php buildPagination($results->pagination, "payroll-archive-ls.php"); ?>
