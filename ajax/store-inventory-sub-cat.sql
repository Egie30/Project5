SELECT 
	STK.ORD_DTE,
	STK.ORD_YEAR,
	STK.ORD_MONTH,
	STK.ORD_DAY,
	STK.ORD_MONTHNAME,
	STK.INV_NBR,
	STK.INV_NAME,
	STK.CAT_NBR,
	STK.CAT_DESC,
	STK.CAT_SUB_NBR,
	STK.CAT_SUB_DESC,
	SUM(COALESCE(STK.RCV_Q,0)) AS RCV_Q,
	SUM(COALESCE(STK.XF_IN_Q,0)) AS XF_IN_Q,
	SUM(COALESCE(STK.RTR_Q,0)) AS RTR_Q,
	SUM(COALESCE(STK.XF_OUT_Q,0)) AS XF_OUT_Q,
	SUM(COALESCE(STK.COR_Q,0)) AS COR_Q,
	SUM(COALESCE(STK.SLS_Q,0)) AS SLS_Q,
	SUM(COALESCE(CSH.RTL_Q,0)) AS RTL_Q,
	SUM(COALESCE(STK.RCV_TOT_SUB,0)) AS RCV_TOT_SUB,
	SUM(COALESCE(STK.RTR_TOT_SUB,0)) AS RTR_TOT_SUB,
	SUM(COALESCE(STK.COR_TOT_SUB,0)) AS COR_TOT_SUB,
	SUM(COALESCE(STK.XF_OUT_TOT_SUB,0)) AS XF_OUT_TOT_SUB,
	SUM(COALESCE(STK.SLS_TOT_SUB,0)) AS SLS_TOT_SUB,
	SUM(COALESCE(CSH.RTL_TOT_SUB,0)) AS RTL_TOT_SUB,
	SUM(COALESCE(STK.RCV_Q,0) + COALESCE(STK.XF_IN_Q,0) - COALESCE(STK.RTR_Q,0) + COALESCE(STK.COR_Q,0) - COALESCE(STK.XF_OUT_Q,0) - COALESCE(STK.SLS_Q,0) - COALESCE(CSH.RTL_Q,0)) AS BALANCE_Q,
	SUM(COALESCE(STK.RCV_TOT_SUB,0) + COALESCE(STK.XF_IN_TOT_SUB,0) - COALESCE(STK.RTR_TOT_SUB,0) + COALESCE(STK.COR_TOT_SUB,0) - COALESCE(STK.XF_OUT_TOT_SUB,0) - COALESCE(STK.SLS_TOT_SUB,0) - COALESCE(CSH.RTL_TOT_SUB,0)) AS BALANCE_INV_PRC,
	SUM((COALESCE(STK.RCV_Q,0) + COALESCE(STK.XF_IN_Q,0) - COALESCE(STK.RTR_Q,0) + COALESCE(STK.COR_Q,0) - COALESCE(STK.XF_OUT_Q,0) - COALESCE(STK.SLS_Q,0) - COALESCE(CSH.RTL_Q,0)) * STK.INV_PRC) AS BALANCE_PRC
