<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	
	$query	= "SELECT CO_NBR_CMPST FROM NST.PARAM_PAYROLL WHERE CO_NBR = ".$CoNbrDef;
	$result	= mysql_query($query);
	$row 	= mysql_fetch_array($result);	
	$CoNbr	= $row['CO_NBR_CMPST'];
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
    <script type="text/javascript" src="framework/slider/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="framework/charts3/js/highcharts.js"></script>
    <script type="text/javascript" src="framework/charts3/js/highcharts-more.js"></script>

	<?php
		$query="SELECT
					PRSN_NBR,
					NAME,
					COALESCE(HED.HED_Q, 0) AS HED_Q,
					COALESCE(JRN.JRN_Q, 0) AS JRN_Q,
					COALESCE(THED.HED_Q, 0) AS THED_Q,
					COALESCE(TJRN.JRN_Q, 0) AS TJRN_Q,
					COALESCE(HED.HED_Q, 0) + COALESCE(JRN.JRN_Q, 0) + COALESCE(THED.HED_Q, 0) + COALESCE(TJRN.JRN_Q, 0) AS ALL_POINT
				FROM CMP.PEOPLE PPL
				LEFT OUTER JOIN
				(
					SELECT
						HED.ORD_NBR AS ORD_NBR,
						HED.CRT_NBR AS CRT_NBR,
						COUNT(DISTINCT(HED.ORD_NBR)) AS HED_Q
					FROM CMP.PRN_DIG_ORD_HEAD HED
						LEFT OUTER JOIN CMP.PRN_DIG_ORD_DET DET ON HED.ORD_NBR=DET.ORD_NBR
					WHERE HED.DEL_NBR=0 AND DET.DEL_NBR=0 AND DATE(ORD_TS) BETWEEN (CURRENT_DATE - INTERVAL 30 DAY) AND CURRENT_DATE
					GROUP BY HED.CRT_NBR
				)HED ON PPL.PRSN_NBR=HED.CRT_NBR
				LEFT OUTER JOIN(
					SELECT 
						ORD_NBR,
						CRT_NBR,
						COUNT(ORD_NBR) AS JRN_Q
					FROM CMP.JRN_PRN_DIG
					WHERE DATE(CRT_TS) BETWEEN (CURRENT_DATE - INTERVAL 30 DAY) AND CURRENT_DATE
					GROUP BY CRT_NBR
				)JRN ON PPL.PRSN_NBR=JRN.CRT_NBR
				LEFT OUTER JOIN (
					SELECT
						HED.TRNSP_NBR AS TRNSP_NBR,
						HED.CRT_NBR AS CRT_NBR,
						COUNT(DISTINCT(HED.TRNSP_NBR)) AS HED_Q
					FROM CMP.TRNSP_HEAD HED
						LEFT OUTER JOIN CMP.TRNSP_DET DET ON HED.TRNSP_NBR=DET.TRNSP_NBR
					WHERE HED.DEL_NBR=0 AND DET.DEL_NBR=0 AND DATE(TRNSP_TS) BETWEEN (CURRENT_DATE - INTERVAL 30 DAY) AND CURRENT_DATE
					GROUP BY HED.CRT_NBR
				)THED ON PPL.PRSN_NBR=THED.CRT_NBR
				LEFT OUTER JOIN(
					SELECT 
						TRNSP_NBR,
						CRT_NBR,
						COUNT(TRNSP_NBR) AS JRN_Q
					FROM CMP.JRN_TRNSP
					WHERE DATE(CRT_TS) BETWEEN (CURRENT_DATE - INTERVAL 30 DAY) AND CURRENT_DATE
					GROUP BY CRT_NBR
				)TJRN ON PPL.PRSN_NBR=TJRN.CRT_NBR
				WHERE PPL.CO_NBR IN (".$CoNbr.") AND PPL.DEL_NBR=0 AND TERM_DTE IS NULL
				GROUP BY PPL.PRSN_NBR
				ORDER BY 7 DESC LIMIT 20
			";
		
		//echo "<pre>".$query;
		
		$result=mysql_query($query);
		$personNumber='[';
		$personName ='[';
		$point='[';
		while($row=mysql_fetch_array($result)){
			$personNumber.=$row['PRSN_NBR'].",";
			$personName .="'".$row['NAME']."',";
			$point .=$row['ALL_POINT'].",";
		}
		$personNumber=substr($personNumber,0,strlen($personNumber)-1);
		$personNumber.=']';
		$personName=substr($personName,0,strlen($personName)-1);
		$personName.='],';
		$point=substr($point,0,strlen($point)-1);
		$point.=']';
	?>

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
            
		$('#container').highcharts({
			chart: {
               type: 'bar'
			},
			title: {
				text: 'Digital Print Productivity (30-Day)',
				defaultSeriesType: 'column',
			},
			xAxis: {
				categories: <?php echo $personName; ?>
				title: {
					text: null
				}
			},
			yAxis: {
				min: 0,
				title: {
					text: 'Productivity (Hits)',
					align: 'high'
				},
				labels: {
					overflow: 'justify'
				}
			},
			tooltip: {
					formatter: function() {
						return '<b>'+ this.x +'</b><br/>Hits Total : '+ Highcharts.numberFormat(this.y, 0);
					}
			},
			plotOptions: {
				bar: {
					dataLabels: {
						enabled: true
					}
				}
			},
			legend: {
				enabled: false
			},
			credits: {
				enabled: false
			},
			series: [{
				data: <?php echo $point; ?>
			}]
		});
	});
			
	</script>
</head>
<body>
	<div id="container" style="width: 1000px; height: 100%; margin: 0 auto;"></div>
</body>
</html>