<?php
	include "framework/database/connect-cashier.php";
	include "framework/security/default-ts.php";
	include "framework/functions/default.php";
	
	if (isset($_GET['AUT'])) {
    $Auth = $_GET['AUT'];
		} else {
			$Auth = 0;
		}

	$POSID=$_GET['POS_ID'];
	$TOUCH=$_GET['TS'];
	
	$loginUserID=$_POST['userID'];
	$password=$_POST['password'];

	if($password!=''){
	//TouchScreen login cek
		if($TOUCH==1){	
			$query="SELECT PRSN_NBR,PRSN_ID,PWD FROM CMP.PEOPLE PPL INNER JOIN CMP.POS_TYP POS ON PPL.POS_TYP=POS.POS_TYP WHERE PRSN_NBR=SUBSTRING('".$password."', 1, length('".$password."')-1) AND TERM_DTE IS NULL";
			$result=mysql_query($query, $cmp);
			$row=mysql_fetch_array($result);
			$Security=getSecurity($row['PRSN_ID'],"Finance",$POSID);
			
			if($password==luhn($row['PRSN_NBR']) && $Security<=2){
				$loginUserID=$row['PRSN_ID'];
				$password=$row['PWD'];				
			}else{
				$loginUserID='';
				$password='';			
			}
		}
		$query="SELECT SEC_KEY,PRSN_NBR FROM CMP.PEOPLE PPL INNER JOIN CMP.POS_TYP POS ON PPL.POS_TYP=POS.POS_TYP WHERE PRSN_ID='".$loginUserID."' AND (PWD='".$password."' OR PWD='".hash('sha512',$password)."') AND TERM_DTE IS NULL";
		$result=mysql_query($query, $cmp);
		$row=mysql_fetch_array($result);
		
		if(mysql_num_rows($result) > 0){
			$warning="<font color='red'>Identitas atau kata sandi salah.</font><br />";
			        session_regenerate_id(true);
        
					// Discount Security
					if ($Auth == 1) {
						$_SESSION['discSEC'] = $loginUserID;
					}
					
					$_SESSION['userID']        = (string) $loginUserID;
					$_SESSION['personNBR']     = (int) $row['PRSN_NBR'];
					$_SESSION['personName']    = (string) $row['NAME'];
					$_SESSION['CREATED']       = time();
					$_SESSION['LAST_ACTIVITY'] = time();
					$_SESSION['NST_ACCESS']    = time();
					$_SESSION['PLUS_MODE']     = $plusMode; // Hold plus mode value in Session
					
					// header('Location:cashier-search.php?POS_ID='.$POSID);
					echo "<script text='type/javascript'>window.top.location.href='cashier.php?POS_ID=" . $POSID . "'</script>";
					exit(0);
		}else{
			$warning = "Identitas atau kata sandi salah.";
		}
		
	}elseif($_GET['COMMAND']=="LOGOUT"){
		unset($_SESSION['userID']);
		unset($_SESSION['personNBR']);
	}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>

<meta http-equiv="content-type" content="text/html;charset=ISO-8859-1"><title>Nestor Sign In</title>
	
<style type="text/css" media="screen">
@import url(championbaru/css/font-san-francisco.css);
body 
	{
	font-family: 'San Francisco Display', 'HelveticaNeue-Light', 'Helvetica Neue Light', Helvetica, Arial, sans-serif;
    color:black;
	margin: 0px;
	background:#222222 url(img/login/login<?php echo rand(1,27); ?>.jpg) no-repeat;
	//background-attachment:fixed;
	
	-webkit-background-size: cover;
	-moz-background-size: cover;
	background-size:cover;
	//background:#354975; /* for non-css3 browsers */

	//filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#010310', endColorstr='#20315d'); /* for IE */
	//background: -webkit-gradient(linear, left top, left bottom, from(#010310), to(#20315d), color-stop(.6,#354975)); /* for webkit browsers */
	//background: -moz-linear-gradient(top, #010310 0%, #354975 60%, #20315d 100%); /* for firefox 3.6+ */
	}

#horizon        
	{
	color: #cccccc;
	text-align: left;
	position: absolute;
	top: 50%;
	left: 0px;
	width: 100%;
	height: 1px;
	overflow: visible;
	visibility: visible;
	display: block
	}

#content    
	{
	font-family: 'San Francisco Display', 'HelveticaNeue-Light', 'Helvetica Neue Light', Helvetica, Arial, sans-serif;
	font-weight: 300;
	margin-left: -200px;
	position: absolute;
	top: -200px;
	left: 50%;
	width: 400px;
	height: 70px;
	visibility: visible;
	}

.bodytext 
	{
	font-size: 11pt;
	}

.headline 
	{
	font-weight: bold;
	font-size: 24px
	}
#footer 
	{
	font-size: 9pt;
	color:#999999;
	font-family: 'San Francisco Display', 'HelveticaNeue-Light', 'Helvetica Neue Light', Helvetica, Arial, sans-serif;
	font-weight:300;
	text-align: center;
	position: absolute;
	bottom: 10px;
	left: 0px;
	width: 100%;
	height: 20px;
	visibility: visible;
	display: block
}
.captions  
	{
	color: white;
	font-size: 9pt;
	line-height: 14px;
	font-family: 'San Francisco Display', 'HelveticaNeue-Light', 'Helvetica Neue Light', Helvetica, Arial, sans-serif;
	font-weight:300;
	text-align: left
	}

