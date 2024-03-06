<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	
	$CashSec=getSecurity($_SESSION['userID'],"Finance");
	
	$TndAmt=mysql_real_escape_string($_GET['TND_AMT']);
	$altOrig	= $_GET['ALT'];
	$alt		= $altOrig;
	$PymtNbr	= $_GET['PYMT_NBR'];
	$OrdNbr		= $_GET['ORD_NBR'];
	$PrsnNbr	= $_SESSION['personNBR'];
	$Del		= $_GET['PYMT_TYP'];
	$type		= $_GET['TYP'];
	
	if($type == "EST"){
		$headtable 		= "RTL.RTL_ORD_HEAD_EST";
		$paymenttable	= "RTL.RTL_ORD_PYMT_EST";
	}else{
		$headtable 		= "RTL.RTL_ORD_HEAD";
		$paymenttable	= "RTL.RTL_ORD_PYMT";
	}
	
	if($Del!=''){
		$query="UPDATE ". $paymenttable ." SET DEL_NBR=".$_SESSION['personNBR'].", UPD_TS=CURRENT_TIMESTAMP, CRT_TS=CRT_TS WHERE PYMT_NBR=$PymtNbr";
		//echo $query;
		$result=mysql_query($query);
	}
	
	if($TndAmt!=''){
		//Add Payment
		$query="INSERT INTO ". $paymenttable ." SET ORD_NBR=$OrdNbr,TND_AMT=$TndAmt,DEL_NBR=0,CRT_NBR=$PrsnNbr";
		$result=mysql_query($query);
	}
	if($TndAmt!=''){
		//Process payment journal
		$query="INSERT INTO RTL.JRN_CSH_FLO (DIV_ID,NM_TBL,ORD_NBR,CSH_FLO_TYP,CSH_AMT,CRT_TS,CRT_NBR)
				VALUES ('PRN','PRN_DIG_ORD_HEAD',".$OrdNbr.",'FL',".$TndAmt.",CURRENT_TIMESTAMP,".$_SESSION['personNBR'].")";
		$resultp=mysql_query($query);
	}
	
	//Process data update back to order head for compatibility
	$query="SELECT PYMT_NBR, ORD_NBR, VAL_NBR, TND_AMT, REF, PPL.NAME, DATE_FORMAT(PYM.CRT_TS,'%d-%m-%Y') AS DTE, PYM.CRT_NBR
			FROM ". $paymenttable ." PYM
			INNER JOIN CMP.PEOPLE PPL ON PYM.CRT_NBR = PPL.PRSN_NBR
			WHERE PYM.DEL_NBR=0 AND ORD_NBR=$OrdNbr 
			ORDER BY PYM.CRT_TS ASC";
			
	$result=mysql_query($query);
	$alt="";
	echo "<table>";
	while($row=mysql_fetch_array($result)){
		echo "<tr class='total' $alt";
		echo ">";
		echo "<td style='padding-left:7px;width:200px'>";
		echo "Pembayaran ".$row['PYMT_DESC']." ".parseDate($row['DTE'])."";
		echo "</td>";		
		echo "<td style='text-align:right;width:150px'>";
		echo "<input value='".$row['TND_AMT']."' type='text' style='margin:1px;width:100px;border:none;text-align:right;' readonly />";
		echo "</td>";
		echo "<td style='width:30px'>";

		if($row['VAL_NBR']=="" || $CashSec<2){
		//if(!((($row['PYMT_REM']!=0)||($row['PYMT_REM']!="")) && ($row['VAL_NBR']=='') && ($row['TOT_REM']=="0") &&($CashSec>2))){
		echo "<div class='listable-btn' style='margin-left:5px'><span class='fa fa-trash listable-btn' onclick=".chr(34)."syncGetContent('pay','retail-order-payment-list.php?ORD_NBR=".$OrdNbr."&PYMT_NBR=".$row['PYMT_NBR']."&TYP=".$type."&PYMT_TYP=DEL');calcAmt();".chr(34)."></span></div>";
		}
		$TotNet+=$rowa['TOT_SUB'];

		echo "</td>";
		echo "</tr>";
		if($alt==""){$alt="class='alt'";}else{$alt="";}
		$TotPay+=$row['TND_AMT'];
	}
?>
<script>
	parent.parent.document.getElementById('content').contentDocument.getElementById('refresh-pay').click();
	parent.parent.document.getElementById('content').contentDocument.getElementById('refresh-tot').click();
</script>
	<input type="hidden" id="TOT_PAY" onchange="calcAmt();" onclick="calcAmt();" value="<?php echo $TotPay; ?>" />
	
	<tr class='total'>
		<td style='font-weight:bold;color:#3464bc;padding-left:7px;width:200px'>Pembayaran</td>
		<td style="text-align:right;width:150px;">
			<input name="TND_AMT" id="TND_AMT" value="<?php echo $row['TND_AMT']; ?>" <?php if((($row['TND_AMT']!="")&&($row['VAL_PYMT_DOWN']!=""))||($CashSec>2)){echo "readonly";} ?> type="text" style="width:100px;border:none;text-align:right;" <?php echo $footerRead; ?> onkeyup="calcAmt();" onchange="calcAmt();" autocomplete="off"/></br>
		</td>
		<td style='width:30px;'>
			<div id='converse' class='listable-btn' style='margin-left:5px'><span class='fa fa-plus listable-btn' onclick="syncGetContent('pay','retail-order-payment-list.php?TND_AMT='+document.getElementById('TND_AMT').value+'&ORD_NBR=<?php echo $OrdNbr; ?>&TYP=<?php echo $type; ?>');calcAmt();" ></span></div>
		</td>
	</tr>
</table>
