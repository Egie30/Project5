<?php
require_once "framework/database/connect-cashier.php";
require_once "framework/security/default.php";
require_once "framework/functions/default.php";
require_once "framework/functions/dotmatrix.php";
?>

<script type='text/javascript'>
    // Let's Clear the notification
    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='none';
    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.innerHTML='';
</script>
<?php
// Make some variables global
$touchScreen  = $_GET['TS'];
$action       = $_GET['ACTION'];
$userID       = $_SESSION['userID'];
$personNumber = $_SESSION['personNBR'];

$security = getSecurity($userID, "Finance");

if ($userID == "") {
    ?><script text="type/javascript">
        if (typeof window.top.forceLogin === "function") {
            window.top.forceLogin();
        } else {
            window.top.location='cashier.php?POS_ID=<?php echo $POSID;?>&TS=<?php echo $touchScreen;?>';
        }
    </script><?php
    exit(0);
}

// Take Care of default value.
$ppnF = false;
$taxF = false;
$surF = false;
$surchargeVal = false;

/**
 * Check to see if any open transaction. Some new procedures was applied to checking an open transaction.
 * - Put it globally. So we are doesn't repeat same query.
 * - This procedures really faster than old procedure.
 * - Strict mode has been applied
 */
$query             = "SELECT MAX(TRSC_NBR) AS TRSC_NBR
                    FROM RTL.CSH_REG
                    WHERE ACT_F=1 AND DATE(CRT_TS)=CURRENT_DATE AND POS_ID='" . $POSID . "'";
$result            = mysql_query($query, $rtl);
$row               = mysql_fetch_array($result);
$transactionNumber = $row['TRSC_NBR'];


$query  = "SELECT MAX(TRSC_NBR_PLUS) AS TRSC_NBR_PLUS
                    FROM RTL.CSH_REG
                    WHERE ACT_F=1 AND DATE(CRT_TS)=CURRENT_DATE AND POS_ID='" . $POSID . "'";
$result            = mysql_query($query, $rtl);
$row               = mysql_fetch_array($result);
$transactionNumberPlus = $row['TRSC_NBR_PLUS'];


$query		= "SELECT MAX(Q_NBR) AS Q_NBR
				FROM RTL.CSH_REG
                WHERE ACT_F=1 AND DATE(CRT_TS)=CURRENT_DATE AND POS_ID='" . $POSID . "'";
$result		= mysql_query($query, $rtl);
$row		= mysql_fetch_array($result);
$QNumber	= $row['Q_NBR'];


	
// Also make some flags global
if (!empty($transactionNumber)) {
	// Check if tax is applied
	$query  = "SELECT REG_NBR FROM RTL.CSH_REG WHERE CSH_FLO_TYP='PN' AND TRSC_NBR='" . $transactionNumber . "'";
	$result = mysql_query($query, $rtl);
	$row    = mysql_fetch_array($result);

	if (!empty($row['REG_NBR'])) {
		$ppnF = true;
	}

	// Check if this is a credit card transaction
	$query  = "SELECT REG_NBR, RTL_PRC FROM RTL.CSH_REG WHERE CSH_FLO_TYP='SU' AND TRSC_NBR='" . $transactionNumber . "'";
	$result = mysql_query($query, $rtl);
	$row    = mysql_fetch_array($result);

	if (!empty($row['REG_NBR'])) {
		$taxF = true;
		$surF = true;
		$surchargeVal = $row['RTL_PRC'];
	}
}

