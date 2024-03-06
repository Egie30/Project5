<?php
	include "framework/functions/default.php";
    include "framework/database/connect.php";
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
?>
<div class="navbar">
    <div class="navbar-inner">
        <div class="left sliding"><a href="#" class="back link color-white"><span class="fa fa-chevron-left"></span></a></div>
            <div class="center sliding">List</div>
        <div class="right"><a href="#" class="open-panel link icon-only color-white"><span class="fa fa-bars"></span></a></div>
    </div>
</div>
<div class="pages navbar-through">
    <div data-page="print-digital-list" class="page">
        <div class="page-content contacts-content">
            <div class="list-block media-list contacts-block">
                <ul>
                    <?php
                        while($row=mysql_fetch_array($result)){
                            echo "<li><a href='print-digital-view.php?ORD_NBR=".$row['ORD_NBR']."' class='item-link'>";
                            echo "<div class='item-inner item-content'>";
                            echo "<div class='item-title-row'>";
                            echo "<div class='item-title'>".$row['ORD_NBR']."</div>";
                            echo "<div class='item-subtitle'>".parseDateTimeLiteralShort($row['DUE_TS'])."</div>";
                            echo "</div>";
                 
                            //Traffic light control
                            $due=strtotime($row['DUE_TS']);
                            $OrdSttId=$row['ORD_STT_ID'];
                            if((strtotime("now")>$due)&&(($OrdSttId=="NE")||($OrdSttId=="RC")||($OrdSttId=="QU")||($OrdSttId=="PR")||($OrdSttId=="FN"))){
                                $dot="<div class='item-after'><span class='fa fa-circle' style='line-height:22px;color:#d92115'></span></div>";
                            }elseif((strtotime("now + ".$row['JOB_LEN_TOT']." minute")>$due)&&(($OrdSttId=="NE")||($OrdSttId=="RC")||($OrdSttId=="QU")||($OrdSttId=="PR")||($OrdSttId=="FN"))){
                                $dot="<div class='item-after'><span class='fa fa-circle' style='line-height:22px;color:#fbad06'></span></div>";
                            }else{
                                $dot="";
                            }

                            echo "<div class='item-description' style='margin-left:-3px'>";
                            if(trim($row['NAME_PPL']." ".$row['NAME_CO'])==""){$name="Tunai";}else{$name=trim($row['NAME_PPL']." ".$row['NAME_CO']);}
                            if(in_array($row['BUY_CO_NBR'],$TopCusts)){
                                echo "<span class='fa fa-fw fa-star'></span>";
                            }				
                            if($row['SPC_NTE']!=""){
                                echo "<span class='fa fa-fw fa-comment'></span>";
                            }
                            if($row['DL_CNT']>0){
                                echo "<span class='fa fa-fw fa-truck' style='margin-left:-1px'></span>";
                            }
                            if($row['PU_CNT']>0){
                                echo "<span class='fa fa-fw fa-shopping-cart'></span>";
                            }
                            if($row['NS_CNT']>0){
                                echo "<span class='fa fa-fw fa-flag'></span>";
                            }
                            if($row['IVC_PRN_CNT']>0){
                                echo "<span class='fa fa-fw fa-print'></span>";
                            }
                            echo "</div>";
                            echo "<div class='item-subtitle-row'>";
                            echo "<div class='item-subtitle color-nestor'>".$name."</div>";
                            echo "<div class='item-after' style='font-size:15px'>$dot</div>";
                            echo "</div>"; 
                            echo "<div class='item-text' >".htmlentities($row['ORD_TTL'],ENT_QUOTES)."</div>";
                            echo "<div class='item-subtitle-row'>";
                            echo "<div class='item-description' style='color:#000000'>".parseDateShort($row['ORD_TS'])."&nbsp;";
                            echo "<span style='font-weight:700' style='color:#000000'>".$row['ORD_STT_DESC']."</span></div>";
                            echo "<div class='item-after' style='font-size:15px;color:#000000'>";
                            if($row['TOT_REM']==0){
                                echo "<span class='fa fa-fw fa-circle' style='line-height:22px;color:#3464bc'></span>";
                            }elseif($row['TOT_AMT']==$row['TOT_REM']){
                                echo "<span class='fa fa-fw fa-circle-o' style='line-height:22px;color:#3464bc'></span>";
                            }else{
                                echo "<span class='fa fa-fw fa-dot-circle-o' style='line-height:22px;color:#3464bc'></span>";
                            }
                            echo "&nbsp;Rp. ".number_format($row['TOT_AMT'],0,',','.')."</div>";
                            echo "</div>";
                            echo "</div></li></a>";
                        }
                    ?>
                </ul>
            </div>
        </div>
    </div>
</div>