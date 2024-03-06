<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	
	$security=getSecurity($_SESSION['userID'],"DigitalPrint");

	//Process filter
	$OrdSttId=$_GET['STT'];		
	if($OrdSttId=="ALL"){
		$where="WHERE HED.ORD_STT_ID LIKE '%' AND HED.DEL_NBR=0 AND DET.DEL_NBR = 0";
	}elseif($OrdSttId=="DUE"){
		$where="WHERE TOT_REM>0 AND CMP_TS IS NOT NULL AND DATE_ADD(CMP_TS,INTERVAL COALESCE(PAY_TERM,0) DAY)<=CURRENT_TIMESTAMP AND HED.DEL_NBR=0 AND DET.DEL_NBR = 0";
	}elseif($OrdSttId=="COL"){
		$where="WHERE TOT_REM>0 AND CMP_TS IS NOT NULL AND HED.DEL_NBR=0 AND DET.DEL_NBR = 0";
	}else{
		$where="WHERE HED.ORD_STT_ID='".$OrdSttId."' AND HED.DEL_NBR=0 AND DET.DEL_NBR = 0";
	}

	//Process equipment
	$orderEquipment	= $_GET['EQP'];
	$equipment		= str_replace(",","','",$orderEquipment);	
	if($equipment != ""){
		$where.=" AND EQP.PRN_DIG_EQP IN ('".$equipment."') AND DET.DEL_NBR = 0";
	}
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">

<script type="text/javascript" src="framework/functions/default.js"></script>
<script type="text/javascript" src="framework/database/jquery.min.js"></script>
<script>
	$(function() {
		$(".meter > span").each(function() {
			$(this)
				.data("origWidth", $(this).width())
				.width(0)
				.animate({
					width: $(this).data("origWidth")
				}, 1200);
		});
	});
</script>
	
