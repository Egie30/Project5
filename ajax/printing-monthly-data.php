<?php
include "../framework/functions/default.php";
include "../framework/database/connect.php";
include "../framework/functions/crypt.php";

$accountExec	= mysql_escape_string($_GET['ACCT']);
$month			= mysql_escape_string($_GET['BULAN']);
$year			= mysql_escape_string($_GET['TAHUN']);
$searchQuery	= mysql_escape_string($_GET['FILTER']);

$additional_data = array();

if ($_GET['DETIL']) {
	$companyNumber = mysql_escape_string($_GET['NBR']);
	$sql = "SELECT 
		PRN.ORD_NBR AS NOMORNOTA,
		STT.ORD_STT_DESC AS ORD_STATUS,
		DATE_FORMAT(PRN.ORD_TS, '%Y-%m-%d') AS TANGGAL,
		DATE_FORMAT(PRN.ORD_TS, '%H:%I:%S') AS WAKTU,
		PRN.ORD_TTL AS JUDUL_PESANAN,
		PRN.TOT_AMT AS TOTAL_NOTA,
		PRN.TOT_REM AS SISA,
		COUNT(DET.ORD_DET_NBR) AS ITEM,
		SUM(DET.ORD_Q) AS TOTAL_ITEM,
		COMP.NAME AS COMPANY_NAME,
		PEOPLE.NAME AS PEOPLE_NAME
	FROM PRN_DIG_ORD_HEAD AS PRN
	LEFT JOIN COMPANY COMP ON COMP.CO_NBR = PRN.BUY_CO_NBR
	LEFT JOIN PRN_DIG_ORD_DET DET ON PRN.ORD_NBR = DET.ORD_NBR
	LEFT JOIN PRN_DIG_STT STT ON PRN.ORD_STT_ID = STT.ORD_STT_ID
	LEFT JOIN PEOPLE ON PEOPLE.PRSN_NBR = COMP.ACCT_EXEC_NBR
	WHERE COMP.CO_NBR = '".$companyNumber."'
		AND COMP.ACCT_EXEC_NBR = '".$accountExec."'
		AND YEAR(PRN.ORD_TS) = '".$year."'
		AND MONTH(PRN.ORD_TS) = '".$month."'
		AND PRN.DEL_NBR = '0'
	GROUP BY PRN.ORD_NBR";
} else if ($_GET['GRAFIK']) {
    $GRAFIK_TYPE = $_GET['GRAFIK'];
    switch ($GRAFIK_TYPE) {
        case '1':
            $sql = "SELECT PEOPLE.NAME AS NAMA,
					YEAR(PRN.ORD_TS) AS TAHUN,
					MONTH(PRN.ORD_TS) AS BULAN,
					DATE_FORMAT(PRN.ORD_TS,'%b') AS ORD_MONTH_NM,
					SUM(PRN.TOT_AMT) AS SUM_AMT,
					SUM(PRN.TOT_REM) AS SUM_SISA,
					COUNT(COMP.CO_NBR) AS COMPANY
				FROM 
					PRN_DIG_ORD_HEAD AS PRN
					LEFT JOIN COMPANY COMP ON COMP.CO_NBR = PRN.BUY_CO_NBR
					LEFT JOIN PEOPLE ON PEOPLE.PRSN_NBR = COMP.ACCT_EXEC_NBR
				WHERE 
					COMP.ACCT_EXEC_NBR = '".$accountExec."'
					AND PRN.DEL_NBR = '0'
					AND PRN.ORD_TS BETWEEN STR_TO_DATE('".$year."-".$month."-1', '%Y-%m-%d') - INTERVAL 4 MONTH
					AND STR_TO_DATE('".$year."-".$month."-31', '%Y-%m-%d')
				GROUP BY 3
				ORDER BY 2 ASC, 3 ASC";
            break;
        case '2':
            $sql = "SELECT DATE_FORMAT(ORD_TS,'%Y') AS ORD_YEAR,
					CAST(DATE_FORMAT(ORD_TS,'%c') AS DECIMAL(2,0)) AS ORD_MONTH,
					DATE_FORMAT(ORD_TS,'%b') AS ORD_MONTH_NM,
					SUM(CASE WHEN PRN_DIG_TYP='PROD' THEN ORD_Q ELSE 0 END) AS PROD,
					SUM(CASE WHEN PRN_DIG_TYP='CUSTOM'  THEN ORD_Q ELSE 0 END) AS CUSTOM,
					SUM(CASE WHEN PRN_DIG_TYP='FL3FL240'  THEN ORD_Q ELSE 0 END) AS FL3FL240,
					SUM(CASE WHEN PRN_DIG_TYP='RVS6IND' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS RVS6IND,
					SUM(CASE WHEN PRN_DIG_TYP='FL3FL280' THEN ORD_Q*PRN_LEN*PRN_WID ELSE 0 END) AS FL3FL280
				FROM 
					PRN_DIG_ORD_HEAD HED 
					INNER JOIN PRN_DIG_ORD_DET DET ON HED.ORD_NBR=DET.ORD_NBR 
					LEFT OUTER JOIN COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR
				WHERE 
					HED.DEL_NBR=0 
					AND DET.DEL_NBR=0
					AND ORD_TS BETWEEN STR_TO_DATE('".$year."-".$month."-1', '%Y-%m-%d') - INTERVAL 4 MONTH
					AND STR_TO_DATE('".$year."-".$month."-31', '%Y-%m-%d')
					AND ACCT_EXEC_NBR='".$accountExec."'
				GROUP BY 
					DATE_FORMAT(ORD_TS,'%Y'),
					DATE_FORMAT(ORD_TS,'%c'),
					DATE_FORMAT(ORD_TS,'%b')
				ORDER BY 1,2";
				break; 
    }

}
else {
    $whereClauses = "AND COMP.ACCT_EXEC_NBR = '".$accountExec."'";
    if ($ACCT == 'ALL') { $whereClauses = ""; };
    if (!$searchQuery=='') {
        if ($ACCT == 'ALL') {
            $whereClauses .= " AND (COMP.NAME LIKE '%".$searchQuery."%' OR PEOPLE.NAME LIKE '%".$searchQuery."%') ";
        } else {
            $whereClauses .= " AND COMP.NAME LIKE '%".$searchQuery."%' ";
        }
    };
	
    $sql = "SELECT COUNT(PRN.ORD_NBR) AS IVC_CNT, 
		COMP.NAME AS CO_NAME, 
		COMP.CO_NBR AS CO_NBR,
		SUM(PRN.TOT_AMT) AS TOT_AMT,
		SUM(PRN.TOT_REM) AS TOT_REM,
		PEOPLE.NAME AS ACCT_EXEC_NAME,
		PEOPLE.PRSN_NBR AS PRSN_NBR
	FROM CMP.PRN_DIG_ORD_HEAD PRN
		LEFT JOIN CMP.COMPANY COMP ON COMP.CO_NBR = PRN.BUY_CO_NBR
		LEFT JOIN CMP.PEOPLE ON COMP.ACCT_EXEC_NBR = PEOPLE.PRSN_NBR
	WHERE 
		YEAR(PRN.ORD_TS) = '".$year."'
		AND MONTH(PRN.ORD_TS) = '".$month."'
		AND PRN.DEL_NBR = '0'
		AND COMP.NAME IS NOT NULL
		AND PEOPLE.NAME IS NOT NULL
		AND PEOPLE.POS_TYP IN ('SNM','RAM','CMA','NAM','COM','DPG','SCM')
		".$whereClauses."
	GROUP BY 
		COMP.NAME
	ORDER BY 
		PEOPLE.NAME, 
		COMP.CO_NBR";
}
//echo $sql;
function prepareResponse($conection, $query, $data=[]) {
    $res = mysqli_query($conection, $query);
    $results = array(
        'parameter' => $_GET,
        'pagination' => $data->pagination,
        'data' => array(),
        'columns' => array(),
        'query' => preg_replace('/[^A-Za-z0-9()=.,_\' \-]/', '', $query),
        'additional' => $data->additional
    );

    while ($row = mysqli_fetch_assoc($res)) {
        $results['data'][] = (array) $row;
        $results['columns'] = array_keys((array)$row);
    }
    
    if ($_GET['debug'] == 'debugplease') {
        header("Content-Type: application/json");
        echo json_encode($results);
    }
    else { 
        header("Content-Type: text/plain");
        echo simple_crypt(json_encode($results));
    }
}

prepareResponse($sql, $additional_data);
?>