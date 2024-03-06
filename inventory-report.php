<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/pagination/pagination.php";

if (empty($_GET['BEG_DT'])) {
	$_GET['BEG_DT'] = date("Y-m-d", strtotime("-1 day"));
}

if (empty($_GET['END_DT'])) {
	$_GET['END_DT'] = date("Y-m-d", strtotime("-1 day"));
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/combobox/chosen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/tab/tabs.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/pagination/pagination.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" />

	<script type="text/javascript">parent.Pace.restart();</script>
	<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
	<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
	<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>
	<script type="text/javascript" src="framework/liveSearch/livesearch.js"></script>
	<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
	<script type="text/javascript" src="framework/tab/tabs.js"></script>
	<script type="text/javascript" src="framework/pagination/pagination.js"></script>
	<script type="text/javascript" src="framework/tablesort/tablesort.js"></script>
	<script type="text/javascript" src="framework/tablesort/customsort.js"></script>
	<script type="text/javascript" src="framework/uri/src/URI.min.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>
	<script type="text/javascript" src="framework/combobox/ajax-chosen/ajax-chosen.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.default.js"></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<script type="text/javascript">jQuery.noConflict();</script>
</head>
<body>

<div class="toolbar">
	<div class="toolbar-text">
	<p class="toolbar-left">
		<span style="display: inline-block; float: left; margin-top: 6px; margin-right: 15px;">
		<?php buildComboboxLimit();?>
		</span>
		
		<span style="display: inline-block; float: left; margin-top: 6px; margin-right: 15px;">
			<input id="BEG_DT" name="BEG_DT" value="<?php echo $_GET['BEG_DT'];?>" type="text" size="10" class="livesearch" style="text-align:center;margin-top:0" />
			<script>new CalendarEightysix('BEG_DT', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });</script>
			
			<input id="END_DT" name="END_DT" value="<?php echo $_GET['END_DT'];?>" type="text" size="10" class="livesearch" style="text-align:center;margin-top:0" />
			<script>new CalendarEightysix('END_DT', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });</script>
			
			<span id="FLTR_DTE" class="fa fa-calendar toolbar fa-lg"  style="padding:3px; cursor:pointer"></span>
		</span>
		</p>
		<p class="toolbar-right"><span class='fa fa-search fa-flip-horizontal toolbar'></span><input type="text" id="livesearch" class="livesearch" /></p>
	</div>
</div>

<div class="searchresult" id="liveRequestResults"></div>
<br>
<div id="mainResult"></div>

<script type="text/javascript">
	function setDefaultQuery(url) {
		url.setQuery(URI.parseQuery(location.search));
		url.setQuery("LIMIT", document.getElementById("LIMIT").value);
		url.setQuery("BEG_DT", document.getElementById("BEG_DT").value);
		url.setQuery("END_DT", document.getElementById("END_DT").value);

		return url;
	}

	var url = setDefaultQuery(new URI("inventory-report-ls.php"));

	url.setQuery("LIMIT", '<?php echo $_GET['LIMIT'];?>');
	url.setQuery("BEG_DT", '<?php echo $_GET['BEG_DT'];?>');
	url.setQuery("END_DT", '<?php echo $_GET['END_DT'];?>');
	getContent("mainResult", url.build().toString());

	document.getElementById("FLTR_DTE").onclick = function() {
		var url = setDefaultQuery(new URI("inventory-report.php"));

		URI.removeQuery(url, "s");
		URI.removeQuery(url, "page");
	
		window.scrollTo(0,0);

		location.href = url.build().toString();
	};
</script>
<script type="text/javascript">
	var url = setDefaultQuery(new URI("inventory-report-ls.php"));
	
	URI.removeQuery(url, "s");
	URI.removeQuery(url, "page");
	
	liveReqInit("livesearch", "liveRequestResults", url.build().toString(), "", "mainResult");
</script>
</body>
</html>