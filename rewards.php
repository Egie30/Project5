<?php
	include_once "home-cdw.php";
	include "framework/functions/print-digital.php";
	include "framework/functions/crypt.php";
	
	$QueryParam = "SELECT VAL_R2S, VAL_R2P FROM NST.PARAM_LOC PLC";
	$ResultParam= mysql_query($QueryParam);
	$rowParam   = mysql_fetch_array($ResultParam);
	$R2S		= $rowParam['VAL_R2S'];
	$R2P		= $rowParam['VAL_R2P'];
	
	//Today's Progress
	$query="SELECT 
		SUM(CASE WHEN PRN_DIG_EQP IN ('FLJ320P', 'ATX67') THEN ORD_Q*PRN_WID*PRN_LEN ELSE 0 END) AS FLJ320P,
		SUM(CASE WHEN PRN_DIG_EQP='KMC6501' THEN ORD_Q ELSE 0 END) AS KMC6501,
		SUM(CASE WHEN PRN_DIG_EQP='RVS640' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS RVS640,
		SUM(CASE WHEN PRN_DIG_EQP='AJ1800F' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS AJ1800F,
		SUM(CASE WHEN PRN_DIG_EQP='MVJ1624' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS MVJ1624,
		SUM(CASE WHEN PRN_DIG_EQP='KMC8000' THEN ORD_Q ELSE 0 END) AS KMC8000,
		SUM(CASE WHEN PRN_DIG_EQP='KMC1085' THEN ORD_Q ELSE 0 END) AS KMC1085,
		SUM(CASE WHEN PRN_DIG_EQP='KMC8000' THEN ORD_Q*".$R2S." ELSE 0 END) AS KMC8000_NETT,
		SUM(CASE WHEN PRN_DIG_EQP='KMC1085' THEN ORD_Q* ".$R2P." ELSE 0 END) AS KMC1085_NETT,
		SUM(CASE WHEN PRN_DIG_EQP='HPL375' THEN ORD_Q*PRN_WID*PRN_LEN ELSE 0 END) AS HPL375,
		SUM(CASE WHEN PRN_DIG_EQP='SGH6090' THEN ORD_Q*PRN_WID*PRN_LEN ELSE 0 END) AS SGH6090,
		SUM(CASE WHEN PRN_DIG_EQP='LQ1390' THEN ORD_Q*PRN_WID*PRN_LEN ELSE 0 END) AS LQ1390
	FROM CMP.PRN_DIG_ORD_DET DET 
		INNER JOIN CMP.PRN_DIG_ORD_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
		INNER JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP 
	WHERE DATE(ORD_TS)=CURRENT_DATE AND HED.DEL_NBR=0 AND DET.DEL_NBR=0 AND DET.PRN_DIG_TYP !='PROD'";
	//echo $query;
    $resultp=mysql_query($query);
	$rowp=mysql_fetch_array($resultp);
	$KMC6501_NETT   =$rowp['KMC6501']+$rowp['KMC8000_NETT']+$rowp['KMC1085_NETT'];
	$last['FLJ320P']=$_COOKIE['FLJ320P'];
	$last['RVS640'] =$_COOKIE['RVS640'];
	$last['AJ1800F']=$_COOKIE['AJ1800F'];
	$last['MVJ1624']=$_COOKIE['MVJ1624'];
	$last['KMC6501']=$_COOKIE['KMC6501'];
	$last['KMC8000']=$_COOKIE['KMC8000'];
	$last['KMC1085']=$_COOKIE['KMC1085'];
	$last['KMC6501_NETT']=$_COOKIE['KMC6501_NETT'];
	$last['HPL375']=$_COOKIE['HPL375'];
	$last['SGH6090']=$_COOKIE['SGH6090'];
	$last['LQ1390']=$_COOKIE['LQ1390'];
	setcookie('FLJ320P',intval($rowp['FLJ320P']),strtotime('today 23:59'),'/');
	setcookie('RVS640', intval($rowp['RVS640']), strtotime('today 23:59'),'/');
	setcookie('AJ1800F',intval($rowp['AJ1800F']),strtotime('today 23:59'),'/');
	setcookie('MVJ1624',intval($rowp['MVJ1624']),strtotime('today 23:59'),'/');
	setcookie('KMC6501',intval($rowp['KMC6501']),strtotime('today 23:59'),'/');
	setcookie('KMC8000',intval($rowp['KMC8000']),strtotime('today 23:59'),'/');
	setcookie('KMC1085',intval($rowp['KMC1085']),strtotime('today 23:59'),'/');
	setcookie('KMC6501_NETT',intval($KMC6501_NETT),strtotime('today 23:59'),'/');
	setcookie('HPL375',intval($rowp['HPL375']),strtotime('today 23:59'),'/');
	setcookie('SGH6090',intval($rowp['SGH6090']),strtotime('today 23:59'),'/');
	setcookie('LQ1390',intval($rowp['LQ1390']),strtotime('today 23:59'),'/');
	
	//Processing
	$queryc		= "SELECT 
		SUM(PRN_FLJ320P) AS PRN_FLJ320P, SUM(FIN_FLJ320P) AS FIN_FLJ320P, SUM(RDY_FLJ320P) AS RDY_FLJ320P,
		SUM(PRN_KMC6501) AS PRN_KMC6501, SUM(FIN_KMC6501) AS FIN_KMC6501, SUM(RDY_KMC6501) AS RDY_KMC6501,
		SUM(PRN_RVS640) AS PRN_RVS640, SUM(FIN_RVS640) AS FIN_RVS640, SUM(RDY_RVS640) AS RDY_RVS640,
		SUM(PRN_AJ1800F) AS PRN_AJ1800F, SUM(FIN_AJ1800F) AS FIN_AJ1800F, SUM(RDY_AJ1800F) AS RDY_AJ1800F,
		SUM(PRN_MVJ1624) AS PRN_MVJ1624, SUM(FIN_MVJ1624) AS FIN_MVJ1624, SUM(RDY_MVJ1624) AS RDY_MVJ1624,
		SUM(PRN_HPL375) AS PRN_HPL375, SUM(FIN_HPL375) AS FIN_HPL375, SUM(RDY_HPL375) AS RDY_HPL375,
		SUM(PRN_SGH6090) AS PRN_SGH6090, SUM(FIN_SGH6090) AS FIN_SGH6090, SUM(RDY_SGH6090) AS RDY_SGH6090,
		SUM(PRN_LQ1390) AS PRN_LQ1390, SUM(FIN_LQ1390) AS FIN_LQ1390, SUM(RDY_LQ1390) AS RDY_LQ1390
	FROM(
		SELECT 
			DTE, 
			SUM(PRN_FLJ320P) + SUM(PRN_ATX67) AS PRN_FLJ320P,
			SUM(FIN_FLJ320P) + SUM(FIN_ATX67) AS FIN_FLJ320P, 
			SUM(RDY_FLJ320P) + SUM(RDY_ATX67) AS RDY_FLJ320P,
			SUM(PRN_KMC6501)+SUM(PRN_KMC8000)+SUM(PRN_KMC1085) AS PRN_KMC6501, 
			SUM(FIN_KMC6501)+SUM(FIN_KMC8000)+SUM(FIN_KMC1085) AS FIN_KMC6501 ,
			SUM(RDY_KMC6501)+SUM(RDY_KMC8000)+SUM(RDY_KMC1085) AS RDY_KMC6501,
			SUM(PRN_RVS640) AS PRN_RVS640, SUM(FIN_RVS640) AS FIN_RVS640, SUM(RDY_RVS640) AS RDY_RVS640,
			SUM(PRN_AJ1800F) AS PRN_AJ1800F, SUM(FIN_AJ1800F) AS FIN_AJ1800F, SUM(RDY_AJ1800F) AS RDY_AJ1800F,
			SUM(PRN_MVJ1624) AS PRN_MVJ1624, SUM(FIN_MVJ1624) AS FIN_MVJ1624, SUM(RDY_MVJ1624) AS RDY_MVJ1624,
			SUM(PRN_HPL375) AS PRN_HPL375, SUM(FIN_HPL375) AS FIN_HPL375, SUM(RDY_HPL375) AS RDY_HPL375,
			SUM(PRN_SGH6090) AS PRN_SGH6090, SUM(FIN_SGH6090) AS FIN_SGH6090, SUM(RDY_SGH6090) AS RDY_SGH6090,
			SUM(PRN_LQ1390) AS PRN_LQ1390, SUM(FIN_LQ1390) AS FIN_LQ1390, SUM(RDY_LQ1390) AS RDY_LQ1390
		FROM CDW.PRN_DIG_PROD
		WHERE DTE < CURRENT_DATE - INTERVAL 7 DAY

		UNION ALL

		SELECT 
			ORD_TS,
			SUM(CASE WHEN PRN_DIG_EQP IN ('FLJ320P', 'ATX67')  AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q != ORD_Q AND FIN_CMP_Q != ORD_Q 
				THEN (ROUND(ORD_Q*PRN_WID*PRN_LEN) - ROUND(PRN_CMP_Q*PRN_WID*PRN_LEN)) ELSE 0 END) AS PRN_FLJ320P,
			SUM(CASE WHEN PRN_DIG_EQP IN ('FLJ320P', 'ATX67') AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q = ORD_Q AND FIN_CMP_Q != ORD_Q 
				THEN (ROUND(ORD_Q*PRN_WID*PRN_LEN) - ROUND(FIN_CMP_Q*PRN_WID*PRN_LEN)) ELSE 0 END) AS FIN_FLJ320P,
			SUM(CASE WHEN PRN_DIG_EQP IN ('FLJ320P', 'ATX67') AND ORD_STT_ID IN ('QU','PR','FN','RD') AND ORD_STT_ID NOT IN ('CP') AND ((PRN_CMP_Q=ORD_Q AND FIN_CMP_Q=ORD_Q) OR (ORD_STT_ID = 'RD')) 
				THEN ROUND(ORD_Q*PRN_WID*PRN_LEN) ELSE 0 END) AS RDY_FLJ320P,

			SUM(CASE WHEN PRN_DIG_EQP IN ('KMC6501', 'KMC8000', 'KMC1085') AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q != ORD_Q AND FIN_CMP_Q != ORD_Q 
				THEN (ORD_Q - PRN_CMP_Q) ELSE 0 END) AS PRN_KMC6501,
			SUM(CASE WHEN PRN_DIG_EQP IN ('KMC6501', 'KMC8000', 'KMC1085') AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q = ORD_Q AND FIN_CMP_Q != ORD_Q 
				THEN (ORD_Q - FIN_CMP_Q) ELSE 0 END) AS FIN_KMC6501,
			SUM(CASE WHEN PRN_DIG_EQP IN ('KMC6501', 'KMC8000', 'KMC1085') AND ORD_STT_ID IN ('QU','PR','FN','RD') AND ORD_STT_ID NOT IN ('CP')	AND ((PRN_CMP_Q=ORD_Q AND FIN_CMP_Q=ORD_Q) OR (ORD_STT_ID = 'RD')) THEN ORD_Q ELSE 0 END) AS RDY_KMC6501,

			SUM(CASE WHEN PRN_DIG_EQP='RVS640' AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q != ORD_Q AND FIN_CMP_Q != ORD_Q 
				THEN (ROUND(ORD_Q*PRN_WID*PRN_LEN) - ROUND(PRN_CMP_Q*PRN_WID*PRN_LEN)) ELSE 0 END) AS PRN_RVS640,
			SUM(CASE WHEN PRN_DIG_EQP='RVS640' AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q = ORD_Q AND FIN_CMP_Q != ORD_Q 
				THEN (ROUND(ORD_Q*PRN_WID*PRN_LEN) - ROUND(FIN_CMP_Q*PRN_WID*PRN_LEN)) ELSE 0 END) AS FIN_RVS640,
			SUM(CASE WHEN PRN_DIG_EQP='RVS640' AND ORD_STT_ID IN ('QU','PR','FN','RD') AND ORD_STT_ID NOT IN ('CP') AND ((PRN_CMP_Q=ORD_Q AND FIN_CMP_Q=ORD_Q) OR (ORD_STT_ID = 'RD')) 
				THEN ROUND(ORD_Q*PRN_WID*PRN_LEN) ELSE 0 END) AS RDY_RVS640,

			SUM(CASE WHEN PRN_DIG_EQP='AJ1800F' AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q != ORD_Q AND FIN_CMP_Q != ORD_Q 
				THEN (ROUND(ORD_Q*PRN_WID*PRN_LEN) - ROUND(PRN_CMP_Q*PRN_WID*PRN_LEN)) ELSE 0 END) AS PRN_AJ1800F,
			SUM(CASE WHEN PRN_DIG_EQP='AJ1800F' AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q = ORD_Q AND FIN_CMP_Q != ORD_Q 
				THEN (ROUND(ORD_Q*PRN_WID*PRN_LEN) - ROUND(FIN_CMP_Q*PRN_WID*PRN_LEN)) ELSE 0 END) AS FIN_AJ1800F,
			SUM(CASE WHEN PRN_DIG_EQP='AJ1800F' AND ORD_STT_ID IN ('QU','PR','FN','RD') AND ORD_STT_ID NOT IN ('CP') AND ((PRN_CMP_Q=ORD_Q AND FIN_CMP_Q=ORD_Q) OR (ORD_STT_ID = 'RD')) 
				THEN ROUND(ORD_Q*PRN_WID*PRN_LEN) ELSE 0 END) AS RDY_AJ1800F,	

			SUM(CASE WHEN PRN_DIG_EQP='MVJ1624' AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q != ORD_Q AND FIN_CMP_Q != ORD_Q 
				THEN (ROUND(ORD_Q*PRN_WID*PRN_LEN) - ROUND(PRN_CMP_Q*PRN_WID*PRN_LEN)) ELSE 0 END) AS PRN_MVJ1624,
			SUM(CASE WHEN PRN_DIG_EQP='MVJ1624' AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q = ORD_Q AND FIN_CMP_Q != ORD_Q 
				THEN (ROUND(ORD_Q*PRN_WID*PRN_LEN) - ROUND(FIN_CMP_Q*PRN_WID*PRN_LEN)) ELSE 0 END) AS FIN_MVJ1624,
			SUM(CASE WHEN PRN_DIG_EQP='MVJ1624' AND ORD_STT_ID IN ('QU','PR','FN','RD') AND ORD_STT_ID NOT IN ('CP') AND ((PRN_CMP_Q=ORD_Q AND FIN_CMP_Q=ORD_Q) OR (ORD_STT_ID = 'RD')) 
				THEN ROUND(ORD_Q*PRN_WID*PRN_LEN) ELSE 0 END) AS RDY_MVJ1624,

			SUM(CASE WHEN PRN_DIG_EQP='HPL375' AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q != ORD_Q AND FIN_CMP_Q != ORD_Q 
				THEN (ROUND(ORD_Q*PRN_WID*PRN_LEN) - ROUND(PRN_CMP_Q*PRN_WID*PRN_LEN)) ELSE 0 END) AS PRN_HPL375,
			SUM(CASE WHEN PRN_DIG_EQP='HPL375' AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q = ORD_Q AND FIN_CMP_Q != ORD_Q 
				THEN (ROUND(ORD_Q*PRN_WID*PRN_LEN) - ROUND(FIN_CMP_Q*PRN_WID*PRN_LEN)) ELSE 0 END) AS FIN_HPL375,
			SUM(CASE WHEN PRN_DIG_EQP='HPL375' AND ORD_STT_ID IN ('QU','PR','FN','RD') AND ORD_STT_ID NOT IN ('CP') AND ((PRN_CMP_Q=ORD_Q AND FIN_CMP_Q=ORD_Q) OR (ORD_STT_ID = 'RD')) 
				THEN ROUND(ORD_Q*PRN_WID*PRN_LEN) ELSE 0 END) AS RDY_HPL375,
				
			SUM(CASE WHEN PRN_DIG_EQP='SGH6090' AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q != ORD_Q AND FIN_CMP_Q != ORD_Q 
				THEN (ROUND(ORD_Q*PRN_WID*PRN_LEN) - ROUND(PRN_CMP_Q*PRN_WID*PRN_LEN)) ELSE 0 END) AS PRN_SGH6090,
			SUM(CASE WHEN PRN_DIG_EQP='SGH6090' AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q = ORD_Q AND FIN_CMP_Q != ORD_Q 
				THEN (ROUND(ORD_Q*PRN_WID*PRN_LEN) - ROUND(FIN_CMP_Q*PRN_WID*PRN_LEN)) ELSE 0 END) AS FIN_SGH6090,
			SUM(CASE WHEN PRN_DIG_EQP='SGH6090' AND ORD_STT_ID IN ('QU','PR','FN','RD') AND ORD_STT_ID NOT IN ('CP') AND ((PRN_CMP_Q=ORD_Q AND FIN_CMP_Q=ORD_Q) OR (ORD_STT_ID = 'RD')) 
				THEN ROUND(ORD_Q*PRN_WID*PRN_LEN) ELSE 0 END) AS RDY_SGH6090,
				
			SUM(CASE WHEN PRN_DIG_EQP='LQ1390' AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q != ORD_Q AND FIN_CMP_Q != ORD_Q 
				THEN (ROUND(ORD_Q*PRN_WID*PRN_LEN) - ROUND(PRN_CMP_Q*PRN_WID*PRN_LEN)) ELSE 0 END) AS PRN_LQ1390,
			SUM(CASE WHEN PRN_DIG_EQP='LQ1390' AND ORD_STT_ID IN ('QU','PR','FN') AND PRN_CMP_Q = ORD_Q AND FIN_CMP_Q != ORD_Q 
				THEN (ROUND(ORD_Q*PRN_WID*PRN_LEN) - ROUND(FIN_CMP_Q*PRN_WID*PRN_LEN)) ELSE 0 END) AS FIN_LQ1390,
			SUM(CASE WHEN PRN_DIG_EQP='LQ1390' AND ORD_STT_ID IN ('QU','PR','FN','RD') AND ORD_STT_ID NOT IN ('CP') AND ((PRN_CMP_Q=ORD_Q AND FIN_CMP_Q=ORD_Q) OR (ORD_STT_ID = 'RD')) 
				THEN ROUND(ORD_Q*PRN_WID*PRN_LEN) ELSE 0 END) AS RDY_LQ1390

		FROM CMP.PRN_DIG_ORD_DET DET 
			LEFT JOIN CMP.PRN_DIG_ORD_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
			LEFT JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP 
		WHERE HED.DEL_NBR=0 
			AND DET.DEL_NBR=0
			AND DATE(HED.ORD_TS) >= CURRENT_DATE - INTERVAL 7 DAY
			AND DET.PRN_DIG_TYP !='PROD'
	)NOTA
	";
	$resultc	= mysql_query($queryc);
	$rowc		= mysql_fetch_array($resultc);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    
    <link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />

	<style type="text/css">
		table {
			/* font-family: 'HelveticaNeue-Light', 'Helvetica Neue Light', 'Helvetica Neue', Helvetica, Arial, sans-serif; */
			font-size:10pt;
		}
		.spiffy{
			display:block;
		}
		.spiffy *{
			display:block;
			height:1px;
			overflow:hidden;
			background:#eeeeee;
		}
		.spiffy1{
			border-right:1px solid #f7f7f7;
			padding-right:1px;
			margin-right:3px;
			border-left:1px solid #f7f7f7;
			padding-left:1px;
			margin-left:3px;
			background:#f2f2f2;
		}
		.spiffy2{
			border-right:1px solid #fdfdfd;
			border-left:1px solid #fdfdfd;
			padding:0px 1px;
			background:#f1f1f1;
			margin:0px 1px;
		}
		.spiffy3{
			border-right:1px solid #f1f1f1;
			border-left:1px solid #f1f1f1;
			margin:0px 1px;
		}
		.spiffy4{
			border-right:1px solid #f7f7f7;
			border-left:1px solid #f7f7f7;
		}
		.spiffy5{
			border-right:1px solid #f2f2f2;
			border-left:1px solid #f2f2f2;
		}
		.spiffy_content{
			padding:0px 5px;
			background:#eeeeee;
			text-align:center;
		}
		#KMC6501,#KMC8000,#KMC1085 {
			font-size:9pt;
			color: #666666;
			height:18px;
		}
	</style>
	
	<!-- 1. Add these JavaScript inclusions in the head of your page -->
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
    	<script type="text/javascript" src="framework/slider/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="framework/charts3/js/highcharts.js"></script>
    	<script type="text/javascript" src="framework/charts3/js/highcharts-more.js"></script>
	
	<link rel="stylesheet" href="framework/jgrowl/jquery.jgrowl.min.css" />
	<script src="framework/database/jquery.min.js"></script>
	<script src="framework/jgrowl/jquery.jgrowl.min.js"></script>


	
	<!-- Optional: add a theme file -->
	<!--
		<script type="text/javascript" src="../js/themes/gray.js"></script>
	-->
	<!-- Chart #1 -->
	<?php
	
	$query_grp 		= "SELECT PRM.PARAM_VALUE 
						FROM NST.PARAM PRM
						WHERE PRM.PARAM = 'GRPHC_CRIT_BEG'
						AND CO_NBR = ".$CoNbrDef."";
	$result_grp		= mysql_query($query_grp);
	$row_grp 		= mysql_fetch_array($result_grp);
	$CriticalBegin 	= $row_grp['PARAM_VALUE'];
	
	$query_grp 		= "SELECT PRM.PARAM_VALUE 
						FROM NST.PARAM PRM
						WHERE PRM.PARAM = 'GRPHC_CRIT_END'
						AND CO_NBR = ".$CoNbrDef."";
	$result_grp		= mysql_query($query_grp);
	$row_grp 		= mysql_fetch_array($result_grp);
	$CriticalEnd 	= $row_grp['PARAM_VALUE'];
	
	$query_grp 		= "SELECT PRM.PARAM_VALUE 
						FROM NST.PARAM PRM
						WHERE PRM.PARAM = 'GRPHC_MOD_BEG'
						AND CO_NBR = ".$CoNbrDef."";
	$result_grp		= mysql_query($query_grp);
	$row_grp 		= mysql_fetch_array($result_grp);
	$ModerateBegin	= $row_grp['PARAM_VALUE'];
	
	$query_grp 		= "SELECT PRM.PARAM_VALUE 
						FROM NST.PARAM PRM
						WHERE PRM.PARAM = 'GRPHC_MOD_END'
						AND CO_NBR = ".$CoNbrDef."";
	$result_grp		= mysql_query($query_grp);
	$row_grp 		= mysql_fetch_array($result_grp);
	$ModerateEnd	= $row_grp['PARAM_VALUE'];

	if($UpperSec<4){
		if($_GET['ALL']=='1'){
			$query="SELECT DATE_FORMAT(DTE,'%e-%c') AS ORD_DTE
						  ,DATE_FORMAT(DTE,'%e') AS ORD_DAY
						  ,DATE_FORMAT(DTE,'%c') AS ORD_MONTH
						  ,DATE_FORMAT(DTE,'%Y') AS ORD_YEAR
			              ,REV_ALL/1000000 AS REVENUE,DTE
			        FROM CDW.PRN_DIG_DSH_BRD 
			        WHERE DTE BETWEEN (CURRENT_DATE - INTERVAL 14 WEEK) AND CURRENT_DATE
			        UNION ALL
			        SELECT DATE_FORMAT(CURRENT_DATE,'%e-%c') AS ORD_DTE
						  ,DATE_FORMAT(CURRENT_DATE,'%e') AS ORD_DAY
						  ,DATE_FORMAT(CURRENT_DATE,'%c') AS ORD_MONTH
						  ,DATE_FORMAT(CURRENT_DATE,'%Y') AS ORD_YEAR
			              ,COALESCE((SELECT (SUM(COALESCE(TOT_SUB,0)+COALESCE(TOT_SUB_ADD,0))+MAX(COALESCE(HED.FEE_MISC,0)))/1000000 AS REVENUE
			        FROM CMP.PRN_DIG_ORD_HEAD HED LEFT OUTER JOIN
			             CMP.PRN_DIG_ORD_DET DET ON HED.ORD_NBR=DET.ORD_NBR LEFT OUTER JOIN
	                             (SELECT ORD_DET_NBR,SUM(TOT_SUB) AS TOT_SUB_ADD FROM CMP.PRN_DIG_ORD_VAL_ADD
	                               WHERE DATE(CRT_TS) = CURRENT_DATE
	                               GROUP BY ORD_NBR) VAL ON DET.ORD_DET_NBR=VAL.ORD_DET_NBR 
			        WHERE HED.DEL_NBR=0 AND DET.DEL_NBR=0 AND DET.PRN_DIG_TYP !='PROD' AND DATE(ORD_TS) = CURRENT_DATE) ,0),CURRENT_DATE AS DTE
			        ORDER BY DTE";
			}else{
			$query="SELECT DATE_FORMAT(DTE,'%e-%c') AS ORD_DTE
						  ,DATE_FORMAT(DTE,'%e') AS ORD_DAY
						  ,DATE_FORMAT(DTE,'%c') AS ORD_MONTH
						  ,DATE_FORMAT(DTE,'%Y') AS ORD_YEAR
			              ,REV/1000000 AS REVENUE,DTE
			        FROM CDW.PRN_DIG_DSH_BRD 
			        WHERE DTE BETWEEN (CURRENT_DATE - INTERVAL 14 WEEK) AND CURRENT_DATE
			        UNION ALL
			        SELECT DATE_FORMAT(CURRENT_DATE,'%e-%c') AS ORD_DTE
						  ,DATE_FORMAT(CURRENT_DATE,'%e') AS ORD_DAY
						  ,DATE_FORMAT(CURRENT_DATE,'%c') AS ORD_MONTH
						  ,DATE_FORMAT(CURRENT_DATE,'%Y') AS ORD_YEAR
			              ,COALESCE((SELECT (SUM(COALESCE(TOT_SUB,0)+COALESCE(TOT_SUB_ADD,0))+MAX(COALESCE(HED.FEE_MISC,0)))/1000000 AS REVENUE
			        FROM CMP.PRN_DIG_ORD_HEAD HED LEFT OUTER JOIN
			             CMP.PRN_DIG_ORD_DET DET ON HED.ORD_NBR=DET.ORD_NBR LEFT OUTER JOIN
	                             (SELECT ORD_DET_NBR,SUM(TOT_SUB) AS TOT_SUB_ADD FROM CMP.PRN_DIG_ORD_VAL_ADD
	                               WHERE DATE(CRT_TS) = CURRENT_DATE
	                               GROUP BY ORD_NBR) VAL ON DET.ORD_DET_NBR=VAL.ORD_DET_NBR 
			        WHERE HED.DEL_NBR=0 AND DET.DEL_NBR=0 AND DET.PRN_DIG_TYP !='PROD' AND (BUY_CO_NBR IS NULL OR BUY_CO_NBR NOT IN ($CoEx)) AND DATE(ORD_TS) = CURRENT_DATE) ,0) REVENUE,CURRENT_DATE AS DTE
			        ORDER BY DTE";
			} 
            //echo "<pre>".$query;
	$Findiconic = json_decode(simple_crypt(file_get_contents('http://findiconic.nestoronline.com/dashboard-data.php'),'d'));
	//print_r($Findiconic)

            $starttime=microtime(true);
            $result=mysql_query($query);
            $endtime= microtime(true);
            $duration=$endtime-$starttime;
            //echo $duration."<br>";
			//Counter to skip lead day for the moving average calculation
			$leadDay=0;
			foreach($Findiconic->data as $dt){
				if($leadDay==7){
				    $begDay1	= $dt->ORD_DAY;;
				    $begMonth1	= $dt->ORD_MONTH-1;
				    $begYear1	= $dt->ORD_YEAR;
		    	}
				if($leadDay>=7){$dailyRev.= $dt->REVENUE.",";}			
				$avgData[] = $dt->REVENUE;				
				$leadDay++;
			}
			/*
			while($row=mysql_fetch_array($result)){
				if($leadDay==7){
				    $begDay1=$row['ORD_DAY'];
				    $begMonth1=$row['ORD_MONTH']-1;
				    $begYear1=$row['ORD_YEAR'];
		    		}
				if($leadDay>=7){$dailyRev.=$row['REVENUE'].",";}			
				$avgData[]=$row['REVENUE'];				
				$leadDay++;
			}
			*/
			$dailyRev='['.substr($dailyRev,0,strlen($dailyRev)-1);
			$dailyRev.=']';
            //echo $dailyRev;
            //echo $dailyRev;
			//Generate moving average data
			$movAvg='[';
			for($avg=7;$avg<=14*7;$avg++){
				$movAvg.=($avgData[$avg-6]+$avgData[$avg-5]+$avgData[$avg-4]+$avgData[$avg-3]+$avgData[$avg-2]+$avgData[$avg-1]+$avgData[$avg])/7;
				$movAvg.=",";
			}
			$movAvg=substr($movAvg,0,strlen($movAvg)-1);
			$movAvg.=']';
			//echo $movAvg;
		}
	?>
	<!-- Chart #1 Retail -->
	<?php
		if($UpperSec<4){
			$query="SELECT DATE_FORMAT(DTE,'%e-%c') AS ORD_DTE
						  ,DATE_FORMAT(DTE,'%e') AS ORD_DAY
						  ,DATE_FORMAT(DTE,'%c') AS ORD_MONTH
						  ,DATE_FORMAT(DTE,'%Y') AS ORD_YEAR
			              ,(REV_RETAIL+REV_ORD)/1000000 AS REVENUE,DTE
			        FROM CDW.PRN_DIG_DSH_BRD 
			        WHERE DTE BETWEEN (CURRENT_DATE - INTERVAL 14 WEEK) AND CURRENT_DATE
			        UNION ALL
			        SELECT 
						DATE_FORMAT(CURRENT_DATE,'%e-%c') AS ORD_DTE,
						DATE_FORMAT(CURRENT_DATE,'%e') AS ORD_DAY,
						DATE_FORMAT(CURRENT_DATE,'%c') AS ORD_MONTH,
						DATE_FORMAT(CURRENT_DATE,'%Y') AS ORD_YEAR,
						COALESCE(SUM(CSH.TND_AMT)+SUM(ORD.REVENUE),0)/1000000 REVENUE,
						CURRENT_DATE AS DTE
					FROM 
					(
						SELECT
							CURRENT_DATE AS DTE,
							COALESCE(SUM(CSH.TND_AMT),0) AS TND_AMT
						FROM (SELECT CSH.RTL_BRC,
									 SUM(CSH.TND_AMT) AS TND_AMT
								FROM RTL.CSH_REG CSH
								WHERE DATE(CSH.CRT_TS)=CURRENT_DATE 
									AND CSH.RTL_BRC<>'' 
									AND CSH.CSH_FLO_TYP ='RT' 
								GROUP BY CSH.RTL_BRC
						) CSH
						LEFT JOIN RTL.INVENTORY INV 
						ON CSH.RTL_BRC=INV.INV_BCD
						WHERE INV.CAT_NBR <> 9
					) CSH
					LEFT JOIN (
						SELECT 	CURRENT_DATE AS DTE,
								COALESCE((SUM(COALESCE(TOT_SUB,0))+MAX(COALESCE(HED.FEE_MISC,0))),0) AS REVENUE
						FROM RTL.RTL_ORD_HEAD HED 
							LEFT JOIN RTL.RTL_ORD_DET DET ON DET.ORD_NBR=HED.ORD_NBR 
							LEFT JOIN RTL.IVC_TYP TYP ON HED.IVC_TYP=TYP.IVC_TYP 
							WHERE ORD_DTE=CURRENT_DATE 
								  AND HED.DEL_F=0 
								  AND DET.DEL_NBR=0 
								  AND (RCV_CO_NBR IS NULL OR RCV_CO_NBR NOT IN ($CoEx))
							ORDER BY CURRENT_DATE	
					) ORD ON ORD.DTE = CSH.DTE
			        ORDER BY DTE";
            $starttime=microtime(true);
            $result=mysql_query($query);
            $endtime= microtime(true);
            $duration=$endtime-$starttime;
            //echo $duration."<br>";
			//echo $query;
			//Counter to skip lead day for the moving average calculation
			$leadDayR=0;
			while($row=mysql_fetch_array($result)){
				if($leadDayR==7){
				    $begDay1R=$row['ORD_DAY'];
				    $begMonth1R=$row['ORD_MONTH']-1;
				    $begYear1R=$row['ORD_YEAR'];
		    	}
				if($leadDayR>=7){$dailyRevR.=$row['REVENUE'].",";}			
				$avgDataR[]=$row['REVENUE'];				
				$leadDayR++;
			}
			$dailyRevR='['.substr($dailyRevR,0,strlen($dailyRevR)-1);
			$dailyRevR.=']';
            //echo $dailyRevR;
	
			//Generate moving average data
			$movAvgR='[';
			for($avgR=7;$avgR<=14*7;$avgR++){
				$movAvgR.=($avgDataR[$avgR-6]+$avgDataR[$avgR-5]+$avgDataR[$avgR-4]+$avgDataR[$avgR-3]+$avgDataR[$avgR-2]+$avgDataR[$avgR-1]+$avgDataR[$avgR])/7;
				$movAvgR.=",";
			}
			$movAvgR=substr($movAvgR,0,strlen($movAvgR)-1);
			$movAvgR.=']';
		}
	?>
	
    <!-- Chart #2 -->
	<?php
		if($UpperSec<4){
		if($_GET['ALL']=='1'){
			$query="SELECT ORD_MONTH, ORD_YEAR, ORD_MONTH_NM, AVG(REVENUE) AS REV_AVG, SUM(REVENUE) AS REV_SUM, SUM(TOT_REM) AS TOT_REM,COUNT(ORD_DAY) AS DAY_NBR 
			          FROM (
					        SELECT DATE_FORMAT(DTE,'%e') AS ORD_DAY
						          ,DATE_FORMAT(DTE,'%c') AS ORD_MONTH
						          ,DATE_FORMAT(DTE,'%Y') AS ORD_YEAR
								  ,DATE_FORMAT(DTE,'%b') AS ORD_MONTH_NM
			                      ,REV_ALL/1000000 AS REVENUE
			                      ,TOT_REM_ALL/1000000 AS TOT_REM
			                  FROM CDW.PRN_DIG_DSH_BRD
	                                 WHERE DTE BETWEEN (DATE_FORMAT(CURRENT_DATE,'%Y-%m-01') - INTERVAL 13 MONTH) AND CURRENT_DATE) DAT GROUP BY ORD_YEAR, ORD_MONTH*1, ORD_MONTH_NM";
			}else{
			$query="SELECT ORD_MONTH, ORD_YEAR, ORD_MONTH_NM, AVG(REVENUE) AS REV_AVG, SUM(REVENUE) AS REV_SUM, SUM(TOT_REM) AS TOT_REM,COUNT(ORD_DAY) AS DAY_NBR 
			          FROM (
					        SELECT DATE_FORMAT(DTE,'%e') AS ORD_DAY
						          ,DATE_FORMAT(DTE,'%c') AS ORD_MONTH
						          ,DATE_FORMAT(DTE,'%Y') AS ORD_YEAR
								  ,DATE_FORMAT(DTE,'%b') AS ORD_MONTH_NM
			                      ,REV/1000000 AS REVENUE
			                      ,TOT_REM/1000000 AS TOT_REM
			                  FROM CDW.PRN_DIG_DSH_BRD
	                                 WHERE DTE BETWEEN (DATE_FORMAT(CURRENT_DATE, '%Y-%m-1') - INTERVAL 14 MONTH) AND CURRENT_DATE) DAT GROUP BY ORD_YEAR, ORD_MONTH*1, ORD_MONTH_NM";
			}
            $starttime=microtime(true);
            $result=mysql_query($query);
            $endtime= microtime(true);
            $duration=$endtime-$starttime;
            //echo $duration."<br>";
            //echo $query;
			$monthlyAvg='[';
			$monthlyTot='[';
			$monthlyRem='[';
			$monthlyEstTot='[';
			$months='[';
			while($row=mysql_fetch_array($result)){
				$monthlyAvg.=$row['REV_AVG'].",";
				$monthlyTot.=$row['REV_SUM'].",";
				$revP[]=$row['REV_SUM'];
				$monthlyRem.=$row['TOT_REM'].",";
				$months.="'".$row['ORD_MONTH_NM']." ".$row['ORD_YEAR']."',";
				$monthlyEstTot.="0,";
				$lastAvg=$row['REV_AVG'];
				$lastMonth=$row['ORD_MONTH'];
				$lastYear=$row['ORD_YEAR'];
				$lastTot=$row['REV_SUM'];
			}
			$monthlyAvg=substr($monthlyAvg,0,strlen($monthlyAvg)-1);
			$monthlyAvg.=']';
			$monthlyTot=substr($monthlyTot,0,strlen($monthlyTot)-1);
			$monthlyTot.=']';
			$monthlyRem=substr($monthlyRem,0,strlen($monthlyRem)-1);
			$monthlyRem.=']';
			$months=substr($months,0,strlen($months)-1);
			$months.=']';
            //echo $monthlyAvg;
			$estTot=cal_days_in_month(CAL_GREGORIAN,$lastMonth,$lastYear)*$lastAvg-$lastTot;
			$monthlyEstTot=substr($monthlyEstTot,0,strlen($monthlyEstTot)-2);
			$monthlyEstTot.=$estTot.']';
			//echo $monthlyEstTot;
            $query="SELECT MONTH(ORD_DTE)*1 AS ORD_MONTH,YEAR(ORD_DTE) AS ORD_YEAR,SUM(TOT_AMT/1000000) AS TOT_AMT, SUM(TOT_REM/1000000) AS TOT_REM FROM RTL.RTL_STK_HEAD WHERE (ORD_DTE BETWEEN (DATE_FORMAT(CURRENT_DATE,'%Y-%m-01') - INTERVAL 14 MONTH) AND CURRENT_DATE) AND DEL_F=0 AND IVC_TYP='RC' GROUP BY YEAR(ORD_DTE),MONTH(ORD_DTE)*1";
			$result=mysql_query($query);
            //echo $query;
			$monthlyPyb='[';
			while($row=mysql_fetch_array($result)){
				$monthlyPyb.=$row['TOT_REM'].",";
            }
			$monthlyPyb=substr($monthlyPyb,0,strlen($monthlyPyb)-1);
			$monthlyPyb.=']';
            //echo $monthlyPyb;
		}
	?>
    <!-- Chart #2 Retail-->
	<?php
		if($UpperSec<4){
			if ($_GET['ALL'] == '1'){
				$query="SELECT ORD_MONTH, ORD_YEAR, ORD_MONTH_NM, AVG(REVENUE) AS REV_AVG, SUM(REVENUE) AS REV_SUM, SUM(TOT_REM) AS TOT_REM,COUNT(ORD_DAY) AS DAY_NBR 
			          FROM (
					        SELECT DATE_FORMAT(DTE,'%e') AS ORD_DAY
						          ,DATE_FORMAT(DTE,'%c') AS ORD_MONTH
						          ,DATE_FORMAT(DTE,'%Y') AS ORD_YEAR
								  ,DATE_FORMAT(DTE,'%b') AS ORD_MONTH_NM
			                      ,(REV_RETAIL + REV_ORD_ALL)/1000000 AS REVENUE
			                      ,TOT_REM_ORD_ALL/1000000 AS TOT_REM
			                  FROM CDW.PRN_DIG_DSH_BRD
	                                 WHERE DTE BETWEEN (DATE_FORMAT(CURRENT_DATE, '%Y-%m-1') - INTERVAL 14 MONTH) AND CURRENT_DATE) DAT GROUP BY ORD_YEAR, ORD_MONTH*1, ORD_MONTH_NM";
			}else{
				$query="SELECT ORD_MONTH, ORD_YEAR, ORD_MONTH_NM, AVG(REVENUE) AS REV_AVG, SUM(REVENUE) AS REV_SUM, SUM(TOT_REM) AS TOT_REM,COUNT(ORD_DAY) AS DAY_NBR 
			          FROM (
					        SELECT DATE_FORMAT(DTE,'%e') AS ORD_DAY
						          ,DATE_FORMAT(DTE,'%c') AS ORD_MONTH
						          ,DATE_FORMAT(DTE,'%Y') AS ORD_YEAR
								  ,DATE_FORMAT(DTE,'%b') AS ORD_MONTH_NM
			                      ,(REV_RETAIL + REV_ORD)/1000000 AS REVENUE
			                      ,TOT_REM_ORD/1000000 AS TOT_REM
			                  FROM CDW.PRN_DIG_DSH_BRD
	                                 WHERE DTE BETWEEN (DATE_FORMAT(CURRENT_DATE, '%Y-%m-1') - INTERVAL 14 MONTH) AND CURRENT_DATE) DAT GROUP BY ORD_YEAR, ORD_MONTH*1, ORD_MONTH_NM";
			}
            $starttime=microtime(true);
            $result=mysql_query($query);
            $endtime= microtime(true);
            $duration=$endtime-$starttime;
            //echo $duration."<br>";
            //echo $query;
			$monthlyAvgR='[';
			$monthlyTotR='[';
			$monthlyRemR='[';
			$monthlyEstTotR='[';
			$monthsR='[';
			while($row=mysql_fetch_array($result)){
				$monthlyAvgR.=$row['REV_AVG'].",";
				$monthlyTotR.=$row['REV_SUM'].",";
				$revR[]=$row['REV_SUM'];
				$monthlyRemR.=$row['TOT_REM'].",";
				$monthsR.="'".$row['ORD_MONTH_NM']." ".$row['ORD_YEAR']."',";
				$monthlyEstTotR.="0,";
				$lastMonthR = $row['ORD_MONTH'];
				$lastYearR  = $row['ORD_YEAR'];
				$lastAvgR   = $row['REV_AVG'];
				$lastTotR   = $row['REV_SUM'];
			}
			$monthlyAvgR=substr($monthlyAvgR,0,strlen($monthlyAvgR)-1);
			$monthlyAvgR.=']';
			$monthlyTotR=substr($monthlyTotR,0,strlen($monthlyTotR)-1);
			$monthlyTotR.=']';
			$monthlyRemR=substr($monthlyRemR,0,strlen($monthlyRemR)-1);
			$monthlyRemR.=']';
			$monthsR=substr($monthsR,0,strlen($monthsR)-1);
			$monthsR.=']';
			
			$estTotR=cal_days_in_month(CAL_GREGORIAN,$lastMonthR,$lastYearR)*$lastAvgR-$lastTotR;
			$monthlyEstTotR=substr($monthlyEstTotR,0,strlen($monthlyEstTotR)-2);
			$monthlyEstTotR.=$estTotR.']';
		}
	?>
	
    <!-- Chart #2 Cafe -->
	<?php
		// if($UpperSec<4){
		// 	$query="SELECT DATE_FORMAT(DTE,'%e-%c') AS ORD_DTE
		// 				  ,DATE_FORMAT(DTE,'%e') AS ORD_DAY
		// 				  ,DATE_FORMAT(DTE,'%c') AS ORD_MONTH
		// 				  ,DATE_FORMAT(DTE,'%Y') AS ORD_YEAR
		// 	              ,REV_CAFE/1000000 AS REVENUE,DTE
		// 	        FROM CDW.PRN_DIG_DSH_BRD 
		// 	        WHERE DTE BETWEEN (CURRENT_DATE - INTERVAL 14 WEEK) AND CURRENT_DATE
		// 	        UNION ALL
		// 	        SELECT 
		// 				DATE_FORMAT(CURRENT_DATE,'%e-%c') AS ORD_DTE,
		// 				DATE_FORMAT(CURRENT_DATE,'%e') AS ORD_DAY,
		// 				DATE_FORMAT(CURRENT_DATE,'%c') AS ORD_MONTH,
		// 				DATE_FORMAT(CURRENT_DATE,'%Y') AS ORD_YEAR,
		// 				COALESCE(SUM(CSH.TND_AMT),0)/1000000 REVENUE,
		// 				CURRENT_DATE AS DTE
		// 			FROM 
		// 			(
		// 				SELECT
		// 					COALESCE(SUM(CSH.TND_AMT),0) AS TND_AMT
		// 				FROM 
														
		// 										(SELECT 
		// 											CSH.RTL_BRC,
		// 											SUM(CSH.TND_AMT) AS TND_AMT
		// 										FROM RTL.CSH_REG CSH
		// 										WHERE DATE(CSH.CRT_TS)=CURRENT_DATE 
		// 											AND CSH.RTL_BRC<>'' 
		// 											AND CSH.CSH_FLO_TYP ='RT' 
		// 										GROUP BY CSH.RTL_BRC
		// 										) CSH
		// 											LEFT JOIN RTL.INVENTORY INV 
		// 												ON CSH.RTL_BRC=INV.INV_BCD
		// 										WHERE INV.CAT_NBR = 9
		// 			) CSH
		// 	        ORDER BY DTE";
  //           $starttime=microtime(true);
  //           $result=mysql_query($query);
  //           $endtime= microtime(true);
  //           $duration=$endtime-$starttime;
  //           //echo $duration."<br>";
			
		// 	$dailyRevRC='[';
		// 	//Counter to skip lead day for the moving average calculation
		// 	$leadDayRC=0;
		// 	while($row=mysql_fetch_array($result)){
		// 		if($leadDayRC==7){
		// 		    $begDay1RC=$row['ORD_DAY'];
		// 		    $begMonth1RC=$row['ORD_MONTH']-1;
		// 		    $begYear1RC=$row['ORD_YEAR'];
		//     	}
		// 		if($leadDayRC>=7){$dailyRevRC.=$row['REVENUE'].",";}			
		// 		$avgDataRC[]=$row['REVENUE'];				
		// 		$leadDayRC++;
		// 	}
		// 	$dailyRevRC=substr($dailyRevRC,0,strlen($dailyRevRC)-1);
		// 	$dailyRevRC.=']';
	
		// 	//Generate moving average data
		// 	$movAvgRC='[';
		// 	for($avgRC=7;$avgRC<=14*7;$avgRC++){
		// 		$movAvgRC.=($avgDataRC[$avgRC-6]+$avgDataRC[$avgRC-5]+$avgDataRC[$avgRC-4]+$avgDataRC[$avgRC-3]+$avgDataRC[$avgRC-2]+$avgDataRC[$avgRC-1]+$avgDataRC[$avgRC])/7;
		// 		$movAvgRC.=",";
		// 	}
		// 	$movAvgRC=substr($movAvgRC,0,strlen($movAvgRC)-1);
		// 	$movAvgRC.=']';
		// }
	?>
        <!-- Chart #3 & #8 -->
	<?php
		if($UpperSec<5){
		
            $query="SELECT 
						DATE_FORMAT(DTE,'%b') AS ORD_MONTH_NM,
						DATE_FORMAT(DTE,'%c') AS ORD_MONTH,
						DATE_FORMAT(DTE,'%Y') AS ORD_YEAR,
						SUM(FLJ320P_ALL) AS FLJ320P,
						SUM(KMC6501_ALL) AS KMC6501,
						SUM(RVS640_ALL)  AS RVS640,
						SUM(AJ1800F_ALL)  AS AJ1800F,
						SUM(MVJ1624_ALL)  AS MVJ1624,
						SUM(KMC8000_ALL) AS KMC8000,
						SUM(KMC1085_ALL) AS KMC1085,
						SUM(KMC6501_ALL) + SUM(KMC8000_ALL * ".$R2S.") + SUM(KMC1085_ALL * ".$R2P.") AS KMCNETT,
						SUM(HPL375_ALL) AS HPL375,
						SUM(SGH6090_ALL) AS SGH6090,
						SUM(LQ1390_ALL) AS LQ1390,
						SUM(REV_FLJ320P_ALL) AS REV_FLJ320P,
						SUM(REV_KMC6501_ALL) AS REV_KMC6501,
						SUM(REV_RVS640_ALL)  AS REV_RVS640,
						SUM(REV_AJ1800F_ALL) AS REV_AJ1800F,
						SUM(REV_MVJ1624_ALL) AS REV_MVJ1624,
						SUM(REV_KMC8000) AS REV_KMC8000,
						SUM(REV_KMC1085) AS REV_KMC1085,
						SUM(REV_HPL375_ALL) AS REV_HPL375,
						SUM(FLJ320P_BON) AS FLJ320P_BON,
						SUM(KMC6501_BON) AS KMC6501_BON,
						SUM(RVS640_BON)  AS RVS640_BON,
						SUM(AJ1800F_BON) AS AJ1800F_BON,
						SUM(MVJ1624_BON) AS MVJ1624_BON,
						SUM(KMC6501_BON) + SUM(KMC8000_BON * ".$R2S.") + SUM(KMC1085_BON * ".$R2P.") AS KMCNETT_BON,
						SUM(HPL375_BON) AS HPL375_BON,
						SUM(SGH6090_BON) AS SGH6090_BON,
						SUM(LQ1390_BON) AS LQ1390_BON
					FROM CDW.PRN_DIG_DSH_BRD
					WHERE DTE BETWEEN (DATE_FORMAT(CURRENT_DATE, '%Y-%m-1') - INTERVAL 14 MONTH) AND CURRENT_DATE 
					GROUP BY DATE_FORMAT(DTE,'%Y'),DATE_FORMAT(DTE,'%c')*1,DATE_FORMAT(DTE,'%b')";
            $starttime=microtime(true);
            $result=mysql_query($query);
            $endtime= microtime(true);
            $duration=$endtime-$starttime;
			
            //echo $duration."<br>";
            //echo $query;
			$monthlyProdFLJ320P='[';
			$monthlyProdKMC6501='[';
			$monthlyProdRVS640='[';
			$monthlyProdAJ1800F='[';
			$monthlyProdMVJ1624='[';
			$monthlyProdKMC8000='[';
			$monthlyProdKMC1085='[';
			$monthlyProdKMCNETT='[';
			$monthlyProdHPL375='[';
			$monthlyProdSGH6090='[';
			$monthlyProdLQ1390='[';
			
			$monthlyProdFLJ320PBon='[';
			$monthlyProdKMC6501Bon='[';
			$monthlyProdRVS640Bon='[';
			$monthlyProdAJ1800FBon='[';
			$monthlyProdMVJ1624Bon='[';
			$monthlyProdKMCNETTBon='[';
			$monthlyProdHPL375Bon='[';
			$monthlyProdSGH6090Bon='[';
			$monthlyProdLQ1390Bon='[';
			
			$monthlyPrcFLJ320P='[';
			$monthlyPrcKMC6501='[';
			$monthlyPrcRVS640='[';
			$monthlyPrcAJ1800F='[';
			$monthlyPrcMVJ1624='[';
			$monthlyPrcHPL375='[';
			$monthlyPrcSGH6090='[';
			$monthlyPrcLQ1390='[';
			
			$months='[';
			while($row=mysql_fetch_array($result)){
				$monthlyProdFLJ320P.=$row['FLJ320P'].",";
				$monthlyProdKMC6501.=$row['KMC6501'].",";
				$monthlyProdRVS640.=$row['RVS640'].",";
				$monthlyProdAJ1800F.=$row['AJ1800F'].",";
				$monthlyProdMVJ1624.=$row['MVJ1624'].",";
				$monthlyProdKMC8000.=$row['KMC8000'].",";
				$monthlyProdKMC1085.=$row['KMC1085'].",";
				$monthlyProdKMCNETT.=$row['KMCNETT'].",";
				$monthlyProdHPL375.=$row['HPL375'].",";
				$monthlyProdSGH6090.=$row['SGH6090'].",";
				$monthlyProdLQ1390.=$row['LQ1390'].",";

				$monthlyProdFLJ320PBon.=$row['FLJ320P_BON'].",";
				$monthlyProdKMC6501Bon.=$row['KMC6501_BON'].",";
				$monthlyProdRVS640Bon.=$row['RVS640_BON'].",";
				$monthlyProdAJ1800FBon.=$row['AJ1800F_BON'].",";
				$monthlyProdMVJ1624Bon.=$row['MVJ1624_BON'].",";
				$monthlyProdKMCNETTBon.=$row['KMCNETT_BON'].",";
				$monthlyProdHPL375Bon.=$row['HPL375_BON'].",";
				$monthlyProdSGH6090Bon.=$row['SGH6090_BON'].",";
				$monthlyProdLQ1390Bon.=$row['LQ1390_BON'].",";
				
				if($row['FLJ320P']==0){$monthlyPrcFLJ320P.="0,";}else{$monthlyPrcFLJ320P.=$row['REV_FLJ320P']/$row['FLJ320P'].",";}
				if($row['KMC6501']==0){$monthlyPrcKMC6501.="0,";}else{$monthlyPrcKMC6501.=$row['REV_KMC6501']/$row['KMC6501'].",";}
				if($row['RVS640']==0){$monthlyPrcRVS640.="0,";}else{$monthlyPrcRVS640.=$row['REV_RVS640']/$row['RVS640'].",";}
				if($row['AJ1800F']==0){$monthlyPrcAJ1800F.="0,";}else{$monthlyPrcAJ1800F.=$row['REV_AJ1800F']/$row['AJ1800F'].",";}
				if($row['MVJ1624']==0){$monthlyPrcMVJ1624.="0,";}else{$monthlyPrcMVJ1624.=$row['REV_MVJ1624']/$row['MVJ1624'].",";}
				if($row['HPL375']==0){$monthlyPrcHPL375.="0,";}else{$monthlyPrcHPL375.=$row['REV_HPL375']/$row['HPL375'].",";}
				if($row['SGH6090']==0){$monthlyPrcSGH6090.="0,";}else{$monthlyPrcSGH6090.=$row['REV_SGH6090']/$row['SGH6090'].",";}
				if($row['LQ1390']==0){$monthlyPrcLQ1390.="0,";}else{$monthlyPrcLQ1390.=$row['REV_LQ1390']/$row['LQ1390'].",";}
				$months.="'".$row['ORD_MONTH_NM']." ".$row['ORD_YEAR']."',";

			}
			$monthlyPrcMVJ1624=substr($monthlyPrcMVJ1624,0,strlen($monthlyPrcMVJ1624)-1);
			$monthlyPrcMVJ1624.=']';
			$monthlyPrcAJ1800F=substr($monthlyPrcAJ1800F,0,strlen($monthlyPrcAJ1800F)-1);
			$monthlyPrcAJ1800F.=']';
			$monthlyPrcRVS640=substr($monthlyPrcRVS640,0,strlen($monthlyPrcRVS640)-1);
			$monthlyPrcRVS640.=']';
			$monthlyPrcKMC6501=substr($monthlyPrcKMC6501,0,strlen($monthlyPrcKMC6501)-1);
			$monthlyPrcKMC6501.=']';
			$monthlyPrcFLJ320P=substr($monthlyPrcFLJ320P,0,strlen($monthlyPrcFLJ320P)-1);
			$monthlyPrcFLJ320P.=']';
			$monthlyPrcHPL375=substr($monthlyPrcHPL375,0,strlen($monthlyPrcHPL375)-1);
			$monthlyPrcHPL375.=']';
			$monthlyPrcSGH6090=substr($monthlyPrcSGH6090,0,strlen($monthlyPrcSGH6090)-1);
			$monthlyPrcSGH6090.=']';
			$monthlyPrcLQ1390=substr($monthlyPrcLQ1390,0,strlen($monthlyPrcLQ1390)-1);
			$monthlyPrcLQ1390.=']';

			$monthlyProdMVJ1624=substr($monthlyProdMVJ1624,0,strlen($monthlyProdMVJ1624)-1);
			$monthlyProdMVJ1624.=']';
			$monthlyProdAJ1800F=substr($monthlyProdAJ1800F,0,strlen($monthlyProdAJ1800F)-1);
			$monthlyProdAJ1800F.=']';
			$monthlyProdRVS640=substr($monthlyProdRVS640,0,strlen($monthlyProdRVS640)-1);
			$monthlyProdRVS640.=']';
			$monthlyProdKMC6501=substr($monthlyProdKMC6501,0,strlen($monthlyProdKMC6501)-1);
			$monthlyProdKMC6501.=']';
			$monthlyProdFLJ320P=substr($monthlyProdFLJ320P,0,strlen($monthlyProdFLJ320P)-1);
			$monthlyProdFLJ320P.=']';
			$monthlyProdKMC8000=substr($monthlyProdKMC8000,0,strlen($monthlyProdKMC8000)-1);
			$monthlyProdKMC8000.=']';
			$monthlyProdKMC1085=substr($monthlyProdKMC1085,0,strlen($monthlyProdKMC1085)-1);
			$monthlyProdKMC1085.=']';
			$monthlyProdKMCNETT=substr($monthlyProdKMCNETT,0,strlen($monthlyProdKMCNETT)-1);
			$monthlyProdKMCNETT.=']';
			$monthlyProdHPL375=substr($monthlyProdHPL375,0,strlen($monthlyProdHPL375)-1);
			$monthlyProdHPL375.=']';
			$monthlyProdSGH6090=substr($monthlyProdSGH6090,0,strlen($monthlyProdSGH6090)-1);
			$monthlyProdSGH6090.=']';
			$monthlyProdLQ1390=substr($monthlyProdLQ1390,0,strlen($monthlyProdLQ1390)-1);
			$monthlyProdLQ1390.=']';
			
			$monthlyProdFLJ320PBon=substr($monthlyProdFLJ320PBon,0,strlen($monthlyProdFLJ320PBon)-1);
			$monthlyProdFLJ320PBon.=']';
			$monthlyProdKMC6501Bon=substr($monthlyProdKMC6501Bon,0,strlen($monthlyProdKMC6501Bon)-1);
			$monthlyProdKMC6501Bon.=']';
			$monthlyProdRVS640Bon=substr($monthlyProdRVS640Bon,0,strlen($monthlyProdRVS640Bon)-1);
			$monthlyProdRVS640Bon.=']';
			$monthlyProdAJ1800FBon=substr($monthlyProdAJ1800FBon,0,strlen($monthlyProdAJ1800FBon)-1);
			$monthlyProdAJ1800FBon.=']';
			$monthlyProdMVJ1624Bon=substr($monthlyProdMVJ1624Bon,0,strlen($monthlyProdMVJ1624Bon)-1);
			$monthlyProdMVJ1624Bon.=']';
			$monthlyProdKMCNETTBon=substr($monthlyProdKMCNETTBon,0,strlen($monthlyProdKMCNETTBon)-1);
			$monthlyProdKMCNETTBon.=']';
			$monthlyProdHPL375Bon=substr($monthlyProdHPL375Bon,0,strlen($monthlyProdHPL375Bon)-1);
			$monthlyProdHPL375Bon.=']';
			$monthlyProdSGH6090Bon=substr($monthlyProdSGH6090Bon,0,strlen($monthlyProdSGH6090Bon)-1);
			$monthlyProdSGH6090Bon.=']';
			$monthlyProdLQ1390Bon=substr($monthlyProdLQ1390Bon,0,strlen($monthlyProdLQ1390Bon)-1);
			$monthlyProdLQ1390Bon.=']';
			
			$months=substr($months,0,strlen($months)-1);
			$months.=']';
		}

	?>
	
	<!-- Chart #4 -->
	<?php
	if($_GET['ALL']=='1'){
		$query="SELECT 	 DATE_FORMAT(DTE,'%e-%c') AS ORD_DTE 
						,DATE_FORMAT(DTE,'%e') AS ORD_DAY 
						,DATE_FORMAT(DTE,'%c') AS ORD_MONTH 
						,DATE_FORMAT(DTE,'%Y') AS ORD_YEAR 
						,FLJ320P_ALL AS FLJ320P 
						,KMC6501_ALL + KMC8000_ALL + KMC1085_ALL AS KMC6501
						,RVS640_ALL AS RVS640
						,AJ1800F_ALL AS AJ1800F
						,MVJ1624_ALL AS MVJ1624
						,HPL375_ALL AS HPL375
						,SGH6090_ALL AS SGH6090
						,LQ1390_ALL AS LQ1390
				FROM CDW.PRN_DIG_DSH_BRD 
				WHERE DTE BETWEEN (CURRENT_DATE - INTERVAL 14 WEEK) 
					  AND CURRENT_DATE GROUP BY DTE 

				UNION ALL 

				SELECT 	 DATE_FORMAT(CURRENT_DATE,'%e-%c') AS ORD_DTE
						,DATE_FORMAT(CURRENT_DATE,'%e') AS ORD_DAY
						,DATE_FORMAT(CURRENT_DATE,'%c') AS ORD_MONTH
						,DATE_FORMAT(CURRENT_DATE,'%Y') AS ORD_YEAR
						,SUM(CASE WHEN PRN_DIG_EQP='FLJ320P' THEN ORD_Q*PRN_WID*PRN_LEN ELSE 0 END) AS FLJ320P
						,SUM(CASE WHEN PRN_DIG_EQP='KMC6501' THEN ORD_Q ELSE 0 END) + SUM(CASE WHEN PRN_DIG_EQP='KMC8000'  THEN ORD_Q ELSE 0 END) + SUM(CASE WHEN PRN_DIG_EQP='KMC1085' THEN ORD_Q ELSE 0 END) AS KMC6501
						,SUM(CASE WHEN PRN_DIG_EQP='RVS640' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS RVS640
						,SUM(CASE WHEN PRN_DIG_EQP='AJ1800F' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS AJ1800F
						,SUM(CASE WHEN PRN_DIG_EQP='MVJ1624' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS MVJ1624
						,SUM(CASE WHEN PRN_DIG_EQP='HPL375' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS HPL375
						,SUM(CASE WHEN PRN_DIG_EQP='SGH6090' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS SGH6090
						,SUM(CASE WHEN PRN_DIG_EQP='LQ1390' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS LQ1390
				FROM CMP.PRN_DIG_ORD_DET DET 
				INNER JOIN CMP.PRN_DIG_ORD_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
				INNER JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP 
				WHERE DATE(ORD_TS)=CURRENT_DATE 
					  AND HED.DEL_NBR=0 
					  AND DET.DEL_NBR=0 
					  AND DET.PRN_DIG_TYP !='PROD'
					  AND DATE(ORD_TS) = CURRENT_DATE
				ORDER BY CURRENT_DATE";
	
		}else{
		$query="SELECT 	 DATE_FORMAT(DTE,'%e-%c') AS ORD_DTE 
						,DATE_FORMAT(DTE,'%e') AS ORD_DAY 
						,DATE_FORMAT(DTE,'%c') AS ORD_MONTH 
						,DATE_FORMAT(DTE,'%Y') AS ORD_YEAR 
						,FLJ320P 
						,KMC6501 + KMC8000 + KMC1085  AS KMC6501
						,RVS640 
						,AJ1800F 
						,MVJ1624 
						,HPL375
						,SGH6090
						,LQ1390
				FROM CDW.PRN_DIG_DSH_BRD 
				WHERE DTE BETWEEN (CURRENT_DATE - INTERVAL 14 WEEK) 
					  AND CURRENT_DATE GROUP BY DTE 

				UNION ALL 

				SELECT 	 DATE_FORMAT(CURRENT_DATE,'%e-%c') AS ORD_DTE
						,DATE_FORMAT(CURRENT_DATE,'%e') AS ORD_DAY
						,DATE_FORMAT(CURRENT_DATE,'%c') AS ORD_MONTH
						,DATE_FORMAT(CURRENT_DATE,'%Y') AS ORD_YEAR
						,SUM(CASE WHEN PRN_DIG_EQP='FLJ320P' THEN ORD_Q*PRN_WID*PRN_LEN ELSE 0 END) AS FLJ320P
						,SUM(CASE WHEN PRN_DIG_EQP='KMC6501' THEN ORD_Q ELSE 0 END) + SUM(CASE WHEN PRN_DIG_EQP='KMC8000'  THEN ORD_Q ELSE 0 END) + SUM(CASE WHEN PRN_DIG_EQP='KMC1085' THEN ORD_Q ELSE 0 END) AS KMC6501
						,SUM(CASE WHEN PRN_DIG_EQP='RVS640' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS RVS640
						,SUM(CASE WHEN PRN_DIG_EQP='AJ1800F' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS AJ1800F
						,SUM(CASE WHEN PRN_DIG_EQP='MVJ1624' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS MVJ1624
						,SUM(CASE WHEN PRN_DIG_EQP='HPL375' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS HPL375
						,SUM(CASE WHEN PRN_DIG_EQP='SGH6090' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS SGH6090
						,SUM(CASE WHEN PRN_DIG_EQP='LQ1390' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS LQ1390
				FROM CMP.PRN_DIG_ORD_DET DET 
				INNER JOIN CMP.PRN_DIG_ORD_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR 
				INNER JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP 
				WHERE DATE(ORD_TS)=CURRENT_DATE 
					  AND HED.DEL_NBR=0 
					  AND DET.DEL_NBR=0 
					  AND DET.PRN_DIG_TYP !='PROD'
					  AND (BUY_CO_NBR IS NULL OR BUY_CO_NBR NOT IN ($CoEx))
					  AND DATE(ORD_TS) = CURRENT_DATE
				ORDER BY CURRENT_DATE";
	}
		$result=mysql_query($query);
        //echo "<pre>".$query;
		$dailyProdFLJ320P='[';
		$dailyProdRVS640='[';
		$dailyProdKMC6501='[';
		$dailyProdAJ1800F='[';
		$dailyProdMVJ1624='[';
		$dailyProdHPL375='[';
		$dailyProdSGH6090='[';
		$dailyProdLQ1390='[';
		$leadDay=0;
		$lastDay="";
		while($row=mysql_fetch_array($result)){
			//Remember the first interval
			if($leadDay>=7){
			    if($begDay==""){$begDay=$row['ORD_DAY'];}
			    //if($begMonth==""){$begMonth=$row['ORD_MONTH']-1.5;}
			    if($begMonth==""){$begMonth=$row['ORD_MONTH']-1;}
			    if($begYear==""){$begYear=$row['ORD_YEAR'];}
		    }
		    
			//Take care of zero-revenue days
			if($lastDay!=""){
				$dayLapse=round((mktime(0,0,0,$row['ORD_MONTH'],$row['ORD_DAY'],$row['ORD_YEAR'])-mktime(0,0,0,$lastMonth,$lastDay,$lastYear))/86400,0)-1;
				//echo $row['ORD_MONTH']."-".$row['ORD_DAY'].":".$dayLapse." ";
				if($dayLapse>0){
					for($day=1;$day<=$dayLapse;$day++){
						if($leadDay>=7){$dailyProdFLJ320P.="0,";}
						if($leadDay>=7){$dailyProdRVS640.="0,";}
						if($leadDay>=7){$dailyProdKMC6501.="0,";}
						if($leadDay>=7){$dailyProdAJ1800F.="0,";}
						if($leadDay>=7){$dailyProdMVJ1624.="0,";}
						if($leadDay>=7){$dailyProdHPL375.="0,";}
						if($leadDay>=7){$dailyProdSGH6090.="0,";}
						if($leadDay>=7){$dailyProdLQ1390.="0,";}
					
						$avgDataFLJ320P[]=0;
						$avgDataRVS640[]=0;
						$avgDataKMC6501[]=0;
						$avgDataAJ1800F[]=0;
						$avgDataMVJ1624[]=0;
						$avgDataHPL375[]=0;
						$avgDataSGH6090[]=0;
						$avgDataLQ1390[]=0;
					}
				}
			}
			$lastDay=$row['ORD_DAY'];
			$lastMonth=$row['ORD_MONTH'];
			$lastYear=$row['ORD_YEAR'];				
			if($leadDay>=7){$dailyProdFLJ320P.=$row['FLJ320P'].",";}		
			if($leadDay>=7){$dailyProdKMC6501.=$row['KMC6501'].",";}		
			if($leadDay>=7){$dailyProdRVS640.=$row['RVS640'].",";}		
			if($leadDay>=7){$dailyProdKMC65C1.=$row['KMC6501'].",";}	
			if($leadDay>=7){$dailyProdAJ1800F.=$row['AJ1800F'].",";}	
			if($leadDay>=7){$dailyProdMVJ1624.=$row['MVJ1624'].",";}
			if($leadDay>=7){$dailyProdHPL375.=$row['HPL375'].",";}
			if($leadDay>=7){$dailyProdSGH6090.=$row['SGH6090'].",";}
			if($leadDay>=7){$dailyProdLQ1390.=$row['LQ1390'].",";}
			
			$avgDataFLJ320P[]=$row['FLJ320P'];	
			$avgDataRVS640[]=$row['RVS640'];	
			$avgDataKMC6501[]=$row['KMC6501'];	
			$avgDataAJ1800F[]=$row['AJ1800F'];		
			$avgDataMVJ1624[]=$row['MVJ1624'];
			$avgDataMVJ1624[]=$row['HPL375'];
			$avgDataSGH6090[]=$row['SGH6090'];
			$avgDataLQ1390[]=$row['LQ1390'];
	
			//Counter to skip lead day for the moving average calculation
			$leadDay++;	
		}
		$dailyProdFLJ320P=substr($dailyProdFLJ320P,0,strlen($dailyProdFLJ320P)-1);
		$dailyProdFLJ320P.=']';
		$dailyProdRVS640=substr($dailyProdRVS640,0,strlen($dailyProdRVS640)-1);
		$dailyProdRVS640.=']';
		$dailyProdKMC6501=substr($dailyProdKMC6501,0,strlen($dailyProdKMC6501)-1);
		$dailyProdKMC6501.=']';
		$dailyProdAJ1800F=substr($dailyProdAJ1800F,0,strlen($dailyProdAJ1800F)-1);
		$dailyProdAJ1800F.=']';
		$dailyProdMVJ1624=substr($dailyProdMVJ1624,0,strlen($dailyProdMVJ1624)-1);
		$dailyProdMVJ1624.=']';
		$dailyProdHPL375=substr($dailyProdHPL375,0,strlen($dailyProdHPL375)-1);
		$dailyProdHPL375.=']';
		$dailyProdSGH6090=substr($dailyProdSGH6090,0,strlen($dailyProdSGH6090)-1);
		$dailyProdSGH6090.=']';
		$dailyProdLQ1390=substr($dailyProdLQ1390,0,strlen($dailyProdLQ1390)-1);
		$dailyProdLQ1390.=']';
		//echo "<pre>".$dailyProdKMC8000."<br/>".$dailyProdKMC1085;
		//Generate moving average data
		$movAvgFLJ320P='[';
		for($avg=8;$avg<=14*7;$avg++){
			$movAvgFLJ320P.=($avgDataFLJ320P[$avg-6]+$avgDataFLJ320P[$avg-5]+$avgDataFLJ320P[$avg-4]+$avgDataFLJ320P[$avg-3]+$avgDataFLJ320P[$avg-2]+$avgDataFLJ320P[$avg-1]+$avgDataFLJ320P[$avg])/7;
			$movAvgFLJ320P.=",";
		}
		$movAvgFLJ320P=substr($movAvgFLJ320P,0,strlen($movAvgFLJ320P)-1);
		$movAvgFLJ320P.=']';
		$movAvgRVS640='[';
		for($avg=8;$avg<=14*7;$avg++){
			$movAvgRVS640.=($avgDataRVS640[$avg-6]+$avgDataRVS640[$avg-5]+$avgDataRVS640[$avg-4]+$avgDataRVS640[$avg-3]+$avgDataRVS640[$avg-2]+$avgDataRVS640[$avg-1]+$avgDataRVS640[$avg])/7;
			$movAvgRVS640.=",";
		}
		$movAvgRVS640=substr($movAvgRVS640,0,strlen($movAvgRVS640)-1);
		$movAvgRVS640.=']';
		$movAvgKMC6501='[';
		for($avg=8;$avg<=14*7;$avg++){
			$movAvgKMC6501.=($avgDataKMC6501[$avg-6]+$avgDataKMC6501[$avg-5]+$avgDataKMC6501[$avg-4]+$avgDataKMC6501[$avg-3]+$avgDataKMC6501[$avg-2]+$avgDataKMC6501[$avg-1]+$avgDataKMC6501[$avg])/7;
			$movAvgKMC6501.=",";
		}
		$movAvgKMC6501=substr($movAvgKMC6501,0,strlen($movAvgKMC6501)-1);
		$movAvgKMC6501.=']';

		$movAJ1800F='[';
		for($avg=8;$avg<=14*7;$avg++){
			$movAvgAJ1800F.=($avgDataAJ1800F[$avg-6]+$avgDataAJ1800F[$avg-5]+$avgDataAJ1800F[$avg-4]+$avgDataAJ1800F[$avg-3]+$avgDataAJ1800F[$avg-2]+$avgDataAJ1800F[$avg-1]+$avgDataAJ1800F[$avg])/7;
			$movAvgAJ1800F.=",";
		}
		$movAvgAJ1800F=substr($movAvgAJ1800F,0,strlen($movAvgAJ1800F)-1);
		$movAvgAJ1800F.=']';

		$movMVJ1624='[';
		for($avg=8;$avg<=14*7;$avg++){
			$movAvgMVJ1624.=($avgDataMVJ1624[$avg-6]+$avgDataMVJ1624[$avg-5]+$avgDataMVJ1624[$avg-4]+$avgDataMVJ1624[$avg-3]+$avgDataMVJ1624[$avg-2]+$avgDataMVJ1624[$avg-1]+$avgDataMVJ1624[$avg])/7;
			$movAvgMVJ1624.=",";
		}
		$movAvgMVJ1624=substr($movAvgMVJ1624,0,strlen($movAvgMVJ1624)-1);
		$movAvgMVJ1624.=']';

		$movHPL375='[';
		for($avg=8;$avg<=14*7;$avg++){
			$movAvgHPL375.=($avgDataHPL375[$avg-6]+$avgDataHPL375[$avg-5]+$avgDataHPL375[$avg-4]+$avgDataHPL375[$avg-3]+$avgDataHPL375[$avg-2]+$avgDataHPL375[$avg-1]+$avgDataHPL375[$avg])/7;
			$movAvgHPL375.=",";
		}
		$movAvgHPL375=substr($movAvgHPL375,0,strlen($movAvgHPL375)-1);
		$movAvgHPL375.=']';		
	?>
	<!-- Chart #5 -->
	<?php
		$vol=0;$volAll=0;$count=0;
		$query="SELECT PRN_DIG_DESC,VOL_ALL
					  ,VOL_LM
				  FROM CDW.PRN_DIG_DSH_BRD_EQP
				 WHERE PRN_DIG_EQP='FLJ320P'
				 ORDER BY VOL_LM DESC";
		$result=mysql_query($query);
		while($row=mysql_fetch_array($result)){
			if($count<8){
				$volTopLMFLJ320P.="['".$row['PRN_DIG_DESC']."',".$row['VOL_LM']."],";
				$volTopAllFLJ320P.="['".$row['PRN_DIG_DESC']."',".$row['VOL_ALL']."],";
			}else{
				$vol+=$row['VOL_LM'];
				$volAll+=$row['VOL_ALL'];
			}
			$count++;
		}
		$volTopLMFLJ320P.="['Other',".$vol."]";
		$volTopAllFLJ320P.="['Other',".$volAll."]";
	?>
	<!-- Chart #6 -->
	<?php
		$vol=0;$volAll=0;$count=0;
		$query="SELECT PRN_DIG_DESC,VOL_ALL
					  ,VOL_LM
				  FROM CDW.PRN_DIG_DSH_BRD_EQP
				 WHERE PRN_DIG_EQP='KMC6501'
				 ORDER BY VOL_LM DESC";
		$result=mysql_query($query);
		while($row=mysql_fetch_array($result)){
			if($count<8){
				$volTopLMKMC6501.="['".$row['PRN_DIG_DESC']."',".$row['VOL_LM']."],";
				$volTopAllKMC6501.="['".$row['PRN_DIG_DESC']."',".$row['VOL_ALL']."],";
			}else{
				$vol+=$row['VOL_LM'];
				$volAll+=$row['VOL_ALL'];
			}
			$count++;
		}
		$volTopLMKMC6501.="['Other',".$vol."]";
		$volTopAllKMC6501.="['Other',".$volAll."]";
		//echo $volTopLMKMC6501;
	?>
	<!-- Chart #7 -->
    <?php
        $query="SELECT
SUM(CASE WHEN DTE BETWEEN (CURRENT_DATE - INTERVAL 1 MONTH) AND CURRENT_DATE THEN TOT_REM+TOT_REM_ORD ELSE 0 END)/1000000 AS MONTH1,
SUM(CASE WHEN DTE BETWEEN (CURRENT_DATE - INTERVAL 2 MONTH) AND (CURRENT_DATE - INTERVAL 1 MONTH) THEN TOT_REM+TOT_REM_ORD ELSE 0 END)/1000000 AS MONTH2,
SUM(CASE WHEN DTE BETWEEN (CURRENT_DATE - INTERVAL 3 MONTH) AND (CURRENT_DATE - INTERVAL 2 MONTH) THEN TOT_REM+TOT_REM_ORD ELSE 0 END)/1000000 AS MONTH3,
SUM(CASE WHEN DTE BETWEEN (CURRENT_DATE - INTERVAL 12 MONTH) AND (CURRENT_DATE - INTERVAL 3 MONTH) THEN TOT_REM+TOT_REM_ORD ELSE 0 END/1000000) AS MONTH12,
SUM(CASE WHEN DTE < (CURRENT_DATE - INTERVAL 12 MONTH) THEN TOT_REM+TOT_REM_ORD ELSE 0 END)/1000000 AS BAD_DEBT
FROM CDW.PRN_DIG_DSH_BRD";
        $result=mysql_query($query);
        $month1='[';
        $month2='[';
        $month3='[';
        $month12='[';
        $badDebt='[';
        while($row=mysql_fetch_array($result)){
            $month1.=$row['MONTH1'].",";
            $month2.=$row['MONTH2'].",";
            $month3.=$row['MONTH3'].",";
            $month12.=$row['MONTH12'].",";
            $badDebt.=$row['BAD_DEBT'].",";
        }
        $query="SELECT
SUM(CASE WHEN ORD_DTE BETWEEN (CURRENT_DATE - INTERVAL 1 MONTH) AND CURRENT_DATE THEN TOT_REM ELSE 0 END)/1000000 AS MONTH1,
SUM(CASE WHEN ORD_DTE BETWEEN (CURRENT_DATE - INTERVAL 2 MONTH) AND (CURRENT_DATE - INTERVAL 1 MONTH) THEN TOT_REM ELSE 0 END)/1000000 AS MONTH2,
SUM(CASE WHEN ORD_DTE BETWEEN (CURRENT_DATE - INTERVAL 3 MONTH) AND (CURRENT_DATE - INTERVAL 2 MONTH) THEN TOT_REM ELSE 0 END)/1000000 AS MONTH3,
SUM(CASE WHEN ORD_DTE BETWEEN (CURRENT_DATE - INTERVAL 12 MONTH) AND (CURRENT_DATE - INTERVAL 3 MONTH) THEN TOT_REM ELSE 0 END)/1000000 AS MONTH12,
SUM(CASE WHEN ORD_DTE < (CURRENT_DATE - INTERVAL 12 MONTH) THEN TOT_REM ELSE 0 END)/1000000 AS BAD_DEBT
FROM RTL.RTL_STK_HEAD WHERE DEL_F=0 AND IVC_TYP='RC'";
        $result=mysql_query($query);
        while($row=mysql_fetch_array($result)){
            $month1.=$row['MONTH1']."]";
            $month2.=$row['MONTH2']."]";
            $month3.=$row['MONTH3']."]";
            $month12.=$row['MONTH12']."]";
            $badDebt.=$row['BAD_DEBT']."]";
        }
    ?>
    
	<!-- Add the JavaScript to initialize the chart on document ready -->
    <script type="text/javascript">
        var chart1;
        var chart1R;
		var chart2;
		var chart2R;
		var chart3;
		var chart3Bon;
		var chart4;
		var chart5;
		var chart6;
		var chart7;
		
		$(document).ready(function() {
            
            Highcharts.setOptions({
                colors: [{linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#54b6ff'],[1, '#1169d8']]},
                         {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#4edd19'],[1, '#009c21']]},
                         {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#fed75c'],[1, '#f9cb1d']]},
                         {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#fd630a'],[1, '#ea1212']]},
                         {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#ab2e96'],[1, '#500a85']]},
                         {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#ed8f1c'],[1, '#a63d00']]},
                         {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#0ace80'],[1, '#008391']]},
                         {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#d2d2d2'],[1, '#b6b6b6']]},
                         {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#747474'],[1, '#242424']]},
                         {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#7d7d7d'],[1, '#303030']]},
                         '#2c83de','#32c028','#F9CB1D','#ea1212','#822694','#cd7115','#08ad90','#b6b6b6','#242424','#575757'],
                chart: {
                    style: {
                        fontFamily: 'San Francisco Display'
                    }
                },
                credits: {
                    enabled: false
                }
            });

			chart1 = new Highcharts.Chart({
				chart: {
					renderTo: 'dailyRev',
					zoomType: 'xy'
				},
				title: {
					text: 'Marketplace 13-Week Revenue Trend'
				},
				subtitle: {
					text: '7-Day Moving Average'
				},
				xAxis: {
			        type: 'datetime',
			        dateTimeLabelFormats: {
			            week: '%e %b'   
			        }
			    },
				yAxis: [{ // Primary yAxis
					min: 0,
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						text: '7-Day Moving Average (millions)',
						style: {
							color: '#666666'
						}
					},
					plotBands: [{
			            from: <?php echo $ModerateBegin; ?>,
			            to: <?php echo $ModerateEnd; ?>,
			            color: 'rgba(200, 200, 200, .2)',
			            label: { text: 'Moderate',
				            style: {
			                  color: '#909090'
   				            }
   				        }
   				    },{
			            from: <?php echo $CriticalBegin; ?>,
			            to: <?php echo $CriticalEnd; ?>,
			            color: 'rgba(122, 186, 218, .2)',
			            label: { text: 'Critical',
				            style: {
			                  color: '#909090'
   				            }
   				        }
			        }]
				}, { // Secondary yAxis
					title: {
						text: 'Daily Revenue (millions)',
						style: {
							color: '#666666'
						}
					},
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					opposite: true
				}],
				tooltip: {
					formatter: function() {
						return ''+
							Highcharts.dateFormat('%e %b %Y', this.x) + '<br/>' + (this.series.name == 'Revenue' ? '' : 'Average ') + 'Revenue: '+  Highcharts.numberFormat(this.y*1000000, 0);
					}
				},
				plotOptions: {
			        series: {
						pointPadding: 0.1,
						borderWidth: 0,
			            groupPadding: 0.01,
						shadow: false
			        }
			    },
				legend: {
					layout: 'vertical',
					align: 'left',
					x: 520,
					verticalAlign: 'top',
					y: 20,
					floating: true,
					backgroundColor: '#FFFFFF'
				},
				series: [{
					name: 'Revenue',
					type: 'column',
					yAxis: 1,
					data: <?php echo $dailyRev; ?>,	
					pointStart: Date.UTC(<?php echo $begYear1; ?>, <?php echo $begMonth1; ?>, <?php echo $begDay1; ?>),
			        pointInterval: 24 * 3600 * 1000 // one day
				}, {
					name: '7-Day Moving Average',
					color: '#8cc152',
					type: 'line',
					data: <?php echo $movAvg; ?>,
					marker: {
                    	enabled: true
                	},
					pointStart: Date.UTC(<?php echo $begYear1; ?>, <?php echo $begMonth1; ?>, <?php echo $begDay1; ?>),
			        pointInterval: 24 * 3600 * 1000 // one day
				}] 
			});

			chart1R = new Highcharts.Chart({
				chart: {
					renderTo: 'dailyRevRetail',
					zoomType: 'xy'
				},
				title: {
					text: 'Goods 13-Week Revenue Trend'
					// text: 'Retail and Café 13-Week Revenue Trend'
				},
				subtitle: {
					text: '7-Day Moving Average'
				},
				xAxis: {
			        type: 'datetime',
			        dateTimeLabelFormats: {
			            week: '%e %b'   
			        }
			    },
				yAxis: [{ // Primary yAxis
					min: 0,
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						text: '7-Day Moving Average (millions)',
						style: {
							color: '#666666'
						}
					}
				}, { // Secondary yAxis
					title: {
						text: 'Daily Revenue (millions)',
						style: {
							color: '#666666'
						}
					},
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					opposite: true
				}],
				tooltip: {
					formatter: function() {
						return ''+
							Highcharts.dateFormat('%e %b %Y', this.x) + '<br/>' + (this.series.name == 'Revenue' ? '' : 'Average ') + 'Revenue: '+  Highcharts.numberFormat(this.y*1000000, 0);
					}
				},
				plotOptions: {
			        series: {
						pointPadding: 0.1,
						borderWidth: 0,
			            groupPadding: 0,
						shadow: false
			        },
					column: {
						stacking: 'normal'
					}
			    },
				legend: {
					layout: 'vertical',
					align: 'left',
					x: 520,
					verticalAlign: 'top',
					y: 20,
					floating: true,
					backgroundColor: '#FFFFFF'
				},
				series: [{
					name: 'Retail Revenue',
					type: 'column',
					yAxis: 1,
					data: <?php echo $dailyRevR; ?>,	
					pointStart: Date.UTC(<?php echo $begYear1; ?>, <?php echo $begMonth1R; ?>, <?php echo $begDay1R; ?>),
			        pointInterval: 24 * 3600 * 1000 // one day
				},
			// 	{
			// 		name: 'Café Revenue',
			// 		type: 'column',
			// 		yAxis: 1,
			// 		data: <?php echo $dailyRevRC; ?>,	
			// 		pointStart: Date.UTC(<?php echo $begYear1; ?>, <?php echo $begMonth1RC; ?>, <?php echo $begDay1RC; ?>),
			//         pointInterval: 24 * 3600 * 1000 // one day
			// 	},
				{
					name: 'Retail 7-Day Moving Average',
					type: 'line',
                    color: '#8cc152',
					data: <?php echo $movAvgR; ?>,
					marker: {
                    	enabled: true
                	},
					pointStart: Date.UTC(<?php echo $begYear1R; ?>, <?php echo $begMonth1R; ?>, <?php echo $begDay1R; ?>),
			        pointInterval: 24 * 3600 * 1000 // one day
				}
			// 	,{
			// 		name: 'Café 7-Day Moving Average',
			// 		type: 'line',
   //                  color: Highcharts.getOptions().colors[13],
			// 		data: <?php echo $movAvgRC; ?>,
			// 		marker: {
   //                  	enabled: true
   //              	},
			// 		pointStart: Date.UTC(<?php echo $begYear1R; ?>, <?php echo $begMonth1R; ?>, <?php echo $begDay1R; ?>),
			//         pointInterval: 24 * 3600 * 1000 // one day
			// 	}
				] 
			});
		
			chart2 = new Highcharts.Chart({
				chart: {
					renderTo: 'monthlyRev',
					defaultSeriesType: 'column',
				},
				title: {
					text: 'Digital Printing Monthly Revenue',
				},
				subtitle: {
					text: 'Average per Working Day',
				},
				xAxis: {
					categories: <?php echo $months; ?>
				},
				yAxis: [{
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						text: 'Average Revenue (millions)',
						style: {
							color: '#666666'
						}
					},
					min:0,
				},{
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						text: 'Total Revenue (millions)',
						style: {
							color: '#666666'
						}
					},
					opposite: true,
					min:0,
				}],
				tooltip: {
					formatter: function() {
						return '<b>'+ this.series.name +'</b><br/>'+
						this.x +': '+ Highcharts.numberFormat(this.y*1000000, 0);
					}
				},
				plotOptions: {
			        series: {
						pointPadding: 0.075,
						borderWidth: 0,
			            groupPadding: 0.15,
						shadow: false
			        },
					column: {
						stacking: 'normal'
					}
			    },
				legend: {
					layout: 'vertical',
					align: 'left',
					verticalAlign: 'top',
					floating: true,
					x: 70,
					y: 25,
					backgroundColor: '#FFFFFF'
				},
				series: [{
					name: 'Average Revenue',
					data: <?php echo $monthlyAvg; ?>,
					stack:'avg'
				},{
					name: 'Estimated Revenue',
                    color: Highcharts.getOptions().colors[7],
					yAxis:1,
					data: <?php echo $monthlyEstTot; ?>,
					stack:'tot',
				},{
					name: 'Total Revenue',
					yAxis:1,
					data: <?php echo $monthlyTot; ?>,
					stack:'tot'
				},{
					name: 'Accounts Receivable',
                    color: Highcharts.getOptions().colors[12],
					yAxis:1,
					type: 'line',
					marker: {
                    	enabled: true
                	},
					data: <?php echo $monthlyRem; ?>
				},{
					name: 'Accounts Payable',
                    color: Highcharts.getOptions().colors[13],
					yAxis:1,
					type: 'line',
					marker: {
                    	enabled: true
                	},
					data: <?php echo $monthlyPyb; ?>
				}]
			});
			
			chart2R = new Highcharts.Chart({
				chart: {
					renderTo: 'monthlyRevRetail',
					defaultSeriesType: 'column',
				},
				title: {
					text: 'Retail Monthly Revenue',
				},
				subtitle: {
					text: 'Average per Working Day',
				},
				xAxis: {
					categories: <?php echo $monthsR; ?>
				},
				yAxis: [{
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						text: 'Average Revenue (millions)',
						style: {
							color: '#666666'
						}
					},
					min:0,
				},{
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						text: 'Total Revenue & Outstanding Balance (millions)',
						style: {
							color: '#666666'
						}
					},
					opposite: true,
					min:0,
				}],
				tooltip: {
					formatter: function() {
						return '<b>'+ this.series.name +'</b><br/>'+
						this.x +': '+ Highcharts.numberFormat(this.y*1000000, 0);
					}
				},
				plotOptions: {
			        series: {
						pointPadding: 0.075,
						borderWidth: 0,
			            groupPadding: 0.15,
						shadow: false
			        },
					column: {
						stacking: 'normal'
					}
			    },
				legend: {
					layout: 'vertical',
					align: 'left',
					verticalAlign: 'top',
					floating: true,
					x: 70,
					y: 25,
					backgroundColor: '#FFFFFF'
				},
				series: [{
					name: 'Average Revenue',
					data: <?php echo $monthlyAvgR; ?>,
					stack:'avg',
				},{
					name: 'Estimated Revenue',
                    color: Highcharts.getOptions().colors[7],
					yAxis:1,
					data: <?php echo $monthlyEstTotR; ?>,
					stack:'tot',
				},{
					name: 'Total Revenue',
					yAxis:1,
 					data: <?php echo $monthlyTotR; ?>,
					stack:'tot',
				},{
					name: 'Outstanding Balance',
					yAxis:1,
					type: 'line',
					marker: {
                    	enabled: true
                	},
					data: <?php echo $monthlyRemR; ?>
				}]
			});
			
			chart3 = new Highcharts.Chart({
				chart: {
					renderTo: 'monthlyProd',
					defaultSeriesType: 'column',
				},
				title: {
					text: 'Net Monthly Production Output',
				},
				subtitle: {
					text: 'By Equipment',
				},
				xAxis: {
					categories: <?php echo $months; ?>
				},
				yAxis: [{
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						text: 'Outdoor / A3+',
						style: {
							color: '#666666'
						}
					},
				},{
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						text: 'Indoor / Fabric',
						style: {
							color: '#666666'
						}
					},
					opposite: true
				}],
				tooltip: {
					formatter: function() {
						return '<b>'+ this.series.name +'</b><br/>'+
						this.x +': '+ Highcharts.numberFormat(this.y, 0);
					}
				},
				legend: {
					layout: 'vertical',
					align: 'left',
					verticalAlign: 'top',
					floating: true,
					x: 70,
					y: 25,
					backgroundColor: '#FFFFFF'
				},
				plotOptions: {
			        series: {
						pointPadding: 0.06,
						borderWidth: 0,
			            groupPadding: 0.12,
						shadow: false
			        }
			    },
				series: [{
					name: 'Outdoor',
					data: <?php echo $monthlyProdFLJ320P; ?>
				},{
					name: 'A3+',
					data: <?php echo $monthlyProdKMCNETT; ?>
				},{
					name: 'Indoor',
					yAxis:1,
					data: <?php echo $monthlyProdRVS640; ?>
				},{
					name: 'Direct Fabric',
					yAxis:1,
					data: <?php echo $monthlyProdAJ1800F; ?>
				},{
					name: 'Heat Transfer',
					yAxis:1,
					data: <?php echo $monthlyProdMVJ1624; ?>
				},{
					name: 'Latex',
					yAxis:1,
					data: <?php echo $monthlyProdHPL375; ?>,
					color: Highcharts.getOptions().colors[9],
				},{
					name: 'UV',
					yAxis:1,
					data: <?php echo $monthlyProdSGH6090; ?>
				},{
					name: 'Laser',
					yAxis:1,
					data: <?php echo $monthlyProdLQ1390; ?>
				}]
			});
					

			chart3Bon = new Highcharts.Chart({
				chart: {
					renderTo: 'monthlyProdBon',
					defaultSeriesType: 'column',
				},
				title: {
					text: 'Monthly Bonus Calculation',
				},
				subtitle: {
					text: 'with Reduction',
				},
				xAxis: {
					categories: <?php echo $months; ?>
				},
				yAxis: [{
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						text: 'Outdoor / A3+',
						style: {
							color: '#666666'
						}
					},
				},{
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						text: 'Indoor / Fabric',
						style: {
							color: '#666666'
						}
					},
					opposite: true
				}],
				tooltip: {
					formatter: function() {
						return '<b>'+ this.series.name +'</b><br/>'+
							this.x +': '+ Highcharts.numberFormat(this.y, 0);
					}
				},
				legend: {
					layout: 'vertical',
					align: 'left',
					verticalAlign: 'top',
					floating: true,
					x: 70,
					y: 25,
					backgroundColor: '#FFFFFF'
				},
				plotOptions: {
					series: {
						pointPadding: 0.06,
						borderWidth: 0,
						groupPadding: 0.12,
						shadow: false
					}
				},
				series: [{
					name: 'Outdoor',
					data: <?php echo $monthlyProdFLJ320PBon; ?>
				},{
					name: 'A3+',
					data: <?php echo $monthlyProdKMCNETTBon; ?>
				},{
					name: 'Indoor',
					yAxis:1,
					data: <?php echo $monthlyProdRVS640Bon; ?>
				},{
					name: 'Direct Fabric',
					yAxis:1,
					data: <?php echo $monthlyProdAJ1800FBon; ?>
				},{
					name: 'Heat Transfer',
					yAxis:1,
					data: <?php echo $monthlyProdMVJ1624Bon; ?>
				},{
					name: 'Latex',
					yAxis:1,
					data: <?php echo $monthlyProdHPL375Bon; ?>,
					color: Highcharts.getOptions().colors[9],
				},{
					name: 'UV',
					yAxis:1,
					data: <?php echo $monthlyProdSGH6090Bon; ?>
				},{
					name: 'Laser',
					yAxis:1,
					data: <?php echo $monthlyProdLQ1390Bon; ?>
				}]
			});
       
			chart4 = new Highcharts.Chart({
				chart: {
					renderTo: 'dailyProd',
					defaultSeriesType: 'area'
				},
				title: {
					text: 'Production Output'
				},
				subtitle: {
					text: 'Daily Production'
				},
				xAxis: {
			        type: 'datetime',
			        dateTimeLabelFormats: {
			            week: '%e %b'   
			        }
			    },
				yAxis: [{ // Primary yAxis
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						}
					},
					title: {
						text: 'Weekly Output (square meters)',
						style: {
							color: '#666666'
						}
					}
				},{ // Secondary yAxis
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						}
					},
					title: {
						text: 'Weekly Output (sheets)',
						style: {
							color: '#666666'
						}
					},
					opposite: true
				}],
				tooltip: {
					formatter: function() {
						return ''+
							Highcharts.dateFormat('%e %b %Y', this.x) + '<br/>' + 'Output: ' +  Highcharts.numberFormat(this.y, 0);
					}
				},
				plotOptions: {
         			area: {
						//stacking: 'normal',
						marker: {
							
							enabled: false,
							symbol: 'circle',
							radius: 2,
							states: {
								hover: {
									enabled: true
								}
							}
						}
					},
			        series: {
						pointPadding: 0,
						borderWidth: 0,
			            groupPadding: 0.075,
						shadow: false,
						lineWidth:0
			        }
				},
				legend: {
					layout: 'vertical',
					align: 'left',
					x: 520,
					verticalAlign: 'top',
					y: 20,
					floating: true,
					backgroundColor: '#FFFFFF'
				},
				series: [{
					name: 'Outdoor',
					data: <?php echo $dailyProdFLJ320P; ?>,	
					pointStart: Date.UTC(<?php echo $begYear; ?>, <?php echo $begMonth; ?>, <?php echo $begDay; ?>),
			        pointInterval: 24 * 3600 * 1000, // one day
					//stack:'out',
				},{
					name: 'A3+',
					data: <?php echo $dailyProdKMC6501; ?>,
					yAxis: 1,
					pointStart: Date.UTC(<?php echo $begYear; ?>, <?php echo $begMonth; ?>, <?php echo $begDay; ?>),
			        pointInterval: 24 * 3600 * 1000, // one day
					//stack:'a3',
				},{
					name: 'Indoor',
					data: <?php echo $dailyProdRVS640; ?>,	
					type: 'column',
					pointStart: Date.UTC(<?php echo $begYear; ?>, <?php echo $begMonth; ?>, <?php echo $begDay; ?>),
			        pointInterval: 24 * 3600 * 1000, // one day
					color: Highcharts.getOptions().colors[2],
				},{
					name: 'Direct Fabric',
					data: <?php echo $dailyProdAJ1800F; ?>,	
					type: 'column',
					pointStart: Date.UTC(<?php echo $begYear; ?>, <?php echo $begMonth; ?>, <?php echo $begDay; ?>),
			        pointInterval: 24 * 3600 * 1000, // one day
					color: Highcharts.getOptions().colors[3],
				},{
					name: 'Heat Transfer',
					data: <?php echo $dailyProdMVJ1624; ?>,	
					type: 'column',
					pointStart: Date.UTC(<?php echo $begYear; ?>, <?php echo $begMonth; ?>, <?php echo $begDay; ?>),
			        pointInterval: 24 * 3600 * 1000, // one day
					color: Highcharts.getOptions().colors[4],
				},{
					name: 'Latex',
					data: <?php echo $dailyProdHPL375; ?>,	
					type: 'column',
					pointStart: Date.UTC(<?php echo $begYear; ?>, <?php echo $begMonth; ?>, <?php echo $begDay; ?>),
			        pointInterval: 24 * 3600 * 1000, // one day
					color: Highcharts.getOptions().colors[9],
				},{
					name: 'UV',
					data: <?php echo $dailyProdSGH6090; ?>,
					yAxis: 1,
					pointStart: Date.UTC(<?php echo $begYear; ?>, <?php echo $begMonth; ?>, <?php echo $begDay; ?>),
			       	pointInterval: 24 * 3600 * 1000, // one day
					color: Highcharts.getOptions().colors[5],
				},{
					name: 'Laser',
					data: <?php echo $dailyProdLQ1390; ?>,
					yAxis: 1,
					pointStart: Date.UTC(<?php echo $begYear; ?>, <?php echo $begMonth; ?>, <?php echo $begDay; ?>),
			        pointInterval: 24 * 3600 * 1000, // one day
					color: Highcharts.getOptions().colors[6],
				}] 
			});
			
			chart5 = new Highcharts.Chart({
				chart: {
					renderTo: 'topVolFLJ320P',
					plotBackgroundColor: null,
					plotBorderWidth: null,
					plotShadow: false
				},
				title: {
					text: 'Top Volume Flex'
				},
				tooltip: {
					formatter: function() {
						return '<b>'+this.series.name+'</b><br/>'+this.point.name+'<br/>'+Highcharts.numberFormat(this.y, 0);
					}
				},
				plotOptions: {
					pie: {
						allowPointSelect: true,
						cursor: 'pointer',
						dataLabels: {
							enabled: false
						},
						borderWidth:0
					}
				},
			    series: [{
					type: 'pie',
					size: 325,
					innerSize: 203,
					name: "30-Day Volume",
					data: [
						<?php echo $volTopLMFLJ320P; ?>
					]
				},{
					type: 'pie',
					size: 200,
					innerSize: 50,
					name: "All Volume",
					data: [
						<?php echo $volTopAllFLJ320P; ?>
					]
				}]
			});
				
			chart6 = new Highcharts.Chart({
				chart: {
					renderTo: 'topVolKMC6501',
					plotBackgroundColor: null,
					plotBorderWidth: null,
					plotShadow: false
				},
				title: {
					text: 'Top Volume A3+'
				},
				tooltip: {
					formatter: function() {
						return '<b>'+this.series.name+'</b><br/>'+this.point.name+'<br/>'+Highcharts.numberFormat(this.y, 0);
					}
				},
				plotOptions: {
					pie: {
						allowPointSelect: true,
						cursor: 'pointer',
						dataLabels: {
							enabled: false
						},
						borderWidth:0
					}
				},
			    series: [{
					type: 'pie',
					size: 325,
					innerSize: 203,
					name: "30-Day Volume",
					data: [
						<?php echo $volTopLMKMC6501; ?>
					]
				},{
					type: 'pie',
					size: 200,
					innerSize: 50,
					name: "All Volume",
					data: [
						<?php echo $volTopAllKMC6501; ?>
					]
				}]
			});
			
            chart7 = new Highcharts.Chart({
					chart: {
						renderTo: 'apar',
						defaultSeriesType: 'bar'
					},
					title: {
						text: 'Total Receivables & Payable'
					},
					xAxis: {
						categories: ['AR','AP']
					},
					yAxis: {
						min: 0,
						title: {
							text: 'Amounts (Millions)',
							style: {
								color: '#666666'
							}
						}
					},
					legend: {
						backgroundColor: '#FFFFFF',
						reversed: true
					},
					tooltip: {
						formatter: function() {
							return ''+
								 this.series.name +': '+ this.y +'';
						}
					},
					plotOptions: {
						series: {
							stacking: 'normal',
                            pointPadding: 0.075,
                            borderWidth: 0,
                            groupPadding: 0.15,
                            shadow: false
						}
					},
				    series: [{
						name: 'Bad Debt',
						data: <?php echo $badDebt; ?>,
						color: Highcharts.getOptions().colors[3],
					}, {
						name: '90-365 Days',
						data: <?php echo $month12; ?>,
						color: Highcharts.getOptions().colors[2],
					}, {
						name: '60-90 Days',
						data: <?php echo $month3; ?>,
						color: Highcharts.getOptions().colors[4],
					}, {
						name: '31-60 Days',
						data: <?php echo $month2; ?>,
						color: Highcharts.getOptions().colors[0],
					}, {
						name: 'Current',
						data: <?php echo $month1; ?>,
						color: Highcharts.getOptions().colors[1],
					}]
				});

		});
	</script>
        	
	<style>
		body {
			color: #666666;
			text-shadow: 0 0px 1px rgba(0,0,0,0.15);
		}
	
		div.title {
			margin-top:10px;
			margin-left:10px;
			font-size:13pt;
			margin-bottom:6px;
			color:#000000;
		}
		
		div.text {
			margin-left:10px;		
		}
		p{
			margin-top: 0px;
			padding-top: 0px;
			margin-bottom: 0px;
			padding-bottom: 0px;
			display: inline-block;
		}
		.justify { text-align: justify;}
		.upper { text-transform: uppercase; }
		
		.test{
			background-color:rgba(255, 255, 255,0.90);
    		color:#000 ;
			border:2px solid #dddddd ;
		}
		.test1{
    		 background-color:rgba(255, 255, 255,0.90);
   			 color:#000 ;
			 border:2px solid #dddddd ;
		}
		.test2{
    		 background-color:rgba(255, 255, 255,0.90);
    		color:#000 ;
			border:2px solid #dddddd ;
		}
		small{
			color:#000 ;
			font-weight:bold;
			border:0px #000 solid;
		}
		div.ex1 {
		    width:auto;
		    margin: auto;
		    margin-top: 10px;
		    margin-left:100px;
		    margin-right:100px;
		    margin-bottom:20px;
		    border: 1px solid #fff;
		    border-radius: 10px;
		    background: #eeeeee;
		}

		div.ex2{
			margin-top: 0px;
			margin-bottom: 0px;
			padding-bottom: 0px;
			padding-top: 0px;
			display: inline-block;
		}
	</style>

	<link rel="stylesheet" href="framework/odometer/odometer-theme-default.css" />				
	<script src="framework/odometer/odometer.min.js"></script>
	<style>
		.odometer {
  			font-size:24pt;
			color:#3464bc;
			height:35px;
			font-family: 'San Francisco Display', 'HelveticaNeue', 'Helvetica Neue', Helvetica, Arial, sans-serif;
			letter-spacing:-1px;
            font-weight:300;
		}
	</style>
</head>
<body>
	<?php
		$Security=getSecurity($_SESSION['userID'],"DigitalPrint");
		$UpperSec=getSecurity($_SESSION['userID'],"Executive");
	?>
	
	<?php
		//Check if exist promos
		$query="SELECT PROMO_DESC,PROMO_DISC_AMT,PRN_DIG_DESC,PRN_DIG_PRC,DATEDIFF(END_DT,CURRENT_DATE) AS DAYS
				FROM CMP.PRN_DIG_PROMO PRM INNER JOIN CMP.PRN_DIG_TYP TYP ON PRM.PRN_DIG_TYP=TYP.PRN_DIG_TYP
				WHERE BEG_DT<=CURRENT_DATE AND END_DT>=CURRENT_DATE ORDER BY BEG_DT,PRN_DIG_DESC";
		//echo $query;
		
		//Bonus plan
		$query="SELECT FLEX_BASE_Q,DOC_BASE_Q,BASE_PCT,FLEX_INC_Q,DOC_INC_Q,FLEX_INC_PCT,DOC_INC_PCT,BEG_DT,END_DT
				FROM CMP.PRN_DIG_BON_PLAN
				WHERE BEG_DT<=CURRENT_DATE AND END_DT>=CURRENT_DATE";
		//echo $query;
		$resultb=mysql_query($query);
		$rowb=mysql_fetch_array($resultb);
			
		echo "<table class='metrics' style='display:none;'>";
		echo "<tr>";

		echo "<td class='metrics'><div class='metrics'><span class='metrics-desc'>OUTDOOR</span></br><div class='odometer' id='FLJ320P'>".number_format($last['FLJ320P'],0,'.',',')."</div>";
		echo "<div class='metrics'><span class='metrics-desc'>meter</br>".number_format($rowb['FLEX_BASE_Q'],0,",",".")."/".number_format($rowb['FLEX_INC_Q'],0,",",".")."/".$rowb['BASE_PCT']."%"."/".$rowb['FLEX_INC_PCT']."%</span></br></div>";
		echo "</td>";

		echo "<td class='metrics'><div class='metrics'><span class='metrics-desc'>INDOOR</span></br><div class='odometer' id='RVS640'>".number_format($last['RVS640'],0,'.',',')."</div>";
		echo "<div class='metrics'><span class='metrics-desc'>meter</br>Gabung outdoor</span></br></div>";
		echo "</td>";

		echo "<td class='metrics'><div class='metrics'><span class='metrics-desc'>LATEX</span></br><div class='odometer' id='HPL375'>".number_format($last['HPL375'],0,'.',',')."</div>";
		echo "<div class='metrics'><span class='metrics-desc'>meter</br>Gabung outdoor</span></br></div>";
		echo "</td>";

		echo "<td class='metrics'><div class='metrics'><span class='metrics-desc'>DIRECT FABRIC</span></br><div class='odometer' id='AJ1800F'>".number_format($last['AJ1800F'],0,'.',',')."</div>";
		echo "<div class='metrics'><span class='metrics-desc'>meter lari</br>Gabung outdoor</span></br></div>";
		echo "</td>";

		echo "<td class='metrics'><div class='metrics'><span class='metrics-desc'>HEAT TRANSFER</span></br><div class='odometer' id='MVJ1624'>".number_format($last['MVJ1624'],0,'.',',')."</div>";
		echo "<div class='metrics'><span class='metrics-desc'>meter</br>Gabung outdoor</span></br></div>";
		echo "</td>";

		echo "<td class='metrics'><div class='metrics'><span class='metrics-desc'>A3+</span></br><div class='odometer' id='KMC6501_NETT'>".number_format($last['KMC6501_NETT'],0,'.',',')."</div>";
		echo "<div class='metrics'><span class='metrics-desc'>lembar (<span class='odometer' id='KMC6501' style='letter-spacing: normal;line-height :normal;'>".number_format($last['KMC6501'],0,'.',',')."</span>/<span class='odometer' id='KMC8000' style='letter-spacing: normal;line-height :normal;'>".number_format($last['KMC8000'],0,'.',',')."</span>/<span class='odometer' id='KMC1085' style='letter-spacing: normal;line-height :normal;'>".number_format($last['KMC1085'],0,'.',',')."</span>)
		</br>".number_format($rowb['DOC_BASE_Q'],0,",",".")."/".number_format($rowb['DOC_INC_Q'],0,",",".")."/".$rowb['BASE_PCT']."%"."/".$rowb['DOC_INC_PCT']."%</span></br></div>";
		echo "</td>";
		
		echo "<td class='metrics'><div class='metrics'><span class='metrics-desc'>UV</span></br><div class='odometer' id='SGH6090'>".number_format($last['SGH6090'],0,'.',',')."</div>";
		echo "<div class='metrics'><span class='metrics-desc'>meter</br>Gabung outdoor</span></br></div>";
		echo "</td>";

		echo "<td class='metrics' style='border-right:none'><div class='metrics'><span class='metrics-desc'>LASER</span></br><div class='odometer' id='LQ1390'>".number_format($last['LQ1390'],0,'.',',')."</div>";
		echo "<div class='metrics'><span class='metrics-desc'>menit</br>Gabung outdoor</span></br></div>";
		echo "</td>";

		echo "</tr>";
		
		echo "<tr>";
		echo "<td class='metrics'><iframe src='home-activity.php?PROD=".getProdCpctyEqp('FLJ320P')."&TOTAL=$rowc[FLJ320P]&PRINTING=$rowc[PRN_FLJ320P]&FINISHING=$rowc[FIN_FLJ320P]&READY=$rowc[RDY_FLJ320P]' style='height:110px'></iframe>";
		echo "</td>";

		echo "<td class='metrics'><iframe src='home-activity.php?PROD=".getProdCpctyEqp('RVS640')."&TOTAL=$rowc[RVS640]&PRINTING=$rowc[PRN_RVS640]&FINISHING=$rowc[FIN_RVS640]&READY=$rowc[RDY_RVS640]'  style='height:110px'></iframe>";
		echo "</td>";

		echo "<td class='metrics'><iframe src='home-activity.php?PROD=".getProdCpctyEqp('HPL375')."&TOTAL=$rowc[HPL375]&PRINTING=$rowc[PRN_HPL375]&FINISHING=$rowc[FIN_HPL375]&READY=$rowc[RDY_HPL375]' style='height:110px'></iframe>";
		echo "</td>";

		echo "<td class='metrics'><iframe src='home-activity.php?PROD=".getProdCpctyEqp('AJ1800F')."&TOTAL=$rowc[AJ1800F]&PRINTING=$rowc[PRN_AJ1800F]&FINISHING=$rowc[FIN_AJ1800F]&READY=$rowc[RDY_AJ1800F]'  style='height:110px'></iframe>";
		echo "</td>";

		echo "<td class='metrics'><iframe src='home-activity.php?PROD=".getProdCpctyEqp('MVJ1624')."&TOTAL=$rowc[MVJ1624]&PRINTING=$rowc[PRN_MVJ1624]&FINISHING=$rowc[FIN_MVJ1624]&READY=$rowc[RDY_MVJ1624]' style='height:110px'></iframe>";
		echo "</td>";

		echo "<td class='metrics'><iframe src='home-activity.php?PROD=".getProdCpctyEqp('KMC6501')."&TOTAL=$rowc[KMC6501]&PRINTING=$rowc[PRN_KMC6501]&FINISHING=$rowc[FIN_KMC6501]&READY=$rowc[RDY_KMC6501]' style='height:110px'></iframe>";
		echo "</td>";

		echo "<td class='metrics'><iframe src='home-activity.php?PROD=".getProdCpctyEqp('SGH6090')."&TOTAL=$rowc[SGH6090]&PRINTING=$rowc[PRN_SGH6090]&FINISHING=$rowc[FIN_SGH6090]&READY=$rowc[RDY_SGH6090]' style='height:110px'></iframe>";
		echo "</td>";

		echo "<td class='metrics' style='border-right:none'><iframe src='home-activity.php?PROD=".getProdCpctyEqp('LQ1390')."&TOTAL=$rowc[LQ1390]&PRINTING=$rowc[PRN_LQ1390]&FINISHING=$rowc[FIN_LQ1390]&READY=$rowc[RDY_LQ1390]' style='height:110px'></iframe>";
		echo "</td>";

		echo "</tr>";

		echo "</table>";
		echo "</br>";
	?>
	
	<div id="dailyRev" style="width: 800px; height: 400px; margin: 0 auto; <?php if($UpperSec>=5){echo "display:none;";} ?>"></div>
	<div id="dailyRevRetail" style="width: 800px; height: 400px; margin: 0 auto; display: none; <?php if($UpperSec>=5){echo "display:none;";} ?>"></div>
	<div id="monthlyRev" style="width: 800px; height: 400px; margin: 0 auto; display: none; <?php if($UpperSec>=5){echo "display:none;";} ?>"></div>
	<div id="monthlyRevRetail" style="width: 800px; height: 400px; margin: 0 auto; display: none; <?php if($UpperSec>=5){echo "display:none;";} ?>"></div>
	<div id="monthlyProd" style="width: 800px; height: 400px; margin: 0 auto; display: none; <?php if($UpperSec>=8){echo "display:none;";} ?>"></div>
	<div id="monthlyProdBon" style="width: 800px; height: 400px; margin: 0 auto; display: none; <?php if($UpperSec>=8){echo "display:none;";} ?>"></div>
	<div id="dailyProd" style="width: 800px; height: 400px; margin: 0 auto; display: none;"></div>
	<div style="width: 800px; height: 400px; margin: 0 auto; display: none;">
		<div id="topVolFLJ320P" style="width: 400px; height: 400px; margin: 0 auto;float:left"></div>
		<div id="topVolKMC6501" style="width: 400px; height: 400px; margin: 0 auto;float:left"></div>
	</div>
	<div id="apar" style="width: 800px; height: 200px; margin: 0 auto; padding-top:20px; padding-bottom:20px; display: none; <?php if($UpperSec>=5){echo "display:none;";} ?>"></div>
	<script>
	    //Odometer action
		setTimeout(function(){
    		$('#FLJ320P').html(<?php echo intval($rowp['FLJ320P']); ?>);
  		}, 1000);
		setTimeout(function(){
    		$('#RVS640').html(<?php echo intval($rowp['RVS640']); ?>);
  		}, 1000);
		setTimeout(function(){
    		$('#AJ1800F').html(<?php echo intval($rowp['AJ1800F']); ?>);
  		}, 1000);
		setTimeout(function(){
    		$('#MVJ1624').html(<?php echo intval($rowp['MVJ1624']); ?>);
  		}, 1000);
		setTimeout(function(){
    		$('#KMC6501').html(<?php echo intval($rowp['KMC6501']); ?>);
  		}, 1000);
		setTimeout(function(){
    		$('#KMC8000').html(<?php echo intval($rowp['KMC8000']); ?>);
  		}, 1000);
		setTimeout(function(){
    		$('#KMC1085').html(<?php echo intval($rowp['KMC1085']); ?>);
  		}, 1000);
		setTimeout(function(){
    		$('#KMC6501_NETT').html(<?php echo intval($KMC6501_NETT); ?>);
  		}, 1000);
  		setTimeout(function(){
    		$('#HPL375').html(<?php echo intval($rowp['HPL375']); ?>);
  		}, 1000);
		setTimeout(function(){
    		$('#SGH6090').html(<?php echo intval($rowp['SGH6090']); ?>);
  		}, 1000);
		setTimeout(function(){
    		$('#LQ1390').html(<?php echo intval($rowp['LQ1390']); ?>);
  		}, 1000);
	</script>
	
	<div class="ex1" style="display: none;">
	<?php if ($_SESSION['just_login']==1){ ?>

	<?php
		$query = "SELECT NT.NTFY_NBR,NT.NTFY_TTL, NT.NTFY_DESC, NT.NTFY_TYP, NT.BEG_DT, NT.END_DT, NT.CRT_TS
			  FROM CMP.NTFY NT WHERE NT.DEL_NBR = 0 AND BEG_DT<=CURRENT_DATE AND END_DT>=CURRENT_DATE ORDER BY NT.END_DT ASC";
		$result = mysql_query($query);
		while ($row = mysql_fetch_array($result)) {
		$date = $row['CRT_TS'];
		$date1 = strtotime($date);
		$time = date('d/m/Y', $date1);
	?>
	<?php if($row['NTFY_TYP']=='Warning'){
	?>
	<script type="text/javascript">
		$(function() {
	 	$.jGrowl("<div class='ex2'><i class='fa fa-exclamation-triangle' aria-hidden='true'  style='color:#1169d8'></i></div> <p class='upper'><b><?php echo $row['NTFY_TTL'];?></b></p><br><?php echo $row['NTFY_DESC'];?><br> <b><?php echo $time;?></b>", { 
	  	  	theme: 'test',
	  	  	position: 'top-right',
	  	   	life: 5000 
	  	});
		});
    </script>

	<?php }else if($row['NTFY_TYP']=='News'){?>

 	<script type="text/javascript">
		$(function() {
 	    $.jGrowl("<div class='ex2'><i class='fa fa-info-circle' aria-hidden='true'  style='color:#009c21'></i></div> <p align='center' class='upper'><b><?php echo $row['NTFY_TTL'];?></b></p><br><?php echo $row['NTFY_DESC'];?><br> <b><?php echo $time;?></b>", { 
	  	  	theme: 'test2',
	  	  	position: 'top-right',
	  	   	life: 5000
	  	});
		});
    </script>

  <?php }else if ($row['NTFY_TYP']=='Promo'){?>

  	<script type="text/javascript">
		$(function() {
	 	$.jGrowl("<div class='ex2'><i class='fa fa-fire' aria-hidden='true'  style='color:#ea1212'></i></div> <p align='center' class='upper'><b><?php echo $row['NTFY_TTL'];?></b></p><br><?php echo $row['NTFY_DESC'];?><br> <b><?php echo $time;?></b>", { 
	  	  	theme: 'test1',
	  	  	position: 'top-right',
	  	   	life: 5000
	  	});
		});
    </script>

	<?php }?>
	<?php }?>


	<?php 
	$query = "SELECT PR.PRN_DIG_TYP, PR.PROMO_DESC, PR.PROMO_DESC, PR.PROMO_DISC_AMT, PR.BEG_DT, PR.END_DT
		  FROM CMP.PRN_DIG_PROMO PR 
		  WHERE BEG_DT<=CURRENT_DATE AND END_DT>=CURRENT_DATE
		  ORDER BY PR.END_DT ASC";	
	$results = mysql_query($query);
	while ($row = mysql_fetch_array($results)) {?>

	<script type="text/javascript">
		$(function() {
	 	$.jGrowl("<div class='ex2'><i class='fa fa-fire' aria-hidden='true'  style='color:#ea1212'></i></div><p align='center' class='upper'><b><?php echo $row['PROMO_DESC'];?></b></p><br><?php echo $row['PROMO_DISC_AMT'];?><br> <b><?php echo $time;?></b>", { 
	  	  	theme: 'test1',
	  	  	position: 'top-right',
	  	   	life: 5000
	  	});
		});
    </script>

	<?php } ?>

	<?php } unset($_SESSION['just_login']);?>
	</div>
	
</body>
</html>
