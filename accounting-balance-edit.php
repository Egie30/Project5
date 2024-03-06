<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/security/default.php";

$bookNumber		= $_GET['BK_NBR'];
$balanceNumber 	= $_GET['TB_NBR'];
$plusMode		= $_GET['PLUS'];
$security 		= getSecurity($_SESSION['userID'], "Accounting");

$Actg		= $_GET['ACTG'];

$ArrayActg	= array(
				0 => "ALL",
				1 => "PT",
				2 => "CV",
				3 => "PR"
				);
				
//Process changes here
if ($_POST['TB_NBR'] != "") {
	$balanceNumber = $_POST['TB_NBR'];

	//Process add new
	if ($balanceNumber == -1) {
		$query = "SELECT COALESCE(MAX(TB_NBR),0)+1 AS NEW_NBR FROM RTL.ACCTG_TB";
		$result = mysql_query($query);
		$row = mysql_fetch_array($result);
		$balanceNumber = $row['NEW_NBR'];

		$query = "INSERT INTO RTL.ACCTG_TB(TB_NBR, BK_NBR, UPD_NBR, CRT_NBR)
			VALUES (" . $balanceNumber . ", (SELECT BK_NBR FROM RTL.ACCTG_BK WHERE ACT_F=1 ORDER BY CRT_TS DESC LIMIT 1), " . $_SESSION['personNBR'] . ", " . $_SESSION['personNBR'] . ")";
		$result = mysql_query($query);
		
		//echo "<pre>".$query."<br /><br />";
		
	}
		
	$query = "UPDATE RTL.ACCTG_TB SET
		BK_NBR = ".$bookNumber.",
		CD_SUB_NBR=" . $_POST['CD_SUB_NBR'] . ",
	   	CRT='" . $_POST['CRT'] . "',
	   	DEB='" . $_POST['DEB'] . "' ";
	if($Actg != 0) {
		$query .= ", ACTG_TYP = '".$Actg."' ";
	}
	$query .= "WHERE TB_NBR=" . $balanceNumber;
	
	$result = mysql_query($query);
	
	//echo $query;
	
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
    <link rel="stylesheet" type="text/css" href="framework/combobox/chosen.css">

	<script type="text/javascript">parent.Pace.restart();</script>
    <script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
    <script type="text/javascript" src="framework/functions/default.js"></script>
    <script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>
    <script type="text/javascript" src="framework/combobox/chosen.default.js"></script>
<body>
<script>
parent.document.getElementById('recordDeleteYes').onclick = function () {
	parent.document.getElementById('content').src='accounting-balance.php?BK_NBR=<?php echo $bookNumber; ?>&DEL_L=<?php echo $balanceNumber;?>&ACTG=<?php echo $Actg; ?>';
	parent.document.getElementById('recordDelete').style.display='none';
	parent.document.getElementById('fade').style.display='none';
	parent.document.getElementById('retailPopupEdit').style.display='none';
};
</script>
<?php
$query = "SELECT TB_NBR,
		TB.BK_NBR,
		CD.ACC_NBR,
		CD.ACC_DESC,
		CD.CD_CAT_NBR,
		CD.CD_CAT_DESC,
		CD.CD_NBR,
		CD.CD_ACC_NBR,
		CD.CD_DESC,
		CD.CD_SUB_NBR,
		CD.CD_SUB_ACC_NBR,
		CD.CD_SUB_DESC,
		TB.DEB,
		TB.CRT,
		TB.DEL_NBR,
		TB.UPD_NBR,
		TB.UPD_TS,
		TB.DEPN_NBR,
		TB.ACTG_TYP
	FROM RTL.ACCTG_TB TB
		INNER JOIN RTL.ACCTG_BK BK ON BK.BK_NBR=TB.BK_NBR
		INNER JOIN (
			SELECT SUB.CD_SUB_NBR, SUB.CD_SUB_ACC_NBR, SUB.CD_SUB_DESC, ACC.CD_NBR, ACC.CD_ACC_NBR, ACC.CD_DESC, CAT.CD_CAT_NBR, CAT.CD_CAT_DESC,
				CONCAT(CAT.CD_CAT_NBR, '-', ACC.CD_ACC_NBR, SUB.CD_SUB_ACC_NBR) AS ACC_NBR,
				CONCAT(CAT.CD_CAT_DESC, ' - ', ACC.CD_DESC, ' :: ', SUB.CD_SUB_DESC) AS ACC_DESC
			FROM RTL.ACCTG_CD_SUB SUB
				INNER JOIN RTL.ACCTG_CD ACC ON ACC.CD_NBR=SUB.CD_NBR
				INNER JOIN RTL.ACCTG_CD_CAT CAT ON CAT.CD_CAT_NBR=ACC.CD_CAT_NBR
		) CD ON CD.CD_SUB_NBR=TB.CD_SUB_NBR
	WHERE TB.TB_NBR=" . $balanceNumber;
	
$result = mysql_query($query);
$row = mysql_fetch_array($result);
?>

<table class="submenu">
	<tr>
		<td class="submenu" style="background-color:">
			<?php
			foreach($ArrayActg as $key => $value) {
				echo "<a class='submenu' href='accounting-balance.php?ACTG=".$key."&BK_NBR=".$bookNumber."'><div class='";
					if ($key == $Actg){ echo "arrow_box"; } else { echo "leftsubmenu"; }
					echo "'>".$value."</div></a>";
			}
			?>	
		</td>
		<td class="subcontent">
		
		<?php if ($security == 0 && $row['TB_NBR'] != 0) { ?>
			<div class="toolbar-only">
				<p class="toolbar-left"><a href="javascript:void(0)" onclick="window.scrollTo(0,0);parent.document.getElementById('recordDelete').style.display='block';parent.document.getElementById('fade').style.display='block'"><img style="cursor:pointer" class="toolbar-left" src="img/delete.png"></a></p>
				<?php 
				if($row['CD_NBR'] == 2) {
				?>
				<p class="toolbar-right">
						<a><img class="toolbar-right" style="cursor:pointer" src="img/plus.png" onclick="parent.document.getElementById('retailPopupEditContent').src='depreciation-edit.php?BK_NBR=<?php echo $bookNumber; ?>&DEPN_NBR=<?php echo $row['DEPN_NBR']; ?>&CODE=TB&CODE_NBR=<?php echo $balanceNumber; ?>';parent.document.getElementById('retailPopupEdit').style.display='block';parent.document.getElementById('fade').style.display='block';"></a>
				</p>
				<?php 
					}
				?>
			</div>
		<?php } ?>

		<form enctype="multipart/form-data" action="#" method="post" style="width:400px">
			<p>
				<h2>
					Kode Rekening: <?php echo $row['ACC_NBR'];if($row['ACC_NBR']==''){echo "Baru";} ?>
				</h2>		
				<input name="TB_NBR" value="<?php echo $row['TB_NBR'];if($row['TB_NBR']==""){echo "-1";} ?>" type="hidden" />

				<label>Rekening</label><br />
				<br />
				<div class='labelbox'></div>
				<select name='CD_SUB_NBR' id='CD_SUB_NBR' class='chosen-select'>
					<option <?php if ($row['CD_SUB_NBR'] == "") {echo "selected";}?>>Pilih Rekening</option>
				<?php
				$query = "SELECT CD_CAT_NBR, CD_CAT_DESC FROM RTL.ACCTG_CD_CAT WHERE CD_CAT_NBR IN (1, 2, 3, 4) ORDER BY 1 ASC";
				$resultCategory = mysql_query($query);

				while($rowCategory = mysql_fetch_array($resultCategory)) {
					echo "<optgroup label='".$rowCategory[ 'CD_CAT_DESC']. "'>";
					
					$query = "SELECT SUB.CD_SUB_NBR,
							CONCAT(CAT.CD_CAT_NBR, '-', ACC.CD_ACC_NBR, SUB.CD_SUB_ACC_NBR) AS ACC_NBR,
							CONCAT(CONCAT(CAT.CD_CAT_NBR, '-', ACC.CD_ACC_NBR, SUB.CD_SUB_ACC_NBR), ' :: ', SUB.CD_SUB_DESC) AS ACC_DESC
						FROM RTL.ACCTG_CD_SUB SUB
							INNER JOIN RTL.ACCTG_CD ACC ON ACC.CD_NBR=SUB.CD_NBR
							INNER JOIN RTL.ACCTG_CD_CAT CAT ON CAT.CD_CAT_NBR=ACC.CD_CAT_NBR
						WHERE ACC.CD_CAT_NBR=" .$rowCategory['CD_CAT_NBR']. "
						ORDER BY CAT.CD_CAT_NBR, ACC.CD_ACC_NBR ASC";
					genCombo($query, "CD_SUB_NBR", "ACC_DESC", $row['CD_SUB_NBR']);

					echo "</optgroup>";
				}
				?>
				</select>
						
				<br/>
				<div class="combobox"></div>
					
				<label>Debit</label><br />
				<input name="DEB" id="DEB" required onkeyup="if(this.value>0){document.getElementById('CRT').value='0';}" value="<?php echo $row['DEB'];?>" type="text" size="20" /><br />

				<label>Kredit</label><br />
				<input name="CRT" id="CRT" required onkeyup="if(this.value>0){document.getElementById('DEB').value='0';}" value="<?php echo $row['CRT'];?>" type="text" size="20" /><br />
			
				<input class="process" type="submit" value="Simpan"/>
			</p>
		</form>

	</td>
	</tr>
</table>


</body>
</html>
