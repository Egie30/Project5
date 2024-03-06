<?php
	include "framework/functions/default.php";
    include "framework/database/connect.php";
    include "framework/functions/crypt.php";

	$OrdSttId=$_GET['STT'];
    $PrsnNbr=$_GET['PRSN_NBR'];
	//Get active order parameter
	//$activePeriod=getParam("print-digital","period-order-active-month");
	//$badPeriod=getParam("print-digital","period-bad-order-month");
	$activePeriod=3;
	$badPeriod=12;
	//Continue process filter
	if($OrdSttId=="ALL"){
		$where="WHERE HED.ORD_STT_ID LIKE '%'";
	}elseif($OrdSttId=="IBX"){
        $family=getChildren($_SESSION['personNBR']);
        if($PrsnNbr==''){
            $children=getChildren($_SESSION['personNBR']);
            if($children!=''){$children=$_SESSION['personNBR'].','.$children;}else{$children=$_SESSION['personNBR'];}
        }else{
            $children=$PrsnNbr;
        }
        $where="WHERE HED.ORD_STT_ID!='CP' AND (ORD_NBR IN (SELECT ORD_NBR FROM CMP.JRN_PRN_DIG WHERE CRT_NBR IN (".$children.") GROUP BY ORD_NBR) OR CRT_NBR IN (".$children.") OR HED.UPD_NBR IN (".$children.") OR SLS_PRSN_NBR IN (".$children.") OR ACCT_EXEC_NBR IN (".$children.")) AND HED.DEL_NBR=0";
	}elseif($OrdSttId=="ACT"){
		$where="WHERE (HED.ORD_STT_ID!='CP' OR (HED.ORD_STT_ID='CP' AND TIMESTAMPADD(MONTH,$activePeriod,ORD_TS)>=CURRENT_TIMESTAMP) OR (TOT_REM>0 AND TIMESTAMPADD(MONTH,$badPeriod,ORD_TS)>=CURRENT_TIMESTAMP)) AND HED.DEL_NBR=0";
	}elseif($OrdSttId=="CP"){
		$where="WHERE HED.ORD_STT_ID='CP' AND TIMESTAMPADD(MONTH,$activePeriod,ORD_TS)>=CURRENT_TIMESTAMP AND HED.DEL_NBR=0";
	}elseif($OrdSttId=="DUE"){
		$where="WHERE TOT_REM>0 AND DATE_ADD(CMP_TS,INTERVAL COALESCE(PAY_TERM,0) DAY)<=CURRENT_TIMESTAMP AND HED.DEL_NBR=0";
	}elseif($OrdSttId=="COL"){
		$buyPrsnNbr=$_GET['BUY_PRSN_NBR'];
		$buyCoNbr=$_GET['BUY_CO_NBR'];
		if($buyCoNbr!=""){
			$whereString=" AND BUY_CO_NBR=".$buyCoNbr;
			if($buyPrsnNbr!=""){
				$whereString.=" AND BUY_PRSN_NBR=".$buyPrsnNbr;
			}
		}else{
			if($buyPrsnNbr!=""){
				$whereString=" AND BUY_PRSN_NBR=".$buyPrsnNbr;
			}
		}
		if(($buyPrsnNbr=="0")&&($buyCoNbr=="0")){$whereString=" AND (BUY_CO_NBR IS NULL AND BUY_PRSN_NBR IS NULL)";}
		$where="WHERE HED.DEL_NBR=0 ".$whereString." AND YEAR(ORD_TS)=".$_GET['YEAR']." AND MONTH(ORD_TS)=".$_GET['MONTH']." AND TOT_REM>0";
	}elseif($OrdSttId=="DLO"){
		$where="WHERE HED.ORD_STT_ID!='CP' AND DL_CNT>0 AND HED.DEL_NBR=0";
	}else{
		$where="WHERE HED.ORD_STT_ID='".$OrdSttId."' AND HED.DEL_NBR=0";
	}

	$query="SELECT NBR FROM CDW.PRN_DIG_TOP_CUST";
	$result=mysql_query($query);
	while($row=mysql_fetch_array($result)){
		$TopCusts[]=strval($row['NBR']);
	}
	//print_r($TopCusts);
	$query="SELECT HED.ORD_NBR,DL_CNT,PU_CNT,NS_CNT,IVC_PRN_CNT,ORD_TS,HED.ORD_STT_ID,ORD_STT_DESC,BUY_PRSN_NBR,PPL.NAME AS NAME_PPL,COM.NAME AS NAME_CO,BUY_CO_NBR,REF_NBR,ORD_TTL,DUE_TS,JOB_LEN_TOT,PRN_CO_NBR,FEE_MISC,TOT_AMT,PYMT_DOWN,PYMT_REM,TOT_REM,CMP_TS,PU_TS,SPC_NTE,HED.CRT_TS,HED.CRT_NBR,HED.UPD_TS,HED.UPD_NBR,CMP_TS,DATEDIFF(DATE_ADD(CMP_TS,INTERVAL COALESCE(PAY_TERM,0) DAY),CURRENT_TIMESTAMP) AS PAST_DUE, WEEKDAY(DUE_TS) AS DUE_WD
    FROM CMP.PRN_DIG_ORD_HEAD HED
    INNER JOIN CMP.PRN_DIG_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID
    LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR
    LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR $where
    ORDER BY ORD_NBR DESC";
	//echo $query;
	$result=mysql_query($query);
    while($row=mysql_fetch_array($result)){
        $str.= $row['ORD_NBR'].chr(31).$row['DUE_TS'].chr(31).$row['ORD_STT_ID'].chr(31).$row['JOB_LEN_TOT'].chr(31).$row['NAME_PPL'].chr(31).$row['NAME_CO'].chr(31).$row['BUY_CO_NBR'].chr(31);
        if(in_array($row['BUY_CO_NBR'],$TopCusts)){
            $str.='T';
        }
        $str.= chr(31).$row['SPC_NTE'].chr(31).$row['DL_CNT'].chr(31).$row['PU_CNT'].chr(31).row['NS_CNT'].chr(31).$row['IVC_PRN_CNT'].chr(31).$row['ORD_TTL'].chr(31).$row['ORD_TS'].chr(31).$row['ORD_STT_DESC'].chr(31).$row['TOT_REM'].chr(31).$row['TOT_AMT'].chr(30);
    }
    echo simple_crypt(substr($str,0,-1));
?>