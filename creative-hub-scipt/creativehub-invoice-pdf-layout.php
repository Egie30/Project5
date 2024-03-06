<?php
require_once "framework/functions/default.php";
require_once "framework/database/connect.php";

if ( ! isset($data)) {
    // Direct access for testing Layout
    $data = [];
    $data['PERSON'] = "PERSON";
    $data['COMPANY'] = "COMPANY";
    $data['ORD_TTL'] = "TITLE";
    $result = null;
}

//if ($payment >= $data['TOT_AMT']) {
//    $payment = 0;
//}

function createDescription($row)
{
    $desc = "";
    if ( ! empty($row['RTL_ORD_DESC'])) {
        $desc = $row['CATEGORY'] . ' - ' . $row['RTL_ORD_DESC'];
    }
    if ( ! empty($row['DET_TTL'])) {
        $desc .= ' ' . $row['DET_TTL'];
    }
    if ( ! empty($row['RM_DESC'])) {
        $desc .= '<br> [<b>Ruang</b>: ' . $row['RM_DESC'] . ']';
    }
    if ( ! empty($row['TBL_DESC'])) {
        $desc .= '<br> [<b>Meja</b>: ' . $row['TBL_DESC'] . ']';
    }
    if ( ! empty($row['WIFI_UNM'])) {
        $desc .= '<br> (<b>User</b>: ' . $row['WIFI_UNM'] . ' <b>Pass</b>: ' . $row['WIFI_PWD'] . ')';
    }
    return trim($desc);
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Creative Hub Invoice</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Source+Sans+3&display=swap');

        html {
            width: 595pt;
            height: 842pt;
            font-family: 'Source Sans 3', 'Dejavu Sans', 'Calibri', sans-serif;
        }

        b {
            font-family: 'Source Sans 3', 'Dejavu Sans', 'Calibri', sans-serif;
            font-size: 8pt;
            font-weight: 600;
        }

        body {
            width: 100%;
            height: 100%;
            font-size: 9pt;
            padding: 20pt;
            line-height: 1.1;
        }

        div {
            padding: 10px;
            padding-left: 0;
        }

        .page-break {
            page-break-after: always;
        }

        #invoice {
            font-weight: bold;
            font-size: 11pt;
            padding: 0;
            border-bottom: 2px solid black;
            display: inline;
            width: 7ch;
        }

        .column {
            /*float: left;*/
            display: inline-block;
            /*padding: 0;*/
        }

        #column-left {
            width: 63.6%;
            padding-right: 10pt;
            padding-top: 30pt;
        }

        #column-right {
            width: 33.3%;
            padding-left: 10pt;
        }

        #logo {
            width: 110%;
            margin-top: -50px;
            margin-left: -15px;
        }

        table {
            /*width: 100%;*/
            width: 400px;
            border-collapse: collapse;
            /*border: 1px solid #aaa;*/
        }

        table th {
            border-top: 1px solid #aaa;
        }

        table tbody td, table th {
            border-bottom: 1px solid #aaa;
            font-size: 8pt;
            padding: 3px;
        }

        table tfoot {
            font-size: 8pt;
            padding: 3px;
            empty-cells: show;
        }

        table th#description {
            width: 100pt;
        }

        table th#qty {
            width: 3pt;
        }

        table th#price,
        table th#disc {
            width: 15ch;
        }

        table th#total {
            width: 15ch;
        }

        table tbody tr {
            background-color: white;
        }

        table tbody tr:nth-child(odd) {
            background-color: #eee;
        }

        table tbody tr td {
            vertical-align: top;
            text-align: right;
        }

        table tbody tr td:first-child {
            text-align: left;
        }

        table tbody tr td:nth-child(2) {
            text-align: center;
        }

        table tfoot tr {
            text-align: right;
        }

        table tfoot tr:nth-child(<?php echo ($count % 2 == 0)?"odd":"even"?>) {
            /*background-color: #eee;*/
        }

        table tfoot tr:nth-child(<?php echo ($count % 2 == 0)?"even":"odd"?>) {
            /*background-color: white;*/
        }

        table tfoot .bg-color {
            background: #eee;
            border-bottom: 1px solid #aaa;
        }

        #services {
            padding-top: 70px;
            padding-right: 0;
        }

        .service {
            font-family: 'Montserrat', sans-serif;
            font-size: 8pt;
            margin: auto;
            text-align: center;
            width: 50pt;
        }

        .service.wide {
            width: 67pt;
        }

        .service.wider {
            width: 102pt;
        }

        hr.spacer {
            border-width: 0.5px;
        }

        .bottom {
            position: absolute;
            font-size: 7pt;
            display: inline-block;
            text-align: center;
            line-height: 1;
        }

        .bottom#alamat {
            top: 95%;
            width: 90pt;
            right: 35pt;
        }

        .bottom#kontak {
            top: 100%;
            width: 100pt;
            right: 30pt;
        }

        hr#bottom {
            top: 95%;
            border-width: 3pt;
            border-style: solid;
            margin: 0;
            width: 320pt;
        }

        .no-padding-top {
            padding-top: 0;
        }
    </style>
