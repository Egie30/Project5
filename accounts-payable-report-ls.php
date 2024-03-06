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
		include __DIR__ . DIRECTORY_SEPARATOR . "ajax/accounts-payable-report.php";

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
			<th class="nosort"  width="7%"  style="text-align:center;">Tanggal Nota</th>
			<th class="nosort"  width="2%"  style="text-align:center;">Sub Kategori</th>
			<th class="nosort"  width="7%"  style="text-align:center;">No Faktur</th>
			<th class="nosort"  width="40%" style="text-align:center;">Pengirim</th>
			<th class="nosort"  width="50%" style="text-align:center;">Penerima</th>
			<th class="nosort"  width="7%"  style="text-align:center;">Total</th>
		</tr>
	</thead>
	<tbody>
	<?php
	$alt="";
	foreach ($results->data as $result) 
	{
		?>
		<tr <?php echo $alt ?> style="cursor:pointer;" onclick="location.href='retail-stock-edit.php?IVC_TYP=RC&ORD_NBR=<?php echo $result->ORD_NBR; ?>' ">
		<td class="std" width="2%" style="text-align:right; background-color: <?php  echo  $backcolor ?>; color:<?php echo $color ?>;"><?php echo $result->ORD_NBR;?></td>
		<td class="std" width="7%" style="text-align:center; background-color: <?php echo  $backcolor ?>; color:<?php echo $color ?>;"><?php echo $result->ORD_DTE;?></td>
		<td class="std" width="2%" style="text-align:right; background-color: <?php  echo  $backcolor ?>; color:<?php echo $color ?>;"><?php echo $result->IVC_NBR;?></td>
		<td class="std" width="7%" style="text-align:right; background-color: <?php  echo  $backcolor ?>; color:<?php echo $color ?>;"><?php echo $result->TAX_IVC_NBR;?></td>
		<td class="std" width="40%" style="text-align:left; background-color: <?php  echo  $backcolor ?>; color:<?php echo $color ?>;"><?php echo $result->SHP_NAME;?></td>
		<td class="std" width="50%" style="text-align:left; background-color: <?php  echo  $backcolor ?>; color:<?php echo $color ?>;"><?php echo $result->RCV_NAME;?></td>
		<td class="std" width="7%" style="text-align:right;background-color: <?php  echo   $backcolor ?>; color:<?php echo $color ?>;"><?php echo number_format($result->TOT_AMT,0,',','.');?></td>
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

		$whereClauses   = array("DEL_F = 0");

		if ($Accounting == 0) 
		{
			$whereClauses[] = "(ORD.IVC_TYP = 'RC')";
		}

		if ($Accounting == 1) 
		{
			$whereClauses[] = "(ORD.IVC_TYP = 'RC') AND ORD.TAX_APL_ID IN ('I', 'A')";
		}

		if ($Accounting == 2) 
		{
			$whereClauses[] = "(ORD.IVC_TYP = 'RC') AND ((ORD.TAX_APL_ID NOT IN ('I', 'A') AND SHP.TAX_F = 1) OR (ORD.TAX_APL_ID NOT IN ('I', 'A') AND RCV.TAX_F = 1))";	
		}

		if ($Accounting == 3) 
		{
			$whereClauses[] = "(ORD.IVC_TYP = 'RC') AND ((ORD.TAX_APL_ID NOT IN ('I', 'A') AND SHP.TAX_F = 0 ) OR (ORD.TAX_APL_ID NOT IN ('I', 'A') AND RCV.TAX_F = 0))";
		}

		$whereClauses = implode(" AND ", $whereClauses);

		if($val != '')
		{
			$queryhutdag  = " SELECT SUM(TOT_AMT) AS TOT_AMT,
									 SUM(PYMT_DOWN) AS PYMT_DOWN,
									 SUM(PYMT_REM) AS PYMT_REM
					 	 	  FROM RTL.RTL_STK_HEAD ORD
					 	 	  LEFT JOIN CMP.COMPANY SHP ON ORD.SHP_CO_NBR  = SHP.CO_NBR
 							  LEFT JOIN CMP.COMPANY RCV ON ORD.RCV_CO_NBR  = RCV.CO_NBR
					   		  WHERE " . $whereClauses . " AND SHP_CO_NBR = '".$val."' AND ORD_DTE <= '".$_GET['END_DT']."' ";
			// echo $queryhutdag;
			$resulthutdag    = mysql_query($queryhutdag);
			$rowhutdag 		 = mysql_fetch_array($resulthutdag);

			$totalnotpem 	 = $rowhutdag['TOT_AMT'] 	+ 0;	
			$totaluangmuk 	 = $rowhutdag['PYMT_DOWN']  + 0;	
			$totalpelun 	 = $rowhutdag['PYMT_REM'] 	+ 0;	
			$totalhutdag     = $totalnotpem - $totaluangmuk - $totalpelun;
		}
		else
		{
			$queryhutdag   = " SELECT SUM(TOT_AMT) AS TOT_AMT,
										 SUM(PYMT_DOWN) AS PYMT_DOWN,
										 SUM(PYMT_REM) AS PYMT_REM
						 	   FROM RTL.RTL_STK_HEAD ORD 
						 	   LEFT JOIN CMP.COMPANY SHP ON ORD.SHP_CO_NBR  = SHP.CO_NBR
							   LEFT JOIN CMP.COMPANY RCV ON ORD.RCV_CO_NBR  = RCV.CO_NBR
						   	   WHERE " . $whereClauses . "  
						   	   AND ORD_DTE <= '".$_GET['END_DT']."' ";
			// echo $queryhutdag;
			$resulthutdag 	 = mysql_query($queryhutdag);
			$rowhutdag 	  	 = mysql_fetch_array($resulthutdag);

			$totalnotpem 	 = $rowhutdag['TOT_AMT'] 	+ 0;	
			$totaluangmuk 	 = $rowhutdag['PYMT_DOWN']  + 0;	
			$totalpelun 	 = $rowhutdag['PYMT_REM'] 	+ 0;	
			$totalhutdag     = $totalnotpem - $totaluangmuk - $totalpelun;
		}
			
		?>

		<tr class='total'>
			<td style="font-weight:bold;color:#3464bc;padding-left:7px;width:150px;" colspan="2">Total Nota Pembelian</td>				
			<td style="font-weight:bold;color:#3464bc;padding-left:7px;width:50px;"  colspan="2"> : </td>	
			<td style="width:150px;">
			<input name="TOT_AMT" id="TOT_AMT" value="<?php echo number_format($totalnotpem,0,',','.');?>" type="text" style="margin:1px;width:100px;border:none;text-align:right;margin-left:5px;" readonly/>
			</td>
		</tr>

		<tr class='total'>
			<td style="font-weight:bold;color:#3464bc;padding-left:7px;width:150px;" colspan="2">Total Uang Muka</td>			
			<td style="font-weight:bold;color:#3464bc;padding-left:7px;width:50px;"  colspan="2"> : </td>	
			<td style="width:150px;">
			<input name="PYMT_DOWN" id="PYMT_DOWN" value="<?php echo number_format($totaluangmuk,0,',','.');?>" type="text" style="margin:1px;width:100px;border:none;text-align:right;margin-left:5px;" readonly/>
			</td>
		</tr>

		<tr class='total'>
			<td style="font-weight:bold;color:#3464bc;padding-left:7px;width:150px;" colspan="2">Total Pelunasan</td>
			<td style="font-weight:bold;color:#3464bc;padding-left:7px;width:50px;"  colspan="2"> : </td>		
			<td style="width:150px;">
			<input name="TOT_REM" id="TOT_REM" value="<?php echo number_format($totalpelun,0,',','.');?>" type="text" style="margin:1px;width:100px;border:none;text-align:right;margin-left:5px;" readonly/>
			</td>
		</tr>

		<tr class='total'>
			<td style="font-weight:bold;color:#3464bc;padding-left:7px;width:150px;" colspan="2">Hutang Dagang</td>		
			<td style="font-weight:bold;color:#3464bc;padding-left:7px;width:50px;"  colspan="2"> : </td>					
			<td style="width:150px;">
			<input name="TOT_REM" id="TOT_REM" value="<?php echo number_format($totalhutdag,0,',','.');?>" type="text" style="margin:1px;width:100px;border:none;text-align:right;margin-left:5px;" readonly/>
			</td>
		</tr>
	</table>
</div>


</form>



