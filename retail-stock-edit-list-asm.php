<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/security/default.php";

$security     = getSecurity($_SESSION['userID'], "Inventory");
$orderNumber  = $_GET['ORD_NBR'];
$invoiceType  = $_GET['IVC_TYP'];
$detailType   = $_GET['DTL_TYP'];
$totalNet     = 0;
$whereClausesHead 	= array("DET.ORD_NBR=" . $orderNumber);
$whereClausesDetail = array("DET.ORD_NBR=" . $orderNumber);
$duplicateInventories = array();

if ($_GET['DEL_D'] != "") {
    $query  = "DELETE FROM RTL.RTL_STK_DET WHERE ORD_NBR=" . $orderNumber . " AND ORD_DET_NBR=" . $_GET['DEL_D'];
    $result = mysql_query($query);
}

if ($invoiceType == "AS") {
	$whereClausesHead[] 	= "DET.ORD_Q > 0";
	$whereClausesDetail[] 	= "DET.ORD_Q < 0";
} elseif ($invoiceType == "DS") {
	$whereClausesHead[] 	= "DET.ORD_Q < 0";
	$whereClausesDetail[] 	= "DET.ORD_Q > 0";
}

$whereClausesHead[] 	= "INV.DEL_NBR=0";
$whereClausesDetail[] 	= "INV.DEL_NBR=0";

$whereClausesHead 	= implode(" AND ", $whereClausesHead);
$whereClausesDetail = implode(" AND ", $whereClausesDetail);

$queryDetail  = "SELECT DET.ORD_DET_NBR,
			DET.ORD_NBR,
			DET.INV_NBR,
			INV.INV_BCD,
			INV.PRC,
			INV.NAME,
			INV.CAT_SUB_NBR,
			DET.INV_DESC,
			ABS(DET.ORD_Q) AS ORD_Q,
			DET.INV_PRC,
			DET.FEE_MISC,
			DET.DISC_PCT,
			DET.DISC_AMT,
			DET.TOT_SUB,
			DET.CRT_TS,
			DET.CRT_NBR,
			DET.UPD_TS,
			DET.UPD_NBR
		FROM RTL.RTL_STK_DET DET
			LEFT OUTER JOIN RTL.INVENTORY INV ON DET.INV_NBR=INV.INV_NBR
		WHERE %s
		GROUP BY DET.ORD_DET_NBR
		ORDER BY DET.ORD_DET_NBR ASC";
