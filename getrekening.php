<?php
include "framework/database/connect.php";

$rekType 	= $_GET['rektype'];
$query 		= "SELECT ACTG_TYP FROM CMP.COMPANY WHERE CO_NBR='".$rekType."'";
$result		= mysql_query($query);
$row 		= mysql_fetch_array($result);

if($row['ACTG_TYP']>0){
	$RekNbr = $row['ACTG_TYP'];
	$RekDesc= $row['ACTG_TYP'];
} else {
	$RekNbr = "";
	$RekDesc= "Pilih";
}

?>
<option value="<?php echo $RekNbr; ?>"><?php echo $RekDesc; ?></option>
<option value="">Pilih</option>
<option value="1">1</option>
<option value="2">2</option>
<option value="3">3</option>