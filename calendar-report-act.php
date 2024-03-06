<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/alert/alert.php";
	$Security=getSecurity($_SESSION['userID'],"Inventory");
	$CalNbr=$_GET['CAL_NBR'];
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

<script type="text/javascript" src="framework/functions/default.js"></script>
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<script type="text/javascript" src='framework/tablesort/tablesort.js'></script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />

<link rel="stylesheet" href="framework/combobox/chosen.css">

</head>

<body>
<div class="toolbar">	
<?php
	$query="SELECT CMP.CO_ID,CAL_ID,CAL_DESC,CAL_TYP FROM CMP.CAL_LST LST INNER JOIN CMP.COMPANY CMP ON LST.CO_NBR=CMP.CO_NBR WHERE CAL_NBR=".$CalNbr;
	//echo $query;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
?>
</br>
<strong><?php echo $row['CO_ID'].$row['CAL_ID'].$row['CAL_TYP']." ".$row['CAL_DESC']; ?></strong>

<div id="report"></div>
<script>getContent('report','calendar-report-act-disp.php?CAL_NBR=<?php echo $CalNbr; ?>');</script>
<br />

</body>
</html>
