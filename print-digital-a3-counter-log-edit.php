<?php
include "framework/database/connect.php";
include "framework/functions/default.php";

$NBR = $_GET['NBR'];
$change = "MALAM";
if ($_POST) {

    $PRSN_NBR = $_SESSION['personNBR'];
    $NBR = $_POST['NBR'];

    if ($_POST['LOG_DTE'] == null) {
        $LOG_DTE = date('Y-m-d');
    } else {
        $LOG_DTE = $_POST['LOG_DTE'];
    }
    if ($_POST['FC_PAGI'] == null) {
        $FC_PAGI = 0;
    } else {
        $FC_PAGI = $_POST['FC_PAGI'];
    }
    if ($_POST['FC_MALAM'] == null) {
        $FC_MALAM = 0;
    } else {
        $FC_MALAM = $_POST['FC_MALAM'];
    }
    if ($_POST['BW_PAGI'] == null) {
        $BW_PAGI = 0;
    } else {
        $BW_PAGI = $_POST['BW_PAGI'];
    }
    if ($_POST['BW_MALAM'] == null) {
        $BW_MALAM = 0;
    } else {
        $BW_MALAM = $_POST['BW_MALAM'];
    }
    if ($_POST['TC_PAGI'] == null) {
        $TC_PAGI = 0;
    } else {
        $TC_PAGI = $_POST['TC_PAGI'];
    }
    if ($_POST['TC_MALAM'] == null) {
        $TC_MALAM = 0;
    } else {
        $TC_MALAM = $_POST['TC_MALAM'];
    }
    if ($_POST['PRN_DIG_EQP'] == null) {
        $PRN_DIG_EQP = 0;
    } else {
        $PRN_DIG_EQP = $_POST['PRN_DIG_EQP'];
    }

    if ($NBR == 0) {
        $rs = mysql_query("SELECT MAX(NBR) AS NBR FROM PRN_DIG_A3_CNTR_LOG");
        $row = mysql_fetch_array($rs);

        $NBR = $row['NBR'] + 1;
        mysql_query("INSERT INTO PRN_DIG_A3_CNTR_LOG(NBR) VALUE(" . $NBR . ")");
    }

    if ($_POST['WAKTU'] == 'PAGI') {
        $query = "UPDATE PRN_DIG_A3_CNTR_LOG SET
                LOG_DTE = '" . $LOG_DTE . "',
                PRN_DIG_EQP = '" . $PRN_DIG_EQP . "',
                FC_OPN = " . $FC_PAGI . ",
                BW_OPN = " . $BW_PAGI . ",
                TC_OPN = " . $TC_PAGI . ",
                PRSN_NBR_OPN = " . $PRSN_NBR . ",
                UPD_TS_OPN = NOW()
                WHERE NBR = " . $NBR;
    } else {
        $query = "UPDATE PRN_DIG_A3_CNTR_LOG SET
                LOG_DTE = '" . $LOG_DTE . "',
                PRN_DIG_EQP = '" . $PRN_DIG_EQP . "',
                FC_CLSE = " . $FC_MALAM . ",
                BW_CLSE = " . $BW_MALAM . ",
                TC_CLSE = " . $TC_MALAM . ",
                PRSN_NBR_CLSE = " . $PRSN_NBR . ",
                UPD_TS_CLSE = NOW()
                WHERE NBR = " . $NBR;
    }

    mysql_query($query);

    $change = $_POST['WAKTU'];
}
$rs = mysql_query("SELECT * FROM PRN_DIG_A3_CNTR_LOG WHERE NBR = " . $NBR);
$row = mysql_fetch_array($rs);
?>
<!DOCTYPE html>
<html>
<head>
    <script>parent.Pace.restart();</script>
    <link rel="stylesheet" type="text/css" media="screen" href="css/screen.css"/>
    <link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css"/>
    <link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css"/>
    <link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css"
          media="screen"/>
    <link rel="stylesheet" href="framework/combobox/chosen.css"/>

    <style>
        <?php
        if ($row['FC_OPN'] != null) {
        ?>
        .input-pagi {
            display: none;
        }

        <?php
        }else{
        ?>
        .input-malam {
            display: none;
        }

        <?php
        }
        ?>
    </style>

    <script type="text/javascript" src="framework/validation/mootools-1.2.3.js"></script>
    <script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
    <script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
    <script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>

    <script src="framework/database/jquery.min.js"></script>
    <script src="framework/combobox/chosen.jquery.js" type="text/javascript"></script>

    <script type="text/javascript">
        jQuery.noConflict();

        window.addEvent('domready', function () {
            //Datepicker
            new CalendarEightysix('textbox-id');
            //Calendar
            new CalendarEightysix('block-element-id');
        });
        MooTools.lang.set('id-ID', 'Date', {
            months: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
            days: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
            dateOrder: ['date', 'month', 'year', '/']
        });
        MooTools.lang.setLanguage('id-ID');

        jQuery(document).ready(function () {
            jQuery('.chosen-select').chosen();
            jQuery('#addressDeleteYes', parent.document).click(function () {
                parent.document.getElementById('content').src = 'print-digital-a3-counter-log.php?DEL=<?php echo $row['NBR'];?>';
                parent.document.getElementById('addressDelete').style.display = 'none';
                parent.document.getElementById('fade').style.display = 'none';
            });
        });

    </script>
