<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/security/default.php";

$glNumber		= $_GET['GL_NBR'];
$cdSubNbr		= $_GET['CD_SUB_NBR'];
$note			= $_GET['NOTE'];
$value			= $_GET['VALUE'];
$plusMode		= $_GET['PLUS'];

$security 	= getSecurity($_SESSION['userID'], "Accounting");


if ($_POST['PRN_NBR'] != "") {

	$PrintNbr 	= $_POST['PRN_NBR'];

	if ($_POST['NAME'] == "") {	$name = "NULL";   } else {   $name = $_POST['NAME'];   }
	if ($_POST['ADDRESS'] == "") {	$address = "NULL";   } else {   $address = $_POST['ADDRESS'];   }
	
	//Process add new
	if ($PrintNbr == -1) {
		$query = "SELECT COALESCE(MAX(PRN_NBR),0)+1 AS NEW_NBR FROM RTL.ACCTG_PRN";
		$result = mysql_query($query);
		$row = mysql_fetch_array($result);
		$PrintNbr = $row['NEW_NBR'];
		
		$query = "INSERT INTO RTL.ACCTG_PRN(PRN_NBR, CRT_TS, CRT_NBR)
			VALUES (" . $PrintNbr . ", CURRENT_TIMESTAMP, " . $_SESSION['personNBR'] . ")";
		$result = mysql_query($query);
		
		//echo $query."<br />";
	}
	
	
	$query = "UPDATE RTL.ACCTG_PRN SET
		GL_NBR 		= ".$glNumber.",
		CD_SUB_NBR	= ".$cdSubNbr.",
		VAL			= ".$value.",
		NAME		='" . $name . "',
	   	ADDRESS		='" . $address . "',
		UPD_TS		= CURRENT_TIMESTAMP,
		UPD_NBR		= ".$_SESSION['personNBR']."
		WHERE PRN_NBR=" . $PrintNbr;
	
	$result = mysql_query($query);

	//echo $query."<br />";
}



if ($_POST['CETAK'] != "") {
	?>
		<script>
			parent.document.getElementById('content').src='general-journal-edit-pdf.php?GL_NBR=<?php echo $glNumber; ?>&PRN_NBR=<?php echo $_POST['NUMBER']; ?>';
			parent.document.getElementById('retailPopupEditContent').src='about:blank';
			parent.document.getElementById('retailPopupEdit').style.display='none';
			parent.document.getElementById('fade').style.display='none';
		</script>
	<?php
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" href="framework/combobox/chosen.css">
	<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />
	
	<script type="text/javascript">parent.Pace.restart();</script>
	<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
	<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
	<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>
	<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>
	<script type="text/javascript" src="framework/combobox/ajax-chosen/ajax-chosen.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.default.js"></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<script type="text/javascript">jQuery.noConflict();</script>
<body>
<script>
	parent.document.getElemenHEDyId('depreciationDeleteYes').onclick = function () {
	parent.document.getElemenHEDyId('content').src='depreciation.php?DEL_L=<?php echo $PrintNbr;?>';
	parent.document.getElemenHEDyId('depreciationDelete').style.display='none';
	parent.document.getElemenHEDyId('fade').style.display='none';
};
</script>
<?php
$query = "SELECT PRN.PRN_NBR, 
				PRN.NAME,
				PRN.ADDRESS
			FROM RTL.ACCTG_PRN PRN
				WHERE PRN.GL_NBR = ".$glNumber." ";
			
$result = mysql_query($query);
$row = mysql_fetch_array($result);


?>

<?php if ($security == 0 && $row['PRN_NBR'] != 0) { ?>
	<div class="toolbar-only">
		<p class="toolbar-left"><a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElemenHEDyId('depreciationDelete').style.display='block';parent.document.getElemenHEDyId('fade').style.display='block'"><img style="cursor:pointer" class="toolbar-left" src="img/delete.png"></a></p>
	</div>
<?php } ?>

<form enctype="multipart/form-data" action="#" method="post" style="width:400px">
	<p>
		<h2>
			<?php echo " Nomor Jurnal ".$_GET['GL_NBR']; ?>
		</h2>
		<input name="PRN_NBR" value="<?php echo $row['PRN_NBR'];if($row['PRN_NBR']==""){echo "-1";} ?>" type="hidden" />
		
		<label>Nama</label><br />
		<input name="NAME" id="NAME" value="<?php echo $row['NAME']; ?>" type="text" size="60" /><br />

		<label>Alamat</label><br />
		<input name="ADDRESS" id="ADDRESS" value="<?php echo $row['ADDRESS']; ?>" type="text" size="60" /><br />
		
		<label>Jumlah Pembayaran</label><br />
		<input name="VALUE" id="VALUE" value="<?php echo $value; ?>" type="text" size="60" readonly/><br />
		
		<label>Guna Membayar</label><br />
		<input name="NOTE" id="NOTE" value="<?php echo $note; ?>" type="text" size="60" readonly/><br />
		
		<input class="process" type="submit" value="Simpan"/>
		
		<?php 
			if($row['PRN_NBR'] != "") {
				echo '<input name="NUMBER" type="hidden" value="'.$row['PRN_NBR'].'"/>';
				echo '<input class="process" type="submit" name="CETAK" value="Cetak"/>';
			}
		?>
	</p>
</form>

						
</body>
</html>
