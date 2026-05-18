<?php
/**
 * Cron Job Script: Auto-Cancel No-Show Appointments
 * 
 * This script should be run periodically (e.g., every 15 minutes) to check for
 * appointments where clients did not arrive and automatically cancel them.
 * 
 * Setup Instructions:
 * 
 * 1. Linux/Unix Cron Job:
 *    Add this line to your crontab (crontab -e):
 *    */15 * * * * /usr/bin/php /path/to/your/project/cron_check_noshow.php
 * 
 * 2. Windows Task Scheduler:
 *    Create a new task that runs every 15 minutes:
 *    Program: C:\xampp\php\php.exe
 *    Arguments: C:\path\to\your\project\cron_check_noshow.php
 * 
 * 3. Manual Trigger (for testing):
 *    Visit: http://yourdomain.com/cron_check_noshow.php?key=YOUR_SECRET_KEY
 */

// Security: Only allow execution from command line or with secret key
$secret_key = 'vet_clinic_2024_secure_key'; // Change this to a random string

if (php_sapi_name() !== 'cli') {
    // If accessed via web, require secret key
    if (!isset($_GET['key']) || $_GET['key'] !== $secret_key) {
        http_response_code(403);
        die('Access denied. Invalid security key.');
    }
}

// Load required files
require_once __DIR__ . '/library/config.php';
require_once __DIR__ . '/library/database.php';
require_once __DIR__ . '/library/functions.php';
require_once __DIR__ . '/library/mail.php';

// Log file for cron execution
$log_file = __DIR__ . '/cron_noshow_log.txt';

function log_message($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $message\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
    echo $log_entry; // Also output to console
}

log_message("=== Starting No-Show Check ===");

try {
    $currentDateTime = date('Y-m-d H:i:s');
    
    // Find appointments that are:
    // 1. Past their scheduled time (with 15-minute grace period)
    // 2. Status is PENDING or APPROVED
    // 3. Client has not checked in
    // 4. Not already auto-cancelled
    $gracePeriod = 15; // minutes
    $checkTime = date('Y-m-d H:i:s', strtotime("-$gracePeriod minutes"));
    
    $sql = "SELECT a.*, u.name, u.email 
            FROM tbl_appointments a 
            JOIN tbl_users u ON a.uid = u.id 
            WHERE a.appointment_date < '$checkTime' 
            AND a.status IN ('PENDING', 'APPROVED') 
            AND (a.checked_in = 0 OR a.checked_in IS NULL)
            AND (a.auto_cancelled = 0 OR a.auto_cancelled IS NULL)";
    
    $result = dbQuery($sql);
    $cancelledCount = 0;
    $failedCount = 0;
    
    if (!$result) {
        log_message("ERROR: Database query failed");
        exit(1);
    }
    
    $totalFound = dbNumRows($result);
    log_message("Found $totalFound appointment(s) to check for no-show");
    
    while ($appointment = dbFetchAssoc($result)) {
        log_message("Processing appointment ID: {$appointment['id']} for {$appointment['name']} (Pet: {$appointment['pet_name']})");
        
        // Update appointment to auto-cancelled status
        $updateSql = "UPDATE tbl_appointments SET 
                      status = 'AUTO CANCELLED',
                      auto_cancelled = 1,
                      auto_cancelled_date = NOW(),
                      cancellation_reason = 'Client did not arrive at scheduled appointment time'
                      WHERE id = " . $appointment['id'];
        
        if (dbQuery($updateSql)) {
            $cancelledCount++;
            log_message("✓ Appointment ID {$appointment['id']} auto-cancelled successfully");
            
            // Send notification email to client
            try {
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
                
                if (send_email($emailData)) {
                    log_message("✓ Email notification sent to {$appointment['email']}");
                } else {
                    log_message("⚠ Warning: Email notification failed for {$appointment['email']}");
                }
            } catch (Exception $e) {
                log_message("⚠ Warning: Email error - " . $e->getMessage());
            }
        } else {
            $failedCount++;
            log_message("✗ Failed to cancel appointment ID {$appointment['id']}");
        }
    }
    
    log_message("=== No-Show Check Complete ===");
    log_message("Summary: $cancelledCount cancelled, $failedCount failed");
    
    // Return JSON response if accessed via web
    if (php_sapi_name() !== 'cli') {
        header('Content-Type: application/json');
        echo json_encode(array(
            'success' => true,
            'cancelled_count' => $cancelledCount,
            'failed_count' => $failedCount,
            'total_checked' => $totalFound,
            'timestamp' => date('Y-m-d H:i:s')
        ));
    }
    
} catch (Exception $e) {
    log_message("FATAL ERROR: " . $e->getMessage());
    log_message("Stack trace: " . $e->getTraceAsString());
    
    if (php_sapi_name() !== 'cli') {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(array(
            'success' => false,
            'error' => $e->getMessage()
        ));
    }
    
    exit(1);
}

exit(0);
?>
