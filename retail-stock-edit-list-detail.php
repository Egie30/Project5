<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	$OrdNbr			= $_GET['ORD_NBR'];
	$OrdDetNbr		= $_GET['ORD_DET_NBR'];
	$invoiceType 	= $_GET['IVC_TYP'];
	$changed=false;
	$addNew=false;
	//Process changes here
	if($_POST['ORD_DET_NBR']!="")
	{
		$OrdDetNbr=$_POST['ORD_DET_NBR'];
		//Take care of nulls
		if($_POST['ORD_Q']==""){$OrdQ="NULL";}else{$OrdQ=$_POST['ORD_Q'];}
		if($_POST['ORD_X']==""){$OrdX="NULL";}else{$OrdX=$_POST['ORD_X'];}
		if($_POST['ORD_Y']==""){$OrdY="NULL";}else{$OrdY=$_POST['ORD_Y'];}
		if($_POST['ORD_Z']==""){$OrdZ="NULL";}else{$OrdZ=$_POST['ORD_Z'];}
		if($_POST['INV_NBR']==""){$InvNbr="NULL";}else{$InvNbr=$_POST['INV_NBR'];}
		if($_POST['INV_PRC']==""){$InvPrc="NULL";}else{$InvPrc=$_POST['INV_PRC'];}
		if($_POST['FEE_MISC']==""){$FeeMisc="NULL";}else{$FeeMisc=$_POST['FEE_MISC'];}
		if($_POST['FAIL_CNT']==""){$FailCnt="NULL";}else{$FailCnt=$_POST['FAIL_CNT'];}
		if($_POST['DISC_PCT']==""){$DiscPct="NULL";}else{$DiscPct=$_POST['DISC_PCT'];}
		if($_POST['DISC_AMT']==""){$DiscAmt="NULL";}else{$DiscAmt=$_POST['DISC_AMT'];}
		if($_POST['TOT_SUB']==""){$TotSub="NULL";}else{$TotSub=$_POST['TOT_SUB'];}
		if($_POST['NBR_REF']==""){$NbrRef="NULL";}else{$NbrRef=$_POST['NBR_REF'];}
		if($_POST['NTE']==""){$Nte="NULL";}else{$Nte="'".$_POST['NTE']."'";}
		if($_POST['OUT_CMN_F']=="on"){$outsourceCom=1;}else{$outsourceCom=0;}
		
		//Process add new
		if($OrdDetNbr==-1)
		{
			$addNew=true;
			$query="SELECT COALESCE(MAX(ORD_DET_NBR),0)+1 AS NEW_NBR FROM RTL.RTL_STK_DET";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			$OrdDetNbr=$row['NEW_NBR'];
			$query="INSERT INTO RTL.RTL_STK_DET (ORD_DET_NBR) VALUES (".$OrdDetNbr.")";
			$result=mysql_query($query);
			$create="CRT_TS=CURRENT_TIMESTAMP,CRT_NBR=".$_SESSION['personNBR'].",";

		}
		
		$query="UPDATE RTL.RTL_STK_DET
	   			SET ORD_NBR=".$OrdNbr.",
					ORD_Q=".$OrdQ.",
					#ORD_X=".$OrdX.",
					#ORD_Y=".$OrdY.",
					#ORD_Z=".$OrdZ.",
					INV_NBR='".$_POST['INV_NBR']."',
					INV_DESC='".$_POST['INV_DESC']."',
					INV_PRC=".$InvPrc.",
					FEE_MISC=".$FeeMisc.",
					DISC_PCT=".$DiscPct.",
					DISC_AMT=".$DiscAmt.",
					TOT_SUB=".$TotSub.",".$create."
					#OUT_CMN_F=".$outsourceCom.",
					#ORD_DET_NBR_REF=".$NbrRef.",
					SER_NBR='".$_POST['SER_NBR']."',
					NTE=".$Nte.",
					UPD_TS=CURRENT_TIMESTAMP,
					UPD_NBR=".$_SESSION['personNBR']."
					WHERE ORD_DET_NBR=".$OrdDetNbr;
		//echo $query;
	   	$result=mysql_query($query);
		
		if($invoiceType=='RC'){
			$query	= "SELECT SHP_CO_NBR FROM RTL.RTL_STK_HEAD 
			WHERE ORD_NBR=".$OrdNbr." AND SHP_CO_NBR IN (SELECT GROUP_CONCAT(CO_NBR) AS ALL_COMPANY FROM NST.PARAM_COMPANY)";
			$result	= mysql_query($query);
			$row=mysql_fetch_array($result);
			if(mysql_num_rows($result)==0){
				include "framework/database/connect-cloud.php";
				$query="UPDATE $RTL.INVENTORY SET
					INV_PRC=".$InvPrc."
					WHERE INV_NBR=".$_POST['INV_NBR'];
				$result=mysql_query($query,$cloud);
				$query=str_replace($RTL,"RTL",$query);
				$result=mysql_query($query,$local);
			}
		}
	
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
    
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">

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
		var ordX=getFloat('ORD_X') || 1;
		var ordY=getFloat('ORD_Y') || 1;
        var ordZ=getFloat('ORD_Z') || 1;
		document.getElementById('TOT_SUB').value=getFloat('ORD_Q')*ordX*ordY*ordZ*(getInt('INV_PRC')
												+getInt('FEE_MISC')-getInt('DISC_AMT'));
	}