<style>
	.meter { 
		height: 12px;  /* Can be anything */
		position: relative;
		margin: 0px 0px 1px 0px; /* Just for demo spacing */
		background: #eee;
		-moz-border-radius: 2px;
		-webkit-border-radius: 2px;
		border-radius: 2px;
		//border:1px solid #bbb;
		padding: 0px;
		//-webkit-box-shadow: inset 0 -1px 1px rgba(255,255,255,0.3);
		//-moz-box-shadow   : inset 0 -1px 1px rgba(255,255,255,0.3);
		//box-shadow        : inset 0 -1px 1px rgba(255,255,255,0.3);
	}
	.meter > span {
		display: block;
		height: 100%;
		   -webkit-border-top-right-radius: 2px;
		-webkit-border-bottom-right-radius: 2px;
		       -moz-border-radius-topright: 2px;
		    -moz-border-radius-bottomright: 2px;
		           border-top-right-radius: 2px;
		        border-bottom-right-radius: 2px;
		    -webkit-border-top-left-radius: 2px;
		 -webkit-border-bottom-left-radius: 2px;
		        -moz-border-radius-topleft: 2px;
		     -moz-border-radius-bottomleft: 2px;
		            border-top-left-radius: 2px;
		         border-bottom-left-radius: 2px;
		background-color: #12c44f;
		//background-image: -webkit-gradient(
		//  linear,
		//  left bottom,
		//  left top,
		//  color-stop(0, rgb(43,194,83)),
		//  color-stop(1, rgb(84,240,84))
		// );
		//background-image: -moz-linear-gradient(
		//  center bottom,
		//  rgb(43,194,83) 37%,
		//  rgb(84,240,84) 69%
		// );
		//-webkit-box-shadow: 
		//  inset 0 2px 9px  rgba(255,255,255,0.3),
		//  inset 0 -2px 6px rgba(0,0,0,0.4);
		//-moz-box-shadow: 
		//  inset 0 2px 9px  rgba(255,255,255,0.3),
		//  inset 0 -2px 6px rgba(0,0,0,0.4);
		//box-shadow: 
		//  inset 0 2px 9px  rgba(255,255,255,0.3),
		//  inset 0 -2px 6px rgba(0,0,0,0.4);
		position: relative;
		overflow: hidden;
	}
	.meter > span:after, .animate > span > span {
		content: "";
		position: absolute;
		top: 0; left: 0; bottom: 0; right: 0;
		//background-image: 
		//   -webkit-gradient(linear, 0 0, 100% 100%, 
		//      color-stop(.25, rgba(255, 255, 255, .2)), 
		//      color-stop(.25, transparent), color-stop(.5, transparent), 
		//      color-stop(.5, rgba(255, 255, 255, .2)), 
		//      color-stop(.75, rgba(255, 255, 255, .2)), 
		//      color-stop(.75, transparent), to(transparent)
		//   );
		//background-image: 
		//	-moz-linear-gradient(
		//	  -45deg, 
		//      rgba(255, 255, 255, .2) 25%, 
		//      transparent 25%, 
		//      transparent 50%, 
		//      rgba(255, 255, 255, .2) 50%, 
		//      rgba(255, 255, 255, .2) 75%, 
		//      transparent 75%, 
		//      transparent
		//   );
		z-index: 1;
		-webkit-background-size: 50px 50px;
		-moz-background-size: 50px 50px;
		-webkit-animation: move 2s linear infinite;
		   -webkit-border-top-right-radius: 2px;
		-webkit-border-bottom-right-radius: 2px;
		       -moz-border-radius-topright: 2px;
		    -moz-border-radius-bottomright: 2px;
		           border-top-right-radius: 2px;
		        border-bottom-right-radius: 2px;
		    -webkit-border-top-left-radius: 2px;
		 -webkit-border-bottom-left-radius: 2px;
		        -moz-border-radius-topleft: 2px;
		     -moz-border-radius-bottomleft: 2px;
		            border-top-left-radius: 2px;
		         border-bottom-left-radius: 2px;
		overflow: hidden;
	}
	
	.animate > span:after {
		display: none;
	}
	
	@-webkit-keyframes move {
	    0% {
	       background-position: 0 0;
	    }
	    100% {
	       background-position: 50px 50px;
	    }
	}
	
	.orange > span {
		background-color: #fbad06;
		//background-image: -moz-linear-gradient(top, #f1a165, #f36d0a);
		//background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0, #f1a165),color-stop(1, //#f36d0a));
		//	background-image: -webkit-linear-gradient(#f1a165, #f36d0a); 
	}
		
	.red > span {
		background-color: #d92115;
		//background-image: -moz-linear-gradient(top, #f0a3a3, #f42323);
		//background-image: -webkit-gradient(linear,left top,left bottom,color-stop(0, #f0a3a3),color-stop(1, //#f42323));
		//background-image: -webkit-linear-gradient(#f0a3a3, #f42323);
	}
	
	.nostripes > span > span, .nostripes > span:after {
		-webkit-animation: none;
		background-image: none;
	}
</style>
	
</head>

<body>

<?php if($delete){echo "<script>parent.document.getElementById('leftmenu').contentDocument.location.reload(true);</script>";} ?>

<!-- A little bug with the jquery
<div class="toolbar" style="border-bottom:1px solid #cacbcf;height:22px;">
	<p class="toolbar-right"><img class="toolbar-right" src="img/search.png"><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>
-->

