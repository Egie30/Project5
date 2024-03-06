<?php

include "framework/database/connect.php";
include "framework/functions/default.php";
include "framework/security/default.php";

$UpperSec = getSecurity($_SESSION['userID'], "Accounting");

$OrdNbr = mysql_escape_string($_REQUEST['ORD_NBR']);
$PrnTyp = mysql_escape_string($_REQUEST['PRN_TYP']);
$type = mysql_escape_string($_REQUEST['TYP']);
$NewTab = mysql_escape_string($_REQUEST['NEW_TAB']);

if ($_GET["LIST_DET_NBR"] == "SESSION") {
    $ListDetNbr = $_SESSION["LIST_DET_NBR"];
} else {
    $ListDetNbr = explode(" ", mysql_escape_string($_REQUEST["LIST_DET_NBR"]));
}

$ArrayList = array_count_values($ListDetNbr);

//$queryActg = "SELECT ACTG_TYP FROM CMP.RTL_ORD_HEAD  WHERE ORD_NBR = '" . $OrdNbr . "'";
//$resultActg = mysql_query($queryActg);
//$rowActg = mysql_fetch_array($resultActg);
//
////Get default company
//if ($rowActg['ACTG_TYP'] == 1) {
//    $co = $CoNbrPkp;
//} else {
//    $co = $CoNbrDef;
//}

$queryUnitBisnis = "SELECT CHB_CO_NBR AS CO_NBR FROM CMP.RTL_ORD_HEAD  WHERE ORD_NBR = '" . $OrdNbr . "'";
$rowUnitBisnis = mysql_fetch_array(mysql_query($queryUnitBisnis));
$co = $rowUnitBisnis['CO_NBR'];

$query = "SELECT NAME,ADDRESS,ZIP,PHONE,CITY_NM,EMAIL 
                    FROM CMP.COMPANY COM 
                    LEFT OUTER JOIN CMP.CITY CT ON CT.CITY_ID=COM.CITY_ID 
                    WHERE CO_NBR = $co";
$result = mysql_query($query);
$CmpDef = mysql_fetch_array($result);

$query = "SELECT PHONE,EMAIL 
                FROM CMP.COMPANY COM 
                WHERE CO_NBR = $co";
$result = mysql_query($query);
$CmpLoc = mysql_fetch_array($result);

//Increment print count
if ($PrnTyp == "Invoice" && ! $NewTab) {
    $query = "UPDATE CMP.RTL_ORD_HEAD SET IVC_PRN_CNT=IVC_PRN_CNT+1 WHERE ORD_NBR=" . $OrdNbr;
    $resultb = mysql_query($query);
}

$query = "SELECT 
                        ORD_NBR,
                        DATE_FORMAT(ORD_BEG_TS,'%d-%m-%Y') AS ORD_DT,
                        ORD_BEG_TS,
                        DATE_FORMAT(CMP_TS,'%d-%m-%Y') AS CMP_DT,
                        STT.ORD_STT_ID,
                        STT.ORD_STT_DESC,
                        BUY_PRSN_NBR,
                        PPL.NAME AS NAME_PPL,
                        COM.NAME AS NAME_COM,
                        BUY_CO_NBR,
                        REF_NBR,
                        ORD_TTL,
                        DUE_TS,
                        CHB_CO_NBR,
                        FEE_MISC,
                        TAX_APL_ID,
                        TAX_AMT,
                        TOT_AMT,
                        PYMT_DOWN,
                        PYMT_REM,
                        TOT_REM,
                        DATE_FORMAT(CMP_TS,'%d-%m-%Y') AS CMP_DT,
                        PU_TS,
                        SPC_NTE,
                        HED.CRT_TS,
                        HED.CRT_NBR,
                        HED.UPD_TS,
                        HED.UPD_NBR,
                        IVC_PRN_CNT
                FROM CMP.RTL_ORD_HEAD HED
                INNER JOIN CMP.RTL_ORD_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID
                LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR
                LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR
                WHERE ORD_NBR=" . $OrdNbr;
//echo $query;
$result = mysql_query($query);
$row = mysql_fetch_array($result);

