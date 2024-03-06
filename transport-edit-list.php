<?php
	include "framework/database/connect.php";
	$TrnspNbr=$_GET['TRNSP_NBR'];
	$DelDet=$_GET['DEL_D'];

	if($DelDet!=""){
		$query="UPDATE CMP.TRNSP_DET SET DEL_NBR=".$_SESSION['personNBR']." WHERE TRNSP_NBR=".$TrnspNbr." AND TRNSP_DET_NBR=".$DelDet;
        //echo $query;
		$result=mysql_query($query);
	}
?>
<table style="background:#ffffff;">
	<tr>
        <th class="listable">Jum</th>
        <th class="listable">Deskripsi</th>
        <th class="listable">TID</th>
        <th class="listable">PID</th>		
        <th class="listable">Keterangan</th>
		<th class="listable"><div class='listable-btn'><span class='fa fa-plus listable-btn' onclick="if(document.getElementById('TRNSP_NBR').value==-1){parent.parent.document.getElementById('invoiceAdd').style.display='block';parent.parent.document.getElementById('fade').style.display='block';return;}slideFormIn('transport-edit-list-detail.php?TRNSP_DET_NBR=0&TRNSP_NBR=<?php echo $TrnspNbr; ?>');"></span></div></th>
	</tr>
	<?php
		$query="SELECT TRNSP_DET_NBR,TRNSP_NBR,TRNSP_Q,TDT.ORD_DET_NBR,ODT.DET_TTL AS ORD_TTL,TDT.DET_TTL AS TRSNP_TTL,PRN_DIG_DESC,ORD_Q,PRN_LEN,PRN_WID
            FROM CMP.TRNSP_DET TDT LEFT OUTER JOIN
            CMP.PRN_DIG_ORD_DET ODT ON TDT.ORD_DET_NBR=ODT.ORD_DET_NBR LEFT OUTER JOIN
            CMP.PRN_DIG_TYP TYP ON ODT.PRN_DIG_TYP=TYP.PRN_DIG_TYP LEFT OUTER JOIN
            CMP.PRN_DIG_EQP EQP ON TYP.PRN_DIG_EQP=EQP.PRN_DIG_EQP
            WHERE TRNSP_NBR=".$TrnspNbr." AND TDT.DEL_NBR=0
            ORDER BY TDT.TRNSP_DET_NBR ASC";
		//echo $query;
		$result=mysql_query($query);
		$alt="";
		while($rowd=mysql_fetch_array($result))
		{
			echo "<tr $alt onclick=".chr(34)."slideFormIn('transport-edit-list-detail.php?TRNSP_DET_NBR=".$rowd['TRNSP_DET_NBR']."&TRNSP_NBR=".$TrnspNbr."')".chr(34).">";
			echo "<td style='cursor:pointer;text-align:right;'>".$rowd['TRNSP_Q']."</td>";
            if(($rowc['PRN_LEN']!="")&&($rowc['PRN_WID']!="")){$prnDim=" ".$rowc['PRN_LEN']."x".$rowc['PRN_WID'];}else{$prnDim="";}
			echo "<td style='cursor:pointer'>".$rowd['ORD_TTL']." ".$rowd['PRN_DIG_DESC'].$prnDim."</td>";
			echo "<td style='cursor:pointer;text-align:center;'>".$rowd['TRNSP_DET_NBR']."</td>";
			echo "<td style='cursor:pointer;text-align:center;'>".$rowd['ORD_DET_NBR']."</td>";
			echo "<td style='cursor:pointer;'>".$rowd['TRSNP_TTL']."</td>";
			echo "<td style='cursor:pointer;text-align:center;'><div class='listable-btn'><span class='fa fa-trash listable-btn'  onclick=".chr(34)."event.cancelBubble=true;getContent('edit-list','transport-edit-list.php?TRNSP_NBR=".$TrnspNbr."&DEL_D=".$rowd['TRNSP_DET_NBR']."');".chr(34)."></span></div>";
			echo "</td></tr>";
			if($alt==""){$alt="class='alt'";}else{$alt="";}
		}
	?>
</table>
