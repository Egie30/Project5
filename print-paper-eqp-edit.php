<?php
include "framework/database/connect-cloud.php";
include "framework/functions/default.php";
include "framework/security/default.php";

$equipmentType	= $_GET['PRN_PPR_EQP'];
$Security		= getSecurity($_SESSION['userID'],"Inventory");	

//Process changes here
if($_POST['PRN_PPR_EQP']!=""){
	
	$equipmentType=$_POST['PRN_PPR_EQP'];
	
	$query="SELECT PRN_PPR_EQP FROM CMP.PRN_PPR_EQP WHERE PRN_PPR_EQP='".$equipmentType."'";
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);

	//Process add new
	if($row['PRN_PPR_EQP']==""){
		$query="INSERT INTO CMP.PRN_PPR_EQP (PRN_PPR_EQP) VALUES ('".$equipmentType."')";
		$result=mysql_query($query);
	}
	
	$query="UPDATE CMP.PRN_PPR_EQP SET 
		PRN_PPR_EQP_DESC='".$_POST['PRN_PPR_EQP_DESC']."',
		PRN_PPR_EQP_PRC='".$_POST['PRN_PPR_EQP_PRC']."',
		PRN_PPR_EQP_OVER='".$_POST['PRN_PPR_EQP_OVER']."',
		PRN_PPR_EQP_PLAT='".$_POST['PRN_PPR_EQP_PLAT']."',
		UPD_TS=CURRENT_TIMESTAMP,
		UPD_NBR=".$_SESSION['personNBR']."
		WHERE PRN_PPR_EQP='".$equipmentType."'";
	$result=mysql_query($query);
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<script>parent.Pace.restart();</script>
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4-core.js"></script>
	<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4.4-more.js"></script>
	<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.js"></script>
	<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<script>
		parent.document.getElementById('catDeleteYes').onclick=
		function () {
			parent.document.getElementById('content').src='print-paper-eqp.php?DEL_L=<?php echo $equipmentType ?>';
			parent.document.getElementById('catDelete').style.display='none';
			parent.document.getElementById('fade').style.display='none';
		};
	</script>
<body>
<?php
$query="SELECT 
	PRN_PPR_EQP,
	PRN_PPR_EQP_DESC,
	PRN_PPR_EQP_COLR,
	PRN_PPR_EQP_PRC,
	PRN_PPR_EQP_OVER,
	PRN_PPR_EQP_PLAT
FROM CMP.PRN_PPR_EQP
WHERE PRN_PPR_EQP='".$equipmentType."'";
$result=mysql_query($query);
$row=mysql_fetch_array($result);
?>

<?php if(($Security==0)&&($equipmentType!="")) { ?>
	<div class="toolbar-only">
		<p class="toolbar-left">
			<a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('catDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><span class="fa fa-trash toolbar" style="cursor:pointer"></span></a>
		</p>
	</div>
<?php } ?>

<form enctype="multipart/form-data" action="#" method="post" style="width:400px" onSubmit="return checkform();">
	<p>
		<h2>
			<?php echo $row['PRN_PPR_EQP'];if($row['PRN_PPR_EQP']==""){echo "Baru";} ?>
		</h2>
		
		<label>Kode Harga</label><br />
		<input name="PRN_PPR_EQP" value="<?php echo $row['PRN_PPR_EQP']; ?>" type="text" size="15" /><br />

		<label>Deskripsi</label><br />
		<input name="PRN_PPR_EQP_DESC" value="<?php echo $row['PRN_PPR_EQP_DESC']; ?>" type="text" size="50" /><br />
		
		<label>Harga Cetak</label><br />
		<input name="PRN_PPR_EQP_PRC" value="<?php echo $row['PRN_PPR_EQP_PRC']; ?>" type="text" size="15" /><br />
		
		<label>Over Cetak/lbr</label><br />
		<input name="PRN_PPR_EQP_OVER" value="<?php echo $row['PRN_PPR_EQP_OVER']; ?>" type="text" size="15" /><br />
		
		<label>Harga Plat</label><br />
		<input name="PRN_PPR_EQP_PLAT" value="<?php echo $row['PRN_PPR_EQP_PLAT']; ?>" type="text" size="15" /><br />
		
		<input class="process" type="submit" value="Simpan"/>
	</p>
</form>
</body>
</html>
