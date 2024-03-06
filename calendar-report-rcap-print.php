<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	$CoNbr=$_GET['CO_NBR'];
?>

<pre style='font-size:11px'>
REKAPITULASI NOTA

<?php
    $query="SELECT CO_NBR,NAME,ADDRESS,CITY_NM
			FROM CMP.COMPANY COM INNER JOIN CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID WHERE CO_NBR=".$CoNbr;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	echo $row['NAME']." ";
	echo $row['ADDRESS'].", ";
	echo $row['CITY_NM']."\n";
	echo "Printed on ".date("d-m-Y h:s:m")."\n\n";;
	
	If($CoNbr==1){
		$query="SELECT HED.ORD_NBR,ORD_DTE AS SORT_DTE,DATE_FORMAT(ORD_DTE,'%d-%m') AS ORD_DTE,ORD_TTL,ORD_DESC,TYP.ORD_TYP,REF_NBR,
				SUM((CASE WHEN PRN_F=1 THEN CAL_PRC_PRN ELSE CAL_PRC_BLK END)*(ORD_Q-FAIL_CNT)) AS TOT_SUB,SUM(DISC_AMT*(ORD_Q-FAIL_CNT)) AS DISC,FEE_FLM+HED.FEE_MISC+SUM((FEE_CLR+FEE_CLM+DET.FEE_MISC)*(ORD_Q-FAIL_CNT)) AS FEE,TOT_AMT,PYMT_DOWN+PYMT_REM AS PYMT,TOT_REM,
				SUM(ORD_Q) AS ORD_Q,CASE WHEN CMS.NAME IS NULL THEN 'Tunai' ELSE CMS.NAME END AS SEL_NAME,CASE WHEN CMB.NAME IS NULL THEN 'Tunai' ELSE CMB.NAME END AS BUY_NAME,SUM(CASE WHEN CAL_TYP='ST' THEN ORD_Q ELSE 0 END) AS ST,SUM(CASE WHEN CAL_TYP='TR' THEN ORD_Q ELSE 0 END) AS TR,SUM(CASE WHEN CAL_TYP='CW' THEN ORD_Q ELSE 0 END) AS CW,SUM(CASE WHEN CAL_TYP='KK' THEN ORD_Q ELSE 0 END) AS KK,SUM(CASE WHEN CAL_TYP NOT IN ('ST','TR','CW','KK') THEN ORD_Q ELSE 0 END) AS LL,HED.ORD_TYP
				FROM CMP.CAL_ORD_HEAD HED INNER JOIN CMP.ORD_TYP TYP ON HED.ORD_TYP=TYP.ORD_TYP LEFT OUTER JOIN CMP.CAL_ORD_DET AS DET ON HED.ORD_NBR=DET.ORD_NBR LEFT OUTER JOIN CMP.CAL_LST LST ON DET.CAL_NBR=LST.CAL_NBR LEFT OUTER JOIN CMP.COMPANY CMS ON HED.SEL_CO_NBR=CMS.CO_NBR LEFT OUTER JOIN CMP.COMPANY CMB ON HED.BUY_CO_NBR=CMB.CO_NBR
				WHERE SEL_CO_NBR=".$CoNbr." AND HED.ORD_TYP IN ('RCV','INV','RET') AND BUY_CO_NBR=0 AND HED.ORD_DTE BETWEEN ".getFiscalYear()."
				GROUP BY HED.ORD_NBR,ORD_DTE,ORD_TTL,CASE WHEN CMS.NAME IS NULL THEN 'Tunai' ELSE CMS.NAME END,TOT_AMT
				ORDER BY 2";
	}else{
		$query="SELECT HED.ORD_NBR,ORD_DTE AS SORT_DTE,DATE_FORMAT(ORD_DTE,'%d-%m') AS ORD_DTE,ORD_TTL,ORD_DESC,TYP.ORD_TYP,REF_NBR,
				SUM((CASE WHEN PRN_F=1 THEN CAL_PRC_PRN ELSE CAL_PRC_BLK END)*(ORD_Q-FAIL_CNT)) AS TOT_SUB,SUM(DISC_AMT*(ORD_Q-FAIL_CNT)) AS DISC,FEE_FLM+HED.FEE_MISC+SUM((FEE_CLR+FEE_CLM+DET.FEE_MISC)*(ORD_Q-FAIL_CNT)) AS FEE,TOT_AMT,PYMT_DOWN+PYMT_REM AS PYMT,TOT_REM,
				SUM(ORD_Q) AS ORD_Q,CASE WHEN CMS.NAME IS NULL THEN 'Tunai' ELSE CMS.NAME END AS SEL_NAME,CASE WHEN CMB.NAME IS NULL THEN 'Tunai' ELSE CMB.NAME END AS BUY_NAME,SUM(CASE WHEN CAL_TYP='ST' THEN ORD_Q ELSE 0 END) AS ST,SUM(CASE WHEN CAL_TYP='TR' THEN ORD_Q ELSE 0 END) AS TR,SUM(CASE WHEN CAL_TYP='CW' THEN ORD_Q ELSE 0 END) AS CW,SUM(CASE WHEN CAL_TYP='KK' THEN ORD_Q ELSE 0 END) AS KK,SUM(CASE WHEN CAL_TYP NOT IN ('ST','TR','CW','KK') THEN ORD_Q ELSE 0 END) AS LL,HED.ORD_TYP
				FROM CMP.CAL_ORD_HEAD HED INNER JOIN CMP.ORD_TYP TYP ON HED.ORD_TYP=TYP.ORD_TYP LEFT OUTER JOIN CMP.CAL_ORD_DET AS DET ON HED.ORD_NBR=DET.ORD_NBR LEFT OUTER JOIN CMP.CAL_LST LST ON DET.CAL_NBR=LST.CAL_NBR LEFT OUTER JOIN CMP.COMPANY CMS ON HED.SEL_CO_NBR=CMS.CO_NBR LEFT OUTER JOIN CMP.COMPANY CMB ON HED.BUY_CO_NBR=CMB.CO_NBR
				WHERE ((SEL_CO_NBR=".$CoNbr." AND HED.ORD_TYP IN ('RCV','INV','RET')) OR (BUY_CO_NBR=".$CoNbr." AND HED.ORD_TYP IN ('RCV','INV','RET'))) AND HED.ORD_DTE BETWEEN ".getFiscalYear()."
				GROUP BY HED.ORD_NBR,ORD_DTE,ORD_TTL,CASE WHEN CMS.NAME IS NULL THEN 'Tunai' ELSE CMS.NAME END,TOT_AMT
				ORDER BY 2";
	}
	//echo $query;
	$result=mysql_query($query);
	
	//    123456 12345 1234567890 12345678901234567890 1.123.123.123 1.123.123.123 123.123.123 1.123.123.123 1.123.123.123 1.123.123.123"
	echo "   No. Tgl.  No. Ref    Judul                       Jumlah      Discount      Ongkos         Total         Bayar          Sisa\n";
	echo "------------------------------------------------------------------------------------------------------------------------------\n";
	while($row=mysql_fetch_array($result))
	{
		if($row['ORD_TYP']=='RET'){
			$TotSubLine=-$row['TOT_SUB'];
			$DiscLine=-$row['DISC'];
			$FeeLine=-$row['FEE'];
			$TotAmtLine=-$row['TOT_AMT'];
			$PymtLine=-$row['PYMT'];
			$TotRemLine=-$row['TOT_REM'];
		}else{
			$TotSubLine=$row['TOT_SUB'];
			$DiscLine=$row['DISC'];
			$FeeLine=$row['FEE'];
			$TotAmtLine=$row['TOT_AMT'];
			$PymtLine=$row['PYMT'];
			$TotRemLine=$row['TOT_REM'];
		}
		echo space(6-strlen($row['ORD_NBR'])).$row['ORD_NBR']." ";
		echo $row['ORD_DTE']." ";
		echo substr($row['REF_NBR'],0,10).space(10-strlen(substr($row['REF_NBR'],0,10)))." ";
		echo substr($row['ORD_TTL'],0,20).space(20-strlen(substr($row['ORD_TTL'],0,20)))." ";
		echo fix_cur($TotSubLine,13)." ";
		echo fix_cur($DiscLine,13)." ";
		echo fix_cur($FeeLine,11)." ";
		echo fix_cur($TotAmtLine,13)." ";
		echo fix_cur($PymtLine,13)." ";
		echo fix_cur($TotRemLine,13)." ";
		echo "\n";
		$TotSub+=$TotSubLine;
		$Disc+=$DiscLine;
		$Fee+=$FeeLine;
		$TotAmt+=$TotAmtLine;
		$Pymt+=$PymtLine;
		$TotRem+=$TotRemLine;

	}
	echo "------------------------------------------------------------------------------------------------------------------------------\n";	
	//    123456 12345 1234567890 12345678901234567890 1.123.123.123 1.123.123.123 123.123.123 1.123.123.123 1.123.123.123 1.123.123.123"
	echo "                                       TOTAL ";
	echo fix_cur($TotSub,13)." ";
	echo fix_cur($Disc,13)." ";
	echo fix_cur($Fee,11)." ";
	echo fix_cur($TotAmt,13)." ";
	echo fix_cur($Pymt,13)." ";
	echo fix_cur($TotRem,13)." ";
?>

</pre>

<script>
	window.print();
</script>

<?php
	function space($nbrSpaces)
	{
		for($i=1;$i<=$nbrSpaces;$i++)
		{
			$strSpace.=" ";
		}
		return $strSpace;
	}
	function fix_cur($value,$nbrSpaces)
	{
		$strVal=number_format($value,0,",",".");
		return space($nbrSpaces-strlen($strVal)).$strVal;
	}
?>