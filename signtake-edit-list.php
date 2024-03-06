<?php
	include "framework/database/connect.php";
	$TrnspNbr 	= $_GET['TRNSP_NBR'];
	$DelDet 	= $_GET['DEL_D'];

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
		<th class="listable"><div class='listable-btn'><span class='fa fa-plus listable-btn' onclick="if(document.getElementById('TRNSP_NBR').value==-1){parent.parent.document.getElementById('invoiceAdd').style.display='block';parent.parent.document.getElementById('fade').style.display='block';return;}slideFormIn('signtake-edit-list-detail.php?TRNSP_DET_NBR=0&TRNSP_NBR=<?php echo $TrnspNbr; ?>');"></span></div></th>
	</tr>
	<?php
        $query 	= "SELECT 
        				TDET.TRNSP_DET_NBR,
        				TDET.TRNSP_Q,
        				CONCAT(COALESCE(INV.NAME,''),' ',DET.INV_DESC) AS INV_NAME,
        				TDET.ORD_DET_NBR,
					TDET.DET_TTL AS TRNSP_TTL
        			FROM CMP.TRNSP_DET TDET 
        			LEFT JOIN RTL.RTL_STK_DET DET ON TDET.ORD_DET_NBR=DET.ORD_DET_NBR 
        			LEFT JOIN RTL.INVENTORY INV ON DET.INV_NBR=INV.INV_NBR 
        			WHERE TDET.DEL_NBR=0 AND TDET.TRNSP_NBR=".$TrnspNbr." 
        			ORDER BY TDET.TRNSP_DET_NBR ASC";
		//echo $query;
		$result = mysql_query($query);
		$alt 	= "";
		while($rowd=mysql_fetch_array($result))
		{
			echo "<tr $alt onclick=".chr(34)."slideFormIn('signtake-edit-list-detail.php?TRNSP_DET_NBR=".$rowd['TRNSP_DET_NBR']."&TRNSP_NBR=".$TrnspNbr."')".chr(34).">";
			echo "<td style='cursor:pointer;text-align:right;'>".$rowd['TRNSP_Q']."</td>";
			echo "<td style='cursor:pointer'>".$rowd['INV_NAME']."</td>";
			echo "<td style='cursor:pointer;text-align:center;'>".$rowd['TRNSP_DET_NBR']."</td>";
			echo "<td style='cursor:pointer;text-align:center;'>".$rowd['ORD_DET_NBR']."</td>";
			echo "<td style='cursor:pointer;'>".$rowd['TRNSP_TTL']."</td>";
			echo "<td style='cursor:pointer;text-align:center;'><div class='listable-btn'><span class='fa fa-trash listable-btn'  onclick=".chr(34)."event.cancelBubble=true;getContent('edit-list','signtake-edit-list.php?TRNSP_NBR=".$TrnspNbr."&DEL_D=".$rowd['TRNSP_DET_NBR']."');".chr(34)."></span></div>";
			echo "</td></tr>";
			if($alt==""){$alt="class='alt'";}else{$alt="";}
		}
	?>
</table>
