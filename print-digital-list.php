<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	date_default_timezone_set("Asia/Jakarta");
	
	//security
	$Security=getSecurity($_SESSION['userID'],"Finance");
	$Securitys=getSecurity($_SESSION['userID'],"AddressBook");
	
	$year  = $_GET['YEAR'];
	$month = $_GET['MONTH'];
	$buyPrsnNbr = $_GET['BUY_PRSN_NBR'];
	$buyCoNbr   = $_GET['BUY_CO_NBR'];
	
	//Process filter
	$OrdSttId=$_GET['STT'];

	//Process delete entry
	$delete=false;
	if($_GET['DEL']!="")
	{
		$query="UPDATE CMP.PRN_DIG_ORD_HEAD SET DEL_NBR=".$_SESSION['personNBR']." WHERE ORD_NBR=".$_GET['DEL'];
		//echo $query;
		$result=mysql_query($query);

		$query="UPDATE CMP.PRN_DIG_ORD_DET SET DEL_NBR=".$_SESSION['personNBR']." WHERE ORD_NBR=".$_GET['DEL'];
		//echo $query;
		$result=mysql_query($query);		

		$OrdSttId="ACT";
		$delete=true;
	}
	//Get active order parameter
	//$activePeriod=getParam("print-digital","period-order-active-month");
	//$badPeriod=getParam("print-digital","period-bad-order-month");
	$activePeriod=3;
	$badPeriod=12;
	//Continue process filter
	if($OrdSttId=="ALL"){
		$where="WHERE HED.ORD_STT_ID LIKE '%'";
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
	}elseif($OrdSttId=="ACT"){
		$buyPrsnNbr = $_GET['BUY_PRSN_NBR'];
		$buyCoNbr   = $_GET['BUY_CO_NBR'];
		$year       = $_GET['YEAR'];
		$month 		= $_GET['MONTH'];

		if($buyCoNbr != "")
		{
			$whereString = " AND BUY_CO_NBR=".$buyCoNbr;
			if($buyPrsnNbr != "")
			{
				$whereString.=" AND BUY_PRSN_NBR=".$buyPrsnNbr;
			}
		}
		else
		{
			if($buyPrsnNbr!="")
			{
				$whereString=" AND BUY_PRSN_NBR=".$buyPrsnNbr;
			}
		}
		if(($buyPrsnNbr == "0")&&($buyCoNbr == "0"))
		{
			$whereString=" AND (BUY_CO_NBR IS NULL AND BUY_PRSN_NBR IS NULL)";
		}
		 $where="WHERE (HED.ORD_STT_ID!='CP' OR (HED.ORD_STT_ID='CP' AND TIMESTAMPADD(MONTH,$activePeriod,ORD_TS)>=CURRENT_TIMESTAMP) OR (TOT_REM>0 AND TIMESTAMPADD(MONTH,$badPeriod,ORD_TS)>=CURRENT_TIMESTAMP)) AND HED.DEL_NBR=0";
		//$where="WHERE HED.DEL_NBR=0 ".$whereString." AND YEAR(ORD_TS)=".$year." AND MONTH(ORD_TS)=".$month." AND TOT_REM>0";
	}
	else{
		$where="WHERE HED.ORD_STT_ID='".$OrdSttId."' AND HED.DEL_NBR=0";
	}
	if($_GET['EXPORT']=='XLS'){	
		header("Cache-Control: no-cache, no-store, must-revalidate");  
		header("Content-Type: application/vnd.ms-excel");  
		header("Content-Disposition: attachment; filename=Order_Report.xls");  
	}else{
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<script type="text/javascript" src="framework/functions/default.js"></script>
<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />

<script src="framework/database/jquery.min.js"></script>
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>

</head>

<body>

<?php if($delete){echo "<script>parent.document.getElementById('leftmenu').contentDocument.location.reload(true);</script>";} ?>

<div class="toolbar">
	<p class="toolbar-left">
		<a href="print-digital-edit.php?ORD_NBR=0"><span class='fa fa-plus toolbar' style="cursor:pointer" onclick="location.href="></span></a>
	</p>
	<p class="toolbar-right">
		<?php if($Security<=1 || $Securitys == 1){?>
			<!--
			<a href="print-digital-list.php?STT=ACT&EXPORT=XLS&YEAR=<?php echo $year?>&MONTH=<?php echo $month?>&BUY_PRSN_NBR=<?php echo $buyPrsnNbr?>&BUY_CO_NBR=<?php echo $buyCoNbr?>"><span class='fa fa-file-excel-o toolbar' style="cursor:pointer" onclick="location.href="></span></a>
			-->
			<a title="Export to Excel" href="report-excel.php?RPT_TYP=print-digital-list-excel&STT=ACT&YEAR=<?php echo $_GET['YEAR']; ?>&MONTH=<?php echo $_GET['MONTH']; ?>&BUY_CO_NBR=<?php echo $_GET['BUY_CO_NBR']; ?>&BUY_PRSN_NBR=<?php echo $_GET['BUY_PRSN_NBR']; ?>" target="_blank"><span class='fa fa-file-excel-o toolbar' style="cursor:pointer" onclick="location.href="></span></a>
		<?php }?>
		<span class='fa fa-search fa-flip-horizontal toolbar'></span><input type="text" id="livesearch" class="livesearch" />
	</p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">
<?php
	}
