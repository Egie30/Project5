<?php
	require_once "framework/database/connect.php";
	require_once "framework/functions/default.php";

	ini_set('max_execution_time',5000);
	ini_set('memori_limit','1024MB');
	ini_set('post_max_size', '100M');
	ini_set('upload_max_filesize', '100M');

	$query 				= "SELECT UPD_TS_LOG FROM NST.PARAM_LOC";
	$result 			= mysql_query($query);
	$row 				= mysql_fetch_array($result);
	$upd_last 			= $row['UPD_TS_LOG'];	

	$query 				= "UPDATE NST.PARAM_LOC SET UPD_TS_LOG = NOW()";
	mysql_query($query);

?>

<?php
		$query_latex		= "SELECT LOG.CMPTR_NBR as CMPTR_NBR, LOG.MACH_TYP as MACH_TYP, LOG.CMPTR_NAME as CMPTR_NAME, LOG.DIR_NAME as DIR_NAME FROM CMP.MACH_LOG LOG WHERE LOG.MACH_TYP = 'HPL375'";
		$result_latex		= mysql_query($query_latex);
		while ($row_latex	= mysql_fetch_array($result_latex))
		{
		//mesin latex campus      
		$xml = simplexml_load_file($row_latex['DIR_NAME']);   
		echo $xml;            
		if($xml===FALSE)
			{
		  	   echo 'No Stats';
			} 
			else 
			{
		$result = $xml->xpath("//xdm:Device");

		foreach ($result as $message )
			 {
			$message = $xml->xpath("//xdm:Job");
				foreach ($message as $message1 )
					{
					 $FileName 				= $message1->xpath("descendant::dd:FileName/text()")[0];
					 $Length 				= $message1->xpath("descendant::dd:Length/text()")[0];
					 $Width 				= $message1->xpath("descendant::dd:Width/text()")[0];
					 $dimensi 				= $Length." x ".$Width;
					 $DateTimeAtCreation 	= $message1->xpath("descendant::pwg:DateTimeAtCreation/text()")[0];
					 $DateTimeAtCompleted 	= $message1->xpath("descendant::pwg:DateTimeAtCompleted/text()")[0];
					 $PrintingTime 			= $message1->xpath("descendant::dd:PrintingTime/text()")[0];

					 // $Total = (float)$Length * (float)$Width;
					 $w 		= array(' ');
					 $ww 		= array('');
					 $Length1 	= $dimensi." "."cm";

					 $xtime 	= strtotime($DateTimeAtCreation);
					 $time 		= date("Y-m-d H:m:s", $xtime);

					 if($upd_last < $time){
					 $query 	= "INSERT INTO CMP.LOG_FILE(CMPTR_NBR,CMPTR_NAME,MACH_TYP,PRN_TYP,FIL_NM,PRN_DIM,BEG_TS,END_TS) VALUES ('".$row_latex['CMPTR_NBR']."','".$row_latex['CMPTR_NAME']."','".$row_latex['MACH_TYP']."','Print','".$FileName."','".$Length1."','".$time."','".$DateTimeAtCompleted."') 
					 		  ";
					 // echo $query."<br>";
					 $result = mysql_query($query);
						}
					}
				}
			}
		}
		?>

<?php
		$query_mutoh 		= "SELECT LOG.CMPTR_NBR as CMPTR_NBR, LOG.MACH_TYP as MACH_TYP, LOG.CMPTR_NAME as CMPTR_NAME, LOG.DIR_NAME as DIR_NAME FROM CMP.MACH_LOG LOG WHERE LOG.MACH_TYP = 'MVJ1624'";
		$result_mutoh		= mysql_query($query_mutoh);
		while ($row_mutoh	= mysql_fetch_array($result_mutoh))
		{
		//mesin mutoh campus
		foreach (glob($row_mutoh['DIR_NAME'])as $job) { //local
		    // echo $job."<br>";
		if (file_exists($job)) { 
			$path 			= file_get_contents($job);
			$File 			= '/File=(.*)/';
			$PrintSetup 	= '/PrintSetup=(.*)/';
			$SourceSizeX 	= '/SourceSizeX=(.*)/';
			$SourceSizeY 	= '/SourceSizeY=(.*)/';
			$Filelg			= '/File=(.*)/';

			preg_match($File,		  $path, $Filematch);
			preg_match($PrintSetup,   $path, $PrintSetupmatch);
			preg_match($SourceSizeX,  $path, $SourceSizeXmatch);
			preg_match($SourceSizeY,  $path, $SourceSizeYmatch);
			preg_match($Filelg,       $path, $Filelgmatch);

			$kol	 = $Filelgmatch[1];

			$str 	 = str_replace('File=', '', $kol);

			$folders = explode('\\', $str);

			$realtime = date("Y-m-d H:i:s", filemtime($job));
			$time = date('Y-m-d H:i:s',strtotime($realtime));
			 // echo $time."<br>";

			$dimensi = $SourceSizeXmatch[1]." x ".$SourceSizeYmatch[1]." cm";

			$testing = $folders[7];
			if($testing == ''){
				$testing = $folders[6];
			}else{
				$testing = $folders[7];
			}

			if($upd_last < $time){
			$query = "INSERT INTO CMP.LOG_FILE(CMPTR_NBR,CMPTR_NAME,MACH_TYP,PRN_TYP,FIL_NM,PRN_DIM,BEG_TS,END_TS) 
					  VALUES
					 ('".$row_mutoh['CMPTR_NBR']."','".$row_mutoh['CMPTR_NAME']."','".$row_mutoh['MACH_TYP']."','Print','".$testing."','".$dimensi."','".$time."','".$time."')";
			// echo $query."<br>";
			$result = mysql_query($query);
						}
					}
			}
		}

	
	?>

