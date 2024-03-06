<?php
	include "framework/database/connect.php";
	$WhseNbr=$_GET['WHSE_NBR'];

	$query="SELECT STA.STA_NBR,CONCAT(STA.NAME,' ',COLR_DESC,' ',MATR,' ',SIZE,' ',TYPE) AS NAME_DESC,STA_TYP_DESC,
			SUM(CASE WHEN MOV_DTE<DATE_SUB(CURDATE(),INTERVAL 7 DAY) AND MOV_TYP='OUT' AND WHSE_NBR=".$WhseNbr." THEN -1*MOV_CNT ELSE 0 END)+SUM(CASE WHEN MOV_DTE<DATE_SUB(CURDATE(),INTERVAL 7 DAY) AND MOV_TYP='IN' AND WHSE_NBR=".$WhseNbr." THEN MOV_CNT ELSE 0 END) AS BEG_VAL,
			SUM(CASE WHEN MOV_DTE=DATE_SUB(CURDATE(),INTERVAL 7 DAY) AND MOV_TYP='IN'  AND WHSE_NBR=".$WhseNbr." THEN LOG_NBR ELSE 0 END) AS IN_NBR1,SUM(CASE WHEN MOV_DTE=DATE_SUB(CURDATE(),INTERVAL 7 DAY) AND MOV_TYP='IN'  AND WHSE_NBR=".$WhseNbr." THEN MOV_CNT ELSE 0 END) AS IN_VAL1,
			SUM(CASE WHEN MOV_DTE=DATE_SUB(CURDATE(),INTERVAL 6 DAY) AND MOV_TYP='IN'  AND WHSE_NBR=".$WhseNbr." THEN LOG_NBR ELSE 0 END) AS IN_NBR2,SUM(CASE WHEN MOV_DTE=DATE_SUB(CURDATE(),INTERVAL 6 DAY) AND MOV_TYP='IN'  AND WHSE_NBR=".$WhseNbr." THEN MOV_CNT ELSE 0 END) AS IN_VAL2,
			SUM(CASE WHEN MOV_DTE=DATE_SUB(CURDATE(),INTERVAL 5 DAY) AND MOV_TYP='IN'  AND WHSE_NBR=".$WhseNbr." THEN LOG_NBR ELSE 0 END) AS IN_NBR3,SUM(CASE WHEN MOV_DTE=DATE_SUB(CURDATE(),INTERVAL 5 DAY) AND MOV_TYP='IN'  AND WHSE_NBR=".$WhseNbr." THEN MOV_CNT ELSE 0 END) AS IN_VAL3,
			SUM(CASE WHEN MOV_DTE=DATE_SUB(CURDATE(),INTERVAL 4 DAY) AND MOV_TYP='IN'  AND WHSE_NBR=".$WhseNbr." THEN LOG_NBR ELSE 0 END) AS IN_NBR4,SUM(CASE WHEN MOV_DTE=DATE_SUB(CURDATE(),INTERVAL 4 DAY) AND MOV_TYP='IN'  AND WHSE_NBR=".$WhseNbr." THEN MOV_CNT ELSE 0 END) AS IN_VAL4,
			SUM(CASE WHEN MOV_DTE=DATE_SUB(CURDATE(),INTERVAL 3 DAY) AND MOV_TYP='IN'  AND WHSE_NBR=".$WhseNbr." THEN LOG_NBR ELSE 0 END) AS IN_NBR5,SUM(CASE WHEN MOV_DTE=DATE_SUB(CURDATE(),INTERVAL 3 DAY) AND MOV_TYP='IN'  AND WHSE_NBR=".$WhseNbr." THEN MOV_CNT ELSE 0 END) AS IN_VAL5,
			SUM(CASE WHEN MOV_DTE=DATE_SUB(CURDATE(),INTERVAL 2 DAY) AND MOV_TYP='IN'  AND WHSE_NBR=".$WhseNbr." THEN LOG_NBR ELSE 0 END) AS IN_NBR6,SUM(CASE WHEN MOV_DTE=DATE_SUB(CURDATE(),INTERVAL 2 DAY) AND MOV_TYP='IN'  AND WHSE_NBR=".$WhseNbr." THEN MOV_CNT ELSE 0 END) AS IN_VAL6,
			SUM(CASE WHEN MOV_DTE=DATE_SUB(CURDATE(),INTERVAL 1 DAY) AND MOV_TYP='IN'  AND WHSE_NBR=".$WhseNbr." THEN LOG_NBR ELSE 0 END) AS IN_NBR7,SUM(CASE WHEN MOV_DTE=DATE_SUB(CURDATE(),INTERVAL 1 DAY) AND MOV_TYP='IN'  AND WHSE_NBR=".$WhseNbr." THEN MOV_CNT ELSE 0 END) AS IN_VAL7,
			SUM(CASE WHEN MOV_DTE=DATE_SUB(CURDATE(),INTERVAL 7 DAY) AND MOV_TYP='OUT' AND WHSE_NBR=".$WhseNbr." THEN LOG_NBR ELSE 0 END) AS OT_NBR1,SUM(CASE WHEN MOV_DTE=DATE_SUB(CURDATE(),INTERVAL 7 DAY) AND MOV_TYP='OUT' AND WHSE_NBR=".$WhseNbr." THEN MOV_CNT ELSE 0 END) AS OT_VAL1,
			SUM(CASE WHEN MOV_DTE=DATE_SUB(CURDATE(),INTERVAL 6 DAY) AND MOV_TYP='OUT' AND WHSE_NBR=".$WhseNbr." THEN LOG_NBR ELSE 0 END) AS OT_NBR2,SUM(CASE WHEN MOV_DTE=DATE_SUB(CURDATE(),INTERVAL 6 DAY) AND MOV_TYP='OUT' AND WHSE_NBR=".$WhseNbr." THEN MOV_CNT ELSE 0 END) AS OT_VAL2,
			SUM(CASE WHEN MOV_DTE=DATE_SUB(CURDATE(),INTERVAL 5 DAY) AND MOV_TYP='OUT' AND WHSE_NBR=".$WhseNbr." THEN LOG_NBR ELSE 0 END) AS OT_NBR3,SUM(CASE WHEN MOV_DTE=DATE_SUB(CURDATE(),INTERVAL 5 DAY) AND MOV_TYP='OUT' AND WHSE_NBR=".$WhseNbr." THEN MOV_CNT ELSE 0 END) AS OT_VAL3,
			SUM(CASE WHEN MOV_DTE=DATE_SUB(CURDATE(),INTERVAL 4 DAY) AND MOV_TYP='OUT' AND WHSE_NBR=".$WhseNbr." THEN LOG_NBR ELSE 0 END) AS OT_NBR4,SUM(CASE WHEN MOV_DTE=DATE_SUB(CURDATE(),INTERVAL 4 DAY) AND MOV_TYP='OUT' AND WHSE_NBR=".$WhseNbr." THEN MOV_CNT ELSE 0 END) AS OT_VAL4,
			SUM(CASE WHEN MOV_DTE=DATE_SUB(CURDATE(),INTERVAL 3 DAY) AND MOV_TYP='OUT' AND WHSE_NBR=".$WhseNbr." THEN LOG_NBR ELSE 0 END) AS OT_NBR5,SUM(CASE WHEN MOV_DTE=DATE_SUB(CURDATE(),INTERVAL 3 DAY) AND MOV_TYP='OUT' AND WHSE_NBR=".$WhseNbr." THEN MOV_CNT ELSE 0 END) AS OT_VAL5,
			SUM(CASE WHEN MOV_DTE=DATE_SUB(CURDATE(),INTERVAL 2 DAY) AND MOV_TYP='OUT' AND WHSE_NBR=".$WhseNbr." THEN LOG_NBR ELSE 0 END) AS OT_NBR6,SUM(CASE WHEN MOV_DTE=DATE_SUB(CURDATE(),INTERVAL 2 DAY) AND MOV_TYP='OUT' AND WHSE_NBR=".$WhseNbr." THEN MOV_CNT ELSE 0 END) AS OT_VAL6,
			SUM(CASE WHEN MOV_DTE=DATE_SUB(CURDATE(),INTERVAL 1 DAY) AND MOV_TYP='OUT' AND WHSE_NBR=".$WhseNbr." THEN LOG_NBR ELSE 0 END) AS OT_NBR7,SUM(CASE WHEN MOV_DTE=DATE_SUB(CURDATE(),INTERVAL 1 DAY) AND MOV_TYP='OUT' AND WHSE_NBR=".$WhseNbr." THEN MOV_CNT ELSE 0 END) AS OT_VAL7,
			SUM(CASE WHEN MOV_DTE<CURRENT_DATE AND MOV_TYP='OUT' AND WHSE_NBR=".$WhseNbr." THEN -1*MOV_CNT ELSE 0 END)+SUM(CASE WHEN MOV_DTE<CURRENT_DATE AND MOV_TYP='IN' AND WHSE_NBR=".$WhseNbr." THEN MOV_CNT ELSE 0 END) AS END_VAL
			FROM CMP.STATIONERY STA LEFT OUTER JOIN CMP.STA_LOG LOG ON LOG.STA_NBR=STA.STA_NBR INNER JOIN CMP.COMPANY CMP ON STA.CO_NBR=CMP.CO_NBR INNER JOIN CMP.STA_TYP TYP ON STA.STA_TYP=TYP.STA_TYP INNER JOIN CMP.STA_COLR CLR ON STA.COLR_NBR=CLR.COLR_NBR
			GROUP BY STA.STA_NBR,STA_TYP_DESC
			ORDER BY 3,2";
	//echo "<pre>".$query."</pre>";
	$result=mysql_query($query);
	
	$title="";
	
	echo "<table style='width:100%'>";
	
	while($row=mysql_fetch_array($result))
	{
		if($title!=strtoupper(substr($row['NAME_DESC'],0,1)))
		{
			$title=strtoupper(substr($row['NAME_DESC'],0,1));	
			$type=$row['STA_TYP_DESC'];
		
			echo "<tr class='listable'><td class='listable' colspan='18' style='padding-top:10px;border-left:0px'><strong>".$type." ".$title."</strong></td></tr>";
		
			$rowcol="a";

			echo "<tr class='listable'>";
			echo "<th class='listable' rowspan='2'>Kode</th>";
			echo "<th class='listable' rowspan='2'>Deskripsi</th>";
			echo "<th class='listable' rowspan='2'>Awal</th>";
			echo "<th class='listable' colspan='7' style='border-bottom-width:1px'>Masuk</th>";
			echo "<th class='listable' colspan='7' style='border-bottom-width:1px'>Keluar</th>";
			echo "<th class='listable' rowspan='2'>Akhir</th>";
			echo "</tr>";

			echo "<tr class='listable'>";
			echo "<th class='listable'>".sprintf("%02d",date("d",time()-86400*7))."</th>";
			echo "<th class='listable'>".sprintf("%02d",date("d",time()-86400*6))."</th>";
			echo "<th class='listable'>".sprintf("%02d",date("d",time()-86400*5))."</th>";
			echo "<th class='listable'>".sprintf("%02d",date("d",time()-86400*4))."</th>";
			echo "<th class='listable'>".sprintf("%02d",date("d",time()-86400*3))."</th>";
			echo "<th class='listable'>".sprintf("%02d",date("d",time()-86400*2))."</th>";
			echo "<th class='listable'>".sprintf("%02d",date("d",time()-86400*1))."</th>";
			echo "<th class='listable'>".sprintf("%02d",date("d",time()-86400*7))."</th>";
			echo "<th class='listable'>".sprintf("%02d",date("d",time()-86400*6))."</th>";
			echo "<th class='listable'>".sprintf("%02d",date("d",time()-86400*5))."</th>";
			echo "<th class='listable'>".sprintf("%02d",date("d",time()-86400*4))."</th>";
			echo "<th class='listable'>".sprintf("%02d",date("d",time()-86400*3))."</th>";
			echo "<th class='listable'>".sprintf("%02d",date("d",time()-86400*2))."</th>";
			echo "<th class='listable'>".sprintf("%02d",date("d",time()-86400*1))."</th>";
			echo "</tr>";
		}
	
		echo "<tr class='list-row-".$rowcol."'>";
		echo "<td style='align:center'>".$row['STA_NBR']."</a></td>";
		echo "<td>".$row['NAME_DESC']."</td>";
		echo "<td align='right'>".number_format($row['BEG_VAL'],0,",",".")."</td>";
		dispInv($row['IN_NBR1'],$row['IN_VAL1']);
		dispInv($row['IN_NBR2'],$row['IN_VAL2']);
		dispInv($row['IN_NBR3'],$row['IN_VAL3']);
		dispInv($row['IN_NBR4'],$row['IN_VAL4']);
		dispInv($row['IN_NBR5'],$row['IN_VAL5']);
		dispInv($row['IN_NBR6'],$row['IN_VAL6']);
		dispInv($row['IN_NBR7'],$row['IN_VAL7']);
		dispInv($row['OT_NBR1'],$row['OT_VAL1']);
		dispInv($row['OT_NBR2'],$row['OT_VAL2']);
		dispInv($row['OT_NBR3'],$row['OT_VAL3']);
		dispInv($row['OT_NBR4'],$row['OT_VAL4']);
		dispInv($row['OT_NBR5'],$row['OT_VAL5']);
		dispInv($row['OT_NBR6'],$row['OT_VAL6']);
		dispInv($row['OT_NBR7'],$row['OT_VAL7']);
		echo "<td style='align:right'>";
		echo number_format($row['END_VAL'],0,",",".")."</a></td>";	
		echo "</tr>";
		if($rowcol=="a"){$rowcol="b";}else{$rowcol="a";}
	}
	
	echo "</table>";
	
	function dispInv($ref,$val)
	{
		if($val==0){
			echo "<td class='sortable'></td>";
		}else{
			echo "<td class='sortable' align='right'><a href='stationery-edit.php?LOG_NBR=".$ref."'>".number_format($val,0,",",".")."</a></td>";
		}
	}
?>