<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/security/default.php";
include "framework/functions/crypt.php";
	
$SecurityAct = getSecurity($_SESSION['userID'],"Accounting");

if (($_GET['MONTH']=='')||($_GET['YEAR']=='')){
	$_GET['MONTH'] = date('m');
	$_GET['YEAR'] = date('Y');
	$filter_date = date('Y-m-01');
} else {
	if ($_GET['MONTH'] < 10) { $month = '0'.$_GET['MONTH']; } else { $month = $_GET['MONTH']; }
	$filter_date = $_GET['YEAR'].'-'.$month.'-01';
}
$companyNumber	= $_GET['CO_NBR'];
$groupDetail	= $_GET['GROUP'];
$groupType	= $_GET['GROUP'];
?>
	<?php 

	$Printing = json_decode(simple_crypt(file_get_contents('192.168.1.70/campus/finance-matrix-data.php?GROUP=CAT_TYP_NBR&DTE='.$filter_date),'d'));
	//echo '<pre>'; print_r($Printing); echo '</pre>';
	
	$Printings = json_decode(simple_crypt(file_get_contents('192.168.1.70/campus/finance-matrix-data.php?GROUP=CAT_SUB_NBR&DTE='.$filter_date),'d'));
	//echo '<pre>'; print_r($Printings); echo '</pre>';
	?>

	<table style="width:100%; padding-right:15px;">
	<thead>
		<tr>
			<?php 
				echo '<th style="width:10%" class="sortable" style="text-align:center;">Printing</th>';	
				echo '<th class="sortable" style="text-align:center;" colspan=3></th>';
			?>
			
			
		</tr>
		<tr>
			<?php 
				echo '<th style="width:10%" class="sortable" style="text-align:center;">Deskripsi</th>';	
				echo '<th class="sortable" style="text-align:center;">Kategori</th>';				
				echo '<th class="sortable" style="text-align:center;"></th>';
				echo '<th class="sortable" style="text-align:center;">Jumlah</th>';
			?>
			
			
		</tr>
	</thead>
	<tbody>

		<?php
		echo "<tr style='cursor:pointer;border-top:1px solid #ddd;border-bottom:1px solid #ddd;' class='tr-master'>";
			echo '<td class="std" style="text-align:left;">Omset</td>';
			echo '<td class="std" style="text-align:left;" colspan=2></td>';
			echo '<td class="std" style="text-align:right;">'.number_format($Printing->total->TOT_OMSET, 0, ',', '.').'</td>';
		echo '</tr>';
		
		$i = 0;
		
		foreach ($Printing->data as $Printingdata) { 
			if (($i % 2) == 0) {
				$style	= 'background-color:#eee;';
			}
			else {
				$style	= 'background-color:white';
			}
			
			echo "<tr style='cursor:pointer;".$style."' class='tr-master'>";
				echo '<td class="std" style="text-align:left;">'; if($i==0){ echo "Biaya"; } echo '</td>';
				if($Printingdata->CAT_TYP == '') {
					echo '<td class="std" style="text-align:left;" colspan=2>Lain-Lain</td>';
				}
				else {
					echo '<td class="std" style="text-align:left;" colspan=2>'.$Printingdata->CAT_TYP.'-'.$Printingdata->CAT_TYP_NBR.'</td>';
				}
			
			
			echo '<td class="std" style="text-align:right;">'.number_format($Printingdata->TOT, 0, ',', '.').'</td>';
			echo '</tr>';
									
			foreach ($Printings->data as $Printingsdata) {
			
			if($Printingdata->CAT_TYP_NBR == $Printingsdata->CAT_TYP_NBR) {
				
				echo "<tr class='tr-detail' style='border-top:1px solid #ddd;border-bottom:1px solid #ddd;'>";
				echo '<td class="std" style="text-align:left;"></td>';
				echo '<td class="std" style="text-align:center; width:10%;"></td>';
				echo '<td class="std" style="text-align:left;">'.$Printingsdata->CAT_SUB_DESC.'</td>';
				echo '<td class="std" style="text-align:right">'.number_format($Printingsdata->TOT, 0, ',', '.').'</td>';
			}	
			echo '</tr>';
			}
		$i++;
		}

		if (($i % 2) == 0) { $style	= 'background-color:#eee;'; } else { $style	= 'background-color:white'; }
		echo "<tr style='cursor:pointer;".$style."' class='tr-master'>";
		echo '<td class="std" style="text-align:left;"></td>';
		echo '<td class="std" style="text-align:left;" colspan=2>Gaji</td>';
		echo '<td class="std" style="text-align:right;">'.number_format($Printing->total->TOT_PAYROLL, 0, ',', '.').'</td>';
		echo '</tr>';
		echo "<tr class='tr-detail' style='border-top:1px solid #ddd;border-bottom:1px solid #ddd;'>";
			echo '<td class="std" style="text-align:left;"></td>';
			echo '<td class="std" style="text-align:center; width:10%;"></td>';
			echo '<td class="std" style="text-align:left;">Gaji Pokok</td>';
			echo '<td class="std" style="text-align:right">'.number_format($Printing->total->BASE_PAYROLL, 0, ',', '.').'</td>';	
		echo '</tr>';
		echo "<tr class='tr-detail' style='border-top:1px solid #ddd;border-bottom:1px solid #ddd;'>";
			echo '<td class="std" style="text-align:left;"></td>';
			echo '<td class="std" style="text-align:center; width:10%;"></td>';
			echo '<td class="std" style="text-align:left;">Gaji Tambahan</td>';
			echo '<td class="std" style="text-align:right">'.number_format($Printing->total->ADD_PAYROLL, 0, ',', '.').'</td>';	
		echo '</tr>';
		echo "<tr class='tr-detail' style='border-top:1px solid #ddd;border-bottom:1px solid #ddd;'>";
			echo '<td class="std" style="text-align:left;"></td>';
			echo '<td class="std" style="text-align:center; width:10%;"></td>';
			echo '<td class="std" style="text-align:left;">Gaji Lembur</td>';
			echo '<td class="std" style="text-align:right">'.number_format($Printing->total->OT_PAYROLL, 0, ',', '.').'</td>';	
		echo '</tr>';

		$i++;
		if (($i % 2) == 0) { $style	= 'background-color:#eee;'; } else { $style	= 'background-color:white'; }
		echo "<tr style='cursor:pointer;".$style."' class='tr-master'>";
		echo '<td class="std" style="text-align:left;"></td>';
		echo '<td class="std" style="text-align:left;" colspan=2>Pengeluaran</td>';
		echo '<td class="std" style="text-align:right;">'.number_format($Printing->total->TOT_EXP, 0, ',', '.').'</td>';
		echo '</tr>';
	
	echo '</tbody>';
	
	echo '<tfoot>';
	echo '<tr class="tr-total">';		
	echo '<td class="std" style="text-align:left;font-weight:bold;">Laba</td>';
	echo '<td class="std" style="text-align:right;font-weight:bold;" colspan=2></td>';
	echo '<td class="std" style="text-align:right;font-weight:bold;">'.number_format($Printing->total->TOT_LABA, 0, ',', '.').'</td>';
	echo '</tr>';
	echo '</tfoot>';
	echo '</table>';
	?>

