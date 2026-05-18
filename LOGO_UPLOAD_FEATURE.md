# Logo Upload Feature Documentation

## Overview
The logo upload feature allows administrators to upload and manage a custom clinic logo that appears throughout the system, particularly on the login page.

---

## Features

### ✅ Upload Custom Logo
- Support for PNG, JPG, JPEG, and GIF formats
- Maximum file size: 2MB
- Automatic file validation
- Real-time preview before saving

### ✅ Logo Management
- View current logo
- Replace existing logo
- Remove logo (revert to default)
- Automatic cleanup of old logos

### ✅ Security
- Admin-only access
- File type validation
- File size validation
- Protected upload directory
- PHP execution disabled in uploads folder

### ✅ Display Locations
- Login page (primary)
- System header (configurable)
- Email notifications (future enhancement)
- Printed documents (future enhancement)

---

## Installation

### Step 1: Run Database Migration
Execute the SQL script to add logo setting:

```bash
mysql -u root -p db_vet_appointment < db-script/add_logo_upload.sql
```

Or via phpMyAdmin:
1. Open phpMyAdmin
2. Select `db_vet_appointment` database
3. Go to SQL tab
4. Paste contents of `db-script/add_logo_upload.sql`
5. Click "Go"

### Step 2: Verify Directory Permissions
Ensure the uploads directory is writable:

**Linux/Unix:**
```bash
chmod 755 uploads/
chmod 755 uploads/logo/
```

**Windows:**
- Right-click on `uploads` folder
- Properties → Security
- Ensure IUSR and IIS_IUSRS have write permissions

### Step 3: Test Upload
1. Log in as administrator
2. Go to Settings page
3. Upload a test logo
4. Verify it appears on the login page

---

## Usage Guide

### Uploading a Logo

1. **Access Settings**
   - Log in as administrator
   - Navigate to Settings page
   - Find "Clinic Logo" section at the top

2. **Choose Logo File**
   - Click "Choose File" button
   - Select your logo image (PNG, JPG, or GIF)
   - Preview will appear automatically

3. **Validate Requirements**
   - File type: PNG, JPG, JPEG, or GIF
   - File size: Maximum 2MB
   - Recommended dimensions: 300x100px or similar ratio
   - Transparent PNG recommended for best results

4. **Save Settings**
   - Click "Save Settings" button
   - Logo will be uploaded and activated
   - Old logo (if any) will be automatically deleted

### Removing a Logo

1. Go to Settings page
2. Check the "Remove current logo" checkbox
3. Click "Save Settings"
4. System will revert to default logo circle

### Replacing a Logo

1. Simply upload a new logo file
2. Old logo will be automatically replaced
3. No need to remove the old one first

---

## Technical Details

### File Storage
- **Location:** `uploads/logo/`
- **Naming:** `clinic_logo_[timestamp].[extension]`
- **Example:** `clinic_logo_1704067200.png`

### Database Storage
- **Table:** `tbl_system_settings`
- **Key:** `clinic_logo`
- **Value:** Relative path (e.g., `uploads/logo/clinic_logo_1704067200.png`)

### File Validation

**Allowed MIME Types:**
- `image/png`
- `image/jpeg`
- `image/jpg`
- `image/gif`

**Size Limit:**
- Maximum: 2MB (2,097,152 bytes)
- Enforced on both client and server side

### Security Measures

1. **File Type Validation**
   - Server-side MIME type checking
   - Extension validation
   - Rejects executable files

2. **Directory Protection**
   - `.htaccess` prevents PHP execution
   - `index.php` prevents directory browsing
   - Only image files are accessible

3. **Access Control**
   - Only administrators can upload logos
   - Session validation required
   - CSRF protection via form submission

---

## Display Implementation

### Login Page
The logo appears at the top of the login form:

```php
<?php 
$logoPath = getSystemSetting('clinic_logo', '');
if (!empty($logoPath) && file_exists($logoPath)) {
    // Display uploaded logo
    echo '<img src="' . WEB_ROOT . $logoPath . '" alt="Clinic Logo">';
} else {
    // Display default logo circle
    echo '<div class="logo-circle">...</div>';
}
?>
```

### Styling
- Maximum width: 250px
- Maximum height: 120px
- Drop shadow effect
- Responsive design
- Centered alignment

---

## Troubleshooting

### Logo Not Uploading

**Problem:** "Failed to upload logo" error

**Solutions:**
1. Check directory permissions:
   ```bash
   ls -la uploads/logo/
   ```
   Should show `drwxr-xr-x` or similar

2. Verify PHP upload settings in `php.ini`:
   ```ini
   upload_max_filesize = 2M
   post_max_size = 8M
   file_uploads = On
   ```

3. Check disk space:
   ```bash
   df -h
   ```

4. Review PHP error log for details

### Logo Not Displaying

**Problem:** Logo uploaded but not showing on login page

