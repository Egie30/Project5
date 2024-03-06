<?php
	include "framework/database/connect.php";
	
	$OrdNbr	= $_GET['CAP_NBR'];
	$type	= $_GET['TYP'];
	$DelDet	= $_GET['DEL_D'];
	$Show	= $_GET['SHOW'];
	$TotNet	= 0;
	
	if($DelDet!=""){
		$query="UPDATE CMP.PRN_DIG_CAP_DET SET DEL_NBR = ".$_SESSION['personNBR'].",UPD_TS=CURRENT_TIMESTAMP WHERE CAP_NBR=".$OrdNbr." AND CAP_DET_NBR=".$DelDet;
		$result=mysql_query($query);
	}
?>
<table style="background:#ffffff;">
	<tr>
		<th class="listable">No Nota</th>
		<th class="listable">Judul Nota</th>
		<th class="listable">Total</th>
		<th class="listable">Komisi</th>
		<th class="listable">Subtotal</th>
		<th class="listable">
			<div class='listable-btn'><span class='fa fa-plus listable-btn' onclick="if(document.getElementById('CAP_NBR').value==-1){parent.parent.document.getElementById('invoiceAdd').style.display='block';parent.parent.document.getElementById('fade').style.display='block';return;}slideFormIn('print-digital-cap-edit-list-detail.php?CAP_DET_NBR=0&CAP_NBR=<?php echo $OrdNbr; ?>');"></span>
		</div>
		</th>
	</tr>
	<?php
		$query="SELECT
			CAP_DET_NBR,
			ORD_NBR,
			ORD_TTL,
			CAP_NBR,
			AMT,
			CAP_PCT,
			TOT_SUB
		FROM CMP.PRN_DIG_CAP_DET DET 
		WHERE DEL_NBR=0 AND CAP_NBR=".$OrdNbr."
		ORDER BY CAP_DET_NBR ASC";
		$result=mysql_query($query);
		$alt="";
		while($rowd=mysql_fetch_array($result)){
			echo "<tr $alt onclick=".chr(34)."slideFormIn('print-digital-cap-edit-list-detail.php?CAP_DET_NBR=".$rowd['CAP_DET_NBR']."&CAP_NBR=".$OrdNbr."&TYP=".$type."')".chr(34).">";
			echo "<td style='cursor:pointer;'>".$rowd['ORD_NBR']."</td>";
			echo "<td style='cursor:pointer;'>".$rowd['ORD_TTL']."</td>";
			echo "<td style='cursor:pointer;text-align:right;'>".number_format($rowd['AMT'],0,",",".")."</td>";
			echo "<td style='cursor:pointer;text-align:center;'>".number_format($rowd['CAP_PCT'],0,",",".")." %</td>";
			echo "<td style='cursor:pointer;text-align:right;'>".number_format($rowd['TOT_SUB'],0,",",".")."</td>";		
			echo "<td style='cursor:pointer;text-align:center;'><div class='listable-btn'><span class='fa fa-trash listable-btn'  onclick=".chr(34)."event.cancelBubble=true;getContent('edit-list','print-digital-cap-edit-list.php?CAP_NBR=".$OrdNbr."&DEL_D=".$rowd['CAP_DET_NBR']."');".chr(34)."></span></div>";
			echo "</td>";
			echo "</tr>";
			if($alt==""){$alt="class='alt'";}else{$alt="";}
			$TotNet+=$rowd['TOT_SUB'];
		}
	?>
</table>
<input type="hidden" id="TOT_NET" value="<?php echo $TotNet; ?>" />