<?php

include "framework/database/connect.php";

$RoomNbr = mysql_escape_string($_GET['ID']);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $RoomNbr = mysql_escape_string($_POST['ROOM_ID']);
    $RoomDesc = mysql_escape_string($_POST['NAME']);
    $Capacity = mysql_escape_string($_POST['CPCTY']);
    $Color = mysql_escape_string($_POST['COLOR']);

    if ($Capacity == "") {
        $Capacity = "NULL";
    }

    if ($RoomNbr == "-1") {
        $query = "INSERT INTO CMP.ROOM (RM_DESC, RM_CPCTY, RM_COLR, CRT_TS, CRT_NBR) 
        VALUES ('" . $RoomDesc . "'," . $Capacity . "'" . $Color . "',CURRENT_TIMESTAMP," . $_SESSION['personNBR']
            . ")";
        mysql_query($query);
//        echo "<pre>" . $query . "</pre>";
        $query = "SELECT MAX(RM_NBR) AS NBR FROM CMP.ROOM";
        $result = mysql_fetch_assoc(mysql_query($query));
        $RoomNbr = $result['NBR'];
    } else {
        $query = "UPDATE CMP.ROOM
        SET RM_DESC='" . $RoomDesc . "',
        RM_CPCTY=" . $Capacity . ",
        RM_COLR='" . $Color . "',
        UPD_TS=CURRENT_TIMESTAMP,
        UPD_NBR=" . $_SESSION['personNBR'] . "
        WHERE RM_NBR=" . $RoomNbr;
        mysql_query($query);
    }
//    echo $query;
}

$title = "Edit Room";
$nbr = $RoomNbr;
if ($RoomNbr == "-1") {
    $title = "Room Baru";
    $nbr = "Baru";
} else {
    $query = "SELECT * FROM CMP.ROOM WHERE RM_NBR=" . $RoomNbr;
    $result = mysql_query($query);
    $row = mysql_fetch_assoc($result);
    $RoomDesc = $row['RM_DESC'];
    $Capacity = $row['RM_CPCTY'];
    $Color = $row['RM_COLR'];
}
?>

<HTML lang=en xml:lang="en" xmlns="http://www.w3.org/1999/xhtml">
<HEAD>

    <SCRIPT>if (top.Pace && !top.Pace.running) top.Pace.restart()</SCRIPT>
    <LINK rel=stylesheet type=text/css href="css/screen.css" media=screen>
    <LINK rel=stylesheet href="css/font-awesome-4.4.0/css/font-awesome.min.css">
    <LINK rel=stylesheet href="framework/tablesorter/themes/nestor/style.css">
    <LINK rel=stylesheet href="framework/combobox/chosen.css">
    <STYLE>
        h2 {
            margin-block-start: 0;
            padding-top: 0.83em;
        }

        span.toolbar {
            cursor: pointer;
        }
    </STYLE>


    <?php
    if ($RoomNbr != '-1') { ?>
        <DIV class=toolbar-only>
            <P class=toolbar-left>
                <A onclick=deleteAction() href="javascript:void(0)">
                    <SPAN class="fa fa-trash toolbar"></SPAN>
                </A>
            </P>
        </DIV>
        <?php
    } ?>
    <!--<h3>Kategori: --><?php
    //= $CATEGORY ?><!--</h3>-->

    <FORM class=mainResult method=post action="">
        <P></P>
        <H2><?php echo $title ?></H2>
        <INPUT type=hidden value="<?php echo $RoomNbr ?>" name=ROOM_ID>
        <BR>
        <DIV>
            <LABEL for=NAMA>Nama Room</LABEL><BR>
            <INPUT id=NAME value="<?php echo $RoomDesc ?>" name=NAME>
        </DIV>
        <DIV>
            <LABEL for=CPCTY>Kapasitas</LABEL><BR>
            <INPUT id=CPCTY value="<?php echo $Capacity ?>" name=CPCTY>
        </DIV>
        <BR>
        <div>
            <label for=COLOR>Warna</label><BR>
            <input id=COLOR value="<?php echo $Color ?>" name=COLOR type="color">
        </div>
        <INPUT class=process type=submit value=Simpan>
    </FORM>
    <SCRIPT type=text/javascript src="framework/database/jquery.min.js"></SCRIPT>
    <SCRIPT type=text/javascript src="framework/combobox/chosen.jquery.js"></SCRIPT>
    <SCRIPT>
      window.onload = () => {
        window.focus()
        if (top.Pace) top.Pace.stop()
      }

      top.document.getElementById('catDeleteYes').onclick = function () {
        window.location = "creativehub-rooms.php?DEL=<?php echo $RoomNbr ?>"
        top.document.getElementById('catDelete').style.display = 'none'
        top.document.getElementById('fade').style.display = 'none'
      }

      const deleteAction = function (e) {
        window.scrollTo(0, 0)
        top.document.getElementById('catDelete').style.display = 'block'
        top.document.getElementById('fade').style.display = 'block'
      }

      const config = {
        '.chosen-select': {},
        '.chosen-select-deselect': { allow_single_deselect: true },
        '.chosen-select-no-single': { disable_search_threshold: 10 },
        '.chosen-select-no-results': { no_results_text: 'Data tidak ketemu' },
        '.chosen-select-width': { width: '95%' },
      }
      for (let selector in config) {
        $(selector).chosen(config[selector])
      }

    </SCRIPT>


    
