<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	
	$OrdNbr		= $_GET['ORD_NBR'];
	$PymtNbr	= $_GET['PYMT_NBR'];
	$PymtTyp	= $_GET['PYMT_TYP'];
	$PymtAmt	= $_GET['PYMT_AMT'];
	$type		= $_GET['TYP'];
	
	if($type == "EST"){
		$headtable 		= "CMP.PRN_DIG_ORD_HEAD_EST";
		$paymenttable	= "CMP.PRN_DIG_ORD_PYMT_EST";
	}else{
		$headtable 		= "CMP.PRN_DIG_ORD_HEAD";
		$paymenttable	= "CMP.PRN_DIG_ORD_PYMT";
	}
	
	if($PymtTyp=="DEL"){
		//Process delete payment
		$query="UPDATE ". $paymenttable ." SET DEL_NBR=".$_SESSION['personNBR']." WHERE PYMT_NBR=$PymtNbr";
		//echo $query;
		$result=mysql_query($query);
	}else if($PymtTyp!=""){
		//Process add payment
		$query="INSERT INTO ". $paymenttable ." (ORD_NBR,PYMT_TYP,PYMT_AMT,CRT_NBR) VALUES ($OrdNbr,'$PymtTyp',$PymtAmt,".$_SESSION['personNBR'].")";
		//echo $query;
		$result=mysql_query($query);
	}

	//Process data update back to order head for compatibility
	
	
	$query="SELECT PYMT_NBR,ORD_NBR,PYMT_DESC,PYMT_AMT,DATE_FORMAT(CRT_TS,'%d.%m.%Y') AS DTE,VAL_NBR FROM ". $paymenttable ." PAY INNER JOIN CMP.PYMT_TYP TYP ON PAY.PYMT_TYP=TYP.PYMT_TYP WHERE ORD_NBR=$OrdNbr AND DEL_NBR=0";
	//echo $query;
	$result=mysql_query($query);
	echo "<table>";
	while($row=mysql_fetch_array($result)){
		echo "<tr class='total'>";
		echo "<td style='padding-left:7px'>";
		echo $row['PYMT_DESC'];
		if($row['VAL_NBR']!=""){echo " ".$row['DTE'];}else{echo " (Pending)";}
		echo "</td>";		
		echo "<td style='text-align:right'>";
		echo "<input value='".$row['PYMT_AMT']."' type='text' style='margin:1px;width:100px;border:none;text-align:right' readonly />";
		echo "</td>";
		echo "<td style='width:24px'>";
		if($row['VAL_NBR']==""){echo "<img src='img/trash.png' style='cursor:pointer;border:0px;padding-left:5px;vertical-align:baseline' onclick=".chr(34)."getContent('payments','print-digital-edit-payment.php?ORD_NBR=".$row['ORD_NBR']."&PYMT_NBR=".$row['PYMT_NBR']."&PYMT_TYP=DEL');calcAmt();".chr(34)." >";}
		echo "</td>";
		echo "</tr>";
	}
?>
	<tr class='total'>
		<td style='padding-left:7px'>
			Bayar
		</td>
		<td style="text-align:right;width:26px">
			<input name="PYMT_AMT" id="PYMT_AMT" type="text" style="width:100px;border:none;text-align:right" <?php echo $footerRead; ?> />	
		</td>
		<td style='width:24px'>
			<img src="img/plus.png" style="cursor:pointer;border:0px;padding-left:5px;vertical-align:baseline" onclick="getContent('payments','print-digital-edit-payment.php?ORD_NBR=<?php echo $OrdNbr; ?>&TYP=<?php echo $type; ?>&PYMT_TYP=CSH&PYMT_AMT='+document.getElementById('PYMT_AMT').value);calcAmt();" >
		</td>
	</tr>	
	<tr class='total'>
		<td style='font-weight:bold;color:#3464bc;border:0px;padding-left:7px'>
			Sisa
		</td>
		<td style="text-align:right;border:0px">
			<input name="TOT_REM" id="TOT_REM" value="<?php echo $row['TOT_REM']; ?>" type="text" style="width:100px;border:none;text-align:right" readonly />	
		</td>
		<td style="border:0px">
			<img src="img/calc.png" style="cursor:pointer;border:0px;padding-left:5px;vertical-align:baseline" onclick="calcAmt();" >
		</td>
	</tr>
</table>