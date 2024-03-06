<?php
require_once "framework/database/connect.php";
require_once "framework/security/default.php";
	
$Security = getSecurity($_SESSION['userID'], "Accounting");

$bookNumber	= $_GET['BK_NBR'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/combobox/chosen.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/tab/tabs.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/pagination/pagination.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="framework/datepicker/css/calendar-eightysix-v1.1-default.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="css/accounting.css" />
	<link rel="stylesheet" href="css/font-awesome-4.4.0/css/font-awesome.min.css">
	
	<script type="text/javascript">parent.Pace.restart();</script>
	<script type="text/javascript" src="framework/mootools/mootools-latest.min.js"></script>
	<script type="text/javascript" src="framework/mootools/mootools-latest-more.js"></script>
	<script type="text/javascript" src="framework/datepicker/js/calendar-eightysix-v1.1.min.js"></script>
	<script type="text/javascript" src="framework/liveSearch/livesearch.js"></script>
	<script type="text/javascript" src="framework/jquery/jquery-latest.min.js"></script>
	<script type="text/javascript" src="framework/tab/tabs.js"></script>
	<script type="text/javascript" src="framework/pagination/pagination.js"></script>
	<script type="text/javascript" src="framework/tablesort/tablesort.js"></script>
	<script type="text/javascript" src="framework/tablesort/customsort.js"></script>
	<script type="text/javascript" src="framework/uri/src/URI.min.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.jquery.js"></script>
	<script type="text/javascript" src="framework/combobox/ajax-chosen/ajax-chosen.js"></script>
	<script type="text/javascript" src="framework/combobox/chosen.default.js"></script>
	<script type="text/javascript" src="framework/functions/default.js"></script>
	<script type="text/javascript" src='framework/jquery-freezeheader/js/jquery.freezeheader.js'></script>
	<script type="text/javascript">jQuery.noConflict();</script>
	
<script>
	function getContent(DivName,url) {
		var http_request=false;
		if(window.XMLHttpRequest){ // Mozilla, Safari, ...
			http_request=new XMLHttpRequest();
			if(http_request.overrideMimeType){
				http_request.overrideMimeType('text/xml');
				//See note below about this line
			}
		}else if(window.ActiveXObject){ // IE
			try{
				http_request=new ActiveXObject("Msxml2.XMLHTTP");
				}catch(e){
				try{
					http_request=new ActiveXObject("Microsoft.XMLHTTP");
				}catch(e){}
			}
		}
		if(!http_request){
			alert('Cannot create an XMLHTTP instance');
			document.getElementById(DivName).innerHTML='';
			return false;
		}
		http_request.onreadystatechange=function(){alertContents(http_request,DivName);};
		http_request.open('GET',url,true);
		http_request.send(null);
	}
	function alertContents(http_request,DivName){
		if(http_request.readyState==4){
			if(http_request.status==200){
				document.getElementById(DivName).innerHTML=http_request.responseText;
			}else{
				alert('There was a problem with the request.');
			}console.log(http_request);
		}else if(http_request.readyState==1){
			document.getElementById(DivName).innerHTML="Please Wait...";
		}
	}
</script>
</head>
<body>
	
		<h2>
			Posting Data
		</h2>

		<div style="display: inline-block; float: left; margin-top: 6px; margin-right: 15px;">
				<select name="BK_NBR" id="BK_NBR" style='width:150px' class="chosen-select" >
				<?php
					$query="SELECT BK_NBR, BEG_DTE, END_DTE, CONCAT(BEG_DTE, ' s/d ',END_DTE) AS TANGGAL, MONTH(BEG_DTE) AS BK_MONTH, YEAR(BEG_DTE) AS BK_YEAR 
							FROM RTL.ACCTG_BK WHERE DEL_NBR = 0 ORDER BY 3";
					
					$result = mysql_query($query);
					
					$bulan = array("", "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember");	
					
					while($row = mysql_fetch_array($result)) {
					
						if($row['BK_NBR'] == $bookNumber){ $pilih="selected";}
							else {$pilih="";}

							echo("<option value=".$row['BK_NBR']." ".$pilih.">".$bulan[$row['BK_MONTH']]." ".$row['BK_YEAR']."</option>"."\n");
					}
				?>
		</select>
		</div>

		<br /><br /><br /><br />

		
	<table>
	<tr>
		<td>
			<div>
			<label>Posting Data Revenue Digital Printing </label><br />
			<div style='border:none;margin:10px 10px 10px 20px;background:none;color:gray' id='posting-revenue'></div>
			<div style='border:1px #cccccc solid;padding:5px;width:100px;margin-top:5px;margin-bottom:5px;cursor:pointer;border-radius:3px'
				onclick="getContent('posting-revenue','ajax/accounting/posting-revenue.php?personNBR=<?php echo $_SESSION['personNBR']?>&BK_NBR=' + document.getElementById('BK_NBR').value);selLeftMenu(this);">Process</div><br />
			</div>
		</td>
		
		<td style='width:25%'>
		</td>
		
		<td>
			<div>
			<label>Posting Data Pengeluaran Kas</label><br />
			<div style='border:none;margin:10px 10px 10px 20px;background:none;color:gray' id='posting-cost-cash'></div>
			<div style='border:1px #cccccc solid;padding:5px;width:100px;margin-top:5px;margin-bottom:5px;cursor:pointer;border-radius:3px'
			onclick="getContent('posting-cost-cash','ajax/accounting/posting-cost-cash.php?personNBR=<?php echo $_SESSION['personNBR']?>&BK_NBR=' + document.getElementById('BK_NBR').value);selLeftMenu(this);">Process</div><br />
		</div>
		<td>
			
		</td>
	</tr>
	
	<tr>
		<td>
			<div>
			<label>Posting Data Pembelian Bahan Baku & Penolong </label><br />
			<div style='border:none;margin:10px 10px 10px 20px;background:none;color:gray' id='posting-purchase'></div>
			<div style='border:1px #cccccc solid;padding:5px;width:100px;margin-top:5px;margin-bottom:5px;cursor:pointer;border-radius:3px'
				onclick="getContent('posting-purchase','ajax/accounting/posting-purchase.php?personNBR=<?php echo $_SESSION['personNBR']?>&BK_NBR=' + document.getElementById('BK_NBR').value);selLeftMenu(this);">Process</div><br />
			</div>
		</td>
		
		<td style='width:25%'>
		</td>
		
		<td>
			<div>
			<label>Posting Data Payroll </label><br />
			<div style='border:none;margin:10px 10px 10px 20px;background:none;color:gray' id='posting-payroll'></div>
			<div style='border:1px #cccccc solid;padding:5px;width:100px;margin-top:5px;margin-bottom:5px;cursor:pointer;border-radius:3px'
			onclick="getContent('posting-payroll','ajax/accounting/posting-payroll.php?personNBR=<?php echo $_SESSION['personNBR']?>&BK_NBR=' + document.getElementById('BK_NBR').value);selLeftMenu(this);">Process</div><br />
			</div>
		</td>
	</tr>
	
	
	<tr>
		<td>
			<div>
			<label>Posting Data Click Charge </label><br />
			<div style='border:none;margin:10px 10px 10px 20px;background:none;color:gray' id='posting-click'></div>
			<div style='border:1px #cccccc solid;padding:5px;width:100px;margin-top:5px;margin-bottom:5px;cursor:pointer;border-radius:3px'
				onclick="getContent('posting-click','ajax/accounting/posting-click.php?personNBR=<?php echo $_SESSION['personNBR']?>&BK_NBR=' + document.getElementById('BK_NBR').value);selLeftMenu(this);">Process</div><br />
			</div>
		</td>
		
		<td style='width:25%'>
		</td>
		
		<td>
			<div>
			<label>Posting Data HPP/Checkout</label><br />
			<div style='border:none;margin:10px 10px 10px 20px;background:none;color:gray' id='posting-hpp'></div>
			<div style='border:1px #cccccc solid;padding:5px;width:100px;margin-top:5px;margin-bottom:5px;cursor:pointer;border-radius:3px'
				onclick="getContent('posting-hpp','ajax/accounting/posting-hpp.php?personNBR=<?php echo $_SESSION['personNBR']?>&BK_NBR=' + document.getElementById('BK_NBR').value);selLeftMenu(this);">Process</div><br />
			</div>
		
		</td>
	</tr>
	
	<tr>
		<td>
			<div>
			<label>Posting Data Biaya </label><br />
			<div style='border:none;margin:10px 10px 10px 20px;background:none;color:gray' id='posting-fee'></div>
			<div style='border:1px #cccccc solid;padding:5px;width:100px;margin-top:5px;margin-bottom:5px;cursor:pointer;border-radius:3px'
				onclick="getContent('posting-fee','ajax/accounting/posting-fee.php?personNBR=<?php echo $_SESSION['personNBR']?>&BK_NBR=' + document.getElementById('BK_NBR').value);selLeftMenu(this);">Process</div><br />
			</div>
		</td>
		
		<td style='width:25%'>
		</td>
		
		<td>
			<div>
			<label>Posting Data Pengeluaran Rutin</label><br />
			<div style='border:none;margin:10px 10px 10px 20px;background:none;color:gray' id='posting-routine'></div>
			<div style='border:1px #cccccc solid;padding:5px;width:100px;margin-top:5px;margin-bottom:5px;cursor:pointer;border-radius:3px'
				onclick="getContent('posting-routine','ajax/accounting/posting-routine.php?personNBR=<?php echo $_SESSION['personNBR']?>&BK_NBR=' + document.getElementById('BK_NBR').value);selLeftMenu(this);">Process</div><br />
			</div>
		
		</td>
	</tr>
	
	<tr>
		<td>
			<div>
			<label>Posting Data Setoran </label><br />
			<div style='border:none;margin:10px 10px 10px 20px;background:none;color:gray' id='posting-deposit'></div>
			<div style='border:1px #cccccc solid;padding:5px;width:100px;margin-top:5px;margin-bottom:5px;cursor:pointer;border-radius:3px'
				onclick="getContent('posting-deposit','ajax/accounting/posting-deposit.php?personNBR=<?php echo $_SESSION['personNBR']?>&BK_NBR=' + document.getElementById('BK_NBR').value);selLeftMenu(this);">Process</div><br />
			</div>
		</td>
		
		<td style='width:25%'>
		</td>
		
		<td>
			<div>
			
			</div>
		
		</td>
	</tr>
	
	</table>
	
	
</body>
</html>


