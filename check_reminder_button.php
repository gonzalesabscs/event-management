<?php
/**
 * Check Reminder Button - Diagnostic Tool
 * This helps diagnose why the reminder button might not be working
 */

require_once './library/config.php';
require_once './library/database.php';
require_once './library/functions.php';

echo "<html><head><title>Reminder Button Diagnostic</title>";
echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
.container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
h1 { color: #2c5aa0; }
.success { background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin: 10px 0; }
.error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin: 10px 0; }
.warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 4px; margin: 10px 0; }
.info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 4px; margin: 10px 0; }
table { width: 100%; border-collapse: collapse; margin: 10px 0; }
th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
th { background: #2c5aa0; color: white; }
pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
.btn { display: inline-block; padding: 8px 15px; background: #2c5aa0; color: white; text-decoration: none; border-radius: 4px; margin: 5px; }
</style></head><body>";

echo "<div class='container'>";
echo "<h1>🔍 Reminder Button Diagnostic</h1>";

// Check session
echo "<h2>1. Session Check</h2>";
if (isset($_SESSION['calendar_fd_user'])) {
    echo "<div class='success'>";
    echo "<strong>✅ Session Active</strong><br>";
    echo "User: " . htmlspecialchars($_SESSION['calendar_fd_user']['name']) . "<br>";
    echo "Type: " . htmlspecialchars($_SESSION['calendar_fd_user']['type']) . "<br>";
    echo "ID: " . htmlspecialchars($_SESSION['calendar_fd_user']['id']) . "<br>";
    echo "</div>";
} else {
    echo "<div class='error'>";
    echo "<strong>❌ No Session Found</strong><br>";
    echo "You need to log in first.";
    echo "</div>";
    echo "<a href='login.php' class='btn'>Go to Login</a>";
    echo "</div></body></html>";
    exit();
}

// Get all appointments with their status
echo "<h2>2. Appointments in Database</h2>";
$sql = "SELECT u.id as user_id, u.name, u.email, a.id as appointment_id, a.pet_name, a.status, a.appointment_date
        FROM tbl_users u 
        JOIN tbl_appointments a ON u.id = a.uid 
        ORDER BY a.appointment_date DESC
        LIMIT 10";
$result = dbQuery($sql);

if (dbNumRows($result) > 0) {
    echo "<table>";
    echo "<tr><th>User ID</th><th>Client Name</th><th>Email</th><th>Pet Name</th><th>Status</th><th>Date</th><th>Test Reminder</th></tr>";
    
    while ($row = dbFetchAssoc($result)) {
        $statusColor = '';
        if ($row['status'] == 'APPROVED') $statusColor = 'style="background: #d4edda;"';
        elseif ($row['status'] == 'PENDING') $statusColor = 'style="background: #fff3cd;"';
        elseif ($row['status'] == 'DENIED') $statusColor = 'style="background: #f8d7da;"';
        
        echo "<tr $statusColor>";
        echo "<td>" . $row['user_id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['pet_name']) . "</td>";
        echo "<td><strong>" . $row['status'] . "</strong></td>";
        echo "<td>" . $row['appointment_date'] . "</td>";
        echo "<td>";
        if ($row['status'] == 'APPROVED') {
            echo "<a href='api/process.php?cmd=sendReminder&userId=" . $row['user_id'] . "' class='btn'>Send Reminder</a>";
        } else {
            echo "<span style='color: #999;'>Not Approved</span>";
        }
        echo "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<div class='warning'>No appointments found in database.</div>";
}

// Check reminder debug log
echo "<h2>3. Recent Reminder Attempts</h2>";
if (file_exists('reminder_debug.txt')) {
    $logContent = file_get_contents('reminder_debug.txt');
    $logLines = explode("\n", $logContent);
    $recentLines = array_slice($logLines, -50); // Last 50 lines
    
    echo "<div class='info'>";
    echo "<strong>Last 50 lines from reminder_debug.txt:</strong>";
    echo "<pre>" . htmlspecialchars(implode("\n", $recentLines)) . "</pre>";
    echo "</div>";
} else {
    echo "<div class='warning'>No reminder_debug.txt file found yet. Click a reminder button to generate it.</div>";
}

// Check email debug log
echo "<h2>4. Recent Email Attempts</h2>";
if (file_exists('email_debug.txt')) {
    $emailLog = file_get_contents('email_debug.txt');
    $emailLines = explode("\n", $emailLog);
    $recentEmailLines = array_slice($emailLines, -30); // Last 30 lines
    
    echo "<div class='info'>";
    echo "<strong>Last 30 lines from email_debug.txt:</strong>";
    echo "<pre>" . htmlspecialchars(implode("\n", $recentEmailLines)) . "</pre>";
    echo "</div>";
} else {
    echo "<div class='warning'>No email_debug.txt file found.</div>";
}

// Instructions
echo "<h2>5. How to Test</h2>";
echo "<div class='info'>";
echo "<ol>";
echo "<li>Find an APPROVED appointment in the table above</li>";
echo "<li>Click the 'Send Reminder' button next to it</li>";
echo "<li>You should be redirected to the appointment list with a success/error message</li>";
echo "<li>Refresh this page to see the debug logs</li>";
echo "<li>Check if the email was sent to the client</li>";
echo "</ol>";
echo "</div>";

// Quick links
echo "<h2>Quick Links</h2>";
echo "<a href='views/?v=LIST' class='btn'>📋 Appointment List</a>";
echo "<a href='test_reminder_email.php' class='btn'>📧 Test Reminder Email</a>";
echo "<a href='?refresh=1' class='btn'>🔄 Refresh This Page</a>";

echo "</div>";
echo "</body></html>";
?>
