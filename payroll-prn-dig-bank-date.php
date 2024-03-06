<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";

	$CoNbr 	= $_GET['CO_NBR'];
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" href="framework/combobox/chosen.css">
<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />

<script type="text/javascript" src="framework/validation/mootools-1.2.3.js"></script>
<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>
<script type="text/javascript">jQuery.noConflict();</script>
<body>
<script> 
	
//jQuery.noConflict();
	window.addEvent('domready', function() {
	//Datepicker
	new CalendarEightysix('textbox-id');
	//Calendar
	new CalendarEightysix('block-element-id');
	});
	MooTools.lang.set('id-ID', 'Date', {
		months:    ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
		days:      ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
		dateOrder: ['date', 'month', 'year', '/']
	});
	MooTools.lang.setLanguage('id-ID');
</script>

<script type="text/javascript">
	function formSubmit(){
		parent.document.getElementById('content').src='payroll-prn-dig-bank.php?CO_NBR=<?php echo $CoNbr;?>'
			+ '&PAYROLL_DTE=' + document.getElementById('PAYROLL_DTE').value;
		parent.document.getElementById('datePayrollPopupEdit').style.display='none';
		parent.document.getElementById('fade').style.display='none';
	}
</script>

<span class='fa fa-times toolbar' style='cursor:pointer' onclick="parent.document.getElementById('datePayrollPopupEdit').style.display='none';parent.document.getElementById('fade').style.display='none'"></span>
<?php 
	function getDatePay($start,$periode){
		$start   = date("Y-m-d",strtotime($start.'1 Day')); //tgl hari ini+1 hari
		$day     = date("Y-m-d", strtotime($start));

		//Holiday
		$query   = "SELECT HLDY_DTE FROM PAY.HOLIDAY WHERE MONTH(HLDY_DTE)='".date('m', strtotime($start))."' AND YEAR(HLDY_DTE)='".date('Y', strtotime($start))."'";
		$result  = mysql_query($query);
		while ($row= mysql_fetch_array($result)) {
			$holiday[]= $row['HLDY_DTE'];
		}

		//Cek Saturday
		$querySat = "SELECT WEEKDAY('".$start."') AS DAY";
		$resSat   = mysql_query($querySat);
		$rowSat   = mysql_fetch_array($resSat);
		$DayIndex = $rowSat['DAY'];
		
		if (in_array($start, $holiday)){ //apakah tgl hari ini+1 hari adalah hari libur?
			return getDatePay($start, $periode); //jika ya (berarti nambah 1 hari lagi)
		}else { //jika bukan hari libur
			if ($DayIndex == 5){ //jika hari sabtu?
				return getDatePay($start, $periode);
			}else{
				if($periode<=1){
					$periode=$periode+1;
					return getDatePay($start, $periode);
				}else{
					return $day;
				}
			}
		}
		
	}

	//echo date('Y-m-d');
?>
<form enctype="multipart/form-data" action="" method="post" style="width:250px">
		
		<label>Tanggal</label><br />
		<input id="PAYROLL_DTE" name="PAYROLL_DTE" value="<?php echo getDatePay(date('Y-m-d'),1); ?>" type="text" size="15" /><br />
		<script>
			new CalendarEightysix('PAYROLL_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
		</script>

		<input class='process' type='submit' value='Simpan' onclick="formSubmit()"/>
	
</form>



</body>

<script src="framework/database/jquery.min.js" type="text/javascript"></script>
<script src="framework/combobox/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript">
	jQuery.noConflict();
		var config = {
			'.chosen-select'           : {},
			'.chosen-select-deselect'  : {allow_single_deselect:true},
			'.chosen-select-no-single' : {disable_search_threshold:10},
			'.chosen-select-no-results': {no_results_text:'Data tidak ketemu'},
			'.chosen-select-width'     : {width:"95%"}
	   	};
		for (var selector in config) {
			jQuery(selector).chosen(config[selector]);
		}
</script>
</html>
