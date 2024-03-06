<?php
	include "framework/functions/default.php";
    include "framework/database/connect.php";
    include "framework/functions/crypt.php";

	$query="SELECT ORD_STT_ORD,STT.ORD_STT_ID,ORD_STT_DESC,ORD_STT_DET_F,COALESCE(COUNT(*),0) AS STT_NBR
			FROM CMP.PRN_DIG_STT STT
			LEFT OUTER JOIN CMP.PRN_DIG_ORD_HEAD HED ON STT.ORD_STT_ID=HED.ORD_STT_ID
			WHERE ORD_NBR IS NOT NULL AND STT.ORD_STT_ID!='CP'
			AND DEL_NBR=0
			GROUP BY 1,2,3,4
			ORDER BY 1";
	$result=mysql_query($query);
    
    while($row=mysql_fetch_array($result)){
        $str.=$row['ORD_STT_ID'].",".returnIcon($row['ORD_STT_ID']).",".$row['ORD_STT_DESC'].",".$row['STT_NBR'].";";
    }
    echo simple_crypt(substr($str,0,-1));

    function returnIcon($status)
    {
        switch($status) {
        case "NE":
            return "file-o";
        break;
        case "RC":
            return "picture-o";
        break;
        case "LT":
            return "object-group";
        break;
        case "PF":
            return "thumbs-up";
        break;
        case "QU":
            return "hourglass-half";
        break;
        case "PR":
            return "print";
        break;
        case "FN":
            return "scissors";
        break;
        case "RD":
            return "align-justify";
        break;
        case "DL":
            return "truck";
        break;
        case "NS":
            return "flag";
        break;
        case "CP":
            return "check";
        break;
        default:
            return "circle-thin";
        }
    }
?>