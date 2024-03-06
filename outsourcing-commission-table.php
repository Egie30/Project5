<?php
include "framework/database/connect.php";
include "framework/functions/default.php";

$PayConfigNbr = $_GET['PAY_CONFIG_NBR'];
$PrsnNbr      = $_GET['PRSN_NBR'];
$filterOption = $_GET['FLR_OPT'];
$FLR_MPR_PPL  = $_GET['FLR_MPR_PPL'];

if($FLR_MPR_PPL != ''){
	$where = " AND DET.ACCT_EXEC_NBR = ".$FLR_MPR_PPL;
}else{
	$where = "";
}

if ($PayConfigNbr==''){
	$query = "SELECT PAY_CONFIG_NBR,PAY_BEG_DTE FROM PAY.PAY_CONFIG_DTE WHERE PAY_BEG_DTE <= CURRENT_DATE AND PAY_END_DTE >= CURRENT_DATE ";
	$result = mysql_query($query, $local);
	$rowDte = mysql_fetch_array($result);

	$PayConfigNbr = $rowDte['PAY_CONFIG_NBR'];
	$date         = $rowDte['PAY_BEG_DTE'];
}else{
	$query = "SELECT PAY_BEG_DTE FROM PAY.PAY_CONFIG_DTE WHERE PAY_CONFIG_NBR = ".$PayConfigNbr;
	$result= mysql_query($query, $local);
	$rowDte= mysql_fetch_array($result);

	$date  = $rowDte['PAY_BEG_DTE'];
}
?>

	<table id="mainTable" class="tablesorter std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable sortable-onload-show">
		<thead>
			<tr>
				<th style="width: 5%">No Perusahan</th>
				<th>Nama Perusahan</th>
				<!--<th>Deskripsi</th> -->
				<th>Harga Beli</th>
				<th>Harga Jual</th>
				<th>Komisi</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$i = 1;
			$sql = "SELECT 
				ORD_DET_NBR,
				ORD_NBR,
				INV_NBR,
				OUT_CMN_F,
				SUM(ORD_Q) AS ORD_Q,
				SUM(INV_PRC) AS INV_PRC,
				PRN_ORD_NBR,
				PRN_ORD_DET_NBR,
				BUY_CO_NBR,
				BUY_CO_NAME,
				DET.ACCT_EXEC_NBR,
				DET_TTL,
				SUM(PRN_ORD_Q) AS PRN_ORD_Q,
				SUM(PRC) AS PRC,
				SUM(TOT_CMSN) AS TOT_CMSN,
				PAY_CONFIG_NBR
			FROM CDW.PAY_OUT_CMSN DET
			WHERE PAY_CONFIG_NBR = ".$PayConfigNbr." ".$where."
			GROUP BY DET.BUY_CO_NBR 
			ORDER BY DET.ORD_DET_NBR DESC";
			$result = mysql_query($sql);
			$alt="";
			while($row= mysql_fetch_array($result)) {
			?>
			<tr <?php echo $alt;?> style="cursor:pointer;" onclick="getContent('outsourcing','outsourcing-commission-table-det.php?FLR_MPR_PPL=<?php echo $FLR_MPR_PPL; ?>&FLR_OPT=<?php echo $filterOption;?>&PAY_CONFIG_NBR=<?php echo $PayConfigNbr; ?>&BUY_CO_NBR=<?php echo $row['BUY_CO_NBR'];?>')" >
				<td style="text-align:center;" ><?php echo $row['BUY_CO_NBR'];?></td>
				<td><?php echo $row['BUY_CO_NAME'];?></td>
				<td style="text-align:right"><?php echo number_format($row['INV_PRC'], 0, ',', '.');?></td>
				<td style="text-align:right"><?php echo number_format($row['PRC'], 0, ',', '.');?></td>
				<td style="text-align:right"><?php echo number_format($row['TOT_CMSN'], 0, ',', '.');?></td>
			</tr>
			<?php
			$i++;
			$totalBuyPrice 	+= $row['INV_PRC'];
			$totalSelPrice 	+= $row['PRC'];
			$totalCommision	+= $row['TOT_CMSN'];
			}
			?>
		</tbody>
	</table>

	<table id="tableBon" class="tablesorter searchTable" border=0 style="width:300px">
		<tbody>
			<tr style="border-top:1px solid grey">
				<td class="std" colspan="2">&nbsp;</td>
			</tr>
			<tr>
				<td class="std" style="font-weight: 700;width:200px">Total Harga Beli</td>
				<td class="std" style="font-weight: 700;width:100px;text-align:right"><?php echo number_format($totalBuyPrice,0,'.',',');?></td>
			</tr>
			<tr>
				<td class="std" style="font-weight: 700;">Total Harga Jual</td>
				<td class="std" style="font-weight: 700;text-align:right"><?php echo number_format($totalSelPrice,0,'.',',');?></td>
			</tr>
			<tr>
				<td class="std" style="font-weight: 700;">Total Komisi</td>
				<td class="std" style="font-weight: 700;text-align:right"><?php echo number_format($totalCommision,0,'.',',');?></td>
			</tr>
		</tbody>
	</table>