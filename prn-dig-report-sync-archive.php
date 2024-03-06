<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/pagination/pagination.php";


if (empty($_GET['BEG_DT'])) {
	$_GET['BEG_DT'] = date('Y-m-01');
}

if (empty($_GET['END_DT'])) {
	$_GET['END_DT'] = date('Y-m-d');
}
	
if (empty($_GET['GROUP'])) {
	$_GET['GROUP'] = "ORD_NBR";
}

if(($locked != 0) && ($_COOKIE["LOCK"] == "LOCK")) {
	$_GET['ACTG'] = 1;
}

$PaymentType	= $_GET['PYMT_TYP'];
$Type			= $_GET['TYP'];
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
	<script>
		function checkConvert(){
			var c=document.getElementsByTagName('input');
			var queryStr='';
				for(var i=0;i<c.length;i++){
				if(c[i].type=='checkbox') {
					if(c[i].name.substr(0,8)=='SEL_IMG_'){
						if(c[i].checked){;
							queryStr+=c[i].name.substr(8,c[i].name.length-8)+',';
						}
					}
				}
			}
			
			parent.document.getElementById('printDigitalPopupBarcodeContent').src='prn-dig-report-sync.php?BEG_DT=<?php echo $_GET['BEG_DT']; ?>&END_DT=<?php echo $_GET['END_DT']; ?>&ORD_NBR='+queryStr.substr(0,queryStr.length-1);
			parent.document.getElementById('printDigitalPopupBarcode').style.display='block';
			parent.document.getElementById('fade').style.display='block';
		};
		
	</script>
</head>
<body>
<div class="toolbar">
	<div class="combobox"></div>
	<div class="toolbar-text">
				
	<div style="display: inline-block; float: left; margin-top: 6px;">
		
		<?php if(($locked==0) && ($_COOKIE["LOCK"] != "LOCK")) { ?>
		<span style="display: inline-block; float: left; margin-right: 15px;">
			<select name="ACTG" id="ACTG" style="width: 100px;" class="chosen-select">
				<option value="0" <?php if($_GET['ACTG'] == 0 || $_GET['ACTG'] == ""){ echo "selected"; } ?>>Rekening</option>
				<option value="1" <?php if($_GET['ACTG'] == 1){ echo "selected"; } ?>>PT</option>
				<option value="2" <?php if($_GET['ACTG'] == 2){ echo "selected"; } ?>>CV</option>
				<option value="3" <?php if($_GET['ACTG'] == 3){ echo "selected"; } ?>>PR</option>
			</select>
		</span>
		<?php } ?>
		
		<?php if($Type == 'PAY') { ?>
		
		<span style="display: inline-block; float: left; margin-right: 15px;">
			<select name="PYMT_TYP" id="PYMT_TYP" style='width:200px;' class="chosen-select" >
				<option value="">Semua Tipe Pembayaran</option>
				<?php
					
					$query="SELECT PYMT_TYP, PYMT_DESC FROM RTL.PYMT_TYP
							ORDER BY PYMT_TYP";
					
					genCombo($query, "PYMT_TYP", "PYMT_DESC", $PaymentType);
				?>
			</select>
		</span>
		<?php } ?>
		
		<span style="display: inline-block; float: left; margin-right: 4px;">		
			<input id="BEG_DT" name="BEG_DT" value="<?php echo $_GET['BEG_DT'];?>" type="text" size="10" class="livesearch" style="text-align:center;margin-top:0" />
			<script>new CalendarEightysix('BEG_DT', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });</script>
			
		</span>	
		
		<span style="display: inline-block; float: left; margin-right: 4px;">		
			<input id="END_DT" name="END_DT" value="<?php echo $_GET['END_DT'];?>" type="text" size="10" class="livesearch" style="text-align:center;margin-top:0" />
			<script>new CalendarEightysix('END_DT', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });</script>
			
		</span>	
		
		</div>
		
		<div style="display: inline-block; float: left; margin-top: 7px; margin-right: 0px;">
			<span id="FLTR_DTE" class="fa fa-calendar toolbar fa-lg"  style="padding:3px;padding-left:10px;cursor:pointer"></span>
		</div>
		
		<?php if(($locked==0) && ($_COOKIE["LOCK"] != "LOCK")) { ?>
		<div style="display: inline-block; float: right; margin-right: 15px;">
			<span title="Copy Data" class="fa fa-copy toolbar" href="javascript:void(0)" onClick="checkConvert()" style="cursor:pointer"></span>
		</div>
		<?php } ?>
	</div>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<?php if(($locked==0) && ($_COOKIE["LOCK"] != "LOCK")) { ?>
	<br />
<?php } ?>	

<div id="mainResult" style="border:transparent;"></div>

<script type="text/javascript">

	function setDefaultQuery(url) {
		url.setQuery(URI.parseQuery(location.search));
		<?php if($Type == 'PAY') { ?>
		url.setQuery("PYMT_TYP", document.getElementById("PYMT_TYP").value);
		<?php } ?>
		url.setQuery("ACTG", document.getElementById("ACTG").value);
		url.setQuery("BEG_DT", document.getElementById("BEG_DT").value);
		url.setQuery("END_DT", document.getElementById("END_DT").value);
				
		return url;
	}

	var url = setDefaultQuery(new URI("prn-dig-report-sync-archive-ls.php"));

	url.setQuery("ACTG", '<?php echo $_GET['ACTG'];?>');
	url.setQuery("BEG_DT", '<?php echo $_GET['BEG_DT'];?>');
	url.setQuery("END_DT", '<?php echo $_GET['END_DT'];?>');
	
	getContent("mainResult", url.build().toString());
	
	document.getElementById("FLTR_DTE").onclick = function() {
		var url = setDefaultQuery(new URI("prn-dig-report-sync-archive.php"));

		URI.removeQuery(url, "s");
		URI.removeQuery(url, "page");
	
		window.scrollTo(0,0);

		location.href = url.build().toString();
	}

</script>
<script type="text/javascript">
	var url = setDefaultQuery(new URI("prn-dig-report-sync-archive-ls.php"));

	URI.removeQuery(url, "s");
	URI.removeQuery(url, "page");
	
	liveReqInit("livesearch", "liveRequestResults", url.build().toString(), "", "mainResult");
</script>
</body>
</html>