if ($PrnTyp == "Invoice") {
    $Suffix = "-I-CreativeHub";
    $header = followSpace($CmpDef['NAME'], 59) . "NOTA CREATIVE HUB" . pSpace(41) . "Nota No. " . leadZero($OrdNbr, 6)
        . "-"
        . leadZero($row['IVC_PRN_CNT'], 2) . chr(13) . chr(10);
}

$header .= followSpace($CmpDef['ADDRESS'] . ", " . $CmpDef['CITY_NM'] . " " . $CmpDef['ZIP'], 110) . "Tanggal Order: "
    . $row['ORD_DT'] . chr(13) . chr(10);
$header .= followSpace("Telp. " . $CmpLoc['PHONE'] . ", E-Mail: " . $CmpLoc['EMAIL'], 108) . "Tanggal Selesai: "
    . $row['CMP_DT'] . chr(13) . chr(10);

$header .= chr(13) . chr(10);
$customer = trim($row['NAME_PPL'] . " " . $row['NAME_COM']);
if ($customer == "") {
    $customer = "Tunai";
}

$prnHeader = chr(27) . "(B" . chr(12) . chr(0) . chr(5) . chr(2) . chr(-3) . chr(11) . chr(0) . chr(2) . leadZero(
        $OrdNbr,
        6
    );
$prnHeader .= pSpace(42) . "Pelanggan: " . $customer . chr(10);
$prnHeader .= pSpace(42) . "Judul Pesanan: " . $row['ORD_TTL'] . chr(13) . chr(10);

$dspHeader = "Pelanggan: " . $customer . chr(10);
$dspHeader .= "Judul Pesanan: " . $row['ORD_TTL'] . chr(13) . chr(10);

$header .= $dspHeader;
$header .= str_repeat("-", 135) . chr(13) . chr(10);

if ($PrnTyp == "Invoice") {
    $spacing = 39;
}


$header .= " Jumlah" . pSpace($spacing) . "Deskripsi Pesanan";
if ($PrnTyp == "Invoice") {
    if ($UpperSec != 8) {
        $header .= pSpace(40) . "Harga" . pSpace(7) . "Disc" . pSpace(8) . "Subtotal";
    }
}
$header .= chr(13) . chr(10);
$header .= str_repeat("-", 135) . chr(13) . chr(10);

$string = $header;
$rowCount = 0;
$pageCount = 0;
$query = "SELECT 
                        ORD_DET_NBR,
                        DET.ORD_NBR,
                        DET_TTL,
                        DET.ORD_TYP,
                        TYP.RTL_ORD_DESC,
                        DET.PRC,
                        ORD_Q,
                        FIL_LOC,
                        FEE_MISC,
                        DISC_PCT,
                        DISC_AMT,
                        TOT_SUB
                FROM CMP.RTL_ORD_DET DET 
                LEFT OUTER JOIN CMP.RTL_ORD_TYP TYP ON DET.ORD_TYP=TYP.RTL_ORD_TYP
                WHERE ORD_NBR=" . $OrdNbr . " 
                    AND ORD_DET_NBR_PAR IS NULL 
                    AND DET.DEL_NBR=0 ORDER BY 1";