?>
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th class="sortable" style="text-align:right;">No.</th>
				<?php if($_GET['EXPORT']!='XLS'){	?>
				<th class="nosort"></th>
				<?php } ?>
				<th>Judul</th>
				<th>Pemesan</th>
				<th style="width:7%;">Pesan</th>
				<th>Status</th>
				<?php
					if(($OrdSttId!="DUE")&&($OrdSttId!="COL")){
						echo "<th style='width:7%;'>Janji</th>";
					}
				?>
				<th style="width:7%;">Jadi </th>
				<?php
					if(($OrdSttId=="DUE")||($OrdSttId=="COL")){
						echo "<th style='width:7%;'>Jatuh Tempo</th>";
					}
				?>
				<th>Jumlah</th>
				<th>Sisa</th>
			</tr>
		</thead>
		<tbody>
		<?php
			$query="SELECT NBR FROM CDW.PRN_DIG_TOP_CUST";
			$result=mysql_query($query);
			while($row=mysql_fetch_array($result)){
				$TopCusts[]=strval($row['NBR']);
			}
			$query="SELECT HED.ORD_NBR,DL_CNT,PU_CNT,NS_CNT,IVC_PRN_CNT,ORD_TS,HED.ORD_STT_ID,ORD_STT_DESC,BUY_PRSN_NBR,PPL.NAME AS NAME_PPL,COM.NAME AS NAME_CO,BUY_CO_NBR,REF_NBR,ORD_TTL,DUE_TS,JOB_LEN_TOT,PRN_CO_NBR,FEE_MISC,TOT_AMT,PYMT_DOWN,PYMT_REM,TOT_REM,CMP_TS,PU_TS,SPC_NTE,HED.CRT_TS,HED.CRT_NBR,HED.UPD_TS,HED.UPD_NBR,CMP_TS,DATEDIFF(DATE_ADD(CMP_TS,INTERVAL COALESCE(COM.PAY_TERM,0) DAY),CURRENT_TIMESTAMP) AS PAST_DUE
					FROM CMP.PRN_DIG_ORD_HEAD HED
					INNER JOIN CMP.PRN_DIG_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID
					LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR
					LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR $where
					ORDER BY ORD_NBR DESC";
			echo "<pre>".$query;
			$result=mysql_query($query);
			$alt="";
			while($row=mysql_fetch_array($result))
			{
				//Traffic light control
				$due=strtotime($row['DUE_TS']);
				$OrdSttId=$row['ORD_STT_ID'];
				if((strtotime("now")>$due)&&(($OrdSttId=="NE")||($OrdSttId=="RC")||($OrdSttId=="QU")||($OrdSttId=="PR")||($OrdSttId=="FN"))){
					$back="print-digital-red";
				}elseif((strtotime("now + ".$row['JOB_LEN_TOT']." minute")>$due)&&(($OrdSttId=="NE")||($OrdSttId=="RC")||($OrdSttId=="QU")||($OrdSttId=="PR")||($OrdSttId=="FN"))){
					$back="print-digital-yellow";				
				}else{
					$back="";
				}
				//echo $due." ".strtotime("now")." ".strtotime("now + ".$row['JOB_LEN_TOT']." minute")."<br>";
				
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."changeSiblingUrl('content','print-digital-edit.php?BEG=LIST&ORD_NBR=".$row['ORD_NBR']."');".chr(34).">";
				echo "<td style='text-align:right'>".$row['ORD_NBR']."</td>";
				if($_GET['EXPORT']!='XLS'){	
	
				echo "<td style='text-align:left;white-space:nowrap'>";
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
				echo "</td>";
				}
				echo "<td>".$row['ORD_TTL']."</td>";
				echo "<td>".$row['NAME_PPL']." ".$row['NAME_CO']."</td>";
				echo "<td style='text-align:center'>".parseDateShort($row['ORD_TS'])."</td>";
				echo "<td style='text-align:center'>".$row['ORD_STT_DESC']."</td>";
				if(($OrdSttId!="DUE")&&($OrdSttId!="COL")){
					echo "<td style='text-align:center;white-space:nowrap'><div class='$back'>".parseDateShort($row['DUE_TS'])." ".parseHour($row['DUE_TS']).":".parseMinute($row['DUE_TS'])."</div></td>";
				}
				echo "<td style='text-align:center'>".parseDateShort($row['CMP_TS'])."</td>";
				if(($OrdSttId=="DUE")||($OrdSttId=="COL")){
					echo "<td style='text-align:right'>xxx".$row['PAST_DUE']."</td>";
				}
				echo "<td style='text-align:right;'>".number_format($row['TOT_AMT'],0,'.','.')."</td>";
				echo "<td style='text-align:right;'>".number_format($row['TOT_REM'],0,'.','.')."</td>";
				echo "</tr>";
			}
		?>
		</tbody>
	</table>
<?php
if($_GET['EXPORT']=='XLS'){	
exit();
}else{
?>
</div>

<script>
	$(document).ready(function()
		{
			$("#mainTable").tablesorter({ 
			widgets:["zebra"],
			headers: { 
				5: { sorter: 'shortDate'},
				6: { sorter: 'shortDate'},
				7: { sorter: 'shortDate'},
				8: { sorter: 'ipAddress'},
				9: { sorter: 'ipAddress'}
				}
			});  
		}
	);
</script>

<script>liveReqInit('livesearch','liveRequestResults','print-digital-list-ls.php','','mainResult');</script>
</body>
</html>
<?php
}
?>
