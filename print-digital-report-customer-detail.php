<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/combobox/chosen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/pagination/pagination.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	
	<script type="text/javascript">parent.Pace.restart();</script>
	<script type="text/javascript" src="framework/liveSearch/livesearch.js"></script>
	<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
	<script type="text/javascript" src="framework/pagination/pagination.js"></script>
	<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>	
	<script type="text/javascript" src="framework/uri/src/URI.min.js"></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<script type="text/javascript" src='framework/jquery-freezeheader/js/jquery.freezeheader.js'></script>
	<script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
	<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>
	<script type="text/javascript" src="framework/combobox/ajax-chosen/ajax-chosen.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.default.js"></script>
	<style>
	.std{
		border-top:1px solid grey;
	}
	</style>
</head>
<body>
<div class="toolbar">
	<p class="toolbar-left">
		<span style="display: inline-block; float: left; margin-right: 15px;padding-left:10px;padding-top:10px;">
		<select name="ORD_STT_ID" id="ORD_STT_ID" style='width:150px;' class="chosen-select" onchange="location.href='?BUY_CO_NBR=<?php echo $_GET['BUY_CO_NBR']; ?>&ORD_STT_ID=' + document.getElementById('ORD_STT_ID').value">
		<?php
			$query="SELECT ORD_STT_ID, ORD_STT_DESC, ORD_STT_ORD FROM CMP.PRN_DIG_STT ORDER BY ORD_STT_ORD";
			genCombo($query,"ORD_STT_ID","ORD_STT_DESC",$_GET['ORD_STT_ID'],"Pilih Status");
		?>
		</select>
		</span>
	</p>
	<p class="toolbar-right">
		<span class="fa fa-search fa-flip-horizontal toolbar"></span><input type="text" id="livesearch" class="livesearch" />
	</p>
</div>

<div class="searchresult" id="liveRequestResults"></div>
<div id="mainResult"></div>

<script type="text/javascript">
	
	$("#mainResult").load("print-digital-report-customer-detail-ls.php?BUY_CO_NBR=<?php echo $_GET['BUY_CO_NBR']; ?>&ORD_STT_ID=<?php echo $_GET['ORD_STT_ID']; ?>", function () {
        $("#mainTable").tablesorter({ widgets:["zebra"]});
    });
	
</script>

<script type="text/javascript">
	var url = new URI("print-digital-report-customer-detail-ls.php?BUY_CO_NBR=<?php echo $_GET['BUY_CO_NBR']; ?>&ORD_STT_ID=<?php echo $_GET['ORD_STT_ID']; ?>");
	
	URI.removeQuery(url, "s");
	
	liveReqInit("livesearch", "liveRequestResults", url.build().toString(), "", "mainResult");
</script>
</body>
</html>			
