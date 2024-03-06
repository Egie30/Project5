<?php

@header("Connection: close\r\n");
include "framework/database/connect.php";
include "framework/functions/default.php";
include "framework/security/default.php";

$UpperSec = getSecurity($_SESSION['userID'], "Accounting");
$OrdNbr = mysql_escape_string($_GET['ORD_NBR']);
$OrdDetNbr = mysql_escape_string($_GET['ORD_DET_NBR']);
$OrdDetNbrPar = mysql_escape_string($_GET['ORD_DET_NBR_PAR']);
$type = mysql_escape_string($_GET['TYP']);
$origin = mysql_escape_string($_GET['ORGN']);
$changed = false;
$addNew = false;


$BegDt = isset($_POST['BEG_TS']) ? date("Y-m-d", strtotime($_POST['BEG_TS'])) : date("Y-m-d");
$EndDt = isset($_POST['END_TS']) ? date("Y-m-d", strtotime($_POST['END_TS'])) : date("Y-m-d");


if($BegDt==""){
  $BegDt=date("Y-m-d ");
}
if($EndDt==""){
  $EndDt=date("Y-m-d ");
}


// Kategori voucher wifi
$wifiCategoryNbr = 12;

//[JOURNAL] get information schema for journal
$query_info = "SELECT TABLE_NAME, COLUMN_NAME, COLUMN_KEY 
               FROM INFORMATION_SCHEMA.COLUMNS 
               WHERE TABLE_SCHEMA = 'CMP' 
               AND TABLE_NAME ='RTL_ORD_DET'";
$result_info = mysql_query($query_info);
$array_info = array();
while ($row_info = mysql_fetch_array($result_info)) {
    if ($row_info['COLUMN_KEY'] == "PRI") {
        $PK = $row_info['COLUMN_NAME'];
    }
    $array_info[] = $row_info['COLUMN_NAME'];
}

//[JOURNAL] get data before changes
$query_awal = "SELECT * FROM CMP.RTL_ORD_DET WHERE ORD_DET_NBR='" . $OrdDetNbr . "'";
$result_awal = mysql_query($query_awal);
$row_awal = mysql_fetch_assoc($result_awal);

//Get order head information
$query = "SELECT HED.BUY_PRSN_NBR, HED.BUY_CO_NBR,
            FROM CMP.RTL_ORD_HEAD HED 
            LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR
            WHERE HED.ORD_NBR=" . $OrdNbr;
//echo $query;
$result = mysql_query($query);
$row = mysql_fetch_array($result);
if ($row['BUY_CO_NBR'] != "") {
    $where = "AND CO_NBR=" . $row['BUY_CO_NBR'];
    $CoNbr = $row['BUY_CO_NBR'];
} elseif ($row['BUY_PRSN_NBR'] != "") {
    $where = "AND PRSN_NBR=" . $row['BUY_PRSN_NBR'];
    $PrsnNbr = $row['BUY_CO_NBR'];
}
//echo $where;

