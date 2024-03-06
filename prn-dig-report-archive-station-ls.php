<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";

$query = "SELECT NBR FROM CDW.PRN_DIG_TOP_CUST";
$result = mysql_query($query);
while($row=mysql_fetch_array($result)){
	$TopCusts[]=strval($row['NBR']);
}

try {
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "ajax/prn-dig-order-station.php";

	$results = json_decode(ob_get_clean());
} catch (\Exception $ex) {
	ob_end_clean();
}
//echo "<pre>";print_r($results);
if (count($results->data) == 0) {
	echo "<div class='searchStatus'>Data yang ingin ditampilkan tidak tersedia.</div>";
	die();
}		
?>
<table id="mainTable" class="tablesorter searchTable">
	<thead>
		<tr style="text-align:center">
			<th class='sortable'>No</th>
			<th class="nosort"></th>
			<th class='sortable'>Tgl Nota</th>
			<th class='sortable'>Judul Nota</th>
			<th class='sortable'>Customer</th>
			<th class="sortable">Status</th>
			<th class="sortable">Janji</th>
			<th class="sortable">Jadi</th>
			<th class='sortable'>Total Nota</th>		
			<th class='sortable'>Sisa</th>		
		</tr>
	</thead>
	<tbody>
		<?php
		$i = $_GET['page'] > 1 ? ($_GET['page'] - 1) * $_GET['LIMIT'] : 0;
		$i++;
		$alt="";
		foreach ($results->data as $result) {
			
			$dueDate	= strtotime($result->DUE_TS);
			$OrdSttId	= $result->ORD_STT_ID;
			if((strtotime("now")>$dueDate)&&(($OrdSttId=="NE")||($OrdSttId=="RC")||($OrdSttId=="QU")||($OrdSttId=="PR")||($OrdSttId=="FN"))){
				$back	= "print-digital-red";
			}elseif((strtotime("now + ".$result->JOB_LEN_TOT." minute")>$dueDate)&&(($OrdSttId=="NE")||($OrdSttId=="RC")||($OrdSttId=="QU")||($OrdSttId=="PR")||($OrdSttId=="FN"))){
				$back	= "print-digital-yellow";				
			}else{
				$back	= "";
			}
		?>
		<tr style="cursor:pointer;" <?php echo $alt; ?> onclick="location.href='print-digital-edit.php?ORD_NBR=<?php echo $result->ORD_NBR;?>'">
			<td class="std" style="text-align:right"><?php echo $result->ORD_NBR;?></td>
			<td class="std">
			<?php
				if(in_array($result->BUY_CO_NBR,$TopCusts)){
					echo "<div class='listable'><span class='fa fa-star listable'></span></div>";
				}				
				if($result->SPC_NTE!=""){
					echo "<div class='listable'><span class='fa fa-comment listable'></span></div>";
				}
				if($result->DL_CNT > 0){
					echo "<div class='listable'><span class='fa fa-truck listable' style='margin-left:-1px'></span></div>";
				}
				if($result->PU_CNT > 0){
					echo "<div class='listable'><span class='fa fa-shopping-cart listable'></span></div>";
				}
				if($result->NS_CNT > 0){
					echo "<div class='listable'><span class='fa fa-flag listable'></span></div>";
				}
				if($result->IVC_PRN_CNT > 0){
					echo "<div class='listable'><span class='fa fa-print listable'></span></div>";
				}
			?>
			</td>			
			<td class="std" style="text-align:left;white-space:nowrap"><?php echo $result->ORD_DTE;?></td>
			<td class="std" style="text-align:left;white-space:nowrap"><?php echo $result->ORD_TTL;?></td>
			<td class="std" style="text-align:left;white-space:nowrap"><?php echo $result->BUY_NAME;?></td>
			<td class="std" style="text-align:center;white-space:nowrap"><?php echo $result->ORD_STT_DESC;?></td>
			<td class="std" style="text-align:center;white-space:nowrap">
				<div class='<?php echo $back; ?>'><?php echo parseDateShort($result->DUE_TS)." ".parseHour($result->DUE_TS).":".parseMinute($result->DUE_TS);?></div>
			</td>
			<td class="std" style="text-align:center;white-space:nowrap"><?php echo $result->CMP_DTE;?></td>
			<td class="std" style="text-align:right"><?php echo number_format($result->TOT_AMT,0,',','.');?></td>
			<td class="std" style="text-align:right"><?php echo number_format($result->TOT_REM,0,',','.');?></td>
		</tr>
			
		<?php
		$i++;
		}
		?>
	</tbody>
</table>
	
<?php buildPagination($results->pagination, "prn-dig-report-archive-station-ls.php"); ?>
