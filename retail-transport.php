<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	date_default_timezone_set("Asia/Jakarta");

	//security
	$Security	= getSecurity($_SESSION['userID'],"Finance");
	$OrdSttId	= $_GET['STT'];
    $PrsnNbr	= $_GET['PRSN_NBR'];
	
	//Process delete entry
	$delete=false;
	if($_GET['DEL']!=""){
		$query="UPDATE RTL.TRNSP_HEAD SET DEL_NBR=".$_SESSION['personNBR']." WHERE TRNSP_NBR=".$_GET['DEL'];
		//echo $query;
		$result=mysql_query($query);
		$OrdSttId		= "";
		$_GET['GOTO']	= "TOP";
		$delete=true;
	}
	
	$Goto=$_GET['GOTO'];
	//Get active order parameter
	//$activePeriod=getParam("print-digital","period-order-active-month");
	//$badPeriod=getParam("print-digital","period-bad-order-month");
	$activePeriod	= 3;
	$badPeriod		= 12;
	//Continue process filter
	if($OrdSttId=="ACT"){
		$where="AND THD.TRNSP_STT_ID!='DL') OR (THD.TRNSP_STT_ID='DL' AND TIMESTAMPADD(MONTH,$activePeriod,TRNSP_TS)>=CURRENT_TIMESTAMP) ";
    }elseif($OrdSttId=="ALL"){
		$where="AND THD.TRNSP_STT_ID LIKE '%')";
	}else{
		$where="AND THD.TRNSP_STT_ID='".$OrdSttId."')";
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

<?php 
	if($delete){
		echo "<script>parent.document.getElementById('leftmenu').contentDocument.location.reload(true);</script>";
	} 
?>

<div class="toolbar">
    <p class="toolbar-left">
		<span class='fa fa-plus toolbar' style='cursor:pointer' onclick="changeSiblingUrl('rightpane','retail-transport-edit.php?TRNSP_NBR=0');deSelLeftPane(this);"></span>
	</p>
    <p class="toolbar-right" style='margin-right:100px'>
		<span class='fa fa-search fa-flip-horizontal toolbar' src="img/search.png"></span><input type="text" id="livesearch" class="livesearch" />
	</p>
</div>

<div style='clear:both;height:10px'></div>

<div class="searchresult" id="liveRequestResults" style='height:calc(100% - 45px);-webkit-overflow-scrolling:touch !important;overflow-y:scroll !important'></div>

<div id="mainResult" style='height:calc(100% - 43px);-webkit-overflow-scrolling:touch !important;overflow-y:scroll !important;overflow-x:hidden'>
		<?php 
			$prsnNbr[]='';//data kosong
			$query = "SELECT PRSN_NBR, NAME FROM PEOPLE WHERE CO_NBR=".$CoNbr;
			$result= mysql_query($query);
			while($rowPpl=mysql_fetch_array($result)){
				echo "<th style='text-align:center;'>".$rowPpl['NAME']."</th>";

				$prsnNbr[]=$rowPpl['PRSN_NBR'];
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
		GROUP BY THD.TRNSP_NBR,OHD.ORD_NBR,ORD_TTL,THD.RCV_CO_NBR,BCM.NAME,DUE_TS,TRNSP_STT_DESC
		ORDER BY THD.UPD_TS DESC";
		//echo "<pre>".$query;
		$result=mysql_query($query);
		$firstRow="";
		while($row=mysql_fetch_array($result))
		{
			//Traffic light control
			$due=strtotime($row['DUE_TS']);
			$TrnspSttId=$row['TRNSP_STT_ID'];
			if((strtotime("now")>$due) && ($TrnspSttId != "DL")){
				$dot="<div class='listable' style='display:inline;float:right'><span class='fa fa-circle listable' style='font-size:8pt;color:#d92115'></span></div>";
			}elseif((strtotime("now + ".$row['JOB_LEN_TOT']." minute")>$due) && ($TrnspSttId != "DL")){
				$dot="<div class='listable' style='display:inline;float:right'><span class='fa fa-circle listable' style='font-size:8pt;color:#fbad06'></span></div>";				
			}else{
				$dot="";
			}
			
			//Perform changes in tranport-edit.php as well
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
			if($firstRow==""){$firstRow=$row['TRNSP_NBR'];}
		}
		?>
		</div>

<script>liveReqInit('livesearch','liveRequestResults','retail-transport-ls.php?STT=<?php echo $OrdSttId; ?>','','mainResult');</script>

<script>fdTableSort.init();</script>

<script>
	<?php 
        if($Goto=='TOP'){
            echo "changeSiblingUrl('rightpane','retail-transport-edit.php?TRNSP_NBR=".$firstRow."');";
        }else{
            echo "changeSiblingUrl('rightpane','retail-transport-edit.php?TRNSP_NBR=".$Goto."');";
        }
    ?>
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
	
	<?php if ($_GET['STT'] != "") {?>			
		var $leftMenu = jQuery(
			parent.parent.document.getElementById('leftmenu')
		).contents().find(".sub");
		
		$leftMenu.find("div").removeClass("leftmenusel").addClass("leftmenu");
		$leftMenu.find("#retail-shipping").removeClass("leftmenu").addClass("leftmenusel");
	<?php }?>
</script>

</body>
</html>