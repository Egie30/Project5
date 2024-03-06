<?php
	@header("Connection: close\r\n"); 
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	$OrdNbr		= $_GET['ORD_NBR'];
	$OrdDetNbr	= $_GET['ORD_DET_NBR'];
	$type		= $_GET['TYP'];
	$changed	= false;
	$addNew		= false;
	
	if($type == "EST"){
		$headtable 	= "RTL.RTL_ORD_HEAD_EST";
		$detailtable= "RTL.RTL_ORD_DET_EST";
	}else{
		$headtable 	= "RTL.RTL_ORD_HEAD";
		$detailtable= "RTL.RTL_ORD_DET";
	}
	
	//Get order head information
	$query="SELECT 
		RCV_CO_NBR
	FROM ". $headtable ."
	WHERE ORD_NBR=".$OrdNbr;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	if($row['RCV_CO_NBR']!=""){
		$where	= "AND CO_NBR=".$row['RCV_CO_NBR'];
		$CoNbr	= $row['RCV_CO_NBR'];
	}
	
	//Process changes here
	if($_POST['ORD_DET_NBR']!="")
	{
		$OrdDetNbr=$_POST['ORD_DET_NBR'];
		//Take care of nulls
		if($_POST['ORD_Q']==""){$OrdQ="NULL";}else{$OrdQ=$_POST['ORD_Q'];}
		if($_POST['INV_NBR']==""){$InvNbr="NULL";}else{$InvNbr=$_POST['INV_NBR'];}
		if($_POST['INV_PRC']==""){$InvPrc="NULL";}else{$InvPrc=$_POST['INV_PRC'];}
		if($_POST['PRC']==""){$prc="NULL";}else{$prc=$_POST['PRC'];}
		if($_POST['FEE_MISC']==""){$FeeMisc="NULL";}else{$FeeMisc=$_POST['FEE_MISC'];}
		if($_POST['FAIL_CNT']==""){$FailCnt="NULL";}else{$FailCnt=$_POST['FAIL_CNT'];}
		if($_POST['DISC_PCT']==""){$DiscPct="NULL";}else{$DiscPct=$_POST['DISC_PCT'];}
		if($_POST['DISC_AMT']==""){$DiscAmt="NULL";}else{$DiscAmt=$_POST['DISC_AMT'];}
		if($_POST['TOT_SUB']==""){$TotSub="NULL";}else{$TotSub=$_POST['TOT_SUB'];}
		
		//Process add new
		if($OrdDetNbr==-1)
		{
			$addNew=true;
			$query="SELECT COALESCE(MAX(ORD_DET_NBR),0)+1 AS NEW_NBR FROM ". $detailtable ."";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			$OrdDetNbr=$row['NEW_NBR'];
			$query="INSERT INTO ". $detailtable ." (ORD_DET_NBR) VALUES (".$OrdDetNbr.")";
			$result=mysql_query($query);
			$create="CRT_TS=CURRENT_TIMESTAMP,CRT_NBR=".$_SESSION['personNBR'].",";

		}
		
		$query="UPDATE ". $detailtable ."
	   			SET ORD_NBR=".$OrdNbr.",
					ORD_Q=".$OrdQ.",
					INV_NBR='".$_POST['INV_NBR']."',
					INV_DESC='".$_POST['INV_DESC']."',
					INV_PRC=".$InvPrc.",
					PRC = ".$prc.",
					FEE_MISC=".$FeeMisc.",
					DISC_PCT=".$DiscPct.",
					DISC_AMT=".$DiscAmt.",
					TOT_SUB=".$TotSub.",".$create."
					UPD_TS=CURRENT_TIMESTAMP,
					UPD_NBR=".$_SESSION['personNBR']."
					WHERE ORD_DET_NBR=".$OrdDetNbr;
		//echo $query;
	   	$result=mysql_query($query);
	   	$changed=true;
		
		//Inquire historical discount
		$query="SELECT DISC_AMT FROM RTL.RAT_ENG
		WHERE INV_NBR='".$_POST['INV_NBR']."' $where AND DIV_ID='ORD' AND UPD_TS=(
			SELECT MAX(UPD_TS) FROM RTL.RAT_ENG WHERE INV_NBR='".$_POST['INV_NBR']."' $where AND DIV_ID='ORD'
		)"; 
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$lastAmount=$row['DISC_AMT'];
		
		if($lastAmount!=$DiscAmt){
			if($CoNbr==""){$CoNbr="NULL";}
			if(!($CoNbr=="NULL")){
				$query="INSERT INTO RTL.RAT_ENG (DIV_ID,INV_NBR,CO_NBR,PRSN_NBR,DISC_AMT,UPD_TS,UPD_NBR)
				VALUES ('ORD','".$_POST['INV_NBR']."',".$CoNbr.",NULL,".$DiscAmt.",CURRENT_TIMESTAMP,".$_SESSION['personNBR'].")";
				$result=mysql_query($query);
			}
		}
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
		var prnLength=1;
		var prnWidth=1;
		document.getElementById('TOT_SUB').value=getInt('ORD_Q')*(getInt('PRC')+getInt('FEE_MISC')-getInt('DISC_AMT'));
		if(getInt('PRC') < getInt('LOW_PRC')){
			document.getElementById('price-message').innerHTML="Harga tidak boleh melewati batas terendah";
			document.getElementById("process").disabled = true;
			document.getElementById("price-message").style.display = "";
			$('process').removeClass('disabled');
		}else{
			document.getElementById('price-message').innerHTML="";
			document.getElementById("price-message").style.display = "none";
			document.getElementById("process").disabled = false;
			$('process').disabled=false;
		}
	}
	function getLastDisc(){
	<?php
		$query="SELECT INV_NBR, DISC_AMT 
			FROM RTL.RAT_ENG
			WHERE DIV_ID='ORD' $where AND UPD_TS=(SELECT MAX(UPD_TS) FROM RTL.RAT_ENG WHERE DIV_ID='ORD' $where)";
		$result=mysql_query($query);
		while($row=mysql_fetch_array($result)){
	?>
		if(document.getElementById('INV_NBR').value == <?php echo $row['INV_NBR']; ?>){
			document.getElementById('DISC_AMT').value=<?php echo $row['DISC_AMT']; ?>;
			document.getElementById('DISC_PCT').value=(<?php echo $row['DISC_AMT']; ?> * 100)/getInt('PRC');
		}
	<?php
		}
	?>
	}
	/*
	var deductionValues = 0;
	jQuery("input[id^='PYMT_DOWN_DESC']").each(function(){
		var valAmt = jQuery(this).val();
		if (valAmt==''){valAmt=0;}
		deductionValues += parseInt(valAmt);
	});
	document.getElementById('TOT_PYMT_DOWN').value=deductionValues;*/
