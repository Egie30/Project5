<?php
require_once 'framework/database/connect.php';
require_once 'framework/functions/default.php';
require_once 'framework/functions/dotmatrix.php';
require_once "framework/phpExcel/Classes/PHPExcel.php";

$year  		= $_GET['YEAR'];
$month 		= $_GET['MONTH'];
$rcvCoNbr   = $_GET['RCV_CO_NBR'];
$where 		= $_GET['where'];
$title 		= 'Receivables';

//Process filter
$OrdSttId=$_GET['STT'];
//Get active order parameter
//$activePeriod=getParam("print-digital","period-order-active-month");
//$badPeriod=getParam("print-digital","period-bad-order-month");
$activePeriod=3;
$badPeriod=12;

//Continue process filter
if($OrdSttId=="ALL")
{
	$where="WHERE HED.ORD_STT_ID LIKE '%'";
}
elseif($OrdSttId=="CP")
{
	$where="WHERE HED.ORD_STT_ID='CP' AND TIMESTAMPADD(MONTH,$activePeriod,ORD_DTE)>=CURRENT_TIMESTAMP AND HED.DEL_F=0";
}
elseif($OrdSttId=="DUE")
{
	$where="WHERE TOT_REM>0 AND DATE_ADD(CMP_TS,INTERVAL COALESCE(PAY_TERM,0) DAY)<=CURRENT_TIMESTAMP AND HED.DEL_F=0";
}
elseif($OrdSttId=="COL")
{
	$rcvCoNbr=$_GET['RCV_CO_NBR'];
	if($rcvCoNbr!="")
	{
		$whereString=" AND RCV_CO_NBR=".$rcvCoNbr;
	}
	if($rcvCoNbr=="0")
	{
		$whereString=" AND (RCV_CO_NBR IS NULL)";
	}
	$where="WHERE HED.DEL_F=0 ".$whereString." AND YEAR(ORD_DTE)=".$_GET['YEAR']." AND MONTH(ORD_DTE)=".$_GET['MONTH']." AND TOT_REM>0";
}
elseif($OrdSttId=="ACT")
{
	$rcvCoNbr   = $_GET['RCV_CO_NBR'];
	$year       = $_GET['YEAR'];
	$month 		= $_GET['MONTH'];

	if($rcvCoNbr != "")
	{
		$whereString = " AND RCV_CO_NBR=".$rcvCoNbr;
	}

	if($rcvCoNbr == "0")
	{
		$whereString=" AND (RCV_CO_NBR IS NULL)";
	}
		
	//$where="WHERE (HED.ORD_STT_ID!='CP' OR (HED.ORD_STT_ID='CP' AND TIMESTAMPADD(MONTH,$activePeriod,ORD_TS)>=CURRENT_TIMESTAMP) OR (TOT_REM>0 AND TIMESTAMPADD(MONTH,$badPeriod,ORD_TS)>=CURRENT_TIMESTAMP)) AND HED.DEL_NBR=0";
	if($month != "")
	{
		$where="WHERE HED.DEL_F=0 ".$whereString." AND YEAR(ORD_DTE)=".$year." AND MONTH(ORD_DTE)=".$month." AND (COALESCE(TOT_SUB,0) - COALESCE(TND_AMT,0)) > 0";
	}
	else
	{
		$where="WHERE HED.DEL_F=0 ".$whereString." AND ((COALESCE(TOT_SUB,0) + COALESCE(FEE_MISC,0)) - COALESCE(TND_AMT,0)) > 0";
	}
}
else
{
	$where="WHERE HED.ORD_STT_ID='".$OrdSttId."' AND HED.DEL_F=0";
}
	
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
		'Jumlah',
		'Bayar',
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
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
            ),
			'numberformat' => array(
                'code' => '#,##0'
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


$query  = "SELECT NBR FROM CDW.PRN_DIG_TOP_CUST";
$result = mysql_query($query);

while($row = mysql_fetch_array($result))
{
	$TopCusts[]=strval($row['NBR']);
}

$query="SELECT 	HED.ORD_NBR,
				IVC_PRN_CNT,
				ORD_DTE,
				HED.ORD_STT_ID,
				ORD_STT_DESC,
				COM.NAME AS NAME_CO,
				RCV_CO_NBR,
				REF_NBR,
				ORD_TTL,
				FEE_MISC,
				TOT_AMT,
				PYMT_DOWN,
				PYMT_REM,
				TOT_REM,
				DL_TS,
				CMP_TS,
				SPC_NTE,
				HED.CRT_TS,
				HED.CRT_NBR,
				HED.UPD_TS,
				HED.UPD_NBR,
				CMP_TS,
				DATEDIFF(DATE_ADD(CMP_TS,INTERVAL COALESCE(COM.PAY_TERM,0) DAY),CURRENT_TIMESTAMP) AS PAST_DUE,
				COALESCE(FEE_MISC,0) AS FEE_MISC,
				COALESCE(TOT_AMT,0) AS TOT_AMT,
				COALESCE(TOT_REM,0) AS TOT_REM,
				COALESCE(TOT_SUB,0) AS TOT_SUB,
				COALESCE(TOT_SUB,0) + COALESCE(FEE_MISC,0) AS TTL_AMT,
				COALESCE(TND_AMT,0) AS TND_AMT,
				(COALESCE(TOT_SUB,0) + COALESCE(FEE_MISC,0)) - COALESCE(TND_AMT,0) AS TTL_REM
		FROM RTL.RTL_ORD_HEAD HED
		INNER JOIN RTL.ORD_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID
		LEFT OUTER JOIN CMP.COMPANY COM ON HED.RCV_CO_NBR=COM.CO_NBR
		LEFT OUTER JOIN(
							SELECT ORD_NBR,
								   SUM(TOT_SUB) AS TOT_SUB
							FROM RTL.RTL_ORD_DET
							WHERE DEL_NBR = 0
							GROUP BY ORD_NBR
						)DET ON HED.ORD_NBR = DET.ORD_NBR
		LEFT OUTER JOIN(
							SELECT 
								ORD_NBR,
								SUM(TND_AMT) AS TND_AMT
							FROM RTL.RTL_ORD_PYMT
							WHERE DEL_NBR = 0
							GROUP BY ORD_NBR
						)PYMT ON HED.ORD_NBR = PYMT.ORD_NBR	
		$where
		ORDER BY ORD_NBR DESC";
//echo $query;
$result=mysql_query($query);
while ($row	= mysql_fetch_array ( $result )) 
{
    $dataArray = array(
		html_entity_decode($row['ORD_NBR'], ENT_QUOTES, "UTF-8"),
        html_entity_decode($row['ORD_TTL'], ENT_QUOTES, "UTF-8"),
        html_entity_decode($row['NAME_CO'], ENT_QUOTES, "UTF-8"),
		html_entity_decode(parseDateShort($row['ORD_DTE']), ENT_QUOTES, "UTF-8"),
		html_entity_decode($row['ORD_STT_DESC'], ENT_QUOTES, "UTF-8"),
		html_entity_decode(parseDateShort($row['DL_TS']), ENT_QUOTES, "UTF-8"),
		html_entity_decode(parseDateShort($row['CMP_TS']), ENT_QUOTES, "UTF-8"),
		html_entity_decode($row['TTL_AMT'], ENT_QUOTES, "UTF-8"),
		html_entity_decode($row['TND_AMT'], ENT_QUOTES, "UTF-8"),
		html_entity_decode($row['TTL_REM'], ENT_QUOTES, "UTF-8"),
    );

    $reports['data'][] = $dataArray;
	
}

return $reports;