//Process changes here
if ($_POST['ORD_DET_NBR'] != "") {
    function parseInt($str)
    {
        $str = str_replace(".", "", $str);
        return intval($str);
    }

    $OrdDetNbr = $_POST['ORD_DET_NBR'];
    //Take care of nulls
    if ($_POST['ORD_Q'] == "") {
        $OrdQ = "NULL";
    } else {
        $OrdQ = $_POST['ORD_Q'];
    }
    if ($_POST['PRC'] == "") {
        $Prc = "NULL";
    } else {
        $Prc = parseInt(mysql_escape_string($_POST['PRC']));
    }
    if ($_POST['FEE_MISC'] == "") {
        $FeeMisc = "NULL";
    } else {
        $FeeMisc = parseInt(mysql_escape_string($_POST['FEE_MISC']));
    }
    if (($_POST['DISC_PCT'] == "") || ($_POST['DISC_PCT'] == "NaN")) {
        $DiscPct = "NULL";
    } else {
        $DiscPct = parseInt(mysql_escape_string($_POST['DISC_PCT']));
    }
    if ($_POST['DISC_AMT'] == "") {
        $DiscAmt = "NULL";
    } else {
        $DiscAmt = parseInt(mysql_escape_string($_POST['DISC_AMT']));
    }
    if ($_POST['TOT_SUB'] == "") {
        $TotSub = "NULL";
    } else {
        $TotSub = parseInt(mysql_escape_string($_POST['TOT_SUB']));
    }
    if ($_POST['RUANG_NBR'] == "") {
        $RmNbr = "NULL";
    } else {
        $RmNbr = mysql_escape_string($_POST['RUANG_NBR']);
    }
    if ($_POST['MEJA_NBR'] == "") {
        $MejaNbr = "NULL";
    } else {
        $MejaNbr = mysql_escape_string($_POST['MEJA_NBR']);
    }

    $OrdCat = mysql_real_escape_string($_POST['CAT_ID']);
    $OrdTyp = mysql_real_escape_string($_POST['ORD_TYP']);

    do {
        // Handle WI-FI Voucher
        if ($OrdCat == $wifiCategoryNbr) {
            // Check in wifi_vchr table
            $query = "SELECT WIFI.WIFI_NBR FROM CMP.WIFI_VCHR WIFI 
                  LEFT JOIN CMP.RTL_ORD_TYP TYP 
                  ON TYP.RTL_ORD_TYP=WIFI.TYP_NBR 
                  WHERE WIFI.DEL_NBR=0
                  AND (WIFI.REF_NBR IS NULL OR WIFI.REF_NBR=0)
                  AND TYP.RTL_ORD_TYP= " . $OrdTyp . "
                  LIMIT 1";
            $result = mysql_query($query);
            $row = mysql_fetch_assoc($result);
            $WifiNbr = $row['WIFI_NBR'];

            if (empty($WifiNbr)) {
                $notif_error = "Voucher tidak tersedia";
                break;
            }
        }

        //Process add new
        if ($OrdDetNbr == -1) {
            $addNew = true;
            $query = "SELECT COALESCE(MAX(ORD_DET_NBR),0)+1 AS NEW_NBR FROM CMP.RTL_ORD_DET";
            $result = mysql_query($query);
            $row = mysql_fetch_assoc($result);
            $OrdDetNbr = $row['NEW_NBR'];
            $query = "INSERT INTO CMP.RTL_ORD_DET (ORD_DET_NBR) VALUES (" . $OrdDetNbr . ")";
            $result = mysql_query($query);
            $create = "CRT_TS=CURRENT_TIMESTAMP,CRT_NBR=" . $_SESSION['personNBR'] . ",";

            if ( ! empty($WifiNbr)) {
                $query = "UPDATE CMP.WIFI_VCHR SET ISU_TS=CURRENT_TIMESTAMP,
                      ISU_NBR=" . $_SESSION['personNBR'] . ",
                      REF_NBR=" . $OrdDetNbr . ',
                      REF_TYP="ORD"
                      WHERE WIFI_NBR=' . $WifiNbr;
                mysql_query($query);
            }
        }

        //Process child row
        if ($OrdDetNbrPar != "") {
            $childRow = "ORD_DET_NBR_PAR=" . $OrdDetNbrPar . ",";
        }

                    // Validate Form data
                    $LIST_OF_VAR = [
                        'ORD_BEG_DTE',
                        'ORD_END_DTE',
                        'DUE_DTE',
                        'DUE_TME',
                        'ORD_BEG_TME',
                        'ORD_END_TME',
                        'DUE_TS_TME',
                    'SLS_PRSN_NBR'
                    ];
        
                    foreach ($LIST_OF_VAR as $var) {
                        $name = str_replace('_', '', ucwords(strtolower($var), '_'));
                        //print_r($name);
                        global ${$name};
                        ${$name} = ($_POST[$var] == "") ? "NULL" : mysql_escape_string($_POST[$var]);
                    }
                    
                    if($OrdTtl == 'NULL'){$OrdTtl = '';}
                    $PuTS = $CmpTS = "";
                    $DueTS = $DueDte . " " . $DueTme;
                    $OrdTS = $OrdDte . " " . $OrdTme;
                    $OrdBegTS = $OrdBegDte . " " . $OrdBegTme;
                    $OrdEndTS = $OrdEndDte . " " . $OrdEndTme;
                    $ActgType = 0;

                    $ord_beg_dte = $_POST['ORD_BEG_DTE'];
                    $ord_end_dte = $_POST['ORD_END_DTE'];

                    $ord_beg_hr = $_POST['ORD_BEG_HR'];
                    $ord_beg_min = $_POST['ORD_BEG_MIN'];
                    $ord_end_hr = $_POST['ORD_END_HR'];
                    $ord_end_min = $_POST['ORD_END_MIN'];

                    $ord_beg_tme = $ord_beg_dte . ' ' . str_pad($ord_beg_hr, 2, '0', STR_PAD_LEFT) . ':' . str_pad($ord_beg_min, 2, '0', STR_PAD_LEFT) . ':00';
                    $ord_end_tme = $ord_end_dte . ' ' . str_pad($ord_end_hr, 2, '0', STR_PAD_LEFT) . ':' . str_pad($ord_end_min, 2, '0', STR_PAD_LEFT) . ':00';

        $query = "UPDATE CMP.RTL_ORD_DET
                SET ORD_NBR=" . $OrdNbr . ",
                    BEG_TS='" . $ord_beg_tme . "', 
                    END_TS='" . $ord_end_tme . "',
                    ORD_Q=" . $OrdQ . ",
                    DET_TTL='" . mysql_real_escape_string($_POST['DET_TTL']) . "',
                    FIL_LOC='" . mysql_real_escape_string($_POST['FIL_LOC']) . "',
                    FIL_ATT='" . $_FILES['FIL_ATT']['name'] . "',
                    FEE_MISC=" . $FeeMisc . ",
                    DISC_PCT=" . $DiscPct . ",
                    DISC_AMT=" . $DiscAmt . ",
                    TOT_SUB=" . $TotSub . ",
                    PRC=" . $Prc . ",
                    ORD_TYP='" . $OrdTyp . "',
                    RM_NBR=" . $RmNbr . ",
                    TBL_NBR=" . $MejaNbr . ",
                    UPD_TS=CURRENT_TIMESTAMP," . $childRow . "
                    UPD_NBR=" . $_SESSION['personNBR'] . "
                    WHERE ORD_DET_NBR=" . $OrdDetNbr;
        // echo "<pre>" . $query . "</pre>";
        $result = mysql_query($query);
        $changed = true;

        //Notif success
        if ($addNew) {
            $notif_success = "Data tersimpan";
        } else {
            if ($changed) {
                $notif_success = "Perubahan tersimpan";
            }
        }
    } while (0);
}

