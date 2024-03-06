<?php
	include "framework/database/connect.php";
	include "framework/database/connect-cloud.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/alert/alert.php";
	date_default_timezone_set('Asia/Jakarta');

	$personNumber 	= $_GET['PRSN_NBR'];
	$Typ 		= $_GET['TYP'];
	$PrsnNbr 	= $_GET['PRSN_NBR'];
	$PymtDte 	= date('Y-m-d');
	
	if ($_GET['FLTR_DTE']==''){
		$_GET['FLTR_DTE']=date('n Y');
	}
	$filter_date=str_replace("+"," ",$_GET['FLTR_DTE']);

	$query	= "SELECT PRSN_NBR, NAME, POS_TYP FROM CMP.PEOPLE WHERE PRSN_NBR = " . $personNumber;
	$result	= mysql_query($query, $local);
	$row	= mysql_fetch_array($result);
	$personName = $row['NAME'];
	$posTyp = $row['POS_TYP'];
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/combobox/chosen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/pagination/pagination.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">

	<script type="text/javascript">parent.Pace.restart();</script>
	<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
	<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
	<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>
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
	<style>
		table.tablesorter thead tr .headerTd{
			border-bottom:1px solid #cacbcf;
		}
	</style>
	<style type="text/css">
		.time-upDown{
			width:9px;
			float:right;
			font-size:8px;
			visibility:hidden;
			margin-right:1px;
		}
		.listUp:hover,.listDown:hover{
			background-color: #989898;
			color:#fff;
		}
		.HeadTab{
			width: 100%;
		}
		.CalLeft, .CalRight{
			width: 50%;
		}
		.TblLeft, .TblRight{
			width: 100%;
		}
	</style>
</head>

<body>
<div class="toolbar">
	<p class="toolbar-left">
		<select id="RCV_DATE" name="RCV_DATE" style="width:150px" class="chosen-select">
		<?php
			$query_dte	= "
			SELECT * FROM (
				SELECT CLOK_IN_TS,CONCAT(MONTH(CLOK_IN_TS),' ',YEAR(CLOK_IN_TS)) AS DTE,
				CONCAT(CASE 
					WHEN MONTH(CLOK_IN_TS)='1' THEN 'Januari'
					WHEN MONTH(CLOK_IN_TS)='2' THEN 'Februari'
					WHEN MONTH(CLOK_IN_TS)='3' THEN 'Maret'
					WHEN MONTH(CLOK_IN_TS)='4' THEN 'April'
					WHEN MONTH(CLOK_IN_TS)='5' THEN 'Mei'
					WHEN MONTH(CLOK_IN_TS)='6' THEN 'Juni'
					WHEN MONTH(CLOK_IN_TS)='7' THEN 'Juli'
					WHEN MONTH(CLOK_IN_TS)='8' THEN 'Agustus'
					WHEN MONTH(CLOK_IN_TS)='9' THEN 'September'
					WHEN MONTH(CLOK_IN_TS)='10' THEN 'Oktober'
					WHEN MONTH(CLOK_IN_TS)='11' THEN 'November'
					WHEN MONTH(CLOK_IN_TS)='12' THEN 'Desember'
				END,' ',YEAR(CLOK_IN_TS)) AS DTE_DESC
				FROM PAY.MACH_CLOK_PROCESS
				GROUP BY YEAR(CLOK_IN_TS), MONTH(CLOK_IN_TS)
			) T WHERE DTE_DESC IS NOT NULL 
			ORDER BY CLOK_IN_TS DESC LIMIT 12";
			genCombo($query_dte, "DTE", "DTE_DESC", $filter_date,"Filter Bulan Tahun");
		?>
		</select>
		<span id="FLTR_DTE" class="fa fa-calendar toolbar fa-lg" id="filter-by-date" style="padding-left:5px;margin-bottom:12px;cursor:pointer">
		</span>
	</p>
	<p class="toolbar-right"><span class="fa fa-search fa-flip-horizontal toolbar"></span><input type="text" id="livesearch" class="livesearch" /></p>
</div>
<?php 
//echo $query_dte;
?>
<div id="mainResult">
	<h2>
		<?php echo $personName ?>
	</h2>
</div>

<div id="mainResult" >
	<h3>
		Perincian Absensi Nomor Induk: <?php echo $personNumber; ?>
	</h3>
	
	<table id="mainTable" class="tablesorter searchTable" style="width:700px;">
		<tr>
			<td><?php include  "payroll-calendar-out.php"; ?></td>
		<tr>
	</table>

	
</div>
<script type="text/javascript">

	function setDefaultQuery(url) {
		url.setQuery(URI.parseQuery(location.search));
		url.setQuery("FLTR_DTE", document.getElementById("RCV_DATE").value);
		return url;
	}

    document.getElementById("FLTR_DTE").onclick = function() {
		var url = setDefaultQuery(new URI("payroll-out.php"));

		URI.removeQuery(url, "s");
		// URI.removeQuery(url, "page");
	
		window.scrollTo(0,0);

		location.href = url.build().toString();
	};

</script>
<script>
	jQuery(document).ready(function () {
		jQuery("#mainTable").tablesorter({widgets: ["zebra"]});
		jQuery("#tableCuti").tablesorter({widgets: ["zebra"]});
		jQuery("#tableHeld").tablesorter({widgets: ["zebra"]});
		jQuery("#tableBon").tablesorter({widgets: ["zebra"]});
		jQuery("#tabelcnrtc").tablesorter({widgets: ["zebra"]});
	});
</script>

</body>
</html>