<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	$CoNbr=$_GET['CO_NBR'];
	$Typ=$_GET['TYP'];
	
	If($CoNbr==1){
		$query="SELECT HED.ORD_NBR,ORD_DTE AS SORT_DTE,DATE_FORMAT(ORD_DTE,'%d-%m-%Y') AS ORD_DTE,ORD_TTL,ORD_DESC,TYP.ORD_TYP,REF_NBR,
				SUM((CASE WHEN PRN_F=1 THEN CAL_PRC_PRN ELSE CAL_PRC_BLK END)*(ORD_Q-FAIL_CNT)) AS TOT_SUB,SUM(DISC_AMT*(ORD_Q-FAIL_CNT)) AS DISC,FEE_FLM+HED.FEE_MISC+SUM((FEE_CLR+FEE_CLM+DET.FEE_MISC)*(ORD_Q-FAIL_CNT)) AS FEE,TOT_AMT,PYMT_DOWN+PYMT_REM AS PYMT,TOT_REM,
				SUM(ORD_Q) AS ORD_Q,CASE WHEN CMS.NAME IS NULL THEN 'Tunai' ELSE CMS.NAME END AS SEL_NAME,CASE WHEN CMB.NAME IS NULL THEN 'Tunai' ELSE CMB.NAME END AS BUY_NAME,SUM(CASE WHEN CAL_TYP='ST' THEN ORD_Q ELSE 0 END) AS ST,SUM(CASE WHEN CAL_TYP='TR' THEN ORD_Q ELSE 0 END) AS TR,SUM(CASE WHEN CAL_TYP='CW' THEN ORD_Q ELSE 0 END) AS CW,SUM(CASE WHEN CAL_TYP='KK' THEN ORD_Q ELSE 0 END) AS KK,SUM(CASE WHEN CAL_TYP NOT IN ('ST','TR','CW','KK') THEN ORD_Q ELSE 0 END) AS LL,HED.ORD_TYP
				FROM CMP.CAL_ORD_HEAD HED INNER JOIN CMP.ORD_TYP TYP ON HED.ORD_TYP=TYP.ORD_TYP LEFT OUTER JOIN CMP.CAL_ORD_DET AS DET ON HED.ORD_NBR=DET.ORD_NBR LEFT OUTER JOIN CMP.CAL_LST LST ON DET.CAL_NBR=LST.CAL_NBR LEFT OUTER JOIN CMP.COMPANY CMS ON HED.SEL_CO_NBR=CMS.CO_NBR LEFT OUTER JOIN CMP.COMPANY CMB ON HED.BUY_CO_NBR=CMB.CO_NBR
				WHERE SEL_CO_NBR=".$CoNbr." AND HED.ORD_TYP IN ('RCV','INV','RET') AND BUY_CO_NBR=0 AND HED.ORD_DTE BETWEEN ".getFiscalYear()."
				GROUP BY HED.ORD_NBR,ORD_DTE,ORD_TTL,CASE WHEN CMS.NAME IS NULL THEN 'Tunai' ELSE CMS.NAME END,TOT_AMT
				ORDER BY 2";
	}else{
		$query="SELECT HED.ORD_NBR,ORD_DTE AS SORT_DTE,DATE_FORMAT(ORD_DTE,'%d-%m-%Y') AS ORD_DTE,ORD_TTL,ORD_DESC,TYP.ORD_TYP,REF_NBR,
				SUM((CASE WHEN PRN_F=1 THEN CAL_PRC_PRN ELSE CAL_PRC_BLK END)*(ORD_Q-FAIL_CNT)) AS TOT_SUB,SUM(DISC_AMT*(ORD_Q-FAIL_CNT)) AS DISC,FEE_FLM+HED.FEE_MISC+SUM((FEE_CLR+FEE_CLM+DET.FEE_MISC)*(ORD_Q-FAIL_CNT)) AS FEE,TOT_AMT,PYMT_DOWN+PYMT_REM AS PYMT,TOT_REM,
				SUM(ORD_Q) AS ORD_Q,CASE WHEN CMS.NAME IS NULL THEN 'Tunai' ELSE CMS.NAME END AS SEL_NAME,CASE WHEN CMB.NAME IS NULL THEN 'Tunai' ELSE CMB.NAME END AS BUY_NAME,SUM(CASE WHEN CAL_TYP='ST' THEN ORD_Q ELSE 0 END) AS ST,SUM(CASE WHEN CAL_TYP='TR' THEN ORD_Q ELSE 0 END) AS TR,SUM(CASE WHEN CAL_TYP='CW' THEN ORD_Q ELSE 0 END) AS CW,SUM(CASE WHEN CAL_TYP='KK' THEN ORD_Q ELSE 0 END) AS KK,SUM(CASE WHEN CAL_TYP NOT IN ('ST','TR','CW','KK') THEN ORD_Q ELSE 0 END) AS LL,HED.ORD_TYP
				FROM CMP.CAL_ORD_HEAD HED INNER JOIN CMP.ORD_TYP TYP ON HED.ORD_TYP=TYP.ORD_TYP LEFT OUTER JOIN CMP.CAL_ORD_DET AS DET ON HED.ORD_NBR=DET.ORD_NBR LEFT OUTER JOIN CMP.CAL_LST LST ON DET.CAL_NBR=LST.CAL_NBR LEFT OUTER JOIN CMP.COMPANY CMS ON HED.SEL_CO_NBR=CMS.CO_NBR LEFT OUTER JOIN CMP.COMPANY CMB ON HED.BUY_CO_NBR=CMB.CO_NBR
				WHERE ((SEL_CO_NBR=".$CoNbr." AND HED.ORD_TYP IN ('RCV','INV','RET')) OR (BUY_CO_NBR=".$CoNbr." AND HED.ORD_TYP IN ('RCV','INV','RET'))) AND HED.ORD_DTE BETWEEN ".getFiscalYear()."
				GROUP BY HED.ORD_NBR,ORD_DTE,ORD_TTL,CASE WHEN CMS.NAME IS NULL THEN 'Tunai' ELSE CMS.NAME END,TOT_AMT
				ORDER BY 2";
	}
		//echo $query;
		$result=mysql_query($query);

	
	echo "<br/><table style='width:100%'>";
			$rowcol="a";
		
			echo "<tr class='listable'>";
			echo "<th class='listable'>No.</th>";
			echo "<th class='listable'>Tgl.</th>";
			echo "<th class='listable'>Ref.</th>";
			echo "<th class='listable'>Judul</th>";
			echo "<th class='listable'>Jumlah</th>";
			echo "<th class='listable'>Disc</th>";
			echo "<th class='listable'>Ongkos</th>";
			echo "<th class='listable'>Total</th>";
			echo "<th class='listable'>Bayar</th>";
			echo "<th class='listable'>Sisa</th>";
			echo "</tr>";
	$order=0;
  	$inventory=0;
		while($row=mysql_fetch_array($result))
		
		{
		echo "<tr ".$alt." >";
		echo "<tr ".$alt." style='cursor:pointer;' onclick=".chr(34)."location.href='calendar-edit.php?ORD_NBR=".$row['ORD_NBR']."';".chr(34).">";
		echo "<td class='listable-first' align='center'>".$row['ORD_NBR']."</td>";
		echo "<td class='listable' align='center' nowrap>".$row['ORD_DTE']."</a></td>";
		echo "<td class='listable'>".$row['REF_NBR']."</a></td>";
		echo "<td class='listable'>".$row['ORD_TTL']."</a></td>";
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
		echo "<td class='listable' align='right'>".number_format($TotSubLine,0,",",".")."</td>";
		$TotSub+=$TotSubLine;
		echo "<td class='listable' align='right'>".number_format($DiscLine,0,",",".")."</td>";
		$Disc+=$DiscLine;
		echo "<td class='listable' align='right'>".number_format($FeeLine,0,",",".")."</td>";
		$Fee+=$FeeLine;
		echo "<td class='listable' align='right'>".number_format($TotAmtLine,0,",",".")."</td>";
		$TotAmt+=$TotAmtLine;
		echo "<td class='listable' align='right'>".number_format($PymtLine,0,",",".")."</td>";
		$Pymt+=$PymtLine;
		echo "<td class='listable' align='right'>".number_format($TotRemLine,0,",",".")."</td>";
		$TotRem+=$TotRemLine;
		echo "</tr>";
		if($rowcol=="a"){$rowcol="b";}else{$rowcol="a";}
		if($alt==""){$alt="class='alt'";}else{$alt="";}
	
}
		echo "<tr class='listable-row-".$rowcol."'>";
		echo "<td class='listable-first' align=left colspan='4'>";
		echo "<strong>Grand Total</strong>&nbsp;";
		echo "</td>";
		echo "<td class='listable' align='right'><strong>".number_format($TotSub,0,",",".")."</strong></td>";
		echo "<td class='listable' align='right'><strong>".number_format($Disc,0,",",".")."</strong></td>";
		echo "<td class='listable' align='right'><strong>".number_format($Fee,0,",",".")."</strong></td>";
		echo "<td class='listable' align='right'><strong>".number_format($TotAmt,0,",",".")."</strong></td>";
		echo "<td class='listable' align='right'><strong>".number_format($Pymt,0,",",".")."</strong></td>";
		echo "<td class='listable' align='right'><strong>".number_format($TotRem,0,",",".")."</strong></td>";
		echo "</tr>";

	echo "</table>";
/*
	function dispInv($ref,$val)
	{
		if($val==0){
			echo "<td class='sortable'></td>";
		}else{
			echo "<td class='sortable' align='right'><a href='calendar-ls-edit.php?LOG_NBR=".$ref."'>".number_format($val,0,",",".")."</a></td>";
		}
	}*/
?>