FROM (SELECT
			DATE(HED.DL_TS) AS ORD_DTE,
			YEAR(HED.DL_TS) AS ORD_YEAR,
			MONTH(HED.DL_TS) AS ORD_MONTH,
			DAY(HED.DL_TS) AS ORD_DAY,
			MONTHNAME(HED.DL_TS) AS ORD_MONTHNAME,
			DET.INV_NBR,
			INV.NAME AS INV_NAME,
			INV.INV_PRC,
			INV.PRC,
			HED.ORD_NBR, 
			CAT.CAT_NBR,
			CAT.CAT_DESC,
			SUB.CAT_SUB_NBR,
			SUB.CAT_SUB_DESC,
			SUM(CASE WHEN HED.IVC_TYP = 'RC' AND HED.RCV_CO_NBR = 271 THEN DET.ORD_Q ELSE 0 END) AS RCV_Q,
			SUM(CASE WHEN HED.IVC_TYP = 'XF' AND HED.RCV_CO_NBR = 271 THEN DET.ORD_Q ELSE 0 END) AS XF_IN_Q,
			SUM(CASE WHEN HED.IVC_TYP = 'RT' AND HED.SHP_CO_NBR = 271 THEN DET.ORD_Q ELSE 0 END) AS RTR_Q,
			SUM(CASE WHEN HED.IVC_TYP = 'CR' THEN DET.ORD_Q ELSE 0 END) AS COR_Q,
			SUM(CASE WHEN HED.IVC_TYP = 'XF' AND HED.SHP_CO_NBR = 271 THEN DET.ORD_Q ELSE 0 END) AS XF_OUT_Q,
			SUM(CASE WHEN HED.IVC_TYP = 'SL' AND HED.SHP_CO_NBR = 271 THEN DET.ORD_Q ELSE 0 END) AS SLS_Q,
			SUM(CASE WHEN HED.IVC_TYP = 'RC' AND HED.RCV_CO_NBR = 271 THEN DET.TOT_SUB ELSE 0 END) AS RCV_TOT_SUB,
			SUM(CASE WHEN HED.IVC_TYP = 'XF' AND HED.RCV_CO_NBR = 271 THEN DET.TOT_SUB ELSE 0 END) AS XF_IN_TOT_SUB,
			SUM(CASE WHEN HED.IVC_TYP = 'RT' AND HED.SHP_CO_NBR = 271 THEN DET.TOT_SUB ELSE 0 END) AS RTR_TOT_SUB,
			SUM(CASE WHEN HED.IVC_TYP = 'CR' THEN DET.TOT_SUB ELSE 0 END) AS COR_TOT_SUB,
			SUM(CASE WHEN HED.IVC_TYP = 'XF' AND HED.SHP_CO_NBR = 271 THEN DET.TOT_SUB ELSE 0 END) AS XF_OUT_TOT_SUB,
			SUM(CASE WHEN HED.IVC_TYP = 'SL' AND HED.SHP_CO_NBR = 271 THEN DET.TOT_SUB ELSE 0 END) AS SLS_TOT_SUB
		FROM RTL.RTL_STK_DET DET
		INNER JOIN RTL.RTL_STK_HEAD HED
			ON DET.ORD_NBR = HED.ORD_NBR
		INNER JOIN RTL.INVENTORY INV
			ON DET.INV_NBR = INV.INV_NBR
		INNER JOIN RTL.CAT_SUB SUB
			ON INV.CAT_SUB_NBR = SUB.CAT_SUB_NBR
		INNER JOIN RTL.CAT CAT
			ON CAT.CAT_NBR = SUB.CAT_NBR
		INNER JOIN CMP.COMPANY SPL
			ON SPL.CO_NBR = HED.SHP_CO_NBR
		INNER JOIN CMP.COMPANY RCV
			ON RCV.CO_NBR = HED.RCV_CO_NBR
		LEFT JOIN RTL.CAT_TYP TYP
			ON TYP.CAT_TYP_NBR = SUB.CAT_TYP_NBR
		WHERE HED.DEL_F=0 AND INV.CAT_SUB_NBR NOT IN (183,217,223) AND TYP.CAT_TYP_NBR NOT IN (4) AND DATE(HED.DL_TS) <= '2017-05-26'  AND (
				(HED.RCV_CO_NBR=271 AND IVC_TYP IN ('RC', 'XF'))
				OR (HED.SHP_CO_NBR=271 AND IVC_TYP IN ('XF'))
				OR (HED.SHP_CO_NBR=271 AND IVC_TYP IN ('RT'))
				OR (HED.SHP_CO_NBR=271 AND IVC_TYP IN ('CR'))
				OR (HED.SHP_CO_NBR=271 AND IVC_TYP IN ('SL')))
		GROUP BY DET.INV_NBR
) STK
LEFT JOIN
(SELECT 	
		CSH.INV_NBR,
		SUM(CSH.RTL_Q) AS RTL_Q,
		SUM(CSH.TND_AMT) AS RTL_TOT_SUB,
		SUB.CAT_SUB_NBR,
		CAT.CAT_NBR
	FROM RTL.CSH_REG CSH
	INNER JOIN RTL.INVENTORY INV 
		ON CSH.INV_NBR = INV.INV_NBR
	INNER JOIN RTL.CAT_SUB SUB 
		ON SUB.CAT_SUB_NBR = INV.CAT_SUB_NBR
	INNER JOIN RTL.CAT CAT
		ON CAT.CAT_NBR = SUB.CAT_NBR
	WHERE INV.DEL_NBR = 0 AND CSH.ACT_F = 0 AND CSH.CSH_FLO_TYP = 'RT' AND DATE(CSH.CRT_TS) <= '2017-05-26'  AND CSH.CO_NBR =271 
	GROUP BY INV.INV_NBR
) CSH ON CSH.INV_NBR = STK.INV_NBR
GROUP BY STK.CAT_SUB_NBR
ORDER BY STK.CAT_SUB_NBR