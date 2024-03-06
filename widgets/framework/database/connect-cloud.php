<?php
	session_start();
	include_once "function_db.php";
	
	$xml=simplexml_load_file("http://localhost/data/param.xml");

	$accessCloud=$xml->config->cloud;

    $OLTP='192.168.1.20';
    $OLTA='192.168.1.10';

	$_SESSION['userID']=$_SESSION['userID'];
	$_SESSION['personNBR']=$_SESSION['personNBR'];

    //db connection OLTA
    mysql_connect($OLTP,"root","Pr0reliance");
    mysql_select_db("cmp");
    
    $query="SELECT TAX_LOCK FROM NST.PARAM_LOC";
    $result=mysql_query($query);
    $row=mysql_fetch_array($result);
    $locked=$row['TAX_LOCK'];

    if($locked==0){
        $defServer=$OLTP;
    }else{
        $defServer=$OLTA;
    }  

    if($display=='TRIM'){
        $defServer=$OLTA;
    }
	
	if($accessCloud!=0){
		$CMP="nestora1_CMP";
		$RTL="nestora1_RTL";
		$NST="nestora1_NST";
	}else{
		$CMP="CMP";
		$RTL="RTL";
		$NST="NST";
		$host=gethostbyname('nestor.asia');
	}
	
	if(($accessCloud!=0)&&(paramCloud()==1)){
		if($host=='nestor.asia'){
			$cloud=false;

		}else{
			$cloud=mysql_connect("nestor.asia:3306","nestora1_prod","Virtuos0");
			if($cloud!=false){
				mysql_select_db($CMP,$cloud);
			}
		}
	}else{
        $cloud=mysql_connect($defServer,"root","Pr0reliance");
	}
	
    $local=mysql_connect($defServer,"root","Pr0reliance");
	mysql_select_db('cmp',$local);
	
	//One-directional sync
	function syncTable($tableName,$primaryKey,$localDB,$cloudDB,$local,$cloud){
	$xml=simplexml_load_file("data/param.xml");
	$accessCloud=$xml->config->cloud;
		if($accessCloud!=0){
			//Determine all records to be brought down
			$query="SELECT MAX(UPD_TS) AS UPD_TS FROM $localDB.$tableName";
			$result=mysql_query($query,$local);
			$row=mysql_fetch_array($result);
			$MaxLocal=$row['UPD_TS'];
			
			//Update cloud columns and data type
			$query="SELECT * FROM $cloudDB.$tableName WHERE UPD_TS>'$MaxLocal'";
			//$query="SELECT * FROM $cloudDB.$tableName WHERE UPD_TS>='$MaxLocal'";
			//echo $query;
			$result=mysql_query($query,$cloud);
			for($i=0;$i<mysql_num_fields($result);$i++){
		    	$field_info=mysql_fetch_field($result,$i);
		 		$fieldNames[]="$field_info->name";    
		 		$fieldTypes[]="$field_info->type";
				//echo "$field_info->name".",";
				//echo "$field_info->type".",";
			}
		
			$fields=explode(',',$primaryKey);
		
			//Update record-by-record
			$j=0;
			while($row=mysql_fetch_array($result,MYSQL_ASSOC)){
				//Determine whether it is a new record or existing record
				$where="";			
				foreach($fields as $field){
					if(is_numeric($row[$field])){
						$where.=$field."=".$row[$field];
					}else{
						$where.=$field."='".$row[$field]."'";
					}
					$where.=" AND ";
				}
				$where=substr($where,0,-5);
			
				$query="SELECT $primaryKey FROM $localDB.$tableName WHERE $where";

				//echo $query."</br>";
				$resultd=mysql_query($query,$local);
				$rowd=mysql_fetch_array($resultd);
				$theNbr=$rowd[$fields[0]];
				if($theNbr==""){
					//Add new if no existing record
					$query="INSERT INTO $localDB.$tableName ($primaryKey) VALUES (";
					$value="";
					foreach($fields as $field){
						if(is_numeric($row[$field])){
							$value.=$row[$field];
						}else{
							$value.="'".$row[$field]."'";
						}
						$value.=",";
					}
					$query.=substr($value,0,-1).")";
					//echo $query;
					$resultd=mysql_query($query,$local);
				}
				$query="UPDATE $localDB.$tableName SET ";
				$i=0;
			
				//Contruct the update SQL
				foreach($row as $column){
					$query.=$fieldNames[$i]."=";
					if(($column=='')&&($fieldTypes[$i]!='string')){
		        		$query.="NULL,";
					}elseif(($column=='')&&($fieldTypes[$i]!='date')){
		        		$query.="NULL,";
		        	}elseif($fieldTypes[$i]=='int'){
			        		$query.="$column".",";
		    	    }else{
		        		$query.="'$column'".",";
		        	}
		     		$i++;
		 		}
				$j++;
				$query=substr($query,0,-1);
				$query.=" WHERE $where";
				$resultd=mysql_query($query,$local);
				//echo $query."</br>";
			}
			return $j;
		}else{
			$j=0;
			return $j;
		}
	}	

    //Cloud as master sync
	function downTable($tableName,$primaryKey,$localDB,$cloudDB,$local,$cloud){
		$xml=simplexml_load_file("data/param.xml");
		$accessCloud=$xml->config->cloud;
		if($accessCloud!=0){
            //Compare last updates of the two tables
			$query="SELECT UPDATE_TIME FROM information_schema.TABLES WHERE TABLE_SCHEMA LIKE '".$localDB."' AND TABLE_NAME LIKE '".$tableName."'";
			$result=mysql_query($query,$local);
			$row=mysql_fetch_array($result);
			$UpdateTimeLocal=$row['UPDATE_TIME'];

            $query="SELECT UPDATE_TIME FROM information_schema.TABLES WHERE TABLE_SCHEMA LIKE '".$cloudDB."' AND TABLE_NAME LIKE '".$tableName."'";
			$result=mysql_query($query,$cloud);
			$row=mysql_fetch_array($result);
			$UpdateTimeCloud=$row['UPDATE_TIME'];
            
            //If the local is less than cloud then cloud overrides local -- there is a loophole in this method; an update to cloud during the process will not be reflected. So this sync is being done once a day only to prevent that.
            if(strtotime($UpdateTimeLocal)>strtotime($UpdateTimeCloud)){
                return 0;
            }
            
			//Copy all rows
			$query="SELECT * FROM $cloudDB.$tableName";
			//$query="SELECT * FROM $cloudDB.$tableName WHERE UPD_TS>='$MaxLocal'";
			//echo $query;
			$result=mysql_query($query,$cloud);
			for($i=0;$i<mysql_num_fields($result);$i++){
		    	$field_info=mysql_fetch_field($result,$i);
		 		$fieldNames[]="$field_info->name";    
		 		$fieldTypes[]="$field_info->type";
				//echo "$field_info->name".",";
				//echo "$field_info->type".",";
			}
		
			$fields=explode(',',$primaryKey);
		
			//Update record-by-record
			$j=0;
			while($row=mysql_fetch_array($result,MYSQL_ASSOC)){
				//Determine whether it is a new record or existing record
				$where="";			
				foreach($fields as $field){
					if(is_numeric($row[$field])){
						$where.=$field."=".$row[$field];
					}else{
						$where.=$field."='".$row[$field]."'";
					}
					$where.=" AND ";
				}
				$where=substr($where,0,-5);
			
				$query="SELECT $primaryKey FROM $localDB.$tableName WHERE $where";

				//echo $query."</br>";
				$resultd=mysql_query($query,$local);
				$rowd=mysql_fetch_array($resultd);
				$theNbr=$rowd[$fields[0]];
				if($theNbr==""){
					//Add new if no existing record
					$query="INSERT INTO $localDB.$tableName ($primaryKey) VALUES (";
					$value="";
					foreach($fields as $field){
						if(is_numeric($row[$field])){
							$value.=$row[$field];
						}else{
							$value.="'".$row[$field]."'";
						}
						$value.=",";
					}
					$query.=substr($value,0,-1).")";
					//echo $query;
					$resultd=mysql_query($query,$local);
				}
				$query="UPDATE $localDB.$tableName SET ";
				$i=0;
			
				//Contruct the update SQL
				foreach($row as $column){
					$query.=$fieldNames[$i]."=";
					if(($column=='')&&($fieldTypes[$i]!='string')){
		        		$query.="NULL,";
					}elseif(($column=='')&&($fieldTypes[$i]!='date')){
		        		$query.="NULL,";
		        	}elseif($fieldTypes[$i]=='int'){
			        		$query.="$column".",";
		    	    }else{
		        		$query.="'$column'".",";
		        	}
		     		$i++;
		 		}
				$j++;
				$query=substr($query,0,-1);
				$query.=" WHERE $where";
				$resultd=mysql_query($query,$local);
				//echo $query."</br>";
			}
			return $j;
		}else{
			$j=0;
			return $j;
		}
	}
	
	/* Function dipindah ke framework/function/default.php
	function dumpText($fileName)
	{
		$handle=fopen($fileName,"r");
		$theData=fread($handle, filesize($fileName));
		echo $theData;
	}
	*/
?>
