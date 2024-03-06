<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

$searchQuery    = strtoupper($_REQUEST['s']);
$whereClauses   = array("TOT_REM > 0", "HED.ORD_STT_ID = 'BL'");
$group			= $_GET['GROUP'];

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
			OR HED.BUY_CO_NBR LIKE '" . $query . "'
			OR COM.NAME LIKE '" . $query . "'
			OR HED.BUY_PRSN_NBR LIKE '" . $query . "'
			OR PPL.NAME LIKE '" . $query . "'
		)";
	}
}

switch (strtoupper($group)) {
	case "CO_NBR":
		$groupClause = "HED.BUY_CO_NBR";
		break;
	case "ORD_NBR":
	default:
		$groupClause = "HED.ORD_NBR";
		break;
}

$whereClauses[] = "HED.DEL_NBR=0";
$whereClauses 	= implode(" AND ", $whereClauses);

$queryTop="SELECT NBR FROM CDW.PRN_DIG_TOP_CUST";
$resultTop=mysql_query($queryTop);
while($rowTop=mysql_fetch_array($resultTop)){
	$TopCusts[]=strval($rowTop['NBR']);
}

$query="SELECT 
	HED.ORD_NBR,
	COUNT(HED.ORD_NBR) AS ORD_NBR_CNT, 
	YEAR(HED.ORD_TS) AS ORD_YEAR,
	MONTH(HED.ORD_TS) AS ORD_MONTH,
	HED.BUY_CO_NBR,
	COM.NAME AS NAME_CO,
	HED.BUY_PRSN_NBR,
	PPL.NAME AS NAME_PPL,
	HED.ORD_STT_ID,
	ORD_STT_DESC,
	DUE_TS,
	PU_TS,
	CMP_TS,
	ORD_TS,
	DATE_ADD(CMP_TS,INTERVAL COALESCE(COM.PAY_TERM,0) DAY) AS PAST_DUE,
	JOB_LEN_TOT,
	DL_CNT,
	PU_CNT,
	NS_CNT,
	IVC_PRN_CNT,
	COUNT(HED.ORD_NBR) AS ORD_NBR_CNT, 
	SUM(COALESCE(TOT_AMT,0)) AS TOT_AMT,
	SUM(COALESCE(PAY.TND_AMT,0)) AS PYMT_DOWN,
	SUM(COALESCE(TOT_AMT,0)) - SUM(COALESCE(PAY.TND_AMT,0)) AS TOT_REM 
FROM CMP.PRN_DIG_ORD_HEAD HED 
	INNER JOIN CMP.PRN_DIG_STT STT ON HED.ORD_STT_ID=STT.ORD_STT_ID 
	LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR 
	LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR
	LEFT JOIN (
		SELECT 
			PYMT.ORD_NBR,
			SUM(COALESCE(PYMT.TND_AMT,0)) AS TND_AMT
		FROM CMP.PRN_DIG_ORD_PYMT PYMT
		WHERE PYMT.DEL_NBR = 0
		GROUP BY PYMT.ORD_NBR
	) PAY ON PAY.ORD_NBR = HED.ORD_NBR
WHERE " . $whereClauses . "
GROUP BY HED.ORD_NBR
ORDER BY HED.ORD_NBR DESC";

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
			<th class="nosort"></th>
			<th>Judul</th>
			<th>Pemesan</th>
			<th style="width:7%;">Pesan</th>
			<th>Status</th>
			<th class="sortable">Janji</th>
			<th style="width:7%;">Jadi </th>
			<th class="sortable">Jatuh Tempo</th>
			<th class="sortable">Jumlah</th>
			<th class="sortable">Sisa</th>
		</tr>
	</thead>
	<tbody>
	<?php 
	$alt;
	while ($row = mysql_fetch_array($result)) {
		$due		= strtotime($row['DUE_TS']);
		$OrdSttId	= $row['ORD_STT_ID'];
		if((strtotime("now")>$due)&&(($OrdSttId=="NE")||($OrdSttId=="RC")||($OrdSttId=="QU")||($OrdSttId=="PR")||($OrdSttId=="FN"))){
			$back="print-digital-red";
		}elseif((strtotime("now + ".$row['JOB_LEN_TOT']." minute")>$due)&&(($OrdSttId=="NE")||($OrdSttId=="RC")||($OrdSttId=="QU")||($OrdSttId=="PR")||($OrdSttId=="FN"))){
			$back="print-digital-yellow";				
		}else{
			$back="";
		}
	?>
	<tr <?php echo $alt; ?> style ="cursor: pointer;" onclick="location.href='print-digital-edit.php?ORD_NBR=<?php echo $row['ORD_NBR'];?>';">
		<td style="text-align:center;"><?php echo $row['ORD_NBR'];?></td>
		<td style="text-align:left;white-space:nowrap'">
		<?php
			if(in_array($row['BUY_CO_NBR'],$TopCusts)){
				echo "<div class='listable'><span class='fa fa-star listable'></span></div>";
			}				
			if($row['SPC_NTE']!=""){
				echo "<div class='listable'><span class='fa fa-comment listable'></span></div>";
			}
			if($row['DL_CNT']>0){
				echo "<div class='listable'><span class='fa fa-truck listable' style='margin-left:-1px'></span></div>";
			}
			if($row['PU_CNT']>0){
				echo "<div class='listable'><span class='fa fa-shopping-cart listable'></span></div>";
			}
			if($row['NS_CNT']>0){
				echo "<div class='listable'><span class='fa fa-flag listable'></span></div>";
			}
			if($row['IVC_PRN_CNT']>0){
				echo "<div class='listable'><span class='fa fa-print listable'></span></div>";
			}
		?>
		</td>
		<td><?php echo $row['ORD_TTL'];?></td>
		<td><?php echo $row['NAME_PPL'];?> <?php echo $row['NAME_CO'];?></td>
		<td style="text-align:center;"><?php echo parseDateShort($row['ORD_TS']);?></td>
		<td style="text-align:center;"><?php echo $row['ORD_STT_DESC'];?></td>
		<td style="text-align:center;">
			<div class='<?php echo $back; ?>'><?php echo parseDateShort($row['DUE_TS']);?> <?php echo parseHour($row['DUE_TS']);?>:<?php echo parseMinute($row['DUE_TS']);?></div>
		</td>
		<td style="text-align:center;"><?php echo parseDateShort($row['CMP_TS']);?></td>
		<td style="text-align:center;"><?php echo parseDateShort($row['PAST_DUE']);?></td>
		<td align="right"><?php echo number_format($row['TOT_AMT'],0,'.',',');?></td>
		<td align="right"><?php echo number_format($row['TOT_REM'],0,'.',',');?></td>
	</tr>
	</tr>
	<?php 
	}
	?>
	</tbody>
</table>