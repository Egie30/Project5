<?php
	include "framework/database/connect.php";
	include "framework/security/default.php";
	$Security=getSecurity($_SESSION['userID'],"Payroll");
	$Finance=getSecurity($_SESSION['userID'],"Finance");
	$upperSecurity 	= getSecurity($_SESSION['userID'],"Executive");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<script type="text/javascript" src="framework/functions/default.js"></script>

</head>

<body class="sub">
<div class="leftmenusel" onclick="changeSiblingUrl('content','payroll-orgchart.php');selLeftMenu(this);">Org Chart</div>
<?php if($Security<8){?>
<?php if($Security < 2) { ?>
<div class="leftmenu" onclick="changeSiblingUrl('content','payroll-group.php?CO_NBR=ALL');selLeftMenu(this);">All</div>
<?php } ?>
<?php 
	if($Security <= 2) { 
		if ($Security<=2 && ($upperSecurity<=5 || $Finance<=1)  ) {
?>
<div class="leftmenu" style="display:none;" onclick="changeSiblingUrl('content','payroll-attendance.php?CO_NBR=ALL');selLeftMenu(this);">Absensi</div>
<?php } } ?>

<div class="leftmenu" onclick="changeSiblingUrl('content','payroll-wage.php');selLeftMenu(this);">Champion Harian</div>
<!--<div class="leftmenu" onclick="changeSiblingUrl('content','payroll-salary.php');selLeftMenu(this);">Champion Bulanan</div>-->
<?php 
$query	= "SELECT PRM.CO_NBR, COM.NAME AS CO_NAME
				FROM NST.PARAM_PAYROLL PRM
				JOIN CMP.COMPANY COM 
					ON PRM.CO_NBR = COM.CO_NBR
				WHERE PRM.NST_F = 1";
$result	= mysql_query($query);
while ($row = mysql_fetch_array($result)) { 
if ($Security<=2 && ($upperSecurity<=5 || $Finance<=1)  ) {
?>

<div class="leftmenu" onclick="changeSiblingUrl('content','payroll.php?CO_NBR=<?php echo $row['CO_NBR']; ?>');selLeftMenu(this);"><?php echo $row['CO_NAME']; ?></div>

<?php 
}else{
	if ($CoNbrDef==$row['CO_NBR']){
?>
	<div class="leftmenu" onclick="changeSiblingUrl('content','payroll.php?CO_NBR=<?php echo $row['CO_NBR']; ?>');selLeftMenu(this);"><?php echo $row['CO_NAME']; ?></div>
<?php
	}
}//securitye
}
?>
<?php if ($Security<=2 && $upperSecurity<=5){?>
<div class="leftmenu" onclick="changeSiblingUrl('content','payroll-hold.php');selLeftMenu(this);">Held Payroll</div>
<?php } ?>

<?php if ($Security<=2 && ($upperSecurity<=5 || $Finance<=1)){?>
<div class="leftmenu" onclick="changeSiblingUrl('content','employee-credit.php');selLeftMenu(this);">Kas Bon</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','payroll-merge.php');selLeftMenu(this);">Merge Absensi</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','contribution.php');selLeftMenu(this);">Reksa Dana</div>
<?php } ?>
<div class="leftmenu" onclick="changeSiblingUrl('content','travel-list.php');selLeftMenu(this);">Travel</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','exceptional-entry.php');selLeftMenu(this);">Exception</div>
<?php }?><!--Security <8 -->
</html>