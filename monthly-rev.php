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

        #KMC6501,
        #KMC8000,
        #KMC1085 {
            font-size: 9pt;
            color: #666666;
            height: 18px;
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

    <!-- Chart #Monthly -->
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
                        SELECT '2023-11-09' + INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY AS DATE
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
                            SUM(TOT_AMT) AS REVENUE,
                            DATE(CRT_TS) AS DTE,
                            SUM(TOT_AMT) AS TOT_AMT,
                            SUM(TOT_REM) AS TOT_REM
                        FROM RTL_ORD_HEAD
                        WHERE DATE(CRT_TS) AND DEL_NBR = 0
                        GROUP BY DATE(CRT_TS)
                    ) RPT ON TGL.Date = RPT.DTE
                    WHERE TGL.Date AND CURRENT_DATE
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
        });
    </script>
</head>

<body>
    <?php if ($upperSecurity < 4) { ?>
        <div id="monthly-revenue-retail" style="width: 800px; margin: 0 auto;"></div>
    <?php } ?>
</body>

</html>