$upload_temp_loc = $_FILES['FIL_ATT']['tmp_name'];
if ( ! is_dir("creativehub\\")) {
    mkdir("creativehub\\", 0770);
}

if (is_uploaded_file($upload_temp_loc)) {
    if (file_exists("creativehub\\" . $OrdDetNbr)) {
        unlink("creativehub\\" . $OrdDetNbr);
    }
    move_uploaded_file($upload_temp_loc, "creativehub\\" . $OrdDetNbr);
}

//Take care the where syntax for javascript
if ($where == "") {
    $where = "AND CO_NBR=0";
}
$where = "' " . $where . "'";

$query = "SELECT DET.ORD_DET_NBR,
                DET.ORD_NBR,
                DET.ORD_Q,
                DET.DET_TTL,
                DET.FIL_LOC,
                DET.FIL_ATT,
                DET.FEE_MISC,
                DET.DISC_PCT,
                DET.DISC_AMT,
                DET.TOT_SUB,
                DET.PRC,
                DET.ORD_TYP,
                DET.RM_NBR,
                DET.TBL_NBR,
                CAT.CAT_ID,
                WIFI.WIFI_UNM,
                WIFI.WIFI_PWD
            FROM CMP.RTL_ORD_DET DET
            LEFT JOIN  CMP.RTL_ORD_TYP TYP ON TYP.RTL_ORD_TYP = DET.ORD_TYP
            LEFT JOIN  CMP.RTL_ORD_TYP_CAT CAT ON TYP.CAT_ID = CAT.CAT_ID
            LEFT JOIN CMP.WIFI_VCHR WIFI ON WIFI.REF_NBR = DET.ORD_DET_NBR
            WHERE DET.ORD_DET_NBR=" . $OrdDetNbr;
