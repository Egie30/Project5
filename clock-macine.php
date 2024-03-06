<?php 
include "framework/database/connect.php";
include "framework/functions/default.php";

if ($_SESSION['personNBR']==''){
	echo '<script>parent.parent.location="login.php";</script>';
	exit;
}

if ($_SESSION['personNBR']!=''){
	$query  = "SELECT PRSN_NBR,NAME FROM $CMP.PEOPLE WHERE PRSN_NBR=".$_SESSION['personNBR'];
	$result = mysql_query($local, $query);
	$rows   = mysql_fetch_array($result);

	$PrsnNbr= $rows['PRSN_NBR'];
	$Name   = $rows['NAME'];
}


$query = "SELECT PRSN_NBR,CRT_TS,COUNT(CRT_TS) AS CNT_ATND FROM $PAY.ATND_CLOK WHERE PRSN_NBR=".$_SESSION['personNBR']." AND DATE(CRT_TS)=CURDATE() ORDER BY CRT_TS DESC LIMIT 1";
$result= mysql_query($local, $query);
$row   = mysql_fetch_array($result);

if ($row['CNT_ATND'] % 2 ==1 || mysql_num_rows($result)<=0){
	$disIn  = "disabled";
	$colorI = "color:#6A6969;";

 }else{
	$disOut = "disabled";
	$colorO = "color:#6A6969;";
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
	
	<script src="framework/database/jquery.min.js"></script>
	<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
	<script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
	<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
</head>
<body>
<div id="mainResult">
<div style="padding-left: 10px;margin-top: 10px;">
	<img src='address-person/showimg.php?PRSN_NBR=".$PrsnNbr."' style='border-radius:50% 50% 50% 50%;width:50px;height:50px;vertical-align:middle;padding-left: 5px;'>&nbsp;&nbsp;<b><?php echo $Name; ?></b> (Employee ID #<?php echo $PrsnNbr; ?>)	
	</div>
<div>
	<form>
		<input onClick='syncGetContent(<?php echo chr(34)."clock".chr(34).",".chr(34)."clock-macine-write.php?PRSN_NBR=".$PrsnNbr."&CLOK_TYP=I".chr(34); ?>);' class='process submit_button' type='button' value='Clock In' style="<?php echo $colorI;?>" <?php echo $disIn;?>/>	
		
		<input onClick='syncGetContent(<?php echo chr(34)."clock".chr(34).",".chr(34)."clock-macine-write.php?PRSN_NBR=".$PrsnNbr."&CLOK_TYP=I".chr(34); ?>);' class='process submit_button' type='button' value='Clock Out' style="<?php echo $colorO;?>"  <?php echo $disOut;?>/>	
	</form>

	<div id='clock'></div>
</div>	
</div>

</body>
</html>