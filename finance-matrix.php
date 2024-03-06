<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/pagination/pagination.php";
include "framework/functions/crypt.php";

if (($_GET['MONTH']=='')||($_GET['YEAR']=='')){
	$_GET['MONTH'] = date('m');
	$_GET['YEAR'] = date('Y');
	$filter_date = date('Y-m-01');
} else {
	if ($_GET['MONTH'] < 10) { $month = '0'.$_GET['MONTH']; } else { $month = $_GET['MONTH']; }
	$filter_date = $_GET['YEAR'].'-'.$month.'-01';
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
		<select id="MONTH" name="MONTH" style="width:100px" class="chosen-select">
		<?php
			$bulan = array("", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");
			for($a=1;$a<=12;$a++){
 				if($a==$_GET['MONTH']){ 
 					$pilih="selected";
 				} else {
					$pilih="";
 				}
				echo("<option value=\"$a\" $pilih>$bulan[$a]</option>"."\n");
			}
		?>
		</select>

		<select id="YEAR" name="YEAR" style="width:80px" class="chosen-select">
		<?php
			for($a=date("Y");$a>=2010;$a--){
 				if($a==$_GET['YEAR']){ 
 					$pilih="selected";
 				} else {
					$pilih="";
 				}
				echo("<option value=\"$a\" $pilih>$a</option>"."\n");
			}
		?>
		</select>
			
		</span>	
		
		</div>
		
		<div style="display: inline-block; float: left; margin-top: 6px; margin-right: 0px;">
			<span id="FLTR_DTE" class="fa fa-calendar toolbar fa-lg"  style="padding:3px;padding-left:10px;cursor:pointer"></span>
		</div>
		
		<?php if(($locked==0) && ($_COOKIE["LOCK"] != "LOCK")) { ?>
			<div class="tabContaier" style="display: none; float: left; margin-top: 0px; margin-right: 0px;">
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
		url.setQuery("MONTH", document.getElementById("MONTH").value);
		url.setQuery("YEAR", document.getElementById("YEAR").value);
		return url;
	}
	var url = setDefaultQuery(new URI("finance-matrix-ls.php"));

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
		var url = setDefaultQuery(new URI("finance-matrix.php"));

		URI.removeQuery(url, "s");
		URI.removeQuery(url, "page");
	
		window.scrollTo(0,0);

		location.href = url.build().toString();
	}

</script>
<script type="text/javascript">
	var url = setDefaultQuery(new URI("finance-matrix-ls.php"));

	URI.removeQuery(url, "s");
	URI.removeQuery(url, "page");
	
	liveReqInit("livesearch", "liveRequestResults", url.build().toString(), "", "mainResult");
			
	jQuery(document).on('click','tr.tr-master',function(){
		jQuery(this).closest('tr').nextUntil('tr.tr-master').toggle();
	});
		
</script>
</body>
</html>