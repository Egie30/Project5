<?php
	require_once "framework/database/connect.php";
	require_once "framework/functions/default.php";
	require_once "framework/pagination/pagination.php";

		if (empty($_GET['END_DT'])) {
			$_GET['END_DT'] = date("Y-m-d");
		}

		if (empty($_GET['CO_NBR'])) {
			$_GET['CO_NBR'] = $CoNbrDef;
			$companyNumber = $CoNbrDef;
		} else {
			$companyNumber = $_GET['CO_NBR'];
		}

		if ($_GET['CO_NBR'] != "") {
			$companyNumber = $_GET['CO_NBR'];
		}else { 
			$companyNumber = "";
		}

	$selectedcompany = isset($_GET['CO_NBR']) ? $_GET['CO_NBR'] : '';
	$selectedCategory = isset($_GET['CAT_SUB_NBR']) ? $_GET['CAT_SUB_NBR'] : '';

// try 
// {
		
// 	ob_start();
// 	include __DIR__ . DIRECTORY_SEPARATOR . "ajax/sub-cat-ajax-contoh.php";

// 	$results = json_decode(ob_get_clean());
// } 
//  catch (\Exception $ex) 
// {
// 	ob_end_clean();
// }

// if (count($results->data) == 0) 
// {
// 	echo "<div class='searchStatus'>Data yang ingin ditampilkan tidak tersedia.</div>";
// 	die();
// }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1.0-strict.dtd">
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
						<select name="CO_NBR" id="CO_NBR" style='width:250px' class="chosen-select">
						<!-- <option value="">Pilih Perusahaan</option>  -->
							<?php
							$query = " SELECT CO_NBR,NAME AS CO_NAME FROM CMP.COMPANY
									WHERE NAME LIKE '%champion%'
									ORDER BY CO_NBR ";
							genCombo($query, "CO_NBR", "CO_NAME", $_GET['CO_NBR'],"Pilih Perusahaan");
							?>
						</select>
					</div>
				
					<div style="display: inline-block; float: left; margin-top: 5px; margin-right: 10px;">
						<select name='CAT_SUB_NBR' id='CAT_SUB_NBR' class='chosen-select'>
						<option value="">Pilih Kategori</option> 
							<?php
								$query = "SELECT CAT_NBR,CAT_DESC FROM RTL.CAT ORDER BY 2";
								$resultc = mysql_query($query);
								while ($rowc = mysql_fetch_array($resultc)) {
								echo "<optgroup label='" . $rowc['CAT_DESC'] . "'>";
							
								$query = "SELECT CAT_SUB_NBR,CAT_SUB_DESC
										FROM RTL.CAT_SUB WHERE CAT_NBR=" . $rowc['CAT_NBR'] . " ORDER BY 2";
								$resultd = mysql_query($query);
								while ($rowd = mysql_fetch_array($resultd)) {
									echo "<option value='" . $rowd['CAT_SUB_NBR'] . "'";
									if ($rowd['CAT_SUB_NBR'] == $selectedCategory) {
										echo " selected";
									}
									echo ">";
									echo removeExtraSpaces($rowd['CAT_SUB_DESC']);
									echo "</option>";
								}
								echo "</optgroup>";
								}
							?>
						</select>
					</div>

					<div style="display: inline-block; float: left; margin-top: 5px; margin-right: 0px;">
						<input id="END_DT" name="END_DT" value="<?php echo $_GET['END_DT']; ?>"type="text" size="10" class="livesearch"style="text-align:center;margin-top:0"/>
							<script>new CalendarEightysix('END_DT', {
								'offsetY': -5,
								'offsetX': 2,
								'format': '%Y-%m-%d',
								'prefill': false,
								'slideTransition': Fx.Transitions.Back.easeOut,
								'draggable': true});
							</script>
					</div>
				
					<div style="display: inline-block; float: left; margin-top: 6px; margin-right: 15px;">
						<span id="FLTR_DTE" class="fa fa-calendar toolbar fa-lg" style="padding:3px; cursor:pointer"></span>
					</div>
					
					<div style="display: inline-block; float: right; margin-right: 15px;">
						<span class='fa fa-search fa-flip-horizontal toolbar'></span><input type="text" id="livesearch"class="livesearch"/>
					</div>
			</div>
		</div>
		
		<div class="searchresult" id="liveRequestResults"></div>
			<div id="mainResult" style="border:transparent;">
				<?php
					if (($locked == 0) && ($_COOKIE["LOCK"] != "LOCK")) {
				?>
					<div >
						<div id="tab1" class="sortable"></div>
					</div>
				<?php
					}else{
				?>
					<div >
						<div id="tab2" class="sortable"></div>
					</div>
				<?php
					}
				?>
			</div>

		<script type="text/javascript">
				function setDefaultQuery(url) 
					{
						url.setQuery(URI.parseQuery(location.search));
						url.setQuery("CO_NBR", document.getElementById("CO_NBR").value);
						url.setQuery("END_DT", document.getElementById("END_DT").value);
						url.setQuery("CAT_SUB_NBR", document.getElementById("CAT_SUB_NBR").value);

						return url;
					}

					var url = setDefaultQuery(URI("store-inventory-matter-ls-contoh.php"));
						<?php if(($locked==0) && ($_COOKIE["LOCK"] != "LOCK")) { ?>
							url.setQuery("ACTG", 0);
							getContent("tab1", url.build().toString());
						<?php } else { ?>
							// url.setQuery("ACTG", 1);
							// getContent("tab2", url.build().toString());
						<?php } ?>

					document.getElementById("FLTR_DTE").onclick = function () {
						var url = setDefaultQuery(URI("store-inventory-matter-contoh.php"));

						URI.removeQuery(url, "s");
						URI.removeQuery(url, "page");

						window.scrollTo(0, 0);

						var selectedDate = document.getElementById("END_DT").value;
						var selectedCategory = document.getElementById("CAT_SUB_NBR").value;
						url.setQuery("CAT_SUB_NBR", selectedCategory);
						url.setQuery("END_DT", selectedDate);

						location.href = url.build().toString();
					};
					
					document.getElementById("CAT_SUB_NBR").addEventListener("input", function() {
						var selectedCategory = document.getElementById("CAT_SUB_NBR").value;
						if (selectedCategory === "") {
							var url = setDefaultQuery(URI("store-inventory-matter-ls-contoh.php"));
							URI.removeQuery(url, "s");
							URI.removeQuery(url, "page");
							url.removeQuery("CAT_SUB_NBR");

							var selectedDate = document.getElementById("END_DT").value;
							url.setQuery("END_DT", selectedDate);
						}
					});
		</script>
		<script type="text/javascript">
				var url = setDefaultQuery(new URI("store-inventory-matter-ls-contoh.php"));

				URI.removeQuery(url, "s");
				URI.removeQuery(url, "page");
				
				liveReqInit("livesearch", "liveRequestResults", url.build().toString(), "", "mainResult");
				
		</script>
	</body>
</html>