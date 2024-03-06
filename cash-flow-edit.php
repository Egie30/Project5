<?php
	$POSID=$_GET['POS_ID'];
	$POSIP=$_GET['POSIP'];
	
	if($POSID != "") { require_once "framework/database/connect-cashier.php"; }
		else { require_once "framework/database/connect.php"; }

	require_once "framework/functions/default.php";
	require_once "framework/security/default.php";
	require_once "framework/alert/alert.php";
	require_once "framework/functions/dotmatrix.php";
	
	$RA="R".$_GET['RA'];
	$div=$_GET['DIV'];
	

	if(($_POST['CSH_AMT_R']!="")&&($_POST['CSH_AMT_R']!=0)){
				
			$transactionServer 	= 0;
			$transactionLocal 	= 1;
			
			$start = microtime(true);
			$limit = 0.1;  // Seconds

			while($transactionServer != $transactionLocal) {
				if (microtime(true) - $start >= $limit) {
				
				$query_cek			= "SELECT TRSC_NBR FROM RTL.CSH_REG_TOKEN";
				$result_cek			= mysql_query($query_cek, $rtl);
				$row_cek			= mysql_fetch_array($result_cek);
				$transactionServer	= $row_cek['TRSC_NBR'];
				
				$transactionLocal 	= $transactionServer+1;
				
				$query 				= "UPDATE RTL.CSH_REG_TOKEN SET TRSC_NBR = TRSC_NBR+1";
				$result				= mysql_query($query, $rtl);			
				
				$query_cek			= "SELECT TRSC_NBR FROM RTL.CSH_REG_TOKEN";
				$result_cek			= mysql_query($query_cek, $rtl);
				$row_cek			= mysql_fetch_array($result_cek);
				$transactionServer	= $row_cek['TRSC_NBR'];
				
				}
			}
			
			$transactionNumber = $transactionServer;
		
			$query 	= "SELECT Q_NBR, DATE(UPD_LAST) AS UPD_LAST FROM CSH.CSH_REG_TOKEN";
			$result	= mysql_query($query, $csh);
			$row	= mysql_fetch_array($result);
			
			if(date("Y-m-d") != $row['UPD_LAST'])
			{	$query = "UPDATE CSH.CSH_REG_TOKEN SET Q_NBR = 1, UPD_LAST = '".date('Y-m-d h:i:s')."' ";
				$result	= mysql_query($query, $csh);
				
				$query_cek			= "SELECT Q_NBR FROM CSH.CSH_REG_TOKEN";
				$result_cek			= mysql_query($query_cek, $csh);
				$row_cek			= mysql_fetch_array($result_cek);
				$QNumber				= $row_cek['Q_NBR'];
			}
			else {
				$query = "UPDATE CSH.CSH_REG_TOKEN SET Q_NBR = (Q_NBR+1), UPD_LAST = '".date('Y-m-d h:i:s')."' ";
				$result	= mysql_query($query, $csh);
				
			
				$query_cek			= "SELECT Q_NBR FROM CSH.CSH_REG_TOKEN";
				$result_cek			= mysql_query($query_cek, $csh);
				$row_cek			= mysql_fetch_array($result_cek);
				$QNumber			= $row_cek['Q_NBR'];
			}
			
	 //Insert into table
		if($_POST['RA']=="RE"){
			
			$check	= $_POST['check'];
			$debit	= $_POST['debit'];
			$credit	= $_POST['credit'];
			$cash	= $_POST['CSH_AMT_R'] - $check - $debit - $credit;
			
			//Check Transaksi agar tidak double
			$queryCk = "SELECT DATE(CRT_TS) AS CRT_DATE, CRT_NBR, CSH_FLO_TYP FROM RTL.CSH_REG WHERE CSH_FLO_TYP='DE' AND DATE(CRT_TS) = CURRENT_DATE AND CRT_NBR= ".$_SESSION['personNBR'];
			$resCk   = mysql_query($queryCk, $rtl);

			if (mysql_num_rows($resCk)  == 0){
			$query="INSERT INTO RTL.CSH_REG (TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,PYMT_TYP,TND_AMT,ACT_F,POS_ID) 
				VALUES (".$transactionNumber.",".$QNumber.",".$_SESSION['personNBR'].",".$CoNbrDef.",'DE','CSH',".$_POST['CSH_AMT_D'].",0,'".$POSID."')";
		   	$result=mysql_query($query, $rtl);
			$registerNumber = mysql_insert_id($rtl);
			$query="INSERT INTO CSH.CSH_REG (REG_NBR,TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,PYMT_TYP,TND_AMT,ACT_F) 
				VALUES (".$registerNumber.",".$transactionNumber.",".$QNumber.",".$_SESSION['personNBR'].",".$CoNbrDef.",'DE','CSH',".$_POST['CSH_AMT_D'].",0)";
			$result=mysql_query($query, $csh);
						
			$query="INSERT INTO RTL.CSH_REG (TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,PYMT_TYP,TND_AMT,ACT_F,POS_ID) 
				VALUES (".$transactionNumber.",".$QNumber.",".$_SESSION['personNBR'].",".$CoNbrDef.",'DR','CSH',".$_POST['CSH_AMT_R'].",0,'".$POSID."')";
		   	$result=mysql_query($query, $rtl);
			$registerNumber = mysql_insert_id($rtl);
			$query="INSERT INTO CSH.CSH_REG (REG_NBR,TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,PYMT_TYP,TND_AMT,ACT_F) 
				VALUES (".$registerNumber.",".$transactionNumber.",".$QNumber.",".$_SESSION['personNBR'].",".$CoNbrDef.",'DR','CSH',".$_POST['CSH_AMT_R'].",0)";
			$result=mysql_query($query, $csh);
			
			echo "<script>parent.document.getElementById('bottom').src='cashier-bottom.php?TRSC_NBR=".$transactionNumber."&DEFCO=".$CoNbrDef."&PRSN_NBR=".$_SESSION['personNBR']."&CSH=".$_SESSION['userID']."&Q_NBR=".$QNumber."&POS_ID=".$POSID."&CASH=".$cash."&DEBIT=".$debit."&CREDIT=".$credit."&CHECK=".$check."&RA=RE';</script>";			
			}
		}else{
			
			//Check Transaksi agar tidak double
			$queryCk = "SELECT DATE(CRT_TS) AS CRT_DATE, CRT_NBR, CSH_FLO_TYP FROM RTL.CSH_REG WHERE CSH_FLO_TYP='RA' AND DATE(CRT_TS) = CURRENT_DATE AND CRT_NBR= ".$_SESSION['personNBR'];
			$resCk   = mysql_query($queryCk, $rtl);
			
			if (mysql_num_rows($resCk)== 0){
			$query="INSERT INTO RTL.CSH_REG (TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,PYMT_TYP,TND_AMT,ACT_F,POS_ID) 
				VALUES (".$transactionNumber.",".$QNumber.",".$_SESSION['personNBR'].",".$CoNbrDef.",'RA','CSH',".$_POST['CSH_AMT_R'].",0,'".$POSID."')";
		   	$result=mysql_query($query, $rtl);
			$registerNumber = mysql_insert_id($rtl);
			
			$query="INSERT INTO CSH.CSH_REG (REG_NBR,TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,PYMT_TYP,TND_AMT,ACT_F) 
				VALUES (".$registerNumber.",".$transactionNumber.",".$QNumber.",".$_SESSION['personNBR'].",".$CoNbrDef.",'RA','CSH',".$_POST['CSH_AMT_R'].",0)";
		   	$result=mysql_query($query,$csh);
			
			echo "<script>parent.document.getElementById('bottom').src='cashier-bottom.php?TRSC_NBR=".$transactionNumber."&DEFCO=".$CoNbrDef."&PRSN_NBR=".$_SESSION['personNBR']."&CSH=".$_SESSION['userID']."&Q_NBR=".$QNumber."&POS_ID=".$POSID."&RA=RA';</script>";
			}
		}
	}
