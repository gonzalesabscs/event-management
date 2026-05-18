<?php 

require_once 'Booking.php';
require_once '../library/config.php';
require_once '../library/functions.php';
require_once '../library/mail.php';

$cmd = isset($_GET['cmd']) ? $_GET['cmd'] : '';

switch($cmd) {
	
	case 'book':
		bookCalendar();
	break;
		
	case 'holiday':
		addHoliday();
	break;
	
	case 'hdelete':
		deleteHoliday();
	break;
		
	case 'calview':
		calendarView();
	break;

	case 'regConfirm':
		regConfirm();
	break;
			
	case 'delete':
		regDelete();
	break;
	
	case 'user':
		userDetails();
	break;
	
	case 'sendReminder':
		sendAppointmentReminder();
	break;
	
	case 'updateAppointment':
		updateAppointment();
	break;
	
	case 'cancelAppointment':
		cancelAppointment();
	break;
	
	case 'autoCheckNoShows':
		autoCheckNoShows();
	break;
	
	default :
	break;
}

function addHoliday() {
	$date 		= $_POST['date'];
	$reason 	= $_POST['reason'];	
	
	$errorMessage = '';
	
	$sql 	= "SELECT * FROM tbl_holidays WHERE date = '$date'";
	$result = dbQuery($sql);
	
	if (dbNumRows($result) > 0) {
		$errorMessage = 'Clinic is already closed on this date.';
		header('Location: ../views/?v=HOLY&err=' . urlencode($errorMessage));
		exit();
	}
	else {
		$sql = "INSERT INTO tbl_holidays (date, reason, bdate)
				VALUES ('$date', '$reason', NOW())";	
		dbQuery($sql);
		$msg = 'Clinic closed day successfully added to calendar.';
		header('Location: ../views/?v=HOLY&msg=' . urlencode($msg));
		exit();
	}
}

function bookCalendar() {
	$userId		= (int)$_POST['name']; // The select dropdown sends the user ID
	$address 	= $_POST['address'];
	$phone 		= $_POST['phone'];
	$email 		= $_POST['email'];
	$rdate		= $_POST['rdate'];
	$rtime		= $_POST['rtime'];
	$bkdate		= $rdate. ' '. $rtime;
	$pet_name	= $_POST['pet_name'];
	$pet_type	= $_POST['pet_type'];
	$pet_breed	= isset($_POST['pet_breed']) ? $_POST['pet_breed'] : '';
	$pet_gender	= $_POST['pet_gender'];
	$pet_age	= (int)$_POST['pet_age'];
	$appointment_type = isset($_POST['appointment_type']) ? $_POST['appointment_type'] : 'General Checkup';
	
	// Validate pet type (only Cat or Dog allowed)
	if (!in_array($pet_type, ['Cat', 'Dog'])) {
		$errorMessage = 'Invalid pet type. Only cats and dogs are accepted.';
		header('Location: ../views/?v=DB&err=' . urlencode($errorMessage));
		exit();
	}
	
	// Validate pet gender
	if (!in_array($pet_gender, ['Male', 'Female'])) {
		$errorMessage = 'Invalid pet gender. Please select Male or Female.';
		header('Location: ../views/?v=DB&err=' . urlencode($errorMessage));
		exit();
	}
	
	// Validate pet age
	if ($pet_age < 0 || $pet_age > 50) {
		$errorMessage = 'Invalid pet age. Age must be between 0 and 50 years.';
		header('Location: ../views/?v=DB&err=' . urlencode($errorMessage));
		exit();
	}
	
	// Get user name for email
	$userSql = "SELECT name FROM tbl_users WHERE id = $userId";
	$userResult = dbQuery($userSql);
	$name = '';
	if (dbNumRows($userResult) > 0) {
		$userRow = dbFetchAssoc($userResult);
		$name = $userRow['name'];
	}
	
	//Check if that date has a holiday
	$hsql	= "SELECT * FROM tbl_holidays WHERE date = '$rdate'";
	$hresult = dbQuery($hsql);
	if (dbNumRows($hresult) > 0) {
		$errorMessage = 'The clinic is closed on this date. Please select another day.';
		header('Location: ../views/?v=DB&err=' . urlencode($errorMessage));
		exit();
	}
	
	$sql = "INSERT INTO tbl_appointments (uid, pet_name, pet_type, pet_breed, pet_gender, pet_age, appointment_date, appointment_type, status, comments, bdate) 
			VALUES ($userId, '$pet_name', '$pet_type', '$pet_breed', '$pet_gender', $pet_age, '$bkdate', '$appointment_type', 'PENDING', '', NOW())";
	dbQuery($sql);
	
	//Send email confirmation to user
	$emailMsg = get_email_msg(array(
		'msg' => 'appointment_booked',
		'name' => $name,
		'pet_name' => $pet_name,
		'pet_type' => $pet_type,
		'appointment_date' => $bkdate,
		'appointment_type' => $appointment_type
	));
	
	$emailData = array(
		'to' => $email, 
		'sub' => 'Veterinary Appointment Confirmation - Pending', 
		'msg' => $emailMsg
	);
	send_email($emailData);
	
	header('Location: ../index.php?msg=' . urlencode('Appointment successfully booked. Check your email for confirmation.'));
	exit();
}

