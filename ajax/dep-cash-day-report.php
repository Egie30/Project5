<?php

	require_once __DIR__ . "/../framework/database/connect.php";
	require_once __DIR__ . "/../framework/functions/default.php";
	require_once __DIR__ . "/../framework/pagination/pagination.php";
	
	$searchQuery=trim(strtoupper(urldecode($_REQUEST[s])));
	$searchQ=explode(" ",$searchQuery);

	if ($searchQuery!=''){
		foreach($searchQ as $searchQuery){
			$whereClause[]="(DEP.CSH_DAY_NBR LIKE '%".$searchQuery."%'  OR CHD.ACCT LIKE '%".$searchQuery."%' OR DEP.DEP_DTE LIKE '%".$searchQuery."%')";
		}	
	}
	
	$whereClause[]="(DEP.DEP_DTE BETWEEN '".$_GET['BEG_DT']."' AND '".$_GET['END_DT']."') ";		
	
	if ($_GET['ACCT']!='ALL' AND $_GET['ACCT']!=''){
		$whereClause[]="CHD.ACCT='".$_GET['ACCT']."'";
	}

	$whereClause=implode(" AND ", $whereClause);
	
	if ($_GET['ACCT']=='ALL'){
		$query="SELECT 	 DEP.CSH_DAY_NBR 
						,DEP.DEP_DTE
						,SUM(DEP.AMT) AS AMT
						,DEP.KET
						,DEP.TYP
						,DEP.S_NBR
						,CHD.ACCT
						,CHD.VRFD_F
				FROM
					(
						SELECT   CSH_DAY_NBR
								,DATE(DEP_DTE) AS DEP_DTE
								,CHK_AMT AS AMT
								,'Cek/Giro' AS KET
								,'CHK' AS TYP
								,S_NBR
						FROM RTL.CSH_DAY
						WHERE DEL_NBR=0 AND CHK_AMT IS NOT NULL

						UNION ALL

						SELECT   CSH_DAY_NBR
								,DATE(DEP_DTE) AS DEP_DTE
								,CSH_AMT AS AMT
								,'Uang Kontan' AS KET
								,'CSH' AS TYP
								,S_NBR
						FROM RTL.CSH_DAY
						WHERE DEL_NBR=0 AND CSH_AMT IS NOT NULL
					)DEP
				LEFT OUTER JOIN RTL.CSH_DAY CHD ON CHD.CSH_DAY_NBR = DEP.CSH_DAY_NBR
				WHERE $whereClause GROUP BY DEP.DEP_DTE, KET
				ORDER BY DEP.DEP_DTE DESC";
	} else {
		$query="SELECT 	 DEP.CSH_DAY_NBR 
						,DEP.DEP_DTE
						,SUM(DEP.AMT) AS AMT
						,DEP.KET
						,DEP.TYP
						,DEP.S_NBR
						,CHD.ACCT
						,CHD.VRFD_F
				FROM
					(
						SELECT   CSH_DAY_NBR
								,DATE(DEP_DTE) AS DEP_DTE
								,CHK_AMT AS AMT
								,'Cek/Giro' AS KET
								,'CHK' AS TYP
								,S_NBR
						FROM RTL.CSH_DAY
						WHERE DEL_NBR=0 AND CHK_AMT IS NOT NULL

						UNION ALL

						SELECT   CSH_DAY_NBR
								,DATE(DEP_DTE) AS DEP_DTE
								,CSH_AMT AS AMT
								,'Uang Kontan' AS KET
								,'CSH' AS TYP
								,S_NBR
						FROM RTL.CSH_DAY
						WHERE DEL_NBR=0 AND CSH_AMT IS NOT NULL
					)DEP
				LEFT OUTER JOIN RTL.CSH_DAY CHD ON CHD.CSH_DAY_NBR = DEP.CSH_DAY_NBR
				WHERE $whereClause GROUP BY DEP.DEP_DTE, KET
				ORDER BY DEP.DEP_DTE DESC";
	}
	
	$pagination = pagination($query, 100);

	$results = array(
		'parameter' => $_GET,
		'data' => array(),
		'pagination' => $pagination
	);
	$result = mysql_query($pagination['query']);

	while($row = mysql_fetch_array($result)) {
		$results['data'][] = $row;
	}

	echo json_encode($results);
?>