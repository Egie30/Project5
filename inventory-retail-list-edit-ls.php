<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/functions/dotmatrix.php";
	//echo "aaa";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<script src="framework/database/jquery.min.js" type="text/javascript"></script>

<?php
	$query 	="SELECT INV.CNT_Y, INV.CNT_Y_TYP, Y.UNIT_DESC AS DESC_Y, INV.PRC_Y, INV.CNT_Z, INV.CNT_Z_TYP, Z.UNIT_DESC AS DESC_Z, INV.PRC_Z FROM RTL.INVENTORY INV 
				LEFT JOIN RTL.UNIT_TYP Y ON INV.CNT_Y_TYP=Y.UNIT_TYP
				LEFT JOIN RTL.UNIT_TYP Z ON INV.CNT_Z_TYP=Z.UNIT_TYP 
				WHERE INV.INV_NBR = '" . $_GET['q'] . "'";
	//echo $query;
	$results = mysql_query($query);
	$row	= mysql_fetch_array($results);
	//echo $row['CNT_Y'];
	//if (($row['CNT_Y'] && $row['CNT_Z']) !="") {
?>
		<label>Konversi Satuan</label><br /><div class='labelbox'></div>
<?php
	if ($row['CNT_Y']!=""){
?>
		<input name='CONV_CHD_Y' id='CONV_CHD_Y' type='checkbox' class='regular-checkbox'>
		<label for="CONV_CHD_Y" style="float:left;"></label><div style="float: left;padding-right: 5px;"><?php echo "@".$row["CNT_Y"]."=Rp ".$row["PRC_Y"]." (1 ".$row["DESC_Y"].")"; ?></div>&nbsp;&nbsp;&nbsp;

<?php
	}
	if ($row['CNT_Z']!=""){
?>
		<input name='CONV_CHD_Z' id='CONV_CHD_Z' type='checkbox' class='regular-checkbox'>
		<label for="CONV_CHD_Z" style="float:left;"></label><div style="float:left;padding-right: 5px;"><?php echo "@".$row["CNT_Z"]."=Rp ".$row["PRC_Z"]." (1 ".$row["DESC_Z"].")"; ?></div>
<?php		
	}
	//}
?>	

<!--
        <select name="CONV_CHD" id="CONV_CHD" class="chosen-select">
        	<option value="">Kosong</option>
			<option value=""><?php echo $row["CNT_Y"]; ?></option>
			<option value=""><?php echo $row["CNT_Z"]; ?></option>
		</select><br/><div class="combobox"></div>
	-->