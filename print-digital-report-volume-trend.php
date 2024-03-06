<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/alert/alert.php";
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">        	
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<link rel="stylesheet" href="framework/combobox/chosen.css">
</head>
<body>
	</br>
	<table style='margin-left:auto;margin-right:auto;width:750px;'>
		<tr style='border:0px;'><td style='border:0px;vertical-align:text-bottom'>
			Pilih customer &nbsp;&nbsp;<select id="Number" style="width:550px" class="chosen-select" onChange="document.getElementById('Chart').src='print-digital-report-volume-trend-chart.php?TYPE='+this.value;" >
			<?php
				$query="SELECT PRN_DIG_TYP, PRN_DIG_DESC FROM CMP.PRN_DIG_TYP WHERE PRN_DIG_DESC<>'' ORDER BY PRN_DIG_DESC";
				genCombo($query,"PRN_DIG_TYP","PRN_DIG_DESC","PRN_DIG_TYP");
			?>
			</select></br>
		</td></tr>
	</table><br/>
	<?php
		$query_first	= "SELECT PRN_DIG_TYP, PRN_DIG_DESC FROM CMP.PRN_DIG_TYP WHERE PRN_DIG_DESC<>'' ORDER BY PRN_DIG_DESC LIMIT 1";
		$result_first	= mysql_query($query_first);
		$row_first		= mysql_fetch_array($result_first);
		
	?>
	<iframe id="Chart" src="print-digital-report-volume-trend-chart.php?TYPE=<?php echo $row_first['PRN_DIG_TYP']; ?>" style="height:400px"></iframe>	
	
	<script src="framework/database/jquery.min.js" type="text/javascript"></script>
	<script src="framework/combobox/chosen.jquery.js" type="text/javascript"></script>
	<script type="text/javascript">
		var config = {
			'.chosen-select'           : {},
			'.chosen-select-deselect'  : {allow_single_deselect:true},
			'.chosen-select-no-single' : {disable_search_threshold:10},
			'.chosen-select-no-results': {no_results_text:'Data tidak ketemu'},
			'.chosen-select-width'     : {width:"95%"}
	   	}
		for (var selector in config) {
			$(selector).chosen(config[selector]);
		}
	</script>
</body>
</html>