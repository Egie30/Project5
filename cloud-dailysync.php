<?php
include "framework/database/connect-cloud.php";

function cloudCheck(){
	$host=gethostbyname('nestor.asia');
	if($host=='nestor.asia'){
		return false;
	}else{
		return true;
	}
}

function serverCheck($local){
	$dbcloud=mysql_connect("nestor.asia","nestora1_prod","Virtuos0");
	if($dbcloud==false){
		$query="UPDATE NST.PARAM_LOC SET CLD_F=0";
		$result=mysql_query($query,$local);
		return false;
	}else{
		$query="UPDATE NST.PARAM_LOC SET CLD_F=1";
		$result=mysql_query($query,$local);
		return true;
	}
}
		
function setLog($path,$data){
	$create=fopen($path,'a+') or die ('File '.$path.' cannot be opened');
	fwrite($create, $data);
	fclose($create);
}
		
$path		= 'log/';
$datestamp	= date('Y-m-d').'.log';
$log		= $path.$datestamp;

if(!file_exists($log)){
	setLog($log,$data);
}

if(cloudCheck()==true){
	if(serverCheck($local)==true){
        $data.=date('Y-m-d').' '.gmdate("H:i:s",time()+7*3600)." Checking NST.SYNC_LIST for DN sync \n";
        $j=downTable('SYNC_LIST','TBL_NM','NST','nestora1_NST',$local,$cloud);
        $data.=date('Y-m-d').' '.gmdate("H:i:s",time()+7*3600).' ';
        if($j>0){
            $data.="NST.SYNC_LIST has been updated with $j record".($j > 1 ? 's': '')."\n";
        }else{
            $data.="NST.SYNC_LIST is current \n";
        }
       
        $query="SELECT DB_NM,TBL_NM,COL_NM,SYNC_DIR_TYP FROM NST.SYNC_LIST";
        $result=mysql_query($query,$local);
        while($row=mysql_fetch_array($result)){
            if($row['DB_NM']=='CMP'){$curDB=$CMP;}
            elseif($row['DB_NM']=='RTL'){$curDB=$RTL;}
            elseif($row['DB_NM']=='PAY'){$curDB=$PAY;}
			if($row['SYNC_DIR_TYP']=='D'){
                $data.=date('Y-m-d').' '.gmdate("H:i:s",time()+7*3600).' Checking '.$row['DB_NM'].'.'.$row['TBL_NM']." for DN sync\n";
                $j=downTable($row['TBL_NM'],$row['COL_NM'],$row['DB_NM'],$curDB,$local,$cloud);
                $data.=date('Y-m-d').' '.gmdate("H:i:s",time()+7*3600).' ';
                if($j>0){
                    $data.=$row['DB_NM'].'.'.$row['TBL_NM']." has been updated with $j record".($j > 1 ? 's': '')."\n";
                }else{
                    $data.=$row['DB_NM'].'.'.$row['TBL_NM']." is current\n";
                }
            }else{
                $j=0;
                $data.=date('Y-m-d').' '.gmdate("H:i:s",time()+7*3600).' ';
                $data.=$row['DB_NM'].'.'.$row['TBL_NM']." is skipped\n";
            }
        }
/*
		$j=syncTable("PEOPLE","PRSN_NBR","CMP",$CMP,$local,$cloud);
		if($j>0){
			$data.=date('Y-m-d').' '.gmdate("h:i:s",time()+7*3600)." CMP.PEOPLE has been updated with $j record".($j > 1 ? 's': 'less than one')."\n";
		}
		
		$j=syncTable("COMPANY","CO_NBR","CMP",$CMP,$local,$cloud);
		if($j>0){
			$data.=date('Y-m-d').' '.gmdate("h:i:s",time()+7*3600)." CMP.COMPANY has been updated with $j record(s)\n";
		}

		$j=syncTable("EMPL_CRDT","PRSN_NBR,PYMT_DTE","CMP",$CMP,$local,$cloud);
		if($j>0){
			$data.=date('Y-m-d').' '.gmdate("h:i:s",time()+7*3600)." CMP.EMPL_CRDT has been updated with $j record(s)\n";
		}
		
		$j=syncTable("PAYROLL","PRSN_NBR,PYMT_DTE","CMP",$CMP,$local,$cloud);
		if($j>0){
			$data.=date('Y-m-d').' '.gmdate("h:i:s",time()+7*3600)." CMP.PAYROLL has been updated with $j record(s)\n";
		}		

		$j=syncTable("PRN_DIG_TYP","PRN_DIG_TYP","CMP",$CMP,$local,$cloud);
		if($j>0){
			$data.=date('Y-m-d').' '.gmdate("h:i:s",time()+7*3600)." CMP.PRN_DIG_TYP has been updated with $j record(s)\n";
		}		

		$j=syncTable("INVENTORY","INV_NBR","RTL",$RTL,$local,$cloud);
		if($j>0){
			$data.=date('Y-m-d').' '.gmdate("h:i:s",time()+7*3600)." RTL.INVENTORY has been updated with $j record(s)\n";
		}		

		$j=syncTable("CAT","CAT_NBR","RTL",$RTL,$local,$cloud);
		if($j>0){
			$data.=date('Y-m-d').' '.gmdate("h:i:s",time()+7*3600)." RTL.CAT has been updated with $j record(s)\n";
		}		

		$j=syncTable("CAT_SUB","CAT_SUB_NBR","RTL",$RTL,$local,$cloud);
		if($j>0){
			$data.=date('Y-m-d').' '.gmdate("h:i:s",time()+7*3600)." RTL.CAT_SUB has been updated with $j record(s)\n";
		}		

		$j=syncTable("CAT_PRC","CAT_PRC_NBR","RTL",$RTL,$local,$cloud);
		if($j>0){
			$data.=date('Y-m-d').' '.gmdate("h:i:s",time()+7*3600)." RTL.CAT_PRC has been updated with $j record(s)\n";
		}		

		$j=syncTable("MACH_CLOK","CLOK_NBR","CMP",$CMP,$local,$cloud);
		if($j>0){
			$data.=date('Y-m-d').' '.gmdate("h:i:s",time()+7*3600)." CMP.MACH_CLOK has been updated with $j record(s)\n";
		}
        
        $j=downTable("ORD_TYP","ORD_TYP","CMP",$CMP,$local,$cloud);
		if($j>0){
			$data.=date('Y-m-d').' '.gmdate("h:i:s",time()+7*3600)." CMP.ORD_TYP has been updated with $j record".($j > 1 ? 's': '')."\n";
		}
*/

		if($data!=''){
			setLog($log,$data);
		}
			
		$periode=14;
		$day=14;
		while($day<=$periode){
			$days=date("Y-m-d",mktime(0,0,0,date('m'),date('d')-$day,date('Y')));
			
			$file=$path.$days.'.log';
			if(file_exists($file)){
				$delete=unlink("$file");
			}	
			$day++;
		}
	}else{
		$dbcloud=serverCheck($local);
		if($dbcould){
			$data =date('Y-m-d').' '.gmdate("h:i:s",time()+7*3600)." Database server is up\n";
		}else{
			$data =date('Y-m-d').' '.gmdate("h:i:s",time()+7*3600)." Database server is down\n";
		}	
		setLog($log,$data);
	}
}else{
	$cloud=cloudCheck();
	if($cloud){
		$data =date('Y-m-d').' '.gmdate("h:i:s",time()+7*3600)." Cloud is up\n";
	}else{
		$data =date('Y-m-d').' '.gmdate("h:i:s",time()+7*3600)." Cloud is down\n";
	}
	setLog($log,$data);
}
echo "<pre>".$data."</pre>";
?>