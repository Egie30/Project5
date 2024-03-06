<?php
include "framework/database/connect.php";

$BeginDate	= '2019-05-01';

//============================================================//

$query 	= "UPDATE CMP.PRN_DIG_ORD_HEAD HED 
LEFT JOIN CMP.COMPANY COM 
	ON HED.BUY_CO_NBR = COM.CO_NBR
SET HED.ACTG_TYP = 1
WHERE HED.TAX_APL_ID IN ('I','A')
	AND DATE(HED.ORD_TS) >= '".$BeginDate."'
	AND (HED.ACTG_TYP= 0 OR HED.ACTG_TYP IS NULL)
	";
	
$result = mysql_query($query);

echo "<pre>".$query."<br />";

//============================================================//

$query 	= "UPDATE CMP.PRN_DIG_ORD_HEAD HED 
LEFT JOIN CMP.COMPANY COM 
	ON HED.BUY_CO_NBR = COM.CO_NBR
SET HED.ACTG_TYP = 2
WHERE ((HED.TAX_APL_ID NOT IN ('I', 'A') AND COM.TAX_F = 1 AND HED.BUY_CO_NBR IS NOT NULL)
						OR (HED.TAX_APL_ID NOT IN ('I', 'A') AND HED.BUY_CO_NBR IS NULL))
	AND DATE(HED.ORD_TS) >= '".$BeginDate."'
	AND (HED.ACTG_TYP= 0 OR HED.ACTG_TYP IS NULL)
	";
	
$result = mysql_query($query);

echo "<pre>".$query."<br />";

//============================================================//


$query 	= "UPDATE CMP.PRN_DIG_ORD_HEAD HED 
LEFT JOIN CMP.COMPANY COM 
	ON HED.BUY_CO_NBR = COM.CO_NBR
SET HED.ACTG_TYP = 3
WHERE (HED.TAX_APL_ID NOT IN ('I', 'A') AND COM.TAX_F = 0 AND HED.BUY_CO_NBR IS NOT NULL)
	AND DATE(HED.ORD_TS) >= '".$BeginDate."'
	AND (HED.ACTG_TYP= 0 OR HED.ACTG_TYP IS NULL)
	";

$result = mysql_query($query);

echo "<pre>".$query."<br />";

header('Location:cash-day-report.php?TYP=CAD');

?>