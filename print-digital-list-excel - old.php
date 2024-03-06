<?php
require_once 'framework/database/connect.php';
require_once 'framework/functions/default.php';
require_once 'framework/functions/dotmatrix.php';
require_once "framework/phpExcel/Classes/PHPExcel.php";

$year  		= $_GET['YEAR'];
$month 		= $_GET['MONTH'];
$buyPrsnNbr = $_GET['BUY_PRSN_NBR'];
$buyCoNbr   = $_GET['BUY_CO_NBR'];
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
if($OrdSttId=="ALL"){
	$where="WHERE HED.ORD_STT_ID LIKE '%'";
}elseif($OrdSttId=="CP"){
	$where="WHERE HED.ORD_STT_ID='CP' AND TIMESTAMPADD(MONTH,$activePeriod,ORD_TS)>=CURRENT_TIMESTAMP AND HED.DEL_NBR=0";
}elseif($OrdSttId=="DUE"){
	$where="WHERE TOT_REM>0 AND DATE_ADD(CMP_TS,INTERVAL COALESCE(PAY_TERM,0) DAY)<=CURRENT_TIMESTAMP AND HED.DEL_NBR=0";
}elseif($OrdSttId=="COL"){
	$buyPrsnNbr=$_GET['BUY_PRSN_NBR'];
	$buyCoNbr=$_GET['BUY_CO_NBR'];
	if($buyCoNbr!=""){
		$whereString=" AND BUY_CO_NBR=".$buyCoNbr;
		if($buyPrsnNbr!=""){
			$whereString.=" AND BUY_PRSN_NBR=".$buyPrsnNbr;
		}
	}else{
		if($buyPrsnNbr!=""){
			$whereString=" AND BUY_PRSN_NBR=".$buyPrsnNbr;
		}
	}
	
	if(($buyPrsnNbr=="0")&&($buyCoNbr=="0")){$whereString=" AND (BUY_CO_NBR IS NULL AND BUY_PRSN_NBR IS NULL)";}
		$where="WHERE HED.DEL_NBR=0 ".$whereString." AND YEAR(ORD_TS)=".$_GET['YEAR']." AND MONTH(ORD_TS)=".$_GET['MONTH']." AND TOT_REM>0";
}elseif($OrdSttId=="ACT"){
	$buyPrsnNbr = $_GET['BUY_PRSN_NBR'];
	$buyCoNbr   = $_GET['BUY_CO_NBR'];
	$year       = $_GET['YEAR'];
	$month 		= $_GET['MONTH'];

	if($buyCoNbr != ""){
		$whereString = " AND BUY_CO_NBR=".$buyCoNbr;
		if($buyPrsnNbr != ""){
			$whereString.=" AND BUY_PRSN_NBR=".$buyPrsnNbr;
		}
	}else{
		if($buyPrsnNbr!=""){
			$whereString=" AND BUY_PRSN_NBR=".$buyPrsnNbr;
		}
	}
		
	if(($buyPrsnNbr == "0")&&($buyCoNbr == "0")){
		$whereString=" AND (BUY_CO_NBR IS NULL AND BUY_PRSN_NBR IS NULL)";
	}
		
	$where="WHERE (HED.ORD_STT_ID!='CP' OR (HED.ORD_STT_ID='CP' AND TIMESTAMPADD(MONTH,$activePeriod,ORD_TS)>=CURRENT_TIMESTAMP) OR (TOT_REM>0 AND TIMESTAMPADD(MONTH,$badPeriod,ORD_TS)>=CURRENT_TIMESTAMP)) AND HED.DEL_NBR=0";
}else{
	$where="WHERE HED.ORD_STT_ID='".$OrdSttId."' AND HED.DEL_NBR=0";
}
	
$reports = array(
    'title' => $title,
    'column' => array( 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'),
    'columnDimension' => array( 10, 50, 60, 15, 15, 15, 15, 15, 15 ),
    'columnDimensionPdf' => array( 1, 3, 8, 2, 2.5, 3, 3, 3 , 3 ),
    'titles' => array(
        'No.',
        'Judul',
        'Pemesan',
        'Pesan',
        'Status',
        'Janji',
		'Jadi',
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
    ),
    'data' => array(),
    'total' => array(
    )
);


$query="SELECT NBR FROM CDW.PRN_DIG_TOP_CUST";
$result=mysql_query($query);
while($row=mysql_fetch_array($result)){
	$TopCusts[]=strval($row['NBR']);
}

$query="SELECT HED.ORD_NBR,DL_CNT,PU_CNT,NS_CNT,IVC_PRN_CNT,ORD_TS,HED.ORD_STT_ID,ORD_STT_DESC,BUY_PRSN_NBR,PPL.NAME AS NAME_PPL,COM.NAME AS NAME_CO,BUY_CO_NBR,REF_NBR,ORD_TTL,DUE_TS,JOB_LEN_TOT,PRN_CO_NBR,FEE_MISC,TOT_AMT,PYMT_DOWN,PYMT_REM,TOT_REM,CMP_TS,PU_TS,SPC_NTE,HED.CRT_TS,HED.CRT_NBR,HED.UPD_TS,HED.UPD_NBR,CMP_TS,DATEDIFF(DATE_ADD(CMP_TS,INTERVAL COALESCE(COM.PAY_TERM,0) DAY),CURRENT_TIMESTAMP) AS PAST_DUE
FROM CMP.PRN_DIG_ORD_HEAD HED
	INNER JOIN CMP.PRN_DIG_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID
	LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR
	LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR $where
ORDER BY ORD_NBR DESC";
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
		html_entity_decode($row['TOT_AMT'], ENT_QUOTES, "UTF-8"),
		html_entity_decode($row['TOT_REM'], ENT_QUOTES, "UTF-8"),
		
    );

    $reports['data'][] = $dataArray;
	
}

return $reports;