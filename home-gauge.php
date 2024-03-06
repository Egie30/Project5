<html style='overflow:hidden'>
<script type="text/javascript" src="framework/slider/jquery-1.9.1.min.js"></script>
<script type="text/javascript">
	$(function () {
		$('#container').highcharts({
		    chart: {
		        type: 'gauge',
		        height: 110,
   	         	width:160
		    },
			credits: {
   	     		enabled: false
			},
			tooltip: {
        		enabled: false,
         	},
			exporting: {
				enabled: false
			},
			title: {
		        text: ''
		    },
		    pane: [{
		        startAngle: -60,
		        endAngle: 60,
		        background: null,
		        center: ['50%','90%'],
		        size: 150
		    }],	    		        
	
		    yAxis: [{
		        min: 0,
		        max: 1,
	            tickWidth: 0,
	            minorTickWidth: 0,
	            lineWidth: 0,
	            endOnTick: false,
		        labels: {
	                enabled: false
	            },
		        
		        plotBands: [{
		        	from: 0.7,
		        	to: 1,
		        	color: '#7abada',
		        	innerRadius: '40%',
		        	outerRadius: '100%'
		        },{
		        	from: 0,
		        	to: 0.7,
		        	color: '#e0e0e0',
		        	innerRadius: '40%',
		        	outerRadius: '100%'
		        }],
		        pane: 0
		    }],
		    
		    plotOptions: {
		    	gauge: {
		    		dataLabels: {
		    			enabled: false
		    		},
		    		dial: {
		    			radius: '105%',
						rearLength: 0,
						baseWidth: 10,
						baseLength: 0,
						backgroundColor: '#666666',
						borderColor: '#666666'
		    		}
		    	}
		    },
		    	
		    series: [{
		        data: [<?php echo $_GET['VAL']; ?>],
		        yAxis: 0
			}]
		
		});
	});
</script>
<script src="framework/charts3/js/highcharts.js"></script>
<script src="framework/charts3/js/highcharts-more.js"></script>
<div id="container" style='height:50px;width:160px;margin-left:auto;margin-right:auto'></div>
</html>