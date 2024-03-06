<?php
include "framework/database/connect.php";
include "framework/functions/default.php";
include "framework/security/default.php";
include "framework/functions/dotmatrix.php";
date_default_timezone_set("Asia/Jakarta");

/* Data for ajax request
 * Used by chosen-ajax library in creativehub-edit.php
 */
if ( ! empty($_POST['id']) && ! empty($_POST['search'])) {
    $LIMIT = 24;

    $search_term = mysql_escape_string($_POST['search']);
    $search_id = mysql_escape_string($_POST['id']);
    if ($search_term == 'undefined') {
        return;
    }

    if ($search_id == "BUY_PRSN_NBR") {
        $query = "SELECT PRSN_NBR AS id, CONCAT(NAME,' ',MBR_NBR,' ',ADDRESS,' ',CITY_NM) AS text
                FROM CMP.PEOPLE PPL INNER JOIN CMP.CITY CIT ON PPL.CITY_ID=CIT.CITY_ID 
                WHERE PPL.DEL_NBR=0 AND PPL.APV_F=1
                AND NAME LIKE '%" . $search_term . "%'
                ORDER BY 2 LIMIT " . $LIMIT;
    } elseif (in_array($search_id, ['BIL_CO_NBR', 'BUY_CO_NBR', 'CHB_CO_NBR'])) {
        $query = "SELECT CO_NBR AS id, CONCAT(NAME,' ',ADDRESS,' ',CITY_NM) AS text
                FROM CMP.COMPANY COM INNER JOIN CMP.CITY CIT ON COM.CITY_ID=CIT.CITY_ID 
                WHERE COM.DEL_NBR=0 AND COM.APV_F=1
                AND NAME LIKE '%" . $search_term . "%'
                OR ADDRESS LIKE '%" . $search_term . "%'
                ORDER BY 2 LIMIT " . $LIMIT;
    } else {
        die();
    }

    $data = [
        'results'    => [],
        'pagination' => ['more' => false],
        // 'query' => $query,
    ];

    $result = mysql_query($query);
    while ($row = mysql_fetch_assoc($result)) {
        $data['results'][] = $row;
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit();
}
// -- End of ajax response --

$OrdNbr 		= mysql_escape_string($_GET['ORD_NBR']);
$type 			= mysql_escape_string($_GET['TYP']);
$stt 			= mysql_escape_string($_GET['STT']);
$origin 		= mysql_escape_string($_GET['ORGN']);
$OrdDetNbrStr 	= mysql_escape_string($_GET['ORD_DET_NBR']);
$new 			= (bool)$_GET['NEW'];
$changed 		= (bool)$_GET['CHANGED'];
$statuschanged = (bool)$_GET['STTCHANGED'];

/* FIXME: Security Clearance */
$Security 	= getSecurity($_SESSION['userID'], "DigitalPrint");
$UpperSec 	= getSecurity($_SESSION['userID'], "Executive");
$CashSec 	= getSecurity($_SESSION['userID'], "Finance");
$Acc 		= getSecurity($_SESSION['userID'], "Accounting");

/* Process changes here (POST request) */
if ($_POST['ORD_NBR'] != "") {
    $OrdNbr = $_POST['ORD_NBR'];

    $create = "";

    // Validate Form data
    $LIST_OF_VAR = [
        'ORD_NBR',
        'ORD_BEG_DTE',
        'ORD_END_DTE',
        'DUE_DTE',
        'DUE_TME',
        'ORD_BEG_TME',
        'ORD_END_TME',
        'DUE_TS_TME',
        'REF_NBR',
        'ORD_TTL',
        'BUY_PRSN_NBR',
        'BUY_CO_NBR',
        'BIL_CO_NBR',
        'CNS_CO_NBR',
        'ORD_STT_ID',
        'TAX_APL_ID',
        'TOT_SUB',
        'FEE_MISC',
        'TAX_AMT',
        'TOT_AMT',
        'TOT_REM',
        'TAX_IVC_NBR',
        'CMP_DTE',
        'PU_DTE',
        'PU_TME',
        'TAX_IVC_DTE',
        'TAX_IVC_NBR',
        'HISTORY',
        'SPC_NTE',
        'CHB_CO_NBR'
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


    //Process add new head
    if ($OrdNbr == -1) {
        $query = "SELECT COALESCE(MAX(ORD_NBR),0)+1 AS NEW_NBR FROM CMP.RTL_ORD_HEAD";
        $result = mysql_query($query);
        $row = mysql_fetch_array($result);
        $OrdNbr = $row['NEW_NBR'];

        $query = "INSERT INTO CMP.RTL_ORD_HEAD (ORD_NBR) VALUES (" . $OrdNbr . ")";
        $result = mysql_query($query);
        $new = true;
        $create = "CRT_TS=CURRENT_TIMESTAMP,CRT_NBR=" . $_SESSION['personNBR'] . ",";
    }

    // Process status journal
    if ( ! empty($_POST['ORD_STT_ID'])) {
        $statuschanged = false;
        $query = "SELECT ORD_STT_ID FROM CMP.RTL_ORD_HEAD WHERE ORD_NBR= " . $OrdNbr;
        $result = mysql_query($query);
        $row = mysql_fetch_assoc($result);
        if ($row['ORD_STT_ID'] != $OrdSttId) {
            $query = "INSERT INTO CMP.RTL_ORD_JRN (ORD_NBR, ORD_STT_ID, CRT_TS, CRT_NBR) 
				VALUES (" . $OrdNbr . ",'" . $OrdSttId . "', CURRENT_TIMESTAMP," . $_SESSION['personNBR'] . ")";
            //echo $query;
            $resultp = mysql_query($query);
            $statuschanged = true;
        }

        if ($OrdSttId == 'RD') { //Jadi
            $CmpTS = 'CMP_TS=CURRENT_TIMESTAMP,';
        } elseif ($OrdSttId == 'CP') { // Selesai
            $PuTS = 'PU_TS=CURRENT_TIMESTAMP,';
        }
        unset($result, $row);
    }

    $query = "UPDATE CMP.RTL_ORD_HEAD SET 
		ORD_TS='" . $OrdBegTS . "',
		ORD_STT_ID='" . $OrdSttId . "',
		BUY_PRSN_NBR=" . $BuyPrsnNbr . ",
		BUY_CO_NBR=" . $BuyCoNbr . ",
		BIL_CO_NBR=" . $BilCoNbr . ",
		CNS_CO_NBR=" . $CnsCoNbr . ",
		REF_NBR='" . $RefNbr . "',
		ORD_TTL='" . $OrdTtl . "',
		DUE_TS='" . $DueTS . "',
		FEE_MISC=" . $FeeMisc . ",
		TAX_APL_ID='" . $TaxAplId . "',
		TAX_AMT=" . $TaxAmt . ",
		TAX_IVC_NBR='" . $TaxIvcNbr . "',
		TAX_IVC_DTE=" . $TaxIvcDte . ",
		TOT_AMT=" . $TotAmt . ",
		PYMT_DOWN=" . 0 . ",
		PYMT_REM=" . 0 . ",
		TOT_REM=" . $TotRem . ",
		SPC_NTE='" . $SpcNte . "',
		CHB_CO_NBR= " . $ChbCoNbr . ",
		" . $CmpTS . "
		" . $PuTS . "
		" . $create . "
		UPD_TS = CURRENT_TIMESTAMP,
		UPD_NBR=" . $_SESSION['personNBR'] . ",
		ACTG_TYP=" . $ActgType . "
	WHERE ORD_NBR=" . $OrdNbr;
	//echo $query.'<br/>';
    //if($_SESSION['personNBR']==3) {echo $query.'<br/>'; }
    $result = mysql_query($query);
	//exit();
    $changed = true;

    // PRG Pattern
    // https://en.wikipedia.org/wiki/Post/Redirect/Get
    $qs = $_GET;
    if ($new) {
        $qs['NEW'] = true;
        $qs['ORD_NBR'] = $OrdNbr;
    }
    $qs['CHANGED'] = true;
    $qs['STTCHANGED'] = $statuschanged;
    $url = "//" . $_SERVER['HTTP_HOST'];
    $url .= parse_url($_SERVER['REQUEST_URI'])['path'];
    $url .= '?' . http_build_query($qs);

    header("HTTP/1.1 303 See Other");
    header("Location: " . $url);
} /* End POST Request /*

/* Queries */
$query = "SELECT HED.ORD_NBR,
       HED.ORD_TS,
       HED.ORD_STT_ID,
       HED.CRT_TS, 
       HED.CRT_NBR, 
       HED.UPD_TS, 
       HED.UPD_NBR,
       HED.BUY_CO_NBR,
       HED.BIL_CO_NBR, 
       HED.ACTG_TYP, 
       HED.REF_NBR, 
       HED.ORD_TTL, 
       HED.DUE_TS, 
       HED.FEE_MISC, 
       HED.TAX_APL_ID, 
       HED.TAX_AMT, 
       HED.TAX_IVC_DTE, 
       HED.TOT_AMT, 
       HED.PYMT_DOWN, 
       HED.PYMT_REM, 
       HED.TOT_REM, 
       HED.CMP_TS, 
       HED.SPC_NTE,
       HED.BUY_PRSN_NBR,
       HED.CHB_CO_NBR,
       HED.PU_TS,
       HED.IVC_PRN_CNT,
    PPL.NAME AS NAME_PPL,
    CONCAT(PPL.NAME,' ',PPL.MBR_NBR,' ',PPL.ADDRESS,' ',CIT.CITY_NM) AS NAME_ADDR,
    CONCAT(BUY.NAME,' ',BUY.ADDRESS,' ',BUY_CIT.CITY_NM) AS NAME_BUY_ADDR,
    CONCAT(BIL.NAME,' ',BIL.ADDRESS,' ',BIL_CIT.CITY_NM) AS NAME_BIL_ADDR,
    CONCAT(CHB.NAME,' ',CHB.ADDRESS,' ',CHB_CIT.CITY_NM) AS NAME_CHB_ADDR,
    PPL.CO_NBR AS PPL_CO_NBR,
    BUY.NAME AS NAME_CO,
    
    COALESCE((HED.TOT_AMT/HED.TAX_AMT)/100,0) AS TAX_PCT,
    
    CRT.NAME AS NAME_CRT,
    UPD.NAME AS NAME_UPD,
    COALESCE(BUY.CRDT_MAX,0) AS COM_CRDT_MAX,
    COALESCE(PPL.CRDT_MAX,0) AS PPL_CRDT_MAX,
    
    STT.ORD_STT_ORD,
    STT.ORD_STT_DESC,
    
    DET.TOT_SUB AS TOT_SUB
    FROM CMP.RTL_ORD_HEAD HED
    INNER JOIN CMP.RTL_ORD_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID
    LEFT OUTER JOIN (
        SELECT SUM(TOT_SUB) AS TOT_SUB, DET.ORD_NBR 
        FROM CMP.RTL_ORD_DET DET 
        WHERE DET.DEL_NBR = 0 
        AND DET.ORD_NBR =" . $OrdNbr . "
    ) DET ON DET.ORD_NBR = HED.ORD_NBR
    LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR
    LEFT OUTER JOIN CMP.CITY CIT ON PPL.CITY_ID=CIT.CITY_ID 
    LEFT OUTER JOIN CMP.COMPANY BUY ON HED.BUY_CO_NBR=BUY.CO_NBR
    LEFT OUTER JOIN CMP.CITY BUY_CIT ON BUY.CITY_ID=BUY_CIT.CITY_ID 
    LEFT OUTER JOIN CMP.COMPANY BIL ON HED.BIL_CO_NBR=BIL.CO_NBR 
    LEFT OUTER JOIN CMP.CITY BIL_CIT ON BIL.CITY_ID=BIL_CIT.CITY_ID
    LEFT OUTER JOIN CMP.COMPANY CHB ON HED.CHB_CO_NBR=CHB.CO_NBR 
    LEFT OUTER JOIN CMP.CITY CHB_CIT ON CHB.CITY_ID=CHB_CIT.CITY_ID 
    LEFT OUTER JOIN CMP.PEOPLE CRT ON HED.CRT_NBR=CRT.PRSN_NBR
    LEFT OUTER JOIN CMP.PEOPLE UPD ON HED.UPD_NBR=UPD.PRSN_NBR
    WHERE HED.ORD_NBR=" . $OrdNbr;
//echo "<pre>".$query;
if (intval($OrdNbr) > 0) {
    $row = mysql_fetch_assoc(mysql_query($query));
}

$query_jrn = "SELECT STT.ORD_STT_DESC,JRN.CRT_TS,NAME
                FROM CMP.RTL_ORD_JRN JRN 
                INNER JOIN CMP.RTL_ORD_STT STT ON JRN.ORD_STT_ID=STT.ORD_STT_ID 
                INNER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR=JRN.CRT_NBR
                WHERE JRN.ORD_NBR=" . $OrdNbr . " ORDER BY CRT_TS";
$result_jrn = mysql_query($query_jrn);

// FIXME: CreativeHub CO_NBR
$query_unit = "SELECT 
	CHB.CO_NBR, 
	CONCAT(CHB.NAME,' ',CHB.ADDRESS,' ',CIT.CITY_NM) AS NAME
FROM CMP.COMPANY CHB
LEFT OUTER JOIN CMP.CITY CIT ON CHB.CITY_ID=CIT.CITY_ID
WHERE CHB.CO_NBR=6284";
$res_unit = mysql_fetch_assoc(mysql_query($query_unit));

// notif success
if ($new) {
    $notif_success = "Nota berhasil dibuat";
}
if ($changed && ! $new) {
    $notif_success = "Perubahan berhasil disimpan";
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title>Creative Hub Form</title>
    <link rel="stylesheet" media="screen" href="css/screen.css"/>
    <link rel="stylesheet" href="css/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen"/>
    <script src="framework/mootools/mootools-latest.min.js"></script>
    <script src="framework/mootools/mootools-latest-more.js"></script>
    <script src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>
    <script src="framework/functions/default.js"></script>
    <script src="framework/database/jquery.min.js"></script>
    <!--    <script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>-->

    <script>var jq = jQuery.noConflict()</script>
    <script>if (top.Pace && !top.Pace.running) top.Pace.restart()</script>

    <link rel="stylesheet" href="framework/combobox/chosen.css">
    <link rel="stylesheet" media="screen" href="framework/alert/alert.css"/>

    <script src="framework/combobox/chosen.jquery.js" type="text/javascript"></script>
    <script src="framework/combobox/ajax-chosen/ajax-chosen.js" type="text/javascript"></script>

    <style type="text/css">
        /* Helper */
        .box * {
            border: 1px solid pink;
            box-sizing: border-box;
        }

        .show {
            display: block;
        }

        .hide {
            display: none !important;
        }

        html {
            scroll-behavior: smooth;
            scrollbar-width: thin;
        }

        body {
            height: max-content;
        }

        ::-webkit-scrollbar {
            width: 7px;
        }

        /* Track */
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        /* Handle */
        ::-webkit-scrollbar-thumb {
            background: #aaa;
        }

        /* Handle on hover */
        ::-webkit-scrollbar-thumb:hover {
            background: #888;
        }

        a:hover {
            text-decoration: none;
        }

        h2#NomorOrder {
            cursor: copy;
            width: max-content;
        }

        div.listable-btn:hover {
            background-color: #cbc8ee;
        }

        form select {
            background-color: white;
            margin: 0;
        }

       
        /* Form */
        form#mainForm {
            width: 100%;
            max-width: 765px;
            box-sizing: border-box;
            cursor: progress;
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
            max-width: 110px;
            display: inline-block;
            height: 2em;
        }

        .left-col #ORD_BEG_TME {
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
            text-align: center;
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

        .left-col .fullwidth {
            grid-column: 1/3;
        }

        .left-col .fullwidth input {
            max-width: 100%;
        }

        .right-col {
            grid-column: 2 / 3;
        }

        .right-col input {
            width: 100%;
        }

        .right-col input#ORD_TTL {
            line-height: 1.2;
        }

        .right-col .info {
            display: grid;
            grid-template-columns: repeat(9, 1fr);
            grid-row-gap: 10px;
            align-items: baseline;
        }

        .right-col .info label {
            grid-column: 1/3;
        }

        .right-col .info .chosen-select,
        .right-col .info .chosen-container,
        .right-col .info .chosen-ajax {
            grid-column: 3/10;
            max-width: 100%;
        }

        .right-col .info #ORD_STT_ID,
        #ORD_STT_ID_chosen,
        .right-col .info .chosen-container:nth-last-of-type(2) {
            grid-column: 3/5 !important;
        }

        .right-col .info label[for=TAX_APL_ID] {
            grid-column: 6/7;
        }

        .right-col .info #TAX_APL_ID,
        #TAX_APL_ID_chosen,
        .right-col .info .chosen-container:nth-last-of-type(1) {
            grid-column: 7/10 !important;
        }

        /* Form Footer */
        #footer {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-gap: 3em;
        }

        #footer .faktur {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        #footer .faktur-col {
            grid-column: auto;
        }

        #footer .faktur-col input {
            display: block;
        }

        #footer textarea {
            width: 100%;
        }

        #footer textarea#SPC_NTE {
            resize: vertical;
        }

        #footer .form-group {
            border-bottom: 1px solid #ddd;
            padding-left: 7px;
            clear: both;
            margin-bottom: 5px;
        }

        #footer .form-group label {
            padding: 3px 2px 3px 2px;
        }

        #footer .form-group input {
            margin-bottom: 0;
            float: right;
            text-align: right;
            padding-right: 10px;
            width: calc(40% - 10px);
        }

        #footer .form-group .listable-btn {
            float: right;
        }

        #footer .form-group span.listable-btn {
            float: none;
        }

        #footer .total .border {
            /*border: 1px solid;*/
        }

        #footer .total .border > .form-group:first-child {
            padding-top: 5px;
        }

        #footer .total .form-group:last-child {
            border-bottom: none;
        }

        .total label[for=TOT_REM],
        .total label[for=TOT_AMT],
        .total label[for=TND_AMT] {
            font-weight: bold;
            color: #3464bc;
        }

        .total .payment:nth-child(even) {
            /*background-color: #f6f6f6;*/
        }

        .total input {
            border-bottom: none;
        }

        #footer .form-group input.pad {
            padding-right: 28px;
            width: calc(40% - 28px);
        }

        #footer .form-group input#TAX_PCT {
            width: 6ch;
        }

        #footer legend {
            background-color: #eee;
            color: black;
            padding: 5px 10px;
        }

        /* Form Table `edit-list` */
        table#edit-table {
            background-color: white;
        }

        table#edit-table.listable-btn:hover {
            background-color: #cbc8ee;
        }

        table#edit-table tr.item > td {
            cursor: pointer;
            text-align: right;
        }

        table#edit-table tr.item td:nth-child(1) {
            text-align: center;
        }

        table#edit-table tr.item td:nth-child(2) {
            text-align: left;
        }

        table#edit-table tr.item:nth-child(even) {
            background-color: #f6f6f6;
        }

        table#edit-table th.listable:last-child {
            width: 10%;
        }

        table#edit-table tr.item td:last-child {
            text-align: center;
            padding-left: 2px;
            padding-right: 2px;
            white-space: nowrap
        }

        table#edit-table tr.item:hover {
            background-color: #e6e6e6;
        }

        table#edit-table tr.nodata > td {
            text-align: center;
            font-style: italic;
            color: #999;
        }

        table#edit-table tr.item-nest td:nth-child(1) {
            text-align: right;
        }

        table#edit-table tr.item-nest td:nth-child(2) {
            padding-left: 30px;
            /*text-align: center*/
        }

        table#edit-table tr.item-nest td:nth-child(3),
        table#edit-table tr.item-nest td:nth-child(4),
        table#edit-table tr.item-nest td:nth-child(5),
        table#edit-table tr.item-nest td:nth-child(6) {
            color: #bbb;
        }

        /* Hidden Form */
        form.hidden {
            display: none;
        }

        /* Deleted list */
        #deleted-list table {
            width: 100%;
            max-width: 765px;
            padding: 5px 10px 5px 10px;
        }

        #deleted-list table tr.nodata {
            text-align: center;
        }

        #deleted-list table tbody tr:not(.nodata) {
            text-decoration: line-through;
        }

        #deleted-list table tbody tr:not(.nodata) td:last-child,
        #deleted-list table thead tr th:last-child {
            display: none;
        }

        #deleted-list table tbody tr:nth-child(even) {
            background-color: #f6f6f6;
        }

        /* History */
        div#history {
            /*height: 100px;*/
        }

        div#history label {
            margin-left: 10px;
        }

        input[type=time]::-webkit-datetime-edit-ampm-field {
            display: none;
        }


        /* Flash */
        .nodata > td {
            border-radius: 3px;
            background: white;
            transition: background 0.6s, color 0.6s
        }

        .nodata.flash > td {
            background: red;
            color: white !important;
        }
    </style>