</script>

</head>

<?php
	if($changed){
		echo "<script>";
		echo "parent.document.getElementById('content').contentDocument.getElementById('refresh-list').click();";
		echo "parent.document.getElementById('content').contentDocument.getElementById('refresh-tot').click();";
		echo "</script>";
	}
	if($addNew){$OrdDetNbr=0;}
?>
<div style="height:100%;width:440px;overflow:auto">
<span class='fa fa-times toolbar' style='margin-left:10px' onclick="pushFormOut();"></span>

<script>
	parent.document.getElementById('content').contentDocument.getElementById('refresh-list').click();
</script>

<?php
	$query="SELECT ORD_DET_NBR,
				   ORD_NBR,
				   INV_NBR,
				   INV_DESC,
				   INV_PRC,
				   ORD_Q,
                   ORD_X,
                   ORD_Y,
                   ORD_Z,
				   FEE_MISC,
				   DISC_PCT,
				   DISC_AMT,
				   TOT_SUB,
				   OUT_CMN_F,
				   ORD_DET_NBR_REF,
				   SER_NBR,
				   NTE
			FROM RTL.RTL_STK_DET
			WHERE ORD_DET_NBR=".$OrdDetNbr;
	//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
?>

<?php if ($row['ORD_DET_NBR_REF']!="") { ?>
<body onload="showResult(<?php echo $row['ORD_DET_NBR_REF']; ?>)">
<?php } ?>

