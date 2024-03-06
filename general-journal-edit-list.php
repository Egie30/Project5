<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/security/default.php";

$glNumber 		= $_GET['GL_NBR'];
$glTypeNumber 	= $_GET['GL_TYP_NBR'];
$security    	= getSecurity($_SESSION['userID'], "Accounting");

if ($_GET['DEL_D'] != "") {
    $query  = "DELETE FROM RTL.ACCTG_GL_DET WHERE GL_NBR=" . $glNumber . " AND GL_DET_NBR=" . $_GET['DEL_D'];
    $result = mysql_query($query);
}

if($glTypeNumber == 0) { $fitur = "on"; }
	else { $fitur = ""; }


?>
<table style="background:#ffffff;" class="std-row-alt">
	<thead>
		<tr>
			<th class="listable" style="text-align:right">No</th>
			<th class="listable" style="text-align:right">Kode Akun</th>
			<th class="listable" style="text-align:center">Nama Akun</th>
			<th class="listable" style="text-align:center">Debit</th>
			<th class="listable" style="text-align:center">Kredit</th>
			<th class="listable">
			
			<?php if (($fitur == "on") && ($security == 0)) { ?>
			
			<div class='listable-btn'><span class='fa fa-plus listable-btn' onclick="if(document.getElementById('GL_NBR').value==-1){parent.parent.document.getElementById('invoiceAdd').style.display='block';parent.parent.document.getElementById('fade').style.display='block';return;};pushFormIn('general-journal-edit-list-detail.php?GL_DET_NBR=-1&GL_NBR=<?php echo $glNumber;?>');"></span></div>
			<?php } ?>
			</th>
			
		</tr>
	</thead>
	<tbody>
	<?php
		$query  = "SELECT DET.GL_DET_NBR,
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
		WHERE DET.GL_NBR=" . $glNumber . "
		GROUP BY DET.GL_DET_NBR
		ORDER BY DET.GL_DET_NBR ASC";
		$result = mysql_query($query);

		$i = 0;
		$totalDebit = 0;
		$totalKredit = 0;
		
		$alt="";
		
		while ($row = mysql_fetch_array($result)) {
			$i++;
			echo "<tr $alt onclick=".chr(34)."pushFormIn('general-journal-edit-list-detail.php?GL_NBR=".$glNumber."&GL_DET_NBR=".$row['GL_DET_NBR']."')".chr(34).">";
			?>
			
			<td style="cursor:pointer;text-align:center;"><?php echo $i;?>.</td>
			<td style="cursor:pointer;text-align:left;"><?php echo $row['ACC_NBR'];?></td>
			<td style="cursor:pointer;text-align:left;"><?php echo $row['CD_SUB_DESC'];?></td>
			<td style="cursor:pointer;text-align:right;"><?php echo number_format($row['DEB'], 0, ",", ".");?></td>
			<td style="cursor:pointer;text-align:right;"><?php echo number_format($row['CRT'], 0, ",", ".");?></td>
		    	<?php if (($fitur == "on") && ($security <= 1)) { 
				echo "<td style='cursor:pointer;text-align:center;' style='padding-left:2px;padding-right:2px;'>";
				echo "<div class='listable-btn'><span class='fa fa-trash listable-btn' onclick=".chr(34)."event.cancelBubble=true;getContent('edit-list','general-journal-edit-list.php?GL_NBR=".$glNumber."&DEL_D=".$row['GL_DET_NBR']."');".chr(34)."></span></div>";
					/*
					<img class="listable" src="img/write.png" style="cursor:pointer;" onclick="parent.document.getElementById('retailPopupEditContent').src='general-journal-edit-list-detail.php?GL_NBR=<?php echo $glNumber;?>&GL_DET_NBR=<?php echo $row['GL_DET_NBR'];?>';parent.document.getElementById('retailPopupEdit').style.display='block';parent.document.getElementById('fade').style.display='block'">
				    <img class="listable" src="img/trash.png" style="cursor:pointer;" onclick="getContent('edit-list','general-journal-edit-list.php?GL_NBR=<?php echo $glNumber;?>&DEL_D=<?php echo $row['GL_DET_NBR'];?>')">
					*/
			    echo "</td></tr>";
				} 
		    $totalDebit += $row['DEB'];
		    $totalKredit += $row['CRT'];
		}
	?>
	</tbody>
</table>

<br />

<input type="hidden" id="GL_TOT_DEB" value="<?php echo $totalDebit;?>"/>
<input type="hidden" id="GL_TOT_CRT" value="<?php echo $totalKredit;?>"/>

<div style="clear:both"></div>

<div class="total-wrapper">
	<table>
		<tr>
			<td>Total Debit</td>
			<td style="text-align:right;font-weight:bold;" id="GL_BALANCE"><?php echo number_format($totalDebit,0,',','.');?></td>
		</tr>
		<tr>
			<td>Total Kredit</td>
			<td style="text-align:right;font-weight:bold;" id="GL_BALANCE"><?php echo number_format($totalKredit,0,',','.');?></td>
		</tr>
		<tr>
			<td style="font-weight:bold;color:#3464bc;">Balance</td>
			<td style="text-align:right;font-weight:bold;" id="GL_BALANCE"><?php echo number_format(abs($totalDebit - $totalKredit),0,',','.');?></td>
		</tr>
	</table>
</div>

<?php if (abs($totalDebit - $totalKredit) !== 0 && ($i % 2) == 0) { ?>
<div style="margin:5px;padding:10px;border-radius:3px;-webkit-border-radius:3px;-moz-boder-radius:3px;background-color:#dddddd;">
	Total nominal dari transaksi yang anda inputkan tidak balance. Silahkan periksa kembali.
</div>
<?php } ?>