// echo $query;
$result = mysql_query($query);
while ($rowd = mysql_fetch_array($result)) {
    $query = "SELECT 
                            ORD_DET_NBR,
                            DET.ORD_NBR,
                            ORD_DET_NBR_PAR,
                            DET_TTL,
                            CONCAT(CAT.CATEGORY, ' - ', TYP.RTL_ORD_DESC, ' ', DET_TTL) AS TTL,
                            TYP.RTL_ORD_DESC,
                            DET.PRC,
                            ORD_Q,
                            FIL_LOC,
                            FEE_MISC,
                            DISC_PCT,
                            DISC_AMT,
                            # Just sum from child item
                            SUM(TOT_SUB) AS TOT_SUB
                        FROM CMP.RTL_ORD_DET DET 
                        LEFT OUTER JOIN CMP.RTL_ORD_TYP TYP ON DET.ORD_TYP=TYP.RTL_ORD_TYP
                        LEFT OUTER JOIN CMP.RTL_ORD_TYP_CAT CAT ON CAT.CAT_ID=TYP.CAT_ID
                        WHERE ORD_DET_NBR=" . $rowd['ORD_DET_NBR'] . " 
                            OR ORD_DET_NBR_PAR=" . $rowd['ORD_DET_NBR'] . " 
                            AND DET.DEL_NBR=0 ORDER BY 1";
    // echo "<pre>" . $query;
    $resultc = mysql_query($query);
    while ($rowc = mysql_fetch_array($resultc)) {
        $rowCount++;
        if ($rowCount == 12) {
            $string .= str_repeat("-", 135) . chr(13) . chr(10);
            if ($PrnTyp == "Invoice") {
                $string .= pSpace(107) . "Total Halaman " . leadSpace($TotNet, 14);
            }
            $string .= chr(13) . chr(10);
            $string .= pRow(4);
            $string .= "Dilanjutkan ke halaman berikutnya" . chr(13) . chr(10);
            $string .= $header;
            $rowCount = 1;
        }

        // Update: Don't show child item
        if ($rowc['ORD_DET_NBR_PAR'] != "") {
            $indent = 7;
        } else {
            $indent = 0;
        }
        $string .= leadSpace($rowc['ORD_Q'], 7 + $indent) . " ";

        // Handle long title
        $TTL = trim($rowc['TTL']);
        if (strlen($TTL) > (80 - $indent)) {
            $ttl = wordwrap($TTL, (80 - $indent), "<br>");
            // $TTL = substr($ttl, 0, strpos($ttl, "<br>"));
            $arr = explode("<br>", $ttl);
            $TTL = $arr[0];
        }
        $prnDesc = trim(leadZero($rowc['ORD_DET_NBR'], 6) . " " . $TTL);
        $string .= followSpace($prnDesc, 88 - $indent) . "  ";
        $price = $rowc['PRC'] + $rowc['FEE_MISC'];

        if ($PrnTyp == "Invoice") {
            if ($UpperSec != 8) {
                $string .= leadSpace($price, 10) . "  ";
                $string .= leadSpace($rowc['DISC_AMT'], 10) . "  ";
                $string .= leadSpace($rowc['TOT_SUB'], 13) . "  ";
            }
        }
        $string .= chr(13) . chr(10);

        // Long title continuation
        if (isset($arr)) {
            foreach (array_slice($arr, 1) as $line) {
                $string .= leadSpace(" ", 7 + $indent + 8);
                $string .= followSpace($line, 88 - $indent) . "  ";
                $string .= chr(13) . chr(10);
                $rowCount++;
            }
            unset($arr, $ttl);
        }
        $TotNet += $rowc['TOT_SUB'];
    }
}

if ($rowCount != 11) {
    $string .= pRow(11 - $rowCount);
}

