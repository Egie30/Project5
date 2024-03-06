<?php
include "framework/database/connect-cloud.php";
include "framework/database/connect.php";
include "framework/functions/default.php";

$broker_type = $_GET['PLAN_TYP'];

if ($_POST) {
    $j = syncTable("PRN_DIG_BRKR_PLAN_TYP", "PLAN_TYP", "CMP", "nestora1_CMP", $local, $cloud);

    $broker_type = $_POST['PLAN_TYP'];

    if ($_POST['PLAN_DESC'] == '') {
        $plan_desc = '';
    } else {
        $plan_desc = $_POST['PLAN_DESC'];
    }
    if ($_POST['MIN_Q'] == '') {
        $min_q = 0;
    } else {
        $min_q = intval($_POST['MIN_Q']);
    }
    if ($_POST['SATUAN'] == '') {
        $satuan = 0;
    } else {
        $satuan = $_POST['SATUAN'];
    }
    if ($_POST['PRC'] == '') {
        $prc = 0;
    } else {
        $prc = intval($_POST['PRC']);
    }

    $created_by = $_SESSION['userID'];
    $created_at = date('Y-m-d H:i:s');

    if ($_POST['PLAN_TYP_ID'] == '') {
        $query = "INSERT INTO PRN_DIG_BRKR_PLAN_TYP(PLAN_TYP) VALUE('$broker_type')";

        mysql_query($query);
        mysql_query($query, $cloud);
    }

    $query = "UPDATE PRN_DIG_BRKR_PLAN_TYP SET 
                PLAN_DESC = '$plan_desc', 
                SATUAN = '$satuan'
                WHERE PLAN_TYP = '$broker_type'";
    mysql_query($query);
    mysql_query($query, $cloud);

    #insert eqp_list
    $j = syncTable("PRN_DIG_BRKR_TYP_EQP", "ID", "CMP", "nestora1_CMP", $local, $cloud);
    $eqp_list = $_POST['EQP_LIST'];
    $min_q_list = $_POST['EQP_MIN_Q_LIST'];
    $prc_list = $_POST['EQP_PRC_LIST'];
    $size_eqp_list = sizeof($eqp_list);
    if ($size_eqp_list > 0) {
        for ($i = 0; $i < $size_eqp_list; $i++) {
            $query = "INSERT INTO PRN_DIG_BRKR_TYP_EQP(PLAN_TYP,PRN_DIG_EQP,MIN_Q,PRC) VALUE('$broker_type','" . $eqp_list[$i] . "'," . $min_q_list[$i] . "," . $prc_list[$i] . ")";
            mysql_query($query);
            mysql_query($query, $cloud);
        }
    }

    #insert cat_list + prc
    $j = syncTable("PRN_DIG_BRKR_TYP_CAT", "ID", "CMP", "nestora1_CMP", $local, $cloud);
    $cat_list = $_POST['CAT_LIST'];
    $prc_list = $_POST['PRC_LIST'];
    $min_q = $_POST['MIN_Q_LIST'];
    if (sizeof($cat_list) > 0) {
        $size = sizeof($cat_list);
        for ($i = 0; $i < $size; $i++) {
            $query = "INSERT INTO PRN_DIG_BRKR_TYP_CAT(PLAN_TYP,CAT_NBR,MIN_Q,PRC) VALUE('$broker_type','" . $cat_list[$i] . "'," . $min_q[$i] . "," . $prc_list[$i] . ")";
            mysql_query($query);
            mysql_query($query, $cloud);
        }
    }

    #update eqp_list
    $eqp_id = $_POST['EQP_ID'];
    $min_q = $_POST['EQP_MIN_Q'];
    $prc = $_POST['EQP_PRC'];
    if (sizeof($eqp_id) > 0) {
        $size = sizeof($eqp_id);
        for ($i = 0; $i < $size; $i++) {
            $query = "UPDATE PRN_DIG_BRKR_TYP_EQP SET "
                . "PRC = " . $prc[$i] . ","
                . "MIN_Q = " . $min_q[$i] . " "
                . "WHERE ID = " . $eqp_id[$i];
            mysql_query($query);
            mysql_query($query, $cloud);
        }
    }

    #update cat_list
    $cat_id = $_POST['CAT_ID'];
    $min_q = $_POST['MIN_Q'];
    $prc = $_POST['PRC'];
    if (sizeof($cat_id) > 0) {
        $size = sizeof($cat_id);
        for ($i = 0; $i < $size; $i++) {
            $query = "UPDATE PRN_DIG_BRKR_TYP_CAT SET "
                . "PRC = " . $prc[$i] . ","
                . "MIN_Q = " . $min_q[$i] . " "
                . "WHERE ID = " . $cat_id[$i];
            mysql_query($query);
            mysql_query($query, $cloud);
        }
    }
} else {
    $del = $_GET['DEL'];
    if (isset($del)) {
        $query = "DELETE FROM " . $_GET['TABLE'] . " WHERE ID = " . $del;
        mysql_query($query);
        mysql_query($query, $cloud);
    }

}
$query = "SELECT * FROM PRN_DIG_BRKR_PLAN_TYP WHERE PLAN_TYP = '" . $broker_type . "'";
$rs = mysql_query($query);
$row = mysql_fetch_array($rs);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8"/>
    <script>parent.Pace.restart();</script>
    <link rel="stylesheet" type="text/css" media="screen" href="css/screen.css"/>
    <link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css"/>
    <link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css"/>
    <link rel="stylesheet" href="framework/combobox/chosen.css"/>

    <script type="text/javascript" src="framework/functions/default.js"></script>
    <script src="framework/database/jquery.min.js"></script>
