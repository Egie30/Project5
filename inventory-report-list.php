<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

if (empty($_GET['BEG_DT'])) {
	$_GET['BEG_DT'] = date("Y-m-d", strtotime("-1 day"));
}

if (empty($_GET['END_DT'])) {
	$_GET['END_DT'] = date("Y-m-d", strtotime("-1 day"));
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/tab/tabs.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" />

	<script type="text/javascript">parent.Pace.restart();</script>
	<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
	<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
	<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>
	<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
	<script type="text/javascript" src='framework/jquery/jquery-latest.min.js'></script>
	<script type="text/javascript" src='framework/tab/tabs.js'></script>
	<script type="text/javascript" src='framework/jquery-pjax/jquery.pjax.js'></script>
	<script type="text/javascript" src='framework/tablesort/tablesort.js'></script>
	<script type="text/javascript" src='framework/tablesort/customsort.js'></script>
	<script type="text/javascript" src='framework/phpjs/functions/strings/number_format.js'></script>
	<script type="text/javascript" src='framework/uri/src/URI.min.js'></script>
	<script type="text/javascript" src='framework/functions/default.js'></script>
	<script type="text/javascript" src='framework/jquery-freezeheader/js/jquery.freezeheader.js'></script>
	<script type="text/javascript">
	jQuery.noConflict();

	(function($) {
		function ajaxLoad($table, $searchStatus, parameters) {
			parameters = $.extend(
				$.extend(URI.parseQuery(location.search), {GROUP: ['RTL_BRC', 'TRSC_NBR']}),
				parameters
			);

			$.ajax({
				url: "ajax/forms-sale-item.php",
				type: 'GET',
				data: parameters,
				error: function(jqXHR, textStatus, errorThrown) {
					$searchStatus.html("Request failed: " + textStatus);
				},
				success: function(response) {
					try{
						response = $.parseJSON(response);
					} catch (errorThrown) {
						$searchStatus.html("Request failed: " + errorThrown);

						return;
					}

					var responseTotal = response.data.length;
					var total = response.total;

					if (responseTotal > 0) {
						var $tr = $("<tr/>");

						$tr.append($('<td class="std" style="text-align:right" colspan="9">Total:</td>'));
						$tr.append($('<td class="std" style="text-align:right">' + number_format(total.RTL_Q, 0, ',', '.') + '</td>'));
						$tr.append($('<td class="std" style="text-align:right">' + number_format(total.RTR_Q, 0, ',', '.') + '</td>'));
						$tr.append($('<td class="std" style="text-align:right">' + number_format(total.INV_PRC, 0, ',', '.') + '</td>'));
						$tr.append($('<td class="std" style="text-align:right">' + number_format(total.INV_RTL_PRC, 0, ',', '.') + '</td>'));
						$tr.append($('<td class="std" style="text-align:right">' + number_format(total.INV_PRC_AMT, 0, ',', '.') + '</td>'));
						$tr.append($('<td class="std" style="text-align:right">' + number_format(total.BRT_AMT, 0, ',', '.') + '</td>'));
						$tr.append($('<td class="std" style="text-align:right">' + number_format(total.DISC_AMT, 0, ',', '.') + '</td>'));
						$tr.append($('<td class="std" style="text-align:right">' + number_format(total.NETT_AMT, 0, ',', '.') + '</td>'));

						$table.find("tfoot").append($tr);

						for (var i = 0; i < responseTotal; i++) {
							var data = response.data[i];

							var $tr = $("<tr></tr>"),
								transactionNumber = data.TRSC_NBR;

							if (parameters.PLUS == 1) {
								transactionNumber = data.TRSC_NBR_PLUS;
							}

							parameters.TRSC_NBR = data.TRSC_NBR;
							<?php if (!$_SESSION['PLUS_MODE']) { ?>
							parameters.PLUS = null;
							<?php } ?>

							$tr.attr('parameters', $.param(parameters))
								.css('cursor', 'pointer')
								.on('click', function() {
								var parameter = $(this).attr('parameters');

								$table.fadeOut("fast", function() {
									$.pjax({
										url: 'forms-sale-day-detail.php?' + parameter,
										container: "#mainResult"
									});
								});
							});

							$tr.append($('<td class="std" style="text-align:center">' + (i + 1) + '.</td>'));
							$tr.append($('<td class="std" style="text-align:center">' + data.Q_NBR + '-' + transactionNumber + '</td>'));
							$tr.append($('<td class="std" style="text-align:center">' + data.CRT_DTE + '</td>'));
							$tr.append($('<td class="std" style="text-align:center">' + data.CRT_HR + ':' + data.CRT_MNT + '</td>'));
									
							if (data.INV_NAME != "Unknown") {
								$tr.append($('<td class="std" style="text-align:left;white-space:nowrap">' + data.INV_NAME + '</td>'));
							} else {
								$tr.append($('<td class="std" style="text-align:left;white-space:nowrap"><div class="label red">' + data.INV_NAME + '</div></td>'));
							}

							if (data.SPL_NAME != "Unknown") {
								$tr.append($('<td class="std" style="text-align:left;white-space:nowrap">' + data.SPL_NAME + '</td>'));
							} else {
								$tr.append($('<td class="std" style="text-align:left;white-space:nowrap"><div class="label red">' + data.SPL_NAME + '</div></td>'));
							}

							$tr.append($('<td class="std" style="text-align:left;white-space:nowrap;">' + data.CAT_DESC + '</td>'));
							$tr.append($('<td class="std" style="text-align:left;white-space:nowrap;">' + data.CAT_SUB_DESC + '</td>'));
							$tr.append($('<td class="std" style="text-align:center">' + data.RTL_BRC + '</td>'));
							$tr.append($('<td class="std" style="text-align:right">' + number_format(data.RTL_Q, 0, ',', '.') + '</td>'));
							$tr.append($('<td class="std" style="text-align:right">' + number_format(data.RTR_Q, 0, ',', '.') + '</td>'));
							$tr.append($('<td class="std" style="text-align:right">' + number_format(data.INV_PRC, 0, ',', '.') + '</td>'));
							$tr.append($('<td class="std" style="text-align:right">' + number_format(data.INV_RTL_PRC, 0, ',', '.') + '</td>'));
							$tr.append($('<td class="std" style="text-align:right">' + number_format(data.INV_PRC_AMT, 0, ',', '.') + '</td>'));
							$tr.append($('<td class="std" style="text-align:right">' + number_format(data.BRT_AMT, 0, ',', '.') + '</td>'));
							$tr.append($('<td class="std" style="text-align:right">' + number_format(data.DISC_AMT, 0, ',', '.') + '</td>'));
							$tr.append($('<td class="std" style="text-align:right">' + number_format(data.NETT_AMT, 0, ',', '.') + '</td>'));

							$table.find("tbody").append($tr);
						};
					}

					fdTableSort.init();

					$searchStatus.fadeOut("fast", function() {
						$table.fadeIn(function() {
							$table.freezeHeader();
						});
					});
				}
			});
		}

		$(document).ready(function() {
			$("#FLTR_DTE").on("click", function () {
				window.scrollTo(0,0);

				if (getCurDate() == $("#BEG_DT").val() || getCurDate() == $("#END_DT").val()) {
					parent.document.getElementById("reportToday").style.display=  'block';
					parent.document.getElementById("fade").style.display = "block";
				} else {
					location.href = "?CO_NBR=<?php echo $_GET['CO_NBR']?>&BEG_DT=" + $("#BEG_DT").val() + "&END_DT=" + $("#END_DT").val()
						+ "&CNMT_F=<?php echo $_GET['CNMT_F']?>&SPL_NBR=<?php echo $_GET['SPL_NBR'];?>&CAT_SUB_NBR=<?php echo $_GET['CAT_SUB_NBR'];?>&PLUS=<?php echo $_GET['PLUS'];?>&INV_NBR=<?php echo $_GET['INV_NBR'];?>";
				}
			});

			<?php if ($_GET['PLUS'] == "") { ?>
			$(".tabContents").each(function() {
			<?php } else { ?>
			$(".tabContaier").each(function() {
			<?php } ?>
				var $table = $("#template table").clone(),
					$searchStatus = $(this).find(".searchStatus"),
					pkpMode = $(this).data('pkp'),
					parameters = {
						BEG_DT: '<?php echo $_GET['BEG_DT'];?>',
						END_DT: '<?php echo $_GET['END_DT'];?>',
						PLUS: pkpMode
					};

				<?php if ($_GET['PLUS'] != "") { ?>
				parameters.PLUS = <?php echo $_GET['PLUS'];?>;
				<?php } ?>

				$(this).append($table);

				ajaxLoad($table, $searchStatus, parameters);
			});
		});
	})(jQuery);
	</script>
	<script type="text/javascript">
		parent.document.getElementById("reportTodayYes").onclick = function () { 
			window.scrollTo(0,0);
			parent.document.getElementById("reportToday").style.display = "none";
			parent.document.getElementById("fade").style.display = "none";
			location.href = "?CO_NBR=<?php echo $_GET['CO_NBR']?>&BEG_DT=" + document.getElementById("BEG_DT").value + "&END_DT=" + document.getElementById("END_DT").value
				+ "&CNMT_F=<?php echo $_GET['CNMT_F']?>&SPL_NBR=<?php echo $_GET['SPL_NBR'];?>&CAT_SUB_NBR=<?php echo $_GET['CAT_SUB_NBR'];?>&PLUS=<?php echo $_GET['PLUS'];?>&INV_NBR=<?php echo $_GET['INV_NBR'];?>";
		};
	</script>
</head>
<body>
<div class="toolbar">
	<p class="toolbar-left">
		&nbsp;
		<input id="BEG_DT" name="BEG_DT" value="<?php echo $_GET['BEG_DT'];?>" type="text" size="10" class="livesearch" style="text-align:center" />
		<script>new CalendarEightysix('BEG_DT', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });</script>
		<input id="END_DT" name="END_DT" value="<?php echo $_GET['END_DT'];?>" type="text" size="10" class="livesearch" style="text-align:center" />
		<script>new CalendarEightysix('END_DT', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });</script>
		<img id="FLTR_DTE" class="toolbar-right" src="img/date.png" style="padding-left:0px;cursor:pointer" title="Filter berdasarkan tanggal">
	</p>
</div>

<div id="mainResult" class="tabContaier">
	<div class='searchStatus'><img src="img/wait.gif" style="vertical-align: middle;border:0px"><span>Please Wait ...</span></div>
</div>

<div id="template">
	<table class="std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable" style="display:none;">
		<thead>
			<tr>
				<th class="sortable">No.</th>
				<th class="sortable">Nota</th>
				<th class="sortable sortable-date-dmy">Tanggal</th>
				<th class="sortable">Waktu</th>
				<th class="sortable">Nama</th>
				<th class="sortable">Suplier</th>
				<th class="sortable">Departemen</th>
				<th class="sortable">Sub Departemen</th>
				<th class="sortable">Barcode</th>
				<th class="sortable sortable-sortDutchCurrencyValues">Item</th>
				<th class="sortable sortable-sortDutchCurrencyValues">Retur Item</th>
				<th class="sortable sortable-sortDutchCurrencyValues">Harga Beli</th>
				<th class="sortable sortable-sortDutchCurrencyValues">Harga Jual</th>
				<th class="sortable sortable-sortDutchCurrencyValues">Subtotal Beli</th>
				<th class="sortable sortable-sortDutchCurrencyValues">Subtotal</th>
				<th class="sortable sortable-sortDutchCurrencyValues">Disc</th>
				<th class="sortable sortable-sortDutchCurrencyValues">Net</th>
			</tr>
		</thead>
		<tbody></tbody>
		<tfoot></tfoot>
	</table>
</div>
</body>
</html>