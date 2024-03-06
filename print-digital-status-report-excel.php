<?php
require_once 'framework/database/connect.php';
require_once 'framework/functions/default.php';
require_once 'framework/functions/dotmatrix.php';
require_once "framework/phpExcel/Classes/PHPExcel.php";

$searchQuery    = strtoupper($_REQUEST['s']);
$whereClauses   = array("TOT_REM > 0", "HED.ORD_STT_ID = 'BL'");
$whereClauses[] = "HED.DEL_NBR=0";
$whereClauses 	= implode(" AND ", $whereClauses);
$title 		= 'Billing Report';
	
$reports = array(
    'title' => $title,
    'column' => array( 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'),
    'columnDimension' => array( 10, 50, 60, 15, 15, 15, 15, 15, 15, 15 ),
    'columnDimensionPdf' => array( 1, 3, 8, 2, 2.5, 3, 3, 3 , 3 , 3 ),
    'titles' => array(
        'No.',
        'Judul',
        'Pemesan',
        'Pesan',
        'Status',
        'Janji',
		'Jadi',
		'Jatuh Tempo',
		'Jumlah',
		'Sisa',
    ),
    'styles' => array(
        'A' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
            )
        ),
        'B' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
            )
        ),
        'C' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
            )
        ),
        'D' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            )
        ),
        'E' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            )
        ),
        'F' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            )
        ),
		'G' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            )
        ),
		'H' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
            )
        ),
		'I' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
            ),
			'numberformat' => array(
                'code' => '#,##0'
            )
        ),
		'J' => array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
            ),
			'numberformat' => array(
                'code' => '#,##0'
            )
        ),
    ),
    'data' => array(),
    'total' => array(
    )
);

if ($searchQuery != "") {
	$searchQuery = explode(" ", $searchQuery);

	foreach ($searchQuery as $query) {
		$query = trim($query);

		if (empty($query)) {
			continue;
		}

		if (strrpos($query, '%') === false) {
			$query = '%' . $query . '%';
		}
		$whereClauses[] = "(
			HED.ORD_NBR LIKE '" . $query . "'
			OR HED.BUY_CO_NBR LIKE '" . $query . "'
			OR COM.NAME LIKE '" . $query . "'
			OR HED.BUY_PRSN_NBR LIKE '" . $query . "'
			OR PPL.NAME LIKE '" . $query . "'
		)";
	}
}

$query="SELECT 
	HED.ORD_NBR,
	HED.ORD_TTL,
	COUNT(HED.ORD_NBR) AS ORD_NBR_CNT, 
	YEAR(HED.ORD_TS) AS ORD_YEAR,
	MONTH(HED.ORD_TS) AS ORD_MONTH,
	HED.BUY_CO_NBR,
	COM.NAME AS NAME_CO,
	HED.BUY_PRSN_NBR,
	PPL.NAME AS NAME_PPL,
	HED.ORD_STT_ID,
	ORD_STT_DESC,
	DUE_TS,
	PU_TS,
	CMP_TS,
	ORD_TS,
	DATE_ADD(CMP_TS,INTERVAL COALESCE(COM.PAY_TERM,0) DAY) AS PAST_DUE,
	JOB_LEN_TOT,
	DL_CNT,
	PU_CNT,
	NS_CNT,
	IVC_PRN_CNT,
	COUNT(HED.ORD_NBR) AS ORD_NBR_CNT, 
	SUM(COALESCE(TOT_AMT,0)) AS TOT_AMT,
	SUM(COALESCE(PAY.TND_AMT,0)) AS PYMT_DOWN,
	SUM(COALESCE(TOT_AMT,0)) - SUM(COALESCE(PAY.TND_AMT,0)) AS TOT_REM 
FROM CMP.PRN_DIG_ORD_HEAD HED 
	INNER JOIN CMP.PRN_DIG_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID 
	LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR 
	LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR
	LEFT JOIN (
		SELECT 
			PYMT.ORD_NBR,
			SUM(COALESCE(PYMT.TND_AMT,0)) AS TND_AMT
		FROM CMP.PRN_DIG_ORD_PYMT PYMT
		WHERE PYMT.DEL_NBR = 0
		GROUP BY PYMT.ORD_NBR
	) PAY ON PAY.ORD_NBR = HED.ORD_NBR
WHERE " . $whereClauses . "
GROUP BY HED.ORD_NBR
ORDER BY HED.ORD_NBR DESC";
//echo $query;
$result=mysql_query($query);
while ($row	= mysql_fetch_array ( $result )) {
   
    $dataArray = array(
		html_entity_decode($row['ORD_NBR'], ENT_QUOTES, "UTF-8"),
        html_entity_decode($row['ORD_TTL'], ENT_QUOTES, "UTF-8"),
        html_entity_decode($row['NAME_PPL']." ".$row['NAME_CO'], ENT_QUOTES, "UTF-8"),
		html_entity_decode(parseDateShort($row['ORD_TS']), ENT_QUOTES, "UTF-8"),
		html_entity_decode($row['ORD_STT_DESC'], ENT_QUOTES, "UTF-8"),
		html_entity_decode(parseDateShort($row['DUE_TS']), ENT_QUOTES, "UTF-8"),
		html_entity_decode(parseDateShort($row['CMP_TS']), ENT_QUOTES, "UTF-8"),
		html_entity_decode(parseDateShort($row['PAST_DUE']), ENT_QUOTES, "UTF-8"),
		html_entity_decode($row['TOT_AMT'], ENT_QUOTES, "UTF-8"),
		html_entity_decode($row['TOT_REM'], ENT_QUOTES, "UTF-8"),
		
    );

    $reports['data'][] = $dataArray;
	
}

return $reports;