</head>
<body>
<div class="toolbar-only">
    <?php
    if ($row['NBR'] != 0) {
        ?>
        <p class="toolbar-left">
            <a href="javascript:void(0)"
               onclick="window.scrollTo(0,0);parent.document.getElementById('addressDelete').style.display='block';parent.document.getElementById('fade').style.display='block'">
                <span class="fa fa-trash toolbar" style="cursor:pointer"></span>
            </a>
        </p>
        <?php
    }
    ?>
</div>
<form action="" method="POST" style="width:700px" autocomplete="off">
    <br/>
    <input type="hidden" name="NBR" value="<?php echo $row['NBR']; ?>"/>

    <label>Tanggal</label><br/>
    <input name="LOG_DTE" value="<?php echo $row['LOG_DTE']; ?>" type="text" size="30" id="DTE"/><br/>
    <script>
        new CalendarEightysix('DTE', {
            'offsetY': -5,
            'offsetX': 2,
            'format': '%Y-%m-%d',
            'slideTransition': Fx.Transitions.Back.easeOut,
            'draggable': true
        });
    </script>

    <label>Waktu</label><br/>
    <select name="WAKTU" class="chosen-select" style="width: 200px;" id="select-waktu">
        <?php
        if ($row['FC_OPN'] != null) {
            ?>
            <option value="PAGI">Pagi</option>
            <option value="MALAM" selected>Malam</option>
            <?php
        } else {
            ?>
            <option value="PAGI">Pagi</option>
            <option value="MALAM">Malam</option>
            <?php
        }
        ?>
    </select><br/>
    <div class="combobox"></div>

    <label>Mesin</label><br/>
    <select name="PRN_DIG_EQP" class="chosen-select" style="width: 200px;">
        <?php
        $query = "SELECT * FROM PRN_DIG_EQP";
        if ($NBR == 0) {
            $selected = 'KMC6501';
        } else {
            $selected = $row['PRN_DIG_EQP'];
        }
        genCombo($query, "PRN_DIG_EQP", "PRN_DIG_EQP_DESC", $selected);
        ?>
    </select><br/>
    <div class="combobox"></div>

    <?php
    $ro_malam = "";
    if ($NBR == 0) {
        $ro_malam = 'readonly';
    }
    ?>

    <div class="input-pagi">
        <label>Full Color</label><br/>
        <input name="FC_PAGI" value="<?php echo $row['FC_OPN']; ?>" type="text" size="30" id="FC_PAGI"
               onkeyup="calcPagi()"/><br/>
        <label>Black/ White</label><br/>
        <input name="BW_PAGI" value="<?php echo $row['BW_OPN']; ?>" type="text" size="30" id="BW_PAGI"
               onkeyup="calcPagi()"/><br/>
    </div>

    <div class="input-malam">
        <label>Full Color</label><br/>
        <input name="FC_MALAM" value="<?php echo $row['FC_CLSE']; ?>" type="text" size="30" id="FC_MALAM"
               onkeyup="calcMalam()" <?php echo $ro_malam; ?>/><br/>
        <label>Black/ White</label><br/>
        <input name="BW_MALAM" value="<?php echo $row['BW_CLSE']; ?>" type="text" size="30" id="BW_MALAM"
               onkeyup="calcMalam()" <?php echo $ro_malam; ?>/><br/>
    </div>

    <div class="input-pagi">
        <label>Total Counter</label><br/>
        <input name="TC_PAGI" value="<?php echo $row['TC_OPN']; ?>" type="text" size="30" id="TC_PAGI" readonly/><br/>
    </div>

    <div class="input-malam">
        <label>Total Counter</label><br/>
        <input name="TC_MALAM" value="<?php echo $row['TC_CLSE']; ?>" type="text" size="30" id="TC_MALAM"
               readonly/><br/>
    </div>

    <input class="process" type="submit" value="Simpan"/>
</form>

<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery('#select-waktu').chosen().change(function () {
            var val = this.value;

            if (val == 'MALAM') {
                jQuery('.input-pagi').hide();
                jQuery('.input-malam').show();
            } else {
                jQuery('.input-pagi').show();
                jQuery('.input-malam').hide();
            }
        });

        var change = '<?php echo $change;?>';
        if (change == 'PAGI') {
            jQuery('#select-waktu').val('PAGI');
            jQuery('#select-waktu').trigger('chosen:updated');
            jQuery('.input-pagi').show();
            jQuery('.input-malam').hide();
        }
    });
    function calcPagi() {
        var fc = jQuery('#FC_PAGI').val() == "" ? 0 : parseInt(jQuery('#FC_PAGI').val());
        var bw = jQuery('#BW_PAGI').val() == "" ? 0 : parseInt(jQuery('#BW_PAGI').val());

        var tc = fc + bw;

        jQuery('#TC_PAGI').val(tc);
    }
    function calcMalam() {
        var fc = jQuery('#FC_MALAM').val() == "" ? 0 : parseInt(jQuery('#FC_MALAM').val());
        var bw = jQuery('#BW_MALAM').val() == "" ? 0 : parseInt(jQuery('#BW_MALAM').val());

        var tc = fc + bw;

        jQuery('#TC_MALAM').val(tc);
    }
</script>
</body>
</html>