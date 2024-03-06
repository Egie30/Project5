<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	if($_GET['NBR']!=""){
		$number=$_GET['NBR'];
	}else{$number='0';}
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
	if($_GET['MONTH']!=''){
		if($_GET['MONTH']=='0'){$month=date('m-Y');}else{$month=$_GET['MONTH'];}
		$query="SELECT 
					ACCT_EXEC_NBR, 
					CASE WHEN COM.ACCT_EXEC_NBR=0 THEN 'Corporate' ELSE PPL.NAME END NAME, 
					SUM(CASE WHEN PRN_DIG_EQP='FLJ320P' AND ACCT_EXEC_NBR=723 THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS FLJ320P, 
					SUM(CASE WHEN PRN_DIG_EQP='KMC6501'  THEN ORD_Q ELSE 0 END) AS KMC6501, 
					SUM(CASE WHEN PRN_DIG_EQP='KMC8000'  THEN ORD_Q ELSE 0 END) AS KMC8000,
					SUM(CASE WHEN PRN_DIG_EQP='KMC1085'  THEN ORD_Q ELSE 0 END) AS KMC1085,
					SUM(CASE WHEN PRN_DIG_EQP='RVS640'  THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS RVS640,
					SUM(CASE WHEN PRN_DIG_EQP='MVJ1624'  THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS MVJ1624,
					SUM(CASE WHEN PRN_DIG_EQP='AJ1800F'  THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS AJ1800F,
					SUM(CASE WHEN PRN_DIG_EQP='HPL375'  THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS HPL375
				FROM CMP.PRN_DIG_ORD_HEAD HED 
					INNER JOIN CMP.PRN_DIG_ORD_DET DET ON HED.ORD_NBR=DET.ORD_NBR 
					INNER JOIN CMP.PRN_DIG_TYP TYP ON TYP.PRN_DIG_TYP=DET.PRN_DIG_TYP 
					LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR
					LEFT OUTER JOIN PEOPLE PPL ON COM.ACCT_EXEC_NBR=PPL.PRSN_NBR 
				WHERE HED.DEL_NBR=0 AND DET.DEL_NBR=0 AND DATE_FORMAT(ORD_TS,'%m-%Y')='$month'  
				GROUP BY ACCT_EXEC_NBR ORDER BY 1,2"; 
				//echo "<pre>".$query;
		$result=mysql_query($query);
		$monthlyProdFLJ320P='[';
		$monthlyProdRVS640='[';
		$monthlyProdKMC6501='[';
		$monthlyProdKMC8000='[';
		$monthlyProdKMC1085='[';
		$monthlyProdAJ1800F='[';
		$monthlyProdMVJ1624='[';
		$monthlyProdHPL375='[';
		$months='[';
		while($row=mysql_fetch_array($result)){
			$monthlyProdFLJ320P.=$row['FLJ320P'].",";
			$monthlyProdRVS640.=$row['RVS640'].",";
			$monthlyProdKMC6501.=$row['KMC6501'].",";
			$monthlyProdKMC8000.=$row['KMC8000'].",";
			$monthlyProdKMC1085.=$row['KMC1085'].",";
			$monthlyProdAJ1800F.=$row['AJ1800F'].",";
			$monthlyProdMVJ1624.=$row['MVJ1624'].",";
			$monthlyProdHPL375.=$row['HPL375'].",";
			$months.="'".$row['NAME']."',";
		}
		$monthlyProdFLJ320P=substr($monthlyProdFLJ320P,0,strlen($monthlyProdFLJ320P)-1);
		$monthlyProdFLJ320P.=']';
		$monthlyProdRVS640=substr($monthlyProdRVS640,0,strlen($monthlyProdRVS640)-1);
		$monthlyProdRVS640.=']';
		$monthlyProdKMC6501=substr($monthlyProdKMC6501,0,strlen($monthlyProdKMC6501)-1);
		$monthlyProdKMC6501.=']';
		$monthlyProdKMC8000=substr($monthlyProdKMC8000,0,strlen($monthlyProdKMC8000)-1);
		$monthlyProdKMC8000.=']';
		$monthlyProdKMC1085=substr($monthlyProdKMC1085,0,strlen($monthlyProdKMC1085)-1);
		$monthlyProdKMC1085.=']';
		$monthlyProdAJ1800F=substr($monthlyProdAJ1800F,0,strlen($monthlyProdAJ1800F)-1);
		$monthlyProdAJ1800F.=']';
		$monthlyProdMVJ1624=substr($monthlyProdMVJ1624,0,strlen($monthlyProdMVJ1624)-1);
		$monthlyProdMVJ1624.=']';
		$monthlyProdHPL375=substr($monthlyProdHPL375,0,strlen($monthlyProdHPL375)-1);
		$monthlyProdHPL375.=']';
		//$months=substr($months,0,strlen($months)-1);
		$months.=']';
		}else{
		$table="AND ACCT_EXEC_NBR=$number";
		if($_GET['NBR']==""){$table="";}
		$query="SELECT 
					DATE_FORMAT(ORD_TS,'%Y') AS ORD_YEAR,
					CAST(DATE_FORMAT(ORD_TS,'%c') AS DECIMAL(2,0)) AS ORD_MONTH,
					DATE_FORMAT(ORD_TS,'%b') AS ORD_MONTH_NM,
					SUM(CASE WHEN PRN_DIG_EQP='FLJ320P' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS FLJ320P,
					SUM(CASE WHEN PRN_DIG_EQP='KMC6501' THEN ORD_Q ELSE 0 END) AS KMC6501,
					SUM(CASE WHEN PRN_DIG_EQP='KMC8000'  THEN ORD_Q ELSE 0 END) AS KMC8000,
					SUM(CASE WHEN PRN_DIG_EQP='KMC1085'  THEN ORD_Q ELSE 0 END) AS KMC1085,
					SUM(CASE WHEN PRN_DIG_EQP='RVS640' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS RVS640,
					SUM(CASE WHEN PRN_DIG_EQP='AJ1800F' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS AJ1800F,
					SUM(CASE WHEN PRN_DIG_EQP='MVJ1624' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS MVJ1624,
					SUM(CASE WHEN PRN_DIG_EQP='HPL375' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS HPL375
				FROM CMP.PRN_DIG_ORD_HEAD HED 
					INNER JOIN CMP.PRN_DIG_ORD_DET DET ON HED.ORD_NBR=DET.ORD_NBR 
					INNER JOIN CMP.PRN_DIG_TYP TYP ON TYP.PRN_DIG_TYP=DET.PRN_DIG_TYP
					LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR
				WHERE HED.DEL_NBR=0 AND DET.DEL_NBR=0 AND ORD_TS>=CURRENT_TIMESTAMP-INTERVAL 13 MONTH $table
				GROUP BY DATE_FORMAT(ORD_TS,'%Y'), DATE_FORMAT(ORD_TS,'%c'), DATE_FORMAT(ORD_TS,'%b')
				ORDER BY 1,2"; 
		//echo "<pre>".$query;
		$result=mysql_query($query);
		$monthlyProdFLJ320P='[';
		$monthlyProdRVS640='[';
		$monthlyProdKMC6501='[';
		$monthlyProdKMC8000='[';
		$monthlyProdKMC1085='[';
		$monthlyProdAJ1800F='[';
		$monthlyProdMVJ1624='[';
		$monthlyProdHPL375='[';
		$months='[';
		while($row=mysql_fetch_array($result)){
			$monthlyProdFLJ320P.=$row['FLJ320P'].",";
			$monthlyProdRVS640.=$row['RVS640'].",";
			$monthlyProdKMC6501.=$row['KMC6501'].",";
			$monthlyProdKMC8000.=$row['KMC8000'].",";
			$monthlyProdKMC1085.=$row['KMC1085'].",";
			$monthlyProdAJ1800F.=$row['AJ1800F'].",";
			$monthlyProdMVJ1624.=$row['MVJ1624'].",";
			$monthlyProdHPL375.=$row['HPL375'].",";
			$months.="'".$row['ORD_MONTH_NM']." ".$row['ORD_YEAR']."',";
		}
		$monthlyProdFLJ320P=substr($monthlyProdFLJ320P,0,strlen($monthlyProdFLJ320P)-1);
		$monthlyProdFLJ320P.=']';
		$monthlyProdRVS640=substr($monthlyProdRVS640,0,strlen($monthlyProdRVS640)-1);
		$monthlyProdRVS640.=']';
		$monthlyProdKMC6501=substr($monthlyProdKMC6501,0,strlen($monthlyProdKMC6501)-1);
		$monthlyProdKMC6501.=']';
		$monthlyProdKMC8000=substr($monthlyProdKMC8000,0,strlen($monthlyProdKMC8000)-1);
		$monthlyProdKMC8000.=']';
		$monthlyProdKMC1085=substr($monthlyProdKMC1085,0,strlen($monthlyProdKMC1085)-1);
		$monthlyProdKMC1085.=']';
		$monthlyProdAJ1800F=substr($monthlyProdAJ1800F,0,strlen($monthlyProdAJ1800F)-1);
		$monthlyProdAJ1800F.=']';
		$monthlyProdMVJ1624=substr($monthlyProdMVJ1624,0,strlen($monthlyProdMVJ1624)-1);
		$monthlyProdMVJ1624.=']';
		$monthlyProdHPL375=substr($monthlyProdHPL375,0,strlen($monthlyProdHPL375)-1);
		$monthlyProdHPL375.=']';
		$months=substr($months,0,strlen($months)-1);
		$months.=']';
		}
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
					text: 'Monthly Employee Productivity',
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
						text: 'Outdoor & A3+',
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
						text: 'Indoor, Direct Fabric, Heat Transfer & Latex',
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
						pointPadding: 0,
						borderWidth: 0,
					groupPadding: 0,
						shadow: false
					}
				},
				series: [{
					name: 'Outdoor',
					data: <?php echo $monthlyProdFLJ320P; ?>
				},{
					name: 'A3+ Full Service',
					data: <?php echo $monthlyProdKMC6501; ?>,
					color: Highcharts.getOptions().colors[7],
				},{
					name: 'A3+ R2S',
					data: <?php echo $monthlyProdKMC8000; ?>,
					color: Highcharts.getOptions().colors[5],
				},{
					name: 'A3+ R2P',
					data: <?php echo $monthlyProdKMC1085; ?>,
					color: Highcharts.getOptions().colors[6],
				},{
					name: 'Indoor',
					yAxis:1,
					data: <?php echo $monthlyProdRVS640; ?>,
					color: Highcharts.getOptions().colors[2],
				},{
					name: 'Direct Fabric',
					yAxis:1,
					data: <?php echo $monthlyProdAJ1800F; ?>,
					color: Highcharts.getOptions().colors[3],
				},{
					name: 'Heat Transfer',
					yAxis:1,
					data: <?php echo $monthlyProdMVJ1624; ?>,
					color: Highcharts.getOptions().colors[4],
				},{
					name: 'Latex',
					yAxis:1,
					data: <?php echo $monthlyProdHPL375; ?>,
					color: Highcharts.getOptions().colors[9],
				}]
			});


		});
			
	</script>
</head>
<body>
	<div id="monthlyProd" style="width: 800px; height: 400px; margin: 0 auto;"></div>
</body>
</html>