switch ($action) {

    case "DSCM": // Member Discount
        $member = $_GET['VALUE'];
		
		// Check if Member feature activated
		/*
        if (getDbParam('CSH_MBR') != 1) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Fitur Member dinonaktifkan. Terima Kasih.';
                </script>";
            break;
        }
        */
		
		// Be sure Member ID is not null
        if (empty($member)) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='ID Member tidak boleh kosong. Terima Kasih.';
                </script>";
            break;
        }
        
		// Be sure Transaction number is not null
		if (empty($transactionNumber)) {
			echo "
				<script type='text/javascript'>
					parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
					parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Tidak terdapat transaksi yang sedang aktif. Terima Kasih.';
				</script>";
			break;
		}
        
        $query   = "SELECT MBR_NBR FROM PEOPLE WHERE MBR_NBR='" . $member . "'";
        $result  = mysql_query($query, $rtl);
        
		// Be sure Member ID is registered
        if (mysql_num_rows($result) == 0) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='ID Member yang anda masukkan belum terdaftar pada sistem. Silakan melakukan pendaftaran terlebih dahulu untuk ID <b>".$member."</b>.<br/>Terima Kasih.';
                </script>";
            break;
        }

        $row     = mysql_fetch_array($result);
        $memberNumber  = $row['MBR_NBR'];
        
        /**
         * New Procedure adapted. The reason is :
         * - Be sure Member ID added at all items in this transaction
         */
        $query  = "UPDATE RTL.CSH_REG
                    SET MBR_ID='" . $memberNumber . "'
                    WHERE TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $rtl);

        $query  = "UPDATE RTL.CSH_REG_TODAY
                    SET MBR_ID='" . $memberNumber . "'
                    WHERE TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $rtl);

        $query  = "UPDATE CSH.CSH_REG
                    SET MBR_ID='" . $memberNumber . "'
                    WHERE TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $csh);
            
        /**
         * New Procedure adapted. The reason is :
         * - If there has Cash Flow Type which is not "RT", old query just change the lastest record. So what we need is just extend the query
         */
        $query  = "UPDATE RTL.CSH_REG REG
                    SET REG.DISC_PCT=(
                        SELECT DSC.CAT_DISC_PCT
                        FROM RTL.INVENTORY INV
                            INNER JOIN RTL.CAT_DISC DSC ON INV.MBR_DISC_NBR=DSC.CAT_DISC_NBR
                        WHERE REG.RTL_BRC=INV.INV_BCD AND INV.DEL_NBR=0
                    ),
                    REG.DISC_AMT=(
                        SELECT DSC.CAT_DISC_AMT
                        FROM RTL.INVENTORY INV
                            INNER JOIN RTL.CAT_DISC DSC ON INV.MBR_DISC_NBR=DSC.CAT_DISC_NBR
                        WHERE REG.RTL_BRC=INV.INV_BCD AND INV.DEL_NBR=0
                    ),
                    REG.CAT_DISC_NBR=(
                        SELECT INV.MBR_DISC_NBR
                        FROM RTL.INVENTORY INV
                        WHERE REG.RTL_BRC=INV.INV_BCD AND INV.DEL_NBR=0
                    )
                    WHERE REG.TRSC_NBR='" . $transactionNumber . "' AND REG.CAT_DISC_NBR < (SELECT INV.MBR_DISC_NBR FROM RTL.INVENTORY INV WHERE REG.RTL_BRC = INV.INV_BCD AND INV.DEL_NBR =0) AND REG.CSH_FLO_TYP='RT' AND REG.RTL_BRC <> ''";
        $result = mysql_query($query, $rtl);

        $query  = "UPDATE RTL.CSH_REG_TODAY REG
                    SET REG.DISC_PCT=(
                        SELECT DSC.CAT_DISC_PCT
                        FROM RTL.INVENTORY INV
                            INNER JOIN RTL.CAT_DISC DSC ON INV.MBR_DISC_NBR=DSC.CAT_DISC_NBR
                        WHERE REG.RTL_BRC=INV.INV_BCD AND INV.DEL_NBR=0
                    ),
                    REG.DISC_AMT=(
                        SELECT DSC.CAT_DISC_AMT
                        FROM RTL.INVENTORY INV
                            INNER JOIN RTL.CAT_DISC DSC ON INV.MBR_DISC_NBR=DSC.CAT_DISC_NBR
                        WHERE REG.RTL_BRC=INV.INV_BCD AND INV.DEL_NBR=0
                    ),
                    REG.CAT_DISC_NBR=(
                        SELECT INV.MBR_DISC_NBR
                        FROM RTL.INVENTORY INV
                        WHERE REG.RTL_BRC=INV.INV_BCD AND INV.DEL_NBR=0
                    )
                    WHERE REG.TRSC_NBR='" . $transactionNumber . "' AND REG.CAT_DISC_NBR < (SELECT INV.MBR_DISC_NBR FROM RTL.INVENTORY INV WHERE REG.RTL_BRC = INV.INV_BCD AND INV.DEL_NBR =0) AND REG.CSH_FLO_TYP='RT' AND REG.RTL_BRC <> ''";
        $result = mysql_query($query, $rtl);
            
        // Let's update discount at current POS
        $query  = "SELECT REG_NBR, DISC_PCT, DISC_AMT, CAT_DISC_NBR FROM RTL.CSH_REG WHERE TRSC_NBR='" . $transactionNumber . "' AND MBR_ID='" . $memberNumber . "'";
        $result = mysql_query($query, $rtl);

        while ($row = mysql_fetch_array($result)) {
            $queryCsh  = "UPDATE CSH.CSH_REG SET DISC_PCT=" . $row['DISC_PCT'] . ",
                    DISC_AMT=" . $row['DISC_AMT'] . ",
                    CAT_DISC_NBR=" . $row['CAT_DISC_NBR'] . ",
                    MBR_ID='" . $memberNumber . "'
                    WHERE TRSC_NBR='" . $transactionNumber . "' AND REG_NBR=" . $row['REG_NBR'];
            mysql_query($queryCsh, $csh);
        }

        // Adjust discount if doesn't exists
        adjustDiscount($transactionNumber);

        // Adjust surcharge if exists
        if ($surF) {
            //adjustSurcharge($transactionNumber, $transactionNumberPlus, $QNumber);
			adjustSurcharge($transactionNumber, $transactionNumberPlus, $QNumber, $surchargeVal);
        }

        // Adjust tax if exists
        if ($ppnF) {
            adjustPPN($transactionNumber, $transactionNumberPlus, $QNumber);
        }
        break;
    case "ADD": // Add an item
        // Get barcode
        $barcode = $_GET['VALUE'];
		
        // Be sure Barcode is not null
        if (empty($barcode)) {
			echo "<script type='text/javascript'>
				parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Barcode tidak boleh kosong. Terima Kasih.';
			</script>";

            break;
        }

        // Get item details
        $query = "SELECT 
					INV.INV_NBR, 
					INV.CAT_NBR, 
					CAT.CAT_DESC, 
					INV.CAT_SUB_NBR, 
					SUB.CAT_SUB_DESC,
					#INV.NAME, 
					(CASE 
							WHEN CONCAT
								(INV.NAME,' ',
								CASE WHEN COLR_DESC IS NULL OR COLR_DESC = '' THEN '' ELSE COLR_DESC END,' ',
								CASE WHEN THIC IS NULL OR THIC = '' THEN '' ELSE THIC END,' ',
								CASE WHEN SIZE IS NULL OR SIZE = '' THEN '' ELSE SIZE END,' ',
								CASE WHEN WEIGHT IS NULL OR WEIGHT = '' THEN '' ELSE WEIGHT END) 
							IS NULL THEN INV.NAME 
							ELSE CONCAT
								(TRIM(INV.NAME),' ',
								CASE WHEN COLR_DESC IS NULL OR COLR_DESC = '' THEN '' ELSE TRIM(COLR_DESC) END,' ',
								CASE WHEN THIC IS NULL OR THIC = '' THEN '' ELSE TRIM(THIC) END,' ',
								CASE WHEN SIZE IS NULL OR SIZE = '' THEN '' ELSE TRIM(SIZE) END,' ',
								CASE WHEN WEIGHT IS NULL OR WEIGHT = '' THEN '' ELSE TRIM(WEIGHT) END)
						END) AS NAME,
					INV.INV_PRC, 
					INV.PRC, 
					SPL.TAX_F, 
					DSC.CAT_DISC_AMT, 
					DSC.CAT_DISC_PCT, 
					INV.CAT_DISC_NBR,
					COALESCE(COALESCE(DSC.CAT_DISC_PCT / 100 * PRC, 
					DSC.CAT_DISC_AMT), 0) AS DISC
                FROM RTL.INVENTORY INV  
                    LEFT JOIN RTL.CAT CAT ON INV.CAT_NBR=CAT.CAT_NBR 
                    LEFT JOIN RTL.CAT_SUB SUB ON INV.CAT_SUB_NBR=SUB.CAT_SUB_NBR 
                    LEFT JOIN RTL.CAT_DISC DSC ON INV.CAT_DISC_NBR=DSC.CAT_DISC_NBR 
                    LEFT JOIN CMP.COMPANY SPL ON INV.CO_NBR=SPL.CO_NBR
					LEFT JOIN CMP.INV_COLR CLR ON INV.COLR_NBR=CLR.COLR_NBR 
                WHERE INV.DEL_NBR=0 AND INV.INV_BCD='" . $barcode . "'";
		
		$result = mysql_query($query, $rtl);
		
        if (mysql_num_rows($result) == 0) {
            echo "<script type='text/javascript'>
                parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Barcode yang diinputkan tidak terdaftar. Terima Kasih.';
            </script>";
            break;
        }

        $row    = mysql_fetch_array($result);
		
        // Take Care of default value
        $inventoryNumber       = $row['INV_NBR'];
        $categoryDiscAmount    = "NULL";
        $registerNumber        = "NULL";
        $transactionNumberPlus = "NULL";
        $registerNumberPlus    = "NULL";
        $categoryDiscPct       = 0;
        // $disc                  = $row['DISC'];
        $disc                  = 0;
        $price                 = $row['PRC'];
        $name                  = $row['NAME'];
        $netPrice              = $price - $disc;
        $taxF                  = (bool) $row['TAX_F'];
        $category              = ($row['CAT_SUB_DESC']) ? $row['CAT_SUB_DESC'] : "NULL";
        $categorySub           = ($row['CAT_SUB_NBR']) ? $row['CAT_SUB_NBR'] : 0;
        $categoryDiscNumber    = 0;
        $memberNumber          = 0;

        if ($row['CAT_DISC_NBR'] > 0) {
            $categoryDiscNumber = $row['CAT_DISC_NBR'];
        }

        if (!empty($row['CAT_DISC_PCT'])) {
            $categoryDiscPct = $row['CAT_DISC_PCT'];
        } elseif (!empty($row['CAT_DISC_AMT'])) {
            $categoryDiscAmount = $row['CAT_DISC_AMT'];
        }

        /**
         * Be sure Transaction number is not null
         * This procedure is really weak, because there have some miliseconds before create a new records
		 */
        if (empty($transactionNumber)) {
			
			$transactionServer 	= 0;
			$transactionLocal 	= 1;
			
			$start = microtime(true);
			$limit = 0.1;  // Seconds

			while($transactionServer != $transactionLocal) {
				if (microtime(true) - $start >= $limit) {
				
				$query_cek			= "SELECT TRSC_NBR FROM RTL.CSH_REG_TOKEN";
				$result_cek			= mysql_query($query_cek, $rtl);
				$row_cek			= mysql_fetch_array($result_cek);
				$transactionServer	= $row_cek['TRSC_NBR'];
				
				$transactionLocal 	= $transactionServer+1;
				
				$query 				= "UPDATE RTL.CSH_REG_TOKEN SET TRSC_NBR = TRSC_NBR+1";
				$result				= mysql_query($query, $rtl);			
				
				$query_cek			= "SELECT TRSC_NBR FROM RTL.CSH_REG_TOKEN";
				$result_cek			= mysql_query($query_cek, $rtl);
				$row_cek			= mysql_fetch_array($result_cek);
				$transactionServer	= $row_cek['TRSC_NBR'];
				
				}
			}
			
			$transactionNumber = $transactionServer;
        }
        
		if (empty($QNumber)) {
			$query 	= "SELECT Q_NBR, DATE(UPD_LAST) AS UPD_LAST FROM CSH.CSH_REG_TOKEN";
			$result	= mysql_query($query, $csh);
			$row	= mysql_fetch_array($result);
			
			if(date("Y-m-d") != $row['UPD_LAST'])
			{	$query = "UPDATE CSH.CSH_REG_TOKEN SET Q_NBR = 1, UPD_LAST = '".date('Y-m-d h:i:s')."' ";
				$result	= mysql_query($query, $csh);
				
				$query_cek			= "SELECT Q_NBR FROM CSH.CSH_REG_TOKEN";
				$result_cek			= mysql_query($query_cek, $csh);
				$row_cek			= mysql_fetch_array($result_cek);
				$QNumber			= $row_cek['Q_NBR'];
			} else 
			{
				$query = "UPDATE CSH.CSH_REG_TOKEN SET Q_NBR = (Q_NBR+1), UPD_LAST = '".date('Y-m-d h:i:s')."' ";
				$result	= mysql_query($query, $csh);
				
			
				$query_cek			= "SELECT Q_NBR FROM CSH.CSH_REG_TOKEN";
				$result_cek			= mysql_query($query_cek, $csh);
				$row_cek			= mysql_fetch_array($result_cek);
				$QNumber			= $row_cek['Q_NBR'];
			}
		}

        $query  = "INSERT INTO RTL.CSH_REG(TRSC_NBR, Q_NBR, CO_NBR, RTL_BRC, RTL_Q, RTL_PRC, DISC_AMT, DISC_PCT, TND_AMT, CSH_FLO_TYP, CRT_NBR, INV_NBR, POS_ID)
                        VALUES ('" . $transactionNumber . "', " . $QNumber . ", " . $CoNbrDef . ", '" . $barcode . "', 1, " . $price . ", " . $categoryDiscAmount . ", " . $categoryDiscPct . ", " . $netPrice . ", 'RT', " . $personNumber . ", " . $inventoryNumber . ", " . $POSID . ")";
        $result = mysql_query($query, $rtl);
        $registerNumber = mysql_insert_id($rtl);

        $query  = "INSERT INTO RTL.CSH_REG_TODAY(REG_NBR, TRSC_NBR, Q_NBR, CO_NBR, RTL_BRC, RTL_Q, RTL_PRC, DISC_AMT, DISC_PCT, TND_AMT, CSH_FLO_TYP, CRT_NBR, INV_NBR, POS_ID)
                        VALUES ('" . $registerNumber . "', '" . $transactionNumber . "', " . $QNumber . ", " . $CoNbrDef . ", '" . $barcode . "', 1, " . $price . ", " . $categoryDiscAmount . ", " . $categoryDiscPct . ", " . $netPrice . ", 'RT', " . $personNumber . ", " . $inventoryNumber . ", " . $POSID . ")";
        $result = mysql_query($query, $rtl);	


        $query  = "INSERT INTO CSH.CSH_REG(REG_NBR, TRSC_NBR, Q_NBR, CO_NBR, RTL_BRC, RTL_Q, CAT_DESC, CAT_SUB_DESC, RTL_PRC, NAME_DESC, DISC_AMT, DISC_PCT, TND_AMT, CSH_FLO_TYP, ACT_F, CSH_FLO_PART, CRT_NBR)
                VALUES ('" . $registerNumber . "', '" . $transactionNumber . "', " . $QNumber . ", " . $CoNbrDef . ", '" . $barcode . "', 1, '" . mysql_escape_string($category) . "', '" . $categorySub . "', " . $price . ", '" . mysql_escape_string($name) . "', " . $categoryDiscAmount . ", " . $categoryDiscPct . ", " . $netPrice . ", 'RT', 1, 'A', " . $personNumber . ")";
		$result = mysql_query($query, $csh);

        // Do the same for the plus mode
        if ($taxF) {
			adjustPlusMode($transactionNumber, $registerNumber, $transactionNumberPlus);
        }

        // Adjust discount if doesn't exists
        adjustDiscount($transactionNumber);

        // Adjust surcharge if exists
        if ($surF) {
            //adjustSurcharge($transactionNumber, $transactionNumberPlus, $QNumber);
			adjustSurcharge($transactionNumber, $transactionNumberPlus, $QNumber, $surchargeVal);
        }

        // Adjust tax if exists
        if ($ppnF) {
            adjustPPN($transactionNumber, $transactionNumberPlus, $QNumber);
        }
        break;
    case "RMV": // Remove Item
        // Get barcode
        $barcode = $_GET['VALUE'];

        // Be sure Transaction number is not null
        if (empty($transactionNumber)) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Tidak terdapat transaksi yang sedang aktif. Terima Kasih.';
                </script>";
            break;
        }

        // Check to see if barcode is empty so we will find the lastest items
        if (empty($barcode)) {
			$query  = "SELECT MAX(REG_NBR) AS REG_NBR FROM RTL.CSH_REG WHERE CSH_FLO_TYP='RT' AND TRSC_NBR='" . $transactionNumber . "'";
		} else {
			$query  = "SELECT MAX(REG_NBR) AS REG_NBR FROM RTL.CSH_REG WHERE CSH_FLO_TYP='RT' AND RTL_BRC='" . $barcode . "' AND TRSC_NBR='" . $transactionNumber . "'";
		}
		
		$result = mysql_query($query, $rtl);
		$row    = mysql_fetch_array($result);
		$registerNumber = $row['REG_NBR'];
		
		#====================================== INSERT TO RTL.CSH_REG_DEL ================================#
		
		$query  = "INSERT INTO RTL.CSH_REG_DEL
                    SELECT *,".$personNumber.",CURRENT_TIMESTAMP FROM RTL.CSH_REG WHERE CSH_FLO_TYP='RT' AND REG_NBR='" . $registerNumber . "' AND TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $rtl);
		
		#====================================== DELETE FROM RTL.CSH_REG ================================#
				
        $query   = "DELETE FROM RTL.CSH_REG WHERE CSH_FLO_TYP='RT' AND REG_NBR='" . $registerNumber . "' AND TRSC_NBR='" . $transactionNumber . "'";
        $result  = mysql_query($query, $rtl);

        $query   = "DELETE FROM RTL.CSH_REG_TODAY WHERE CSH_FLO_TYP='RT' AND REG_NBR='" . $registerNumber . "' AND TRSC_NBR='" . $transactionNumber . "'";
        $result  = mysql_query($query, $rtl);

        $query   = "DELETE FROM CSH.CSH_REG WHERE CSH_FLO_TYP='RT' AND REG_NBR='" . $registerNumber . "' AND TRSC_NBR='" . $transactionNumber . "'";
        $result  = mysql_query($query, $csh);

        // Adjust discount if doesn't exists
        adjustDiscount($transactionNumber);

        // Adjust surcharge if exists
        if ($surF) {
            adjustSurcharge($transactionNumber, $transactionNumberPlus, $QNumber, $surchargeVal);
        }

        // Adjust tax if exists
        if ($ppnF) {
            adjustPPN($transactionNumber, $transactionNumberPlus, $QNumber);
        }
		
        break;
    case "RMVALL": // Remove all items

		if (($security > 1) || ($security == '')) {
			?>
			<script type="text/javascript">
                //                parent.document.getElementById('listing').src = 'check-user.php?POS_ID=<?php echo $POSID; ?>&TRSC_NBR=<?php echo $transactionNumber; ?>&ACTION=RMVSPVALL';
                var iframe = window.top.frames['search'];
                iframe.contentDocument.getElementById('fadeCashier').style.display = 'block';
                iframe.contentDocument.getElementById('popupLogin').style.display = 'block';
                iframe.contentDocument.getElementById('popupLoginContent').src = 'check-user.php?POS_ID=<?php echo $POSID; ?>&TRSC_NBR=<?php echo $transactionNumber; ?>&ACTION=RMVSPVALL';
				//alert(<?php echo $transactionNumber; ?>);
            </script>
			<?php
        }
		else {
        // Be sure Transaction number is not null
        if (empty($transactionNumber)) {
            // Nothing to do
            break;
        }
		 
		#====================================== INSERT TO RTL.CSH_REG_DEL ================================#
		
		$query  = "INSERT INTO RTL.CSH_REG_DEL 
                    SELECT *,".$personNumber.",CURRENT_TIMESTAMP FROM RTL.CSH_REG WHERE TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $rtl);
		
		
		#====================================== DELETE FROM RTL.CSH_REG ================================#
		 
        $query   = "DELETE FROM RTL.CSH_REG WHERE TRSC_NBR='" . $transactionNumber . "'";
        $result  = mysql_query($query, $rtl);

        $query   = "DELETE FROM RTL.CSH_REG_TODAY WHERE TRSC_NBR='" . $transactionNumber . "'";
        $result  = mysql_query($query, $rtl);

        $query   = "DELETE FROM CSH.CSH_REG WHERE TRSC_NBR='" . $transactionNumber . "'";
        $result  = mysql_query($query, $csh);
		}
        break;
    case "RMLT": // Remove last transaction
        /**
         * Check Last Closed Transcation
         * - Strict mode has been applied
         */
        $query  = "SELECT MAX(TRSC_NBR) AS TRSC_NBR FROM RTL.CSH_REG WHERE ACT_F=0 AND DATE(CRT_TS)=CURRENT_DATE AND POS_ID=" . $POSID;
        $result = mysql_query($query, $rtl);
        $row    = mysql_fetch_array($result);
        $transactionNumber = $row['TRSC_NBR'];

        // Be sure Transaction number is not null
        if (!empty($transactionNumber)) {
			// Every record is important. So just change activation flag
			$query  = "UPDATE RTL.CSH_REG SET ACT_F=-1 WHERE TRSC_NBR='" . $transactionNumber . "'";
			$result = mysql_query($query, $rtl);

            $query  = "UPDATE RTL.CSH_REG_TODAY SET ACT_F=-1 WHERE TRSC_NBR='" . $transactionNumber . "'";
            $result = mysql_query($query, $rtl);

			$query  = "UPDATE CSH.CSH_REG SET ACT_F=-1 WHERE TRSC_NBR='" . $transactionNumber . "'";
			$result = mysql_query($query, $csh);
        }
        break;
    case "OCD": // Open Cash Drawer
        echo "<script type='text/javascript'>
            parent.document.getElementById('bottom').src='http://" . $POSIP . "/campus/cashier-bottom.php?OCD=1&TRSC_NBR=" . $transactionNumber . "&DEFCO=" . $CoNbrDef . "&PRSN_NBR=" . $personNumber . "&CSH=" . $userID . "&Q_NBR=".$QNumber."&POS_ID=".$POSID."';
        </script>";
        break;
    case "CPN": // Print copy cash receipt
        $QNumber = $_GET['VALUE'];
        
        // Be sure Transaction number is not null
        if (empty($QNumber)) {
			$query  = "SELECT MAX(TRSC_NBR) AS TRSC_NBR FROM RTL.CSH_REG WHERE ACT_F=0 AND POS_ID=" . $POSID;
            $result = mysql_query($query, $rtl);
            $row    = mysql_fetch_array($result);
            $transactionNumber = $row['TRSC_NBR'];
        }
		else {
			$query 	= "SELECT TRSC_NBR FROM CSH.CSH_REG WHERE Q_NBR = " .$QNumber." AND DATE(CRT_TS) = CURRENT_DATE";
			$result	= mysql_query($query, $csh);
			$row	= mysql_fetch_array($result);
			$transactionNumber	= $row['TRSC_NBR'];
		}
		
        echo "<script type='text/javascript'>
            parent.document.getElementById('bottom').src='http://" . $POSIP . "/campus/cashier-bottom.php?COPY=1&TRSC_NBR=" . $transactionNumber . "&DEFCO=" . $CoNbrDef . "&PRSN_NBR=" . $personNumber . "&CSH=" . $userID . "&Q_NBR=".$QNumber."&POS_ID=".$POSID."';
			</script>";
        break;
    case "CHL": // Create hold transaction
        // Be sure Transaction number is not null
		
        if (empty($QNumber)) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Tidak terdapat transaksi yang sedang aktif. Terima Kasih.';
                </script>";
            break;
        }
		
		$query 	= "SELECT TRSC_NBR FROM CSH.CSH_REG WHERE Q_NBR = " .$QNumber." AND DATE(CRT_TS) = CURRENT_DATE";
		$result	= mysql_query($query, $csh);
		$row	= mysql_fetch_array($result);
		$transactionNumber	= $row['TRSC_NBR'];

        $query  = "UPDATE RTL.CSH_REG SET ACT_F=2 WHERE TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $rtl);

        $query  = "UPDATE RTL.CSH_REG_TODAY SET ACT_F=2 WHERE TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $rtl);

        $query  = "UPDATE CSH.CSH_REG SET ACT_F=2 WHERE TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $csh);

        echo "<script type='text/javascript'>
            parent.document.getElementById('bottom').src='http://" . $POSIP . "/campus/cashier-bottom.php?HOLD=1&TRSC_NBR=" . $transactionNumber . "&DEFCO=" . $CoNbrDef . "&PRSN_NBR=" . $personNumber . "&CSH=" . $userID . "&Q_NBR=".$QNumber."&POS_ID=".$POSID."';
        </script>";
        break;
    case "OHL": // Open holded transaction
        $QNumber = $_GET['VALUE'];

        // Be sure Transaction number is not null
        if (empty($QNumber)) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Nomor transaksi tidak boleh kosong. Terima Kasih.';
                </script>";
            break;
        }
        
		$query 	= "SELECT TRSC_NBR FROM CSH.CSH_REG WHERE Q_NBR = " .$QNumber." AND DATE(CRT_TS) = CURRENT_DATE";
		$result	= mysql_query($query, $csh);
		$row	= mysql_fetch_array($result);
		$transactionNumber	= $row['TRSC_NBR'];
		
        // Check to see if the given transaction number is valid.
        $query  = "SELECT POS_ID FROM RTL.CSH_REG WHERE ACT_F=2 AND TRSC_NBR='" . $transactionNumber . "' ORDER BY TRSC_NBR DESC LIMIT 1";
        $result  = mysql_query($query, $rtl);

        if (mysql_num_rows($result) == 0) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Nomor transaksi yang diinputkan tidak terdaftar. Terima Kasih.';
                </script>";
            break;
        }
		
		$row = mysql_fetch_array($result);
		
		/**
         * Be sure holded transaction just can opened from their POS
         * - Strict mode has been applied. Just increase security issue
         */
        if ($row['POS_ID'] != $POSID) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Nomor transaksi yang diinputkan merupakan transaksi dari POS <b>" . $row['POS_ID'] . "</b>, Sehingga perintah tidak dapat dilanjutkan.<br/>Terima Kasih.';
                </script>";
            break;
        }

        /**
         * Check to see if any open transaction.
         * - Strict mode has been applied. So there doesn't contain multiple active transaction
         */
        $query  = "SELECT TRSC_NBR FROM RTL.CSH_REG WHERE ACT_F=1 AND CRT_NBR=" . $personNumber . " AND POS_ID=" . $POSID. " GROUP BY TRSC_NBR";
        $result = mysql_query($query, $rtl);

        /**
         * Be sure we are didn't lost any transaction
         * - Strict mode has been applied. So make another active transaction holded
         */
        while($row = mysql_fetch_array($result)) {
            $query  = "UPDATE RTL.CSH_REG SET ACT_F=2 WHERE TRSC_NBR='" . $row['TRSC_NBR'] . "'";
            mysql_query($query, $rtl);

            $query  = "UPDATE RTL.CSH_REG_TODAY SET ACT_F=2 WHERE TRSC_NBR='" . $row['TRSC_NBR'] . "'";
            mysql_query($query, $rtl);

            $query  = "UPDATE CSH.CSH_REG SET ACT_F=2 WHERE TRSC_NBR='" . $row['TRSC_NBR'] . "'";
            mysql_query($query, $csh);
			
			// Be sure user know if there has a new holded transaction
			echo "<script type='text/javascript'>
				parent.document.getElementById('bottom').src='http://" . $POSIP . "/campus/cashier-bottom.php?HOLD=1&TRSC_NBR=" . $row['TRSC_NBR'] . "&DEFCO=" . $CoNbrDef . "&PRSN_NBR=" . $personNumber . "&CSH=" . $userID . "&Q_NBR=".$QNumber."&POS_ID=".$POSID."';
			</script>";
        }

        // Let's activate the given transaction number
        $query = "UPDATE RTL.CSH_REG SET ACT_F=1, CRT_NBR=" . $personNumber . " WHERE TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $rtl);

        $query = "UPDATE RTL.CSH_REG_TODAY SET ACT_F=1, CRT_NBR=" . $personNumber . " WHERE TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $rtl);

        $query  = "UPDATE CSH.CSH_REG SET ACT_F=1, CRT_NBR=" . $personNumber . " WHERE TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $csh);
        break;
    case "PPN": // Add PPN
        // Be sure Transaction number is not null
        if (empty($transactionNumber)) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Tidak terdapat transaksi yang sedang aktif. Terima Kasih.';
                </script>";
            break;
        }
        
        adjustPPN($transactionNumber, $transactionNumberPlus, $QNumber);
        break;
    case "PRM": // Remove PPN
        // Be sure Transaction number is not null
        if (empty($transactionNumber)) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Tidak terdapat transaksi yang sedang aktif. Terima Kasih.';
                </script>";
            break;
        }
        
		
		#====================================== DELETE FROM RTL.CSH_REG ================================#
		
        // Remove previous PPN calculation
        $query  = "DELETE FROM RTL.CSH_REG WHERE CSH_FLO_TYP='PN' AND TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $rtl);

        $query  = "DELETE FROM RTL.CSH_REG_TODAY WHERE CSH_FLO_TYP='PN' AND TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $rtl);

        $query  = "DELETE FROM CSH.CSH_REG WHERE CSH_FLO_TYP='PN' AND TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $csh);
            
        // Restore original plus items
        $query  = "SELECT REG_NBR
                FROM RTL.CSH_REG REG
                    LEFT JOIN RTL.INVENTORY INV ON REG.RTL_BRC=INV.INV_BCD AND INV.DEL_NBR=0
                    LEFT JOIN CMP.COMPANY COM ON INV.CO_NBR=COM.CO_NBR AND COM.DEL_NBR=0
                WHERE RTL_BRC <> '' AND TAX_F!=1 AND TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $rtl);

        while ($row = mysql_fetch_array($result)) {
            // Process the register number               
            $query   = "UPDATE RTL.CSH_REG SET REG_NBR_PLUS=NULL, TRSC_NBR_PLUS=NULL WHERE REG_NBR='" . $row['REG_NBR'] . "' AND TRSC_NBR='" . $transactionNumber . "'";
            mysql_query($query, $rtl);

            $query   = "UPDATE RTL.CSH_REG_TODAY SET REG_NBR_PLUS=NULL, TRSC_NBR_PLUS=NULL WHERE REG_NBR='" . $row['REG_NBR'] . "' AND TRSC_NBR='" . $transactionNumber . "'";
            mysql_query($query, $rtl);
        }
        break;
    case "MLT": // Multiply quantity of the last item
        //Get multiplier
        $multiplier = $_GET['VALUE'];

        // Be sure Transaction number is not null
        if (empty($transactionNumber)) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Tidak terdapat transaksi yang sedang aktif. Terima Kasih.';
                </script>";
            break;
        }
        
        // Get the last item
        $query  = "SELECT REG_NBR, RTL_Q 
                FROM RTL.CSH_REG
                WHERE RTL_BRC <> '' AND TRSC_NBR='" . $transactionNumber . "' AND REG_NBR=(
                    SELECT COALESCE(MAX(REG_NBR),0) AS REG_NBR FROM RTL.CSH_REG WHERE RTL_BRC <>'' AND TRSC_NBR='" . $transactionNumber . "'
                )";
        $result = mysql_query($query, $rtl);
        $row    = mysql_fetch_array($result);
        $registerNumber = $row['REG_NBR'];

        // Be sure register number is not null
        if (empty($registerNumber)) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Daftar item pada transaksi yang sedang aktif masih kosong. Terima Kasih.';
                </script>";
            break;
        }
        
        if ($row['RTL_Q'] != '') {
            $multiplier = $row['RTL_Q'] * $multiplier;
        }
        
        // Update quantity
        $query  = "UPDATE RTL.CSH_REG SET RTL_Q=" . $multiplier . ",TND_AMT=(RTL_PRC-COALESCE(DISC_AMT,0))*" . $multiplier . " WHERE REG_NBR='" . $registerNumber . "' AND TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $rtl);

        $query  = "UPDATE RTL.CSH_REG_TODAY SET RTL_Q=" . $multiplier . ",TND_AMT=(RTL_PRC-COALESCE(DISC_AMT,0))*" . $multiplier . " WHERE REG_NBR='" . $registerNumber . "' AND TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $rtl);

        $query  = "UPDATE CSH.CSH_REG SET RTL_Q=" . $multiplier . ",TND_AMT=(RTL_PRC-COALESCE(DISC_AMT,0))*" . $multiplier . " WHERE REG_NBR='" . $registerNumber . "' AND TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $csh);
        
        // Adjust discount if doesn't exists
        adjustDiscount($transactionNumber);

        // Adjust surcharge if exists
        if ($surF) {
            adjustSurcharge($transactionNumber, $transactionNumberPlus, $QNumber, $surchargeVal);
        }

        // Adjust tax if exists
        if ($ppnF) {
            adjustPPN($transactionNumber, $transactionNumberPlus, $QNumber);
        }
        break;
    case "MLTQ": // Change quantity of the last item
        // Get quantity
        $quantity = $_GET['VALUE'];

        // Be sure Transaction number is not null
        if (empty($transactionNumber)) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Tidak terdapat transaksi yang sedang aktif. Terima Kasih.';
                </script>";
            break;
        }
        
        // Get the last item
        $query  = "SELECT REG_NBR, RTL_Q 
                FROM RTL.CSH_REG
                WHERE RTL_BRC <> '' AND TRSC_NBR='" . $transactionNumber . "' AND REG_NBR=(
                    SELECT COALESCE(MAX(REG_NBR),0) AS REG_NBR FROM RTL.CSH_REG WHERE RTL_BRC <>'' AND TRSC_NBR='" . $transactionNumber . "'
                )";
        $result = mysql_query($query, $rtl);
        $row    = mysql_fetch_array($result);
        $registerNumber = $row['REG_NBR'];

        // Be sure register number is not null
        if (empty($registerNumber)) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Daftar item pada transaksi yang sedang aktif masih kosong. Terima Kasih.';
                </script>";
            break;
        }
        
        // Update quantity
        $query  = "UPDATE RTL.CSH_REG SET RTL_Q=" . $quantity . ",TND_AMT=(RTL_PRC-COALESCE(DISC_AMT,0))*" . $quantity . " WHERE REG_NBR='" . $registerNumber . "' AND TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $rtl);

        $query  = "UPDATE RTL.CSH_REG_TODAY SET RTL_Q=" . $quantity . ",TND_AMT=(RTL_PRC-COALESCE(DISC_AMT,0))*" . $quantity . " WHERE REG_NBR='" . $registerNumber . "' AND TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $rtl);

        $query  = "UPDATE CSH.CSH_REG SET RTL_Q=" . $quantity . ",TND_AMT=(RTL_PRC-COALESCE(DISC_AMT,0))*" . $quantity . " WHERE REG_NBR='" . $registerNumber . "' AND TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $csh);
        
        // Adjust discount if doesn't exists
        adjustDiscount($transactionNumber);

        // Adjust surcharge if exists
        if ($surF) {
            adjustSurcharge($transactionNumber, $transactionNumberPlus, $QNumber, $surchargeVal);
        }

        // Adjust tax if exists
        if ($ppnF) {
            adjustPPN($transactionNumber, $transactionNumberPlus, $QNumber);
        }
        break;
    case "DSA": // Discount Amount
        // Get discount amount
        $discount = explode("-", $_GET['VALUE']);

        // Be sure Discount is not null
        if (empty($discount)) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Total nilai diskon tidak boleh kosong. Terima Kasih.';
                </script>";
            break;
        }
        
        // Be sure Transaction number is not null
        if (empty($transactionNumber)) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Tidak terdapat transaksi yang sedang aktif. Terima Kasih.';
                </script>";
            break;
        }

        if (count($discount) == 2) {
            // Add discount amount and make Discount Percent empty
            $query  = "UPDATE RTL.CSH_REG SET DISC_AMT=" . $discount[0] . ", DISC_PCT=NULL WHERE TRSC_NBR='" . $transactionNumber . "' AND RTL_BRC='" . $discount[1] . "'";
            $result = mysql_query($query, $rtl);

            $query  = "UPDATE RTL.CSH_REG_TODAY SET DISC_AMT=" . $discount[0] . ", DISC_PCT=NULL WHERE TRSC_NBR='" . $transactionNumber . "' AND RTL_BRC='" . $discount[1] . "'";
            $result = mysql_query($query, $rtl);

            $query  = "UPDATE CSH.CSH_REG SET DISC_AMT=" . $discount[0] . ", DISC_PCT=NULL WHERE TRSC_NBR='" . $transactionNumber . "' AND RTL_BRC='" . $discount[1] . "'";
            $result = mysql_query($query, $csh);
        } else {
            $discount = $discount[0];

            // Add discount amount
            $query          = "INSERT INTO RTL.CSH_REG (TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,RTL_PRC,TND_AMT,POS_ID) VALUES
                            ('" . $transactionNumber . "'," . $QNumber . "," . $personNumber . "," . $CoNbrDef . ",'DS',''," . $discount . "," . $discount . ",'" . $POSID . "')";
            $result         = mysql_query($query, $rtl);
            $registerNumber = mysql_insert_id($rtl);

            $query          = "INSERT INTO RTL.CSH_REG_TODAY (REG_NBR, TRSC_NBR, Q_NBR, CRT_NBR, CO_NBR, CSH_FLO_TYP, RTL_BRC, RTL_PRC, TND_AMT, POS_ID) VALUES
                            (" . $registerNumber . ",'" . $transactionNumber . "'," . $QNumber . "," . $personNumber . "," . $CoNbrDef . ",'DS',''," . $discount . "," . $discount . ",'" . $POSID . "')";
            $result         = mysql_query($query, $rtl);

            $query          = "INSERT INTO CSH.CSH_REG (REG_NBR,TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,RTL_PRC,TND_AMT,CSH_FLO_PART) VALUES
                            (" . $registerNumber . ",'" . $transactionNumber . "'," . $QNumber . "," . $personNumber . "," . $CoNbrDef . ",'DS',''," . $discount . "," . $discount . ",'B')";
            $result         = mysql_query($query, $csh);
        }

        // Adjust discount if doesn't exists
        adjustDiscount($transactionNumber);

        // Adjust surcharge if exists
        if ($surF) {
            adjustSurcharge($transactionNumber, $transactionNumberPlus, $QNumber, $surchargeVal);
        }

        // Adjust tax if exists
        if ($ppnF) {
            adjustPPN($transactionNumber, $transactionNumberPlus, $QNumber);
        }
        break;
	case "DSRD": // Discount Amount
        // Get discount amount
        $discount = explode("-", $_GET['VALUE']);

        // Be sure Discount is not null
        if (empty($discount)) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Total nilai diskon tidak boleh kosong. Terima Kasih.';
                </script>";
            break;
        }
        
        // Be sure Transaction number is not null
        if (empty($transactionNumber)) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Tidak terdapat transaksi yang sedang aktif. Terima Kasih.';
                </script>";
            break;
        }

        if (count($discount) == 2) {
            // Add discount amount and make Discount Percent empty
            $query  = "UPDATE RTL.CSH_REG SET DISC_AMT=-" . $discount[0] . ", DISC_PCT=NULL WHERE TRSC_NBR='" . $transactionNumber . "' AND RTL_BRC='" . $discount[1] . "'";
            $result = mysql_query($query, $rtl);

            $query  = "UPDATE RTL.CSH_REG_TODAY SET DISC_AMT=-" . $discount[0] . ", DISC_PCT=NULL WHERE TRSC_NBR='" . $transactionNumber . "' AND RTL_BRC='" . $discount[1] . "'";
            $result = mysql_query($query, $rtl);

            $query  = "UPDATE CSH.CSH_REG SET DISC_AMT=-" . $discount[0] . ", DISC_PCT=NULL WHERE TRSC_NBR='" . $transactionNumber . "' AND RTL_BRC='" . $discount[1] . "'";
            $result = mysql_query($query, $csh);
        } else {
            $discount = $discount[0];

            // Add discount amount
            $query          = "INSERT INTO RTL.CSH_REG (TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,RTL_PRC,TND_AMT,POS_ID) VALUES
                            ('" . $transactionNumber . "'," . $QNumber . "," . $personNumber . "," . $CoNbrDef . ",'DS','',-" . $discount . ",-" . $discount . ",'" . $POSID . "')";
            $result         = mysql_query($query, $rtl);
            $registerNumber = mysql_insert_id($rtl);

            $query          = "INSERT INTO RTL.CSH_REG_TODAY (REG_NBR, TRSC_NBR, Q_NBR, CRT_NBR, CO_NBR, CSH_FLO_TYP, RTL_BRC, RTL_PRC, TND_AMT, POS_ID) VALUES
                            (" . $registerNumber . ",'" . $transactionNumber . "'," . $QNumber . "," . $personNumber . "," . $CoNbrDef . ",'DS','',-" . $discount . ",-" . $discount . ",'" . $POSID . "')";
            $result         = mysql_query($query, $rtl);
						
            $query          = "INSERT INTO CSH.CSH_REG (REG_NBR,TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,RTL_PRC,TND_AMT,CSH_FLO_PART) VALUES
                            (" . $registerNumber . ",'" . $transactionNumber . "'," . $QNumber . "," . $personNumber . "," . $CoNbrDef . ",'DS','',-" . $discount . ",-" . $discount . ",'B')";
            $result         = mysql_query($query, $csh);
        }

        // Adjust discount if doesn't exists
        adjustDiscount($transactionNumber);

        // Adjust surcharge if exists
        if ($surF) {
            adjustSurcharge($transactionNumber, $transactionNumberPlus, $QNumber, $surchargeVal);
        }

        // Adjust tax if exists
        if ($ppnF) {
            adjustPPN($transactionNumber, $transactionNumberPlus, $QNumber);
        }
        break;
    case "DSP": // Discount Percent
        // Get discount percent
        $discount = explode("-", $_GET['VALUE']);

        // Be sure Discount is not null
        if (empty($discount)) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Total nilai diskon tidak boleh kosong. Terima Kasih.';
                </script>";
            break;
        }
        
        // Be sure Transaction number is not null
        if (empty($transactionNumber)) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Tidak terdapat transaksi yang sedang aktif. Terima Kasih.';
                </script>";
            break;
        }

        if (count($discount) == 2) {
            // Add discount percent and make discount amount empty
            $query  = "UPDATE RTL.CSH_REG SET DISC_PCT=" . $discount[0] . ", DISC_AMT=NULL WHERE TRSC_NBR='" . $transactionNumber . "' AND RTL_BRC='" . $discount[1] . "'";
            $result = mysql_query($query, $rtl);

            $query  = "UPDATE RTL.CSH_REG_TODAY SET DISC_PCT=" . $discount[0] . ", DISC_AMT=NULL WHERE TRSC_NBR='" . $transactionNumber . "' AND RTL_BRC='" . $discount[1] . "'";
            $result = mysql_query($query, $rtl);

            $query  = "UPDATE CSH.CSH_REG SET DISC_PCT=" . $discount[0] . ", DISC_AMT=NULL WHERE TRSC_NBR='" . $transactionNumber . "' AND RTL_BRC='" . $discount[1] . "'";
            $result = mysql_query($query, $csh);
        } else {
            $discount = $discount[0];

            $totalAmount    = round($discount * (getTotalOrderAmount($transactionNumber) / 100));
            $query          = "INSERT INTO RTL.CSH_REG (TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,RTL_PRC,TND_AMT,POS_ID) VALUES
                            ('" . $transactionNumber . "'," . $QNumber . "," . $personNumber . "," . $CoNbrDef . ",'DS',''," . $discount . "," . $totalAmount . ",'" . $POSID . "')";
            $result         = mysql_query($query, $rtl);
            $registerNumber = mysql_insert_id($rtl);

            $query          = "INSERT INTO RTL.CSH_REG_TODAY (REG_NBR, TRSC_NBR, Q_NBR, CRT_NBR, CO_NBR, CSH_FLO_TYP, RTL_BRC, RTL_PRC, TND_AMT, POS_ID) VALUES
                            (" . $registerNumber . ",'" . $transactionNumber . "'," . $QNumber . "," . $personNumber . "," . $CoNbrDef . ",'DS',''," . $discount . "," . $totalAmount . ",'" . $POSID . "')";
            $result         = mysql_query($query, $rtl);

            $query          = "INSERT INTO CSH.CSH_REG (REG_NBR,TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,RTL_PRC,TND_AMT,CSH_FLO_PART) VALUES
                            (" . $registerNumber . ",'" . $transactionNumber . "'," . $QNumber . ", " . $personNumber . "," . $CoNbrDef . ",'DS',''," . $discount . "," . $totalAmount . ",'B')";
            $result         = mysql_query($query, $csh);
        }

        // Adjust discount if doesn't exists
        adjustDiscount($transactionNumber);

        // Adjust surcharge if exists
        if ($surF) {
            adjustSurcharge($transactionNumber, $transactionNumberPlus, $QNumber, $surchargeVal);
        }

        // Adjust tax if exists
        if ($ppnF) {
            adjustPPN($transactionNumber, $transactionNumberPlus, $QNumber);
        }
        break;
	case "DSRP": // Discount Percent
        // Get discount percent
        $discount = explode("-", $_GET['VALUE']);

        // Be sure Discount is not null
        if (empty($discount)) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Total nilai diskon tidak boleh kosong. Terima Kasih.';
                </script>";
            break;
        }
        
        // Be sure Transaction number is not null
        if (empty($transactionNumber)) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Tidak terdapat transaksi yang sedang aktif. Terima Kasih.';
                </script>";
            break;
        }

        if (count($discount) == 2) {
            // Add discount percent and make discount amount empty
            $query  = "UPDATE RTL.CSH_REG SET DISC_PCT=-" . $discount[0] . ", DISC_AMT=NULL WHERE TRSC_NBR='" . $transactionNumber . "' AND RTL_BRC='" . $discount[1] . "'";
            $result = mysql_query($query, $rtl);

            $query  = "UPDATE RTL.CSH_REG_TODAY SET DISC_PCT=-" . $discount[0] . ", DISC_AMT=NULL WHERE TRSC_NBR='" . $transactionNumber . "' AND RTL_BRC='" . $discount[1] . "'";
            $result = mysql_query($query, $rtl);

            $query  = "UPDATE CSH.CSH_REG SET DISC_PCT=-" . $discount[0] . ", DISC_AMT=NULL WHERE TRSC_NBR='" . $transactionNumber . "' AND RTL_BRC='" . $discount[1] . "'";
            $result = mysql_query($query, $csh);
        } else {
            $discount = $discount[0];

            $totalAmount    = -1 * (round($discount * (getTotalOrderAmount($transactionNumber) / 100)));
						
            $query          = "INSERT INTO RTL.CSH_REG (TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,RTL_PRC,TND_AMT,POS_ID) VALUES
                            ('" . $transactionNumber . "'," . $QNumber . "," . $personNumber . "," . $CoNbrDef . ",'DS','','" . $discount . "','" . -($totalAmount) . "','" . $POSID . "')";
            $result         = mysql_query($query, $rtl);
			$registerNumber = mysql_insert_id($rtl);

            $query          = "INSERT INTO RTL.CSH_REG_TODAY (REG_NBR,TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,RTL_PRC,TND_AMT,POS_ID) VALUES
                            (" . $registerNumber . ",'" . $transactionNumber . "'," . $QNumber . "," . $personNumber . "," . $CoNbrDef . ",'DS','','" . $discount . "','" . -($totalAmount) . "','" . $POSID . "')";
            $result         = mysql_query($query, $rtl);
			
            $query          = "INSERT INTO CSH.CSH_REG (REG_NBR,TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,RTL_PRC,TND_AMT,CSH_FLO_PART) VALUES
                            (" . $registerNumber . ",'" . $transactionNumber . "'," . $QNumber . ", " . $personNumber . "," . $CoNbrDef . ",'DS','','" . $discount . "','" . -($totalAmount) . "','B')";
            $result         = mysql_query($query, $csh);
        }

        // Adjust discount if doesn't exists
        adjustDiscount($transactionNumber);

        // Adjust surcharge if exists
        if ($surF) {
            adjustSurcharge($transactionNumber, $transactionNumberPlus, $QNumber, $surchargeVal);
        }

        // Adjust tax if exists
        if ($ppnF) {
            adjustPPN($transactionNumber, $transactionNumberPlus, $QNumber);
        }
        break;
	case "DSZ": // Remove all discount from the current transaction
        // Be sure Transaction number is not null
        if (empty($transactionNumber)) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Tidak terdapat transaksi yang sedang aktif. Terima Kasih.';
                </script>";
            break;
        }

		#====================================== DELETE FROM RTL.CSH_REG ================================#
		
        // Remove all discount
        $query  = "DELETE FROM RTL.CSH_REG WHERE CSH_FLO_TYP='DS' AND TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $rtl);

        $query  = "DELETE FROM RTL.CSH_REG_TODAY WHERE CSH_FLO_TYP='DS' AND TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $rtl);

        $query  = "DELETE FROM CSH.CSH_REG WHERE CSH_FLO_TYP='DS' AND TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $csh);

        /**
         * Remove all discount item
         * Is this necessary?
         */
        $query  = "UPDATE RTL.CSH_REG SET DISC_AMT=NULL, DISC_PCT=NULL WHERE CSH_FLO_TYP='RT' AND TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $rtl);

        $query  = "UPDATE RTL.CSH_REG_TODAY SET DISC_AMT=NULL, DISC_PCT=NULL WHERE CSH_FLO_TYP='RT' AND TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $rtl);

        $query  = "UPDATE CSH.CSH_REG SET DISC_AMT=NULL, DISC_PCT=NULL WHERE CSH_FLO_TYP='RT' AND TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $csh);

        // Adjust surcharge if exists
        if ($surF) {
            adjustSurcharge($transactionNumber, $transactionNumberPlus, $QNumber, $surchargeVal);
        }

        // Adjust tax if exists
        if ($ppnF) {
            adjustPPN($transactionNumber, $transactionNumberPlus, $QNumber);
        }
        break;
    case "PCSH": // Add cash payment
    case "PDEB": // Add Debit Card payment
    case "PCHK": // Add Giro payment
    case "PCRT": // Add Credit Card payment
    case "PTRF": // Add Transfer payment
    case "PVCR": // Add voucher payment
        // Be sure Transaction number is not null
        if (empty($transactionNumber)) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Tidak terdapat transaksi yang sedang aktif. Terima Kasih.';
                </script>";
            break;
        }

        // Get payment amount
        $payment     = $_GET['VALUE'];
        $paymentType = substr($action, -3);
        
        // Get payment description
        $query       = "SELECT PYMT_DESC FROM RTL.PYMT_TYP WHERE PYMT_TYP='" . $paymentType . "'";
        $result      = mysql_query($query, $rtl);
        $row         = mysql_fetch_array($result);
        $paymentDesc = $row['PYMT_DESC'];
        
        // Be sure payment description is not null
        if (empty($paymentDesc)) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Payment type yang anda gunakan tidak terdaftar. Terima Kasih.';
                </script>";
            break;
        }

        // Get total order
        /*
        $query  = "SELECT SUM(CSH_FLO_MULT*TND_AMT) AS TND_AMT
                FROM RTL.CSH_REG REG
                   INNER JOIN RTL.CSH_FLO_TYP TYP ON REG.CSH_FLO_TYP=TYP.CSH_FLO_TYP
                WHERE ((REG.CSH_FLO_TYP IN ('RT') AND RTL_BRC <> '') OR REG.CSH_FLO_TYP IN ('DS','PN','SU','FL','DP','ED','GP')) AND TRSC_NBR=" . $transactionNumber;
        $result = mysql_query($query, $rtl);
        $row    = mysql_fetch_array($result);
        $totalAmount = $row['TND_AMT'];
        */
		
        $totalAmount = getTotalOrderAmount($transactionNumber);

        // Indicates whether the payment type is voucher
        if ($paymentType == "VCR" && $totalAmount == $payment) {
            // Add payment amount
            $query   = "INSERT INTO RTL.CSH_REG (TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,PYMT_TYP,TND_AMT,RTL_PRC,POS_ID) VALUES 
                    ('" . $transactionNumber . "', " . $QNumber . ", " . $personNumber . "," . $CoNbrDef . ",'VC','','" . $paymentType . "'," . $payment . "," . $payment . ",'" . $POSID . "')";
            $result  = mysql_query($query, $rtl);
            $registerNumber  = mysql_insert_id($rtl);

            $query   = "INSERT INTO RTL.CSH_REG_TODAY (REG_NBR,TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,PYMT_TYP,TND_AMT,RTL_PRC,POS_ID) VALUES 
                    (" . $registerNumber . ",'" . $transactionNumber . "', " . $QNumber . ", " . $personNumber . "," . $CoNbrDef . ",'VC','','" . $paymentType . "'," . $payment . "," . $payment . ",'" . $POSID . "')";
            $result  = mysql_query($query, $rtl);

            $query   = "INSERT INTO CSH.CSH_REG (REG_NBR,TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,PYMT_TYP,PYMT_DESC,TND_AMT,CSH_FLO_PART,RTL_PRC) VALUES 
                    (" . $registerNumber . ",'" . $transactionNumber . "', " . $QNumber . ", " . $personNumber . "," . $CoNbrDef . ",'VC','','" . $paymentType . "','" . $paymentDesc . "'," . $payment . ",'C'," . $payment . ")";
            $result  = mysql_query($query, $csh);
            $payment = 0;
        }

        // Indicates whether the payment type is not cash
        if (!($paymentType != "CSH" && $totalAmount < $payment)) {
            // Add payment amount
            $query  = "INSERT INTO RTL.CSH_REG (TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,PYMT_TYP,TND_AMT,POS_ID) VALUES 
                    ('" . $transactionNumber . "'," . $QNumber . ", " . $personNumber . ", " . $CoNbrDef . ",'PA','','" . $paymentType . "'," . $payment . ",'" . $POSID . "')";
            $result = mysql_query($query, $rtl);
            $registerNumber = mysql_insert_id($rtl);

            $query  = "INSERT INTO RTL.CSH_REG_TODAY (REG_NBR,TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,PYMT_TYP,TND_AMT,POS_ID) VALUES 
                    (" . $registerNumber . ",'" . $transactionNumber . "'," . $QNumber . ", " . $personNumber . ", " . $CoNbrDef . ",'PA','','" . $paymentType . "'," . $payment . ",'" . $POSID . "')";
            $result = mysql_query($query, $rtl);
	
            $query  = "INSERT INTO CSH.CSH_REG (REG_NBR,TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,PYMT_TYP,PYMT_DESC,TND_AMT,CSH_FLO_PART) VALUES
                        (" . $registerNumber . ",'" . $transactionNumber . "'," . $QNumber . ", " . $personNumber . "," . $CoNbrDef . ",'PA','','" . $paymentType . "','" . $paymentDesc . "'," . $payment . ",'C')";
            $result = mysql_query($query, $csh);
        }

        // Indicates whether the payment type is Debit then all plus
        if (($paymentType == "DEB") || ($paymentType == "CRT")) {
			adjustPlusMode($transactionNumber, null, $transactionNumberPlus);
        }
		/*
		echo "<script type='text/javascript'>
            parent.document.getElementById('bottom').src='http://" . $POSIP . "/cashier-bottom.php?TRSC_NBR=" . $transactionNumber . "&DEFCO=" . $CoNbrDef . "&PRSN_NBR=" . $personNumber . "&CSH=" . $userID . "&Q_NBR=".$QNumber."&POS_ID=".$POSID."';
        </script>";
		*/
		
        break;
    case "PREM": // Remove last payment method
        // Check to see if any open transaction
        $query  = "SELECT COUNT(*) AS OTRSC FROM RTL.CSH_REG WHERE ACT_F=1 AND CRT_NBR=" . $personNumber . " AND POS_ID=" . $POSID;
        $result  = mysql_query($query, $rtl);
        $row     = mysql_fetch_array($result);
        
        if ($row['OTRSC'] > 0) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Tidak dapat menghapus pembayaran terakhir karena terdapat transaksi yang sedang aktif.<br/>Terima Kasih.';
                </script>";
            break;
        }

        // Check last closed transaction
        $query  = "SELECT MAX(TRSC_NBR) AS TRSC_NBR FROM RTL.CSH_REG WHERE ACT_F=0 AND CRT_NBR=" . $personNumber . " AND POS_ID=" . $POSID;
        $result = mysql_query($query, $rtl);
        $row    = mysql_fetch_array($result);
        $transactionNumber = $row['TRSC_NBR'];

        if (empty($transactionNumber)) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Tidak terdapat transaksi terakhir dalam daftar. Terima Kasih.';
                </script>";
            break;
        }

		#====================================== DELETE FROM RTL.CSH_REG ================================#
		
        // Remove all payment
        $query  = "DELETE FROM RTL.CSH_REG WHERE CSH_FLO_TYP IN ('PA','CH','TL') AND TRSC_NBR=" . $transactionNumber;
        $result = mysql_query($query, $rtl);
        $query  = "UPDATE RTL.CSH_REG SET ACT_F=1 WHERE TRSC_NBR=" . $transactionNumber;
        $result = mysql_query($query, $rtl);

        $query  = "DELETE FROM RTL.CSH_REG_TODAY WHERE CSH_FLO_TYP IN ('PA','CH','TL') AND TRSC_NBR=" . $transactionNumber;
        $result = mysql_query($query, $rtl);
        $query  = "UPDATE RTL.CSH_REG_TODAY SET ACT_F=1 WHERE TRSC_NBR=" . $transactionNumber;
        $result = mysql_query($query, $rtl);

        $query  = "DELETE FROM CSH.CSH_REG WHERE CSH_FLO_TYP IN ('PA','CH','TL') AND TRSC_NBR=" . $transactionNumber;
        $result = mysql_query($query, $csh);
        $query  = "UPDATE CSH.CSH_REG SET ACT_F=1 WHERE TRSC_NBR=" . $transactionNumber;
        $result = mysql_query($query, $csh);
        break;
    case "RET":
        // Get barcode
        $barcode = $_GET['VALUE'];

        // Get item details
        $query = "SELECT INV.INV_NBR, INV.CAT_SUB_NBR, INV.CAT_SUB_NBR, SUB.CAT_SUB_DESC, INV.NAME, CAT.CAT_DESC, INV.PRC,
                COALESCE(COALESCE(DSC.CAT_DISC_PCT/100*PRC, DSC.CAT_DISC_AMT),0) AS DISC, TAX_F, DSC.CAT_DISC_AMT,
                DSC.CAT_DISC_PCT, INV.CAT_DISC_NBR
                FROM RTL.INVENTORY INV  
                    LEFT JOIN RTL.CAT CAT ON INV.CAT_NBR=CAT.CAT_NBR 
                    LEFT JOIN RTL.CAT_SUB SUB ON INV.CAT_SUB_NBR=SUB.CAT_SUB_NBR 
                    LEFT JOIN RTL.CAT_DISC DSC ON INV.CAT_DISC_NBR=DSC.CAT_DISC_NBR 
                    LEFT JOIN CMP.COMPANY COM ON INV.CO_NBR=COM.CO_NBR AND COM.DEL_NBR=0
                WHERE INV.DEL_NBR=0 AND INV.INV_BCD = '" . $barcode . "'";
        $result = mysql_query($query, $rtl);
		
        if (mysql_num_rows($result) == 0) {
            echo "<script type='text/javascript'>
                parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Barcode yang diinputkan tidak terdaftar. Terima Kasih.';
            </script>";
            break;
        }

        $row    = mysql_fetch_array($result);

        // Take Care of default value
        $inventoryNumber       = $row['INV_NBR'];
        $categoryDiscAmount    = "NULL";
        $registerNumber        = "NULL";
        $transactionNumberPlus = "NULL";
        $registerNumberPlus    = "NULL";
        $categoryDiscPct       = 0;
        // $disc                  = $row['DISC']; I Don't Know why
        $disc                  = 0;
        $price                 = $row['PRC'];
        $name                  = $row['NAME'];
        $netPrice              = -($price - $disc);
        $taxF                  = $row['TAX_F'];
        $category              = ($row['CAT_SUB_DESC']) ? $row['CAT_SUB_DESC'] : "NULL";
        $categorySub           = ($row['CAT_SUB_NBR']) ? $row['CAT_SUB_NBR'] : 0;
        $categoryDiscNumber    = 0;
        $memberNumber          = 0;

        if ($row['CAT_DISC_NBR'] > 0) {
            $categoryDiscNumber = $row['CAT_DISC_NBR'];
        }

        if (!empty($row['CAT_DISC_PCT'])) {
            $categoryDiscPct = $row['CAT_DISC_PCT'];
        } elseif (!empty($row['CAT_DISC_AMT'])) {
            $categoryDiscAmount = $row['CAT_DISC_AMT'];
        }

        // Be sure Transaction number is not null
        if (empty($transactionNumber)) {
				
			$transactionServer 	= 0;
			$transactionLocal 	= 1;
			
			$start = microtime(true);
			$limit = 0.1;  // Seconds

			while($transactionServer != $transactionLocal) {
				if (microtime(true) - $start >= $limit) {
				
				$query_cek			= "SELECT TRSC_NBR FROM RTL.CSH_REG_TOKEN";
				$result_cek			= mysql_query($query_cek, $rtl);
				$row_cek			= mysql_fetch_array($result_cek);
				$transactionServer	= $row_cek['TRSC_NBR'];
				
				$transactionLocal 	= $transactionServer+1;
				
				$query 				= "UPDATE RTL.CSH_REG_TOKEN SET TRSC_NBR = TRSC_NBR+1";
				$result				= mysql_query($query, $rtl);			
				
				$query_cek			= "SELECT TRSC_NBR FROM RTL.CSH_REG_TOKEN";
				$result_cek			= mysql_query($query_cek, $rtl);
				$row_cek			= mysql_fetch_array($result_cek);
				$transactionServer	= $row_cek['TRSC_NBR'];
				
				}
			}
			
			$transactionNumber = $transactionServer;
        }
        
		if (empty($QNumber)) {
			$query 	= "SELECT Q_NBR, DATE(UPD_LAST) AS UPD_LAST FROM CSH.CSH_REG_TOKEN";
			$result	= mysql_query($query, $csh);
			$row	= mysql_fetch_array($result);
			
			if(date("Y-m-d") != $row['UPD_LAST'])
			{	$query = "UPDATE CSH.CSH_REG_TOKEN SET Q_NBR = 1, UPD_LAST = '".date('Y-m-d h:i:s')."' ";
				$result	= mysql_query($query, $csh);
				
				$query_cek			= "SELECT Q_NBR FROM CSH.CSH_REG_TOKEN";
				$result_cek			= mysql_query($query_cek, $csh);
				$row_cek			= mysql_fetch_array($result_cek);
				$QNumber				= $row_cek['Q_NBR'];
			}
			else {
				$query = "UPDATE CSH.CSH_REG_TOKEN SET Q_NBR = (Q_NBR+1), UPD_LAST = '".date('Y-m-d h:i:s')."' ";
				$result	= mysql_query($query, $csh);
				
				$query_cek			= "SELECT Q_NBR FROM CSH.CSH_REG_TOKEN";
				$result_cek			= mysql_query($query_cek, $csh);
				$row_cek			= mysql_fetch_array($result_cek);
				$QNumber			= $row_cek['Q_NBR'];
			}
		}
				
        //Add item
        $query  = "INSERT INTO RTL.CSH_REG(TRSC_NBR, Q_NBR, CO_NBR, RTL_BRC, RTL_Q, RTL_PRC, DISC_AMT, DISC_PCT, TND_AMT, CSH_FLO_TYP, CRT_NBR, INV_NBR, POS_ID)
                        VALUES ('" . $transactionNumber . "', " . $QNumber . ", " . $CoNbrDef . ", '" . $barcode . "', -1, " . $price . ", " . $categoryDiscAmount . ", " . $categoryDiscPct . ", " . $netPrice . ", 'RT', " . $personNumber . ", " . $inventoryNumber . ", " . $POSID . ")";
        $result = mysql_query($query, $rtl);
        $registerNumber = mysql_insert_id($rtl);

        $query  = "INSERT INTO RTL.CSH_REG_TODAY(REG_NBR,TRSC_NBR, Q_NBR, CO_NBR, RTL_BRC, RTL_Q, RTL_PRC, DISC_AMT, DISC_PCT, TND_AMT, CSH_FLO_TYP, CRT_NBR, INV_NBR, POS_ID)
                        VALUES (" . $registerNumber . ",'" . $transactionNumber . "', " . $QNumber . ", " . $CoNbrDef . ", '" . $barcode . "', -1, " . $price . ", " . $categoryDiscAmount . ", " . $categoryDiscPct . ", " . $netPrice . ", 'RT', " . $personNumber . ", " . $inventoryNumber . ", " . $POSID . ")";
        $result = mysql_query($query, $rtl);

        $query  = "INSERT INTO CSH.CSH_REG(REG_NBR, TRSC_NBR, Q_NBR, CO_NBR, RTL_BRC, RTL_Q, CAT_DESC, CAT_SUB_DESC, RTL_PRC, NAME_DESC, DISC_AMT, DISC_PCT, TND_AMT, CSH_FLO_TYP, ACT_F, CSH_FLO_PART, CRT_NBR)
                VALUES (" . $registerNumber . ", '" . $transactionNumber . "', " . $QNumber . ", " . $CoNbrDef . ", '" . $barcode . "', -1, '" . mysql_escape_string($category) . "', '" . $categorySub . "', " . $price . ", '" . mysql_escape_string($name) . "', " . $categoryDiscAmount . ", " . $categoryDiscPct . ", " . $netPrice . ", 'RT', 1, 'A', " . $personNumber . ")";
        $result = mysql_query($query, $csh);

        adjustDiscount($transactionNumber);

        // Adjust surcharge if exists
        if ($surF) {
            adjustSurcharge($transactionNumber, $transactionNumberPlus, $QNumber, $surchargeVal);
        }

        // Adjust tax if exists
        if ($ppnF) {
            adjustPPN($transactionNumber, $transactionNumberPlus, $QNumber);
        }
        break;
    case "BAK":
        $paymentType = "CSH";

        // Be sure Transaction number is not null
        if (empty($transactionNumber)) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Tidak terdapat transaksi yang sedang aktif. Terima Kasih.';
                </script>";
            break;
        }

        // Get payment description
        $query       = "SELECT PYMT_DESC FROM RTL.PYMT_TYP WHERE PYMT_TYP='" . $paymentType . "'";
        $result      = mysql_query($query, $rtl);
        $row         = mysql_fetch_array($result);
        $paymentDesc = $row['PYMT_DESC'];

        // Count total payment
        /*
        $query  = "SELECT SUM(CSH_FLO_MULT*TND_AMT) AS TND_AMT,
					SUM(DISC_AMT) AS DISC_AMT,
					SUM((DISC_PCT/100)*(CSH_FLO_MULT*TND_AMT)) AS DISC_PCT
                FROM RTL.CSH_REG REG
                INNER JOIN RTL.CSH_FLO_TYP TYP ON REG.CSH_FLO_TYP=TYP.CSH_FLO_TYP
                WHERE ((REG.CSH_FLO_TYP='RT' AND RTL_BRC <> '') OR REG.CSH_FLO_TYP='DS') AND TRSC_NBR=" . $transactionNumber;
        $result  = mysql_query($query, $rtl);
        $row     = mysql_fetch_array($result);
        $payment = $row['TND_AMT'] - $row['DISC_AMT'] - $row['DISC_PCT'];
        */
        $payment = getTotalOrderAmount($transactionNumber);

        $query          = "INSERT INTO RTL.CSH_REG (TRSC_NBR,Q_NBR, CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,PYMT_TYP,TND_AMT,POS_ID) VALUES
                        (" . $transactionNumber . ", " . $QNumber . ", " . $personNumber . "," . $CoNbrDef . ",'PA','','" . $paymentType . "'," . $payment . ",'" . $POSID . "')";
        $result         = mysql_query($query, $rtl);
        $registerNumber = mysql_insert_id($rtl);

        $query          = "INSERT INTO RTL.CSH_REG_TODAY (REG_NBR,TRSC_NBR,Q_NBR, CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,PYMT_TYP,TND_AMT,POS_ID) VALUES
                        (" . $registerNumber . "," . $transactionNumber . ", " . $QNumber . ", " . $personNumber . "," . $CoNbrDef . ",'PA','','" . $paymentType . "'," . $payment . ",'" . $POSID . "')";
        $result         = mysql_query($query, $rtl);

        $query  = "INSERT INTO CSH.CSH_REG (REG_NBR,TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,PYMT_TYP,PYMT_DESC,TND_AMT,CSH_FLO_PART) VALUES
                    (" . $registerNumber . "," . $transactionNumber . "," . $QNumber . ", " . $personNumber . "," . $CoNbrDef . ",'PA','','" . $paymentType . "','" . $paymentDesc . "'," . $payment . ",'C')";
        $result = mysql_query($query, $csh);
        break;
    case "SUR": // Add surcharge calculation
		 $surchargeVal = $_GET['VALUE'];
        // Be sure Transaction number is not null
        if (empty($transactionNumber)) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Tidak terdapat transaksi yang sedang aktif. Terima Kasih.';
                </script>";
            break;
        }
		if($surchargeVal == ''){
			echo "<script>";
            echo "var iframe = window.top.frames['search'];";
            echo "iframe.contentDocument.getElementById('fadeCashier').style.display='block';";
            echo "iframe.contentDocument.getElementById('popupLogin').style.display='block';";
            echo "iframe.contentDocument.getElementById('popupLoginContent').src='check-surcharge.php?POS_ID=".$POSID."&TRSC_NBR=".$transactionNumber."&ACTION=SUR'";
            echo "</script>";
            break;
		}else{
			adjustSurcharge($transactionNumber, $transactionNumberPlus, $QNumber, $surchargeVal);

			// Adjust tax if exists
			if ($ppnF) {
				adjustPPN($transactionNumber, $transactionNumberPlus, $QNumber);
			}

			echo "<script>";
            echo "var iframeSearch = window.top.frames['search'];";
            echo "iframeSearch.contentDocument.getElementById('fadeCashier').style.display = 'none';";
            echo "iframeSearch.contentDocument.getElementById('popupLogin').style.display = 'none';";
			echo "iframeSearch.contentDocument.getElementById('livesearch').focus();";
            echo "var iframeListing = window.top.frames['listing'];";
            echo "iframeListing.src='cashier-listing.php?POS_ID=".$POSID."&ACTION=ADD'";
            echo "</script>";
		}
        break;
    case "RMC": // Remove surcharge calculation
        // Be sure Transaction number is not null
        if (empty($transactionNumber)) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Tidak terdapat transaksi yang sedang aktif. Terima Kasih.';
                </script>";
            break;
        }
		
		#====================================== DELETE FROM RTL.CSH_REG ================================#

        // Remove previous surcharge calculation
        $query  = "DELETE FROM RTL.CSH_REG WHERE CSH_FLO_TYP='SU' AND TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $rtl);

        $query  = "DELETE FROM RTL.CSH_REG_TODAY WHERE CSH_FLO_TYP='SU' AND TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $rtl);

        $query  = "DELETE FROM CSH.CSH_REG WHERE CSH_FLO_TYP='SU' AND TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $csh);

        // Restore original plus items
        $query  = "SELECT REG_NBR
                FROM RTL.CSH_REG REG
                LEFT JOIN RTL.INVENTORY INV ON REG.RTL_BRC=INV.INV_BCD AND INV.DEL_NBR=0
                LEFT JOIN CMP.COMPANY COM ON INV.CO_NBR=COM.CO_NBR AND COM.DEL_NBR=0
                WHERE RTL_BRC <> '' AND TAX_F!=1 AND TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $rtl);

        while ($row = mysql_fetch_array($result)) {
            // Process the register number
            $query   = "UPDATE RTL.CSH_REG SET REG_NBR_PLUS=NULL, TRSC_NBR_PLUS=NULL WHERE REG_NBR=" . $row['REG_NBR'] . " AND TRSC_NBR=" . $transactionNumber;
            mysql_query($query, $rtl);

            $query   = "UPDATE RTL.CSH_REG_TODAY SET REG_NBR_PLUS=NULL, TRSC_NBR_PLUS=NULL WHERE REG_NBR=" . $row['REG_NBR'] . " AND TRSC_NBR=" . $transactionNumber;
            mysql_query($query, $rtl);
        }
        break;
    case "DAFTAR": // Register new member
        $member = $_GET['VALUE'];

        if (empty($member)) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='ID Member tidak boleh kosong. Terima Kasih.';
                </script>";
            break;
        }
        
        $query  = "SELECT * FROM PEOPLE WHERE MBR_NBR='" . $member . "'";
        $result = mysql_query($query, $rtl);
        $row    = mysql_fetch_array($result);

        if (mysql_num_rows($result) == 1) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='ID Member <b>" . $member . "</b> telah terdaftar. Silahkan melakukan penginputan kembali. Terima Kasih.';
                </script>";
        } else {
            $query   = "SELECT COALESCE(MAX(PRSN_NBR), 0) + 1 AS NEW_NBR FROM PEOPLE";
            $result  = mysql_query($query, $rtl);
            $row     = mysql_fetch_array($result);
            $number = $row['NEW_NBR'];
            $memberName = "MEMBER" . $number;
            
            $query="INSERT INTO PEOPLE (PRSN_NBR, PRSN_ID, NAME, MBR_NBR, CAP_PNT_SUM, UPD_NBR) VALUES
                (" . $number . ",'" . $memberName . "','" . $memberName . "','" . $member . "', 0, '" . $personNumber . "')";
            $result = mysql_query($query, $rtl);

            if ($result) {
                echo "<script type='text/javascript'>
                        parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                        parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='ID Member <b>" . $member . "</b> berhasil terdaftar. Silahkan melanjutkan transaksi.<br/>Terima Kasih.';
                </script>";
                
            } else {
                echo "<script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='ID Member <b>" . $member . "</b> gagal didaftarkan. Silahkan mengulangi penginputan kembali.<br/>Terima Kasih.';
                </script>";
            }
        }
        break;
	case "RMVSPV": // Register new member
		        // Get barcode
        $barcode 		= $_GET['BCD_NBR'];
		$SpvId			= $_GET['SPV_ID'];
		$SpvPassword	= $_GET['SPV_PWD'];
		
		$query  = "SELECT SEC_KEY,PRSN_NBR
            FROM PEOPLE PPL
            INNER JOIN POS_TYP POS ON PPL.POS_TYP=POS.POS_TYP
            WHERE PRSN_ID='" . $SpvId . "' AND (PWD='".$SpvPassword."' OR PWD='".hash('sha512',$SpvPassword)."') AND TERM_DTE IS NULL";
		$result = mysql_query($query, $cmp);
		$row = mysql_fetch_array($result);
		
		$Person			= $row['PRSN_NBR'];
		
		if (mysql_num_rows($result) > 0) {
			
			$holdSecurity= getSecurity($SpvId, "Finance");
		
			if($holdSecurity <= 1){
			
			// Be sure Transaction number is not null
			if (empty($transactionNumber)) {
				echo "
					<script type='text/javascript'>
						parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
						parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Tidak terdapat transaksi yang sedang aktif. Terima Kasih.';
					</script>";
				break;
			}

			// Check to see if barcode is empty so we will find the lastest items
			if (empty($barcode)) {
				$query  = "SELECT MAX(REG_NBR) AS REG_NBR FROM RTL.CSH_REG WHERE CSH_FLO_TYP='RT' AND TRSC_NBR='" . $transactionNumber . "'";
			} else {
				$query  = "SELECT MAX(REG_NBR) AS REG_NBR FROM RTL.CSH_REG WHERE CSH_FLO_TYP='RT' AND RTL_BRC='" . $barcode . "' AND TRSC_NBR='" . $transactionNumber . "'";
			}
			
			$result = mysql_query($query, $rtl);
			$row    = mysql_fetch_array($result);
			$registerNumber = $row['REG_NBR'];
			
			
			#====================================== INSERT TO RTL.CSH_REG_DEL ================================#
			
			$query  = "INSERT INTO RTL.CSH_REG_DEL
						SELECT *,".$Person.", CURRENT_TIMESTAMP FROM RTL.CSH_REG WHERE CSH_FLO_TYP='RT' AND REG_NBR='" . $registerNumber . "' AND TRSC_NBR='" . $transactionNumber . "'";
			$result = mysql_query($query, $rtl);
					
			#====================================== DELETE FROM RTL.CSH_REG ================================#
					
			$query   = "DELETE FROM RTL.CSH_REG WHERE CSH_FLO_TYP='RT' AND REG_NBR='" . $registerNumber . "' AND TRSC_NBR='" . $transactionNumber . "'";
			$result  = mysql_query($query, $rtl);

            $query   = "DELETE FROM RTL.CSH_REG_TODAY WHERE CSH_FLO_TYP='RT' AND REG_NBR='" . $registerNumber . "' AND TRSC_NBR='" . $transactionNumber . "'";
            $result  = mysql_query($query, $rtl);

			$query   = "DELETE FROM CSH.CSH_REG WHERE CSH_FLO_TYP='RT' AND REG_NBR='" . $registerNumber . "' AND TRSC_NBR='" . $transactionNumber . "'";
			$result  = mysql_query($query, $csh);
			
			// Adjust discount if doesn't exists
			adjustDiscount($transactionNumber);

			// Adjust surcharge if exists
			if ($surF) {
				adjustSurcharge($transactionNumber, $transactionNumberPlus, $QNumber, $surchargeVal);
			}

			// Adjust tax if exists
			if ($ppnF) {
				adjustPPN($transactionNumber, $transactionNumberPlus, $QNumber);
			}
			
			}
		}
		?>
        <script type="text/javascript">
            var iframeSearch = window.top.frames['search'];
            iframeSearch.contentDocument.getElementById('fadeCashier').style.display = 'none';
            iframeSearch.contentDocument.getElementById('popupLogin').style.display = 'none';
            
            var iframeListing = window.top.frames['listing'];                        
            iframeListing.src='cashier-listing.php?POS_ID=<?php echo $POSID; ?>&VALUE=&ACTION=ADD';
        </script>
        <?php
        break;
	case "RMVSPVALL": // Register new member
		
		// Get barcode
        $transactionNumber 		= $_GET['TRSC_NBR'];
		$SpvId					= $_GET['SPV_ID'];
		$SpvPassword			= $_GET['SPV_PWD'];
	
		$query  = "SELECT SEC_KEY,PRSN_NBR
            FROM PEOPLE PPL
            INNER JOIN POS_TYP POS ON PPL.POS_TYP=POS.POS_TYP
            WHERE PRSN_ID='" . $SpvId . "' AND (PWD='".$SpvPassword."' OR PWD='".hash('sha512',$SpvPassword)."') AND TERM_DTE IS NULL";
		
		$result = mysql_query($query, $cmp);
		$row = mysql_fetch_array($result);
		
		$Person		= $row['PRSN_NBR'];
		
		if (mysql_num_rows($result) > 0) {
			
			$holdSecurity= getSecurity($SpvId, "Finance");
		
			if($holdSecurity <= 1){

				if (empty($transactionNumber)) {
					// Nothing to do
					break;
				}
				 
				#====================================== INSERT TO RTL.CSH_REG_DEL ================================#
				
				$query  = "INSERT INTO RTL.CSH_REG_DEL
							SELECT *,".$Person.",CURRENT_TIMESTAMP FROM RTL.CSH_REG WHERE TRSC_NBR='" . $transactionNumber . "'";
				$result = mysql_query($query, $rtl);
		
		
				#====================================== DELETE FROM RTL.CSH_REG ================================#
				 
				$query   = "DELETE FROM RTL.CSH_REG WHERE TRSC_NBR='" . $transactionNumber . "'";
				$result  = mysql_query($query, $rtl);

                $query   = "DELETE FROM RTL.CSH_REG_TODAY WHERE TRSC_NBR='" . $transactionNumber . "'";
                $result  = mysql_query($query, $rtl);
				
				echo $query;
				
				$query   = "DELETE FROM CSH.CSH_REG WHERE TRSC_NBR='" . $transactionNumber . "'";
				$result  = mysql_query($query, $csh);
				
			}
		}
		?>
        <script type="text/javascript">
            var iframeSearch = window.top.frames['search'];
            iframeSearch.contentDocument.getElementById('fadeCashier').style.display = 'none';
            iframeSearch.contentDocument.getElementById('popupLogin').style.display = 'none';
            
            var iframeListing = window.top.frames['listing'];                        
            iframeListing.src='cashier-listing.php?POS_ID=<?php echo $POSID; ?>&VALUE=&ACTION=ADD';
        </script>
        <?php
        break;
	case "ORD":
		//Get barcode
		$ordNbr=$_GET['VALUE'];

		$ordNbr	= ltrim($ordNbr,"0");
		
		//Get info
		$query="SELECT SUM(TND_AMT) AS TND_AMT, VAL_NBR FROM CMP.PRN_DIG_ORD_PYMT 
			WHERE (VAL_NBR IS NULL OR VAL_NBR = 0) AND ORD_NBR = ".$ordNbr."
			AND DEL_NBR = 0";
				
		$result=mysql_query($query,$rtl);
		$row=mysql_fetch_array($result);
		$tndAmt=$row['TND_AMT'];
		$valNbr=$row['VAL_NBR'];

		if(($tndAmt!=0)&&(($valNbr==null)||($valNbr==0))){
			$pymtType='FL';$price=$tndAmt;
		}
			
		if (empty($transactionNumber)) {
			
			$transactionServer 	= 0;
			$transactionLocal 	= 1;
			
			$start = microtime(true);
			$limit = 0.1;  // Seconds

			while($transactionServer != $transactionLocal) {
				if (microtime(true) - $start >= $limit) {
				
				$query_cek			= "SELECT TRSC_NBR FROM RTL.CSH_REG_TOKEN";
				$result_cek			= mysql_query($query_cek, $rtl);
				$row_cek			= mysql_fetch_array($result_cek);
				$transactionServer	= $row_cek['TRSC_NBR'];
				
				$transactionLocal 	= $transactionServer+1;
				
				$query 				= "UPDATE RTL.CSH_REG_TOKEN SET TRSC_NBR = TRSC_NBR+1";
				$result				= mysql_query($query, $rtl);			
				
				$query_cek			= "SELECT TRSC_NBR FROM RTL.CSH_REG_TOKEN";
				$result_cek			= mysql_query($query_cek, $rtl);
				$row_cek			= mysql_fetch_array($result_cek);
				$transactionServer	= $row_cek['TRSC_NBR'];
				
				}
			}
			
			$transactionNumber = $transactionServer;
        }
		
		if (empty($QNumber)) {
			$query 	= "SELECT Q_NBR, DATE(UPD_LAST) AS UPD_LAST FROM CSH.CSH_REG_TOKEN";
			$result	= mysql_query($query, $csh);
			$row	= mysql_fetch_array($result);
			
			if(date("Y-m-d") != $row['UPD_LAST'])
			{	$query = "UPDATE CSH.CSH_REG_TOKEN SET Q_NBR = 1, UPD_LAST = '".date('Y-m-d h:i:s')."' ";
				$result	= mysql_query($query, $csh);
				
				$query_cek			= "SELECT Q_NBR FROM CSH.CSH_REG_TOKEN";
				$result_cek			= mysql_query($query_cek, $csh);
				$row_cek			= mysql_fetch_array($result_cek);
				$QNumber				= $row_cek['Q_NBR'];
			}
			else {
				$query = "UPDATE CSH.CSH_REG_TOKEN SET Q_NBR = (Q_NBR+1), UPD_LAST = '".date('Y-m-d h:i:s')."' ";
				$result	= mysql_query($query, $csh);
				
				$query_cek			= "SELECT Q_NBR FROM CSH.CSH_REG_TOKEN";
				$result_cek			= mysql_query($query_cek, $csh);
				$row_cek			= mysql_fetch_array($result_cek);
				$QNumber			= $row_cek['Q_NBR'];
			}
		}
				
		$query  = "INSERT INTO RTL.CSH_REG(TRSC_NBR, Q_NBR, CO_NBR, RTL_BRC, RTL_Q, RTL_PRC, DISC_AMT, DISC_PCT, TND_AMT, CSH_FLO_TYP, CRT_NBR, POS_ID)
                        VALUES ('" . $transactionNumber . "', " . $QNumber . ", " . $CoNbrDef . ", '" . $ordNbr . "', 1, " . $price . ", NULL, NULL, " . $price . ", '".$pymtType."', " . $personNumber . ", " . $POSID . ")";
						echo $query;
        $result = mysql_query($query, $rtl);
        $registerNumber = mysql_insert_id($rtl);

        $query  = "INSERT INTO RTL.CSH_REG_TODAY(REG_NBR,TRSC_NBR, Q_NBR, CO_NBR, RTL_BRC, RTL_Q, RTL_PRC, DISC_AMT, DISC_PCT, TND_AMT, CSH_FLO_TYP, CRT_NBR, POS_ID)
                        VALUES ('" . $registerNumber . "','" . $transactionNumber . "', " . $QNumber . ", " . $CoNbrDef . ", '" . $ordNbr . "', 1, " . $price . ", NULL, NULL, " . $price . ", '".$pymtType."', " . $personNumber . ", " . $POSID . ")";
        $result = mysql_query($query, $rtl);
		
        $query  = "INSERT INTO CSH.CSH_REG(REG_NBR, TRSC_NBR, Q_NBR, CO_NBR, RTL_BRC, RTL_Q, CAT_DESC, CAT_SUB_DESC, RTL_PRC, NAME_DESC, DISC_AMT, DISC_PCT, TND_AMT, CSH_FLO_TYP, ACT_F, CSH_FLO_PART, CRT_NBR)
                VALUES ('" . $registerNumber . "', '" . $transactionNumber . "', " . $QNumber . ", " . $CoNbrDef . ", '" . $ordNbr . "', 1, '" . mysql_escape_string($category) . "', '" . $categorySub . "', " . $price . ", '" . mysql_escape_string($name) . "', NULL, NULL, " . $price . ", '".$pymtType."', 1, 'A', " . $personNumber . ")";
        $result = mysql_query($query, $csh);
		
		//Get info
		$query="SELECT ORD_NBR,TAX_APL_ID FROM CMP.PRN_DIG_ORD_HEAD WHERE DEL_NBR=0 AND ORD_NBR=".$ordNbr;
		$result=mysql_query($query,$rtl);
		$row=mysql_fetch_array($result);
		$taxAplID=$row['TAX_APL_ID'];
		
		//Do the same for the plus mode
		if (($taxAplID=='A')||($taxAplID=='I')){
			adjustPlusMode($transactionNumber, $registerNumber, $transactionNumberPlus);
		}
		
		
		adjustDiscount($transactionNumber,$transactionNumberPlus,$QNumber);
		//Adjust surcharge if exists
		
		if ($surF) {
            adjustSurcharge($transactionNumber, $transactionNumberPlus, $QNumber, $surchargeVal);
        }
		
		//Validate order
		
		$query="UPDATE CMP.PRN_DIG_ORD_PYMT SET VAL_NBR";
		$query.="=".$registerNumber." WHERE VAL_NBR IS NULL AND DEL_NBR=0 AND ORD_NBR=".$ordNbr;
		$result=mysql_query($query,$rtl);
		
		break;
	case "VDO":
		$barcode = $_GET['VALUE'];
		

        // Be sure Transaction number is not null
        if (empty($barcode)) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Tidak terdapat transaksi yang sedang aktif. Terima Kasih.';
                </script>";
            break;
        }

        
		$query  = "SELECT TRSC_NBR, REG_NBR FROM RTL.CSH_REG WHERE CSH_FLO_TYP='FL' AND ACT_F != 0 AND RTL_BRC='" . $barcode . "'";
		$result = mysql_query($query, $rtl);
		$row    = mysql_fetch_array($result);
		$registerNumber 	= $row['REG_NBR'];
		$transactionNumber 	= $row['TRSC_NBR'];
		
		//update to null
        $query="UPDATE CMP.PRN_DIG_ORD_PYMT SET VAL_NBR = NULL WHERE DEL_NBR = 0 AND ORD_NBR = '" . $barcode . "' AND VAL_NBR = '" . $registerNumber . "'";
        $result = mysql_query($query);
		
		#====================================== INSERT TO RTL.CSH_REG_DEL ================================#
		
		$query  = "INSERT INTO RTL.CSH_REG_DEL
                    SELECT *,".$personNumber.",CURRENT_TIMESTAMP FROM RTL.CSH_REG WHERE CSH_FLO_TYP='FL' AND REG_NBR='" . $registerNumber . "' AND TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $rtl);
		
		#====================================== DELETE FROM RTL.CSH_REG ================================#
				
        $query   = "DELETE FROM RTL.CSH_REG WHERE CSH_FLO_TYP='FL' AND REG_NBR='" . $registerNumber . "' AND TRSC_NBR='" . $transactionNumber . "'";
        $result  = mysql_query($query, $rtl);

        $query   = "DELETE FROM RTL.CSH_REG_TODAY WHERE CSH_FLO_TYP='FL' AND REG_NBR='" . $registerNumber . "' AND TRSC_NBR='" . $transactionNumber . "'";
        $result  = mysql_query($query, $rtl);
		
        $query   = "DELETE FROM CSH.CSH_REG WHERE CSH_FLO_TYP='FL' AND REG_NBR='" . $registerNumber . "' AND TRSC_NBR='" . $transactionNumber . "'";
        $result  = mysql_query($query, $csh);
		
        // Adjust discount if doesn't exists
        adjustDiscount($transactionNumber);

        // Adjust surcharge if exists
        if ($surF) {
            adjustSurcharge($transactionNumber, $transactionNumberPlus, $QNumber, $surchargeVal);
        }

        // Adjust tax if exists
        if ($ppnF) {
            adjustPPN($transactionNumber, $transactionNumberPlus, $QNumber);
        }
		
        break;
		
	case "IVC":
		//Get barcode
		$ordNbr=$_GET['VALUE'];
			
			$query       = "SELECT ORD_NBR,PYMT_DOWN,PYMT_REM,VAL_PYMT_DOWN,VAL_PYMT_REM FROM RTL.RTL_STK_HEAD WHERE DEL_F=0 AND IVC_TYP='RC' AND ORD_NBR=" . $ordNbr;

			$result      = mysql_query($query, $rtl);
			$row         = mysql_fetch_array($result);
			
			$pymtDown    = $row['PYMT_DOWN'];
			$pymtRem     = $row['PYMT_REM'];
			$valPymtDown = $row['VAL_PYMT_DOWN'];
			$valPymtRem  = $row['VAL_PYMT_REM'];
			
			//Determine payment type
			if(($pymtDown!=0)&&($valPymtDown==0)&&($pymtRem==0)&&($valPymtRem==0)){
				$pymtType='IV';
				$price=$pymtDown;
			}
			if(($pymtRem!=0)&&($valPymtRem==0)){
				$pymtType='IV';
				$price=$pymtRem;
			}

		if (empty($transactionNumber)) {
			
			$transactionServer 	= 0;
			$transactionLocal 	= 1;
			
			$start = microtime(true);
			$limit = 0.1;  // Seconds

			while($transactionServer != $transactionLocal) {
				if (microtime(true) - $start >= $limit) {
				
				$query_cek			= "SELECT TRSC_NBR FROM RTL.CSH_REG_TOKEN";
				$result_cek			= mysql_query($query_cek, $rtl);
				$row_cek			= mysql_fetch_array($result_cek);
				$transactionServer	= $row_cek['TRSC_NBR'];
				
				$transactionLocal 	= $transactionServer+1;
				
				$query 				= "UPDATE RTL.CSH_REG_TOKEN SET TRSC_NBR = TRSC_NBR+1";
				$result				= mysql_query($query, $rtl);			
				
				$query_cek			= "SELECT TRSC_NBR FROM RTL.CSH_REG_TOKEN";
				$result_cek			= mysql_query($query_cek, $rtl);
				$row_cek			= mysql_fetch_array($result_cek);
				$transactionServer	= $row_cek['TRSC_NBR'];
				
				}
			}
			
			$transactionNumber = $transactionServer;
        }
		
		if (empty($QNumber)) {
			$query 	= "SELECT Q_NBR, DATE(UPD_LAST) AS UPD_LAST FROM CSH.CSH_REG_TOKEN";
			$result	= mysql_query($query, $csh);
			$row	= mysql_fetch_array($result);
			
			if(date("Y-m-d") != $row['UPD_LAST'])
			{	$query = "UPDATE CSH.CSH_REG_TOKEN SET Q_NBR = 1, UPD_LAST = '".date('Y-m-d h:i:s')."' ";
				$result	= mysql_query($query, $csh);

				$query_cek			= "SELECT Q_NBR FROM CSH.CSH_REG_TOKEN";
				$result_cek			= mysql_query($query_cek, $csh);
				$row_cek			= mysql_fetch_array($result_cek);
				$QNumber				= $row_cek['Q_NBR'];
				
			}
			else {
				$query = "UPDATE CSH.CSH_REG_TOKEN SET Q_NBR = (Q_NBR+1), UPD_LAST = '".date('Y-m-d h:i:s')."' ";
				$result	= mysql_query($query, $csh);
				
				$query_cek			= "SELECT Q_NBR FROM CSH.CSH_REG_TOKEN";
				$result_cek			= mysql_query($query_cek, $csh);
				$row_cek			= mysql_fetch_array($result_cek);
				$QNumber			= $row_cek['Q_NBR'];
			
			}
		}
				
		$query  = "INSERT INTO RTL.CSH_REG(TRSC_NBR, Q_NBR, CO_NBR, RTL_BRC, RTL_Q, RTL_PRC, DISC_AMT, DISC_PCT, TND_AMT, CSH_FLO_TYP, CRT_NBR, POS_ID)
                        VALUES ('" . $transactionNumber . "', " . $QNumber . ", " . $CoNbrDef . ", '" . $ordNbr . "', 1, " . $price . ", NULL, NULL, " . $price . ", '".$pymtType."', " . $personNumber . ", " . $POSID . ")";
        $result = mysql_query($query, $rtl);
        $registerNumber = mysql_insert_id($rtl);

        $query  = "INSERT INTO RTL.CSH_REG_TODAY(REG_NBR,TRSC_NBR, Q_NBR, CO_NBR, RTL_BRC, RTL_Q, RTL_PRC, DISC_AMT, DISC_PCT, TND_AMT, CSH_FLO_TYP, CRT_NBR, POS_ID)
                        VALUES ('" . $registerNumber . "','" . $transactionNumber . "', " . $QNumber . ", " . $CoNbrDef . ", '" . $ordNbr . "', 1, " . $price . ", NULL, NULL, " . $price . ", '".$pymtType."', " . $personNumber . ", " . $POSID . ")";
        $result = mysql_query($query, $rtl);

        $query  = "INSERT INTO CSH.CSH_REG(REG_NBR, TRSC_NBR, Q_NBR, CO_NBR, RTL_BRC, RTL_Q, CAT_DESC, CAT_SUB_DESC, RTL_PRC, NAME_DESC, DISC_AMT, DISC_PCT, TND_AMT, CSH_FLO_TYP, ACT_F, CSH_FLO_PART, CRT_NBR)
                VALUES ('" . $registerNumber . "', '" . $transactionNumber . "', " . $QNumber . ", " . $CoNbrDef . ", '" . $ordNbr . "', 1, '', '', " . $price . ", '" . mysql_escape_string($name) . "', NULL, NULL, " . $price . ", '".$pymtType."', 1, 'A', " . $personNumber . ")";
        $result = mysql_query($query, $csh);

		adjustDiscount($transactionNumber,$transactionNumberPlus,$QNumber);
		
		//Adjust surcharge if exists
		if($surF==1){
			adjustSurcharge($transactionNumber, $transactionNumberPlus, $QNumber, $surchargeVal);
		}
		if($ppnF==1){
			adjustPPN($transactionNumber, $transactionNumberPlus, $QNumber);
		}		
		//Validate order
		$query="UPDATE RTL.RTL_STK_HEAD SET VAL_PYMT_";
		if(($pymtType=='IV')&&($pymtDown!=0)&&($valPymtDown==0)&&($pymtRem==0)&&($valPymtRem==0)){
			$query.="DOWN";
		}elseif(($pymtType=='IV')&&($pymtRem!=0)&&($valPymtRem==0)){
			$query.="REM";
		}
		$query.="=".$registerNumber." WHERE ORD_NBR=".$ordNbr;
		$result=mysql_query($query,$rtl);	

		break;
	case "VVC":
		//Get transaction number
		$orderNumber=$_GET['VALUE'];

			//Unvalidate order
			$query="SELECT REG_NBR,TRSC_NBR,CSH_FLO_TYP,RTL_BRC FROM RTL.CSH_REG WHERE CSH_FLO_TYP='IV' AND RTL_BRC=".$orderNumber." AND DATE(CRT_TS) = CURRENT_DATE";
		
			$result				= mysql_query($query,$rtl);
			$row				= mysql_fetch_array($result);
			$pymtType			= $row['CSH_FLO_TYP'];
			$retailBcd			= $row['RTL_BRC'];
			$registerNumber		= $row['REG_NBR'];
			$transactionNumber	= $row['TRSC_NBR'];
			
			$queryHead       = "SELECT ORD_NBR,PYMT_DOWN,PYMT_REM,VAL_PYMT_DOWN,VAL_PYMT_REM FROM RTL.RTL_STK_HEAD WHERE DEL_F=0 AND IVC_TYP='RC' AND ORD_NBR=" . $retailBcd;
			
			$resultHead		= mysql_query($queryHead, $rtl);
			$rowHead     	= mysql_fetch_array($resultHead);
			
			$pymtDown    = $rowHead['PYMT_DOWN'];
			$pymtRem     = $rowHead['PYMT_REM'];
			$valPymtDown = $rowHead['VAL_PYMT_DOWN'];
			$valPymtRem  = $rowHead['VAL_PYMT_REM'];
			
			#====================================== EMPTY VALIDATE PAYMENT ================================#
			
			$query="UPDATE RTL.RTL_STK_HEAD SET VAL_PYMT_";
			if($valPymtDown==$registerNumber){
				$query.="DOWN";
			}elseif($valPymtRem==$registerNumber){
				$query.="REM";
			}
			$query.="=NULL WHERE ORD_NBR=".$retailBcd;
			
			$result=mysql_query($query,$rtl);
			
			#====================================== INSERT TO RTL.CSH_REG_DEL ================================#
		
			$query  = "INSERT INTO RTL.CSH_REG_DEL
						SELECT *,".$personNumber.",CURRENT_TIMESTAMP FROM RTL.CSH_REG WHERE CSH_FLO_TYP='IV' AND REG_NBR='" . $registerNumber . "' AND TRSC_NBR='" . $transactionNumber . "'";
			$result = mysql_query($query, $rtl);

			#====================================== DELETE FROM RTL.CSH_REG ================================#
			$query="DELETE FROM RTL.CSH_REG WHERE TRSC_NBR=".$transactionNumber;
			$result=mysql_query($query,$rtl);

            $query="DELETE FROM RTL.CSH_REG_TODAY WHERE TRSC_NBR=".$transactionNumber;
            $result=mysql_query($query,$rtl);

			$query="DELETE FROM CSH.CSH_REG WHERE TRSC_NBR=".$transactionNumber;
			$result=mysql_query($query,$csh);
			
			adjustDiscount($transactionNumber,$transactionNumberPlus,$QNumber);

			//Adjust surcharge if exists
			if($surF==1){
				adjustSurcharge($transactionNumber, $transactionNumberPlus, $QNumber, $surchargeVal);
			}
			if($ppnF==1){
				adjustPPN($transactionNumber, $transactionNumberPlus, $QNumber);
			}	
		
		break;
	//End of payment receiving
	
	case "SAL":
		//Get barcode
		$orderNumber=$_GET['VALUE'];
		
		//Get info
		$query = "SELECT 
			SUM(TND_AMT) AS TND_AMT, VAL_NBR 
		FROM RTL.RTL_ORD_PYMT 
		WHERE (VAL_NBR IS NULL OR VAL_NBR = 0) AND ORD_NBR = ".$orderNumber." AND DEL_NBR = 0";
		$result	=	mysql_query($query,$rtl);
		$row	= mysql_fetch_array($result);
		$tndAmt	= $row['TND_AMT'];
		$valNbr	= $row['VAL_NBR'];
		
		if(($tndAmt!=0)&&(($valNbr==null)||($valNbr==0))){
			$pymtType	= 'FL';
			$price		= $tndAmt;
		}
	
		if (empty($transactionNumber)) {
			
			$transactionServer 	= 0;
			$transactionLocal 	= 1;
			
			$start = microtime(true);
			$limit = 0.1;  // Seconds

			while($transactionServer != $transactionLocal) {
				if (microtime(true) - $start >= $limit) {
				
				$query_cek			= "SELECT TRSC_NBR FROM RTL.CSH_REG_TOKEN";
				$result_cek			= mysql_query($query_cek, $rtl);
				$row_cek			= mysql_fetch_array($result_cek);
				$transactionServer	= $row_cek['TRSC_NBR'];
				
				$transactionLocal 	= $transactionServer+1;
				
				$query 				= "UPDATE RTL.CSH_REG_TOKEN SET TRSC_NBR = TRSC_NBR+1";
				$result				= mysql_query($query, $rtl);			
				
				$query_cek			= "SELECT TRSC_NBR FROM RTL.CSH_REG_TOKEN";
				$result_cek			= mysql_query($query_cek, $rtl);
				$row_cek			= mysql_fetch_array($result_cek);
				$transactionServer	= $row_cek['TRSC_NBR'];
				
				}
			}
			
			$transactionNumber = $transactionServer;
        	}
		
		if (empty($QNumber)) {
			$query 	= "SELECT Q_NBR, DATE(UPD_LAST) AS UPD_LAST FROM CSH.CSH_REG_TOKEN";
			$result	= mysql_query($query, $csh);
			$row	= mysql_fetch_array($result);
			
			if(date("Y-m-d") != $row['UPD_LAST'])
			{	$query = "UPDATE CSH.CSH_REG_TOKEN SET Q_NBR = 1, UPD_LAST = '".date('Y-m-d h:i:s')."' ";
				$result	= mysql_query($query, $csh);
				
				$query_cek			= "SELECT Q_NBR FROM CSH.CSH_REG_TOKEN";
				$result_cek			= mysql_query($query_cek, $csh);
				$row_cek			= mysql_fetch_array($result_cek);
				$QNumber				= $row_cek['Q_NBR'];
			}
			else {
				$query = "UPDATE CSH.CSH_REG_TOKEN SET Q_NBR = (Q_NBR+1), UPD_LAST = '".date('Y-m-d h:i:s')."' ";
				$result	= mysql_query($query, $csh);
				
				$query_cek			= "SELECT Q_NBR FROM CSH.CSH_REG_TOKEN";
				$result_cek			= mysql_query($query_cek, $csh);
				$row_cek			= mysql_fetch_array($result_cek);
				$QNumber			= $row_cek['Q_NBR'];
			}
		}
		
		$query  = "INSERT INTO RTL.CSH_REG(TRSC_NBR, Q_NBR, CO_NBR, RTL_BRC, RTL_Q, RTL_PRC, DISC_AMT, DISC_PCT, TND_AMT, CSH_FLO_TYP, CRT_NBR, POS_ID)
				VALUES ('" . $transactionNumber . "', " . $QNumber . ", " . $CoNbrDef . ", '" . $orderNumber . "', 1, " . $price . ", NULL, NULL, " . $price . ", '".$pymtType."', " . $personNumber . ", " . $POSID . ")";
		$result = mysql_query($query, $rtl);
		$registerNumber = mysql_insert_id($rtl);

        $query  = "INSERT INTO RTL.CSH_REG_TODAY(REG_NBR,TRSC_NBR, Q_NBR, CO_NBR, RTL_BRC, RTL_Q, RTL_PRC, DISC_AMT, DISC_PCT, TND_AMT, CSH_FLO_TYP, CRT_NBR, POS_ID)
                VALUES ('" . $registerNumber . "', '" . $transactionNumber . "', " . $QNumber . ", " . $CoNbrDef . ", '" . $orderNumber . "', 1, " . $price . ", NULL, NULL, " . $price . ", '".$pymtType."', " . $personNumber . ", " . $POSID . ")";
        $result = mysql_query($query, $rtl);
			
		$query  = "INSERT INTO CSH.CSH_REG(REG_NBR, TRSC_NBR, Q_NBR, CO_NBR, RTL_BRC, RTL_Q, CAT_DESC, CAT_SUB_DESC, RTL_PRC, NAME_DESC, DISC_AMT, DISC_PCT, TND_AMT, CSH_FLO_TYP, ACT_F, CSH_FLO_PART, CRT_NBR)
				VALUES ('" . $registerNumber . "', '" . $transactionNumber . "', " . $QNumber . ", " . $CoNbrDef . ", '" . $orderNumber . "', 1, '" . mysql_escape_string($category) . "', '" . $categorySub . "', " . $price . ", '" . mysql_escape_string($name) . "', NULL, NULL, " . $price . ", '".$pymtType."', 1, 'A', " . $personNumber . ")";
		$result = mysql_query($query, $csh);

		//Get info
		$query="SELECT ORD_NBR,TAX_APL_ID FROM CMP.PRN_DIG_ORD_HEAD WHERE DEL_NBR=0 AND ORD_NBR=".$orderNumber;
		$result=mysql_query($query,$rtl);
		$row=mysql_fetch_array($result);
		$taxAplID=$row['TAX_APL_ID'];
			
		//Do the same for the plus mode
		if (($taxAplID=='A')||($taxAplID=='I')){
			adjustPlusMode($transactionNumber, $registerNumber, $transactionNumberPlus);
		}
			
		adjustDiscount($transactionNumber,$transactionNumberPlus,$QNumber);
		//Adjust surcharge if exists
		
		if ($surF) {
			adjustSurcharge($transactionNumber, $transactionNumberPlus, $QNumber, $surchargeVal);
		}
		
		//Validate order
		$query="UPDATE RTL.RTL_ORD_PYMT SET VAL_NBR";
		$query.="=".$registerNumber." WHERE VAL_NBR IS NULL AND DEL_NBR=0 AND ORD_NBR=".$orderNumber;
		$result=mysql_query($query,$rtl);
		
		break;
		
		
	case "VSL":
		$orderNumber=$_GET['VALUE'];
		
		//Be sure Transaction number is not null
        if (empty($transactionNumber)) {
            echo "
                <script type='text/javascript'>
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                    parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Tidak terdapat transaksi yang sedang aktif. Terima Kasih.';
                </script>";
            break;
        }
	
		//fine invoice number and transaction number
		$query 	= "SELECT REG_NBR, Q_NBR, RTL_BRC FROM RTL.CSH_REG WHERE RTL_BRC='".$orderNumber."' AND ACT_F=1";
		$result = mysql_query($query, $rtl);
		$row 	= mysql_fetch_array($result);
		$registerNumber = $row['REG_NBR'];
		
		#====================================== INSERT TO RTL.CSH_REG_DEL ================================#
		$query  = "INSERT INTO RTL.CSH_REG_DEL
				SELECT *,".$personNumber.",CURRENT_TIMESTAMP FROM RTL.CSH_REG WHERE REG_NBR='" . $registerNumber . "' AND TRSC_NBR='" . $transactionNumber . "'";
		$result = mysql_query($query, $rtl);
		
		#====================================== UPDATE TO RTL.RTL_ORD_PYMT================================#
		$query 	= "UPDATE RTL.RTL_ORD_PYMT SET VAL_NBR = NULL WHERE ORD_NBR = '".$orderNumber."' AND VAL_NBR='" . $registerNumber . "'";
		$result = mysql_query($query , $rtl);
		
		#====================================== DELETE FROM RTL.CSH_REG ==================================#
		//delete transaction from server
		$query 	= "DELETE FROM RTL.CSH_REG WHERE REG_NBR='" . $registerNumber . "' AND TRSC_NBR='" . $transactionNumber . "'";
		$result = mysql_query($query, $rtl);

        $query  = "DELETE FROM RTL.CSH_REG_TODAY WHERE REG_NBR='" . $registerNumber . "' AND TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $rtl);
		
		//delete transaction from local
		$query 	= "DELETE FROM CSH.CSH_REG WHERE REG_NBR='" . $registerNumber . "' AND TRSC_NBR='" . $transactionNumber . "'";
		$result = mysql_query($query, $csh);
		   			
		// Adjust discount if doesn't exists
        adjustDiscount($transactionNumber);

        // Adjust surcharge if exists
        if ($surF) {
            adjustSurcharge($transactionNumber, $transactionNumberPlus, $QNumber, $surchargeVal);
        }

        // Adjust tax if exists
        if ($ppnF) {
            adjustPPN($transactionNumber, $transactionNumberPlus, $QNumber);
        }	
		
		break;
				
}

