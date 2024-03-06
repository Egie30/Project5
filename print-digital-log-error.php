<?php 
include "framework/database/connect.php";
echo '<pre>';

$query 	= "TRUNCATE TABLE CDW.LOG_ERROR_ORD";
mysql_query($query);

$query 	= "SELECT PLAFOND_DEF FROM NST.PARAM_LOC";
$result = mysql_query($query);
$row 	= mysql_fetch_array($result);
$plafond= $row['PLAFOND_DEF'];

#TYP 1 Item nota dihapus
$query 	= "INSERT INTO CDW.LOG_ERROR_ORD (ORD_NBR, UPD_TS, UPD_NBR, LOG_ERROR_TYP_NBR)
			SELECT 
				DET.ORD_NBR, 
				DET.UPD_TS,
				DET.UPD_NBR,
				1 AS LOG_ERROR_TYP_NBR
			FROM CMP.PRN_DIG_ORD_DET DET 
			WHERE DET.DEL_NBR!=0 
			GROUP BY DET.ORD_NBR";
mysql_query($query);

echo 'TYP 1<br/>'.$query.'<br/><br/>';

#TYP 2 Nota digital printing dengan pembeli tunai (bukan atas nama perusahaan) tanpa pembayaran/DP
$query 	= "INSERT INTO CDW.LOG_ERROR_ORD (ORD_NBR, UPD_TS, UPD_NBR, LOG_ERROR_TYP_NBR)
		SELECT ORD_NBR, UPD_TS, UPD_NBR, LOG_ERROR_TYP_NBR FROM (
			SELECT 
				HED.ORD_NBR,
				HED.UPD_TS,
				HED.UPD_NBR,
				2 AS LOG_ERROR_TYP_NBR,
				COALESCE(PYMT.TND_AMT,0) AS TND_AMT
			FROM CMP.PRN_DIG_ORD_HEAD HED 
			LEFT JOIN (
				SELECT 
					PYMT.ORD_NBR,
					SUM(PYMT.TND_AMT) AS TND_AMT
				FROM CMP.PRN_DIG_ORD_PYMT PYMT
				WHERE PYMT.DEL_NBR=0
				GROUP BY PYMT.ORD_NBR
			) PYMT ON HED.ORD_NBR = PYMT.ORD_NBR
			WHERE HED.DEL_NBR=0
				AND HED.BUY_PRSN_NBR IS NULL 
				AND HED.BUY_CO_NBR IS NULL
			HAVING TND_AMT <= 0
		) TYP2";
mysql_query($query);

echo 'TYP 2<br/>'.$query.'<br/><br/>';

#TYP 3 Nota baru belum lunas, total nota sudah melebihi plafond (company)
$query 	= "SELECT 
				HED.BUY_CO_NBR,
				HED.TOT_REM 
			FROM CMP.PRN_DIG_ORD_HEAD HED
			WHERE HED.DEL_NBR=0 
				AND HED.TOT_REM>0
				AND HED.BUY_CO_NBR IS NOT NULL
			GROUP BY HED.BUY_CO_NBR";
$result = mysql_query($query);
echo 'TYP 3<br/>'.$query.'<br/><br/>';
while ($row = mysql_fetch_array($result)) {
	$querydet 	= "INSERT INTO CDW.LOG_ERROR_ORD (ORD_NBR, UPD_TS, UPD_NBR, LOG_ERROR_TYP_NBR)
					SELECT ORD_NBR, UPD_TS, UPD_NBR, LOG_ERROR_TYP_NBR
					FROM (
						SELECT 
							HED.ORD_NBR,
							HED.UPD_TS,
							HED.UPD_NBR,
							3 AS LOG_ERROR_TYP_NBR,
							HED.TOT_REM,
							COALESCE(COM.CRDT_MAX, $plafond) AS CRDT_MAX_VAL,
							(
								SELECT 
									SUM(HEDD.TOT_REM) 
								FROM CMP.PRN_DIG_ORD_HEAD HEDD
								WHERE HEDD.DEL_NBR = 0
									AND HEDD.TOT_REM > 0
									AND HEDD.BUY_CO_NBR = HED.BUY_CO_NBR
									AND HEDD.ORD_NBR <= HED.ORD_NBR
							) AS REM_NOW
						FROM CMP.PRN_DIG_ORD_HEAD HED 
						LEFT JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR = COM.CO_NBR
						WHERE HED.DEL_NBR = 0 
							AND HED.TOT_REM > 0
							AND HED.BUY_CO_NBR IS NOT NULL
							AND HED.BUY_CO_NBR = ".$row['BUY_CO_NBR']."
						HAVING REM_NOW > CRDT_MAX_VAL
						ORDER BY HED.ORD_NBR ASC
					) TYP3";
	echo $querydet.'<br/>';
	mysql_query($querydet);
}