if ($row['TAX_APL_ID'] == "E") {
    $totLine1 = "Biaya Tambahan " . leadSpace($row['FEE_MISC'], 14);
    $totLine2 = "         Total " . leadSpace($row['TOT_AMT'], 14);

    $querypymt = "SELECT SUM(TND_AMT) AS TND_AMT,TOT_REM,PYMT.CRT_TS FROM CMP.RTL_ORD_PYMT PYMT 
                    LEFT OUTER JOIN CMP.RTL_ORD_HEAD HED ON PYMT.ORD_NBR=HED.ORD_NBR
                    WHERE PYMT.DEL_NBR=0 AND PYMT.ORD_NBR=" . $OrdNbr . " ORDER BY PYMT.CRT_TS ASC";
    $resultpymt = mysql_query($querypymt);
    $rowttl = mysql_num_rows($resultpymt);
    $rowpym = mysql_fetch_array($resultpymt);
    if ($rowttl == 1 && $rowpym['TOT_REM'] == 0) {
        $TotAmt = 0;
    } else {
        $TotAmt = $rowpym['TND_AMT'];
    }
    $totLine3 = "     Uang Muka " . leadSpace($TotAmt, 14);

    $querypymt = "SELECT SUM(TND_AMT) AS TND_AMT,TOT_REM,PYMT.CRT_TS FROM CMP.RTL_ORD_PYMT PYMT 
                    LEFT OUTER JOIN CMP.RTL_ORD_HEAD HED ON PYMT.ORD_NBR=HED.ORD_NBR
                    WHERE PYMT.DEL_NBR=0 AND PYMT.ORD_NBR=" . $OrdNbr . " ORDER BY PYMT.CRT_TS DESC";
    $resultpymt = mysql_query($querypymt);
    $rowpym = mysql_fetch_array($resultpymt);
    if ($rowpym['TOT_REM'] == 0) {
        $TotAmt = $rowpym['TND_AMT'];
    } else {
        $TotAmt = 0;
    }
    $totLine4 = "     Pelunasan " . leadSpace($TotAmt, 14);

    $totLine5 = "          Sisa " . leadSpace($row['TOT_REM'], 14);
    $totLine6 = "";
} elseif ($row['TAX_APL_ID'] == "A") {
    $totLine1 = "Biaya Tambahan " . leadSpace($row['FEE_MISC'], 14);
    $totLine2 = "           PPN " . leadSpace($row['TAX_AMT'], 14);
    $totLine3 = "         Total " . leadSpace($row['TOT_AMT'], 14);

    $querypymt = "SELECT SUM(TND_AMT) AS TND_AMT,TOT_REM,PYMT.CRT_TS FROM CMP.RTL_ORD_PYMT PYMT 
                    LEFT OUTER JOIN CMP.RTL_ORD_HEAD HED ON PYMT.ORD_NBR=HED.ORD_NBR
                    WHERE PYMT.DEL_NBR=0 AND PYMT.ORD_NBR=" . $OrdNbr . " ORDER BY PYMT.CRT_TS ASC";
    $resultpymt = mysql_query($querypymt);
    $rowttl = mysql_num_rows($resultpymt);
    $rowpym = mysql_fetch_array($resultpymt);
    if ($rowttl == 1 && $rowpym['TOT_REM'] == 0) {
        $TotAmt = 0;
    } else {
        $TotAmt = $rowpym['TND_AMT'];
    }
    $totLine4 = "     Uang Muka " . leadSpace($TotAmt, 14);

    $querypymt = "SELECT SUM(TND_AMT) AS TND_AMT,TOT_REM,PYMT.CRT_TS FROM CMP.RTL_ORD_PYMT PYMT 
                    LEFT OUTER JOIN CMP.RTL_ORD_HEAD HED ON PYMT.ORD_NBR=HED.ORD_NBR
                    WHERE PYMT.DEL_NBR=0 AND PYMT.ORD_NBR=" . $OrdNbr . " ORDER BY PYMT.CRT_TS DESC";
    $resultpymt = mysql_query($querypymt);
    $rowpym = mysql_fetch_array($resultpymt);
    if ($rowpym['TOT_REM'] == 0) {
        $TotAmt = $rowpym['TND_AMT'];
    } else {
        $TotAmt = 0;
    }
    $totLine5 = "     Pelunasan " . leadSpace($TotAmt, 14);

    $totLine6 = "          Sisa " . leadSpace($row['TOT_REM'], 14);
} elseif ($row['TAX_APL_ID'] == "I") {
    $totLine1 = "Biaya Tambahan " . leadSpace($row['FEE_MISC'], 14);
    $totLine2 = "         Total " . leadSpace($row['TOT_AMT'], 14);
    $querypymt = "SELECT SUM(TND_AMT) AS TND_AMT,TOT_REM,PYMT.CRT_TS FROM CMP.RTL_ORD_PYMT PYMT 
                    LEFT OUTER JOIN CMP.RTL_ORD_HEAD HED ON PYMT.ORD_NBR=HED.ORD_NBR
                    WHERE PYMT.DEL_NBR=0 AND PYMT.ORD_NBR=" . $OrdNbr . " ORDER BY PYMT.CRT_TS ASC";
    $resultpymt = mysql_query($querypymt);
    $rowttl = mysql_num_rows($resultpymt);
    $rowpym = mysql_fetch_array($resultpymt);
    if ($rowttl == 1 && $rowpym['TOT_REM'] == 0) {
        $TotAmt = 0;
    } else {
        $TotAmt = $rowpym['TND_AMT'];
    }
    $totLine3 = "     Uang Muka " . leadSpace($TotAmt, 14);

    $querypymt = "SELECT SUM(TND_AMT) AS TND_AMT,TOT_REM,PYMT.CRT_TS FROM CMP.RTL_ORD_PYMT PYMT 
                    LEFT OUTER JOIN CMP.RTL_ORD_HEAD HED ON PYMT.ORD_NBR=HED.ORD_NBR
                    WHERE PYMT.DEL_NBR=0 AND PYMT.ORD_NBR=" . $OrdNbr . " ORDER BY PYMT.CRT_TS DESC";
    $resultpymt = mysql_query($querypymt);
    $rowpym = mysql_fetch_array($resultpymt);
    if ($rowpym['TOT_REM'] == 0) {
        $TotAmt = $rowpym['TND_AMT'];
    } else {
        $TotAmt = 0;
    }
    $totLine4 = "     Pelunasan " . leadSpace($TotAmt, 14);

    $totLine5 = "          Sisa " . leadSpace($row['TOT_REM'], 14);
    $totLine6 = "    Jumlah PPN " . leadSpace($row['TAX_AMT'], 14);
}
$string .= str_repeat("-", 135) . chr(13) . chr(10);
if ($PrnTyp == "Invoice") {
    $string .= pSpace(106) . $totLine1;
}
$string .= chr(13) . chr(10);
if ($PrnTyp == "Invoice") {
    $spacing = 0;
} elseif ($PrnTyp = "PackingSlip") {
    $spacing = 21;
}

