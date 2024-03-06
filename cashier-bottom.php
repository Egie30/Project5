<?php
require_once "framework/database/connect-cashier.php";
require_once "framework/functions/default.php";
require_once "framework/functions/dotmatrix.php";

$POSID			   = $_GET['POS_ID'];
$transactionNumber = $_GET['TRSC_NBR'];
$personNumber      = $_GET['PRSN_NBR'];
$userID            = substr($_GET['CSH'], 0, 7);
$print             = strtoupper(trim(getParam('config', 'print')));
$isHold            = $_GET['HOLD'];
$isCopy            = $_GET['COPY'];
$isOpenCashDrawer  = $_GET['OCD'];
$RA					=$_GET['RA'];
$cashRegisterDir   = __DIR__ . DIRECTORY_SEPARATOR . "cash-register" . DIRECTORY_SEPARATOR;
$fileName          = $transactionNumber;
if(!empty($transactionNumber)) {
$query				= "SELECT Q_NBR FROM CSH.CSH_REG WHERE TRSC_NBR = ".$transactionNumber;
$result				= mysql_query($query, $csh);
$row				= mysql_fetch_array($result);
$QNumber			= $row['Q_NBR'];
}

/**
 * Open Cash Draw
 * @see http://keyhut.com/popopen.htm
 */
switch ($print) {
    case 'EPSON':
    case 'TM-U220': // Epson TM-U220
        // TO CECK OPEN CASH DROWER
        if ($isOpenCashDrawer) {
            $receipt = chr(27) . chr(112) . chr(48) . chr(25) . chr(250);
        }
        break;
    default: // SP712
        $receipt = chr(7);
        break;
}

if ($isOpenCashDrawer == 1) {
    $fileName = "OCD";
} elseif ($isCopy == 1) {
    $fileName = "CPN-" . $transactionNumber;
} elseif ($isHold == 1) {
    $fileName = "CHL-" . $transactionNumber;
} 

