<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/pagination/pagination.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
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
</head>
<body>
<div class="toolbar">
	<p class="toolbar-left">&nbsp;</p>
	<p class="toolbar-right">
		<!--
		<a title="Export to Excel" href="report-excel.php?RPT_TYP=print-digital-status-report-excel&CO_NBR=<?php echo $_GET['CO_NBR']; ?>" target="_blank"><span class='fa fa-file-excel-o toolbar' style="cursor:pointer" onclick="location.href="></span></a>
		-->
		<a title="Export to Excel" href="#" id="EXPORT_EXCEL">
					<img style="padding:3px;cursor:pointer" class="toolbar-right" src="img/excelicon16.png" style="padding: 5px;">
				</a>
		<span class="fa fa-search fa-flip-horizontal toolbar"></span><input type="text" id="livesearch" class="livesearch" />
	</p>
</div>

<div class="searchresult" id="liveRequestResults"></div>
<div id="mainResult"></div>
<!--
<script type="text/javascript">
	
	$("#mainResult").load("print-digital-status-report-ls.php", function () {
        $("#mainTable").tablesorter({ widgets:["zebra"]});
    });
	
</script>

<script type="text/javascript">
	var url = new URI("print-digital-status-report-ls.php");
	
	URI.removeQuery(url, "s");
	
	liveReqInit("livesearch", "liveRequestResults", url.build().toString(), "", "mainResult");
</script>
-->

	<script type="text/javascript">
		function setDefaultQuery(url) {
			url.setQuery(URI.parseQuery(location.search));
			return url;
		}

		var url = setDefaultQuery(new URI("print-digital-status-report-ls.php"));

		getContent("mainResult", url.build().toString());

		document.getElementById("EXPORT_EXCEL").onclick = function() {
			var url = setDefaultQuery(new URI("report-excel.php"));
			
			window.scrollTo(0,0);

			url.setQuery("s", document.getElementById("livesearch").value);
			url.setQuery("RPT_TYP", "print-digital-status-report-excel");

			window.open(url.build().toString(), "_blank");
		};
	</script>
	<script type="text/javascript">
		var url = setDefaultQuery(new URI("print-digital-status-report-ls.php"));

		URI.removeQuery(url, "s");
		URI.removeQuery(url, "page");
		
		liveReqInit("livesearch", "liveRequestResults", url.build().toString(), "", "mainResult");
	</script>
</body>
</html>			