$string .= pSpace(18 + $spacing) . "Penerima" . pSpace(40);
if ($PrnTyp == "Invoice") {
    $string .= " Penjual ";
    $string .= pSpace(31) . $totLine2;
} elseif ($PrnTyp = "PackingSlip") {
    $string .= "Pengantar";
}
$string .= chr(13) . chr(10);
if ($PrnTyp == "Invoice") {
    $string .= pSpace(106) . $totLine3;
}
$string .= chr(13) . chr(10);
$string .= pSpace(13 + $spacing) . "(________________)" . pSpace(30) . "(_________________)";
if ($PrnTyp == "Invoice") {
    $string .= pSpace(26) . $totLine4;
}
$string .= chr(13) . chr(10);
if ($PrnTyp == "Invoice") {
    $string .= pSpace(106) . $totLine5;
}
$string .= chr(13) . chr(10);
if ($PrnTyp == "Invoice") {
    $string .= "Terima kasih atas kepercayaan anda. Silakan hubungi kami untuk pelayanan Creative Hub yang lain."
        . pSpace(6) . $totLine6;
} elseif ($PrnTyp = "PackingSlip") {
    $string .= "Barang harap diperiksa dengan baik. Pengajuan klaim sesudah staff meninggalkan tempat tidak dilayani dan menjadi tanggung jawab pembeli.";
}

$string .= chr(13) . chr(10);
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="css/font-awesome-4.7.0/css/font-awesome.min.css">
    <script>if (top.Pace && !top.Pace.running) top.Pace.restart()</script>
    <style>
        @import url(css/font-san-francisco.css);

        body {
            /*background: #e2e1e0;*/
            background: white;
            height: 100%;
            font-size: 10pt;
            font-family: 'San Francisco Display', 'HelveticaNeue', 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }

        pre {
            font-size: 8pt;
            letter-spacing: -1.25px;
            margin: auto;
            width: max-content;
            /*background: white;*/
            background: #f2f2f2;
            padding: 15px;
        }

        .card {
            border-radius: 2px;
            user-select: none;
            margin-top: 20px;
        }

        .card-1 {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12), 0 1px 2px rgba(0, 0, 0, 0.24);
            transition: all 0.3s cubic-bezier(.25, .8, .25, 1);
        }

        .card-1:hover {
            /*box-shadow: 0 14px 28px rgba(0, 0, 0, 0.25), 0 10px 10px rgba(0, 0, 0, 0.22);*/
        }

        .toolbar {
            border-bottom: 1px solid #cacbcf;
            margin-bottom: 10px;
        }

        .toolbar .btn {
            cursor: pointer;
            color: #777777;
        }

        .toolbar #newtab,
        .toolbar #fullscreen {
            float: right;
            padding-right: 10px;
        }

        form {
            display: none;
        }
    </style>
