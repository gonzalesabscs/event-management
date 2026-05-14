# Installation Guide - New Features

## Quick Start

Follow these steps to install the new no-show auto-cancellation and pet registration enhancements.

---

## Step 1: Database Migration

Run the SQL migration script to add new database fields:

### Option A: Using phpMyAdmin
1. Open phpMyAdmin
2. Select your database: `db_vet_appointment`
3. Click on the "SQL" tab
4. Copy and paste the contents of `db-script/add_noshow_and_pet_fields.sql`
5. Click "Go" to execute
6. Repeat for `db-script/add_logo_upload.sql`

### Option B: Using MySQL Command Line
```bash
mysql -u root -p db_vet_appointment < db-script/add_noshow_and_pet_fields.sql
mysql -u root -p db_vet_appointment < db-script/add_logo_upload.sql
```

### Verify Installation
Run this query to verify the new fields were added:
```sql
DESCRIBE tbl_appointments;
```

You should see these new columns:
- `checked_in` (TINYINT)
- `checked_in_time` (TIMESTAMP)
- `pet_gender` (VARCHAR)
- `pet_age` (INT)
- `auto_cancelled` (TINYINT)
- `auto_cancelled_date` (TIMESTAMP)

---

## Step 2: Set Up Auto-Cancellation Cron Job

### For Windows (XAMPP)

1. **Create a Batch File**
   Create `run_noshow_check.bat` in your project root:
   ```batch
   @echo off
   C:\xampp\php\php.exe C:\xampp\htdocs\your-project\cron_check_noshow.php
   ```

2. **Set Up Windows Task Scheduler**
   - Open Task Scheduler (search in Start menu)
   - Click "Create Basic Task"
   - Name: "Vet Clinic No-Show Check"
   - Trigger: Daily
   - Start time: 8:00 AM
   - Repeat task every: 15 minutes
   - Duration: 1 day
   - Action: Start a program
   - Program/script: Browse to your `run_noshow_check.bat` file
   - Click Finish

### For Linux/Unix

1. **Edit Crontab**
   ```bash
   crontab -e
   ```

2. **Add This Line**
   ```
   */15 * * * * /usr/bin/php /path/to/your/project/cron_check_noshow.php >> /path/to/your/project/cron_noshow_log.txt 2>&1
   ```
   
   Replace `/path/to/your/project/` with your actual project path.

3. **Save and Exit**
   - Press `Ctrl+X`, then `Y`, then `Enter`

### Test the Cron Job

Run manually to test:
```bash
php cron_check_noshow.php
```

Or visit in browser (change the secret key first!):
```
http://localhost/your-project/cron_check_noshow.php?key=vet_clinic_2024_secure_key
```

---

## Step 3: Update Secret Key (Important!)

For security, change the secret key in `cron_check_noshow.php`:

```php
// Line 20 - Change this to a random string
$secret_key = 'your_random_secure_key_here_12345';
```

Generate a random key using:
- Online: https://randomkeygen.com/
- PHP: `echo bin2hex(random_bytes(16));`
- Command line: `openssl rand -hex 16`

---

## Step 4: Test the New Features

### Test Pet Registration
1. Go to the booking form
2. Try to book without selecting gender → Should show error
3. Try to book without entering age → Should show error
4. Try to select a pet type other than Cat/Dog → Should only see Cat and Dog
5. Successfully book with all required fields

### Test No-Show Warning
1. Check the booking form → Warning should be visible at the top
2. Check "My Appointments" page → Warning should be visible
3. Book an appointment → Confirmation email should mention the policy

### Test Auto-Cancellation
1. **Create a test appointment in the past:**
   ```sql
   INSERT INTO tbl_appointments 
   (uid, pet_name, pet_type, pet_breed, pet_gender, pet_age, appointment_date, appointment_type, status, bdate) 
   VALUES 
   (15, 'Test Pet', 'Dog', 'Labrador', 'Male', 5, '2024-01-01 10:00:00', 'General Checkup', 'APPROVED', NOW());
   ```

2. **Run the cron job:**
   ```bash
   php cron_check_noshow.php
   ```

