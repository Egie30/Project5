<?php
require_once "framework/database/connect.php";
$Key	= $_GET['q'];
$query	= "SELECT ORD_DET_NBR, ORD_NBR, DET_TTL, TOT_SUB FROM CMP.PRN_DIG_ORD_DET 
			WHERE ORD_DET_NBR = '$Key' AND DEL_NBR=0";
$result	= mysql_query($query);
?>
<div style="margin-top:5px;" class="edit-list-ls" id="liveRequestResults2">
<?php 
	if(mysql_num_rows($result)>0)
	{
		echo "<table style='padding:0px;margin:0px'>";

		while($row=mysql_fetch_array($result))
		{
			$OnClick="<tr $alt style='cursor:pointer;' onclick=".chr(34)."document.getElementById('NBR_REF').value='".$row['ORD_DET_NBR']."';".chr(34).">";
			echo $OnClick;
			echo "<td>";
			echo $row['ORD_DET_NBR'];
			echo "<br/>";
			echo $row['ORD_NBR']." <span style='color:#999999'>".$row['DET_TTL']."</span>";
			echo "</td>";
			echo "<td style='vertical-align:top;text-align:right'><b>".number_format($row['TOT_SUB'],0,",",".")."</b></td>";
			echo "</tr>";
			if($alt==""){$alt="class='alt'";}else{$alt="";}
		}
		echo "</table>";
	}else{
		echo "Nama tidak ada didalam kumpulan data.";
	} 
?>
</div>
