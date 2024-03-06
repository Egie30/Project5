<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	
	$Conv=mysql_real_escape_string($_GET['CONV']);
	$altOrig=$_GET['ALT'];
	$alt=$altOrig;
	$CmptNbr=$_GET['CMPT_NBR'];
	$PrsnNbr=$_SESSION['personNBR'];
	$Del=$_GET['DEL'];
	if($Del!=''){
		$query="UPDATE CMP.CONV_THRD SET DEL_NBR=$PrsnNbr WHERE CONV_NBR=$Del";
		$result=mysql_query($query);
	}
	if($Conv!=''){
		//Add conversation
		$query="INSERT INTO CMP.CONV_THRD (CMPT,CMPT_NBR,CONV,DEL_NBR,UPD_NBR) VALUES ('DIG_PRN',$CmptNbr,'$Conv',0,$PrsnNbr)";
		$result=mysql_query($query);
		//Add indicator
		$query="SELECT SPL_NTE FROM CMP.PRN_DIG_ORD_HEAD WHERE ORD_NBR=$CmptNbr";
		echo $query;
		$result=mysql_query($query);
		$row=(mysql_fetch_array($result));
	}
	if ($_GET['CONV_NBR'] != ''){
	$query="SELECT CONV_NBR,CONV,PPL.NAME,CMPT_NBR,CNV.UPD_TS,CNV.UPD_NBR FROM CMP.CONV_THRD CNV INNER JOIN CMP.PEOPLE PPL ON CNV.UPD_NBR=PPL.PRSN_NBR WHERE CMPT='DIG_PRN' AND CNV.DEL_NBR=0 AND CMPT_NBR=$CmptNbr ORDER BY UPD_TS DESC";
	//echo $query;
	$result=mysql_query($query);
	while($row=mysql_fetch_array($result)){
		echo "<div class='conv-item".$alt."'>";
		echo $row['CONV']." &nbsp;<img src='img/conversation-user.png' style='border:none'> ".shortName($row['NAME'])." &nbsp;<img src='img/conversation-clock.png' style='border:none'> ";
		$time=strtotime($row['UPD_TS']);
		echo humanTiming($time)." yang lalu";
		$elapsed=time()-$time;
		//Need a universal variable for the whole system
		if(($elapsed<=3600)&&($PrsnNbr==$row['UPD_NBR'])){
			echo "&nbsp;<img src='img/conversation-trash.png' style='border:none;cursor:pointer' onclick=".chr(34)."getContent('conversation','calendar-edit-conversation.php?&CMPT_NBR=".$row['CMPT_NBR']."&DEL=".$row['CONV_NBR']."&ALT=$altOrig');".chr(34).">";
		}
		echo "</div>";
		if($alt=="-alt"){$alt="";}else{$alt="-alt";}
	}
	}
?>
