<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/alert/alert.php";
	$Security=getSecurity($_SESSION['userID'],"Inventory");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

<script type="text/javascript" src="framework/functions/default.js"></script>

<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />

</head>

<body>
<div class="toolbar">
	<div class="toolbar-text">
		Gudang
		<select onchange="getContent('report','stationery-report-whse-disp.php?WHSE_NBR='+this.value);">
			<?php
				$query="SELECT WHSE_NBR,WHSE_DESC FROM CMP.WHSE_LOC ORDER BY 2";
				genCombo($query,"WHSE_NBR","WHSE_DESC",1);
			?>
		</select>
	</div>
</div>

<div id="report"></div>
<script>getContent('report','stationery-report-whse-disp.php?WHSE_NBR=2');</script>

</body>
</html>
