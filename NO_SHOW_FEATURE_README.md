# No-Show Auto-Cancellation & Pet Registration Enhancement

## Overview
This document describes the new features added to the Veterinary Appointment System.

---

## 1. No-Show Warning for Clients

### What Changed:
Clients now see a clear warning message when booking or viewing appointments:

**"If you do not arrive on your expected schedule, your appointment will be automatically cancelled."**

### Where It Appears:
- ✅ Booking form page (`views/eventform.php`)
- ✅ My Appointments page (`views/my_appointments.php`)
- ✅ Email confirmations

### Purpose:
Inform clients in advance that missing their scheduled appointment will result in automatic cancellation.

---

## 2. Auto-Cancel Appointments for No-Shows

### How It Works:
The system automatically cancels appointments when:
- Current time is past the scheduled appointment time (with 15-minute grace period)
- Appointment status is `PENDING` or `APPROVED`
- Client has not checked in
- Appointment is not already cancelled

### Status Changes:
- New status: `AUTO CANCELLED`
- Tracked in database with `auto_cancelled` flag and `auto_cancelled_date` timestamp

### Notifications:
- Clients receive an email notification explaining the auto-cancellation
- Email includes the no-show policy reminder

### Implementation:

#### Option 1: Automated Cron Job (Recommended)
Run the `cron_check_noshow.php` script every 15 minutes:

**Linux/Unix:**
```bash
crontab -e
# Add this line:
*/15 * * * * /usr/bin/php /path/to/your/project/cron_check_noshow.php
```

**Windows Task Scheduler:**
1. Open Task Scheduler
2. Create Basic Task
3. Trigger: Daily, repeat every 15 minutes
4. Action: Start a program
   - Program: `C:\xampp\php\php.exe`
   - Arguments: `C:\path\to\your\project\cron_check_noshow.php`

#### Option 2: Manual Trigger (Testing)
Visit: `http://yourdomain.com/cron_check_noshow.php?key=vet_clinic_2024_secure_key`

**Security Note:** Change the secret key in `cron_check_noshow.php` before deployment!

#### Option 3: API Call
```php
// From within the application
$result = file_get_contents(WEB_ROOT . 'api/process.php?cmd=autoCheckNoShows');
```

---

## 3. Pet Gender and Age - Now Required

### New Required Fields:
- **Gender**: Dropdown with options `Male` or `Female`
- **Age**: Number input (0-50 years)

### Validation:
- ✅ Frontend validation (JavaScript)
- ✅ Backend validation (PHP)
- ✅ Database storage

### Database Changes:
```sql
ALTER TABLE `tbl_appointments` 
ADD COLUMN `pet_gender` VARCHAR(10) NULL AFTER `pet_breed`,
ADD COLUMN `pet_age` INT(3) NULL AFTER `pet_gender`;
```

---

## 4. Pet Type Restricted to Cat or Dog

### What Changed:
Pet type dropdown now only allows:
- 🐕 Dog
- 🐈 Cat

### Previous Options Removed:
- ~~Bird~~
- ~~Rabbit~~
- ~~Hamster~~
- ~~Fish~~
- ~~Reptile~~
- ~~Other~~

### Backend Validation:
```php
if (!in_array($pet_type, ['Cat', 'Dog'])) {
    // Reject the booking
}
```

---

## 5. Login Page Logo Enhancement

### New Design Features:
- ✅ Animated logo circle with heartbeat icon
- ✅ Gradient background (purple to blue)
- ✅ Floating animation effect
- ✅ Modern, professional appearance
- ✅ Responsive design for mobile devices

### Visual Elements:
- Logo appears at the top of the login form
- Background uses gradient with animated patterns
- Logo has a floating animation effect
- Fully responsive on all screen sizes

---

## Database Migration

### Run This SQL Script:
Execute `db-script/add_noshow_and_pet_fields.sql` to add all required database fields:

