<?php
	require_once "framework/database/connect.php";
	require_once "framework/functions/default.php";
	require_once "framework/pagination/pagination.php";
	
	$searchQuery=trim(strtoupper(urldecode($_REQUEST[s])));
	$searchQ=explode(" ",$searchQuery);
	$CshDayDte =$_GET['CSH_DAY_DTE'];
	$typ=$_GET['TYP'];
	$month=date('m');
	$year=date('Y');

	if ($searchQuery!=''){
		foreach($searchQ as $searchQuery){
			$whereClause[]="(CSH_DAY_NBR LIKE '%".$searchQuery."%' OR CSH_DAY_DTE LIKE '%".$searchQuery."%' OR ACCT LIKE '%".$searchQuery."%' OR DEP_DTE LIKE '%".$searchQuery."%')";
		}	
	}
	
	if ($typ=='CAR'){
		$whereClause[]="(DATE(CSH_DAY_DTE) BETWEEN '".$_GET['BEG_DT']."' AND '".$_GET['END_DT']."') ";		
	}
	
	if ($_GET['ACCT']!='ALL' AND $_GET['ACCT']!=''){
		$whereClause[]="ACCT='".$_GET['ACCT']."'";
	}

	if ($typ!='CAR'){
		$order= "ORDER BY CSH_DAY_DTE DESC";
	}

	$whereClause[]="DEL_NBR =0";

	$whereClause=implode(" AND ", $whereClause);
	
		$query="SELECT CSH_DAY_NBR,DATE(CSH_DAY_DTE) AS CSH_DAY_DTE,S_NBR,SUM(CHK_AMT) AS CHK_AMT,SUM(CSH_AMT)AS CSH_AMT,SUM(CSH_IN_DRWR)AS CSH_IN_DRWR,SUM(TOT_AMT) AS TOT_AMT,SUM(CSH_REG) AS CSH_REG, SUM(CSH_REG-CSH_IN_DRWR) AS CORRTN_AMT,(CHK_AMT+CSH_AMT)AS KAS_SET,DATE(DEP_DTE)AS DEP_DTE,ACCT, (CASE WHEN VRFD_F=1 THEN 'Verify' ELSE '' END) AS VRFD_DESC, VRFD_F
				FROM RTL.CSH_DAY 
				WHERE  ".$whereClause." GROUP BY CSH_DAY_DTE, S_NBR ".$order;
	
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