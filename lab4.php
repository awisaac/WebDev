<?php
	
	// COMP 484 Lab 4 Andrew Isaac lab4.php
	
	header("Content-Type: text/json");
	
	// connect to database1;
	$connection = mysqli_connect("localhost","php_login","zhnxhd","database1");
	
	// web service actions determined by task sent from client
	if (isset($_POST['task'])) {
		
		// validates username and retrieves salt. if username is unknown, generates a new salt. 
		if ($_POST['task'] == "getSalt" && validUsername($_POST['username'])) {
			
			$username = mysqli_real_escape_string($connection, $_POST['username']); // all sql input values are sanitized
			
			$query = "SELECT salt FROM users WHERE username ='$username'";
			$salt;
		
			if (mysqli_num_rows(mysqli_query($connection, $query)) > 0) { // username already has a salt
				$saltArray = mysqli_fetch_array(mysqli_query($connection, $query));
				$salt = $saltArray['salt'];
			}
		
			else { // unknown username - new salt generated
				$salt = uniqid(mt_rand(), true);	
			}
		
			$std = new stdClass();
			$std->salt = $salt;
			echo json_encode($std);	// send salt back to client		
		}
		
		// validates username, password, and salt and inserts new user row into users 
		elseif ($_POST['task'] == "createUser" && validUsername($_POST['username']) && validPassHash($_POST['password']) && validSalt($_POST['salt']))  {		
			
			$username = mysqli_real_escape_string($connection, $_POST['username']);
			$clientPasswordHash = mysqli_real_escape_string($connection, $_POST['password']);
			$salt = mysqli_real_escape_string($connection, $_POST['salt']);
			
			$query = "SELECT * FROM users WHERE username ='$username'";
		
			$std = new stdClass();
			
			// username already exists, new credentials not accepted
			if (mysqli_num_rows(mysqli_query($connection, $query)) > 0) {
				$std->exists = true; 
			}
			
			// new credentials saved since username does not already exist
			else {
				
				// Client side hash is de-facto password. By hashing the hash, if hash stored in DB gets compromised, client side hash is still secure.			
				$passwordHash = hash("sha512", $clientPasswordHash);
				
				$query = "INSERT INTO users (username, password, salt) VALUES ('$username', '$passwordHash','$salt')";
				mysqli_query($connection, $query);
				$std->exists = false;			
			}
		
			echo json_encode($std);	// return whether user previously existed, true or false		
		}
		
		// validates username and password input and verifies valid credentials
		elseif ($_POST['task'] == "checkCreds" && validUsername($_POST['username']) && validPassHash($_POST['password'])) {
			$username = mysqli_real_escape_string($connection, $_POST['username']);
			$clientPasswordHash = mysqli_real_escape_string($connection, $_POST['password']);
			$passwordHash = hash("sha512", $clientPasswordHash);
			$query = "SELECT * FROM users WHERE username ='$username' AND password ='$passwordHash'";
		
			$std = new stdClass();
		
			if (mysqli_num_rows(mysqli_query($connection, $query)) > 0) {
				$std->exists = true; // credentials match database entry
			}
			else {
				$std->exists = false; // creds don't match database entry
			}
		
			echo json_encode($std);
		}
		
		// store message action
		elseif ($_POST['task'] == "checkMessage" && validMessage()) {
		
			$username = mysqli_real_escape_string($connection, $_POST['username']);			
			$clientPasswordHash = mysqli_real_escape_string($connection, $_POST['password']);
			$email = mysqli_real_escape_string($connection, $_POST['email']);
			$month = mysqli_real_escape_string($connection, $_POST['month']);
			$date = mysqli_real_escape_string($connection, $_POST['date']);
			$year = mysqli_real_escape_string($connection, $_POST['year']);
			$hour = mysqli_real_escape_string($connection, $_POST['hour']);
			$minute = mysqli_real_escape_string($connection, $_POST['minute']);
			$message = mysqli_real_escape_string($connection, $_POST['message']);
			$passwordHash = hash("sha512", $clientPasswordHash);
			$query = "SELECT userid FROM users WHERE username ='$username'";
			$useridArray = mysqli_fetch_array(mysqli_query($connection, $query));
			$userid = $useridArray['userid'];
			$std = new stdClass();
			
			// user must be a registered user for message to be accepted
			$query = "SELECT * FROM users WHERE username ='$username' AND password ='$passwordHash'";
			if (mysqli_num_rows(mysqli_query($connection, $query)) > 0) {
			
				$query = "INSERT INTO messages (username, userid, email, message, month, date, year, hour, minute, sent) VALUES ('$username', '$userid', '$email','$message','$month','$date','$year','$hour','$minute',false)";
				mysqli_query($connection, $query);
				$std->exists = true; // message saved			
			}
			else {
				$std->exists = false; // message not saved
			}
			
			echo json_encode($std);
		}
	}
	else {
		header('location: /lab4.html'); // php file redirects to lab4.html when task is not set
	}
	
	// username is 5 to 15 alphanumeric chars
	function validUsername($username) {
		return preg_match("/^[A-Za-z0-9]{5,15}$/",$username);
	}
	// password is hexadecimal and 128 chars
	function validPassHash($passHash) {
		 return preg_match("/^[a-f0-9]{128}$/",$passHash);
	}	
	// salt is hexadecimal and '.' and either 32 or 33 chars.
	function validSalt($salt) {
		return preg_match("/^[a-f0-9.]{32,33}$/",$salt);
	}
	
	// all other values checked
	function validMessage() {
	
		$username = $_POST['username'];
		$passwordHash = $_POST['password'];
		$email = $_POST['email'];
		$month = $_POST['month'];
		$date = $_POST['date'];
		$year = $_POST['year'];
		$hour = $_POST['hour'];
		$minute = $_POST['minute'];
		$message = $_POST['message'];
		
		$valid = true;
		
		if ($valid) {			
			$valid = validUsername($username);
		}
		if ($valid) {
			$valid = validPassHash($passwordHash);
		}
		if ($valid) {			
			$valid = preg_match("/^\S{1,20}@\S{1,20}\.\S{2,6}$/",$email); // user@domain.toplevel
		}
		if ($valid) {			
			$valid = ($month > 0 && $month < 13); 
		}
		if ($valid) {			
			$valid = ($date > 0 && $date < 32); 
		}
		if ($valid) {			
			$valid = ($year > 2013 && $year < 2050); 
		}
		if ($valid) {			
			$valid = ($hour >= 0 && $hour < 24); 
		}
		if ($valid) {			
			$valid = ($minute == 0 || $minute == 30); 
		}
		if ($valid) {			
			$valid = preg_match("/^.{0,255}$/",$message); // no more than 255 chars
		}
		return $valid;	
	}	
?>