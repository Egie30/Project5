<?php
	@header("Connection: close\r\n"); 
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	$OrdNbr=$_GET['ORD_NBR'];
	$OrdDetNbr=$_GET['ORD_DET_NBR'];
	$OrdValAddNbr=$_GET['ORD_VAL_ADD_NBR'];
	$changed=false;
	$addNew=false;
	//Process changes here
	if($_POST['ORD_VAL_ADD_NBR']!="")
	{
		$OrdValAddNbr=$_POST['ORD_VAL_ADD_NBR'];
		//Take care of nulls
		if($_POST['VAL_ADD_Q']==""){$ValAddQ="NULL";}else{$ValAddQ=$_POST['VAL_ADD_Q'];}
		if($_POST['FEE_MISC']==""){$FeeMisc="NULL";}else{$FeeMisc=$_POST['FEE_MISC'];}
		if($_POST['FAIL_CNT']==""){$FailCnt="NULL";}else{$FailCnt=$_POST['FAIL_CNT'];}
		if($_POST['DISC_PCT']==""){$DiscPct="NULL";}else{$DiscPct=$_POST['DISC_PCT'];}
		if($_POST['DISC_AMT']==""){$DiscAmt="NULL";}else{$DiscAmt=$_POST['DISC_AMT'];}
		if($_POST['TOT_SUB']==""){$TotSub="NULL";}else{$TotSub=$_POST['TOT_SUB'];}
		
		//Process add new
		if($OrdValAddNbr==-1)
		{
			$addNew=true;
			$query="SELECT COALESCE(MAX(ORD_VAL_ADD_NBR),0)+1 AS NEW_NBR FROM CMP.PRN_DIG_ORD_VAL_ADD";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			$OrdValAddNbr=$row['NEW_NBR'];
			$query="INSERT INTO CMP.PRN_DIG_ORD_VAL_ADD (ORD_VAL_ADD_NBR) VALUES (".$OrdValAddNbr.")";
			$result=mysql_query($query);
			$create="CRT_TS=CURRENT_TIMESTAMP,CRT_NBR=".$_SESSION['personNBR'].",";

		}
		
		$query="UPDATE CMP.PRN_DIG_ORD_VAL_ADD
	   			SET ORD_NBR=".$OrdNbr.",
	   				ORD_DET_NBR=".$OrdDetNbr.",
	   				VAL_ADD_TYP='".$_POST['VAL_ADD_TYP']."',
					VAL_ADD_Q=".$ValAddQ.",
					VAL_ADD_PRC=".$_POST['VAL_ADD_PRC'].",
					FEE_MISC=".$FeeMisc.",
					DISC_PCT=".$DiscPct.",
					DISC_AMT=".$DiscAmt.",
					TOT_SUB=".$TotSub.",".$create."
					UPD_TS=CURRENT_TIMESTAMP,
					UPD_NBR=".$_SESSION['personNBR']."
					WHERE ORD_VAL_ADD_NBR=".$OrdValAddNbr;
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
		document.getElementById('TOT_SUB').value=getInt('VAL_ADD_Q')*(getInt('VAL_ADD_PRC')
												+getInt('FEE_MISC')-getInt('DISC_AMT'));
	}
</script>

