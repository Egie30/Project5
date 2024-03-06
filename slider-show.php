<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<script type="text/javascript" src="framework/database/jquery.min.js"></script>
<script type="text/javascript" src="framework/slider/coin-slider.min.js"></script>
<link rel="stylesheet" href="framework/slider/coin-slider-styles.css" type="text/css" />

<script type="text/javascript">
	$(document).ready(function(){
		$('#coin-slider').coinslider({
			width: 800, 
			navigation: false, 	
			delay: 10000, // delay between images in ms
			height: 480, // height of slider panel
			spw: 2, // squares per width
			sph: 1, // squares per height
			sDelay: 100, // delay beetwen squares in ms
			opacity: 0, // opacity of title and navigation
			//titleSpeed: 500, // speed of title appereance in ms
			effect: 'rain', // random, swirl, rain, straight
			navigation: false, // prev next and buttons
			links : false, // show images as links
			hoverPause: false // pause on hover
			
		});
	});
</script>

</head>

<body style="padding:0;margin:0;border:none;overflow: hidden;">
	<div style="width:800;height:480;">
		<?php
		$query="SELECT SLDR_IMG FROM CMP.SLIDER WHERE SLDR_STAT=1 ORDER BY SLDR_NBR ASC";
		$result=mysql_query($query);
		while($row=mysql_fetch_array($result)){
		?>
		<video id="hero-video" loop="" muted="" autoplay="" poster="img/background1.jpg" class="bg-video" width="800" height="480">
			<source src="img/slider/<?php echo $row['SLDR_IMG']; ?>" type="video/mp4">
		</video>
		<?php } ?>
	</div>
</body>
</html>