</head>
<body>
<div class="toolbar-only">
    <p class="toolbar-left">
        <a id="toolbar-hapus">
            <span title="Hapus" class="fa fa-trash toolbar"></span>
        </a>
    </p>
    <p class="toolbar-right">
        <a id="toolbar-pdf">
            <span title="PDF" class="fa fa-file-pdf-o toolbar"></span>
        </a>
        <span title="Cetak <?php echo ($row['IVC_PRN_CNT'] > 0) ? "Lagi" : "" ?>" class=" fa fa-print toolbar"
              id="toolbar-print"></span>
    </p>
</div>

<form id='mainForm' enctype="multipart/form-data" action="" method="post">
    <input id="ORD_NBR" name="ORD_NBR" type="hidden" value="<?php echo ($row['ORD_NBR'] == "") ? "-1" : $row['ORD_NBR'] ?>"/>

    <h3>Nota Penjualan</h3>
    <h2 id="NomorOrder"><?php echo ($row['ORD_NBR'] == "") ? "Baru" : $row['ORD_NBR'] ?></h2>
    <!-- Upper -->
    <div id="upper">
        <div class="left-col">
            <div class="tanggal">
                <label for="ORD_BEG_DTE">Tanggal Mulai</label>
                <input type="text" name="ORD_BEG_DTE" id="ORD_BEG_DTE"
                       value="<?php echo ($row['ORD_TS'] == "") ? "" : parseDate($row['ORD_TS']) ?>" required/>

                <label for="DUE_DTE">Tanggal Dijanjikan</label>
                <input type="text" name="DUE_DTE" id="DUE_DTE"
                       value="<?php echo ($row['DUE_TS'] == "") ? "" : parseDate($row['DUE_TS']) ?>" required/>
            </div>
            <div class="waktu">
                <label for="ORD_BEG_TME">Waktu</label>
                <input name="ORD_BEG_TME" id="ORD_BEG_TME" type="text"
                       value="<?php echo ($row['ORD_TS'] == "") ? "" : parseTime($row['ORD_TS']) ?>" required/>
                <div class='listable-btn'>
                    <span title="Waktu sekarang" class='fa fa-clock-o listable-btn'
                          onclick="document.id('ORD_BEG_TME').value=getCurTime();"></span>
                </div>

                <label for="DUE_TME">Waktu</label>
                <div class="select-time-wrapper">
                    <input type="hidden" name="DUE_TME" id="DUE_TME">
                    <select class="hour" name="DUE_HR">
                        <?php
                        for ($x = 0; $x <= 23; $x++): ?>
                            <option value="<?php echo $x ?>" <?php echo (parseHour($row['DUE_TS']) == $x) ? "selected"
                                : "" ?>>
                                <?php echo str_pad($x, 2, "0", 0) ?>
                            </option>
                        <?php
                        endfor; ?>
                    </select>
                    <select class="minutes" name="DUE_MIN">
                        <?php
                        for ($x = 0; $x <= 60; $x += 5): ?>
                            <option value="<?php echo $x ?>" <?php echo (parseMinute($row['DUE_TS']) == $x) ? "selected"
                                : "" ?>>
                                <?php echo str_pad($x, 2, "0", 0) ?>
                            </option>
                        <?php
                        endfor; ?>
                    </select>
                </div>
            </div>

            <div class="fullwidth">
                <label for="REF_NBR">No Referensi</label>
                <input name="REF_NBR" id="REF_NBR" type="text"
                       value="<?php echo ($row['REF_NBR'] == 'NULL') ? "" : $row['REF_NBR'] ?>" autocomplete="off"/>
            </div>

        </div>

        <div class="right-col">
            <label for="ORD_TTL">Judul</label>
            <input name="ORD_TTL" id="ORD_TTL" value="<?php echo htmlentities($row['ORD_TTL'], ENT_QUOTES) ?>"
                   autocomplete="off" spellcheck="false"/>

            <div class="info">
                <label for="BUY_PRSN_NBR">Nama Pembeli</label>
                <select name="BUY_PRSN_NBR" id="BUY_PRSN_NBR" class="chosen-select chosen-ajax" data-placeholder="Tunai">
                    <option value="<?php echo $row['BUY_PRSN_NBR'] ?: "" ?>"
                            selected><?php echo $row['NAME_ADDR'] ?: "Tunai" ?></option>
                </select>

                <label for="BUY_CO_NBR">Perusahaan Pembeli</label>
                <select name="BUY_CO_NBR" id="BUY_CO_NBR" class="chosen-select chosen-ajax" data-placeholder="Tunai">
                    <option value="<?php echo $row['BUY_CO_NBR'] ?: "" ?>"
                            selected><?php echo $row['NAME_BUY_ADDR'] ?: "Tunai" ?></option>
                </select>

                <label for="BIL_CO_NBR">Pihak Yang Ditagih</label>
                <select name="BIL_CO_NBR" id="BIL_CO_NBR" class="chosen-select chosen-ajax" data-placeholder="Sama dengan diatas">
                    <option value="<?php echo $row['BIL_CO_NBR'] ?: "" ?>"
                            selected><?php echo $row['NAME_BIL_ADDR'] ?: "Sama dengan diatas" ?>
                    </option>
                </select>

                <label for="CHB_CO_NBR">Unit Bisnis</label>
                <select name="CHB_CO_NBR" id="CHB_CO_NBR" class="chosen-select chosen-ajax" data-placeholder="--">
                    <?php
                    // FIXME: What should this column table be?
                    if ($OrdNbr == "-1"): ?>
                        <option value="<?php echo $res_unit['CO_NBR']; ?>"
                                selected><?php echo $res_unit['NAME']; ?></option>
                    <?php
                    else: ?>
                        <option value="<?php echo $row['CHB_CO_NBR']; ?>"
                                selected><?php echo $row['NAME_CHB_ADDR']; ?></option>
                    <?php
                    endif; ?>
                </select>

                <label for="ORD_STT_ID">Status</label>
                <select name="ORD_STT_ID" id="ORD_STT_ID" class="chosen-select">
                    <?php
                    $Q_ORD_STT_ID = "SELECT ORD_STT_ID, ORD_STT_DESC, ORD_STT_ORD FROM CMP.RTL_ORD_STT ORDER BY 3";
                    genCombo($Q_ORD_STT_ID, "ORD_STT_ID", "ORD_STT_DESC", $row["ORD_STT_ID"]) ?>
                </select>

                <label for="TAX_APL_ID">PPN</label>
                <select name="TAX_APL_ID" id="TAX_APL_ID" class="chosen-select">
                    <?php
                    $Q_TAX = "SELECT TAX_APL_ID, TAX_APL_DESC FROM CMP.TAX_APL ORDER BY SORT";
                    $TaxApl = ($row["TAX_APL_ID"] == "") ? "E" : $row["TAX_APL_ID"];
                    genCombo($Q_TAX, "TAX_APL_ID", "TAX_APL_DESC", $TaxApl); ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Listing -->
    <div id="edit-list" class="edit-list"></div>

    <!-- Footer -->
    <div id="footer">
        <div class="total">
            <div class="border">
                <!--<legend>Payment</legend>-->
                <div class="form-group">
                    <label for="TOT_SUB">Subtotal</label>
                    <input class="pad" name="TOT_SUB" id="TOT_SUB" value="<?php echo $row['TOT_SUB'] ?>" type="text"
                           readonly/>
                </div>
                <div class="form-group">
                    <label for="FEE_MISC">Biaya Tambahan</label>
                    <input class="pad" name="FEE_MISC" id="FEE_MISC"
                           value="<?php echo number_format($row['FEE_MISC'], 0, ",", ".") ?>"
                           type="text"
                           autocomplete="off"/>
                </div>

                <div class="form-group">
                    <label for="TAX_AMT">PPN</label>
                    <input class="pad" name="TAX_AMT" id="TAX_AMT"
                           value="<?php echo number_format($row['TAX_AMT'], 0, ",", ".") ?>"
                           type="text"
                           readonly/>
                    <input class="accounting" name="TAX_PCT" id="TAX_PCT" value="0" step="0.1" min="0" max="100"
                           type="number"/>
                </div>

                <div class="form-group">
                    <label for="TOT_AMT">Total nota</label>
                    <input class="pad" name="TOT_AMT" id="TOT_AMT" value="<?php echo $row['TOT_AMT'] ?>" type="text"
                           readonly/>
                </div>

                <!-- Payment AJAX-->
                <div id="pay"></div>
                <div class="form-group">
                    <label for="TND_AMT">Pembayaran</label>
                    <div id='SAVE_PAY' class='listable-btn' title="Simpan Pembayaran">
                        <span class='fa fa-plus listable-btn'></span>
                    </div>
                    <input name="TND_AMT" id="TND_AMT" value="" type="text" autocomplete="off"/>
                </div>

                <div class="form-group">
                    <label for="TOT_REM">Sisa</label>
                    <div class='listable-btn' id="RECALC" title="Hitung ulang">
                        <span class='fa fa-refresh listable-btn'></span>
                    </div>
                    <input name="TOT_REM" id="TOT_REM" value="<?php echo $row['TOT_REM'] ?>" type="text" readonly/>
                </div>
            </div>
        </div>
        <div class="faktur">
            <div class="faktur-col">
                <label for="TAX_IVC_NBR">No Faktur Pajak</label>
                <input name="TAX_IVC_NBR" id="TAX_IVC_NBR" value="<?php echo $row['TAX_IVC_NBR'] ?>" type="text"/>

                <label for="CMP_DTE">Tanggal Jadi</label>
                <input name="CMP_DTE" id="CMP_DTE"
                       value="<?php echo ($row['CMP_TS'] == "") ? "" : parseDate($row['CMP_TS']) ?>" type="text"
                       disabled/>

                <label for="PU_DTE">Tanggal Selesai </label>
                <input name="PU_DTE" id="PU_DTE"
                       value="<?php echo ($row['PU_TS'] == "") ? "" : parseDate($row['PU_TS']) ?>" type="text"
                       disabled/>
            </div>
            <div class="faktur-col">
                <label for="TAX_IVC_DTE">Tanggal Faktur Pajak</label>
                <input name="TAX_IVC_DTE" id="TAX_IVC_DTE"
                       value="<?php echo ($row['TAX_IVC_DTE'] == "") ? "" : parseDate($row['TAX_IVC_DTE']) ?>"
                       type="text"
                       autocomplete="off"/>

                <label for="CMP_TME">Waktu Jadi</label>
                <input name="CMP_TME" id="CMP_TME"
                       value="<?php echo ($row['CMP_TS'] == "") ? "" : parseTime($row['CMP_TS']) ?>" type="time"
                       disabled/>

                <label for="PU_TME">Waktu Selesai</label>
                <input name="PU_TME" id="PU_TME"
                       value="<?php echo ($row['PU_TS'] == "") ? "" : parseTime($row['PU_TS']) ?>" type="time"
                       disabled/>
            </div>
        </div>

        <div>
            <label for="SPC_NTE">Catatan</label><br/>
            <textarea name="SPC_NTE" id="SPC_NTE" spellcheck="false"
                      placeholder="Catatan..."><?php echo ($row['SPC_NTE'] == 'NULL') ? ""
                    : $row['SPC_NTE'] ?></textarea>
        </div>

        <div id="history">
            <label>History</label><br/>
            <div class="userLog">
                <?php echo $row['CRT_TS'] . ' ' . shortName($row['NAME_CRT']) . " membuat" ?>
                <br>
                <?php
                while ($row_jrn = mysql_fetch_array($result_jrn)) {
                    echo $row_jrn['CRT_TS'] . " " . shortName($row_jrn['NAME']) . " status: "
                        . strtolower($row_jrn['ORD_STT_DESC']) . "<br />\n";
                }
                ?>
                <?php echo $row['UPD_TS'] . ' ' . shortName($row['NAME_UPD']) . " ubah akhir" ?>
                <br>
            </div>
        </div>
    </div>
    <input class="process" type="submit" value="Simpan" title="Simpan form"/>
