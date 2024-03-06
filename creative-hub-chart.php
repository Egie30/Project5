<?php
include "framework/database/connect.php";
include "framework/functions/default.php";
include "framework/security/default.php";

$Security = getSecurity($_SESSION['userID'], "Retail");
$upperSecurity = getSecurity($_SESSION['userID'], "Executive");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
        <style type="text/css">
            table {
                font-size: 10pt;
            }

            .spiffy {
                display: block;
            }

            .spiffy * {
                display: block;
                height: 1px;
                overflow: hidden;
                background: #eeeeee;
            }

            .spiffy1 {
                border-right: 1px solid #f7f7f7;
                padding-right: 1px;
                margin-right: 3px;
                border-left: 1px solid #f7f7f7;
                padding-left: 1px;
                margin-left: 3px;
                background: #f2f2f2;
            }

            .spiffy2 {
                border-right: 1px solid #fdfdfd;
                border-left: 1px solid #fdfdfd;
                padding: 0px 1px;
                background: #f1f1f1;
                margin: 0px 1px;
            }

            .spiffy3 {
                border-right: 1px solid #f1f1f1;
                border-left: 1px solid #f1f1f1;
                margin: 0px 1px;
            }

            .spiffy4 {
                border-right: 1px solid #f7f7f7;
                border-left: 1px solid #f7f7f7;
            }

            .spiffy5 {
                border-right: 1px solid #f2f2f2;
                border-left: 1px solid #f2f2f2;
            }

            .spiffy_content {
                padding: 0px 5px;
                background: #eeeeee;
                text-align: center;
            }

            /* #KMC6501,
            #KMC8000,
            #KMC1085 {
                font-size: 9pt;
                color: #666666;
                height: 18px;
            } */

            .tabs {
                margin: 10px;
            }

            .tab {
                display: inline-block;
                padding: 8px;
                font-size: 15px;
                cursor: pointer;
                background-color: #eee;
                margin-right: 5px;
                border: 1px solid #ccc;
                border-radius: 5px 5px 0 0;
            }

            .tab:hover {
                background-color: #ddd;
            }

            .tab.active {
                background-color: #ccc;
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
        <script type="text/javascript" src="https://code.highcharts.com/modules/exporting.js"></script>


                            <!-- ========== Creative Hub ==========  -->
        <!-- Query #Daily -->
        <?php
            if ($upperSecurity < 4) {
                $n = (14 * 7);
                $date = mktime(0, 0, 0, date("m"), date("d") - $n, date("Y"));
                $beginDate = date('Y-m-d', $date);

                $query = "SELECT
                                CONCAT(MONTH(TGL.Date), '-', YEAR(TGL.Date)) AS ORD_DTE,
                                DAY(TGL.Date) AS ORD_DAY,
                                MONTH(TGL.Date) AS ORD_MONTH,
                                YEAR(TGL.Date) AS ORD_YEAR,
                                COALESCE((RPT.REVENUE), 0) AS REVENUE
                            FROM (
                                SELECT '".$beginDate."' + INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY AS DATE
                                FROM (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS a
                                CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS b
                                CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS c
                            ) TGL
                            LEFT OUTER JOIN (
                                    SELECT
                                        DATE_FORMAT(ORD_TS,'%e-%c') AS ORD_DTE,
                                        DATE_FORMAT(ORD_TS,'%e') AS ORD_DAY,
                                        DATE_FORMAT(ORD_TS,'%c') AS ORD_MONTH,
                                        DATE_FORMAT(ORD_TS,'%Y') AS ORD_YEAR,
                                        SUM(TOT_AMT) AS REVENUE,
                                        DATE(ORD_TS) AS DTE
                                    FROM RTL_ORD_HEAD
                                    WHERE DATE(ORD_TS) AND DEL_NBR = 0
                                    GROUP BY DATE(ORD_TS)
                                    )RPT ON TGL.Date = RPT.DTE
                            WHERE TGL.Date BETWEEN (CURRENT_DATE - INTERVAL 14 WEEK) AND CURRENT_DATE
                            ORDER BY TGL.Date ASC";

                $result = mysql_query($query);

                $leadDayCreative = 0;
                $beginDaycreativehub = 0;
                $beginMonthcreativehub = 0;
                $beginYearcreativehub = 0;
                $dailyRevRetail = array();
                $avgData = array();
                $moveAvgRetail = array();

                while ($row = mysql_fetch_array($result)) {
                    if ($leadDayCreative == 7) {
                        $beginDaycreativehub = $row['ORD_DAY'];
                        $beginMonthcreativehub = $row['ORD_MONTH'] - 1;
                        $beginYearcreativehub = $row['ORD_YEAR'];
                    }

                    if ($leadDayCreative >= 7) {
                        $dailyRevRetail[] = $row['REVENUE'];
                    }

                    $avgData[] = $row['REVENUE'];				
                    $leadDayCreative++;
                }

                for($i = 7; $i <= (14 * 7); $i++){
                    $moveAvgRetail[] = ($avgData[$i - 6] + $avgData[$i - 5] + $avgData[$i - 4] + $avgData[$i - 3] + $avgData[$i - 2] + $avgData[$i - 1] + $avgData[$i]) / 7;

                }

                $dailyRevRetail = implode(", ", $dailyRevRetail);
                $moveAvgRetail = implode(", ", $moveAvgRetail);
            }    
        ?>

            <!-- Query #Monthly -->
        <?php
            if ($upperSecurity < 4) {
                $n = (14 * 7);
                $date = mktime(0, 0, 0, date("m"), date("d") - $n, date("Y"));
                $beginDate = date('Y-m-d', $date);

                $query = "SELECT  
                                DATE_FORMAT(TGL.Date, '%M %Y') AS ORD_DTE,
                                MONTH(TGL.Date) AS ORD_MONTH,
                                YEAR(TGL.Date) AS ORD_YEAR,
                                COALESCE(SUM(RPT.REVENUE), 0) AS REVENUE,
                                COALESCE(AVG(RPT.REVENUE), 0) AS AVG_REVENUE
                            FROM
                                (
                                SELECT '".$beginDate."' + INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY AS DATE
                                FROM (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS a
                                CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS b
                                CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS c
                                ) TGL
                            LEFT OUTER JOIN 
                                (SELECT
                                    DATE_FORMAT(ORD_TS,'%e-%c') AS ORD_DTE,
                                    DATE_FORMAT(ORD_TS,'%e') AS ORD_DAY,
                                    DATE_FORMAT(ORD_TS,'%c') AS ORD_MONTH,
                                    DATE_FORMAT(ORD_TS,'%Y') AS ORD_YEAR,
                                    SUM(TOT_AMT) AS REVENUE,
                                    DATE(ORD_TS) AS DTE
                                FROM RTL_ORD_HEAD
                                WHERE DATE(ORD_TS) AND DEL_NBR = 0
                                GROUP BY DATE(ORD_TS)
                            ) RPT ON TGL.Date = RPT.DTE
                            WHERE TGL.Date AND CURRENT_DATE
                            GROUP BY ORD_MONTH, ORD_YEAR  
                            ORDER BY ORD_YEAR, ORD_MONTH";

                $result = mysql_query($query);

                $monthlyRev = array();
                $monthlyAvgRev = array();
                $monthsRet = array();

                while ($row = mysql_fetch_array($result)) {
                    $monthlyRev[] = $row['REVENUE'];
                    $monthlyAvgRev[] = $row['AVG_REVENUE'];
                    $monthsRet[] = "'" . $row['ORD_DTE'] . "'";
                }

                $monthlyRev = implode(", ", $monthlyRev);
                $monthlyAvgRev = implode(", ", $monthlyAvgRev);
            }
        ?>

            	<!-- CHART PIE CREATIVE HUB -->
        <?php
            $volcreativehub=0;$volAllcreativehub=0;$count=0;
            $query="SELECT HED.ORD_TTL AS NAME,
                        SUM(DET.ORD_Q) AS RTL_Q,
                        SUM(CASE WHEN DATE(HED.ORD_TS) BETWEEN (CURRENT_DATE - INTERVAL 30 DAY) AND CURRENT_DATE THEN DET.ORD_Q ELSE 0 END) AS RTLD_30,
                        SUM(DET.ORD_Q) AS RTLD_All,
                        DATE(HED.ORD_TS) AS DTE
                    FROM CMP.RTL_ORD_DET DET
                    INNER JOIN (
                                SELECT
                                    ORD_TTL,
                                    ORD_NBR,
                                    ORD_TS
                                FROM CMP.RTL_ORD_HEAD 
                                WHERE DATE(ORD_TS) BETWEEN (CURRENT_DATE - INTERVAL 1 MONTH) AND CURRENT_DATE
                                GROUP BY ORD_NBR
                                ) HED ON DET.ORD_NBR = HED.ORD_NBR
                    WHERE DEL_NBR = 0  
                    AND DATE(HED.ORD_TS) BETWEEN (CURRENT_DATE - INTERVAL 1 MONTH) AND CURRENT_DATE
                    GROUP BY HED.ORD_NBR
                    ORDER BY HED.ORD_NBR DESC";
                    
            $result=mysql_query($query);
            while($row=mysql_fetch_array($result)){
                if($count<10){
                    $volTopcreativehub.="['".$row['NAME']."',".$row['RTLD_30']."],"; 
                    $volTopAllcreativehub.="['".$row['NAME']."',".$row['RTLD_All']."],"; 
                }else{
                    $volcreativehub+=$row['RTL_Q'];
                    $volAllcreativehub+=$row['RTL_Q'];
                }
                $count++;

            }
            $volTopcreativehub.="['Other',".$volcreativehub."]";
            $volTopAllcreativehub.="['Other',".$volAllcreativehub."]";
        ?>


                    <!-- ========== Kopi Tugu ==========  -->
        <!-- Chart #Daily -->
        <?php
            if ($upperSecurity < 4) {
                $n 			= (14 * 7);
                $date		= mktime(0 , 0 , 0 , date("m"), date("d") - $n, date("Y"));
                $beginDate	= date('Y-m-d', $date);

                $query = "SELECT TGL.Date, 
                                CONCAT(MONTH(TGL.Date),'-',DAY(TGL.Date)) AS ORD_DTE,
                                DAY(TGL.Date) AS ORD_DAY,
                                MONTH(TGL.Date) AS ORD_MONTH,
                                YEAR(TGL.Date) AS ORD_YEAR,
                                COALESCE(RPT.REVENUE,0) AS REVENUE,
                                CSH_FLO_TYP
                            FROM
                                (
                                SELECT '" . $beginDate . "' + INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY AS DATE
                                FROM (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS a
                                CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS b
                                CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS c
                                ) TGL
                            LEFT OUTER JOIN 
                                (SELECT 
                                    DATE_FORMAT(CRT_TS,'%e-%c') AS ORD_DTE,
                                    DATE_FORMAT(CRT_TS,'%e') AS ORD_DAY,
                                    DATE_FORMAT(CRT_TS,'%c') AS ORD_MONTH,
                                    DATE_FORMAT(CRT_TS,'%Y') AS ORD_YEAR,
                                    SUM(CASE WHEN CSH.CSH_FLO_TYP = 'RT' THEN CSH.TND_AMT - COALESCE((TTL.DISC_PCT_AMT + TTL.DISC_AMT), 0) ELSE CSH.TND_AMT END) AS REVENUE,
                                    DATE(CRT_TS) AS DTE,
                                    CSH.CSH_FLO_TYP
                                FROM RTL.CSH_REG CSH
                            LEFT JOIN 
                                ( SELECT 
                                        REG_NBR,
                                        COALESCE(CASE WHEN CSH_FLO_TYP IN ('RT', 'FL') THEN DISC_PCT ELSE 0 END, 0) AS DISC_PCT, 
                                        COALESCE(CASE WHEN CSH_FLO_TYP IN ('RT', 'FL') THEN (DISC_PCT/100)*TND_AMT ELSE 0 END, 0) AS DISC_PCT_AMT, 
                                        COALESCE(CASE WHEN CSH_FLO_TYP IN ('RT', 'FL') THEN DISC_AMT ELSE 0 END, 0) AS DISC_AMT
                                    FROM RTL.CSH_REG
                                    WHERE POS_ID=3  
                                ) TTL ON TTL.REG_NBR = CSH.REG_NBR
                                WHERE CSH.CSH_FLO_TYP='RT' AND CSH.POS_ID = 3
                                GROUP BY DATE(CRT_TS)
                                ORDER BY DATE(CRT_TS)
                            ) RPT ON TGL.Date = RPT.DTE
                            WHERE TGL.Date BETWEEN (CURRENT_DATE - INTERVAL 14 WEEK) AND CURRENT_DATE
                            ORDER BY TGL.Date ASC";
                            // echo "<pre>".$query;

                $result = mysql_query($query);

                $leadDayKopitugu = 0;
                $beginDaykopitugu = 0;
                $beginMonthkopitugu = 0;
                $beginYearkopitugu = 0;
                $revKopiTugu = array();
                $avgData = array();
                $moveAvgRetail = array();

                while ($row = mysql_fetch_array($result)) {
                    if ($leadDayKopitugu == 7) {
                        $beginDaykopitugu = $row['ORD_DAY'];
                        $beginMonthkopitugu = $row['ORD_MONTH'] - 1;
                        $beginYearkopitugu = $row['ORD_YEAR'];
                    }

                    if ($leadDayKopitugu >= 7) {
                        $revKopiTugu[] = $row['REVENUE'];
                    }

                    $avgData[] = $row['REVENUE'];				
                    $leadDayKopitugu++;
                }

                for($i = 7; $i <= (14 * 7); $i++){
                    $moveAvgRetail[] = ($avgData[$i - 6] + $avgData[$i - 5] + $avgData[$i - 4] + $avgData[$i - 3] + $avgData[$i - 2] + $avgData[$i - 1] + $avgData[$i]) / 7;
                
                }

                $revKopiTugu = implode(", ", $revKopiTugu);
                $moveAvgRetail = implode(", ", $moveAvgRetail);
            }
        ?>

        <!-- Chart #Monthly -->
        <?php
            if ($upperSecurity < 4) {
                $n = (14 * 7);
                $date = mktime(0, 0, 0, date("m"), date("d") - $n, date("Y"));
                $beginDate = date('Y-m-d', $date);

            $query = "SELECT DATE_FORMAT(TGL.Date, '%M %Y') AS ORD_DTE,
                            MONTH(TGL.Date) AS ORD_MONTH,
                            YEAR(TGL.Date) AS ORD_YEAR,
                            COALESCE(SUM(RPT.REVENUE), 0) AS REVENUE,
                            COALESCE(AVG(RPT.REVENUE), 0) AS AVG_REVENUE, 
                            COALESCE(RPT.CSH_FLO_TYP, 'RT') AS CSH_FLO_TYP
                        FROM
                            (
                            SELECT '".$beginDate."' + INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY AS DATE
                            FROM (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS a
                            CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS b
                            CROSS JOIN (SELECT 0 AS a UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) AS c
                            ) TGL
                        LEFT OUTER JOIN 
                            (SELECT 
                                DATE_FORMAT(CRT_TS,'%e-%c') AS ORD_DTE,
                                DATE_FORMAT(CRT_TS,'%e') AS ORD_DAY,
                                DATE_FORMAT(CRT_TS,'%c') AS ORD_MONTH,
                                DATE_FORMAT(CRT_TS,'%Y') AS ORD_YEAR,
                                SUM(CASE WHEN CSH.CSH_FLO_TYP = 'RT' THEN CSH.TND_AMT - COALESCE((TTL.DISC_PCT_AMT + TTL.DISC_AMT), 0) ELSE CSH.TND_AMT END) AS REVENUE,
                                DATE(CRT_TS) AS DTE,
                                CSH.CSH_FLO_TYP
                            FROM RTL.CSH_REG CSH
                        LEFT JOIN 
                            ( SELECT 
                                    REG_NBR,
                                    COALESCE(CASE WHEN CSH_FLO_TYP IN ('RT', 'FL') THEN DISC_PCT ELSE 0 END, 0) AS DISC_PCT, 
                                    COALESCE(CASE WHEN CSH_FLO_TYP IN ('RT', 'FL') THEN (DISC_PCT/100)*TND_AMT ELSE 0 END, 0) AS DISC_PCT_AMT, 
                                    COALESCE(CASE WHEN CSH_FLO_TYP IN ('RT', 'FL') THEN DISC_AMT ELSE 0 END, 0) AS DISC_AMT
                                FROM RTL.CSH_REG
                                WHERE POS_ID=3  
                            ) TTL ON TTL.REG_NBR = CSH.REG_NBR
                            WHERE CSH.CSH_FLO_TYP='RT' AND CSH.POS_ID = 3
                            GROUP BY DATE(CRT_TS)
                            ORDER BY DATE(CRT_TS)
                        ) RPT ON TGL.Date = RPT.DTE
                        WHERE TGL.Date #BETWEEN (CURRENT_DATE - INTERVAL 14 WEEK) AND CURRENT_DATE
                        GROUP BY ORD_MONTH, ORD_YEAR  
                        ORDER BY ORD_YEAR, ORD_MONTH";

            $result = mysql_query($query);

            $monthlyRevenue = array();
            $monthlyAvgRevenue = array();
            $monthsRetail = array();

            while ($row = mysql_fetch_array($result)) {
                $monthlyRevenue[] = $row['REVENUE'];
                $monthlyAvgRevenue[] = $row['AVG_REVENUE'];
                $monthsRetail[] = "'" . $row['ORD_DTE'] . "'";
            }

            $monthlyRevenue = implode(", ", $monthlyRevenue);
            $monthlyAvgRevenue = implode(", ", $monthlyAvgRevenue);
            }
        ?>

        <!-- CHART PIE ALL  -->
        <?php
            $vol=0;$volAll=0;$count=0;
            $query="SELECT INV.NAME AS NAME,
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
                            WHERE CSH_FLO_TYP = 'RT' AND POS_ID = '3' AND DATE(CRT_TS) BETWEEN (CURRENT_DATE - INTERVAL 1 MONTH) AND CURRENT_DATE
                            GROUP BY RTL_BRC, INV_NBR
                            ) CSH ON INV.INV_NBR = CSH.INV_NBR
                    WHERE INV.CAT_NBR IN ('7', '9', '11', '116', '118') 
                    AND NOT INV.CAT_SUB_NBR  = '213' 
                    AND DATE(CSH.CRT_TS) BETWEEN (CURRENT_DATE - INTERVAL 1 MONTH) AND CURRENT_DATE
                    GROUP BY INV.NAME
                    ORDER BY INV.UPD_TS DESC";
                    
            $result=mysql_query($query);
            // echo "<pre>".$query;
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
            //echo $volTopLMKMC6501;
        ?>

        <!-- CHART PIE DOGU -->
        <?php
            $volDogu=0;$volAllDogu=0;$count=0;
            $query="SELECT INV.NAME AS NAME,
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
                                WHERE CSH_FLO_TYP = 'RT' AND POS_ID = '3' AND DATE(CRT_TS) BETWEEN (CURRENT_DATE - INTERVAL 1 MONTH) AND CURRENT_DATE
                                GROUP BY RTL_BRC, INV_NBR
                                ) CSH ON INV.INV_NBR = CSH.INV_NBR
                    WHERE INV.CAT_NBR = 9 AND DEL_NBR = 0 AND CAT_SUB_NBR = 213 
                    AND DATE(CSH.CRT_TS) BETWEEN (CURRENT_DATE - INTERVAL 1 MONTH) AND CURRENT_DATE
                    GROUP BY INV.NAME
                    ORDER BY INV.UPD_TS DESC";
                    
            $result=mysql_query($query);
            // echo "<pre>".$query;
            while($row=mysql_fetch_array($result)){
                if($count<50){
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

        <script type="text/javascript">
                $(document).ready(function() {
                    var monthlyRevRetail;

                    Highcharts.setOptions({
                        colors: ['#54b6ff', '#1169d8', '#4edd19', '#009c21', '#fed75c', '#f9cb1d', '#fd630a', '#ea1212', '#ab2e96', '#500a85', '#ed8f1c', '#a63d00', '#0ace80', '#008391', '#d2d2d2', '#b6b6b6', '#747474', '#242424', '#7d7d7d', '#303030'],
                        chart: {
                            style: {
                                fontFamily: 'San Francisco Display'
                            }
                        },
                        credits: {
                            enabled: false
                        }
                    });
                    //================== Creative Hub ===================//
                    //================== CHART DAILY ===================//
                    dailyRevRetail = new Highcharts.Chart({
                        chart: {
                            renderTo: 'daily-revenue-retail',
                            zoomType: 'xy'
                        },
                        title: {
                            text: 'Creative Hub 14-Week Revenue Trend'
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
                                pointPadding: 0,
                                borderWidth: 0,
                                groupPadding: 0.075,
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
                            pointStart: Date.UTC(<?php echo $beginYearcreativehub; ?>, <?php echo $beginMonthcreativehub; ?>, <?php echo $beginDaycreativehub; ?>),
                            pointInterval: 24 * 3600 * 1000 // one day
                        }, {
                            name: '7-Day Moving Average',
                            color: '#89A54E',
                            type: 'line',
                            data: [<?php echo $moveAvgRetail; ?>],
                            marker: {
                                enabled: true
                            },
                            pointStart: Date.UTC(<?php echo $beginYearcreativehub; ?>, <?php echo $beginMonthcreativehub; ?>, <?php echo $beginDaycreativehub; ?>),
                            pointInterval: 24 * 3600 * 1000 // one day
                        }] 
                    });

                    //================== CHART MONTHLY ===================//

                    monthlyRevRetail = new Highcharts.Chart({
                        chart: {
                            renderTo: 'monthly-revenue-retail',
                            defaultSeriesType: 'column',
                        },
                        title: {
                            text: 'Creative Hub Monthly Revenue',
                        },
                        subtitle: {
                            text: 'Total Revenue and Average per Working Day',
                        },
                        xAxis: {
                            categories: [<?php echo implode(", ", $monthsRet); ?>]
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
                                text: 'Total Revenue (millions)',
                                style: {
                                    color: '#666666'
                                }
                            },
                            min: 0,
                        }, {
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
                            min: 0,
                            opposite: true,
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
                                groupPadding: 0.35,
                                shadow: false
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
                            color: '#2a80b9',
                            data: [<?php echo $monthlyRev; ?>]
                        }, {
                            name: 'Average Revenue',
                            color: '#c1392b',
                            type: 'line',
                            yAxis: 1,
                            marker: {
                                enabled: true
                            },
                            data: [<?php echo $monthlyAvgRev; ?>]
                        }]
                    });

                    ChartPieCreativehub = new Highcharts.Chart({
                        chart: {
                            renderTo: 'tophub',
                            plotBackgroundColor: null,
                            plotBorderWidth: null,
                            plotShadow: false
                        },
                        title: {
                            text: 'Creative Hub'
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
                                <?php echo $volTopcreativehub; ?>
                            ]
                        },{
                            type: 'pie',
                            size: 200,
                            innerSize: 50,
                            name: "All Volume",
                            data: [
                                <?php echo $volTopAllcreativehub; ?>
                            ]
                        }]
                    });


                    //================== Kopi Tugu ===================//
                    //================== Chart Daily ===================//
                    
                    revKopiTugu = new Highcharts.Chart({
                    chart: {
                        renderTo: 'rev-kopitugu',
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
                            pointPadding: 0,
                            borderWidth: 0,
                            groupPadding: 0.075,
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
                        data: [<?php echo $revKopiTugu; ?>],	
                        pointStart: Date.UTC(<?php echo $beginYearkopitugu; ?>, <?php echo $beginMonthkopitugu; ?>, <?php echo $beginDaykopitugu; ?>),
                        pointInterval: 24 * 3600 * 1000 // one day
                    }, {
                        name: '7-Day Moving Average',
                        color: '#89A54E',
                        type: 'line',
                        data: [<?php echo $moveAvgRetail; ?>],
                        marker: {
                            enabled: true
                        },
                        pointStart: Date.UTC(<?php echo $beginYearkopitugu; ?>, <?php echo $beginMonthkopitugu; ?>, <?php echo $beginDaykopitugu; ?>),
                        pointInterval: 24 * 3600 * 1000 // one day
                    }] 
                });

                ChartAllKopitugu = new Highcharts.Chart({
                    chart: {
                        renderTo: 'topVol',
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false
                    },
                    title: {
                        text: 'All '
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

                ChartDogu = new Highcharts.Chart({
                    chart: {
                        renderTo: 'topDogu',
                        plotBackgroundColor: null,
                        plotBorderWidth: null,
                        plotShadow: false
                    },
                    title: {
                        text: 'Dogu '
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

                monthlyRevKopitugu = new Highcharts.Chart({
                    chart: {
                        renderTo: 'rev-monthly-kopitugu',
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
                            text: 'Total Revenue (millions)',
                            style: {
                                color: '#666666'
                            }
                        },
                        min: 0,
                    }, {
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
                        min: 0,
                        opposite: true,
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
                            groupPadding: 0.35,
                            shadow: false
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
                        color: '#2a80b9',
                        data: [<?php echo $monthlyRevenue; ?>]
                    }, {
                        name: 'Average Revenue',
                        color: '#c1392b',
                        type: 'line',
                        yAxis: 1,
                        marker: {
                            enabled: true
                        },
                        data: [<?php echo $monthlyAvgRevenue; ?>]
                    }]
                });

                function showChart(chartId) {
                    $(".chart-container").hide();
                    $("#" + chartId).show();
                }

                showChart("rev-creative");

                $("#creativehub").click(function() {
                    showChart("rev-creative");
                });

                $("#kopi-tugu").click(function() {
                    showChart("monthly-revenue-retail-container");
                });
            });
        </script>
    </head>
    <body>
        <?php if ($upperSecurity < 4) { ?>
            <div class="tabs">
                <div id="creativehub" class="tab active">Creativehub</div>
                <div id="kopi-tugu" class="tab">Kopi Tugu</div>
            </div>

            <div id="rev-creative" class="chart-container" style="width: 800px; margin: 0 auto;">
                <div id="daily-revenue-retail" style="width: 800px; margin: 0 auto;"></div>
                <div id="monthly-revenue-retail" style="width: 800px; margin: 0 auto;"></div>
                <div id="tophub" style="width: 800px; margin: 0 auto;"></div>
            </div>

            <div id="monthly-revenue-retail-container" class="chart-container" style="width: 800px; margin: 0 auto;">
                <div id="daily-live-retail" style="width: 800px; margin: 0 auto;"></div>
                <div id="rev-kopitugu" style="width: 800px; margin: 0 auto;"></div>
                <div id="rev-monthly-kopitugu" style="width: 800px; margin: 0 auto;"></div>
                <div id="topChartsContainer" style="display: flex; justify-content: center;">
                    <div id="topVol" style="width: 400px; margin: 0;"></div>
                    <div id="topDogu" style="width: 400px; margin: 0;"></div>
                </div>
            </div>
        <?php } ?>
    </body>
</html>
