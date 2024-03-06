<?php
	include "framework/functions/default.php";
    include "framework/database/connect.php";
    include "framework/functions/crypt.php";
    $OrdNbr=$_GET['ORD_NBR'];

    //Header
	$query="SELECT ORD_NBR,ORD_TS,HED.ORD_STT_ID,ORD_STT_DESC,BUY_PRSN_NBR,NBR,PPL.NAME AS NAME_PPL,COM.NAME AS NAME_CO,BUY_CO_NBR,HED.CNS_CO_NBR,BIL_CO_NBR,REF_NBR,ORD_TTL,DUE_TS,PRN_CO_NBR,SLS_PRSN_NBR,FEE_MISC,TAX_APL_ID,TAX_AMT,TOT_AMT,PYMT_DOWN,PYMT_REM,VAL_PYMT_DOWN,VAL_PYMT_REM,TOT_REM,CMP_TS,PU_TS,DL_CNT,PU_CNT,NS_CNT,IVC_PRN_CNT,SPC_NTE,JOB_LEN_TOT,HED.CRT_TS,HED.CRT_NBR,HED.UPD_TS,HED.UPD_NBR,CRT.NAME AS NAME_CRT,UPD.NAME AS NAME_UPD
			FROM CMP.PRN_DIG_ORD_HEAD HED
			INNER JOIN CMP.PRN_DIG_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID
			LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR
			LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR
			LEFT OUTER JOIN CMP.PEOPLE CRT ON HED.CRT_NBR=CRT.PRSN_NBR
			LEFT OUTER JOIN CMP.PEOPLE UPD ON HED.UPD_NBR=UPD.PRSN_NBR
			LEFT OUTER JOIN CDW.PRN_DIG_TOP_CUST TOP ON HED.BUY_CO_NBR=TOP.NBR
			WHERE ORD_NBR=".$OrdNbr;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
    if(trim($row['NAME_PPL']." ".$row['NAME_CO'])==""){$name="Tunai";}else{$name=trim($row['NAME_PPL']." ".$row['NAME_CO']);}
    $data[]=array('timestamp'=>$row['DUE_TS'],'title'=>'Deadline','description'=>'','person'=>$row['NAME']);
    $due=strtotime($row['DUE_TS']);
    $OrdSttId=$row['ORD_STT_ID'];
    if((strtotime("now")>$due)&&(($OrdSttId=="NE")||($OrdSttId=="RC")||($OrdSttId=="QU")||($OrdSttId=="PR")||($OrdSttId=="FN"))){
        $dot="<span class='fa fa-circle' style='line-height:22px;color:#d92115'></span>";
    }elseif((strtotime("now + ".$row['JOB_LEN_TOT']." minute")>$due)&&(($OrdSttId=="NE")||($OrdSttId=="RC")||($OrdSttId=="QU")||($OrdSttId=="PR")||($OrdSttId=="FN"))){
        $dot="<span class='fa fa-circle' style='line-height:22px;color:#fbad06'></span>";
    }else{
        $dot="";
    }
    $str.=$name.chr(31).$row['ORD_TTL'].chr(31).$row['ORD_STT_DESC'].chr(31).$dot.chr(30);

    //Details need to work on the product type.
    $query="SELECT ORD_DET_NBR,DET.ORD_NBR,DET_TTL,PRN_DIG_DESC,DET.PRN_DIG_PRC,ORD_Q,FIL_LOC,PRN_LEN,PRN_WID,FEE_MISC,FAIL_CNT,DISC_PCT,DISC_AMT,VAL_ADD_AMT,TOT_SUB,ROLL_F,HND_OFF_TYP,HND_OFF_TS,SORT_BAY_ID,DET.UPD_TS,NAME
				FROM CMP.PRN_DIG_ORD_DET DET INNER JOIN
                     CMP.PEOPLE PPL ON DET.UPD_NBR=PPL.PRSN_NBR LEFT OUTER JOIN
				     CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP LEFT OUTER JOIN
					 CMP.PRN_DIG_EQP EQP ON TYP.PRN_DIG_EQP=EQP.PRN_DIG_EQP
				WHERE ORD_NBR=".$OrdNbr." AND ORD_DET_NBR_PAR IS NULL AND DET.DEL_NBR=0 ORDER BY 1";
    $resultd=mysql_query($query);
    while($rowd=mysql_fetch_array($resultd)){
        if(($rowd['PRN_LEN']!="")&&($rowd['PRN_WID']!="")){$prnDim=" ".$rowd['PRN_LEN']."x".$rowd['PRN_WID'];}else{$prnDim="";}
        $data[]=array('timestamp'=>$rowd['UPD_TS'],'title'=>$rowd['ORD_DET_NBR'],'description'=>$rowd['ORD_Q']." ".trim($rowd['DET_TTL']." ".$rowd['PRN_DIG_DESC']." ".$prnDim),'person'=>$rowd['NAME']);
    }

    //Journal
    $query="SELECT ORD_NBR,ORD_STT_DESC,CRT_TS,NAME FROM CMP.JRN_PRN_DIG JRN INNER JOIN CMP.PRN_DIG_STT STT ON JRN.ORD_STT_ID=STT.ORD_STT_ID INNER JOIN CMP.PEOPLE PPL ON JRN.CRT_NBR=PPL.PRSN_NBR WHERE ORD_NBR=".$OrdNbr;
    $resultj=mysql_query($query);
    while($rowj=mysql_fetch_array($resultj)){
        $data[]=array('timestamp'=>$rowj['CRT_TS'],'title'=>$rowj['ORD_STT_DESC'],'description'=>'','person'=>$rowj['NAME']);
    }

    //Comments TBD
    $query="SELECT CNV.UPD_TS,CONV,NAME FROM CMP.CONV_THRD CNV INNER JOIN CMP.PEOPLE PPL ON CNV.UPD_NBR=PPL.PRSN_NBR WHERE CMPT='DIG_PRN' AND CNV.DEL_NBR=0 AND CMPT_NBR=".$OrdNbr;
    $resultc=mysql_query($query);
    while($rowc=mysql_fetch_array($resultc)){
        $data[]=array('timestamp'=>$rowc['UPD_TS'],'title'=>'Notes','description'=>$rowc['CONV'],'person'=>$rowc['NAME']);
    }

    //Sort by timestamp
    foreach($data as $key=>$datum) {
        $timestamp[$key]=$datum['timestamp'];
        $title[$key]=$datum['title'];
    }
    array_multisort($timestamp, SORT_ASC, $title, SORT_ASC, $data);
    
    $str.=$row['ORD_TS'].chr(31).'Baru'.chr(31).chr(31).$row['NAME_UPD'].chr(30);
    foreach($data as $detail){
        $str.=$detail['timestamp'].chr(31).$detail['title'].chr(31).$detail['description'].chr(31).$detail['person'].chr(30);
    }
    echo simple_crypt(substr($str,0,-1));
?>