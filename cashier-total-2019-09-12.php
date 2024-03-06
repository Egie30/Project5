<?php
require_once "framework/database/connect-cashier.php";
require_once "framework/functions/default.php";

$userID       = $_SESSION['userID'];
$personNumber = $_SESSION['personNBR'];

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

/*
if (!empty($transactionNumber)) {
    $query  = "SELECT MBR_ID FROM RTL.CSH_REG WHERE MBR_ID > 0 AND TRSC_NBR='" . $transactionNumber . "' GROUP BY MBR_ID";
    $result = mysql_query($query, $rtl);
    $row    = mysql_fetch_array($result);
    $memberNumber   = $row['MBR_ID'];
}
*/

$query		= "SELECT MAX(Q_NBR) AS Q_NBR
				FROM RTL.CSH_REG
                WHERE ACT_F=1 AND DATE(CRT_TS)=CURRENT_DATE AND POS_ID='" . $POSID . "'";
$result		= mysql_query($query, $rtl);
$row		= mysql_fetch_array($result);
$QNumber	= $row['Q_NBR'];

$totalItem = 0;
$totalReturn = 0;
$totalBruto = 0;
$totalNetto = 0;
$totalDisc = 0;
$totalTax = 0;
$totalCreditSur = 0;
$totalPayment = 0;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
</head>
<body style="background-image:url(img/total-back.png);background-position:bottom center; background-size:100%;">
<div style="height:20px"></div>
<?php
	// Show No. Nota
	echo "<h2>&nbsp&nbsp&nbsp&nbspNo. Struk ";
    // Be sure Transaction number is not null
	if (empty($QNumber)) {
        $query  = "SELECT Q_NBR FROM CSH.CSH_REG_TOKEN";
        $result = mysql_query($query, $csh);
        $row    = mysql_fetch_array($result);
		
		echo $row['Q_NBR'];	
    } else {
		//echo $transactionNumber;		
		echo $QNumber;
	}
	echo "</h2>";
    
    // Show member ID
	/*
    if (!empty($memberNumber)) {
        echo "<h2>&nbsp&nbsp&nbsp&nbspMember ID: " . $memberNumber . "</h2>";  
    }
	*/
?>
<table style="width:100%;overflow:scroll">
<?php
	$query  = "SELECT SUM(CASE WHEN RTL_Q > 0 THEN RTL_Q ELSE 0 END) AS RTL_Q,
                SUM(CASE WHEN RTL_Q < 0 THEN RTL_Q ELSE 0 END) AS RTR_Q,
                SUM(COALESCE(CSH_FLO_MULT, 1)*TND_AMT) AS TND_AMT,
                SUM(CASE WHEN REG.CSH_FLO_TYP='RT' THEN COALESCE(DISC_AMT, 0) ELSE 0 END) AS DISC_AMT,
                SUM(CASE WHEN REG.CSH_FLO_TYP='RT' THEN (COALESCE(DISC_PCT, 0)/100)*(COALESCE(CSH_FLO_MULT, 1)*TND_AMT) ELSE 0 END) AS DISC_PCT
            FROM RTL.CSH_REG REG
				LEFT OUTER JOIN RTL.CSH_FLO_TYP TYP ON REG.CSH_FLO_TYP=TYP.CSH_FLO_TYP
            WHERE ((REG.CSH_FLO_TYP IN ('RT') AND RTL_BRC <> '') OR REG.CSH_FLO_TYP IN ('DP', 'FL', 'ED', 'GP', 'IV'))
			    AND REG.TRSC_NBR='" . $transactionNumber . "'
			ORDER BY REG.CRT_TS DESC";
	$result = mysql_query($query, $rtl);
	$row    = mysql_fetch_array($result);
    $totalItem = abs($row['RTL_Q']);
    $totalReturn = abs($row['RTR_Q']);
	$totalBruto = $row['TND_AMT'] - ($row['DISC_AMT'] + $row['DISC_PCT']);

    echo "<tr>";
    echo "<td style='text-align:left;border-bottom:0px;font-size:16pt;padding-left:20px;color:#999999'>Jumlah item</td>";
    echo "<td style='text-align:right;border-bottom:0px;font-size:16pt;padding-right:20px'><b> " . number_format($totalItem, 0, ",", ".") . "</td>";
    echo "</tr>";

    if ($totalReturn > 0) {
    	echo "<tr>";
    	echo "<td style='text-align:left;border-bottom:0px;font-size:16pt;padding-left:20px;color:#999999'>Jumlah retur item</td>";
    	echo "<td style='text-align:right;border-bottom:0px;font-size:16pt;padding-right:20px'><b> " . number_format($totalReturn, 0, ",", ".") . "</td>";
    	echo "</tr>";
    }

	echo "<tr>";
	echo "<td style='text-align:left;border-bottom:0px;font-size:16pt;padding-left:20px;color:#999999'>Total</td>";
	echo "<td style='text-align:right;border-bottom:0px;font-size:16pt;padding-right:20px'><b>Rp. " . number_format($totalBruto, 0, ",", ".") . "</td>";
	echo "</tr>";