$result = mysql_query(sprintf($queryDetail, $whereClausesHead));
$rowHead = mysql_fetch_array($result);
?>
<table style="background:#ffffff;" class="std-row-alt">
	<thead>
		<tr>
			<?php if ($detailType == "HED") { ?>
			<th class="listable" style="text-align:right">Jumlah</th>
			<?php } ?>
			<?php if ($detailType != "HED") { ?>
				<th class="listable" style="text-align:center">Total</th>
			<?php } ?>
			<th class="listable" style="text-align:center">Barcode</th>
			<th class="listable" style="text-align:center">Nama</th>
			<th class="listable">Faktur</th>
			<th class="listable">Jual</th>
			<th class="listable">Sub Faktur</th>
			<th class="listable">Sub Jual</th>
			<th class="listable">
		    	<?php if ($security <= 1 && (($detailType == "HED" && $rowHead['INV_NBR'] == "") || ($detailType != "HED" && $rowHead['INV_NBR'] != ""))) { ?>
					<div class='listable-btn'><span class='fa fa-plus listable-btn' onclick="if(document.getElementById('ORD_NBR').value==-1){parent.parent.document.getElementById('invoiceAdd').style.display='block';parent.parent.document.getElementById('fade').style.display='block';return;};pushFormIn('retail-stock-edit-list-detail-asm.php?ORD_DET_NBR=0&ORD_NBR=<?php echo $orderNumber;?>&IVC_TYP=<?php echo $invoiceType;?>&DTL_TYP=<?php echo $detailType;?>');"></span></div>
			    <?php } ?>
			</th>
		</tr>
	</thead>
	<tbody>
	<?php
		$alt = "";
		while ($row = mysql_fetch_array($result)) {
			$duplicateInventories[] = $row['INV_NBR'];
		}

		if ($detailType == "HED") {
			$result = mysql_query(sprintf($queryDetail, $whereClausesHead));
		} else {
			if ($invoiceType == "AS") {
				$query  = "SELECT DISTINCT INV_NBR FROM RTL.RTL_STK_DET DET
						WHERE ORD_NBR=" . $orderNumber . " AND DET.ORD_Q < 0
						GROUP BY INV_NBR
						HAVING COUNT(*) > 1";
			} elseif ($invoiceType == "DS") {
				$query  = "SELECT DISTINCT INV_NBR FROM RTL.RTL_STK_DET DET
						WHERE ORD_NBR=" . $orderNumber . " AND DET.ORD_Q > 0
						GROUP BY INV_NBR
						HAVING COUNT(*) > 1";
			}

			$result = mysql_query($query);
			$result = mysql_query(sprintf($queryDetail, $whereClausesDetail));
		}
		//echo sprintf($queryDetail, $whereClausesDetail);
		while ($row = mysql_fetch_array($result)) {
			$rowStyle = "";

			if (in_array($row['INV_NBR'], $duplicateInventories)) {
				$rowStyle = "background-color:rgba(255, 200, 0, 0.5)";
			}
			?>
			<tr <?php echo $alt; ?> id="<?php echo $row['ORD_DET_NBR'];?>" style="cursor:pointer;<?php echo $rowStyle;?>" onclick="pushFormIn('retail-stock-edit-list-detail.php?ORD_DET_NBR=<?php echo $row['ORD_DET_NBR'];?>&ORD_NBR=<?php echo $orderNumber;?>&IVC_TYP=<?php echo $invoiceType;?>')">

			<?php if ($detailType == "HED") { ?>
			<td style="text-align:center;">
				<?php
					if ($invoiceType == "AS") {
						echo "+ ";
					} elseif ($invoiceType == "DS") {
						echo "- ";
					}
					if ($detailType == "HED") {
						echo number_format($row['ORD_Q'], 0, ",", ".");
					} else {
						echo number_format($row['ORD_Q'] / $rowHead['ORD_Q'], 0, ",", ".");
					}
				?>
			</td>
			<?php } ?>
			
			<?php if ($detailType != "HED") { ?>
				<td style="text-align:center;">
					<?php
					if ($invoiceType == "AS") {
						echo "-";
					} elseif ($invoiceType == "DS") {
						echo "+";
					}

					echo "&nbsp;" . number_format($row['ORD_Q'], 0, ",", ".");
					?>
				</td>
			<?php } ?>
		    <td><?php echo $row['INV_BCD'];?></td>
		    <td><?php echo $row['NAME'] . trim(" " . $row['INV_DESC']);?></td>
		    <td style="text-align:right;"><?php echo number_format($row['INV_PRC'], 0, ",", ".");?></td>
			<td style="text-align:right;"><?php echo number_format($row['PRC'], 0, ",", ".");?></td>
			<td style="text-align:right;"><?php echo number_format($row['TOT_SUB'], 0, ",", ".");?></td>
			<td style="text-align:right;"><?php echo number_format($row['PRC'] * $row['ORD_Q'], 0, ",", ".");?></td>
		    <td style="text-align:center;padding-left:2px;padding-right:2px;white-space:nowrap">
		    	<input type="hidden" id="ORD_DET_NBR_<?php echo $row['ORD_DET_NBR'];?>" value="<?php echo $row['ORD_DET_NBR'];?>"/>
		    	<input type="hidden" id="INV_PRC_<?php echo $row['ORD_DET_NBR'];?>" value="<?php echo $row['INV_PRC'];?>"/>
		    	<input type="hidden" id="PRC_<?php echo $row['ORD_DET_NBR'];?>" value="<?php echo $row['PRC'];?>"/>
		    	<input type="hidden" id="TOT_SUB_<?php echo $row['ORD_DET_NBR'];?>" value="<?php echo $row['TOT_SUB'];?>"/>
		    	<input type="hidden" id="ORD_Q_<?php echo $row['ORD_DET_NBR'];?>" value="<?php echo $row['ORD_Q'];?>"/>
		    	<input type="hidden" id="FREE_CNT_<?php echo $row['ORD_DET_NBR'];?>" value="<?php echo $row['FREE_CNT'];?>"/>
		    	<input type="hidden" id="DISC_PCT_<?php echo $row['ORD_DET_NBR'];?>" value="<?php echo $row['DISC_PCT'];?>"/>
		    	<input type="hidden" id="DISC_AMT_<?php echo $row['ORD_DET_NBR'];?>" value="<?php echo $row['DISC_AMT'];?>"/>
				<div class='listable-btn'><span class='fa fa-trash listable-btn' onclick="getContent('edit-list','retail-stock-edit-list-asm.php?DEL_D=<?php echo $row['ORD_DET_NBR'];?>&ORD_NBR=<?php echo $orderNumber;?>&IVC_TYP=<?php echo $invoiceType;?>&DTL_TYP=<?php echo $detailType;?>');"></span></div>
		    </td>
		    </tr>
		    <?php
		    $totalNet += $row['TOT_SUB'];
		}
	?>
	</tbody>
</table>
<?php if ($detailType != "HED") { ?>
<input type="hidden" id="TOT_NET" value="<?php echo $totalNet;?>"/>
<?php } ?>
