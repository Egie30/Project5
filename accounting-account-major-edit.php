<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/security/default.php";
	
$accountNumber = $_GET['CD_NBR'];
$security = getSecurity($_SESSION['userID'], "Accounting");	

//Process changes here
if ($_POST['CD_NBR'] != "") {
	$accountNumber = $_POST['CD_NBR'];

	//Process add new
	if ($accountNumber == -1) {
		$query = "SELECT COALESCE(MAX(CD_NBR),0) + 1 AS NEW_NBR FROM RTL.ACCTG_CD";
		$result = mysql_query($query);
		$row = mysql_fetch_array($result);

		$accountNumber = $row['NEW_NBR'];

		$query = "INSERT INTO RTL.ACCTG_CD (CD_NBR, UPD_NBR) VALUES (" . $accountNumber . ", " . $_SESSION['personNBR'] . ")";
		$result = mysql_query($query);
	}

	//Process add new
	if ($_POST['CD_ACC_NBR'] == "") {
		$query = "SELECT COALESCE(MAX(CD_ACC_NBR),0) + 1 AS NEW_NBR FROM RTL.ACCTG_CD WHERE CD_CAT_NBR=" . $_POST['CD_CAT_NBR'] ;
		$result = mysql_query($query);
		$row = mysql_fetch_array($result);

		$_POST['CD_ACC_NBR'] = $row['NEW_NBR'];
	}
			
	$query = "UPDATE RTL.ACCTG_CD SET
		CD_ACC_NBR='" . $_POST['CD_ACC_NBR'] . "',
		CD_DESC='" . $_POST['CD_DESC'] . "',
		CD_CAT_NBR=" . $_POST['CD_CAT_NBR'] . "
	WHERE CD_NBR=" . $accountNumber;

	$result = mysql_query($query);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" href="framework/combobox/chosen.css">

	<script type="text/javascript">parent.Pace.restart();</script>
	<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.default.js"></script>
<body>
<script>
	parent.document.getElementById('recordDeleteYes').onclick=
	function () {
		parent.document.getElementById('content').src='accounting-account-major.php?DEL_L=<?php echo $accountNumber?>';
		parent.document.getElementById('recordDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none';
	};
</script>

<?php
$query = "SELECT ACC.CD_NBR, ACC.CD_ACC_NBR, ACC.CD_DESC, CAT.CD_CAT_NBR, CAT.CD_CAT_DESC
	FROM RTL.ACCTG_CD ACC
		INNER JOIN RTL.ACCTG_CD_CAT CAT ON CAT.CD_CAT_NBR=ACC.CD_CAT_NBR
	WHERE ACC.CD_NBR=" . $accountNumber;

$result = mysql_query($query);
$row = mysql_fetch_array($result);
?>

<?php if($Security == 0 && $row['CD_NBR'] != "") { ?>
	<div class="toolbar-only">
		<p class="toolbar-left">
			<!-- <a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('recordDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><img style="cursor:pointer" class="toolbar-left" src="img/delete.png"></a> -->
		</p>
	</div>
<?php } ?>

<form enctype="multipart/form-data" action="#" method="post" style="width:400px">
	<p>
		<h2>
			Nomor Rekening: 
			<?php if ($row['CD_ACC_NBR'] == "") {
				echo "Baru";
			} else {
				echo $row['CD_CAT_NBR'] . "-" . $row['CD_ACC_NBR'];
			}?>
		</h2>
		<input name="CD_NBR" value="<?php echo $row['CD_NBR'];if($row['CD_NBR']==""){echo "-1";} ?>" type="hidden" />

		<label>Kode</label><br />
		<input name="CD_ACC_NBR" value="<?php echo $row['CD_ACC_NBR']; ?>" type="text" size="50" /><br />
			
		<label>Klasifikasi</label><br /><div class='labelbox'></div>
		<select name="CD_CAT_NBR" class="chosen-select" style="width: 127px;"><br />
		<?php
			$query="SELECT CD_CAT_NBR, CD_CAT_DESC
					FROM RTL.ACCTG_CD_CAT";
			genCombo($query, "CD_CAT_NBR", "CD_CAT_DESC", $row['CD_CAT_NBR']);
		?>
		</select><br /><div class="combobox"></div>

		<label>Deskripsi</label><br />
		<input name="CD_DESC" value="<?php echo $row['CD_DESC']; ?>" required type="text" size="50" /><br />
			
		<input class="process" type="submit" value="Simpan"/>
	</p>
</form>
</body>
</html>
