<?php
	include "framework/database/connect-cloud.php";
	$ProdNbr=$_GET['PROD_NBR'];
	
	if($_GET['DEL_D']!="")
	{
		$query = "UPDATE $CMP.PROD_LST_DET 
					SET DEL_NBR='".$_SESSION['personNBR']."', UPD_TS=CURRENT_TIMESTAMP
					WHERE PROD_DET_NBR='".$_GET['DEL_D']."'";
		$result= mysql_query($query,$cloud);
		$query = str_replace($CMP,"CMP",$query);
		$result= mysql_query($query,$local);
	}



?>
<table style="background:#ffffff;">
	<tr>
			<?php
			echo '
			<th class="listable">Deskripsi</th>
			<th class="listable">Harga</th>
			<th class="listable">Lain2</th>
			<th class="listable">Subtot</th>
			';
		 ?>		
		<th class="listable">
			<div class='listable-btn'>
				<span class='fa fa-plus listable-btn' style='text-align: left;' onclick="if(document.getElementById('PROD_NBR').value==-1){parent.parent.document.getElementById('discountAdd').style.display='block';parent.parent.document.getElementById('fade').style.display='block';return;};pushFormIn('product-list-detail.php?PROD_NBR=<?php echo $ProdNbr; ?>&amp;PROD_DET_NBR=-1');"></span>
			</div>
		</th>
	</tr>
	<?php
		$query="SELECT 
			PDET.PROD_DET_NBR, 
			PDET.PROD_NBR, 
			PDET.PROD_DET_DESC, 
			PDET.PROD_DET_X, 
			PDET.PROD_DET_Y, 
			PDET.PROD_DET_PRC, 
			PDET.FEE_MISC,
			PDET.TOT_SUB,
			PHEAD.PROD_NBR,
			PRN_DIG_DESC
		FROM CMP.PROD_LST_DET PDET 
			LEFT JOIN CMP.PROD_LST PHEAD ON PDET.PROD_NBR=PHEAD.PROD_NBR
			LEFT OUTER JOIN CMP.PRN_DIG_TYP TYP ON PDET.PRN_DIG_TYP=TYP.PRN_DIG_TYP 
			LEFT OUTER JOIN CMP.PRN_DIG_EQP EQP ON TYP.PRN_DIG_EQP=EQP.PRN_DIG_EQP
		WHERE PDET.DEL_NBR=0 AND PDET.PROD_NBR='".$ProdNbr."'";
		// echo $query;
		$result = mysql_query($query, $local);
		$alt    = "";
		$TotNet	= 0;
		if (mysql_num_rows($result)>0){
		while($rowd=mysql_fetch_array($result))
		{
			$link   = "pushFormIn('product-list-detail.php?PROD_NBR=".$ProdNbr."&amp;PROD_DET_NBR=".$rowd['PROD_DET_NBR']."');";
			$ukuran = "";
			if ($rowd['PROD_DET_X']!=''){
				$ukuran = $rowd['PROD_DET_X']."x".$rowd['PROD_DET_Y'];	
			}
			
			echo "<tr $alt >";
			echo "<td style='text-align:left;cursor:pointer;' onclick=".chr(34).$link.chr(34).">".$rowd['PROD_DET_DESC']." ".$rowd['PRN_DIG_DESC']." ".$ukuran."</td>";
			echo "<td style='text-align:right;cursor:pointer;' onclick=".chr(34).$link.chr(34).">".number_format($rowd['PROD_DET_PRC'],0,',','.')."</td>";
			echo "<td style='text-align:right;cursor:pointer;' onclick=".chr(34).$link.chr(34).">".number_format($rowd['FEE_MISC'],0,',','.')."</td>";
			echo "<td style='text-align:right;cursor:pointer;' onclick=".chr(34).$link.chr(34).">".number_format($rowd['TOT_SUB'],0,',','.')."</td>";
			
			echo "<td style='text-align:center;padding-left:2px;padding-right:2px;'>";
			echo "<div class='listable-btn'>";
			echo "<span class='fa fa-pencil listable-btn' style='cursor:pointer;' onclick=".chr(34).$link.chr(34)."></span>";
			echo "</div>";
			echo "<div class='listable-btn'><span class='fa fa-trash listable-btn' onclick=".chr(34)."getContent('edit-list','product-list.php?PROD_NBR=".$ProdNbr."&amp;DEL_D=".$rowd['PROD_DET_NBR']."');doStuff();".chr(34)."></span></div>";
			echo "</td>";
			echo "</tr>";
			if($alt==""){$alt="class='alt'";}else{$alt="";}
			$TotNet+=$rowd['TOT_SUB'];
		}
		}
	?>
	<input type="hidden" id="TOT_NET" value="<?php echo $TotNet; ?>" />
</table>
