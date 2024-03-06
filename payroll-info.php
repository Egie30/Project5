<?php
	include "framework/database/connect.php";
	include "framework/database/connect-cloud.php";
	include "framework/functions/komisi.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/alert/alert.php";
	date_default_timezone_set('Asia/Jakarta');

	$personNumber 	= $_SESSION['personNBR'];
	$PrsnNbr 		= $_SESSION['personNBR'];
	$selectPerson	= $_GET['PRSN_NBR'];
	$Typ 		 	= $_GET['TYP'];
	$filterOption 	= $_GET['FLR_OPT'];
	$FLR_MPR_PPL    = $_GET['FLR_MPR_PPL'];
	$BuyCoNbr 		= $_GET['BUY_CO_NBR'];
	$configNbr 		= $_GET['PAY_CONFIG_NBR'];
	$PymtDte 		= date('Y-m-d');
	
	if($_GET['TYP']=="ACCOUNT"){
		$query	= "SELECT PRSN_NBR, NAME, POS_TYP FROM CMP.PEOPLE WHERE PRSN_NBR = " . $_GET['FLR_MPR_PPL'];
		$result	= mysql_query($query, $local);
		$row	= mysql_fetch_array($result);
		$personName = $row['NAME'];
		$posTyp = $row['POS_TYP'];
	} else {
		$query	= "SELECT PRSN_NBR, NAME, POS_TYP FROM CMP.PEOPLE WHERE PRSN_NBR = " . $personNumber;
		$result	= mysql_query($query, $local);
		$row	= mysql_fetch_array($result);
		$personName = $row['NAME'];
		$posTyp = $row['POS_TYP'];
	}

	$marketingArr = array('MGR','SNM','RAM','CMA','NAM','SYS','COM','DPG','SCM');
	$address		= getSecurity($_SESSION['userID'],"AddressBook");
	$security		= getSecurity($_SESSION['userID'],"Executive");
	
	if ($_POST['PRSN_NBR'] != "") {
		if (isset($_POST['ACT_F'])) {
			$paymentNbr 	= "";
			$approveF 		= $_POST['ACT_F'];
			
			$queryUpd 	= "UPDATE CDW.MKG_BNS SET 
				ACT_F=1, 
				UPD_NBR='".$_SESSION['personNBR']."', 
				UPD_TS=CURRENT_TIMESTAMP  
			WHERE ACCT_EXEC_NBR = ".$selectPerson." AND BUY_CO_NBR = ". $BuyCoNbr;
			echo $queryUpd."<br>";
			$resultUpd 	= mysql_query($queryUpd);

        	for ($i=0; $i < count($approveF) ; $i++){
				if($approveF[$i] > 0){
					$paymentNbr = $paymentNbr.$approveF[$i].",";

					$query 	= "UPDATE CDW.MKG_BNS SET 
						ACT_F=0, 
						UPD_NBR='".$_SESSION['personNBR']."', 
						UPD_TS=CURRENT_TIMESTAMP  
					WHERE PYMT_NBR =".$approveF[$i]." AND ACCT_EXEC_NBR = ".$selectPerson." AND BUY_CO_NBR = ". $BuyCoNbr;
					echo $query."<br>";
					$result 	= mysql_query($query);
				}
        	}
    	}
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/combobox/chosen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/pagination/pagination.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">

	<script type="text/javascript">parent.Pace.restart();</script>
	<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
	<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
	<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>
	<script type="text/javascript" src="framework/liveSearch/livesearch.js"></script>
	<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
	<script type="text/javascript" src="framework/pagination/pagination.js"></script>
	<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
	<script type="text/javascript" src="framework/uri/src/URI.min.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>
	<script type="text/javascript" src="framework/combobox/ajax-chosen/ajax-chosen.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.default.js"></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<script type="text/javascript">jQuery.noConflict();</script>
	<style>
		table.tablesorter thead tr .headerTd{
			border-bottom:1px solid #cacbcf;
		}
	</style>
	<style type="text/css">
		.time-upDown{
			width:9px;
			float:right;
			font-size:8px;
			visibility:hidden;
			margin-right:1px;
		}
		.listUp:hover,.listDown:hover{
			background-color: #989898;
			color:#fff;
		}
		.HeadTab{
			width: 100%;
		}
		.CalLeft, .CalRight{
			width: 50%;
		}
		.TblLeft, .TblRight{
			width: 100%;
		}
	</style>
</head>
<body>
<div id="mainResult">
	<h2>
		<?php echo $personName ?>
	</h2>
</div>
<div class="toolbar">
	<div class="combobox"></div>
	
	<div class="toolbar-text" style="padding-left: 0px;<?php if($Typ=="ACCOUNT"){ echo "display:none;";} ?>" >
	<p class="toolbar-left">
		<select id="FLR_OPT" name="FLR_OPT" class="chosen-select" style="width:300px;" onchange="location.href='?FLR_OPT='+document.getElementById('FLR_OPT').value">
			<option value="">Select Filter</option>
			<option value="FLR_ATND" <?php if($filterOption =="FLR_ATND"){echo "selected";}else{"";} ?>>Absensi</option>
			<option value="FLR_ATND_FULL" <?php if($filterOption =="FLR_ATND_FULL"){echo "selected";}else{"";} ?>>Absensi Full</option>
			<option value="FLR_OFF" <?php if($filterOption =="FLR_OFF"){echo "selected";}else{"";} ?>>Cuti</option>
			<option value="FLR_CRT" <?php if($filterOption =="FLR_CRT"){echo "selected";}else{"";} ?>>Kas Bon</option>
			<option value="FLR_HLD" <?php if($filterOption =="FLR_HLD"){echo "selected";}else{"";} ?>>Held Payroll</option>
			<option value="FLR_CNTRC" <?php if($filterOption =="FLR_CNTRC"){echo "selected";}else{"";} ?>>Kontrak</option>
			<!--<option value="FLR_GSS" <?php if($filterOption =="FLR_GSS"){echo "selected";}else{"";} ?>>Tebakan</option>-->
			<?php if (in_array($posTyp, $marketingArr) || $security <1){ ?>
				<option value="MKG_PRMC" <?php if($filterOption =="MKG_PRMC"){echo "selected";}else{"";} ?>>Sales Perfomance</option>
				<option value="FLR_MPR" <?php if($filterOption =="FLR_MPR"){echo "selected";}else{"";} ?>>Marketing Perfomance</option>
				<option value="OUT_CMSN" <?php if($filterOption =="OUT_CMSN"){echo "selected";}else{"";} ?>>Outsourcing Commission</option>
				<option value="MKG_BNS" <?php if($filterOption =="MKG_BNS"){echo "selected";}else{"";} ?>>Bonus Marketing</option>
			<?php  } ?>
			<!-- <?php if ($personNumber==3){ ?><option value="FLR_MPR" <?php if($filterOption =="FLR_MPR"){echo "selected";}else{"";} ?>>Marketing Perfomance</option><?php  } ?> -->
		</select>
		<?php if ($filterOption == 'FLR_MPR' || $filterOption == 'OUT_CMSN' && $security<1 || $posTyp == 'COM' || $posTyp == 'MGR'){ ?>
		<select id="FLR_MPR_PPL" name="FLR_MPR_PPL" class="chosen-select" style="width:300px;" onchange="location.href='?TYP=<?php echo $Typ; ?>&FLR_OPT=<?php echo $filterOption;?>&FLR_MPR_PPL='+document.getElementById('FLR_MPR_PPL').value">
			<?php 
				$queryPar = "SELECT CO_NBR_CMPST FROM NST.PARAM_COMPANY WHERE CO_NBR =".$CoNbrDef;
				$resultPar= mysql_query($queryPar, $local);
				$RowPar   = mysql_fetch_array($resultPar);
				$companyParam = $RowPar['CO_NBR_CMPST'];
				
				if($_GET['TYP']=="ACCOUNT" || $filterOption == 'OUT_CMSN'){
					$query = "SELECT PRSN_NBR, NAME FROM CMP.PEOPLE WHERE TERM_DTE IS NULL AND DEL_NBR = 0 AND POS_TYP IN ('SNM','RAM','CMA','NAM','COM','DPG') GROUP BY PRSN_NBR";
				} else {
					$query = "SELECT PRSN_NBR, NAME FROM CMP.PEOPLE WHERE TERM_DTE IS NULL AND DEL_NBR = 0 AND POS_TYP IN ('SNM','RAM','CMA','NAM','COM','DPG') GROUP BY PRSN_NBR";
				}
				genCombo($query, "PRSN_NBR","NAME",$FLR_MPR_PPL, "Kosong");
			?>
		</select>
		<?php } ?>
	</p>
	</div>
</div>
<div id="mainResult" >
	<?php if($filterOption == "" || $filterOption == "FLR_ATND" ){ ?>
	<h3>
		Perincian Absensi Nomor Induk: <?php echo $personNumber; ?>
	</h3>
	<div class="toolbar-left" style="padding: 5px;padding-left: 0;">
		<form action="" method="GET" style="padding: 0;margin: 0;margin-top: 10px;">
			<i style="display:none">
				<input type="text" style="width:200px; display" name="PRSN_NBR" value="<?php echo $personNumber;?>">
				<input type="text" style="width:200px; " name="CO_NBR" value="<?php echo $CoNbr;?>">
				<input type="text" style="width:200px; display" name="FLR_OPT" value="FLR_ATND">
			</i>
			<select name="BULAN" class="chosen-select" id="select-bulan" style="width: 150px;">
				<?php
				if($_GET['BULAN'] == ""){
					$_GET['BULAN'] = date('m');
				}
				for ($i = 1; $i <= 12; $i++) {
					$select = '';
					if ($i == $_GET['BULAN']) {
						$select = 'selected=""';
					}
					echo '<option value="' . $i . '" ' . $select . '>' . date('F', strtotime(date('d-' . $i . '-Y'))) . '</option>';
				}
				?>
			</select>
			<span style="padding-left: 12px;">
			<select name="TAHUN" class="chosen-select" id="select-tahun" style="width: 100px; ">
				<?php
				if($_GET['TAHUN'] == ""){
					$_GET['TAHUN'] = date('Y');
				}
				for ($i = date('Y') - 1; $i <= date('Y'); $i++) {
					$select = '';
					if ($i == $_GET['TAHUN']) {
						$select = 'selected=""';
					}
					echo '<option value="' . $i . '" ' . $select . '>' . $i . '</option>';
				}
				?>
			</select>
			</span>
			<button type="submit" style="background: none;border:none; cursor: pointer; padding-left: 13px;">
				<span class="fa fa-calendar toolbar fa-lg" style="padding-left: 0px;"></span>
			</button>
		</form>
    </div>
	<table id="mainTable" class="tablesorter searchTable" style="width:700px;">
		<tr>
			<td><?php include  "payroll-calendar-out.php"; ?></td>
		<tr>
	</table>
	<?php } ?>

	<?php if($filterOption == "FLR_ATND_FULL" ){ ?>
	<?php 
	$PrsnNbr	= $_GET['PRSN_NBR'];
	$CoNbr		= $_GET['CO_NBR'];
	$UpdTsDel	= $_GET['UPD_TS'];
	$del 		= $_GET['DEL'];
	$BlnAtnd	= $_GET['BULANATND'];
	$BlnMch		= $_GET['BULANMACH'];
	$nbrDays	= 0;	
	$month		= parseMonth($PymtDte);
	$year		= parseYear($PymtDte);
	
	$bulan = date('m');
	$tahun = date('Y');
	if (isset($_GET['BULAN'])) {
		$bulan = $_GET['BULAN'];
	}
	if (isset($_GET['TAHUN'])) {
		$tahun = $_GET['TAHUN'];
	}
	?>
	<h3>
		Perincian Absensi Nomor Induk: <?php echo $personNumber; ?>
	</h3>
	<div class="toolbar-left" style="padding: 5px;padding-left: 0;">
    <form action="" method="GET" style="padding: 0;margin: 0;margin-top: 10px;">
		<i style="display:none">
			<input type="text" style="width:200px; display" name="PRSN_NBR" value="<?php echo $personNumber;?>">
			<input type="text" style="width:200px; " name="CO_NBR" value="<?php echo $CoNbr;?>">
			<input type="text" style="width:200px; display" name="FLR_OPT" value="FLR_ATND_FULL">
		</i>
        <select name="BULAN" class="chosen-select" id="select-bulan" style="width: 150px;">
            <?php
            for ($i = 1; $i <= 12; $i++) {
                $select = '';
                if ($i == $bulan) {
                    $select = 'selected=""';
                }
                echo '<option value="' . $i . '" ' . $select . '>' . date('F', strtotime(date('d-' . $i . '-Y'))) . '</option>';
            }
            ?>
        </select>
		<span style="padding-left: 12px;">
        <select name="TAHUN" class="chosen-select" id="select-tahun" style="width: 100px; ">
            <?php
            for ($i = date('Y') - 1; $i <= date('Y'); $i++) {
                $select = '';
                if ($i == $tahun) {
                    $select = 'selected=""';
                }
                echo '<option value="' . $i . '" ' . $select . '>' . $i . '</option>';
            }
            ?>
        </select>
		</span>
        <button type="submit" style="background: none;border:none; cursor: pointer; padding-left: 13px;">
            <span class="fa fa-calendar toolbar fa-lg" style="padding-left: 0px;"></span>
        </button>
    </form>
    </div>
	<?php 
	$query		= "SELECT 	PPL.PRSN_NBR,
							NAME, 
							ACK.CRT_TS
						FROM CMP.PEOPLE PPL
						LEFT JOIN (
									SELECT 	PRSN_NBR,
											CRT_TS 
										FROM PAY.ATND_CLOK 
										WHERE MONTH(CRT_TS)= ".$bulan."
										AND YEAR(CRT_TS)= ".$tahun."
										AND DEL_NBR =0
								  )ACK ON PPL.PRSN_NBR = ACK.PRSN_NBR
						WHERE DEL_NBR=0 AND PPL.PRSN_NBR=".$personNumber." ORDER BY ACK.CRT_TS ASC";
	$days		= date("t");
	$result		= mysql_query($query);
	$num		= mysql_num_rows($result);
	$PymtDte	= date("Y-m-d H:i:s");	
	while($row = mysql_fetch_array($result)){
		$name 	= $row['NAME'];
		$PrsnNbr= $row['PRSN_NBR'];
		$UpdTs[]= $row['CRT_TS'];
		
	}
	?>
		<table id="mainTable" class="tablesorter searchTable" style="width:600px;">
        <thead>
        <tr >
            <th style="width:5%;">No</th>
			<th style="width:45%">Date</th>
			<th style="width:40%">Time</th>
        </tr>
        </thead>
        <tbody>
        <?php
		for ($i=0;$i<$num;$i++){
		if (isset($UpdTs[$i])){		
		?>
		<tr>
			<td align="center" ><?php $j=$i+1; echo $j;?></td>
			<td style ="cursor: pointer;" onclick="pushFormIn('payroll-attendance-edit.php?PRSN_NBR=<?php echo $PrsnNbr;?>&CO_NBR=<?php echo $CoNbr; ?>&CRTIME=<?php echo $UpdTs[$i];?>');"><?php echo parseDate($UpdTs[$i]);?></td>
			<td style ="cursor: pointer;" onclick="pushFormIn('payroll-attendance-edit.php?PRSN_NBR=<?php echo $PrsnNbr;?>&CO_NBR=<?php echo $CoNbr; ?>&CRTIME=<?php echo $UpdTs[$i];?>');"><?php echo parseTimeShort($UpdTs[$i])?></td>
		</tr>
        <?php
			}//if
		}//for
        ?>		
        </tbody>
    </table>
	<?php } ?>
	
	<?php if($filterOption != "" && $filterOption == "FLR_OFF" ){ ?>
	<h3>
		Perincian Data Cuti Nomor Induk: <?php echo $personNumber; ?>
	</h3>
	<table id="tableCuti" class="tablesorter searchTable" style="width:700px;">
		<thead>
			<tr>
				<th style="width: 8%">No.</th>
				<th style="width: 12%">Mulai</th>
				<th style="width: 12%">Selesai</th>
				<th style="width: 15%">Jumlah Cuti</th>
				<th style="width: 50%">Alasan</th>
				<th>Status</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$Cuti 			= 8;
			$sql="SELECT 
				TM_OFF_NBR,
				PRSN_NBR,
				CONCAT(TM_OFF_BEG_DTE,' - ',TM_OFF_END_DTE) AS TM_OFF_DTE,
				TM_OFF_BEG_DTE,
				TM_OFF_END_DTE,
				TM_OFF_RSN,
				TM_OFF_F,
				DATEDIFF(TM_OFF_END_DTE,TM_OFF_BEG_DTE)+1 AS CNT_TM_OFF,
				(CASE WHEN TM_OFF_F=1 THEN DATEDIFF(TM_OFF_END_DTE,TM_OFF_BEG_DTE)+1 ELSE 0 END) AS CNT_DTE
			FROM PAY.TM_OFF  
			WHERE PRSN_NBR = ". $personNumber ." AND DEL_NBR=0 AND YEAR(TM_OFF_BEG_DTE) = YEAR(CURRENT_DATE)
			GROUP BY TM_OFF_NBR DESC";
			//echo $sqlx;
			$result = mysql_query($sql, $local);
			while($row= mysql_fetch_array($result)) {
			?>
			<tr>
				<td><?php echo $row['TM_OFF_NBR'];?></td>
				<td><?php echo $row['TM_OFF_BEG_DTE'];?></td>
				<td><?php echo $row['TM_OFF_END_DTE'];?></td>
				<td><?php echo $row['CNT_TM_OFF'];?></td>
				<td><?php echo $row['TM_OFF_RSN'];?></td>
				<td><?php if($row['TM_OFF_F']=="1"){echo "Disetujui";}else{echo "Tidak Disetujui";} ?></td>
			</tr>
			<?php
				$totalCntDte+= $row['CNT_DTE'];
			}
			?>
		</tbody>
		</tfoot>
			<tr style="border-top:1px solid grey">
				<td class="std" colspan="2"><b>Total Cuti</b></td>
				<td class="std" colspan="3"><b><?php echo number_format($totalCntDte,0,'.',',');?> hari</b></td>
			</tr>
			<tr>
				<td class="std" colspan="2" style="font-weight: 700;">Total Sisa Cuti</td>
				<td class="std" colspan="3" id="TotCuti" style="font-weight: 700;"><?php echo number_format($Cuti-$totalCntDte,0,'.',',');?> hari</td>
			</tr>
		</tfoot>
	</table>
	<?php } ?>
	
	<?php if($filterOption != "" && $filterOption == "FLR_HLD" ){ ?>
	<h3>
		Perincian Data Gaji Ditahan Nomor Induk: <?php echo $personNumber; ?>
	</h3>
	<table id="tableHeld" class="tablesorter searchTable" style="width:700px;">
		<thead>
			<tr>
				<th style="width: 5%">No</th>
				<th>Tanggal</th>
				<th>Gaji Ditahan</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$h = 1;
			$sql = "SELECT 
				LST.PYMT_DTE,
				COALESCE(SUM(PAY_HLD_AMT),0) AS PAY_HLD_AMT,
				COALESCE(TOT_HLD_AMT,0) AS TOT_HLD_AMT,
				COALESCE(TOT_HLD_PD,0) AS TOT_HLD_PD,
				COALESCE(TOT_HLD_COM,0) AS TOT_HLD_COM,
				COALESCE(TOT_LEAD,0) AS TOT_LEAD
			FROM PAY.PAY_HLD_LST LST
			LEFT OUTER JOIN(
				SELECT 
					LST.PRSN_NBR,
					SUM(CASE WHEN LST.PAY_HLD_TYP = '1' THEN LST.PAY_HLD_AMT ELSE 0 END) AS TOT_HLD_AMT,
					SUM(CASE WHEN LST.PAY_HLD_TYP = '2' THEN LST.PAY_HLD_AMT ELSE 0 END) AS TOT_HLD_PD,
					SUM(CASE WHEN LST.PAY_HLD_TYP = '3' THEN LST.PAY_HLD_AMT ELSE 0 END) AS TOT_HLD_COM,
					SUM(CASE WHEN LST.PAY_HLD_TYP = '1' THEN LST.PAY_HLD_AMT ELSE 0 END) - 
					SUM(CASE WHEN LST.PAY_HLD_TYP = '2' THEN LST.PAY_HLD_AMT ELSE 0 END) - 
					SUM(CASE WHEN LST.PAY_HLD_TYP = '3' THEN LST.PAY_HLD_AMT ELSE 0 END) AS TOT_LEAD,
					LST.PYMT_DTE
				FROM PAY.PAY_HLD_LST LST
				GROUP BY LST.PRSN_NBR
			)HLD ON LST.PRSN_NBR = HLD.PRSN_NBR
			WHERE PAY_HLD_TYP='1' AND LST.PRSN_NBR = ". $personNumber ."
			GROUP BY LST.PYMT_DTE";
			$result = mysql_query($sql, $local);
			while($row= mysql_fetch_array($result)) {
			?>
			<tr>
				<td><?php echo $h;?></td>
				<td><?php echo $row['PYMT_DTE'];?></td>
				<td style="text-align:right"><?php echo number_format($row['PAY_HLD_AMT'],0,'.',',');?></td>
			</tr>
			<?php
				$totalHold 			= $row['TOT_HLD_AMT'];
				$totalholdRemain 	= $row['TOT_LEAD'];
				$totalholdPaid 		= $row['TOT_HLD_PD'];
				$h++;
			}
			?>
		</tbody>
		</tfoot>
			<tr style="border-top:1px solid grey">
				<td class="std" colspan="2" style="font-weight: 700;">Total Gaji Ditahan</td>
				<td class="std" style="text-align:right;font-weight: 700;"><?php echo number_format($totalHold,0,'.',',');?></td>
			</tr>
			<tr>
				<td class="std" colspan="2" style="font-weight: 700;">Total Gaji Diberikan</td>
				<td class="std" style="text-align:right;font-weight: 700;"><?php echo number_format($totalholdPaid,0,'.',',');?></td>
			</tr style="border-top:1px solid grey">
			<tr>
				<td class="std" colspan="2" style="font-weight: 700;">Sisa</td>
				<td class="std" style="text-align:right;font-weight: 700;"><?php echo number_format($totalholdRemain,0,'.',',');?></td>
			</tr>
		</tfoot>
	</table>
	<?php } ?>
	
	<?php if($filterOption != "" && $filterOption == "FLR_CRT" ){ ?>
	<h3>
		Perincian Data Kas Bon Nomor Induk: <?php echo $personNumber; ?>
	</h3>
	<table id="tableBon" class="tablesorter searchTable" style="width:700px;">
		<thead>
			<tr>
				<th style="width: 5%">No</th>
				<!--
				<th>Tanggal Pinjam</th>
				<th>Bon Pokok</th>
				<th>Jumlah bon</th>
				-->
				<th>Tanggal Pembayaran Cicilan</th>
				<th>Cicilan per bulan</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$i = 1;
			$sql = "SELECT 
				PAY.PRSN_NBR,
				PAY.PYMT_DTE,
				DEBT_MO,
				CRD.CRDT_AMT
			FROM PAY.PAYROLL PAY
				LEFT OUTER JOIN(
					SELECT 
						PRSN_NBR, 
						SUM(CRDT_AMT) AS CRDT_AMT
					FROM PAY.EMPL_CRDT 
					WHERE (DEL_NBR = 0 OR DEL_NBR IS NULL) AND CRDT_APV = 1 AND CRDT_APV_FIN = 1
					GROUP BY PRSN_NBR
				) AS CRD ON CRD.PRSN_NBR = PAY.PRSN_NBR
			WHERE ( PAY.DEL_NBR = 0 OR PAY.DEL_NBR IS NULL) AND DEBT_MO != 0 AND PAY.PRSN_NBR = ".$personNumber."
			GROUP BY PAY.PYMT_DTE
			ORDER BY PAY.PYMT_DTE DESC";
			
			$result = mysql_query($sql, $local);
			while($row= mysql_fetch_array($result)) {
			?>
			<tr>
				<td><?php echo $i;?></td>
				<!--
				<td style="text-align:center"><?php echo $row['BON_DTE'];?></td>
				<td style="text-align:right"><?php echo number_format($row['CRDT_PRNC'], 0, ',', '.');?></td>
				<td style="text-align:right"><?php echo number_format($row['CRDT_AMT'], 0, ',', '.');?></td>
				-->
				<td style="text-align:center"><?php echo $row['PYMT_DTE'];?></td>
				<td style="text-align:right"><?php echo number_format($row['DEBT_MO'], 0, ',', '.');?></td>
			</tr>
			<?php
			$i++;
			$totalcreditPrice 	= $row['CRDT_PRNC'];
			$totalcreditAmount 	= $row['CRDT_AMT'];
			$totalcreditRemain 	+= $row['DEBT_MO'];
			}
			?>
				
		</tbody>
		</tfoot>
			<tr style="border-top:1px solid grey">
				<td class="std" colspan="3">&nbsp;</td>
			</tr>
			<tr>
				<td class="std" colspan="2" style="font-weight: 700;">Total Bon</td>
				<td class="std" style="font-weight: 700;text-align:right"><?php echo number_format($totalcreditAmount,0,'.',',');?></td>
			</tr>
			<tr>
				<td class="std" colspan="2" style="font-weight: 700;">Total Cicilan</td>
				<td class="std" style="font-weight: 700;text-align:right"><?php echo number_format($totalcreditRemain,0,'.',',');?></td>
			</tr>
			<tr>
				<td class="std" colspan="2" style="font-weight: 700;">Total Sisa Bon</td>
				<td class="std" style="font-weight: 700;text-align:right"><?php echo number_format($totalcreditAmount-$totalcreditRemain,0,'.',',');?></td>
			</tr>
		</tfoot>
	</table>
	<?php } ?>


	<!-- Tampilan untuk Kontrak Karyawan -->
	<?php if($filterOption != "" && $filterOption == "FLR_CNTRC" )
	{ 
		include "framework/database/connect-cloud.php";
		error_reporting(0);
	?>

		<table id="tabelcnrtc" class="tablesorter searchTable" style="width:700px;">
		<thead>
			<tr>
				<th  style="text-align:center;">Jenis Kontrak</th>
				<th  style="text-align:center;">Tanggal Mulai</th>
				<th  style="text-align:center;">Tanggal Selesai</th>
			</tr>
		</thead>
		<tbody>

		<?php 
		$query 	= "SELECT CNTRCT.EMPL_CNTRCT_NBR AS EMPL_CNTRCT_NBR,
						  CNTRCT.PRSN_NBR AS PRSN_NBR,
						  CNTRCT_TYP.EMPL_CNTRCT_DESC AS EMPL_CNTRCT_DESC,
						  CNTRCT.BEG_DTE AS BEG_DTE,
						  CNTRCT.END_DTE AS END_DTE
					FROM $CMP.EMPL_CNTRCT CNTRCT
					LEFT JOIN $CMP.EMPL_CNTRCT_TYP CNTRCT_TYP ON CNTRCT_TYP.EMPL_CNTRCT_TYP = CNTRCT.EMPL_CNTRCT_TYP
					WHERE PRSN_NBR = '".$personNumber."' ORDER BY CNTRCT.BEG_DTE DESC";
				 // echo $query;
		$resultc  	= mysql_query($query,$cloud);
		$alt="";

		while($rowc = mysql_fetch_array($resultc))
		{
				$begdet   = strtotime($rowc['BEG_DTE']);
				$begdetz  = date("d M Y", $begdet);

				$enddet   = strtotime($rowc['END_DTE']);
				$enddetz  = date("d M Y", $enddet);

				echo "<tr class='std' $alt onclick=".chr(34)."pushFormIn('employment-contract-edit-detail.php?EMPL_CNTRCT_NBR=".$rowc['EMPL_CNTRCT_NBR']."&PRSN_NBR=".$PRSN_NBR."')".chr(34).">";
				echo "<td class='std' style = 'cursor:pointer;text-align:left'>".$rowc['EMPL_CNTRCT_DESC']."</td>";

				echo "<td class='std' style = 'cursor:pointer;text-align:center;'>".$begdetz."</td>";
				echo "<td class='std' style = 'cursor:pointer;text-align:center;'>".$enddetz."</td>";
				echo "</tr>";
				if($alt == "") { $alt = "class='alt'"; } else { $alt = ""; }
		}
		?>
		</tbody>
	</table>
	<?php } ?>

<!-- Tampilan untuk tebak tebakan -->
<?php if($filterOption != "" && $filterOption == "FLR_GSS" ){ ?>
<?php 
	include "framework/database/connect-cloud.php";
	ini_set('max_execution_time',-1);
	$j=syncTable("TEBAKAN","PRSN_NBR","CMP",$CMP,$local,$cloud);

	if ($_POST['PRSN_NBR']!=''){
		if ($_POST['PRSN_NBR']==-1){
			$query = "SELECT NAMA, PRSN_NBR FROM $CMP.TEBAKAN WHERE PRSN_NBR = ".$personNumber;
			$result= mysql_query($query,$cloud);
			$row   = mysql_fetch_array($result);

			if ($row['PRSN_NBR']!=''){$prsnNbr = $row['PRSN_NBR'];$name=$row['NAMA'];}else{$prsnNbr = $personNumber;$name = $personName;}

			$query = "INSERT INTO $CMP.TEBAKAN (NAMA, PRSN_NBR, TS) VALUES ('".$name."',".$prsnNbr.", CURRENT_TIMESTAMP)";
			$result= mysql_query($query,$cloud);
			$query = str_replace($CMP,"CMP",$query);
			$result= mysql_query($query,$local);
		}

		if ($_POST['TM_JM_BORN']==''){$tmJmBorn='00';}else{$tmJmBorn="".$_POST['TM_JM_BORN']."";}
		if ($_POST['TM_MN_BORN']==''){$tmMnBorn='00';}else{$tmMnBorn="".$_POST['TM_MN_BORN']."";}
		if ($_POST['TM_SC_BORN']==''){$tmScBorn='00';}else{$tmScBorn="".$_POST['TM_SC_BORN']."";}
		if ($_POST['BERAT']==''){$berat='NULL';}else{$berat=$_POST['BERAT'];}
		
		$tmBorn = "'".$tmJmBorn.":".$tmMnBorn.":".$tmScBorn."'";
		$berat  = str_replace(",", ".", $berat);

		$query = "UPDATE $CMP.TEBAKAN SET 
					BERAT   = ".$berat.",
					TM_BORN = ".$tmBorn.",
					VALID   = 1,
					UPD_TS  = CURRENT_TIMESTAMP
				 WHERE PRSN_NBR = ".$personNumber." AND YEAR(TS)=2018";
		$result= mysql_query($query,$cloud);
		$query = str_replace($CMP,"CMP",$query);
		$result= mysql_query($query,$local);

		$query = "INSERT INTO CMP.TEBAKAN_LOG(NAMA, PRSN_NBR, TM_BORN, BERAT, TS, VALID, UPD_TS) VALUES 
					('".$personName."',".$personNumber.",".$tmBorn.",".$berat.",CURRENT_TIMESTAMP,1,CURRENT_TIMESTAMP)";
		$result= mysql_query($query, $local);

	}

	$query = "SELECT PRSN_NBR, TM_BORN, BERAT, HOUR(TM_BORN) AS HR, MINUTE(TM_BORN) AS MN, SECOND(TM_BORN) AS SC  FROM CMP.TEBAKAN WHERE PRSN_NBR =".$personNumber." AND YEAR(TS)=2018";
	$result= mysql_query($query,$local);
	$row   = mysql_fetch_array($result);
	$jam   = $row['HR'];
	$menit = $row['MN'];
	$detik = $row['SC'];

?>
	<h3>
		<?php //echo $personNumber; ?>
		<?php if ($row['TM_BORN']!='') { ?>
		Jawaban terakhir jam lahir <?php echo $row['TM_BORN'];?> dan berat <?php echo $row['BERAT'];?> Kg
		<?php } ?>
	</h3>
	
	<table style="border-width: 0px;width: 100%">
		<td>
			<form enctype="multipart/form-data" action="#" method="post" style="width:50%;padding-left: 0px;" autocomplete="off">
				<input name="PRSN_NBR" value="<?php echo $row['PRSN_NBR'];if($row['PRSN_NBR']==""){echo "-1";} ?>" type="hidden" />

				<label>Jam berapakah Charlotte Quinn Onggowijaya lahir?</label><br/>
				<input id="TM_JM_BORN" name="TM_JM_BORN"  placeholder="00" value="<?php echo $jam; ?>" type="text" size="3" /> : 
				<input id="TM_MN_BORN" name="TM_MN_BORN"  placeholder="00" value="<?php echo $menit; ?>" type="text" size="3" /> : 
				<input id="TM_SC_BORN" name="TM_SC_BORN"  placeholder="00" value="<?php echo $detik; ?>" type="text" size="3" /><br />
				
				<label>Berapa berat Charlotte Quinn Onggowijaya lahir?</label><br/>
				<input id="BERAT" name="BERAT"  type="text" value="<?php echo $row['BERAT'];?>" size="8" /><span style="padding-left: 5px;">Kg</span><br />

				<input  id='submit_button'  class='process submit_button' type='submit' value='Simpan' />
			</form>
		</td>

		<td>

		</td>
	</table>
<?php } ?>

<!-- Tampilan untuk tebak Outsourcing Commission -->
<?php if($filterOption != "" && $filterOption == "OUT_CMSN" ){ ?>
	<h3>
		Rincian Data Outsourcing Commission: <?php echo $PayConfigNbr; ?>
	</h3>
	<select id="PAY_CONFIG_NBR" name="PAY_CONFIG_NBR" class="chosen-select" style="width:300px;" onchange="getContent('outsourcing','outsourcing-commission-table.php?FLR_MPR_PPL=<?php echo $FLR_MPR_PPL; ?>&FLR_OPT=<?php echo $filterOption;?>&PAY_CONFIG_NBR='+this.value);">
		<?php 
			$PayConfigNbr = $_GET['PAY_CONFIG_NBR'];
			if ($PayConfigNbr==''){
				$query = "SELECT PAY_CONFIG_NBR,PAY_BEG_DTE FROM PAY.PAY_CONFIG_DTE WHERE PAY_BEG_DTE <= CURRENT_DATE AND PAY_END_DTE >= CURRENT_DATE ";
				$result = mysql_query($query, $local);
				$rowDte = mysql_fetch_array($result);

				$PayConfigNbr = $rowDte['PAY_CONFIG_NBR'];
			}else{
				$PayConfigNbr = $rowDte['PAY_CONFIG_NBR'];
			}
			$query = "SELECT PAY_CONFIG_NBR, DATE_FORMAT(PAY_BEG_DTE, '%M %Y') AS MY_PAY_RWD 
			FROM PAY.PAY_CONFIG_DTE
			ORDER BY PAY_BEG_DTE DESC";
			genCombo($query,"PAY_CONFIG_NBR","MY_PAY_RWD",$PayConfigNbr);
		?>
	</select></br>
	<div id="outsourcing"></div>
	<script>getContent('outsourcing','outsourcing-commission-table.php?FLR_MPR_PPL=<?php echo $FLR_MPR_PPL; ?>&FLR_OPT=<?php echo $filterOption;?>&PAY_CONFIG_NBR=<?php echo $PayConfigNbr; ?>');</script>
<?php } ?>

<!-- Tampilan untuk tebak Marketing Perfomance -->
<?php if($filterOption != "" && $filterOption == "MKG_PRMC" ){ ?>
	<?php 
	$query = "SELECT PAY_CONFIG_NBR,PAY_BEG_DTE FROM PAY.PAY_CONFIG_DTE WHERE PAY_BEG_DTE <= CURRENT_DATE AND PAY_END_DTE >= CURRENT_DATE";
	$result = mysql_query($query);
	$rowDte = mysql_fetch_array($result);
	$PayConfigNbr = $rowDte['PAY_CONFIG_NBR'];
	
	if (isset($_GET['PAY_CONFIG_NBR'])) {
		$configNbr 	= $_GET['PAY_CONFIG_NBR'];
		$where 		= "AND PAY_CONFIG_NBR=".$configNbr;
	}
	?>
	<h3>
		Nomor Induk: <?php echo $personNumber; ?>
	</h3>
	<div class="toolbar-left" style="padding: 5px;padding-left: 0;">
		<select id="PRSN_NBR" name="PRSN_NBR" class="chosen-select" style="width:250px;">
			<?php 
				if (isset($_GET['PRSN_NBR'])) {
					$configNbr 		= $_GET['PAY_CONFIG_NBR'];
					$selectNumber 	= $_GET['PRSN_NBR'];
				}
				if($security < 1 || in_array($posTyp,array('COM','MGR','SCM'))){
					$query = "SELECT 
						PRSN_NBR, NAME FROM CMP.PEOPLE 
					WHERE TERM_DTE IS NULL AND DEL_NBR = 0 AND POS_TYP IN ('SNM','RAM','CMA','NAM','COM','DPG','SCM')
					GROUP BY PRSN_NBR";
					genCombo($query, "PRSN_NBR","NAME",$selectNumber, "Kosong");
					echo $query;
				}else{
					$query = "SELECT 
						PRSN_NBR, NAME FROM CMP.PEOPLE 
					WHERE TERM_DTE IS NULL AND DEL_NBR = 0 AND POS_TYP IN ('SNM','RAM','CMA','NAM','COM','DPG','SCM') AND PRSN_NBR = ".$personNumber."
					GROUP BY PRSN_NBR";
					genCombo($query, "PRSN_NBR","NAME",$_GET['PRSN_NBR']);
				}
			?>
		</select>
		
        <select name="PAY_CONFIG_NBR" id="PAY_CONFIG_NBR" class="chosen-select" style="margin-left:20px;width:200px;">
            <?php 
				$query = "SELECT PAY_CONFIG_NBR,PAY_BEG_DTE FROM PAY.PAY_CONFIG_DTE WHERE PAY_BEG_DTE <= CURRENT_DATE AND PAY_END_DTE >= CURRENT_DATE";
				$result = mysql_query($query);
				$rowDte = mysql_fetch_array($result);
				$PayConfigNbr = $rowDte['PAY_CONFIG_NBR'];
				if ($_GET['PAY_CONFIG_NBR'] == ""){
					$_GET['PAY_CONFIG_NBR'] = $PayConfigNbr;
				}
				
				$query = "SELECT 
					PAY_CONFIG_NBR, PAY_BEG_DTE, PAY_END_DTE, GROUP_CONCAT(PAY_BEG_DTE,' - ',PAY_END_DTE) AS PERIOD_DTE 
				FROM PAY.PAY_CONFIG_DTE
				WHERE  MONTH(PAY_END_DTE) <= MONTH(CURRENT_DATE) AND YEAR(PAY_END_DTE) = YEAR(CURRENT_DATE)
				GROUP BY PAY_CONFIG_NBR";
				genCombo($query, "PAY_CONFIG_NBR","PERIOD_DTE",$_GET['PAY_CONFIG_NBR'], "Kosong");
			?>
        </select>
		<span class="fa fa-calendar toolbar fa-lg" style="cursor:pointer;padding-left:10px;" onclick="location.href='?FLR_OPT=MKG_BNS&PRSN_NBR='+document.getElementById('PRSN_NBR').value+'&PAY_CONFIG_NBR='+document.getElementById('PAY_CONFIG_NBR').value"></span>
    </div>
	<?php
	// Perhitungan untuk mendapatkan Net Monthly Reward (chart1)
	$query = "SELECT
		COUNT(HED.ORD_NBR) AS CNT_ORD_NBR,
		DATE(HED.ORD_TS) AS ORD_DTE,
		MONTH(HED.ORD_TS) AS ORD_MON,
		MONTHNAME(HED.ORD_TS) AS ORD_MON_NAME,
		YEAR(HED.ORD_TS) AS ORD_YER,
		SUM(HED.TOT_AMT) AS TOT_AMT,
		SUM(HED.TOT_REM) AS TOT_REM,
		SUM(PYMT.TND_AMT) AS TND_AMT
	FROM CMP.PRN_DIG_ORD_HEAD HED
		LEFT OUTER JOIN CMP.PRN_DIG_ORD_PYMT PYMT ON HED.ORD_NBR = PYMT.ORD_NBR AND PYMT.DEL_NBR=0
		INNER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR = COM.CO_NBR
	WHERE HED.DEL_NBR =0
		AND COM.ACCT_EXEC_NBR !=0
		AND (HED.BUY_CO_NBR NOT IN (SELECT CO_NBR FROM NST.PARAM_COMPANY) OR HED.BUY_CO_NBR IS NULL) 
		AND DATE(HED.ORD_TS) BETWEEN (DATE_FORMAT(CURRENT_DATE, '%Y-%m-1') - INTERVAL 14 MONTH) AND CURRENT_DATE
	GROUP BY DATE_FORMAT(DATE(HED.ORD_TS),'%Y'),DATE_FORMAT(DATE(HED.ORD_TS),'%c')*1,DATE_FORMAT(DATE(HED.ORD_TS),'%b')";
	//echo "<pre>".$query."<br><br>";
	$result= mysql_query($query);
	$monthPrint		= array();
	$amountPrint	= array();
	$numArr= mysql_num_rows($result);
	while ($row = mysql_fetch_array($result)) {
		$amountPrint[]= $row['TOT_AMT'];
		$monthPrint[]= "'".$row['ORD_MON']." ".$row['ORD_MON_NAME']."'";
		$i++;
	}

	$monthAmount	= "[".implode(",", $amountPrint)."]";
	$monthName		= "[".implode(",", $monthPrint)."]";
	?>
	</br>
	<span id="monthlyPerfomance" style="display:inline-block;width: 500px; height: 300px; margin:0 10px 0;padding:0;"></span>
	<div id="marketing-performance"></div>
	<script>getContent('marketing-performance','marketing-performance.php?FLR_MPR_PPL=<?php echo $FLR_MPR_PPL; ?>&PAY_CONFIG_NBR=<?php echo $PayConfigNbr; ?>');</script>
<?php } ?>

<?php if($filterOption == "MKG_BNS" ){ ?>
	<?php 
	$query = "SELECT PAY_CONFIG_NBR,PAY_BEG_DTE FROM PAY.PAY_CONFIG_DTE WHERE PAY_BEG_DTE <= CURRENT_DATE AND PAY_END_DTE >= CURRENT_DATE";
	$result = mysql_query($query);
	$rowDte = mysql_fetch_array($result);
	$PayConfigNbr = $rowDte['PAY_CONFIG_NBR'];
	
	if (isset($_GET['PAY_CONFIG_NBR'])) {
		$configNbr 	= $_GET['PAY_CONFIG_NBR'];
		$where 		= "AND PAY_CONFIG_NBR=".$configNbr;
	}
	?>
	<h3>
		Nomor Induk: <?php echo $personNumber; ?>
	</h3>
	<div class="toolbar-left" style="padding: 5px;padding-left: 0;">
		<select id="PRSN_NBR" name="PRSN_NBR" class="chosen-select" style="width:250px;">
			<?php 
				if (isset($_GET['PRSN_NBR'])) {
					$configNbr 		= $_GET['PAY_CONFIG_NBR'];
					$selectNumber 	= $_GET['PRSN_NBR'];
				}
				if($security < 1 || in_array($posTyp,array('COM','MGR','SCM'))){
					$query = "SELECT 
						PRSN_NBR, NAME FROM CMP.PEOPLE 
					WHERE TERM_DTE IS NULL AND DEL_NBR = 0 AND POS_TYP IN ('SNM','RAM','CMA','NAM','COM','DPG','SCM')
					GROUP BY PRSN_NBR";
					genCombo($query, "PRSN_NBR","NAME",$selectNumber, "Kosong");
					echo $query;
				}else{
					$query = "SELECT 
						PRSN_NBR, NAME FROM CMP.PEOPLE 
					WHERE TERM_DTE IS NULL AND DEL_NBR = 0 AND POS_TYP IN ('SNM','RAM','CMA','NAM','COM','DPG','SCM') AND PRSN_NBR = ".$personNumber."
					GROUP BY PRSN_NBR";
					genCombo($query, "PRSN_NBR","NAME",$_GET['PRSN_NBR']);
				}
			?>
		</select>
		
        <select name="PAY_CONFIG_NBR" id="PAY_CONFIG_NBR" class="chosen-select" style="margin-left:20px;width:200px;">
            <?php 
				$query = "SELECT PAY_CONFIG_NBR,PAY_BEG_DTE FROM PAY.PAY_CONFIG_DTE WHERE PAY_BEG_DTE <= CURRENT_DATE AND PAY_END_DTE >= CURRENT_DATE";
				$result = mysql_query($query);
				$rowDte = mysql_fetch_array($result);
				$PayConfigNbr = $rowDte['PAY_CONFIG_NBR'];
				if ($_GET['PAY_CONFIG_NBR'] == ""){
					$_GET['PAY_CONFIG_NBR'] = $PayConfigNbr;
				}
				
				$query = "SELECT 
					PAY_CONFIG_NBR, PAY_BEG_DTE, PAY_END_DTE, GROUP_CONCAT(PAY_BEG_DTE,' - ',PAY_END_DTE) AS PERIOD_DTE 
				FROM PAY.PAY_CONFIG_DTE
				WHERE  MONTH(PAY_END_DTE) <= MONTH(CURRENT_DATE) AND YEAR(PAY_END_DTE) = YEAR(CURRENT_DATE)
				GROUP BY PAY_CONFIG_NBR";
				genCombo($query, "PAY_CONFIG_NBR","PERIOD_DTE",$_GET['PAY_CONFIG_NBR'], "Kosong");
			?>
        </select>
		<span class="fa fa-calendar toolbar fa-lg" style="cursor:pointer;padding-left:10px;" onclick="location.href='?FLR_OPT=MKG_BNS&PRSN_NBR='+document.getElementById('PRSN_NBR').value+'&PAY_CONFIG_NBR='+document.getElementById('PAY_CONFIG_NBR').value"></span>
    </div>
	<?php if($_GET['VIEW'] == ""){ ?>
	<table id="mainTable" class="tablesorter searchTable" style="width:600px;">
		<thead>
			<tr >
				<th style="width:5%;">No</th>
				<th style="width:45%">Perusahaan</th>
				<th style="width:40%">Total</th>
				<th style="width:40%">Permbayaran</th>
				<th style="width:40%">Sisa</th>
				<th style="width:40%">Bonus</th>
			</tr>
        </thead>
        <tbody>
        <?php
		if($security > 1 && !in_array($posTyp,array('COM','MGR','SCM'))){
			$_GET['PRSN_NBR'] = $personNumber;
		}else{
			$_GET['PRSN_NBR'] = $selectPerson;
		}
		$_GET['GROUP'] = 'BUY_CO_NBR';
		
		try {
			ob_start();
			include __DIR__ . DIRECTORY_SEPARATOR . "ajax/marketing-bonus.php";

			$results = json_decode(ob_get_clean());
		} catch (\Exception $ex) {
			ob_end_clean();
		}
		//echo "<pre>";
		//print_r($query);
		
		if (count($results->data) == 0) {
			echo "<div class='searchStatus'>Data not found</div>";
			exit;
		}
		foreach ($results->data as $data) {
		?>
		<tr style ="cursor: pointer;" onclick="location.href='?VIEW=DETAIL&FLR_OPT=MKG_BNS&BUY_CO_NBR=<?php echo $data->BUY_CO_NBR;?>&PRSN_NBR=<?php echo $selectPerson;?>&PAY_CONFIG_NBR=<?php echo $configNbr;?>';">
			<td align="center"><?php echo $data->BUY_CO_NBR;?></td>
			<td style ="white-space: nowrap;"><?php echo $data->BUY_CO_NAME;?></td>
			<td align="right"><?php echo number_format($data->TOT_AMT,0,'.',',');?></td>
			<td align="right"><?php echo number_format($data->TND_AMT_APV,0,'.',',');?></td>
			<td align="right"><?php echo number_format($data->TOT_REM,0,'.',',');?></td>
			<td align="right"><?php echo number_format($data->BNS_AMT_APV,0,'.',',');?></td>
		</tr>
		<?php } ?>	
        </tbody>
		<tfoot>
			<tr style="border-top:1px solid #cacbcf;">
				<td class="std" style="font-weight:bold;" colspan="2">Total:</td>
				<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->TOT_AMT, 0, ',', '.'); ?></td>
				<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->TND_AMT_APV, 0, ',', '.'); ?></td>
				<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->TOT_REM, 0, ',', '.'); ?></td>
				<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->BNS_AMT_APV, 0, ',', '.'); ?></td>
			</tr>
		</tfoot>
	</table>
	<?php }else{ ?>
		<div class='listable' style="padding-bottom: 4px;cursor: pointer;"><span style="cursor: pointer;" class='fa fa-angle-double-left listable' onclick="location.href='?FLR_OPT=MKG_BNS&PRSN_NBR=<?php echo $selectPerson;?>&PAY_CONFIG_NBR=<?php echo $configNbr;?>';"></span></div><span style="cursor: pointer;" onclick="location.href='?FLR_OPT=MKG_BNS&PRSN_NBR=<?php echo $selectPerson;?>&PAY_CONFIG_NBR=<?php echo $configNbr;?>';">&nbsp;Back</span>
	<form enctype="multipart/form-data" action="#" method="post">
	<input type="hidden" name="PRSN_NBR" id="PRSN_NBR" value="<?php echo $selectPerson;?>" >
	<table id="mainTable" class="tablesorter searchTable" style="width:600px;">
		<thead>
			<tr >
				<th style="width:5%;">No</th>
				<th style="width:45%">Judul</th>
				<th style="width:45%">Perusahaan</th>
				<th style="width:45%">Tgl Pesan</th>
				<th style="width:45%">Status</th>
				<th style="width:45%">Tgl Billing</th>
				<th style="width:45%">Jatuh Tempo</th>
				<th style="width:45%">Tgl Bayar</th>
				<th style="width:40%">Total</th>
				<th style="width:40%">Sisa</th>
				<th style="width:40%">Pengurangan</th>
				<th style="width:40%">Bonus</th>
				<th style="width:40%">Status</th>
			</tr>
        </thead>
        <tbody>
			<?php
			unset($_GET['GROUP']);
			
			$_GET['GROUP'] = 'PYMT_NBR';

			try {
				ob_start();
				include __DIR__ . DIRECTORY_SEPARATOR . "ajax/marketing-bonus.php";

				$results = json_decode(ob_get_clean());
			} catch (\Exception $ex) {
				ob_end_clean();
			}

			if (count($results->data) == 0) {
				echo "<div class='searchStatus'>Data not found</div>";
				exit;
			}
			//echo "<pre>";
			//print_r($query);
			foreach ($results->data as $result) {
			?>
			<tr>
				<td align="center" ><?php echo $result->PYMT_NBR;?> - <?php echo $result->ORD_NBR;?></td>
				<td style ="white-space: nowrap;"><?php echo $result->ORD_TTL;?></td>
				<td style ="white-space: nowrap;"><?php echo $result->BUY_CO_NAME;?></td>
				<td style ="white-space: nowrap;"><?php echo $result->ORD_DTE;?></td>
				<td style ="white-space: nowrap;"><?php echo $result->ORD_STT_DESC;?></td>
				<td style ="white-space: nowrap;"><?php echo $result->BILL_DTE;?></td>
				<td style ="white-space: nowrap;"><?php echo $result->CRT_DTE;?></td>
				<td style ="white-space: nowrap;"><?php echo $result->DUE_DTE;?></td>
				<td align="right"><?php echo number_format($result->TND_AMT,0,',',',');?></td>
				<td align="right"><?php echo number_format($result->TOT_REM,0,'.',',');?></td>
				<td align="right"><?php echo $result->DEPN_PCT;?></td>
				<td align="right"><?php echo number_format($result->BNS_AMT,0,'.',',');?></td>
				<td align="center">
					<?php
					if($result->ACT_F==0){ $cekF = "checked"; } else { $cekF = ""; }
					?>
					<input  type="checkbox" <?php echo $cekF; ?> id="ACT_F_<?php echo $result->PYMT_NBR;?>" name="ACT_F[]" value="<?php echo $result->PYMT_NBR;?>" onchange="toggleCheckbox(this)">
				</td>
			</tr>
			<?php }?>		
        </tbody>
		<tfoot>
			<tr style="border-top:1px solid #cacbcf;">
				<td class="std" style="font-weight:bold;" colspan="8">Total:</td>
				<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->TOT_AMT, 0, ',', '.'); ?></td>
				<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->TOT_REM, 0, ',', '.'); ?></td>
				<td class="std" style="text-align:right;font-weight:bold;">-</td>
				<td class="std" style="text-align:right;font-weight:bold;"><?php echo number_format($results->total->BNS_AMT, 0, ',', '.'); ?></td>
				<td class="std" style="text-align:center;">
					<input class="process" type="submit" value="Simpan"/>
				</td>
			</tr>
		</tfoot>
	</table>
	</form>
	<?php } ?>
	
	<table>
		<tr>
			<td class="std" colspan="2" style="font-weight: 700;">Total Pembayaran</td>
			<td class="std" style="font-weight: 700;text-align:right"><?php echo number_format($results->total->TND_AMT_APV,0,'.',',');?></td>
		</tr>
		<tr>
			<td class="std" colspan="2" style="font-weight: 700;">Total Bonus</td>
			<td class="std" style="font-weight: 700;text-align:right"><?php echo number_format($results->total->BNS_AMT_APV,0,'.',',');?></td>
		</tr>
		<tr>
			<td class="std" colspan="2" style="font-weight: 700;border-top:1px solid grey">Batas Pembayaran</td>
			<td class="std" style="font-weight: 700;text-align:right;border-top:1px solid grey"><?php echo number_format(25000000,0,'.',',');?></td>
		</tr>
		<tr>
			<td class="std" colspan="2" style="font-weight: 700;">Total Bonus</td>
			<?php
			$perhitungan = floor($results->total->TND_AMT_APV/25000000);
			?>
			<td class="std" style="font-weight: 700;text-align:right"><?php echo number_format($perhitungan*250000,0,'.',','); ?></td>
		</tr>
	</table>
	
	
	<h3>
		Komisi Supervisor
	</h3>
	<table id="mainTable" class="tablesorter searchTable" style="width:600px;">
		<thead>
			<tr>
				<th style="width: 5%">No</th>
				<th>Nama</th>
				<th>Bonus</th>
				<th>Komisi</th>
			</tr>
		</thead>
		<tbody>
			<?php
			unset($_GET['GROUP']);
			
			$_GET['PRSN_NBR']	= '';
			$_GET['GROUP'] 		= 'ACCT_EXEC_NBR';

			try {
				ob_start();
				include __DIR__ . DIRECTORY_SEPARATOR . "ajax/marketing-bonus.php";

				$results = json_decode(ob_get_clean());
			} catch (\Exception $ex) {
				ob_end_clean();
			}
			//echo "<pre>";
			//print_r($results);

			if (count($results->data) == 0) {
				echo "<div class='searchStatus'>Data not found</div>";
				exit;
			}
			foreach ($results->data as $result) {
			?>
			<tr <?php echo $alt;?>>
				<td style="text-align:center;" ><?php echo $result->ACCT_EXEC_NBR; ?></td>
				<td><?php echo $result->ACCT_EXEC_NAME; ?></td>
				<td style="text-align:right"><?php echo number_format($result->BNS_AMT, 0, ',', '.');?></td>
				<td style="text-align:right"><?php echo number_format($result->BNS_SPV_AMT, 0, ',', '.');?></td>
			</tr>
			<?php } ?>
			<tr>
				<td class="std" style="font-weight: 700;" colspan="3">Total Komisi</td>
				<td class="std" style="font-weight: 700;text-align:right"><?php echo number_format($results->total->BNS_SPV_AMT,0,'.',',');?></td>
			</tr>
		</tbody>
	</table>
	
	
	<?php } ?>


