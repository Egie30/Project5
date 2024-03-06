<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	//include "framework/security/default.php";
	if ($_GET['CO_NBR']==''){
		$CoNbr = '1677';
	}else{
		$CoNbr = $_GET['CO_NBR'];	
	}
	
	if ($_GET['ACTG']==''){
		$Actg='ALL';
	}else{
		$Actg=$_GET['ACTG'];
	}

	function tableDetail($ordTs,$ordQ,$finCmpQ,$ordNbr,$ordDetNbr){
		//echo "<tr>";
		if ($ordQ == $finCmpQ){
			$styleProd= "print-digital-green";

		}

		echo "<table  class ='tableDetail'width='100%'>";
		echo  "<tr>";
		echo  "<td class='tableDet' style='vertical-align:top;'><b>Nota Order</b></td>";
		echo  "<td class='tableDet' style='text-align:right;'>".$ordNbr."<br/>".date('m-d',strtotime($ordTs))."</td>";
		echo  "</tr>";
		echo  "<tr>";
		echo  "<td class='tableDet' style='vertical-align:top;'><b>Produksi</b></td>";
		echo  "<td style='text-align:right;'><div class='".$styleProd."'>".$ordQ."</div></td>";
		echo  "</tr>";
		echo  "<tr>";
		echo  "<td  class='tableDet'><b>Jadi</b></td>";
		echo  "<td class='tableDet' style='text-align:right;'><div class='".$styleProd."'>".$finCmpQ."</div></td>";
		echo  "</tr>";
		echo  "<tr>";
		echo  "<td  class='tableDet' style='vertical-align:top;'><b>Diterima</b></td>";
		echo  "<td>";

		$queryTrns = "SELECT
						COALESCE(SUM(TRNSP_Q),0) AS TRNSP_Q,
						DATE(TRNSP_TS) AS TRNSP_TS
					 FROM CMP.TRNSP_HEAD HED 
					 LEFT OUTER JOIN CMP.TRNSP_DET DET ON HED.TRNSP_NBR = DET.TRNSP_NBR
					 WHERE
					 	HED.DEL_NBR=0
					 	AND DET.DEL_NBR=0
					 	AND HED.ORD_NBR=".$ordNbr."
					 AND ORD_DET_NBR=".$ordDetNbr."
					 GROUP BY HED.TRNSP_NBR
					 ORDER BY  TRNSP_TS DESC";
		$resultTrns= mysql_query($queryTrns);

		echo "<table width='100%'>";
		while($rowTrns=mysql_fetch_array($resultTrns)){
			$trnspQTot +=$rowTrns['TRNSP_Q'];
			$trnspQ[]=$rowTrns['TRNSP_Q'];
			$trnspTs[]=$rowTrns['TRNSP_TS'];
		}

		for ($i=0; $i < count($trnspQ) ; $i++) { 
			if ($trnspQTot == $ordQ){
					$styleTrnsp = "print-digital-green";
				}
				echo "<tr>";
				echo "<td class='tableDet' style='text-align:right;padding-top:0px;padding-right:0px;padding-left:0px;'>".date('m-d',strtotime($trnspTs[$i]))."</td>";
				echo "</tr>";
				echo "<tr>";
				echo "<td class='tableDet' style='text-align:right;padding-top:0px;padding-right:0px;padding-left:0px;'><div class='".$styleTrnsp."'>".$trnspQ[$i]."</div></td>";
				echo "</tr>";
		}
		echo "</table>";
		echo  "</td>";
		echo  "</tr>";
		echo "</table>";
		
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
<link rel="stylesheet" type="text/css" media="screen" href="framework/combobox/chosen.css" />

<script src="framework/database/jquery.min.js"></script>
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<script type="text/javascript" src="framework/tablesorter/jquery-latest.js"></script>
<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
<script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>
<script type="text/javascript" src="framework/combobox/chosen.default.js"></script>
<script type="text/javascript" src="framework/combobox/ajax-chosen/ajax-chosen.js"></script>	
<script>
    
</script>
<script type="text/javascript">
	function change(x){
		var selectIndex=x.selectedIndex;
		var selectValue=x.options[selectIndex].value;

		if (selectValue!=''){
			location.href='print-digital-report-special.php?CO_NBR='+selectValue;
		}
	}
</script>
<style type="text/css">
	table.tableZebra tbody tr.even {
		-moz-border-radius: 3px 0px 0px 3px;
	    -webkit-border-radius: 3px 0px 0px 3px;
	    border-radius: 3px 0px 0px 3px;
	    background: #f6f6f6;
	} 

	table.tableZebra tbody tr.odd table.tableDetail tr td.tableDet {
    	background: #ffffff;
	}
	

tr.headTab th{
		border-bottom: 1px solid #cccccc;
	}
</style>
</head>

<body>

<table class="submenu">
	<tr>
		<td class="submenu" style="background-color:">
			<?php
				$queryTyp = "SELECT 
								SUBSTRING_INDEX(SUBSTRING_INDEX(DET_TTL,'-',2),'-',1) AS DET_TTL_NBR,
								CASE 
									WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(DET_TTL,'-',2),'-',1) = 'SB'
										THEN 'Jenis Serbaguna'
									WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(DET_TTL,'-',2),'-',1) = 'SP'
										THEN 'Sepatu Pendek'
									WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(DET_TTL,'-',2),'-',1) = 'SN'
										THEN 'Sepatu Normal'
									WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(DET_TTL,'-',2),'-',1) = 'SS'
										THEN 'Sepatu Sedang'
									WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(DET_TTL,'-',2),'-',1) = 'SJB'
										THEN 'Sarung Jilbab'
									WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(DET_TTL,'-',2),'-',1) = 'JB'
										THEN 'Jilbab'
									WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(DET_TTL,'-',2),'-',1) = 'TN'
										THEN 'Tas Normal'
									WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(DET_TTL,'-',2),'-',1) = 'T7'
										THEN 'Tas 7Plong'
								END AS DET_TTL_DESC,
								DET.DET_TTL
							FROM CMP.PRN_DIG_ORD_HEAD HED
							LEFT OUTER JOIN CMP.PRN_DIG_ORD_DET DET ON HED.ORD_NBR = DET.ORD_NBR
							LEFT OUTER JOIN CMP.PRN_DIG_STT STT ON HED.ORD_STT_ID = STT.ORD_STT_ID
							LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR = PPL.PRSN_NBR
							WHERE 
								HED.DEL_NBR=0
								AND DET.DEL_NBR=0
								AND STT.ORD_STT_ORD <11
								AND BUY_CO_NBR =".$CoNbr."
								AND SUBSTRING_INDEX(SUBSTRING_INDEX(DET_TTL,'-',2),'-',1) IN ('SB','SP', 'SN', 'SS', 'SJB', 'JB', 'TN', 'T7')
							GROUP BY 
								SUBSTRING_INDEX(SUBSTRING_INDEX(DET_TTL,'-',2),'-',1)
							ORDER BY SUBSTRING_INDEX(SUBSTRING_INDEX(DET_TTL,'-',2),'-',1) ASC";
			$resultTyp= mysql_query($queryTyp);
			
			if ($Actg == 'ALL' ){ $classTyp= "arrow_box"; } else { $classTyp= "leftsubmenu"; }
			echo  "<a class ='submenu' href='?CO_NBR=".$CoNbr."&ACTG=ALL'>";
			echo  "<div class='".$classTyp."'>ALL</div></a>";

			while($rowTyp=mysql_fetch_array($resultTyp)){
				echo "<a class='submenu' href='?CO_NBR=".$CoNbr."&ACTG=".$rowTyp['DET_TTL_NBR']."'><div class='";
					if ($rowTyp['DET_TTL'] == $Actg){ echo "arrow_box"; } else { echo "leftsubmenu"; }
					echo "'>".$rowTyp['DET_TTL_DESC']."</div></a>";
			}
			
			?>	
		</td>
		<td class="subcontent">
			
		<div class="toolbar" >
		<div class="combobox"></div>
			<div class="toolbar-text">
				<p class="toolbar-left" style="float:none;margin-top: 6px;">
					<select id="CO_NBR" name="CO_NBR" style="width:150px" class="chosen-select" onchange="change(this);">
						<?php				
							$query = "SELECT CO_NBR, NAME FROM CMP.COMPANY WHERE DEL_NBR= 0 AND CO_NBR=".$CoNbr;
							genCombo($query, "CO_NBR", "NAME", $CoNbr);
						?>
					</select>

				</p>		
				<p class="toolbar-right" style="display: none;">
					<span class='fa fa-search fa-flip-horizontal toolbar'></span><input type="text" id="livesearch" class="livesearch" />
				</p>
			</div>
			<input type="hidden" id="ACTG" name="ACTG" value="<?php echo $Actg; ?>">
		</div>

		
	
	</div>

	<div class="searchresult" id="liveRequestResults"></div>
	<br />
	<div id="mainResult"></div>
	<table id="mainTable" class="tableZebra searchTable">
		<thead>
			<tr class="headTab">
				<th style="text-align:center;">Jenis Print</th>
				<th style="text-align:center;" >Tunai</th>
				<?php 
					$prsnNbr[]='';//data kosong
					$query = "SELECT PRSN_NBR, NAME FROM PEOPLE WHERE CO_NBR=".$CoNbr;
					$result= mysql_query($query);
					while($rowPpl=mysql_fetch_array($result)){
						echo "<th style='text-align:center;'>".$rowPpl['NAME']."</th>";

						$prsnNbr[]=$rowPpl['PRSN_NBR'];
					}
				?>
			</tr>
		</thead>
		<tbody>
		<?php
			if ($Actg=='ALL'){
				$whereClause= " AND SUBSTRING_INDEX(SUBSTRING_INDEX(DET_TTL,'-',2),'-',1) IN ('SB','SP', 'SN', 'SS', 'SJB', 'JB', 'TN', 'T7')";
			}else{
				$whereClause= " AND SUBSTRING_INDEX(SUBSTRING_INDEX(DET_TTL,'-',2),'-',1)='".$Actg."'";
			}

			$query ="SELECT 
						HED.ORD_NBR,
						DATE(HED.ORD_TS) AS ORD_TS,
						HED.BUY_PRSN_NBR,
						DET.PRN_DIG_TYP,
						DET.ORD_DET_NBR,
						SUBSTRING_INDEX(SUBSTRING_INDEX(DET_TTL,'-',2),'-',1) AS DET_TTL_NBR,
						CASE 
							WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(DET_TTL,'-',2),'-',1) = 'SB'
								THEN 'Jenis Serbaguna'
							WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(DET_TTL,'-',2),'-',1) = 'SP'
								THEN 'Sepatu Pendek'
							WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(DET_TTL,'-',2),'-',1) = 'SN'
								THEN 'Sepatu Normal'
							WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(DET_TTL,'-',2),'-',1) = 'SS'
								THEN 'Sepatu Sedang'
							WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(DET_TTL,'-',2),'-',1) = 'SJB'
								THEN 'Sarung Jilbab'
							WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(DET_TTL,'-',2),'-',1) = 'JB'
								THEN 'Jilbab'
							WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(DET_TTL,'-',2),'-',1) = 'TN'
								THEN 'Tas Normal'
							WHEN SUBSTRING_INDEX(SUBSTRING_INDEX(DET_TTL,'-',2),'-',1) = 'T7'
								THEN 'Tas 7Plong'
						END AS DET_TTL_DESC,
						UPPER(DET.DET_TTL) AS DET_TTL,
						COALESCE(SUM(DET.ORD_Q),0) AS ORD_Q,
						COALESCE(SUM(DET.FIN_CMP_Q),0) AS FIN_CMP_Q
					FROM CMP.PRN_DIG_ORD_HEAD HED
					LEFT OUTER JOIN CMP.PRN_DIG_ORD_DET DET ON HED.ORD_NBR = DET.ORD_NBR
					LEFT OUTER JOIN CMP.PRN_DIG_STT STT ON HED.ORD_STT_ID = STT.ORD_STT_ID
					LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR = PPL.PRSN_NBR
					LEFT OUTER JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP = TYP.PRN_DIG_TYP
					WHERE 
						HED.DEL_NBR = 0
						AND DET.DEL_NBR = 0
						AND STT.ORD_STT_ORD <11
						AND BUY_CO_NBR =".$CoNbr.$whereClause."
					GROUP BY
						DET.DET_TTL,
						#HED.BUY_PRSN_NBR
						HED.ORD_NBR
					ORDER BY DET.DET_TTL ASC";

			$result = mysql_query($query);
			while ($row=mysql_fetch_array($result)){
					$data = $row['ORD_NBR'].",".$row['ORD_TS'].",".$row['BUY_PRSN_NBR'].",".$row['PRN_DIG_TYP'].",".$row['ORD_DET_NBR'].",".$row['DET_TTL_NBR'].",".$row['DET_TTL_DESC'].",".$row['DET_TTL'].",".$row['ORD_Q'].",".$row['FIN_CMP_Q'];
					$detTtlNbr[]   = $row['DET_TTL_NBR'];
					$buyPrsnNbr[]=$row['BUY_PRSN_NBR'];
				$dt[] = array('DET_TTL_NBR'=>$row['DET_TTL_NBR'],'DET_TTL'=>$row['DET_TTL'],'BUY_PRSN_NBR'=>$row['BUY_PRSN_NBR'],"data"=>$data);  

				
			}

			if (mysql_num_rows($result)>0){
			foreach ($dt as $key => $value) {
				$data2 =array('DET_TTL'=>$value['DET_TTL'],'BUY_PRSN_NBR'=>$value['BUY_PRSN_NBR'],'data'=>$value['data']);
				foreach ($prsnNbr as $key2 => $value2) {
					if ($value2==$value['BUY_PRSN_NBR']){
						$data3[$value['DET_TTL_NBR']][$value['DET_TTL']][$value2][]=$data2;
					}
				}
			}
			}
			

			//$widthTd= 100/(count($prsnNbr)+1);			
			
			// foreach ($dt as $key => $val) {
			// 	echo "<tr>";
			// 		echo "<td width='".$widthTd."%'>".$val['DET_TTL']."</td>";
			// 	foreach ($prsnNbr as $key1 => $val1) {
			// 		echo "<td width='".$widthTd."%'>";
			// 			if ($val1==$val['BUY_PRSN_NBR']){

			// 				$dataDet= explode(',', $val['data']);
			// 				tableDetail($dataDet[1],$dataDet[8],$dataDet[9],$dataDet[0],$dataDet[4]);
			// 			}
			// 		echo "</td>";
			// 	}
			// 	echo "</tr>";
			// }
			$styleWidthTd = 100/(count($prsnNbr)+1);
			if (count($data3)>0){
			foreach ($data3 as $key => $value) {
				foreach ($value as $key2 => $value2) {
					echo "<tr>";
					echo "<td width='".$styleWidthTd."%' style='vertical-align:top;'>$key2</td>";
					foreach ($prsnNbr as $key3 => $value3) {
						echo "<td width='".$styleWidthTd."%' style='vertical-align:top;'>";
						foreach ($value2 as $key4 => $value4) {
							if ($key4 == $value3){
								
								foreach ($value4 as $key5 => $value5) {
									//echo "oke";	
									$dataDet = explode(',', $value5['data']);
									tableDetail($dataDet[1],$dataDet[8],$dataDet[9],$dataDet[0],$dataDet[4]);
										
								}
							}	
						}
						echo "</td>";
					}
					echo "</tr>";
				}
			}
			}
				// echo "<pre>";
				// print_r($fn);
				// echo "</pre>";
		?>
		</tbody>
	</table>
	</td>
	</tr>
</table>

<script>
	$(document).ready(function()
		{
			$("#mainTable").tablesorter({ widgets:["zebra"]});  
		}
	);
</script>

<script>liveReqInit('livesearch','liveRequestResults','print-digital-report-special-ls.php?CO_NBR=<?php echo $CoNbr; ?>&ACTG=<?php echo $Actg;?>','','mainResult');</script>
</body>
</html>