```sql
-- Add check-in/arrival status
ALTER TABLE `tbl_appointments` 
ADD COLUMN `checked_in` TINYINT(1) DEFAULT 0 AFTER `status`,
ADD COLUMN `checked_in_time` TIMESTAMP NULL DEFAULT NULL AFTER `checked_in`;

-- Add pet gender and age
ALTER TABLE `tbl_appointments` 
ADD COLUMN `pet_gender` VARCHAR(10) NULL AFTER `pet_breed`,
ADD COLUMN `pet_age` INT(3) NULL AFTER `pet_gender`;

-- Add auto-cancelled tracking
ALTER TABLE `tbl_appointments` 
ADD COLUMN `auto_cancelled` TINYINT(1) DEFAULT 0 AFTER `cancellation_reason`,
ADD COLUMN `auto_cancelled_date` TIMESTAMP NULL DEFAULT NULL AFTER `auto_cancelled`;

-- Add index for performance
ALTER TABLE `tbl_appointments` 
ADD INDEX `idx_appointment_status` (`appointment_date`, `status`, `checked_in`);
```

---

## Files Modified

### Backend:
- ✅ `api/process.php` - Added pet validation, auto-cancel function
- ✅ `library/mail.php` - Added auto-cancel email template

### Frontend:
- ✅ `views/eventform.php` - Added warning, gender, age fields
- ✅ `views/my_appointments.php` - Added warning, auto-cancel status display
- ✅ `login.php` - Added logo and gradient background

### Database:
- ✅ `db-script/add_noshow_and_pet_fields.sql` - New migration script

### New Files:
- ✅ `cron_check_noshow.php` - Automated no-show checker
- ✅ `NO_SHOW_FEATURE_README.md` - This documentation

---

## Testing Checklist

### 1. Pet Registration:
- [ ] Try booking without gender - should show error
- [ ] Try booking without age - should show error
- [ ] Try booking with pet type other than Cat/Dog - should be rejected
- [ ] Successfully book with Cat, Male, Age 5
- [ ] Successfully book with Dog, Female, Age 2

### 2. No-Show Warning:
- [ ] Check booking form shows warning message
- [ ] Check My Appointments page shows warning
- [ ] Verify warning is clear and visible

### 3. Auto-Cancellation:
- [ ] Create a test appointment in the past
- [ ] Run cron job: `php cron_check_noshow.php`
- [ ] Verify appointment status changed to `AUTO CANCELLED`
- [ ] Check email was sent to client
- [ ] Verify cancellation appears in My Appointments

### 4. Login Page:
- [ ] Logo displays correctly
- [ ] Background gradient shows properly
- [ ] Logo animation works
- [ ] Responsive on mobile devices
- [ ] Login functionality still works

---

## Configuration

### Cron Job Settings:
Edit `cron_check_noshow.php` to customize:
- `$secret_key` - Change to a secure random string
- `$gracePeriod` - Adjust grace period (default: 15 minutes)

### Email Templates:
Edit `library/mail.php` to customize email messages:
- `appointment_booked` - Includes no-show warning
- `appointment_auto_cancelled` - No-show notification

---

## Support & Troubleshooting

### Cron Job Not Running:
1. Check cron log: `tail -f cron_noshow_log.txt`
2. Verify PHP path: `which php`
3. Test manually: `php cron_check_noshow.php`
4. Check file permissions: `chmod +x cron_check_noshow.php`

### Emails Not Sending:
1. Check email configuration in `library/email_config.php`
2. Review email debug log: `email_debug.txt`
3. Test email: `php test_email.php`

### Database Errors:
1. Verify migration script was executed
2. Check table structure: `DESCRIBE tbl_appointments;`
3. Review database connection in `library/config.php`

---

## Future Enhancements

Potential improvements:
- SMS notifications for no-shows
- Check-in QR code system
- Grace period configuration in admin panel
- No-show statistics dashboard
- Automatic rebooking suggestions

---

## Contact

For questions or issues, contact the development team or refer to the main `VETERINARY_SYSTEM_README.md`.
