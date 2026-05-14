# Logo Upload - Quick Start Guide

## 🚀 Quick Setup (5 Minutes)

### 1. Run Database Script
```bash
mysql -u root -p db_vet_appointment < db-script/add_logo_upload.sql
```

### 2. Set Directory Permissions
```bash
chmod 755 uploads/logo/
```

### 3. Upload Your Logo
1. Log in as **admin**
2. Go to **Settings** page
3. Click **Choose File** under "Clinic Logo"
4. Select your logo (PNG, JPG, or GIF)
5. Click **Save Settings**

### 4. Verify
- Visit login page
- Your logo should appear at the top!

---

## 📋 Requirements

### Logo Specifications
- **Format:** PNG (recommended), JPG, or GIF
- **Size:** Maximum 2MB
- **Dimensions:** 300x100px recommended
- **Background:** Transparent PNG works best

### System Requirements
- Admin access
- PHP file upload enabled
- Writable uploads directory

---

## ✅ Quick Checklist

- [ ] Database migration completed
- [ ] Directory permissions set (755)
- [ ] Logo file prepared (PNG/JPG/GIF)
- [ ] File size under 2MB
- [ ] Logged in as administrator
- [ ] Logo uploaded successfully
- [ ] Logo appears on login page

---

## 🔧 Common Issues

### "Failed to upload logo"
**Fix:** Check directory permissions
```bash
chmod 755 uploads/logo/
```

### "File too large"
**Fix:** Compress your image
- Use TinyPNG.com
- Or resize to 300x100px

### Logo not showing
**Fix:** Clear browser cache
- Press Ctrl+F5 (Windows)
- Press Cmd+Shift+R (Mac)

---

## 📖 Full Documentation

For detailed information, see:
- `LOGO_UPLOAD_FEATURE.md` - Complete feature documentation
- `INSTALLATION_GUIDE.md` - Full installation guide

---

## 💡 Tips

1. **Use PNG with transparency** for best results
2. **Keep file size small** (under 100KB ideal)
3. **Test on mobile** after uploading
4. **Backup original** logo file separately
5. **Use simple design** for better visibility

---

## 🎨 Design Recommendations

### Good Logo Characteristics
✅ Simple and clean design
✅ High contrast colors
✅ Transparent background
✅ Horizontal orientation (3:1 ratio)
✅ Readable at small sizes

### Avoid
❌ Complex details
❌ Low contrast
❌ White background (use transparent)
❌ Very tall logos (use wide format)
❌ Large file sizes

---

## 🔄 Managing Your Logo

### To Replace Logo
1. Go to Settings
2. Upload new logo
3. Old logo is automatically deleted

### To Remove Logo
1. Go to Settings
2. Check "Remove current logo"
3. Save settings
4. System reverts to default

### To Preview Before Saving
- Preview appears automatically when you select a file
- No need to save to see preview

---

## 📞 Need Help?

1. Check error message in red alert box
2. Review `LOGO_UPLOAD_FEATURE.md` for troubleshooting
3. Verify file meets requirements
4. Check PHP error log
5. Test with different image file

---

**That's it! Your custom logo should now appear on the login page.** 🎉
