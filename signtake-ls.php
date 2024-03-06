<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	$searchQuery = trim(strtoupper(urldecode($_REQUEST[s])));
	
	//Take care of leading zeros on the order number
	if(is_numeric($searchQuery)){
		$searchQuery=$searchQuery+0;
	}

    $query  = "SELECT NBR FROM CDW.PRN_DIG_TOP_CUST";
    $result = mysql_query($query);
    while($row=mysql_fetch_array($result)){
        $TopCusts[]=strval($row['NBR']);
    }

    $query  = "SELECT 
                    THD.TRNSP_NBR,
                    THD.DUE_TS,
                    THD.TRNSP_TS,
                    COM.NAME AS NAME_CO,
                    SUB.CAT_SUB_DESC,
                    STT.TRNSP_STT_DESC,
                    THD.ORD_NBR,
                    COUNT(*) AS TYPE_CNT,
                    SUM(TDE.TRNSP_Q) AS ITEM_CNT
                FROM CMP.TRNSP_HEAD THD 
                LEFT JOIN CMP.TRNSP_DET TDE ON THD.TRNSP_NBR=TDE.TRNSP_NBR 
                LEFT JOIN CMP.TRNSP_STT STT ON THD.TRNSP_STT_ID=STT.TRNSP_STT_ID
                LEFT JOIN RTL.RTL_STK_HEAD HED ON THD.ORD_NBR=HED.ORD_NBR 
                LEFT JOIN CMP.COMPANY COM ON HED.SHP_CO_NBR=COM.CO_NBR
                LEFT JOIN RTL.CAT_SUB SUB ON HED.CAT_SUB_NBR=SUB.CAT_SUB_NBR
                WHERE THD.DEL_NBR=0 AND STT.TRNSP_STT_ID='RP' AND (COM.NAME LIKE '%".$searchQuery."%' OR SUB.CAT_SUB_DESC LIKE '%".$searchQuery."%' OR TRNSP_STT_DESC LIKE '%".$searchQuery."%')
                GROUP BY THD.TRNSP_NBR
                ORDER BY THD.TRNSP_NBR DESC";
			//echo $query;
	$result=mysql_query($query);
	if(mysql_num_rows($result)==0){
		echo "<div class='searchStatus'>Nama atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}
	
	//$alt="class='tripane-list-alt'";
	$firstRow="";
	while($row=mysql_fetch_array($result))
	{
		//Perform changes in tranport-edit.php as well
            echo "<div id='O".($row['TRNSP_NBR'])."' class='tripane-list' onclick=".chr(34)."changeSiblingUrl('rightpane','signtake-edit.php?STT=RP&TRNSP_NBR=".$row['TRNSP_NBR']."');selLeftPane(this);".chr(34);
            if($firstRow==""){
                echo "style='background-color:#eef8fb'";
            }
            //Need testing, also need similar implementation for the print-digital.
            if($row['TRNSP_NBR']==$Goto){
                echo "style='background-color:#eef8fb'";
            }
            echo ">";
            echo "<div style='font-weight:bold;color:#666666;font-size:12pt;display:inline;float:left'>".$row['TRNSP_NBR']."</div>";
            echo "<div style='display:inline;float:right;'>".parseDateTimeLiteralShort($row['TRNSP_TS'])."</div>";
            echo "<div style='clear:both'></div>";
            echo "<div style='font-weight:700;color:#3464bc'>".$row['NAME_CO']."</div>";
            echo "<div>".$row['CAT_SUB_DESC']."</div>";
            echo "<div><span style='font-weight:700'>".$row['TRNSP_STT_DESC']."</span>";
            echo "&nbsp;".$row['ORD_NBR'];
            echo "<span style='float:right;style='color:#888888'>".number_format($row['TYPE_CNT'],0,'.',',')." Jenis ";
            echo "".number_format($row['ITEM_CNT'],0,'.',',')." buah";
            echo "</span></div></div>";
            if($firstRow==""){$firstRow=$row['TRNSP_NBR'];}
	}
?>
