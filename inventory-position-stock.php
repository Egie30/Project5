<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/pagination/pagination.php";

if ($_GET['CO_NBR'] == "") {
	$_GET['CO_NBR'] = $CoNbrDef;
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
	<link rel="stylesheet" type="text/css" media="screen" href="framework/pagination/pagination.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/accounting.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">

	<script type="text/javascript">parent.Pace.restart();</script>
	<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
	<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
	<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
	<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>
	<script type="text/javascript" src="framework/liveSearch/livesearch.js"></script>
	<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
	<script type="text/javascript" src="framework/tab/tabs.js"></script>
	<script type="text/javascript" src="framework/pagination/pagination.js"></script>
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
		<p class="toolbar-left">
		<label>Lokasi</label>&nbsp;
		<select id="CO_NBR" name="CO_NBR" class="chosen-select" style="width: 300px">
			<?php
			$query = "SELECT 
				COM.CO_NBR,
				COM.NAME AS CO_NAME 
			FROM CMP.COMPANY COM
			WHERE DEL_NBR = 0 AND CO_NBR IN (SELECT CO_NBR FROM NST.PARAM_PAYROLL)";
			genCombo($query, "CO_NBR", "CO_NAME", $_GET['CO_NBR'], "Semua");
			?>
		</select>
		<?php buildComboboxLimit();?>

		<input id="END_DT" name="END_DT" value="<?php echo $_GET['END_DT']; ?>" type="text" size="10" class="livesearch" style="text-align:center" />
		<script>
			new CalendarEightysix('END_DT', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
		</script>
		<span id="FLTR_DTE" class="fa fa-calendar toolbar fa-lg" style="padding-left:0px;cursor:pointer" title="Filter berdasarkan tanggal"></span>
		</p>
		
		<p class="toolbar-right">
			<span class='fa fa-search fa-flip-horizontal toolbar' style='cursor:default'></span><input type="text" id="livesearch" class="livesearch" />
		</p>
	</div>
</div><br>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult"></div>

<script type="text/javascript">
	function setDefaultQuery(url) {
		url.setQuery(URI.parseQuery(location.search));
		url.setQuery("LIMIT", document.getElementById("LIMIT").value);
		url.setQuery("CO_NBR", document.getElementById("CO_NBR").value);
		url.setQuery("END_DT", document.getElementById("END_DT").value);

		return url;
	}

	var url = setDefaultQuery(new URI("inventory-position-stock-ls.php"));

	url.setQuery("LIMIT", '<?php echo $_GET['LIMIT'];?>');
	url.setQuery("CO_NBR", '<?php echo $_GET['CO_NBR'];?>');
	url.setQuery("END_DT", '<?php echo $_GET['END_DT'];?>');
	getContent("mainResult", url.build().toString());

	document.getElementById("FLTR_DTE").onclick = function() {
		var url = setDefaultQuery(new URI("inventory-position-stock.php"));

		URI.removeQuery(url, "s");
		URI.removeQuery(url, "page");
	
		window.scrollTo(0,0);

		location.href = url.build().toString();
	};

	document.getElementById("EXPORT_EXCEL").onclick = function() {
		var url = setDefaultQuery(new URI("report-excel.php"));
		
		window.scrollTo(0,0);

		url.setQuery("s", document.getElementById("livesearch").value);
		url.setQuery("page", jQuery("#pagination-container").length > 0 ? jQuery("#pagination-container").attr("data-page") : 1);
		url.setQuery("RPT_TYP", "inventory-position-excel");

		window.open(url.build().toString(), "_blank");
	};
</script>

<script type="text/javascript">
	var url = setDefaultQuery(new URI("inventory-position-stock-ls.php"));

	URI.removeQuery(url, "s");
	URI.removeQuery(url, "page");
	
	liveReqInit("livesearch", "liveRequestResults", url.build().toString(), "", "mainResult");
	
	jQuery(document).ready(function(){		
		setTimeout(function(){			
			jQuery("table.table-accounting").tablesorter({ widgets:["zebra"]});  		
		},500);
	});
</script>
</body>
</html>