<?php
include "framework/database/connect-cloud.php";
require_once "framework/functions/default.php";
require_once "framework/security/default.php";

$notif = $_GET['NTFY_NBR'];
$security = getSecurity($_SESSION['userID'], "Executive");	


	if ($_POST['NTFY_NBR'] != ""&&($cloud!=false)) {
		$notif = $_POST['NTFY_NBR'];

		if($_POST['NTFY_TTL']==""){$NTFY_TTL="NULL";}else{$NTFY_TTL="'".$_POST['NTFY_TTL']."'";}
		if($_POST['NTFY_DESC']==""){$NTFY_DESC="NULL";}else{$NTFY_DESC="'".$_POST['NTFY_DESC']."'";}
		if($_POST['NTFY_TYP']==""){$NTFY_TYP="NULL";}else{$NTFY_TYP="'".$_POST['NTFY_TYP']."'";}
		if($_POST['BEG_DT']==""){$BEG_DT="NULL";}else{$BEG_DT="'".$_POST['BEG_DT']."'";}
		if($_POST['END_DT']==""){$END_DT="NULL";}else{$END_DT="'".$_POST['END_DT']."'";}
		
		if ($notif == -1) {
			$query = "SELECT COALESCE(MAX(NTFY_NBR),0) + 1 AS NEW_NBR FROM $CMP.NTFY";
			// echo $query;
			$result=mysql_query($query,$cloud);
			$row = mysql_fetch_array($result);
			$notif = $row['NEW_NBR'];
			
			$query       = "INSERT INTO $CMP.NTFY (NTFY_NBR, CRT_TS, CRT_NBR) VALUES (" . $notif . ", CURRENT_TIMESTAMP, " . $_SESSION['personNBR'] . ")";
			// echo $query;
			$result=mysql_query($query,$cloud);
		
		}

		$query = "UPDATE $CMP.NTFY SET
				NTFY_TTL 		='" . $_POST['NTFY_TTL'] . "',
				NTFY_DESC 		='" . $_POST['NTFY_DESC'] . "',
				NTFY_TYP 		='" . $_POST['NTFY_TYP'] . "',
				BEG_DT 			='" . $_POST['BEG_DT'] . "',
				END_DT 			='" . $_POST['END_DT'] . "',
				UPD_TS			=	CURRENT_TIMESTAMP,
				UPD_NBR			=".$_SESSION['personNBR']."
				WHERE NTFY_NBR 	= " . $notif;

		 	$result=mysql_query($query,$cloud);

	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<script>parent.Pace.restart();</script>
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />

	<script type="text/javascript" src="framework/validation/mootools-1.2.3.js"></script>
	<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
	<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
	<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>

	<script type="text/javascript" src="framework/functions/default.js"></script>

	<script type="text/javascript">jQuery.noConflict();</script>

	<link rel="stylesheet" href="framework/combobox/chosen.css">

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

<script>
	parent.document.getElementById('addressDeleteYes').onclick=
	function () {
		parent.document.getElementById('content').src='notif.php?DEL_L=<?php echo $notif; ?>';
		parent.document.getElementById('addressDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none';
	};
</script>

<?php
$query="SELECT NTFY_NBR, NTFY_TTL, NTFY_DESC,NTFY_TYP, BEG_DT, END_DT
	FROM $CMP.NTFY
	WHERE NTFY_NBR = " . $notif;

	// echo $query;
	$result=mysql_query($query,$cloud);
	$row = mysql_fetch_array($result);

if(empty($row) || ($row['NTFY_DESC'] != 0)) {	$readonly = "";	}
	else { $readonly = "readonly"; }
	
?>


<?php if (($Security==0)&&($notif!=0)) { ?>
	<div class="toolbar-only">
	<p class="toolbar-left"><?php if(($cloud!=false)&&(paramCloud()==1)){ ?><a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('addressDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><span class='fa fa-trash toolbar' style="cursor:pointer"></span></a><?php } ?></p>
	</div>
<?php } ?>



<form action="#" method="post" style="width:700px" autocomplete="off">
	<p>
		<h2>
			Nomor Notifikasi : 
			<?php if ((!$cloud)&&$row['NTFY_NBR'] == "") {
				echo "<script>window.scrollTo(0,0);parent.document.getElementById('offline').style.display='block';parent.document.getElementById('fade').style.display='block';</script>";
			} 
				echo $row['NTFY_NBR'];if($row['NTFY_NBR']==""){echo "Baru";}
			?>
		</h2>
		<input name="NTFY_NBR" value="<?php echo $row['NTFY_NBR'];if($row['NTFY_NBR']==""){echo "-1";} ?>" type="hidden" />

		<label class="side" >Judul</label>
		<input style="" id="NTFY_TTL" name="NTFY_TTL" value="<?php echo $row['NTFY_TTL']; ?>" type="text" size="50" /><br />	

		<label class="side">Deskripsi</label>
		<input id="NTFY_DESC" name="NTFY_DESC" value="<?php echo $row['NTFY_DESC']; ?>" type="text" style="width:70%;"/><br />	

		<div class="side">
		<label class="side">Tipe</label>
		<select id="NTFY_TYP" name="NTFY_TYP" class="chosen-select" style="width:150px;" onchange="selectDetail(this);">
			<option value="Warning"  <?php echo $row['NTFY_TYP'] == 'Warning' ? 'selected=""' : ''; ?> >Warning</option>
			  <option value="Promo" <?php echo $row['NTFY_TYP'] == 'Promo' ? 'selected=""' : ''; ?>>Promo</option>
			  <option value="News" <?php echo $row['NTFY_TYP'] == 'News' ? 'selected=""' : ''; ?>>News</option>
		</select>
		</div>

		<br />
		<label class="side">Mulai</label>
		<input id="BEG_DT" name="BEG_DT" value="<?php echo $row['BEG_DT']; ?>" type="text" size="30" <?php echo $readonly; ?> />
		<script>
			new CalendarEightysix('BEG_DT', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
		</script>
		<br />

		<label class="side">Selesai</label>
		<input id="END_DT" name="END_DT" value="<?php echo $row['END_DT']; ?>" type="text" size="30" <?php echo $readonly; ?> />
			<script>
			new CalendarEightysix('END_DT', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
		</script>
		</span>

		<br />
		<?php
			if(($cloud!=false)&&(paramCloud()==1)){
			echo "<input class='process' type='submit' value='Simpan'/>";
			}
			?>
	</p>
</form>

		
<script type="text/javascript"  src="framework/database/jquery.min.js"></script>
<script src="framework/combobox/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript">
	jQuery.noConflict();
	var config = {
			'.chosen-select'           : {},
			'.chosen-select-deselect'  : {allow_single_deselect:true},
			'.chosen-select-no-single' : {disable_search_threshold:10},
			'.chosen-select-no-results': {no_results_text:'Data tidak ketemu'},
			'.chosen-select-width'     : {width:"95%"}
   	}
	for (var selector in config) {
		jQuery(selector).chosen(config[selector]);
	}
</script>
</body>
</html>