</form>


<!-- Deleted -->
<div id="deleted-list" class="deleted-list"></div>
<!-- Hidden form -->
<form id="pdf" method="get" action="creativehub-invoice-pdf.php" target="_self" class="hidden">
    <input type="hidden" name="ORD_NBR" value="">
</form>
<form id="unduh" method="post" action="creativehub-download.php" target="_blank" class="hidden">
    <input type="hidden" name="ORD_DET_NBR" value="">
</form>
<iframe id="pdfDocument" width="0" height="0" style="display: none"></iframe>
<script>
  printJS = function (url, callback) {
    const iframeElement = document.getElementById('pdfDocument')
    iframeElement.setAttribute('src', url)
    iframeElement.onload = () => {
      if (callback && typeof callback === 'function') callback()
      iframeElement.focus()
      iframeElement.contentWindow.print()
    }
  }
</script>
<script type="text/javascript">
  'use strict'

  window.onload = function () {
    (($) => {
      // MooTools IIFE
      // var $ = document.id

      const ord_nbr = $('ORD_NBR').value

      let defaults = {
        'offsetY': -5,
        'offsetX': 2,
        'format': '%Y-%m-%d',
        'slideTransition': Fx.Transitions.Back.easeOut,
        'draggable': true,
        'disallowUserInput': true,
      }

      window.calendarMulai = new CalendarEightysix('ORD_BEG_DTE', defaults)
      window.calendarSelesai = new CalendarEightysix('ORD_END_DTE', defaults)
      window.calendarDijanjikan = new CalendarEightysix('DUE_DTE', defaults)
      window.calendarFaktur = new CalendarEightysix('TAX_IVC_DTE', {
        ...defaults, 'alignX': 'left',
        'alignY': 'top', 'offsetX': -100, 'prefill': false,
      })
	  
	  /*
      if (ord_nbr == '-1') {
        let today = new Date
        calendarMulai.options.minDate = today
        calendarSelesai.options.minDate = today
        calendarDijanjikan.options.minDate = today

        calendarMulai.render()
        calendarSelesai.render()
        calendarDijanjikan.render()
      }
	  */
    })(document.id)

    // Normal Select
    jq('#ORD_STT_ID, #TAX_APL_ID, .hour, .minutes').each(function (i, el) {
      jq(el).chosen()
    })

    const ajaxChosenSetting = function (id) {
      return {
        type: 'POST',
        url: 'creativehub-edit.php',
        dataType: 'json',
        jsonTermKey: 'search',
        minTermLength: 1,
        data: { id: id },
      }
    }

    /* Config for chosen. */
    let config = {
      '.chosen-select': {},
      '.chosen-select-deselect': { allow_single_deselect: true },
      '.chosen-select-no-single': { disable_search_threshold: 10 },
      '.chosen-select-no-results': { no_results_text: 'Data tidak ketemu' },
      '.chosen-select-width': { width: '95%' },
    }
    for (let selector in config) {
      jq(selector).chosen(config[selector])
    }

    jq('.chosen-ajax').each(function (i, el) {
      let config = ajaxChosenSetting(el.id)
      jq(el).ajaxChosen(config, function (data) {
        let results = []
        if (data.results) {
          jq.each(data.results, function (i, val) {
            // someone add "element = element[0]" in ajax-chosen.js
            results.push([{ value: val.id, text: val.text }])
          })
        }
        return results
      })
    })

  }
