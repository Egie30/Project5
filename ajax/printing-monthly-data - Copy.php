<?php
include "../framework/functions/default.php";
include "../framework/database/connect.php";
include "../framework/functions/crypt.php";

$con    = mysqli_connect("localhost", "root", "", "cmp_campus");

$ACCT   = mysqli_escape_string($con, $_GET['ACCT']);
$BULAN  = mysqli_escape_string($con, $_GET['BULAN']);
$TAHUN  = mysqli_escape_string($con, $_GET['TAHUN']);
$S      = mysqli_escape_string($con, $_GET['FILTER']);

$additional_data = array();

if ($_GET['DETIL']) {
    $NBR = mysqli_escape_string($con, $_GET['NBR']);
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
            WHERE COMP.CO_NBR = '{$NBR}'
                AND COMP.ACCT_EXEC_NBR = '{$ACCT}'
                AND YEAR(PRN.ORD_TS) = '{$TAHUN}'
                AND MONTH(PRN.ORD_TS) = '{$BULAN}'
                AND PRN.DEL_NBR = '0'
            GROUP BY PRN.ORD_NBR";
}
else if ($_GET['GRAFIK']) {
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
                        COMP.ACCT_EXEC_NBR = '{$ACCT}'
                        AND PRN.DEL_NBR = '0'
                        AND PRN.ORD_TS BETWEEN STR_TO_DATE('{$TAHUN}-{$BULAN}-1', '%Y-%m-%d') - INTERVAL 4 MONTH
                        AND STR_TO_DATE('{$TAHUN}-{$BULAN}-31', '%Y-%m-%d')
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
                        AND ORD_TS BETWEEN STR_TO_DATE('{$TAHUN}-{$BULAN}-1', '%Y-%m-%d') - INTERVAL 4 MONTH
                        AND STR_TO_DATE('{$TAHUN}-{$BULAN}-31', '%Y-%m-%d')
                        AND ACCT_EXEC_NBR='{$ACCT}'
                    GROUP BY 
                        DATE_FORMAT(ORD_TS,'%Y'),
                        DATE_FORMAT(ORD_TS,'%c'),
                        DATE_FORMAT(ORD_TS,'%b')
                    ORDER BY 1,2";
                    break; 
    }

}
else {
    $CLAUSE = "AND COMP.ACCT_EXEC_NBR = '{$ACCT}'";
    if ($ACCT == 'ALL') { $CLAUSE = ""; };
    if (!$S=='') {
        if ($ACCT == 'ALL') {
            $CLAUSE .= " AND (COMP.NAME LIKE '%{$S}%' OR PEOPLE.NAME LIKE '%{$S}%') ";
        } else {
            $CLAUSE .= " AND COMP.NAME LIKE '%{$S}%' ";
        }
    };
    $sql = "SELECT COUNT(PRN.ORD_NBR) AS JUMLAH_NOTA, 
                COMP.NAME AS PERUSAHAAN, 
                COMP.CO_NBR AS NBR,
                SUM(PRN.TOT_AMT) AS TOTAL_NOTA,
                SUM(PRN.TOT_REM) AS TOTAL_SISA,
                PEOPLE.NAME AS MARKETING,
                PEOPLE.PRSN_NBR AS PEOPLE
            FROM 
                PRN_DIG_ORD_HEAD PRN
                LEFT JOIN COMPANY COMP ON COMP.CO_NBR = PRN.BUY_CO_NBR
                LEFT JOIN PEOPLE ON COMP.ACCT_EXEC_NBR = PEOPLE.PRSN_NBR
            WHERE 
                YEAR(PRN.ORD_TS) = '{$TAHUN}'
                AND MONTH(PRN.ORD_TS) = '{$BULAN}'
                AND PRN.DEL_NBR = '0'
                AND COMP.NAME IS NOT NULL
                AND PEOPLE.NAME IS NOT NULL
                AND PEOPLE.POS_TYP IN ('SNM','RAM','CMA','NAM','COM','DPG','SCM')
                {$CLAUSE}
            GROUP BY 
                COMP.NAME
            ORDER BY 
                PEOPLE.NAME, 
                COMP.CO_NBR";
}

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

prepareResponse($con, $sql, $additional_data);
?>