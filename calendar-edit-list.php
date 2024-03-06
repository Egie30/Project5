<?php
	include "framework/database/connect.php";
	$OrdNbr=$_GET['ORD_NBR'];
	$DelNbr=$_GET['DEL_D'];
	$TotNet=0;
	//$IvcTyp=$_GET['IVC_TYP'];

	if($DelNbr!=""){
		$query="DELETE FROM CMP.CAL_ORD_DET WHERE ORD_NBR=".$OrdNbr." AND ORD_DET_NBR=".$DelNbr;
		$result=mysql_query($query);
	}
?>
<table style="background:#ffffff;">
	<tr>
		<?php echo '
			<th class="listable">Jum</th>
			<th class="listable">Kode</th>
			<th class="listable">Deskripsi</th>
			<th class="listable">Harga</th>		
			<th class="listable">Disc</th>
			<th class="listable">Klem</th>
			<th class="listable">Warna</th>
			<th class="listable">Lain2</th>
			<th class="listable">Sub Total</th>	';
		?>		
		<th class="listable"><img class="listable" src="img/plus.png" onclick="if(document.getElementById('ORD_NBR').value==-1){parent.document.getElementById('invoiceAdd').style.display='block';parent.document.getElementById('fade').style.display='block';return;}parent.document.getElementById('printDigitalPopupEditContent').src='calendar-edit-list-detail.php?ORD_DET_NBR=0&ORD_NBR=<?php echo $OrdNbr."&IVC_TYP=".$IvcTyp; ?>';parent.document.getElementById('printDigitalPopupEdit').style.display='block';parent.document.getElementById('fade').style.display='block';"></th>
	
	</tr>
	<?php
	 if ($_GET['ORD_NBR'] != ''){
		$query="SELECT ORD_DET_NBR,DET.CAL_NBR,CONCAT(CO_ID,CAL_ID,CAL_TYP) AS CAL_CODE,CAL_DESC,ORD_Q,CASE WHEN PRN_F=1 THEN CAL_PRC_PRN ELSE CAL_PRC_BLK END AS CAL_PRC,FEE_CLM,FEE_CLR,FEE_MISC,FAIL_CNT,DISC_AMT,TOT_SUB
				FROM CMP.CAL_ORD_DET DET
					INNER JOIN CMP.CAL_LST LST ON DET.CAL_NBR=LST.CAL_NBR
					INNER JOIN CMP.COMPANY COM ON LST.CO_NBR=COM.CO_NBR
				WHERE ORD_NBR=".$OrdNbr."" ;
				
		//echo $query;
		$result=mysql_query($query);
		$alt="";
		while($rowd=mysql_fetch_array($result))
		{
			echo "<tr $alt>";
			echo "<td style='text-align:right;'>".($rowd['ORD_Q']-$rowd['FAIL_CNT'])."</td>";
			echo "<td>".$rowd['CAL_CODE']."</td>";
			echo "<td>".$rowd['CAL_DESC']."</td>";
			echo "<td style='text-align:right;'>".$rowd['CAL_PRC']."</td>";
			echo "<td style='text-align:right;'>".number_format($rowd['DISC_AMT'],0,",",".")."</td>";
			echo "<td style='text-align:right;'>".number_format($rowd['FEE_CLM'],0,",",".")."</td>";
			echo "<td style='text-align:right;'>".number_format($rowd['FEE_CLR'],0,",",".")."</td>";
			echo "<td style='text-align:right;'>".number_format($rowd['FEE_MISC'],0,",",".")."</td>";
			echo "<td style='text-align:right;'>".number_format($rowd['TOT_SUB'],0,",",".")."</td>";
			echo "<td style='text-align:center;' style='padding-left:2px;padding-right:2px;'>";
			echo "<img class='listable' src='img/write.png' style='cursor:pointer;' onclick=".chr(34)."parent.document.getElementById('printDigitalPopupEditContent').src='calendar-edit-list-detail.php?ORD_DET_NBR=".$rowd['ORD_DET_NBR']."&ORD_NBR=".$OrdNbr."&IVC_TYP=".$IvcTyp."';parent.document.getElementById('printDigitalPopupEdit').style.display='block';parent.document.getElementById('fade').style.display='block'".chr(34).">";
			echo "<img class='listable' src='img/trash.png' onclick=".chr(34)."getContent('edit-list','calendar-edit-list.php?ORD_NBR=".$OrdNbr."&DEL_D=".$rowd['ORD_DET_NBR']."');".chr(34).">";
			echo "</td></tr>";
			if($alt==""){$alt="class='alt'";}else{$alt="";}
			$TotNet+=$rowd['TOT_SUB'];
		}
		}	
	?>
</table>
<input type="hidden" id="TOT_NET" value="<?php echo $TotNet; ?>" />