</script>

<script type="text/javascript">
  'use strict'

  const getValById = id => {
    return (window[id]?.value ?? '0')
    // let el, val
    // return (val = (el = window[id]) === null || el === void 0 ? void 0 : el.value) !== null && val !== void 0
    //   ? val
    //   : '0'
  }
  const getInt = id => normal(getValById(id))
  const getFloat = id => parseFloat(getValById(id))
  let ribuan = str => {
    let n = str.toString().replaceAll('.', '')
    return (parseInt(n) ? parseInt(n) : 0).toLocaleString('id-ID')
  }
  const normal = str => Number(str.replaceAll('.', '').replaceAll(',', '.'))
  const roundDecimal = n => Number(Math.round(n.toString() + 'e1') + 'e-1') // satu dibelakang koma: 10.123 => 10.1

  const makeInputWrapper = (selector) => {
    const input = document.querySelector(selector)
    const get = () => normal(input.value)
    const set = (newVal) => {
      input.value = ribuan(newVal)
    }
    return { input, get, set }
  }

  const TOT_AMT = makeInputWrapper('#TOT_AMT')
  const TOT_SUB = makeInputWrapper('#TOT_SUB')
  const TOT_REM = makeInputWrapper('#TOT_REM')
  const TAX_AMT = makeInputWrapper('#TAX_AMT')

  function calcAmt () {
    let value = document.id('TAX_APL_ID').value
    TOT_AMT.set(getInt('TOT_NET') + getInt('FEE_MISC'))
    TOT_SUB.set(getInt('TOT_NET'))

    calcTax()

    if (value === 'A') { // Pertambahan
      TOT_AMT.set(getInt('TOT_NET') + getInt('FEE_MISC') + getInt('TAX_AMT'))
    }
    TOT_REM.set(getInt('TOT_AMT') - getInt('TOT_PAY') - getInt('TND_AMT'))
  }

  function setTaxPct () {
    //console.info('setTaxPct')
    //console.info(TAX_AMT.get())
    let TAX_PCT = document.id('TAX_PCT')
    if (!TAX_PCT.value) {
      TAX_PCT.value = parseFloat(getParam('tax', 'ppn')) * 100
    }
  }

  function resetTaxPct () {
    let value = document.id('TAX_APL_ID').value
    let TAX_PCT = document.id('TAX_PCT')
    if (!!TAX_AMT.get()) {
      if (value === 'I') {
        TAX_PCT.value = (getInt('TAX_AMT') / getInt('TOT_AMT')) * 100
      } else if (value === 'A') {
        TAX_PCT.value = (getInt('TAX_AMT') / (getInt('TOT_AMT') - getInt('TAX_AMT'))) * 100
      }
    }
  }

  function calcPct () {
    TAX_AMT.set(0)
    calcAmt()
  }

  function calcTax () {
    let value = document.id('TAX_APL_ID').value
    let TAX_PCT = document.id('TAX_PCT')
    //console.log(`calcTax, TAX_PCT: ${TAX_PCT.value}`)
    if (value === 'E') { // Tidak termasuk
      TAX_PCT.value = ''
      TAX_AMT.set(0)
      //console.log(`calcTax E, TAX_AMT: ${TAX_AMT.get}`)
    } else if (value === 'I') { // Termasuk
      setTaxPct()
      TAX_AMT.set(Math.round(getInt('TOT_AMT') * (getFloat('TAX_PCT') / 100)))
      //console.log(`calcTax I, TAX_PCT: ${TAX_PCT.value}`)
    } else if (value === 'A') { // Pertambahan
      setTaxPct()
      TAX_AMT.set(Math.round(getInt('TOT_AMT') * (getFloat('TAX_PCT') / 100)))
      //console.log(`calcTax A, TAX_PCT: ${TAX_PCT.value}`)
    }
  }

  /* Function collections */
  function createSpinner ($) {
    return $('<div/>').
      css('position', 'relative').
      css('height', '100%').
      append($('<div/>').
        addClass('spinner').
        css('position', 'absolute').
        css('margin', 'auto').
        css('top', '50%').
        css('left', '50%').
        append($('<div/>').addClass('double-bounce1')).
        append($('<div/>').addClass('double-bounce2')))
  }

  // asyncGetContent: getContent using jQuery ajax
  // with callback.
  const asyncGetContent = function (id, url, callback) {
    (function (id, url) {
      const $el = jq('#' + id)
      return jq.ajax({
        url: url,
        type: 'get',
        beforeSend: function () {
          const h = createSpinner(jq)
          $el.html(h)
        },
      })
    })(id, url).done(function (data) {
      jq('#' + id).html(data)
      if (typeof callback === 'function') callback()
    })
  }

  // Add dot on thousands
  function text_ribuan (e) {
    let el = e.target
    if (el.value !== '') {
      let d = ribuan(el.value)
      if (d !== 'NaN') {
        el.value = d
      } else {
        el.value = 0
      }
    }
  }

  function refreshLeftPane (goto) {
    if (goto === undefined) return
    if (goto === 'TOP') {
      reloadLeftPane(goto)
    } else if (goto !== '') {
      parent.leftpane?.contentWindow.updateInPlace(goto)
    }
  }

  function reloadLeftPane (goto) {
    if (goto === undefined) return
    if (goto !== '-1') {
      let loc = parent.leftpane?.contentWindow.location
      if (loc) {
        let url = new URL(loc.href)
        url.searchParams.set('GOTO', goto)
        parent.leftpane.contentWindow.location = url.href
      }
    }
  }

  function refreshLeftMenu (callback) {
    top.leftmenu.contentWindow.reload(callback)
  }

  function refreshEditList () {
    const editUrl = new URL('creativehub-edit-list.php', window.location)
    editUrl.searchParams.append('ORD_NBR', order_nbr)
    editUrl.searchParams.append('TYP', type)
    editUrl.searchParams.append('ORGN', origin)
    asyncGetContent('edit-list', editUrl.href, function () {
      calcAmt()
    })
  }

  function refreshDeletedList () {
    const editUrl = new URL('creativehub-edit-list.php', window.location)
    editUrl.searchParams.append('ORD_NBR', order_nbr)
    editUrl.searchParams.append('TYP', type)
    editUrl.searchParams.append('ORGN', origin)
    editUrl.searchParams.append('SHOW', 'NO')
    asyncGetContent('deleted-list', editUrl.href, function () {
      calcAmt()
    })
  }

  function refreshPayList () {
    const paymentUrl = new URL('creativehub-edit-payment-list.php', window.location)
    paymentUrl.searchParams.append('ORD_NBR', order_nbr)
    paymentUrl.searchParams.append('TYP', type)
    paymentUrl.searchParams.append('ORGN', origin)
    asyncGetContent('pay', paymentUrl.href, function () {
      calcAmt()
    })
  }

  function savePayList () {
    makeFormDirty()
    const tnd_amt = getInt('TND_AMT')
    const paymentUrl = new URL('creativehub-edit-payment-list.php', window.location)
    paymentUrl.searchParams.append('ORD_NBR', order_nbr)
    paymentUrl.searchParams.append('TYP', type)
    paymentUrl.searchParams.append('TND_AMT', tnd_amt)
    asyncGetContent('pay', paymentUrl.href, function () {
      document.querySelector('input#TND_AMT').value = ''
      calcAmt()
    })
  }

  function deletePayList () {
    makeFormDirty()
    const pymt_nbr = this.dataset.nbr
    const paymentUrl = new URL('creativehub-edit-payment-list.php', window.location)
    paymentUrl.searchParams.append('ORD_NBR', order_nbr)
    paymentUrl.searchParams.append('TYP', type)
    paymentUrl.searchParams.append('PYMT_NBR', pymt_nbr)
    paymentUrl.searchParams.append('PYMT_TYP', 'DEL')
    asyncGetContent('pay', paymentUrl.href, function () {
      calcAmt()
    })
  }

  function notifWarning (msg, timeout = 5000) {
    let $el = jq('.notif-warning span')
    $el.text(msg).parent().show()
    setTimeout(() => {
      $el.parent().hide()
    }, timeout)
  }

  function notifSuccess (msg, timeout = 5000) {
    let $el = jq('.notif-success span')
    $el.text(msg).parent().show()
    setTimeout(() => {
      $el.parent().hide()
    }, timeout)
  }

  function makeFormDirty () {
    formdirty = true
  }
