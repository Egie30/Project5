<?php
include "../functions/default.php";
include "../functions/dotmatrix.php";

$rtl = mysql_connect("localhost", "root", "", true);
mysql_select_db("rtl_findiconic", $rtl);

$querys = "SELECT 
	PRN_DIG_TYP, CAT_NBR, CAT_SUB_NBR, PRN_DIG_DESC, PRN_DIG_PRC 
FROM PRN_DIG_TYP TYP
LEFT OUTER JOIN INVENTORY_NEW INV ON TYP.INV_NBR = INV.INV_NBR";
$results = mysql_query($querys);
while($rows = mysql_fetch_array($results)) {
	$query="SELECT COALESCE(MAX(INV_NBR),0)+1 AS NEW_NBR FROM INVENTORY2";
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	$InvNbr=$row['NEW_NBR'];
	
	$InvBcd=LeadZero(Luhn($InvNbr),8);
	
	$query="INSERT INTO INVENTORY2 (INV_NBR, CO_NBR, NAME, CAT_NBR, CAT_SUB_NBR, INV_BCD, INV_PRC, CAT_DISC_NBR, CAT_SHLF_NBR, CAT_PRC_NBR, PRC, SPL_NTE, UPD_TS, UPD_NBR) 
		VALUES 
	(".$InvNbr.", 3, '" . $rows['PRN_DIG_DESC'] . "', " . $rows['CAT_NBR'] . ", " . $rows['CAT_SUB_NBR'] . ", '" . $InvBcd . "', 0, 1, 1, 1, " . $rows['PRN_DIG_PRC'] . ", '" . $rows['PRN_DIG_TYP'] . "',  CURRENT_TIMESTAMP, 1)";
	
	echo $query."<br>";
	$result=mysql_query($query);
}
?>
