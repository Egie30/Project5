<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	$type=$_GET['TYPE'];
	if($_GET['TYPE']!=""){
		$type="WHERE PRN_DIG_TYP='$type'";
	}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
					
	<!-- 1. Add these JavaScript inclusions in the head of your page -->
    <script type="text/javascript" src="framework/slider/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="framework/charts3/js/highcharts.js"></script>
    <script type="text/javascript" src="framework/charts3/js/highcharts-more.js"></script>
	
	<!-- Optional: add a theme file -->
	<!--
		<script type="text/javascript" src="../js/themes/gray.js"></script>
	-->

	<!-- Chart #3 -->
	<?php
		$query="SELECT * FROM (SELECT YEAR(CRT_TS) AS YR,MONTH(CRT_TS) AS MO,SUM(ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1)) AS VOL FROM CMP.PRN_DIG_ORD_DET $type GROUP BY YEAR(CRT_TS),MONTH(CRT_TS) ORDER BY 1 DESC, 2 DESC LIMIT 24) DAT WHERE MO IS NOT NULL ORDER BY YR,MO";
		$result=mysql_query($query);
		$vol='[';
		$months='[';
		while($row=mysql_fetch_array($result)){
			$vol.=$row['VOL'].",";
			$months.="'";
			$months.=date("M", mktime(0,0,0,$row['MO'], 10));
			//$months.=str_pad($row['MO'],2,'0',STR_PAD_LEFT);
			$months.=" ".$row['YR']."',";
		}
		$vol=substr($vol,0,strlen($vol)-1);
		$vol.=']';
		$months=substr($months,0,strlen($months)-1);
		$months.=']';
	?>

	<!-- Add the JavaScript to initialize the chart on document ready -->
	<script type="text/javascript">
	
		var chart3;
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

			chart3 = new Highcharts.Chart({
				chart: {
					renderTo: 'monthlyProd',
					defaultSeriesType: 'column',
				},
				title: {
					text: 'Monthly Production Output',
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
						text: 'Volume',
						style: {
							color: '#666666'
						}
					},
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
						pointPadding: .1,
						borderWidth: 0,
			            groupPadding: 0,
						shadow: false
			        }
			    },
				series: [{
					name: 'Volume',
					data: <?php echo $vol; ?>
				}]
			});


		});
			
	</script>
</head>
<body>
	<div id="monthlyProd" style="width: 800px; height: 400px; margin: 0 auto;"></div>
</body>
</html>