function getTotalOrderAmount($transactionNumber)
{
    // Keep DRY. Just take from the global variables
    global $rtl;

    // Get total transaction and discount amount
    $query  = "SELECT SUM(COALESCE(CSH_FLO_MULT, 1)*TND_AMT) AS TND_AMT,
                SUM(CASE WHEN REG.CSH_FLO_TYP='RT' THEN COALESCE(DISC_AMT, 0) ELSE 0 END) AS DISC_AMT,
                SUM(CASE WHEN REG.CSH_FLO_TYP='RT' THEN (COALESCE(DISC_PCT, 0)/100)*(COALESCE(CSH_FLO_MULT, 1)*TND_AMT) ELSE 0 END) AS DISC_PCT_AMT
            FROM RTL.CSH_REG REG
                LEFT OUTER JOIN RTL.CSH_FLO_TYP TYP ON REG.CSH_FLO_TYP=TYP.CSH_FLO_TYP
            WHERE ((REG.CSH_FLO_TYP IN ('RT') AND RTL_BRC <> '') OR REG.CSH_FLO_TYP IN ('DS','PN','SU','FL','DP','ED','GP'))
                AND REG.TRSC_NBR='" . $transactionNumber . "'";

    $result = mysql_query($query, $rtl);
    $row    = mysql_fetch_array($result);
    $brutoAmount           = $row['TND_AMT'];
    $discountAmount        = $row['DISC_AMT'];
    $discountPercentAmount = $row['DISC_PCT_AMT'];

    return $brutoAmount - ($discountAmount + $discountPercentAmount);

}