function regConfirm() {
	$userId		= $_GET['userId'];
	$action 	= $_GET['action'];
	$stat		= ($action == 'approve') ? 'APPROVED' : 'DENIED';
	
	// Update appointment status and set approved_date if approving
	if ($stat == 'APPROVED') {
		$sql = "UPDATE tbl_appointments SET status = '$stat', approved_date = NOW() WHERE uid = $userId";
	} else {
		$sql = "UPDATE tbl_appointments SET status = '$stat' WHERE uid = $userId";
	}
	dbQuery($sql);
	
	//Get user and appointment details for email
	$userSql = "SELECT u.name, u.email, a.pet_name, a.appointment_date, a.appointment_type 
				FROM tbl_users u, tbl_appointments a 
				WHERE u.id = a.uid AND u.id = $userId 
				LIMIT 1";
	$userResult = dbQuery($userSql);
	
	if (dbNumRows($userResult) > 0) {
		$userData = dbFetchAssoc($userResult);
		
		if ($stat == 'APPROVED') {
			$emailMsg = get_email_msg(array(
				'msg' => 'appointment_confirmed',
				'name' => $userData['name'],
				'pet_name' => $userData['pet_name'],
				'appointment_date' => $userData['appointment_date'],
				'appointment_type' => $userData['appointment_type']
			));
			$subject = 'Veterinary Appointment CONFIRMED';
		} else {
			$emailMsg = get_email_msg(array(
				'msg' => 'appointment_denied',
				'name' => $userData['name'],
				'appointment_date' => $userData['appointment_date'],
				'reason' => 'Time slot no longer available'
			));
			$subject = 'Veterinary Appointment Update';
		}
		
		$emailData = array(
			'to' => $userData['email'], 
			'sub' => $subject, 
			'msg' => $emailMsg
		);
		send_email($emailData);
	}
	
	header('Location: ../views/?v=DB&msg=' . urlencode('Appointment status successfully changed and email sent to client.'));
	exit();
}

function regDelete() {
	$userId	= (int)$_GET['userId'];
	
	// Only delete the appointment, NOT the user
	// This allows pet owners to book future appointments
	$sql = "DELETE FROM tbl_appointments WHERE uid = $userId";
	dbQuery($sql);
	
	// Note: User account is preserved for future bookings
	
	header('Location: ../views/?v=LIST&msg=' . urlencode('Appointment successfully deleted. Pet owner account preserved.'));
	exit();
}

function deleteHoliday() {
	$holyId	= $_GET['hId'];
	$dsql	= "DELETE FROM tbl_holidays WHERE id = $holyId";
	dbQuery($dsql);
	header('Location: ../views/?v=HOLY&msg=' . urlencode('Clinic closed day successfully removed.'));
	exit();
}

