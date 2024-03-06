<?php
	include "framework/database/connect.php";
	include_once "framework/functions/default.php";
	include "framework/security/default.php";
	include "framework/alert/alert.php";
	
	$LeadNbr	= $_GET['LEAD_NBR'];
	$CoNbr		= $_GET['CO_NBR'];
	
	$Security=getSecurity($_SESSION['userID'],"DigitalPrint");
	
	//Process changes here
	if($_POST['LEAD_NBR']!=""){
		
		$LeadNbr 	= $_POST['LEAD_NBR'];
				
		//Take care of nulls
		if($_POST['CO_NBR']==""){$CoNbr="NULL";}else{$CoNbr=$_POST['CO_NBR'];}
		if($_POST['LEAD_STG']==""){$LeadStg="NULL";}else{$LeadStg="'".$_POST['LEAD_STG']."'";}
		if($_POST['LEAD_ACT']==""){$LeadAct="NULL";}else{$LeadAct="'".$_POST['LEAD_ACT']."'";}
		if($_POST['LEAD_RAT']==""){$LeadRat="NULL";}else{$LeadRat="'".$_POST['LEAD_RAT']."'";}
		if($_POST['ACT_NTE']==""){$ActNte="NULL";}else{$ActNte="'".$_POST['ACT_NTE']."'";}

		//Process add new
		if($LeadNbr==-1)
		{
		$query="SELECT COALESCE(MAX(LEAD_NBR),0)+1 AS NEW_NBR FROM CMP.LEAD_DET";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			$LeadNbr=$row['NEW_NBR'];
			$query="INSERT INTO CMP.LEAD_DET (LEAD_NBR) VALUES (".$LeadNbr.")";
			$result=mysql_query($query);
		}
		
		$query="UPDATE CMP.LEAD_DET
	   			SET CO_NBR=".$CoNbr.",
	   				LEAD_STG=".$LeadStg.",
	   				LEAD_ACT=".$LeadAct.",
	   				LEAD_RAT=".$LeadRat.",
					ACT_TS=CURRENT_TIMESTAMP,
	   				ACT_NTE=".$ActNte.",
					UPD_TS=CURRENT_TIMESTAMP,
					UPD_NBR=".$_SESSION['personNBR']."
					WHERE LEAD_NBR=".$LeadNbr;
	   	$result=mysql_query($query);
				
	}
	
	$query_company 	= "SELECT NAME FROM COMPANY WHERE CO_NBR = ".$CoNbr;
	$result_company	= mysql_query($query_company);
	$company 		= mysql_fetch_array($result_company);
	$CompanyName	= $company['NAME'];
	
	$query="SELECT 
		LEAD_NBR, CO_NBR, LEAD_STG, LEAD_ACT, LEAD_RAT, ACT_TS, ACT_NTE, DEL_NBR, UPD_TS, UPD_NBR
		FROM CMP.LEAD_DET LED
		WHERE LEAD_NBR=".$LeadNbr;
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<script>parent.Pace.restart();</script>
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" media="screen" />
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4-core.js"></script>
<script type="text/javascript" src="framework/datepicker/js/mootools-1.2.4.4-more.js"></script>
<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.js"></script>
<script type="text/javascript" src="framework/functions/default.js"></script>



<link rel="stylesheet" type="text/css" media="screen" href="framework/alert/alert.css" />

<link rel="stylesheet" href="framework/combobox/chosen.css">

</head>

<body>

<script>
	parent.parent.document.getElementById('addressDeleteYes').onclick=
	function () { 
		parent.parent.document.getElementById('content').contentDocument.getElementById('rightpane').src='lead-management-act.php?CO_NBR=<?php echo $CoNbr ; ?>&DEL_C=<?php echo $LeadNbr ?>';
		parent.parent.document.getElementById('addressDelete').style.display='none';
		parent.parent.document.getElementById('fade').style.display='none';
	};
</script>

<div class="toolbar-only">
<?php if(($Security==0)&&($LeadNbr!=0)) { ?>
	<p class="toolbar-left"><a href="javascript:void(0)" onclick = "window.scrollTo(0,0);parent.parent.document.getElementById('addressDelete').style.display='block';parent.parent.document.getElementById('fade').style.display='block'"><span class='fa fa-trash toolbar' style="cursor:pointer"></span></a></p>
<?php } ?>
</div>

