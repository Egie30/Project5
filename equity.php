<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/pagination/pagination.php";

$bookNumber	= $_GET['BK_NBR'];

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
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	
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
	<script type="text/javascript" src='framework/jquery-freezeheader/js/jquery.freezeheader.js'></script>
	<script type="text/javascript">jQuery.noConflict();</script>
</head>
<body>
<div class="toolbar">
	<div class="combobox"></div>
	<div class="toolbar-text">
		<div class="tabContaier" style="display: inline-block; float: left; margin-top: 6px; margin-right: 0px;">
			<!-- Tab buttons -->
			<ul>
				<li><a class="active" href="#tab1"><div>Semua</div></a></li>
				<li><a href="#tab2"><div>PT</div></a></li>
				<li><a href="#tab3"><div>CV</div></a></li>
				<li><a href="#tab4"><div>PR</div></a></li>
			</ul>
			<!-- End Tab buttons -->			
		</div>
		
		<div style="display: inline-block; float: right; margin-top: 6px; margin-right: 15px;">
			<span style="padding-top: 4px;" class="fa fa-search fa-flip-horizontal toolbar"></span><input type="text" id="livesearch" class="livesearch" style="margin-top:0;"/>
		</div>
		
		<div style="display: inline-block; float: left; margin-top: 45px; margin-right: 0px;">
				<select name="BK_NBR" id="BK_NBR" style='width:150px' class="chosen-select" >
				<?php
					$query="SELECT BK_NBR, BEG_DTE, END_DTE, CONCAT(BEG_DTE, ' s/d ',END_DTE) AS TANGGAL, MONTH(BEG_DTE) AS BK_MONTH, YEAR(BEG_DTE) AS BK_YEAR 
							FROM RTL.ACCTG_BK WHERE DEL_NBR = 0 ORDER BY 3";
					
					$result = mysql_query($query);
					
					$bulan = array("", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");	
					
					while($row = mysql_fetch_array($result)) {
					
						if($row['BK_NBR'] == $bookNumber){ $pilih="selected";}
							else {$pilih="";}

							echo("<option value=".$row['BK_NBR']." ".$pilih.">".$bulan[$row['BK_MONTH']]." ".$row['BK_YEAR']."</option>"."\n");
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
	
	
	<!-- Tab content -->
    <div class="tabDetails">
    	<div id="tab1" class="tabContents"></div>
    	<div id="tab2" class="tabContents"></div>
    	<div id="tab3" class="tabContents"></div>
		<div id="tab4" class="tabContents"></div>
	</div>
	<!-- End Tab content -->

</div>

<script type="text/javascript">

	function setDefaultQuery(url) {
		url.setQuery(URI.parseQuery(location.search));
		url.setQuery("BK_NBR", document.getElementById("BK_NBR").value);
		return url;
	}

	var url = setDefaultQuery(new URI("equity-ls.php"));

		url.setQuery("ACTG", 0);
		getContent("tab1", url.build().toString());

		url.setQuery("ACTG", 1);
		getContent("tab2", url.build().toString());

		url.setQuery("ACTG", 2);
		getContent("tab3", url.build().toString());
		
		url.setQuery("ACTG", 3);
		getContent("tab4", url.build().toString());
	
	document.getElementById("FLTR_DTE").onclick = function() {
		var url = setDefaultQuery(new URI("equity.php"));

		URI.removeQuery(url, "s");
		URI.removeQuery(url, "page");
	
		window.scrollTo(0,0);

		location.href = url.build().toString();
	}

</script>
<script type="text/javascript">
	var url = setDefaultQuery(new URI("equity-ls.php"));

	URI.removeQuery(url, "s");
	URI.removeQuery(url, "page");
	
	liveReqInit("livesearch", "liveRequestResults", url.build().toString(), "", "mainResult");
	
</script>
</body>
</html>