<?php
require_once "framework/database/connect.php";
require_once "framework/functions/default.php";
require_once "framework/pagination/pagination.php";
?>

<html>

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
</head>

<body>
<?php if ($_GET['TYP'] == 'CAD') { ?>
	<table>
		<tr>
			<td colspan="4" style="font-weight:bold;">Saran Setoran Bank</td>
		</tr>		
		<tr>
			<td >Rekening 1</td><td><input name="TOT_AMT_PT" id="TOT_AMT_PT" type="text"> </td>
			<td >Rekening 2</td><td><input name="TOT_AMT_CV" id="TOT_AMT_CV" type="text"> </td>
			<td >Rekening 3</td><td><input name="TOT_AMT_PR" id="TOT_AMT_PR" type="text"> </td>
			<td >Rekening 4</td><td><input name="TOT_AMT_AD" id="TOT_AMT_AD" type="text"> </td>
			<td >
				<a href="refresh-deposit.php"><i class="fa fa-refresh"></i>
			</td>
		</tr>
	</table>

<div class="toolbar-only">
</div>
<?php } ?>	
		<script type="text/javascript">
			$(document).ready(function () {
					
					$.ajax({
						type: "GET",
						url: 'get-deposit.php',
						data: 'CO_NBR=<?php echo $CoNbrDef; ?>',
						success: function (data) {								
							var json = $.parseJSON(data);										
												
							//alert(json);
							
							$('#OMSET_PT').val(json.OMSET_PT);
							$('#OMSET_CV').val(json.OMSET_CV);
							$('#OMSET_PR').val(json.OMSET_PR);
							$('#OMSET_AD').val(json.OMSET_AD);
							
							$('#TOT_AMT_PT').val(json.PT);
							$('#TOT_AMT_CV').val(json.CV);
							$('#TOT_AMT_PR').val(json.PR);
							$('#TOT_AMT_AD').val(json.AD);
						}
					})
				
			});
		</script>
</body>
</html>