<form enctype="multipart/form-data" action="#" method="post" style="width:400px" onSubmit="return checkform();">
    <table>
		<tr>
			<td style="vertical-align:top">Cari Barang</td>
			<td>
				<input type="text" id="livesearch" /></input>
            </td>
        </tr>
        <tr>
            <td colspan='2'>
				<div style="margin-top:5px;" class="edit-list-ls" id="liveRequestResults"></div>
				<div id="mainResult" ></div>
				<script>liveReqInit();</script>
			</td>
		</tr>
		<tr>
			<td style='width:100px'>Jumlah order</td>
			<td>
				<input name="ORD_DET_NBR" id="ORD_DET_NBR" value="<?php echo $row['ORD_DET_NBR'];if($row['ORD_DET_NBR']==""){echo "-1";$addNew=true;} ?>" type="hidden" />
				<input id="ORD_Q" name="ORD_Q" onkeyup="calcPay();" onchange="calcPay();" value="<?php echo $row['ORD_Q']; ?>" type="text" style="width:100px" />
			</td>
		</tr>
		<tr>
			<td>Ukuran/Isi</td>
			<td>
                Panjang <input id="ORD_X" name="ORD_X" value="<?php echo $row['ORD_X']; ?>" type="text" style="width:30px;" onkeyup="calcPay();" /> x 
                Lebar <input id="ORD_Y" name="ORD_Y" value="<?php echo $row['ORD_Y']; ?>" type="text" style="width:30px;" onkeyup="calcPay();" /> x
                Tinggi <input id="ORD_Z" name="ORD_Z" value="<?php echo $row['ORD_Z']; ?>" type="text" style="width:30px;" onkeyup="calcPay();" />
            </td>
		</tr>
		<tr>
			<td>Keterangan</td>
			<td><input id="INV_DESC" name="INV_DESC" value="<?php echo $row['INV_DESC']; ?>" type="text" style="width:200px;" /></td>
		</tr>
		<tr>
			<td style='width:100px'>Outsource</td>
			<td>
				<div class='side' style='top:4px'><input name='OUT_CMN_F' id='OUT_CMN_F' type='checkbox' class='regular-checkbox' <?php if($row['OUT_CMN_F']=="1"){echo "checked";} ?>/>&nbsp;<label for="OUT_CMN_F"></label></div>
			</td>			
		</tr>
		<tr>
			<td style="vertical-align:top">PID</td>
			<td>
				<input type="text" id="NBR_REF" name="NBR_REF" value="<?php echo $row['ORD_DET_NBR_REF']; ?>" onkeyup="showResult(this.value)" onkeyup="showResult(this.value)" /></input>
            </td>
        </tr>
		<tr>
			<td colspan='2'>
				<div id="livesearch2"></div>
			</td>
		</tr>
		<tr>
			<td style='width:100px'>Serial Number</td>
			<td><input id="SER_NBR" name="SER_NBR" value="<?php echo $row['SER_NBR']; ?>" type="text" style="width:200px;" /></td>			
		</tr>
		<tr>
			<td style="vertical-align:top">Note</td>
			<td>
				<input type="text" id="NTE" name="NTE" value="<?php echo $row['NTE']; ?>"  /></input>
            </td>
        </tr>
		<tr>
			<td>No. Inventaris</td>
			<td><input id="INV_NBR" name="INV_NBR" value="<?php echo $row['INV_NBR']; ?>" type="text" style="width:100px;" readonly /></td>
		</tr>
		<tr>
			<td>Harga</td>
			<td><input id="INV_PRC" name="INV_PRC" value="<?php echo $row['INV_PRC']; ?>" type="text" style="width:100px;" onkeyup="calcPay();" onchange="calcPay();" /></td>
		</tr>
		<tr>
			<td>Discount</td>
			<td>
				<input id="DISC_PCT" name="DISC_PCT" onkeyup="document.getElementById('DISC_AMT').value=parseInt(getInt('RTL_PRC')*getInt('DISC_PCT')/100);calcPay();" onchange="document.getElementById('DISC_AMT').value=parseInt(getInt('INV_PRC')*getInt('DISC_PCT')/100);calcPay();" value="<?php echo $row['DISC_PCT']; ?>" type="text" style="width:50px;"  /> % atau
				<input id="DISC_AMT" name="DISC_AMT" onkeyup="document.getElementById('DISC_PCT').value=parseInt(getInt('DISC_AMT')*100/getInt('RTL_PRC'));calcPay();" onchange="document.getElementById('DISC_PCT').value=parseInt(getInt('DISC_AMT')*100/getInt('INV_PRC'));calcPay();" value="<?php echo $row['DISC_AMT']; ?>" type="text" style="width:100px;" />
			</td>
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
	<input class="process" type="submit" value="<?php if($addNew){echo 'Tambah';}else{echo 'Simpan';} ?>"/>
</form>
	
	<script>
		liveReqInit('livesearch','liveRequestResults','retail-stock-edit-list-detail-ls.php?IVC_TYP=<?php echo $invoiceType; ?>','','mainResult');
		<?php if($row['INV_NBR']!="") { ?>
		getContent('liveRequestResults',"retail-stock-edit-list-detail-ls.php?INV_NBR=<?php echo $row['INV_NBR']; ?>");
		document.getElementById('liveRequestResults').style.display="";	
		document.getElementById('livesearch').value="<?php echo $row['INV_NBR']; ?>";
		<?php } ?>
	</script>
<script>
	function showResult(str) {
		if (str == "") {
			document.getElementById("livesearch2").innerHTML="";
			return;
		} else { 
			if (window.XMLHttpRequest) {
				// code for IE7+, Firefox, Chrome, Opera, Safari
				xmlhttp = new XMLHttpRequest();
			} else {
				// code for IE6, IE5
				xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
			}
			xmlhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 200) {
					document.getElementById("livesearch2").innerHTML = this.responseText;
				} else {
					document.getElementById("livesearch2").innerHTML = "<div align='center' style='padding:5px'><div class='spinnerMini'><div class='double-bounce1'></div><div class='double-bounce2'></div></div></div>";
				}
			};
			xmlhttp.open("GET","pid-ls.php?q="+str,true);
			xmlhttp.send();
		}
	}
	</script>
</div>
</body>
</html>


