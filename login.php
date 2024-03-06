<?php
	include "framework/database/connect.php";
	$_SESSION['just_login']	= 1;
	$loginUserID			= mysql_real_escape_string($_POST['userID']);
	$password				= mysql_real_escape_string($_POST['password']);
    if(substr($password,-1)=="~"){
        $display="?DISP=ON";
        $password=substr($password,0,-1);
    }
    if(substr($password,-1)=="+"){
        $display="?DISP=TRIM";
        $password=substr($password,0,-1);
    }
	
	if($_GET['NO_NOTA'] != ""){
        $display="?NO_NOTA=".$_GET['NO_NOTA'];
    }
	if($password!=''){
		$query="SELECT SEC_KEY,PRSN_NBR,PWD FROM CMP.PEOPLE PPL INNER JOIN CMP.POS_TYP POS ON PPL.POS_TYP=POS.POS_TYP WHERE PRSN_ID='".$loginUserID."' AND (PWD='".$password."' OR PWD='".hash('sha512',$password)."') AND TERM_DTE IS NULL";
        $result=mysql_query($query);
		$row=mysql_fetch_array($result);
		if(mysql_num_rows($result)==0){
			$warning="<font color='red'>Identitas atau kata sandi salah.</font><br />";
		}else{
			$warning="<br/>";
			$_SESSION['userID']		= $loginUserID;
			$_SESSION['personNBR']	= $row['PRSN_NBR'];
            //Enforce hashing
            if($row['PWD']==$password){
                $query="UPDATE CMP.PEOPLE SET PWD='".hash('sha512',$password)."' WHERE PRSN_NBR=".$_SESSION['personNBR'];
                //echo $query;
                $result=mysql_query($query);
            }
			header('Location:index.php'.$display);
			exit;
		}
	}elseif($_GET['COMMAND']=="LOGOUT"){
		unset($_SESSION['userID']);
		unset($_SESSION['personNBR']);
	}elseif($_GET['COMMAND']=="LOCK"){
        $defServer=$OLTA;
        mysql_connect($defServer,"root","Pr0reliance");
        mysql_select_db("cmp");
        $query="UPDATE NST.PARAM_LOC SET TAX_LOCK=1";
		$result=mysql_query($query);
    }elseif($_GET['COMMAND']=="UNLOCK"){
        $defServer=$OLTA;
        mysql_connect($defServer,"root","Pr0reliance");
        mysql_select_db("cmp");
        $query="UPDATE NST.PARAM_LOC SET TAX_LOCK=0";
		$result=mysql_query($query);
        $defServer=$OLTP;
    }
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>

<meta http-equiv="content-type" content="text/html;charset=ISO-8859-1"><title>Nestor Sign In</title>

<script src="framework/pace/pace.min.js"></script>
	
<style type="text/css" media="screen"><!--

@import url(css/font-san-francisco.css);

body 
	{
	font-family: 'San Francisco Display', 'HelveticaNeue-Light', 'Helvetica Neue Light', Helvetica, Arial, sans-serif;
    color:black;
	margin: 0px;
	background:#222222 url(img/login/login<?php echo rand(1,71); ?>.jpg) no-repeat;
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
	color:#cccccc;
	text-align:center;
	position:absolute;
	top:50%;
	left:-300px;
	width:100%;
	height:1px;
	overflow:visible;
	visibility:visible;
	display: block
	}

#content    
	{
	font-weight:300;
	margin-left:0;
	position:absolute;
	top:-140px;
	left:50%;
    height:70px;
	width:600px;
    visibility:visible;
	}

.bodytext 
	{
	font-size: 11pt;
	}

.headline 
	{
	font-weight:bold;
	font-size:24px
	}
#footer 
	{
	font-size: 9pt;
	color:#999999;
	font-weight:300;
	text-align:left;
	position:absolute;
	bottom:10px;
	left:0px;
	width:100%;
	height:20px;
	visibility:visible;
	display:block
}
.captions  
	{
	color: white;
	font-size: 9pt;
	line-height: 14px;
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
	padding: 2px 0px;
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
	width:600px;
	//border:solid 1px #4964a1;
	padding-top:10px;
	border-radius:5px;
	-moz-border-radius: 5px;
	-webkit-border-radius: 5px;
	box-shadow: 0 0 6px 2px rgba(0, 0, 0, 0.1);
    text-align:left;
    padding-left:20px;
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
				<form action="login.php<?php echo $display; ?>" method="post">	
					<div class="login">
						<img src="img/nestor-logo-black-outline.svg" style='padding-top:15px;width:200px'><br/><br/>
						<font style='font-weight:400;font-size:12pt'>Sign in to Nestor</font><br/><br/>
						<input class="field" name="userID" type="text" placeholder="Identitas" autocomplete="off" autofocus />&nbsp;
						<input class="field" name="password" type="password" placeholder="Kata sandi" autocomplete="off"/><br/>
						<?php
							echo "$warning";
						?>
						<input class="process" style="cursor:pointer;" type="submit" value="Masuk" /><br/>
						<br/>
						<div style='font-size:9pt;color:#cccccc;padding-bottom:10px'><img src="img/pro-logo.svg" style='width:18px;vertical-align:-5px'>&nbsp;&nbsp;Copyright &copy 2008-<?php echo date("Y"); ?> proreliance.com. All rights reserved.</div>
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
