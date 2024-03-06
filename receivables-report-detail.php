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
			<div class="toolbar-text">
			<input name="CO_NBR" id="CO_NBR" value="<?php echo $_GET['CO_NBR']; ?>" type="hidden" />
			<div style="display: inline-block; float: left; margin-top: 6px; margin-right: 15px;">
				<label>Bulan</label>&nbsp;
				<select id="MONTH" name="MONTH" style="width:150px" class="chosen-select">
					<?php
					$month = array("", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
					echo "<option value=''>Pilih Bulan</option>";
					for($y=1;$y<=12;$y++){
					if($y==$_GET['MONTH']){ $pilih="selected";}
					else {$pilih="";}
					echo("<option value=\"$y\" $pilih>$month[$y]</option>"."\n");
					}
					?>
				</select>
			</div>
			
			<div style="display: inline-block; float: left; margin-top: 6px; margin-right: 15px;">
				<select id="YEAR" name="YEAR" style="width:100px" class="chosen-select">
					<?php

					$now=date("Y");
					echo "<option value=''>Pilih Tahun</option>";
					for($y=$now;$y>=2015;$y--){
					if($y==$_GET['YEAR']){ $pilih="selected";}
					else {$pilih="";}
					echo("<option value=\"$y\" $pilih>$y</option>"."\n");
					}
					?>
				</select>
			</div>
			
			<div style="display: inline-block; float: left; margin-top: 6px; margin-right: 15px;">
				<span id="FLTR_DTE" class="fa fa-calendar toolbar fa-lg"  style="padding:3px; cursor:pointer"></span>
			</div>
			
			<div style="display: inline-block; float: right;">
				<a title="Export to Excel" href="report-excel.php?RPT_TYP=receivables-report-detail-excel&YEAR=<?php echo $_GET['YEAR']; ?>&MONTH=<?php echo $_GET['MONTH']; ?>&CO_NBR=<?php echo $_GET['CO_NBR']; ?>" target="_blank"><span class='fa fa-file-excel-o toolbar' style="cursor:pointer" onclick="location.href="></span></a>
				<span class='fa fa-search fa-flip-horizontal toolbar'></span><input type="text" id="livesearch" class="livesearch" />
			</div>
			
		</div>
	</div>

	<div class="searchresult" id="liveRequestResults"></div>
	<div id="mainResult"></div>
	
	<script type="text/javascript">
		function setDefaultQuery(url) {
			url.setQuery(URI.parseQuery(location.search));
			url.setQuery("MONTH", document.getElementById("MONTH").value);
			url.setQuery("YEAR", document.getElementById("YEAR").value);
			url.setQuery("CO_NBR", document.getElementById("CO_NBR").value);

			return url;
		}

		var url = setDefaultQuery(new URI("receivables-report-detail-ls.php"));

		url.setQuery("MONTH", '<?php echo $_GET['MONTH'];?>');
		url.setQuery("YEAR", '<?php echo $_GET['YEAR'];?>');
		url.setQuery("CO_NBR", '<?php echo $_GET['CO_NBR'];?>');
		getContent("mainResult", url.build().toString());

		document.getElementById("FLTR_DTE").onclick = function() {
			var url = setDefaultQuery(new URI("receivables-report-detail.php"));

			URI.removeQuery(url, "s");
			URI.removeQuery(url, "page");
		
			window.scrollTo(0,0);

			location.href = url.build().toString();
		};
	</script>
	<script type="text/javascript">
		var url = setDefaultQuery(new URI("receivables-report-detail-ls.php"));

		URI.removeQuery(url, "s");
		URI.removeQuery(url, "page");
		
		liveReqInit("livesearch", "liveRequestResults", url.build().toString(), "", "mainResult");
	</script>

</body>
</html>			
