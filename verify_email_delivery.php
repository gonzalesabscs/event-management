<?php
/**
 * Verify Email Delivery
 * Sends a test email and provides tracking information
 */

require_once './library/config.php';
require_once './library/mail.php';

echo "<html><head><title>Verify Email Delivery</title>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
h1 { color: #2c5aa0; }
.success { background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin: 10px 0; }
.error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin: 10px 0; }
.info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 4px; margin: 10px 0; }
.warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 4px; margin: 10px 0; }
input[type=email] { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; }
.btn { display: inline-block; padding: 10px 20px; background: #2c5aa0; color: white; text-decoration: none; border: none; border-radius: 4px; cursor: pointer; }
pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
</style></head><body>";

echo "<div class='container'>";
echo "<h1>📧 Verify Email Delivery</h1>";

if (isset($_POST['send_test'])) {
    $testEmail = $_POST['test_email'];
    
    echo "<h2>Sending Test Email...</h2>";
    
    $testMessage = "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <h2 style='color: #2c5aa0;'>✅ Email Delivery Test</h2>
        <p>If you receive this email, your email system is working correctly!</p>
        <div style='background: #d1ecf1; padding: 15px; border-radius: 4px; margin: 20px 0;'>
            <strong>Test Details:</strong><br>
            Sent at: " . date('Y-m-d H:i:s') . "<br>
            From: Veterinary Clinic System<br>
            Method: " . (USE_SMTP ? 'SMTP' : 'PHP Mail') . "
        </div>
        <p><strong>What to do next:</strong></p>
        <ol>
            <li>Check your inbox for this email</li>
            <li>Check spam/junk folder</li>
            <li>If you see this, the reminder button should work!</li>
        </ol>
        <p>Best regards,<br>
        <strong>Veterinary Clinic System</strong></p>
    </body>
    </html>";
    
    $emailData = array(
        'to' => $testEmail,
        'sub' => '✅ Email Delivery Test - ' . date('H:i:s'),
        'msg' => $testMessage
    );
    
    $result = send_email($emailData);
    
    if ($result) {
        echo "<div class='success'>";
        echo "<strong>✅ Email Sent Successfully!</strong><br>";
        echo "Test email sent to: <strong>" . htmlspecialchars($testEmail) . "</strong><br>";
        echo "Time: " . date('Y-m-d H:i:s') . "<br><br>";
        echo "<strong>Next Steps:</strong><br>";
        echo "1. Check the inbox for: " . htmlspecialchars($testEmail) . "<br>";
        echo "2. Check spam/junk folder<br>";
        echo "3. Search for subject: 'Email Delivery Test'<br>";
        echo "4. If you receive it, the reminder button is working!<br>";
        echo "</div>";
        
        echo "<div class='info'>";
        echo "<strong>📊 Email Configuration:</strong><br>";
        echo "SMTP Server: " . ini_get('SMTP') . "<br>";
        echo "SMTP Port: " . ini_get('smtp_port') . "<br>";
        echo "Sendmail From: " . ini_get('sendmail_from') . "<br>";
        echo "Sendmail Path: " . ini_get('sendmail_path') . "<br>";
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "<strong>❌ Email Failed to Send!</strong><br>";
        echo "Check email_debug.txt for error details.";
        echo "</div>";
    }
    
    // Show recent email log
    if (file_exists('email_debug.txt')) {
        $logLines = file('email_debug.txt');
        $lastLines = array_slice($logLines, -15);
        echo "<h3>Recent Email Log:</h3>";
        echo "<pre>" . htmlspecialchars(implode('', $lastLines)) . "</pre>";
    }
    
} else {
    echo "<div class='info'>";
    echo "<strong>📧 Email Delivery Verification</strong><br>";
    echo "This tool sends a test email to verify your email system is working.<br>";
    echo "Enter your email address below to receive a test message.";
    echo "</div>";
    
    echo "<form method='post'>";
    echo "<label><strong>Your Email Address:</strong></label>";
    echo "<input type='email' name='test_email' value='justinechua0921@gmail.com' required>";
    echo "<button type='submit' name='send_test' class='btn'>📧 Send Test Email</button>";
    echo "</form>";
    
    echo "<div class='warning'>";
    echo "<strong>⚠️ Important:</strong><br>";
    echo "• Make sure to check your spam/junk folder<br>";
    echo "• Gmail may filter emails to Promotions or Updates tab<br>";
    echo "• Search for 'Email Delivery Test' if you can't find it<br>";
    echo "• The email should arrive within 1-2 minutes";
    echo "</div>";
}

// Show current email status from logs
echo "<h2>📊 Recent Email Activity</h2>";
if (file_exists('email_debug.txt')) {
    $logContent = file_get_contents('email_debug.txt');
    $logLines = explode("\n", $logContent);
    
    // Count successful sends
    $successCount = 0;
    $failCount = 0;
    foreach ($logLines as $line) {
        if (strpos($line, 'returned: TRUE') !== false) $successCount++;
        if (strpos($line, 'returned: FALSE') !== false) $failCount++;
    }
    
    echo "<div class='info'>";
    echo "<strong>Email Statistics:</strong><br>";
    echo "✅ Successful sends: <strong>$successCount</strong><br>";
    echo "❌ Failed sends: <strong>$failCount</strong><br>";
    echo "</div>";
    
    // Show last 5 email attempts
    $attempts = array();
    $currentAttempt = array();
    foreach ($logLines as $line) {
        if (strpos($line, 'EMAIL ATTEMPT') !== false) {
            if (!empty($currentAttempt)) {
                $attempts[] = $currentAttempt;
            }
            $currentAttempt = array($line);
        } else if (!empty($currentAttempt)) {
            $currentAttempt[] = $line;
        }
    }
    if (!empty($currentAttempt)) {
        $attempts[] = $currentAttempt;
    }
    
    $lastAttempts = array_slice($attempts, -5);
    
    echo "<h3>Last 5 Email Attempts:</h3>";
    foreach ($lastAttempts as $attempt) {
        $attemptText = implode("\n", $attempt);
        $isSuccess = strpos($attemptText, 'returned: TRUE') !== false;
        $class = $isSuccess ? 'success' : 'error';
        echo "<div class='$class'>";
        echo "<pre>" . htmlspecialchars($attemptText) . "</pre>";
        echo "</div>";
    }
} else {
    echo "<div class='warning'>No email_debug.txt file found.</div>";
}

echo "<h2>Quick Links</h2>";
echo "<a href='check_reminder_button.php' class='btn'>🔍 Check Reminder Button</a>";
echo "<a href='test_reminder_email.php' class='btn'>📧 Test Reminder Email</a>";
echo "<a href='views/?v=LIST' class='btn'>📋 Appointment List</a>";

echo "</div>";
echo "</body></html>";
?>
