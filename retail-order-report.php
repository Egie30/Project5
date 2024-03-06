<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/pagination/pagination.php";


if (empty($_GET['BEG_DT'])) {
	$_GET['BEG_DT'] = date('Y-m-01');
}

if (empty($_GET['END_DT'])) {
	$_GET['END_DT'] = date('Y-m-d');
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/combobox/chosen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/pagination/pagination.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	
	<script type="text/javascript">parent.Pace.restart();</script>
	<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
	<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
	<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>
	<script type="text/javascript" src="framework/liveSearch/livesearch.js"></script>
	<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
	<script type="text/javascript" src="framework/pagination/pagination.js"></script>
	<script type="text/javascript" src='framework/tablesort/tablesort.js'></script>
	<script type="text/javascript" src="framework/uri/src/URI.min.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>
	<script type="text/javascript" src="framework/combobox/ajax-chosen/ajax-chosen.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.default.js"></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<script type="text/javascript" src='framework/jquery-freezeheader/js/jquery.freezeheader.js'></script>
	<script type="text/javascript">jQuery.noConflict();</script>
</head>
<body>
<div class="toolbar">
	<div class="combobox"></div>
	<div class="toolbar-text">
				
		<div style="display: inline-block; float: left; margin-top: 6px;">
			<span style="display: inline-block; float: left; margin-right: 15px;">
				<select name="IVC_TYP" id="IVC_TYP" style='width:150px;' class="chosen-select" >
					<option value="">Semua</option>
					<option value="SL" <?php if($_GET['IVC_TYP'] == "SL"){echo "selected";} ?>>Retail</option>
					<option value="PR" <?php if($_GET['IVC_TYP'] == "PR"){echo "selected";} ?>>Cetakan</option>
				</select>
			</span>
			
			<span style="display: inline-block; float: left; margin-right: 4px;">		
				<input id="BEG_DT" name="BEG_DT" value="<?php echo $_GET['BEG_DT'];?>" type="text" size="10" class="livesearch" style="text-align:center;margin-top:0" />
				<script>new CalendarEightysix('BEG_DT', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });</script>
				
			</span>	
			
			<span style="display: inline-block; float: left; margin-right: 4px;">		
				<input id="END_DT" name="END_DT" value="<?php echo $_GET['END_DT'];?>" type="text" size="10" class="livesearch" style="text-align:center;margin-top:0" />
				<script>new CalendarEightysix('END_DT', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });</script>
				
			</span>	
		
		</div>
		
		<div style="display: inline-block; float: left; margin-top: 7px; margin-right: 0px;">
			<span id="FLTR_DTE" class="fa fa-calendar toolbar fa-lg"  style="padding:3px;padding-left:10px;cursor:pointer"></span>
		</div>
		
		<div style="display: inline-block; float: right; margin-top: 0px; margin-right: 15px;">
			<a title="Export to Excel" href="report-excel.php?RPT_TYP=retail-order-report-excel&BEG_DT=<?php echo $_GET['BEG_DT'];?>&END_DT=<?php echo $_GET['END_DT'];?>&IVC_TYP=<?php echo $_GET['IVC_TYP']; ?>" target="_blank"><span class='fa fa-file-excel-o toolbar' style="cursor:pointer" onclick="location.href="></span></a>
			
			<span class='fa fa-search fa-flip-horizontal toolbar'></span><input type="text" id="livesearch" class="livesearch" />
		</div>
	</div>
</div>

<div class="searchresult" id="liveRequestResults"></div>
<br>
<div id="mainResult"></div>

<script type="text/javascript">
	function setDefaultQuery(url) {
		url.setQuery(URI.parseQuery(location.search));
		url.setQuery("IVC_TYP", document.getElementById("IVC_TYP").value);
		url.setQuery("BEG_DT", document.getElementById("BEG_DT").value);
		url.setQuery("END_DT", document.getElementById("END_DT").value);

		return url;
	}

	var url = setDefaultQuery(new URI("retail-order-report-ls.php"));

	url.setQuery("IVC_TYP", '<?php echo $_GET['IVC_TYP'];?>');
	url.setQuery("BEG_DT", '<?php echo $_GET['BEG_DT'];?>');
	url.setQuery("END_DT", '<?php echo $_GET['END_DT'];?>');
	getContent("mainResult", url.build().toString());

	document.getElementById("FLTR_DTE").onclick = function() {
		var url = setDefaultQuery(new URI("retail-order-report.php"));

		URI.removeQuery(url, "s");
		URI.removeQuery(url, "page");
	
		window.scrollTo(0,0);

		location.href = url.build().toString();
	};
</script>
<script type="text/javascript">
	var url = setDefaultQuery(new URI("retail-order-report-ls.php"));
	
	URI.removeQuery(url, "s");
	URI.removeQuery(url, "page");
	
	liveReqInit("livesearch", "liveRequestResults", url.build().toString(), "", "mainResult");
</script>
</body>
</html>