function adjustDiscount($transactionNumber)
{
    // Keep DRY. Just take from the global variables
    global $rtl, $csh;

    // Get total transaction and discount amount
    $query  = "SELECT SUM(COALESCE(CSH_FLO_MULT, 1)*TND_AMT) AS TND_AMT,
                SUM(CASE WHEN REG.CSH_FLO_TYP='RT' THEN COALESCE(DISC_AMT, 0) ELSE 0 END) AS DISC_AMT,
                SUM(CASE WHEN REG.CSH_FLO_TYP='RT' THEN (COALESCE(DISC_PCT, 0)/100)*(COALESCE(CSH_FLO_MULT, 1)*TND_AMT) ELSE 0 END) AS DISC_PCT_AMT
            FROM RTL.CSH_REG REG
                INNER JOIN RTL.CSH_FLO_TYP TYP ON REG.CSH_FLO_TYP=TYP.CSH_FLO_TYP
            WHERE REG.CSH_FLO_TYP NOT IN ('DP', 'FL') AND (
                (REG.CSH_FLO_TYP='RT' AND RTL_BRC <> '') OR (REG.CSH_FLO_TYP='DS' AND REG.RTL_PRC=REG.TND_AMT)
            ) AND (REG.DISC_AMT IS NULL OR REG.DISC_AMT=0) AND REG.TRSC_NBR='" . $transactionNumber . "'";
    $result = mysql_query($query, $rtl);
    $row    = mysql_fetch_array($result);
    $totalAmount = $row['TND_AMT'] - ($row['DISC_AMT'] + $row['DISC_PCT_AMT']);

    // Be sure total amount is positive
	/*
    if ($totalAmount < 0) {
        $totalAmount = 0;
    }
	*/
	
    // Get register number of discount percent if exists
    $query  = "SELECT REG_NBR, RTL_PRC FROM RTL.CSH_REG WHERE CSH_FLO_TYP='DS' AND RTL_PRC != TND_AMT AND TRSC_NBR='" . $transactionNumber . "' ORDER BY REG_NBR";
    $result = mysql_query($query, $rtl);

    while ($row = mysql_fetch_array($result)) {
        // Get discount percentage
        $price  = $row['RTL_PRC'];
        $registerNumber = $row['REG_NBR'];
        $netPrice = round($price * ($totalAmount / 100));

        // Adjust discount amount
        $query   = "UPDATE RTL.CSH_REG SET TND_AMT=" . $netPrice . " WHERE TRSC_NBR='" . $transactionNumber . "' AND REG_NBR=" . $registerNumber;
        mysql_query($query, $rtl);

        $query   = "UPDATE RTL.CSH_REG_TODAY SET TND_AMT=" . $netPrice . " WHERE TRSC_NBR='" . $transactionNumber . "' AND REG_NBR=" . $registerNumber;
        mysql_query($query, $rtl);

        $query   = "UPDATE CSH.CSH_REG SET TND_AMT=" . $netPrice . "  WHERE TRSC_NBR='" . $transactionNumber . "' AND REG_NBR=" . $registerNumber;
        mysql_query($query, $csh);

        $totalAmount -= $netPrice;
    }
}

