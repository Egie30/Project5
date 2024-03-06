<?php 
	include "framework/database/connect-cloud.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";

	$Security 		= getSecurity($_SESSION['userID'],"Executive");
	$PayConfigNbr 	= $_GET['PAY_CONFIG_NBR'];

	//Process changes here
	if(($_POST['PAY_CONFIG_NBR']!="")&&($cloud!=false)){
		$j=syncTable("PAY_CONFIG_DTE","PAY_CONFIG_NBR","PAY",$PAY,$local,$cloud);

		$PayConfigNbr = $_POST['PAY_CONFIG_NBR'];

		//Process add new
		if($PayConfigNbr==-1){
			$query 	     = "SELECT COALESCE(MAX(PAY_CONFIG_NBR),0)+1 AS NEW_NBR FROM $PAY.PAY_CONFIG_DTE";
			$result      = mysql_query($query,$cloud);
			$row         = mysql_fetch_array($result);
			$PayConfigNbr= $row['NEW_NBR'];
			$query       = "INSERT INTO $PAY.PAY_CONFIG_DTE (PAY_CONFIG_NBR) VALUES (".$PayConfigNbr.")";
			$result      = mysql_query($query,$cloud);
			$query       = str_replace($PAY,"PAY",$query);
			$result      = mysql_query($query,$local);
		}

		if($_POST['PAY_BEG_DTE']==""){$PayBegDte="NULL";}else{$PayBegDte="'".$_POST['PAY_BEG_DTE']."'";}
		if($_POST['PAY_END_DTE']==""){$PayEndDte="NULL";}else{$PayEndDte="'".$_POST['PAY_END_DTE']."'";}
		if($_POST['PAY_ACT_F']=="on"){$PayActF=1;}else{$PayActF=0;}

		$query  = "UPDATE $PAY.PAY_CONFIG_DTE SET PAY_BEG_DTE = ".$PayBegDte.",
						  PAY_END_DTE = ".$PayEndDte.",
						  PAY_ACT_F= ".$PayActF.",
						  UPD_TS=CURRENT_TIMESTAMP 
					WHERE PAY_CONFIG_NBR = ".$PayConfigNbr;
		$result = mysql_query($query,$cloud);
		$query  = str_replace($PAY,"PAY",$query);
		$result = mysql_query($query,$local);
	}


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
<script type="text/javascript">
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
</head>
<body>
	<?php 
		$query = "SELECT PAY_CONFIG_NBR,
						 PAY_BEG_DTE,
						 PAY_END_DTE,
						 PAY_ACT_F
					FROM PAY.PAY_CONFIG_DTE 
					WHERE PAY_CONFIG_NBR = ".$PayConfigNbr;
		$result = mysql_query($query, $local);
		$row    = mysql_fetch_array($result);
	?>
	<form enctype="multipart/form-data" action="#" method="post" style="width:600px">
		<p>
			<h2>
				Nomor Konfigurasi Tanggal Payroll : <?php echo $row['PAY_CONFIG_NBR'];if($row['PAY_CONFIG_NBR']==0){echo "Baru";} ?>
			</h2>

			<input name="PAY_CONFIG_NBR" value="<?php echo $row['PAY_CONFIG_NBR'];if($row['PAY_CONFIG_NBR']==""){echo "-1";} ?>" type="hidden" />

			<label class='side'>Tanggal Awal</label>
			<input id="PAY_BEG_DTE" name="PAY_BEG_DTE" value="<?php echo $row['PAY_BEG_DTE']; ?>" type="text" size="20" /><br />
			<script>
				new CalendarEightysix('PAY_BEG_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
			</script>

			<label class='side'>Tanggal Akhir</label>
			<input id="PAY_END_DTE" name="PAY_END_DTE" value="<?php echo $row['PAY_END_DTE']; ?>" type="text" size="20" /><br />
			<script>
				new CalendarEightysix('PAY_END_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
			</script>

			<label class='side'>Status</label>
			<div class='side' style='top:4px'>
				<input name='PAY_ACT_F' id='PAY_ACT_F' type='checkbox' class='regular-checkbox' <?php if($row['PAY_ACT_F']=="1"){echo "checked";} ?>/>&nbsp;<label for="PAY_ACT_F"></label>
			</div><br/>
			<?php
				if(($cloud!=false)&&(paramCloud()==1)){	
					echo "<input class='process' type='submit' value='Simpan'/>";
				}
			?>
		</p>
	</form>
</body>
</html>