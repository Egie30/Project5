<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/pagination/pagination.php";

$StockYear		= $_GET['STK_YEAR'];


if (empty($StockYear)) {
		$_GET['STK_YEAR'] = date("Y");
	}
	else { $_GET['STK_YEAR'] = $StockYear; }
	
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
	<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
	<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
	<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>
	<script type="text/javascript" src="framework/liveSearch/livesearch.js"></script>
	<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
	<script type="text/javascript" src="framework/tab/tabs.js"></script>
	<script type="text/javascript" src="framework/pagination/pagination.js"></script>
	<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
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
		<div class="tabContaier" style="display: inline-block; float: left; margin-top: 6px; margin-right: 0px;">
			<?php if (!$_SESSION['PLUS_MODE']) { ?>
				<!-- Tab buttons -->
				<ul>
					<li><a class="active" href="#tab1"><div>Semua</div></a></li>
					<li><a href="#tab2"><div>PT</div></a></li>
					<li><a href="#tab3"><div>CV</div></a></li>
					<li><a href="#tab4"><div>PR</div></a></li>
				</ul>
				<!-- End Tab buttons -->
			<?php } ?>
		</div>
		
		<div style="display: inline-block; float: right; margin-top: 6px; margin-right: 15px;">
			<span style="padding-top: 4px;" class="fa fa-search fa-flip-horizontal toolbar"></span>
			<input type="text" id="livesearch" class="livesearch" style="margin-top:0;"/>
		</div>
		<div style="display: inline-block; float: left; margin-top: 45px; margin-right: 0px;">
				<select id="STK_YEAR" name="STK_YEAR" style="width:150px" class="chosen-select">
					<?php

					$now=date("Y");
					for($y=$now;$y>=2016;$y--){
					if($y==$_GET['STK_YEAR']){ $pilih="selected";}
					else {$pilih="";}
					echo("<option value=\"$y\" $pilih>$y</option>"."\n");
					}
					?>
				</select>
		</div>
		
		<div style="display: inline-block; float: left; margin-top: 45px; margin-right: 15px;">
			<span id="FLTR_DTE" class="fa fa-calendar toolbar fa-lg"  style="padding:3px;padding-left:10px;cursor:pointer"></span>
		</div>
		
	</div>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<br /><br />

<div id="mainResult" class="tabContaier" style="border:transparent;">
	<?php if (!$_SESSION['PLUS_MODE']) { ?>
	<!-- Tab content -->
    <div class="tabDetails">
    	<div id="tab1" class="tabContents"></div>
    	<div id="tab2" class="tabContents"></div>
    	<div id="tab3" class="tabContents"></div>
		<div id="tab4" class="tabContents"></div>
	</div>
	<!-- End Tab content -->
	<?php } ?>
</div>

<script type="text/javascript">

	function setDefaultQuery(url) {
		url.setQuery(URI.parseQuery(location.search));
		url.setQuery("STK_YEAR", document.getElementById("STK_YEAR").value);
		return url;
	}

	var url = setDefaultQuery(new URI("store-inventory-monthly-ls.php"));

		url.setQuery("ACTG", 0);
		getContent("tab1", url.build().toString());

		url.setQuery("ACTG", 1);
		getContent("tab2", url.build().toString());

		url.setQuery("ACTG", 2);
		getContent("tab3", url.build().toString());
		
		url.setQuery("ACTG", 3);
		getContent("tab4", url.build().toString());
	

	document.getElementById("FLTR_DTE").onclick = function() {
		var url = setDefaultQuery(new URI("store-inventory-monthly.php"));

		URI.removeQuery(url, "s");
		URI.removeQuery(url, "page");
	
		window.scrollTo(0,0);

		location.href = url.build().toString();
	};

</script>
<script type="text/javascript">
	var url = setDefaultQuery(new URI("store-inventory-monthly-ls.php"));

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