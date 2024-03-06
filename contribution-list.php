<?php
require_once "framework/database/connect.php";
require_once "framework/security/default.php";
$security = getSecurity($_SESSION['userID'], "Executive");	

$query = "SELECT PRSN_NBR, NAME FROM CMP.PEOPLE WHERE PRSN_NBR = " . $_GET['PRSN_NBR'];
$result = mysql_query($query);
$row = mysql_fetch_array($result);

$queryPrc = "SELECT CNBTN_PRC_DTE, PRC, UPD_TS FROM PAY.CNBTN_PRC ORDER BY CNBTN_PRC_NBR DESC LIMIT 1";
$resultPrc = mysql_query($queryPrc);
$rowPrc = mysql_fetch_array($resultPrc);

if($_POST['submit']){
	print_r($_POST['SEL_IMG']);
	/*
	$query  = "UPDATE PAY.CNBTN_LST 
		SET VAL_NBR=".$_SESSION['personNBR'].", 
		VAL_TS = CURRENT_TIMESTAMP 
	WHERE CNBTN_NBR=" . $documentNumber;
    $result = mysql_query($query);
*/    
}  
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" media="screen" href="css/accounting.css" />

	<script type="text/javascript">parent.Pace.restart();</script>
	<script type="text/javascript" src="framework/liveSearch/livesearch.js"></script>
	<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
	<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
	<script type="text/javascript" src="framework/uri/src/URI.min.js"></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<script type="text/javascript" src='framework/jquery-freezeheader/js/jquery.freezeheader.js'></script>
	<script type="text/javascript">
		function getFloat(objectID)
		{
			if(document.getElementById(objectID).value=='')
			{
				return 0;
			}
				else
			{
				return parseFloat(document.getElementById(objectID).value);
			}
		}
	
		function checkTotal() {
			document.listForm.total.value = '';
			var sum = 0;
			for (i=0;i<document.listForm.SEL_IMG.length;i++) {
			  if (document.listForm.SEL_IMG[i].checked) {
				sum = sum + parseFloat(document.listForm.SEL_IMG[i].value);
			  }
			}
			document.listForm.total.value = sum;
			document.listForm.TOT_AMT.value = Math.floor(sum * <?php echo $rowPrc['PRC']; ?>);
		}
		function gettotal(){
			checkTotal();
		};
	</script>
</head>
<body>

