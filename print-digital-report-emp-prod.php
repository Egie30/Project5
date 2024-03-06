<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/alert/alert.php";
	$query 		= "SELECT CO_NBR_CMPST FROM NST.PARAM_PAYROLL WHERE CO_NBR=".$CoNbrDef."";
	$result 	= mysql_query($query); 
	$row 		= mysql_fetch_array($result);	
	$company 	= $row['CO_NBR_CMPST'];
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
			Employee Name &nbsp;&nbsp;<select id="Number" style="width:550px" class="chosen-select" onChange="document.getElementById('Chart2').src='print-digital-report-emp-prod-chart.php?NBR='+this.value;" >
			<?php
				if($_GET["NBR"]==""){$SlsPrsnNbr="";}else{$SlsPrsnNbr=$_GET["NBR"];}
				//$query="SELECT PRSN_NBR,NAME FROM CMP.PEOPLE WHERE CO_NBR IN (".$CoNbrPay.") AND TERM_DTE IS NULL";
				$query="SELECT
					PRSN_NBR, NAME
					FROM CMP.PEOPLE
					WHERE TERM_DTE IS NULL AND DEL_NBR = 0 AND POS_TYP IN ('SNM','RAM','NAM','CMA','DPG') AND CO_NBR IN (".$company.")";
				genCombo($query,"PRSN_NBR","NAME",$SlsPrsnNbr,"Corporate");
			?>
			</select></br><?php //echo $query; ?>
		</td></tr>
	</table><br/>
	<iframe id="Chart2" src="print-digital-report-emp-prod-chart.php" style="height:500px"></iframe>
	
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