function calendarView() {
	$start 	= $_POST['start'];
	$end 	= $_POST['end'];
	$bookings = array();
	$sql	= "SELECT u.name AS u_name, u.id AS user_id, a.appointment_date, a.status, a.pet_name, a.appointment_type 
			   FROM tbl_users u, tbl_appointments a 
			   WHERE u.id = a.uid  
			   AND (a.appointment_date BETWEEN '$start' AND '$end')";
	$result = dbQuery($sql);
	while($row = dbFetchAssoc($result)) {
		extract($row);
		$book = new Booking();
		$book->title = $u_name . ' - ' . $pet_name . ' (' . $appointment_type . ')';
		$book->start = $appointment_date; 
		$bgClr = '#f39c12';//pending
		if($status == 'DENIED') {$bgClr = '#ff0000';}
		else if($status == 'APPROVED') {$bgClr = '#00cc00';}
		$book->backgroundColor = $bgClr;
		$book->borderColor = $bgClr;
		$book->url = WEB_ROOT . 'views/?v=USER&ID='.$user_id;
		$bookings[] = $book; 
	}
	//Get clinic closed days
	$hsql	= "SELECT * FROM tbl_holidays 
			   WHERE (date BETWEEN '$start' AND '$end')";
	$hresult = dbQuery($hsql);
	while($hrow = dbFetchAssoc($hresult)) {	
		extract($hrow);	   
		$b = new Booking();
		$b->block = true;
		$b->title = $reason;
		$b->start = $date;
		$b->allDay = true; 
		$b->borderColor = '#F0F0F0';
		$b->className = 'fc-disabled';
		$bookings[] = $b;
	}
	echo json_encode($bookings);
}

function userDetails() {
	// Clean any output buffer to ensure clean JSON
	if (ob_get_level()) {
		ob_clean();
	}
	
	$userId	= (int)$_GET['userId'];
	
	// Validate user ID
	if ($userId <= 0) {
		header('Content-Type: application/json');
		echo json_encode(array('error' => 'Invalid user ID'));
		exit();
	}
	
	$hsql	= "SELECT * FROM tbl_users WHERE id = $userId AND type = 'client'";
	$hresult = dbQuery($hsql);
	$user = array();
	
	if ($hresult && dbNumRows($hresult) > 0) {
		$hrow = dbFetchAssoc($hresult);
		$user['user_id'] = $hrow['id'];
		$user['address'] = $hrow['address'] ? $hrow['address'] : '';
		$user['phone_no'] = $hrow['phone'] ? $hrow['phone'] : '';
		$user['email'] = $hrow['email'] ? $hrow['email'] : '';
	} else {
		$user['error'] = 'User not found';
	}
	
	// Set proper content type and output clean JSON
	header('Content-Type: application/json');
	echo json_encode($user);
	exit();
}

