<?php
include "framework/database/connect.php";

$query = "SELECT PRSN_NBR, PAY_BASE, PAY_ADD FROM PAY.PEOPLE WHERE PRSN_NBR=".$_POST['PRSN_NBR'];
$result= mysql_query($query);
$row   = mysql_fetch_array($result);

if (mysql_num_fields($result)>0){
	echo json_encode($row);
}
