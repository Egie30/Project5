<?php 
	include "framework/database/connect.php";echo "<pre>";

	//Mengambil configurasi tanggal payroll bedasarkan tanggal kemarin
	$query = "SELECT  PAY_CONFIG_NBR,
					  PAY_BEG_DTE,
					  PAY_END_DTE 
				FROM  PAY.PAY_CONFIG_DTE 
				WHERE (CURRENT_DATE - INTERVAL 1 DAY) >= PAY_BEG_DTE 
					AND (CURRENT_DATE - INTERVAL 1 DAY) <= PAY_END_DTE
					  -- PAY_CONFIG_NBR=1";
	$result = mysql_query($query);
	$row    = mysql_fetch_array($result);

	$PayConfigNbr = $row['PAY_CONFIG_NBR'];
	$PayBegDte    = $row['PAY_BEG_DTE'];
	$PayEndDte    = $row['PAY_END_DTE'];

	//Untuk menghapus data CDW yang memiliki PAY_CONFIG_NBR hari kemarin
	$query  = "DELETE FROM CDW.PAY_RWD_DET WHERE PAY_CONFIG_NBR=".$PayConfigNbr;
	$result = mysql_query($query);

	//Perhitungan rata rata harga jual dan quantity omset sesuai dengan PAY_BEG_DTE dan PAY_END_DTE Marketing
	$query  = "INSERT IGNORE INTO CDW.PAY_RWD_DET (
					ORD_DET_NBR,
					ORD_NBR,
					PRSN_NBR,
					CO_NBR,
					ORD_DTE,
					MAX_PYMT_TS,
					CRT_TS,
					PRN_DIG_EQP,
					ORD_Q,
					PRN_LEN,
					PRN_WID,
					QTY_DET,
					TOT_SUB,
					PAY_CONFIG_NBR,
					BUY_CO_NBR,
					PRN_DIG_TYP
				)
				SELECT
					DET.ORD_DET_NBR,
					HED.ORD_NBR,
					PPL.PRSN_NBR,
					$CoNbrDef AS CO_NBR,
					DATE(HED.ORD_TS) AS ORD_DTE,
					PAY.MAX_CRT_TS AS MAX_PYMT_TS,
					HED.CRT_TS,
					PRN_DIG_EQP,
					ORD_Q,
					PRN_LEN,
					PRN_WID,
					(CASE
						WHEN
							DATE(PAY.MAX_CRT_TS) >= DATE(HED.ORD_TS)
							AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL COALESCE(PAY_TERM, 32) DAY))
							THEN (ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1))* (100 / 100)
						WHEN
                            DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL COALESCE(PAY_TERM, 32) DAY))
                            AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 2 MONTH))
                            THEN (ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1)) * (80 / 100)
                        WHEN
                        	DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 2 MONTH))
                        	AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 3 MONTH))
                        	THEN (ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1))* (60 / 100)
                       	WHEN
                       		DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 3 MONTH))
                       		AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 4 MONTH))
                       		THEN (ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1)) * (40 / 100)
                       	WHEN
                       		DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 4 MONTH))
                       		AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 5 MONTH))
                       		THEN (ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1)) * (20 / 100)
                       	WHEN
                       		DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 5 MONTH))
                       		THEN (ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1)) * (0 / 100)
                    END) AS QTY_DET,
					TOT_SUB,
					$PayConfigNbr  AS PAY_CONFIG_NBR,
					BUY_CO_NBR,
					DET.PRN_DIG_TYP
			    FROM CMP.PRN_DIG_ORD_HEAD HED
			    LEFT JOIN(
			    	SELECT
			    			PYMT.PYMT_NBR,
			    			PYMT.ORD_NBR,
			    			SUM(PYMT.TND_AMT) AS TND_AMT,
			    			PYMT.CRT_TS,
			    			MAX(PYMT.CRT_TS)  AS MAX_CRT_TS
			    	FROM CMP.PRN_DIG_ORD_PYMT PYMT
			    	WHERE PYMT.DEL_NBR = 0
			    		  AND VAL_NBR IS NOT NULL
			    	GROUP BY PYMT.ORD_NBR
			    ) PAY ON PAY.ORD_NBR = HED.ORD_NBR
			    LEFT JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR = COM.CO_NBR
			    LEFT JOIN CMP.PRN_DIG_ORD_DET DET ON HED.ORD_NBR = DET.ORD_NBR
			    LEFT JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP = TYP.PRN_DIG_TYP
			    LEFT JOIN CMP.PEOPLE PPL  ON COM.ACCT_EXEC_NBR  = PPL.PRSN_NBR
			    WHERE PAY.TND_AMT >= HED.TOT_AMT
			    	  AND DATE(PAY.MAX_CRT_TS) >= '$PayBegDte' 
			    	  AND DATE(PAY.MAX_CRT_TS) <= '$PayEndDte'
			    	  AND DATE(ORD_TS) >= '2019-01-01' 
			    	  AND HED.DEL_NBR   = 0 
			    	  AND DET.DEL_NBR   = 0 
			    	  AND PPL.POS_TYP IN ('SNM','RAM','CMA','NAM')
			    	  AND (BUY_CO_NBR IS NOT NULL OR BUY_CO_NBR NOT IN (SELECT CO_NBR FROM NST.PARAM_COMPANY))
			    	  AND PRN_DIG_EQP IN ('FLJ320P','RVS640','AJ1800F','MVJ1624','HPL375','ATX67','KMC6501','KMC8000','KMC1085')
				  AND DET.ORD_DET_NBR NOT IN (
			    	  	SELECT ORD_DET_NBR FROM CDW.PAY_RWD_DET WHERE PAY_CONFIG_NBR = $PayConfigNbr
			    	  )
			    GROUP BY DET.ORD_DET_NBR"; 
	$result = mysql_query($query);

	if ($result){echo "<br/>sukses";}else {echo "gagal";}
	
	//Perhitungan rata rata harga jual dan quantity omset sesuai dengan PAY_BEG_DTE dan PAY_END_DTE Company
	$query  = "INSERT IGNORE INTO CDW.PAY_RWD_DET (
					ORD_DET_NBR,
					ORD_NBR,
					PRSN_NBR,
					CO_NBR,
					ORD_DTE,
					MAX_PYMT_TS,
					CRT_TS,
					PRN_DIG_EQP,
					ORD_Q,
					PRN_LEN,
					PRN_WID,
					QTY_DET,
					TOT_SUB,
					PAY_CONFIG_NBR,
					BUY_CO_NBR,
					PRN_DIG_TYP
				)
				SELECT
					DET.ORD_DET_NBR,
					HED.ORD_NBR,
					PPL.PRSN_NBR,
					$CoNbrDef AS CO_NBR,
					DATE(HED.ORD_TS) AS ORD_DTE,
					PAY.MAX_CRT_TS AS MAX_PYMT_TS,
					HED.CRT_TS,
					PRN_DIG_EQP,
					ORD_Q,
					PRN_LEN,
					PRN_WID,
					(CASE
						WHEN
							DATE(PAY.MAX_CRT_TS) >= DATE(HED.ORD_TS)
							AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL COALESCE(PAY_TERM, 32) DAY))
							THEN (ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1))* (100 / 100)
						WHEN
                            DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL COALESCE(PAY_TERM, 32) DAY))
                            AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 2 MONTH))
                            THEN (ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1)) * (80 / 100)
                        WHEN
                        	DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 2 MONTH))
                        	AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 3 MONTH))
                        	THEN (ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1))* (60 / 100)
                       	WHEN
                       		DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 3 MONTH))
                       		AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 4 MONTH))
                       		THEN (ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1)) * (40 / 100)
                       	WHEN
                       		DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 4 MONTH))
                       		AND DATE(PAY.MAX_CRT_TS) <= LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 5 MONTH))
                       		THEN (ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1)) * (20 / 100)
                       	WHEN
                       		DATE(PAY.MAX_CRT_TS) > LAST_DAY(DATE_ADD(DATE(HED.ORD_TS), INTERVAL 5 MONTH))
                       		THEN (ORD_Q*COALESCE(PRN_LEN,1)*COALESCE(PRN_WID,1)) * (0 / 100)
                    END) AS QTY_DET,
					TOT_SUB,
					$PayConfigNbr  AS PAY_CONFIG_NBR,
					BUY_CO_NBR,
					DET.PRN_DIG_TYP
			    FROM CMP.PRN_DIG_ORD_HEAD HED
			    LEFT JOIN(
			    	SELECT
			    			PYMT.PYMT_NBR,
			    			PYMT.ORD_NBR,
			    			SUM(PYMT.TND_AMT) AS TND_AMT,
			    			PYMT.CRT_TS,
			    			MAX(PYMT.CRT_TS)  AS MAX_CRT_TS
			    	FROM CMP.PRN_DIG_ORD_PYMT PYMT
			    	WHERE PYMT.DEL_NBR = 0
			    		  AND VAL_NBR IS NOT NULL
			    	GROUP BY PYMT.ORD_NBR
			    ) PAY ON PAY.ORD_NBR = HED.ORD_NBR
			    LEFT JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR = COM.CO_NBR
			    LEFT JOIN CMP.PRN_DIG_ORD_DET DET ON HED.ORD_NBR = DET.ORD_NBR
			    LEFT JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP = TYP.PRN_DIG_TYP
			    LEFT JOIN CMP.PEOPLE PPL  ON COM.ACCT_EXEC_NBR  = PPL.PRSN_NBR
			    WHERE PAY.TND_AMT >= HED.TOT_AMT
			    	  AND DATE(PAY.MAX_CRT_TS) >= '$PayBegDte' 
			    	  AND DATE(PAY.MAX_CRT_TS) <= '$PayEndDte'
			    	  AND DATE(ORD_TS) >= '2019-01-01' 
			    	  AND HED.DEL_NBR   = 0 
			    	  AND DET.DEL_NBR   = 0 
			    	   AND (COM.ACCT_EXEC_NBR = 0 OR COM.ACCT_EXEC_NBR IS NULL)
			    	  AND (BUY_CO_NBR IS NOT NULL OR BUY_CO_NBR NOT IN (SELECT CO_NBR FROM NST.PARAM_COMPANY))
			    	  AND PRN_DIG_EQP IN ('FLJ320P','RVS640','AJ1800F','MVJ1624','HPL375','ATX67','KMC6501','KMC8000','KMC1085')
				  AND DET.ORD_DET_NBR NOT IN (
			    	  	SELECT ORD_DET_NBR FROM CDW.PAY_RWD_DET WHERE PAY_CONFIG_NBR = $PayConfigNbr
			    	  )
			    GROUP BY DET.ORD_DET_NBR"; 
	$result = mysql_query($query);
?>