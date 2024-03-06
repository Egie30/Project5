<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	$OrdNbr=$_GET['ORD_NBR'];
	$DelDet=$_GET['DEL_D'];
	$TotNet=0;
	$IvcTyp=$_GET['IVC_TYP'];
	$Security=getSecurity($_SESSION['userID'],"AddressBook");

	if($DelDet!=""){
		$query="DELETE FROM RTL.RTL_STK_DET WHERE ORD_NBR=".$OrdNbr." AND ORD_DET_NBR=".$DelDet;
		$result=mysql_query($query);
		
		if ($IvcTyp=="RC"){
		$query="UPDATE RTL.INV_MOV SET DEL_NBR = ".$_SESSION['personNBR']." WHERE INV_NBR = ".$_GET['INV_NBR']." AND ORD_DET_NBR=".$DelDet;
		$result=mysql_query($query);
		}
	}
?>
<table style="background:#ffffff;">
	<tr>
		<?php if ($IvcTyp=="SL"){
			echo '
			<th class="listable">Jum</th>
			<th class="listable">Barcode</th>
			<th class="listable">Nama</th>
			<th class="listable">PID</th>
			<th class="listable">SN</th>
			<th class="listable">Harga</th>		
			<th class="listable">Disc</th>
			<th class="listable">Sub Total</th>	';
		} else if ($IvcTyp == "TS") {
			echo '
			<th class="listable">Jum</th>
			<th class="listable"style="padding:2px"><input name="SEL_IMG" id="SEL_IMG" type="checkbox" class="regular-checkbox" onclick="toggleCheckBox(this)"/>
			<label for="SEL_IMG" style="margin-top:5px;margin-right:0px"></label></th>
			<th class="listable">Barcode</th>
			<th class="listable">Nama</th>
			<th class="listable">PID</th>
			<th class="listable">SN</th>
			<th class="listable">Note</th>
			<th class="listable">Faktur</th>
			<th class="listable">Jual</th>		
			<th class="listable">Disc</th>
			<th class="listable" nowrap>Sub F</th>
			<th class="listable" nowrap>Sub J</th>	';
		}else{
			echo '
			<th class="listable">Jum</th>
			<th class="listable"style="padding:2px"><input name="SEL_IMG" id="SEL_IMG" type="checkbox" class="regular-checkbox" onclick="toggleCheckBox(this)"/>
			<label for="SEL_IMG" style="margin-top:5px;margin-right:0px"></label></th>
			<th class="listable">Barcode</th>
			<th class="listable">Nama</th>
			<th class="listable">PID</th>
			<th class="listable">SN</th>
			<th class="listable">Faktur</th>
			<th class="listable">Jual</th>		
			<th class="listable">Disc</th>
			<th class="listable" nowrap>Sub F</th>
			<th class="listable" nowrap>Sub J</th>	';
		} ?>		
		
		<th class="listable">
		<div class='listable-btn'><span class='fa fa-plus listable-btn' onclick="if(document.getElementById('ORD_NBR').value==-1){parent.parent.document.getElementById('invoiceAdd').style.display='block';parent.parent.document.getElementById('fade').style.display='block';return;};pushFormIn('retail-stock-edit-list-detail.php?ORD_DET_NBR=0&ORD_NBR=<?php echo $OrdNbr."&IVC_TYP=".$IvcTyp; ?>');"></span></div>
		</th>
	</tr>
	<?php
	
		$TotNetDet 	= 0;
		$query 		= "SELECT SUM(COALESCE(TOT_SUB,0)) AS TOT_SUB FROM RTL.RTL_STK_DET DET 
						WHERE ORD_NBR=".$OrdNbr." AND ORD_NBR!=0";
		$result 	= mysql_query($query);
		$row 		= mysql_fetch_array($result);
		$TotNetDet 	= $row['TOT_SUB'];	

		$query="SELECT ORD_DET_NBR,ORD_NBR,DET.INV_NBR,INV.INV_BCD,INV.PRC,INV.NAME,DET.INV_DESC,ORD_Q,ORD_X,ORD_Y,ORD_Z,DET.INV_PRC,FEE_MISC,DISC_PCT,DISC_AMT,TOT_SUB,CRT_TS,CRT_NBR,DET.UPD_TS,DET.UPD_NBR,DET.ORD_DET_NBR_REF,DET.SER_NBR, DET.NTE
				FROM RTL.RTL_STK_DET DET LEFT OUTER JOIN
					 RTL.INVENTORY INV ON DET.INV_NBR=INV.INV_NBR
				WHERE ORD_NBR=".$OrdNbr." AND ORD_NBR!=0
				ORDER BY DET.ORD_DET_NBR ASC";
		//echo $query;
		$result=mysql_query($query);
		$alt="";
		while($rowd=mysql_fetch_array($result))
		{
			echo "<tr $alt onclick=".chr(34)."pushFormIn('retail-stock-edit-list-detail.php?ORD_DET_NBR=".$rowd['ORD_DET_NBR']."&ORD_NBR=".$OrdNbr."&IVC_TYP=".$IvcTyp."')".chr(34).">";
			if ($IvcTyp=="SL"){
			echo "<td style='cursor:pointer;text-align:right;'>".$rowd['ORD_Q']."</td>";
			echo "<td style='cursor:pointer'>".$rowd['INV_BCD']."</td>";
			echo "<td style='cursor:pointer'>".$rowd['NAME'];
            if($rowd['ORD_X']!=''){echo " Uk/Isi ".$rowd['ORD_X'];}
            if($rowd['ORD_Y']!=''){echo "x".$rowd['ORD_Y'];}
            if($rowd['ORD_Z']!=''){echo "x".$rowd['ORD_Z'];}
            echo "</td>";
			echo "<td style='cursor:pointer'>".$rowd['ORD_DET_NBR_REF'];
			echo "<td style='cursor:pointer'>".$rowd['SER_NBR'];
			echo "<td style='cursor:pointer;text-align:right;'>".$rowd['INV_PRC']."</td>";
			echo "<td style='cursor:pointer;text-align:right;'>".number_format($rowd['DISC_PCT'],0,",",".")."/".number_format($rowd['DISC_AMT'],0,",",".")."</td>";
			echo "<td style='cursor:pointer;text-align:right;'>".number_format($rowd['TOT_SUB'],0,",",".")."</td>";
			}else if ($IvcTyp == "TS"){
			echo "<td style='cursor:pointer;text-align:right;'>".$rowd['ORD_Q']."</td>";
			echo "<td style='padding:2px;text-align:center'><input name='SEL_IMG_".$rowd['ORD_DET_NBR']."' id='SEL_IMG_".$rowd['ORD_DET_NBR']."' type='checkbox' class='regular-checkbox' onclick=".chr(34)."event.cancelBubble=true;".chr(34)."/><label for='SEL_IMG_".$rowd['ORD_DET_NBR']."' style='margin-right:0px;margin-top:5px' onclick=".chr(34)."event.cancelBubble=true;".chr(34)."></label></td>";
			echo "<td style='cursor:pointer'>".$rowd['INV_BCD']."</td>";
			echo "<td style='cursor:pointer'>".$rowd['NAME']." ".$rowd['INV_DESC'];
            if($rowd['ORD_X']!=''){echo " Uk/Isi ".$rowd['ORD_X'];}
            if($rowd['ORD_Y']!=''){echo "x".$rowd['ORD_Y'];}
            if($rowd['ORD_Z']!=''){echo "x".$rowd['ORD_Z'];}
			echo "<td style='cursor:pointer'>".$rowd['ORD_DET_NBR_REF'];
			echo "<td style='cursor:pointer'>".$rowd['SER_NBR'];
			echo "<td style='cursor:pointer'>".$rowd['NTE'];
			echo "<td style='cursor:pointer;text-align:right;'>".$rowd['INV_PRC']."</td>";
			echo "<td style='cursor:pointer;text-align:right;'>".$rowd['PRC']."</td>";
			echo "<td style='cursor:pointer;text-align:right;'>".number_format($rowd['DISC_PCT'],0,",",".")."/".number_format($rowd['DISC_AMT'],0,",",".")."</td>";
			echo "<td style='cursor:pointer;text-align:right;'>".number_format($rowd['TOT_SUB'],0,",",".")."</td>";
			$prc=$rowd['PRC']*$rowd['ORD_Q']*($rowd['ORD_X'] ?: 1)*($rowd['ORD_Y'] ?: 1)*($rowd['ORD_Z'] ?: 1);
			echo "<td style='cursor:pointer;text-align:right;'>".number_format($prc,0,",",".")."</td>";
			}else{
			echo "<td style='cursor:pointer;text-align:right;'>".$rowd['ORD_Q']."</td>";
			echo "<td style='padding:2px;text-align:center'><input name='SEL_IMG_".$rowd['ORD_DET_NBR']."' id='SEL_IMG_".$rowd['ORD_DET_NBR']."' type='checkbox' class='regular-checkbox' onclick=".chr(34)."event.cancelBubble=true;".chr(34)."/><label for='SEL_IMG_".$rowd['ORD_DET_NBR']."' style='margin-right:0px;margin-top:5px' onclick=".chr(34)."event.cancelBubble=true;".chr(34)."></label></td>";
			echo "<td style='cursor:pointer'>".$rowd['INV_BCD']."</td>";
			echo "<td style='cursor:pointer'>".$rowd['NAME']." ".$rowd['INV_DESC'];
            if($rowd['ORD_X']!=''){echo " Uk/Isi ".$rowd['ORD_X'];}
            if($rowd['ORD_Y']!=''){echo "x".$rowd['ORD_Y'];}
            if($rowd['ORD_Z']!=''){echo "x".$rowd['ORD_Z'];}
			echo "<td style='cursor:pointer'>".$rowd['ORD_DET_NBR_REF'];
			echo "<td style='cursor:pointer'>".$rowd['SER_NBR'];
			echo "<td style='cursor:pointer;text-align:right;'>".$rowd['INV_PRC']."</td>";
			echo "<td style='cursor:pointer;text-align:right;'>".$rowd['PRC']."</td>";
			echo "<td style='cursor:pointer;text-align:right;'>".number_format($rowd['DISC_PCT'],0,",",".")."/".number_format($rowd['DISC_AMT'],0,",",".")."</td>";
			echo "<td style='cursor:pointer;text-align:right;'>".number_format($rowd['TOT_SUB'],0,",",".")."</td>";
			$prc=$rowd['PRC']*$rowd['ORD_Q']*($rowd['ORD_X'] ?: 1)*($rowd['ORD_Y'] ?: 1)*($rowd['ORD_Z'] ?: 1);
			echo "<td style='cursor:pointer;text-align:right;'>".number_format($prc,0,",",".")."</td>";
			}
			echo "<td style='cursor:pointer;text-align:center;' style='padding-left:2px;padding-right:2px;'>";
			$TotNetPar=$TotNetDet-$rowd['TOT_SUB'];
			if($Security==0 && $IvcTyp=="RC"){
			echo "<div class='listable-btn'><span class='fa fa-trash listable-btn' onclick=".chr(34)."event.cancelBubble=true;getContent('edit-list','retail-stock-edit-list.php?IVC_TYP=".$IvcTyp."&ORD_NBR=".$OrdNbr."&DEL_D=".$rowd['ORD_DET_NBR']."&INV_NBR=".$rowd['INV_NBR']."');calcAmtChild($TotNetPar);".chr(34)."></span></div>";
			}
			echo "</td></tr>";
			if($alt==""){$alt="class='alt'";}else{$alt="";}
			$TotNet+=$rowd['TOT_SUB'];
		}
	?>
</table>
<input type="hidden" id="TOT_NET" value="<?php echo $TotNet; ?>" />
