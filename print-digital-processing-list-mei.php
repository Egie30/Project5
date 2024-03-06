<?php
include "framework/database/connect.php";
include "framework/functions/default.php";
include "framework/security/default.php";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<script>parent.Pace.restart();</script>
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<script type="text/javascript" src="framework/database/jquery.min.js"></script>
</head>
<body>
<div id="mainResult">
	<table id="mainTable" class="rowstyle-alt colstyle-alt no-arrow searchTable" >
		<thead>
			</tr>
				<th>No Nota</th>
				<th>Customer</th>
				<th>Jum</th>
				<th>Panjang</th>
				<th>Lebar</th>
				<th>Keterangan</th>
				<th style="text-align:right;">Sub Total</th>
			</tr>
		</thead>
		<tbody>
		<?php
			//Determine the 'child' rows
			$query="SELECT ORD_DET_NBR,
				DET.ORD_NBR,
				BUY_PRSN_NBR,
				PPL.NAME AS NAME_PPL,
				BUY_CO_NBR,
				COM.NAME AS NAME_CO,
				DET_TTL,
				TYP.PRN_DIG_EQP,
				PRN_DIG_DESC,
				DET.PRN_DIG_PRC,
				ORD_Q,
				FIL_LOC,
				PRN_LEN,
				PRN_WID,
				FAIL_CNT,
				DISC_PCT,
				DISC_AMT,
				VAL_ADD_AMT,
				TOT_SUB,
				ROLL_F,FIN_BDR_DESC,
				COALESCE(FIN_BDR_WID,0) AS FIN_BDR_WID,
				FIN_BDR_DESC,
				COALESCE(FIN_LOP_WID,0) AS FIN_LOP_WID,
				GRM_CNT_TOP,
				GRM_CNT_BTM,GRM_CNT_LFT,
				GRM_CNT_RGT,
				COALESCE(PRN_CMP_Q,0) AS PRN_CMP_Q,
				COALESCE(FIN_CMP_Q,0) AS FIN_CMP_Q,
				PRFO_F
			FROM CMP.PRN_DIG_ORD_DET DET 
				LEFT OUTER JOIN CMP.PRN_DIG_ORD_HEAD HED ON DET.ORD_NBR = HED.ORD_NBR
				LEFT OUTER JOIN CMP.PRN_DIG_TYP TYP ON DET.PRN_DIG_TYP=TYP.PRN_DIG_TYP 
				LEFT OUTER JOIN CMP.PRN_DIG_EQP EQP ON TYP.PRN_DIG_EQP=EQP.PRN_DIG_EQP 
				LEFT OUTER JOIN CMP.PRN_DIG_FIN_BDR_TYP BDR ON DET.FIN_BDR_TYP=BDR.FIN_BDR_TYP
				LEFT OUTER JOIN CMP.PEOPLE PPL ON HED.BUY_PRSN_NBR=PPL.PRSN_NBR
				LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR 
			WHERE EQP.PRN_DIG_EQP='AJ1800F' AND DATE(ORD_TS) >= '2019-05-01' AND DET.DEL_NBR=0 
			GROUP BY DET.ORD_DET_NBR ORDER BY DET.ORD_NBR ASC";
				$resultd=mysql_query($query);
				//Details
				while($rowd=mysql_fetch_array($resultd)){
					echo "<tr $alt>";
					echo "<td>".$rowd['ORD_NBR']."</td>";
					echo "<td>".$rowd['NAME_PPL']." ".$rowd['NAME_CO']."</td>";
					echo "<td>".$rowd['ORD_Q']."</td>";
					echo "<td>".$rowd['PRN_LEN']."</td>";
					echo "<td>".$rowd['PRN_WID']."</td>";
					//echo "<td>".$rowd['ORD_DET_NBR']."</td>";
					echo "<td>".trim($rowd['DET_TTL']." ".$rowd['PRN_DIG_DESC'])."</td>";
					echo "<td style='text-align:right;'>".number_format($rowd['TOT_SUB'])."</td>";
					echo "</tr>";
					if($alt==""){$alt="class='alt'";}else{$alt="";}
				}
		?>
		</tbody>
	</table>
</div>
</body>
</html>


