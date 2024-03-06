<?php
require_once "framework/database/connect-cloud.php";
//require_once "framework/database/connect.php";
$T 	= $_GET['T'];
$D 	= $_GET['D'];
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/pagination/pagination.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/combobox/chosen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">

	<script type="text/javascript" src="framework/liveSearch/livesearch.js"></script>
	<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
	<script type="text/javascript" src="framework/pagination/pagination.js"></script>
	<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
	<script type="text/javascript" src="framework/uri/src/URI.min.js"></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<script type="text/javascript" src='framework/jquery-freezeheader/js/jquery.freezeheader.js'></script>
	<script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.default.js"></script>
	
	</head>
<body>
	<div class="toolbar">
		<p class='toolbar-left'>
			<a href='cloud-list-all-edit.php?T=<?php echo $T ?>&&D=<?php echo $D ?>'><span class="fa  fa-plus toolbar" style="cursor:pointer"></span></a>
		</p>
		<p class="toolbar-right" style="position:relative">
			<span class='fa fa-search fa-flip-horizontal toolbar'></span>
			<input type="text" id="livesearch" class="livesearch"/>
		</p>
	</div>
	<div class="searchresult" id="liveRequestResults"></div>
	<div id="mainResult" class="toolsub"></div>
</body>
<script type="text/javascript">
	var url = new URI("cloud-list-all-ls.php");
	
	url.setQuery(URI.parseQuery(location.search));	
	
	$("#mainResult").load(url.build().toString(), function () {
        $("#mainTable").tablesorter({ widgets:["zebra"]});
    });
	//getContent("mainResult", url.build().toString());
</script>

<script type="text/javascript">
	var url = new URI("cloud-list-all-ls.php");
	function setDefaultQuery(url) {
		url.setQuery(URI.parseQuery(location.search));
		url.setQuery("T", document.getElementById("T").value);
		url.setQuery("D", document.getElementById("D").value);

		return url;
	}
	url.setQuery("T", '<?php echo $_GET['T'];?>');
	url.setQuery("D", '<?php echo $_GET['D'];?>');
	URI.removeQuery(url, "s");
	
	liveReqInit("livesearch", "liveRequestResults",url.build(). toString(), "", "mainResult");
</script>
</html>			