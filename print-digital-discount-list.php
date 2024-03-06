<?php
	//include "framework/database/connect.php";
	include "framework/database/connect-cloud.php";
	$PlanTyp=$_GET['PLAN_TYP'];
	$DelDet=$_GET['DEL_D'];
	$TotNet=0;
	$IvcTyp=$_GET['IVC_TYP'];

	if($DelDet!=""){
		$query="DELETE FROM CMP.PRN_DIG_VOL_SCHED WHERE PLAN_TYP='".$PlanTyp."' AND SCHED_ID=".$DelDet;
		//echo $query;
		$result = mysql_query($query, $cloud);
		$query = str_replace($CMP, "CMP", $query);
		$result = mysql_query($query, $local);
	}
?>
<table style="background:#ffffff;">
	<tr>
			<?php
			echo '
			<th class="listable">Sched Id</th>
			<th class="listable">Discount (Rp)</th>
			<th class="listable">Minimal Quantity</th>
			<th class="listable">Maksimal Quantity</th>		
			';
		 ?>		
		<th class="listable">
			<div class='listable-btn'>
				<span class='fa fa-plus listable-btn' onclick="if(document.getElementById('PLAN_NBR').value==-1){parent.parent.document.getElementById('discountAdd').style.display='block';parent.parent.document.getElementById('fade').style.display='block';return;};pushFormIn('print-digital-discount-list-detail.php?SCHED_ID=0&PLAN_TYP=<?php echo $PlanTyp?>');"></span>
			</div>
		</th>
	</tr>
	<?php
		$query="SELECT TYP.PLAN_TYP, TYP.PLAN_DESC, SCH.SCHED_ID, SCH.DISC_AMT, SCH.MIN_Q, SCH.MAX_Q
				FROM CMP.PRN_DIG_VOL_SCHED SCH LEFT OUTER JOIN
					 CMP.PRN_DIG_VOL_PLAN_TYP TYP ON SCH.PLAN_TYP=TYP.PLAN_TYP
				WHERE SCH.PLAN_TYP='".$PlanTyp."'
				ORDER BY SCH.SCHED_ID ASC";
		//echo $query;
		$result=mysql_query($query, $local);
		$alt="";
		while($rowd=mysql_fetch_array($result))
		{
			echo "<tr $alt>";
			echo "<td style='text-align:right;'>".$rowd['SCHED_ID']."</td>";
			echo "<td style='text-align:right;'>".number_format($rowd['DISC_AMT'],0,',','.')."</td>";
			echo "<td style='text-align:right;'>".number_format($rowd['MIN_Q'],0,',','.')."</td>";
			echo "<td style='text-align:right;'>".number_format($rowd['MAX_Q'],0,',','.')."</td>";
			
			echo "<td style='text-align:center;' style='padding-left:2px;padding-right:2px;'>";
			echo "<div class='listable-btn'><span class='fa fa-pencil listable-btn' style='cursor:pointer;' onclick=".chr(34)."pushFormIn('print-digital-discount-list-detail.php?SCHED_ID=".$rowd['SCHED_ID']."&PLAN_TYP=".$PlanTyp."');".chr(34)."></span></div>";
			echo "<div class='listable-btn'><span class='fa fa-trash listable-btn' onclick=".chr(34)."getContent('edit-list','print-digital-discount-list.php?PLAN_TYP=".$PlanTyp."&DEL_D=".$rowd['SCHED_ID']."');".chr(34)."></span></div>";
			echo "</td></tr>";
			if($alt==""){$alt="class='alt'";}else{$alt="";}
		}
	?>
</table>
