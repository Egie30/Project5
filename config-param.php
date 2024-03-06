<?php
$xml = simplexml_load_file("data/param.xml"); 
$sxe = new SimpleXMLElement($xml->asXML());

if ($_GET['opr']=='edit'){
	$i=0;
	foreach ($sxe->children() as $node) {
    if($sxe->record[$i]->main == $_GET['MAIN'] && $sxe->record[$i]->sub == $_GET['SUB'] ){
            $sxe->record[$i]->data =  ($_GET['DATA']);
      }
      $i++;
	  }
    $sxe->asXML("data/param2.xml");
	}

if ($_GET['opr']=='add'){
    $query = $sxe->addChild("record");  
    $query->addChild("main", ($_GET['MAIN']));
    $query->addChild("sub",  ($_GET['SUB']));
    $query->addChild("data",  ($_GET['DATA']));
    $sxe->asXML("data/param2.xml");
	}

if ($_GET['opr']=='del'){
	$i=0;
	foreach ($sxe->children() as $node) {
    if($sxe->record[$i]->main == $_GET['MAIN'] && $sxe->record[$i]->sub == $_GET['SUB'] && $sxe->record[$i]->data ==  $_GET['DATA'] ){
            unset($sxe->record[$i]);
            break;
     }
      $i++;
	  }
    $sxe->asXML("data/param2.xml");
	}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>

<script type="text/javascript" src='framework/tablesort/tablesort.js'></script>

	<link rel="stylesheet" type="text/css" media="screen" href="css/screen.css" />
<script src="js/jquery-latest.js"></script>
<script>
function addparam(){
parent.document.getElementById('printDigitalPopupJournalContent').src='config-param-edit.php?NEW=1';
parent.document.getElementById('printDigitalPopupJournal').style.display='block';
				parent.document.getElementById('fade').style.display='block';

}
function editparam(id){
parent.document.getElementById('printDigitalPopupJournalContent').src='config-param-edit.php?opr=edit&MAIN='+
document.getElementById('main'+id).value+'&SUB='+document.getElementById('sub'+id).value+'&DATA='+document.getElementById('data'+id).value;
parent.document.getElementById('printDigitalPopupJournal').style.display='block';
				parent.document.getElementById('fade').style.display='block';

}

function deleteparam(id){
parent.document.getElementById('deleteAlertContent').src='config-param-edit.php?opr=delete&MAIN='+
document.getElementById('main'+id).value+'&SUB='+document.getElementById('sub'+id).value+'&DATA='+document.getElementById('data'+id).value;
parent.document.getElementById('deleteAlert').style.display='block';
				parent.document.getElementById('fade').style.display='block';

}

</script>
</head>
<body>
<div class="toolbar">
	<p class="toolbar-left"><a href="#"><img class="toolbar-left" src="img/add.png" onclick="addparam();"></a></p>
	<p class="toolbar-right"></p>
</div>
<?php

echo( "<table id='mainTable' class='rowstyle-alt colstyle-alt no-arrow searchTable'>
		<thead>
			<tr>
				<th class='sortable'></th>
				<th class='sortable'>Main Title</th>
				<th class='sortable'>Sub Title</th>
				<th class='sortable'>Value Data</th>
			</tr>
		</thead>
		<tbody>

");
	$i=0;
	foreach ($sxe->children() as $node) {
       echo  "<tr><td><a href='#' onclick='editparam(".$i.");'><img src='img/write.png'/></a>
	   <a href='#' onclick='deleteparam(".$i.");'><img src='img/trash.png'/></a>
	  </td>";
	   echo  "<td>".($sxe->record[$i]->main)."<input type='hidden' name='recid' id='recid' value='".$i."' /><input type='hidden' name='main' id='main".$i."' value='".($sxe->record[$i]->main)."' /></td>";
       echo  "<td>".($sxe->record[$i]->sub)."<input type='hidden' name='sub' id='sub".$i."' value='".($sxe->record[$i]->sub)."' /></td>";
       echo  "<td>".($sxe->record[$i]->data)."<input type='hidden' name='data' id='data".$i."' value='".($sxe->record[$i]->data)."' /></td></tr>";
      $i++;
	  }
echo( "</tbody></table>");

/*
 <a href='#' onclick=\"window.scrollTo(0,0);parent.document.getElementById('paramDelete').style.display='block';
	   parent.document.getElementById('fade').style.display='block'\"><img style='cursor:pointer' class='toolbar-left' src='img/delete.png'></a>
	
	
<a href="#" onclick="editparam('.$id.');"><img src="img/write.png"/></a><a href="#" onclick="editparam('.$id.');"><img src="img/trash.png"/></a></td>'; }
    echo( "<td>". $node -> textContent . "<td>");
    if(++$n % 3 == 0) { echo '<input type="hidden" name="LPARAM" id="LPARAM'.$n.'" value="'.$node -> textContent.'" /><input type="hidden" name="VPARAM" id="VPARAM'.$n.'" value="'.$nodeID.'" /></tr>'; }


*/
?>
</body>
</html>