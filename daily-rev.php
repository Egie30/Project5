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
                        CONCAT(MONTH(TGL.Date), '-', YEAR(TGL.Date)) AS ORD_DTE,
                        DAY(TGL.Date) AS ORD_DAY,
                        MONTH(TGL.Date) AS ORD_MONTH,
                        YEAR(TGL.Date) AS ORD_YEAR,
                        COALESCE((RPT.REVENUE), 0) AS REVENUE,
                        TOT_AMT,
                        TOT_REM
                    FROM (
                        SELECT '2023-09-10' + INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY AS DATE
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
                                SUM(TOT_AMT) AS REVENUE,
                                DATE(CRT_TS) AS DTE,
                                SUM(TOT_AMT) AS TOT_AMT,
                                SUM(TOT_REM) AS TOT_REM
                            FROM RTL_ORD_HEAD
                            WHERE DATE(CRT_TS) AND DEL_NBR = 0
                            GROUP BY DATE(CRT_TS)
                            )RPT ON TGL.Date = RPT.DTE
                    WHERE TGL.Date BETWEEN (CURRENT_DATE - INTERVAL 14 WEEK) AND CURRENT_DATE
                    ORDER BY TGL.Date ASC
                    ";

$result = mysql_query($query);

$leadDay = 0;
$beginDayFirst = 0;
$beginMonthFirst = 0;
$beginYearFirst = 0;
$dailyRevRetail = array();
$avgData = array();
$moveAvgRetail = array();

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
}    ?>

	<!-- CHART PIE DOGU -->
	<?php
		$volcreativehub=0;$volAllcreativehub=0;$count=0;
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
                            WHERE DATE(CRT_TS) BETWEEN (CURRENT_DATE - INTERVAL 1 MONTH) AND CURRENT_DATE
                            GROUP BY RTL_BRC, INV_NBR
                            ) CSH ON INV.INV_NBR = CSH.INV_NBR
                WHERE INV.CAT_NBR = 118 AND DEL_NBR = 0 #AND CAT_SUB_NBR = 370 
                AND DATE(CSH.CRT_TS) BETWEEN (CURRENT_DATE - INTERVAL 1 MONTH) AND CURRENT_DATE
                GROUP BY INV.NAME
                ORDER BY INV.UPD_TS DESC";
				
		$result=mysql_query($query);
		// echo "<pre>".$query;
		while($row=mysql_fetch_array($result)){
			if($count<30){
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

            chartcreativehub = new Highcharts.Chart({
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
        });
    </script>
</head>

<body>
    <?php if ($upperSecurity < 4) { ?>
        <div id="daily-revenue-retail" style="width: 800px; margin: 0 auto;"></div>
        <div id="tophub" style="width: 800px; margin: 0 auto;"></div>
    <?php } ?>
</body>

</html>
