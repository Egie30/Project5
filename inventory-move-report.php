<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/pagination/pagination.php";

$Type	= $_GET['TYP'];

if (empty($_GET['BEG_DT'])) {
	$_GET['BEG_DT'] = date("Y-m-d");
}

if (empty($_GET['END_DT'])) {
	$_GET['END_DT'] = date("Y-m-d");
}

if (empty($_GET['CO_NBR'])) {
	$_GET['CO_NBR']	= $CoNbrDef;
	$companyNumber 	= $CoNbrDef;
}
else {
	$companyNumber = $_GET['CO_NBR'];
}


$query_composite	= "SELECT 
							CO_NBR_CMPST
						FROM NST.PARAM_PAYROLL
						WHERE CO_NBR = (SELECT CO_NBR_DEF FROM NST.PARAM_LOC)";
$result_composite	= mysql_query($query_composite);
$row_composite		= mysql_fetch_array($result_composite);


$_GET['TYP']	= $Type;


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
	
	<style>
	.tr-detail{
		display:none;
	}
	.tr-detail td{
		border-top:1px solid #ddd;
	}
	
	.tr-total td{
		border-top:1px solid #A9A9A9;
	}
	
	
	</style>

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
		
	<p class="toolbar-left">
		<span style="display: inline-block; float: left; margin-top: 6px; margin-right: 15px;">
		<select name="CO_NBR" id="CO_NBR" style='width:250px' class="chosen-select" >
			<?php
				
				
			$query	= "SELECT 
							PRM.CO_NBR,
							COM.NAME AS CO_NAME
						FROM NST.PARAM_PAYROLL PRM
						LEFT JOIN CMP.COMPANY COM 
							ON PRM.CO_NBR = COM.CO_NBR
						WHERE PRM.CO_NBR IN (".$row_composite['CO_NBR_CMPST'].")
						GROUP BY PRM.CO_NBR";
			
			genCombo($query, "CO_NBR", "CO_NAME", $companyNumber);
			?>
		</select>
		</span>
		
		<span style="display: inline-block; float: left; margin-top: 6px; margin-right: 15px;">
			<input id="BEG_DT" name="BEG_DT" value="<?php echo $_GET['BEG_DT'];?>" type="text" size="10" class="livesearch" style="text-align:center;margin-top:0" />
			<script>new CalendarEightysix('BEG_DT', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });</script>
			
			<input id="END_DT" name="END_DT" value="<?php echo $_GET['END_DT'];?>" type="text" size="10" class="livesearch" style="text-align:center;margin-top:0" />
			<script>new CalendarEightysix('END_DT', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });</script>
			
			<span id="FLTR_DTE" class="fa fa-calendar toolbar fa-lg"  style="padding:3px; cursor:pointer"></span>
		</span>
		
		<input type="hidden" name="TYP" id="TYP" value="<?php echo $Type; ?>">
	</p>
		
	<p class="toolbar-right">
		<span class='fa fa-search fa-flip-horizontal toolbar' style='cursor:default'></span><input type="text" id="livesearch" class="livesearch" />
	</p>
	
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">

</div>


<script type="text/javascript">
	function setDefaultQuery(url) {
		url.setQuery(URI.parseQuery(location.search));
		url.setQuery("CO_NBR", document.getElementById("CO_NBR").value);
		url.setQuery("TYP", document.getElementById("TYP").value);
		url.setQuery("BEG_DT", document.getElementById("BEG_DT").value);
		url.setQuery("END_DT", document.getElementById("END_DT").value);

		return url;
	}

	var url = setDefaultQuery(new URI("inventory-move-report-ls.php"));

	url.setQuery("CO_NBR", '<?php echo $_GET['CO_NBR'];?>');
	url.setQuery("BEG_DT", '<?php echo $_GET['BEG_DT'];?>');
	url.setQuery("END_DT", '<?php echo $_GET['END_DT'];?>');
	url.setQuery("TYP", '<?php echo $_GET['TYP'];?>');

	getContent("mainResult", url.build().toString());

	var onClickListener = function () {
		var url = setDefaultQuery(new URI("inventory-move-report.php"));
		
		window.scrollTo(0,0);

		URI.removeQuery(url, "s");
		URI.removeQuery(url, "page");
	
		location.href = url.build().toString();
	};
		
	document.getElementById("FLTR_DTE").onclick = function() {
		onClickListener();
	};
		
</script>
<script type="text/javascript">
	var url = setDefaultQuery(new URI("inventory-move-report-ls.php"));

	URI.removeQuery(url, "s");
	URI.removeQuery(url, "page");
	
	liveReqInit("livesearch", "liveRequestResults", url.build().toString(), "", "mainResult");
		
	jQuery(document).on('click','tr.tr-master',function(){
		jQuery(this).closest('tr').nextUntil('tr.tr-master').toggle();
	});
		
</script>
</body>
</html>