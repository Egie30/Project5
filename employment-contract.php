<?php

	include "framework/functions/default.php"; /*NEW*/
	include "framework/database/connect-cloud.php";
	include "framework/security/default.php";
	

	// $number = 2.33;
	// echo floor( $number);


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

	if(($_GET['DEL_A']!="")&&(!$cloud))
	{
		echo "<script>window.scrollTo(0,0);parent.document.getElementById('offline').style.display='block';parent.document.getElementById('fade').style.display='block';</script>";			
	}	
?>

<div class="toolbar">
	<p class="toolbar-right"><span class='fa fa-search fa-flip-horizontal toolbar' style='cursor:default'></span><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">
	<table id="mainTable" class="tablesorter" style="width:100%;">
		<thead>
			<tr>
				<th style="text-align:right;width:8%;">ID Karyawan</th>
				<th>Nama</th>
				<th>Perusahaan</th>
				<th>Jenis Kontrak Terakhir</th>
				<th>Tanggal Kontrak Terakhir</th>
			</tr>
		</thead>
		<tbody>
		<?php
			$query  = "SELECT PPL.PRSN_NBR,
							  PPL.NAME,
							  CNTRCT_TYP.EMPL_CNTRCT_DESC AS EMPL_CNTRCT_DESC,
							  COM.NAME AS COMPANY,
							  CNTRCT.CONTRACTBEGDATE AS CONTRACTBEGDATE,
							  CNTRCT.CONTRACTENDDATE AS CONTRACTENDDATE
					   FROM  $CMP.PEOPLE PPL
					   INNER JOIN $CMP.POS_TYP POS ON PPL.POS_TYP = POS.POS_TYP
					   LEFT  JOIN $CMP.COMPANY COM ON PPL.CO_NBR = COM.CO_NBR
					   LEFT  JOIN $CMP.EMPL_CNTRCT_TYP CNTRCT_TYP ON CNTRCT_TYP.EMPL_CNTRCT_TYP = PPL.EMPL_CNTRCT
					   LEFT  JOIN (SELECT PRSN_NBR,MAX(BEG_DTE) AS CONTRACTBEGDATE, MAX(END_DTE) AS CONTRACTENDDATE FROM $CMP.EMPL_CNTRCT GROUP BY PRSN_NBR ORDER BY BEG_DTE ASC) CNTRCT ON
					   CNTRCT.PRSN_NBR =  PPL.PRSN_NBR
					   WHERE TERM_DTE IS NULL AND PPL.DEL_NBR = 0 AND PPL.EMPL_CNTRCT > 0 
					   ORDER BY CNTRCT.CONTRACTENDDATE ASC";
			 //echo '<pre>'.$query.'</pre>';
			$result = mysql_query($query,$cloud);

			$alt = "";
			
			while($row = mysql_fetch_array($result))
			{
				$now = date("Y-m-d");
				// echo $now
				$endday = $row['CONTRACTENDDATE'];

				if($now >= date('Y-m-d',(strtotime ( '-30 day' , strtotime ($endday)))) && $now <= date('Y-m-d',(strtotime ('-15 day' , strtotime ($endday))))) 
				{
					 $color = "<div class='print-digital-green'>";
				} 
				else if ($now >= date('Y-m-d',(strtotime ( '-14 day' , strtotime ($endday)))) && $now <= date('Y-m-d',(strtotime ( '-1 day' , strtotime ($endday))))) 
				{
					 $color = "<div class='print-digital-yellow'>";
				}

				if ($now <=  date('Y-m-d',(strtotime ( '-30 day' , strtotime ($endday))))) 
				{
					 $color = "";
				}
				else if ($now >=  date('Y-m-d',(strtotime ( '-1 day' , strtotime ($endday))))) 
				{
					 $color = "<div class='print-digital-red'>";
				}

				if ($row['CONTRACTENDDATE'] == '') 
				{
					$color = "";
					$contractdesc = "";
				}
				else
				{
					$contractdesc = $row['EMPL_CNTRCT_DESC'];;
				}


				echo "<tr $alt style = 'cursor:pointer;' onclick = ".chr(34)."location.href = 'employment-contract-edit.php?PRSN_NBR=".$row['PRSN_NBR']."';".chr(34).">";

				echo "<td style='text-align:right'>".$row['PRSN_NBR']."</td>";
				echo "<td>".$row['NAME']."</td>";
				echo "<td>".$row['COMPANY']."</td>";
				echo "<td>".$contractdesc."</td>";
				echo "<td style='text-align:center;'>$color".$row['CONTRACTENDDATE']."</div></td>";
				echo "</tr>";
				//if($alt==""){$alt="class='alt'";}else{$alt="";}
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

<script>liveReqInit('livesearch','liveRequestResults','employment-contract-ls.php','','mainResult');</script>

</body>
</html>