3. **Check the results:**
   - Appointment status should change to `AUTO CANCELLED`
   - Email should be sent to the client
   - Check `cron_noshow_log.txt` for execution details

### Test Login Page
1. Visit the login page
2. Verify the logo appears at the top
3. Check the gradient background
4. Test on mobile device for responsiveness

### Test Logo Upload
1. Log in as administrator
2. Go to Settings page
3. Upload a test logo (PNG, JPG, or GIF)
4. Verify preview appears
5. Save settings
6. Check login page shows new logo
7. Try removing logo
8. Try replacing logo

---

## Step 5: Monitor and Maintain

### Check Cron Job Logs
View the log file to monitor auto-cancellations:
```bash
tail -f cron_noshow_log.txt
```

### Check Email Logs
View email sending status:
```bash
tail -f email_debug.txt
```

### Database Queries for Monitoring

**Count auto-cancelled appointments:**
```sql
SELECT COUNT(*) as total_auto_cancelled 
FROM tbl_appointments 
WHERE status = 'AUTO CANCELLED';
```

**View recent auto-cancellations:**
```sql
SELECT a.*, u.name, u.email 
FROM tbl_appointments a 
JOIN tbl_users u ON a.uid = u.id 
WHERE a.status = 'AUTO CANCELLED' 
ORDER BY a.auto_cancelled_date DESC 
LIMIT 10;
```

**Check appointments with missing pet data:**
```sql
SELECT * FROM tbl_appointments 
WHERE pet_gender IS NULL OR pet_age IS NULL;
```

---

## Troubleshooting

### Cron Job Not Running
- Check PHP path: `which php` (Linux) or `where php` (Windows)
- Verify file permissions: `chmod +x cron_check_noshow.php` (Linux)
- Check cron syntax: Use https://crontab.guru/
- View system cron logs: `grep CRON /var/log/syslog` (Linux)

### Emails Not Sending
- Check email configuration in `library/email_config.php`
- Review `email_debug.txt` for errors
- Test email manually: `php test_email.php`
- Verify SMTP settings in `php.ini`

### Database Errors
- Verify migration was successful: `DESCRIBE tbl_appointments;`
- Check for syntax errors in queries
- Review MySQL error log
- Ensure database user has proper permissions

### Pet Type Validation Failing
- Clear browser cache
- Check that old appointments with other pet types still display correctly
- Verify backend validation in `api/process.php`

---

## Rollback Instructions

If you need to revert the changes:

### Remove Database Fields
```sql
ALTER TABLE `tbl_appointments` 
DROP COLUMN `checked_in`,
DROP COLUMN `checked_in_time`,
DROP COLUMN `pet_gender`,
DROP COLUMN `pet_age`,
DROP COLUMN `auto_cancelled`,
DROP COLUMN `auto_cancelled_date`,
DROP INDEX `idx_appointment_status`;
```

### Disable Cron Job
- **Windows:** Delete the scheduled task from Task Scheduler
- **Linux:** Remove the cron entry with `crontab -e`

### Restore Old Files
If you backed up the original files, restore:
- `api/process.php`
- `views/eventform.php`
- `views/my_appointments.php`
- `views/appedit.php`
- `library/mail.php`
- `login.php`

---

## Support

For issues or questions:
1. Check `NO_SHOW_FEATURE_README.md` for detailed feature documentation
2. Review log files: `cron_noshow_log.txt` and `email_debug.txt`
3. Verify database structure matches the migration script
4. Test each feature individually to isolate problems

---

## Next Steps

After successful installation:
1. ✅ Monitor the cron job for the first few days
2. ✅ Review auto-cancelled appointments weekly
3. ✅ Adjust grace period if needed (default: 15 minutes)
4. ✅ Train staff on the new pet registration requirements
5. ✅ Update client-facing documentation about the no-show policy
6. ✅ Consider adding SMS notifications for no-shows (future enhancement)

---

**Installation Complete!** 🎉

Your veterinary appointment system now includes:
- ✅ No-show warnings for clients
- ✅ Automatic cancellation for missed appointments
- ✅ Required pet gender and age fields
- ✅ Restricted pet types (Cat/Dog only)
- ✅ Enhanced login page with logo
