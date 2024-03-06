<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

$beginDate 		= $_GET['BEG_DT'];
$endDate 		= $_GET['END_DT'];
$searchQuery    = strtoupper($_REQUEST['s']);
$whereClauses   = array("HED.IVC_TYP='RC'");
$group			= $_GET['GROUP'];


if (!empty($beginDate)) {
	$whereClauses[] = "DATE(DL_TS) >= '" . $beginDate . "'";
}

if (!empty($endDate)) {
	$whereClauses[] = "DATE(DL_TS) <= '" . $endDate . "'";
}

if ($searchQuery != "") {
	$searchQuery = explode(" ", $searchQuery);

	foreach ($searchQuery as $query) {
		$query = trim($query);

		if (empty($query)) {
			continue;
		}

		if (strrpos($query, '%') === false) {
			$query = '%' . $query . '%';
		}
		$whereClauses[] = "(
			HED.ORD_NBR LIKE '" . $query . "'
			OR HED.RCV_CO_NBR LIKE '" . $query . "'
			OR RCV.NAME LIKE '" . $query . "'
			OR HED.SHP_CO_NBR LIKE '" . $query . "'
			OR SHP.NAME LIKE '" . $query . "'
		)";
	}
}

switch (strtoupper($group)) {
	case "CO_NBR":
		$groupClause = "HED.RCV_CO_NBR";
		break;
	case "ORD_NBR":
	default:
		$groupClause = "HED.ORD_NBR";
		break;
}

$whereClauses[] = "HED.DEL_F=0";
$whereClauses 	= implode(" AND ", $whereClauses);

$query="SELECT 
		HED.ORD_NBR,
		COUNT(HED.ORD_NBR) AS CNT_ORD_NBR,
		HED.CAT_SUB_NBR,
		SUB.CAT_SUB_DESC,
		TYP.CAT_TYP,
		COALESCE(SUM(HED.TOT_AMT),0) AS TOT_AMT,
		DATE(HED.ORD_DTE) AS ORD_DTE,
		DATE(HED.DL_TS) AS DL_DTE,
		HED.TAX_AMT,
		COALESCE(SUM(DET.ORD_Q),0) AS TOTAL_ORD_Q,
		COALESCE(SUM(DET.TOT_SUB),0) AS TOTAL_SUB,
		COALESCE(HED.FEE_MISC,0) AS HED_FEE_MISC,
		COALESCE(SUM(
		CASE WHEN HED.TAX_APL_ID IN ('I','A')
				THEN (HED.TOT_AMT)/1.1 
			ELSE HED.TOT_AMT
			END
		),0) AS SUBTOTAL,
		DATE(HED.PYMT_REM_TS) AS PAID_DTE,
		HED.SHP_CO_NBR,
		SHP.NAME AS SHIPPER,
		HED.RCV_CO_NBR,
		RCV.NAME AS RECEIVER,
		HED.TOT_REM,
		HED.ACTG_TYP,
		HED.PYMT_TYP,
		TYP.CAT_TYP_NBR,
		SUB.CD_SUB_NBR AS AKUN,
		PYMT.PYMT_DESC,			
		HED.TAX_APL_ID,
		TAX_IVC_NBR,
		TAX_IVC_DTE
	FROM RTL.RTL_STK_HEAD HED 
	LEFT JOIN RTL.RTL_STK_DET DET ON DET.ORD_NBR = HED.ORD_NBR
	LEFT JOIN RTL.CAT_SUB SUB ON SUB.CAT_SUB_NBR = HED.CAT_SUB_NBR
	LEFT JOIN CMP.COMPANY SHP ON SHP.CO_NBR = HED.SHP_CO_NBR
	LEFT JOIN CMP.COMPANY RCV ON RCV.CO_NBR = HED.RCV_CO_NBR
	LEFT JOIN RTL.PYMT_TYP PYMT ON PYMT.PYMT_TYP = HED.PYMT_TYP
	INNER JOIN RTL.CAT_TYP TYP  ON TYP.CAT_TYP_NBR = SUB.CAT_TYP_NBR
WHERE " . $whereClauses . "
GROUP BY HED.SHP_CO_NBR
ORDER BY HED.DL_TS";

$result = mysql_query($query);
//echo "<pre>".$query."<br><br>";
if (mysql_num_rows($result) == 0) {
    echo "<div class='searchStatus'>Data or Number Not Found</div>";
    exit;
}
?>
<table id="mainTable" class="tablesorter table-freeze std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable sortable-onload-show">
	<thead>
		<tr>
			<th class="sortable" style="text-align:right;">No.</th>
			<th class="sortable">Pengirim</th>
			<th class="sortable">Nota</th>
			<th class="sortable" style="text-align:right;">Jumlah</th>
			<th class="sortable" style="text-align:right;">Subtotal Nota</th>				
			<th class="sortable" style="text-align:right;">Biaya Tambahan</th>				
			<th class="sortable" style="text-align:right;">PPN</th>				
			<th class="sortable" style="text-align:right;">Total Nota</th>
			<th class="sortable" style="text-align:right;">Sisa</th>
		</tr>
	</thead>
	<tbody>
	<?php 
	$alt="";
	while ($row = mysql_fetch_array($result)) {
	?>
	<tr <?php echo $alt; ?> style ="cursor: pointer;" onclick="location.href='retail-stock-report.php?IVC_TYP=RC&CO_NBR=<?php echo $row['SHP_CO_NBR'];?>&BEG_DT=<?php echo $_GET['BEG_DT'];?>&END_DT=<?php echo $_GET['END_DT'];?>';">
		<td style="text-align:center;"><?php echo $row['SHP_CO_NBR'];?></td>
		<td><?php echo $row['SHIPPER'];?></td>
		<td style="text-align:center;"><?php echo $row['CNT_ORD_NBR'];?></td>
		<td style="text-align:center;"><?php echo $row['TOTAL_ORD_Q'];?></td>
		<td style="text-align:right;"><?php echo number_format($row['TOTAL_SUB'],0,',','.');?></td>
		<td style="text-align:right;"><?php echo number_format($row['HED_FEE_MISC'],0,',','.');?></td>
		<td style="text-align:right;"><?php echo number_format($row['TAX_AMT'],0,',','.');?></td>
		<td style="text-align:right;"><?php echo number_format($row['TOT_AMT'],0,',','.');?></td>
		<td style="text-align:right;"><?php echo number_format($row['TOT_REM'],0,',','.');?></td>
	</tr>
	<?php 
		$totalInvoice	+= $row['CNT_ORD_NBR'];
		$totalQty		+= $row['TOTAL_ORD_Q'];
		$totalSub		+= $row['TOTAL_SUB'];
		$totalFeemisc	+= $row['HED_FEE_MISC'];
		$totalTax		+= $row['TAX_AMT'];
		$totalAmoun		+= $row['TOT_AMT'];
		$totalRemain	+= $row['TOT_REM'];
	}
	?>
	</tbody>
	<tfoot>
		<tr style="border-top:1px solid grey">
			<td class="std" style="text-align:left;font-weight:bold;" colspan="2">Total:</td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($totalInvoice, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($totalQty, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($totalSub, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($totalFeemisc, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($totalTax, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($totalAmoun, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($totalRemain, 0, ',', '.');?></td>
			<td class="std" style="text-align:right;font-weight:bold;"></td>
		</tr>
	</tfoot>
</table>