<div class="searchresult" id="liveRequestResults"></div>
<div id="mainResult">
	<h2><?php echo $row['NAME']; ?></h2>
	<h3>Rincian Gaji Kontribusi: <?php echo $row['PRSN_NBR']; ?></h3>
	
	<form enctype="multipart/form-data" name="listForm" action="#" method="post" style="width:98%" onSubmit="return checkform();">
	
	<table id="mainTable" class="tablesorter table-freeze std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable sortable-onload-show">
		<thead>
			<tr>
				<th class="sortable" style="text-align:center;">No</th>
				<th class="sortable">Tanggal</th>
				<th class="sortable">Gaji Kontribusi</th>
				<th class="sortable">Harga</th>
				<th class="sortable">Unit</th>
				<th class="sortable"style="padding:2px">
					<input name='SEL_IMGS' id='SEL_IMGS' type='checkbox' class='regular-checkbox' onclick="toggleCheckBox(this)" onchange="gettotal()"/>
					<label for='SEL_IMGS' style='margin-top:5px;margin-right:0px'></label>
				</th>
			</tr>
		</thead>
		<tbody>
		<?php 
		$i = 1;
		$query = "SELECT 
			CNBTN_NBR, LST.PRSN_NBR, NAME, POS_DESC, PYMT_DTE, CNBTN_VAL, CNBTN_PRC, COALESCE(CNBTN_PNT,0) AS CNBTN_PNT,0
		FROM PAY.CNBTN_LST LST
			INNER JOIN CMP.PEOPLE PPL ON LST.PRSN_NBR = PPL.PRSN_NBR
			INNER JOIN CMP.POS_TYP TYP ON PPL.POS_TYP = TYP.POS_TYP
		WHERE LST.DEL_NBR=0 AND LST.PRSN_NBR = " . $_GET['PRSN_NBR'] . "
		GROUP BY PYMT_DTE
		ORDER BY PYMT_DTE DESC";
		//echo $query;
		$result = mysql_query($query);
		while ($row = mysql_fetch_array($result)) { ?>
			<!--
			<tr style="cursor:pointer;" onclick="location.href='payroll-edit.php?PRSN_NBR=<?php echo $row['PRSN_NBR'];?>&PYMT_DTE=<?php echo $row['PYMT_DTE'];?>';">-->
			<tr>
				<td style="text-align:center;"><?php echo $i;?></td>
				<td style="text-align:center;"><?php echo $row['PYMT_DTE'];?></td>
				<td style="text-align:right;"><?php echo number_format($row['CNBTN_VAL'],0,",",".");?></td>
				<td style="text-align:right;"><?php echo number_format($row['CNBTN_PRC'],2,",",".");?></td>
				<td style="text-align:right;"><?php echo number_format($row['CNBTN_PNT'],2,",",".");?></td>
				<td style='padding:2px;text-align:center'>
					<input name='SEL_IMG' id='SEL_IMG_"<?php echo $row['CNBTN_NBR'];?>"' class='regular-checkbox' value="<?php echo $row['CNBTN_PNT'];?>" type='checkbox' onchange="checkTotal()"/>
					<label name='SEL_IMG' for='SEL_IMG_"<?php echo $row['CNBTN_NBR'];?>"' style='margin-right:0px;margin-top:5px' ></label>
				</td>
			</tr>
		<?php 
			$i++; 
			$totalUnit += $row['CNBTN_PNT'];
		}
		?>
		</tbody>
		<tfoot>
			<tr>
				<td class="std" style="text-align:right;font-weight:bold;border-top:1px solid grey;" colspan="6">&nbsp;</td>
			</tr>
			<tr>
				<td colspan=6>
						<table style='padding:0px;margin-bottom:10px' id="payment">
							<tr>
							<td style='padding:0px;width:380px'>
								<div class='total'>
								<table style="width:100%;">
									<tr class='total'>
										<td style='padding-left:7px;width:200px'>Total Unit</td>
										<td style="text-align:right">
											<input name="TOT_SUB" id="TOT_SUB" value="<?php echo $totalUnit; ?>" type="text" style="margin:1px;width:100px;border:none;text-align:right" readonly />
										</td>
									</tr>
									
									<tr class='total'>
										<td style='font-weight:bold;color:#3464bc;padding-left:7px;width:200px'>Unit Ditukar</td>
										<td style="text-align:right">
											<input name="CHG_PNT" id="total" value="" type="text" style="margin:1px;width:100px;border:none;text-align:right" readonly />
											<input name="CHG_NBR" id="nomor" value="" type="text" style="margin:1px;width:100px;border:none;text-align:right" readonly />
										</td>
									</tr>
									
									<?php
									$query = "SELECT CNBTN_PRC_DTE, PRC, UPD_TS FROM PAY.CNBTN_PRC ORDER BY CNBTN_PRC_NBR DESC LIMIT 1";
									$result = mysql_query($query);
									$row = mysql_fetch_array($result);
									?>
									<tr class='total'>
										<td style='font-weight:bold;color:#3464bc;padding-left:7px'>Harga Saat Ini</td>
										<td style="text-align:right">
											<input name="PYMT_REM" id="PYMT_REM" value="<?php echo $rowPrc['PRC']; ?>" type="text" style="margin:1px;width:100px;border:none;text-align:right" readonly />
										</td>
									</tr>
											
									<tr class='total'>
										<td style='font-weight:bold;color:#3464bc;border:0px;padding-left:7px'>Total</td>
										<td style="text-align:right;border:0px">
											<input name="TOT_AMT" id="TOT_AMT" value="" type="text" style="width:100px;border:none;text-align:right" readonly />	
										</td>
									</tr>
								</table>
								</div>
							</td>
							</tr>
						</table>
						<input class="process" type="submit" name="submit" value="Simpan">
				</td>
			</tr>
		</tfoot>
	</table>
	</form>
</div>
</body>
</html>			
