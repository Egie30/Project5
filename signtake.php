<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	date_default_timezone_set("Asia/Jakarta");
	
	//security
	$Security 	= getSecurity($_SESSION['userID'],"Finance");
	//Process filter
	$OrdSttId 	= $_GET['STT'];
    $PrsnNbr 	= $_GET['PRSN_NBR'];
	//Process auto detail display
	$Goto 		= $_GET['GOTO'];

	//Process delete entry
	$delete 	= false;
	if($_GET['DEL']!="")
	{
		$query 		= "UPDATE CMP.TRNSP_HEAD SET DEL_NBR=".$_SESSION['personNBR']." WHERE TRNSP_NBR=".$_GET['DEL'];
		//echo $query;
		$result 	= mysql_query($query);
		$OrdSttId 	= "ST";
		$delete 	= true;
	}
	//Get active order parameter
	//$activePeriod=getParam("print-digital","period-order-active-month");
	//$badPeriod=getParam("print-digital","period-bad-order-month");
	$activePeriod 	= 3;
	$badPeriod 		= 12;
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
    <p class="toolbar-left"><span class='fa fa-plus toolbar' style='cursor:pointer' onclick="changeSiblingUrl('rightpane','signtake-edit.php?TRNSP_NBR=0');deSelLeftPane(this);"></span></p>
    <p class="toolbar-right" style='margin-right:100px'><span class='fa fa-search fa-flip-horizontal toolbar' src="img/search.png"></span><input type="text" id="livesearch" class="livesearch" /></p>
</div>
<div style='clear:both;height:10px'></div>

<div class="searchresult" id="liveRequestResults" style='height:calc(100% - 45px);-webkit-overflow-scrolling:touch !important;overflow-y:scroll !important'></div>

<div id="mainResult" style='height:calc(100% - 43px);-webkit-overflow-scrolling:touch !important;overflow-y:scroll !important;overflow-x:hidden'>
	<?php
        $query 	= "SELECT 
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
	        		WHERE THD.DEL_NBR=0 AND STT.TRNSP_STT_ID='RP'
	        		GROUP BY THD.TRNSP_NBR
	        		ORDER BY THD.TRNSP_NBR DESC";
		$result = mysql_query($query);
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
</div>

<script>liveReqInit('livesearch','liveRequestResults','signtake-ls.php','','mainResult');</script>

<script>fdTableSort.init();</script>

<script>
	<?php 
        if($Goto=='TOP'){
            echo "changeSiblingUrl('rightpane','signtake-edit.php?TRNSP_NBR=".$firstRow."');";
        }else{
            echo "changeSiblingUrl('rightpane','signtake-edit.php?TRNSP_NBR=".$Goto."');";
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

</script>

</body>
</html>