#TYP 4 Nota baru belum lunas, masih ada outstanding (company)
$query 	= "SELECT 
				HED.BUY_CO_NBR,
				HED.TOT_REM 
			FROM CMP.PRN_DIG_ORD_HEAD HED
			WHERE HED.DEL_NBR=0 
				AND HED.TOT_REM>0
				AND HED.BUY_CO_NBR IS NOT NULL
			GROUP BY HED.BUY_CO_NBR";
$result = mysql_query($query);
echo 'TYP 4<br/>'.$query.'<br/><br/>';
while ($row = mysql_fetch_array($result)) {
	$querydet 	= "INSERT INTO CDW.LOG_ERROR_ORD (ORD_NBR, UPD_TS, UPD_NBR, LOG_ERROR_TYP_NBR)
					SELECT ORD_NBR, UPD_TS, UPD_NBR, LOG_ERROR_TYP_NBR
					FROM (
						SELECT 
							HED.ORD_NBR,
							HED.UPD_TS,
							HED.UPD_NBR,
							4 AS LOG_ERROR_TYP_NBR,
							HED.TOT_REM,
							COALESCE(COM.CRDT_MAX, $plafond) AS CRDT_MAX_VAL,
							COALESCE(COM.PAY_TERM, 30) AS PAY_TERM,
							(
								SELECT 
									SUM(HEDD.TOT_REM) 
								FROM CMP.PRN_DIG_ORD_HEAD HEDD
								WHERE HEDD.DEL_NBR = 0
									AND HEDD.TOT_REM > 0
									AND HEDD.BUY_CO_NBR = HED.BUY_CO_NBR
									AND HEDD.ORD_NBR <= HED.ORD_NBR
							) AS REM_NOW,
							(DATEDIFF(NOW(), DATE(HED.ORD_TS))) AS PAY_TERM_NOW
						FROM CMP.PRN_DIG_ORD_HEAD HED 
						LEFT JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR = COM.CO_NBR
						WHERE HED.DEL_NBR = 0 
							AND HED.TOT_REM > 0
							AND HED.BUY_CO_NBR IS NOT NULL
							AND HED.BUY_CO_NBR = ".$row['BUY_CO_NBR']."
						HAVING REM_NOW <= CRDT_MAX_VAL
							AND PAY_TERM_NOW > PAY_TERM
						ORDER BY HED.ORD_NBR ASC
					) TYP4";
	echo $querydet.'<br/>';
	mysql_query($querydet);
}

#TYP 5 Nota baru belum lunas, total nota sudah melebihi plafond (contacts)
$query 	= "SELECT 
				HED.BUY_PRSN_NBR,
				HED.TOT_REM 
			FROM CMP.PRN_DIG_ORD_HEAD HED
			WHERE HED.DEL_NBR=0 
				AND HED.TOT_REM>0
				AND HED.BUY_CO_NBR IS NULL
				AND HED.BUY_PRSN_NBR IS NOT NULL
			GROUP BY HED.BUY_PRSN_NBR";
$result = mysql_query($query);
echo 'TYP 5<br/>'.$query.'<br/><br/>';
while ($row = mysql_fetch_array($result)) {
	$querydet 	= "INSERT INTO CDW.LOG_ERROR_ORD (ORD_NBR, UPD_TS, UPD_NBR, LOG_ERROR_TYP_NBR)
					SELECT ORD_NBR, UPD_TS, UPD_NBR, LOG_ERROR_TYP_NBR
					FROM (
						SELECT 
							HED.ORD_NBR,
							HED.UPD_TS,
							HED.UPD_NBR,
							5 AS LOG_ERROR_TYP_NBR,
							HED.TOT_REM,
							COALESCE(PPL.CRDT_MAX, $plafond) AS CRDT_MAX_VAL,
							(
								SELECT 
									SUM(HEDD.TOT_REM) 
								FROM CMP.PRN_DIG_ORD_HEAD HEDD
								WHERE HEDD.DEL_NBR = 0
									AND HEDD.TOT_REM > 0
									AND HEDD.BUY_PRSN_NBR = HED.BUY_PRSN_NBR
									AND HEDD.ORD_NBR <= HED.ORD_NBR
							) AS REM_NOW
						FROM CMP.PRN_DIG_ORD_HEAD HED 
						LEFT JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR = PPL.PRSN_NBR
						WHERE HED.DEL_NBR = 0 
							AND HED.TOT_REM > 0
							AND HED.BUY_CO_NBR IS NULL
							AND HED.BUY_PRSN_NBR IS NOT NULL
							AND HED.BUY_PRSN_NBR = ".$row['BUY_PRSN_NBR']."
						HAVING REM_NOW > CRDT_MAX_VAL
						ORDER BY HED.ORD_NBR ASC
					) TYP5";
	echo $querydet.'<br/>';
	mysql_query($querydet);
}