function sendAppointmentReminder() {
	// Check if user is logged in
	if (!isset($_SESSION['calendar_fd_user'])) {
		header('Location: ../views/?v=LIST&err=' . urlencode('Session expired. Please log in again.'));
		exit();
	}
	
	$userId = (int)$_GET['userId'];
	
	// Debug logging
	$debugLog = "\n=== REMINDER EMAIL DEBUG ===\n";
	$debugLog .= "Time: " . date('Y-m-d H:i:s') . "\n";
	$debugLog .= "User ID: $userId\n";
	$debugLog .= "Session User: " . $_SESSION['calendar_fd_user']['name'] . " (ID: " . $_SESSION['calendar_fd_user']['id'] . ")\n";
	
	// Validate user ID
	if ($userId <= 0) {
		$debugLog .= "ERROR: Invalid user ID\n";
		file_put_contents('reminder_debug.txt', $debugLog, FILE_APPEND | LOCK_EX);
		header('Location: ../views/?v=LIST&err=' . urlencode('Invalid user ID provided.'));
		exit();
	}
	
	// Get user and appointment details - Fixed query to get the most recent approved appointment
	$sql = "SELECT u.name, u.email, a.id as appointment_id, a.pet_name, a.pet_type, a.appointment_date, a.appointment_type, a.status 
			FROM tbl_users u 
			JOIN tbl_appointments a ON u.id = a.uid 
			WHERE u.id = $userId 
			AND a.status = 'APPROVED'
			ORDER BY a.appointment_date DESC
			LIMIT 1";
	
	$debugLog .= "SQL Query: $sql\n";
	$result = dbQuery($sql);
	
	$debugLog .= "Query executed\n";
	$debugLog .= "Results found: " . dbNumRows($result) . "\n";
	
	if (dbNumRows($result) > 0) {
		$data = dbFetchAssoc($result);
		
		$debugLog .= "Client Name: " . $data['name'] . "\n";
		$debugLog .= "Client Email: " . $data['email'] . "\n";
		$debugLog .= "Pet Name: " . $data['pet_name'] . "\n";
		$debugLog .= "Appointment Date: " . $data['appointment_date'] . "\n";
		$debugLog .= "Appointment Status: " . $data['status'] . "\n";
		
		// Format appointment date for better display
		try {
			$appointmentDateTime = new DateTime($data['appointment_date']);
			$formattedDate = $appointmentDateTime->format('l, F j, Y \a\t g:i A');
			$debugLog .= "Formatted Date: $formattedDate\n";
		} catch (Exception $e) {
			$debugLog .= "ERROR formatting date: " . $e->getMessage() . "\n";
			$formattedDate = $data['appointment_date'];
		}
		
		$emailMsg = get_email_msg(array(
			'msg' => 'appointment_reminder',
			'name' => $data['name'],
			'pet_name' => $data['pet_name'],
			'pet_type' => $data['pet_type'],
			'appointment_date' => $formattedDate,
			'appointment_type' => $data['appointment_type']
		));
		
		$debugLog .= "Email message generated\n";
		$debugLog .= "Message length: " . strlen($emailMsg) . " characters\n";
		
		$emailData = array(
			'to' => $data['email'], 
			'sub' => 'Appointment Reminder - ' . $data['pet_name'] . ' at Veterinary Clinic', 
			'msg' => $emailMsg
		);
		
		$debugLog .= "Attempting to send email to: " . $data['email'] . "\n";
		$emailSent = send_email($emailData);
		$debugLog .= "Email send result: " . ($emailSent ? 'SUCCESS' : 'FAILED') . "\n";
		
		// Write debug log
		file_put_contents('reminder_debug.txt', $debugLog, FILE_APPEND | LOCK_EX);
		
		if ($emailSent) {
			// Log the reminder in database
			$staffId = isset($_SESSION['calendar_fd_user']['id']) ? $_SESSION['calendar_fd_user']['id'] : 1;
			$logSql = "INSERT INTO tbl_appointment_reminders (appointment_id, sent_date, sent_by, email_status) 
					   VALUES (" . $data['appointment_id'] . ", NOW(), $staffId, 'sent')";
			dbQuery($logSql);
			
			$message = 'Reminder email successfully sent to ' . $data['name'] . ' (' . $data['email'] . ')';
			header('Location: ../views/?v=LIST&msg=' . urlencode($message));
		} else {
			$error = 'Failed to send reminder email. Email system returned false. Check reminder_debug.txt and email_debug.txt for details.';
			header('Location: ../views/?v=LIST&err=' . urlencode($error));
		}
	} else {
		$debugLog .= "ERROR: No approved appointment found for user ID $userId\n";
		$debugLog .= "Possible reasons:\n";
		$debugLog .= "- User has no appointments\n";
		$debugLog .= "- All appointments are PENDING, DENIED, or CANCELLED\n";
		$debugLog .= "- User ID is incorrect\n";
		file_put_contents('reminder_debug.txt', $debugLog, FILE_APPEND | LOCK_EX);
		
		$error = 'No approved appointment found for this client. Only confirmed (APPROVED) appointments can receive reminders.';
		header('Location: ../views/?v=LIST&err=' . urlencode($error));
	}
	exit();
}