</script>
<script type="text/javascript">
  'use strict'

  const order_nbr = jq('#ORD_NBR').val()
  const type 		= "<?php echo $type; ?>"
  const stt 		= "<?php echo $stt; ?>"
  const origin 		= "<?php echo $origin; ?>"
  const isnew 		= "<?php echo $new; ?>"
  const ischanged 	= "<?php echo $changed; ?>"
  const ischangestatus = "<?php echo $statuschanged; ?>"
  const uppersec 	= "<?php echo $UpperSec; ?>"
  let formdirty 	= false

  resetTaxPct()
  refreshEditList()
  refreshPayList()

  jq('document').ready(function ($) {
    // console.info(`jQuery version: ${$.fn.jquery}`)
    window.focus()
    if (top.Pace) {
      top.Pace.stop()
      // Pace is buggy, keeps adding classname
      top.document.body.classList.remove('pace-running', 'pace-done')
    }

    if (top.hash) {
      top.hash.set(order_nbr, 3)
      top.hash.save()
    }

    const $mainForm = $('form#mainForm')
    $mainForm.css('cursor', 'unset')

    if (uppersec <= 0) {
      refreshDeletedList()
    }

    // New nota
    if (order_nbr === '-1') {
      $('.toolbar-only').hide()
      $('#history').hide()
      $mainForm.find('.hour').val((new Date).getHours()).change()
      $mainForm.find('#ORD_BEG_TME').val(getCurTime())

      let m = 5
      // Set minutes of increment 'm'
      $mainForm.find('.minutes').val(m * (Math.ceil((new Date).getMinutes() / m))).change()
    }

    if (isnew === '1') {
      refreshLeftPane('TOP')
      refreshLeftMenu()
    }

    if (ischanged === '1') {
      refreshLeftPane(order_nbr)
      refreshLeftMenu()
    }

    if (ischangestatus === '1') {
      let status = $('#ORD_STT_ID').val()
      if (status == 'CP') status = 'ALL'
      if (!(parent === top)) {
        parent.leftpane.contentWindow.changeStatus(status, order_nbr)
        refreshLeftMenu(function (win) {
          win.selectByUrl('creativehub-tripane.php?STT=' + status)
        })
      }
    }

    // Click handler for edit-list
    const $divEditList = $('div#edit-list')
    $divEditList.on('click', 'table#edit-table tr.item', function () {
      const detail_nbr = $(this).data('detail')
      const detailUrl = new URL('creativehub-edit-list-detail.php', window.location)
      detailUrl.searchParams.append('ORD_NBR', order_nbr)
      detailUrl.searchParams.append('ORD_DET_NBR', detail_nbr)
      detailUrl.searchParams.append('TYP', '')
      detailUrl.searchParams.append('SHOW', '')
      detailUrl.searchParams.append('STT', '')
      detailUrl.searchParams.append('ORGN', '')
      slideFormIn(detailUrl.href)
    })

    $divEditList.on('click', 'div.nest-btn', function (e) {
      e.stopPropagation()
      const detail_nbr = $(this).parents('tr.item').data('detail')
      const nestUrl = new URL('creativehub-edit-list-detail.php', window.location)
      nestUrl.searchParams.append('ORD_NBR', order_nbr)
      nestUrl.searchParams.append('ORD_DET_NBR', -1)
      nestUrl.searchParams.append('ORD_DET_NBR_PAR', detail_nbr)
      nestUrl.searchParams.append('TYP', '')
      slideFormIn(nestUrl.href)
    })

    $divEditList.on('click', 'div.trash-btn', function (e) {
      e.stopPropagation()
      const detail_nbr = $(this).parents('tr.item').data('detail')
      const editUrl = new URL('creativehub-edit-list.php', window.location)
      editUrl.searchParams.append('ORD_NBR', order_nbr)
      editUrl.searchParams.append('DEL_D', detail_nbr)
      editUrl.searchParams.append('TYP', '')
      asyncGetContent('edit-list', editUrl.href, function () {
        calcAmt()
      })
    })

    $divEditList.on('click', 'div.link-btn', function (e) {
      e.stopPropagation()
      const detail_nbr = $(this).parents('tr.item').data('detail')
      console.info('Unduh file ' + detail_nbr)
      $('#unduh').find('input[name=ORD_DET_NBR]').val(detail_nbr).end().submit()
    })

    $divEditList.on('click', 'table#edit-table thead  div:has(span.fa-plus)', function () {
      if (order_nbr === '-1') {
        top.jQuery('#fade, #invoiceAdd').show()
      } else {
        const editUrl = new URL('creativehub-edit-list-detail.php', window.location)
        editUrl.searchParams.append('ORD_NBR', order_nbr)
        editUrl.searchParams.append('ORD_DET_NBR', '-1')
        editUrl.searchParams.append('TYP', '')
        slideFormIn(editUrl.href)
      }
    })

    // Form Submit Handler
    $mainForm.on('submit', function () {
      formdirty = false

      if (order_nbr != '-1') {
        refreshLeftPane(order_nbr)
      }

      // Normalize number before submit
      const ids = ['#TOT_AMT', '#TOT_SUB', '#TOT_REM', '#TAX_AMT', '#FEE_MISC']
      for (let i = 0, len = ids.length; i < len; i++) {
        $(this).find(ids[i]).val(function (i, c) {
          return normal(c)
        })
      }

      // Make time from select
      // Selesai Time
      let hr = ('0' + $('select[NAME=ORD_END_HR]').val()).slice(-2)
      let min = ('0' + $('select[NAME=ORD_END_MIN]').val()).slice(-2)
      $('input[NAME=ORD_END_TME]').val(hr + ':' + min + ':' + '00')

      // Dijanjikan Time
      hr = ('0' + $('select[NAME=DUE_HR]').val()).slice(-2)
      min = ('0' + $('select[NAME=DUE_MIN]').val()).slice(-2)
      $('input[NAME=DUE_TME]').val(hr + ':' + min + ':' + '00')
      return true
    })

    // Function handler for pay-list
    const $divPay = $('div#pay')
    $divPay.on('change click', '#TOT_PAY', calcAmt)
    $divPay.on('change click', '#TND_PAY', calcAmt)
    $divPay.on('click', '.delete-pay', deletePayList)

    $mainForm.on('click', '#SAVE_PAY', savePayList)
    $mainForm.on('click', '#RECALC', calcAmt)
    $mainForm.on('change', '#TAX_APL_ID', calcAmt)
    $mainForm.on('change keyup', '#TAX_PCT', calcPct)
    $mainForm.on('change keyup', '#TND_AMT', text_ribuan)
    $mainForm.on('change keyup', '#FEE_MISC', function (e) {
      text_ribuan(e)
      calcAmt()
    })

    // PDF
    $('#toolbar-pdf').on('click', function () {
      if ($('#edit-list tbody tr.item').length > 0) {
        $('html').css('cursor', 'wait')
        $('body div, #mainForm').remove()
        $('body').append(createSpinner($)).css('height', '100%')
        $('#pdf').find('input[name=ORD_NBR]').val(order_nbr).end().submit()
        refreshLeftPane(order_nbr)
      } else {
        notifWarning('Belum ada item!', 2000)
        $('.nodata').addClass('flash')
        setTimeout(function () {$('.nodata').removeClass('flash')}, 600)
      }
    })

    // Print
    $('#toolbar-print').on('click', function () {
      if ($('#edit-list tbody tr.item').length > 0) {
        $('html').css('cursor', 'wait')
        let url = new URL('creativehub-invoice-pdf.php', window.location)
        url.searchParams.set('ORD_NBR', order_nbr)
        printJS(url.href, function () {$('html').css('cursor', 'default')})
      } else {
        notifWarning('Belum ada item!', 2000)
        $('.nodata').addClass('flash')
        setTimeout(function () {$('.nodata').removeClass('flash')}, 600)
      }
    })

    // Delete nota
    $('#toolbar-hapus').on('click', function () {
      window.scrollTo(0, 0)
      top.jQuery('#fade, #invoiceDelete').show()
    })

    $('#invoiceDeleteYes', top.document).on('click', function () {
      // Currently Two possible caller. Normal tripane and creativehub-list.php
      let leftpane = parent.leftpane?.contentWindow.location
      if (leftpane) {
        let url = new URL(leftpane.href)
        url.searchParams.append('DEL', order_nbr)
        parent.leftpane.contentWindow.location = url.href
        top.jQuery('#fade, #invoiceDelete').hide()
      } else {
        let url = new URL('creativehub-list.php')
        url.searchParams.append('DEL', order_nbr)
        url.searchParams.append('STT', stt)
        url.searchParams.append('TYP', type)
        top.jQuery('#fade, #invoiceDelete').hide()
        top.content.contentWindow.location = url.href
      }
    })

    // Copy Nomor Nota
    $('h2#NomorOrder').on('click', function () {
      navigator.clipboard.writeText(this.innerText)
      notifSuccess('Nomor nota tersimpan di clipboard.')
    })

    // Set dirty bit
    $mainForm.find(':input').on('input', function () {
      formdirty = true
    })

    // A lot can trigger formdirty, alert user when there's unsaved information
    window.addEventListener('beforeunload', function (evt) {
      if (formdirty) {
        evt.preventDefault()
        console.error('Formdirty')
        return 'Masih ada perubahan yang belum tersimpan'
        // notifWarning('Masih ada perubahan yang belum tersimpan')
      }
    })

    // Detect back button, don't  show notif
    $(window).bind('pageshow', function (event) {
      const perfEntries = performance.getEntriesByType('navigation')
      if ((event.originalEvent.persisted || !!event.persisted) ||
        (perfEntries.length && perfEntries[0].type === 'back_forward')) {
        console.log('User got here from Back or Forward button.')
        $('.notif').hide()
        parent.leftpane?.contentWindow.selLeftPaneByOrder(order_nbr)
      }
    })
  })
</script>
</body>
</html>