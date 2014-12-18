<?php
	
	// COMP 484 Lab 4 mailer.php
	
	require_once 'vendor/autoload.php'; // requires phpmailer
		
	$connection = mysqli_connect("localhost","php_login","zhnxhd","database1"); // connect to database
	
	$timeStamp = getDate(); // current date and time
	$year = $timeStamp['year'];
	$month = $timeStamp['mon'];
	$date = $timeStamp['mday'];
	$hour = $timeStamp['hours'];
	$minute = $timeStamp['minutes'];
	
	$query = "SELECT * FROM messages WHERE sent='0'"; // get messages not yet sent
	$result = mysqli_query($connection, $query);
		
	while ($messageArray = mysqli_fetch_array($result)) { // fetch each row until $result array reaches end
		
		// messageid, date, and time for each unsent message in database
		$_year = $messageArray['year'];
		$_month = $messageArray['month'];
		$_date = $messageArray['date'];
		$_hour = $messageArray['hour'];
		$_minute = $messageArray['minute'];
		$_messageId = $messageArray['messageId'];
		
		// if, elseif statements make year to minute values most to least significant so only messages dated in the past get sent
		if (($_year <= $year) && ($_month <= $month) && ($_date <= $date) && ($_hour <= $hour) && ($_minute < $minute)) {
			
			$updateQuery = "UPDATE messages SET sent='1' WHERE messageId = '$_messageId'";
			mysqli_query($connection, $updateQuery);			
			
			$email = $messageArray['email'];
			$message = $messageArray['message'];
			emailMessage($email, $message);
		}
		
		elseif (($_year <= $year) && ($_month <= $month) && ($_date <= $date) && ($_hour < $hour)) {
			
			$updateQuery = "UPDATE messages SET sent='1' WHERE messageId = '$_messageId'";
			mysqli_query($connection, $updateQuery);			
			
			$email = $messageArray['email'];
			$message = $messageArray['message'];
			emailMessage($email, $message);
		}
		
		elseif (($_year <= $year) && ($_month <= $month) && ($_date < $date)) {
			
			$updateQuery = "UPDATE messages SET sent='1' WHERE messageId = '$_messageId'";
			mysqli_query($connection, $updateQuery);			
			
			$email = $messageArray['email'];
			$message = $messageArray['message'];
			emailMessage($email, $message);
		}
		
		elseif (($_year <= $year) && ($_month < $month)) {
			
			$updateQuery = "UPDATE messages SET sent='1' WHERE messageId = '$_messageId'";
			mysqli_query($connection, $updateQuery);			
			
			$email = $messageArray['email'];
			$message = $messageArray['message'];
			emailMessage($email, $message);
		}
		
		elseif ($_year < $year) {
			
			$updateQuery = "UPDATE messages SET sent='1' WHERE messageId = '$_messageId'";
			mysqli_query($connection, $updateQuery);			
			
			$email = $messageArray['email'];
			$message = $messageArray['message'];
			emailMessage($email, $message);
		}
	}
	
	// uses phpmailer to create email message and send it
	function emailMessage($email, $message) {
		
		$mail = new PHPMailer(); 
		$mail->IsSMTP(); 
		// $mail->Mailer = "smtp";
		$mail->SMTPAuth = true; 
		$mail->SMTPSecure = 'ssl';
		$mail->Host = "ssl://smtp.gmail.com";
		$mail->Port = 465; 
		$mail->IsHTML(true);
		$mail->Username = "aisaac.messageserver@gmail.com";
		$mail->Password = "@Yk2A89%";
		$mail->SetFrom("aisaac.messageserver@gmail.com");
		$mail->Subject = "Message Server Message";
		$mail->Body = $message;
		$mail->AddAddress($email);
 
		$mail->Send();
	}	
?>	
		