</head>
<body>
<?php if (($Security == 0) && ($broker_type != '0')) { ?>
    <div class="toolbar-only">
        <p class="toolbar-left">
            <a href="javascript:void(0)" onclick="window.scrollTo(0, 0);
                    parent.document.getElementById('addressDelete').style.display = 'block';
                    parent.document.getElementById('fade').style.display = 'block'"><span class='fa fa-trash toolbar'
                                                                                          style="cursor:pointer"></span></a>
        </p>
    </div>
<?php } ?>
<form action="#" method="post" style="width:700px" autocomplete="off">
    <input type="hidden" name="PLAN_TYP_ID" value="<?php echo $row['PLAN_TYP']; ?>"/>
    <p>
    <h2>
        <?php
        echo $row['PLAN_TYP'] == "" ? "Baru" : $row['PLAN_TYP'];
        ?>
    </h2>
    <label class="side">Kode Broker</label>
    <input name="PLAN_TYP" value="<?php echo $row['PLAN_TYP']; ?>" type="text" size="30" autofocus=""/><br/>
    <label class="side">Keterangan</label>
    <input name="PLAN_DESC" value="<?php echo $row['PLAN_DESC']; ?>" type="text" size="50"/><br/>
    <div class="side">
        <label class="side">Perhitungan Pendapatan</label>
        <select name="SATUAN" class="chosen-select" style="width:80px;" onchange="selectDetail(this);">
            <option value="METER" <?php echo $row['SATUAN'] == 'METER' ? 'selected=""' : ''; ?> >Meter</option>
            <option value="RUPIAH" <?php echo $row['SATUAN'] == 'RUPIAH' ? 'selected=""' : ''; ?> >Rupiah</option>
        </select>
    </div>
    <div class="labelbox"></div>
    <div id="detail-eqp">
        <label class="side detail-eqp">Equipment</label>
        <select name="PRN_DIG_EQP" class="chosen-select detail-eqp" id="combo-eqp">
            <?php
            $query = "SELECT * FROM PRN_DIG_EQP";
            genCombo($query, "PRN_DIG_EQP", "PRN_DIG_EQP_DESC", $row['PRN_DIG_EQP']);
            ?>
        </select>
        <div class="listable-btn detail-eqp" id="btn-add-eqp">
            <span class="fa fa-plus listable-btn"></span>
        </div>
        <br/><br/>
    </div>
    <div id="detail-cat">
        <label class="side">Kategori Retail</label>
        <select name="CATEGORY" class="chosen-select" id="combo-cat" style="width: 200px;">
            <?php
            $query = "SELECT * FROM RTL.CAT";
            genCombo($query, "CAT_NBR", "CAT_DESC", null);
            ?>
        </select>
        <div class="listable-btn" id="btn-add-cat">
            <span class="fa fa-plus listable-btn"></span>
        </div>
        <br/><br/>
    </div>

    <table style="width: 700px;" id="table-eqp">
        <thead>
        <tr>
            <th class="listable" style="width: 120px;">Equipment</th>
            <th class="listable" style="width: 280px;">Keterangan</th>
            <th class="listable" style="width: 100px;">Minimal</th>
            <th class="listable" style="width: 100px;">Komisi</th>
            <th class="listable" style="width: 10px;"></th>
        </tr>
        </thead>
        <tbody>
        <?php
        $query = "SELECT B.ID,B.PRN_DIG_EQP, E.PRN_DIG_EQP_DESC, B.MIN_Q, B.PRC
                                    FROM PRN_DIG_BRKR_TYP_EQP B 
                                    LEFT JOIN PRN_DIG_EQP E ON B.PRN_DIG_EQP = E.PRN_DIG_EQP
                                    WHERE B.PLAN_TYP = '" . $row['PLAN_TYP'] . "' ";
        $rs = mysql_query($query);
        while ($row_eqp = mysql_fetch_array($rs)) {
            ?>
            <tr>
                <td><?php echo $row_eqp['PRN_DIG_EQP']; ?></td>
                <td>
                    <?php echo $row_eqp['PRN_DIG_EQP_DESC']; ?>
                </td>
                <td>
                    <input type="hidden" name="EQP_ID[]" value="<?php echo $row_eqp['ID']; ?>"/>
                    <input type="text" name="EQP_MIN_Q[]" value="<?php echo $row_eqp['MIN_Q']; ?>" placeholder="(meter)"
                           style="text-align: right;"/>
                </td>
                <td>
                    <input type="text" name="EQP_PRC[]" value="<?php echo $row_eqp['PRC']; ?>"
                           placeholder="(persen/nominal)" style="text-align: right;"/>
                </td>
                <td>
                    <i class="fa fa-trash" style="cursor: pointer;float: right;color: #989898;"
                       onclick="location.href = 'print-digital-komisi-edit.php?PLAN_TYP=<?php echo $row['PLAN_TYP']; ?>&DEL=<?php echo $row_eqp['ID'] . '&TABLE=PRN_DIG_BRKR_TYP_EQP'; ?>'"></i>
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>

    <table style="width: 500px;" id="table-cat">
        <thead>
        <tr>
            <th class="listable" style="width: 290px;">Category</th>
            <th class="listable" style="width: 100px;">Minimal</th>
            <th class="listable" style="width: 100px;">Komisi</th>
            <th class="listable" style="width: 10px;"></th>
        </tr>
        </thead>
        <tbody>
        <?php
        $query = "SELECT B.ID, C.CAT_NBR, C.CAT_DESC,B.MIN_Q, B.PRC
                                    FROM PRN_DIG_BRKR_TYP_CAT B 
                                    LEFT JOIN RTL.CAT C ON B.CAT_NBR = C.CAT_NBR
                                    WHERE B.PLAN_TYP = '" . $row['PLAN_TYP'] . "' ";
        $rs = mysql_query($query);
        while ($row_eqp = mysql_fetch_array($rs)) {
            ?>
            <tr>
                <td><?php echo $row_eqp['CAT_DESC']; ?></td>
                <td>
                    <input type="hidden" name="CAT_ID[]" value="<?php echo $row_eqp['ID']; ?>"/>
                    <input type="text" name="MIN_Q[]" value="<?php echo $row_eqp['MIN_Q']; ?>"
                           style="text-align: right;"/>
                </td>
                <td>
                    <input type="text" name="PRC[]" value="<?php echo $row_eqp['PRC']; ?>" style="text-align: right;"/>
                </td>
                <td>
                    <i class="fa fa-trash" style="cursor: pointer;float: right;color: #989898;"
                       onclick="location.href = 'print-digital-komisi-edit.php?PLAN_TYP=<?php echo $row['PLAN_TYP']; ?>&DEL=<?php echo $row_eqp['ID'] . '&TABLE=PRN_DIG_BRKR_TYP_CAT'; ?>'"></i>
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <input id="submit_button" class="process submit_button" type="submit" value="Simpan"/>
    </p>
</form>

<script src="framework/combobox/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('.chosen-select').chosen();
        $('#btn-add-eqp').click(function () {
            var val = $('#combo-eqp').val();
            var text = $('#combo-eqp option:selected').text();

            var tr = '<tr>';
            tr += '<td>' + val + '<input type="hidden" name="EQP_LIST[]" value="' + val + '"/></td>';
            tr += '<td>' + text + '</td>';
            tr += '<td><input type="text" name="EQP_MIN_Q_LIST[]" placeholder="(meter)"/></td>';
            tr += '<td><input type="text" name="EQP_PRC_LIST[]"/></td>';
            tr += '<td><i style="cursor:pointer;float:right;color: #989898;" onclick="removeEqp(this.parentElement);" class="fa fa-trash listable-btn"></i></td>';
            tr += '</tr>';

            $('#table-eqp > tbody').append(tr);
        });

        $('#btn-add-cat').click(function () {
            var val = $('#combo-cat').val();
            var text = $('#combo-cat option:selected').text();

            var tr = '<tr>';
            tr += '<td>' + text + '<input type="hidden" name="CAT_LIST[]" value="' + val + '"/></td>';
            tr += '<td><input placeholder="rupiah" type="text" name="MIN_Q_LIST[]" style="text-align: right;"/></td>';
            tr += '<td><input placeholder="%" type="text" name="PRC_LIST[]" style="text-align: right;"/></td>';
            tr += '<td><i style="cursor:pointer;float:right;color: #989898;" onclick="removeEqp(this.parentElement);" class="fa fa-trash listable-btn"></i></td>';
            tr += '</tr>';

            $('#table-cat > tbody').append(tr);
        });

        var satuan = '<?php echo $row['SATUAN']; ?>';
        if (satuan == 'RUPIAH') {
            $('#detail-eqp').hide();
            $('#table-eqp').hide();
        } else {
            $('#detail-cat').hide();
            $('#table-cat').hide();

        }
    });

    function removeEqp(e) {
        $(e.parentElement).remove();
    }

    function selectDetail(e) {
        var val = $(e).val();
        if (val == 'RUPIAH') {
            $('#detail-eqp').hide();
            $('#detail-cat').show();

            $('#table-eqp').hide();
            $('#table-cat').show();
        } else {
            $('#detail-eqp').show();
            $('#detail-cat').hide();

            $('#table-eqp').show();
            $('#table-cat').hide();
        }
    }
    parent.document.getElementById('addressDeleteYes').onclick =
        function () {
            parent.document.getElementById('content').src = 'print-digital-komisi.php?DEL_A=<?php echo $broker_type ?>';
            parent.document.getElementById('addressDelete').style.display = 'none';
            parent.document.getElementById('fade').style.display = 'none';
        }
</script>
</body>
</html>