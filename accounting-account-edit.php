<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/security/default.php";
	
$accountNumber = $_GET['CD_SUB_NBR'];
$security = getSecurity($_SESSION['userID'], "Executive");	

//Process changes here
if ($_POST['CD_SUB_NBR'] != "") {
	
	$accountNumber = $_POST['CD_SUB_NBR'];

	$query = "SELECT CD_NBR, CD_SUB_NBR FROM RTL.ACCTG_CD_SUB WHERE CD_NBR=" . $_POST['CD_NBR'] . " AND CD_SUB_DESC = '" . $_POST['CD_SUB_DESC'] . "'";
	$result = mysql_query($query);
	$row = mysql_fetch_array($result);
	
	if (mysql_num_rows($result) > 0 && $row['CD_SUB_NBR'] != $accountNumber) { 
	} else {
		
		//Process add new
		if ($accountNumber == -1) {
			$query = "SELECT COALESCE(MAX(CD_SUB_NBR),0) + 1 AS NEW_NBR FROM RTL.ACCTG_CD_SUB";
			$result = mysql_query($query);
			$row = mysql_fetch_array($result);

			$accountNumber = $row['NEW_NBR'];

			$query = "INSERT INTO RTL.ACCTG_CD_SUB (CD_SUB_NBR, UPD_NBR) VALUES (" . $accountNumber . ", " . $_SESSION['personNBR'] . ")";
			
			//echo $query;
			
			$result = mysql_query($query);
			
		}
		
		//Process add new
		
		if ($_POST['CD_SUB_ACC_NBR'] == "") {
			$query = "SELECT COALESCE(MAX(CD_SUB_ACC_NBR),0) + 1 AS NEW_NBR FROM RTL.ACCTG_CD_SUB WHERE CD_NBR=" . $_POST['CD_NBR'] ;
			$result = mysql_query($query);
			$row = mysql_fetch_array($result);

			$_POST['CD_SUB_ACC_NBR'] = $row['NEW_NBR'];
		}
		
			
		$query = "UPDATE RTL.ACCTG_CD_SUB SET
			CD_SUB_ACC_NBR='" . FollowNull($_POST['CD_SUB_ACC_NBR'], 3) . "',
			CD_SUB_DESC='" . $_POST['CD_SUB_DESC'] . "',
		   	CD_NBR=" . $_POST['CD_NBR'] . "
		   	WHERE CD_SUB_NBR = " . $accountNumber;
		   	
		$result = mysql_query($query);
		
		//echo $query;
	}
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
		parent.document.getElementById('content').src='accounting-account.php?DEL_L=<?php echo $accountNumber?>';
		parent.document.getElementById('recordDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none';
	};
</script>

<?php
$query = "SELECT SUB.CD_SUB_NBR, SUB.CD_SUB_ACC_NBR, SUB.CD_SUB_DESC, ACC.CD_NBR, ACC.CD_ACC_NBR, ACC.CD_DESC, CAT.CD_CAT_DESC,
		CONCAT(CAT.CD_CAT_NBR, '-', ACC.CD_ACC_NBR, SUB.CD_SUB_ACC_NBR) AS ACC_NBR,
		CONCAT(CAT.CD_CAT_DESC, ' - ', ACC.CD_DESC, ' :: ', SUB.CD_SUB_DESC) AS ACC_DESC
	FROM RTL.ACCTG_CD_SUB SUB
		INNER JOIN RTL.ACCTG_CD ACC ON ACC.CD_NBR=SUB.CD_NBR
		INNER JOIN RTL.ACCTG_CD_CAT CAT ON CAT.CD_CAT_NBR=ACC.CD_CAT_NBR
	WHERE SUB.CD_SUB_NBR = " . $accountNumber;

$result = mysql_query($query);
$row = mysql_fetch_array($result);
?>

<?php if($Security == 0 && $row['CD_SUB_NBR'] != "") { ?>
	<div class="toolbar-only">
		<p class="toolbar-left">
			<a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('recordDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><img style="cursor:pointer" class="toolbar-left" src="img/delete.png"></a>
		</p>
	</div>
<?php } ?>

<form enctype="multipart/form-data" action="#" method="post" style="width:400px">
	<p>
		<h2>
			Nomor Rekening: 
			<?php if ($row['ACC_NBR'] == "") {
				echo "Baru";
			} else {
				echo $row['ACC_NBR'];
			}?>
		</h2>

		<input name="CD_SUB_NBR" value="<?php echo $row['CD_SUB_NBR'];if($row['CD_SUB_NBR']==""){echo "-1";} ?>" type="hidden" />

		<label>Kode Akun</label><br />
		<input name="CD_SUB_ACC_NBR" value="<?php echo $row['CD_SUB_ACC_NBR'];?>" type="text" size="50" /><br />
			
		<label>Klasifikasi</label><br /><div class='labelbox'></div>
		<select name="CD_NBR" id="CD_NBR" class="chosen-select"><br />
		<?php
	    $query = "SELECT CD_CAT_NBR, CD_CAT_DESC FROM RTL.ACCTG_CD_CAT ORDER BY 1 ASC";
	    $resultCategory = mysql_query($query);

	    while($rowCategory = mysql_fetch_array($resultCategory)) {
	    	echo "<optgroup label='".$rowCategory[ 'CD_CAT_DESC']. "'>";
			
			$query = "SELECT ACC.CD_NBR, ACC.CD_ACC_NBR, ACC.CD_DESC, CAT.CD_CAT_NBR, CAT.CD_CAT_DESC,
					CONCAT(CAT.CD_CAT_NBR, '-', ACC.CD_ACC_NBR) AS ACC_NBR,
					CONCAT(CONCAT(CAT.CD_CAT_NBR, '-', ACC.CD_ACC_NBR), ' :: ', ACC.CD_DESC) AS ACC_DESC
				FROM RTL.ACCTG_CD ACC
					INNER JOIN RTL.ACCTG_CD_CAT CAT ON CAT.CD_CAT_NBR=ACC.CD_CAT_NBR
				 WHERE ACC.CD_CAT_NBR=" .$rowCategory['CD_CAT_NBR']. "
				ORDER BY CAT.CD_CAT_NBR, ACC.CD_ACC_NBR ASC";
			genCombo($query, "CD_NBR", "ACC_DESC", $row['CD_NBR']);

	    	echo "</optgroup>";
	    }
		?>
		</select><br /><div class="combobox"></div>

		<label>Deskripsi</label><br />
		<input name="CD_SUB_DESC" value="<?php echo $row['CD_SUB_DESC']; ?>" required type="text" size="50" /><br />
			
		<input class="process" type="submit" value="Simpan"/>
	</p>
</form>
</body>
</html>
