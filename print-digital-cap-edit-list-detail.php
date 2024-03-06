<?php
@header("Connection: close\r\n"); 
include "framework/database/connect.php";
include "framework/functions/default.php";
$CapNbr		= $_GET['CAP_NBR'];
$CapDetNbr	= $_GET['CAP_DET_NBR'];
$changed	= false;
$addNew		= false;

//Process changes here
if($_POST['CAP_DET_NBR']!=""){
	$CapDetNbr=$_POST['CAP_DET_NBR'];
	//Take care of nulls
	if($_POST['ORD_NBR']==""){$OrdNbr="NULL";}else{$OrdNbr=$_POST['ORD_NBR'];}
	if($_POST['INV_PRC']==""){$InvPrc="NULL";}else{$InvPrc=$_POST['INV_PRC'];}
	if($_POST['AMT']==""){$amount="NULL";}else{$amount=$_POST['AMT'];}
	if($_POST['CAP_PCT']==""){$CapPct="NULL";}else{$CapPct=$_POST['CAP_PCT'];}
	if($_POST['TOT_SUB']==""){$TotSub="NULL";}else{$TotSub=$_POST['TOT_SUB'];}
	
	//Process add new
	if($CapDetNbr==-1){
		$addNew=true;
		$query="SELECT COALESCE(MAX(CAP_DET_NBR),0)+1 AS NEW_NBR FROM PRN_DIG_CAP_DET";
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$CapDetNbr=$row['NEW_NBR'];
		$query="INSERT INTO PRN_DIG_CAP_DET (CAP_DET_NBR) VALUES (".$CapDetNbr.")";
		$result=mysql_query($query);
	}
	
	$query="UPDATE PRN_DIG_CAP_DET SET 
		CAP_NBR=".$CapNbr.",
		ORD_NBR=".$OrdNbr.",
		ORD_TTL='".$_POST['ORD_TTL']."',
		AMT = ".$amount.",
		CAP_PCT=".$CapPct.",
		TOT_SUB=".$TotSub.",".$create."
		UPD_TS=CURRENT_TIMESTAMP,
		UPD_NBR=".$_SESSION['personNBR']."
	WHERE CAP_DET_NBR=".$CapDetNbr;
	//echo $query;
	$result=mysql_query($query);
	$changed=true;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
		
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	<script src="framework/database/jquery.min.js"></script>
	<script type="text/javascript">
	function getInt(objectID){
		if(document.getElementById(objectID).value==""){
			return 0;
		}else{
			return parseInt(document.getElementById(objectID).value);
		}
	}
	function getFloat(objectID){
		if(document.getElementById(objectID).value==""){
			return 0;
		}else{
			return parseFloat(document.getElementById(objectID).value);
		}
	}
	</script>
</head>
<body>
<?php
	if($changed){
		echo "<script>";
		echo "parent.document.getElementById('content').contentDocument.getElementById('refresh-list').click();";
		echo "parent.document.getElementById('content').contentDocument.getElementById('refresh-tot').click();";
		echo "</script>";
	}
	if($addNew){$CapDetNbr=0;}
?>
<span class='fa fa-times toolbar' style='margin-left:10px' onclick="slideFormOut();"></span></a>
<?php
	$query="SELECT 
		CAP_DET_NBR,
		CAP_NBR,
		ORD_NBR,
		AMT,
		CAP_PCT,
		TOT_SUB
	FROM PRN_DIG_CAP_DET
	WHERE CAP_DET_NBR=".$CapDetNbr;
	//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
?>
<form enctype="multipart/form-data" action="#" method="post" style="width:450px;" onSubmit="return checkform();">
	<table>
		<tr>
			<td style="vertical-align:top">Cari No Nota</td>
			<td><input type="text" id="livesearch" autofocus /></td>
		</tr>
		<tr>
			<td colspan="2">
				<div style="margin-top:10px;width:410px;" class="edit-list-ls" id="liveRequestResults"></div>
				<div id="mainResult" ></div>
				<script>liveReqInit();</script>
			</td>
		</tr>
		<tr>
			<td>No. Nota</td>
			<input name="CAP_DET_NBR" id="CAP_DET_NBR" value="<?php echo $row['CAP_DET_NBR'];if($row['CAP_DET_NBR']==""){echo "-1";$addNew=true;} ?>" type="hidden" />
			<input name="ORD_TTL" id="ORD_TTL" value="<?php echo $row['ORD_TTL']; ?>" type="hidden" />
			<td><input id="ORD_NBR" name="ORD_NBR" value="<?php echo $row['ORD_NBR']; ?>" type="text" style="width:100px;" readonly /></td>
		</tr>
		<tr>
			<td>Harga</td>
			<td>
				<input id="AMT"  name="AMT" value="<?php echo $row['AMT']; ?>" type="text" style="width:100px;" onkeyup="calcPay();" onchange="calcPay();" />
			</td>
		</tr>
		<tr>
			<td>Komisi</td>
			<td>
				<input id="CAP_PCT" name="CAP_PCT" onkeyup="document.getElementById('TOT_SUB').value=parseInt(getInt('AMT')*getInt('CAP_PCT')/100);calcPay();" onchange="document.getElementById('TOT_SUB').value=parseInt(getInt('AMT')*getInt('CAP_PCT')/100);" value="<?php echo $row['CAP_PCT']; ?>" type="text" style="width:100px;"  /> %
			</td>
		</tr>
		<tr>
			<td>Sub total</td>
			<td><input id="TOT_SUB" name="TOT_SUB" value="<?php echo $row['TOT_SUB']; ?>" type="text" style="width:100px" readonly /></td>
		</tr>
	</table>
	<br />
	<input class="process" id="process" type="submit" value="<?php if($addNew){echo 'Tambah';}else{echo 'Simpan';} ?>"/>
</form>

<script>
	liveReqInit('livesearch','liveRequestResults','print-digital-cap-edit-list-detail-ls.php','','mainResult');
	<?php if($row['ORD_NBR']!="") { ?>
	getContent('liveRequestResults',"print-digital-cap-edit-list-detail-ls.php?ORD_NBR=<?php echo $row['ORD_NBR']; ?>");
	document.getElementById('liveRequestResults').style.display="";	
	document.getElementById('livesearch').value="<?php echo $row['ORD_NBR']; ?>";
	<?php } ?>
</script>
</body>
</html>