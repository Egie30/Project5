<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/pagination/pagination.php";

$type 			= $_GET['TYPE'];

if(empty($_GET['TYPE'])) { 
	$type = ""; 
}else {	
	$type = $_GET['TYPE'];
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/combobox/chosen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/pagination/pagination.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">

	<script type="text/javascript">parent.Pace.restart();</script>
	<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
	<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
	<script type="text/javascript" src="framework/liveSearch/livesearch.js"></script>
	<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
	<script type="text/javascript" src="framework/pagination/pagination.js"></script>
	<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
	<script type="text/javascript" src="framework/uri/src/URI.min.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>
	<script type="text/javascript" src="framework/combobox/ajax-chosen/ajax-chosen.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.default.js"></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<script type="text/javascript">jQuery.noConflict();</script>
	<script>
	window.addEvent('domready',function() {
		$('FLTR_DTE').addEvent('click',function() {
			location.href = "?TYPE=" + document.getElementById('TYPE').value;
		});
	});
	</script>
</head>
<body>

<div class="toolbar">
	<div class="combobox"></div>
	<div class="toolbar-text">
		<div style="display: inline-block; float: left; margin-top: 6px; margin-right: 15px;">
			<select id="TYPE" name="TYPE" class="chosen-select" style="width:150px;">
				<option value="" selected="">Semua</option>
				<option value="1" <?php if($type == "1"){echo "selected";}else{ echo ""; } ?>>1 Bulan</option>
				<option value="2" <?php if($type == "2"){echo "selected";}else{ echo ""; } ?>>2 Bulan</option>
			</select>
		</div>
		
		<div style="display: inline-block; float: left; margin-top: 6px; margin-right: 15px;">
			<span id="FLTR_DTE" class="fa fa-calendar toolbar fa-lg"  style="padding:3px; cursor:pointer"></span>
		</div>
		
		<div style="display: inline-block; float: right; margin-right: 15px;">
			<span class='fa fa-search fa-flip-horizontal toolbar'></span><input type="text" id="livesearch" class="livesearch" />
		</div>
	</div>
</div>


<div class="searchresult" id="liveRequestResults"></div>
<div id="mainResult"></div>

<script type="text/javascript">
	var url = new URI("print-digital-customer-report-ls.php?TYPE=<?php echo $type;?>");

	url.setQuery(URI.parseQuery(location.search));
	
	getContent("mainResult", url.build().toString());
	
	jQuery(document).ready(function($)
		{
			$("#mainTable").tablesorter({ widgets:["zebra"]});  
		}
	);
</script>
<script type="text/javascript">
	var url = new URI("print-digital-customer-report-ls.php?TYPE=<?php echo $type;?>");
	
	URI.removeQuery(url, "s");
	
	liveReqInit("livesearch", "liveRequestResults", url.build().toString(), "", "mainResult");
</script>
</body>
</html>			