</head>
<body>
<div class="column" id="column-left">
    <p id="invoice">INVOICE</p>
    <div class="no-padding-top">
        No. <?php echo str_pad($orderNumber, 6, "0", STR_PAD_LEFT) ?>
        <br>
        <?php echo strftime('%d %B %Y', strtotime($data['ORD_BEG_TS'])) ?>
        <br>
        <br>
        From: <b><?php echo $data['CREATIVEHUB'] ?></b>
        <br>
        <?php echo $data['ALAMAT'] ?>
    </div>
    <div id="customer" class="no-padding-top">
        Customer: <b><?php echo dispNameScreen($data['PERSON']) ?></b>
        <br>
        <?php echo $data['COMPANY'] ?>
    </div>
    <div id="title" class="no-padding-top">
        Order Title: <b><?php echo $data['ORD_TTL'] ?></b>
    </div>
    <table>
        <thead>
        <tr>
            <th id="description">Description</th>
            <th id="qty">Qty</th>
            <th id="price">Price/Rate</th>
            <th id="disc">Disc</th>
            <th id="total">Total</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $TOT = 0;
        while ($row = mysql_fetch_assoc($result)) {
            $TOT += $row['TOT_SUB'];
            ?>
            <tr>
                <td><?php echo createDescription($row) ?></td>
                <td><?php echo $row['ORD_Q'] ?></td>
                <td><?php echo number_format($row['PRC'], 0, ",", ".") ?></td>
                <td><?php echo number_format($row['DISC_AMT'], 0, ",", ".") ?></td>
                <td><?php echo number_format($row['TOT_SUB'], 0, ",", ".") ?></td>
            </tr>
            <?php
        } ?>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="2"></td>
            <td colspan="2">Subtotal</td>
            <td><?php echo number_format($TOT, 0, ",", ".") ?></td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td colspan="2">Tax</td>
            <td><?php echo number_format($data['TAX_AMT'], 0, ",", ".") ?></td>
        </tr>
        <?php
        if ( ! in_array($data['FEE_MISC'], ['', 'NULL', 0, '0'], true)) {
            ?>
            <tr>
                <td colspan="2"></td>
                <td colspan="2">Biaya Tambahan</td>
                <td><?php echo number_format($data['FEE_MISC'], 0, ",", ".") ?></td>
            </tr>
            <?php
        } ?>
        <tr>
            <td colspan="2"></td>
            <td colspan="2" class="bg-color"><b>TOTAL Rp.</b></td>
            <td class="bg-color"><?php echo number_format($data['TOT_AMT'], 0, ",", ".") ?></td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td colspan="2">Paid</td>
            <td><?php echo number_format($payment, 0, ",", ".") ?></td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td colspan="2" class="bg-color"><b>BALANCE Rp.</b></td>
            <td class="bg-color"><?php echo number_format($data['TOT_REM'], 0, ",", ".") ?></td>
        </tr>
        </tfoot>
    </table>
    <div>
        <b>Please make a payment to:</b>
        <br>
        Account Name: <b><?php echo $AccountName ?></b>
        <br>
        Account Number: <b><?php echo $AccountNbr ?></b>
        <br>
        Bank Name: <b><?php echo $AccountBank ?></b>
    </div>
    <hr id="bottom" class="bottom">
</div>
<div class="column" id="column-right">
    <img src="<?php echo $logo ?>" id="logo" alt="logo">
    <div id="services">
        <div class="service wide">CO-WORKING SPACE</div>
        <hr class="spacer">
        <div class="service">OFFICE SPACES</div>
        <hr class="spacer">
        <div class="service">MEETING ROOMS</div>
        <hr class="spacer">
        <div class="service">VIRTUAL OFFICE</div>
        <hr class="spacer">
        <div class="service wider">LEGAL AND CORPORATE SERVICE</div>
    </div>
    <div class="bottom" id="alamat">
        1st Floor the Jayan Building
        Jl. Affandi (Gejayan) No. 4
        Sleman, Yogyakarta 55281
    </div>
    <div class="bottom" id="kontak">
        +62.274.558156
        +62.882.0081.35797
        info@creativehub.id
        www.creativehub.id
    </div>
</div>
</body>
</html>