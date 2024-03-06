<?php
include "framework/database/connect.php";
include "framework/database/connect-cloud.php";
include "framework/functions/default.php";

$query	  = "SELECT CO_NBR_DEF FROM NST.PARAM_LOC";
$result	  = mysql_query($query,$local);
$row 	  = mysql_fetch_array($result);
$conbrDef = $row['CO_NBR_DEF'];

$sql 	= "SELECT * FROM RTL.RTL_STK_HEAD HED 
		   JOIN CMP.COMPANY COM ON HED.SHP_CO_NBR = COM.CO_NBR
		   WHERE COM.ACCT_EXEC_NBR != 'NULL' AND HED.CAT_SUB_NBR IN (273,316) AND HED.IVC_TYP = 'RC' AND HED.DEL_F = 0 AND HED.TOT_AMT > 0 
		   AND HED.UPD_TS > (CURRENT_TIMESTAMP - INTERVAL 1 DAY)  GROUP BY HED.ORD_NBR";
// echo $sql;
$results 	= mysql_query($sql,$local);

if (mysql_num_rows($results)>0)
{
	while($row  = mysql_fetch_array($results))
	{
		$querydel = "DELETE FROM $RTL.RTL_STK_HEAD WHERE ORD_NBR =".$row['ORD_NBR'];
		$resultdel = mysql_query($querydel,$cloud);
		// echo $querydel;

		$query     = "INSERT INTO $RTL.RTL_STK_HEAD(
							ORD_NBR,
							ORD_DTE,
							RCV_CO_NBR,
							REF_NBR,
							SHP_CO_NBR,
							IVC_TYP,
							FEE_MISC,
							DISC_PCT,
							DISC_AMT,
							TOT_AMT,
							PYMT_DOWN,
							PYMT_REM,
							TOT_REM,
							DL_TS,
							SPC_NTE,
							IVC_PRN_CNT,
							DEL_F,
							CRT_TS,
							CRT_NBR,
							UPD_TS,
							UPD_NBR,
							TAX_APL_ID,
							TAX_AMT,
							SLS_PRSN_NBR,
							SLS_TYP_ID,
							VAL_PYMT_DOWN,
							VAL_PYMT_REM,
							PYMT_DOWN_TS,
							PYMT_REM_TS,
							PYMT_TYP,
							CAT_SUB_NBR,
							ACTG_TYP,
							CO_NBR_DEF
							)VALUES (
								'".$row['ORD_NBR']."',
								'".$row['ORD_DTE']."',
								'".$row['RCV_CO_NBR']."',
								'".$row['REF_NBR']."', 
								'".$row['SHP_CO_NBR']."', 
								'".$row['IVC_TYP']."',
								'".$row['FEE_MISC']."',
								'".$row['DISC_PCT']."',
								'".$row['DISC_AMT']."',
								'".$row['TOT_AMT']."',
								'".$row['PYMT_DOWN']."',
								'".$row['PYMT_REM']."',
								'".$row['TOT_REM']."',
								'".$row['DL_TS']."',
								'".$row['SPC_NTE']."',
								'".$row['IVC_PRN_CNT']."',
								'".$row['DEL_F']."',
								'".$row['CRT_TS']."',
								'".$row['CRT_NBR']."',
								'".$row['UPD_TS']."',
								'".$row['UPD_NBR']."',
								'".$row['TAX_APL_ID']."',
								'".$row['TAX_AMT']."',
								'".$row['SLS_PRSN_NBR']."',
								'".$row['SLS_TYP_ID']."',
								'".$row['VAL_PYMT_DOWN']."',
								'".$row['VAL_PYMT_REM']."',
								'".$row['PYMT_DOWN_TS']."',
								'".$row['PYMT_REM_TS']."',
								'".$row['PYMT_TYP']."',
								'".$row['CAT_SUB_NBR']."',
								'".$row['ACTG_TYP']."',
								'".$conbrDef."')";
			// echo $query.'<br>';
			$result = mysql_query($query,$cloud);
	}
}
 else 
{
	while($row  = mysql_fetch_array($results))
		{
			$query     = "INSERT INTO $RTL.RTL_STK_HEAD(
							ORD_NBR,
							ORD_DTE,
							RCV_CO_NBR,
							REF_NBR,
							SHP_CO_NBR,
							IVC_TYP,
							FEE_MISC,
							DISC_PCT,
							DISC_AMT,
							TOT_AMT,
							PYMT_DOWN,
							PYMT_REM,
							TOT_REM,
							DL_TS,
							SPC_NTE,
							IVC_PRN_CNT,
							DEL_F,
							CRT_TS,
							CRT_NBR,
							UPD_TS,
							UPD_NBR,
							TAX_APL_ID,
							TAX_AMT,
							SLS_PRSN_NBR,
							SLS_TYP_ID,
							VAL_PYMT_DOWN,
							VAL_PYMT_REM,
							PYMT_DOWN_TS,
							PYMT_REM_TS,
							PYMT_TYP,
							CAT_SUB_NBR,
							ACTG_TYP,
							CO_NBR_DEF
							)VALUES (
								'".$row['ORD_NBR']."',
								'".$row['ORD_DTE']."',
								'".$row['RCV_CO_NBR']."',
								'".$row['REF_NBR']."', 
								'".$row['SHP_CO_NBR']."', 
								'".$row['IVC_TYP']."',
								'".$row['FEE_MISC']."',
								'".$row['DISC_PCT']."',
								'".$row['DISC_AMT']."',
								'".$row['TOT_AMT']."',
								'".$row['PYMT_DOWN']."',
								'".$row['PYMT_REM']."',
								'".$row['TOT_REM']."',
								'".$row['DL_TS']."',
								'".$row['SPC_NTE']."',
								'".$row['IVC_PRN_CNT']."',
								'".$row['DEL_F']."',
								'".$row['CRT_TS']."',
								'".$row['CRT_NBR']."',
								'".$row['UPD_TS']."',
								'".$row['UPD_NBR']."',
								'".$row['TAX_APL_ID']."',
								'".$row['TAX_AMT']."',
								'".$row['SLS_PRSN_NBR']."',
								'".$row['SLS_TYP_ID']."',
								'".$row['VAL_PYMT_DOWN']."',
								'".$row['VAL_PYMT_REM']."',
								'".$row['PYMT_DOWN_TS']."',
								'".$row['PYMT_REM_TS']."',
								'".$row['PYMT_TYP']."',
								'".$row['CAT_SUB_NBR']."',
								'".$row['ACTG_TYP']."',
								'".$conbrDef."')";
			// echo $query.'<br>';
			$result = mysql_query($query,$cloud);

		}
}

?>