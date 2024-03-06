<?php
	include "../framework/database/connect.php";
	include "../framework/functions/default.php";
	include "../framework/security/default.php";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" class='iframe'>

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

<script type="text/javascript" src="../framework/functions/default.js"></script>

<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />

<script type="text/javascript" src="../framework/clock/coolclock.js"></script>
<script type="text/javascript" src="../framework/clock/moreskins.js"></script>

</head>

<body class='iframe' onload="CoolClock.findAndCreateClocks()">
<div class='title'>
	Summary
</div>
<canvas id="clk2" style="display:block;position:absolute;top:765px;left:500px;" class="CoolClock:swissRail"></canvas>

<table>
<?php
	$query="SELECT HED.ORD_STT_ID AS STT_ID,ORD_STT_DESC,COUNT(*) AS NBR_ORD FROM CMP.PRN_DIG_ORD_HEAD HED INNER JOIN CMP.PRN_DIG_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID WHERE HED.ORD_STT_ID IN ('QU','PR','FN','DL','RD') GROUP BY 1,2 ORDER BY ORD_STT_ORD";
	$result=mysql_query($query);
	while($row=mysql_fetch_array($result)){
		if($row['STT_ID']!='FN'){echo "<tr>";}
		echo "<td";
		if($row['STT_ID']!='PR'){echo " colspan=2";}
		echo ">";
		echo "<div class='summary'>";
		echo "<div style='padding-top:5px'><b>".$row['ORD_STT_DESC']."</b></div>";
		echo "<span class='summary-large'>".number_format($row['NBR_ORD'],0,",",".")."</span><br/>";
		$query="SELECT COUNT(*) AS NBR_ITEM,SUM(ORD_Q) AS ITEM_CNT FROM CMP.PRN_DIG_ORD_DET DET INNER JOIN CMP.PRN_DIG_ORD_HEAD HED ON DET.ORD_NBR=HED.ORD_NBR WHERE ORD_STT_ID='".$row['STT_ID']."'";
		$resultd=mysql_query($query);
		$rowd=mysql_fetch_array($resultd);
		echo number_format($rowd['NBR_ITEM'],0,",",".")." <span class='summary-label'>Items</span>&nbsp;";
		echo number_format($rowd['ITEM_CNT'],0,",",".")." <span class='summary-label'>Pieces</span><br/>";
		echo "</div>";
		echo "</td>";
		if($row['STT_ID']!='PR'){echo "</tr>";}
	}
?>
</table>
			
</body>
</html>