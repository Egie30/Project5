
<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	ini_set('max_execution_time',5000);

	// error_reporting (E_ALL);
	// ini_set ('display_errors',1);

	$security = getSecurity($_SESSION['userID'], "Executive");	

	$CMPTR_NAME  =$_GET['CMPTR_NAME'];
	$BegDt 	  =$_GET['BEG_DT'];
	$EndDt 	  =$_GET['END_DT'];
	if($BegDt==""){
		$BegDt=date("Y-m-d",strtotime("-7 days"));
	}
	if($EndDt==""){
		$EndDt=date("Y-m-d");
	}


	//Process equipment
	$Eqp=$_GET['EQP'];		
	if($Eqp!=""){
		$where.=" AND LOG.MACH_TYP='".$Eqp."'";
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<script>parent.Pace.restart();</script>
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

	 <div class='tabmenu' style='width:100px' onclick='document.getElementById("mainResult").src="log-list.php";selTabMenu(this);'>Semua</div>
	   <div class='tabmenu' style='width:100px' onclick='document.getElementById("mainResult").src="log-list.php?EQP=MVJ1624";selTabMenu(this);'>MVJ1624</div>
	   <div class='tabmenu' style='width:100px' onclick='document.getElementById("mainResult").src="log-list.php?EQP=RVS640";selTabMenu(this);'>RVS640</div>
	   <div class='tabmenu' style='width:100px' onclick='document.getElementById("mainResult").src="log-list.php?EQP=HPL375";selTabMenu(this);'>HPL375</div>
	   <div class='tabmenu' style='width:100px' onclick='document.getElementById("mainResult").src="log-list.php?EQP=C512i30PL";selTabMenu(this);'>C512i30PL</div>

		<p style="display: inline-block; float: right; margin-top: 6px; margin-right: 5px;">
			<span style="padding-top: 4px;" class="fa fa-search fa-flip-horizontal toolbar"></span>
			<input type="text" id="livesearch" class="livesearch" style="margin-top:0;"/>
		</p>

		</div>
		</div>
		
		<div class="searchresult" id="liveRequestResults"></div>

		<br>
		 <iframe id="mainResult" src='log-list.php'></iframe>
	

	
	<img id='loading' src='img/wait.gif' style='visibility: hidden;'>

	<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
	<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>	
	<script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
	<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
	<script>
		$(document).ready(function()
		{
			$("#mainTable").tablesorter({ widgets:["zebra"]});  
		}
	);
	</script>

	<script>liveReqInit('livesearch','liveRequestResults','log-ls.php','','mainResult');</script>



</script>
</body>
</html>

