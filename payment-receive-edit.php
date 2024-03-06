<?php
include "framework/database/connect.php";
include "framework/functions/default.php";
include "framework/security/default.php";
include "framework/functions/dotmatrix.php";

$paymentRcvNbr	= $_GET['PYMT_RCV_NBR'];
$Security		= getSecurity($_SESSION['userID'],"Finance");

if($_POST['PYMT_RCV_NBR']!=""){
	$paymentRcvNbr=$_POST['PYMT_RCV_NBR'];

	//Process add new
	if($paymentRcvNbr==-1){
		$query="SELECT COALESCE(MAX(PYMT_RCV_NBR),0)+1 AS NEW_NBR FROM RTL.PYMT_RCV";
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		//echo $query;
		$paymentRcvNbr=$row['NEW_NBR'];
		$query="INSERT INTO RTL.PYMT_RCV (PYMT_RCV_NBR,CRT_TS,CRT_NBR) VALUES (".$paymentRcvNbr.",CURRENT_TIMESTAMP,".$_SESSION['personNBR'].")";
		$result=mysql_query($query);
	}
	
	//Take care of nulls
	if($_POST['PYMT_RCV_DTE']==""){$paymentRcvDte="NULL";}else{$paymentRcvDte=$_POST['PYMT_RCV_DTE'];}
	if($_POST['SHP_CO_NBR']==""){$shipperNbr="NULL";}else{$shipperNbr=$_POST['SHP_CO_NBR'];}
	if($_POST['PYMT_TYP']==""){$paymentType="NULL";}else{$paymentType=$_POST['PYMT_TYP'];}
	if($_POST['TND_AMT']==""){$TndAmt="0";}else{$TndAmt=$_POST['TND_AMT'];}
	$query="UPDATE RTL.PYMT_RCV SET 
		PYMT_RCV_DTE='".$_POST['PYMT_RCV_DTE']."',
		SHP_CO_NBR=".$shipperNbr.",
		REF_NBR='".$_POST['REF_NBR']."',
		PYMT_TYP='".$paymentType."',
		TND_AMT='".$TndAmt."',
		UPD_TS=CURRENT_TIMESTAMP,
		UPD_NBR=".$_SESSION['personNBR']."
		WHERE PYMT_RCV_NBR=".$paymentRcvNbr;
	$result=mysql_query($query);
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
	<link rel="stylesheet" href="framework/combobox/chosen.css">
	<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
	<script type="text/javascript" src="framework/validation/mootools-1.2.3.js"></script>
	<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
	<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
	<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>
	<script type="text/javascript">jQuery.noConflict()</script>

	<script> 
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
	<script>
		parent.document.getElementById('DeleteDataYes').onclick=
		function () {
			parent.document.getElementById('content').src='payment-receive.php?DEL_A=<?php echo $paymentRcvNbr ?>';
			parent.document.getElementById('DeleteData').style.display='none';
			parent.document.getElementById('fade').style.display='none';
		};
	</script>
</head>
<body>


<?php
	$query="SELECT 
		PYMT_RCV_NBR,
		PYMT_RCV_DTE,
		SHP_CO_NBR,
		REF_NBR,
		TND_AMT,
		PYMT_TYP
	FROM RTL.PYMT_RCV 
	WHERE PYMT_RCV_NBR=".$paymentRcvNbr;
	//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
?>
<div class="toolbar-only">
	<?php if(($Security==0)&&($paymentRcvNbr!=0)) { ?>
		<p class="toolbar-left">
			<a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('DeleteData').style.display='block';parent.document.getElementById('fade').style.display='block'"><span class="fa fa-trash toolbar" style="cursor:pointer"></span></a>
		</p>
		<p class="toolbar-right">
			<a href="payment-receive-detail-list.php?PYMT_RCV_NBR=<?php echo $row['PYMT_RCV_NBR']; ?>&SHP_CO_NBR=<?php echo $row['SHP_CO_NBR']; ?>"><span class='fa fa-exchange toolbar' style="cursor:pointer"></span></a>
		</p>
	<?php } ?>
</div>

<form enctype="multipart/form-data" action="#" method="post" style="width:500px" onSubmit="return checkform();">
	<p>
		<h3>
			Nomor: <?php echo $row['PYMT_RCV_NBR'];if($row['PYMT_RCV_NBR']==""){echo "Baru";} ?>
		</h3>

		<input name="PYMT_RCV_NBR" value="<?php echo $row['PYMT_RCV_NBR'];if($row['PYMT_RCV_NBR']==""){echo "-1";} ?>" type="hidden" />

		<label>Tanggal</label><br />
		<input name="PYMT_RCV_DTE" id="PYMT_RCV_DTE" value="<?php if(!empty($row['PYMT_RCV_DTE'])) {echo $row['PYMT_RCV_DTE'];}else{ echo date("Y-m-d");} ?>" type="text" size="15" /><br />
		<script>
			new CalendarEightysix('PYMT_RCV_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'prefill': false, 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });
		</script>
		
		<label>Dibayarkan Oleh</label><br /><div class='labelbox'></div>
		<select name="SHP_CO_NBR" style='width:450px' class="chosen-select">
			<?php
				$query="SELECT CO_NBR,CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS CO_DESC
						FROM CMP.COMPANY COM INNER JOIN
						CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID ORDER BY 2";
				genCombo($query,"CO_NBR","CO_DESC",$row['SHP_CO_NBR'],"Kosong");
			?>
		</select><br /><div class="combobox"></div>
		
		<label>Nomor Referensi</label><br />
		<input name="REF_NBR" value="<?php echo $row['REF_NBR']; ?>" type="text"  style='width:450px' /><br />
		
		<label>Jumlah Pembaayaran</label><br />
		<input name="TND_AMT" id="TND_AMT" value="<?php echo $row['TND_AMT']; ?>" type="text" size="25" /><br />
		
		<label>Jenis</label><br /><div class='labelbox'></div>
		<select name="PYMT_TYP" class="chosen-select">
			<?php
				$query="SELECT PYMT_TYP,PYMT_DESC
						FROM RTL.PYMT_TYP ORDER BY 2";
				genCombo($query,"PYMT_TYP","PYMT_DESC",$row['PYMT_TYP']);
			?>
		</select><br /><div class="combobox"></div>
		
		<input class='process' type='submit' value='Bayar'/>
	
		</p>
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
		   	}
			for (var selector in config) {
				jQuery(selector).chosen(config[selector]);
			}
		</script>
	</form>
</body>
</html>