?>
</table>

<table style="width:100%;overflow:scroll">
<?php
    $query  = "SELECT RTL_PRC, REG.CSH_FLO_TYP, COALESCE(CSH_FLO_MULT, 1) AS CSH_FLO_MULT, COALESCE(CSH_FLO_MULT, 1)*TND_AMT AS TND_AMT
    		FROM RTL.CSH_REG REG
    			LEFT OUTER JOIN RTL.CSH_FLO_TYP TYP ON REG.CSH_FLO_TYP=TYP.CSH_FLO_TYP
    		WHERE REG.CSH_FLO_TYP='DS' AND REG.TRSC_NBR='" . $transactionNumber . "'
				AND REG.TND_AMT > 0
            ORDER BY REG.REG_NBR";
    $result = mysql_query($query, $rtl);

    while ($row = mysql_fetch_array($result)) {
        echo "<tr>";
        echo "<td style='text-align:left;border-bottom:0px;font-size:16pt;padding-left:20px;color:#999999'>Diskon";
        
        if ($row['RTL_PRC'] != ($row['CSH_FLO_MULT'] * $row['TND_AMT'])) {
            echo " (" . $row['RTL_PRC'] . "%)";
        }
        
        echo "</td>";
        echo "<td style='text-align:right;border-bottom:0px;font-size:16pt;padding-right:20px'><b>Rp. " . number_format(abs($row['TND_AMT']), 0, ",", ".") . "</td>";
        
        $totalDisc += $row['TND_AMT'];
    }
?>
</table>

<table style="width:100%;overflow:scroll">
<?php
    $query  = "SELECT RTL_PRC, REG.CSH_FLO_TYP, COALESCE(CSH_FLO_MULT, 1) AS CSH_FLO_MULT, COALESCE(CSH_FLO_MULT, 1)*TND_AMT AS TND_AMT
    		FROM RTL.CSH_REG REG
    			LEFT OUTER JOIN RTL.CSH_FLO_TYP TYP ON REG.CSH_FLO_TYP=TYP.CSH_FLO_TYP
    		WHERE REG.CSH_FLO_TYP='DS' AND REG.TRSC_NBR='" . $transactionNumber . "'
				AND REG.TND_AMT < 0
            ORDER BY REG.REG_NBR";
    $result = mysql_query($query, $rtl);

    while ($row = mysql_fetch_array($result)) {
        echo "<tr>";
        echo "<td style='text-align:left;border-bottom:0px;font-size:16pt;padding-left:20px;color:#999999'>Diskon Retur";
        
        if ($row['RTL_PRC'] != ($row['CSH_FLO_MULT'] * $row['TND_AMT'])) {
            echo " (" . $row['RTL_PRC'] . "%)";
        }
        
        echo "</td>";
        echo "<td style='text-align:right;border-bottom:0px;font-size:16pt;padding-right:20px'><b>Rp. " . number_format(abs($row['TND_AMT']), 0, ",", ".") . "</td>";
        
        $totalDisc += $row['TND_AMT'];
    }
