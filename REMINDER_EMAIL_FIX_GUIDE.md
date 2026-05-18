# Reminder Email Fix Guide

## Problem
The "Send Reminder" button is not sending emails to clients.

## Quick Diagnosis

### Step 1: Test the Reminder Function
Visit: `http://yourdomain.com/test_reminder_email.php`

This will:
- ✅ Check email configuration
- ✅ Find an approved appointment
- ✅ Generate the email message
- ✅ Allow you to send a test email
- ✅ Show detailed error logs

### Step 2: Check Debug Logs
After clicking the reminder button, check these files:
- `reminder_debug.txt` - Reminder-specific debug log
- `email_debug.txt` - General email debug log

---

## Common Issues & Solutions

### Issue 1: Email Not Configured

**Symptoms:**
- Error: "Failed to send reminder email"
- `email_debug.txt` shows "mail() returned: FALSE"

**Solution for XAMPP/Windows:**

1. **Edit php.ini** (C:\xampp\php\php.ini):
```ini
[mail function]
SMTP = localhost
smtp_port = 25
sendmail_from = appointments@vetclinic.com
```

2. **Install Fake Sendmail:**
   - Download: https://www.glob.com.au/sendmail/
   - Extract to: C:\xampp\sendmail\
   - Edit sendmail.ini:
```ini
smtp_server=smtp.gmail.com
smtp_port=587
auth_username=your-email@gmail.com
auth_password=your-app-password
force_sender=your-email@gmail.com
```

3. **Update php.ini to use sendmail:**
```ini
sendmail_path = "C:\xampp\sendmail\sendmail.exe -t"
```

4. **Restart Apache**

**Solution for Linux:**

1. **Install mail server:**
```bash
sudo apt-get install sendmail
# or
sudo apt-get install postfix
```

2. **Test mail command:**
```bash
echo "Test email" | mail -s "Test" your-email@example.com
```

---

### Issue 2: No Approved Appointments

**Symptoms:**
- Error: "Appointment not found or not approved"
- `reminder_debug.txt` shows "Results found: 0"

**Solution:**
1. Go to appointment list
2. Find a PENDING appointment
3. Click "Approve" button
4. Try reminder again

---

### Issue 3: Invalid Email Address

**Symptoms:**
- Email sends but never arrives
- `email_debug.txt` shows success but no email received

**Solution:**
1. Check the client's email address in database
2. Verify it's a valid email format
3. Check spam/junk folder
4. Try with a different email address

---

### Issue 4: Firewall/Port Blocking

**Symptoms:**
- Connection timeout
- "Could not connect to SMTP host"

**Solution:**
1. Check firewall allows port 25, 587, or 465
2. Try different SMTP ports
3. Contact hosting provider about email restrictions

---

## Testing Procedure

### 1. Basic Email Test
```bash
# Visit in browser:
http://yourdomain.com/test_email.php
```

### 2. Reminder Email Test
```bash
# Visit in browser:
http://yourdomain.com/test_reminder_email.php
```

### 3. Manual Reminder Test
1. Log in as admin/staff
2. Go to appointment list
3. Find APPROVED appointment
4. Click "Remind" button
5. Check for success/error message
6. Check `reminder_debug.txt`
7. Check client's email inbox

---

## Debug Information

### Check Reminder Debug Log
```bash
# View last 20 lines
tail -20 reminder_debug.txt

# Or on Windows
type reminder_debug.txt
```

### Check Email Debug Log
```bash
# View last 20 lines
tail -20 email_debug.txt

# Or on Windows
type email_debug.txt
```

### Check PHP Mail Configuration
```php
<?php
phpinfo();
// Look for "mail function" section
?>
```

---

## Configuration Files

### 1. Email Config
**File:** `library/email_config.php`

**For PHP Mail (default):**
```php
define('USE_SMTP', false);
```

**For SMTP:**
```php
define('USE_SMTP', true);
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_ENCRYPTION', 'tls');
```

### 2. PHP.ini (for PHP Mail)
**Location:** 
- XAMPP: `C:\xampp\php\php.ini`
- Linux: `/etc/php/7.x/apache2/php.ini`