function updateAppointment() {
	$appointmentId = (int)$_POST['appointmentId'];
	$userId = (int)$_POST['userId'];
	$name = $_POST['name'];
	$address = $_POST['address'];
	$phone = $_POST['phone'];
	$email = $_POST['email'];
	$pet_name = $_POST['pet_name'];
	$pet_type = $_POST['pet_type'];
	$pet_breed = isset($_POST['pet_breed']) ? $_POST['pet_breed'] : '';
	$pet_gender = $_POST['pet_gender'];
	$pet_age = (int)$_POST['pet_age'];
	$appointment_type = $_POST['appointment_type'];
	$status = $_POST['status'];
	$comments = isset($_POST['comments']) ? $_POST['comments'] : '';
	$rdate = $_POST['rdate'];
	$rtime = $_POST['rtime'];
	$appointment_date = $rdate . ' ' . $rtime;
	
	// Validate pet type (only Cat or Dog allowed)
	if (!in_array($pet_type, ['Cat', 'Dog'])) {
		$errorMessage = 'Invalid pet type. Only cats and dogs are accepted.';
		header('Location: ../views/?v=EDIT&ID=' . $userId . '&err=' . urlencode($errorMessage));
		exit();
	}
	
	// Validate pet gender
	if (!in_array($pet_gender, ['Male', 'Female'])) {
		$errorMessage = 'Invalid pet gender. Please select Male or Female.';
		header('Location: ../views/?v=EDIT&ID=' . $userId . '&err=' . urlencode($errorMessage));
		exit();
	}
	
	// Validate pet age
	if ($pet_age < 0 || $pet_age > 50) {
		$errorMessage = 'Invalid pet age. Age must be between 0 and 50 years.';
		header('Location: ../views/?v=EDIT&ID=' . $userId . '&err=' . urlencode($errorMessage));
		exit();
	}
	
	// Get original status for email notification
	$originalStatusSql = "SELECT status FROM tbl_appointments WHERE id = $appointmentId";
	$originalResult = dbQuery($originalStatusSql);
	$originalStatus = '';
	if (dbNumRows($originalResult) > 0) {
		$originalRow = dbFetchAssoc($originalResult);
		$originalStatus = $originalRow['status'];
	}
	
	// Check if the new date has a holiday
	$hsql = "SELECT * FROM tbl_holidays WHERE date = '$rdate'";
	$hresult = dbQuery($hsql);
	if (dbNumRows($hresult) > 0) {
		$errorMessage = 'The clinic is closed on the selected date. Please choose another day.';
		header('Location: ../views/?v=EDIT&ID=' . $userId . '&err=' . urlencode($errorMessage));
		exit();
	}
	
	// Update user information
	$userSql = "UPDATE tbl_users SET 
				name = '$name',
				address = '$address',
				phone = '$phone',
				email = '$email'
				WHERE id = $userId";
	dbQuery($userSql);
	
	// Update appointment information
	$appointmentSql = "UPDATE tbl_appointments SET 
					   pet_name = '$pet_name',
					   pet_type = '$pet_type',
					   pet_breed = '$pet_breed',
					   pet_gender = '$pet_gender',
					   pet_age = $pet_age,
					   appointment_date = '$appointment_date',
					   appointment_type = '$appointment_type',
					   status = '$status',
					   comments = '$comments'";
	
	// If status is ARRIVED, also set checked_in flag and time
	if ($status == 'ARRIVED') {
		$appointmentSql .= ", checked_in = 1, checked_in_time = NOW()";
	}
	
	$appointmentSql .= " WHERE id = $appointmentId";
	dbQuery($appointmentSql);
	
	// Send email notification if status changed
	if ($originalStatus != $status && ($status == 'APPROVED' || $status == 'DENIED')) {
		$formattedDate = date('l, F j, Y \a\t g:i A', strtotime($appointment_date));
		
		if ($status == 'APPROVED') {
			$emailMsg = get_email_msg(array(
				'msg' => 'appointment_confirmed',
				'name' => $name,
				'pet_name' => $pet_name,
				'appointment_date' => $formattedDate,
				'appointment_type' => $appointment_type
			));
			$subject = 'Veterinary Appointment CONFIRMED - Updated';
		} else {
			$emailMsg = get_email_msg(array(
				'msg' => 'appointment_denied',
				'name' => $name,
				'appointment_date' => $formattedDate,
				'reason' => 'Appointment has been updated and declined'
			));
			$subject = 'Veterinary Appointment Update';
		}
		
		$emailData = array(
			'to' => $email, 
			'sub' => $subject, 
			'msg' => $emailMsg
		);
		send_email($emailData);
	}
	
	// Log the update
	$staffId = isset($_SESSION['calendar_fd_user']['user_id']) ? $_SESSION['calendar_fd_user']['user_id'] : 1;
	$logSql = "INSERT INTO tbl_appointment_reminders (appointment_id, sent_date, sent_by, email_status) 
			   VALUES ($userId, NOW(), $staffId, 'updated')";
	dbQuery($logSql);
	
	$message = 'Appointment successfully updated.';
	if ($originalStatus != $status) {
		$message .= ' Email notification sent to client.';
	}
	
	header('Location: ../views/?v=LIST&msg=' . urlencode($message));
	exit();
}