<?php
		$query_rolland 		= "SELECT LOG.CMPTR_NBR as CMPTR_NBR, LOG.MACH_TYP as MACH_TYP, LOG.CMPTR_NAME as CMPTR_NAME, LOG.DIR_NAME as DIR_NAME FROM CMP.MACH_LOG LOG WHERE LOG.MACH_TYP = 'RVS640'";
		$result_rolland		= mysql_query($query_rolland);
		while ($row_rolland	= mysql_fetch_array($result_rolland))
		{
		// mesin rolland campus
		foreach (glob($row_rolland['DIR_NAME'])as $log) { 
		if (file_exists($log)) { 

	    $xml = simplexml_load_file($log);	
		foreach ($xml as $readxml):

		$strJobName    		   =  $readxml->strJobName;//FILE
		$dimensionwidth  	   =  $readxml->pageSize_mm->x;//DIMENSION
		$dimensionlengthwidth  =  $readxml->pageSize_mm->y;
		$year				   =  $readxml->printStartTime->year;//startdate1
		$month 				   =  $readxml->printStartTime->month;
		$day  				   =  $readxml->printStartTime->day;
		$hour				   =  $readxml->printStartTime->hour;
		$minute				   =  $readxml->printStartTime->minute;
		$second				   =  $readxml->printStartTime->second;
		$year1				   =  $readxml->printCompleteTime->year;//enddate1
		$month1 			   =  $readxml->printCompleteTime->month;
		$day1 				   =  $readxml->printCompleteTime->day;
		$hour1				   =  $readxml->printCompleteTime->hour;
		$minute1			   =  $readxml->printCompleteTime->minute;
		$second1			   =  $readxml->printCompleteTime->second;

		$dimensi 	= $dimensionwidth." x ".$dimensionlengthwidth." cm";

		$AOA 		= "1999-11-30 00:00:00";
		$stardate 	= $year."/".$month."/".$day." ".$hour.":".$minute.":".$second;
		$xtime		= strtotime($stardate);
		$time 		= date("Y-m-d H:i:s",$xtime);

		if($AOA == $time){
		$waktu 		= $waktu1;
		}else{
		$waktu 		= date("Y-m-d H:i:s",$xtime);
		}

		$enddate 	= $year1."/".$month1."/".$day1." ".$hour1.":".$minute1.":".$second1;
		$xtime1 	= strtotime($enddate);
		$time1 		= date("Y-m-d H:i:s",$xtime1);
		if($AOA == $time1){
		$waktu1 		= $waktu;
		}else{
		$waktu1		= date("Y-m-d H:i:s",$xtime1);
		}
	    if($upd_last < $waktu){
		$query 		= "INSERT INTO CMP.LOG_FILE(CMPTR_NBR,CMPTR_NAME,MACH_TYP,PRN_TYP,FIL_NM,PRN_DIM,BEG_TS,END_TS) VALUES ('".$row_rolland['CMPTR_NBR']."','".$row_rolland['CMPTR_NAME']."','".$row_rolland['MACH_TYP']."','Print','".$strJobName."','".$dimensi."','".$waktu."','".$waktu1."')";
		// echo $query;
		$result  	= mysql_query($query);			
							}		 
		endforeach;
				}
			}
		}
	?>

	<?php
		// mesin indoor bawah campus fix
		$query_flora 		= "SELECT LOG.CMPTR_NBR as CMPTR_NBR, LOG.MACH_TYP as MACH_TYP, LOG.CMPTR_NAME as CMPTR_NAME, LOG.DIR_NAME as DIR_NAME FROM CMP.MACH_LOG LOG WHERE LOG.MACH_TYP = 'C512i30PL' ";
		$result_flora		= mysql_query($query_flora);
		while ($row_flora	= mysql_fetch_array($result_flora))
		foreach (glob($row_flora['DIR_NAME'])as $html1) {  //server
		if (file_exists($html1)) { 
		$source1 = file_get_contents($html1);
		$dom1 = new DOMDocument();

		$dom1->loadHTML($source1);

		$xpath = new DOMXPath($dom1);

		$textList1 = $xpath->query("//table");
		foreach ( $textList1 as $text1 ){

			$pala 	 = $xpath->evaluate(
		              "string(descendant::tr/th/h1/text())",
		               $text1);
		    $one 	 = $pala;

		    $plintel = $xpath->evaluate(
		              "string(descendant::tr[th[contains(text(),'Printer') or contains(text(),'Device name')]]/td/text())",
		               $text1);
		    $two 	 = $plintel;

		    $files   = $xpath->evaluate(
		               "string(descendant::tr[th[contains(text(),'File')]]/td/text())",
		               $text1);
		    $threee  = $files;


		    $dmnsi   = $xpath->evaluate(
		               "string(descendant::tr[th[contains(text(),'Dimensions')]]/td/text())",
		               $text1);
		    $five    = str_replace('in','cm', $dmnsi);

		    $tglmlai = $xpath->evaluate(
		               "string(descendant::tr[th[contains(text(),'RIP Start Date and Time') or contains(text(),'Output Start Date And Time')]]/td/text())",
		               $text1);
		    $sven 	 = $tglmlai;

		    $tglakhr = $xpath->evaluate(
		               "string(descendant::tr[th[contains(text(),'RIP End Date and Time') or contains(text(),'Output End Date And Time')]]/td/text())",
		               $text1);
		    $eight   = $tglakhr;

		    $durasi  = $xpath->evaluate(
		               "string(descendant::tr[th[contains(text(),'RIP Duration') or contains(text(),'Output Duration')]]/td/text())",
		               $text1);
		    $nine    = $durasi;

			$one1      = preg_replace("/[^0-9a-zA-Z \/:\-]/", "", $one);
			$one11     = str_replace('Start','', $one1);
			$one111    = str_replace('Job','', $one11);
			$one1111   = str_replace('Printing','Print', $one111);
			$nine1     = preg_replace("/[^0-9a-zA-Z \/:\-]/", "", $nine);
			$threess   = preg_replace("/[^0-9a-zA-Z \/:\-]/", "", $threee);
			$threesss  = str_replace('tif','.tif', $threess);			
							 
			// if ($row_flora['CMPTR_NAME'] == 'CAMPUS15') {
			// // tanggal bulan
			// $date  	= preg_replace("/[^0-9a-zA-Z \/:\-]/", "", $sven);
			// $tgl 	= str_replace("/","-", $date);
			// $tgl1 	= strtotime($tgl);
			// $rslt   = date("Y-m-d H:i:s",$tgl1);

			// $date2   = preg_replace("/[^0-9a-zA-Z \/:\-]/", "", $eight);
			// $tgl2 	 = str_replace("/","-", $date2);
			// $tgl3    = strtotime($tgl2);
			// $rslt1   = date("Y-m-d H:i:s",$tgl3);
			// }
			//  else
			// {				 
			 //BULAN tanggal 
			 $tgl     = preg_replace("/[^0-9a-zA-Z \/:\-]/", "", $sven);
			 $tgl1    = strtotime($tgl);
			 $rslt    = date("Y-m-d H:i:s",$tgl1);
			 $tgl2    = preg_replace("/[^0-9a-zA-Z \/:\-]/", "", $eight);
			 $tgl3    = strtotime($tgl2);
			 $rslt1   = date("Y-m-d H:i:s",$tgl3);
			 // }

			if($upd_last < $rslt){
		$query = "INSERT INTO CMP.LOG_FILE(CMPTR_NBR,CMPTR_NAME,MACH_TYP,PRN_TYP,FIL_NM,PRN_DIM,BEG_TS,END_TS,DUR_PRN) VALUES ('".$row_flora['CMPTR_NBR']."','".$row_flora['CMPTR_NAME']."','".$row_flora['MACH_TYP']."','".$one1111."','".$threesss."','".utf8_decode($five)."','".$rslt."','".$rslt1."','".$nine1."')";
		$result = mysql_query($query); 
						}
					}
				}
			}
	
	

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<style>
		body{
			color: #666666;
			text-shadow: 0 0px 1px rgba(0,0,0,0.15);
			}
	
		div.title {
			margin-top:10px;
			margin-left:10px;
			font-size:13pt;
			margin-bottom:6px;
			color:#000000;
		}
		
		div.text {
			margin-left:10px;		
		}
</style>
<style type="text/css">
			.loader{
			position: fixed;
			left: 0px;
			top: 0px;
			width: 100%;
			height: 100%;
			z-index: 9999;
			background: url('img/wait.gif') 50% 50% no-repeat rgb(249,249,249);
			}
	</style>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	<script type="text/javascript" src="framework/jquery/jquery-1.5.0.min.js"></script>
	<script type="text/javascript" src="framework/jquery/jquery-2.1.1.min.js"></script>
	<script type="text/javascript">
	$(window).load(function() {
		$(".loader").fadeOut("slow");
	})
	</script>
</head>
<body>
		<div class="loader"></div>
		<div class="text">
		<br \>
				Insert Log File Process Complete.
		</div>
</form>
</body>
</html>

