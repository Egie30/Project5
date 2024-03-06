<?php
	include "framework/database/connect.php";
	include "framework/database/connect-cloud.php";
	include "framework/functions/komisi.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/alert/alert.php";
	include "framework/functions/crypt.php";
			
			//proreliance IN (2162,2841,1198,2118,1168,1060)
			//corporate IN (1159,1697,2214,3177,3194,3578,4042,4043)
			//champion campus IN (706,719,723,732,738,741,788,790,909,1044,1124,1143,1230,1233,1434,1495,2139,2308,2309,2350,2519,3655,3674,3699,3751,3763,3790,3807,3870,3911,3917,3954,4006,4023,4027)
			//korner IN (47,3208,3696)
			//champion printing IN (250,253,273,299,316,347,368,419,495,708,774,1111,1301,1687,1775,2537,2933,2981,3125,3276,3297,3539,3563,3621,3681,3753,3761,3765,3778,3805,3817,4005,4035)
			$query 	= "SELECT 
						DATE_TS,
						PRSN_NBR,
						CO_NBR,
						DAYNAME_PAY,
						HOLIDAY,
						CLOK_IN_TS,
						CLOK_OT_TS,
						WORKHOUR,
						SUM(CASE 
					        WHEN (CLOK_IN_TS IS NULL OR CLOK_OT_TS IS NULL) THEN 0.5 
							ELSE (
							CASE WHEN CO_NBR NOT IN (1002,2997) AND HOLIDAY = 0 AND CLOK_OT_TS IS NOT NULL 
									THEN 1
								WHEN CO_NBR IN (1002,2997) AND CLOK_OT_TS IS NOT NULL 
									THEN 1
								ELSE 0
								END
							)
							END) AS DAY_NORMAL,
						SUM(CASE 
					        WHEN CO_NBR NOT IN (1002,2997) 
								AND HOLIDAY = 1 
								AND CLOK_OT_TS IS NOT NULL 
								AND (COALESCE(WORKHOUR,0) DIV WORK_TM > 0)
							THEN (COALESCE(WORKHOUR,0) DIV WORK_TM)
							ELSE 0
							END
							) AS DAY_HOLIDAY,
					    SUM(
					        CASE 
					        WHEN CO_NBR NOT IN (1002,2997) AND HOLIDAY = 0 AND CLOK_OT_TS IS NOT NULL
					            THEN (COALESCE(WORKHOUR,0) - WORK_TM)
					        WHEN CO_NBR IN (1002,2997) AND CLOK_OT_TS IS NOT NULL
					            THEN (COALESCE(WORKHOUR,0) - WORK_TM)
					        ELSE 0
					        END
					    ) AS OT_NORMAL,
						SUM(
					        CASE 
					        WHEN (CO_NBR NOT IN (1002,2997) AND HOLIDAY = 1 AND COALESCE(WORKHOUR,0) > WORK_TM )
					            THEN (COALESCE(WORKHOUR,0) MOD WORK_TM) 
					        WHEN (CO_NBR NOT IN (1002,2997) AND HOLIDAY = 1 AND COALESCE(WORKHOUR,0) < WORK_TM)
					            THEN COALESCE(WORKHOUR,0) 
					        ELSE 0 END
					    )AS OT_HOLIDAY
					FROM
					(SELECT 
	                    CLOK_NBR, 
	                    PPL.CO_NBR,
	                    PPL.PRSN_NBR,
	                    DATE(CLOK_IN_TS) AS DATE_TS, 
	                    DAYNAME(DATE(CLOK_IN_TS)) AS DAYNAME_PAY,
	                    HLDY.HLDY_DTE,
	                    PPAY.WORK_TM,
	                    MAC.CLOK_IN_TS,
	                    MAC.CLOK_OT_TS,
	                    (CASE WHEN HLDY.HLDY_DTE IS NOT NULL THEN 1 ELSE 0 END) AS HOLIDAY,
	                    SUM(CASE 
	                        WHEN HLDY.HLDY_DTE IS NOT NULL
	                        THEN ((ROUND(TIME_TO_SEC(TIMEDIFF(CLOK_OT_TS,CLOK_IN_TS))/3600,2)) * 1) 
	                        ELSE (ROUND(TIME_TO_SEC(TIMEDIFF(CLOK_OT_TS,CLOK_IN_TS))/3600,2))
	                        END) AS WORKHOUR 
	                FROM PAY.MACH_CLOK MAC 
	                LEFT OUTER JOIN CMP.PEOPLE PPL 
	                    ON MAC.PRSN_NBR=PPL.PRSN_NBR 
			LEFT OUTER JOIN PAY.PEOPLE PPAY
				ON PPAY.PRSN_NBR=PPL.PRSN_NBR
	                LEFT JOIN PAY.HOLIDAY HLDY
	                    ON DATE(MAC.CLOK_IN_TS) = HLDY.HLDY_DTE
	                WHERE DATE(CLOK_IN_TS) 
	                    AND DATE(CLOK_IN_TS) >= '2019-05-27' 
						AND DATE(CLOK_IN_TS) <= '2019-05-31'
						AND MAC.PRSN_NBR IN (250,253,273,299,316,347,368,419,495,708,774,1111,1301,1687,1775,2537,2933,2981,3125,3276,3297,3539,3563,3621,3681,3753,3761,3765,3778,3805,3817,4005,4035)
	                GROUP BY PRSN_NBR,DATE(CLOK_IN_TS)
					) WORK
					GROUP BY PRSN_NBR";
echo '<pre>'.$query.'</pre><br/>';
$result 	= mysql_query($query);
echo "<table border='1'>";
while($row=mysql_fetch_array($result)){
	$InDays	  = $row['DAY_NORMAL'];
	$Overtime = $row['OT_NORMAL'];
	$WorkTime = 8;
	if($Overtime < 0)  {
		
		$InDays 		-= floor(abs($Overtime/$WorkTime));
		
		if((abs($Overtime) >= ($WorkTime/2)) && (abs($Overtime) < $WorkTime)) {
			$InDays 		-= 0.5;
		}
		
		$Overtime	= 0;
	}
	
	echo "<tr><td>".$row['PRSN_NBR']."</td><td>".$InDays."</td><td>".$Overtime."</td></tr>";
}
echo "</table>";
?>