?>
</table>

<table style="width:100%;overflow:scroll">
<?php
    $query  = "SELECT RTL_PRC, REG.CSH_FLO_TYP, COALESCE(CSH_FLO_MULT, 1) AS CSH_FLO_MULT, COALESCE(CSH_FLO_MULT, 1)*TND_AMT AS TND_AMT
            FROM RTL.CSH_REG REG
                LEFT OUTER JOIN RTL.CSH_FLO_TYP TYP ON REG.CSH_FLO_TYP=TYP.CSH_FLO_TYP
            WHERE REG.CSH_FLO_TYP='PN' AND REG.TRSC_NBR='" . $transactionNumber . "'
            ORDER BY REG.REG_NBR";
    $result = mysql_query($query, $rtl);

    while ($row = mysql_fetch_array($result)) {
        echo "<tr>";
        echo "<td style='text-align:left;border-bottom:0px;font-size:16pt;padding-left:20px;color:#999999'>PPN";
        
        if ($row['RTL_PRC'] != ($row['CSH_FLO_MULT'] * $row['TND_AMT'])) {
            echo " (" . $row['RTL_PRC'] . "%)";
        }
        
        echo "</td>";
        echo "<td style='text-align:right;border-bottom:0px;font-size:16pt;padding-right:20px'><b>Rp. " . number_format(abs($row['TND_AMT']), 0, ",", ".") . "</td>";
        
        $totalTax += $row['TND_AMT'];
    }
?>
</table>

<table style="width:100%;overflow:scroll">
<?php
    $query  = "SELECT RTL_PRC, REG.CSH_FLO_TYP, COALESCE(CSH_FLO_MULT, 1) AS CSH_FLO_MULT, COALESCE(CSH_FLO_MULT, 1)*TND_AMT AS TND_AMT
            FROM RTL.CSH_REG REG
                LEFT OUTER JOIN RTL.CSH_FLO_TYP TYP ON REG.CSH_FLO_TYP=TYP.CSH_FLO_TYP
            WHERE REG.CSH_FLO_TYP='SU' AND REG.TRSC_NBR='" . $transactionNumber . "'
            ORDER BY REG.REG_NBR";
    $result = mysql_query($query, $rtl);
	
    while ($row = mysql_fetch_array($result)) {
        echo "<tr>";
        echo "<td style='text-align:left;border-bottom:0px;font-size:16pt;padding-left:20px;color:#999999'>Card fee";
        
        if ($row['RTL_PRC'] != ($row['CSH_FLO_MULT'] * $row['TND_AMT'])) {
            echo " (" . $row['RTL_PRC'] . "%)";
        }
        
        echo "</td>";
        echo "<td style='text-align:right;border-bottom:0px;font-size:16pt;padding-right:20px'><b>Rp. " . number_format(abs($row['TND_AMT']), 0, ",", ".") . "</td>";
        
        $totalCreditSur += $row['TND_AMT'];
    }
?>
</table>

<?php
$totalNetto = $totalBruto + $totalDisc + $totalTax + $totalCreditSur;
?>
<div style="height:1px;margin:10px;background-color:#dddddd"></div>
<table style="width:100%;overflow:scroll">
    <tr>
        <td style="text-align:left;border-bottom:0px;font-size:16pt;padding-left:20px;color:#999999">Nett</td>
        <td style="text-align:right;border-bottom:0px;font-size:16pt;padding-right:20px"><b>Rp. <?php echo number_format($totalNetto, 0, ",", ".");?></td>
    </tr>
</table>

