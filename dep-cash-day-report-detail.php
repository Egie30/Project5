<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/pagination/pagination.php";

$BegDt		= $_GET['BEG_DT'];
$EndDt		= $_GET['END_DT'];
$DepDate 	= $_GET['DEP_DTE'];
$SNbr 		= $_GET['S_NBR'];
$Type		= $_GET['TYP'];

if ($Type == 'CSH') { $field = "CSH_AMT"; }
	else { $field = "CHK_AMT"; }
	
	
if ($_POST['submit'] != "") {

	$query_upd	= "UPDATE RTL.CSH_DAY 
				SET VRFD_F = 1,
					VRFD_TS = CURRENT_TIMESTAMP, 
					VRFD_NBR = ".$_SESSION['personNBR']."
				WHERE DEP_DTE = '".$_POST['DEP_DTE']."'
				";
		
	$result_upd	= mysql_query($query_upd);

}


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/accounting.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	
	<script type="text/javascript">parent.Pace.restart();</script>
	<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
	<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<script type="text/javascript">jQuery.noConflict();</script>
</head>

</head>
<body>

<div style="width:100%" id="mainResult">
	<table class="table-accounting tablesorter">
	<thead>
		<tr>
			<th class="sortable" style="text-align:center;">Tanggal Setoran Bank</th>
			<th class="sortable" style="text-align:center;">Tanggal Setoran Kasir</th>
			<th class="sortable" style="text-align:center;">Shift</th>
			<th class="sortable" style="text-align:center;">Rek 1</th>
			<th class="sortable" style="text-align:center;">Rek 2</th>
			<th class="sortable" style="text-align:center;">Rek 3</th>
			<th class="sortable" style="text-align:center;">Rek 4</th>
			<th class="sortable" style="text-align:center;">Total</th>
			<th class="sortable" style="text-align:center;">Keterangan</th>
			<th class="sortable" style="text-align:center;">Verifikasi</th>

		</tr>
	</thead>
	<tbody>
	<?php
	
		$query	= "SELECT 
				DATE(CSH.DEP_DTE) AS DEP_DTE,
				DATE(CSH.CSH_DAY_DTE) AS CSH_DAY_DTE,
				CSH.S_NBR,
				SUM(CASE WHEN ACCT = 'PT' THEN ".$field." ELSE 0 END) AS CSH_PT,
				SUM(CASE WHEN ACCT = 'CV' THEN ".$field." ELSE 0 END) AS CSH_CV,
				SUM(CASE WHEN ACCT = 'PR' THEN ".$field." ELSE 0 END) AS CSH_PR,
				SUM(CASE WHEN ACCT = 'AD' THEN ".$field." ELSE 0 END) AS CSH_AD,
				SUM(".$field.") AS CSH_AMT,
				(CASE WHEN '".$Type."' = 'CHK' THEN 'Cek/Giro' ELSE 'Uang Kontan' END) AS KET,
				CSH.VRFD_F
			FROM RTL.CSH_DAY CSH
			WHERE CSH.DEP_DTE = '".$DepDate."'
				AND CSH.DEL_NBR = 0
			GROUP BY CSH.CSH_DAY_DTE, CSH.S_NBR
			";
		
		//echo $query;
		
		$result	= mysql_query($query);
	
		$alt="";
			while ($row = mysql_fetch_array($result)) {
			
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='cash-day-report-edit.php?CSH_DAY_DTE=".$row['CSH_DAY_DTE']."&CSH_DAY_NBR=".$row['CSH_DAY_NBR']."&S_NBR=".$row['S_NBR']."';".chr(34).">";
				echo "<td style='text-align:center;'>".$row['DEP_DTE']."</td>";
				echo "<td style='text-align:center'>".$row['CSH_DAY_DTE']."</td>";
				echo "<td style='text-align:center;'>".$row['S_NBR']."</td>";
				echo "<td style='text-align:right'>".number_format($row['CSH_PT'],0,'.','.')."</td>";
				echo "<td style='text-align:right'>".number_format($row['CSH_CV'],0,'.','.')."</td>";
				echo "<td style='text-align:right'>".number_format($row['CSH_PR'],0,'.','.')."</td>";
				echo "<td style='text-align:right'>".number_format($row['CSH_AD'],0,'.','.')."</td>";
				echo "<td style='text-align:right'>".number_format($row['CSH_AMT'],0,'.','.')."</td>";
				echo "<td>".$row['KET']."</td>";
	?>
				<td style='text-align:center'><input disabled name='VRFD_F' id='VRFD_F'  type='checkbox' class='regular-checkbox'  <?php if($row['VRFD_F'] =="1"){ echo "checked"; } ?> />&nbsp;
				<label for='VRFD_F'></label></td>
	<?php 
				echo "</tr>";
				echo "</tbody>";
				
				$rek1	+= $row['CSH_PT'];
				$rek2	+= $row['CSH_CV'];
				$rek3	+= $row['CSH_PR'];
				$rek4	+= $row['CSH_AD'];
				$total	+= $row['CSH_AMT'];
			
			}
			echo "<tfoot>";
				echo "<tr class='tr-total'>";
				echo "<td colspan=3 style='text-align:right;font-weight:bold;'>Total Setoran</td>";
				echo "<td style='text-align:right;font-weight:bold;'>".number_format($rek1,0,'.','.')."</td>";
				echo "<td style='text-align:right;font-weight:bold;'>".number_format($rek2,0,'.','.')."</td>";
				echo "<td style='text-align:right;font-weight:bold;'>".number_format($rek3,0,'.','.')."</td>";
				echo "<td style='text-align:right;font-weight:bold;'>".number_format($rek4,0,'.','.')."</td>";
				echo "<td style='text-align:right;font-weight:bold;'>".number_format($total,0,'.','.')."</td>";
				echo "</tr>";
			echo "</tfoot>";

	?>
	
</table>


<form enctype="multipart/form-data" action="#" method="post">
	<?php 
		echo '<input type="hidden" name="DEP_DTE" value="'.$DepDate.'">';
		echo '<input type="hidden" name="S_NBR" value="'.$SNbr.'">';
	?>
	<input class='process' name="submit" type='submit' value='Verifikasi'/>
</form>
</div>


<script type="text/javascript">
	
	jQuery(document).ready(function(){		
		setTimeout(function(){			
			jQuery("table.table-accounting").tablesorter({ widgets:["zebra"]});  		
		},500);		
	});

</script>

</body>
</html>