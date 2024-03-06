<?php
	//Lightbox Alert Generator
	//$object: div object name
	//$message: alert message
	//$icon: "info" or "stop"
	//$yesURL: URL if yes is pressed
	function createAlert($object,$title,$message,$icon="info")
	{
		echo "<div id='".$object."' class='white_content'>";
		echo "<table class='alert'><tr class='alert'>";
		//echo "<td class='alert' width='80'><img class='alert' src='framework/alert/".$icon.".png' style='vertical-align:top;'></td>";
		echo "<td class='alert' width='80'><span class='fa fa-info-circle fa-5x' style='vertical-align:top;'></span></td>";
		echo "<td class='alert'><span class='alert-title'>$title </span><br /><br /> $message<br/>";
		echo "</td>";
		echo "</tr></table>";
		echo "<input type='button' class='alert' id='".$object."Yes' value='Ya'> ";
		echo "<input type='button' class='alert' value='Tidak' title='' onclick=".chr(34)."document.getElementById('".$object."').style.display='none';document.getElementById('fade').style.display='none'".chr(34).">";
		echo "</div>";
	}

	//Lightbox Stop Generator
	//$object: div object name
	//$message: alert message
	//$icon: "info" or "stop"
	function createStop($object,$title,$message,$icon="stop")
	{
		echo "<div id='".$object."' class='white_content'>";
		echo "<table class='alert'><tr class='alert'>";
		echo "<td class='alert' width='80'><span class='fa fa-times-circle fa-5x' style='vertical-align:top;'></span></td>";
		echo "<td class='alert'><span class='alert-title'>$title </span><br /><br /> $message<br/>";
		echo "</td>";
		echo "</tr></table>";
		echo "<input type='button' class='alert' value='Stop' onclick=".chr(34)."document.getElementById('".$object."').style.display='none';document.getElementById('fade').style.display='none'".chr(34).">";
		echo "</div>";
	}
	
	
	function createInfo($object,$title,$message,$icon="info")
	{
		echo "<div id='".$object."' class='white_content'>";
		echo "<table class='alert'><tr class='alert'>";
		echo "<td class='alert' width='80'><img class='alert' src='framework/alert/".$icon.".png' style='vertical-align:top;'></td>";
		echo "<td class='alert'><span class='alert-title'>$title </span><br /><br /> $message<br/>";
		echo "</td>";
		echo "</tr></table>";
		echo "<input type='button' class='alert' id='".$object."Yes' value='Ya'> ";
		/*
		echo "<input type='button' class='alert' value='Tidak' title='' onclick=".chr(34)."document.getElementById('".$object."').style.display='none';document.getElementById('fade').style.display='none'".chr(34).">";
		*/
		echo "</div>";
	}
?>