</script>

</head>

<body>

<?php
	if($changed){
		echo "<script>";
		echo "parent.document.getElementById('content').contentDocument.getElementById('rightpane').contentDocument.getElementById('refresh-list').click();";
		echo "parent.document.getElementById('content').contentDocument.getElementById('rightpane').contentDocument.getElementById('refresh-tot').click();";
		echo "</script>";
	}
	if($addNew){$OrdDetNbr=0;}
?>
<span class='fa fa-times toolbar' style='margin-left:10px' onclick="slideFormOut();"></span></a>

<?php
	$query="SELECT ORD_DET_NBR,
				   ORD_NBR,
				   INV_NBR,
				   INV_DESC,
				   INV_PRC,
				   ORD_Q,
				   PRC,
				   FEE_MISC,
				   DISC_PCT,
				   DISC_AMT,
				   TOT_SUB,
				   PYMT_DOWN_DESC
			FROM ". $detailtable ."
			WHERE ORD_DET_NBR=".$OrdDetNbr;
	//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	
	if (!empty($row['PYMT_DOWN_DESC'])) {
		$row['PYMT_DOWN_DESC'] = explode('+', $row['PYMT_DOWN_DESC']);
	} else {
		$row['PYMT_DOWN_DESC'] = array(); 
	}
	$PymtDescsQ = count($row['PYMT_DOWN_DESC']);
