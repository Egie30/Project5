<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/security/default.php";

$security    = getSecurity($_SESSION['userID'], "Accounting");
$glNumber = $_GET['GL_NBR'];
$glDetailNumber = $_GET['GL_DET_NBR'];
$changed     = false;
$addNew      = ($glDetailNumber == -1);

//Process changes here
if ($_POST['GL_DET_NBR'] != "") {
    $glDetailNumber = $_POST['GL_DET_NBR'];

    //Take care of nulls
    if ($_POST['DEB'] == "") {
        $totalDebit = "NULL";
    } else {
        $totalDebit = $_POST['DEB'];
    }

    if ($_POST['CRT'] == "") {
        $totalKredit = "NULL";
    } else {
        $totalKredit = $_POST['CRT'];
    }
    
    $query = "SELECT COUNT(DISTINCT GL_DET_NBR) AS GL_TOT, SUM(DEB - CRT) AS NETT
            FROM RTL.ACCTG_GL_DET WHERE GL_NBR=" . $glNumber . " AND GL_DET_NBR !=" . $glDetailNumber;
    $result = mysql_query($query);
    $row = mysql_fetch_array($result);

    if ($row['GL_TOT'] > 0 && $row['GL_TOT'] % 2 == 1 && ($row['NETT'] + ($totalDebit - $totalKredit)) !== 0) {
        $invalid = true;
    }
    
    //Process add new
    if ($glDetailNumber == -1) {
        $addNew    = true;
        $query     = "SELECT COALESCE(MAX(GL_DET_NBR),0)+1 AS NEW_NBR FROM RTL.ACCTG_GL_DET";
        $result    = mysql_query($query);
        $row       = mysql_fetch_array($result);

        $glDetailNumber = $row['NEW_NBR'];

        $query     = "INSERT INTO RTL.ACCTG_GL_DET (GL_DET_NBR, GL_NBR, UPD_NBR) VALUES (" . $glDetailNumber . ", " . $glNumber . ", " . $_SESSION['personNBR'] . ")";
        $result    = mysql_query($query);
    }
        
    $query   = "UPDATE RTL.ACCTG_GL_DET SET
            CD_SUB_NBR=" . $_POST['CD_SUB_NBR'] . ",
            DEB=" . $totalDebit . ",
            CRT=" . $totalKredit . ",
            UPD_NBR=" . $_SESSION['personNBR'] . "
        WHERE GL_DET_NBR=" . $glDetailNumber;
    $result  = mysql_query($query);

    $changed = true;
}

