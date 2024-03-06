<?php
include "framework/database/connect.php";

$ppnType 	= $_GET['ppntype'];
$query 		= "SELECT ACTG_TYP FROM CMP.COMPANY WHERE CO_NBR='".$ppnType."'";
$result		= mysql_query($query);
$row 		= mysql_fetch_array($result);

if($row['ACTG_TYP']==1){
	$TaxApl = "A";
} else {
	$TaxApl = "E";
}
$query 		= "SELECT TAX_APL_ID, TAX_APL_DESC FROM CMP.TAX_APL WHERE TAX_APL_ID='$TaxApl'";
$result		= mysql_query($query);
$row 		= mysql_fetch_array($result);

?>
<option value="<?php echo $TaxApl; ?>"><?php echo $row["TAX_APL_DESC"]; ?></option>
<?php
$queryppn	= "SELECT TAX_APL_ID,TAX_APL_DESC
				FROM CMP.TAX_APL ORDER BY SORT";
$resultppn	= mysql_query($queryppn);
while($rowppn = mysql_fetch_array($resultppn))
{
?>
	<option value="<?php echo $rowppn["TAX_APL_ID"]; ?>"><?php echo $rowppn["TAX_APL_DESC"]; ?></option>
<?php	
}
?>