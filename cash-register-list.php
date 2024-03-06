<?php
	include "framework/database/connect.php";
	include "framework/security/default.php";	
	include "framework/functions/dotmatrix.php";
	$security=getSecurity($_SESSION['userID'],"Finance");

	$TrscNbr=$_GET['TRSC_NBR'];
	$TotNet=0;
	
	//Process adding entry
	if($_GET['RTL_BRC']!="")
	{
		$PrsnNbr=$_GET['PRSN_NBR'];
		$RtlBrc=$_GET['RTL_BRC'];
		$RtlQ=$_GET['RTL_Q'];
		$CoNbr=$_GET['CO_NBR'];
		
		//Get Price
		$query="SELECT RTL_PRC FROM CMP.RTL_TYP WHERE RTL_BRC='".$RtlBrc."'";
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$RtlPrc=$row['RTL_PRC'];
		
		$TndAmt=$RtlPrc*$RtlQ;
	
		//Get new register number
		$query="SELECT COALESCE(MAX(REG_NBR),0)+1 AS NEW_NBR FROM CMP.CSH_REG";
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$RegNbr=$row['NEW_NBR'];
		
		//If active transaction exist, use the current transaction number for the corresponding person number
		$query="SELECT TRSC_NBR FROM CMP.CSH_REG WHERE ACT_F=1 AND CRT_NBR=".$PrsnNbr;
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$TrscNbr=$row['TRSC_NBR'];
		//Otherwise create a new one
		if($TrscNbr==""){
			$query="SELECT COALESCE(MAX(TRSC_NBR),0)+1 AS NEW_NBR FROM CMP.CSH_REG";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			$TrscNbr=$row['NEW_NBR'];
		}
		$query="INSERT INTO CMP.CSH_REG (REG_NBR,TRSC_NBR,CRT_NBR,CSH_FLO_TYP,CO_NBR,RTL_BRC,RTL_Q,TND_AMT) VALUES (".$RegNbr.",".$TrscNbr.",".$PrsnNbr.",'RT',".$CoNbr.",'".$RtlBrc."',".$RtlQ.",".$TndAmt.")";
		$result=mysql_query($query);
		//echo $query;
	}

	//Process delete entry
	if($_GET['DEL_D']!="")
	{
		$query="SELECT ORD_NBR,CSH_FLO_TYP FROM CMP.CSH_REG WHERE REG_NBR=".$_GET['DEL_D'];
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		if($row['CSH_FLO_TYP']=="DP"){
			$query="UPDATE CMP.PRN_DIG_ORD_HEAD SET TOT_REM=TOT_REM+PYMT_DOWN WHERE ORD_NBR=".$row['ORD_NBR'];
			$result=mysql_query($query);
			$query="UPDATE CMP.PRN_DIG_ORD_HEAD SET PYMT_DOWN=NULL,VAL_PYMT_DOWN=NULL WHERE ORD_NBR=".$row['ORD_NBR'];
			$result=mysql_query($query);
		}
		if($row['CSH_FLO_TYP']=="FL"){
			$query="UPDATE CMP.PRN_DIG_ORD_HEAD SET TOT_REM=TOT_REM+PYMT_REM WHERE ORD_NBR=".$row['ORD_NBR'];
			$result=mysql_query($query);
			$query="UPDATE CMP.PRN_DIG_ORD_HEAD SET PYMT_REM=NULL,VAL_PYMT_REM=NULL WHERE ORD_NBR=".$row['ORD_NBR'];
			$result=mysql_query($query);
		}
		//echo $query;
		$query="DELETE FROM CMP.CSH_REG WHERE REG_NBR=".$_GET['DEL_D'];
		$result=mysql_query($query);
	}