//echo $query;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4-core.js"></script>
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4.4-more.js"></script>
<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.js"></script>

<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />

<script type="text/javascript">
	window.addEvent('domready', function() {
	//Datepicker
	new CalendarEightysix('textbox-id');
	//Calendar
	new CalendarEightysix('block-element-id');
	});
	MooTools.lang.set('id-ID', 'Date', {
		months:    ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
		days:      ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
		dateOrder: ['date', 'month', 'year', '/']
	});
	MooTools.lang.setLanguage('id-ID');
</script>

<script type="text/javascript">
	function checkform()
	{
		if(document.getElementById('NAME').value=="")
		{
			window.scrollTo(0,0);
			document.getElementById('nameBlank').style.display='block';document.getElementById('fade').style.display='block';
			return false;
		}

		return true;
	}
</script>

<script type="text/javascript">
	function applyAbs(sourceObj,destinationID,dateID,valueID)
	{
		var strDate=document.getElementById(dateID).value.split("-");
		var nbrDays=32-new Date(strDate[0],strDate[1]-1,32).getDate();
		document.getElementById(destinationID).value=parseInt(sourceObj.value/nbrDays*document.getElementById(valueID).value/1000)*1000;
	}
	function getInt(objectID)
	{
		if(document.getElementById(objectID).value=="")
		{
			return 0;
		}else{
			return parseInt(document.getElementById(objectID).value);
		}
	}
	function calcPayD(){
		<?php
			$coins=array(25,50,100,200,500,1000);
			$bills=array(1000,2000,5000,10000,20000,50000,100000);
			foreach($coins as $coin)
			{
				echo "document.getElementById('DC".$coin."Tot').value=getInt('DC".$coin."')*".$coin.";";
			}
			foreach($bills as $bill)
			{
				echo "document.getElementById('DB".$bill."Tot').value=getInt('DB".$bill."')*".$bill.";";
			}
			echo "document.getElementById('CSH_AMT_D').value=getInt('check')+getInt('debit')+getInt('credit')";
			foreach($coins as $coin)
			{
				echo "+getInt('DC".$coin."Tot')";
			}
			foreach($bills as $bill)
			{
				echo "+getInt('DB".$bill."Tot')";
			}
			echo ";";
		?>
	}
		function calcPayR(){
		<?php
			$coins=array(25,50,100,200,500,1000);
			$bills=array(1000,2000,5000,10000,20000,50000,100000);
			foreach($coins as $coin)
			{
				echo "document.getElementById('RC".$coin."Tot').value=getInt('RC".$coin."')*".$coin.";";
			}
			foreach($bills as $bill)
			{
				echo "document.getElementById('RB".$bill."Tot').value=getInt('RB".$bill."')*".$bill.";";
			}
			echo "document.getElementById('CSH_AMT_R').value=0";
			foreach($coins as $coin)
			{
				echo "+getInt('RC".$coin."Tot')";
			}
			foreach($bills as $bill)
			{
				echo "+getInt('RB".$bill."Tot')";
			}
			echo ";";
		?>
	}

