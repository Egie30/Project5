<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/pagination/pagination.php";
require_once "framework/security/default.php";

$security		= getSecurity($_SESSION['userID'],"Executive");

if (empty($_GET['BEG_DT'])) {
	$_GET['BEG_DT'] = date('Y-m-01');
}

if (empty($_GET['END_DT'])) {
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
	<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" media="screen" href="css/accounting.css" />
	
	<script type="text/javascript">parent.Pace.restart();</script>
	<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
	<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
	<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>
	<script type="text/javascript" src="framework/liveSearch/livesearch.js"></script>
	<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
	<script type="text/javascript" src="framework/tab/tabs.js"></script>
	<script type="text/javascript" src="framework/pagination/pagination.js"></script>
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
	<p class="toolbar-left">
	<div style="display: inline-block; float: left; margin-top: 6px; margin-right: 25px;margin-top:9px">
		<select id="PRSN_NBR" name="PRSN_NBR" class="chosen-select" style="width:200px;">
			<?php 
				if ($security > 6) {
					$_GET['PRSN_NBR'] 	= $_SESSION['personNBR'];
					$whereNumber		= "AND PRSN_NBR = ". $_GET['PRSN_NBR'];
				}

				$query = "SELECT 
					PRSN_NBR, NAME 
				FROM CMP.PEOPLE 
				WHERE POS_TYP IN ('RET','SAO') AND TERM_DTE IS NULL AND DEL_NBR = 0 ". $whereNumber ."
				GROUP BY PRSN_NBR";
				echo $query;
				genCombo($query, "PRSN_NBR","NAME",$_GET['PRSN_NBR'], "Pilih Sales");
			?>
		</select>
	</div>
	
	<div style="display: inline-block; float: left; margin-top: 6px; margin-right: 15px;margin-top:9px">
		Periode
		<select id="PAY_CONFIG_NBR" name="PAY_CONFIG_NBR" class="chosen-select" style="width:200px;">
		<?php 
			$query = "SELECT PAY_CONFIG_NBR,PAY_BEG_DTE FROM PAY.PAY_CONFIG_DTE WHERE PAY_BEG_DTE <= CURRENT_DATE AND PAY_END_DTE >= CURRENT_DATE";
			$result = mysql_query($query);
			$rowDte = mysql_fetch_array($result);
			$PayConfigNbr = $rowDte['PAY_CONFIG_NBR'];
			if ($_GET['PAY_CONFIG_NBR'] == ""){
				$_GET['PAY_CONFIG_NBR'] = $PayConfigNbr;
			}
			
			$query = "SELECT 
				PAY_CONFIG_NBR, PAY_BEG_DTE, PAY_END_DTE, GROUP_CONCAT(PAY_BEG_DTE,' - ',PAY_END_DTE) AS PERIOD_DTE 
			FROM PAY.PAY_CONFIG_DTE
			WHERE (YEAR(PAY_END_DTE) = YEAR(CURRENT_DATE) - 1 OR YEAR(PAY_END_DTE) = YEAR(CURRENT_DATE))
			GROUP BY PAY_CONFIG_NBR
			ORDER BY PAY_BEG_DTE DESC";
			genCombo($query, "PAY_CONFIG_NBR","PERIOD_DTE",$_GET['PAY_CONFIG_NBR'], "Pilih Periode");
		?>
		</select></br>
	</div>
	
	<span id="FLTR_DTE" class="fa fa-calendar toolbar fa-lg" style="padding:3px;padding-left:0px;cursor:pointer;margin-top: 10px;" title="Filter berdasarkan tanggal">
	</span>

	<div style="display: inline-block; float: right; margin-top: 0px; margin-right: 15px;">
	 	<?php buildComboboxLimit();?>
	<span class='fa fa-search fa-flip-horizontal toolbar'></span><input type="text" id="livesearch" class="livesearch" />
	</div>
	</p>
</div>
<br>
<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult"></div>

<script type="text/javascript">
	function setDefaultQuery(url) {
		url.setQuery(URI.parseQuery(location.search));
		url.setQuery("PAY_CONFIG_NBR", document.getElementById("PAY_CONFIG_NBR").value);
		url.setQuery("PRSN_NBR", document.getElementById("PRSN_NBR").value);

		return url;
	}

	var url = setDefaultQuery(new URI("creative-hub-bonus-report-ls.php"));

	url.setQuery("PAY_CONFIG_NBR", '<?php echo $_GET['PAY_CONFIG_NBR'];?>');
	url.setQuery("PRSN_NBR", '<?php echo $_GET['PRSN_NBR'];?>');
	getContent("mainResult", url.build().toString());

	document.getElementById("FLTR_DTE").onclick = function() {
		var url = setDefaultQuery(new URI("creative-hub-bonus-report.php"));

		URI.removeQuery(url, "s");
		URI.removeQuery(url, "page");
	
		window.scrollTo(0,0);

		location.href = url.build().toString();
	};
</script>
<script type="text/javascript">
	var url = setDefaultQuery(new URI("creative-hub-bonus-report-ls.php"));

	URI.removeQuery(url, "s");
	URI.removeQuery(url, "page");
	
	liveReqInit("livesearch", "liveRequestResults", url.build().toString(), "", "mainResult");
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