?>

<script>
	//parent.document.getElementById('content').contentDocument.getElementById('refresh-list').click();
</script>
<script type="text/javascript">	
	window.onload = function () {
		var pymtDescQ   = "<?php echo $PymtDescsQ; ?>";
		var pymtAmtArr  =  <?php echo json_encode($row['PYMT_DOWN_DESC']); ?>;
		
		for(i=1;i<=pymtDescQ; i++){
			var numx = i-1;
			console.log(i);
			if (i>1){
				document.getElementById('payAddButton').click();
			}
			document.getElementById('PYMT_DOWN_DESC'+i).value=pymtAmtArr[numx];
		}
	};
		
</script>
<form enctype="multipart/form-data" action="#" method="post" style="width:450px;" onSubmit="return checkform();">
<table>
		<tr>
			<td style="vertical-align:top">Cari Barang</td>
			<td>
				<input type="text" id="livesearch" autofocus />
			</td>
		</tr>
		<tr>
            <td colspan="2">
				<div style="margin-top:10px;width:410px;" class="edit-list-ls" id="liveRequestResults"></div>
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
			<td>Keterangan</td>
			<td><input id="INV_DESC" name="INV_DESC" value="<?php echo $row['INV_DESC']; ?>" type="text" style="width:300px;" /></td>
		</tr>
		<tr>
			<td>No. Inventaris</td>
			<td><input id="INV_NBR" name="INV_NBR" value="<?php echo $row['INV_NBR']; ?>" type="text" style="width:100px;" readonly /></td>
		</tr>
		
		<input id="INV_PRC" name="INV_PRC" value="<?php echo $row['INV_PRC']; ?>" type="hidden" style="width:100px;" onkeyup="calcPay();" onchange="calcPay();" />

		<tr>
			<td>Harga</td>
			<td>
				<input id="PRC"  name="PRC" value="<?php echo $row['PRC']; ?>" type="text" style="width:100px;" onkeyup="calcPay();" onchange="calcPay();" />
				<input id="TOP_PRC" name="TOP_PRC" value="<?php echo $row['TOP_PRC']; ?>" type="hidden" style="width:50px;" />
				<input id="LOW_PRC" name="LOW_PRC" value="<?php echo $row['LOW_PRC']; ?>" type="hidden" style="width:50px;"/>
				<div id="price-message" class="print-digital-red" style="display:none;padding: 3px 5px 3px 5px;background-color: #d92115;border-radius: 3px;-webkit-border-radius: 3px;-moz-border-radius: 3px;color: #ffffff;text-align: center;"></div>
			</td>
		</tr>
		<tr>
			<td>Discount</td>
			<td>
				<input id="DISC_PCT" name="DISC_PCT" onkeyup="document.getElementById('DISC_AMT').value=parseInt(getInt('PRC')*getInt('DISC_PCT')/100);calcPay();" onchange="document.getElementById('DISC_AMT').value=parseInt(getInt('PRC')*getInt('DISC_PCT')/100);calcPay();" value="<?php echo $row['DISC_PCT']; ?>" type="text" style="width:50px;"  /> % atau
				<input id="DISC_AMT" name="DISC_AMT" onkeyup="document.getElementById('DISC_PCT').value=parseInt(getInt('DISC_AMT')*100/getInt('PRC'));calcPay();" onchange="document.getElementById('DISC_PCT').value=parseInt(getInt('DISC_AMT')*100/getInt('PRC'));calcPay();" value="<?php echo $row['DISC_AMT']; ?>" type="text" style="width:100px;" />
			</td>
		</tr>
		
		<tr id="RowPayAdd" <?php echo $hide;?>>
			<td colspan="2" id="payAddGroup">
				<div id="payAddDiv" style="width: 100%;">
					<div style="float: left;width: 25%;">
						<label style="padding-top: 0px;">Extra</label>
						<div  class="listable-btn" style="margin-left:2px;cursor:pointer;" id='payAddButton'>
							<span class='fa fa-plus listable-btn' style="margin-top: 1px;"  title="Add New"></span>
						</div>
					</div>
					
					<div style="float: left;">
						<input name="PYMT_DOWN_DESC1" id="PYMT_DOWN_DESC1" size="11" onkeyup="calcPay();" onchange="calcPay();">
					</div>
					
					<div class="listable-btn" id="removeButton1" style="float: left;margin-left: 2px;cursor: pointer;margin-top: 5px;">
						<span class="fa fa-trash listable-btn"  style="margin-top: 1px;" title="Remove" onclick="removePotPayAdd('')"></span>
					</div>
				</div>
			</td>
			<input name="PAD" id="PAD"  size="5" value="1" type="hidden"/>
			<input name="TOT_PAY_ADD" id="TOT_PAY_ADD" type="hidden" size="10" value="0" />
		</tr>

		<tr>
			<td>
				<b>Total Extra</b>
			</td>
			<td><input name="TOT_PYMT_DOWN" id="TOT_PYMT_DOWN" size="15" onkeyup="calcPay();" value="<?php if ($row['TOT_PYMT_DOWN'] !=''){echo $row['TOT_PYMT_DOWN'];}else{echo '0';} ?>">
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
	<input class="process" id="process" type="submit" value="<?php if($addNew){echo 'Tambah';}else{echo 'Simpan';} ?>"/>
