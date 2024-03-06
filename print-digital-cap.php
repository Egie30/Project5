<?php
require_once "framework/database/connect.php";
	
if ($_GET['DEL_CAP_NBR']!="") {
	$query = "UPDATE CMP.PRN_DIG_CAP SET DEL_NBR=" . $_SESSION['personNBR'] . " WHERE CAP_NBR=" . $_GET['DEL_CAP_NBR'];
	$result = mysql_query($query);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/pagination/pagination.css" />

	<script type="text/javascript">parent.Pace.restart();</script>
	<script type="text/javascript" src="framework/liveSearch/livesearch.js"></script>
	<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
	<script type="text/javascript" src="framework/pagination/pagination.js"></script>
	<script type="text/javascript" src="framework/tablesort/tablesort.js"></script>
	<script type="text/javascript" src="framework/tablesort/customsort.js"></script>
	<script type="text/javascript" src="framework/uri/src/URI.min.js"></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<script type="text/javascript" src='framework/jquery-freezeheader/js/jquery.freezeheader.js'></script>
</head>
<body>
<div class="toolbar">
	<p class="toolbar-left"><a href="print-digital-cap-edit.php?CAP_NBR=-1"><img class="toolbar-left" src="img/add.png"></a></p>
	<p class="toolbar-right"><img class="toolbar-right" src="img/search.png"><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>
<div id="mainResult"></div>

<script type="text/javascript">
	var url = new URI("print-digital-cap-ls.php");

	url.setQuery(URI.parseQuery(location.search));
	
	getContent("mainResult", url.build().toString());
</script>
<script type="text/javascript">
	var url = new URI("print-digital-cap-ls.php");
	
	URI.removeQuery(url, "s");
	
	liveReqInit("livesearch", "liveRequestResults", url.build().toString(), "", "mainResult");
</script>
</body>
</html>			