**Settings:**
```ini
[mail function]
SMTP = localhost
smtp_port = 25
sendmail_from = appointments@vetclinic.com
sendmail_path = "C:\xampp\sendmail\sendmail.exe -t"  ; Windows only
```

---

## Gmail SMTP Setup (Recommended)

### 1. Enable 2-Factor Authentication
1. Go to Google Account settings
2. Security → 2-Step Verification
3. Enable it

### 2. Generate App Password
1. Google Account → Security
2. App passwords
3. Select "Mail" and "Other"
4. Name it "Veterinary Clinic"
5. Copy the 16-character password

### 3. Update Configuration
Edit `library/email_config.php`:
```php
define('USE_SMTP', true);
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'xxxx xxxx xxxx xxxx'); // App password
define('SMTP_ENCRYPTION', 'tls');
```

### 4. Test
Visit: `http://yourdomain.com/test_reminder_email.php`

---

## Troubleshooting Checklist

- [ ] Email configuration checked
- [ ] PHP mail() function enabled
- [ ] SMTP settings correct (if using SMTP)
- [ ] Firewall allows email ports
- [ ] Apache restarted after config changes
- [ ] Approved appointment exists
- [ ] Client email address is valid
- [ ] Debug logs checked
- [ ] Test email sent successfully
- [ ] Spam folder checked

---

## Quick Fixes

### Fix 1: Use Gmail SMTP (Easiest)
1. Get Gmail app password
2. Update `library/email_config.php`
3. Set `USE_SMTP = true`
4. Test with `test_reminder_email.php`

### Fix 2: Use Fake Sendmail (XAMPP)
1. Download Fake Sendmail
2. Configure sendmail.ini
3. Update php.ini
4. Restart Apache

### Fix 3: Disable Email (Temporary)
If you need the system to work without email:
1. Edit `library/mail.php`
2. Make `send_email()` always return `true`
3. Log emails to file instead

---

## Support Files

### Test Scripts
- `test_email.php` - Basic email test
- `test_reminder_email.php` - Reminder email test
- `email_diagnostic.php` - Email diagnostics
- `fix_email_xampp.php` - XAMPP email fix

### Debug Logs
- `reminder_debug.txt` - Reminder debug log
- `email_debug.txt` - Email debug log

### Configuration
- `library/email_config.php` - Email settings
- `php.ini` - PHP mail configuration

---

## Still Not Working?

### 1. Check System Requirements
- PHP mail() function enabled
- Or SMTP server accessible
- Ports 25, 587, or 465 open

### 2. Contact Hosting Provider
Ask about:
- Email sending restrictions
- SMTP server details
- Firewall rules
- PHP mail() availability

### 3. Use External Email Service
Consider:
- SendGrid
- Mailgun
- Amazon SES
- Postmark

---

## Success Indicators

When working correctly, you should see:

1. **Success Message:**
   > "Reminder email successfully sent to [Name] ([email])"

2. **Debug Log (reminder_debug.txt):**
   ```
   === REMINDER EMAIL DEBUG ===
   Time: 2024-01-15 10:30:00
   User ID: 15
   Query executed
   Results found: 1
   Client Name: John Doe
   Client Email: john@example.com
   Email send result: SUCCESS
   ```

3. **Email Received:**
   - Check client's inbox
   - Subject: "Appointment Reminder - [Pet Name] at Veterinary Clinic"
   - Contains appointment details
   - Contains no-show warning

---

## Prevention

To avoid future email issues:

1. **Regular Testing**
   - Test emails weekly
   - Monitor debug logs
   - Check spam reports

2. **Backup Email Method**
   - Configure both PHP mail and SMTP
   - Fallback to alternative if primary fails

3. **Monitoring**
   - Set up email delivery monitoring
   - Track bounce rates
   - Monitor spam complaints

---

## Additional Resources

- PHP Mail Documentation: https://www.php.net/manual/en/function.mail.php
- Gmail SMTP Guide: https://support.google.com/mail/answer/7126229
- Fake Sendmail: https://www.glob.com.au/sendmail/
- Email Testing: https://www.mail-tester.com/

---

**Need Help?** Check the test scripts first:
1. Visit `test_reminder_email.php`
2. Follow the step-by-step diagnosis
3. Check debug logs
4. Try the suggested fixes
