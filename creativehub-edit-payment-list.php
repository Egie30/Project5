<?php

global $paymenttable;
include "framework/database/connect.php";
include "framework/functions/default.php";
include "framework/security/default.php";

$CashSec = getSecurity($_SESSION['userID'], "Finance");

$TndAmt = mysql_escape_string($_GET['TND_AMT']);
$altOrig = $_GET['ALT'];
$alt = $altOrig;
$PymtNbr = mysql_escape_string($_GET['PYMT_NBR']);
$OrdNbr = mysql_escape_string($_GET['ORD_NBR']);
$PrsnNbr = $_SESSION['personNBR'];
$Del = mysql_escape_string($_GET['PYMT_TYP']);

if ($Del != '') {
    $query = "UPDATE CMP.RTL_ORD_PYMT SET DEL_NBR=" . $_SESSION['personNBR'] . ", 
    UPD_TS=CURRENT_TIMESTAMP, CRT_TS=CRT_TS WHERE PYMT_NBR=" . $PymtNbr;
    //echo $query;
    $result = mysql_query($query);
}

if ($TndAmt != '') {
    //Add Payment
    $query = "INSERT INTO CMP.RTL_ORD_PYMT SET 
    ORD_NBR=" . $OrdNbr . ",
    TND_AMT=" . $TndAmt . ",
    DEL_NBR=0,
    CRT_TS=CURRENT_TIMESTAMP,
    CRT_NBR=" . $PrsnNbr;
    $result = mysql_query($query);

    //Process payment journal
    $query = "INSERT INTO CMP.JRN_CSH_FLO (DIV_ID,NM_TBL,ORD_NBR,CSH_FLO_TYP,CSH_AMT,CRT_TS,CRT_NBR)
                VALUES ('PRN','PRN_DIG_ORD_HEAD'," . $OrdNbr . ",'FL'," . $TndAmt . ",CURRENT_TIMESTAMP,"
        . $_SESSION['personNBR'] . ")";
    $resultp = mysql_query($query);
}

//Process data update back to order head for compatibility
$query = "SELECT PYM.PYMT_NBR, PYM.ORD_NBR, PYM.VAL_NBR, PYM.TND_AMT, PYM.REF, 
       PPL.NAME, DATE_FORMAT(PYM.CRT_TS,'%d-%m-%Y') AS DTE, PYM.CRT_NBR
            FROM CMP.RTL_ORD_PYMT PYM
            INNER JOIN CMP.PEOPLE PPL ON PYM.CRT_NBR = PPL.PRSN_NBR
            WHERE PYM.DEL_NBR=0 AND PYM.ORD_NBR=" . $OrdNbr . "
            ORDER BY PYM.CRT_TS ASC";

$result = mysql_query($query);
$TotPay = 0;
while ($row = mysql_fetch_array($result)) {
    ?>
    <div class="form-group payment">
        <div class='print-digital-grey'><?php echo $row['PYMT_NBR'] ?></div>
        <label>Pembayaran <?php echo $row['PYMT_DESC'] . " " . parseDate($row['DTE']) ?></label>
        <div class='listable-btn delete-pay' data-nbr="<?php echo $row['PYMT_NBR'] ?>" title="Hapus Pembayaran">
            <span class='fa fa-trash listable-btn'></span>
        </div>
        <input value='<?php echo number_format($row['TND_AMT'], 0, ",", ".") ?>' type='text' readonly/>
    </div>
    <?php
    $TotPay += $row['TND_AMT'];
}
?>
<input type="hidden" id="TOT_PAY" value="<?php
echo $TotPay; ?>"/>
