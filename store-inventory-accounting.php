<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/pagination/pagination.php";

if (empty($_GET['BEG_DT'])) {
	$_GET['BEG_DT'] = date('Y-m-d',strtotime("-1 days"));
}

if (empty($_GET['END_DT'])) {
	$_GET['END_DT'] = date('Y-m-d',strtotime("-1 days"));
}

if ($_GET['CAT_TYP_NBR'] != "") {
	$CatTypeNumber = $_GET['CAT_TYP_NBR'];
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
</head>
<body>
<div class="toolbar">
	<div class="combobox"></div>
	<div class="toolbar-text">
				
	<div style="display: inline-block; float: left; margin-top: 6px;">
		<span style="display: inline-block; float: left; margin-right: 15px;">
			<select name="CAT_TYP_NBR" id="CAT_TYP_NBR" style='width:200px;' class="chosen-select" >
				<option value="">Semua</option>
				<?php
					
					$query="SELECT CAT_TYP_NBR, CAT_TYP FROM RTL.CAT_TYP
							ORDER BY CAT_TYP_NBR";
					
					genCombo($query, "CAT_TYP_NBR", "CAT_TYP", $CatTypeNumber);
				?>
			</select>
		</span>
		
		<span style="display: inline-block; float: left; margin-right: 15px;">		
			<input id="END_DT" name="END_DT" value="<?php echo $_GET['END_DT'];?>" type="text" size="10" class="livesearch" style="text-align:center;margin-top:0" />
			<script>new CalendarEightysix('END_DT', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });</script>
			
		</span>	
		
		</div>
		
		<div style="display: inline-block; float: left; margin-top: 6px; margin-right: 0px;">
			<span id="FLTR_DTE" class="fa fa-calendar toolbar fa-lg"  style="padding:3px;padding-left:10px;cursor:pointer"></span>
		</div>
		
		<?php if(($locked==0) && ($_COOKIE["LOCK"] != "LOCK")) { ?>
			<div class="tabContaier" style="display: inline-block; float: left; margin-top: 12px; margin-right: 0px;">
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
	</div>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<?php if(($locked==0) && ($_COOKIE["LOCK"] != "LOCK")) { ?>
	<br /><br />
<?php } ?>	

<div id="mainResult" class="tabContaier" style="border:transparent;">
	
	
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

<script type="text/javascript">

	function setDefaultQuery(url) {
		url.setQuery(URI.parseQuery(location.search));
		url.setQuery("CAT_TYP_NBR", document.getElementById("CAT_TYP_NBR").value);
		url.setQuery("END_DT", document.getElementById("END_DT").value);
		return url;
	}
	var url = setDefaultQuery(new URI("store-inventory-accounting-ls.php"));

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
	
	document.getElementById("FLTR_DTE").onclick = function() {
		var url = setDefaultQuery(new URI("store-inventory-accounting.php"));

		URI.removeQuery(url, "s");
		URI.removeQuery(url, "page");
	
		window.scrollTo(0,0);

		location.href = url.build().toString();
	}

</script>
<script type="text/javascript">
	var url = setDefaultQuery(new URI("store-inventory-accounting-ls.php"));

	URI.removeQuery(url, "s");
	URI.removeQuery(url, "page");
	
	liveReqInit("livesearch", "liveRequestResults", url.build().toString(), "", "mainResult");
	
	/*
	jQuery(document).ready(function(){		
			setTimeout(function(){			
				jQuery("table.table-accounting").tablesorter({ widgets:["zebra"]});  		
			},500);		
	});
	*/
			
	jQuery(document).on('click','tr.tr-master',function(){
		jQuery(this).closest('tr').nextUntil('tr.tr-master').toggle();
	});
		
</script>
</body>
</html>