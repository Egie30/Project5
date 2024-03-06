<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/alert/alert.php";
	include "framework/security/default.php";
	
	$security=getSecurity($_SESSION['userID'],"DigitalPrint");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

<script type="text/javascript" src="framework/functions/default.js"></script>

<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />

</head>

<body>
	<p>
		<?php
			$query="SELECT PRN_DIG_EQP,PRN_DIG_EQP_DESC FROM CMP.PRN_DIG_EQP ORDER BY 1";
			$result=mysql_query($query);
			while($row=mysql_fetch_array($result))
			{
				echo "<div>";
				echo "<label>&nbsp;".$row['PRN_DIG_EQP_DESC']." (".$row['PRN_DIG_EQP'].")</label><br />";
				echo "</div>";
				echo "<div id='".$row['PRN_DIG_EQP']."'></div></br></br>";
				echo "<script>getContent('".$row['PRN_DIG_EQP']."','summary-list.php?PRN_DIG_EQP=".$row['PRN_DIG_EQP']."');</script>";
			}
		?>
	</p>			
</body>
</html>