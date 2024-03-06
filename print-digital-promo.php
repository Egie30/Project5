<?php 
	include "framework/database/connect-cloud.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
<link rel="stylesheet" type="text/css" media="screen" href="framework/combobox/chosen.css" />

<script src="framework/database/jquery.min.js"></script>
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
<script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>
<script type="text/javascript" src="framework/combobox/chosen.default.js"></script>
<script type="text/javascript" src="framework/combobox/ajax-chosen/ajax-chosen.js"></script>	

</head>
<body>
	<div class="toolbar">
		<p class="toolbar-left">
			<?php 
				if((paramCloud()==1)){ 
					echo'<a href="payroll-config-dte-edit.php?PAY_CONFIG_NBR=0"><span class="fa fa-plus toolbar" style="cursor:pointer" onclick="location.href="></span></a>';
				} 
			?>

			<select id="FLTR_YEAR" name="FLTR_YEAR" style="width:150px" class="chosen-select">
			<?php
			$query = "SELECT  YEAR(PAY_BEG_DTE) AS YEAR
							FROM PAY.PAY_CONFIG_DTE 
							GROUP BY YEAR(PAY_BEG_DTE)";echo $query;
			genCombo($query, "YEAR", "YEAR", $_GET['FLTR_YEAR'],"Filter Tahun");
		?>
		</select>
		<span class="fa fa-calendar toolbar fa-lg" id="filter-by-date" style="padding-left:5px;margin-bottom:12px;cursor:pointer;display: none;"
				onclick="location.href='payroll-config-dte.php?FLTR_YEAR='+document.getElementById('FLTR_YEAR').value"></span>
		</p>

		<p class="toolbar-right">
			<span class='fa fa-search fa-flip-horizontal toolbar'></span><input type="text" id="livesearch" class="livesearch" />
		</p>
	</div>

	<div class="searchresult" id="liveRequestResults"></div>
	<div id="mainResult">
		<table id="mainTable" class="tablesorter searchTable">
			<thead>
				<tr>
					<th>No.</th>
					<th>Tanggal Awal</th>
					<th>Tanggal Akhir</th>
					<th>Status</th>
				</tr>
			</thead>
			<tbody>
				<?php 
					$query = "SELECT PAY_CONFIG_NBR,
									 PAY_BEG_DTE,
									 PAY_END_DTE,
									 (CASE 
									 	WHEN PAY_ACT_F =0 THEN 'Tidak Aktif'
									 	ELSE 'Aktif'
									 END) AS PAY_ACT_F
								FROM PAY.PAY_CONFIG_DTE
								WHERE YEAR(PAY_BEG_DTE) = ".$_GET['FLTR_YEAR']."
								ORDER BY PAY_BEG_DTE DESC";
					$result = mysql_query($query, $local);

					$alt = "";
					while ($row =mysql_fetch_array($result) ) {
						$link = "location.href='payroll-config-dte-edit.php?PAY_CONFIG_NBR=".$row['PAY_CONFIG_NBR']."';";

						echo "<tr $alt style='cursor:pointer;' onclick=".chr(34).$link.chr(34).">";
						echo "<td class='std-first' style='text-align:right;'>".$row['PAY_CONFIG_NBR']."</td>";
						echo "<td class='std' style='text-align:center;'>".$row['PAY_BEG_DTE']."</td>";
						echo "<td class='std' style='text-align:center;'>".$row['PAY_END_DTE']."</td>";
						echo "<td class='std'>".$row['PAY_ACT_F']."</td>";
						echo "</tr>";
					}
				?>
			</tbody>
		</table>
	</div>
</body>
<script type="text/javascript">
	$(document).ready(function()
		{
			$("#mainTable").tablesorter({ widgets:["zebra"]});  
		}
	);
</script>
<script type="text/javascript">
	$(document).ready(function () {
		$('#FLTR_YEAR').change(function(){
			$('#filter-by-date').click();
		});
	});
</script>
<script type="text/javascript">
	liveReqInit('livesearch','liveRequestResults','payroll-config-dte-ls.php?YEAR=<?php echo $year;?>','','mainResult');
</script>
</html>