function adjustPPN($transactionNumber, $transactionNumberPlus = null, $QNumber = null)
{
    // Keep DRY. Just take from the global variables
    global $rtl, $csh, $CoNbrDef, $POSID, $personNumber;


	#====================================== DELETE FROM RTL.CSH_REG ================================#
	
    // Remove previous ppn calculation
    $query  = "DELETE FROM RTL.CSH_REG WHERE CSH_FLO_TYP='PN' AND TRSC_NBR='" . $transactionNumber . "'";
    $result = mysql_query($query, $rtl);

    $query  = "DELETE FROM RTL.CSH_REG_TODAY WHERE CSH_FLO_TYP='PN' AND TRSC_NBR='" . $transactionNumber . "'";
    $result = mysql_query($query, $rtl);

    $query  = "DELETE FROM CSH.CSH_REG WHERE CSH_FLO_TYP='PN' AND TRSC_NBR='" . $transactionNumber . "'";
    $result = mysql_query($query, $csh);
    
    // Get ppn amount
    $ppn = getDbParamGlobal('TAX_PPN');

    // Get total transaction and discount amount
    $query  = "SELECT SUM(COALESCE(CSH_FLO_MULT, 1)*TND_AMT) AS TND_AMT,
                SUM(CASE WHEN REG.CSH_FLO_TYP='RT' THEN COALESCE(DISC_AMT, 0) ELSE 0 END) AS DISC_AMT,
                SUM(CASE WHEN REG.CSH_FLO_TYP='RT' THEN (COALESCE(DISC_PCT, 0)/100)*(COALESCE(CSH_FLO_MULT, 1)*TND_AMT) ELSE 0 END) AS DISC_PCT_AMT
            FROM RTL.CSH_REG REG
                INNER JOIN RTL.CSH_FLO_TYP TYP ON REG.CSH_FLO_TYP=TYP.CSH_FLO_TYP
            WHERE (
                (REG.CSH_FLO_TYP IN ('RT','DP','DL','FL') AND RTL_BRC <> '') OR (REG.CSH_FLO_TYP='DS')
            ) AND (REG.DISC_AMT IS NULL OR REG.DISC_AMT=0) AND REG.TRSC_NBR='" . $transactionNumber . "'";
    $result = mysql_query($query, $rtl);
    $row    = mysql_fetch_array($result);
    $totalAmount = $row['TND_AMT'] - ($row['DISC_AMT'] + $row['DISC_PCT_AMT']);

    $ppnAmount = round($ppn * ($totalAmount / 100));

    // Add ppn amount
    $query  = "INSERT INTO RTL.CSH_REG (TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,RTL_PRC,TND_AMT,POS_ID) VALUES  ('" . $transactionNumber . "'," . $QNumber . "," . $personNumber . "," . $CoNbrDef . ",'PN',''," . $ppn . "," . $ppnAmount . ",'" . $POSID . "')";
    $result = mysql_query($query, $rtl);
    $registerNumber = mysql_insert_id($rtl);

    $query  = "INSERT INTO RTL.CSH_REG_TODAY (REG_NBR,TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,RTL_PRC,TND_AMT,POS_ID) VALUES  (" . $registerNumber . ",'" . $transactionNumber . "'," . $QNumber . "," . $personNumber . "," . $CoNbrDef . ",'PN',''," . $ppn . "," . $ppnAmount . ",'" . $POSID . "')";
    $result = mysql_query($query, $rtl);
	
    $query  = "INSERT INTO CSH.CSH_REG (REG_NBR,TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,RTL_PRC,TND_AMT,CSH_FLO_PART) VALUES  (" . $registerNumber . ",'" . $transactionNumber . "'," . $QNumber . "," . $personNumber . "," . $CoNbrDef . ",'PN',''," . $ppn . "," . $ppnAmount . ",'B')";
    $result = mysql_query($query, $csh);
	
    // Adjust plus mode if exists
    adjustPlusMode($transactionNumber, null, $transactionNumberPlus);
}