#caption1    
	{
	padding-left: 6px;
	position: absolute;
	font-size:16px;
	top: -30px;
	left: -75px;
	width: 350px;
	height: 20px;
	visibility: visible;
	display: block
	}

#caption2    
	{
	padding-left: 6px;
	position: absolute;
	top: 70px;
	left: 300px;
	width: 250px;
	height: auto;
	visibility: visible;
	display: block
	}

input.field {
	height:30px;
	margin: 3px 0px;
	padding: 2px 10px;
	color:#ffffff;
	border:0px;
    border-bottom:1px #888888 solid;
	//background-color:#cccccc;
    background:transparent;
	font-weight:300;
	font-size:11pt;
    font-family: 'San Francisco Display', 'HelveticaNeue-Light', 'Helvetica Neue Light', Helvetica, Arial, sans-serif;
    width:285px;
    outline: none;
    -webkit-transition: background 0.2s linear, border 0.2s linear, opacity 0.2s linear;
    -moz-transition: background 0.2s linear, border 0.2s linear, opacity 0.2s linear;
    transition: background 0.2s linear, border 0.2s linear, opacity 0.2s linear;
    border-radius: 0;
}

input.field:focus {
    border-bottom:1px #ffffff solid;
    color:#ffffff;
}

input.process {
    font-family: 'San Francisco Display', 'HelveticaNeue-Light', 'Helvetica Neue Light', Helvetica, Arial, sans-serif;
	font-size:11pt;
	/* styles for button */
	text-align:center;
	vertical-align:top;
	width:75px;
	height:34px;
	//padding: 3px 10px 4px 10px;
	color: #fff;
	text-decoration: none;
	line-height: 1;
	margin-top:10px;
	margin-bottom:10px;
	
	/* button color */
	background-color: #3464bc;
	
	/* css3 implementation :) */
	/* rounded corner */
	border-radius:4px;
	-moz-border-radius:4px;
	-webkit-border-radius:4px;
	
	border:0px;
	position: relative;
	cursor: pointer;

    -webkit-transition: background 0.2s linear;
    -moz-transition: background 0.2s linear;
    transition: background 0.2s linear;
    
    -webkit-appearance:none;
}

input.process:hover, input.process:focus {
	background-color: #204ba3;	
}


div.login {
	width:400px;
	//border:solid 1px #4964a1;
	padding-top:10px;
	padding-bottom:30px;
	border-radius:5px;
	-moz-border-radius: 5px;
	-webkit-border-radius: 5px;
	box-shadow: 0 0 6px 2px rgba(0, 0, 0, 0.1);
    text-align:center;
}

.pace .pace-progress {
  background: #007aff;
  position: fixed;
  z-index: 2000;
  top: 0;
  left: 0;
  height: 2px;

  -webkit-transition: width 1s;
  -moz-transition: width 1s;
  -o-transition: width 1s;
  transition: width 1s;
}

.pace-inactive {
  display: none;
}


</style>

<script type='text/javascript' src='framework/database/jquery.min.js'></script>
<script type='text/javascript' src='framework/popup/blur.js'></script>

<link rel="shortcut icon" href="favicon.ico" />

</head>

<body>
	<div style="width:100%;height:100%">
	</div>
	<div id="horizon">
		<div id="content">
			<div class="bodytext">
				<div id="caption1" class="captions"></div>
				<form action="cashier-login.php?POS_ID=<?php echo $POSID.'&TS='.$TOUCH; ?>" method="post">
					<div class="login">
						<img src="img/nestor-logo-black-outline.svg" style='padding-top:15px;width:200px'><br/><br/>
						<font style='font-weight:400;font-size:12pt'>Sign in to Nestor</font><br/><br/>
						<input class="field" name="userID" type="text" placeholder="Identitas" autocomplete="off" autofocus /><br/>
						<input class="field" name="password" type="password" placeholder="Kata sandi" /><br/><br/>
						<?php
							echo "$warning";
						?>
						<input class="process" style="cursor:pointer;" type="submit" value="Masuk" /><br/>
						<br/>
						<div style='font-size:9pt;color:#cccccc;padding-bottom:10px;padding-bottom:0px;'><img src="img/pro-logo.svg" style='width:18px;vertical-align:-5px'>&nbsp;&nbsp;Copyright &copy 2008-<?php echo date('Y'); ?> proreliance.com. All rights reserved.</div>
					</div>
				</form>
				<div id="caption2" class="captions"></div>
			</div>
		</div>
	</div>
	<div id="footer">
        <div style='padding-left:20px'>
            Photography by Stanley Onggowijaya
        </div>
	</div>
</body>
<script>
	$(document).ready(function(){
		$('.login').blurjs({
			source: 'body',
			overlay: 'rgba(0,0,0,.35)',
			radius:20
		});
	});
</script></html>