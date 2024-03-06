<?php
require_once "/../../framework/database/connect.php";
require_once "/../../framework/functions/default.php";

$bookNumber		= $_GET['BK_NBR'];
$Accounting		= $_GET['ACTG'];

if(empty($_GET['YEARS'])) {
	$years	= date('Y');
}
else {
	$years	= $_GET['YEARS'];
}

$array_stock 	= array();
$array_cost 	= array();
$profit_loss 	= array();


$results = array(
	'parameter' => $_GET,
	'monthly' => array(),
	'data' => array(),
	'hpp' => array(),
	'total' => array()
);

$_GET['GROUP']		= 'MONTH';
$_GET['YEARS']		= $years;
$_GET['RL_TYP']		= 'RL_YEAR';

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "../prn-dig-report-order.php";

	$resultsPrintOrder = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

echo "<pre>";
print_r($resultsPrintOrder);

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "../prn-dig-report-full.php";

	$resultsPrintFull = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}



$query_bk	= "SELECT BK_NBR, 
				(BEG_DTE - INTERVAL 1 DAY) AS BEGIN, 
				BEG_DTE AS BEG_DT,
				END_DTE AS END_DT,
				MONTH(BEG_DTE) AS BK_MONTH,
				YEAR(BEG_DTE) AS BK_YEAR,
				MONTH(BEG_DTE - INTERVAL 1 MONTH) AS BK_MONTH_BEG,
				YEAR(BEG_DTE - INTERVAL 1 MONTH) AS BK_YEAR_BEG
			FROM RTL.ACCTG_BK WHERE YEAR(BEG_DTE) = '".$years."'
			";
			
$result_bk 	= mysql_query($query_bk);



while($row_bk = mysql_fetch_array($result_bk)) {
	
$_GET['STK_MONTH']	= $row_bk['BK_MONTH'];
$_GET['STK_YEAR']	= $row_bk['BK_YEAR'];

	try {
		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "../store-inventory-monthly.php";

		$resultsStock = json_decode(ob_get_clean());
	} catch (\Exception $ex) {
		ob_end_clean();
	}
	
	
	foreach($resultsStock->data as $stock) {
		array_push($array_stock,array(
			'STK_MONTH' => $_GET['STK_MONTH'],
			'STK_YEAR' => $_GET['STK_YEAR'],
			'RCV_TOT_SUB' => $stock->RCV_TOT_SUB,
			'RTR_TOT_SUB' => $stock->RTR_TOT_SUB,
			'COR_TOT_SUB' => $stock->COR_TOT_SUB,
			'BALANCE_Q' => $stock->BALANCE_Q,
			'BALANCE_AMT' => $stock->BALANCE_AMT
		));
	}

	unset($_GET['STK_MONTH']);
	unset($_GET['STK_YEAR']);
}

$results['hpp'] = $array_stock;


$_GET['GROUP']		= 'MONTH';

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "../cost-cash.php";

	$resultsCash = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}


try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "../cost-routine.php";

	$resultsRoutine = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}


try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "../payroll.php";

	$resultsPayroll = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

unset($_GET['GROUP']);

$_GET['CAT_SUB_TYP']= "COST";
$_GET['IVC_TYP']	= 'RC';
$_GET['GROUP'] 		= "MONTH";
$_GET['CAT_NBR'] 	= "12,16,17";

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "../purchase-report.php";

	$resultsCost = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

unset($_GET['CAT_SUB_NBR']);
unset($_GET['CAT_SUB_TYP']);
unset($_GET['CAT_NBR']);

$_GET['CAT_SUB_TYP']	= 'CLICK';
$_GET['IVC_TYP']		= 'RC';
$_GET['TYP']			= 'PRN_DIG';
$_GET['CAT_SUB_NBR'] 	= 202;

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "../purchase-report.php";

	$resultsClick = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}

$results['data']['PRINT_ORDER']		= $resultsPrintOrder;
$results['data']['PRINT_FULL']		= $resultsPrintFull;

$results['data']['COGS']			= $array_stock;;

$results['data']['ROUTINE'] 		= $resultsRoutine;
$results['data']['UTILITY'] 		= $resultsRoutine->utility;

$results['data']['CASH'] 			= $resultsCash;
$results['data']['EXPENSE'] 		= $resultsCash->expense;

$results['data']['PAYROLL'] 		= $resultsPayroll;

//$results['data']['COST'] 			= $resultsCost;

$results['data']['CLICK'] 			= $resultsClick;

$results['data']['TOTAL_COST'] 		= $resultsPayroll->total->PAY_AMT + $resultsRoutine->total->TOT_SUB + $resultsCash->total->TOT_SUB + $resultsCost->total->RCV_TOT_SUB;


/*
$query_month	= "SELECT BK_NBR, MONTH(BEG_DTE) AS BK_MONTH, MONTHNAME(BEG_DTE) AS BK_MONTHNAME, YEAR(BEG_DTE) AS BK_YEAR,MONTHNAME(BEG_DTE) AS BK_MONTHNAME FROM RTL.ACCTG_BK WHERE YEAR(BEG_DTE) = ".$years." ";

$result_month	= mysql_query($query_month);

while($row_month = mysql_fetch_array($result_month)) {
	
	$Month = $row_month['BK_MONTH'];
	
	foreach($resultsPayroll->data as $payroll) {
		if($Month == $payroll->PAY_MONTH) {
			$array_cost[$Month]		+= $payroll->PAY_AMT;
		}
	}

	foreach($resultsCash->data as $cash) {
		if($Month == $cash->EXP_MONTH) {
			$array_cost[$Month]		+= $cash->TOT_SUB;
		}
	}
	
	foreach($resultsRoutine->data as $routine) {
		if($Month == $routine->UTL_MONTH) {
			$array_cost[$Month]		+= $routine->TOT_SUB;
		}
	}
	
	foreach($resultsCost->data as $cost) {
		if($Month == $cost->ORD_MONTH) {
			$array_cost[$Month]		+= $resultsCost->total->RCV_TOT_SUB;
		}
	}
	
	foreach($resultsClick->data as $click) {
		if($Month == $click->ORD_MONTH) {
			$array_cost[$Month]		+= $resultsClick->total->RCV_TOT_SUB;
		}
	}
}

$results['data']['COST'] 	= $array_cost;
*/

//echo "<pre>";
//print_r($array_cost);

//echo json_encode($results);