<!-- Tampilan untuk tebak Marketing Performance Reward -->
<?php if($filterOption != "" && $filterOption == "FLR_MPR" ){ 

	//Champion Campus
	$query = "SELECT 
				M_AVG_ALL, S_AVG_ALL, RWD.PAY_CONFIG_NBR 
			FROM $CDW.PAY_RWD RWD 
			LEFT JOIN $PAY.PAY_CONFIG_DTE PAYC ON PAYC.PAY_CONFIG_NBR = RWD.PAY_CONFIG_NBR 
			WHERE RWD.OWN_CO_NBR = ".$CoNbrDef."  
				AND PAY_BEG_DTE BETWEEN (DATE_FORMAT(CURRENT_DATE, '%Y-%m-1') - INTERVAL 14 MONTH) AND CURRENT_DATE 
				GROUP BY DATE_FORMAT(PAY_BEG_DTE,'%Y'),DATE_FORMAT(PAY_BEG_DTE,'%c')*1,DATE_FORMAT(PAY_BEG_DTE,'%b')";
	//echo "<pre>".$query."<br><br>";
	$result = mysql_query($query, $cloud);

	$mAvgAllArr = array();
	$sAvgAllArr = array();

	$mAvgAllArr = array(0);
	$sAvgAllArr = array(0);
	$numAll     = mysql_num_rows($result);
	while ($rowOld = mysql_fetch_array($result)) {
		$mAvgAllArr[] = $rowOld['M_AVG_ALL'];
		$sAvgAllArr[] = $rowOld['S_AVG_ALL'];
	}

	if ($security < 1 || $address <= 1){
		if ($FLR_MPR_PPL!=''){
			$personNumber = $FLR_MPR_PPL;
		}	
	}

	// Perhitungan untuk mendapatkan Net Monthly Reward (chart1)
	$query = "SELECT RWD.M_Q, 
					 RWD.S_Q,
					 RWD.M_AVG,
					 RWD.S_AVG,
					 DATE_FORMAT(PAYC.PAY_BEG_DTE,'%b') AS RWD_MONTH_NM,
					 DATE_FORMAT(PAYC.PAY_BEG_DTE,'%Y') AS RWD_YEAR 
				FROM $CDW.PAY_RWD RWD 
				LEFT JOIN $PAY.PAY_CONFIG_DTE PAYC ON PAYC.PAY_CONFIG_NBR = RWD.PAY_CONFIG_NBR
				WHERE RWD.PRSN_NBR = ".$personNumber." AND RWD.OWN_CO_NBR = ".$CoNbrDef." 
				AND PAY_BEG_DTE BETWEEN (DATE_FORMAT(CURRENT_DATE, '%Y-%m-1') - INTERVAL 14 MONTH) AND CURRENT_DATE
				GROUP BY DATE_FORMAT(PAY_BEG_DTE,'%Y'),DATE_FORMAT(PAY_BEG_DTE,'%c')*1,DATE_FORMAT(PAY_BEG_DTE,'%b')";
	//echo "<pre>".$query."<br><br>";
	$result= mysql_query($query, $cloud);
	
	$mQArray = array();
	$sQArray  = array();
	$dateArray= array();
	$mAvgArray= array();
	$sAvgArray= array();
	$numArr= mysql_num_rows($result);
	if ($numAll == $numArr){$i=0;}else{$i=$numAll-$numArr;};
	while ($row = mysql_fetch_array($result)) {
		if($mAvgAllArr[$i] != ''){
			if ($row['M_Q']!=''){
				$mQArray[]  = ($row['M_AVG']/$mAvgAllArr[$i])*$row['M_Q'];
			}
			if ($row['S_Q']!=''){
				$sQArray[]  = ($row['S_AVG']/$sAvgAllArr[$i])*$row['S_Q'];
			}
		}else{
			$mQArray[] = 0;
			$sQArray[] = 0;
		}
		
		$mAvgArray[]= $row['M_AVG'];
		$sAvgArray[]= $row['S_AVG'];
		$dateArray[]= "'".$row['RWD_MONTH_NM']." ".$row['RWD_YEAR']."'";
		$i++;
	}

	$mQ 		= "[".implode(",", $mQArray)."]";
	$sQ 		= "[".implode(",", $sQArray)."]";
	$mAvg 		= "[".implode(",", $mAvgArray)."]";
	$sAvg 		= "[".implode(",", $sAvgArray)."]";
	$monthRwd 	= "[".implode(",", $dateArray)."]";
	
	
	//Champion Printing
	$query = "SELECT 
				M_AVG_ALL, S_AVG_ALL, RWD.PAY_CONFIG_NBR 
			FROM $CDW.PAY_RWD RWD 
			LEFT JOIN $PAY.PAY_CONFIG_DTE PAYC ON PAYC.PAY_CONFIG_NBR = RWD.PAY_CONFIG_NBR 
			WHERE RWD.OWN_CO_NBR != ".$CoNbrDef."  
				AND PAY_BEG_DTE BETWEEN (DATE_FORMAT(CURRENT_DATE, '%Y-%m-1') - INTERVAL 14 MONTH) AND CURRENT_DATE 
				GROUP BY DATE_FORMAT(PAY_BEG_DTE,'%Y'),DATE_FORMAT(PAY_BEG_DTE,'%c')*1,DATE_FORMAT(PAY_BEG_DTE,'%b')";
	//echo "<pre>".$query."<br><br>";
	$result = mysql_query($query, $cloud);

	$mAvgAllArrPrint = array();
	$sAvgAllArrPrint = array();

	$mAvgAllArrPrint = array(0);
	$sAvgAllArrPrint = array(0);
	$numAll     = mysql_num_rows($result);
	while ($rowOld = mysql_fetch_array($result)) {
		$mAvgAllArrPrint[] = $rowOld['M_AVG_ALL'];
		$sAvgAllArrPrint[] = $rowOld['S_AVG_ALL'];
	}

	if ($security < 1 || $address <= 1 || $_GET['TYP']=="ACCOUNT" || $_GET['FLR_OPT']!="OUT_CMSN"){
		if ($FLR_MPR_PPL!=''){
			$personNumber = $FLR_MPR_PPL;
		}	
	}

	// Perhitungan untuk mendapatkan Net Monthly Reward (chart1)
	$query = "SELECT RWD.M_Q, 
					 RWD.S_Q,
					 RWD.M_AVG,
					 RWD.S_AVG,
					 DATE_FORMAT(PAYC.PAY_BEG_DTE,'%b') AS RWD_MONTH_NM,
					 DATE_FORMAT(PAYC.PAY_BEG_DTE,'%Y') AS RWD_YEAR 
				FROM $CDW.PAY_RWD RWD 
				LEFT JOIN $PAY.PAY_CONFIG_DTE PAYC ON PAYC.PAY_CONFIG_NBR = RWD.PAY_CONFIG_NBR
				WHERE RWD.PRSN_NBR = ".$personNumber." AND RWD.OWN_CO_NBR != ".$CoNbrDef." 
				AND PAY_BEG_DTE BETWEEN (DATE_FORMAT(CURRENT_DATE, '%Y-%m-1') - INTERVAL 14 MONTH) AND CURRENT_DATE
				GROUP BY DATE_FORMAT(PAY_BEG_DTE,'%Y'),DATE_FORMAT(PAY_BEG_DTE,'%c')*1,DATE_FORMAT(PAY_BEG_DTE,'%b')";
	//echo "<pre>".$query."<br><br>";
	$result= mysql_query($query, $cloud);
	
	$mQArrayPrint   = array();
	$sQArrayPrint   = array();
	$dateArrayPrint = array();
	$mAvgArrayPrint = array();
	$sAvgArrayPrint = array();
	$numArr= mysql_num_rows($result);
	if ($numAll == $numArr){$i=0;}else{$i=$numAll-$numArr;};
	while ($row = mysql_fetch_array($result)) {
		if($mAvgAllArrPrint[$i] != ''){
			if ($row['M_Q']!=''){
				$mQArrayPrint[]  = ($row['M_AVG']/$mAvgAllArrPrint[$i])*$row['M_Q'];
			}
			if ($row['S_Q']!=''){
				$sQArrayPrint[]  = ($row['S_AVG']/$sAvgAllArrPrint[$i])*$row['S_Q'];
			}
		}else{
			$mQArrayPrint[] = 0;
			$sQArrayPrint[] = 0;
		}
		
		$mAvgArrayPrint[]= $row['M_AVG'];
		$sAvgArrayPrint[]= $row['S_AVG'];
		$dateArrayPrint[]= "'".$row['RWD_MONTH_NM']." ".$row['RWD_YEAR']."'";
		$i++;
	}

	$mQPrint 		= "[".implode(",", $mQArrayPrint)."]";
	$sQPrint 		= "[".implode(",", $sQArrayPrint)."]";
	$mAvgPrint 		= "[".implode(",", $mAvgArrayPrint)."]";
	$sAvgPrint 		= "[".implode(",", $sAvgArrayPrint)."]";
	$monthRwdPrint 	= "[".implode(",", $dateArrayPrint)."]";


	//Get parameter 
	$query = "SELECT RWD_M_BASE_Q, RWD_S_BASE_Q FROM NST.PARAM_LOC";
	$result= mysql_query($query, $local);
	$rowP  = mysql_fetch_array($result);

	$rwdMBaseQ = $rowP['RWD_M_BASE_Q'];
	$rwdSBaseQ = $rowP['RWD_S_BASE_Q'];

?>
<script type="text/javascript">jQuery.noConflict();</script>
<script type="text/javascript" src="framework/charts3/js/highcharts.js"></script>
<script type="text/javascript" src="framework/charts3/js/highcharts-more.js"></script>
<script type="text/javascript">
	var chart1;
	var chart2;
	var chart3;
	var chart4;

	jQuery(document).ready(function() {
		Highcharts.setOptions({
			colors: [{linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#54b6ff'],[1, '#1169d8']]},
                     {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#4edd19'],[1, '#009c21']]},
                     {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#fed75c'],[1, '#f9cb1d']]},
                     {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#fd630a'],[1, '#ea1212']]},
                     {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#ab2e96'],[1, '#500a85']]},
                     {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#ed8f1c'],[1, '#a63d00']]},
                     {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#0ace80'],[1, '#008391']]},
                     {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#d2d2d2'],[1, '#b6b6b6']]},
                     {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#747474'],[1, '#242424']]},
                     {linearGradient: {x1: 0, y1: 0, x2: 0, y2: 1}, stops: [[0, '#7d7d7d'],[1, '#303030']]},
                      '#2c83de','#32c028','#F9CB1D','#ea1212','#822694','#cd7115','#08ad90','#b6b6b6','#242424','#575757'],
            chart: {
                style: {
                    fontFamily: 'San Francisco Display'
                }
            },
            credits: {
                enabled: false
            }
        });

		// Chart untuk Net Monthly Reward
        chart1 = new Highcharts.Chart({
				chart: {
					renderTo: 'monthlyReward',
					defaultSeriesType: 'column',
				},
				title: {
					text: 'Monthly Marketing Reward',
				},
				subtitle: {
					text: 'By Equipment',
				},
				xAxis: {
					categories: <?php echo $monthRwd; ?>
				},
				yAxis: [{
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						text: 'Meter / Lembar',
						style: {
							color: '#666666'
						}
					},
					plotBands: [{
			            from: 0,
			            to: <?php echo $rwdMBaseQ; ?>,
			            color: 'rgba(200, 200, 200, .2)',
			            label: { text: 'Critical Meters',
				            style: {
			                  color: '#909090'
   				            }
   				        }
   				    },{
			            from: 0,
			            to: <?php echo $rwdSBaseQ; ?>,
			            color: 'rgba(122, 186, 218, .2)',
			            label: { text: 'Critical Sheets',
				            style: {
			                  color: '#909090'
   				            }
   				        }
			        }]
				}],
				tooltip: {
					formatter: function() {
						return '<b>'+ this.series.name +'</b><br/>'+
						this.x +': '+ Highcharts.numberFormat(this.y, 0);
					}
				},
				legend: {
					layout: 'vertical',
					align: 'left',
					verticalAlign: 'top',
					floating: true,
					x: 70,
					y: 25,
					backgroundColor: '#FFFFFF'
				},
				plotOptions: {
					series: {
						pointPadding: 0.06,
						borderWidth: 0,
						groupPadding: 0.12,
						shadow: false
					}
				},
				series: [{
					name: 'Meter',
					data: <?php echo $mQ; ?>
				},{
					name: 'Lembar',
					data: <?php echo $sQ; ?>
				}]
			});

        // Chart untuk Price index Reward
        chart2 = new Highcharts.Chart({
				chart: {
					renderTo: 'monthlyPrcRwd',
					defaultSeriesType: 'spline',
				},
				title: {
					text: 'Monthly Reward Price Index ',
				},
				subtitle: {
					text: 'By Type',
				},
				xAxis: {
					categories: <?php echo $monthRwd; ?>
				},
				yAxis: [{
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						text: 'Meter / Lembar ',
						style: {
							color: '#666666'
						}
					},
				},{
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						text: 'A3+',
						style: {
							color: '#666666'
						}
					},
					opposite: true
				}],
				tooltip: {
					formatter: function() {
						return '<b>'+ this.series.name +'</b><br/>'+
						this.x +': '+ Highcharts.numberFormat(this.y, 0);
					}
				},
				legend: {
					layout: 'vertical',
					align: 'left',
					verticalAlign: 'top',
					floating: true,
					x: 70,
					y: 25,
					backgroundColor: '#FFFFFF'
				},
				plotOptions: {
					series: {
						shadow: false,
						marker: {
                    		enabled: true
                		},
					}
				},
				series: [{
					name: 'Meter',
					data: <?php echo $mAvg; ?>,
					color: Highcharts.getOptions().colors[10],
				},{
					name: 'Lembar',
					data: <?php echo $sAvg; ?>,
					color: Highcharts.getOptions().colors[11],
				}]
			});
			
			// Chart untuk Net Monthly Reward
        chart3 = new Highcharts.Chart({
				chart: {
					renderTo: 'monthlyRewardPrint',
					defaultSeriesType: 'column',
				},
				title: {
					text: 'Monthly Marketing Reward',
				},
				subtitle: {
					text: 'By Equipment',
				},
				xAxis: {
					categories: <?php echo $monthRwdPrint; ?>
				},
				yAxis: [{
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						text: 'Meter / Lembar',
						style: {
							color: '#666666'
						}
					},
					plotBands: [{
			            from: 0,
			            to: <?php echo $rwdMBaseQ; ?>,
			            color: 'rgba(200, 200, 200, .2)',
			            label: { text: 'Critical Meters',
				            style: {
			                  color: '#909090'
   				            }
   				        }
   				    },{
			            from: 0,
			            to: <?php echo $rwdSBaseQ; ?>,
			            color: 'rgba(122, 186, 218, .2)',
			            label: { text: 'Critical Sheets',
				            style: {
			                  color: '#909090'
   				            }
   				        }
			        }]
				}],
				tooltip: {
					formatter: function() {
						return '<b>'+ this.series.name +'</b><br/>'+
						this.x +': '+ Highcharts.numberFormat(this.y, 0);
					}
				},
				legend: {
					layout: 'vertical',
					align: 'left',
					verticalAlign: 'top',
					floating: true,
					x: 70,
					y: 25,
					backgroundColor: '#FFFFFF'
				},
				plotOptions: {
					series: {
						pointPadding: 0.06,
						borderWidth: 0,
						groupPadding: 0.12,
						shadow: false
					}
				},
				series: [{
					name: 'Meter',
					data: <?php echo $mQPrint; ?>
				},{
					name: 'Lembar',
					data: <?php echo $sQPrint; ?>
				}]
			});

        // Chart untuk Price index Reward
        chart4 = new Highcharts.Chart({
				chart: {
					renderTo: 'monthlyPrcRwdPrint',
					defaultSeriesType: 'spline',
				},
				title: {
					text: 'Monthly Reward Price Index ',
				},
				subtitle: {
					text: 'By Type',
				},
				xAxis: {
					categories: <?php echo $monthRwdPrint; ?>
				},
				yAxis: [{
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						text: 'Meter / Lembar ',
						style: {
							color: '#666666'
						}
					},
				},{
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						text: 'A3+',
						style: {
							color: '#666666'
						}
					},
					opposite: true
				}],
				tooltip: {
					formatter: function() {
						return '<b>'+ this.series.name +'</b><br/>'+
						this.x +': '+ Highcharts.numberFormat(this.y, 0);
					}
				},
				legend: {
					layout: 'vertical',
					align: 'left',
					verticalAlign: 'top',
					floating: true,
					x: 70,
					y: 25,
					backgroundColor: '#FFFFFF'
				},
				plotOptions: {
					series: {
						shadow: false,
						marker: {
                    		enabled: true
                		},
					}
				},
				series: [{
					name: 'Meter',
					data: <?php echo $mAvgPrint; ?>,
					color: Highcharts.getOptions().colors[10],
				},{
					name: 'Lembar',
					data: <?php echo $sAvgPrint; ?>,
					color: Highcharts.getOptions().colors[11],
				}]
			});
			
		// Chart untuk Monthly Perfomance
        chart5 = new Highcharts.Chart({
				chart: {
					renderTo: 'monthlyPerfomance',
					defaultSeriesType: 'column',
				},
				title: {
					text: 'Monthly Marketing Perfomance',
				},
				subtitle: {
					text: 'By Equipment',
				},
				xAxis: {
					categories: <?php echo $monthName; ?>
				},
				yAxis: [{
					labels: {
						formatter: function() {
							return Highcharts.numberFormat(this.value, 0);
						},
						style: {
							color: '#666666'
						}
					},
					title: {
						text: 'Meter / Lembar',
						style: {
							color: '#666666'
						}
					},
					plotBands: [{
			            from: 0,
			            to: <?php echo $monthAmount; ?>,
			            color: 'rgba(200, 200, 200, .2)',
			            label: { text: 'Critical Meters',
				            style: {
			                  color: '#909090'
   				            }
   				        }
   				    }]
				}],
				tooltip: {
					formatter: function() {
						return '<b>'+ this.series.name +'</b><br/>'+
						this.x +': '+ Highcharts.numberFormat(this.y, 0);
					}
				},
				legend: {
					layout: 'vertical',
					align: 'left',
					verticalAlign: 'top',
					floating: true,
					x: 70,
					y: 25,
					backgroundColor: '#FFFFFF'
				},
				plotOptions: {
					series: {
						pointPadding: 0.06,
						borderWidth: 0,
						groupPadding: 0.12,
						shadow: false
					}
				},
				series: [{
					name: 'Meter',
					data: <?php echo $monthAmount; ?>
				}]
			});
	});
</script>
<div style="max-width:1043px;margin:0 auto;padding:0;margin-top:30px;text-align:center;<?php if($Typ=="ACCOUNT"){ echo "display:none;";} ?>" >
	<?php
	if($CoNbrDef == '1002') {
		echo "<h3 style='text-align:center;' >Champion Campus</h3>";
	} else {
		echo "<h3 style='text-align:center;' >Champion Printing</h3>";
	}
	?>
	<span id="monthlyReward" style="display:inline-block;width: 500px; height: 300px; margin:0 10px 0;padding:0;"></span>
	<span id="monthlyPrcRwd" style="display:inline-block;width: 500px; height: 300px; margin:0 10px 0;padding:0;"></span>
</div>
<div style="max-width:1043px;margin:0 auto;padding:0;margin-top:30px;text-align:center;<?php if($Typ=="ACCOUNT"){ echo "display:none;";} ?>" >
	<?php
	if($CoNbrDef != '1002') {
		echo "<h3 style='text-align:center;' >Champion Campus</h3>";
	} else {
		echo "<h3 style='text-align:center;' >Champion Printing</h3>";
	}
	?>
	<span id="monthlyRewardPrint" style="display:inline-block;width: 500px; height: 300px; margin:0 10px 0;padding:0;"></span>
	<span id="monthlyPrcRwdPrint" style="display:inline-block;width: 500px; height: 300px; margin:0 10px 0;padding:0;"></span>
</div>

<div id="tableDetail"></div>

<script type="text/javascript">
	jQuery(document).ready(function () {
		var url = "marketing-performance-table.php?TYP=<?php echo $Typ; ?>&PRSN_NBR=<?php echo $personNumber; ?>&FLR_OPT=<?php echo $filterOption;?>";
		jQuery("#tableDetail").load(url);


	});

	function detailData(buyCoNbr,PayConfigNbr,PrsnNbr,Typ,OwnCoDesc){
			
		var url          = "marketing-performance-table-det.php?TYP="+Typ+"&BUY_CO_NBR="+buyCoNbr+"&PAY_CONFIG_NBR="+PayConfigNbr+"&PRSN_NBR="+PrsnNbr+"&OWN_CO_DESC="+OwnCoDesc;

		jQuery("#tableDetail").load(url);
	}

	function BackData(PayConfigNbr,PrsnNbr,Typ){
		var url = "marketing-performance-table.php?TYP="+Typ+"&PRSN_NBR="+PrsnNbr+"&PAY_CONFIG_NBR="+PayConfigNbr;
		jQuery("#tableDetail").load(url);
	}
</script>
<?php } ?>

</div>
<script>
	jQuery(document).ready(function () {
		jQuery("#mainTable").tablesorter({widgets: ["zebra"]});
		jQuery("#tableCuti").tablesorter({widgets: ["zebra"]});
		jQuery("#tableHeld").tablesorter({widgets: ["zebra"]});
		jQuery("#tableBon").tablesorter({widgets: ["zebra"]});
		jQuery("#tabelcnrtc").tablesorter({widgets: ["zebra"]});
	});
</script>

</body>
</html>