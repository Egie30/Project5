<?php
	function paramCloud(){
        if(substr($_SERVER['REMOTE_ADDR'],0,8)==substr($_SERVER['SERVER_ADDR'],0,8)){
		$OLTP='localhost';
        $OLTA='localhost';
    }else{
        $OLTP='localhost';
        $OLTA='localhost';
    }

		mysql_connect($OLTP,"root","");
		mysql_select_db("nst");
	
		$query="SELECT CLD_F FROM NST.PARAM_LOC";
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
        $cldF=$row['CLD_F'];

        $query="SELECT TAX_LOCK FROM NST.PARAM_LOC";
        $result=mysql_query($query);
        $row=mysql_fetch_array($result);
        $locked=$row['TAX_LOCK'];

        if($locked==0){
            $defServer=$OLTP;
        }else{
            $defServer=$OLTA;
        }

        mysql_connect($defServer,"root","");
        mysql_select_db('cmp');
        
        return $cldF;
    }
	
	function dumpText($fileName)
	{
		$handle=fopen($fileName,"r");
		$theData=fread($handle, filesize($fileName));
		echo $theData;
	}
?>
