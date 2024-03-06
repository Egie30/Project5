<?php 
include_once "framework/email/config.php"; 
include_once "framework/email/smtp/PHPMailerAutoload.php";
		$email = "ridwan@proreliance.com";
		$mail	= new PHPMailer;  
		$mail->IsSMTP(); 
		$mail->Host = SMTP_HOST; 
		$mail->Port = SMTP_PORT; 
		$mail->SMTPAuth = true; 
		$mail->Username = SMTP_UNAME; 
		$mail->Password = SMTP_PWORD; 
		$mail->Subject = "Perincian Gaji Karyawan"; 
		$mail->AddAddress($email,"Hesty"); 
		$mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);
$email->From = "nestor@champs.asia";
$email->FromName = "Nestor";
		//$mail->AddAttachment($MailDir.$PrsnNbr."-".$PymtDte.".txt");  
		$mail->MsgHTML("<span style='line-height:1.34em;color:rgb(153,153,153);font-size:9px;font-family:Geneva,Verdana,Arial,Helvetica,sans-serif'>This communication contains proprietary information and may be confidential. If you are not the intended recipient, the reading, copying, disclosure or other use of the contents of this e-mail is strictly prohibited and you are instructed to please delete this e-mail immediately.</span>"); 
		$send = $mail->Send(); //Send the mails
		if($send){
			echo "<pre style='font-size:9pt;letter-spacing:-1.25px;'><font style='color:#009933;'>Email sent to ".$email."...  </font></pre>";
		}else{
			echo "<pre style='font-size:9pt;letter-spacing:-1.25px;'><font style='color:#FF3300;'>Email not sent to ".$email."...  ".$mail->ErrorInfo." </font></pre>";
		}	

?>
