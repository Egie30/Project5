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

    <script type="text/javascript" src="framework/functions/default.js"></script>
    <script src="framework/database/jquery.min.js"></script>
    <script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
    <script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
    <script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
</head>
<body>
<br/>
<select name="BROKER" class="chosen-select" onchange="change(this.value)" style="width: 250px;">
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
    <th>No. Nota</th>
    <th>Perusahaan</th>
    <th>Category</th>
    <th>Pendapatan Minimal</th>
    <th>Harga Komisi</th>
    <th>Total Pendapatan</th>
    <th>Komisi</th>
    <th>Broker</th>
    </thead>
    <tbody>
    <?php
    $total_pendapatan = 0;
    $total_komisi = 0;

    if ($_GET['PRSN_NBR']) {
        $result_sales = json_decode(calcKomisiSales(date('m'), date('Y'), $_GET['PRSN_NBR']));
    } else {
        $result_sales = json_decode(calcKomisiSales(date('m'), date('Y')));
    }

    foreach ($result_sales as $key => $value) {
        ?>
        <tr>
            <td><?php echo $value->ORD_NBR; ?></td>
            <td><?php echo $value->COMPANY; ?></td>
            <td><?php echo $value->CATEGORY; ?></td>
            <td align="right"><?php echo number_format($value->MIN_Q, 0, ',', '.'); ?></td>
            <td align="right"><?php echo number_format($value->PRC, 0, ',', '.') . '%'; ?></td>
            <td align="right"><?php echo number_format($value->SUB_TOTAL, 0, ',', '.'); ?></td>
            <td align="right"><?php echo number_format($value->KOMISI, 0, ',', '.'); ?></td>
            <td><?php echo shortName($value->PRSN_NAME) . ' (' . $value->BRKR_PLAN_TYP . ')'; ?></td>
        </tr>
        <?php
        $total_pendapatan += $value-SUB_TOTAL;
        $total_komisi += $value->KOMISI;
    }


    if ($_GET['PRSN_NBR']) {
        $result_retail = json_decode(calcKomisiRetail(date('m'), date('Y'), $_GET['PRSN_NBR']));
    } else {
        $result_retail = json_decode(calcKomisiRetail(date('m'), date('Y')));
    }

    foreach ($result_retail as $key => $value) {
        ?>
        <tr>
            <td><?php echo $value->ORD_NBR; ?></td>
            <td><?php echo $value->COMPANY; ?></td>
            <td><?php echo $value->CATEGORY; ?></td>
            <td align="right"><?php echo number_format($value->MIN_Q, 0, ',', '.'); ?></td>
            <td align="right"><?php echo number_format($value->PRC, 0, ',', '.') . '%'; ?></td>
            <td align="right"><?php echo number_format($value->TOTAL, 0, ',', '.'); ?></td>
            <td align="right"><?php echo number_format($value->KOMISI, 0, ',', '.'); ?></td>
            <td><?php echo shortName($value->PRSN_NAME) . ' (' . $value->BRKR_PLAN_TYP . ')'; ?></td>
        </tr>
        <?php
        $total_pendapatan += $value->TOTAL;
        $total_komisi += $value->KOMISI;
    }
    ?>
    </tbody>
    <tfoot>
    <tr>
        <td colspan="5" align="right"><b>Total :</b></td>
        <td align="right"><b><?php echo number_format($total_pendapatan, 0, ',', '.'); ?></b></td>
        <td align="right"><b><?php echo number_format($total_komisi, 0, ',', '.'); ?></b></td>
    </tr>
    </tfoot>
</table>
</div>
<script src="framework/combobox/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $("#mainTable").tablesorter({widgets: ["zebra"]});
        $('.chosen-select').chosen();
    });
    function change(prsn_nbr) {
        location.href = 'commission-canvaser-report.php?PRSN_NBR=' + prsn_nbr;
    }
</script>
</body>
</html>