</script>

<link rel="stylesheet" type="text/css" media="screen" href="framework/alert/alert.css" />

</head>

<body>

<form enctype="multipart/form-data" action="" method="post" style="width:500px" onSubmit="enableCombos(this);">
	<p>	
		<input name="RA" id="RA" size="1" style='display:none' value='<?php echo $RA; ?>'>	
		<table class="flat" <?php if($RA=='RB'){echo "style='display:none'";} ?>>
		
			<tr class="flat"><td class="flat" colspan="3"><strong>Setoran</strong></td></tr>					
			<tr class="flat" style="height:10px"><td class="flat" colspan="3"><hr style="height:1px;border:0px;border-bottom:1px #CCCCCC solid" /></td></tr>

			<?php
				$count=1;
				foreach($coins as $coin)
				{
					echo "<tr>";
					echo "<td>";
					if($count==1){echo "Koin";}
					echo "</td>";
					echo "<td style='text-align:right'><input name='DC".$coin."' id='DC".$coin."' size='10' onkeyup='calcPayD();'></input> X <input size='5' readonly tabindex='-1' readonly value='".$coin."'></input>&nbsp;</td>";
					echo "<td>= Rp. <input readonly tabindex='-1' name='DC".$coin."Tot' id='DC".$coin."Tot' size='15'></td>";
					echo "</tr>";
					$count++;
				}
				$count=1;
				foreach($bills as $bill)
				{
					echo "<tr>";
					echo "<td>";
					if($count==1){echo "Lembar";}
					echo "</td>";
					echo "<td style='text-align:right'><input name='DB".$bill."' id='DB".$bill."' size='10' onkeyup='calcPayD();'></input> X <input size='5' readonly tabindex='-1' readonly value='".$bill."'></input>&nbsp;</td>";
					echo "<td>= Rp. <input readonly tabindex='-1' name='DB".$bill."Tot' id='DB".$bill."Tot' size='15''></td>";
					echo "</tr>";
					$count++;
				}
			?>

			<tr>
				<td align="right" colspan="2">Cek/Giro</td>
				<td>= Rp. <input name="check" id="check" size="15" onkeyup="calcPayD();"></td>
			</tr>
			<tr>
				<td align="right" colspan="2">Debit</td>
				<td>= Rp. <input name="debit" id="debit" size="15" onkeyup="calcPayD();"></td>
			</tr>
			<tr>
				<td align="right" colspan="2">Credit</td>
				<td>= Rp. <input name="credit" id="credit" size="15" onkeyup="calcPayD();"></td>
			</tr>

			<tr class="flat" style="height:10px">
				<td class="flat" colspan="3"><hr style="height:1px;border:0px;border-bottom:1px #CCCCCC solid" /></td>
			</tr>
			<tr>
				<td align="right" colspan="2"><strong>Total&nbsp;</strong></td>
				<td>= Rp. <input name="CSH_AMT_D" id="CSH_AMT_D" size="15" readonly tabindex="-1"></td>
			</tr>
			</span>
		</table>
			
		<table class="flat">
			<tr class="flat"><td class="flat" colspan="3"><strong>Uang Di Laci</strong></td></tr>					
			<tr class="flat" style="height:10px"><td class="flat" colspan="3"><hr style="height:1px;border:0px;border-bottom:1px #CCCCCC solid" /></td></tr>

			<?php 
				$count=1;
				foreach($coins as $coin)
				{
					echo "<tr>";
					echo "<td>";
					if($count==1){echo "Koin";}
					echo "</td>";
					echo "<td style='text-align:right'><input name='RC".$coin."' id='RC".$coin."' size='10' onkeyup='calcPayR();'></input> X <input size='5' readonly tabindex='-1' readonly value='".$coin."'></input>&nbsp;</td>";
					echo "<td>= Rp. <input readonly tabindex='-1' name='RC".$coin."Tot' id='RC".$coin."Tot' size='15'></td>";
					echo "</tr>";
					$count++;
				}
				$count=1;
				foreach($bills as $bill)
				{
					echo "<tr>";
					echo "<td>";
					if($count==1){echo "Lembar";}
					echo "</td>";
					echo "<td style='text-align:right'><input name='RB".$bill."' id='RB".$bill."' size='10' onkeyup='calcPayR();'></input> X <input size='5' readonly tabindex='-1' readonly value='".$bill."'></input>&nbsp;</td>";
					echo "<td>= Rp. <input readonly tabindex='-1' name='RB".$bill."Tot' id='RB".$bill."Tot' size='15''></td>";
					echo "</tr>";
					$count++;
				}
			?>

			<tr class="flat" style="height:10px"><td class="flat" colspan="3"><hr style="height:1px;border:0px;border-bottom:1px #CCCCCC solid" /></td></tr>
			<tr><td align="right" colspan="2"><strong>Total&nbsp;</strong></td><td>= Rp. <input name="CSH_AMT_R" id="CSH_AMT_R" size="15" readonly tabindex="-1"></td></tr>
			
			<tr class="flat" style="flat"><td class="flat" colspan="3"><input class="process" type="submit" value="Simpan"/><div></div></td></tr>	

		</table>		
	</p>		
</form>
<div></div>				
</body>
</html>
