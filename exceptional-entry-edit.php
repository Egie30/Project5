<?php
include "framework/database/connect-cloud.php";
require_once "framework/functions/default.php";
require_once "framework/security/default.php";
$Security=getSecurity($_SESSION['userID'],"Payroll");
ini_set('max_execution_time',-1);

$exceptionNumber 	= $_GET['EXCPTN_ETRY_NBR'];

//Process changes here
if ($_POST['EXCPTN_ETRY_NBR'] != "") {
	$exceptionNumber = $_POST['EXCPTN_ETRY_NBR'];

	//Process add new
	if ($exceptionNumber == -1) {
		$query = "SELECT COALESCE(MAX(EXCPTN_ETRY_NBR),0)+1 AS NEW_NBR FROM $PAY.EXCPTN_ETRY";
		$result=mysql_query($query,$cloud);
		$row = mysql_fetch_array($result);
		$exceptionNumber = $row['NEW_NBR'];

		$query = "INSERT INTO $PAY.EXCPTN_ETRY(EXCPTN_ETRY_NBR, CRT_NBR, CRT_TS) VALUES (" . $exceptionNumber . ", " . $_SESSION['personNBR'] . ", CURRENT_TIMESTAMP)";
		$result=mysql_query($query,$cloud);
		$query=str_replace($PAY,"PAY",$query);
		$result=mysql_query($query,$local);
		
		//echo "<pre>".$query."<br /><br />";	
	}
		
	$query = "UPDATE $PAY.EXCPTN_ETRY SET
		PRSN_NBR=" . $_POST['PRSN_NBR'] . ",
		EXCPTN_ETRY_TS='" . $_POST['ETRY_DTE'] . " " . $_POST['ETRY_TME'] . "',
	   	EXCPTN_ETRY_ACT='" . $_POST['EXCPTN_ETRY_ACT'] . "',
	   	EXCPTN_ETRY_RSN='" . $_POST['EXCPTN_ETRY_RSN'] . "',
		UPD_TS=CURRENT_TIMESTAMP,
		UPD_NBR=".$_SESSION['personNBR']."
		WHERE EXCPTN_ETRY_NBR=" . $exceptionNumber;
	
	$result=mysql_query($query,$cloud);
	$query=str_replace($PAY,"PAY",$query);
	$result=mysql_query($query,$local);
	
	$query = "INSERT INTO $PAY.ATND_CLOK (PRSN_NBR,CRT_TS,UPD_TS) VALUES (
					".$_POST['PRSN_NBR'].",
					'".$_POST['ETRY_DTE'] . " " . $_POST['ETRY_TME'] . "',
					CURRENT_TIMESTAMP)";
	$result=mysql_query($query,$cloud);
	$query=str_replace($PAY,"PAY",$query);
	$result=mysql_query($query,$local);
	//echo $query;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
    <link rel="stylesheet" type="text/css" href="framework/combobox/chosen.css">
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />
	
	<script type="text/javascript">parent.Pace.restart();</script>
    <script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
    <script type="text/javascript" src="framework/functions/default.js"></script>
    <script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>
    <script type="text/javascript" src="framework/combobox/chosen.default.js"></script>
	<script type="text/javascript">jQuery.noConflict()</script>
	<script type="text/javascript" src="framework/validation/mootools-1.2.3.js"></script>
	<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
	<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
	<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>
	<script type="text/javascript">jQuery.noConflict()</script>
<body>
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
<script type="text/javascript">
parent.document.getElementById('addressDeleteYes').onclick = 
function () {
	parent.document.getElementById('content').src='exceptional-entry.php?DEL_L=<?php echo $exceptionNumber;?>';
	parent.document.getElementById('addressDelete').style.display='none';
	parent.document.getElementById('fade').style.display='none';
};
</script>
<?php
$query = "SELECT EXCPTN_ETRY_NBR,
		PRSN_NBR,
		EXCPTN_ETRY_TS,
		EXCPTN_ETRY_ACT,
		EXCPTN_ETRY_RSN,
		CLOK_NBR
	FROM PAY.EXCPTN_ETRY
	WHERE EXCPTN_ETRY_NBR=" . $exceptionNumber;
$result = mysql_query($query,$local);
$row = mysql_fetch_array($result);
?>

