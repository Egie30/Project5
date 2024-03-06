<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/security/default.php";
	
$SecurityAct 	= getSecurity($_SESSION['userID'],"Accounting");

$companyNumber	= $_GET['CO_NBR'];
$endDate		= $_GET['END_DT'];

$groupDetail	= $_GET['GROUP'];

$_GET['GROUP']	= 'PRN_DIG_TYP';
$groupType		= $_GET['GROUP'];

$searchQuery    = strtoupper($_REQUEST['s']);


try 
{
		
	ob_start();
	include __DIR__ . DIRECTORY_SEPARATOR . "ajax/sub-cat-ajax-contoh.php";

	$results = json_decode(ob_get_clean());
} 
 catch (\Exception $ex) 
{
	ob_end_clean();
}

if (count($results->data) == 0) 
{
	echo "<div class='searchStatus'>Data yang ingin ditampilkan tidak tersedia.</div>";
	die();
}
?>

<head>
			<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
			<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
			<link rel="stylesheet" type="text/css" media="screen" href="framework/combobox/chosen.css" />
			<link rel="stylesheet" type="text/css" media="screen" href="framework/tab/tabs.css" />
			<link rel="stylesheet" type="text/css" media="screen" href="framework/pagination/pagination.css" />
			<link rel="stylesheet" type="text/css" media="screen" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" />
			<link rel="stylesheet" type="text/css" media="screen" href="css/accounting.css" />
			<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
			<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
			<script type="text/javascript">parent.Pace.restart();</script>
			<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
			<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
			<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>
			<script type="text/javascript" src="framework/liveSearch/livesearch.js"></script>
			<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
			<script type="text/javascript" src="framework/tab/tabs.js"></script>
			<script type="text/javascript" src="framework/pagination/pagination.js"></script>
			<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
			<script type="text/javascript" src="framework/uri/src/URI.min.js"></script>
			<script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>
			<script type="text/javascript" src="framework/combobox/ajax-chosen/ajax-chosen.js"></script>
			<script type="text/javascript" src="framework/combobox/chosen.default.js"></script>
			<script type="text/javascript" src="framework/functions/default.js"></script>
			<script type="text/javascript" src='framework/jquery-freezeheader/js/jquery.freezeheader.js'></script>
            <script type="text/javascript" src='framework/tablesort/tablesort.js'></script>
			<script type="text/javascript">jQuery.noConflict();</script>
		</head>

<?php if(($locked==0) && ($_COOKIE["LOCK"] != "LOCK")) { ?>
	<br>
<?php } ?>	
        <div id="mainResult" >
		<table class="std-row-alt table-freeze rowstyle-alt colstyle-alt no-arrow searchTable" >
            <thead>
                <tr>
                <th class="sortable">Kode Inventory</th>
                <th class="sortable">Nama Inventory</th>
                <th class="sortable">Barcode</th>
                <th class="sortable">Sub Category</th>
                <th class="sortable">Suplier</th>
				<th class="sortable">Harga Beli</th>
				<th class="sortable">Harga Jual</th>
                <th class="sortable">Beli</th>
                <th class="sortable">Mutasi Masuk</th>
                <th class="sortable">Retur</th>
                <th class="sortable">Mutasi Keluar</th>
                <th class="sortable">Koreksi</th>
                <th class="sortable">Sales</th>
                <th class="sortable">Retail</th>
                <th class="sortable">Stock</th>

                </tr>
            </thead>


            <tbody>
			<?php foreach ($results->data as $data) {?>			
				<tr>
				    <td style="text-align:center;"><?php echo number_format($data->INV_NBR, 0, '.', ','); ?></td>
					<td style="text-align:left;white-space:nowrap;"><?php echo $data->INV_NAME; ?></td>
                    <td style="text-align:center;"><?php echo $data->BARCODE; ?></td>
                    <td style="text-align:left;"><?php echo $data->CAT_SUB_DESC; ?></td>
                    <td style="text-align:left;white-space:nowrap;"><?php echo $data->SUPLIER; ?></td>
					<td style="text-align:center;"><?php echo number_format($data->INV_PRC, 0, '.', ','); ?></td>
					<td style="text-align:center;"><?php echo number_format($data->PRC, 0, '.', ','); ?></td>
                    <td style="text-align:center;"><?php echo number_format($data->RCV_Q, 0, '.', ','); ?></td>
                    <td style="text-align:center;"><?php echo number_format($data->XF_IN_Q, 0, '.', ','); ?></td>
                    <td style="text-align:center;"><?php echo number_format($data->RTR_Q, 0, '.', ','); ?></td>
                    <td style="text-align:center;"><?php echo number_format($data->XF_OUT_Q, 0, '.', ','); ?></td>
                    <td style="text-align:center;"><?php echo number_format($data->COR_Q, 0, '.', ','); ?></td>
                    <td style="text-align:center;"><?php echo number_format($data->SLS_Q, 0, '.', ','); ?></td>
                    <td style="text-align:center;"><?php echo number_format($data->RTL_Q, 0, '.', ','); ?></td>
                    <td style="text-align:center;"><?php echo number_format($data->BALANCE_Q, 0, '.', ','); ?></td>

				</tr>
				<?php } ?>
		    </tbody>

            <tfoot>
                <tr>
                    <td style="text-align:right;font-weight:bold;" colspan="5">Total:</td>
					<td style="text-align:center;font-weight:bold;"><?php echo number_format($results->total->INV_PRC, 0, '.', ','); ?></td>
					<td style="text-align:center;font-weight:bold;"><?php echo number_format($results->total->PRC, 0, '.', ','); ?></td>
                    <td style="text-align:center;font-weight:bold;"><?php echo number_format($results->total->RCV_Q, 0, '.', ','); ?></td>
                    <td style="text-align:center;font-weight:bold;"><?php echo number_format($results->total->XF_IN_Q, 0, '.', ','); ?></td>
                    <td style="text-align:center;font-weight:bold;"><?php echo number_format($results->total->RTR_Q, 0, '.', ','); ?></td>
                    <td style="text-align:center;font-weight:bold;"><?php echo number_format($results->total->XF_OUT_Q, 0, '.', ','); ?></td>
                    <td style="text-align:center;font-weight:bold;"><?php echo number_format($results->total->COR_Q, 0, '.', ','); ?></td>
                    <td style="text-align:center;font-weight:bold;"><?php echo number_format($results->total->SLS_Q, 0, '.', ','); ?></td>
                    <td style="text-align:center;font-weight:bold;"><?php echo number_format($results->total->RTL_Q, 0, '.', ','); ?></td>
                    <td style="text-align:center;font-weight:bold;"><?php echo number_format($results->total->BALANCE_Q, 0, '.', ','); ?></td>

                </tr>
            </tfoot>
         </table>
</div>
</body>
    <?php
	buildPagination($results->pagination, "store-inventory-matter-ls-contoh.php"); 
	?>

