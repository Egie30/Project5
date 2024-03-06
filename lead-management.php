<?php
	include "framework/functions/default.php"; /*NEW*/
	include "framework/security/default.php";
	date_default_timezone_set("Asia/Jakarta");

	include "framework/database/connect.php";

	$Security=getSecurity($_SESSION['userID'],"AddressBook");
	
	if($Security>=8){
		$where="WHERE ACCT_EXEC_NBR='".$_SESSION['userID']."' AND PPL.CO_NBR='".$CoNbrDef."'";
	}else{
		$where="WHERE ACCT_EXEC_NBR IS NOT NULL AND PPL.CO_NBR='".$CoNbrDef."'";
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<script type="text/javascript" src="framework/functions/default.js"></script>

<script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
<style type="text/css">
	.ellip{
		white-space: nowrap; 
    	width: 12em; 
    	overflow: hidden;
    	text-overflow: ellipsis; 
	}

</style>

</head>

<body>

<div class="toolbar">
	<p class="toolbar-right"><span class='fa fa-search fa-flip-horizontal toolbar' style='cursor:default'></span><input type="text" id="livesearch" class="livesearch" /></p>
</div>
<div style='clear:both;height:10px'></div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult" style='height:calc(100% - 55px);-webkit-overflow-scrolling:touch !important;overflow-y:scroll !important'>
	<?php
		$query="SELECT 
				COM.CO_NBR,
				COM.NAME,
				CONCAT(COM.ADDRESS,', ',CITY_NM) AS ADDR,
				COM.PHONE,PPL.NAME AS ACCT_EXEC_NAME,
				STG_DESC,
				ACT_DESC,
				RAT_DESC,
				ACT_NTE,
				DET.UPD_TS 
			FROM CMP.COMPANY COM 
				INNER JOIN CMP.CITY CTY ON COM.CITY_ID=CTY.CITY_ID 
				INNER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR=COM.ACCT_EXEC_NBR 
				LEFT OUTER JOIN ( 
					SELECT 
						LED.CO_NBR,
						LED.LEAD_NBR,
						STG_DESC,
						ACT_DESC,
						RAT_DESC,
						ACT_TS,
						ACT_NTE,
						UPD_TS 
					FROM CMP.LEAD_DET LED 
						INNER JOIN CMP.LEAD_STG STG ON LED.LEAD_STG=STG.STG_TYP 
						INNER JOIN CMP.LEAD_ACT ACT ON LED.LEAD_ACT=ACT.ACT_TYP 
						INNER JOIN CMP.LEAD_RAT RAT ON LED.LEAD_RAT=RAT.RAT_TYP 
						INNER JOIN (SELECT CO_NBR,MAX(LEAD_NBR) AS LEAD_NBR 
							FROM CMP.LEAD_DET WHERE DEL_NBR=0 GROUP BY CO_NBR
						) LST ON LED.LEAD_NBR=LST.LEAD_NBR 
				) DET ON COM.CO_NBR=DET.CO_NBR
			$where ORDER BY 2
		";
		
		$result=mysql_query($query);
		//$alt="class='tripane-list-alt'";
		$firstRow="";
		while($row=mysql_fetch_array($result)){
			echo "<div id='O".($row['CO_NBR'])."' class='tripane-list' onclick=".chr(34)."changeSiblingUrl('rightpane','lead-management-act.php?CO_NBR=".$row['CO_NBR']."');selLeftPane(this);".chr(34);
			if($firstRow==""){
				echo "style='background-color:#eef8fb'";
			}
			echo ">";
			
			//$back="";
			if($row['RAT_DESC']=='Hot'){
				$dot="<div class='listable' style='display:inline;float:right'><span class='fa fa-circle listable' style='font-size:8pt;color:#d92115'></span></div>";
			}elseif($row['RAT_DESC']=='Warm'){
				$dot="<div class='listable' style='display:inline;float:right'><span class='fa fa-circle listable' style='font-size:8pt;color:#fbad06'></span></div>";
			}elseif($row['RAT_DESC']=='Cold'){
				$dot="";
			}

			echo "<div  style='font-weight:bold;color:#666666;font-size:12pt;display:inline;float:left'>".$row['CO_NBR']."</div>";
			echo "<div style='display:inline;float:right;'>".$row['RAT_DESC']."</div>";
			echo "<div style='clear:both'></div>";
			echo $dot;
			//echo "<div style='width:75%'>";
			echo "<div class='ellip' style='width:85%; font-weight:700;color:#3464bc;display:inline;float:left;padding-right:9px;'>".$row['NAME']."</div>";
			echo "<div style='clear:both'></div>";
			if($row['STG_DESC']!=''){
				echo "<div>".$row['STG_DESC']."</div>";
			}
			//echo "</div>";
			
			echo "<div>".trim($row['ADDR']." ".$row['PHONE'])."</div>";
			echo "<div style='clear:both'></div>";
			echo "<div>".parseDateShort($row['UPD_TS'])."&nbsp;";
			echo "<span style='font-weight:700'>".$row['ACT_DESC']."</span>";
			echo "</div>";
			echo "<div style='text-align: right;'>".$row['ACCT_EXEC_NAME']."</div>";
			echo "</div>";
			//if($alt=="class='tripane-list-alt'"){$alt="class='tripane-list'";}else{$alt="class='tripane-list-alt'";}
			if($firstRow==""){$firstRow=$row['CO_NBR'];}
		}
	?>
</div>

<script>liveReqInit('livesearch','liveRequestResults','lead-management-ls.php','','mainResult');</script>

<script>fdTableSort.init();</script>

<script>
	$(document).ready(function(){
		$('.tablesorter-childRow td').hide();
		$("#mainTable").tablesorter({widgets:["zebra"],cssChildRow:"tablesorter-childRow"});
		$('.tablesorter').delegate('.toggle','click',function(){
	   		$(this).closest('tr').nextUntil('tr:not(.tablesorter-childRow)').find('td').toggle();
    		return false;
  		});
	});
</script>

</body>
</html>

