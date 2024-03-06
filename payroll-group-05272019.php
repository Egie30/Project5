<?php
	include "framework/database/connect-cloud.php";
	//Process delete entry
	include "framework/security/default.php";
	$Security=getSecurity($_SESSION['userID'],"Payroll");

	if($cloud!=false){
	
		if(($_GET['DEL']!="")&&($_GET['DATE']!=""))
		{
			$query="UPDATE $PAY.PAYROLL SET DEL_NBR=".$_SESSION['personNBR'].", UPD_TS=CURRENT_TIMESTAMP WHERE PRSN_NBR=".$_GET['DEL']." AND PYMT_DTE='".$_GET['DATE']."'";
			//echo $query;
			$result=mysql_query($query,$cloud);
			$query=str_replace($PAY,"PAY",$query);
			$result=mysql_query($query,$local);
		}	
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
<script src="framework/database/jquery.min.js"></script>

<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>	
</head>

<body>


<div class="toolbar">
	<p class="toolbar-left"></p>
	<p class="toolbar-right">
		<?php if($Security<2){ ?> 
		<a href="payroll-prn-dig-bank.php"><span class='fa fa-bank toolbar' style="cursor:pointer" onclick="location.href="></span></a> 
		<?php } ?>
		<a href="payroll-prn-dig-edit-print.php?CONBR=ALL&EMAIL=1&AUTO=1"><span class='fa fa-paper-plane-o toolbar' style="cursor:pointer" onclick="location.href="></span></a>
		<?php if($Security<2){ ?>
		<a href="payroll-prn-dig-edit-print.php?CONBR=ALL&AUTO=1"><span class='fa fa-print toolbar' style="cursor:pointer" onclick="location.href="></span></a>
		<?php } ?>
		<span class='fa fa-search fa-flip-horizontal toolbar' style='cursor:default'></span><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>


<div id="mainResult">
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th style="text-align:center;">Id Karyawan</th>
				<th>Nama</th>
				<th>Jabatan</th>
				<th class="sorter-shortDate dateFormat-ddmmyyyy" style="border-right:0px;">Gajian Terakhir</th>
				<th>Lokasi</th>
				<th>Pokok</th>
				<th>Tambah</th>
				<th>Kontribusi</th>
				<th>Masuk</th>
				<th>Lembur</th>
				<th>%</th>
				<th>Rek</th>
				<th>Total</th>
				<th>Bonus</th>
				<th>Cicilan</th>
				<th>Extra</th>
				<th>Potong</th>
				<th>Travel</th>
				<th>Gaji Ditahan</th>
				<th>Pencairan Bon</th>
				<th>Pemasangan</th>
			</tr>
		</thead>
		<tbody>
		<?php
			$i = 1;
			
			$query="SELECT PPL.PRSN_NBR
						,PPL.NAME
						,COM.NAME AS CO_NAME
						,POS_DESC
						,PAY.PYMT_DTE AS PYMT_DTE
						,PPAY.PAY_BASE
						,PPAY.PAY_ADD
						,PPAY.PAY_CONTRB
						,PAY.BASE_CNT
						,PAY.OT_CNT
						,PAY.BON_PCT
						,PPL.BNK_ACCT_NBR
						,PAY.PAY_AMT
						,PAY.BON_MO_AMT
						,PAY.DEBT_MO
						,PAY.BON_SNG_AMT
						,PAY.DED_SNG_AMT
						,PAY.DSBRS_CRDT
						,PAY.AUTH_TRVL_AMT
						,PAY.PAY_HLD_AMT
						,PAY.PAY_MISC_AMT
					FROM CMP.PEOPLE PPL
					LEFT OUTER JOIN PAY.PEOPLE PPAY ON PPL.PRSN_NBR = PPAY.PRSN_NBR
					INNER JOIN CMP.POS_TYP POS ON PPL.POS_TYP = POS.POS_TYP
					LEFT OUTER JOIN (
						SELECT PRSN_NBR
							,PYMT_DTE
							,PAY_AMT
							,BASE_CNT
							,OT_CNT
							,BON_PCT
							,BON_MO_AMT
							,DEBT_MO
							,BON_SNG_AMT
							,DED_SNG_AMT
							,DSBRS_CRDT
							,DEL_NBR
							,AUTH_TRVL_AMT
							,PAY_HLD_AMT
							,PAY_MISC_AMT
						FROM PAY.PAYROLL AMT
						WHERE AMT.PYMT_DTE = (
								SELECT MAX(PYMT_DTE)
								FROM PAY.PAYROLL SLP
								WHERE SLP.PRSN_NBR = AMT.PRSN_NBR
								)
						) PAY ON PAY.PRSN_NBR = PPL.PRSN_NBR
					INNER JOIN CMP.COMPANY COM ON PPL.CO_NBR = COM.CO_NBR
					WHERE TERM_DTE IS NULL
						AND PPAY.PAY_TYP = 'MON'
						AND PPL.CO_NBR IN (
							SELECT CO_NBR
							FROM NST.PARAM_PAYROLL
							)
						AND (
							PAY.DEL_NBR = 0
							OR PAY.DEL_NBR IS NULL
							)
						AND PPL.DEL_NBR = 0
					GROUP BY PPL.PRSN_NBR
						,NAME
						,POS_DESC
					ORDER BY 2";
			// echo $query;
			$result=mysql_query($query,$local);
			$alt="";
			while($row=mysql_fetch_array($result))
			{
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='payroll-edit.php?PRSN_NBR=".$row['PRSN_NBR']."&CO_NBR=".$CoNbr."';".chr(34).">";
				echo "<td style='text-align:center'>".$row['PRSN_NBR']."</td>";
				echo "<td>".$row['NAME']."</td>";
				echo "<td>".$row['POS_DESC']."</td>";
				if ($row['PYMT_DTE'] != "") { 			
				echo "<td>".date('d-m-Y', strtotime($row['PYMT_DTE']))."</td>";
				}
				else { echo "<td></td>";	}

					echo "<td>".$row['CO_NAME']."</td>";
					echo "<td style='text-align:right'>".number_format($row['PAY_BASE'],0,",",".")."</td>";
					echo "<td style='text-align:right'>".number_format($row['PAY_ADD'],0,",",".")."</td>";
					echo "<td style='text-align:right'>".number_format($row['PAY_CONTRB'],0,",",".")."</td>";
					echo "<td style='text-align:right'>".number_format($row['BASE_CNT'],0,",",".")."</td>";
					echo "<td style='text-align:right'>".number_format($row['OT_CNT'],0,",",".")."</td>";
					echo "<td style='text-align:right'>".number_format($row['BON_PCT'],1,",",".")."%</td>";
					echo "<td style='text-align:right'>".$row['BNK_ACCT_NBR']."</td>";
					echo "<td style='text-align:right'>".number_format($row['PAY_AMT'],0,",",".")."</td>";
					echo "<td style='text-align:right'>".number_format($row['BON_MO_AMT'],0,",",".")."</td>";
					echo "<td style='text-align:right'>".number_format($row['DEBT_MO'],0,",",".")."</td>";
					echo "<td style='text-align:right'>".number_format($row['BON_SNG_AMT'],0,",",".")."</td>";
					echo "<td style='text-align:right'>".number_format($row['DED_SNG_AMT'],0,",",".")."</td>";
					echo "<td style='text-align:right'>".number_format($row['AUTH_TRVL_AMT'],0,",",".")."</td>";
					echo "<td style='text-align:right'>".number_format($row['PAY_HLD_AMT'],0,",",".")."</td>";
					echo "<td style='text-align:right'>".number_format($row['DSBRS_CRDT'],0,",",".")."</td>";
					echo "<td style='text-align:right'>".number_format($row['PAY_MISC_AMT'],0,",",".")."</td>";
				echo "</tr>";
				
				$i++;
			}
		?>
		</tbody>
	</table>
</div>
<script>
	$(document).ready(function()
		{
			$("#mainTable").tablesorter({ widgets:["zebra"]});  
		}
	);
</script>

<script>liveReqInit('livesearch','liveRequestResults','payroll-ls.php?CO_NBR=<?php echo $_GET['CO_NBR'];?>','','mainResult');</script>

</body>
</html>


