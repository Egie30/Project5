<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/alert/alert.php";
	include "framework/functions/dotmatrix.php";
	$Security=getSecurity($_SESSION['userID'],"Inventory");
	$UpperSec=getSecurity($_SESSION['userID'],"Executive");
	$CashSec=getSecurity($_SESSION['userID'],"Finance");
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

<script type="text/javascript" src="framework/functions/default.js"></script>
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<script type="text/javascript" src='framework/tablesort/tablesort.js'></script>

<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />

<link rel="stylesheet" href="framework/combobox/chosen.css">

</head>

<body>
<?php// if(($Security==0)&&($OrdNbr!=0)) { ?>
<div class="toolbar-only">
		<p class="toolbar-right">
		<a href="calendar-report-rcap-print.php?CO_NBR=" onclick ="this.href+=document.getElementById('link').value"><img style="cursor:pointer" class="toolbar-right" src="img/print.png"></a>
		</p>
</div>
<?php //} ?>
<div style='margin:10px 0 10px 0'> 
	<label>Perusahaan</label>&nbsp;
	<select id="link" class="chosen-select"  onchange="getContent('report','calendar-report-rcap-disp.php?CO_NBR='+this.value);">
		<?php	
			$query="SELECT DISTINCT SEL_CO_NBR AS CODES FROM CMP.CAL_ORD_HEAD
					UNION ALL
					SELECT DISTINCT BUY_CO_NBR AS CODES FROM CMP.CAL_ORD_HEAD WHERE BUY_CO_NBR NOT IN (1,0) ";
			//echo $query;
			$result=mysql_query($query);
			while($row=mysql_fetch_array($result)){
				$codes[]=$row['CODES'];
			}
			$query="SELECT CO_NBR,NAME,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
					FROM CMP.COMPANY COM INNER JOIN CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID
					WHERE CO_NBR IN (".implode(",",$codes).")
					ORDER BY 2";
			//echo $query;
			genCombo($query,"CO_NBR","CO_DESC",1);
		?>
	</select>
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
</div>

<div id="report"></div>
<script>getContent('report','calendar-report-rcap-disp.php?CO_NBR=1');</script>
<br />
</body>
</html>
