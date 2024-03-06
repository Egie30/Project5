<?php
include "framework/database/connect.php";
include "framework/functions/komisi.php";
include "framework/functions/default.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8"/>
    <script>parent.Pace.restart();</script>
    <link rel="stylesheet" type="text/css" media="screen" href="css/screen.css"/>
    <link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css"/>
    <link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css"/>
    <link rel="stylesheet" href="framework/combobox/chosen.css">
<style>
        table#mainTable tr:nth-child(even) {
            background: #F6F6F6;
        }
    </style>
    <script type="text/javascript" src="framework/functions/default.js"></script>
    <script src="framework/database/jquery.min.js"></script>
    <script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
    <script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
    <script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
</head>
<body>
<br/>
<select name="BROKER" class="chosen-select" onchange="change(this.value)" style="width: 250px;margin-top: 20px;">
    <option value="">Semua</option>
    <?php
    $query = "SELECT PRSN_NBR,NAME
                FROM PEOPLE ppl
                RIGHT JOIN prn_dig_brkr_plan_typ typ ON typ.PLAN_TYP = ppl.BRKR_PLAN_TYP";
    $rs = mysql_query($query);
    while ($row = mysql_fetch_array($rs)) {
        if ($_GET['PRSN_NBR']) {
            if ($row['PRSN_NBR'] == $_GET['PRSN_NBR']) {
                $select = 'selected=""';
            } else {
                $select = '';
            }
        }
        ?>
        <option <?php echo $select; ?> value="<?php echo $row['PRSN_NBR']; ?>"><?php echo $row['NAME']; ?></option>
        <?php
    }
    ?>
</select>
<table id="mainTable" class="tablesorter searchTable">
    <thead>
    <tr>
        <th>No. Nota</th>
        <th>Perusahaan</th>
        <th>Jenis Print</th>
        <th>Equipment</th>
        <th>Total Meter</th>
        <th>Broker</th>
    </tr>
    </thead>
    <tbody>
    <?php

    $grand_total_meter = 0;
    $grand_total_komisi = 0;

    $total_meter = 0;
    $total_komisi = 0;

    if ($_GET['PRSN_NBR']) {
        $result = json_decode(calcKomisiPrint(date('m'), date('Y'), $_GET['PRSN_NBR']));
    } else {
        $result = json_decode(calcKomisiPrint(date('m'), date('Y')));
    }

    $i = 0;
    $size = sizeof($result);
    foreach ($result as $key => $value) {
        ?>
        <tr>
            <td><?php echo $value->ORD_NBR; ?></td>
            <td><?php echo $value->BUY_CO_NAME; ?></td>
            <td><?php echo $value->PRN_DIG_DESC; ?></td>
            <td><?php echo $value->PRN_DIG_EQP_DESC; ?></td>
            <td align="right"><?php echo number_format($value->TOTAL_METER, 2, ',', '.'); ?></td>
            <td><?php echo shortName($value->PRSN_NAME) . ' (' . $value->BRKR_PLAN_TYP . ')'; ?></td>
        </tr>
        <?php

        $total_meter += $value->TOTAL_METER;
        $total_komisi += $value->KOMISI;

        if ($result[$i]->PRN_DIG_EQP != $result[$i + 1]->PRN_DIG_EQP || $i==$size-1) {
            ?>
            <tr>
                <td align="right" colspan="4" style="font-weight: bold;">Total Meter:</td>
                <td align="right"
                    style="font-weight: bold;"><?php echo number_format($total_meter, 0, ',', '.'); ?></td>
                <td></td>
            </tr>
            <tr>
                <td align="right" colspan="4" style="font-weight: bold;">Minimal Meter:</td>
                <td align="right"
                    style="font-weight: bold;"><?php echo number_format($value->MIN_Q, 0, ',', '.'); ?></td>
                <td></td>
            </tr>
            <tr>
                <td align="right" colspan="4" style="font-weight: bold;">Nett Meter:</td>
                <td align="right"
                    style="font-weight: bold;"><?php echo number_format($total_meter - $value->MIN_Q, 0, ',', '.'); ?></td>
                <td></td>
            </tr>
            <tr>
                <td align="right" colspan="4" style="font-weight: bold;">Komisi:</td>
                <td align="right"
                    style="font-weight: bold;"><?php echo number_format($total_komisi, 0, ',', '.'); ?></td>
                <td></td>
            </tr>
            <?php
            $total_meter = 0;
            $total_komisi = 0;
        }

        $i++;
        $grand_total_meter += $value->TOTAL_METER;
        $grand_total_komisi += $value->KOMISI;
    }
    ?>
    </tbody>
    <tfoot>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td align="right" colspan="4" style="font-weight: bold;">Grand Total Meter:</td>
        <td align="right" style="font-weight: bold;"><?php echo number_format($grand_total_meter, 0, ',', '.'); ?></td>
        <td></td>
    </tr>
    <tr>
        <td align="right" colspan="4" style="font-weight: bold;">Grand Total Komisi:</td>
        <td align="right" style="font-weight: bold;"><?php echo number_format($grand_total_komisi, 0, ',', '.'); ?></td>
        <td></td>
    </tr>
    </tfoot>
</table>
</div>
<script src="framework/combobox/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('.chosen-select').chosen();
    });
    function change(prsn_nbr) {
        location.href = 'commission-broker-report.php?PRSN_NBR=' + prsn_nbr;
    }
</script>
</body>
</html>