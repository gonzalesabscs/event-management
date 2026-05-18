<?php
// Email Configuration Settings
// Modify these settings according to your environment

// Email settings
define('EMAIL_FROM_ADDRESS', 'gonzales.a.bscs@gmail.com');
define('EMAIL_FROM_NAME', 'Veterinary Clinic');
define('EMAIL_REPLY_TO', 'gonzales.a.bscs@gmail.com');

// SMTP Settings (if using SMTP instead of PHP mail())
define('USE_SMTP', false); // Set to true to use SMTP instead of PHP mail()
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'gonzales.a.bscs@gmail.com');
define('SMTP_PASSWORD', 'zmepsaymhlqcuoji');
define('SMTP_ENCRYPTION', 'tls'); // 'tls' or 'ssl'

// Email debugging
define('EMAIL_DEBUG', false); // Set to false in production

// Function to check if email is properly configured
function is_email_configured() {
    if (USE_SMTP) {
        return !empty(SMTP_HOST) && !empty(SMTP_USERNAME) && !empty(SMTP_PASSWORD);
    } else {
        // Check if PHP mail is configured
        $smtp = ini_get('SMTP');
        $sendmail_path = ini_get('sendmail_path');
        return !empty($smtp) || !empty($sendmail_path);
    }
}

// Function to get email configuration status
function get_email_config_status() {
    $status = array();
    
    if (USE_SMTP) {
        $status['method'] = 'SMTP';
        $status['host'] = SMTP_HOST;
        $status['port'] = SMTP_PORT;
        $status['username'] = SMTP_USERNAME;
        $status['configured'] = is_email_configured();
    } else {
        $status['method'] = 'PHP Mail';
        $status['smtp'] = ini_get('SMTP');
        $status['smtp_port'] = ini_get('smtp_port');
        $status['sendmail_from'] = ini_get('sendmail_from');
        $status['sendmail_path'] = ini_get('sendmail_path');
        $status['configured'] = is_email_configured();
    }
    
    return $status;
}
?>