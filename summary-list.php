<?php
	include "framework/database/connect.php";
	include "framework/security/default.php";	
	$security=getSecurity($_SESSION['userID'],"DigitalPrint");

	$PrnDigEqp=$_GET['PRN_DIG_EQP'];
?>
<table style="background:#ffffff;width:99%">
	<tr>
		<th class="sortable" class="cursor:pointer">Type</th>
		<th colspan="2" class="sortable">Volume</th>
		<th colspan="2" class="sortable">Total Revenue</th>
		<th colspan="2" class="sortable">Average Revenue</th>
		<th colspan="2" class="sortable">Average Discount</th>
		<th colspan="2" class="sortable">Average Disc Pct</th>
		<th colspan="2" class="sortable">Average Vol Per Kind</th>
	</tr>
	<?php
		$query="SELECT PRN_DIG_DESC
					  ,PRN_DIG_EQP
					  ,COALESCE(SUM(ORD_Q*COALESCE(PRN_WID,1)*COALESCE(PRN_LEN,1)),0) AS VOL_ALL
					  ,COALESCE(SUM(CASE WHEN ORD_TS>=(CURRENT_TIMESTAMP - INTERVAL 30 DAY) THEN ORD_Q*COALESCE(PRN_WID,1)*COALESCE(PRN_LEN,1) ELSE 0 END),0) AS VOL_LM
					  ,COALESCE(SUM(TOT_SUB),0) AS REV_TOT_ALL
					  ,COALESCE(SUM(CASE WHEN ORD_TS>=(CURRENT_TIMESTAMP - INTERVAL 30 DAY) THEN TOT_SUB ELSE 0 END),0) AS REV_TOT_LM
					  ,COALESCE(SUM(TOT_SUB),0)/COALESCE(SUM(ORD_Q*COALESCE(PRN_WID,1)*COALESCE(PRN_LEN,1)),1) AS REV_AVG_ALL
					  ,COALESCE(SUM(CASE WHEN ORD_TS>=(CURRENT_TIMESTAMP - INTERVAL 30 DAY) THEN TOT_SUB ELSE 0 END),0)/COALESCE(SUM(CASE WHEN ORD_TS>=(CURRENT_TIMESTAMP - INTERVAL 30 DAY) THEN ORD_Q*COALESCE(PRN_WID,1)*COALESCE(PRN_LEN,1) ELSE 0 END),1) AS REV_AVG_LM
					  ,COALESCE(SUM(ORD_Q*COALESCE(PRN_WID,1)*COALESCE(PRN_LEN,1)*DET.PRN_DIG_PRC-TOT_SUB),0)/COALESCE(SUM(ORD_Q*COALESCE(PRN_WID,1)*COALESCE(PRN_LEN,1)),1) AS DISC_AVG_ALL
					  ,COALESCE(SUM(CASE WHEN ORD_TS>=(CURRENT_TIMESTAMP - INTERVAL 30 DAY) THEN ORD_Q*COALESCE(PRN_WID,1)*COALESCE(PRN_LEN,1)*DET.PRN_DIG_PRC-TOT_SUB ELSE 0 END),0)/COALESCE(SUM(CASE WHEN ORD_TS>=(CURRENT_TIMESTAMP - INTERVAL 30 DAY) THEN ORD_Q*COALESCE(PRN_WID,1)*COALESCE(PRN_LEN,1) ELSE 0 END),1) AS DISC_AVG_LM
					  ,COALESCE(SUM(ORD_Q*COALESCE(PRN_WID,1)*COALESCE(PRN_LEN,1)*DET.PRN_DIG_PRC-TOT_SUB),0) AS DISC_TOT_ALL
					  ,COALESCE(SUM(CASE WHEN ORD_TS>=(CURRENT_TIMESTAMP - INTERVAL 30 DAY) THEN ORD_Q*COALESCE(PRN_WID,1)*COALESCE(PRN_LEN,1)*DET.PRN_DIG_PRC-TOT_SUB ELSE 0 END),0) AS DISC_TOT_LM
					  ,AVG(ORD_Q*COALESCE(PRN_WID,1)*COALESCE(PRN_LEN,1)) AS VOL_AVG_ALL
					  ,AVG(CASE WHEN ORD_TS>=(CURRENT_TIMESTAMP - INTERVAL 30 DAY) THEN ORD_Q*COALESCE(PRN_WID,1)*COALESCE(PRN_LEN,1) ELSE NULL END) AS VOL_AVG_LM
				  FROM CMP.PRN_DIG_TYP TYP LEFT OUTER JOIN CMP.PRN_DIG_ORD_DET DET ON TYP.PRN_DIG_TYP=DET.PRN_DIG_TYP LEFT OUTER JOIN CMP.PRN_DIG_ORD_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR
 				 WHERE PRN_DIG_EQP='".$PrnDigEqp."' AND DET.DEL_NBR=0 AND DET.PRN_DIG_TYP !='PROD'				 GROUP BY PRN_DIG_EQP,TYP.PRN_DIG_TYP
				 ORDER BY PRN_DIG_EQP,VOL_LM DESC";
		//echo $query;
		$result=mysql_query($query);
		$alt="";
		while($rowd=mysql_fetch_array($result))
		{
			echo "<tr $alt>";
			echo "<td style='text-align:left;'>".$rowd['PRN_DIG_DESC']."</td>";
			echo "<td style='text-align:right;'>".number_format($rowd['VOL_ALL'],0,",",".")."</td>";
			echo "<td style='text-align:right;'>".number_format($rowd['VOL_LM'],0,",",".")."</td>";
			echo "<td style='text-align:right;'>".number_format($rowd['REV_TOT_ALL'],0,",",".")."</td>";
			echo "<td style='text-align:right;'>".number_format($rowd['REV_TOT_LM'],0,",",".")."</td>";
			echo "<td style='text-align:right;'>".number_format($rowd['REV_AVG_ALL'],0,",",".")."</td>";
			echo "<td style='text-align:right;'>".number_format($rowd['REV_AVG_LM'],0,",",".")."</td>";
			echo "<td style='text-align:right;'>".number_format($rowd['DISC_AVG_ALL'],0,",",".")."</td>";
			echo "<td style='text-align:right;'>".number_format($rowd['DISC_AVG_LM'],0,",",".")."</td>";
			if($rowd['REV_TOT_ALL']==0){$DiscPctAll=0;}else{$DiscPctAll=$rowd['DISC_TOT_ALL']/$rowd['REV_TOT_ALL']*100;}
			if($rowd['REV_TOT_LM']==0){$DiscPctLM=0;}else{$DiscPctLM=$rowd['DISC_TOT_LM']/$rowd['REV_TOT_LM']*100;}
			echo "<td style='text-align:right;'>".number_format($DiscPctAll,0,",",".")."%</td>";
			echo "<td style='text-align:right;'>".number_format($DiscPctLM,0,",",".")."%</td>";
			echo "<td style='text-align:right;'>".number_format($rowd['VOL_AVG_ALL'],2,",",".")."</td>";
			echo "<td style='text-align:right;'>".number_format($rowd['VOL_AVG_LM'],2,",",".")."</td>";
			echo "</tr>";
			if($alt==""){$alt="class='alt'";}else{$alt="";}
		}
	?>
</table>