<form enctype="multipart/form-data" action="#" method="post" style="width:700px" onSubmit="return checkform();" id="signup">
	<p>
		<h2>
			<?php
				if((!$cloud)&&($row['LEAD_NBR']=="")){
					echo "<script>window.scrollTo(0,0);parent.document.getElementById('offline').style.display='block';parent.document.getElementById('fade').style.display='block';</script>";
				}
				echo $row['LEAD_NBR'];if($row['LEAD_NBR']==""){echo "Baru";}
			?>
		</h2>
		<h3>
			<?php echo $CompanyName;if($CompanyName==""){echo "";} ?>
		</h3>
		<h3>
			Nomor Induk: <?php echo $CoNbr;if($CoNbr==""){echo "";} ?>
		</h3>
		<input name="LEAD_NBR" value="<?php echo $row['LEAD_NBR'];if($row['LEAD_NBR']==""){echo "-1";} ?>" type="hidden" />
		<input name="CO_NBR" value="<?php echo $CoNbr; ?>" type="hidden" />
		
		<label>Stage</label><br /><div class='labelbox'></div>
		<select name="LEAD_STG" class="chosen-select"><br /><div class='labelbox'></div>
		<?php
			$query="SELECT STG_TYP, STG_DESC FROM CMP.LEAD_STG ORDER BY STG_ORD";

			genCombo($query,"STG_TYP","STG_DESC",$row['LEAD_STG']);
		?>
		</select><div class="combobox"></div>
		
		<label>Aktivitas</label><br /><div class='labelbox'></div>
		<select name="LEAD_ACT" class="chosen-select"><br /><div class='labelbox' ></div>
		<?php
			$query="SELECT ACT_TYP, ACT_DESC FROM CMP.LEAD_ACT ORDER BY ACT_ORD";
			
			genCombo($query,"ACT_TYP","ACT_DESC",$row['LEAD_ACT']);
		?>
		</select><div class="combobox"></div>
		
		<label>Rating</label><br /><div class='labelbox' ></div>
		<select name="LEAD_RAT" class="chosen-select" style="width:100px"><br />
		<?php
			$query_rating="SELECT RAT_TYP, RAT_DESC FROM CMP.LEAD_RAT ORDER BY RAT_ORD";
					
			$back="";
			$selected = "";
			
			$result_rating = mysql_query($query_rating);
			while($row_rating = mysql_fetch_array($result_rating)) {

			if($row_rating['RAT_DESC']=='Hot'){
					$back="style='background-color:#d92115;color:#ffffff'";
				}elseif($row_rating['RAT_DESC']=='Warm'){
					$back="style='background-color:#fbad06;color:#ffffff'";
					}
					else { $back="style='background-color:#008000;color:#ffffff'";	}
						
			if($row_rating['RAT_TYP'] == $row['LEAD_RAT']){ $selected = "selected"; } else { $selected = ""; }
			
				echo "<option $back $selected value='".$row_rating['RAT_TYP']."'>".$row_rating['RAT_DESC']."</option>" ;
							
			} 
		 ?>
		
		</select><div class="combobox"></div>
		
		<label>Keterangan</label><br />
		<textarea name="ACT_NTE" style="width:400px;height:40px;"><?php echo $row['ACT_NTE']; ?></textarea><br />

		<?php
			echo "<input class='process' type='submit' value='Simpan' />";

		?>
	</p>
	<script src="framework/database/jquery.min.js" type="text/javascript"></script>
	<script src="framework/combobox/chosen.jquery.js" type="text/javascript"></script>
	<script type="text/javascript">
		jQuery.noConflict();
		var config = {
			'.chosen-select'           : {},
			'.chosen-select-deselect'  : {allow_single_deselect:true},
			'.chosen-select-no-single' : {disable_search_threshold:10},
			'.chosen-select-no-results': {no_results_text:'Data tidak ketemu'},
			'.chosen-select-width'     : {width:"95%"}
	   	}
		for (var selector in config) {
			jQuery(selector).chosen(config[selector]);
		}
	</script>
</form>
<div></div>



</body>
</html>
