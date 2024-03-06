<?php
	function jobLength($type,$q,$len=0,$wid=0){
		$time=0;
		
		//Get equipment type
		$query="SELECT PRN_DIG_EQP FROM CMP.PRN_DIG_TYP WHERE PRN_DIG_TYP='".$type."'";
		$result=mysql_query($query);
		$row=mysql_fetch_array($result);
		$equipment=$row['PRN_DIG_EQP'];
		
		//Add setup time
		switch($equipment)
		{
			case "FLJ320P":
				$time+=10;
				break;
			case "KMC6501":
				$time+=5;
				break;
			case "RVS640":
				$time+=10;
				break;
			case "AJ1800F":
				$time+=10;
				break;
			case "MVJ1624":
				$time+=10;
				break;
			case "ATX67":
				$time+30;
				break;
			case "CLAM":
				$time+=5;
				break;
			case "HLAM":
				$time+=30;
				break;
			default:
				$time+=10;
		}
		
		//Add processing time
		switch($equipment)
		{
			case "FLJ320P":
				$time+=$q*$len*$wid/50*60;
				break;
			case "KMC6501":
				$time+=$q/50*3;
				break;
			case "RVS640":
				$time+=$q*$len*wid/8*60;
				break;
			case "AJ1800F":
				$time+=$q*$len*wid/28*60;
				break;
			case "MVJ1624":
				$time+=$q*$len*wid/8*60;
				break;
			case "ATX67":
				$time+=$q*$len*wid/30*60;
				break;
			case "CLAM":
				$time+=$q*$len*$wid/24*60;
				break;
			case "HLAM":
				$time+=$q/6;
				break;
			default:
				$time+=60;
		}
		
		//Add finising time -- WIP
		switch($equipment)
		{
			case "FLJ320P":
				$time+=30;
				break;
			case "KMC6501":
				$time+=$q/50*2;
				break;
			case "RVS640":
				$time+=20;
				break;
			case "AJ1800F":
				$time+=20;
				break;
			case "MVJ1624":
				$time+=20;
				break;
			case "CLAM":
				$time+=20;
				break;
			case "HLAM":
				$time+=20;
				break;
			default:
				$time+=0;
		}		
		return $time;
	}
	function prodCapacity($type,$value){
		//Add finising time -- WIP
		switch($type)
		{
			case "FLJ320P":
				$capacity=$value/getProdCpctyEqp('FLJ320P');
				break;
			case "KMC6501":
				$capacity=$value/getProdCpctyEqp('KMC6501');
				break;
			case "RVS640":
				$capacity=$value/getProdCpctyEqp('RVS640');
				break;
			case "AJ1800F":
				$capacity=$value/getProdCpctyEqp('AJ1800F');
				break;
			case "MVJ1624":
				$capacity=$value/getProdCpctyEqp('MVJ1624');
				break;
			default:
				$capacity=0;
		}
		if($capacity>1){$capacity=1;}
		return $capacity;
	}

	function getProdCpctyEqp($PrnDigEqpDesc){
		$table = "PROD_CPCTY_".$PrnDigEqpDesc;
		$query = "SELECT $table FROM NST.PARAM_LOC";
		$result= mysql_query($query);
		$row   = mysql_fetch_array($result);
		$cpcty = $row[$table];
		return $cpcty;
	}
?>