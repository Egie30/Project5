<?php
	include "../framework/database/connect.php";
	include "../framework/functions/default.php";
	$PrsnNbr=$_GET['PRSN_NBR'];
	
	//Check for validity of scan
	if(LuhnVal($PrsnNbr)){
		//Need error checking here
		$PrsnNbr=substr($PrsnNbr,0,-1);
		$query="SELECT NAME FROM CMP.PEOPLE WHERE PRSN_NBR=".$PrsnNbr;
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		echo "<div class='scan-ok'><img src='../address-person/showimg.php?PRSN_NBR=".$PrsnNbr."' style='border-radius:50% 50% 50% 50%;width:50px;height:50px;vertical-align:-120%'>&nbsp;&nbsp;<b>".$row['NAME']."</b> (Employee ID #".$PrsnNbr.")</div>";
		echo "<div style='margin:10px 0px 0px 0px'>";
		echo "<input class='process' type='button' value='Clock In' onClick='syncGetContent(".chr(34)."clock".chr(34).",".chr(34)."clock-machine-write.php?PRSN_NBR=".$PrsnNbr."&CLOK_TYP=I".chr(34).");'/>&nbsp;";
		echo "<input class='process' type='button' value='Clock Out' onClick='syncGetContent(".chr(34)."clock".chr(34).",".chr(34)."clock-machine-write.php?PRSN_NBR=".$PrsnNbr."&CLOK_TYP=O".chr(34).");'/>&nbsp;";
		echo "<div>";
		echo "<div id='clock'></div>";
	}else{
		echo "<div class='scan-fail'>Employee ID tidak dikenal</div>";
	}
?>