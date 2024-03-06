<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	
	$Conv	= mysql_real_escape_string($_GET['CONV']);
	$altOrig= $_GET['ALT'];
	$alt	= $altOrig;
	$CmptNbr= $_GET['CMPT_NBR'];
	$PrsnNbr= $_SESSION['personNBR'];
	$Del	= $_GET['DEL'];
	if($Del!=''){
		$query="UPDATE RTL.CONV_THRD SET DEL_NBR=$PrsnNbr WHERE CONV_NBR=$Del";
		$result=mysql_query($query);
		//Remove indicator if no active thread exist
		$query="SELECT COUNT(*) AS NBR_CONV FROM RTL.CONV_THRD WHERE CMPT_NBR=$CmptNbr AND DEL_NBR=0";
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$NbrConv=$row['NBR_CONV'];
		$query="SELECT SPC_NTE FROM RTL.TRNSP_HEAD WHERE ORD_NBR=$CmptNbr";
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$SpcNte=$row['SPC_NTE'];
		if(($SpcNte=='CONV_THRD')&&($NbrConv==0)){
			$query="UPDATE RTL.TRNSP_HEAD SET SPC_NTE='' WHERE ORD_NBR=$CmptNbr";
			$result=mysql_query($query);
		}
	}
	if($Conv!=''){
		//Add conversation
		$query="INSERT INTO RTL.CONV_THRD (CMPT,CMPT_NBR,CONV,DEL_NBR,UPD_NBR) VALUES ('TRNSP',$CmptNbr,'$Conv',0,$PrsnNbr)";
        //echo $query;
		$result=mysql_query($query);
		//Add indicator
		$query="SELECT SPC_NTE FROM RTL.TRNSP_HEAD WHERE ORD_NBR=$CmptNbr";
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$SpcNte=$row['SPC_NTE'];
		if($SpcNte==''){
			$query="UPDATE RTL.TRNSP_HEAD SET SPC_NTE='CONV_THRD' WHERE ORD_NBR=$CmptNbr";
			$result=mysql_query($query);
		}
	}
	
	$query="SELECT 
		CONV_NBR,CONV,PPL.NAME,CMPT_NBR,CNV.UPD_TS,CNV.UPD_NBR 
	FROM RTL.CONV_THRD CNV 
		INNER JOIN CMP.PEOPLE PPL ON CNV.UPD_NBR=PPL.PRSN_NBR 
	WHERE CMPT='TRNSP' AND CNV.DEL_NBR=0 AND CMPT_NBR=$CmptNbr 
	ORDER BY UPD_TS DESC";
	//echo $query;
	$result=mysql_query($query);
	while($row=mysql_fetch_array($result)){
		echo "<div class='conv-item".$alt."'>";
		echo $row['CONV']." &nbsp;<span class='fa fa-user'></span> ".shortName($row['NAME'])." &nbsp;<span class='fa fa-clock-o'></span> ";
		$time=strtotime($row['UPD_TS']);
		echo humanTiming($time)." yang lalu";
		$elapsed=time()-$time;
		echo "</div>";
		if($alt=="-alt"){$alt="";}else{$alt="-alt";}
	}
?>