function cancelAppointment() {
	// Check if user is logged in
	if (!isset($_SESSION['calendar_fd_user'])) {
		http_response_code(401);
		echo json_encode(array('error' => 'Not authenticated'));
		exit();
	}
	
	$appointmentId = (int)$_POST['appointmentId'];
	$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
	$userId = $_SESSION['calendar_fd_user']['id'];
	$userType = $_SESSION['calendar_fd_user']['type'];
	$isAdminCancel = isset($_POST['adminCancel']) && $_POST['adminCancel'];
	
	// Validate appointment ID
	if ($appointmentId <= 0) {
		http_response_code(400);
		echo json_encode(array('error' => 'Invalid appointment ID'));
		exit();
	}
	
	// Get appointment details and verify ownership/permissions
	$sql = "SELECT a.*, u.name, u.email 
			FROM tbl_appointments a 
			JOIN tbl_users u ON a.uid = u.id 
			WHERE a.id = $appointmentId";
	
	// If user is a client, they can only cancel their own appointments
	if ($userType === 'client') {
		$sql .= " AND a.uid = $userId";
	}
	
	$result = dbQuery($sql);
	
	if (!$result || dbNumRows($result) == 0) {
		http_response_code(404);
		echo json_encode(array('error' => 'Appointment not found or access denied'));
		exit();
	}
	
	$appointment = dbFetchAssoc($result);
	
	// Check if appointment can be cancelled
	if ($appointment['status'] === 'CANCELLED') {
		http_response_code(400);
		echo json_encode(array('error' => 'Appointment is already cancelled'));
		exit();
	}
	
	if ($appointment['status'] === 'DENIED') {
		http_response_code(400);
		echo json_encode(array('error' => 'Cannot cancel a denied appointment'));
		exit();
	}
	
	// Check if appointment is in the past (only for non-admin users)
	if ($userType === 'client' && strtotime($appointment['appointment_date']) < time()) {
		http_response_code(400);
		echo json_encode(array('error' => 'Cannot cancel past appointments'));
		exit();
	}
	
	// For approved appointments, check 24-hour rule (only for clients, not admin/staff)
	if ($userType === 'client' && $appointment['status'] === 'APPROVED' && $appointment['approved_date']) {
		$approvedTime = strtotime($appointment['approved_date']);
		$currentTime = time();
		$hoursSinceApproval = ($currentTime - $approvedTime) / 3600;
		
		if ($hoursSinceApproval > 24) {
			http_response_code(400);
			echo json_encode(array('error' => 'Cannot cancel appointment. 24-hour cancellation period has expired.'));
			exit();
		}
	}
	
	// Admin/Staff cancellations require a reason
	if (($userType === 'admin' || $userType === 'staff') && empty($reason)) {
		http_response_code(400);
		echo json_encode(array('error' => 'Cancellation reason is required for staff cancellations'));
		exit();
	}
	
	// Update appointment status to cancelled
	$updateSql = "UPDATE tbl_appointments SET 
				  status = 'CANCELLED',
				  cancelled_date = NOW(),
				  cancelled_by = $userId,
				  cancellation_reason = '" . addslashes($reason) . "'
				  WHERE id = $appointmentId";
	
	if (!dbQuery($updateSql)) {
		http_response_code(500);
		echo json_encode(array('error' => 'Failed to cancel appointment'));
		exit();
	}
	
	// Determine who cancelled the appointment for email
	$cancelledBy = 'You';
	if ($userType === 'admin' || $userType === 'staff') {
		$cancelledBy = 'The clinic staff';
	}
	
	// Send cancellation email
	$emailMsg = get_email_msg(array(
		'msg' => 'appointment_cancelled',
		'name' => $appointment['name'],
		'pet_name' => $appointment['pet_name'],
		'appointment_date' => date('l, F j, Y \a\t g:i A', strtotime($appointment['appointment_date'])),
		'appointment_type' => $appointment['appointment_type'],
		'reason' => $reason ? $reason : 'No reason provided',
		'cancelled_by' => $cancelledBy
	));
	
	$emailData = array(
		'to' => $appointment['email'], 
		'sub' => 'Appointment Cancelled - ' . $appointment['pet_name'], 
		'msg' => $emailMsg
	);
	
	send_email($emailData);
	
	// Log the cancellation
	$logSql = "INSERT INTO tbl_appointment_reminders (appointment_id, sent_date, sent_by, email_status) 
			   VALUES ($appointmentId, NOW(), $userId, 'cancelled')";
	dbQuery($logSql);
	
	// Return success response
	header('Content-Type: application/json');
	echo json_encode(array(
		'success' => true,
		'message' => 'Appointment cancelled successfully'
	));
	exit();
}

