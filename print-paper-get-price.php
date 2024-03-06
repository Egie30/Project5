<?php
require_once "framework/database/connect.php";

$PRN_PPR_EQP_TYP = $_GET['PRN_PPR_EQP_TYP'];

echo "<option value=''>Pilih Plat</option>";
$query = "SELECT NBR,PRN_PPR_EQP_PRC_TYP,PRN_PPR_EQP_PRC FROM CMP.PRN_PPR_EQP_PRC WHERE PRN_PPR_EQP = '".$PRN_PPR_EQP_TYP."'  ORDER BY 2";
$results=mysql_query($query);
while($row=mysql_fetch_array($results)){
	echo "<option value=\"".$row['NBR']."\">".$row['PRN_PPR_EQP_PRC_TYP']."</option>\n";
}
?>	
