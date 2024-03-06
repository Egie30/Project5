<?php
require_once __DIR__ . "/../../framework/database/connect.php";
require_once __DIR__ . "/../../framework/functions/default.php";

$_GET['LIMIT'] = -1;

if ($_GET['CD_CAT_NBR'] == "") {
	// $_GET['CD_CAT_NBR'] = array(1,2,3);
}

try {
	$_GET['GROUP'] = array("CD_CAT_NBR");

	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "account.php";

	$resultsAccountCategory = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}


$resultsTrialBalance = array(
	'parameter' => $_GET,
	'data' => array(),
	'total' => array(
		'BEGIN_DEB' => 0,
		'BEGIN_CRT' => 0,
		'ACC_DEB' => 0,
		'ACC_CRT' => 0,
		'BALANCE_DEB' => 0,
		'BALANCE_CRT' => 0,
	)
);

foreach ($resultsAccountCategory->data as $resultAccountCategory) {
	try {
		$_GET['CD_CAT_NBR'] = $resultAccountCategory->CD_CAT_NBR;
		$_GET['GROUP'] = array("CD_NBR");

		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "account.php";

		$resultsAccount = json_decode(ob_get_clean());
	} catch (\Exception $ex) {
		ob_end_clean();
	}

	if (empty($resultsAccount->data)) {
		continue;
	}
	
	$resultAccountCategory->ACCOUNT = array();

	foreach ($resultsAccount->data as $resultAccount) {
		unset($_GET['CD_CAT_NBR']);
		unset($_GET['CD_NBR']);
		unset($_GET['CD_SUB_NBR']);

		try {
			$_GET['CD_NBR'] = $resultAccount->CD_NBR;
			$_GET['GROUP'] = array("CD_SUB_NBR");

			ob_start();
			include __DIR__ . DIRECTORY_SEPARATOR . "account.php";

			$resultsAccountSub = json_decode(ob_get_clean());
		} catch (\Exception $ex) {
			ob_end_clean();
		}

		if (empty($resultsAccountSub->data)) {
			continue;
		}

		$resultAccount->ACCOUNT = array();

		foreach ($resultsAccountSub->data as $resultAccountSub) {
			try {
				$_GET['CD_SUB_NBR'] = $resultAccountSub->CD_SUB_NBR;
				$_GET['GROUP'] = array("CD_SUB_NBR");

				ob_start();
				include __DIR__ . DIRECTORY_SEPARATOR . "journal.php";

				$resultJournal = json_decode(ob_get_clean());
			} catch (\Exception $ex) {
				ob_end_clean();
			}

			if (empty($resultJournal->data) && $resultAccountSub->DEB == 0 && $resultAccountSub->CRT == 0) {
				continue;
			}

			$resultAccountSub->GL_DEB = $resultJournal->total->GL_DEB;
			$resultAccountSub->GL_CRT = $resultJournal->total->GL_CRT;
			$resultAccountSub->BALANCE_DEB = $resultAccountSub->DEB + $resultJournal->total->GL_DEB;
			$resultAccountSub->BALANCE_CRT = $resultAccountSub->CRT + $resultJournal->total->GL_CRT;

			if (in_array($resultAccountSub->CD_CAT_NBR, array(1,5,6,9))) {
				$resultAccountSub->BALANCE = $resultAccountSub->BALANCE_DEB - $resultAccountSub->BALANCE_CRT;
			} else {
				$resultAccountSub->BALANCE = $resultAccountSub->BALANCE_CRT - $resultAccountSub->BALANCE_DEB;
			}
		
			$resultAccount->ACCOUNT[] = $resultAccountSub;

			$resultAccountCategory->GL_DEB += $resultAccountSub->GL_DEB;
			$resultAccountCategory->GL_CRT += $resultAccountSub->GL_CRT;
			$resultAccountCategory->BALANCE_DEB += $resultAccountSub->BALANCE_DEB;
			$resultAccountCategory->BALANCE_CRT += $resultAccountSub->BALANCE_CRT;

			$resultsTrialBalance['total']['BEGIN_DEB'] += $resultAccountSub->DEB;
			$resultsTrialBalance['total']['BEGIN_CRT'] += $resultAccountSub->CRT;
			$resultsTrialBalance['total']['ACC_DEB'] += $resultAccountSub->GL_DEB;
			$resultsTrialBalance['total']['ACC_CRT'] += $resultAccountSub->GL_CRT;
			$resultsTrialBalance['total']['BALANCE_DEB'] += $resultAccountSub->BALANCE_DEB;
			$resultsTrialBalance['total']['BALANCE_CRT'] += $resultAccountSub->BALANCE_CRT;
		}

		if (empty($resultAccount->ACCOUNT)) {
			continue;
		}

		$resultAccountCategory->ACCOUNT[] = $resultAccount;
	}
	
	$resultsTrialBalance['data'][] = $resultAccountCategory;
	
	unset($_GET['CD_CAT_NBR']);
	unset($_GET['CD_NBR']);
	unset($_GET['CD_SUB_NBR']);
}

echo json_encode($resultsTrialBalance);