<?php
	function paramCloud(){
		//mysql_connect("192.168.1.20","root","Pr0reliance");
		mysql_connect("localhost","root");
		mysql_select_db("nst");
	
		$query="SELECT CLD_F FROM NST.PARAM_LOC";
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
        $cldF=$row['CLD_F'];
        
        //$OLTP='192.168.1.20';
		//$OLTA='192.168.1.10';
        $OLTP='localhost';
        $OLTA='localhost';

        $query="SELECT TAX_LOCK FROM NST.PARAM_LOC";
        $result=mysql_query($query);
        $row=mysql_fetch_array($result);
        $locked=$row['TAX_LOCK'];

        if($locked==0){
            $defServer=$OLTP;
        }else{
            $defServer=$OLTA;
        }

        //mysql_connect($defServer,"root","Pr0reliance");
        mysql_connect($defServer,"root");
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