<table style="width:100%;overflow:scroll">
<?php
    $query  = "SELECT RTL_PRC, REG.CSH_FLO_TYP, COALESCE(CSH_FLO_MULT, 1) AS CSH_FLO_MULT, TND_AMT, PYMT_DESC, REG.PYMT_TYP
            FROM RTL.CSH_REG REG
                LEFT OUTER JOIN RTL.CSH_FLO_TYP TYP ON REG.CSH_FLO_TYP=TYP.CSH_FLO_TYP
                LEFT OUTER JOIN RTL.PYMT_TYP PAY ON REG.PYMT_TYP=PAY.PYMT_TYP
            WHERE (REG.CSH_FLO_TYP='PA' OR REG.CSH_FLO_TYP='VC') AND REG.TRSC_NBR='" . $transactionNumber . "'
            ORDER BY REG.REG_NBR";
    $result = mysql_query($query, $rtl);

    $isPaid = (mysql_num_rows($result) > 0);

    while ($row = mysql_fetch_array($result)) {
        echo "<tr>";
        echo "<td style='text-align:left;border-bottom:0px;font-size:16pt;padding-left:20px;color:#999999'>";
        echo $row['PYMT_DESC'];
        echo "</td>";
        echo "<td style='text-align:right;border-bottom:0px;font-size:16pt;padding-right:20px'><b>Rp. " . number_format(abs($row['TND_AMT']), 0, ",", ".") . "</td>";
        
        $totalPayment += $row['TND_AMT'];
    }
?>
</table>

