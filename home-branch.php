<?php
include "framework/database/connect.php";
include "framework/functions/default.php";
include "framework/security/default.php";

$Security 		= getSecurity($_SESSION['userID'], "Retail");
$upperSecurity 	= getSecurity($_SESSION['userID'], "Executive");

$n 			= (14 * 7);
$date		= mktime(0 , 0 , 0 , date("m"), date("d") - $n, date("Y"));
$beginDate	= date('Y-m-d', $date);
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
<link rel="stylesheet" href="framework/jgrowl/jquery.jgrowl.min.css" />

<script type="text/javascript" src="framework/slider/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="framework/charts3/js/highcharts.js"></script>
<script type="text/javascript" src="framework/charts3/js/highcharts-more.js"></script>
<script type="text/javascript" src="framework/database/jquery.min.js"></script>
<script type="text/javascript" src="framework/jgrowl/jquery.jgrowl.min.js"></script>
	
	<!--CHAR CREATIVEHUB-->
	<?php
	$query = "SELECT 
		TGL.Date, 
		(CASE WHEN RPT.ORD_DTE IS NULL THEN CONCAT(MONTH(TGL.Date),'-',DAY(TGL.Date)) ELSE RPT.ORD_DTE END) AS ORD_DTE,
		(CASE WHEN RPT.ORD_DAY IS NULL THEN DAY(TGL.Date) ELSE RPT.ORD_DAY END) AS ORD_DAY,
		(CASE WHEN RPT.ORD_MONTH IS NULL THEN MONTH(TGL.Date) ELSE RPT.ORD_MONTH END) AS ORD_MONTH,
		(CASE WHEN RPT.ORD_YEAR IS NULL THEN YEAR(TGL.Date) ELSE RPT.ORD_YEAR END) AS ORD_YEAR,
		COALESCE(RPT.REVENUE,0) AS REVENUE,
		COALESCE(PYMT.TND_AMT,0) AS OMZET
	FROM(
		SELECT '".$beginDate."' + INTERVAL (A.A + (10 * B.A) + (100 * C.A)) DAY AS DATE
		FROM (SELECT 0 AS A UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS A
		CROSS JOIN (SELECT 0 AS A UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS B
		CROSS JOIN (SELECT 0 AS A UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS C
	) TGL
	LEFT OUTER JOIN (
		SELECT
			DATE_FORMAT(HED.ORD_TS,'%e-%c') AS ORD_DTE,
			DATE_FORMAT(HED.ORD_TS,'%e') AS ORD_DAY,
			DATE_FORMAT(HED.ORD_TS,'%c') AS ORD_MONTH,
			DATE_FORMAT(HED.ORD_TS,'%Y') AS ORD_YEAR,
			SUM(DET.TOT_SUB) / 1000000 AS REVENUE,
			DATE(HED.ORD_TS) AS DTE
		FROM CMP.RTL_ORD_DET DET
			LEFT OUTER JOIN CMP.RTL_ORD_HEAD HED ON DET.ORD_NBR = HED.ORD_NBR
		WHERE ORD_TS BETWEEN (CURRENT_DATE - INTERVAL 14 WEEK) AND CURRENT_DATE 
			AND HED.DEL_NBR = 0
			AND DET.DEL_NBR = 0
		GROUP BY DATE(ORD_TS)
	) RPT ON TGL.Date = RPT.DTE
		LEFT OUTER JOIN(
			SELECT 
				PYM.ORD_NBR,
				SUM(PYM.TND_AMT) / 1000000 AS TND_AMT,
				DATE(CRT_TS) AS CRT_DTE
			FROM CMP.RTL_ORD_PYMT PYM
			WHERE PYM.DEL_NBR = 0 
			GROUP BY DATE(CRT_TS)
		)PYMT ON TGL.Date = PYMT.CRT_DTE
	WHERE TGL.Date BETWEEN (CURRENT_DATE - INTERVAL 14 WEEK) AND CURRENT_DATE";
	//echo "<PRE>".$query;

	$starttime	= microtime(true);
	$result		= mysql_query($query);
	$endtime	= microtime(true);
	$duration	= $endtime-$starttime;
	$leadDay	= 0;
	while($row=mysql_fetch_array($result)){
		if($leadDay==7){
			$begDay1	= $row['ORD_DAY'];
			$begMonth1	= $row['ORD_MONTH']-1;
			$begYear1	= $row['ORD_YEAR'];
		}
		if($leadDay>=7){$dailyRevCreativeHub.=$row['REVENUE'].",";}
		if($leadDay>=7){$dailyOmzCreativeHub.=$row['OMZET'].",";}
		$avgDataCreativeHub[]=$row['REVENUE'];				
		$avgOmzCreativeHub[]=$row['OMZET'];				
		$leadDay++;
	}
	$dailyRevCreativeHub='['.substr($dailyRevCreativeHub,0,strlen($dailyRevCreativeHub)-1);
	$dailyRevCreativeHub.=']';
	
	$dailyOmzCreativeHub='['.substr($dailyOmzCreativeHub,0,strlen($dailyOmzCreativeHub)-1);
	$dailyOmzCreativeHub.=']';
	
	//Generate moving average data
	$movAvgCreativeHub='[';
	for($avg=7;$avg<=14*7;$avg++){
		$movAvgCreativeHub.=($avgDataCreativeHub[$avg-6]+$avgDataCreativeHub[$avg-5]+$avgDataCreativeHub[$avg-4]+$avgDataCreativeHub[$avg-3]+$avgDataCreativeHub[$avg-2]+$avgDataCreativeHub[$avg-1]+$avgDataCreativeHub[$avg])/7;
		$movAvgCreativeHub.=",";
	}
	$movAvgCreativeHub=substr($movAvgCreativeHub,0,strlen($movAvgCreativeHub)-1);
	$movAvgCreativeHub.=']';
	
	$movAvgOmzetCreativeHub='[';
	for($avg=7;$avg<=14*7;$avg++){
		$movAvgOmzetCreativeHub.=($avgOmzCreativeHub[$avg-6]+$avgOmzCreativeHub[$avg-5]+$avgOmzCreativeHub[$avg-4]+$avgOmzCreativeHub[$avg-3]+$avgOmzCreativeHub[$avg-2]+$avgOmzCreativeHub[$avg-1]+$avgOmzCreativeHub[$avg])/7;
		$movAvgOmzetCreativeHub.=",";
	}
	$movAvgOmzetCreativeHub=substr($movAvgOmzetCreativeHub,0,strlen($movAvgOmzetCreativeHub)-1);
	$movAvgOmzetCreativeHub.=']';
	?>
	
	<?php
		$query = "SELECT
			DATE_FORMAT( ORD.ORD_TS, '%e-%c' ) AS ORD_DTE, 
			DATE_FORMAT( ORD.ORD_TS, '%e' ) AS ORD_DAY, 
			DATE_FORMAT( ORD.ORD_TS, '%c' ) AS ORD_MONTH, 
			DATE_FORMAT( ORD.ORD_TS, '%Y' ) AS ORD_YEAR, 
			DATE_FORMAT( ORD.ORD_TS, '%b' ) AS ORD_MONTH_NM,
			((COALESCE((SUM(DET.TOT_SUB) + SUM(ORD.TAX_AMT)),0)) / COUNT(DISTINCT(ORD.ORD_NBR))) / 1000000 AS REV_AVG,
			(COALESCE((SUM(DET.TOT_SUB) + SUM(ORD.TAX_AMT)),0) / 1000000) AS REV_SUM,
			SUM(PYMT.TND_AMT) AS PYMT_AMT,
			(PYM.TND_AMT) AS PYM_AMT,
			(COALESCE((SUM(DET.TOT_SUB) + SUM(ORD.TAX_AMT)),0) - SUM(PYMT.TND_AMT)) /1000000 AS REM_SUM,
			SUM(ORD.PYMT_DOWN) AS PYMT_DOWN,
			SUM(ORD.PYMT_REM) AS PYMT_REM,
			SUM(ORD.TOT_REM) AS TOT_REM
		FROM CMP.RTL_ORD_HEAD ORD
			INNER JOIN (
				SELECT DET.ORD_NBR,
					COUNT(DISTINCT DET.INV_NBR) AS ITM_AMT,
					SUM(CASE WHEN DET.ORD_DET_NBR_PAR IS NULL THEN DET.ORD_Q ELSE 0 END) AS ORD_Q,
					SUM(COALESCE(DET.TOT_SUB, 0)) AS TOT_SUB
				FROM CMP.RTL_ORD_DET DET
				LEFT OUTER JOIN CMP.RTL_ORD_TYP TYP ON DET.ORD_TYP = TYP.RTL_ORD_TYP
				WHERE DET.DEL_NBR=0
				GROUP BY DET.ORD_NBR
			) DET ON ORD.ORD_NBR=DET.ORD_NBR
			LEFT OUTER JOIN (
				SELECT 
					ORD_NBR,
					SUM(TND_AMT) AS TND_AMT
				FROM CMP.RTL_ORD_PYMT
				WHERE DEL_NBR=0
				GROUP BY ORD_NBR
			) PYMT ON ORD.ORD_NBR=PYMT.ORD_NBR
			LEFT OUTER JOIN(
				SELECT 
					PYM.ORD_NBR,
					SUM(PYM.TND_AMT) /1000000 AS TND_AMT,
					DATE(PYM.CRT_TS) AS CRT_DTE,
					MONTH(PYM.CRT_TS) AS PYMT_MONTH,
					YEAR(PYM.CRT_TS) AS PYMT_YEAR
				FROM CMP.RTL_ORD_PYMT PYM
				INNER JOIN CMP.RTL_ORD_HEAD HED ON PYM.ORD_NBR = HED.ORD_NBR
				WHERE 
					PYM.DEL_NBR = 0 
					AND HED.DEL_NBR = 0 
				GROUP BY YEAR(PYM.CRT_TS),MONTH(PYM.CRT_TS)
			)PYM ON MONTH(ORD.ORD_TS) = PYM.PYMT_MONTH AND YEAR(ORD.ORD_TS) = PYM.PYMT_YEAR
		WHERE ORD.DEL_NBR=0 
			AND DATE(ORD.ORD_TS) BETWEEN ( DATE(CURRENT_DATE - INTERVAL 6 MONTH) ) AND DATE(CURRENT_DATE)
		GROUP BY YEAR(ORD.ORD_TS), MONTH(ORD.ORD_TS)
		ORDER BY YEAR(ORD.ORD_TS), MONTH(ORD.ORD_TS)";
		$result = mysql_query($query);
		
		$monthlyAvgCreativeHub 			= array();
		$monthlyTotalCreativeHub		= array();
		$monthlyTotalOmzetCreativeHub	= array();
		$monthlyRemRetailCreativeHub	= array();
		$monthsRetailCreativeHub 		= array();

		while ($row = mysql_fetch_array($result)) {
			$monthlyAvgCreativeHub[] 		= $row['REV_AVG'];
			$monthlyTotalCreativeHub[] 		= $row['REV_SUM'];
			$monthlyTotalOmzetCreativeHub[]	= $row['PYM_AMT'];
			$monthlyRemRetailCreativeHub[] 	= $row['REM_SUM'];
			//$monthlyEstTotRetail			.="0,";
			$ordYear						= $row['ORD_YEAR'];
			$ordMounth						= $row['ORD_MONTH'];
			$monthlyEstTotRetail			.="0,";
			$monthsRetailCreativeHub[] 		= "'" . $row['ORD_MONTH_NM'] . " " . $row['ORD_YEAR'] . "'";
		}

		$monthlyAvgCreativeHub 			= implode(", ", $monthlyAvgCreativeHub);
		$monthlyTotalCreativeHub 		= implode(", ", $monthlyTotalCreativeHub);
		$monthlyTotalOmzetCreativeHub	= implode(", ", $monthlyTotalOmzetCreativeHub);
		$monthlyRemRetailCreativeHub	= implode(", ", $monthlyRemRetailCreativeHub);
		$monthsRetailCreativeHub		= implode(", ", $monthsRetailCreativeHub);
		$estTotRetail					= cal_days_in_month(CAL_GREGORIAN,$ordMounth,$ordYear)*$monthlyAvgCreativeHub-$monthlyTotalCreativeHub;
		$monthlyEstTotRetail			= substr($monthlyEstTotRetail,0,strlen($monthlyEstTotRetail)-2);
		$monthlyEstTotRetail			.=$estTotRetail;
	?>
	
	<!--CHAR KOPI TUGU GEJAYARN-->
	
	<!-- Chart #1 -->
	<?php
		if ($upperSecurity < 4) {
			$query = "SELECT 
				TGL.Date, 
				(CASE WHEN RPT.ORD_DTE IS NULL THEN CONCAT(MONTH(TGL.Date),'-',DAY(TGL.Date)) ELSE RPT.ORD_DTE END) AS ORD_DTE,
				(CASE WHEN RPT.ORD_DAY IS NULL THEN DAY(TGL.Date) ELSE RPT.ORD_DAY END) AS ORD_DAY,
				(CASE WHEN RPT.ORD_MONTH IS NULL THEN MONTH(TGL.Date) ELSE RPT.ORD_MONTH END) AS ORD_MONTH,
				(CASE WHEN RPT.ORD_YEAR IS NULL THEN YEAR(TGL.Date) ELSE RPT.ORD_YEAR END) AS ORD_YEAR,
				COALESCE(RPT.REVENUE,0) - COALESCE(RPT.DISC_FLO_AMT,0) AS REVENUE,
				COALESCE(RPT.DISC_FLO_AMT,0) AS DISC_FLO_AMT,
				COALESCE(RPT.CSH_FLO_TYP, 'RT') AS CSH_FLO_TYP
			FROM(
				SELECT '" . $beginDate . "' + INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY AS DATE
				FROM (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS a 
				CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS b
				CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS c
				) TGL
			LEFT OUTER JOIN (
				SELECT 
					DATE_FORMAT(CRT_TS,'%e-%c') AS ORD_DTE,
					DATE_FORMAT(CRT_TS,'%e') AS ORD_DAY,
					DATE_FORMAT(CRT_TS,'%c') AS ORD_MONTH,
					DATE_FORMAT(CRT_TS,'%Y') AS ORD_YEAR,
					SUM(CASE 
						WHEN CSH.CSH_FLO_TYP = 'RT' THEN CSH.TND_AMT - COALESCE((TTL.DISC_PCT_AMT + TTL.DISC_AMT), 0) 
						WHEN CSH.CSH_FLO_TYP = 'FL' THEN CSH.TND_AMT
						ELSE 0 END) 
					AS REVENUE,
					SUM(CASE WHEN CSH.CSH_FLO_TYP = 'DS' THEN CSH.TND_AMT ELSE 0 END) AS DISC_FLO_AMT,
					DATE(CRT_TS) AS DTE,
					CSH.CSH_FLO_TYP
				FROM RTL.CSH_REG CSH
					LEFT JOIN( 
						SELECT 
							REG_NBR,
							COALESCE(CASE WHEN CSH_FLO_TYP IN ('RT', 'FL') THEN DISC_PCT ELSE 0 END, 0) AS DISC_PCT, 
							COALESCE(CASE WHEN CSH_FLO_TYP IN ('RT', 'FL') THEN (DISC_PCT/100)*TND_AMT ELSE 0 END, 0) AS DISC_PCT_AMT, 
							COALESCE(CASE WHEN CSH_FLO_TYP IN ('RT', 'FL') THEN DISC_AMT ELSE 0 END, 0) AS DISC_AMT
						FROM RTL.CSH_REG
						WHERE POS_ID=3 AND ACT_F = 0
					) TTL ON TTL.REG_NBR = CSH.REG_NBR
				WHERE CSH.ACT_F = 0 AND CSH.CSH_FLO_TYP IN ('RT','DS') AND CSH.POS_ID = 3
				GROUP BY DATE(CRT_TS)
				ORDER BY DATE(CRT_TS)
			) RPT ON TGL.Date = RPT.DTE
			WHERE TGL.Date BETWEEN (CURRENT_DATE - INTERVAL 14 WEEK) AND CURRENT_DATE
			ORDER BY TGL.Date ASC";
			$result = mysql_query($query);
			//ECHO "<PRE>".$query;

			$leadDay 			= 0;
			$beginDayFirst 		= 0;
			$beginMonthFirst 	= 0;
			$beginYearFirst 	= 0;
			$dailyRevRetail 	= array();
			$avgData 			= array();
			$moveAvgRetail 		= array();

			while ($row = mysql_fetch_array($result)) {
				if ($leadDayRetail == 7) {
				    $beginDayFirst = $row['ORD_DAY'];
				    $beginMonthFirst = $row['ORD_MONTH'] - 1;
				    $beginYearFirst = $row['ORD_YEAR'];
		    	}

				if ($leadDayRetail >= 7) {
					$dailyRevRetail[] = $row['REVENUE'];
				}

				$avgData[] = $row['REVENUE'];				
				$leadDayRetail++;
			}

			for($i = 7; $i <= (14 * 7); $i++){
				$moveAvgRetail[] = ($avgData[$i - 6] + $avgData[$i - 5] + $avgData[$i - 4] + $avgData[$i - 3] + $avgData[$i - 2] + $avgData[$i - 1] + $avgData[$i]) / 7;
			
			}

			$dailyRevRetail = implode(", ", $dailyRevRetail);
			$moveAvgRetail = implode(", ", $moveAvgRetail);
		}

	?>

    <!-- Chart #Monthly -->
    <?php
    if ($upperSecurity < 4) {
        $query = "SELECT 
			DATE_FORMAT(TGL.Date, '%M %Y') AS ORD_DTE,
			MONTH(TGL.Date) AS ORD_MONTH,
			YEAR(TGL.Date) AS ORD_YEAR,
			COALESCE(SUM(RPT.REVENUE), 0) AS REVENUE,
			COALESCE(AVG(RPT.REVENUE), 0) AS AVG_REVENUE, 
			COALESCE(RPT.CSH_FLO_TYP, 'RT') AS CSH_FLO_TYP
		FROM(
			SELECT '".$beginDate."' + INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY AS DATE
			FROM (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS a
			CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS b
			CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS c
			) TGL
		LEFT OUTER JOIN (
			SELECT 
				DATE_FORMAT(CRT_TS,'%e-%c') AS ORD_DTE,
				DATE_FORMAT(CRT_TS,'%e') AS ORD_DAY,
				DATE_FORMAT(CRT_TS,'%c') AS ORD_MONTH,
				DATE_FORMAT(CRT_TS,'%Y') AS ORD_YEAR,
				SUM(CASE WHEN CSH.CSH_FLO_TYP = 'RT' THEN CSH.TND_AMT - COALESCE((TTL.DISC_PCT_AMT + TTL.DISC_AMT), 0) ELSE CSH.TND_AMT END) AS REVENUE,
				DATE(CRT_TS) AS DTE,
				CSH.CSH_FLO_TYP
			FROM RTL.CSH_REG CSH
				LEFT JOIN (
					SELECT 
						REG_NBR,
						COALESCE(CASE WHEN CSH_FLO_TYP IN ('RT', 'FL') THEN DISC_PCT ELSE 0 END, 0) AS DISC_PCT, 
						COALESCE(CASE WHEN CSH_FLO_TYP IN ('RT', 'FL') THEN (DISC_PCT/100)*TND_AMT ELSE 0 END, 0) AS DISC_PCT_AMT, 
						COALESCE(CASE WHEN CSH_FLO_TYP IN ('RT', 'FL') THEN DISC_AMT ELSE 0 END, 0) AS DISC_AMT
					FROM RTL.CSH_REG
					WHERE POS_ID=3 AND ACT_F = 0 
				) TTL ON TTL.REG_NBR = CSH.REG_NBR
			WHERE CSH.CSH_FLO_TYP='RT' AND CSH.POS_ID = 3 AND ACT_F = 0 
			GROUP BY DATE(CRT_TS)
			ORDER BY DATE(CRT_TS)
		) RPT ON TGL.date = RPT.DTE
		WHERE TGL.Date #BETWEEN (CURRENT_DATE - INTERVAL 14 WEEK) AND CURRENT_DATE
		GROUP BY ORD_MONTH, ORD_YEAR  
		ORDER BY ORD_YEAR, ORD_MONTH";
		$result = mysql_query($query);
		//echo "<pre>".$query;

		$monthlyRevenue 	= array();
		$monthlyAvgRevenue 	= array();
		$monthsRetail 		= array();

		while ($row = mysql_fetch_array($result)) {
			$monthlyRevenue[] 		= $row['REVENUE'];
			$monthlyAvgRevenue[] 	= $row['AVG_REVENUE'];
			$monthsRetail[] 		= "'" . $row['ORD_DTE'] . "'";
		}

		$monthlyRevenue 	= implode(", ", $monthlyRevenue);
		$monthlyAvgRevenue 	= implode(", ", $monthlyAvgRevenue);
	}
    ?>

	<!-- CHART PIE ALL  -->
	<?php
		$vol=0;$volAll=0;$count=0;
		$query="SELECT 
			INV.NAME AS NAME,
			INV.CAT_NBR,
			INV.CAT_SUB_NBR,
			INV.INV_BCD,
			INV.UPD_TS,
			SUM(CSH.RTL_Q) AS RTL_Q,
			SUM(CASE WHEN DATE(UPD_TS) BETWEEN (CURRENT_DATE - INTERVAL 30 DAY) AND CURRENT_DATE THEN CSH.RTL_Q ELSE 0 END) AS RTL_30,
			SUM(CSH.RTL_Q) AS RTL_All
		FROM RTL.INVENTORY INV
		INNER JOIN (
					SELECT
						RTL_BRC,
						SUM(RTL_Q) AS RTL_Q,
						INV_NBR,
						POS_ID,
						CRT_TS
				FROM RTL.CSH_REG
				WHERE ACT_F = 0 AND CSH_FLO_TYP = 'RT' AND POS_ID = '3' AND DATE(CRT_TS) BETWEEN (CURRENT_DATE - INTERVAL 1 MONTH) AND CURRENT_DATE
				GROUP BY RTL_BRC, INV_NBR
				) CSH ON INV.INV_NBR = CSH.INV_NBR
		WHERE INV.CAT_NBR IN ('7', '9', '11', '116', '118') 
		AND NOT INV.CAT_SUB_NBR  = '213' 
		AND DATE(CSH.CRT_TS) BETWEEN (CURRENT_DATE - INTERVAL 1 MONTH) AND CURRENT_DATE
		GROUP BY INV.NAME
		ORDER BY INV.UPD_TS DESC";
		$result=mysql_query($query);
		while($row=mysql_fetch_array($result)){
			if($count<50){
				$volTop.="['".$row['NAME']."',".$row['RTL_30']."],"; 
				$volTopAll.="['".$row['NAME']."',".$row['RTL_All']."],"; 
			}else{
				$vol+=$row['RTL_Q'];
				$volAll+=$row['RTL_Q'];
			}
			$count++;

		}
		$volTop.="['Other',".$vol."]";
		$volTopAll.="['Other',".$volAll."]";
	?>

	<!-- CHART PIE DOGU -->
	<?php
		$volDogu=0;$volAllDogu=0;$count=0;
		$query="SELECT 
			INV.NAME AS NAME,
			INV.CAT_NBR,
			INV.CAT_SUB_NBR,
			INV.INV_BCD,
			INV.UPD_TS,
			SUM(CSH.RTL_Q) AS RTL_Q,
			SUM(CASE WHEN DATE(CSH.CRT_TS) BETWEEN (CURRENT_DATE - INTERVAL 30 DAY) AND CURRENT_DATE THEN CSH.RTL_Q ELSE 0 END) AS RTLD_30,
			SUM(CSH.RTL_Q) AS RTLD_All
		FROM RTL.INVENTORY INV
		INNER JOIN (
			SELECT
				RTL_BRC,
				SUM(RTL_Q) AS RTL_Q,
				INV_NBR,
				POS_ID,
				CRT_TS
			FROM RTL.CSH_REG
			WHERE 
				ACT_F = 0 
				AND CSH_FLO_TYP = 'RT' 
				AND POS_ID = '3'
				AND DATE(CRT_TS) BETWEEN (CURRENT_DATE - INTERVAL 1 MONTH) AND CURRENT_DATE
			GROUP BY RTL_BRC, INV_NBR
		) CSH ON INV.INV_NBR = CSH.INV_NBR
		WHERE 
			INV.CAT_NBR = 9 
			AND DEL_NBR = 0 
			AND CAT_SUB_NBR = 213 
			AND DATE(CSH.CRT_TS) BETWEEN (CURRENT_DATE - INTERVAL 1 MONTH) AND CURRENT_DATE
		GROUP BY INV.NAME
		ORDER BY INV.UPD_TS DESC";
		$result=mysql_query($query);
		// echo "<pre>".$query;
		while($row=mysql_fetch_array($result)){
			if($count<20){
				$volTopDogu.="['".$row['NAME']."',".$row['RTLD_30']."],"; 
				$volTopAllDogu.="['".$row['NAME']."',".$row['RTLD_All']."],"; 
			}else{
				$volDogu+=$row['RTL_Q'];
				$volAllDogu+=$row['RTL_Q'];
			}
			$count++;

		}
		$volTopDogu.="['Other',".$volDogu."]";
		$volTopAllDogu.="['Other',".$volAllDogu."]";
		//echo $volTopLMKMC6501;
	?>
 
	<!-- Add the JavaScript to initialize the chart on document ready -->
	<script type="text/javascript">
		$(document).ready(function() {
			var dailyRevCreativeHub, monthlyRevCreativeHub, dailyRevRetail, chart6, chartdogu;

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
			
			/*===========Creative Hub Start===========*/
			dailyRevCreativeHub = new Highcharts.Chart({
				chart: {
					renderTo: 'daily-rev-creative-hub',
					zoomType: 'xy'
				},
				title: {
					text: 'Creative Hub 13-Week Revenue Trend'
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
						return ''+ Highcharts.dateFormat('%e %b %Y', this.x) + '<br/>' + this.series.name + ' : '+  Highcharts.numberFormat(this.y*1000000, 0);
					}
				},
				plotOptions: {
					series: {
						pointPadding: 0.13,
						borderWidth: 0,
						groupPadding: 0.01,
						pointWidth: 5,
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
					data: <?php echo $dailyRevCreativeHub; ?>,	
					pointStart: Date.UTC(<?php echo $begYear1; ?>, <?php echo $begMonth1; ?>, <?php echo $begDay1; ?>),
			        pointInterval: 24 * 3600 * 1000 // one day
				},{
					name: '7-Day Moving Average Revenue',
					color: '#8cc152',
					type: 'line',
					data: <?php echo $movAvgCreativeHub; ?>,
					marker: {
                    	enabled: true
                	},
					pointStart: Date.UTC(<?php echo $begYear1; ?>, <?php echo $begMonth1; ?>, <?php echo $begDay1; ?>),
			        pointInterval: 24 * 3600 * 1000 // one day
				}, {
					name: 'Omzet',
					color: '#f1c40f',
					type: 'line',
					data: <?php echo $movAvgOmzetCreativeHub; ?>,
					marker: {
                    	enabled: true
                	},
					pointStart: Date.UTC(<?php echo $begYear1; ?>, <?php echo $begMonth1; ?>, <?php echo $begDay1; ?>),
			        pointInterval: 24 * 3600 * 1000 // one day
				}] 
			});
			
			monthlyRevCreativeHub = new Highcharts.Chart({
				chart: {
					renderTo: 'monthly-rev-creative-hub',
					defaultSeriesType: 'column',
				},
				title: {
					text: 'Creative Hub Monthly Revenue',
				},
				subtitle: {
					text: 'Average per Working Day',
				},
				xAxis: {
					categories: [<?php echo $monthsRetailCreativeHub; ?>]
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
					data: [<?php echo $monthlyAvgCreativeHub; ?>],
					stack:'avg',
				},{
					name: 'Estimated Revenue',
                    color: Highcharts.getOptions().colors[7],
					yAxis:1,
					data: [<?php echo $monthlyEstTotRetail; ?>],
					stack:'tot',
				},{
					name: 'Total Revenue',
					yAxis:1,
 					data: [<?php echo $monthlyTotalCreativeHub; ?>],
					stack:'tot',
				},{
					name: 'Outstanding Balance',
					yAxis:1,
					type: 'line',
					marker: {
                    	enabled: true
                	},
					data: [<?php echo $monthlyRemRetailCreativeHub; ?>]
				}]
			});
			/*===========Creative Hub End===========*/
			
			/*===========Kopi Tugu Start===========*/
			dailyRevRetail = new Highcharts.Chart({
				chart: {
					renderTo: 'daily-revenue-retail',
					zoomType: 'xy'
				},
				title: {
					text: 'Kopi Tugu 14-Week Revenue Trend'
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
							Highcharts.dateFormat('%e %b %Y', this.x) + '<br/>' + (this.series.name == 'Revenue' ? '' : 'Average ') + 'Revenue: '+  Highcharts.numberFormat(this.y, 0);
					}
				},
				plotOptions: {
					series: {
						pointPadding: 0.13,
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
					color: '#4572A7',
					type: 'column',
					color: Highcharts.getOptions().colors[20],
					yAxis: 1,
					data: [<?php echo $dailyRevRetail; ?>],	
					pointStart: Date.UTC(<?php echo $beginYearFirst; ?>, <?php echo $beginMonthFirst; ?>, <?php echo $beginDayFirst; ?>),
					pointInterval: 24 * 3600 * 1000 // one day
				}, {
					name: '7-Day Moving Average',
					color: '#89A54E',
					type: 'line',
					data: [<?php echo $moveAvgRetail; ?>],
					marker: {
                    	enabled: true
                	},
					pointStart: Date.UTC(<?php echo $beginYearFirst; ?>, <?php echo $beginMonthFirst; ?>, <?php echo $beginDayFirst; ?>),
					pointInterval: 24 * 3600 * 1000 // one day
				}] 
			});

			chart6 = new Highcharts.Chart({
				chart: {
					renderTo: 'topVol',
					plotBackgroundColor: null,
					plotBorderWidth: null,
					plotShadow: false
				},
				title: {
					text: 'All Product'
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
						<?php echo $volTop; ?>
					]
				},{
					type: 'pie',
					size: 200,
					innerSize: 50,
					name: "All Volume",
					data: [
						<?php echo $volTopAll; ?>
					]
				}]
			});

		chartdogu = new Highcharts.Chart({
				chart: {
					renderTo: 'topDogu',
					plotBackgroundColor: null,
					plotBorderWidth: null,
					plotShadow: false
				},
				title: {
					text: 'Dogu'
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
						<?php echo $volTopDogu; ?>
					]
				},{
					type: 'pie',
					size: 200,
					innerSize: 50,
					name: "All Volume",
					data: [
						<?php echo $volTopAllDogu; ?>
					]
				}]
			});
			

            //================== CHART MONTHLY ===================//

            monthlyRevRetail = new Highcharts.Chart({
                chart: {
                    renderTo: 'monthly-revenue-retail',
                    defaultSeriesType: 'column',
                },
                title: {
                    text: 'Kopi Tugu Monthly Revenue',
                },
                subtitle: {
                    text: 'Total Revenue and Average per Working Day',
                },
                xAxis: {
                    categories: [<?php echo implode(", ", $monthsRetail); ?>]
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
						return '<b>' + this.series.name + '</b><br/>' +
							this.x + ': ' + Highcharts.numberFormat(this.y , 0);
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
                    name: 'Total Revenue',
                    data: [<?php echo $monthlyRevenue; ?>]
                },{
					name: 'Average Revenue',
					yAxis:1,
 					data: [<?php echo $monthlyAvgRevenue; ?>],
					stack:'tot',
				}]
            });
        });
    </script>
</head>
<body>
<?php if ($upperSecurity < 4) { ?>
	<div id="daily-rev-creative-hub" style="width: 800px; margin: 0 auto;"></div>
	<div id="monthly-rev-creative-hub" style="width: 800px; margin: 0 auto;"></div>
	<div id="daily-live-retail" style="width: 800px; margin: 0 auto;"></div>
	<div id="daily-revenue-retail" style="width: 800px; margin: 0 auto;"></div>
	<div id="monthly-revenue-retail" style="width: 800px; margin: 0 auto;"></div>
    <div id="topChartsContainer" style="display: flex; justify-content: center;">
        <div id="topVol" style="width: 400px; margin: 0;"></div>
        <div id="topDogu" style="width: 400px; margin: 0;"></div>
    </div>
<?php } ?>
</body>
</html>