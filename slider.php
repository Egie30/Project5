<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/alert/alert.php";
	$PrsnNbr=$_GET['PRSN_NBR'];
	$PymtDte=$_GET['PYMT_DTE'];
	$Security=getSecurity($_SESSION['userID'],"Payroll");

	if($_GET['DEL_S']!="")
	{
		$query="DELETE FROM CMP.SLIDER WHERE SLDR_NBR=".$_GET['DEL_S'];
	   	$result=mysql_query($query);	
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<script type="text/javascript" src='framework/tablesort/tablesort.js'></script>
<script type="text/javascript" src='framework/tablesort/customsort.js'></script>
	
</head>

<body>
<div class="toolbar">
	<p class="toolbar-left"><a href="slider-edit.php?SLDR_NBR=0"><img class="toolbar-left" src="img/add.png" onclick="location.href="></a></p>
	<p class="toolbar-right"><img class="toolbar-right" src="img/search.png"><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>


<div id="mainResult">
	<table id="mainTable" class="rowstyle-alt colstyle-alt no-arrow searchTable">
		<thead> 
			<tr>
				<th  class="sortable">ID</td>
				<th  class="sortable">Name</td>
				<th  class="sortable">File</td>
				<th  class="sortable">Status</td>
			</tr>
		</thead>
		<tbody>
		<?php
			$query="SELECT SLDR_NBR,SLDR_NAME,SLDR_IMG,SLDR_STAT FROM CMP.SLIDER ORDER BY SLDR_NBR";
			$result=mysql_query($query);
			while($row=mysql_fetch_array($result))
			{
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='slider-edit.php?SLDR_NBR=".$row['SLDR_NBR']."';".chr(34).">";
			    echo "<td align=center>".$row['SLDR_NBR']."</td>";
			    echo "<td>".$row['SLDR_NAME']."</td>";
			    echo "<td>".$row['SLDR_IMG']."</td>";
			    echo "<td align=center>";
			    if($row['SLDR_STAT']==1){
			    	echo "Aktif";
			    }else{
			    	echo "Non-aktif";
			    }
			    echo "</td>";
			    echo "</tr>";
			    if($alt==""){$alt="class='alt'";}else{$alt="";}
			}
		?>
		</tbody>
	</table>
</div>

</body>
</html>