<?php
	include "framework/database/connect.php";
	include "framework/security/default.php";
	$Security=getSecurity($_SESSION['userID'],"Executive");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<script type="text/javascript" src="framework/functions/default.js"></script>

</head>

<body class="sub">

<div class="leftmenusel" onclick="changeSiblingUrl('content','home.php');selLeftMenu(this);">Dashboard</div>
<?php if($Security<=5){ ?>
<div class="leftmenu" onclick="changeSiblingUrl('content','metrics.php');selLeftMenu(this);">Metrics</div>
<?php } ?>
<!--<div class="leftmenu" onclick="changeSiblingUrl('content','home.php?ALL=1');selLeftMenu(this);">Dashboard ALL</div>-->
<div class="leftmenu" onclick="changeSiblingUrl('content','info.php');selLeftMenu(this);">Corporate Info</div>
<?php if($Security<=3){ ?>
<div class="leftmenu" onclick="changeSiblingUrl('content','summary.php');selLeftMenu(this);">Daily Flash</div>
<?php } ?>
<?php if($Security<=7){ ?>
<div class="leftmenu" onclick="changeSiblingUrl('content','print-digital-report-customer-trend.php');selLeftMenu(this);">Customer Trend</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','print-digital-report-volume-trend.php');selLeftMenu(this);">Volume Trend</div>
<?php } ?>
<?php if($Security<=6){ ?>
<div class="leftmenu" onclick="changeSiblingUrl('content','print-digital-report-emp-prod.php');selLeftMenu(this);">Sales Performance</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','print-digital-emp-prod-chart.php');selLeftMenu(this);">Productivity</div>
<div class="leftmenu" onclick="changeSiblingUrl('content','scorecard.php');selLeftMenu(this);">Scorecard</div>
<?php } ?>
</body>
</html>