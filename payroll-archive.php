<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/pagination/pagination.php";

$years			= $_GET['YEARS'];
$PayActgType	= $_GET['PAY_ACTG_TYP'];
$companyNumber	= $_GET['CO_NBR'];

if (empty($years)) {
		$_GET['YEARS'] = date("Y");
	}
	else { $_GET['YEARS'] = $years; }

if (empty($_GET['GROUP'])) {
	$_GET['GROUP'] = "MONTH";
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
	<div class="toolbar-text">
		<div style="display: inline-block; float: left; margin-top: 5px; margin-right: 15px;">
			<select name="CO_NBR" id="CO_NBR" style='width:150px' class="chosen-select" >
			<?php
				
				$query = "SELECT PAR.CO_NBR, CO_NBR_CMPST, NAME FROM NST.PARAM_COMPANY PAR
				INNER JOIN CMP.COMPANY COM ON PAR.CO_NBR = COM.CO_NBR
				WHERE SORT IS NOT NULL
				GROUP BY PAR.CO_NBR
				ORDER BY SORT ASC";
				
				genCombo($query, "CO_NBR_CMPST", "NAME", $companyNumber, "Semua");
			?>
			</select>
		</div>

		<span style="display: inline-block; float: left; margin-top: 6px; margin-right: 15px;">
			<select name="PAY_ACTG_TYP" id="PAY_ACTG_TYP" style='width:250px' class="chosen-select" >
				<option value="0" <?php echo ($PayActgType == '0') ? "selected" : ""; ?> >Semua</option>
				<option value="1" <?php echo ($PayActgType == '1') ? "selected" : ""; ?> >Tenaga Kerja Langsung</option>
				<option value="2" <?php echo ($PayActgType == '2') ? "selected" : ""; ?> >Tenaga Kerja Tidak Langsung</option>
			</select>
		</span>
		
		<div style="display: inline-block; float: left; margin-top: 6px; margin-right: 15px;">
				<select id="YEARS" name="YEARS" style="width:100px" class="chosen-select">
					<?php

					$now=date("Y");
					for($y=$now;$y>=2016;$y--){
					if($y==$_GET['YEARS']){ $pilih="selected";}
					else {$pilih="";}
					echo("<option value=\"$y\" $pilih>$y</option>"."\n");
					}
					?>
				</select>
		</div>
		
		<div style="display: inline-block; float: left; margin-top: 6px; margin-right: 15px;">
			<img id="FLTR_DTE" class="toolbar-right" src="img/date.png" style="padding:3px;padding-left:0px;cursor:pointer" title="Filter by date">
		</div>

	</div>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<?php if(($locked==0) && ($_COOKIE["LOCK"] != "LOCK")) { ?>
	<br /><br />
<?php } ?>	

<?php if(($locked==0) && ($_COOKIE["LOCK"] != "LOCK")) { ?>
<div id="mainResult" class="tabContaier">
	<?php if (!$_SESSION['PLUS_MODE']) { ?>
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
	<?php } ?>
</div>
<?php } else { ?>
	<div id="mainResult" class="tabContaier" style="display: none;">
	<?php if (!$_SESSION['PLUS_MODE']) { ?>
	<!-- Tab buttons -->
	<ul>
    	<li><a href="#tab2"><div>PT</div></a></li>
    </ul>
	<!-- End Tab buttons -->
	
	<!-- Tab content -->
    <div class="tabDetails">
    	<div id="tab2" class="tabContents"></div>
	</div>
	<!-- End Tab content -->
	<?php } ?>
</div>
<?php } ?>

<script type="text/javascript">

	function setDefaultQuery(url) {
		url.setQuery(URI.parseQuery(location.search));
		url.setQuery("PAY_ACTG_TYP", document.getElementById("PAY_ACTG_TYP").value);
		url.setQuery("YEARS", document.getElementById("YEARS").value);
		url.setQuery("CO_NBR", document.getElementById("CO_NBR").value);
		return url;
	}

	var url = setDefaultQuery(new URI("payroll-archive-ls.php"));

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
		var url = setDefaultQuery(new URI("payroll-archive.php"));

		URI.removeQuery(url, "s");
		URI.removeQuery(url, "page");
	
		window.scrollTo(0,0);

		location.href = url.build().toString();
	};

</script>
<script type="text/javascript">
	var url = setDefaultQuery(new URI("payroll-archive-ls.php"));

	URI.removeQuery(url, "s");
	URI.removeQuery(url, "page");
	
	liveReqInit("livesearch", "liveRequestResults", url.build().toString(), "", "mainResult");
		
	jQuery(document).ready(function(){		
			setTimeout(function(){			
				jQuery("table.table-accounting").tablesorter({ widgets:["zebra"]});  		
			},500);		
	});
</script>
</body>
</html>