<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/slack/slack.php";
	date_default_timezone_set("Asia/Jakarta");
	
	$Conv=mysql_real_escape_string($_GET['CONV']);
	$ConvSlack=$_GET['CONV'];
	$altOrig=$_GET['ALT'];
	$alt=$altOrig;
	$CmptNbr=$_GET['CMPT_NBR'];
	$PrsnNbr=$_SESSION['personNBR'];
	$Del=$_GET['DEL'];
	if($Del!=''){
		$query="UPDATE CMP.CONV_THRD SET DEL_NBR=$PrsnNbr WHERE CONV_NBR=$Del";
		$result=mysql_query($query);
		//Remove indicator if no active thread exist
		$query="SELECT COUNT(*) AS NBR_CONV FROM CMP.CONV_THRD WHERE CMPT_NBR=$CmptNbr AND DEL_NBR=0";
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$NbrConv=$row['NBR_CONV'];
		$query="SELECT SPC_NTE FROM CMP.PRN_DIG_ORD_HEAD WHERE ORD_NBR=$CmptNbr";
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$SpcNte=$row['SPC_NTE'];
		if(($SpcNte=='CONV_THRD')&&($NbrConv==0)){
			$query="UPDATE CMP.PRN_DIG_ORD_HEAD SET SPC_NTE='' WHERE ORD_NBR=$CmptNbr";
			$result=mysql_query($query);
		}
	}
	if($Conv!=''){
		//Add conversation
		$query="INSERT INTO CMP.CONV_THRD (CMPT,CMPT_NBR,CONV,DEL_NBR,UPD_NBR) VALUES ('DIG_PRN',$CmptNbr,'$Conv',0,$PrsnNbr)";//echo $query;
		$result=mysql_query($query);
		//Add indicator
		$query="SELECT SPC_NTE FROM CMP.PRN_DIG_ORD_HEAD WHERE ORD_NBR=$CmptNbr";
		//echo $query;
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$SpcNte=$row['SPC_NTE'];
		if($SpcNte==''){
			$query="UPDATE CMP.PRN_DIG_ORD_HEAD SET SPC_NTE='CONV_THRD' WHERE ORD_NBR=$CmptNbr";
			$result=mysql_query($query);
		}
		$slack=true;
	}

	if ($slack){
		$query="SELECT NAME FROM CMP.COMPANY WHERE CO_NBR=$CoNbrDef";
		$resultp=mysql_query($query);
		$rowp=mysql_fetch_array($resultp);
		$DefCoName=$rowp['NAME'];
		$query="SELECT ORD_TTL,COM.NAME AS COM_NM,PPL.NAME AS PPL_NM,ORD_STT_DESC,UPD.NAME AS UPD_NM,SLACK_CHNNL_NM FROM CMP.PRN_DIG_ORD_HEAD HED
                    INNER JOIN CMP.PRN_DIG_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID
                    INNER JOIN CMP.PEOPLE UPD ON HED.UPD_NBR=UPD.PRSN_NBR
                    LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR
                    LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR
                    WHERE ORD_NBR=$CmptNbr";
        $resultp=mysql_query($query);
		$rowp=mysql_fetch_array($resultp);

		//Check message from people 
		$query= "SELECT NAME FROM PEOPLE WHERE PRSN_NBR=$PrsnNbr";
		$result= mysql_query($query);
		$rowNm= mysql_fetch_array($result);
        
        //Check chanel from cabang
        $query = "SELECT SLACK_CHNNL_NM FROM COMPANY WHERE CO_NBR=".$CoNbrDef;
        $result= mysql_query($query);
        $rowCn= mysql_fetch_array($result);
			
        $cust=trim($rowp['COM_NM']." ".$rowp['PPL_NM']);
        if($cust==""){$cust="Tunai";}
        //$slackChannelName=$rowp['SLACK_CHNNL_NM'];
        //if slack channel name null
        $slackChannelName=$rowCn['SLACK_CHNNL_NM'];
        

        $message="Pesan dari *".shortName($rowNm['NAME'])."* mengenai *".$rowp['ORD_TTL']."/".$CmptNbr."/".str_replace('&', '%26', $cust)."/".$DefCoName."*: ```".$ConvSlack."```";

        slack($message,$slackChannelName);
	}
	
	$query="SELECT CONV_NBR,CONV,PPL.NAME,CMPT_NBR,CNV.UPD_TS,CNV.UPD_NBR FROM CMP.CONV_THRD CNV INNER JOIN CMP.PEOPLE PPL ON CNV.UPD_NBR=PPL.PRSN_NBR WHERE CMPT='DIG_PRN' AND CNV.DEL_NBR=0 AND CMPT_NBR=$CmptNbr ORDER BY UPD_TS DESC";
	//echo $query;
	$result=mysql_query($query);
	while($row=mysql_fetch_array($result)){
		echo "<div class='conv-item".$alt."'>";
		echo $row['CONV']." &nbsp;<span class='fa fa-user'></span> ".shortName($row['NAME'])." &nbsp;<span class='fa fa-clock-o'></span> ";
		$time=strtotime($row['UPD_TS']);
		//echo humanTiming($time)." yang lalu";
		echo humanTiming($time)." yang lalu";
		$elapsed=time()-$time;
		//Need a universal variable for the whole system
		if(($elapsed<=3600)&&($PrsnNbr==$row['UPD_NBR'])){
			echo "&nbsp;<span class='fa fa-trash' style='cursor:pointer' onclick=".chr(34)."getContent('conversation','print-digital-edit-conversation.php?&CMPT_NBR=".$row['CMPT_NBR']."&DEL=".$row['CONV_NBR']."&ALT=$altOrig');".chr(34)."></span>";
		}
		echo "</div>";
		if($alt=="-alt"){$alt="";}else{$alt="-alt";}
	}


?>
