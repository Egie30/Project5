<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	date_default_timezone_set("Asia/Jakarta");
	
	//security
	$Security=getSecurity($_SESSION['userID'],"Finance");
	
	//Process filter
	$OrdSttId	= $_GET['STT'];
	$type		= $_GET['TYP'];
    $PrsnNbr	= $_GET['PRSN_NBR'];

	//Process auto detail display
	$Goto=$_GET['GOTO'];
	
	//Filter table Print digital or Pro forma
	if($type == "EST"){
		$headtable 	= "CMP.PRN_DIG_ORD_HEAD_EST";
		$detailtable= "CMP.PRN_DIG_ORD_DET_EST";
	}else{
		$headtable 	= "CMP.PRN_DIG_ORD_HEAD";
		$detailtable= "CMP.PRN_DIG_ORD_DET";
	}

	//Process delete entry
	$delete=false;
	if($_GET['DEL']!="")
	{
		$query="UPDATE ". $headtable ." SET DEL_NBR=".$_SESSION['personNBR']." WHERE ORD_NBR=".$_GET['DEL'];
		//echo $query;
		$result=mysql_query($query);
		$OrdSttId	= "ACT";
		$Goto  		= "TOP"; 
		$delete		= true;
		
		$query="UPDATE ". $detailtable ." SET DEL_NBR=".$_SESSION['personNBR']." WHERE ORD_NBR=".$_GET['DEL'];
		$result=mysql_query($query);
	}
	//Get active order parameter
	//$activePeriod=getParam("print-digital","period-order-active-month");
	//$badPeriod=getParam("print-digital","period-bad-order-month");
	$activePeriod=3;
	$badPeriod=12;
	//Continue process filter
	if ($OrdSttId=="DEL") {
		$where="WHERE HED.ORD_STT_ID LIKE '%' AND HED.DEL_NBR<>0";
	}
	elseif($OrdSttId=="ALL"){
		$where="WHERE HED.ORD_STT_ID LIKE '%' AND HED.DEL_NBR=0";
	}elseif($OrdSttId=="IBX"){
        $family=getChildren($_SESSION['personNBR']);
        if($PrsnNbr==''){
            $children=getChildren($_SESSION['personNBR']);
            if($children!=''){$children=$_SESSION['personNBR'].','.$children;}else{$children=$_SESSION['personNBR'];}
        }else{
            $children=$PrsnNbr;
        }
        $where="WHERE HED.ORD_STT_ID!='CP' AND (HED.ORD_NBR IN (SELECT ORD_NBR FROM CMP.JRN_PRN_DIG WHERE CRT_NBR IN (".$children.") GROUP BY ORD_NBR) OR CRT_NBR IN (".$children.") OR HED.UPD_NBR IN (".$children.") OR SLS_PRSN_NBR IN (".$children.") OR ACCT_EXEC_NBR IN (".$children.")) AND HED.DEL_NBR=0";
	}elseif($OrdSttId=="ACT"){
		$where="WHERE (HED.ORD_STT_ID!='CP' OR (HED.ORD_STT_ID='CP' AND TIMESTAMPADD(MONTH,$activePeriod,ORD_TS)>=CURRENT_TIMESTAMP AND LAST_DAY(DATE_ADD(ORD_TS,INTERVAL COALESCE(PAY_TERM,32) DAY))>=CURRENT_DATE) OR (HED.ORD_STT_ID!='CP' AND TOT_REM>0 AND TIMESTAMPADD(MONTH,$badPeriod,ORD_TS)>=CURRENT_TIMESTAMP)) AND HED.DEL_NBR=0";
	}elseif($OrdSttId=="SLM"){
		$where="WHERE HED.ORD_STT_ID!='CP' AND JRN.CRT_TS <= NOW() - INTERVAL 48 HOUR AND HED.DEL_NBR=0 GROUP BY HED.ORD_NBR";
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
	}elseif($OrdSttId=="POD"){
		$where="WHERE HED.ORD_STT_ID ='CP' AND TOT_REM>0 AND LAST_DAY(DATE_ADD(ORD_TS,INTERVAL COALESCE(PAY_TERM,32) DAY))<=CURRENT_DATE AND HED.DEL_NBR=0";
	}else{
		$where="WHERE HED.ORD_STT_ID='".$OrdSttId."' AND HED.DEL_NBR=0";
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<script type="text/javascript" src='framework/tablesort/tablesort.js'></script>
<script type="text/javascript" src='framework/tablesort/customsort.js'></script>
<script type="text/javascript" src="framework/functions/default.js"></script>
<script type="text/javascript"  src="framework/database/jquery.min.js"></script>

<link rel="stylesheet" href="framework/combobox/chosen.css">
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">

</head>

<body>

<?php if($delete){echo "<script>parent.document.getElementById('leftmenu').contentDocument.location.reload(true);</script>";} ?>

<div class="toolbar">
	<?php if (!in_array($OrdSttId, array('SLM','POD'))) { ?>
    <p class="toolbar-left">
		<span class='fa fa-plus toolbar' style='cursor:pointer' onclick="changeSiblingUrl('rightpane','print-digital-edit.php?ORD_NBR=0&TYP=<?php echo $type; ?>');deSelLeftPane(this);"></span>
	</p>
	<?php } ?>	
    <p class="toolbar-right" style='margin-right:100px'>
		<span class='fa fa-search fa-flip-horizontal toolbar' style='cursor:default' src="img/search.png"></span><input type="text" id="livesearch" class="livesearch" />
	</p>
</div>
<div style='clear:both;height:10px'></div>

<div class="searchresult" id="liveRequestResults" style='height:calc(100% - 45px);-webkit-overflow-scrolling:touch !important;overflow-y:scroll !important'></div>

<?php
    $offsetHeight=43;
    if($family!=''){
        echo "<select class='chosen-select' style='width:350px' name='PRSN_NBR' onChange=".chr(34)."changeSiblingUrl('leftpane','print-digital.php?STT=IBX&GOTO=TOP&PRSN_NBR='+this.value);".chr(34)."><br />";
            $query="SELECT PRSN_NBR,NAME FROM CMP.PEOPLE WHERE PRSN_NBR IN ($family)";
            genCombo($query,"PRSN_NBR","NAME",$PrsnNbr,"Semua");
        echo "</select>";
        echo "<div style='clear:both;height:10px'></div>";
    $offsetHeight=74;
    }
?>

<div id="mainResult" style='height:calc(100% - <?php echo $offsetHeight; ?>px);-webkit-overflow-scrolling:touch !important;overflow-y:scroll !important;overflow-x:hidden'>
	<?php
		$query="SELECT NBR FROM CDW.PRN_DIG_TOP_CUST";
		$result=mysql_query($query);
		while($row=mysql_fetch_array($result)){
			$TopCusts[]=strval($row['NBR']);
		}
		//print_r($TopCusts);
		$query="SELECT HED.ORD_NBR,DL_CNT,PU_CNT,NS_CNT,IVC_PRN_CNT,ORD_TS,HED.ORD_STT_ID,ORD_STT_DESC,BUY_PRSN_NBR,PPL.NAME AS NAME_PPL,COM.NAME AS NAME_CO,BUY_CO_NBR,REF_NBR,ORD_TTL,DUE_TS,JOB_LEN_TOT,PRN_CO_NBR,FEE_MISC,TOT_AMT,PYMT_DOWN,PYMT_REM,TOT_REM,CMP_TS,PU_TS,SPC_NTE,HED.CRT_TS,HED.CRT_NBR,HED.UPD_TS,HED.UPD_NBR,CMP_TS,
		TIMESTAMPDIFF(HOUR,JRN.CRT_TS,NOW()) AS SLM_HRS,
		DATEDIFF(DATE_ADD(CMP_TS,INTERVAL COALESCE(PAY_TERM,0) DAY),CURRENT_TIMESTAMP) AS PAST_DUE
				FROM ". $headtable ." HED
				INNER JOIN CMP.PRN_DIG_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID
				LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR
				LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR 
				LEFT OUTER JOIN(
					SELECT
						ORD_NBR,
						MAX(CRT_TS) AS CRT_TS
					FROM CMP.JRN_PRN_DIG
					GROUP BY ORD_NBR
				)JRN ON HED.ORD_NBR=JRN.ORD_NBR
				$where
				ORDER BY ORD_NBR DESC";
		//echo $query;
		$result=mysql_query($query);
		//$alt="class='tripane-list-alt'";
		$firstRow="";
		while($row=mysql_fetch_array($result))
		{
			//Traffic light control
			$due=strtotime($row['DUE_TS']);
			$OrdSttId=$row['ORD_STT_ID'];
			if((strtotime("now")>$due)&&(($OrdSttId=="NE")||($OrdSttId=="RC")||($OrdSttId=="QU")||($OrdSttId=="PR")||($OrdSttId=="FN"))){
				$dot="<div class='listable' style='display:inline;float:right'><span class='fa fa-circle listable' style='font-size:8pt;color:#d92115'></span></div>";
			}elseif((strtotime("now + ".$row['JOB_LEN_TOT']." minute")>$due)&&(($OrdSttId=="NE")||($OrdSttId=="RC")||($OrdSttId=="QU")||($OrdSttId=="PR")||($OrdSttId=="FN"))){
				$dot="<div class='listable' style='display:inline;float:right'><span class='fa fa-circle listable' style='font-size:8pt;color:#fbad06'></span></div>";
			}else{
				$dot="";
			}
			
			//Perform changes in print-digital-edit.php as well
			echo "<div id='O".($row['ORD_NBR'])."' class='tripane-list' onclick=".chr(34)."changeSiblingUrl('rightpane','print-digital-edit.php?ORD_NBR=".$row['ORD_NBR']."&STT=".$_GET['STT']."&TYP=".$type."');selLeftPane(this);".chr(34);
			if($firstRow==""){
				echo "style='background-color:#eef8fb'";
			}
			echo ">";
			echo "<div style='font-weight:bold;color:#666666;font-size:12pt;display:inline;float:left'>".$row['ORD_NBR']."</div>";
			echo "<div style='display:inline;float:right;'>".parseDateTimeLiteralShort($row['DUE_TS'])."</div>";
			echo "<div style='clear:both'></div>";
			echo "<div style='display:inline;float:left;'>";
			if(in_array($row['BUY_CO_NBR'],$TopCusts)){
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
			if($row['SLM_HRS']>=48 && $row['ORD_STT_ID'] != "CP"){
				echo "<div class='listable'><span class='fa fa-history listable'></span></div>";
			}
			echo "&nbsp;</div>";
			echo "<div style='clear:both'></div>";
			if(trim($row['NAME_PPL']." ".$row['NAME_CO'])==""){$name="Tunai";}else{$name=trim($row['NAME_PPL']." ".$row['NAME_CO']);}
			echo $dot;
			echo "<div style='font-weight:700;color:#3464bc'>".$name."</div>";
			echo "<div>".htmlentities($row['ORD_TTL'],ENT_QUOTES)."</div>";
			/*
			echo "<div style='margin-top:2px;'>";
			$items 	= explode(" ",$row['PRN_DIG_CD']);
			$colors = explode(" ",$row['PRN_DIG_EQP_COLR']);
			foreach( $items as $data => $item){
				$color = $colors[$data];
				if($item != ''){
					echo "<span style='padding: 0px 3px 0px 3px;
						background-color: ".$color.";
						border-radius: 3px;
                        -webkit-border-radius: 3px;
                        -moz-border-radius: 3px;
                        color: #ffffff;
                        width:90px;
                        text-align: left;
						font-size: 9pt;    
						vertical-align: 1px;
                        overflow:hidden;
                        text-overflow:ellipsis;    
                        white-space:nowrap;'>".htmlentities(trim($item),ENT_QUOTES)."</span>&nbsp;";
				}
			}
			echo "</div>";
			*/
			echo "<div>".parseDateShort($row['ORD_TS'])."&nbsp;";
			echo "<span style='font-weight:700'>".$row['ORD_STT_DESC']."</span>";
			echo "<span style='float:right;style='color:#888888'>";
			if($row['TOT_REM']==0){
                echo "<div class='listable' style='display:inline;float:left'><span class='fa fa-circle listable' style='font-size:8pt;color:#3464bc'></span></div>";
            }elseif($row['TOT_AMT']==$row['TOT_REM']){
                echo "<div class='listable' style='display:inline;float:left'><span class='fa fa-circle-o listable' style='font-size:8pt;color:#3464bc'></span></div>";
            }else{
                echo "<div class='listable' style='display:inline;float:left'><span class='fa fa-dot-circle-o listable' style='font-size:8pt;color:#3464bc'></span></div>";
            }
			echo "&nbsp;Rp. ".number_format($row['TOT_AMT'],0,',','.');
			echo "</span></div></div>";
			//if($alt=="class='tripane-list-alt'"){$alt="class='tripane-list'";}else{$alt="class='tripane-list-alt'";}
			if($firstRow==""){$firstRow=$row['ORD_NBR'];}
		}
	?>
</div>

<script>liveReqInit('livesearch','liveRequestResults','print-digital-ls.php?STT=<?php echo $_GET['STT'];?>&TYP=<?php echo $type;?>','','mainResult');</script>

<script>fdTableSort.init();</script>

<script>
	<?php if($Goto=='TOP'){echo "changeSiblingUrl('rightpane','print-digital-edit.php?ORD_NBR=".$firstRow."&TYP=".$type."');";} ?>
</script>

<script src="framework/combobox/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript">
	var config = {
        '.chosen-select'           : {},
        '.chosen-select-deselect'  : {allow_single_deselect:true},
        '.chosen-select-no-single' : {disable_search_threshold:10},
        '.chosen-select-no-results': {no_results_text:'Data tidak ketemu'},
        '.chosen-select-width'     : {width:"95%"}
    }
    for (var selector in config) {
        jQuery(selector).chosen(config[selector]);
    }
</script>

</body>
</html>
