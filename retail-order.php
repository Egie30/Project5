<?php
include "framework/database/connect.php";
include "framework/functions/default.php";
include "framework/security/default.php";
date_default_timezone_set("Asia/Jakarta");

$Security	= getSecurity($_SESSION['userID'],"Finance");
$IvcTyp		= $_GET['IVC_TYP'];
$type		= $_GET['TYP'];
$OrdSttId	= $_GET['STT'];
$Goto		= $_GET['GOTO'];
$delete		= false;

if($type == "EST"){
	$headtable 	= "RTL.RTL_ORD_HEAD_EST";
	$detailtable= "RTL.RTL_ORD_DET_EST";
}else{
	$headtable 	= "RTL.RTL_ORD_HEAD";
	$detailtable= "RTL.RTL_ORD_DET";
}

if($_GET['DEL']!=""){
	$query		= "UPDATE ". $headtable ." SET DEL_F=".$_SESSION['personNBR']." WHERE ORD_NBR=".$_GET['DEL'];
	$result		= mysql_query($query);
	$OrdSttId	= "ACT";
	$delete		= true;
}

$activePeriod	= 3;
$badPeriod		= 12;
if($IvcTyp != "PR"){
	if($OrdSttId=="ACT"){
		$where="AND ((HED.ORD_STT_ID!='NE') OR (HED.ORD_STT_ID='NE' AND TIMESTAMPADD(MONTH,$activePeriod,ORD_DTE)>=CURRENT_TIMESTAMP)) ";
	}elseif($OrdSttId=="ALL"){
		$where="AND HED.ORD_STT_ID LIKE '%'";
	}elseif($OrdSttId=="CP"){
		$where="AND HED.ORD_STT_ID='CP' AND TIMESTAMPADD(MONTH,$activePeriod,ORD_DTE)>=CURRENT_TIMESTAMP AND HED.DEL_F=0";
	}else{
		$where="AND HED.ORD_STT_ID = '".$OrdSttId."'";
	}
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	<script>parent.Pace.restart();</script>
	<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<script type="text/javascript"  src="framework/database/jquery.min.js"></script>
</head>
<body>

<?php
	if($delete){echo "<script>parent.document.getElementById('leftmenu').contentDocument.location.reload(true);</script>";} 
?>

<div class="toolbar">
	<p class="toolbar-left">
		<span class='fa fa-plus toolbar' style='cursor:pointer' onclick="changeSiblingUrl('rightpane','retail-order-edit.php?IVC_TYP=<?php echo $IvcTyp;?>&TYP=<?php echo $type;?>&ORD_NBR=0');deSelLeftPane(this);"></span>
	</p>
	<p class="toolbar-right">
		<span class='fa fa-search fa-flip-horizontal toolbar' style='cursor:default'></span><input type="text" id="livesearch" class="livesearch" />
	</p>
</div>

<div style='clear:both;height:10px'></div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult" style='height:calc(100% - 55px);-webkit-overflow-scrolling:touch !important;overflow-y:scroll !important'>
	<?php
		$query="SELECT 
			HED.ORD_NBR,
			HED.ORD_DTE,
			ORD_TTL,
			HED.DL_TS,
			RCV_CO_NBR,
			RCV.NAME AS RCV_NAME,
			SHP_CO_NBR,
			SHP.NAME AS SHP_NAME,
			HED.ORD_STT_ID, 
			ORD_STT_DESC,
			TOT_AMT,
			TOT_REM
		FROM ". $headtable ." HED 
			INNER JOIN RTL.ORD_STT STT ON HED.ORD_STT_ID = STT.ORD_STT_ID 
			LEFT OUTER JOIN CMP.COMPANY RCV ON HED.RCV_CO_NBR = RCV.CO_NBR 
			LEFT OUTER JOIN CMP.COMPANY SHP ON HED.SHP_CO_NBR = SHP.CO_NBR 
			LEFT OUTER JOIN ". $detailtable ." DET ON HED.ORD_NBR = DET.ORD_NBR 
		WHERE HED.IVC_TYP = '".$IvcTyp."' AND HED.DEL_F=0 " . $where . "
		GROUP BY HED.ORD_NBR,RCV_CO_NBR,SHP_CO_NBR,HED.ORD_STT_ID 
		ORDER BY HED.UPD_TS DESC";
		//echo "<pre>".$query;
		$result=mysql_query($query);
		$firstRow	= "";
		while($row = mysql_fetch_array($result)){
			
			//Traffic light control
			$due		= strtotime($row['ORD_DTE']);
			$statusID	= $row['ORD_STT_ID'];
			if((strtotime("now")>$due) && ($statusID != "NE")){
				$dot="<div class='listable' style='display:inline;float:right'><span class='fa fa-circle listable' style='font-size:8pt;color:#d92115'></span></div>";
			}elseif((strtotime("now")>$due) && ($statusID != "NE")){
				$dot="<div class='listable' style='display:inline;float:right'><span class='fa fa-circle listable' style='font-size:8pt;color:#fbad06'></span></div>";				
			}else{
				$dot="";
			}
			
			//Perform changes in tranport-edit.php as well
			echo "<div id='O".($row['ORD_NBR'])."' class='tripane-list' onclick=".chr(34)."changeSiblingUrl('rightpane','retail-order-edit.php?IVC_TYP=".$IvcTyp."&TYP=".$type."&ORD_NBR=".$row['ORD_NBR']."');selLeftPane(this);".chr(34);
			if($firstRow==""){
				echo "style='background-color:#eef8fb'";
			}
			echo ">";
			
			echo "<div  style='font-weight:bold;color:#666666;font-size:12pt;display:inline;float:left'>".$row['ORD_NBR']."</div>";
			echo "<div style='display:inline;float:right;'>".parseDateTimeLiteralShort($row['DL_TS'])."</div>";
			echo "<div style='clear:both'></div>";
			if(trim($row['RCV_NAME'])==""){$name="Tunai";}else{$name=trim($row['RCV_NAME']);}
			echo $dot;
			echo "<div style='font-weight:700;color:#3464bc'>".$name."</div>";
			echo "<div>".$row['ORD_TTL']."</div>";
			
			echo "<div>".$row['ORD_DTE']."&nbsp;";
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
			if($firstRow==""){$firstRow=$row['ORD_NBR'];}
		}
		
	?>
</div>
<script>liveReqInit('livesearch','liveRequestResults','retail-order-ls.php?IVC_TYP=<?php echo $IvcTyp;?>&TYP=<?php echo $type;?>&STT=<?php echo $OrdSttId;?>','','mainResult');</script>
<script>
	<?php if($Goto=='TOP'){echo "changeSiblingUrl('rightpane','retail-order-edit.php?IVC_TYP=".$IvcTyp."&TYP=".$type."&ORD_NBR=".$firstRow."');";} ?>
</script>
<script>
<?php 

if($Goto=='TOP'){
if($firstRow==""){$firstRow=0;}
	echo "changeSiblingUrl('rightpane','retail-order-edit.php?IVC_TYP=".$IvcTyp."&TYP=".$type."&ORD_NBR=".$firstRow."');";
}else{
	echo "changeSiblingUrl('rightpane','retail-order-edit.php?IVC_TYP=".$IvcTyp."&TYP=".$type."&ORD_NBR=".$Goto."');";
}
?>
</script>
</body>
</html>