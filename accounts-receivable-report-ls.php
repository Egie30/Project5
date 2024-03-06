<?php

	require_once "framework/database/connect.php";
	require_once "framework/functions/default.php";
	require_once "framework/pagination/pagination.php";

	$LS  		= $_GET['LS'];	
		// echo $LS;
	$val 		= $_GET['CO_NBR'];
		// echo $val;
	$Accounting = $_GET['ACTG'];
		// echo $Accounting ;
	$limit 		= $_GET['LIMIT'];
		// echo $limit ;

	try 
	{
		$_GET['ORD_BY'] = array("UPD_TS" => "DESC", "ORD_NBR" => "DESC");
		
		ob_start();
		include __DIR__ . DIRECTORY_SEPARATOR . "ajax/accounts-receivable-report.php";

		$results = json_decode(ob_get_clean());
		// print_r($results) ;
	} 
	catch (\Exception $ex)
	{
		ob_end_clean();
	}
	if (count($results->data) == 0) 
	{
		echo "<div class='searchStatus'>Data yang ingin ditampilkan tidak tersedia.</div>";
	    exit;
	}
?>

<style type="text/css">
	.scroll-table
	{
       	height:	350px;
       	overflow: auto;
    }
</style>

<?php 
	  if ($LS == '1') 
	  { 
		  	echo "<br><br>"; 
	  } 
	  else 
	  {

	  } 
?>

<div class="scroll-table">
<table class="table-accounting tablesorter std-row-alt rowstyle-alt colstyle-alt no-arrow searchTable sortable-onload-show">
	<thead>
		<tr>
			<th class="nosort"  width="2%"  style="text-align:center;">No Nota</th>
			<th class="nosort"  width="30%"  style="text-align:center;">Tanggal Nota</th>
			<th class="nosort"  width="50%"  style="text-align:center;">Customer</th>
			<th class="nosort"  width="12%"  style="text-align:center;">Total Nota</th>
			<th class="nosort"  width="10%" style="text-align:center;">Total Bayar</th>
			<th class="nosort"  width="5%" style="text-align:center;">Sisa</th>
		</tr>
	</thead>
	<tbody>
	<?php
	$alt="";
	foreach ($results->data as $result) 
	{
		?>
		<tr <?php echo $alt ?> style="" >
		<td class="std" width="2%" style="text-align:right; background-color: <?php echo $backcolor ?>; color:<?php echo $color ?>;"><?php echo $result->ORD_NBR;?></td>
		<td class="std" width="30%" style="text-align:center; background-color: <?php echo $backcolor ?>; color:<?php echo $color ?>;"><?php echo $result->ORD_TS;?></td>
		<td class="std" width="50%" style="text-align:left; background-color: <?php echo $backcolor ?>; color:<?php echo $color ?>;"><?php echo $result->BUY_NAME;?></td>
		<td class="std" width="12%" style="text-align:right; background-color: <?php echo $backcolor ?>; color:<?php echo $color ?>;"><?php echo number_format($result->TOTALNOTA,0,',','.');?></td>
		<td class="std" width="10%" style="text-align:right; background-color: <?php echo $backcolor ?>; color:<?php echo $color ?>;"><?php echo number_format($result->TOTALBAYAR,0,',','.');?></td>
		<td class="std" width="5%" style="text-align:right; background-color: <?php echo $backcolor ?>; color:<?php echo $color ?>;"><?php echo number_format($result->SISA,0,',','.');?></td>
		</tr>
	<?php
	}
	?> 
	</tbody>
</table>
</div>
  <?php
		buildPagination($results->pagination, "accounts-payable-report-ls.php");  
  ?>
</div>