<?php if ($security == 0 && $row['EXCPTN_ETRY_NBR'] != 0) { ?>
	<div class="toolbar-only">
		<p class="toolbar-left"><a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('addressDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><img style="cursor:pointer" class="toolbar-left" src="img/delete.png"></a></p>
	</div>
<?php } ?>

<form enctype="multipart/form-data" action="#" method="post" style="width:400px">
	<p>
		<h2>
			Exception Entry No: <?php echo $row['EXCPTN_ETRY_NBR'];if($row['EXCPTN_ETRY_NBR']==''){echo "New";} ?>
		</h2>		
		<input name="EXCPTN_ETRY_NBR" value="<?php echo $row['EXCPTN_ETRY_NBR'];if($row['EXCPTN_ETRY_NBR']==""){echo "-1";} ?>" type="hidden" />
		<input name="CLOK_NBR" id="CLOK_NBR" value="<?php echo $row['CLOK_NBR']; ?>" type="hidden" />
		
		<label>Staff</label><br /><div class='labelbox'></div>
		<select name="PRSN_NBR" id="PRSN_NBR" class="chosen-select" style="width:400px">
		<?php
			if($row["PRSN_NBR"]==""){$personNumber="";}else{$personNumber=$row["PRSN_NBR"];}
			/*$querySls	= "SELECT CO_NBR_CMPST FROM NST.PARAM_PAYROLL WHERE CO_NBR = ".$CoNbrDef." ";
			$resultSls	= mysql_query($querySls);
			$rowSls		= mysql_fetch_array($resultSls);
			$CoNbrSls	= $rowSls['CO_NBR_CMPST'];
			*/
			$query="SELECT PRSN_NBR,CONCAT(PRSN_NBR,' ',NAME) AS NAME FROM CMP.PEOPLE WHERE CO_NBR IN (SELECT CO_NBR FROM NST.PARAM_PAYROLL) AND TERM_DTE IS NULL AND DEL_NBR=0";
			genCombo($query,"PRSN_NBR","NAME",$personNumber,"Pilih Staff",$local);
		?>
		</select><br /><div class="combobox"></div>
		
		<label>Jenis Absensi</label><br /><div class='labelbox'></div>
	    <select name='EXCPTN_ETRY_ACT' id='EXCPTN_ETRY_ACT' class='chosen-select' style="min-width:110px" >
	    	<option <?php if ($row['EXCPTN_ETRY_ACT'] == "") {echo "selected";}?>>Pilih Jenis</option>
			<option value="IN" <?php if($row['EXCPTN_ETRY_ACT']=="IN"){echo "selected";} ?>>Masuk</option>
			<option value="OUT" <?php if($row['EXCPTN_ETRY_ACT']=="OUT"){echo "selected";} ?>>Keluar</option>
	    </select><br/>
		<div style="display:none;padding-top: 10px;" id="EXCPTN_ETRY_ACT_DATA"></div>
	    <div class="combobox"></div>
		
		<label>Tanggal Absensi</label><br/>
		<?php if($row['EXCPTN_ETRY_TS']==""){$entryDate="";}else{$entryDate=parseDate($row['EXCPTN_ETRY_TS']);}?>
		<input name="ETRY_DTE" id="ETRY_DTE" value="<?php echo $entryDate; ?>" type="text" style="width:110px;"/>
		<script>
			new CalendarEightysix('ETRY_DTE', { 'offsetY': -5, 'offsetX': 2, 'format': '%Y-%m-%d', 'slideTransition': Fx.Transitions.Back.easeOut, 'draggable': true });			
		</script><br />
			
		<label>Waktu Absensi</label><br/>
		<?php if($row['EXCPTN_ETRY_TS']==""){$entryTime=date("G:i:s");}else{$entryTime=parseTime($row['EXCPTN_ETRY_TS']);}?>
		<input name="ETRY_TME" id="ETRY_TME" value="<?php echo $entryTime; ?>" type="text" style="width:110px;"/>
		<div class='listable-btn'><span class='fa fa-clock-o listable-btn' style='font-size:14px' onclick="document.getElementById('ETRY_TME').value=getCurTime();"></span></div><br />
		
		<label>Alasan</label><br />
		<textarea name="EXCPTN_ETRY_RSN" style="width:400px;height:40px;"><?php echo $row['EXCPTN_ETRY_RSN']; ?></textarea><br />
        <?php if ($exceptionNumber<0 || $Security == 0){?>
		<input class="process" type="submit" value="Save"/>
		<?php } ?>
	</p>
	
</form>
</body>
</html>
