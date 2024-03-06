<?php
session_start();
// require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/database/connect-cloud.php";

	$PRSN_NBR = $_GET['PRSN_NBR'];

	$query  = "SELECT PPL.PRSN_NBR,
					  PPL.NAME,
					  COM.NAME AS COMPANY
			   FROM  $CMP.PEOPLE PPL
			   LEFT  JOIN $CMP.COMPANY COM ON PPL.CO_NBR 	= COM.CO_NBR
			   LEFT  JOIN $CMP.EMPL_CNTRCT_TYP CNTRCT_TYP ON CNTRCT_TYP.EMPL_CNTRCT_TYP = PPL.EMPL_CNTRCT
			   LEFT  JOIN $CMP.EMPL_CNTRCT CNTRCT ON  CNTRCT.PRSN_NBR =  PPL.PRSN_NBR
			   WHERE TERM_DTE IS NULL AND PPL.DEL_NBR  = 0 AND PPL.EMPL_CNTRCT > 0 AND PPL.PRSN_NBR = '".$PRSN_NBR."'
			   ORDER BY PPL.UPD_TS DESC";
	// echo $query;
	$result 	= mysql_query($query,$cloud);
	$row 		= mysql_fetch_array($result);
 	$idkrywn 	= $row['PRSN_NBR']; 
 	$name 		= $row['NAME']; 
 	$cmpany 	= $row['COMPANY']; 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />
<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />

<script src="framework/database/jquery.min.js"></script>
<script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<script type="text/javascript" src="framework/functions/default.js"></script>
<script type="text/javascript" src="framework/validation/mootools-1.2.3.js"></script>
<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>

	<style type="text/css">
		label.nbsp
		{
				padding-left:1px;
				padding-bottom:2px;
				display:inline-block;
				width:170px;
				text-align:left;
				border-bottom:0px solid #dddddd;
		}
	</style>
</head>
<body>

<div style="display:none;">
	<input id="refresh-list" type="button" value="Refresh" onclick="syncGetContent('edit-list','employment-contract-edit-list.php?PRSN_NBR=<?php echo $PRSN_NBR; ?>');" />
</div>

<form enctype="multipart/form-data" action="" method="post" style="width:700px" onSubmit="return checkform();">
	
	<br>
		<h3>
				<label class="nbsp">ID Karyawan </label> <span id="idkrywn"> : <?php echo $idkrywn ?></span><br /><br />
				<label class="nbsp">Nama </label>   <span   id="name"> : <?php echo $name ?></span><br /><br />
				<label class="nbsp">Perusahaan </label> <span 	id="cmpany"> : <?php echo $cmpany ?></span><br /><br />
		</h3>
	
	<table id="mainTable" class="table-freeze tablesorter std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable sortable-onload-show" style="padding-top: 0px; margin-top: 0px; width:100%">

	<tbody>
	<div id="edit-list" class="edit-list"></div>
	<script>getContent('edit-list','employment-contract-edit-list.php?PRSN_NBR=<?php echo $PRSN_NBR; ?>');</script>
	</tbody>

	</table>
		
</form>

<div class="searchresult" id="liveRequestResults"></div>
<div id="mainResult"></div>

	<script type="text/javascript">
	var url = setDefaultQuery(new URI("finance-retur-report-detail.php"));

	URI.removeQuery(url, "s");
	URI.removeQuery(url, "page");
	
	liveReqInit("livesearch", "liveRequestResults", url.build().toString(), "", "mainResult");
	</script>

	<script type="text/javascript">
	    $("#mainTable").tablesorter({ widgets:["zebra"]});
	</script>

</body>
</html>