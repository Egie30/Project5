
<script>parent.Pace.restart();</script>

<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />

<script type="text/javascript" src="framework/validation/mootools-1.2.3.js"></script>
<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>

<script type="text/javascript" src="framework/functions/default.js"></script>

<script type="text/javascript">jQuery.noConflict();</script>
<?php
	include "framework/functions/dotmatrix.php";
	include "framework/database/connect.php";

	$CoNbr 			= $_GET['CO_NBR'];
	$PayrollDte 	= $_GET['PAYROLL_DTE'];

	//echo $PayrollDte;
	
	$i = 0;

	if($CoNbr!=''){
		$query_nst 	= "SELECT CO_NBR_CMPST FROM NST.PARAM_PAYROLL WHERE CO_NBR =".$CoNbr;
		$result_nst = mysql_query($query_nst);
		$row_nst 	= mysql_fetch_array($result_nst);
	}
	
	$query_pymt		= "SELECT MAX(PYMT_DTE) AS PYMT_DTE FROM PAY.PAYROLL";
	$result_pymt	= mysql_query($query_pymt);
	$row_pymt 		= mysql_fetch_array($result_pymt);
	
	$PymtDte		= $row_pymt['PYMT_DTE'];
	//echo $PymtDte;
	
	$query_tot		= "SELECT 
							COUNT(PPL.PRSN_NBR) AS CNT, 
							SUM(PAY.PAY_AMT) AS PAY_TOT,
							PPL.CO_NBR_PAY
						FROM PAY.PAYROLL PAY 
						INNER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR=PAY.PRSN_NBR 
						WHERE PPL.CO_NBR_PAY IN (SELECT CO_NBR FROM NST.PARAM_COMPANY WHERE CO_CD_NBR IS NOT NULL)";
	if($CoNbr!=''){$query_tot.= " AND PPL.CO_NBR IN (".$row_nst['CO_NBR_CMPST'].") ";}
	$query_tot.= " AND PPL.TERM_DTE IS NULL 
							AND PAY.DEL_NBR=0 
							AND PAY.PYMT_DTE='".$PymtDte."' 
							AND MONTH(PAY.PYMT_DTE)=MONTH(CURRENT_DATE) 
							AND PPL.BNK_ACCT_NBR IS NOT NULL 
							AND PPL.BNK_CO_NBR=859 
							AND PPL.DEL_NBR = 0
						GROUP BY PPL.CO_NBR_PAY";
	//echo '<pre>'.$query_tot;
	$result_tot	= mysql_query($query_tot);

	$CoCdAcct 	= "0456";
	$periode 	= 1;
	while ($row=mysql_fetch_array($result_tot)) {
		$queryDet 	= "SELECT PC.*, COM.BNK_ACCT_NBR, COM.NAME FROM NST.PARAM_COMPANY PC 
						LEFT JOIN CMP.COMPANY COM ON PC.CO_NBR=COM.CO_NBR
						WHERE PC.CO_NBR=".$row['CO_NBR_PAY'];
		$resultDet 	= mysql_query($queryDet);
		$rowDet 	= mysql_fetch_array($resultDet);

		$header="";
		$header="00000000000"; //1
		$header.=$CoCdAcct."0".$rowDet['CO_CD_NBR'].$rowDet['CO_CD_CHR']; //2
		$header.=date('d', strtotime($PayrollDte)); //3 tgl cair
		$header.="01"; //4
		$header.=$rowDet['BNK_ACCT_NBR']; //5
		$header.="00MF"; //6
		$header.=leadZero($row['CNT'],5); //7 jumlah karyawan
		$header.=leadZero($row['PAY_TOT'],14); //8 jumlah rupiah
		$header.=".00"; //9
		$header.=date('m', strtotime($PayrollDte)); //10 bulan cair
		$header.=date('Y', strtotime($PayrollDte)).chr(13).chr(10); //tahun cair
		?>
		<div style="padding-left: 10px; width: 530px;">
			<!--<div class="toolbar-only">
				<a href="payroll-prn-dig-bank-print.php?CO_NBR_PAY=<?php echo $row['CO_NBR_PAY']; ?>"><span class='fa fa-download toolbar toolbar-left' style="cursor:pointer" onclick="location.href="></span></a>
			</div>-->
			<h3><?php echo $rowDet['NAME']; ?></h3>
		</div>
		<?php
		echo "<pre style='font-size:9pt;letter-spacing:-1.25px;'>";
		echo $header;

		$queryList	= "SELECT 
						NAME,
						BNK_ACCT_NBR,
						PAY_AMT,
						PPL.PRSN_NBR,
						CO_NBR
				FROM PAY.PAYROLL PAY INNER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR=PAY.PRSN_NBR 
				WHERE CO_NBR_PAY = ".$row['CO_NBR_PAY']." ";
		if($CoNbr!=''){$queryList.= " AND PPL.CO_NBR IN (".$row_nst['CO_NBR_CMPST'].") ";}
		$queryList.= " AND TERM_DTE IS NULL 
					AND PAY.DEL_NBR=0 
					AND PYMT_DTE='".$PymtDte."' 
					AND MONTH(PYMT_DTE)=MONTH(CURRENT_DATE) 
					AND BNK_ACCT_NBR IS NOT NULL 
					AND BNK_CO_NBR=859 
					AND PPL.DEL_NBR = 0 
				ORDER BY CO_NBR, PPL.PRSN_NBR";
		$resultList	= mysql_query($queryList);

		$i=0;
		
		$string	= "";

		while($rowList = mysql_fetch_array($resultList))
		{	
			
			$i++;
			$string.="0".$rowList['BNK_ACCT_NBR'].leadZero($rowList['PAY_AMT'],13)."00".leadZero($rowList['PRSN_NBR'],10).followSpace(strtoupper($rowList['NAME']),30);
			
			if($rowList['CO_NBR']==271){
				$string.="CPRN";
			}elseif($rowList['CO_NBR']==997){
				$string.="PROL";
			}elseif($rowList['CO_NBR']==889){
				$string.="TJYN";
			}elseif($rowList['CO_NBR']==1002){
				$string.="CCPS";
			}elseif($rowList['CO_NBR']==1099){
				$string.="CGND";
			}elseif($rowList['CO_NBR']==2996){
				$string.="CVCP";
			}elseif($rowList['CO_NBR']==2997){
				$string.="CVCC";
			}elseif($rowList['CO_NBR']==3680){
				$string.="KOPR";
			}elseif($rowList['CO_NBR']==3110){
				$string.="CCRP";
			}

			$string.=chr(13).chr(10);				
		}
		echo $string;
		echo "</pre>";

		//payroll-prn-dig-bank-print.php
		?>
		<form id='mainForm' enctype="multipart/form-data" action="payroll-prn-dig-bank-print.php" method="post" style="width:700px">
			<input type="hidden" name="CO_NBR_PAY" id="CO_NBR_PAY" value="<?php echo $row['CO_NBR_PAY']; ?>"/>
			<input type="hidden" name="CO_NBR" id="CO_NBR" value="<?php echo $_GET['CO_NBR']; ?>"/>
			<input type="hidden" name="PAYROLL_DTE" id="PAYROLL_DTE" value="<?php echo $_GET['PAYROLL_DTE']; ?>"/>
			<input type="hidden" name="CO_CD_CHR" id="CO_CD_CHR" value="<?php echo $rowDet['CO_CD_CHR']; ?>"/>
			<input type="hidden" name="CNT" id="CNT" value="<?php echo $row['CNT']; ?>"/>
			<input type="hidden" name="PAY_TOT" id="PAY_TOT" value="<?php echo $row['PAY_TOT']; ?>"/>
			<input class="process" type="submit" value="Simpan"/>

			<?php 
			$folder = "payroll-bank/out/"; //Sesuaikan Folder nya
			if(!($buka_folder = opendir($folder))) die ("eRorr... Tidak bisa membuka Folder");

			$filecari 	= $fileProp = "Payroll_".$rowDet['CO_CD_CHR']."_".date('d', strtotime($PayrollDte))."_".date('mY', strtotime($PayrollDte))."_".$row['CNT']."_".$row['PAY_TOT']."_checksum.txt";

			$file_array = array();
			while($baca_folder = readdir($buka_folder))
			{
				$file_array[] = $baca_folder;
			}

			$jumlah_array = count($file_array);
			for($k=2; $k<$jumlah_array; $k++)
			{
				//echo $file_array[$k];
				if ($file_array[$k] == $filecari){
					echo "<br/><a href='payroll-prn-dig-bank-download.php?filename=$filecari'>$filecari</a><br/>";
				}
			}

			closedir($buka_folder);
			?>
		</form>
		<?php
	}

	if($_POST['CO_NBR_PAY']!=''){
		//Payroll_kodeunitrekening_tanggalcair_bulantahuncair_jumlahkaryawan_nominalrupiah
		$file = "payroll-bank/in/Payroll_".$_POST['CO_CD_CHR']."_".date('d', strtotime($PayrollDte))."_".date('mY', strtotime($PayrollDte))."_".$_POST['CNT']."_".$_POST['PAY_TOT'].".txt";
		$fileProp = "Payroll_".$_POST['CO_CD_CHR']."_".date('d', strtotime($PayrollDte))."_".date('mY', strtotime($PayrollDte))."_".$_POST['CNT']."_".$_POST['PAY_TOT'];

		$txt = fopen($file, "w") or die("Unable to open file!");
		fwrite($txt, $header);
		fwrite($txt, $string);
		fclose($txt);

		$fileProperties 	= "Checksum.properties";
		$txtProperties 		= fopen($fileProperties, "w") or die("Unable to open file!");
		$stringProperties 	= "Is_Logged     = true".chr(13).chr(10);
		$stringProperties 	.= "Log_File_Path = C:/xampp/htdocs/champion/checksum/log/checksum.log".chr(13).chr(10);
		$stringProperties 	.= "Payroll_File_Path          = C:/xampp/htdocs/champion/payroll-bank/in/".$fileProp.".txt".chr(13).chr(10);
		$stringProperties 	.= "Payroll_Checksum_File_Path = C:/xampp/htdocs/champion/payroll-bank/out/".$fileProp."_checksum.txt".chr(13).chr(10);

		fwrite($txtProperties, $stringProperties);
		fclose($txtProperties);

		sleep(2);

		exec('START C:\xampp\htdocs\champion\PayrollChecksum.cmd');
	}

	function getDatePay($start,$periode){
		$start   = date("Y-m-d",strtotime($start.'1 Day'));
		$day     = date("d", strtotime($start));

		//Holiday
		$query   = "SELECT HLDY_DTE FROM PAY.HOLIDAY WHERE MONTH(HLDY_DTE)='".date('m')."' AND YEAR(HLDY_DTE)='".date('Y')."'";
		$result  = mysql_query($query);
		while ($row= mysql_fetch_array($result)) {
			$holiday[]= $row['HLDY_DTE'];
		}

		//Cek Saturday
		$querySat = "SELECT WEEKDAY('".$start."') AS DAY";
		$resSat   = mysql_query($querySat);
		$rowSat   = mysql_fetch_array($resSat);
		$DayIndex = $rowSat['DAY'];
		
		if (in_array($start, $holiday)){
			return getDatePay($start, $periode);
		}else {
			if ($DayIndex == 5){
				return getDatePay($start, $periode);
			}else{
				if($periode<=1){
					$periode=$periode+1;
					return getDatePay($start, $periode);
				}else{
					return $day;
				}
			}
			
		}
	}

	//exit();
	//CO_NBR_PAY IS NULL
	$query_tot		= "SELECT 
							COUNT(PPL.PRSN_NBR) AS CNT, 
							SUM(PAY.PAY_AMT) AS PAY_TOT,
							PPL.CO_NBR_PAY
						FROM PAY.PAYROLL PAY 
						INNER JOIN CMP.PEOPLE PPL ON PPL.PRSN_NBR=PAY.PRSN_NBR 
						WHERE PPL.CO_NBR_PAY IS NULL";
	if($CoNbr!=''){$query_tot.= " AND PPL.CO_NBR IN (".$row_nst['CO_NBR_CMPST'].") ";}
	$query_tot.= "	AND PPL.TERM_DTE IS NULL 
							AND PAY.DEL_NBR=0 
							AND PAY.PYMT_DTE='".$PymtDte."' 
							AND MONTH(PAY.PYMT_DTE)=MONTH(CURRENT_DATE) 
							AND PPL.BNK_ACCT_NBR IS NOT NULL 
							AND PPL.BNK_CO_NBR=859 
							AND PPL.DEL_NBR = 0";
	//echo '<pre>'.$query_tot;
	$result_tot	= mysql_query($query_tot);
	$row_tot	= mysql_fetch_array($result_tot);
	
	$i			= $row_tot['CNT'];
	$payTot		= $row_tot['PAY_TOT'];

	if($payTot>0){
		$header="00000000000003700138GAJI";
		$header.=date('d', strtotime($PayrollDte)); //tanggal cair
		$header.="01037320777000MF";
		$header.=leadZero($i,5); //jumlah karyawan
		$header.=leadZero($payTot,14).".00"; //jumlah rupiah
		$header.=date('m', strtotime($PayrollDte)); //bulan cair
		$header.=date('Y', strtotime($PayrollDte)).chr(13).chr(10); //tahun cair
		
		?>
		<div style="padding-left: 10px; width: 530px;">
			<!--<div class="toolbar-only">
				<a href="payroll-prn-dig-bank-print.php?CO_NBR_PAY=ELSE"><span class='fa fa-download toolbar toolbar-left' style="cursor:pointer" onclick="location.href="></span></a>
			</div>-->
			<h3><?php echo 'Rekening 3'; ?></h3>
		</div>
		<?php	
			
		echo "<pre style='font-size:9pt;letter-spacing:-1.25px;'>";
		echo $header;
		
		$query	= "SELECT 
						NAME,
						BNK_ACCT_NBR,
						PAY_AMT,
						PPL.PRSN_NBR,
						CO_NBR
				FROM PAY.PAYROLL PAY INNER JOIN	CMP.PEOPLE PPL ON PPL.PRSN_NBR=PAY.PRSN_NBR 
				WHERE CO_NBR_PAY IS NULL ";
		if($CoNbr!=''){$query.= " AND PPL.CO_NBR IN (".$row_nst['CO_NBR_CMPST'].") ";}
		$query.= "
					AND TERM_DTE IS NULL 
					AND PAY.DEL_NBR=0 
					AND PYMT_DTE='".$PymtDte."' 
					AND MONTH(PYMT_DTE)=MONTH(CURRENT_DATE) 
					AND BNK_ACCT_NBR IS NOT NULL 
					AND BNK_CO_NBR=859 
					AND PPL.DEL_NBR = 0 
				ORDER BY CO_NBR, PPL.PRSN_NBR";
				
		//echo $query."<br /><br />";
		$result	= mysql_query($query);
		$i=0;
			
		$string	= "";
			
		while($row = mysql_fetch_array($result))
		{	
			$i++;
			$string.="0".$row['BNK_ACCT_NBR'].leadZero($row['PAY_AMT'],13)."00".leadZero($row['PRSN_NBR'],10).followSpace(strtoupper($row['NAME']),30);
		
			if($row['CO_NBR']==271){
				$string.="CPRN";
			}elseif($row['CO_NBR']==997){
				$string.="PROL";
			}elseif($row['CO_NBR']==889){
				$string.="TJYN";
			}elseif($row['CO_NBR']==1002){
				$string.="CCPS";
			}elseif($row['CO_NBR']==1099){
				$string.="CGND";
			}elseif($row['CO_NBR']==2996){
				$string.="CVCP";
			}elseif($row['CO_NBR']==2997){
				$string.="CVCC";
			}elseif($row['CO_NBR']==3680){
				$string.="KOPR";
			}elseif($row['CO_NBR']==3110){
				$string.="CCRP";
			}
			$string.=chr(13).chr(10);
		}
			
		echo $string;
				
		$string=str_replace($dspHeader,$prnHeader,$string);
		echo "</pre>";

		?>
		<form id='mainForm' enctype="multipart/form-data" action="payroll-prn-dig-bank-print.php" method="post" style="width:700px">
			<input type="hidden" name="CO_NBR_PAY" id="CO_NBR_PAY" value="ELSE"/>
			<input type="hidden" name="CO_NBR" id="CO_NBR" value="<?php echo $_GET['CO_NBR']; ?>"/>
			<input type="hidden" name="PAYROLL_DTE" id="PAYROLL_DTE" value="<?php echo $_GET['PAYROLL_DTE']; ?>"/>
		    <input class="process" type="submit" value="Simpan"/>

			<?php 
				$folder = "payroll-bank/out/"; //Sesuaikan Folder nya
				if(!($buka_folder = opendir($folder))) die ("eRorr... Tidak bisa membuka Folder");

				$filecari = "Payroll_GAJI_".date('d', strtotime($PayrollDte))."_".date('mY', strtotime($PayrollDte))."_".$i."_".$payTot."_checksum.txt";

				$file_array = array();
				while($baca_folder = readdir($buka_folder))
				{
					$file_array[] = $baca_folder;
				}

				$jumlah_array = count($file_array);
				for($k=2; $k<$jumlah_array; $k++)
				{
					//echo $file_array[$k];
					if ($file_array[$k] == $filecari){
						echo "<br/><a href='payroll-prn-dig-bank-download.php?filename=$filecari'>$filecari</a><br/>";
					}
				}

				closedir($buka_folder);
			?>

		</form>
		<?php
	}
?>