<?php
$shipper	= mysql_connect("192.168.1.20","nestor","{;?qSpu!Fw)^jX8E","RTL"); //192.168.1.20
$receiver 	= mysql_connect("192.168.1.10","nestor","cG>cFk3q!RMh]qa#","RTL"); //192.168.1.10

if(isset($_POST['submit'])){
	//============================================================HEAD
	$queryShipper 	= "SELECT * FROM RTL.RTL_STK_HEAD WHERE DEL_F = 0 AND ORD_NBR IN (". $_GET['ORD_NBR'] .")";
	//echo $queryShipper.'<br/>';
	$resultShipper 	= mysql_query($queryShipper, $shipper);

	while ($rowShipper=mysql_fetch_array($resultShipper)) {
		$primary 	= explode(',', 'ORD_NBR');
		$whereDel	= "";
		for($i=0;$i<count($primary);$i++){
			$whereDel .= $primary[$i]."='".$rowShipper[$primary[$i]]."', ";
		}
			
		$queryDel 	= "DELETE FROM RTL.RTL_STK_HEAD WHERE ".substr($whereDel, 0, -2);
		mysql_query($queryDel, $receiver);

		$query_clm	= "SELECT 
			COLUMN_NAME, IS_NULLABLE 
		FROM INFORMATION_SCHEMA.COLUMNS 
		WHERE TABLE_SCHEMA = 'RTL' AND TABLE_NAME = 'RTL_STK_HEAD'";	
		$result_clm	= mysql_query($query_clm, $receiver);
		$val 		= "";
		while($row_clm=mysql_fetch_assoc($result_clm)) {
			$col 	= $row_clm['COLUMN_NAME'];
			if(($row_clm['IS_NULLABLE']=='YES')&&($rowShipper[$col]=="")){
				$val 	.= "NULL, ";
			} else {
				$val 	.= "'".$rowShipper[$col]."', ";
			}
		}

		$queryIns 	= "INSERT INTO RTL.RTL_STK_HEAD VALUES (".substr($val, 0, -2).")";
		mysql_query($queryIns, $receiver);

		//echo $queryDel.'<br/>';
		//echo $queryIns.'<br/>';
	}

	//============================================================DETAIL
	$queryShipper 	= "SELECT * FROM RTL.RTL_STK_DET WHERE ORD_NBR IN (". $_GET['ORD_NBR'] .")";
	$resultShipper 	= mysql_query($queryShipper, $shipper);

	while ($rowShipper=mysql_fetch_array($resultShipper)) {
		
		$primary 	= explode(',', 'ORD_DET_NBR');
		$whereDel	= "";
		for($i=0;$i<count($primary);$i++){
			$whereDel .= $primary[$i]."='".$rowShipper[$primary[$i]]."', ";
		}
			
		$queryDel 	= "DELETE FROM RTL.RTL_STK_DET WHERE ".substr($whereDel, 0, -2);
		mysql_query($queryDel, $receiver);

		$query_clm	= "SELECT 
			COLUMN_NAME, IS_NULLABLE 
		FROM INFORMATION_SCHEMA.COLUMNS 
		WHERE TABLE_SCHEMA = 'RTL' AND TABLE_NAME = 'RTL_STK_DET'";	
		$result_clm	= mysql_query($query_clm, $receiver);
		$val 		= "";
		while($row_clm=mysql_fetch_assoc($result_clm)) {
			$col 	= $row_clm['COLUMN_NAME'];
			if(($row_clm['IS_NULLABLE']=='YES')&&($rowShipper[$col]=="")){
				$val 	.= "NULL, ";
			} else {
				$val 	.= "'".$rowShipper[$col]."', ";
			}
		}

		$queryIns 	= "INSERT INTO RTL.RTL_STK_DET VALUES (".substr($val, 0, -2).")";
		mysql_query($queryIns, $receiver);

		//echo $queryDel.'<br/>';
		//echo $queryIns.'<br/>';
	}
	$status = 'Selesai';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/popup.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
</head>
<body style='width:250px'>

<span class='fa fa-times toolbar' style='cursor:pointer' onclick="parent.document.getElementById('printDigitalPopupBarcode').style.display='none';parent.document.getElementById('fade').style.display='none'"></span><br><br>
<form enctype="multipart/form-data" action="#" method="post" style="width: 100%; box-sizing: border-box;text-align:center;">
	<input class="process" name="submit" type="submit" value="Proses"/><br><br><b><?php echo $status; ?></b>
</form>

</body>
</html>