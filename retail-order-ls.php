<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	$IvcTyp	= $_GET['IVC_TYP'];
	$type	= $_GET['TYP'];
	
	if($type == "EST"){
		$headtable 	= "RTL.RTL_ORD_HEAD_EST";
		$detailtable= "RTL.RTL_ORD_DET_EST";
	}else{
		$headtable 	= "RTL.RTL_ORD_HEAD";
		$detailtable= "RTL.RTL_ORD_DET";
	}
	$searchQuery=trim(strtoupper(urldecode($_REQUEST[s])));

	$query="SELECT 
				HED.ORD_NBR,
				HED.ORD_DTE,
				HED.DL_TS,
				RCV_CO_NBR,
				RCV.NAME AS RCV_NAME,
				SHP_CO_NBR,
				SHP.NAME AS SHP_NAME,
				HED.ORD_STT_ID, 
				ORD_STT_DESC,
				TOT_AMT,
				TOT_REM
			FROM ". $headtable ." HED 
			INNER JOIN RTL.ORD_STT STT ON HED.ORD_STT_ID = STT.ORD_STT_ID 
			LEFT OUTER JOIN CMP.COMPANY RCV ON HED.RCV_CO_NBR = RCV.CO_NBR 
			LEFT OUTER JOIN CMP.COMPANY SHP ON HED.SHP_CO_NBR = SHP.CO_NBR 
			LEFT OUTER JOIN ". $detailtable ." DET ON HED.ORD_NBR = DET.ORD_NBR 
			WHERE (
				HED.ORD_NBR LIKE '%".$searchQuery."%' 
				OR RCV.NAME LIKE '%".$searchQuery."%' 
				OR ORD_STT_DESC LIKE '%".$searchQuery."%' 
			) AND DEL_F=0 
			GROUP BY HED.ORD_NBR,RCV_CO_NBR,SHP_CO_NBR,HED.ORD_STT_ID 
			ORDER BY DL_TS DESC";
	$result=mysql_query($query);
	
	if(mysql_num_rows($result)==0){
		echo "<div class='searchStatus'>Nama atau nomor yang dicari tidak ada didalam kumpulan data</div>";
		exit;
	}

	$firstRow	= "";
		while($row = mysql_fetch_array($result)){
			
			//Traffic light control
			$due		= strtotime($row['ORD_DTE']);
			$statusID	= $row['ORD_STT_ID'];
			if((strtotime("now")>$due) && ($statusID != "NE")){
				$dot="<div class='listable' style='display:inline;float:right'><span class='fa fa-circle listable' style='font-size:8pt;color:#d92115'></span></div>";
			}elseif((strtotime("now")>$due) && ($statusID != "NE")){
				$dot="<div class='listable' style='display:inline;float:right'><span class='fa fa-circle listable' style='font-size:8pt;color:#fbad06'></span></div>";				
			}else{
				$dot="";
			}
			
			//Perform changes in tranport-edit.php as well
			echo "<div id='O".($row['ORD_NBR'])."' class='tripane-list' onclick=".chr(34)."changeSiblingUrl('rightpane','retail-order-edit.php?ORD_NBR=".$row['ORD_NBR']."');selLeftPane(this);".chr(34);
			if($firstRow==""){
				echo "style='background-color:#eef8fb'";
			}
			echo ">";
			
			echo "<div  style='font-weight:bold;color:#666666;font-size:12pt;display:inline;float:left'>".$row['ORD_NBR']."</div>";
			echo "<div style='display:inline;float:right;'>".parseDateTimeLiteralShort($row['DL_TS'])."</div>";
			echo "<div style='clear:both'></div>";
			if(trim($row['RCV_NAME'])==""){$name="Tunai";}else{$name=trim($row['RCV_NAME']);}
			echo $dot;
			echo "<div style='font-weight:700;color:#3464bc'>".$name."</div>";
			echo "<div>".$row['ORD_TTL']."</div>";
			
			echo "<div>".$row['ORD_DTE']."&nbsp;";
			echo "<span style='font-weight:700'>".$row['ORD_STT_DESC']."</span>";
			echo "<span style='float:right;style='color:#888888'>";
			if($row['TOT_REM']==0){
                echo "<div class='listable' style='display:inline;float:left'><span class='fa fa-circle listable' style='font-size:8pt;color:#3464bc'></span></div>";
            }elseif($row['TOT_AMT']==$row['TOT_REM']){
                echo "<div class='listable' style='display:inline;float:left'><span class='fa fa-circle-o listable' style='font-size:8pt;color:#3464bc'></span></div>";
            }else{
                echo "<div class='listable' style='display:inline;float:left'><span class='fa fa-dot-circle-o listable' style='font-size:8pt;color:#3464bc'></span></div>";
            }
			echo "&nbsp;Rp. ".number_format($row['TOT_AMT'],0,',','.');
			echo "</span></div></div>";
			if($firstRow==""){$firstRow=$row['ORD_NBR'];}
		}
?>