function adjustSurcharge($transactionNumber, $transactionNumberPlus = null, $QNumber = null, $surchargeVal = null) {
    // Keep DRY. Just take from the global variables
    global $rtl, $csh, $CoNbrDef, $POSID, $personNumber, $surchargeVal;
		
	#====================================== DELETE FROM RTL.CSH_REG ================================#
	
    // Remove previous surcharge calculation
    $query  = "DELETE FROM RTL.CSH_REG WHERE CSH_FLO_TYP='SU' AND TRSC_NBR='" . $transactionNumber . "'";
    $result = mysql_query($query, $rtl);

    $query  = "DELETE FROM RTL.CSH_REG_TODAY WHERE CSH_FLO_TYP='SU' AND TRSC_NBR='" . $transactionNumber . "'";
    $result = mysql_query($query, $rtl);

    $query  = "DELETE FROM CSH.CSH_REG WHERE CSH_FLO_TYP='SU' AND TRSC_NBR='" . $transactionNumber . "'";
    $result = mysql_query($query, $csh);
    
    // Get surcharge amount
    $creditCardSur = $surchargeVal;
    //$creditCardSur = getDbParamGlobal('CRT_CRD_SUR');
    
    // Get total transaction and discount amount
    $query  = "SELECT SUM(COALESCE(CSH_FLO_MULT, 1)*TND_AMT) AS TND_AMT,
                SUM(CASE WHEN REG.CSH_FLO_TYP='RT' THEN COALESCE(DISC_AMT, 0) ELSE 0 END) AS DISC_AMT,
                SUM(CASE WHEN REG.CSH_FLO_TYP='RT' THEN (COALESCE(DISC_PCT, 0)/100)*(COALESCE(CSH_FLO_MULT, 1)*TND_AMT) ELSE 0 END) AS DISC_PCT_AMT
            FROM RTL.CSH_REG REG
                INNER JOIN RTL.CSH_FLO_TYP TYP ON REG.CSH_FLO_TYP=TYP.CSH_FLO_TYP
            WHERE (
                (REG.CSH_FLO_TYP IN ('RT','DP','DL','FL') AND RTL_BRC <> '') OR (REG.CSH_FLO_TYP='DS')
            ) AND (REG.DISC_AMT IS NULL OR REG.DISC_AMT=0) AND REG.TRSC_NBR='" . $transactionNumber . "'";
			
    $result = mysql_query($query, $rtl);
    $row    = mysql_fetch_array($result);
    $totalAmount = $row['TND_AMT'] - ($row['DISC_AMT'] + $row['DISC_PCT_AMT']);

    $creditCardSurAmount = round($creditCardSur * ($totalAmount / 100));

    // Add surcharge amount
    $query  = "INSERT INTO RTL.CSH_REG (TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,RTL_PRC,TND_AMT,POS_ID) VALUES
            ('" . $transactionNumber . "'," . $QNumber . ", " . $personNumber . "," . $CoNbrDef . ",'SU',''," . $creditCardSur . "," . $creditCardSurAmount . ",'" . $POSID . "')";
    $result = mysql_query($query, $rtl);
    $registerNumber = mysql_insert_id($rtl);

    $query  = "INSERT INTO RTL.CSH_REG_TODAY (REG_NBR,TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,RTL_PRC,TND_AMT,POS_ID) VALUES
            (" . $registerNumber . ", '" . $transactionNumber . "'," . $QNumber . ", " . $personNumber . "," . $CoNbrDef . ",'SU',''," . $creditCardSur . "," . $creditCardSurAmount . ",'" . $POSID . "')";
    $result = mysql_query($query, $rtl);
	
    $query  = "INSERT INTO CSH.CSH_REG (REG_NBR,TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,RTL_PRC,TND_AMT,CSH_FLO_PART) VALUES
            (" . $registerNumber . ",'" . $transactionNumber . "'," . $QNumber . ", " . $personNumber . "," . $CoNbrDef . ",'SU',''," . $creditCardSur . "," . $creditCardSurAmount . ",'B')";
    $result = mysql_query($query, $csh);
    
    adjustPlusMode($transactionNumber, null, $transactionNumberPlus);
	
}