</head>
<body>
<div class="toolbar">
    <span class="btn" id="back"><i class="fa fa-angle-double-left"></i> Kembali</span>
    <span class="btn" id="fullscreen"><span>Lihat fullscreen </span><i class="fa fa-desktop"></i></span>
    <!--    <span class="btn" id="newtab"><span>Buka tab baru </span><i class="fa fa-external-link"></i></span>-->
    <!--    <a id="x" href="javascript:void(0)" target="_blank">Click</a>-->
</div>
<div id="container">
    <pre class="card card-1"><?php echo $string ?></pre>
</div>
<form action="<?php
echo $_SERVER["SCRIPT_NAME"] ?>" method="post" target="_blank">
    <input type="hidden" name="ORD_NBR" value="<?php echo $OrdNbr ?>">
    <input type="hidden" name="PRN_TYP" value="<?php echo $PrnTyp ?>">
    <input type="hidden" name="TYP" value="<?php echo $type ?>">
    <input type="hidden" name="NEW_TAB" value="1">
</form>
<script>
  // document.querySelector('#newtab').addEventListener('click', () => {
  //   window.open(window.location, '_blank')
  //   document.getElementsByTagName('form')[0].submit()
  //
  //   // const x = document.querySelector('a#x')
  //   // const dataUri = 'data:text/html;base64,' + btoa(document.documentElement.outerHTML)
  //   // x.setAttribute('href', dataUri)
  //
  //   // const newWindow = window.open('', '_blank')
  //   // newWindow.document.write('<iframe src="' + dataUri +'" style="border:0; top:0; left:0; bottom:0; right:0; width:100%; height:100%;" allowfullscreen></iframe>')
  // })

  document.querySelector('#fullscreen').addEventListener('click', () => {
    // window.open(window.location, '_blank')
    // document.getElementsByTagName('form')[0].submit()
    if (!document.fullscreenElement) {
      document.documentElement.requestFullscreen({ navigationUI: 'hide' })
    } else {
      document.exitFullscreen()
    }
  })

  document.addEventListener('fullscreenchange', (event) => {
    let info = document.querySelector('#fullscreen')
    if (document.fullscreenElement) {
      info.firstElementChild.textContent = 'Tutup fullscreen '
      info.lastElementChild.classList.remove('fa-desktop')
      info.lastElementChild.classList.add('fa-close')
      let size = Math.round(screen.width / 114)
      document.querySelectorAll('pre').forEach(el => {
        el.style.letterSpacing = '-0.5'
        el.style.fontSize = size + 'pt'
      })
    } else {
      info.firstElementChild.textContent = 'Lihat fullscreen '
      info.lastElementChild.classList.remove('fa-close')
      info.lastElementChild.classList.add('fa-desktop')
      document.querySelectorAll('pre').forEach(el => {
        el.style.fontSize = '8pt'
        el.style.letterSpacing = '-1.25px'
      })
    }
  })

  document.querySelector('#back').addEventListener('click', () => {
    if (document.fullscreenElement) document.exitFullscreen()
    // window.history.back()
    let url = new URL('creativehub-edit.php', window.location)
    url.searchParams.set('ORD_NBR', <?php echo  $OrdNbr ?>)
    window.location = url.href
  })

  document.querySelector('pre').
    textContent.split('Dilanjutkan ke halaman berikutnya').
    forEach((t, i) => {
      if (i === 0) {
        document.querySelector('pre').textContent = t
      } else {
        let pre = document.createElement('pre')
        pre.className = 'card card-1'
        pre.textContent = t.trim()
        document.querySelector('#container').appendChild(pre)
      }
    })

  if (window.parent === window) {
    document.querySelector('div.toolbar').style.display = 'none'
    document.querySelectorAll('pre').forEach(el => el.style.fontSize = '12pt')
  }

  if (!'<?php echo  $OrdNbr ?>') {
    window.close()
  }

  window.onload = () => {
    if (top.Pace && top.Pace.running) top.Pace.stop()
  }
</script>
</body>


<?php
$string = str_replace($dspHeader, $prnHeader, $string);

$fh = fopen("print-digital/" . $OrdNbr . "$Suffix.txt", "w");
fwrite($fh, chr(15) . $string . chr(18));
fclose($fh);
?>