function autoCheckNoShows() {
	// This function checks for appointments that are past their scheduled time
	// and automatically cancels them if the client hasn't checked in
	
	$currentDateTime = date('Y-m-d H:i:s');
	
	// Find appointments that are:
	// 1. Past their scheduled time
	// 2. Status is PENDING or APPROVED
	// 3. Client has not checked in
	// 4. Not already auto-cancelled
	$sql = "SELECT a.*, u.name, u.email 
			FROM tbl_appointments a 
			JOIN tbl_users u ON a.uid = u.id 
			WHERE a.appointment_date < '$currentDateTime' 
			AND a.status IN ('PENDING', 'APPROVED') 
			AND (a.checked_in = 0 OR a.checked_in IS NULL)
			AND (a.auto_cancelled = 0 OR a.auto_cancelled IS NULL)";
	
	$result = dbQuery($sql);
	$cancelledCount = 0;
	
	while ($appointment = dbFetchAssoc($result)) {
		// Update appointment to auto-cancelled status
		$updateSql = "UPDATE tbl_appointments SET 
					  status = 'AUTO CANCELLED',
					  auto_cancelled = 1,
					  auto_cancelled_date = NOW(),
					  cancellation_reason = 'Client did not arrive at scheduled appointment time'
					  WHERE id = " . $appointment['id'];
		
		if (dbQuery($updateSql)) {
			$cancelledCount++;
			
			// Send notification email to client
			$emailMsg = get_email_msg(array(
				'msg' => 'appointment_auto_cancelled',
				'name' => $appointment['name'],
				'pet_name' => $appointment['pet_name'],
				'appointment_date' => date('l, F j, Y \a\t g:i A', strtotime($appointment['appointment_date'])),
				'appointment_type' => $appointment['appointment_type']
			));
			
			$emailData = array(
				'to' => $appointment['email'], 
				'sub' => 'Appointment Auto-Cancelled - No Show', 
				'msg' => $emailMsg
			);
			
			send_email($emailData);
		}
	}
	
	// Return JSON response
	header('Content-Type: application/json');
	echo json_encode(array(
		'success' => true,
		'cancelled_count' => $cancelledCount,
		'message' => "$cancelledCount appointment(s) auto-cancelled due to no-show"
	));
	exit();
}
