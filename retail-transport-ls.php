<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	$searchQuery = trim(strtoupper(urldecode($_REQUEST[s])));
	
	//Take care of leading zeros on the order number
	if(is_numeric($searchQuery)){
		$searchQuery=$searchQuery+0;
	}

    $OrdSttId=$_GET['STT'];

    $query="SELECT NBR FROM CDW.PRN_DIG_TOP_CUST";
    $result=mysql_query($query);
    while($row=mysql_fetch_array($result)){
        $TopCusts[]=strval($row['NBR']);
    }

    $activePeriod=3;
    $badPeriod=12;

    //Continue process filter
    if($OrdSttId=="ACT"){
        $where="AND (THD.TRNSP_STT_ID!='DL' OR (THD.TRNSP_STT_ID='DL' AND TIMESTAMPADD(MONTH,$activePeriod,TRNSP_TS)>=CURRENT_TIMESTAMP)) ";
    }elseif($OrdSttId=="ALL"){
        $where="AND THD.TRNSP_STT_ID LIKE '%'";
    }else{
        $where="AND THD.TRNSP_STT_ID='".$OrdSttId."'";
    }

	$query="SELECT 
		THD.TRNSP_NBR,
		OHD.ORD_NBR,
		THD.ORD_TTL,
		THD.RCV_CO_NBR,
		BCM.NAME AS NAME_CO,
		THD.DUE_TS,
		TRNSP_STT_DESC,
		COUNT(*) AS TYPE_CNT,
		SUM(TRNSP_Q) AS ITEM_CNT,
		THD.TRNSP_STT_ID
	FROM RTL.TRNSP_HEAD THD
		INNER JOIN RTL.TRNSP_STT STT ON THD.TRNSP_STT_ID=STT.TRNSP_STT_ID
		LEFT OUTER JOIN RTL.RTL_ORD_HEAD OHD ON THD.ORD_NBR=OHD.ORD_NBR
		LEFT OUTER JOIN CMP.COMPANY BCM ON OHD.RCV_CO_NBR=BCM.CO_NBR
		LEFT OUTER JOIN RTL.TRNSP_DET DET ON THD.TRNSP_NBR=DET.TRNSP_NBR
	WHERE THD.DEL_NBR=0 
		#$where 
		AND (DET.DEL_NBR=0 OR DET.DEL_NBR IS NULL) 
		AND (THD.ORD_TTL LIKE '%".$searchQuery."%'
			OR BCM.NAME LIKE '%".$searchQuery."%' 
			OR THD.ORD_NBR LIKE '%".$searchQuery."%' 
			OR THD.TRNSP_NBR LIKE '%".$searchQuery."%'
		)
	GROUP BY THD.TRNSP_NBR,OHD.ORD_NBR,ORD_TTL,THD.RCV_CO_NBR,BCM.NAME,DUE_TS,TRNSP_STT_DESC
	ORDER BY THD.UPD_TS DESC";
	//echo "<pre>".$query;
	$result=mysql_query($query);
	if(mysql_num_rows($result)==0){
		echo "<div class='searchStatus'>Nama atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}
	
	//$alt="class='tripane-list-alt'";
	$firstRow="";
	while($row=mysql_fetch_array($result))
	{
		if($firstRow==""){$firstRow=$row['ORD_NBR'];}
		//Traffic light control
		$due=strtotime($row['DUE_TS']);
		$OrdSttId=$row['ORD_STT_ID'];
        if(strtotime("now")>$due){
            $dot="<div class='listable' style='display:inline;float:right'><span class='fa fa-circle listable' style='font-size:8pt;color:#d92115'></span></div>";
        }elseif(strtotime("now + ".$row['JOB_LEN_TOT']." minute")>$due){
            $dot="<div class='listable' style='display:inline;float:right'><span class='fa fa-circle listable' style='font-size:8pt;color:#fbad06'></span></div>";				
        }else{
            $dot="";
        }
		
		//Perform changes in print-digital-edit.php as well
		echo "<div id='O".($row['TRNSP_NBR'])."' class='tripane-list' onclick=".chr(34)."changeSiblingUrl('rightpane','retail-transport-edit.php?TRNSP_NBR=".$row['TRNSP_NBR']."');selLeftPane(this);".chr(34);
        if($firstRow==""){
            echo "style='background-color:#eef8fb'";
        }
        //Need testing, also need similar implementation for the print-digital.
        if($row['TRNSP_NBR']==$Goto){
            echo "style='background-color:#eef8fb'";
        }
        echo ">";
        echo "<div style='font-weight:bold;color:#666666;font-size:12pt;display:inline;float:left'>".$row['TRNSP_NBR']."</div>";
        echo "<div style='display:inline;float:right;'>".parseDateTimeLiteralShort($row['DUE_TS'])."</div>";

        if ((in_array($row['RCV_CO_NBR'],$TopCusts)) || $row['SPC_NTE']!="" || $row['DL_CNT']>0 || $row['PU_CNT']>0 || $row['NS_CNT']>0 || $row['NS_CNT']> 0 || $row['IVC_PRN_CNT']>0){
            echo "<div style='clear:both'></div>";
            echo "<div style='display:inline;float:left;'>";

            if(in_array($row['RCV_CO_NBR'],$TopCusts)){
                echo "<div class='listable'><span class='fa fa-star listable'></span></div>";
            }				
            if($row['SPC_NTE']!=""){
                echo "<div class='listable'><span class='fa fa-comment listable'></span></div>";
            }
            if($row['DL_CNT']>0){
                echo "<div class='listable'><span class='fa fa-truck listable' style='margin-left:-1px'></span></div>";
            }
            if($row['PU_CNT']>0){
                echo "<div class='listable'><span class='fa fa-shopping-cart listable'></span></div>";
            }
            if($row['NS_CNT']>0){
                echo "<div class='listable'><span class='fa fa-flag listable'></span></div>";
            }
            if($row['IVC_PRN_CNT']>0){
                echo "<div class='listable'><span class='fa fa-print listable'></span></div>";
            }
            echo "&nbsp;</div>";
        }
        echo "<div style='clear:both'></div>";
        if(trim($row['NAME_PPL']." ".$row['NAME_CO'])==""){$name="Tunai";}else{$name=trim($row['NAME_PPL']." ".$row['NAME_CO']);}
        echo $dot;
        echo "<div style='font-weight:700;color:#3464bc'>".$name."</div>";
        echo "<div>".$row['ORD_TTL']."</div>";
        echo "<div><span style='font-weight:700'>".$row['TRNSP_STT_DESC']."</span>";
        echo "&nbsp;".$row['ORD_NBR'];
        echo "<span style='float:right;style='color:#888888'>".number_format($row['TYPE_CNT'],0,'.',',')." Jenis ";
        echo "".number_format($row['ITEM_CNT'],0,'.',',')." buah";
        echo "</span></div></div>";
        //if($alt=="class='tripane-list-alt'"){$alt="class='tripane-list'";}else{$alt="class='tripane-list-alt'";}
        if($firstRow==""){$firstRow=$row['TRNSP_NBR'];}
	}
?>
