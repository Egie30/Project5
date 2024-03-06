<?php
	include "framework/database/connect.php";
	include "framework/functions/dotmatrix.php";
	include_once "framework/functions/default.php";
	include "framework/security/default.php";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<link rel="stylesheet" type="text/css" media="screen" href="css/orgchart.css" />
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>	
	<script type="text/javascript">
		google.load("visualization", "1", {packages:["orgchart"]});
		google.setOnLoadCallback(drawChart);
		function drawChart() {
		<?php
		$query_get	= "SELECT 
			GROUP_CONCAT(CO_NBR_CMPST) AS CO_NBR_CMPST, 
			CO_NBR_ORG,
			SORT 
		FROM NST.PARAM_PAYROLL 
		WHERE SORT<>'' 
		GROUP BY CO_NBR_ORG 
		ORDER BY SORT";
		$result_get	= mysql_query($query_get);
		$i=0;
		while ($row_get	= mysql_fetch_array($result_get)){
			$i++;
			$div="chart_div".$i;
			
		?>
		<?php 
			$script="";$attend=""; ?>
			var data = new google.visualization.DataTable();
			data.addColumn('string','Name');
			data.addColumn('string','Manager');
			data.addColumn('string','ToolTip');
			data.addRows([
			<?php
				$onDuty=0;
				$query="SELECT 
					PPL.PRSN_NBR,
					PPL.NAME,
					POS_DESC,
					MGR_NBR 
				FROM CMP.PEOPLE PPL 
				INNER JOIN CMP.POS_TYP POS ON PPL.POS_TYP=POS.POS_TYP  
				WHERE TERM_DTE IS NULL AND PPL.CO_NBR IN (".$row_get['CO_NBR_CMPST'].") AND PPL.DEL_NBR=0 
				GROUP BY PPL.PRSN_NBR,NAME,POS_DESC 
				ORDER BY 4,3,1";
				//echo $query;
				$result=mysql_query($query);
					
				while($row=mysql_fetch_array($result)){
					$script.="[{v:'".$row['PRSN_NBR']."', f:'".addslashes($row['NAME'])."<div style=".chr(34)."color:#888888;width:90px;font-size:8pt;overflow:hidden".chr(34).">".$row['POS_DESC']."</div>'}, '".$row['MGR_NBR']."', ''],";
					$onDuty++;
				}
				$script=substr($script,0,-1);
				echo $script;
			?>
			]);
			<?php echo $attend; ?>
			var chart = new google.visualization.OrgChart(document.getElementById('<?php echo $div; ?>'));
			chart.draw(data, {allowHtml:true,size:'medium',nodeClass:'defaultNode',selectedNodeClass:'defaultSelNode'});
		<?php } ?>
		}
	</script>
</head>
<body>
	<?php echo $debug; ?>
	<div id="chart_div1"></div><br>
	<hr style="height:1px;border:0px;border-bottom:1px #CCCCCC solid" /><br>
	<div id="chart_div2"></div><br>
	<hr style="height:1px;border:0px;border-bottom:1px #CCCCCC solid" /><br>
	<div id="chart_div3"></div><br>
	<!--
	<hr style="height:1px;border:0px;border-bottom:1px #CCCCCC solid" /><br>
	<div id="chart_div4"></div><br>
	<hr style="height:1px;border:0px;border-bottom:1px #CCCCCC solid" /><br>
	<div id="chart_div5"></div><br>
	<hr style="height:1px;border:0px;border-bottom:1px #CCCCCC solid" /><br>
	<div id="chart_div6"></div><br>
	-->
</body>
</html>