if ($changed && $addNew) {
	$glDetailNumber = -1;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
    <link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
    <link rel="stylesheet" type="text/css" href="framework/combobox/chosen.css">
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
		
    <script type="text/javascript">parent.Pace.restart();</script>
    <script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
    <script type="text/javascript" src="framework/fieldsetClone/fieldsetClone.js"></script>
    <script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
    <script type="text/javascript" src="framework/functions/default.js"></script>
    <script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>
    <script type="text/javascript" src="framework/combobox/chosen.default.js"></script>

    <style type="text/css">
        table tr td {
            min-width: 75px;
        }
    </style>
</head>
<body>
<?php if ($changed) { ?>
<script type="text/javascript">
	parent.document.getElementById('content').contentDocument.getElementById('refresh-list').click();
</script>
<?php } ?>

<?php if ($changed) {?>
<script type="text/javascript">
    // Let's move scroll position to the bottom
    setTimeout(function() {
        parent.window[1].scroll(
			0,
            parent.document.getElementById('content').contentDocument.getElementById('<?php echo $glDetailNumber;?>').getTop()
        );
    }, 1000);
</script>
<?php } ?>

<div style="height:100%;width:440px;overflow:auto">
<span class='fa fa-times toolbar' style='margin-left:10px' onclick="pushFormOut();"></span>
<?php
$query = "SELECT DET.GL_DET_NBR,
    DET.GL_NBR,
    ACC.ACC_NBR,
    ACC.ACC_DESC,
    ACC.CD_NBR,
    ACC.CD_DESC,
    ACC.CD_SUB_NBR,
    ACC.CD_SUB_DESC,
    COALESCE(DET.DEB, 0) AS DEB,
    COALESCE(DET.CRT, 0) AS CRT,
    COALESCE(DET.DEB, 0) - COALESCE(DET.CRT, 0) AS NETT,
    DET.UPD_TS,
    DET.UPD_NBR
FROM RTL.ACCTG_GL_DET DET
    INNER JOIN(
        SELECT SUB.CD_SUB_NBR, SUB.CD_SUB_ACC_NBR, SUB.CD_SUB_DESC, ACC.CD_NBR, ACC.CD_ACC_NBR, ACC.CD_DESC, CAT.CD_CAT_NBR, CAT.CD_CAT_DESC,
            CONCAT(CAT.CD_CAT_NBR, '-', ACC.CD_ACC_NBR, SUB.CD_SUB_ACC_NBR) AS ACC_NBR,
            CONCAT(CAT.CD_CAT_DESC, ' - ', ACC.CD_DESC, ' :: ', SUB.CD_SUB_DESC) AS ACC_DESC
        FROM RTL.ACCTG_CD_SUB SUB
            INNER JOIN RTL.ACCTG_CD ACC ON ACC.CD_NBR=SUB.CD_NBR
            INNER JOIN RTL.ACCTG_CD_CAT CAT ON CAT.CD_CAT_NBR=ACC.CD_CAT_NBR
    ) ACC ON ACC.CD_SUB_NBR=DET.CD_SUB_NBR
WHERE DET.GL_DET_NBR=" . $glDetailNumber;

$result = mysql_query($query);
$row = mysql_fetch_array($result);
?>
<form method="post" style="width:100%;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;">
    <input name="GL_DET_NBR" id="GL_DET_NBR" value="<?php echo $row['GL_DET_NBR'];if($row['GL_DET_NBR'] == ""){echo "-1 ";$addNew=true;} ?>" type="hidden" />

    <table>
        </tbody>
            <tr>
                <td>Kode Akun</td>
                <td>
                    <select name="CD_SUB_NBR" id="CD_SUB_NBR" class="chosen-select" style="width: 300px;">
                        <option <?php if ($row['CD_SUB_NBR'] == "") {echo "selected";}?>>Pilih Rekening</option>
                    <?php
                    $query = "SELECT CD_CAT_NBR, CD_CAT_DESC FROM RTL.ACCTG_CD_CAT ORDER BY 1 ASC";
                    $resultCategory = mysql_query($query);

                    while($rowCategory = mysql_fetch_array($resultCategory)) {
                        echo "<optgroup label='".$rowCategory[ 'CD_CAT_DESC']. "'>";
                        
                        $query = "SELECT SUB.CD_SUB_NBR,
                                CONCAT(CAT.CD_CAT_NBR, '-', ACC.CD_ACC_NBR, SUB.CD_SUB_ACC_NBR) AS ACC_NBR,
                                CONCAT(CONCAT(CAT.CD_CAT_NBR, '-', ACC.CD_ACC_NBR, SUB.CD_SUB_ACC_NBR), ' :: ', ACC.CD_DESC, ' - ', SUB.CD_SUB_DESC) AS ACC_DESC
                            FROM RTL.ACCTG_CD_SUB SUB
                                INNER JOIN RTL.ACCTG_CD ACC ON ACC.CD_NBR=SUB.CD_NBR
                                INNER JOIN RTL.ACCTG_CD_CAT CAT ON CAT.CD_CAT_NBR=ACC.CD_CAT_NBR
                            WHERE ACC.CD_CAT_NBR=" .$rowCategory['CD_CAT_NBR']. "
                            ORDER BY CAT.CD_CAT_NBR, ACC.CD_ACC_NBR ASC";
                        genCombo($query, "CD_SUB_NBR", "ACC_DESC", $row['CD_SUB_NBR']);

                        echo "</optgroup>";
                    }
                    ?>
                    </select><div class="combobox"></div>
                </td>
            </tr>
            <tr>
                <td>Total Debit</td>
                <td>
                    <input id="DEB" name="DEB" value="<?php echo $row['DEB'];?>" onkeyup="if(this.value>0){document.getElementById('CRT').value='0';}" type="text" style="width:300px;" />
                </td>
            </tr>
            <tr>
                <td>Total Kredit</td>
                <td>
                    <input id="CRT" name="CRT" value="<?php echo $row['CRT'];?>" onkeyup="if(this.value>0){document.getElementById('DEB').value='0';}" type="text" style="width:300px;" />
                </td>
            </tr>
        </tbody>
    </table>
    
    <br />
    
    <input class="process" type="submit" value="<?php if ($addNew){echo 'Tambah';}else{echo 'Simpan';} ?>"/>
</form>
</div>
</body>
</html>