function adjustPlusMode($transactionNumber, $registerNumber = null, $transactionNumberPlus = null ) {
    // Keep DRY. Just take from the global variables
    global $rtl;

			
    // Check if the register number is null then do a loop
    if (null == $registerNumber) {
        $query  = "SELECT REG_NBR FROM RTL.CSH_REG WHERE RTL_BRC <> '' AND REG_NBR_PLUS IS NULL AND TRSC_NBR='" . $transactionNumber . "' ORDER BY REG_NBR ASC";
        $result = mysql_query($query, $rtl);

        while ($row = mysql_fetch_array($result)) {
            adjustPlusMode($transactionNumber, $row['REG_NBR'], $transactionNumberPlus);
        }   
    } else {
        // Process the register number
			
			$query  = "SELECT MAX(TRSC_NBR_PLUS) AS TRSC_NBR_PLUS
                    FROM RTL.CSH_REG
                    WHERE ACT_F=1 AND DATE(CRT_TS)=CURRENT_DATE AND CRT_NBR='" . $personNumber . "' AND POS_ID='" . $POSID . "'";
			$result            = mysql_query($query, $rtl);
			$row               = mysql_fetch_array($result);
			$transactionNumberPlus = $row['TRSC_NBR_PLUS'];

			$transactionServerPlus 	= 0;
			$transactionLocalPlus 	= 1;
			$registerServerPlus 	= 0;
			$registerLocalPlus 		= 1;
			
			$start = microtime(true);
			$limit = 0.1;  // Seconds
			
			if($transactionNumberPlus == "") {
				while($transactionServerPlus != $transactionLocalPlus) {
					if (microtime(true) - $start >= $limit) {
					
					$query_cek				= "SELECT TRSC_NBR_PLUS FROM RTL.CSH_REG_TOKEN";
					$result_cek				= mysql_query($query_cek, $rtl);
					$row_cek				= mysql_fetch_array($result_cek);
					$transactionServerPlus	= $row_cek['TRSC_NBR_PLUS'];
					
					$transactionLocalPlus	= $transactionServerPlus+1;
					
					$query 					= "UPDATE RTL.CSH_REG_TOKEN SET TRSC_NBR_PLUS = TRSC_NBR_PLUS+1";
					$result					= mysql_query($query, $rtl);			
					
					$query_cek				= "SELECT TRSC_NBR_PLUS FROM RTL.CSH_REG_TOKEN";
					$result_cek				= mysql_query($query_cek, $rtl);
					$row_cek				= mysql_fetch_array($result_cek);
					$transactionServerPlus	= $row_cek['TRSC_NBR_PLUS'];
					}
				}
			
				$transactionNumberPlus = $transactionServerPlus;
			}
			
			while($registerServerPlus != $registerLocalPlus) {
				if (microtime(true) - $start >= $limit) {
				
				$query_cek			= "SELECT REG_NBR_PLUS FROM RTL.CSH_REG_TOKEN";
				$result_cek			= mysql_query($query_cek, $rtl);
				$row_cek			= mysql_fetch_array($result_cek);
				$registerServerPlus	= $row_cek['REG_NBR_PLUS'];
				
				$registerLocalPlus 	= $registerServerPlus+1;
				
				$query 				= "UPDATE RTL.CSH_REG_TOKEN SET REG_NBR_PLUS = REG_NBR_PLUS+1";
				$result				= mysql_query($query, $rtl);			
				
				$query_cek			= "SELECT REG_NBR_PLUS FROM RTL.CSH_REG_TOKEN";
				$result_cek			= mysql_query($query_cek, $rtl);
				$row_cek			= mysql_fetch_array($result_cek);
				$registerServerPlus	= $row_cek['REG_NBR_PLUS'];
				
				}
			}
			
			$registerNumberPlus 	= $registerServerPlus;

        $query  = "UPDATE RTL.CSH_REG AS REG INNER JOIN (
                    SELECT (CASE WHEN CSH.TRSC_NBR_PLUS > 0 THEN CSH.TRSC_NBR_PLUS ELSE (".$transactionNumberPlus.") END) AS TRSC_NBR_PLUS,
                    (".$registerNumberPlus.") AS REG_NBR_PLUS
                    FROM (SELECT MAX(TRSC_NBR_PLUS) AS TRSC_NBR_PLUS FROM RTL.CSH_REG WHERE TRSC_NBR='" . $transactionNumber . "') AS CSH
                ) AS PLUS
                SET REG.REG_NBR_PLUS=PLUS.REG_NBR_PLUS, REG.TRSC_NBR_PLUS=PLUS.TRSC_NBR_PLUS
                WHERE TRSC_NBR='" . $transactionNumber . "' AND REG_NBR=" . $registerNumber;
		mysql_query($query, $rtl);
		
        $query  = "UPDATE RTL.CSH_REG_TODAY AS REG INNER JOIN (
                    SELECT (CASE WHEN CSH.TRSC_NBR_PLUS > 0 THEN CSH.TRSC_NBR_PLUS ELSE (".$transactionNumberPlus.") END) AS TRSC_NBR_PLUS,
                    (".$registerNumberPlus.") AS REG_NBR_PLUS
                    FROM (SELECT MAX(TRSC_NBR_PLUS) AS TRSC_NBR_PLUS FROM RTL.CSH_REG WHERE TRSC_NBR='" . $transactionNumber . "') AS CSH
                ) AS PLUS
                SET REG.REG_NBR_PLUS=PLUS.REG_NBR_PLUS, REG.TRSC_NBR_PLUS=PLUS.TRSC_NBR_PLUS
                WHERE TRSC_NBR='" . $transactionNumber . "' AND REG_NBR=" . $registerNumber;
        mysql_query($query, $rtl);
    }

}


	function getDbParamGlobal($keys)
	{
		global $rtl;
		
		$results = null;
		
		if (!is_resource($rtl)) {
			return $result;
		}
		
		if (is_string($keys)) {
			$query = "SELECT ".$keys." FROM NST.PARAM_GLBL";
			$result = mysql_query($query, $rtl);
			$row = mysql_fetch_array($result);
			$results = $row[$keys];
		} 
		/*
		elseif (is_array($keys)) {
			$query = "SELECT NAME, VALUE, UPD_NBR, UPD_TS FROM RTL.PARAM WHERE NAME IN ('" . implode("', '", $keys) . "')";
			$result = mysql_query($query);
			$results = array();

			while($row = mysql_fetch_array($result)) {
				$results[$row['NAME']] = $row['VALUE'];
			}
		}
		*/
		
		return $results;
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
    <script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
    <link rel="stylesheet" type="text/css" media="screen" href="css/cashier.css" />
    <script type='text/javascript'>
        parent.document.getElementById('listing').style.backgroundColor='#dddddd';
        parent.document.getElementById('total').style.backgroundColor='#ffffff';
        parent.document.getElementById('total').src='cashier-total.php?POS_ID=<?php echo $POSID; ?>';
    </script>
</head>
<body>
    <table style='background-color:#ffffff;border-left:solid 1px #eeeeee;border-right:solid 1px #eeeeee;width:100%;overflow:scroll;'>
    <?php
        // Retail entries
        $query  = "SELECT REG_NBR,
                        TRSC_NBR,
                        REG.CO_NBR,
                        REG.RTL_BRC,
                        RTL_Q,
                        REG.RTL_PRC,
                        #INV.NAME AS NAME_DESC,
						(CASE 
							WHEN CONCAT
								(INV.NAME,' ',
								CASE WHEN COLR_DESC IS NULL OR COLR_DESC = '' THEN '' ELSE COLR_DESC END,' ',
								CASE WHEN THIC IS NULL OR THIC = '' THEN '' ELSE THIC END,' ',
								CASE WHEN SIZE IS NULL OR SIZE = '' THEN '' ELSE SIZE END,' ',
								CASE WHEN WEIGHT IS NULL OR WEIGHT = '' THEN '' ELSE WEIGHT END) 
							IS NULL THEN INV.NAME 
							ELSE CONCAT
								(TRIM(INV.NAME),' ',
								CASE WHEN COLR_DESC IS NULL OR COLR_DESC = '' THEN '' ELSE TRIM(COLR_DESC) END,' ',
								CASE WHEN THIC IS NULL OR THIC = '' THEN '' ELSE TRIM(THIC) END,' ',
								CASE WHEN SIZE IS NULL OR SIZE = '' THEN '' ELSE TRIM(SIZE) END,' ',
								CASE WHEN WEIGHT IS NULL OR WEIGHT = '' THEN '' ELSE TRIM(WEIGHT) END)
						END) AS NAME_DESC,
                        COALESCE(DISC_AMT,0) AS DISC_AMT,
                        COALESCE(DISC_PCT,0) AS DISC_PCT,
                        TND_AMT,
                        ORD_NBR,
                        CSH_FLO_DESC,
                        REG.CSH_FLO_TYP,
                        CSH_FLO_MULT,
                        PYMT_DESC,
                        REG.PYMT_TYP,ACT_F
                FROM RTL.CSH_REG REG
                    LEFT JOIN CMP.COMPANY COM ON REG.CO_NBR=COM.CO_NBR
                    LEFT JOIN RTL.CSH_FLO_TYP TYP ON REG.CSH_FLO_TYP=TYP.CSH_FLO_TYP
                    LEFT JOIN RTL.PYMT_TYP PAY ON REG.PYMT_TYP=PAY.PYMT_TYP
                    LEFT JOIN RTL.INVENTORY INV ON REG.RTL_BRC=INV.INV_BCD
					LEFT JOIN CMP.INV_COLR CLR ON INV.COLR_NBR=CLR.COLR_NBR 
                WHERE ACT_F=1 AND (INV.DEL_NBR=0 OR REG.CSH_FLO_TYP='FL' OR REG.CSH_FLO_TYP='DP' OR REG.CSH_FLO_TYP='GP' OR REG.CSH_FLO_TYP='IV')
                    AND TRSC_NBR=(SELECT MAX(TRSC_NBR) AS TRSC_NBR FROM RTL.CSH_REG WHERE ACT_F=1 AND DATE(CRT_TS)=CURRENT_DATE AND CRT_NBR=" . $personNumber . " AND POS_ID=" . $POSID . ")
                    AND REG.RTL_BRC <> ''
                ORDER BY CRT_TS DESC";
		
        $result = mysql_query($query, $rtl);
        $alt    = "";
		
        while ($row = mysql_fetch_array($result)) {
            echo "<tr $alt>";

            if (($row['CSH_FLO_TYP'] == 'DP') || ($row['CSH_FLO_TYP'] == 'IV') || ($row['CSH_FLO_TYP'] == 'FL') || ($row['CSH_FLO_TYP'] == 'ED') || ($row['CSH_FLO_TYP'] == 'GP')) {
                if (($row['CSH_FLO_TYP'] == 'DP') || ($row['CSH_FLO_TYP'] == 'ED') || ($row['CSH_FLO_TYP'] == 'IV')) {
                    $description = "Pembayaran";
                } elseif (($row['CSH_FLO_TYP'] == 'FL') || ($row['CSH_FLO_TYP'] == 'GP')) {
                    $description = "Pembayaran";
                }
                
                if (($row['CSH_FLO_TYP'] == 'DP') || ($row['CSH_FLO_TYP'] == 'FL')) {
                    $description .= " Digital Printing";
                } elseif($row['CSH_FLO_TYP'] == 'IV') {
                    $description .= " Pembelian";
                } else {
                    $description .= " Retail Sales";
                }
                
                echo "<td style='text-align:left'>" . $description . "<br/>Nota <span style='color:#999999'>" . $row['RTL_BRC'] . "</span></td>";
                echo "<td style='text-align:right;vertical-align:top'><b>Rp. " . number_format($row['CSH_FLO_MULT'] * $row['TND_AMT'], 0, ",", ".") . "</b></td>";
            } else {
                $discount = "&nbsp;<span style='color:#999999'>";

                if ($row['DISC_AMT'] > 0) {
                    $discount .= "(Disc Rp. " . number_format($row['DISC_AMT'], 0, ",", ".") . ")";
                } elseif ($row['DISC_PCT'] > 0) {
                    $discount .= "(Disc " . number_format($row['DISC_PCT'], 0, ",", ".") . "%)";
                }

                $discount .= "</span>";

                echo "<td style='text-align:left'>" . $row['NAME_DESC'];

                if ($row['CSH_FLO_TYP'] == 'PN') {
                    echo "&nbsp;(PPN)";
                }

                echo "<br/><span style='color:#999999'>" . $row['RTL_BRC'] . "</span> " . $row['RTL_Q'] . " x @ Rp. " . number_format($row['RTL_PRC'], 0, ",", ".") . $discount . "</td>";

                $discountAmount = $row['DISC_AMT'];

                if ($row['DISC_PCT'] > 0) {
                    $discountAmount += ($row['CSH_FLO_MULT'] * $row['TND_AMT']) * ($row['DISC_PCT'] / 100);
                }

                echo "<td style='text-align:right;vertical-align:top'><b>Rp. " . number_format(($row['CSH_FLO_MULT'] * $row['TND_AMT']) - $discountAmount, 0, ",", ".") . "</b></td>";
            }

            echo "</tr>";
            
            if ($alt == "") {$alt = "class='alt-cashier'";} else {$alt = "";}
        }
    ?>
    </table>
</body>
</html>