#TYP 6 Nota baru belum lunas, masih ada outstanding (contacts)
$query 	= "SELECT 
				HED.BUY_PRSN_NBR,
				HED.TOT_REM 
			FROM CMP.PRN_DIG_ORD_HEAD HED
			WHERE HED.DEL_NBR=0 
				AND HED.TOT_REM>0
				AND HED.BUY_CO_NBR IS NULL
				AND HED.BUY_PRSN_NBR IS NOT NULL
			GROUP BY HED.BUY_PRSN_NBR";
$result = mysql_query($query);
echo 'TYP 6<br/>'.$query.'<br/><br/>';
while ($row = mysql_fetch_array($result)) {
	$querydet 	= "INSERT INTO CDW.LOG_ERROR_ORD (ORD_NBR, UPD_TS, UPD_NBR, LOG_ERROR_TYP_NBR)
					SELECT ORD_NBR, UPD_TS, UPD_NBR, LOG_ERROR_TYP_NBR
					FROM (
						SELECT 
							HED.ORD_NBR,
							HED.UPD_TS,
							HED.UPD_NBR,
							6 AS LOG_ERROR_TYP_NBR,
							HED.TOT_REM,
							COALESCE(PPL.CRDT_MAX, (SELECT PLAFOND_DEF FROM NST.PARAM_LOC)) AS CRDT_MAX_VAL,
							COALESCE(PPL.PAY_TERM_PPL, 30) AS PAY_TERM,
							(
								SELECT 
									SUM(HEDD.TOT_REM) 
								FROM CMP.PRN_DIG_ORD_HEAD HEDD
								WHERE HEDD.DEL_NBR = 0
									AND HEDD.TOT_REM > 0
									AND HEDD.BUY_PRSN_NBR = HED.BUY_PRSN_NBR
									AND HEDD.ORD_NBR <= HED.ORD_NBR
							) AS REM_NOW,
							(DATEDIFF(NOW(), DATE(HED.ORD_TS))) AS PAY_TERM_NOW
						FROM CMP.PRN_DIG_ORD_HEAD HED 
						LEFT JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR = PPL.PRSN_NBR
						WHERE HED.DEL_NBR = 0 
							AND HED.TOT_REM > 0
							AND HED.BUY_CO_NBR IS NULL
							AND HED.BUY_PRSN_NBR IS NOT NULL
							AND HED.BUY_PRSN_NBR = ".$row['BUY_PRSN_NBR']."
						HAVING REM_NOW <= CRDT_MAX_VAL
							AND PAY_TERM_NOW > PAY_TERM
						ORDER BY HED.ORD_NBR ASC
					) TYP6";
	echo $querydet.'<br/>';
	mysql_query($querydet);
}


#TYP7 nota digital printing yang detail nota  nya (custom) tdk ada PID purchase ordernya
$querydet 	= "INSERT INTO CDW.LOG_ERROR_ORD (ORD_NBR, UPD_TS, UPD_NBR, LOG_ERROR_TYP_NBR)
			SELECT 
				DET.ORD_NBR, 
				DET.UPD_TS,
				DET.UPD_NBR,
				7 AS LOG_ERROR_TYP_NBR
			FROM CMP.PRN_DIG_ORD_DET DET 
			WHERE DET.ORD_DET_NBR_REF=0 AND PRN_DIG_TYP = 'CUSTOM' GROUP BY DET.ORD_NBR";

mysql_query($querydet);

echo 'TYP 7<br/>'.$querydet.'<br/><br/>';


?>