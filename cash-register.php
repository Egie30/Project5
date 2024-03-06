<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/alert/alert.php";
	include "framework/security/default.php";
	include "framework/functions/dotmatrix.php";
	
	$Security=getSecurity($_SESSION['userID'],"Finance");
	$UpperSec=getSecurity($_SESSION['userID'],"Executive");
		
	//Process delete all -- may need to look at the deletion of downpayments affecting the fulfillment payment.
	if($_GET['DEL_A']!="")
	{
		$query="SELECT REG_NBR,TRSC_NBR,CO_NBR,TND_AMT,ORD_NBR,CSH_FLO_TYP,PYMT_TYP,ACT_F
			FROM CMP.CSH_REG
			WHERE TRSC_NBR=".$_GET['DEL_A']." AND (ORD_NBR IS NOT NULL AND ORD_NBR!=0)";
		//echo $query."</br>";
		$result=mysql_query($query);
		while($row=mysql_fetch_array($result))
		{
			if($row['CSH_FLO_TYP']=="DP"){
				$query="UPDATE CMP.PRN_DIG_ORD_HEAD SET PYMT_DOWN=NULL,VAL_PYMT_DOWN=NULL WHERE ORD_NBR=".$row['ORD_NBR'];
			}
			if($row['CSH_FLO_TYP']=="FL"){
				$query="UPDATE CMP.PRN_DIG_ORD_HEAD SET PYMT_REM=NULL,VAL_PYMT_REM=NULL WHERE ORD_NBR=".$row['ORD_NBR'];
			}
			//echo $query."</br>";
			$resultd=mysql_query($query);
			$query="DELETE FROM CMP.CSH_REG WHERE TRSC_NBR=".$_GET['DEL_A'];
			//echo $query."</br>";
			$resultd=mysql_query($query);
		}
	}
		
	//If making payment
	if($_POST['PAY_AMT']!="")
	{
		//Get new register number
		$query="SELECT COALESCE(MAX(REG_NBR),0)+1 AS NEW_NBR FROM CMP.CSH_REG";
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$RegNbr=$row['NEW_NBR'];
		//Need to remove the hardcoded company number
		$query="INSERT INTO CMP.CSH_REG (REG_NBR,TRSC_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,PYMT_TYP,TND_AMT) VALUES (".$RegNbr.",".$_POST['TRSC_NBR'].",".$_SESSION['personNBR'].","."271".",'PA','".$_POST['PYMT_TYP']."',".$_POST['PAY_AMT'].")";
		//echo $query;
		$result=mysql_query($query);
		
		//If payment is complete
		if(($_POST['TOT_NET']-$_POST['PAY_AMT']-$_POST['DISC_AMT'])<=0){
			//Process discount
			if($_POST['DISC_AMT']>0){
				$query="SELECT COALESCE(MAX(REG_NBR),0)+1 AS NEW_NBR FROM CMP.CSH_REG";
				$result=mysql_query($query);
				$row=mysql_fetch_array($result);
				$RegNbr=$row['NEW_NBR'];
				$query="INSERT INTO CMP.CSH_REG (REG_NBR,TRSC_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,PYMT_TYP,TND_AMT) VALUES (".$RegNbr.",".$_POST['TRSC_NBR'].",".$_SESSION['personNBR'].","."271".",'DS','CSH',".$_POST['DISC_AMT'].")";
				$result=mysql_query($query);
			}
			
			$query="SELECT COALESCE(MAX(REG_NBR),0)+1 AS NEW_NBR FROM CMP.CSH_REG";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			$RegNbr=$row['NEW_NBR'];
			//Need to remove the hardcoded company number
			$query="INSERT INTO CMP.CSH_REG (REG_NBR,TRSC_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,PYMT_TYP,TND_AMT) VALUES (".$RegNbr.",".$_POST['TRSC_NBR'].",".$_SESSION['personNBR'].","."271".",'CH','CSH',".$_POST['CHG_AMT'].")";
			//echo $query;
			$result=mysql_query($query);
			
			//Generate receipt
			$receipt="            SALES TRANSACTION".chr(13).chr(10);
			$receipt.="------------------------------------------".chr(13).chr(10);
			//         123456789012345678901234567890123456789012
			
			$query="SELECT REG_NBR,TRSC_NBR,REG.CO_NBR,REG.RTL_BRC,RTL_Q,RTL.RTL_PRC,TRIM(CONCAT(TRIM(CONCAT(TRIM(CONCAT(TRIM(CONCAT(STA.NAME,' ',COLR_DESC)),' ',MATR)),' ',SIZE)),' ',TYPE)) AS NAME_DESC,TND_AMT,ORD_NBR,CSH_FLO_DESC,REG.CSH_FLO_TYP,CSH_FLO_MULT,CSH_FLO_PART,PYMT_DESC,REG.PYMT_TYP,ACT_F
				FROM CMP.CSH_REG REG INNER JOIN
				     CMP.COMPANY COM ON REG.CO_NBR=COM.CO_NBR INNER JOIN
				     CMP.CSH_FLO_TYP TYP ON REG.CSH_FLO_TYP=TYP.CSH_FLO_TYP LEFT OUTER JOIN
				     CMP.PYMT_TYP PAY ON REG.PYMT_TYP=PAY.PYMT_TYP LEFT OUTER JOIN RTL_TYP RTL ON REG.RTL_BRC=RTL.RTL_BRC LEFT OUTER JOIN CMP.STATIONERY STA ON RTL.RTL_NBR=STA.STA_NBR LEFT OUTER JOIN CMP.STA_COLR CLR ON STA.COLR_NBR=CLR.COLR_NBR
				WHERE ACT_F=1 AND CSH_FLO_PART='A' AND TRSC_NBR=".$_POST['TRSC_NBR'];
			//echo $query;
			$result=mysql_query($query);
			while($row=mysql_fetch_array($result)){
				if($row['CSH_FLO_TYP']=='RT'){
					$receipt.=followSpace(trim($row['RTL_BRC']." ".$row['NAME_DESC']),42).chr(13).chr(10);
			    	$receipt.=followSpace($row['RTL_Q']." x @ Rp. ".number_format($row['RTL_PRC'],0,".",",")." ".$row['ORD_NBR'],24)." Rp. ";
				}else{
					$receipt.=followSpace(trim(trim($row['CSH_FLO_DESC']." ".$row['PYMT_DESC'])." ".$row['ORD_NBR']),24)." Rp. ";

			    }
			    $receipt.=leadSpace($row['TND_AMT'],13).chr(13).chr(10);
		    	$TotNet+=$row['TND_AMT'];
			}
			$receipt.="------------------------------------------".chr(13).chr(10);
			$receipt.=followSpace("TOTAL",24)." Rp. ".leadSpace($TotNet,13).chr(13).chr(10);
			$query="SELECT REG_NBR,TRSC_NBR,REG.CO_NBR,TND_AMT,ORD_NBR,CSH_FLO_DESC,REG.CSH_FLO_TYP,CSH_FLO_MULT,CSH_FLO_PART,PYMT_DESC,REG.PYMT_TYP,ACT_F
				FROM CMP.CSH_REG REG INNER JOIN
				     CMP.COMPANY COM ON REG.CO_NBR=COM.CO_NBR INNER JOIN
				     CMP.CSH_FLO_TYP TYP ON REG.CSH_FLO_TYP=TYP.CSH_FLO_TYP LEFT OUTER JOIN
				     CMP.PYMT_TYP PAY ON REG.PYMT_TYP=PAY.PYMT_TYP
				WHERE ACT_F=1 AND CSH_FLO_PART='B' AND TRSC_NBR=".$_POST['TRSC_NBR'];
			$result=mysql_query($query);
			while($row=mysql_fetch_array($result)){
				$receipt.=followSpace("Diskon",24)." Rp. ".leadSpace($row['TND_AMT'],13).chr(13).chr(10);
				$receipt.="------------------------------------------".chr(13).chr(10);
				$receipt.=followSpace("NET TOTAL",24)." Rp. ".leadSpace($TotNet-$row['TND_AMT'],13).chr(13).chr(10);
			}
			$query="SELECT REG_NBR,TRSC_NBR,REG.CO_NBR,TND_AMT,ORD_NBR,CSH_FLO_DESC,REG.CSH_FLO_TYP,CSH_FLO_MULT,CSH_FLO_PART,PYMT_DESC,REG.PYMT_TYP,ACT_F
				FROM CMP.CSH_REG REG INNER JOIN
				     CMP.COMPANY COM ON REG.CO_NBR=COM.CO_NBR INNER JOIN
				     CMP.CSH_FLO_TYP TYP ON REG.CSH_FLO_TYP=TYP.CSH_FLO_TYP LEFT OUTER JOIN
				     CMP.PYMT_TYP PAY ON REG.PYMT_TYP=PAY.PYMT_TYP
				WHERE ACT_F=1 AND CSH_FLO_PART='C' AND TRSC_NBR=".$_POST['TRSC_NBR'];
			$result=mysql_query($query);
			while($row=mysql_fetch_array($result)){
				$receipt.=followSpace(trim(trim($row['CSH_FLO_DESC']." ".$row['PYMT_DESC'])." ".$row['ORD_NBR']),24)." Rp. ";
			    $receipt.=leadSpace($row['TND_AMT'],13).chr(13).chr(10);
			}
			$receipt.="------------------------------------------".chr(13).chr(10);
			$query="SELECT REG_NBR,TRSC_NBR,REG.CO_NBR,TND_AMT,ORD_NBR,CSH_FLO_DESC,REG.CSH_FLO_TYP,CSH_FLO_MULT,CSH_FLO_PART,PYMT_DESC,REG.PYMT_TYP,ACT_F
				FROM CMP.CSH_REG REG INNER JOIN
				     CMP.COMPANY COM ON REG.CO_NBR=COM.CO_NBR INNER JOIN
				     CMP.CSH_FLO_TYP TYP ON REG.CSH_FLO_TYP=TYP.CSH_FLO_TYP LEFT OUTER JOIN
				     CMP.PYMT_TYP PAY ON REG.PYMT_TYP=PAY.PYMT_TYP
				WHERE ACT_F=1 AND CSH_FLO_PART='D' AND TRSC_NBR=".$_POST['TRSC_NBR'];
			$result=mysql_query($query);
			while($row=mysql_fetch_array($result)){
				$receipt.=followSpace("Kembali",24)." Rp. ".leadSpace($row['TND_AMT'],13).chr(13).chr(10);
			}
			$receipt.=chr(13).chr(10);
			$receipt.=" Thank you for choosing Champion Printing".chr(13).chr(10);
			$receipt.="    ".leadZero($_POST['TRSC_NBR'],6)." ".leadZero($_SESSION['personNBR'],6)." ".date("d-m-Y")." ".date("H:m:s").chr(13).chr(10);
			$receipt.=chr(13).chr(10);
			$receipt.=chr(13).chr(10);
			$receipt.=chr(13).chr(10);
			$receipt.="            Champion Printing".chr(13).chr(10);
			$receipt.="   If you can think it, we can print it.".chr(13).chr(10);
			$receipt.=" Jl. Bhayangkara No. 33A Yogyakarta 55122".chr(13).chr(10);
			$receipt.="        E-mail: print@champs.asia".chr(13).chr(10);
			$receipt.=chr(13).chr(10);

			echo "<PRE>$receipt</PRE>";
			 
			$fh=fopen("cash-register/".$_POST['TRSC_NBR'].".txt","w");
			fwrite($fh,$receipt.chr(7));
			fclose($fh);

			//Change the flag to inactive
			$query="UPDATE CMP.CSH_REG SET ACT_F=0 WHERE TRSC_NBR=".$_POST['TRSC_NBR'];
			$result=mysql_query($query);
			
			exit;
		}
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4-core.js"></script>
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4.4-more.js"></script>
<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.js"></script>

<script type="text/javascript" src="framework/functions/default.js"></script>

<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />

<script type="text/javascript">
	//Get parameters
	var salesTax=getParam("tax","ppn");
	
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
	function getInt(objectID)
	{
		if(document.getElementById(objectID).value=="")
		{
			return 0;
		}else{
			return parseInt(document.getElementById(objectID).value);
		}
	}
	
	function attach_file(p_script_url){
     	// create new script element, set its relative URL, and load it
    	script=document.createElement('script');
     	script.src=p_script_url;
     	document.getElementsByTagName('head')[0].appendChild(script);
	}	
</script>

<link rel="stylesheet" type="text/css" media="screen" href="framework/alert/alert.css" />
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>

</head>

<body>

<?php
	$query="SELECT REG_NBR,TRSC_NBR,CO_NBR,TND_AMT,ORD_NBR,CSH_FLO_TYP,PYMT_TYP,ACT_F
			FROM CMP.CSH_REG
			WHERE ACT_F=1 AND CRT_NBR=".$_SESSION['personNBR'];
	//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	$TrscNbr=$row['TRSC_NBR'];

	//Assign temporary number if new transaction
	if($TrscNbr==""){
		$query="SELECT COALESCE(MAX(TRSC_NBR),0)+1 AS NEW_NBR FROM CMP.CSH_REG";
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$TrscNbr=$row['NEW_NBR'];
	}
?>

<div class="toolbar-only">
	<p class="toolbar-left"><a href="javascript:void(0)" onclick = "window.scrollTo(0,0);parent.document.getElementById('registerDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><img style="cursor:pointer" class="toolbar-left" src="img/delete.png"></a></p>
</div>

<!--
<div style="display:none;">
	<input id="refresh-list" type="button" value="Refresh" onclick="syncGetContent('edit-list','cash-register-list.php?TRSC_NBR=<?php echo $TrscNbr; ?>');" />
	<input id="refresh-tot" type="button" value="Total" onclick="calcAmt();" />
</div>
-->

<form enctype="multipart/form-data" action="#" method="post" style="width:350px">
	<p>
		<h3>
			<input id="TRSC_NBR" name="TRSC_NBR" type="hidden" value="<?php echo $TrscNbr; ?>"/>
			Nomor transaksi: <?php if($TrscNbr==""){echo "Kosong";$TrscNbr=0;}else{echo $TrscNbr;} ?>
		</h3>
		
		<!-- Retail Entry -->
		<input id="livesearch" style="width:250px" autofocus onFocus="this.select()" onKeyPress="return event.keyCode!=13" /> x <input id="RTL_Q" onFocus="this.select()" style="width:50px;text-align:right" value="1"/> <img src="img/plus.png" style="cursor:pointer" onclick="syncGetContent('edit-list','cash-register-list.php?PRSN_NBR=<?php echo $_SESSION['personNBR']; ?>&CO_NBR=271&RTL_BRC='+document.getElementById('livesearch').value+'&RTL_Q='+getInt('RTL_Q'));document.getElementById('RTL_Q').value='1';eval(document.getElementById('calcAmt').innerHTML);document.getElementById('livesearch').focus();">
		<div style="border:1px solid #dddddd;background:#ffffff" id="liveRequestResults"></div>
		<script>liveReqInit();</script><br />
		
		<!-- Listing -->
		<div id="edit-list" class="edit-list"></div>
		<script>syncGetContent('edit-list','cash-register-list.php?TRSC_NBR=<?php echo $TrscNbr; ?>');</script>
		
		<!-- Footer -->
		<div style="float:left;width:95px">
			<label>Persen Diskon</label><br />
			<input class="cashreg" name="DISC_PCT" id="DISC_PCT" onkeyup="eval(document.getElementById('calcDiscAmt').innerHTML);eval(document.getElementById('calcAmt').innerHTML);" onchange="eval(document.getElementById('calcDiscAmt').innerHTML);eval(document.getElementById('calcAmt').innerHTML);" type="text" style="width:95px;" value='<?php echo $_POST['DISC_PCT']; ?>' />
			<br />	
		</div>

		<div style="float:right;width:245px">
			<label>Jumlah Diskon</label><br />
			<input class="cashreg" name="DISC_AMT" id="DISC_AMT" onkeyup="eval(document.getElementById('calcDiscPct').innerHTML);eval(document.getElementById('calcAmt').innerHTML);" onchange="eval(document.getElementById('calcDiscPct').innerHTML);eval(document.getElementById('calcAmt').innerHTML);" type="text" style="width:240px;" value='<?php echo $_POST['DISC_AMT']; ?>' />
			<br />	
		</div>

		<div style="clear:both"></div>		

		<div style="float:left;width:345px;">
			<label>Total</label><br />
			<input class="cashreg" name="TOT_AMT" id="TOT_AMT" type="text" style="width:345px;" readonly /><br />
			<script>eval(document.getElementById('calcAmt').innerHTML);</script>
		</div>

		<div style="clear:both"></div>

		<div style="float:left;width:345px;">
			<label>Terima</label><br />
			<input class="cashreg" name="PAY_AMT" id="PAY_AMT" onkeyup="eval(document.getElementById('calcAmt').innerHTML);" onchange="eval(document.getElementById('calcAmt').innerHTML);" type="text" style="width:345px;" <?php echo $footerRead; ?> /><br />	
		</div>

		<div style="float:left;width:345px;">
			<label>Tipe Pembayaran</label><br />
			<select name="PYMT_TYP" id="PYMT_TYP">
			<?php
				$query="SELECT PYMT_TYP,PYMT_DESC
						FROM CMP.PYMT_TYP";
				genCombo($query,"PYMT_TYP","PYMT_DESC","CSH");
			?>
			</select><br />
		</div>

		<div style="clear:both"></div>

		<div style="float:left;width:345px;">
			<label>Kembali</label><br />
			<input class="cashreg" name="CHG_AMT" id="CHG_AMT" type="text" style="width:345px;" readonly /><br />	
		</div>

		
		<div style="width:100%;clear:both;border-bottom:1px solid #dddddd;margin-bottom:10px;"></div>
			
		<input class="process" type="submit" value="Bayar"/>		
		<!-- Deactivate validation check for now until the workflow process is figured out
			<input class="process" type="submit" value="Simpan" onClick="return checkValidation();"/>
		-->
	</p>		
</form>

<script>liveReqInit('livesearch','liveRequestResults','cash-register-ls.php','','retail-list');</script>

<script>
	parent.document.getElementById('registerDeleteYes').onclick=
	function () { 
		parent.document.getElementById('content').src='cash-register.php?DEL_A=<?php echo $TrscNbr ?>';
		parent.document.getElementById('registerDelete').style.display='none';
		parent.document.getElementById('fade').style.display='none';
	};
</script>
</body>
</html>