<form class="form" enctype="multipart/form-data" name="listForm" action="#" method="post">
<br><br>
<div class="total" style="width:500px;height:auto;">
	<table style='background-color:white;width:500px;' border="0" >
	<?php

		$whereClauses   = array("ORD.DEL_NBR = 0");

		if ($Accounting == 0) 
		{
			$whereClauses[] = "(ORD.ORD_STT_ID = 'CP')";
		}

		if ($Accounting == 1) 
		{
			$whereClauses[] = "(ORD.ORD_STT_ID = 'CP') AND ORD.ACTG_TYP = 1";
		}

		if ($Accounting == 2) 
		{
			$whereClauses[] = "(ORD.ORD_STT_ID = 'CP') AND ((ORD.ACTG_TYP = 2 AND BUY.TAX_F = 1) OR (ORD.ACTG_TYP = 2 AND PRN.TAX_F = 1))";	
		}

		if ($Accounting == 3) 
		{
			$whereClauses[] = "(ORD.ORD_STT_ID = 'CP') AND ((ORD.ACTG_TYP = 3 AND BUY.TAX_F = 0 ) OR (ORD.ACTG_TYP = 3 AND PRN.TAX_F = 0))";
		}

		$whereClauses = implode(" AND ", $whereClauses);

	    if($val != '') 
	    {
	    	$querypitdag  = "SELECT SUM(ORD.TOT_AMT) AS ALLTOTALNOTA,
	    					  		SUM(PYMT.TND_AMT) AS TOTALBAYAR,
	    					  		ORD.ORD_NBR,
	    					  		ORD.PRN_CO_NBR,
	    					  		ORD.BUY_CO_NBR
			    			 FROM CMP.PRN_DIG_ORD_HEAD ORD
			    			 LEFT JOIN (SELECT ORD.ORD_NBR,
			    			 				   SUM(PYMT.TND_AMT) AS TND_AMT
			    			   			FROM CMP.PRN_DIG_ORD_PYMT PYMT
			    			   			LEFT JOIN CMP.PRN_DIG_ORD_HEAD ORD ON ORD.ORD_NBR = PYMT.ORD_NBR
			    			   			GROUP BY PYMT.ORD_NBR) PYMT ON PYMT.ORD_NBR = ORD.ORD_NBR 
			    			 LEFT JOIN CMP.COMPANY BUY ON ORD.BUY_CO_NBR = BUY.CO_NBR
			    			 LEFT JOIN CMP.COMPANY PRN ON ORD.PRN_CO_NBR = PRN.CO_NBR
			    			 WHERE " . $whereClauses . " AND ORD.BUY_CO_NBR = '".$val."' AND DATE(ORD.ORD_TS) <= '" . $_GET['END_DT'] . "'";
	    	// echo $querypitdag;
	    	$resultpitdag    = mysql_query($querypitdag);
			$rowpitdag 		 = mysql_fetch_array($resultpitdag);

			$totalnotpem 	 = $rowpitdag['ALLTOTALNOTA'] 	+ 0;	
			$totalpelun 	 = $rowpitdag['TOTALBAYAR']  	+ 0;	
			$totalhutdag     = $totalnotpem - $totalpelun;
	    }
	    else
	    {
	    	$querypitdag = "SELECT SUM(ORD.TOT_AMT) AS ALLTOTALNOTA,
	    					 	   SUM(PYMT.TND_AMT) AS TOTALBAYAR,
	    					 	   ORD.ORD_NBR,
	    					 	   ORD.PRN_CO_NBR,
	    					 	   ORD.BUY_CO_NBR
	    			  		FROM CMP.PRN_DIG_ORD_HEAD ORD 
	    			  		LEFT JOIN (SELECT ORD.ORD_NBR,
	    			  						  SUM(PYMT.TND_AMT) AS TND_AMT
	    			  			 FROM CMP.PRN_DIG_ORD_PYMT PYMT
	    			  			 LEFT JOIN CMP.PRN_DIG_ORD_HEAD ORD ON ORD.ORD_NBR = PYMT.ORD_NBR
	    			  			 GROUP BY PYMT.ORD_NBR) PYMT ON PYMT.ORD_NBR = ORD.ORD_NBR 
	    			 		LEFT JOIN CMP.COMPANY BUY ON ORD.BUY_CO_NBR = BUY.CO_NBR
	    			  		LEFT JOIN CMP.COMPANY PRN ON ORD.PRN_CO_NBR = PRN.CO_NBR
	    			 		WHERE " . $whereClauses . " AND DATE(ORD.ORD_TS) <= '" . $_GET['END_DT'] . "'";
	    	// echo $querypitdag;
	    	$resultpitdag    = mysql_query($querypitdag);
			$rowpitdag 		 = mysql_fetch_array($resultpitdag);

			$totalnotpem 	 = $rowpitdag['ALLTOTALNOTA'] 	+ 0;	
			$totalpelun 	 = $rowpitdag['TOTALBAYAR']  	+ 0;
			$totalhutdag     = $totalnotpem - $totalpelun;	
	    }
	    
	?>
	<tr class='total'>
			<td style="font-weight:bold;color:#3464bc;padding-left:7px;width:150px;" colspan="2">Total Nota Pembelian</td>				
			<td style="font-weight:bold;color:#3464bc;padding-left:7px;width:50px;" colspan="2"> : </td>	
			<td style="width:150px;">
			<input name="TOT_AMT" id="TOT_AMT" value="<?php echo number_format($totalnotpem,0,',','.');?>" type="text" style="margin:1px;width:100px;border:none;text-align:right;margin-left:5px;" readonly/>
			</td>
		</tr>

		<tr class='total'>
			<td style="font-weight:bold;color:#3464bc;padding-left:7px;width:150px;" colspan="2">Total Pelunasan</td>
			<td style="font-weight:bold;color:#3464bc;padding-left:7px;width:50px;" colspan="2"> : </td>		
			<td style="width:150px;">
			<input name="TOT_REM" id="TOT_REM" value="<?php echo number_format($totalpelun,0,',','.');?>" type="text" style="margin:1px;width:100px;border:none;text-align:right;margin-left:5px;" readonly/>
			</td>
		</tr>


		<tr class='total'>
			<td style="font-weight:bold;color:#3464bc;padding-left:7px;width:150px;" colspan="2">Hutang Dagang</td>		
			<td style="font-weight:bold;color:#3464bc;padding-left:7px;width:50px;" colspan="2"> : </td>					
			<td style="width:150px;">
			<input name="TOT_REM" id="TOT_REM" value="<?php echo number_format($totalhutdag,0,',','.');?>" type="text" style="margin:1px;width:100px;border:none;text-align:right;margin-left:5px;" readonly/>
			</td>
		</tr>
	</table>
</div>
</form>



