<?php 
	include "framework/database/connect.php";echo "<pre>";

	//Mengambil configurasi tanggal payroll bedasarkan tanggal kemarin
	$query = "SELECT  PAY_CONFIG_NBR,
					  PAY_BEG_DTE,
					  PAY_END_DTE 
				FROM  PAY.PAY_CONFIG_DTE 
				WHERE  (CURRENT_DATE - INTERVAL 1 DAY) >= PAY_BEG_DTE 
					  AND (CURRENT_DATE - INTERVAL 1 DAY) <= PAY_END_DTE
					 -- PAY_CONFIG_NBR=1";
	$result = mysql_query($query);
	$row    = mysql_fetch_array($result);

	$PayConfigNbr = $row['PAY_CONFIG_NBR'];
	$PayBegDte    = $row['PAY_BEG_DTE'];
	$PayEndDte    = $row['PAY_END_DTE'];

	//Untuk menghapus data CDW yang memiliki PAY_CONFIG_NBR hari kemarin
	$query  = "DELETE FROM CDW.PAY_RWD WHERE PAY_CONFIG_NBR = ".$PayConfigNbr;
	$result = mysql_query($query);

	//Perhitungan rata rata harga jual dan quantity omset sesuai dengan PAY_BEG_DTE dan PAY_END_DTE
	$query  = "INSERT INTO CDW.PAY_RWD (
					PRSN_NBR,
					CO_NBR,
					PAY_CONFIG_NBR,
					M_Q,
					S_Q,
					M_REV,
					S_REV,
					M_AVG,
					S_AVG,
					M_AVG_ALL,
					S_AVG_ALL
				)
				SELECT
					PRSN_NBR,
					CO_NBR,
					$PayConfigNbr AS PAY_CONFIG_NBR, 
					SUM(CASE WHEN PRN_DIG_EQP IN ('FLJ320P','RVS640','AJ1800F','MVJ1624','HPL375','ATX67') THEN QTY_DET ELSE 0 END) AS M_Q,
					SUM(CASE WHEN PRN_DIG_EQP IN ('KMC6501','KMC8000','KMC1085') THEN QTY_DET ELSE 0 END) AS M_Q,
					SUM(CASE WHEN PRN_DIG_EQP IN ('FLJ320P','RVS640','AJ1800F','MVJ1624','HPL375','ATX67') THEN TOT_SUB ELSE 0 END) AS M_REV,
					SUM(CASE WHEN PRN_DIG_EQP IN ('KMC6501','KMC8000','KMC1085') THEN TOT_SUB ELSE 0 END) AS S_REV,
					((SUM(CASE WHEN PRN_DIG_EQP IN ('FLJ320P','RVS640','AJ1800F','MVJ1624','HPL375','ATX67') THEN TOT_SUB ELSE 0 END)) /
					(SUM(CASE WHEN PRN_DIG_EQP IN ('FLJ320P','RVS640','AJ1800F','MVJ1624','HPL375','ATX67') THEN QTY_DET ELSE 0 END))) AS M_AVG,
					((SUM(CASE WHEN PRN_DIG_EQP IN ('KMC6501','KMC8000','KMC1085') THEN TOT_SUB ELSE 0 END)) /
					(SUM(CASE WHEN PRN_DIG_EQP IN ('KMC6501','KMC8000','KMC1085') THEN QTY_DET ELSE 0 END))) AS S_AVG,
					0 AS M_AVG_ALL,
					0 AS S_AVG_ALL
			    FROM CDW.PAY_RWD_DET DET
			    WHERE PAY_CONFIG_NBR= $PayConfigNbr
			    GROUP BY PRSN_NBR"; 
	echo $query."<BR><BR><BR>";
	$result = mysql_query($query);


	//update untuk perhitungan rata-rata keseluruhan data 
	$query  = "UPDATE CDW.PAY_RWD RWD 
				LEFT JOIN (
				SELECT (
						(SUM(
							CASE 
								WHEN TYP.PRN_DIG_EQP IN ('FLJ320P','RVS640','AJ1800F','MVJ1624','HPL375','ATX67') 
								THEN DET.TOT_SUB ELSE 0 
								END
						))/
					  	(SUM(
					  		CASE WHEN TYP.PRN_DIG_EQP IN ('FLJ320P','RVS640','AJ1800F','MVJ1624','HPL375','ATX67') 
					  		THEN DET.ORD_Q*DET.PRN_LEN*DET.PRN_WID ELSE 0 END
					  	))
					  ) AS M_AVG_ALL,
					  (
					  	(SUM(
					  		CASE WHEN TYP.PRN_DIG_EQP IN ('KMC6501','KMC8000','KMC1085') 
					  		THEN DET.TOT_SUB ELSE 0 
					  		END
					  	)) /
					  	(SUM(
					  		CASE WHEN TYP.PRN_DIG_EQP IN ('KMC6501','KMC8000','KMC1085') 
					  		THEN DET.ORD_Q ELSE 0 END
					  	))
					  ) AS S_AVG_ALL,
					  $PayConfigNbr AS PAY_CONFIG_NBR
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
			    	GROUP BY PYMT.ORD_NBR
			    ) PAY ON PAY.ORD_NBR = HED.ORD_NBR
			    LEFT JOIN CMP.PRN_DIG_ORD_DET DET ON HED.ORD_NBR = DET.ORD_NBR
			    LEFT JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP = TYP.PRN_DIG_TYP
			    WHERE PAY.TND_AMT >= HED.TOT_AMT
			    	  AND DATE(PAY.MAX_CRT_TS) >= '$PayBegDte' 
			    	  AND DATE(PAY.MAX_CRT_TS) <= '$PayEndDte'
			    	  AND DATE(HED.ORD_TS) >= '2019-01-01' 
			    	  AND HED.DEL_NBR   = 0 
			    	  AND DET.DEL_NBR   = 0 
			    	  AND (HED.BUY_CO_NBR IS NOT NULL OR HED.BUY_CO_NBR NOT IN (SELECT CO_NBR FROM NST.PARAM_COMPANY))
			    	  AND TYP.PRN_DIG_EQP IN ('FLJ320P','RVS640','AJ1800F','MVJ1624','HPL375','ATX67','KMC6501','KMC8000','KMC1085')
			    ) DET ON DET.PAY_CONFIG_NBR = RWD.PAY_CONFIG_NBR
			    SET RWD.M_AVG_ALL = DET.M_AVG_ALL,
			    	RWD.S_AVG_ALL = DET.S_AVG_ALL
			    WHERE RWD.PAY_CONFIG_NBR =".$PayConfigNbr; 
	$result = mysql_query($query);echo $query;
?>