?>
<table style="background:#ffffff;">
	<tr>
		<th class="listable">Item</th>
		<th class="listable" style="text-align:right">Jumlah</th>
		<th class="listable"></th>
	</tr>
	<?php
		$query="SELECT REG_NBR,TRSC_NBR,REG.CO_NBR,REG.RTL_BRC,RTL_Q,RTL.RTL_PRC,CONCAT(STA.NAME,' ',COLR_DESC,' ',MATR,' ',SIZE,' ',TYPE) AS NAME_DESC,TND_AMT,ORD_NBR,CSH_FLO_DESC,REG.CSH_FLO_TYP,CSH_FLO_MULT,PYMT_DESC,REG.PYMT_TYP,ACT_F
				FROM CMP.CSH_REG REG INNER JOIN
				     CMP.COMPANY COM ON REG.CO_NBR=COM.CO_NBR LEFT JOIN
				     CMP.CSH_FLO_TYP TYP ON REG.CSH_FLO_TYP=TYP.CSH_FLO_TYP LEFT OUTER JOIN
				     CMP.PYMT_TYP PAY ON REG.PYMT_TYP=PAY.PYMT_TYP LEFT OUTER JOIN RTL_TYP RTL ON REG.RTL_BRC=RTL.RTL_BRC LEFT OUTER JOIN CMP.STATIONERY STA ON RTL.RTL_NBR=STA.STA_NBR LEFT OUTER JOIN CMP.STA_COLR CLR ON STA.COLR_NBR=CLR.COLR_NBR
				WHERE ACT_F=1 AND TRSC_NBR=".$TrscNbr."
				ORDER BY CSH_FLO_PART,REG_NBR";
		//echo $query;
		$result=mysql_query($query);
		$alt="";
		while($row=mysql_fetch_array($result))
		{
			echo "<tr $alt>";
			if($row['RTL_BRC']!=""){
				echo "<td style='text-align:left'>".$row['RTL_BRC']." ".$row['NAME_DESC']."<br>".$row['RTL_Q']." x @ Rp. ".number_format($row['RTL_PRC'],0,",",".")."</td>";
			}else{
				if($row['ORD_NBR']!=""){
					echo "<td style='text-align:left'>".$row['CSH_FLO_DESC']." ".$row['PYMT_DESC']." Nota ".leadZero($row['ORD_NBR'],6)."</td>";
				}else{
					echo "<td style='text-align:left'>".$row['CSH_FLO_DESC']." ".$row['PYMT_DESC']."</td>";
				}
			}
			echo "<td style='text-align:right'>".number_format($row['CSH_FLO_MULT']*$row['TND_AMT'],0,",",".")."</td>";
			echo "<td style='text-align:center' style='padding-left:2px;padding-right:2px;'>";
			if(($row['ORD_NBR']!="")||($row['ORD_NBR']!=0)){
				$query="SELECT VAL_PYMT_REM FROM CMP.PRN_DIG_ORD_HEAD WHERE ORD_NBR=".$row['ORD_NBR'];
				$resultd=mysql_query($query);
				$rowd=mysql_fetch_array($resultd);
				if(!(($rowd['VAL_PYMT_REM']!="")&&($row['CSH_FLO_TYP']=="DP"))){
					echo "<img class='listable' src='img/trash.png' onclick=".chr(34)."syncGetContent('edit-list','cash-register-list.php?TRSC_NBR=".$TrscNbr."&DEL_D=".$row['REG_NBR']."');eval(document.getElementById('calcAmt').innerHTML);".chr(34).">";
				}
			}
			if($row['ORD_NBR']==''){
				echo "<img class='listable' src='img/trash.png' onclick=".chr(34)."syncGetContent('edit-list','cash-register-list.php?TRSC_NBR=".$TrscNbr."&DEL_D=".$row['REG_NBR']."');eval(document.getElementById('calcAmt').innerHTML);".chr(34).">";
			}
			echo "</td></tr>";
			if($alt==""){$alt="class='alt'";}else{$alt="";}
			$TotNet+=$row['CSH_FLO_MULT']*$row['TND_AMT'];
			if($row['RTL_BRC']!=""){
				$TotRtl+=$row['CSH_FLO_MULT']*$row['TND_AMT'];
			}
		}
	?>
</table>
<input type="hidden" id="TOT_NET" name="TOT_NET" value="<? echo $TotNet; ?>" />
<input type="hidden" id="TOT_RTL" name="TOT_RTL" value="<? echo $TotRtl; ?>" />
<script id="calcDiscPct">
	document.getElementById('DISC_PCT').value=parseInt(document.getElementById('DISC_AMT').value/document.getElementById('TOT_RTL').value*100);
</script>
<script id="calcDiscAmt">
	document.getElementById('DISC_AMT').value=parseInt(document.getElementById('DISC_PCT').value*document.getElementById('TOT_RTL').value/100);
</script>
<script id="calcAmt">
	document.getElementById('TOT_AMT').value=<? echo $TotNet; ?>-document.getElementById('DISC_AMT').value;
	if(getInt('TOT_AMT')>getInt('PAY_AMT')){
	//alert('1');
		document.getElementById('CHG_AMT').value=0;
	}else{
	//alert('2');
		document.getElementById('CHG_AMT').value=getInt('PAY_AMT')-getInt('TOT_AMT');
	}
</script>