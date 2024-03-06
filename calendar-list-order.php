<?php
	include "framework/functions/default.php"; /*NEW*/
	include "framework/database/connect.php";
	$ordTyp=$_GET['ORD_TYP'];
	
	//Process delete entry
	if(isset($_GET['DEL'])!="")
	{
		$query="DELETE FROM CMP.CAL_ORD_HEAD WHERE ORD_NBR=".$_GET['DEL'];
		$result=mysql_query($query);
		
		$query="DELETE FROM CMP.CAL_ORD_DET WHERE ORD_NBR=".$_GET['DEL'];
		$result=mysql_query($query);
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<script type="text/javascript" src='framework/liveSearch/livesearch.js'></script>
<script type="text/javascript" src='framework/tablesort/tablesort.js'></script>

</head>

<body>

<?php
	if(($_GET['DEL_A']!="")&&(!$cloud)){
		echo "<script>window.scrollTo(0,0);parent.document.getElementById('offline').style.display='block';parent.document.getElementById('fade').style.display='block';</script>";			
	}	
?>

<div class="toolbar">
	<p class="toolbar-left"><?php if(paramCloud()==1){echo '<a href="calendar-edit.php?PRSN_NBR=0&ORD_TYP='.$ordTyp.'"><img class="toolbar-left" src="img/add.png" onclick="location.href="></a>';} ?></p>
	<p class="toolbar-right"><img class="toolbar-right" src="img/search.png"><input type="text" id="livesearch" class="livesearch" /></p>
</div>

<div class="searchresult" id="liveRequestResults"></div>

<div id="mainResult">
	<table id="mainTable" class="rowstyle-alt colstyle-alt no-arrow searchTable">
		<thead>
			<tr>
				<th class="sortable">No</th>
				<th class="sortable">Tanggal</th>
				<th class="sortable">No. Referensi</th>
				<?php if (($ordTyp=="REQ")||($ordTyp=="INV")){?>
				<th class="sortable">Judul</th>
				<?php }else{?>
				<th class="sortable">Penjual</th>
				<?php }?>
				<th class="sortable">Pembeli</th>
				<th class="sortable" style="border-right:0px;">Total</th>
			</tr>
		</thead>
		<tbody>
		<?php
			if (($ordTyp=="REQ")||($ordTyp=="INV")){
				$query="SELECT 
					ORD_TYP,
					HED.REF_NBR, 
					HED.ORD_NBR,
					DATE_FORMAT(ORD_DTE,'%d-%m-%y') AS ORD_DTE,
					ORD_TTL,CASE WHEN NAME IS NULL THEN 'Tunai' ELSE NAME END AS NAME,
					SUM(CASE WHEN CAL_TYP='ST' THEN ORD_Q ELSE 0 END) AS ST,
					SUM(CASE WHEN CAL_TYP='TR' THEN ORD_Q ELSE 0 END) AS TR,
					SUM(CASE WHEN CAL_TYP='CW' THEN ORD_Q ELSE 0 END) AS CW,
					SUM(CASE WHEN CAL_TYP='TH' THEN ORD_Q ELSE 0 END) AS TH,
					SUM(CASE WHEN CAL_TYP='KK' THEN ORD_Q ELSE 0 END) AS KK,
					SUM(CASE WHEN CAL_TYP NOT IN ('ST','TR','CQ','TH','KK') THEN ORD_Q ELSE 0 END) AS LL,
					TOT_AMT
				FROM CMP.CAL_ORD_HEAD HED 
					LEFT OUTER JOIN CMP.CAL_ORD_DET AS DET ON HED.ORD_NBR=DET.ORD_NBR 
					LEFT OUTER JOIN CMP.CAL_LST LST ON DET.CAL_NBR=LST.CAL_NBR 
					LEFT OUTER JOIN CMP.COMPANY COM ON HED.BUY_CO_NBR=COM.CO_NBR
				WHERE ORD_TYP='".$ordTyp."' #AND ORD_DTE BETWEEN ".getFiscalYear()."
				GROUP BY HED.ORD_NBR,ORD_DTE,ORD_TTL,CASE WHEN NAME IS NULL THEN 'Tunai' ELSE NAME END,TOT_AMT
				ORDER BY HED.UPD_DTE DESC LIMIT 0,100";
			}else{
			$query="SELECT 
					ORD_TYP,
					HED.REF_NBR, 
					HED.ORD_NBR,
					DATE_FORMAT(ORD_DTE,'%d-%m-%Y') AS ORD_DTE,
					CASE WHEN CMS.NAME IS NULL THEN 'Tunai' ELSE CMS.NAME END AS SEL_NAME,
					CASE WHEN CMB.NAME IS NULL THEN 'Tunai' ELSE CMB.NAME END AS BUY_NAME,
					SUM(CASE WHEN CAL_TYP='ST' THEN ORD_Q ELSE 0 END) AS ST,
					SUM(CASE WHEN CAL_TYP='TR' THEN ORD_Q ELSE 0 END) AS TR,
					SUM(CASE WHEN CAL_TYP='CW' THEN ORD_Q ELSE 0 END) AS CW,
					SUM(CASE WHEN CAL_TYP='TH' THEN ORD_Q ELSE 0 END) AS TH,
					SUM(CASE WHEN CAL_TYP='KK' THEN ORD_Q ELSE 0 END) AS KK,
					SUM(CASE WHEN CAL_TYP NOT IN ('ST','TR','CQ','TH','KK') THEN ORD_Q ELSE 0 END) AS LL,
					TOT_AMT
				FROM CMP.CAL_ORD_HEAD HED 
					LEFT OUTER JOIN CMP.CAL_ORD_DET AS DET ON HED.ORD_NBR=DET.ORD_NBR 
					LEFT OUTER JOIN CMP.CAL_LST LST ON DET.CAL_NBR=LST.CAL_NBR 
					LEFT OUTER JOIN CMP.COMPANY CMS ON HED.SEL_CO_NBR=CMS.CO_NBR 
					LEFT OUTER JOIN CMP.COMPANY CMB ON HED.BUY_CO_NBR=CMB.CO_NBR
				WHERE ORD_TYP='".$ordTyp."' #AND ORD_DTE BETWEEN ".getFiscalYear()."
				GROUP BY HED.ORD_NBR,ORD_DTE,ORD_TTL,CASE WHEN CMS.NAME IS NULL THEN 'Tunai' ELSE CMS.NAME END,TOT_AMT
				ORDER BY HED.UPD_DTE DESC LIMIT 0,100";
			}
			//echo "<pre>".$query;
			$result=mysql_query($query);
			$alt="";
			while($row=mysql_fetch_array($result))
			{	
				
				echo "<tr $alt style='cursor:pointer;' onclick=".chr(34)."location.href='calendar-edit.php?ORD_NBR=".$row['ORD_NBR']."&ORD_TYP=".$row['ORD_TYP']."';".chr(34).">";
				echo "<td style='text-align:center'>".$row['ORD_NBR']."</td>";
				echo "<td style='text-align:center'>".$row['ORD_DTE']."</td>";
				echo "<td style='text-align:center'>".$row['REF_NBR']."</td>";
				if (($ordTyp=="REQ")||($ordTyp=="INV")){
				echo "<td>".$row['ORD_TTL']."</td>";
				echo "<td>".$row['NAME']."</td>";
				}else{
				echo "<td>".$row['SEL_NAME']."</td>";
				echo "<td>".$row['BUY_NAME']."</td>";
				}
				echo "<td style='text-align:right'>".number_format($row['TOT_AMT'],0,",",".")."</td>";
				echo "</tr>";
				if($alt==""){$alt="class='alt'";}else{$alt="";}
			}
		?>
		</tbody>
	</table>
</div>
<script>liveReqInit('livesearch','liveRequestResults','calendar-list-order-ls.php','','mainResult');</script>
<script>fdTableSort.init();</script>
</body>
</html>


