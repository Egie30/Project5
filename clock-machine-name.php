<?php
	include "framework/database/connect.php";
	include "framework/functions/default.php";
	$PrsnNbr=$_GET['PRSN_NBR'];

	//Check for validity of scan
	if(LuhnVal($PrsnNbr)){
		//Need error checking here
		$PrsnNbr=substr($PrsnNbr,0,-1);
		$query="SELECT NAME FROM CMP.PEOPLE WHERE PRSN_NBR=".intval($PrsnNbr);
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		
		echo "<div class='scan-ok' style='width:200px;padding-left:10px;text-align:center;'><img src='address-person/showimg.php?PRSN_NBR=".$PrsnNbr."' style='border-radius:50% 50% 50% 50%;width:50px;height:50px;vertical-align:-120%'>&nbsp;&nbsp;<h3>".$row['NAME']."</h3> (Employee ID #".$PrsnNbr.")</div>";
		
		$queryCek  = "SELECT PRSN_NBR,CRT_TS,COUNT(CRT_TS)AS CNT_ATND FROM PAY.ATND_CLOK WHERE PRSN_NBR=".intval($PrsnNbr)." AND DATE(CRT_TS)=CURDATE() ORDER BY CRT_TS DESC LIMIT 1";
		$results = mysql_query($queryCek);
		$rows    = mysql_fetch_array($results);
		if ($rows['CNT_ATND'] % 2 ==1 || mysql_num_rows($results)<=0){
			$disIn  = "disabled";
			$colorI = "color:#6A6969;";

		 }else{
			$disOut = "disabled";
			$colorO = "color:#6A6969;";
		}
		
		echo "<form><div style='margin:10px 0px 0px 25px'>";
		echo "<input class='process submit_button' type='button' value='Clock In' style='".  $colorI ."' ". $disIn ." onClick='syncGetContent(".chr(34)."clock".chr(34).",".chr(34)."clock-machine-write.php?PRSN_NBR=".$PrsnNbr."&CLOK_TYP=I".chr(34).");'/>&nbsp;";
		echo "<input class='process submit_button' type='button' value='Clock Out' style='".  $colorO ."' ". $disOut ." onClick='syncGetContent(".chr(34)."clock".chr(34).",".chr(34)."clock-machine-write.php?PRSN_NBR=".$PrsnNbr."&CLOK_TYP=O".chr(34).");'/>&nbsp;";
		echo "<div></form>";
		echo "<div id='clock'></div>";
	}else{
		echo "<div class='scan-fail'>Employee ID tidak dikenal</div>";
	}
?>