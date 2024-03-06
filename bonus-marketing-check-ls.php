<?php
require_once "framework/database/connect.php";

$query = "SELECT PAY_CONFIG_NBR, PAY_BEG_DTE, PAY_END_DTE FROM PAY.PAY_CONFIG_DTE WHERE PAY_BEG_DTE <= CURRENT_DATE AND PAY_END_DTE >= CURRENT_DATE";
$result = mysql_query($query);
$row    = mysql_fetch_array($result);
$PayBegDte    	= $row['PAY_BEG_DTE'];
$PayEndDte    	= $row['PAY_END_DTE'];
$searchQuery    = strtoupper($_REQUEST['s']);
$whereClauses   = array("HED.DEL_NBR=0","HED.ORD_STT_ID = 'CP'","COM.ACCT_EXEC_NBR != 0");

if ($PayBegDte != ""){
	$whereClauses[] = "DATE(CPJRN.CP_DTE) = '" . $PayBegDte . "'";
}

if ($PayEndDte != ""){
	$whereClauses[] = "DATE(CPJRN.CP_DTE) = '" . $PayEndDte . "'";
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
			ORD_NBR LIKE '" . $query . "'
			OR STT.ORD_STT_DESC LIKE '" . $query . "'
			OR COM.NAME LIKE '" . $query . "'
			OR COM.NAME LIKE '" . $query . "'
		)";
	}
}

$whereClauses[] = "PYMT.DEL_NBR=0";
$whereClauses 	= implode(" AND ", $whereClauses);

$query="SELECT
	PYMT.PYMT_NBR,
	HED.ORD_NBR,
	HED.ORD_TS,
	HED.ORD_TTL,
	HED.ORD_STT_ID,
	STT.ORD_STT_DESC,
	COM.ACCT_EXEC_NBR,
	HED.BUY_CO_NBR,
	COM.NAME AS BUY_CO_NAME,
	DATE(HED.DUE_TS)AS DUE_DTE,
	BLJRN.BILL_DTE,
	CPJRN.CP_DTE,
	HED.PRN_CO_NBR,
	COM.NAME AS PRN_CO_NAME,
	HED.TOT_AMT,
	HED.TOT_REM,
	PYMT.PYMT_TYP,
	PYMT.TND_AMT,
	PYMT.BNK_CO_NBR,
	PYMT.VAL_NBR,
	PYMT.CRT_NBR,
	PYMT.CRT_TS
FROM CMP.PRN_DIG_ORD_PYMT PYMT
	INNER JOIN CMP.PRN_DIG_ORD_HEAD HED ON PYMT.ORD_NBR = HED.ORD_NBR
	INNER JOIN CMP.PRN_DIG_STT STT ON HED.ORD_STT_ID = STT.ORD_STT_ID
	INNER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR = COM.CO_NBR
	LEFT OUTER JOIN(
		SELECT
			 ORD_NBR,
			DATE( MIN(CRT_TS)) AS BILL_DTE
	   FROM CMP.JRN_PRN_DIG
	   WHERE ORD_STT_ID = ('BL')
	   GROUP BY ORD_NBR
	)BLJRN ON PYMT.ORD_NBR = BLJRN.ORD_NBR
	LEFT OUTER JOIN(
		SELECT
			 ORD_NBR,
			 DATE(MIN(CRT_TS)) AS CP_DTE
	   FROM CMP.JRN_PRN_DIG
	   WHERE ORD_STT_ID = ('CP')
	   GROUP BY ORD_NBR
	)CPJRN ON PYMT.ORD_NBR = CPJRN.ORD_NBR
WHERE ".$whereClauses." 
GROUP BY PYMT.PYMT_NBR";

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
			<th class="sortable" style="text-align:center;">No Nota</th>
			<th class="sortable">Judul</th>
			<th class="sortable">Pemesan</th>
			<th class="sortable">Pesan</th>
			<th class="sortable">Status</th>
			<th class="sortable">Billing</th>
			<th class="sortable">Selesai</th>
			<th class="sortable">Validasi</th>
			<th class="sortable">Jumlah</th>
		</tr>
	</thead>
	<tbody>
	<?php 
	$alt;
	while ($row = mysql_fetch_array($result)) { 
	?>
	<tr <?php echo $alt; ?> style='cursor:pointer;'>
		<td style="text-align:center;"><?php echo $row['ORD_NBR'];?></td>
		<td><?php echo $row['ORD_TTL'];?></td>
		<td><?php echo $row['BUY_CO_NAME'];?></td>
		<td style="text-align:center;"><?php echo $row['ORD_DTE'];?></td>
		<td style="text-align:center;"><?php echo $row['ORD_STT_DESC'];?></td>
		<td style="text-align:center;"><?php echo $row['BILL_DTE'];?></td>
		<td style="text-align:center;"><?php echo $row['CP_DTE'];?></td>
		<td style="text-align:center;">
			<?php if($row['VAL_NBR'] != ""){?>
			<span class="fa fa-check-circle"></span>
			<?php } ?>
		</td>
		<td align="right"><?php echo number_format($row['TND_AMT'],0,'.',',');?></td>
	</tr>
	<?php 
	}
	?>
	</tbody>
</table>