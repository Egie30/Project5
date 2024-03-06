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
		<?php
		$query = "SELECT CAT.CD_CAT_DESC, 
			CONCAT(CAT.CD_CAT_NBR, '-', ACC.CD_ACC_NBR, SUB.CD_SUB_ACC_NBR) AS ACC_NBR,
			CONCAT(CAT.CD_CAT_DESC, ' - ', ACC.CD_DESC, ' :: ', SUB.CD_SUB_DESC) AS ACC_DESC
		FROM RTL.ACCTG_CD_SUB SUB
			INNER JOIN RTL.ACCTG_CD ACC ON ACC.CD_NBR=SUB.CD_NBR
			INNER JOIN RTL.ACCTG_CD_CAT CAT ON CAT.CD_CAT_NBR=ACC.CD_CAT_NBR
			LEFT OUTER JOIN RTL.ACCTG_TB TB ON TB.CD_SUB_NBR=SUB.CD_SUB_NBR";

		if ($_GET['CD_SUB_NBR']) {
			$query .= " WHERE SUB.CD_SUB_NBR=" . $_GET['CD_SUB_NBR'];
		} else {
			$query .= " WHERE CAT.CD_CAT_NBR=" . $_GET['CD_CAT_NBR'];
		}

		$result = mysql_query($query);
		$row = mysql_fetch_array($result);

		if ($_GET['CD_SUB_NBR']) {
			echo "<b>" . $row['ACC_NBR'] . " " . $row['ACC_DESC'] . "</b>";
		} else {
			echo "<b>" . $row['CD_CAT_DESC'] . "</b>";
		}
		?>
		
		<input type="hidden" id="BK_NBR" name="BK_NBR" value=<?php echo $bookNumber; ?> >
	</div>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<br /><br />

<div id="mainResult" class="tabContaier">
	<!-- Tab buttons -->
	<ul>
    	<li><a class="active" href="#tab1"><div>Semua</div></a></li>
    	<li><a href="#tab2"><div>PT</div></a></li>
    	<li><a href="#tab3"><div>CV</div></a></li>
		<li><a href="#tab4"><div>PR</div></a></li>
    </ul>
	<!-- End Tab buttons -->
	
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

	var url = setDefaultQuery(new URI("general-ledger-detail-ls.php"));

		url.setQuery("ACTG", 0);
		getContent("tab1", url.build().toString());

		url.setQuery("ACTG", 1);
		getContent("tab2", url.build().toString());

		url.setQuery("ACTG", 2);
		getContent("tab3", url.build().toString());
		
		url.setQuery("ACTG", 3);
		getContent("tab4", url.build().toString());
	
	document.getElementById("FLTR_DTE").onclick = function() {
		var url = setDefaultQuery(new URI("general-ledger-detail.php"));

		URI.removeQuery(url, "s");
		URI.removeQuery(url, "page");
	
		window.scrollTo(0,0);

		location.href = url.build().toString();
	}

</script>
<script type="text/javascript">
	var url = setDefaultQuery(new URI("general-ledger-detail-ls.php"));

	URI.removeQuery(url, "s");
	URI.removeQuery(url, "page");
	
	liveReqInit("livesearch", "liveRequestResults", url.build().toString(), "", "mainResult");
	
</script>

<script>
	$(document).ready(function()
		{
			$("#mainResult").tablesorter({ widgets:["zebra"]});  
		}
	);
</script>

</body>
</html>