<?php
	include "framework/database/connect.php";

	$searchQuery = trim(strtoupper(urldecode($_REQUEST[s])));
	$InvNbr=$_GET['INV_NBR'];
	$IvcTyp=$_GET['IVC_TYP'];
	
	if($InvNbr!=""){
		$whereClause="INV_NBR=".$InvNbr;
	}else{
		$searchQ=explode(" ",$searchQuery);
		$whereClause="";
		if($IvcTyp=='INV_TBL'){$whereClause=" INV_NBR<>'".$_GET['INVNBR']."' AND ";}
		foreach($searchQ as $searchQuery)
		{
			$whereClause.="((INV_NBR LIKE '%".$searchQuery."%' OR INV.NAME LIKE '%".$searchQuery."%' OR CAT_DESC LIKE '%".$searchQuery."%' OR CAT_SUB_DESC LIKE '%".$searchQuery."%' OR COM.NAME LIKE '%".$searchQuery."%' OR INV_BCD LIKE '%".$searchQuery."%' OR CAT_DISC_DESC LIKE '%".$searchQuery."%' OR CAT_SHLF_DESC LIKE '%".$searchQuery."%' OR CAT_PRC_DESC LIKE '%".$searchQuery."%')) AND ";
		}
		$whereClause=substr($whereClause,0,strlen($whereClause)-4);
	}

	//Search for inventory number
	$query="SELECT INV_NBR,INV.NAME NAME,CAT_DESC,COM.NAME AS CO_NAME,CAT_SUB_DESC,INV_BCD,INV_PRC,CAT_DISC_DESC,CAT_SHLF_DESC,CAT_PRC_DESC,PRC
						FROM RTL.INVENTORY INV LEFT OUTER JOIN
				 		RTL.CAT CAT ON INV.CAT_NBR=CAT.CAT_NBR LEFT OUTER JOIN
						RTL.CAT_SUB SUB ON INV.CAT_SUB_NBR=SUB.CAT_SUB_NBR LEFT OUTER JOIN
						RTL.CAT_DISC DSC ON INV.CAT_DISC_NBR=DSC.CAT_DISC_NBR LEFT OUTER JOIN
						RTL.CAT_SHLF SLF ON INV.CAT_DISC_NBR=SLF.CAT_SHLF_NBR LEFT OUTER JOIN
						RTL.CAT_PRC PRC ON INV.CAT_PRC_NBR=PRC.CAT_PRC_NBR LEFT OUTER JOIN
						CMP.COMPANY COM ON INV.CO_NBR=COM.CO_NBR
			WHERE $whereClause AND INV.DEL_NBR=0
			ORDER BY INV.UPD_TS DESC";
	//echo $query;
	$result=mysql_query($query);
	if(mysql_num_rows($result)>0)
	{
		echo "<table style='padding:0px;margin:0px'>";

		while($row=mysql_fetch_array($result))
		{
			if($IvcTyp=='SL'){
				if($row['PRC']==''){$InvPrc='0';}else{$InvPrc=$row['PRC'];}
			}else{
				if($row['INV_PRC']==''){$InvPrc='0';}else{$InvPrc=$row['INV_PRC'];}
			}
			
			if($IvcTyp=='INV_TBL'){
			$OnClick="<tr $alt style='cursor:pointer;' onclick=".chr(34)."document.getElementById('livesearch').value='".$row['INV_BCD']."';document.getElementById('INV_BCD').value='".$row['INV_BCD']."';document.getElementById('INV_NBR').value=".$row['INV_NBR'].";getName(".$row['INV_NBR'].");document.getElementById('INV_PRC').value=".$row['INV_PRC'].";document.getElementById('INV_PRC').value=".$row['INV_PRC'].";calcPay();".chr(34).">";
			}else if($IvcTyp=='RSC1'){
			$OnClick="<tr $alt style='cursor:pointer;' onclick=".chr(34)."document.getElementById('livesearch').value='".$row['INV_BCD']."';document.getElementById('INV_NBR1').value='".$row['INV_NBR']."';document.getElementById('INV_NM1').value='".$row['NAME']."';document.getElementById('INV_NM1').value='".$row['NAME']."';".chr(34).">";
			}else if($IvcTyp=='RSC2'){
			$OnClick="<tr $alt style='cursor:pointer;' onclick=".chr(34)."document.getElementById('livesearch').value='".$row['INV_BCD']."';document.getElementById('INV_NBR2').value='".$row['INV_NBR']."';document.getElementById('INV_NM2').value='".$row['NAME']."';".chr(34).">";
			}else{
			$OnClick="<tr $alt style='cursor:pointer;' onclick=".chr(34)."document.getElementById('livesearch').value='".$row['INV_BCD']."';document.getElementById('INV_NBR').value=".$row['INV_NBR'].";document.getElementById('INV_PRC').value=".$InvPrc.";document.getElementById('INV_PRC').value=".$InvPrc.";
			calcPay();".chr(34).">";
			}
			echo $OnClick;
			echo "<td>";
			echo $row['NAME'];
			echo " <span style='color:#999999'>".$row['INV_NBR']."</span><br/>";
			echo $row['CAT_DESC']." ".$row['CAT_SUB_DESC']." <span style='color:#999999'>".$row['INV_BCD']."</span> ".$row['CO_NAME']."</div>";
			echo "</td>";
			if($IvcTyp=='SL'){
				echo "<td style='vertical-align:top;text-align:right'><b>".number_format($row['PRC'],0,",",".")."</b></td>";
			}else{
				echo "<td style='vertical-align:top;text-align:right'><b>".number_format($row['INV_PRC'],0,",",".")."</b></td>";
			}
			echo "</tr>";
			if($alt==""){$alt="class='alt'";}else{$alt="";}
		}
		echo "</table>";
	}else{
		echo "Nama tidak ada didalam kumpulan data.";
	}
?>
