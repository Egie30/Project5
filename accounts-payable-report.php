<?php
	require_once "framework/database/connect.php";
	require_once "framework/functions/default.php";
	require_once "framework/pagination/pagination.php";

	$val = $_GET['CO_NBR'];

	if(empty($_GET['END_DT'])) 
	{
		$_GET['END_DT'] = date('Y-m-d');
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
	<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
	<script type="text/javascript" src="framework/uri/src/URI.min.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>
	<script type="text/javascript" src="framework/combobox/ajax-chosen/ajax-chosen.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.default.js"></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<!-- <script type="text/javascript" src='framework/jquery-freezeheader/js/jquery.freezeheader.js'></script> -->
	<script type="text/javascript">jQuery.noConflict();</script>

	<script type="text/javascript">
		function getInt(objectID)
		{	
			if(document.getElementById(objectID).value=="")
			{
				return 0;
			}
				else
			{
				return parseInt(document.getElementById(objectID).value);
			}
		}
	</script>

	<style type="text/css">
		.scroll-table
		{
	       	height:	350px;
	       	overflow: auto;
	    }
	</style>

</head>

<body>

<div class="toolbar">	
	<p class="toolbar-left">
	<div style="display: inline-block; float: left; margin-top: 6px; margin-right: 15px;">
		<select name="CO_NBR" id="CO_NBR" style='width:500px' class="chosen-select">
				<?php
					$query="SELECT CO_NBR, CONCAT(CO_NBR,'-',CO_ID) AS VAL,
								   CONCAT(NAME,' - ',COALESCE(CO_ID,''),' - ',ADDRESS,' ',CITY_NM) AS CO_DESC
							FROM CMP.COMPANY COM 
							INNER JOIN CITY CIT ON COM.CITY_ID = CIT.CITY_ID 
							ORDER BY 2";
					genCombo($query, "CO_NBR", "CO_DESC", $val,"Pilih Supplier");
				?>
		</select>
	</div>

	<input id="END_DT" name="END_DT" value="<?php echo $_GET['END_DT'];?>" type="text" size="10" class="livesearch" style="text-align:center;margin-top:9px;" />
	<script>new CalendarEightysix('END_DT', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });</script>

	<span id="FLTR_DTE" class="fa fa-calendar toolbar fa-lg" style="padding:3px;padding-left:0px;cursor:pointer;margin-top: 10px;" title="Filter berdasarkan tanggal">
	</span>

	<div style="display: inline-block; float: right; margin-top: 0px; margin-right: 15px;">
	 <!-- 	
		<a title="Export to Excel" href="#" id="EXPORT_EXCEL">
			<span class="fa fa-file-excel-o fa-flip-horizontal toolbar" style="padding:0px;cursor:pointer"></span>
		</a>
	 -->	
	 	<?php buildComboboxLimit();?>
	<span class='fa fa-search fa-flip-horizontal toolbar'></span><input type="text" id="livesearch" class="livesearch" />
	</div>

	<?php if(($locked==0) && ($_COOKIE["LOCK"] != "LOCK")) { ?>
		<div class="tabContaier" style="display: inline-block; float: left; margin-top: 2px; margin-right: 0px;">
			<ul>
				<li><a class="active" href="#tab1"><div>Semua</div></a></li>
				<li><a href="#tab2"><div>PT</div></a></li>
				<li><a href="#tab3"><div>CV</div></a></li>
				<li><a href="#tab4"><div>PR</div></a></li>
			</ul>
		</div>
	<?php } else { ?>
		<div class="tabContaier" style="display: none; float: left;">
			<ul>
				<li><a href="#tab2"><div>PT</div></a></li>
			</ul>
		</div>
	<?php } ?>
	</p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult" class="tabContaier" style="width:auto;">
	<?php if(($locked==0) && ($_COOKIE["LOCK"] != "LOCK")) { ?>
		<div class="tabDetails">
			<div id="tab1" class="tabContents"></div>
			<div id="tab2" class="tabContents"></div>
			<div id="tab3" class="tabContents"></div>
			<div id="tab4" class="tabContents"></div>		
		</div>
	<?php } else { ?>
		<div class="tabDetails">
			<div id="tab2" class="tabContents"></div>		
		</div>
	<?php } ?>
</div>
	
	<script>
		function setDefaultQuery(url) 
		{
			url.setQuery(URI.parseQuery(location.search));
			url.setQuery("CO_NBR", document.getElementById("CO_NBR").value);
			url.setQuery("END_DT", document.getElementById("END_DT").value);
			url.setQuery("LIMIT",  document.getElementById("LIMIT").value);
			return url;
		}

		var url = setDefaultQuery(new URI("accounts-payable-report-ls.php"));

		// url.setQuery("CO_NBR", '<?php echo $_GET['CO_NBR'];?>');
		// url.setQuery("END_DT", '<?php echo $_GET['END_DT'];?>');
		// getContent("mainResult", url.build().toString());

	<?php if(($locked==0) && ($_COOKIE["LOCK"] != "LOCK")) { ?>
		url.setQuery("ACTG", 0);
		getContent("tab1", url.build().toString());

		url.setQuery("ACTG", 1);
		getContent("tab2", url.build().toString());
		
		url.setQuery("ACTG", 2);
		getContent("tab3", url.build().toString());
		
		url.setQuery("ACTG", 3);
		getContent("tab4", url.build().toString());
	<?php } else { ?>
		url.setQuery("ACTG", 1);
		getContent("tab2", url.build().toString());
	<?php } ?>
	
		document.getElementById("FLTR_DTE").onclick = function() 
		{
			var url = setDefaultQuery(new URI("accounts-payable-report.php"));

			URI.removeQuery(url, "s");
			URI.removeQuery(url, "page");
		
			window.scrollTo(0,0);

			location.href = url.build().toString();
		};

		document.getElementById("EXPORT_EXCEL").onclick = function() 
		{
			var url = setDefaultQuery(new URI("report-excel.php"));
			
			window.scrollTo(0,0);

			url.setQuery("s", document.getElementById("livesearch").value);
			url.setQuery("page", jQuery("#pagination-container").length > 0 ? jQuery("#pagination-container").attr("data-page") : 1);
			url.setQuery("RPT_TYP", "accounts-payable-report-excel");
			
			URI.removeQuery(url, "GROUP");
			
			window.open(url.build().toString(), "_blank");
		};

		</script>

		<script type="text/javascript">
			var url = setDefaultQuery(new URI("accounts-payable-report-ls.php?LS=1"));

			URI.removeQuery(url, "s");
			URI.removeQuery(url, "page");
			
			liveReqInit("livesearch", "liveRequestResults", url.build().toString(), "", "mainResult");
				
			jQuery(document).on('click','tr.tr-master',function()
			{
				jQuery(this).closest('tr').nextUntil('tr.tr-master').toggle();
			});	
		</script>

		<script type="text/javascript">
		jQuery(document).ready(function()
		{		
				setTimeout(function()
				{			
					jQuery("table.table-accounting").tablesorter({ widgets:["zebra"]});  		
				},100);		
		});
		</script>

</body>


</html>