<?php if ($isPaid && $totalPayment >= $totalNetto) {
    $query = "SELECT REG_NBR, TRSC_NBR, CO_NBR, TND_AMT, CSH_FLO_TYP, RTL_BRC, DISC_AMT, DISC_PCT
            FROM RTL.CSH_REG
            WHERE TRSC_NBR = ". $transactionNumber . "
            ORDER BY REG_NBR ASC";
    $result       = mysql_query($query, $rtl);

    while($row = mysql_fetch_array($result)) {
        $whereClauses = array();

        $whereClauses[] = "TRSC_NBR = " . $row['TRSC_NBR'];
        $whereClauses[] = "REG_NBR = " . $row['REG_NBR'];
        $whereClauses[] = "CO_NBR = " . $row['CO_NBR'];
        $whereClauses[] = "TND_AMT = " . $row['TND_AMT'];
        $whereClauses[] = "CSH_FLO_TYP = '" . $row['CSH_FLO_TYP'] . "'";

        if ($row['RTL_BRC'] != "") {
            $whereClauses[] = "RTL_BRC = " . $row['RTL_BRC'];
        }

        if ($row['DISC_AMT'] != "") {
            $whereClauses[] = "DISC_AMT = " . $row['DISC_AMT'];
        }

        if ($row['DISC_PCT'] != "") {
            $whereClauses[] = "DISC_PCT = " . $row['DISC_PCT'];
        }

        $queryCheck = "SELECT REG_NBR, TRSC_NBR FROM CSH.CSH_REG
                WHERE " . implode(" AND ", $whereClauses);
        $resultCheck = mysql_query($queryCheck, $csh);

		//echo $queryCheck."<br />";
		
        if (mysql_num_rows($resultCheck) != 1) {
			/*
            ?>
            <script type='text/javascript'>
                parent.parent.document.getElementById('search').contentDocument.getElementById('notification').style.display='';
                parent.parent.document.getElementById('search').contentDocument.getElementById('notification').innerHTML='Maaf terdapat record yang rusak didalam transaksi yang sedang aktif.<br/>Diharapkan anda untuk mengulang kembali transaksi ini.<br/>Terima Kasih.';
            </script>
            <?php

            // Be sure we didn't continue this transaction
            return;
			*/
        }
    }

    if ($totalPayment >= ($totalBruto + $totalDisc) || $totalPayment == 0) {
        $change = $totalPayment - ($totalNetto);

        $query  = "INSERT INTO RTL.CSH_REG (TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,PYMT_TYP,TND_AMT,POS_ID) VALUES
                ('" . $transactionNumber . "'" . "," . $QNumber . "," . $personNumber . "," . $CoNbrDef . ",'TL','','CSH'," . $totalBruto . ",'" . $POSID . "')";
        $result = mysql_query($query, $rtl);
            
        // Record change
        if ($change > 0) {
            $query  = "INSERT INTO RTL.CSH_REG (TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,PYMT_TYP,TND_AMT,POS_ID) VALUES
                    ('" . $transactionNumber . "'" . "," . $QNumber . "," . $personNumber . "," . $CoNbrDef . ",'CH','','CSH'," . $change . ",'" . $POSID . "')";
            $result = mysql_query($query, $rtl);
            $registerNumber = mysql_insert_id($rtl);
            $query  = "INSERT INTO CSH.CSH_REG (REG_NBR,TRSC_NBR,Q_NBR,CRT_NBR,CO_NBR,CSH_FLO_TYP,RTL_BRC,PYMT_TYP,TND_AMT,CSH_FLO_PART) VALUES
                    (" . $registerNumber . ",'" . $transactionNumber . "'" . "," . $QNumber . ", " . $personNumber . "," . $CoNbrDef . ",'CH','','CSH'," . $change . ",'D')";
            $result = mysql_query($query, $csh);
        }
            
        // Close transaction
        $query  = "UPDATE RTL.CSH_REG SET ACT_F=0 WHERE TRSC_NBR='" . $transactionNumber . "'";
        $result = mysql_query($query, $rtl);
    }
    ?>
    <div style="height:1px;margin:10px;background-color:#dddddd"></div>
    <table style="width:100%;overflow:scroll">
        <tr>
            <td style="text-align:left;border-bottom:0px;font-size:16pt;padding-left:20px;color:#999999">Kembali</td>
            <td style="text-align:right;border-bottom:0px;font-size:16pt;padding-right:20px"><b>Rp. <?php echo number_format($change, 0, ",", ".");?></td>
        </tr>
    </table>
    
    <script type='text/javascript'>
        parent.document.getElementById('bottom').src='http://<?php echo $POSIP;?>/cashier-bottom.php?TRSC_NBR=<?php echo $transactionNumber;?>&PRSN_NBR=<?php echo $personNumber;?>&CSH=<?php echo $userID;?>&Q_NBR=<?php echo $QNumber; ?>&POS_ID=<?php echo $POSID; ?>';

		//parent.document.getElementById('bottom').src='http://<?php echo $POSIP;?>/cashier-bottom.php?TRSC_NBR=<?php echo $transactionNumber;?>&PRSN_NBR=<?php echo $personNumber;?>&CSH=<?php echo $userID;?>&POS_ID=<?php echo $POSID; ?>';
		
        setTimeout(function(){
            parent.displaySlideshow();
        }, 15000);
    </script>
<?php 

//echo $POSIP." // ".$transactionNumber." // ".$personNumber." // ".$userID." // ".$POSID;

}

/**
 * Check to see if any open transaction. Some new procedures was applied to checking an open transaction.
 * - Put it globally. So we are doesn't repeat same query.
 * - This procedures really faster than old procedure.
 * - Strict mode has been applied
 */
$query             = "SELECT MAX(TRSC_NBR) AS TRSC_NBR
                    FROM RTL.CSH_REG
                    WHERE ACT_F=1 AND DATE(CRT_TS)=CURRENT_DATE AND CRT_NBR='" . $personNumber . "' AND POS_ID='" . $POSID . "'";
$result            = mysql_query($query, $rtl);
$row               = mysql_fetch_array($result);
$transactionNumber = $row['TRSC_NBR'];

if (empty($transactionNumber)) { ?>
    <script type='text/javascript'>
        var displaySlideshowTimer;

        function endAndStartTimer() {
            clearTimeout(displaySlideshowTimer);

            displaySlideshowTimer = setTimeout(function(){
                parent.displaySlideshow();
            }, 15000); 
        }

        endAndStartTimer();
    </script>
<?php } ?>
</body>
</html>