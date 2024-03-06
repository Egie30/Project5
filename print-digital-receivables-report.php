<?php
require_once "framework/database/connect.php";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/pagination/pagination.css" />
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
</head>
<body>
	<div class="toolbar">
		<p class="toolbar-left">&nbsp;</p>
		<p class="toolbar-right"><span class="fa fa-search fa-flip-horizontal toolbar"></span><input type="text" id="livesearch" class="livesearch" /></p>
	</div>

	<div class="searchresult" id="liveRequestResults"></div>
	<div id="mainResult"></div>

	<script type="text/javascript">
		
		$("#mainResult").load("print-digital-receivables-report-ls.php", function () {
			$("#mainTable").tablesorter({ widgets:["zebra"]});
		});
		
	</script>

	<script type="text/javascript">
		var url = new URI("print-digital-receivables-report-ls.php");
		
		URI.removeQuery(url, "s");
		
		liveReqInit("livesearch", "liveRequestResults", url.build().toString(), "", "mainResult");
	</script>
</body>
</html>			
