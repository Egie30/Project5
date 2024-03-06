<html style="height:140px">
<head>
<script src="framework/highcharts/highcharts.js"></script>
<script src="framework/highcharts/highcharts-more.js"></script>
<script src="framework/highcharts/modules/solid-gauge.js"></script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
</head>
<body style="height:140px;margin:0px;padding:0px">
<div id="container" style="width: 140px; height: 140px; margin: 0 auto;">
</div>
<?php
//$total		= $_GET['TOTAL']; //jumlah orderan
$printing	= $_GET['PRINTING']; //jumlah diprint
$finishing	= $_GET['FINISHING']; //jumlah difinishing
$ready		= $_GET['READY']; //jumlah sudah selesai
$prod		= $_GET['PROD']; //nilai maksimal
//$nilai_max	= max($total-$printing, $total-$finishing, $ready);
//$nilai_max2	= ($total-$printing)+($total-$finishing)+$ready;
//echo "nilai maksimal ".$nilai_max;

?>
<script>

if (!Highcharts.theme) {
    Highcharts.setOptions({
        colors: [{linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#54b6ff'],[1, '#1169d8']]},
                 {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#4edd19'],[1, '#009c21']]},
                 {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#FED75C'],[1, '#F9CB1D']]}],
        tooltip: {
                style: {
                    color: '#666666'
                }
            }
    });
}

Highcharts.chart('container', {
    chart: {
        type: 'solidgauge'
    },

    title: {
        text: ''
    },

    tooltip: {
        useHTML: true,
        borderWidth: 0,
        backgroundColor: 'none',
        borderColor: '#ff0000',
        shadow: false,
        style: {
            fontSize: '10px',
            fontFamily: 'San Francisco Display',
            fontColor: '#666666'
            //fontFamily: 'San Francisco Display', 'HelveticaNeue-Light', 'Helvetica Neue Light', 'Helvetica', 'Arial', 'sans-serif'
            //<div style="font-size:1em; color: {point.color}; font-weight: bold; text-align: center">{point.val}</div>
        },
        pointFormat: '<div style="font-size:1em; color: #666666; text-align: center;">{series.name}<br/><span style="font-size:2em; color: #666666; font-weight: bold; line-height: 15px;">{point.val}</span></div>',
        positioner: function (labelWidth) {
            return {
                x: 70 - labelWidth / 2,
                y: 43
            };
        }
    },
	
	credits: {
		enabled: false
	},

    pane: {
        startAngle: 0,
        endAngle: 360,
        background: [{ // Track for Move
            outerRadius: '112%',
            innerRadius: '88%',
            backgroundColor: Highcharts.Color('#54b6ff')
                .setOpacity(0.3)
                .get(),
            borderWidth: 0
        }, { // Track for Exercise
            outerRadius: '87%',
            innerRadius: '63%',
            backgroundColor: Highcharts.Color('#4edd19')
                .setOpacity(0.3)
                .get(),
            borderWidth: 0
        }, { // Track for Stand
            outerRadius: '62%',
            innerRadius: '38%',
            backgroundColor: Highcharts.Color('#FED75C')
                .setOpacity(0.3)
                .get(),
            borderWidth: 0
        }]
    },

    yAxis: { //digunakan untuk skala
        min: 0,
        max: 1,//<?php echo $prod; ?>,
        lineWidth: 0,
        tickPositions: []
    },

    plotOptions: {
        solidgauge: {
            dataLabels: {
                enabled: false
            },
            linecap: 'round',
            stickyTracking: false,
            rounded: true
        }
    },

    series: [{
        name: 'Printing',
        data: [{
            color: Highcharts.getOptions().colors[0],
            radius: '112%',
            innerRadius: '88%',
            y: <?php echo $printing/$prod; ?>, //jumlah belum diprint
			val: <?php echo $printing ?>
        }]
    }, {
        name: 'Finishing',
        data: [{
            color: Highcharts.getOptions().colors[1],
            radius: '87%',
            innerRadius: '63%',
            y: <?php echo $finishing/$prod; ?>, //jumlah belum difinishing
			val: <?php echo $finishing; ?>
        }]
    }, {
        name: 'Ready',
        data: [{
            color: Highcharts.getOptions().colors[2],
            radius: '62%',
            innerRadius: '38%',
            y: <?php echo $ready/$prod; ?>, //jumlah sudah jadi
			val: <?php echo $ready; ?>
        }]
    }]
},

/**
 * In the chart load callback, add icons on top of the circular shapes
 */
function callback() {

    // Move icon
    this.renderer.path(['M', -8, 0, 'L', 8, 0, 'M', 0, -8, 'L', 8, 0, 0, 8])
        .attr({
            'stroke': '#303030',
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            'stroke-width': 2,
            'zIndex': 10
        })
        .translate(190, 26)
        .add(this.series[2].group);

    // Exercise icon
    this.renderer.path(['M', -8, 0, 'L', 8, 0, 'M', 0, -8, 'L', 8, 0, 0, 8,
            'M', 8, -8, 'L', 16, 0, 8, 8])
        .attr({
            'stroke': '#ffffff',
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            'stroke-width': 2,
            'zIndex': 10
        })
        .translate(190, 61)
        .add(this.series[2].group);

    // Stand icon
    this.renderer.path(['M', 0, 8, 'L', 0, -8, 'M', -8, 0, 'L', 0, -8, 8, 0])
        .attr({
            'stroke': '#303030',
            'stroke-linecap': 'round',
            'stroke-linejoin': 'round',
            'stroke-width': 2,
            'zIndex': 10
        })
        .translate(190, 96)
        .add(this.series[2].group);
});
</script>
</body>
</html>
