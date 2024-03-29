<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/alert/alert.php";
	$Security=getSecurity($_SESSION['userID'],"Inventory");
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

<div class="toolbar">
	<div class="combobox"></div>
	<div class="toolbar-text">
		<label>Perusahaan</label>&nbsp;
		<select class="chosen-select" onchange="getContent('report','inventory-report-defc-disp.php?CO_NBR='+this.value);">
			<?php
				$query="SELECT DISTINCT CO_NBR FROM CMP.INVENTORY";
				$result=mysql_query($query);
				while($row=mysql_fetch_array($result))
				{
					$codes[]=$row['CO_NBR'];
				}
		
				$query="SELECT CO_NBR,NAME,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
						FROM CMP.COMPANY COM INNER JOIN CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID
						WHERE CO_NBR IN (".implode(",",$codes).")
						ORDER BY 2";
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
</div>

<div id="report"></div>
<script>getContent('report','inventory-report-defc-disp.php?CO_NBR=1');</script>
<br />

</body>
</html>
