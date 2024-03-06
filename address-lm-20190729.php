<?php
	include "framework/database/connect.php";
	include "framework/security/default.php";

	$Security 		= getSecurity($_SESSION['userID'],"AddressBook");
	$upperSecurity 	= getSecurity($_SESSION['userID'],"Executive");
	$SalesSec 		= getSecurity($_SESSION['userID'],"Sales");
	$PrnDigSec 		= getSecurity($_SESSION['userID'],"DigitalPrint");
	$CashSec 		= getSecurity($_SESSION['userID'],"Finance");
	$Acc 			= getSecurity($_SESSION['userID'],"Accounting");
	
	$RowSel=$_GET['ROW'];
	$query = "SELECT EMPL_CNTRCT,POS_TYP, HIRE_DTE FROM CMP.PEOPLE WHERE PRSN_NBR=".$_SESSION['personNBR'];
	$result= mysql_query($query);
	$row   = mysql_fetch_array($result);
	

	
	if (($row['EMPL_CNTRCT']!=5)) {
		$display= "display:none;";
		
		if ((in_array($row['POS_TYP'], array("HRO","SHR","SYS")))){
			$display= "";	
		}
	}
	
	if ($row['HIRE_DTE']!=''){
		$display_kas_bon = "display:none";

		$date1 = date_create($row['HIRE_DTE']);
		$date2 = date_create(date("Y-m-d"));

		$diff=date_diff($date1,$date2);
		if ($diff->format("%y") >= 1 ){
			$display_kas_bon =""; 
		}
	}

	$queryPeople 	= "SELECT COUNT(*) AS PPL_NBR FROM CMP.PEOPLE WHERE APV_F=0 AND DEL_NBR=0";
	$resultPeople 	= mysql_query($queryPeople);
	$rowPeople 	= mysql_fetch_array($resultPeople);

	$queryCompany 	= "SELECT COUNT(*) AS COM_NBR FROM CMP.COMPANY WHERE APV_F=0 AND DEL_NBR=0";
	$resultCompany 	= mysql_query($queryCompany);
	$rowCompany	= mysql_fetch_array($resultCompany);


	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<script type="text/javascript" src="framework/functions/default.js"></script>

</head>

<body class="sub">

<?php if (!in_array($_SESSION['personNBR'], array("3681","3817"))){ ?>
<div class="leftmenu<?php if(($RowSel==1)||($RowSel=="")){echo 'sel';} ?>" onclick="changeSiblingUrl('content','address-person.php');selLeftMenu(this);">Contacts</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','address-company.php');selLeftMenu(this);">Accounts</div>

<?php if(($PrnDigSec<2 && $upperSecurity<7 && $CashSec<3 && $Acc<8) && ($CashSec<>1 || $UpperSec<1)){ 
?>
	<div class="leftmenu" onclick="changeSiblingUrl('content','address-person.php?TYP=APV');selLeftMenu(this);">Approval Contacts&nbsp;&nbsp;<?php if($rowPeople['PPL_NBR']>0){ ?><span class='badge'><?php echo $rowPeople['PPL_NBR']; ?></span> <?php } ?></div>

	<div class="leftmenu" onclick="changeSiblingUrl('content','address-company.php?TYP=APV');selLeftMenu(this);">Approval Accounts&nbsp;&nbsp;<?php if($rowCompany['COM_NBR']>0){ ?><span class='badge'><?php echo $rowCompany['COM_NBR']; ?></span><?php } ?></div>
<?php
} 
?>

<div class="leftmenu" onclick="changeSiblingUrl('content','lead-management-tripane.php');selLeftMenu(this);">Lead Management</div>
<?php }else{ ?>
<div class="leftmenusel" onclick="changeSiblingUrl('content','lead-management-tripane.php');selLeftMenu(this);">Lead Management</div>
<?php } ?>

<div class="leftmenu" onclick="changeSiblingUrl('content','peer-form.php');selLeftMenu(this);">Peer Form</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','leave-of-absence.php');selLeftMenu(this);">Leave Of Absence</div>
<div style="<?php echo $display;?>" class="leftmenu" onclick="changeSiblingUrl('content','time-off.php');selLeftMenu(this);">Time Off</div>
<div style="<?php echo $display_kas_bon;?>" class="leftmenu" onclick="changeSiblingUrl('content','kas-bon.php?FLTR_DATE=<?php echo date('n Y'); ?>');selLeftMenu(this);">Kas Bon</div>
<?php if($_SESSION['personNBR'] != "") { ?>
<div class="leftmenu" onclick="changeSiblingUrl('content','payroll-info.php?PRSN_NBR=<?php echo $_SESSION['personNBR'];?>');selLeftMenu(this);">My Info</div>
<?php } ?>
<div class="leftmenu" onclick="changeSiblingUrl('content','employment-contract.php');selLeftMenu(this);">Employment Contract</div>
</body>
</html>
