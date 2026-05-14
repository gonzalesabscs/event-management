<?php
/**
 * Test Reminder Email Functionality
 * This script tests if reminder emails can be sent successfully
 */

require_once './library/config.php';
require_once './library/database.php';
require_once './library/functions.php';
require_once './library/mail.php';
require_once './library/email_config.php';

// Start output
echo "<html><head><title>Reminder Email Test</title>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
h1 { color: #2c5aa0; }
.success { background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #28a745; }
.error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #dc3545; }
.warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #ffc107; }
.info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 4px; margin: 10px 0; border-left: 4px solid #17a2b8; }
pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
.btn { display: inline-block; padding: 10px 20px; background: #2c5aa0; color: white; text-decoration: none; border-radius: 4px; margin: 5px; }
.btn:hover { background: #1e3a6e; }
</style></head><body>";

echo "<div class='container'>";
echo "<h1>🔔 Reminder Email Test</h1>";

// Step 1: Check email configuration
echo "<h2>Step 1: Email Configuration Check</h2>";
$emailConfig = get_email_config_status();

echo "<div class='info'>";
echo "<strong>Email Method:</strong> " . $emailConfig['method'] . "<br>";

if ($emailConfig['method'] == 'PHP Mail') {
    echo "<strong>SMTP Server:</strong> " . ($emailConfig['smtp'] ?: 'Not configured') . "<br>";
    echo "<strong>SMTP Port:</strong> " . ($emailConfig['smtp_port'] ?: 'Not configured') . "<br>";
    echo "<strong>Sendmail From:</strong> " . ($emailConfig['sendmail_from'] ?: 'Not configured') . "<br>";
    echo "<strong>Sendmail Path:</strong> " . ($emailConfig['sendmail_path'] ?: 'Not configured') . "<br>";
} else {
    echo "<strong>SMTP Host:</strong> " . $emailConfig['host'] . "<br>";
    echo "<strong>SMTP Port:</strong> " . $emailConfig['port'] . "<br>";
    echo "<strong>SMTP Username:</strong> " . $emailConfig['username'] . "<br>";
}

echo "<strong>Configured:</strong> " . ($emailConfig['configured'] ? '✅ Yes' : '❌ No') . "<br>";
echo "</div>";

if (!$emailConfig['configured']) {
    echo "<div class='error'>";
    echo "<strong>⚠️ Email Not Configured!</strong><br>";
    echo "Please configure email settings in <code>library/email_config.php</code> or your <code>php.ini</code> file.";
    echo "</div>";
}

// Step 2: Find an approved appointment
echo "<h2>Step 2: Find Approved Appointment</h2>";

$sql = "SELECT u.id as user_id, u.name, u.email, a.id as appointment_id, a.pet_name, a.pet_type, 
        a.appointment_date, a.appointment_type, a.status 
        FROM tbl_users u 
        JOIN tbl_appointments a ON u.id = a.uid 
        WHERE a.status = 'APPROVED'
        ORDER BY a.appointment_date DESC
        LIMIT 1";
$result = dbQuery($sql);

if (dbNumRows($result) > 0) {
    $appointment = dbFetchAssoc($result);
    
    echo "<div class='success'>";
    echo "<strong>✅ Approved Appointment Found!</strong><br>";
    echo "<strong>Client:</strong> " . htmlspecialchars($appointment['name']) . "<br>";
    echo "<strong>Email:</strong> " . htmlspecialchars($appointment['email']) . "<br>";
    echo "<strong>Pet:</strong> " . htmlspecialchars($appointment['pet_name']) . " (" . htmlspecialchars($appointment['pet_type']) . ")<br>";
    echo "<strong>Date:</strong> " . htmlspecialchars($appointment['appointment_date']) . "<br>";
    echo "<strong>Type:</strong> " . htmlspecialchars($appointment['appointment_type']) . "<br>";
    echo "</div>";
    
    // Step 3: Generate email message
    echo "<h2>Step 3: Generate Email Message</h2>";
    
    $appointmentDateTime = new DateTime($appointment['appointment_date']);
    $formattedDate = $appointmentDateTime->format('l, F j, Y \a\t g:i A');
    
    $emailMsg = get_email_msg(array(
        'msg' => 'appointment_reminder',
        'name' => $appointment['name'],
        'pet_name' => $appointment['pet_name'],
        'pet_type' => $appointment['pet_type'],
        'appointment_date' => $formattedDate,
        'appointment_type' => $appointment['appointment_type']
    ));
    
    echo "<div class='info'>";
    echo "<strong>Email Subject:</strong> Appointment Reminder - " . htmlspecialchars($appointment['pet_name']) . " at Veterinary Clinic<br>";
    echo "<strong>Email To:</strong> " . htmlspecialchars($appointment['email']) . "<br>";
    echo "<strong>Message Length:</strong> " . strlen($emailMsg) . " characters<br>";
    echo "</div>";
    
    echo "<details><summary><strong>Click to view email HTML</strong></summary>";
    echo "<pre>" . htmlspecialchars($emailMsg) . "</pre>";
    echo "</details>";
    
    // Step 4: Test send email
    echo "<h2>Step 4: Send Test Email</h2>";
    
    if (isset($_GET['send']) && $_GET['send'] == 'yes') {
        echo "<div class='warning'><strong>Sending email...</strong></div>";
        
        $emailData = array(
            'to' => $appointment['email'], 
            'sub' => 'Appointment Reminder - ' . $appointment['pet_name'] . ' at Veterinary Clinic', 
            'msg' => $emailMsg
        );
        
        $emailSent = send_email($emailData);
        
        if ($emailSent) {
            echo "<div class='success'>";
            echo "<strong>✅ Email Sent Successfully!</strong><br>";
            echo "Check the email inbox for: " . htmlspecialchars($appointment['email']) . "<br>";
            echo "Also check <code>email_debug.txt</code> for details.";
            echo "</div>";
        } else {
            echo "<div class='error'>";
            echo "<strong>❌ Email Failed to Send!</strong><br>";
            echo "Check <code>email_debug.txt</code> for error details.<br>";
            echo "Common issues:<br>";
            echo "• SMTP not configured in php.ini<br>";
            echo "• Firewall blocking port 25/587<br>";
            echo "• Invalid email address<br>";
            echo "• PHP mail() function disabled<br>";
            echo "</div>";
        }
        
        // Show email debug log
        if (file_exists('email_debug.txt')) {
            echo "<h3>Email Debug Log (last 20 lines):</h3>";
            $logLines = file('email_debug.txt');
            $lastLines = array_slice($logLines, -20);
            echo "<pre>" . htmlspecialchars(implode('', $lastLines)) . "</pre>";
        }
    } else {
        echo "<div class='warning'>";
        echo "<strong>Ready to send test email</strong><br>";
        echo "Click the button below to send a test reminder email to: " . htmlspecialchars($appointment['email']);
        echo "</div>";
        
        echo "<a href='?send=yes' class='btn'>📧 Send Test Email Now</a>";
    }
    
} else {
    echo "<div class='error'>";
    echo "<strong>❌ No Approved Appointments Found!</strong><br>";
    echo "Please create and approve an appointment first before testing reminder emails.";
    echo "</div>";
}

// Step 5: Configuration help
echo "<h2>Step 5: Email Configuration Help</h2>";

echo "<div class='info'>";
echo "<h3>For XAMPP/Windows:</h3>";
echo "<p>1. Edit <code>php.ini</code> file (usually in C:\\xampp\\php\\php.ini)</p>";
echo "<p>2. Find and update these lines:</p>";
echo "<pre>";
echo "[mail function]\n";
echo "SMTP = localhost\n";
echo "smtp_port = 25\n";
echo "sendmail_from = appointments@vetclinic.com\n";
echo "</pre>";
echo "<p>3. Or use a tool like <strong>sendmail</strong> or <strong>Fake Sendmail</strong></p>";
echo "<p>4. Restart Apache after changes</p>";

echo "<h3>For Production/Linux:</h3>";
echo "<p>1. Ensure mail server is installed (postfix, sendmail, etc.)</p>";
echo "<p>2. Or configure SMTP in <code>library/email_config.php</code></p>";
echo "<pre>";
echo "define('USE_SMTP', true);\n";
echo "define('SMTP_HOST', 'smtp.gmail.com');\n";
echo "define('SMTP_PORT', 587);\n";
echo "define('SMTP_USERNAME', 'your-email@gmail.com');\n";
echo "define('SMTP_PASSWORD', 'your-app-password');\n";
echo "</pre>";
echo "</div>";

// Quick links
echo "<h2>Quick Links</h2>";
echo "<a href='test_email.php' class='btn'>📧 Simple Email Test</a>";
echo "<a href='email_diagnostic.php' class='btn'>🔍 Email Diagnostic</a>";
echo "<a href='fix_email_xampp.php' class='btn'>🔧 XAMPP Email Fix</a>";
echo "<a href='views/?v=LIST' class='btn'>📋 Back to Appointments</a>";

echo "</div>";
echo "</body></html>";
?>