<div id="mainResult">
	<table id="mainTable" class="rowstyle-alt colstyle-alt no-arrow searchTable">
		<tbody>
		<?php
			$query="SELECT HED.ORD_NBR,ORD_TS,HED.ORD_STT_ID,ORD_STT_DESC,BUY_PRSN_NBR,PPL.NAME AS NAME_PPL,COM.NAME AS NAME_CO,BUY_CO_NBR,REF_NBR,ORD_TTL,DUE_TS,PRN_CO_NBR,CMP_TS,PU_TS,SPC_NTE,HED.CRT_TS,HED.CRT_NBR,HED.UPD_TS,HED.UPD_NBR,CMP_TS,DATEDIFF(DATE_ADD(CMP_TS,INTERVAL COALESCE(PAY_TERM,0) DAY),CURRENT_TIMESTAMP) AS PAST_DUE,CASE WHEN SUM(ORD_Q)=0 THEN 1 ELSE SUM(ORD_Q) END AS ORD_Q,SUM(PRN_CMP_Q) AS PRN_CMP_Q,SUM(FIN_CMP_Q) AS FIN_CMP_Q,JOB_LEN_TOT
					FROM CMP.PRN_DIG_ORD_HEAD HED
					LEFT OUTER JOIN CMP.PRN_DIG_ORD_DET DET ON HED.ORD_NBR=DET.ORD_NBR
					LEFT OUTER JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP 
					LEFT OUTER JOIN CMP.PRN_DIG_EQP EQP ON TYP.PRN_DIG_EQP=EQP.PRN_DIG_EQP
					LEFT OUTER JOIN CMP.PRN_DIG_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID
					LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR
					LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR $where
					GROUP BY HED.ORD_NBR,ORD_TS,HED.ORD_STT_ID,ORD_STT_DESC,BUY_PRSN_NBR,PPL.NAME,COM.NAME,BUY_CO_NBR,REF_NBR,ORD_TTL,DUE_TS,PRN_CO_NBR,CMP_TS,PU_TS,SPC_NTE,HED.CRT_TS,HED.CRT_NBR,HED.UPD_TS,HED.UPD_NBR,CMP_TS,DATEDIFF(DATE_ADD(CMP_TS,INTERVAL COALESCE(PAY_TERM,0) DAY),CURRENT_TIMESTAMP)
					ORDER BY HED.DUE_TS ASC, ORD_NBR ASC";
			//echo $query;
			$result=mysql_query($query);
			$alt="";
			while($row=mysql_fetch_array($result))
			{
				//Determine the 'child' rows
				$query="SELECT ORD_DET_NBR,DET.ORD_NBR,DET_TTL,TYP.PRN_DIG_EQP,PRN_DIG_DESC,DET.PRN_DIG_PRC,ORD_Q,FIL_LOC,PRN_LEN,PRN_WID,FEE_MISC,FAIL_CNT,DISC_PCT,DISC_AMT,VAL_ADD_AMT,TOT_SUB,ROLL_F,FIN_BDR_DESC,COALESCE(FIN_BDR_WID,0) AS FIN_BDR_WID,FIN_BDR_DESC,COALESCE(FIN_LOP_WID,0) AS FIN_LOP_WID,GRM_CNT_TOP,GRM_CNT_BTM,GRM_CNT_LFT,GRM_CNT_RGT,COALESCE(PRN_CMP_Q,0) AS PRN_CMP_Q,COALESCE(FIN_CMP_Q,0) AS FIN_CMP_Q,PRFO_F
					FROM CMP.PRN_DIG_ORD_DET DET 
						LEFT OUTER JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP 
						LEFT OUTER JOIN CMP.PRN_DIG_EQP EQP ON TYP.PRN_DIG_EQP=EQP.PRN_DIG_EQP 
						LEFT OUTER JOIN CMP.PRN_DIG_FIN_BDR_TYP BDR ON DET.FIN_BDR_TYP=BDR.FIN_BDR_TYP
					WHERE ORD_NBR=".$row['ORD_NBR']." AND DET.DEL_NBR=0 AND (DET.ORD_DET_NBR_PAR IS NULL OR DET.ORD_DET_NBR_PAR IN
						 (SELECT ORD_DET_NBR FROM CMP.PRN_DIG_ORD_DET WHERE DEL_NBR = 0))";
				$resultd=mysql_query($query);
				$rows=mysql_num_rows($resultd)+1;
				
				//Traffic light control
				$due=strtotime($row['DUE_TS']);
				if(strtotime("now")>$due){
					$back="print-digital-red";
				}elseif(strtotime("now + ".$row['JOB_LEN_TOT']." minute")>$due){
					$back="print-digital-yellow";				
				}else{
					$back="";
				}
				
				//Header
				echo "<tr $alt>";
				echo "<td rowspan='$rows' style='text-align:right;vertical-align:top;background-color:#ffffff;'><span style='font-size:12pt;font-weight:300'>".$row['ORD_NBR']."</span>";
				echo "<br><div class='listable-btn'><span class='fa fa-pencil listable-btn' style='padding-left:1px' onclick=".chr(34)."location.href='print-digital-edit.php?ORD_NBR=".$row['ORD_NBR']."';".chr(34)."></span></div>";
				echo "</td>";
				echo "<td colspan='3'>".$row['ORD_TTL']."</span><br><span style='font-weight:bold;color:#3464bc'>".$row['NAME_PPL']." ".$row['NAME_CO']."</span><br>";
				echo "</td>";
				echo "<td style='text-align:right;vertical-align:middle'><span style='color:#999999;'>Waktu<br>janji</span></td>";
				echo "<td style='vertical-align:top'><div style='width:40px' class='$back'>".parseDateOnly($row['DUE_TS'])."-".parseMonth($row['DUE_TS'])."<br>";
				echo parseHour($row['DUE_TS']).".".parseMinute($row['DUE_TS']);
				echo "</div></td>";
				
				//Progress bar
				echo "<td style='text-align:right;vertical-align:middle;color:#999999;'>P:<br>F:</td>";
				$orderQuantity=$row['ORD_Q'];
				//Making sure the denominator is not zero
				if($orderQuantity==0){$orderQuantity=1;}
				$pctValuePRN=number_format($row['PRN_CMP_Q']/$orderQuantity*100,0);
				if($pctValuePRN<50){
					$barColorPRN="red";
				}elseif($pctValuePRN<100){
					$barColorPRN="orange";
				}else{
					$barColorPRN="";
				}
				$pctValueFIN=number_format($row['FIN_CMP_Q']/$orderQuantity*100,0);
				if($pctValueFIN<50){
					$barColorFIN="red";
				}elseif($pctValueFIN<100){
					$barColorFIN="orange";
				}else{
					$barColorFIN="";
				}
				echo "<td style='vertical-align:middle;'><div id='printProgress".$row['ORD_NBR']."'><div class='meter $barColorPRN' style='width:130px'><span style='width: $pctValuePRN%'></span></div></div><div id='finishingProgress".$row['ORD_NBR']."'><div class='meter $barColorFIN' style='width:130px'><span style='width: $pctValueFIN%'></span></div></div></td>";
				
				echo "</tr>";
				if($alt==""){$alt="class='alt'";}else{$alt="";}

				//Details
				while($rowd=mysql_fetch_array($resultd))
				{
					echo "<tr $alt>";
					echo "<td style='font-size:10pt;font-weight:bold;'>".$rowd['ORD_Q']."</td>";
					if($Eqp==$rowd['PRN_DIG_EQP']){
						echo "<td><div class='print-digital-lightgrey'>".$rowd['ORD_DET_NBR']."</div></td>";
					}else{
						echo "<td><div class='print-digital-white'>".$rowd['ORD_DET_NBR']."</td>";
					}
					if(($rowd['PRN_LEN']!="")&&($rowd['PRN_WID']!="")){$prnDim=" ".$rowd['PRN_LEN']."x".$rowd['PRN_WID'];}else{$prnDim="";}
					echo "<td>".trim($rowd['DET_TTL']." ".$rowd['PRN_DIG_DESC']).$prnDim."<br>";
					echo "<span style='color:#999999;'>Finishing: </span>".$rowd['FIN_BDR_DESC']."&nbsp;&nbsp;";
					echo "<span style='color:#999999;'>P: </span>".$rowd['FIN_BDR_WID']."&nbsp;&nbsp;";					
					echo "<span style='color:#999999;'>K: </span>".$rowd['FIN_LOP_WID']."&nbsp;&nbsp;";					
					if(($rowd['GRM_CNT_TOP']!="")||($rowd['GRM_CNT_BTM']!="")||($rowd['GRM_CNT_LFT']!="")||($rowd['GRM_CNT_RGT']!="")){
						echo "<span style='color:#999999;'>Keling </span>";
						if($rowd['GRM_CNT_TOP']!=""){
							echo "<span style='color:#999999;'>A: </span>".$rowd['GRM_CNT_TOP']."&nbsp;&nbsp;";					
						}
						if($rowd['GRM_CNT_BTM']!=""){
							echo "<span style='color:#999999;'>B: </span>".$rowd['GRM_CNT_BTM']."&nbsp;&nbsp;";					
						}
						if($rowd['GRM_CNT_LFT']!=""){
							echo "<span style='color:#999999;'>KA: </span>".$rowd['GRM_CNT_LFT']."&nbsp;&nbsp;";					
						}
						if($rowd['GRM_CNT_RGT']!=""){
							echo "<span style='color:#999999;'>KI: </span>".$rowd['GRM_CNT_RGT']."&nbsp;&nbsp;";					
						}
					}
					if($rowd['PRFO_F']==1){echo "Perforasi";}
					echo "</td>";
					echo "<td style='text-align:right;color:#999999;white-space:nowrap'>";
					
					//Printing progress
					echo "Printing:<br><div id='printCount".$rowd['ORD_DET_NBR']."' style='display:inline;color:#000000;'>".$rowd['PRN_CMP_Q']."</div>";
					echo "<span style='color:#000000;'>/".$rowd['ORD_Q']."</span>";
					echo "</td><td style='color:#999999;white-space:nowrap'>";
					if(($security<="2")||($rowd['PRN_DIG_EQP']!='FLJ320P')){
						echo "<input id='PRN_CMP_Q".$rowd['ORD_DET_NBR']."' type='text' style='border-radius:3px;-webkit-border-radius:3px;-moz-border-radius:3px;width:40px;' />&nbsp;&nbsp;";
						echo "<div class='listable-btn' style='vertical-align:0px'><span class='fa fa-print listable-btn' style='padding-top:0px;padding-left:0.5px;' onclick=".chr(34)."syncGetContentMini('printCount".$rowd['ORD_DET_NBR']."','print-digital-processing-count.php?ORD_DET_NBR=".$rowd['ORD_DET_NBR']."&CNT_TYP=PRN&ADD_Q='+document.getElementById('PRN_CMP_Q".$rowd['ORD_DET_NBR']."').value);syncGetContentMini('printProgress".$row['ORD_NBR']."','print-digital-processing-chart.php?ORD_NBR=".$row['ORD_NBR']."&CNT_TYP=PRN');".chr(34)."></span></div>";
					}
					echo "</td><td style='text-align:right;color:#999999;white-space:nowrap'>";					
					
					//Finishing progress
					echo "Finishing:<br><div id='finishingCount".$rowd['ORD_DET_NBR']."' style='display:inline;color:#000000;'>".$rowd['FIN_CMP_Q']."</div>";
					echo "<span style='color:#000000;'>/".$rowd['ORD_Q']."</span>";
					echo "</td><td style='color:#999999;white-space:nowrap'>";					
					if(($security<="2")||($rowd['PRN_DIG_EQP']!='FLJ320P')){
						echo "<input id='FIN_CMP_Q".$rowd['ORD_DET_NBR']."' type='text' style='border-radius:3px;-webkit-border-radius:3px;-moz-border-radius:3px;width:40px;' />&nbsp;&nbsp;";
						echo "<div class='listable-btn' style='vertical-align:0px'><span class='fa fa-scissors listable-btn' style='vertical-align:1px;' onclick=".chr(34)."syncGetContentMini('finishingCount".$rowd['ORD_DET_NBR']."','print-digital-processing-count.php?ORD_DET_NBR=".$rowd['ORD_DET_NBR']."&CNT_TYP=FIN&ADD_Q='+document.getElementById('FIN_CMP_Q".$rowd['ORD_DET_NBR']."').value);syncGetContentMini('finishingProgress".$row['ORD_NBR']."','print-digital-processing-chart.php?ORD_NBR=".$row['ORD_NBR']."&CNT_TYP=FIN');".chr(34)."></span></div>";
					}
					echo "<div class='listable-btn' style='vertical-align:0px'><span class='fa fa-barcode listable-btn' onclick=".chr(34)."location.href='print-digital-processing-barcode.php?ORD_DET_NBR=".$rowd['ORD_DET_NBR']."';".chr(34)."></span></div>";
					echo "</td>";
					echo "</tr>";
					if($alt==""){$alt="class='alt'";}else{$alt="";}
				}
			}
		?>
		</tbody>
	</table>
</div>

<script>liveReqInit('livesearch','liveRequestResults','print-digital-processing-ls.php','','mainResult');</script>

<script>fdTableSort.init();</script>

</body>
</html>