<!-- Assigning price to combo box change -->
<script>
	function setPrice(printDigitalType){

		var printDigitalPrice="";
		switch (printDigitalType) {
			<?php
				$query="SELECT VAL_ADD_TYP,VAL_ADD_DESC,VAL_ADD_PRC
						FROM CMP.PRN_DIG_VAL_ADD_TYP ORDER BY 2";
				$result=mysql_query($query);
				$defaultPrice=0;
				while($row=mysql_fetch_array($result))
				{
					if($defaultPrice==0){$defaultPrice=$row['VAL_ADD_PRC'];}
					echo "case '".$row['VAL_ADD_TYP']."': printDigitalPrice = '".$row['VAL_ADD_PRC']."'; break; \n";
				}
			?>
		}
		document.getElementById("VAL_ADD_PRC").value=printDigitalPrice;
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
	if($addNew){$OrdValAddNbr=0;}
?>

<img class="toolbar-left" style="cursor:pointer" src="img/close.png" onclick="parent.document.getElementById('printDigitalPopupEdit').style.display='none';parent.document.getElementById('fade').style.display='none'"></a></p>

<?php
	$query="SELECT ORD_VAL_ADD_NBR,
				ORD_DET_NBR,
				ORD_NBR,
				VAL_ADD_Q,
				VAL_ADD_PRC,
				VAL_ADD_TYP,
				FEE_MISC,
				DISC_PCT,
				DISC_AMT,
				TOT_SUB	
			FROM CMP.PRN_DIG_ORD_VAL_ADD
			WHERE ORD_VAL_ADD_NBR=".$OrdValAddNbr;
	//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
?>

<script>
	parent.document.getElementById('content').contentDocument.getElementById('refresh-list').click();
</script>

<form enctype="multipart/form-data" action="#" method="post" style="width:457px;height:435px;" onSubmit="return checkform();">
	<table>
		<tr>
			<td>Jumlah tambahan</td>
			<td>
				<input name="ORD_VAL_ADD_NBR" id="ORD_VAL_ADD_NBR" value="<?php echo $row['ORD_VAL_ADD_NBR'];if($row['ORD_VAL_ADD_NBR']==""){echo "-1";$addNew=true;} ?>" type="hidden" />
				<input id="VAL_ADD_Q" name="VAL_ADD_Q" onkeyup="calcPay();" onchange="calcPay();" value="<?php echo $row['VAL_ADD_Q']; ?>" type="text" style="width:100px" />
			</td>
		</tr>
		<tr>
			<td>Jenis barang</td>
			<td><select style="margin:0px;padding:0px;width:300px;" name="VAL_ADD_TYP" onchange="setPrice(this.value);calcPay();">
				<?php
					$query="SELECT VAL_ADD_TYP,VAL_ADD_DESC,VAL_ADD_PRC
							FROM CMP.PRN_DIG_VAL_ADD_TYP TYP ORDER BY 2";
					genCombo($query,"VAL_ADD_TYP","VAL_ADD_DESC",$row['VAL_ADD_TYP'],"");
				?>
				</select>

			</td>
		</tr>
		<tr>
			<td>Harga</td>
			<?php if($row['VAL_ADD_PRC']==""){$prnDigPrc=$defaultPrice;}else{$prnDigPrc=$row['VAL_ADD_PRC'];} ?>
			<td><input id="VAL_ADD_PRC" name="VAL_ADD_PRC" value="<?php echo $prnDigPrc; ?>" type="text" style="width:100px;" readonly /></td>
		</tr>
		<tr>
			<td>Discount</td>
			<td>
				<input id="DISC_PCT" name="DISC_PCT" onkeyup="document.getElementById('DISC_AMT').value=parseInt(getInt('VAL_ADD_PRC')*getInt('DISC_PCT')/100);calcPay();" onchange="document.getElementById('DISC_AMT').value=parseInt(getInt('VAL_ADD_PRC')*getInt('DISC_PCT')/100);calcPay();" value="<?php echo $row['DISC_PCT']; ?>" type="text" style="width:50px;"  /> % atau
				<input id="DISC_AMT" name="DISC_AMT" onkeyup="document.getElementById('DISC_PCT').value=parseInt(getInt('DISC_AMT')*100/getInt('VAL_ADD_PRC'));calcPay();" onchange="document.getElementById('DISC_PCT').value=parseInt(getInt('DISC_AMT')*100/getInt('VAL_ADD_PRC'));calcPay();" value="<?php echo $row['DISC_AMT']; ?>" type="text" style="width:100px;" />
			</td>
		</tr>
			<td>Ongkos lain-lain</td>
			<td><input id="FEE_MISC" name="FEE_MISC" onkeyup="calcPay();" onchange="calcPay();" value="<?php echo $row['FEE_MISC']; ?>" type="text" style="width:100px;" /></td>
		</tr>
		<tr>
			<td>Sub total</td>
			<td><input id="TOT_SUB" name="TOT_SUB" value="<?php echo $row['TOT_SUB']; ?>" type="text" style="width:100px" readonly /></td>
		</tr>
	</table>
	<br />
	<input class="process" type="submit" value="<?php if($addNew){echo 'Tambah';}else{echo 'Simpan';} ?>"/>
</body>
</html>


