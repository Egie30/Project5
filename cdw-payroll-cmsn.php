<?php 
	include 'framework/database/connect.php';

	$query = "SELECT PAY_CONFIG_NBR,
					 PAY_BEG_DTE,
					 PAY_END_DTE
				FROM PAY.PAY_CONFIG_DTE 
				WHERE (CURRENT_DATE - INTERVAL 1 DAY) >= PAY_BEG_DTE
					  AND (CURRENT_DATE - INTERVAL 1 DAY) <= PAY_END_DTE
				#WHERE PAY_CONFIG_NBR = 9";
	$result= mysql_query($query);
	$row   = mysql_fetch_array($result);

	$PayConfigNbr = $row['PAY_CONFIG_NBR'];
	$PayBegDte    = $row['PAY_END_DTE'];
	$PayEndDte    = $row['PAY_END_DTE'];

	$query = "DELETE CDW.PAY_CMSN WHERE PAY_CONFIG_NBR = ".$PayConfigNbr;
	$result= mysql_query($query);

	$query  = "INSERT INTO CDW.PAY_CMSN(
					PAY_CONFIG_NBR,
					PRSN_NBR,
					TOT_CMSN
				)
				SELECT 
					PAY_CONFIG_NBR,
					PRSN_NBR,
					SUM(DET.CMSN) AS TOT_CMSN
				FROM CDW.PAY_CMSN_DET DET 
				WHERE DET.PAY_CONFIG_NBR = $PayConfigNbr
				GROUP BY PRSN_NBR";
	$result= mysql_query($query);echo "<pre>$query</pre>";