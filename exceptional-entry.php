<?php
error_reporting(0);
if($_GET['DEL_L']!=""){
	include "framework/database/connect-cloud.php";
}else{
	include "framework/database/connect.php";
}
include "framework/functions/default.php";
include "framework/security/default.php";

$Payroll = getSecurity($_SESSION['userID'],"Payroll");

ini_set('max_execution_time',-1);

if ($_GET['FLTR_DTE']==''){
	$_GET['FLTR_DTE']=date('n Y');
}
$filter_date=str_replace("+"," ",$_GET['FLTR_DTE']);
	
if ($_GET['DEL_L']!="") {
	$querys  = "SELECT * FROM PAY.EXCPTN_ETRY WHERE EXCPTN_ETRY_NBR=".$_GET['DEL_L'];
	$results = mysql_query($querys,$local);
	$rows   = mysql_fetch_array($results);
	//echo $query;
	$query  = "UPDATE $PAY.EXCPTN_ETRY SET UPD_TS=CURRENT_TIMESTAMP, DEL_NBR=" . $_SESSION['personNBR'] . "  WHERE EXCPTN_ETRY_NBR=" . $_GET['DEL_L'];
	$result=mysql_query($query,$cloud);
	$query=str_replace($PAY,"PAY",$query);
	$result=mysql_query($query,$local);
	
	$query  = "UPDATE $PAY.ATND_CLOK SET DEL_NBR=".$_SESSION['personNBR'].", UPD_TS=CURRENT_TIMESTAMP WHERE PRSN_NBR=".$rows['PRSN_NBR']." AND CRT_TS='".$rows['EXCPTN_ETRY_TS']."'"; 
	$result=mysql_query($query,$cloud);
	$query=str_replace($PAY,"PAY",$query);
	$result=mysql_query($query,$local);
	//echo $query;
	
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/pagination/pagination.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" media="screen" href="framework/combobox/chosen.css" />

	<script type="text/javascript">parent.Pace.restart();</script>
	<script type="text/javascript" src="framework/liveSearch/livesearch.js"></script>
	<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
	<script type="text/javascript" src="framework/pagination/pagination.js"></script>
	<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
	<script type="text/javascript" src="framework/uri/src/URI.min.js"></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<script type="text/javascript" src='framework/jquery-freezeheader/js/jquery.freezeheader.js'></script>
	<script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.default.js"></script>
	<script type="text/javascript" src="framework/combobox/ajax-chosen/ajax-chosen.js"></script>

</head>
<body>
<div class="toolbar">
	<p class="toolbar-left">
		<?php if ($Payroll<=2){?>
		<a href="exceptional-entry-edit.php?EXCPTN_ETRY_NBR=-1"><span  class="fa fa-plus toolbar" style="cursor:pointer" ></span></a>
		<?php }?>
		<select id="RCV_DATE" name="RCV_DATE" style="width:150px" class="chosen-select">
		<?php
			
			$query_dte	= "SELECT  EXCPTN_ETRY_TS,CONCAT(MONTH(EXCPTN_ETRY_TS),' ',YEAR(EXCPTN_ETRY_TS)) AS DTE,
				CONCAT(CASE 
					WHEN MONTH(EXCPTN_ETRY_TS)='1' THEN 'Januari'
					WHEN MONTH(EXCPTN_ETRY_TS)='2' THEN 'Februari'
					WHEN MONTH(EXCPTN_ETRY_TS)='3' THEN 'Maret'
					WHEN MONTH(EXCPTN_ETRY_TS)='4' THEN 'April'
					WHEN MONTH(EXCPTN_ETRY_TS)='5' THEN 'Mei'
					WHEN MONTH(EXCPTN_ETRY_TS)='6' THEN 'Juni'
					WHEN MONTH(EXCPTN_ETRY_TS)='7' THEN 'Juli'
					WHEN MONTH(EXCPTN_ETRY_TS)='8' THEN 'Agustus'
					WHEN MONTH(EXCPTN_ETRY_TS)='9' THEN 'September'
					WHEN MONTH(EXCPTN_ETRY_TS)='10' THEN 'Oktober'
					WHEN MONTH(EXCPTN_ETRY_TS)='11' THEN 'November'
					WHEN MONTH(EXCPTN_ETRY_TS)='12' THEN 'Desember'
				END,' ',YEAR(EXCPTN_ETRY_TS)) AS DTE_DESC
			FROM PAY.EXCPTN_ETRY 
			WHERE DEL_NBR=0 
			GROUP BY YEAR(EXCPTN_ETRY_TS),MONTH(EXCPTN_ETRY_TS)";
			genCombo($query_dte, "DTE", "DTE_DESC", $filter_date,"Filter Bulan Tahun");
		?>
		</select>
		<span id="FLTR_DTE" class="fa fa-calendar toolbar fa-lg" id="filter-by-date" style="padding-left:5px;margin-bottom:12px;cursor:pointer">
		</span>
	</p>
	<p class="toolbar-right"><span class="fa fa-search fa-flip-horizontal toolbar"></span><input type="text" id="livesearch" class="livesearch" /></p>
</div>
<div class="searchresult" id="liveRequestResults"></div>
<div id="mainResult"></div>

<script type="text/javascript">

	function setDefaultQuery(url) {
		url.setQuery(URI.parseQuery(location.search));
		url.setQuery("FLTR_DTE", document.getElementById("RCV_DATE").value);
		return url;
	}

    document.getElementById("FLTR_DTE").onclick = function() {
		var url = setDefaultQuery(new URI("exceptional-entry.php"));

		URI.removeQuery(url, "s");
		// URI.removeQuery(url, "page");
	
		window.scrollTo(0,0);

		location.href = url.build().toString();
	};

	var url = setDefaultQuery(new URI("exceptional-entry-ls.php"));

	$("#mainResult").load(url.build().toString(), function () {
        $("#mainTable").tablesorter({ widgets:["zebra"]});
    });
	
</script>

<script type="text/javascript">
	var url = setDefaultQuery(new URI("exceptional-entry-ls.php"));
	
	URI.removeQuery(url, "s");
	
	liveReqInit("livesearch", "liveRequestResults", url.build().toString(), "", "mainResult");
</script>
</body>
</html>			
