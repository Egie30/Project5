<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/pagination/pagination.php";

$BegDt		= $_GET['BEG_DT'];
$EndDt		= $_GET['END_DT'];
$CshDyDte 	= $_GET['CSH_DAY_DTE'];
$typ 		= $_GET['TYP'];

if (empty($BegDt)) {
		$BegDt = date("Y-m");
		$BegDt = $BegDt."-01";
}
if (empty($EndDt)) {
		$EndDt = date("Y-m-d");
}
if ($typ=='CAD'){
	$cssDisplay="display:none;";
	$mgnTpMainResult="0";
	$mgnTpSearch="0";
}else{
	$mgnTpMainResult="45";
	$mgnTpSearch="65";
}

if (isset($_GET['DEL_A'])){
	$query="UPDATE RTL.CSH_DAY SET DEL_NBR=".$_SESSION['personNBR'].", UPD_TS=CURRENT_TIMESTAMP,UPD_NBR=".$_SESSION['personNBR']."
			WHERE CSH_DAY_DTE='".$_GET['DEL_A']."' AND S_NBR = ".$_GET['S_NBR']." ";
	mysql_query($query);
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
	<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
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

<?php
	if($locked == 0) {
		include 'deposit.php';
	}
?>

<div class="toolbar">
	
	<div class="toolbar-text tabContaier">
		<?php if ($typ!='CAR'){?>
		<div style="display: inline-block; float: left;  margin-right: 10px;">
			<a href='cash-day-report-edit.php?CSH_DAY_DTE=0'><span class='fa fa-plus toolbar' style="cursor:pointer;padding-top: 8px;" onclick="location.href="></span></a>
		</div>
		<?php } ?>
		<?php if ($typ=='CAR'){?>
		<div style="display: inline-block; float: left;  margin-right: 15px;<?php echo $cssDisplay;?>" id ="ToolTap">
			
			<!-- Tab buttons -->
				<ul>
			    	<li><a class="active" href="#tab1"><div>Semua</div></a></li>
			    	<li><a href="#tab2"><div>Rekening 1</div></a></li>
			    	<li><a href="#tab3"><div>Rekening 2</div></a></li>
					<li><a href="#tab4"><div>Rekening 3</div></a></li>
					<li><a href="#tab5"><div>Rekening 4</div></a></li>
			    </ul>
				<!-- End Tab buttons -->
		</div>
		<?php } ?>

		<?php if ($typ=='CAD') {?>
		<div style="display: inline-block; float: right;  margin-right: 15px;">
			<span class="fa fa-search fa-flip-horizontal toolbar" style="padding-top: 4px;" ></span><input type="text" id="livesearch" class="livesearch" style="margin-top:0;" onkeyup="toolUp()" />
		</div>
		<?php }else{ echo "<div id='livesearch' class='livesearch' style='display:none;'></div>";} ?>
	</div>
	<div class="toolbar-text" style="margin-top:34px; <?php echo $cssDisplay;?>">
		<div style="display: inline-block; float: left;  margin-right: 15px;">
			<input id="BEG_DT" name="BEG_DT" value="<?php echo $BegDt;?>" type="text" size="10" class="livesearch" style="text-align:center;margin-top:0;" />
			<script>new CalendarEightysix('BEG_DT', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });</script>
			<input id="END_DT" name="END_DT" value="<?php echo $EndDt;?>" type="text" size="10" class="livesearch" style="text-align:center;margin-top:0"/>
			<script>new CalendarEightysix('END_DT', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });</script>
			<span id="FLTR_DTE" class="fa fa-calendar toolbar fa-lg"  style="padding:0px;padding-left:0px;cursor:pointer"></span>
		</div>		
	</div>
	
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult" class="tabContaier" style="margin-top: <?php echo $mgnTpMainResult;?>px;">
	<!-- Tab content -->
    <div class="tabDetails" style="margin-top: 0px;">
    	<div id="tab1" class="tabContents"></div>
    	<div id="tab2" class="tabContents"></div>
    	<div id="tab3" class="tabContents"></div>
		<div id="tab4" class="tabContents"></div>
		<div id="tab5" class="tabContents"></div>
	</div>
	<!-- End Tab content -->

</div>

<script type="text/javascript">
	function setDefaultQuery(url) {
		url.setQuery(URI.parseQuery(location.search));
		url.setQuery("BEG_DT", document.getElementById("BEG_DT").value);
		url.setQuery("END_DT", document.getElementById("END_DT").value);
		
		return url;
	}


	var url = setDefaultQuery(new URI("cash-day-report-ls.php"));

		url.setQuery("ACCT", "ALL");
		getContent("tab1", url.build().toString());

		url.setQuery("ACCT", "PT");
		getContent("tab2", url.build().toString());

		url.setQuery("ACCT", "CV");
		getContent("tab3", url.build().toString());
		
		url.setQuery("ACCT", "PR");
		getContent("tab4", url.build().toString());
		
		url.setQuery("ACCT", "AD");
		getContent("tab5", url.build().toString());
	

	document.getElementById("FLTR_DTE").onclick = function() {
		var url = setDefaultQuery(new URI("cash-day-report.php"));

		URI.removeQuery(url, "s");
		URI.removeQuery(url, "page");
	
		window.scrollTo(0,0);

		location.href = url.build().toString();
	};

</script>
<script type="text/javascript">
	var url = setDefaultQuery(new URI("cash-day-report-ls.php"));


	URI.removeQuery(url, "s");
	URI.removeQuery(url, "page");

	liveReqInit("livesearch", "liveRequestResults", url.build().toString(), "", "mainResult");
	
	jQuery(document).ready(function(){		
		setTimeout(function(){			
			jQuery("table.table-accounting").tablesorter({ widgets:["zebra"]});  		
		},500);		
	});

</script>
<script type="text/javascript">
	function toolUp(){
		var ax= document.getElementById("livesearch").value;
		if (ax!=''){
			document.getElementById("ToolTap").style.display = "none";
		}else{
			document.getElementById("ToolTap").style.display = "block";
		}
	}
</script>
</body>
</html>