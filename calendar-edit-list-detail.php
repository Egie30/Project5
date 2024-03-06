<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	$OrdNbr=$_GET['ORD_NBR'];
	$OrdDetNbr=$_GET['ORD_DET_NBR'];
	$changed=false;
	$addNew=false;
	//Process changes here
	if($_POST['ORD_DET_NBR']!="")
	{
		$OrdDetNbr=$_POST['ORD_DET_NBR'];
		//Take care of nulls
		if($_POST['CAL_NBR']==""){$CalNbr="NULL";}else{$CalNbr=$_POST['CAL_NBR'];}
		if($_POST['ORD_Q']==""){$OrdQ="NULL";}else{$OrdQ=$_POST['ORD_Q'];}
	//	if($_POST['CAL_PRC']==""){$CalPrc="NULL";}else{$CalPrc=$_POST['CAL_PRC'];}
		if($_POST['PRN_F']==""){$PrnF="0";}else{$PrnF=$_POST['PRN_F'];}
		if($_POST['FEE_CLM']==""){$FeeClm="NULL";}else{$FeeClm=$_POST['FEE_CLM'];}
		if($_POST['FEE_CLR']==""){$FeeClr="NULL";}else{$FeeClr=$_POST['FEE_CLR'];}		
		if($_POST['FEE_MISC']==""){$FeeMisc="NULL";}else{$FeeMisc=$_POST['FEE_MISC'];}
		if($_POST['FAIL_CNT']==""){$FailCnt="NULL";}else{$FailCnt=$_POST['FAIL_CNT'];}
		if($_POST['DISC_PCT']==""){$DiscPct="NULL";}else{$DiscPct=$_POST['DISC_PCT'];}
		if($_POST['DISC_AMT']==""){$DiscAmt="NULL";}else{$DiscAmt=$_POST['DISC_AMT'];}
		if($_POST['TOT_SUB']==""){$TotSub="NULL";}else{$TotSub=$_POST['TOT_SUB'];}
		
		//Process add new
		if($OrdDetNbr==-1)
		{
			$addNew=true;
			$query="SELECT MAX(ORD_DET_NBR)+1 AS NEW_NBR FROM CMP.CAL_ORD_DET";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			$OrdDetNbr=$row['NEW_NBR'];
			$query="INSERT INTO CMP.CAL_ORD_DET (ORD_DET_NBR) VALUES (".$OrdDetNbr.")";
			//echo $query;
			$result=mysql_query($query);
			//$create="CRT_TS=CURRENT_TIMESTAMP,CRT_NBR=".$_SESSION['personNBR'].",";

		}
		
		$query="UPDATE CMP.CAL_ORD_DET
	   			SET ORD_NBR=".$OrdNbr.",
	   				CAL_NBR=".$CalNbr.",
					ORD_Q=".$OrdQ.",
					PRN_F=".$PrnF.",
					FEE_CLM=".$FeeClm.",
					FEE_CLR=".$FeeClr.",
					FEE_MISC=".$FeeMisc.",
					FAIL_CNT=".$FailCnt.",
					DISC_PCT=".$DiscPct.",
					DISC_AMT=".$DiscAmt.",
					TOT_SUB=".$TotSub.",
					UPD_DTE=CURRENT_DATE,
					UPD_NBR=".$_SESSION['personNBR']."
					WHERE ORD_DET_NBR=".$OrdDetNbr."";
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
<link rel="stylesheet" type="text/css" media="screen" href="css/popup.css" />
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<script type="text/javascript" src="framework/functions/default.js"></script>

<script type="text/javascript">
	function getInt(objectID)
	{
		if(document.getElementById(objectID).value=="")
		{
			return 0;
		}else{
			return parseInt(document.getElementById(objectID).value);
		}
	}
	function getFloat(objectID)
	{
		if(document.getElementById(objectID).value=="")
		{
			return 0;
		}else{
			return parseFloat(document.getElementById(objectID).value);
		}
	}
	function calcPay(){
		document.getElementById('TOT_SUB').value=(getInt('ORD_Q')-getInt('FAIL_CNT'))*(getInt('CAL_PRC')
												+getInt('FEE_CLM')+getInt('FEE_CLR')+getInt('FEE_MISC')
												-getInt('DISC_AMT'));
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
	if($addNew){$OrdDetNbr=0;}
?>
<div style="height:480px; overflow:auto">
<img class="toolbar-left" style="cursor:pointer" src="img/close.png" onclick="parent.document.getElementById('printDigitalPopupEdit').style.display='none';parent.document.getElementById('fade').style.display='none'"></a></p>

<?php
	$query="SELECT ORD_DET_NBR,ORD_NBR,ORD.CAL_NBR,CONCAT(CO_ID,CAL_ID,CAL_TYP) AS CAL_CODE,CAL_DESC,PRN_F,CASE WHEN PRN_F=1 THEN CAL_PRC_PRN ELSE CAL_PRC_BLK END AS CAL_PRC,ORD_Q,FAIL_CNT,DISC_PCT,DISC_AMT,FEE_CLM,FEE_CLR,FEE_MISC,TOT_SUB
				FROM CMP.CAL_ORD_DET ORD INNER JOIN CMP.CAL_LST CAL ON ORD.CAL_NBR=CAL.CAL_NBR INNER JOIN CMP.COMPANY COM ON CAL.CO_NBR=COM.CO_NBR
				WHERE ORD_DET_NBR=".$OrdDetNbr."";
	//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
?>

<script>
	parent.document.getElementById('content').contentDocument.getElementById('refresh-list').click();
</script>

<form enctype="multipart/form-data" action="#" method="post" style="width:450px;" onSubmit="return checkform();">
<table> <tr>
			<td style="vertical-align:top">Cari Barang</td>
			<td>
				<input type="text" id="livesearch" /></input>
				<input id="ORD_DET_NBR" name="ORD_DET_NBR" type="hidden" value="<?php echo $row['ORD_DET_NBR'];if($row['ORD_DET_NBR']==""){echo "-1";} ?>"/>
					<input id="CAL_NBR" name="CAL_NBR" value="<?php echo $row['CAL_NBR']; ?>" type="hidden"/>
					<input id="ORD_NBR" name="ORD_NBR" value="<?php echo $row['ORD_NBR']; ?>" type="hidden" />
					<input id="PRN_F" name="PRN_F" value="<?php echo $row['PRN_F']; ?>" type="hidden" />
				<div style="margin-top:5px;" class="edit-list-ls" id="liveRequestResults"></div>
				<div id="mainResult" ></div>
				<script>liveReqInit();</script>
			</td>
		</tr>
		<tr>
			<td>Deskripsi</td>
			<td><input id="CAL_DESC" name="CAL_DESC" value="<?php echo $row['CAL_DESC']; ?>" type="text" style="width:255px;" /></td>
		</tr>
		<tr class="std">
				<td><div id="label-print">Harga <?php if($row['PRN_F']==1){echo "cetak";};if($row['PRN_F']==0){echo "blanko";}; ?></div></td>
				<td><input id="CAL_PRC" name="CAL_PRC" value="<?php echo $row['CAL_PRC']; ?>" style="width:100px;" type="text" size="15" readonly /></td>
			</tr>
		<tr>
			<td>Jumlah Order</td>
			<td>
			<input id="ORD_Q" name="ORD_Q" onkeyup="calcPay();" value="<?php echo $row['ORD_Q']; ?>" style="width:100px;" type="text" size="15" />
					rusak
			<input id="FAIL_CNT" name="FAIL_CNT" onkeyup="calcPay();" value="<?php echo $row['FAIL_CNT']; ?>" style="width:110px;" type="text" size="15" />
			</td>
		</tr>
		<tr>
			<td>Diskon</td>
			<td>
				<input id="DISC_PCT" name="DISC_PCT" onkeyup="document.getElementById('DISC_AMT').value=parseInt(getInt('CAL_PRC')*getInt('DISC_PCT')/100);calcPay();" onchange="document.getElementById('DISC_AMT').value=parseInt(getInt('CAL_PRC')*getInt('DISC_PCT')/100);calcPay();" value="<?php echo $row['DISC_PCT']; ?>" type="text" style="width:100px;"  /> % atau
				<input id="DISC_AMT" name="DISC_AMT" onkeyup="document.getElementById('DISC_PCT').value=parseInt(getInt('DISC_AMT')*100/getInt('CAL_PRC'));calcPay();" onchange="document.getElementById('DISC_PCT').value=parseInt(getInt('DISC_AMT')*100/getInt('CAL_PRC'));calcPay();" value="<?php echo $row['DISC_AMT']; ?>" type="text" style="width:100px;" />
			</td>
				</tr>
		<tr>
			<td>Ongkos Klem/Spiral</td>
			<td><input id="FEE_CLM" name="FEE_CLM" value="<?php echo $row['FEE_CLM']; ?>" type="text" style="width:100px;" onkeyup="calcPay();" onchange="calcPay();" /></td>
	
		</tr>
		<tr>
			<td>Ongkos Warna</td>
			<td><input id="FEE_CLR" name="FEE_CLR" value="<?php echo $row['FEE_CLR']; ?>" type="text" style="width:100px;" onkeyup="calcPay();" onchange="calcPay();" /></td>
	
		</tr>
		<tr>
			<td>Ongkos lain-lain</td>
			<td><input id="FEE_MISC" name="FEE_MISC" onkeyup="calcPay();" onchange="calcPay();" value="<?php echo $row['FEE_MISC']; ?>" type="text" style="width:100px;" /></td>
		</tr>
		<tr>
			<td>Sub total</td>
			<td><input id="TOT_SUB" name="TOT_SUB" value="<?php echo $row['TOT_SUB']; ?>" type="text" style="width:100px" readonly /></td>
		</tr>
	</table>
	<br />
	<?php 
		
			if(@$_GET['readonly']!=1){
	?>
	<input class="process" type="submit" value="<?php if($addNew){echo 'Tambah';}else{echo 'Simpan';} ?>"/>
	<?php }?>
</form>
	
	<script>
		liveReqInit('livesearch','liveRequestResults','calendar-edit-list-detail-ls.php','','mainResult');
		<?php if($row['CAL_CODE']!="") { ?>
		getContent('liveRequestResults',"calendar-edit-list-detail-ls.php?CAL_CODE=<?php echo $row['CAL_CODE']; ?>");
		document.getElementById('liveRequestResults').style.display="";	
		document.getElementById('livesearch').value="<?php echo $row['CAL_CODE']; ?>";
		<?php } ?>
	</script>
</div>
</body>
</html>


