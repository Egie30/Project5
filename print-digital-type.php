<?php
	include "framework/database/connect.php";
	include "framework/security/default.php";
	
	$UpperSec=getSecurity($_SESSION['userID'],"Executive");
	

		//Process delete entry
		if($_GET['DEL_L']!="")
		{
			$query="UPDATE $CMP.PRN_DIG_TYP SET DEL_NBR=".$_SESSION['personNBR'].",UPD_TS=CURRENT_TIMESTAMP WHERE PRN_DIG_TYP='".$_GET['DEL_L']."'";
			//echo $query;
	   		$result=mysql_query($query,$local);
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

<?php
	if($_GET['DEL_L']!=""){
		echo "<script>window.scrollTo(0,0);parent.document.getElementById('offline').style.display='block';parent.document.getElementById('fade').style.display='block';</script>";			
	}	
?>

<div class="toolbar">
	<?php if($UpperSec<5){ ?>
	<p class="toolbar-left"><a href="print-digital-type-edit.php?PRN_DIG_TYP="><span class='fa fa-plus toolbar' style='cursor:pointer' onclick="location.href="></a></p>
	<?php } ?>
	<p class="toolbar-right"><span class='fa fa-search fa-flip-horizontal toolbar' style='cursor:default' src="img/search.png"></span><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">
	<table id="mainTable" class="tablesorter searchTable">
		<thead>
			<tr>
				<th style="text-align:right;">Kode</th>
				<th>Deskripsi</th>
				<th><div style="float: left;text-align: center;width: 80%">Sub Kategori</div></th>
				<th><div style="float: left;text-align: center;width: 70%">No. Stock</div></th>
				<th>Harga</th>
				<th>Harga Tunai</th>
				<th>Harga Member</th>
				<?php if ($UpperSec ==0){?>
				<th><div style="float: left;text-align: center;width: 70%;padding-left: 6.5px;">Harga Beli</div></th>
				<th><div style="float: left;text-align: center;width: 85%;margin-right: -10px;">Harga Rata-Rata</div></th>
				<th style="width: 6%">Faktor</th>
				<?php } ?>
				<th style="width: 6%">Promo</th>
				<th style="border-right:0px;">Vol Disc</th>
				<th>Daftar Harga Induk</th>
				<th style="width: 6%">Alias</th>	
				<?php if ($UpperSec == 0){ ?>
				<th style="width: 6%">Status</th>
				<?php } ?>
			</tr>
		</thead>
		<tbody>
		<?php
			// $query="SELECT TYP.PRN_DIG_TYP,PRN_DIG_TYP_PAR,PRN_DIG_DESC,PRN_DIG_EQP_DESC,TYP.INV_NBR,PRN_DIG_PRC,PROMO_DISC_AMT,PLAN_DESC
			// 		  FROM CMP.PRN_DIG_TYP TYP INNER JOIN 
			// 		  CMP.PRN_DIG_VOL_PLAN_TYP PLN ON TYP.PLAN_TYP=PLN.PLAN_TYP INNER JOIN
			// 		  CMP.PRN_DIG_EQP EQP ON TYP.PRN_DIG_EQP=EQP.PRN_DIG_EQP LEFT OUTER JOIN
			// 		  (SELECT PRN_DIG_TYP,SUM(PROMO_DISC_AMT) AS PROMO_DISC_AMT FROM CMP.PRN_DIG_PROMO WHERE BEG_DT<=CURRENT_DATE AND END_DT>=CURRENT_DATE GROUP BY PRN_DIG_TYP) PRO ON TYP.PRN_DIG_TYP=PRO.PRN_DIG_TYP
			// 		  WHERE TYP.DEL_NBR=0
			// 		  ORDER BY 2";
			$query="SELECT TYP.PRN_DIG_TYP,PRN_DIG_TYP_PAR,PRN_DIG_DESC,CAT_SUB_DESC,PRN_DIG_EQP_DESC,TYP.INV_NBR,TYP.PRN_DIG_PRC,PRN_DIG_PRC_PRSN,PRN_DIG_PRC_MBR,INV_PRC,AVG_INV_PRC,(CASE WHEN AVG_INV_PRC <>0 THEN  TYP.PRN_DIG_PRC/AVG_INV_PRC ELSE TYP.PRN_DIG_PRC/INV_PRC END) AS FACTOR,PRN_DIG_PRC,PROMO_DISC_AMT,PLAN_DESC,PRN_DIG_CD,ACT_F
					FROM CMP.PRN_DIG_TYP TYP INNER JOIN 
					CMP.PRN_DIG_VOL_PLAN_TYP PLN ON TYP.PLAN_TYP=PLN.PLAN_TYP INNER JOIN
					CMP.PRN_DIG_EQP EQP ON TYP.PRN_DIG_EQP=EQP.PRN_DIG_EQP LEFT OUTER JOIN
					(SELECT PRN_DIG_TYP,SUM(PROMO_DISC_AMT) AS PROMO_DISC_AMT FROM CMP.PRN_DIG_PROMO WHERE BEG_DT<=CURRENT_DATE AND END_DT>=CURRENT_DATE GROUP BY PRN_DIG_TYP) PRO ON TYP.PRN_DIG_TYP=PRO.PRN_DIG_TYP
					LEFT OUTER JOIN 
						(
							SELECT INV_NBR, CAT_SUB_NBR, PRD_PRC_TYP
							FROM RTL.INVENTORY
							GROUP BY PRD_PRC_TYP
						) INV ON INV.PRD_PRC_TYP = TYP.PRN_DIG_TYP
					LEFT OUTER  JOIN RTL.CAT_SUB SUB ON INV.CAT_SUB_NBR=SUB.CAT_SUB_NBR AND SUB.DEL_NBR=0
					LEFT OUTER JOIN (
						SELECT 
							DET.ORD_DET_NBR, DET.ORD_NBR, HED.ORD_DTE, DET.INV_NBR, DET.INV_PRC, INV.PRD_PRC_TYP
						FROM RTL.RTL_STK_DET DET 
						LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON HED.ORD_NBR = DET.ORD_NBR
						LEFT JOIN RTL.INVENTORY INV ON INV.INV_NBR = DET.INV_NBR
						INNER JOIN (
							SELECT ORD_DET_NBR, DET.INV_NBR, MAX(ORD_DTE) AS MAX_ORD_DATE, INV.PRD_PRC_TYP
							FROM RTL.RTL_STK_DET DET 
							LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON HED.ORD_NBR = DET.ORD_NBR
							LEFT JOIN RTL.INVENTORY INV ON INV.INV_NBR = DET.INV_NBR
							WHERE IVC_TYP='RC'
							GROUP BY PRD_PRC_TYP
						)MAX ON MAX.PRD_PRC_TYP = INV.PRD_PRC_TYP AND MAX.MAX_ORD_DATE = HED.ORD_DTE
						WHERE IVC_TYP='RC'
						GROUP BY INV.PRD_PRC_TYP
					) STK ON STK.PRD_PRC_TYP = TYP.PRN_DIG_TYP 
					LEFT OUTER JOIN (
						SELECT 
								DET.ORD_DET_NBR, DET.INV_NBR, AVG(DET.INV_PRC) AS AVG_INV_PRC,PRD_PRC_TYP
						FROM 
								RTL.RTL_STK_DET DET 
						LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON HED.ORD_NBR =  DET.ORD_NBR
						LEFT OUTER JOIN RTL.INVENTORY INV ON INV.INV_NBR = DET.INV_NBR
						INNER JOIN (
								SELECT ORD_DET_NBR, INV_NBR, MAX(ORD_DTE) AS MAX_ORD_DATE
								FROM RTL.RTL_STK_DET DET 
								LEFT OUTER JOIN RTL.RTL_STK_HEAD HED ON HED.ORD_NBR = DET.ORD_NBR 
								WHERE IVC_TYP='RC'
								GROUP BY INV_NBR
						) MAX ON MAX.INV_NBR = DET.INV_NBR
						WHERE 
							IVC_TYP='RC' AND
							(HED.ORD_DTE BETWEEN  (MAX.MAX_ORD_DATE - INTERVAL 6 MONTH) AND MAX.MAX_ORD_DATE)
						GROUP BY INV.PRD_PRC_TYP
					)AVG ON AVG.PRD_PRC_TYP =  TYP.PRN_DIG_TYP
					WHERE TYP.DEL_NBR=0
					ORDER BY 2";
					//ECHO "<PRE>".$query;
			$result=mysql_query($query);
			$alt="";
			while($row=mysql_fetch_array($result))
			{
				if ($row['ACT_F']=='1')
				{ 
					$status = "checked"; 
				}
				else
				{
					$status = "";
				}

				echo "<tr $alt ";
				if($UpperSec<5){ 
					echo "style='cursor:pointer;' onclick=".chr(34)."location.href='print-digital-type-edit.php?PRN_DIG_TYP=".$row['PRN_DIG_TYP']."';".chr(34);
				}
				echo ">";
				echo "<td>".$row['PRN_DIG_TYP']."</td>";
				echo "<td>".$row['PRN_DIG_DESC']."</td>";
				echo "<td>".$row['CAT_SUB_DESC']."</td>";
				echo "<td style='text-align:center'>".$row['INV_NBR']."</td>";
				echo "<td style='text-align:right'>".number_format($row['PRN_DIG_PRC'],0,'.','.');
				echo "<td style='text-align:right'>".number_format($row['PRN_DIG_PRC_PRSN'],0,'.','.');
				echo "<td style='text-align:right'>".number_format($row['PRN_DIG_PRC_MBR'],0,'.','.');
				if ($UpperSec == 0){
					echo "<td style='text-align:right'>".number_format($row['INV_PRC'],0,'.','.');
					echo "<td style='text-align:right'>".number_format($row['AVG_INV_PRC'],0,'.','.');
					echo "<td style='text-align:right'>".number_format($row['FACTOR'],2,'.','.');
				}
				echo "<td style='text-align:right'>";
				if($row['PROMO_DISC_AMT']!=""){
					echo number_format($row['PRN_DIG_PRC']-$row['PROMO_DISC_AMT'],0,'.','.');
				}
				echo "</td>";
				echo "<td>".$row['PLAN_DESC']."</td>";
				echo "<td>".$row['PRN_DIG_TYP_PAR']."</td>";
				echo "<td>".$row['PRN_DIG_CD']."</td>";
				if ($UpperSec == 0){
				echo "<td><input name='ACT_F' id='ACT_F' type='checkbox' class='regular-checkbox' ".$status."/><label for='ACT_F'></label></td>";
				}
				echo "</tr>";
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
<script>liveReqInit('livesearch','liveRequestResults','print-digital-type-ls.php','','mainResult');</script>
</body>
</html>