//echo $query;
$result = mysql_query($query);
$row = mysql_fetch_array($result);

?>
<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" type="text/css" media="screen" href="css/screen.css"/>
        <script type="text/javascript" src="framework/functions/default.js"></script>

        <link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="framework/combobox/chosen.css">
        <script src="framework/database/jquery.min.js" type="text/javascript"></script>
        <script src="framework/combobox/chosen.jquery.js" type="text/javascript"></script>
        <script>var jq = jQuery.noConflict()</script>
        <script>if (top.Pace && !top.Pace.running) top.Pace.restart()</script>
    
        <link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />

        <script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4-core.js"></script>
        <script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4.4-more.js"></script>
        <script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.js"></script>
        
        

        <style>
            span.fa-times {
                margin-left: 10px;
            }

            span.fa-times:hover {
                color: red;
            }

            form {
                width: 400px;
            }

            form input, form select {
                width: 100%;
                margin-bottom: 0;
            }

            tr.upper-sec {
                display: table-row;
            }

            form tr:nth-of-type(1) td:nth-of-type(1) {
                // vertical-align: top;
            }

            form td {
                vertical-align: bottom;
                text-align: left;
                height: 30px;
            }

            form td span#CATEGORY {
                color: black;
                /*font-weight: bold;*/
            }

            form td input#RTL_ORD_PRC,
            form td input#TOT_SUB,
            form td input#ORD_Q {
                /*border: none;*/
                width: 100px;
                /*font-weight: bold;*/
                padding: 0;
                background: white;
            }

            form td input#FEE_MISC,
            form td input#DISC_AMT {
                width: 100px;
            }

            /*td input#ORD_Q,*/
            form td input#DISC_PCT {
                width: 6ch;
            }

            form td input#FIL_ATT {
                height: 0;
                width: 0;
                display: inline;
                float: right;
                margin: -10px 0 0;
            }

            form td input#TOT_SUB {
                /*font-size: 1.2rem;*/
            }

            form select#DURASI,
            form #DURASI_chosen {
                display: none;
            }

            #voucher {
                padding: 15px;
            }

            #voucher thead {
                background: #eee;
            }

            #voucher thead td {
                border-bottom: 1px solid #444;
            }

            #voucher table {
                border: 1px solid #444;
            }
                    /* Form upper part */
                    #upper {
                width: 100%;
                display: grid;
                grid-template-columns: 1fr 3fr;
                gap: 10px;
                grid-auto-rows: minmax(100px, auto);
            }

            .left-col {
                grid-column: 1 / 2;
                display: grid;
                grid-template-columns: 1fr 1fr;
                column-gap: 0;
            }

            .left-col input {
                max-width: 200px;
                display: inline-block;
                height: 2em;
            }

            .left-col #ORD_BEG_TME #ORD_END_TME {
                width: 70px;
            }

            .left-col label {
                display: block;
            }

            .left-col .tanggal,
            .left-col .waktu {
                grid-column: auto;
                white-space: nowrap;
            }

            .left-col .tanggal label,
            .left-col .tanggal input {
                max-width: 10ch;
            }

            .left-col .waktu {
                text-align: left;
                // padding-left: 15px;
            }

            .left-col .waktu .select-time-wrapper {
                height: 2em;
                margin-bottom: 10px;
                padding: 3px 2px 2px 2px;
            }

            .left-col .waktu .select-time-wrapper .chosen-single {
                padding-left: 0;
            }

            .left-col .waktu .select-time-wrapper .chosen-single span {
                margin-right: 15px;
            }

            .left-col .waktu .select-time-wrapper .hour,
            .left-col .waktu .select-time-wrapper .minutes {
                width: 50px;
            }
        </style>
    </head>
    <body>
        <span class='fa fa-times toolbar close' title="Tutup"></span>
        <form enctype="multipart/form-data" action="" method="post">
            <input name="ORD_DET_NBR" id="ORD_DET_NBR" value="<?php echo $OrdDetNbr ?>" type="hidden"/>
            <table>
                <tr>
                    <td>
                        <label for="ORD_TYP">Jenis Layanan</label>
                    </td>
                    <td>
                        <select name="ORD_TYP" id="ORD_TYP" class="chosen-select">
                            <?php
                            $query = "SELECT RTL_ORD_TYP, RTL_ORD_DESC, CAT_ID FROM CMP.RTL_ORD_TYP ORDER BY CAT_ID";
                            $result = mysql_query($query);
                            $prevCat = null;
                            while ($typ = mysql_fetch_array($result)) {
                                if ($prevCat !== $typ['CAT_ID']) {
                                    if ($prevCat !== null) {
                                        echo "</optgroup>";
                                    }
                                    echo "<optgroup label='" . getCatName($typ['CAT_ID']) . "'>";
                                }
                                $selected = ($row['ORD_TYP'] == $typ['RTL_ORD_TYP']) ? "selected" : "";
                                echo "<option value='" . $typ['RTL_ORD_TYP'] . "' " . $selected . ">" . $typ['RTL_ORD_DESC'] . "</option>";
                                $prevCat = $typ['CAT_ID'];
                            }
                            if ($prevCat !== null) {
                                echo "</optgroup>";
                            }
                            function getCatName($catId)
                            {
                                $query = "SELECT CATEGORY FROM CMP.RTL_ORD_TYP_CAT WHERE CAT_ID=" . $catId;
                                $result = mysql_query($query);
                                $row = mysql_fetch_array($result);
                                return $row['CATEGORY'];
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <td>Ruangan</td>
                    <td>
                        <select name="RUANG_NBR" class="chosen-select">
                            <?php
                            genCombo("SELECT * FROM CMP.ROOM", 0, 1, $row['RM_NBR'], "Pilih Ruangan")
                            ?>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <td>Meja</td>
                    <td>
                        <select name="MEJA_NBR" class="chosen-select">
                            <?php
                            genCombo("SELECT * FROM CMP.TABEL", 0, 1, $row['TBL_NBR'], "Pilih Meja")
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="ORD_BEG_DTE">Tanggal Mulai</label>
                    </td>
                    <td>
                        <input type="text" name="ORD_BEG_DTE" id="ORD_BEG_DTE" style="width:95px;"
                            value="<?php echo $BegDt == "" ? "" : parseDate($BegDt) ?>" required/>
                        <script>
                            new CalendarEightysix('ORD_BEG_DTE', {
                                'offsetY': -5,
                                'offsetX': 2,
                                'format': '%Y-%m-%d',
                                'prefill': false,
                                'slideTransition': Fx.Transitions.Back.easeOut,
                                'draggable': true
                            });
                        </script>

                        <input type="hidden" name="ORD_BEG_TME" id="ORD_BEG_TME"/>
                        <select class="hour chosen-select" name="ORD_BEG_HR" style="width: 55px;">
                            <?php for ($x = 0; $x <= 23; $x++): ?>
                                <option value="<?php echo $x ?>" <?php echo (parseHour($BegDt) == $x) ? "selected" : "" ?>>
                                    <?php echo str_pad($x, 2, "0", STR_PAD_LEFT) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <select class="minutes chosen-select" name="ORD_BEG_MIN" style="width: 55px;">
                            <?php for ($x = 0; $x <= 60; $x += 5): ?>
                                <option value="<?php echo $x ?>" <?php echo (parseMinute($BegDt) == $x) ? "selected" : "" ?>>
                                    <?php echo str_pad($x, 2, "0", STR_PAD_LEFT) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td>
                        <label for="ORD_END_DTE">Tanggal Selesai</label>
                    </td>
                    <td>
                        <input type="text" name="ORD_END_DTE" id="ORD_END_DTE" style="width:95px;"
                            value="<?php echo $EndDt == "" ? "" : parseDate($EndDt) ?>" required/>
                        <script>
                            new CalendarEightysix('ORD_END_DTE', {
                                'offsetY': -5,
                                'offsetX': 2,
                                'format': '%Y-%m-%d',
                                'prefill': false,
                                'slideTransition': Fx.Transitions.Back.easeOut,
                                'draggable': true
                            });
                        </script>

                        <input type="hidden" name="ORD_END_TME" id="ORD_END_TME"/>
                        <select class="hour chosen-select" name="ORD_END_HR" style="width: 55px;">
                            <?php for ($x = 0; $x <= 23; $x++): ?>
                                <option value="<?php echo $x ?>" <?php echo (parseHour($EndDt) == $x) ? "selected" : "" ?>>
                                    <?php echo str_pad($x, 2, "0", STR_PAD_LEFT) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <select class="minutes chosen-select" name="ORD_END_MIN" style="width: 55px;">
                            <?php for ($x = 0; $x <= 60; $x += 5): ?>
                                <option value="<?php echo $x ?>" <?php echo (parseMinute($EndDt) == $x) ? "selected" : "" ?>>
                                    <?php echo str_pad($x, 2, "0", STR_PAD_LEFT) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </td>
                </tr>
            
                <tr>
                    <td>Harga</td>
                    <td>
                        <input id="PRC" name="PRC" value="<?php echo $row['PRC'] ?>" type="hidden"/>
                        <input id="RTL_ORD_PRC" name="RTL_ORD_PRC" value="<?php echo number_format($row['PRC'], 0, ",", ".") ?>"
                            type="text" disabled/>
                    </td>
                </tr>

                <tr>
                    <td>Jumlah</td>
                    <td>
                        <input id="ORD_Q" name="ORD_Q" value="<?php echo $row['ORD_Q'] ?: 1 ?>" type="number" min="1"/>
                    </td>
                </tr>
                
                <tr>
                    <td>Deskripsi</td>
                    <td><input id="DET_TTL" name="DET_TTL" value="<?php echo $row['DET_TTL'] ?>" type="text"
                            autocomplete="off"/></td>
                </tr>
                
                <tr>
                    <td>Lokasi file</td>
                    <td><input id="FIL_LOC" name="FIL_LOC" value="<?php echo $row['FIL_LOC'] ?>" type="text"
                            autocomplete="off"/></td>
                </tr>
                
                <tr>
                    <td>Lampiran</td>
                    <td>
                        <div class='browse'>
                            Browse ...
                        </div>
                        <span><?php echo $row['FIL_ATT'] ?></span>
                        <input class="browse" id="FIL_ATT" name="FIL_ATT" type="file" tabindex=-1 accept="image/*, .pdf"/>
                    </td>
                </tr>
                
                <tr class="upper-sec">
                    <td>Discount</td>
                    <td>
                        <input id="DISC_PCT" name="DISC_PCT" value="<?php echo $row['DISC_PCT'] ?>" type="number" min="0"
                            max="100"
                            autocomplete="off"/> %
                        atau
                        <input id="DISC_AMT" name="DISC_AMT" value="<?php echo number_format($row['DISC_AMT'], 0, ",", ".") ?>"
                            type="text"
                            autocomplete="off"/>
                    </td>
                </tr>
                
                <tr class="upper-sec">
                    <td>Spot</td>
                    <td>
                        <input id="FEE_MISC" name="FEE_MISC" value="<?php echo number_format($row['FEE_MISC'], 0, ",", ".") ?>"
                            type="text"
                            autocomplete="off"/>
                    </td>
                </tr>
                <tr class="upper-sec">
                    <td>Sub total</td>
                    <td>
                        <input id="TOT_SUB" name="TOT_SUB" value="<?php echo number_format($row['TOT_SUB'], 0, ",", ".") ?>"
                            type="text" readonly/>
                    </td>
                </tr>
            </table>

            <?php
            if (@$_GET['readonly'] != 1) { ?>
                <input class="process" type="submit" value="<?php echo ($addNew) ? 'Tambah' : 'Simpan' ?>"/>
                <?php
            } ?>
        </form>
        
        <?php
        if ( ! empty ($row['WIFI_UNM']) && false): ?>
            <div id="voucher">
                <h4>Voucher Wi-Fi</h4>
                <table>
                    <thead>
                    <tr>
                        <td>Username</td>
                        <td>Password</td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><?php echo $row['WIFI_UNM'] ?></td>
                        <td><?php echo $row['WIFI_PWD'] ?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        <?php
        endif;
        ?>

        <?php
        /* Queries for javascript */
        $query = 'SELECT * FROM CMP.RTL_ORD_TYP_CAT';
        $result = mysql_query($query);
        $price = [];
        $category = [];
        while ($rowd = mysql_fetch_assoc($result)) {
            $cat = $rowd['CAT_ID'];
            $cat_name = $rowd['CATEGORY'];
            $result2 = mysql_query('SELECT * FROM CMP.RTL_ORD_TYP WHERE CAT_ID=' . $cat);
            while ($typ = mysql_fetch_assoc($result2)) {
                $category[$cat][] = ['value' => $typ['RTL_ORD_TYP'], 'text' => $typ['RTL_ORD_DESC']];
                $price[$typ['RTL_ORD_TYP']] = $typ['RTL_ORD_PRC'];
            }
        }
        ?>

        <script type="text/javascript">
        // Check browser ES version
        const ES2020 = !!''.replaceAll
        const ES2021 = !![].at

        if (ES2021) {
            getInt = id => parseInt((window[id]?.value.replaceAll('.', '') ?? 0) || '0')
            getFloat = id => parseFloat((window[id]?.value.replaceAll('.', '') ?? 0) || '0')
        } else {
            // Babel.js transpilation to ES5
            getInt = function getInt (id) {
            var _window$id$value$repl, _window$id
            return parseInt(((_window$id$value$repl = (_window$id = window[id]) === null || _window$id === void 0
                ? void 0
                : _window$id.value.replaceAll('.', '')) !== null && _window$id$value$repl !== void 0
                ? _window$id$value$repl
                : 0) || '0')
            }
            getFloat = function getFloat (id) {
            var _window$id$value$repl2, _window$id2
            return parseFloat(((_window$id$value$repl2 = (_window$id2 = window[id]) === null || _window$id2 === void 0
                ? void 0
                : _window$id2.value.replaceAll('.', '')) !== null && _window$id$value$repl2 !== void 0
                ? _window$id$value$repl2
                : 0) || '0')
            }
        }
        ribuan = str => parseInt(str.toString().replaceAll('.', ''), 10).toLocaleString('id-ID')
        calcPay = false
        const price = JSON.parse('<?=json_encode($price)?>')
        const category = JSON.parse('<?=json_encode($category)?>')

        // Refresh tripane
        if (top?.content?.contentWindow.location.pathname === '/creativehub-edit.php') {
            top?.content?.contentWindow?.refreshEditList()
        } else {
            top?.content?.contentWindow?.rightpane?.contentWindow.refreshEditList()
        }

        (function ($) {
            $(document).ready(function () {
            $('.chosen-select').chosen()

            const calcPay = function () {
                let price = $('#RTL_ORD_PRC').val()
                let res = (getInt('RTL_ORD_PRC') * getInt('ORD_Q'))
                + getInt('FEE_MISC') - getInt('DISC_AMT')
                $('#TOT_SUB').val(ribuan(res))
            }

            const hideTr = function (indexes) {
                $.each(indexes, function (i, val) {
                $('tbody tr').eq(val).hide()
                })
            }

            const showTr = function (indexes) {
                $.each(indexes, function (i, val) {
                $('tbody tr').eq(val).show()
                })
            }

            // Category
            // const syncCategory = function () {
            //   let CAT = $('#ORD_TYP option:checked').parent().attr('label')
            //   $('#CATEGORY').text(CAT)
            // }
            // syncCategory()

            $('div.browse').on('click', function (e) {
                $('#FIL_ATT').trigger('click')
            })

            $('#DISC_PCT').on('blur change keyup', function () {
                let value = this.value
                if (value === '' || Number.isNaN(value)) this.value = 0
                let disc = parseInt(getInt('RTL_ORD_PRC') * getInt('ORD_Q') * getInt('DISC_PCT') / 100)
                $('#DISC_AMT').val(disc).trigger('change')
                calcPay()
            })

            $('#DISC_AMT').on('blur change keyup', function () {
                let value = this.value
                if (value.startsWith('0')) {
                this.value = value.slice(1)
                }
                if (value === '' || Number.isNaN(value)) this.value = 0
                if ($('#RTL_ORD_PRC').val()) {
                let totalDiscount = parseInt(getInt('DISC_AMT') * 100 / (getInt('RTL_ORD_PRC') * getInt('ORD_Q')))
                $('#DISC_PCT').val(totalDiscount)
                }
                if (!isNaN(value)) {
                this.value = ribuan(value)
                calcPay()
                } else {
                this.value = ''
                }
                calcPay()
            })

            $('#FEE_MISC').on('blur change keyup', function () {
                let value = this.value
                if (value.startsWith('0')) {
                value = value.slice(1)
                }
                if (value === '' || Number.isNaN(value)) value = 0
                if (!isNaN(value)) {
                this.value = ribuan(value)
                calcPay()
                } else {
                this.value = ''
                }
            })

            syncCategory = function (CAT) {
                let type = $('#ORD_TYP')
                if (!!CAT) {
                type.empty()
                console.log(category[CAT])
                $.each(category[CAT], function (i, item) {
                    type.append($('<option>', {
                    value: item.value,
                    text: item.text,
                    }))
                })
                type.trigger('chosen:updated')
                type.change()
                }
            }
            syncCategory($('#CAT_ID').val())
            // Initial value
            $('#ORD_TYP').val('<?php echo $row['ORD_TYP'] ?>').trigger('chosen:updated')
            if ($('#CAT_ID').val() == '<?php echo $wifiCategoryNbr ?>') {
                hideTr([1, 2, 4, 6, 7])
            }

            // Change dropdown as the Category changed.
            // Show diffrent dropdown text for WIFI
            $('#CAT_ID').on('blur change keyup', function () {
                let CAT = this.value
                if (CAT == '<?php echo $wifiCategoryNbr ?>') {
                hideTr([1, 2, 4, 6, 7])
                } else {
                showTr([1, 2, 3, 4, 5, 6, 7, 8, 9])
                }
                syncCategory(CAT)
            })

            $('#ORD_TYP').on('blur change keyup', function () {
                let TYP = this.value
                $('#RTL_ORD_PRC').val(ribuan(price[TYP]))
                $('#PRC').val(price[TYP])
                $('#DISC_PCT').trigger('change')
                calcPay()
                $('.notif-warning').hide()
            })

            $('#ORD_Q').on('blur change keyup', function () {
                $('#DISC_PCT').trigger('change')
                calcPay()
            })

            /* Form submit */
            $('form').on('submit', function (e) {
                if ($('#RTL_ORD_PRC').val() == '') {
                $('.notif-warning').text('Silahkan pilih layanan!').show()
                return false
                }
                // Mark edit form as dirty.
                top?.content?.contentWindow?.rightpane?.contentWindow.makeFormDirty()
            })

            /* Close */
            $('.fa.close').on('click', slideFormOut)
            $(top.fade).on('click', slideFormOut)

            /* notif, show only when there is text in it. */
            $('.notif').each(function () {
                if ($(this).text().trim().length) {
                $(this).show()
                let el = this
                setTimeout(function () {
                    $(el).hide()
                }, 10000)
                }
            })
            $('button.btn-close').on('click', function () {
                $('.notif').hide()
            })
            })
        })(jQuery)
        </script>
    </body>
</html>