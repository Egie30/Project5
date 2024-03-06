<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	if($_GET['NBR']!=""){
		$type=substr($_GET['NBR'],0,1);
		$number=substr($_GET['NBR'],1,strlen($_GET['NBR'])-1);
	}
	$PrnDigTyp=$_GET['PRN_DIG_TYP'];
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
					
	<!-- 1. Add these JavaScript inclusions in the head of your page -->
	<script type="text/javascript" src="framework/charts/js/jquery.min.js"></script>
	<script type="text/javascript" src="framework/charts/js/highcharts.js"></script>
	
	
	<?php
		//Get the bin size -- there might be a better and more automatic calculation than this rule-based approach
		$query="SELECT PRN_DIG_PRC,PRN_DIG_DESC FROM CMP.PRN_DIG_TYP WHERE PRN_DIG_TYP='$PrnDigTyp'";
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		if($row['PRN_DIG_PRC']<10000){
			$bin=50;
		}else{
			$bin=500;
		}
        $title=$row['PRN_DIG_DESC'];
		
		//Get customer data
		if($type=="C"){$table="AND BUY_CO_NBR=$number";}
		if($type=="P"){$table="AND BUY_PRSN_NBR=$number";}
		if($_GET['NBR']=="P0"){$table="";}
		$query="SELECT COALESCE(SUM(VOL),0) AS VOL,PRC FROM (SELECT (ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1)) AS VOL,CEILING((TOT_SUB)/COALESCE((ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1)),1)/$bin)*$bin AS PRC FROM CMP.PRN_DIG_ORD_DET DET INNER JOIN CMP.PRN_DIG_ORD_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR WHERE PRN_DIG_TYP='$PrnDigTyp' AND DATE(DET.CRT_TS)>DATE_SUB(NOW(),INTERVAL 12 MONTH)  $table) DAT GROUP BY PRC";
						
		$result=mysql_query($query);
		$cust='[';
		while($row=mysql_fetch_array($result)){
			$cust.="[".$row['VOL'].",".$row['PRC']."],";
		}
		if(strlen($cust)>1){$cust=substr($cust,0,strlen($cust)-1);}
		$cust.=']';
			
		//Get historica data
		$query="SELECT COALESCE(SUM(VOL),0) AS VOL,PRC FROM (SELECT (ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1)) AS VOL,CEILING((TOT_SUB)/COALESCE((ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1)),1)/$bin)*$bin AS PRC FROM CMP.PRN_DIG_ORD_DET DET INNER JOIN CMP.PRN_DIG_ORD_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR WHERE PRN_DIG_TYP='$PrnDigTyp' AND DATE(DET.CRT_TS)>DATE_SUB(NOW(),INTERVAL 12 MONTH)) DAT GROUP BY PRC";
			
		$result=mysql_query($query);
		
		$count = mysql_num_rows($result);
		
		$data='[';
		$volStr='[';
		$volArr=array();
		$prcArr=array();
		while($row=mysql_fetch_array($result)){
			if($row['VOL']!=0){
				$data.="[".$row['VOL'].",";
				$volArr[]=$row['VOL'];
				$data.=$row['PRC']."],";
				$prcArr[]=$row['PRC'];
				//echo $row['VOL'].",".$row['PRC']."<br/>";
			}
		}
		$data=substr($data,0,strlen($data)-1);
		$data.=']';
		
		list($origa,$origb,$origm,$origs,$origr2)=regressionln($volArr,$prcArr);
		//echo $origa." ".$origb." ".$origm." ".$origs." ".$origr2."<br/>";
		
		//Removing outliers (1 sigma)
		$i=0;
		$inliers='[';
		$outliers='[';
		$volNew=array();
		$prcNew=array();
		foreach($volArr as $vol){
			//echo (log($vol))*$origa+$origb-$origs." ".$prcArr[$i]."<br/>";
			if(((log($vol)*$origa+$origb-$origs)>$prcArr[$i])||((log($vol)*$origa+$origb+$origs)<$prcArr[$i])){
				$outliers.="[".$vol.",".$prcArr[$i].'],';	
			}else{
				$inliers.="[".$vol.",".$prcArr[$i].'],';
				$volNew[]=$vol;
				$prcNew[]=$prcArr[$i];
			}			
			$i++;
		}
		if(strlen($outliers)>1){$outliers=substr($outliers,0,strlen($outliers)-1);}
		$outliers.=']';
		if(strlen($inliers)>1){$inliers=substr($inliers,0,strlen($inliers)-1);}
		$inliers.=']';
		
		//echo "in ".$inliers."<br/> out ".$outliers."<br/>";

		if($inliers!='[]'){
			list($newa,$newb,$newm,$news,$newr2)=regressionln($volNew,$prcNew);
		}else{
			list($newa,$newb,$newm,$news,$newr2)=regressionln($volArr,$prcArr);
		}
		//echo $newa." ".$newb." ".$newm." ".$news." ".$newr2."<br/>";
		
		//Regression plot
		$reg='[';
		$mean='[';
		foreach($volArr as $vol){
			$reg.="[".$vol.",".(log($vol)*$newa+$newb)."],";
			$mean.="[".$vol.",".$newm."],";
		}
		if(strlen($reg)>1){$reg=substr($reg,0,strlen($reg)-1);}
		$reg.=']';
		if(strlen($mean)>1){$mean=substr($mean,0,strlen($mean)-1);}
		$mean.=']';
		
		/*
		echo $inliers."<br />";
		echo $outliers."<br />";
		echo $cust."<br />";
		*/
	?>
	
	<!-- Add the JavaScript to initialize the chart on document ready -->
	<script type="text/javascript">
	
		var chart;
		$(document).ready(function() {

            Highcharts.setOptions({
                colors: [{linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#54b6ff'],[1, '#1169d8']]},
                         {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#4edd19'],[1, '#009c21']]},
                         {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#FED75C'],[1, '#F9CB1D']]},
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

			chart = new Highcharts.Chart({
				chart: {
					renderTo: 'priceCurve',
					defaultSeriesType: 'scatter',
					zoomType: 'xy'
				},
				title: {
					text: '<?php echo $title; ?>',
				},
				subtitle: {
					text: 'Last 12 Months',
				},
				xAxis: {
					title: {
						enabled: true,
						text: 'Volume',
						style: {
							color: '#666666'
						}
					},
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
						text: 'Price',
						style: {
							color: '#666666'
						}
					},
					plotBands: [{
						from: 0,
						to: <?php echo $newm; ?>,
						color: 'rgba(68, 170, 213, 0.1)',
						label: { text: 'Below Mean',
				            style: {
			                  color: '#909090'
   				            }
   				        }

					}]
				}],
				tooltip: {
					formatter: function() {
						return '<b>'+ this.series.name +'</b><br/>'+ Highcharts.numberFormat(this.y, 0);
					}
				},
				legend: {
					layout: 'vertical',
					align: 'left',
					verticalAlign: 'top',
					floating: true,
					x: 600,
					y: 25,
					backgroundColor: '#FFFFFF'
				},
				plotOptions: {
					scatter: {
						marker: {
							radius: 5,
							states: {
								hover: {
									enabled: true,
									lineColor: 'rgb(100,100,100)'
								}
							}
						},
						states: {
							hover: {
								marker: {
									enabled: false
								}
							}
						}
					},
					spline: {
						shadow: false
					},
					line: {
						shadow: false
					}
				},
				series: [{
					name: 'Price Curve',
					data: <?php echo $inliers; ?>,
					color: Highcharts.getOptions().colors[10],					
				},{
					name: 'Outliers',
					data: <?php echo $outliers; ?>,
					color: Highcharts.getOptions().colors[11],
				},{
					name: "Customer's History",
					data: <?php echo $cust; ?>,
					color: Highcharts.getOptions().colors[12],
				},{
					type: 'spline',
					name: 'Regression',
					marker: {
						enabled: false
					},
					data: <?php echo $reg; ?>,
					color: Highcharts.getOptions().colors[13],
				}]
			});
		});
			
	</script>
</head>
<body>
	<div id="priceCurve" style="width: 800px; height: 400px; margin: 0 auto;"></div>
</body>
</html>
<?php
	function regression($xArr,$yArr){
		$i=0;
		foreach($xArr as $x){
			$lnx[]=$x;
			$lnx2[]=pow($x,2);
			$lnxy[]=$x*$yArr[$i];
			$sumlnx+=$x;
			$sumy+=$yArr[$i];
			$sumlnx2+=pow($x,2);
			$sumlnxy+=$x*$yArr[$i];
			//echo "a".log($x)." b".(pow(log($x),2))." c".(log($x)*$yArr[$i])." d".$yArr[$i]."<br/>";
			$i++;
		}
		//echo $sumlnx." ".$sumy." ".$sumlnx2." ".$sumlnxy."<br/>";
		$a=($i*$sumlnxy-$sumlnx*$sumy)/($i*$sumlnx2-pow($sumlnx,2));
		$b=($sumy-$a*$sumlnx)/$i;
		$m=$sumlnxy/$sumlnx;
		$n=$i;
		$i=0;
		foreach($xArr as $x){
			$sumlnxm+=$x*pow(($yArr[$i]-$m),2);
			$SSRes+=pow($yArr[$i]-($x*$a+$b),2);
			$SSTot+=pow($yArr[$i]-$sumy/$n,2);
			$i++;
		}
		//echo $SSRes." S ".$SSTot."<br/>";
		$s=sqrt($sumlnxm/$sumlnx);
		$r2=1-($SSRes/$SSTot);
		return array($a,$b,$m,$s,$r2);
	}
	function regressionLn($xArr,$yArr){
		$i=0;
		$lnx=array();
		$lnx2=array();
		$lnxy=array();
		$sumlnx=0;
		$sumy=0;
		$sumlnx2=0;
		$sumlnxy=0;
		$sumlnxm=0;
		$SSRes=0;
		$SSTot=0;
		foreach($xArr as $x){
			$lnx[]=log($x);
			$lnx2[]=pow(log($x),2);
			$lnxy[]=log($x)*$yArr[$i];
			$sumlnx+=log($x);
			$sumy+=$yArr[$i];
			$sumlnx2+=pow(log($x),2);
			$sumlnxy+=log($x)*$yArr[$i];
			//echo "a".log($x)." b".(pow(log($x),2))." c".(log($x)*$yArr[$i])." d".$yArr[$i]."<br/>";
			$i++;
		}
		
		if($i > 1) {
			$a=($i*$sumlnxy-$sumlnx*$sumy)/($i*$sumlnx2-pow($sumlnx,2));	
		} else {
			$a=($i*$sumlnxy-$sumlnx*$sumy)/1;
		}
		
			
		//echo "=====> ".$i." == ".$sumlnx2." == ".$sumlnx."<br />";
		
		$b=($sumy-$a*$sumlnx)/$i;
		$m=$sumlnxy/$sumlnx;
		$n=$i;
		$i=0;
		foreach($xArr as $x){
			$sumlnxm+=log($x)*pow(($yArr[$i]-$m),2);
			$SSRes+=pow($yArr[$i]-(log($x)*$a+$b),2);
			$SSTot+=pow($yArr[$i]-$sumy/$n,2);
			$i++;
		}
		
		$s=sqrt($sumlnxm/$sumlnx);
		
		if($i > 1) {
			$r2=1-($SSRes/$SSTot);
			}
			else {	
				$r2=1-($SSRes/1);
			}
		
		return array($a,$b,$m,$s,$r2);

		
		
	}
?>