do {
	
    // Don't do anything
    if ($isOpenCashDrawer == 1) {
        break;
    }
	
    // If transaction number is empty don't print details of transaction
    if (empty($transactionNumber)) {
        continue;
    }
	
    // Open Cash Draw
	if ($isCopy != 1 && $isHold != 1) {
		switch ($print) {
			case 'EPSON':
			case 'TM-U220': // Epson TM-U220
				$receipt = chr(27) . chr(112) . chr(48) . chr(25) . chr(250);
				break;
			default: // SP712
				$receipt = chr(7);
				break;
		}
	}

    if ($print != 'EPSON') {
        $receipt = chr(7); //SP712
    }

	$receipt.="  Yogyakarta 55281 Telp.(0274) 6698111  " .chr(13).chr(10);
	$receipt.="          campus@champs.asia".chr(13).chr(10).chr(13).chr(10);
	
    if ($RwdChgCsh > 0){
		$receipt .= "       PENUKARAN HADIAH LANGSUNG        " . chr(13) . chr(10);
        //           1234567890123456789012345678901234567890
        //               1         2         3         4
		$query 	= "SELECT RWD.*, DATE(RWD.CRT_TS) AS CRT_DTE, PPL.NAME AS MBR_NAME 
					FROM CMP.RWD_CHG_CSH RWD 
					LEFT JOIN CMP.PEOPLE PPL ON RWD.MBR_NBR = PPL.MBR_NBR
					WHERE RWD.DEL_NBR=0 AND RWD.RWD_CHG_CSH_NBR=".$RwdChgCsh;
		$result = mysql_query($query);
		$row 	= mysql_fetch_array($result);
		$receipt .= "No Member     : ".$row['MBR_NBR']. chr(13) . chr(10);
		$receipt .= "Nama Member   : ".$row['MBR_NAME']. chr(13) . chr(10);
		$receipt .= "No Nota       : ".$row['ORD_NBR']. chr(13) . chr(10);
		$receipt .= "Total Nota    : ".$row['TOT_AMT']. chr(13) . chr(10);
    } elseif ($isCopy == 1) {
        $receipt .= "            DUPLIKAT STRUK              " . chr(13) . chr(10);
        //           1234567890123456789012345678901234567890
        //               1         2         3         4
    } elseif ($isHold == 1) {
        $receipt .= "             HOLD RECEIPT               " . chr(13) . chr(10);
        //           1234567890123456789012345678901234567890
        //               1         2         3         4
    } elseif($RA=="PO"){
	   	//Generate receipt
		$receipt.="               PAID-OUT".chr(13).chr(10);
		$receipt.="----------------------------------------".chr(13).chr(10);
		$receipt.="  Telah dibayarkan kepada:".chr(13).chr(10);
		if($_GET['PPL_NAME']!=""){
			$receipt.="    ".$_GET['PPL_NAME'].chr(13).chr(10);
		}
		if($_GET['COM_NAME']!=""){
			$receipt.="    ".$_GET['COM_NAME'].chr(13).chr(10);
		}
		$receipt.="  Untuk pembayaran:".chr(13).chr(10);
		$receipt.="    ".$_GET['EXP_DESC']." (".$ExpNbr.")".chr(13).chr(10);
		if($_GET['REF_NBR_INT']!=""){
			$receipt.="     No. Referensi Internal ".$_GET['REF_NBR_INT'].chr(13).chr(10);
		}
		if($_GET['REF_NBR_EXT']!=""){
			$receipt.="     No. Referensi External ".$_GET['REF_NBR_EXT'].chr(13).chr(10);		
		}
		$receipt.="  Dengan perincian sebagai berikut:".chr(13).chr(10);;
		$receipt.="        Nominal Satuan Rp. ".leadSpace($_GET['EXP_AMT'],11).chr(13).chr(10);
		$receipt.="                Jumlah     ".leadSpace($_GET['EXP_Q'],11).chr(13).chr(10);
		$receipt.="        Biaya Tambahan Rp. ".leadSpace($_GET['EXP_ADD'],11).chr(13).chr(10);
		$receipt.="----------------------------------------".chr(13).chr(10);
		$receipt.="                 TOTAL Rp. ".leadSpace($_GET['TOT_SUB'],11).chr(13).chr(10);
	} elseif ($RA=="RE"){
			$receipt.="         DEPOSIT/CASH-IN-DRAWER".chr(13).chr(10);
			$receipt.="----------------------------------------".chr(13).chr(10);
			$query="SELECT (SELECT TND_AMT FROM CSH.CSH_REG WHERE CSH_FLO_TYP='DE' AND TRSC_NBR=CSH.TRSC_NBR) CSH_AMT_D,
					(SELECT TND_AMT FROM CSH.CSH_REG WHERE CSH_FLO_TYP='DR' AND TRSC_NBR=CSH.TRSC_NBR) CSH_AMT_R FROM CSH.CSH_REG CSH WHERE TRSC_NBR=".$transactionNumber." ";
			//echo $query;
		   	$result=mysql_query($query, $csh);
			$row = mysql_fetch_array($result);
			$receipt.=followSpace("Setoran",24)." Rp. ".leadSpace($row['CSH_AMT_D'],11).chr(13).chr(10);
			$receipt.=followSpace("Uang di laci",24)." Rp. ".leadSpace($row['CSH_AMT_R'],11).chr(13).chr(10);
	} elseif ($RA=="RA") {
			$query="SELECT TND_AMT FROM CSH.CSH_REG WHERE TRSC_NBR=".$transactionNumber." AND CSH_FLO_TYP='RA' ";
			//echo $query;
		   	$result=mysql_query($query, $csh);
			$row = mysql_fetch_array($result);
			$receipt.="           RECEIVED-ON-ACCOUNT".chr(13).chr(10);
			$receipt.="----------------------------------------".chr(13).chr(10);
			$receipt.=followSpace("Terima uang di laci",24)." Rp. ".leadSpace($row['TND_AMT'],11).chr(13).chr(10);
	}
	else {
        $receipt .= "           SALES TRANSACTION            " . chr(13) . chr(10);
        //           1234567890123456789012345678901234567890
        //               1         2         3         4
    }

	if($RA == "") {
    $totalBruto      = 0;
    $totalItem       = 0;
    $totalDiscItem   = 0;
    $totalDisc       = 0;
    $totalPPN        = 0;
    $totalSurcharges = 0;
    $memberNumber    = 0;

    $receipt .= "----------------------------------------" . chr(13) . chr(10);

    // Section A -- Items
    $query  = "SELECT REG_NBR,
                TRSC_NBR,
                REG.RTL_BRC,
                RTL_Q,
                REG.RTL_PRC,
                IF((SELECT TND_AMT FROM CSH.CSH_REG WHERE TRSC_NBR=REG.TRSC_NBR AND RTL_BRC=REG.RTL_BRC AND CSH_FLO_TYP='PN') > 0, CONCAT(NAME_DESC, ' (+PPN)'), NAME_DESC) AS NAME_DESC,
                COALESCE(DISC_AMT, 0) AS DISC_AMT,
                DISC_PCT,
                (TND_AMT + IFNULL((SELECT TND_AMT FROM CSH.CSH_REG WHERE TRSC_NBR=REG.TRSC_NBR AND RTL_BRC=REG.RTL_BRC AND CSH_FLO_TYP='PN'), 0)) AS TND_AMT,
                ORD_NBR,
                REG.CSH_FLO_TYP,
                PYMT_DESC,
                REG.PYMT_TYP,
                PYMT_DESC
            FROM CSH.CSH_REG REG
            WHERE REG.CSH_FLO_PART='A' AND CSH_FLO_TYP!='PN' AND TRSC_NBR='" . $transactionNumber . "' ORDER BY CRT_TS, REG_NBR ASC";
    $result = mysql_query($query, $csh);
    while ($row = mysql_fetch_array($result)) {
		$discountDetail    = 0;
		if ($row['DISC_AMT'] != 0) {
            $discountDetail += $row['DISC_AMT'];
        }
            
        if ($row['DISC_PCT'] != 0) {
            $discountDetail += ($row['DISC_PCT'] / 100) * $row['TND_AMT'];
        }
		
		//Pembayaran Digital Printing
		
		if($row['CSH_FLO_TYP']=='FL'){
			$receipt.=followSpace("Pembayaran Digital Printing",40);
			$receipt.=chr(13).chr(10);
			$receipt.=followSpace("Nota ".$row['RTL_BRC'],24);
			$receipt.=" Rp. ";
			$receipt.=leadSpace($row['TND_AMT'],11).chr(13).chr(10);
		}
		else {
        
        $retailPriceDetail = $row['RTL_Q'] . "xRp " . number_format($row['RTL_PRC'], 0, ",", ".");
        $retailPriceAmount = $row['TND_AMT'];
            
        if ($discountDetail != 0) {
            $retailPriceAmount -= $discountDetail;
            $retailPriceDetail .= " - " . number_format($discountDetail, 0, ",", ".");
        }
            
        $receipt .= substr(trim($row['RTL_BRC'] . " " . $row['NAME_DESC']), 0, 40) . chr(13) . chr(10);
        $receipt .= followSpace($retailPriceDetail . " " . $row['ORD_NBR'], 25) . leadSpace(" Rp " . number_format($retailPriceAmount, 0, ",", "."), 15) . chr(13) . chr(10);
		
		}
		
        $totalBruto += $row['TND_AMT'];
        $totalItem += $row['RTL_Q'];
        $totalDiscItem += $discountDetail;
    }

    $receipt .= "----------------------------------------" . chr(13) . chr(10);
    $receipt .= followSpace("TOTAL (" . number_format($totalItem, 0, ",", ".") . " item)", 25) . leadSpace(" Rp " . number_format($totalBruto, 0, ",", "."), 15) . chr(13) . chr(10);

    if ($totalDiscItem > 0) {
        $receipt .= followSpace("TOTAL DISC", 25) . leadSpace(" Rp " . number_format($totalDiscItem, 0, ",", "."), 15) . chr(13) . chr(10);
    }

    // Section B -- Discounts And Tax
    $query  = "SELECT TND_AMT, CSH_FLO_TYP, RTL_PRC
            FROM CSH.CSH_REG REG
            WHERE REG.CSH_FLO_PART='B' AND CSH_FLO_TYP IN ('DS','PN') AND TRSC_NBR='" . $transactionNumber . "' AND TND_AMT > 0";
    $result = mysql_query($query, $csh);
    while ($row = mysql_fetch_array($result)) {
        if ($row['CSH_FLO_TYP'] == 'DS') {
            $title = "Diskon";

            if (abs($row['RTL_PRC']) != abs($row['TND_AMT'])) {
                $title .= " (" . $row['RTL_PRC'] . "%)";
            }
        
            $receipt .= followSpace($title, 25) . leadSpace(" Rp " . number_format($row['TND_AMT'], 0, ",", "."), 15) . chr(13) . chr(10);
            $totalDisc += $row['TND_AMT'];
        } else {
            $receipt .= followSpace("PPN (" . $row['RTL_PRC'] . "%)", 25) . leadSpace(" Rp " . number_format($row['TND_AMT'], 0, ",", "."), 15) . chr(13) . chr(10);
            $totalPPN += $row['TND_AMT'];
        }
    }

	// Section B -- Discounts And Tax (Retur)
    $query  = "SELECT TND_AMT, CSH_FLO_TYP, RTL_PRC
            FROM CSH.CSH_REG REG
            WHERE REG.CSH_FLO_PART='B' AND CSH_FLO_TYP IN ('DS','PN') AND TRSC_NBR='" . $transactionNumber . "' AND TND_AMT < 0";
    $result = mysql_query($query, $csh);
    while ($row = mysql_fetch_array($result)) {
        if ($row['CSH_FLO_TYP'] == 'DS') {
            $title = "Diskon Retur";

            if (abs($row['RTL_PRC']) != abs($row['TND_AMT'])) {
                $title .= " (" . $row['RTL_PRC'] . "%)";
            }
        
            $receipt .= followSpace($title, 25) . leadSpace(" Rp " . number_format($row['TND_AMT'], 0, ",", "."), 15) . chr(13) . chr(10);
            $totalDisc += $row['TND_AMT'];
        } else {
            $receipt .= followSpace("PPN (" . $row['RTL_PRC'] . "%)", 25) . leadSpace(" Rp " . number_format($row['TND_AMT'], 0, ",", "."), 15) . chr(13) . chr(10);
            $totalPPN += $row['TND_AMT'];
        }
    }
	
    // Section B -- Surcharges
    $query  = "SELECT TND_AMT
            FROM CSH.CSH_REG REG
            WHERE REG.CSH_FLO_PART='B' AND CSH_FLO_TYP IN ('SU') AND TRSC_NBR='" . $transactionNumber . "'";
    $result = mysql_query($query, $csh);
    while ($row = mysql_fetch_array($result)) {
        $receipt .= followSpace("Card fee", 25) . leadSpace(" Rp " . number_format($row['TND_AMT'], 0, ",", "."), 15) . chr(13) . chr(10);
        $totalSurcharges += $row['TND_AMT'];
    }

    $receipt .= "----------------------------------------" . chr(13) . chr(10);
    $receipt .= followSpace("NET TOTAL", 25) . leadSpace(" Rp " . number_format($totalBruto + $totalPPN + $totalSurcharges - ($totalDisc + $totalDiscItem), 0, ",", "."), 15) . chr(13) . chr(10);

    // Section C -- Payments
    $query  = "SELECT TND_AMT,ORD_NBR,PYMT_DESC
                FROM CSH.CSH_REG REG
                WHERE REG.CSH_FLO_PART='C' AND TRSC_NBR='" . $transactionNumber . "'";
    $result = mysql_query($query, $csh);
            
    while ($row = mysql_fetch_array($result)) {
        $receipt .= followSpace(trim($row['PYMT_DESC'] . " " . $row['ORD_NBR']), 25);
        $receipt .= leadSpace(" Rp " . number_format($row['TND_AMT'], 0, ",", "."), 15) . chr(13) . chr(10);
    }

    // Section D -- Change
    $query  = "SELECT TND_AMT FROM CSH.CSH_REG REG WHERE REG.CSH_FLO_PART='D' AND TRSC_NBR='" . $transactionNumber . "'";
    $result = mysql_query($query, $csh);

    if (mysql_num_rows($result) > 0) {        
        $receipt .= "----------------------------------------" . chr(13) . chr(10);

        while ($row = mysql_fetch_array($result)) {
            $receipt .= followSpace("Kembali", 25) . leadSpace(" Rp " . number_format($row['TND_AMT'], 0, ",", "."), 15) . chr(13) . chr(10);
        }
    }
	
	} //end of empty $RA

	$receipt .= chr(13) . chr(10);
    $receipt .= followSpace("Terima kasih atas kunjungan Anda", 40) . chr(13) . chr(10);
    $receipt .= followSpace(leadZero($QNumber, 3)."#".leadZero($transactionNumber, 6) ." ".$userID . " " . date("d-m-Y") . " " . date("H:i:s"), 40) . chr(13) . chr(10);
	
    $receipt .= chr(13) . chr(10);
    $receipt .= chr(13) . chr(10);
	$receipt .= chr(13) . chr(10);
	
    //               1         2         3         4
    //           1234567890123456789012345678901234567890
	
	$receipt.="            CHAMPION CAMPUS".chr(13).chr(10);
	$receipt.="         Lt.2 The Jayan Building".chr(13).chr(10);
	$receipt.="        Jl. Affandi (Gejayan) No.4 ".chr(13).chr(10);
	
    //           1234567890123456789012345678901234567890
    //               1         2         3         4
    break;
} while (false);

if ($fileName != "") {

    $fileHandler = fopen($cashRegisterDir . $fileName . ".txt", "w");
    
    fwrite($fileHandler, $receipt);
    fclose($fileHandler);
}

// If transaction number is not empty change the flag to inactive
if (!empty($transactionNumber)) {
    $query  = "UPDATE CSH.CSH_REG SET ACT_F=0 WHERE TRSC_NBR='" . $transactionNumber . "'";
    $result = mysql_query($query, $csh);
}

include_once __DIR__ . "/cashier-navigation.php";