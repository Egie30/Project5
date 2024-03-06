<?php
	//include "framework/database/connect.php";
	include "framework/database/connect-cloud.php";
	include "framework/functions/default.php";
	include "framework/security/default.php";
	date_default_timezone_set("Asia/Jakarta");

	$Security = getSecurity($_SESSION['userID'], "DigitalPrint");

	$PrsnNbr	= $_GET['PRSN_NBR'];
	$CoNbr		= $_GET['CO_NBR'];
	$UpdTsDel	= $_GET['UPD_TS'];
	$del 		= $_GET['DEL'];
	$BlnAtnd	= $_GET['BULANATND'];
	$BlnMch		= $_GET['BULANMACH'];
	$nbrDays	= 0;	
	$month		= parseMonth($PymtDte);
	$year		= parseYear($PymtDte);
	
	$bulan = date('m');
	$tahun = date('Y');
	if (isset($_GET['BULAN'])) {
		$bulan = $_GET['BULAN'];
	}
	if (isset($_GET['TAHUN'])) {
		$tahun = $_GET['TAHUN'];
	}
	
	$query		= "SELECT 	PPL.PRSN_NBR,
							NAME, 
							ACK.CRT_TS
						FROM CMP.PEOPLE PPL
						LEFT JOIN (
									SELECT 	PRSN_NBR,
											CRT_TS 
										FROM PAY.ATND_CLOK 
										WHERE MONTH(CRT_TS)= ".$bulan."
										AND YEAR(CRT_TS)= ".$tahun."
										AND DEL_NBR =0
								  )ACK ON PPL.PRSN_NBR = ACK.PRSN_NBR
						WHERE DEL_NBR=0 AND PPL.PRSN_NBR=".$PrsnNbr." ORDER BY ACK.CRT_TS ASC";
	
	$days		= date("t");
	$result		= mysql_query($query,$local);
	$num		= mysql_num_rows($result);
	$PymtDte	= date("Y-m-d H:i:s");	
	while($row		= mysql_fetch_array($result)){
		$name 	= $row['NAME'];
		$PrsnNbr= $row['PRSN_NBR'];
		$UpdTs[]= $row['CRT_TS'];
		
	}
	
	//Delete
	if ($del=='TRUE'){
		
		$QueryDel = "UPDATE $PAY.ATND_CLOK SET DEL_NBR=".$_SESSION['personNBR']." 
						WHERE PRSN_NBR= $PrsnNbr AND CRT_TS= '$UpdTsDel'";
		$ResultDel= mysql_query($QueryDel,$cloud);
		$QueryDel = str_replace($PAY,"PAY",$QueryDel);
		$ResultDel= mysql_query($QueryDel,$local);
		$delete=true;
	}
		
	//Insert  mach_clok
	if($_GET['MCK']=='TRUE'){
		//Query Search data mach_clok yang tidak di atnd
		$query_mck		= "SELECT *
							FROM (
								SELECT CLOK_IN_TS AS CLOK
								FROM PAY.MACH_CLOK
								WHERE MONTH(CLOK_IN_TS) = $bulan
									AND YEAR(CLOK_IN_TS) = $tahun
									AND PRSN_NBR = $PrsnNbr
									AND CLOK_IN_TS != 'NULL'
								
								UNION ALL
								
								SELECT CLOK_OT_TS AS CLOK
								FROM PAY.MACH_CLOK
								WHERE MONTH(CLOK_IN_TS) = $bulan
									AND YEAR(CLOK_IN_TS) = $tahun
									AND PRSN_NBR = $PrsnNbr
									AND CLOK_OT_TS != 'NULL'
								) MACH_CLOK
							WHERE (
									CLOK NOT IN (
										SELECT CRT_TS
										FROM PAY.ATND_CLOK
										WHERE MONTH(CRT_TS) = '".$bulan."'
											AND YEAR(CRT_TS) = $tahun
											AND DEL_NBR = 0
											AND PRSN_NBR = $PrsnNbr
										)
									)";
		//echo $query_mck;
		$result_mck 	= mysql_query($query_mck);
		while ($row_mck = mysql_fetch_array($result_mck)){
			if (isset($row_mck['CLOK'])){
					$DelMck		= "DELETE FROM PAY.MACH_CLOK WHERE CLOK_IN_TS='".$row_mck['CLOK']."' OR CLOK_OT_TS='".$row_mck['CLOK']."' AND PRSN_NBR=$PrsnNbr" ;
					mysql_query($DelMck);
					//echo $DelMck;
			}
			
		}
		
		//Query Search data atnd yang tidak di mach_clok
		$query_atnd		= "SELECT *
							FROM PAY.ATND_CLOK
							WHERE MONTH(CRT_TS) = '".$bulan."'
								AND YEAR(CRT_TS) = '".$tahun."'
								AND DEL_NBR = 0
								AND PRSN_NBR = $PrsnNbr
								AND (
									CRT_TS NOT IN (
										SELECT CLOK_IN_TS AS CLOK
										FROM PAY.MACH_CLOK
										WHERE MONTH(CLOK_IN_TS) = $bulan
											AND YEAR(CLOK_IN_TS) = $tahun
											AND PRSN_NBR = $PrsnNbr
											AND CLOK_IN_TS != 'NULL'
										
										UNION ALL
										
										SELECT CLOK_OT_TS AS CLOK
										FROM PAY.MACH_CLOK
										WHERE MONTH(CLOK_IN_TS) = $bulan
											AND YEAR(CLOK_IN_TS) = $tahun
											AND PRSN_NBR = $PrsnNbr
											AND CLOK_OT_TS != 'NULL'
										)
									)
							ORDER BY PRSN_NBR";
		$result_atnd	= mysql_query($query_atnd);
		
		while($row_atnd = mysql_fetch_array($result_atnd)) {
			$personNbr	= $row_atnd['PRSN_NBR'];
			$scanDate	= $row_atnd['CRT_TS'];
					
            $sql = "SELECT * FROM PAY.MACH_CLOK WHERE PRSN_NBR = " . $personNbr . " AND DATE(CLOK_IN_TS) = '" . date('Y-m-d', strtotime($scanDate)) . "' AND CLOK_OT_TS IS NULL";
            $rs = mysql_query($sql);
			
			$num_rows = mysql_num_rows($rs);
            $row = mysql_fetch_array($rs);

            if ($num_rows == 0) {
                $query = "INSERT INTO PAY.MACH_CLOK(PRSN_NBR, CLOK_IN_TS, UPD_TS) VALUES (" . $personNbr . ",'" . $scanDate . "','" . $scanDate . "')";
                mysql_query($query);
				
				//echo $query."<br />";
            } else {
                
				$query_diff = "SELECT HOUR(TIMEDIFF('" . date('Y-m-d H:i:s', strtotime($scanDate)) . "','" . $row['CLOK_IN_TS'] . "')) AS diff";
				
                $result_diff 	= mysql_query($query_diff);
                $row_diff		= mysql_fetch_array($result_diff);

				if ($row_diff['diff'] >= 1) {
                    $query = "UPDATE PAY.MACH_CLOK SET CLOK_OT_TS = '" . $scanDate . "', UPD_TS = '" . $scanDate . "' WHERE CLOK_NBR = " . $row['CLOK_NBR'];
                    mysql_query($query);
					
					echo $query."<br />";
                }
				
            }		
			
		}
		
	$query_back   = "SELECT * FROM PAY.MACH_CLOK 
						WHERE MONTH(CLOK_IN_TS)=$bulan AND YEAR(CLOK_IN_TS)=$tahun AND PRSN_NBR=$PrsnNbr";
		$result_back  = mysql_query($query_back);
		while($row_back = mysql_fetch_array($result_back)) {
			if ($row_back['CLOK_OT_TS']!=""){
				if ($row_back['CLOK_IN_TS'] > $row_back['CLOK_OT_TS']){
					//echo "balik<br/>";
					$query_u_back  = "UPDATE PAY.MACH_CLOK SET CLOK_IN_TS='".$row_back['CLOK_OT_TS']."', CLOK_OT_TS='".$row_back['CLOK_IN_TS']."' 
									WHERE CLOK_NBR='".$row_back['CLOK_NBR']."' ";
					$result_u_back = mysql_query($query_u_back);
					//echo $query_u_back."<br/>";
				}
			}
		}
		
		$prosess=true;
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/combobox/chosen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/pagination/pagination.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/tablesorter/themes/nestor/style.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">

	<script type="text/javascript">parent.Pace.restart();</script>
	<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
	<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
	<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>
	<script type="text/javascript" src="framework/liveSearch/livesearch.js"></script>
	<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
	<script type="text/javascript" src="framework/pagination/pagination.js"></script>
	<script type="text/javascript" src="framework/tablesorter/jquery.tablesorter.js"></script>
	<script type="text/javascript" src="framework/uri/src/URI.min.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>
	<script type="text/javascript" src="framework/combobox/ajax-chosen/ajax-chosen.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.default.js"></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<script type="text/javascript">jQuery.noConflict();</script>
	<style>
		table.tablesorter thead tr .headerTd{
			border-bottom:1px solid #cacbcf;
		}
	</style>
	<script><!--awal-->
	<?php if ($delete){?>
		setTimeout(function(){
		   window.location='<?php echo "payroll-attendance-ls.php?PRSN_NBR=".$PrsnNbr."&CO_NBR=".$CoNbr?>';
		}, 500);
	<?php }?>
	
	<?php if ($prosess){?>
		setTimeout(function(){
		   window.location='<?php echo "payroll-attendance-ls.php?PRSN_NBR=".$PrsnNbr."&CO_NBR=".$CoNbr?>';
		}, 500);
	<?php } ?>
	</script><!--akhir-->
</head>
<body>
<h2>
	<?php echo $name ?>
</h2>
<h3>
	Perincian Absensi Nomor Induk: <?php echo $PrsnNbr;if($PrsnNbr==""){echo "Nomor Baru";} ?>
</h3>
<div class="toolbar">
    <div class="toolbar-left" style="padding: 5px;padding-left: 0;">
    <form action="" method="GET" style="padding: 0;margin: 0;margin-top: 10px;">
		<i style="display:none">
			<input type="text" style="width:200px; display" name="PRSN_NBR" value="<?php echo $PrsnNbr;?>">
			<input type="text" style="width:200px; " name="CO_NBR" value="<?php echo $CoNbr;?>">
		</i>
        <select name="BULAN" class="chosen-select" id="select-bulan" style="width: 150px;">
            <?php
            for ($i = 1; $i <= 12; $i++) {
                $select = '';
                if ($i == $bulan) {
                    $select = 'selected=""';
                }
                echo '<option value="' . $i . '" ' . $select . '>' . date('F', strtotime(date('d-' . $i . '-Y'))) . '</option>';
            }
            ?>
        </select>
		<span style="padding-left: 12px;">
        <select name="TAHUN" class="chosen-select" id="select-tahun" style="width: 100px; ">
            <?php
            for ($i = date('Y') - 1; $i <= date('Y'); $i++) {
                $select = '';
                if ($i == $tahun) {
                    $select = 'selected=""';
                }
                echo '<option value="' . $i . '" ' . $select . '>' . $i . '</option>';
            }
            ?>
        </select>
		</span>
        <button type="submit" style="background: none;border:none; cursor: pointer; padding-left: 13px;">
            <span class="fa fa-calendar toolbar fa-lg" style="padding-left: 0px;"></span>
        </button>
    </form>
    </div>
</div>
<br/>
<div id="mainResult" >
	
    <table id="mainTable" class="tablesorter searchTable" style="width:600px;">
        <thead>
        <tr >
            <th style="width:5%;">No</th>
			<th style="width:45%">Date</th>
			<th style="width:40%">Time</th>
			<td class="headerTd" style="width:10%">
				<div class='listable-btn' style="border-bottom: 1px solid #cacbcf;">
					<span class="fa fa-plus  listable-btn" onclick="pushFormIn('payroll-attendance-edit.php?PRSN_NBR=<?php echo $PrsnNbr;?>');"></span>
				</div>
			</td>
        </tr>
        </thead>
        <tbody>
        <?php
		for ($i=0;$i<$num;$i++){
		if (isset($UpdTs[$i])){		
		?>
		<tr>
			<td align="center" ><?php $j=$i+1; echo $j;?></td>
			<td style ="cursor: pointer;" onclick="pushFormIn('payroll-attendance-edit.php?PRSN_NBR=<?php echo $PrsnNbr;?>&CO_NBR=<?php echo $CoNbr; ?>&CRTIME=<?php echo $UpdTs[$i];?>');"><?php echo parseDate($UpdTs[$i]);?></td>
			<td style ="cursor: pointer;" onclick="pushFormIn('payroll-attendance-edit.php?PRSN_NBR=<?php echo $PrsnNbr;?>&CO_NBR=<?php echo $CoNbr; ?>&CRTIME=<?php echo $UpdTs[$i];?>');"><?php echo parseTimeShort($UpdTs[$i])?></td>
			<td style="background:#FFFFFF;">
				<div class="listable-btn">
					<div class='listable-btn'>
						<span class="fa fa-trash listable-btn" onclick="DelAttd(<?php echo $PrsnNbr.",".$CoNbr.",'".$UpdTs[$i]."'";?>)"></span>
					</div>
				</div>
			</td>
		</tr>
        <?php
			}//if
		}//for
        ?>
			
        </tbody>
    </table>
	<form action="" method="GET" style="padding: 0;margin: 0;margin-top: 10px;">
		<i style="display:none">
			<input type="text" style="width:200px; " name="MCK" value="TRUE">
			<input type="text" style="width:200px; display" name="PRSN_NBR" value="<?php echo $PrsnNbr;?>">
			<input type="text" style="width:200px; " name="CO_NBR" value="<?php echo $CoNbr;?>">
			<input type="text" style="width:200px; " name="BULAN" value="<?php echo $bulan;?>">
			<input type="text" style="width:200px; " name="TAHUN" value="<?php echo $tahun;?>">
		</i>
	<input class="process" id="proses" name="proses" type="submit" value="Proses"/>
	</form>
	<script>
		var config = {
        '.chosen-select': {},
        '.chosen-select-deselect': {allow_single_deselect: true},
        '.chosen-select-no-single': {disable_search_threshold: 10},
        '.chosen-select-no-results': {no_results_text: 'Data tidak ketemu'},
        '.chosen-select-width': {width: "95%"}
		}
		for (var selector in config) {
			$(selector).chosen(config[selector]);
		}
		
		function DelAttd(prsn_nbr,co_nbr,upd_ts){
			window.scrollTo(0, 0);
			parent.parent.document.getElementById('addressDelete').style.display = 'block';
			parent.parent.document.getElementById('fade').style.display = 'block';
			
			parent.parent.document.getElementById('atndDeleteYes').onclick =
			function () {
				parent.document.getElementById('content').src = 'payroll-attendance-ls.php?PRSN_NBR=' + prsn_nbr + '&CO_NBR='+ co_nbr +'&UPD_TS= '+ upd_ts +'&DEL=TRUE';
				parent.parent.document.getElementById('atndDelete').style.display = 'none';
				parent.parent.document.getElementById('fade').style.display = 'none';
			};
		}
	</script>
    <script>
        jQuery(document).ready(function () {
            jQuery("#mainTable").tablesorter({widgets: ["zebra"]});
            }
        );
    </script>
</body>
</html>