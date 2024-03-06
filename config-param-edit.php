<?php
if($_GET['opr']=='edit'){
$opr='edit';
$main=' value="'.$_GET['MAIN'].'" readonly ';
$sub=' value="'.$_GET['SUB'].'" readonly ';
$data=' value="'.$_GET['DATA'].'"  ';
}else{$opr='add';}
	
?>
<?php if($_GET['opr']=='delete') {?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">


<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />

<link rel="stylesheet" type="text/css" media="screen" href="framework/alert/alert.css" />
<style type="text/css">
body {
    border: medium none;
    font-family: 'HelveticaNeue','Helvetica Neue',Helvetica,Arial,sans-serif;
    font-size: 10pt;
    font-weight: 400;

}
td.alert{
	padding:0 0 20px;
}
table.alert {
	font-size:85%;
}
#button{
margin-left:280px;
}
</style>
</head>

<body>

<table class='alert'><tr class='alert'>
<td class='alert' width='80'><img class='alert' src='framework/alert/info.png' style='vertical-align:top;'></td>
<td class='alert' width='400' ><span class='alert-title'>Menghapus Parameter</span><br /><br />Parameter ini akan dihapus. Apakah operasi akan diteruskan?<br/>
</td>
</tr></table>
<div id="button">
<input class='alert' type="button"  value="Ya" onclick="parent.document.getElementById('content').src=
	'config-param.php?opr=del&MAIN=<?php echo $_GET['MAIN'];?>&SUB=<?php echo $_GET['SUB'];?>&DATA=<?php echo $_GET['DATA'];?>';
	parent.document.getElementById('deleteAlert').style.display='none';
	parent.document.getElementById('fade').style.display='none';"/>
<input class='alert' type="button" value="Tidak" onclick="parent.document.getElementById('deleteAlert').style.display='none';parent.document.getElementById('fade').style.display='none'" />	
</div>
<?php }else{ ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">


<head>

<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<link rel="stylesheet" type="text/css" media="screen" href="css/popup.css" />
<link rel="stylesheet" type="text/css" media="screen" href="framework/alert/alert.css" />
</head>

<body>

<img class="toolbar-left" style="cursor:pointer" src="img/close.png" onclick="parent.document.getElementById('printDigitalPopupJournal').style.display='none';parent.document.getElementById('fade').style.display='none'"></a></p>
<form enctype="multipart/form-data" action="#" method="post" style="width:340px;height:90px;" onSubmit="return checkform();">
	<table>
<tr><td>Main Title</td><td><input type='text' name='main' id='main' <?php echo $main;?>  size='40' /></td></tr>
<tr><td>Sub Title</td><td><input type='text' name='sub' id='sub' <?php echo $sub;?> size='40'/></td></tr>
<tr><td>Value Data</td><td><input type='text' name='data' id='data' <?php echo $data;?> size='40'/></td></tr>
	</table>
	<br />
	<input class="process" type="button" value="Simpan" onclick="parent.document.getElementById('content').src=
	'config-param.php?opr=<?php echo $opr;?>&MAIN='+document.getElementById('main').value+'&SUB='+document.getElementById('sub').value+'&DATA='+document.getElementById('data').value;
	parent.document.getElementById('printDigitalPopupJournal').style.display='none';
	parent.document.getElementById('fade').style.display='none';"/>
</form>	
<?php } ?>

</body>
</html>