</form>
	<script type="text/javascript">

		jQuery(document).ready(function(){
	    	var counter = parseInt(document.getElementById('PAD').value)+1;

		  	jQuery("#payAddButton").click(function () {
		  		var length  = parseInt(jQuery("input[id^='PYMT_DOWN_DESC']").length)+1;
		  		var counter = parseInt(document.getElementById('PAD').value)+1;
				var newTextBoxDiv  = jQuery(document.createElement('div')).attr("id", 'TextBoxDiv' + counter);
				
				//console.log(newTextBoxDiv);
								
				newTextBoxDiv.css({"width":"100%","padding-top":"10px"});
				newTextBoxDiv.after().html('<br/>'+
					'</div>'
					+'<div style="float: left;width: 25%;"><label>Potongan </label></div>' +
				      '<div style="float: left;width: 95px;"><input type="text" size="11" name="PYMT_DOWN_DESC' + counter + 
				      '" id="PYMT_DOWN_DESC' + counter + '" onkeyup="calcPay();" onchange="calcPay();">'+
				      '<div class="listable-btn removeButton" id="removeButton'+counter+'" style="float:left;margin-left:2px;cursor:pointer;margin-top:5px;position: absolute;" onclick="removePot('+counter+')">'+
					'<span class="fa fa-trash listable-btn" style="margin-top: 1px;" title="Remove"></span>');
			
				newTextBoxDiv.appendTo("#payAddGroup");

				document.getElementById('PAD').value=counter;
				counter++;	
		    });
	  	});
</script>
	<script>
		liveReqInit('livesearch','liveRequestResults','retail-order-edit-list-detail-ls.php','','mainResult');
		<?php if($row['INV_NBR']!="") { ?>
		getContent('liveRequestResults',"retail-order-edit-list-detail-ls.php?INV_NBR=<?php echo $row['INV_NBR']; ?>");
		document.getElementById('liveRequestResults').style.display="";	
		document.getElementById('livesearch').value="<?php echo $row['INV_NBR']; ?>";
		<?php } ?>
	</script>

</div>
</body>
</html>


