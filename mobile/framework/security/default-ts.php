<?php
	function getSecurity($userID,$Component,$POSID){
		switch($Component)
		{
			case "AddressBook":
				$Location=0;
				break;
			case "Payroll":
				//0 - Manager, 1 - Direct reports only, 2 - Attendance only
				$Location=1;
				break;
			case "Salary":
				$Location=2;
				break;
			case "Calendar":
				$Location=3;
				break;
			case "Inventory":
				$Location=4;
				break;
			case "Stationery":
				$Location=5;
				break;
			case "DigitalPrint";
				$Location=6;
				break;
			case "Executive";
				//0 - Owner/CEO, 2 - VP, 3 - Dir, 4 - Mgr, 5 - Admin, 6 - Supervisor, 7 - Coordinator
				$Location=7;
				break;
			case "Finance";
				$Location=8;
				//0 - All access, 1 - 1000000 (Controller), 2 - 100000 (Cashier)
				break;
			case "Accounting";
				$Location=9;
				break;
			default:
				$Location=-1;
		}
		if($Location!=-1){
			$query="SELECT SEC_KEY FROM CMP.PEOPLE PPL INNER JOIN CMP.POS_TYP POS ON PPL.POS_TYP=POS.POS_TYP WHERE PRSN_ID='".$userID."'";
			$result=mysql_query($query);
			$row=mysql_fetch_array($result);
			if(substr($row['SEC_KEY'],$Location,1)==9){
				echo "<script>parent.location='cashier.php?POS_ID=".$POSID."&TS=1'</script>";
				exit;
			}else{
				return substr($row['SEC_KEY'],$Location,1);
			}
		}
	}
?>