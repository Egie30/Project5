<?php
	include "framework/database/connect.php";
	
	$OrdNbr	= $_GET['ORD_NBR'];
	$type	= $_GET['TYP'];
	$DelDet	= $_GET['DEL_D'];
	$Show	= $_GET['SHOW'];
	$TotNet	= 0;
	
	if($type == "EST"){
		$headtable 	= "RTL.RTL_ORD_HEAD_EST";
		$detailtable= "RTL.RTL_ORD_DET_EST";
	}else{
		$headtable 	= "RTL.RTL_ORD_HEAD";
		$detailtable= "RTL.RTL_ORD_DET";
	}
	
	if ($Show=="NO"){
		$WhereDel="DET.DEL_NBR<>0";
		$OpenS = "<strike>";
		$CloseS= "</strike>";
	} else {
		$WhereDel="DET.DEL_NBR=0";
		$OpenS = "";
		$CloseS= "";
	}
	
	if($DelDet!=""){
		$query="UPDATE ". $detailtable ." SET DEL_NBR = ".$_SESSION['personNBR'].",UPD_TS=CURRENT_TIMESTAMP WHERE ORD_NBR=".$OrdNbr." AND ORD_DET_NBR=".$DelDet;
		$result=mysql_query($query);
	}
?>
<table style="background:#ffffff;">
	<tr>
		<th class="listable">Jum</th>
		<th class="listable"style='padding:2px'>
			<input name='SEL_IMG' id='SEL_IMG' type='checkbox' class='regular-checkbox' onclick="toggleCheckBox(this)"/>
			<label for='SEL_IMG' style='margin-top:5px;margin-right:0px'></label>
		</th>
		<th class="listable">Barcode</th>
		<th class="listable">Nama</th>
		<th class="listable">Harga</th>
		<th class="listable">Lain2</th>			
		<th class="listable">Disc</th>
		<th class="listable">Subtotal</th>
		<?php if($Show != "NO"){ ?>
		<th class="listable">
		<div class='listable-btn'><span class='fa fa-plus listable-btn' onclick="if(document.getElementById('ORD_NBR').value==-1){parent.parent.document.getElementById('invoiceAdd').style.display='block';parent.parent.document.getElementById('fade').style.display='block';return;}slideFormIn('retail-order-edit-list-detail.php?ORD_DET_NBR=0&TYP=<?php echo $type; ?>&ORD_NBR=<?php echo $OrdNbr; ?>');"></span>
		</div></th>
		<?php } ?>
	</tr>
	<?php
		$TotNetDet 	= 0;
		$query 		= "SELECT SUM(COALESCE(TOT_SUB,0)) AS TOT_SUB FROM ". $detailtable ." DET 
						WHERE ORD_NBR=".$OrdNbr." AND ORD_NBR!=0";
		$result 	= mysql_query($query);
		$row 		= mysql_fetch_array($result);
		$TotNetDet 	= $row['TOT_SUB'];

		$query="SELECT 
			ORD_DET_NBR,
			ORD_NBR,
			DET.INV_NBR,
			INV.INV_BCD,
			DET.PRC,
			INV.NAME,
			INV.SIZE,
			CLR.COLR_DESC,
			INV.THIC,
			INV.WEIGHT,
			ORD_Q,
			DET.INV_PRC,
			FEE_MISC,
			DISC_PCT,
			DISC_AMT,
			TOT_SUB,
			CRT_TS,
			CRT_NBR,
			DET.UPD_TS,
			DET.UPD_NBR
		FROM ". $detailtable ." DET 
			LEFT OUTER JOIN RTL.INVENTORY INV ON DET.INV_NBR=INV.INV_NBR
			LEFT OUTER JOIN CMP.INV_COLR CLR ON INV.COLR_NBR=CLR.COLR_NBR
		WHERE ".$WhereDel." AND ORD_NBR=".$OrdNbr."
		ORDER BY DET.ORD_DET_NBR ASC";
		//echo $query;
		$result=mysql_query($query);
		$alt="";
		while($rowd=mysql_fetch_array($result))
		{
			echo "<tr $alt onclick=".chr(34)."slideFormIn('retail-order-edit-list-detail.php?ORD_DET_NBR=".$rowd['ORD_DET_NBR']."&ORD_NBR=".$OrdNbr."&TYP=".$type."')".chr(34).">";
			echo "<td style='cursor:pointer;text-align:right;'>".$rowd['ORD_Q']."</td>";
			echo "<td style='padding:2px;text-align:center'><input name='SEL_IMG_".$rowd['ORD_DET_NBR']."' id='SEL_IMG_".$rowd['ORD_DET_NBR']."' type='checkbox' class='regular-checkbox' onclick=".chr(34)."event.cancelBubble=true;".chr(34)."/><label for='SEL_IMG_".$rowd['ORD_DET_NBR']."' style='margin-right:0px;margin-top:5px' onclick=".chr(34)."event.cancelBubble=true;".chr(34)."></label></td>";
			echo "<td style='cursor:pointer;'>".$rowd['INV_BCD']."</td>";
			echo "<td style='cursor:pointer;'>".$OpenS.$rowd['NAME']." ".$rowd['SIZE']." ".$rowd['COLR_DESC']." ".$rowd['THIC']." ".$rowd['WEIGHT']."</td>";
			//echo "<td style='cursor:pointer;text-align:right;'>".$rowd['INV_PRC']."</td>";
			echo "<td style='cursor:pointer;text-align:right;'>".$rowd['PRC']."</td>";
			echo "<td style='cursor:pointer;text-align:right;'>".$OpenS.number_format($rowd['FEE_MISC'],0,",",".")."</td>";
			echo "<td style='cursor:pointer;text-align:right;'>".$OpenS.number_format($rowd['DISC_PCT'],0,",",".")."/".number_format($rowd['DISC_AMT'],0,",",".")."</td>";
			echo "<td style='cursor:pointer;text-align:right;'>".$OpenS.number_format($rowd['TOT_SUB'],0,",",".")."</td>";
			$prc=$rowd['PRC']*$rowd['ORD_Q'];
			//echo "<td style='cursor:pointer;text-align:right;'>".number_format($prc,0,",",".")."</td>";
			if($Show != "NO"){
			$TotNetPar=$TotNetDet-$rowd['TOT_SUB'];				
			echo "<td style='cursor:pointer;text-align:center;'><div class='listable-btn'><span class='fa fa-trash listable-btn'  onclick=".chr(34)."event.cancelBubble=true;getContent('edit-list','retail-order-edit-list.php?ORD_NBR=".$OrdNbr."&DEL_D=".$rowd['ORD_DET_NBR']."&TYP=".$type."');calcAmtChild($TotNetPar);".chr(34)."></span></div>";
			echo "</td>";
			}
			echo "</tr>";
			if($alt==""){$alt="class='alt'";}else{$alt="";}
			$TotNet+=$rowd['TOT_SUB'];
		}
	?>
</table>
<input type="hidden" id="TOT_NET" value="<?php echo $TotNet; ?>" />