**Solutions:**
1. Clear browser cache (Ctrl+F5)
2. Check file path in database:
   ```sql
   SELECT * FROM tbl_system_settings WHERE setting_key = 'clinic_logo';
   ```
3. Verify file exists:
   ```bash
   ls -la uploads/logo/
   ```
4. Check file permissions (should be readable)

### File Too Large Error

**Problem:** "File size too large" message

**Solutions:**
1. Compress image using online tools:
   - TinyPNG (https://tinypng.com/)
   - Compressor.io (https://compressor.io/)
   
2. Resize image to recommended dimensions (300x100px)

3. Convert to PNG with optimization

### Invalid File Type Error

**Problem:** "Invalid file type" message

**Solutions:**
1. Ensure file extension is .png, .jpg, .jpeg, or .gif
2. Check actual file type (not just extension)
3. Re-save image in correct format using image editor
4. Avoid SVG files (not currently supported)

---

## Best Practices

### Logo Design

1. **Dimensions**
   - Recommended: 300x100px
   - Aspect ratio: 3:1 (landscape)
   - Maximum: 400x150px

2. **File Format**
   - **PNG:** Best for logos with transparency
   - **JPG:** Good for photographic logos
   - **GIF:** Suitable for simple graphics

3. **File Size**
   - Target: Under 100KB
   - Maximum: 2MB
   - Optimize before uploading

4. **Design Tips**
   - Use transparent background (PNG)
   - High contrast for readability
   - Simple, clean design
   - Scalable graphics

### Maintenance

1. **Regular Backups**
   - Include `uploads/logo/` in backups
   - Export database settings regularly

2. **Version Control**
   - Keep original logo files separately
   - Document logo changes
   - Maintain brand guidelines

3. **Testing**
   - Test on different browsers
   - Check mobile responsiveness
   - Verify email display (future)

---

## API Reference

### Upload Function
Located in: `views/process.php`

```php
function updateSettings() {
    // Handles logo upload as part of settings update
    // Validates file type and size
    // Moves file to uploads/logo/
    // Updates database with path
    // Deletes old logo if exists
}
```

### Display Function
Located in: `library/functions.php`

```php
function getSystemSetting($key, $default = '') {
    // Retrieves setting value from database
    // Returns default if not found
}
```

---

## File Structure

```
project-root/
├── uploads/
│   └── logo/
│       ├── .htaccess              # Security rules
│       ├── index.php              # Prevent browsing
│       ├── README.txt             # Directory info
│       └── clinic_logo_*.png      # Uploaded logos
├── views/
│   ├── settings.php               # Upload interface
│   └── process.php                # Upload handler
├── login.php                      # Logo display
└── db-script/
    └── add_logo_upload.sql        # Database migration
```

---

## Future Enhancements

Planned improvements:
1. **Email Integration**
   - Display logo in email headers
   - Branded email templates

2. **Multiple Logo Sizes**
   - Automatic thumbnail generation
   - Responsive image sizes
   - Retina display support

3. **Logo Variants**
   - Light/dark mode versions
   - Favicon generation
   - Social media formats

4. **Advanced Features**
   - Image cropping tool
   - Color picker for branding
   - Logo position settings
   - Multiple logo slots (header, footer, etc.)

5. **Analytics**
   - Track logo views
   - A/B testing support
   - Brand consistency reports

---

## Security Considerations

### Upload Security
- ✅ File type whitelist (not blacklist)
- ✅ MIME type validation
- ✅ File size limits
- ✅ Unique filenames (timestamp-based)
- ✅ Directory permissions
- ✅ PHP execution disabled

### Access Control
- ✅ Admin-only upload
- ✅ Session validation
- ✅ CSRF protection
- ✅ Input sanitization

### File Storage
- ✅ Outside web root (recommended)
- ✅ Protected directory
- ✅ No directory listing
- ✅ Automatic cleanup

---

## Support

### Common Questions

**Q: Can I use SVG files?**
A: Not currently supported. Use PNG for transparency or JPG for photos.

**Q: What happens to the old logo?**
A: It's automatically deleted when you upload a new one.

**Q: Can I have multiple logos?**
A: Currently only one logo is supported. Future versions may support variants.

**Q: Does the logo appear in emails?**
A: Not yet, but this is planned for a future update.

**Q: Can I revert to the default logo?**
A: Yes, check "Remove current logo" and save settings.

### Getting Help

1. Check this documentation
2. Review error messages in browser console
3. Check PHP error log
4. Verify file permissions
5. Test with a different image file

---

## Changelog

### Version 1.0 (Current)
- Initial logo upload feature
- Support for PNG, JPG, GIF
- Login page integration
- Admin settings interface
- File validation and security
- Automatic old logo cleanup

---

## Credits

Feature developed as part of the Veterinary Appointment System enhancement project.

**Related Documentation:**
- `NO_SHOW_FEATURE_README.md` - No-show auto-cancellation
- `INSTALLATION_GUIDE.md` - System installation
- `CHANGES_SUMMARY.md` - All system changes
