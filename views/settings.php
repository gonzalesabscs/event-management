<?php
// Check if user is admin - only admins can access system settings
$user_type = $_SESSION['calendar_fd_user']['type'];
if ($user_type !== 'admin') {
    echo "<div class='alert alert-danger'>Access denied. Only administrators can access system settings.</div>";
    return;
}

// Get current settings
$settings = getSystemSettings();
?>

<div class="row">
  <div class="col-md-8">
    <div class="box box-info">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-cogs"></i> System Settings</h3>
      </div>
      <!-- /.box-header -->
      <!-- form start -->
      <form class="form-horizontal" action="<?php echo WEB_ROOT; ?>views/process.php?cmd=updatesettings" method="post" enctype="multipart/form-data">
        <div class="box-body">
          
          <!-- Logo Upload Section -->
          <div class="form-group">
            <label for="clinic_logo" class="col-sm-3 control-label">Clinic Logo</label>
            <div class="col-sm-9">
              <?php 
              $currentLogo = isset($settings['clinic_logo']) && !empty($settings['clinic_logo']) ? $settings['clinic_logo'] : '';
              if ($currentLogo && file_exists('../' . $currentLogo)) {
              ?>
              <div class="current-logo-preview" style="margin-bottom: 15px; padding: 15px; background: #f9f9f9; border-radius: 8px; text-align: center;">
                <p style="margin-bottom: 10px; font-weight: bold; color: #555;">Current Logo:</p>
                <img src="<?php echo WEB_ROOT . $currentLogo; ?>" alt="Current Logo" style="max-width: 200px; max-height: 100px; border: 2px solid #ddd; border-radius: 4px; padding: 5px; background: white;">
                <div style="margin-top: 10px;">
                  <label class="checkbox-inline">
                    <input type="checkbox" name="remove_logo" value="1"> Remove current logo
                  </label>
                </div>
              </div>
              <?php } else { ?>
              <div class="alert alert-info" style="margin-bottom: 15px;">
                <i class="fa fa-info-circle"></i> No logo uploaded yet. Upload one below.
              </div>
              <?php } ?>
              
              <input type="file" name="clinic_logo" id="clinic_logo" accept="image/png,image/jpeg,image/jpg,image/gif" class="form-control">
              <p class="help-block">
                <i class="fa fa-upload"></i> Upload your clinic logo (PNG, JPG, GIF). Max size: 2MB. Recommended: 300x100px or similar ratio.
              </p>
              
              <!-- Preview area for new upload -->
              <div id="logo-preview" style="display: none; margin-top: 15px; padding: 15px; background: #f0f8ff; border-radius: 8px; text-align: center;">
                <p style="margin-bottom: 10px; font-weight: bold; color: #555;">Preview:</p>
                <img id="logo-preview-img" src="" alt="Logo Preview" style="max-width: 200px; max-height: 100px; border: 2px solid #3498db; border-radius: 4px; padding: 5px; background: white;">
              </div>
            </div>
          </div>
          
          <hr style="margin: 30px 0; border-top: 2px solid #eee;">
          
          <div class="form-group">
            <label for="clinic_name" class="col-sm-3 control-label">Clinic Name</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" name="clinic_name" id="clinic_name" 
                     value="<?php echo htmlspecialchars($settings['clinic_name']); ?>" 
                     placeholder="Enter veterinary clinic name" required>
              <p class="help-block">This name will appear in the system header and emails.</p>
            </div>
          </div>

          <div class="form-group">
            <label for="clinic_address" class="col-sm-3 control-label">Clinic Address</label>
            <div class="col-sm-9">
              <textarea class="form-control" name="clinic_address" id="clinic_address" rows="3" 
                        placeholder="Enter clinic address"><?php echo htmlspecialchars($settings['clinic_address']); ?></textarea>
              <p class="help-block">Full address of the veterinary clinic.</p>
            </div>
          </div>

          <div class="form-group">
            <label for="clinic_phone" class="col-sm-3 control-label">Clinic Phone</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" name="clinic_phone" id="clinic_phone" 
                     value="<?php echo htmlspecialchars($settings['clinic_phone']); ?>" 
                     placeholder="Enter clinic phone number">
              <p class="help-block">Main contact phone number.</p>
            </div>
          </div>

          <div class="form-group">
            <label for="clinic_email" class="col-sm-3 control-label">Clinic Email</label>
            <div class="col-sm-9">
              <input type="email" class="form-control" name="clinic_email" id="clinic_email" 
                     value="<?php echo htmlspecialchars($settings['clinic_email']); ?>" 
                     placeholder="Enter clinic email address">
              <p class="help-block">Email address used for system notifications.</p>
            </div>
          </div>

          <div class="form-group">
            <label for="clinic_hours" class="col-sm-3 control-label">Operating Hours</label>
            <div class="col-sm-9">
              <textarea class="form-control" name="clinic_hours" id="clinic_hours" rows="3" 
                        placeholder="e.g., Mon-Fri: 8:00 AM - 6:00 PM, Sat: 9:00 AM - 4:00 PM"><?php echo htmlspecialchars($settings['clinic_hours']); ?></textarea>
              <p class="help-block">Clinic operating hours (displayed in emails and forms).</p>
            </div>
          </div>

          <div class="form-group">
            <label for="appointment_duration" class="col-sm-3 control-label">Default Appointment Duration</label>
            <div class="col-sm-9">
              <select class="form-control" name="appointment_duration" id="appointment_duration">
                <option value="15" <?php echo ($settings['appointment_duration'] == '15') ? 'selected' : ''; ?>>15 minutes</option>
                <option value="30" <?php echo ($settings['appointment_duration'] == '30') ? 'selected' : ''; ?>>30 minutes</option>
                <option value="45" <?php echo ($settings['appointment_duration'] == '45') ? 'selected' : ''; ?>>45 minutes</option>
                <option value="60" <?php echo ($settings['appointment_duration'] == '60') ? 'selected' : ''; ?>>60 minutes</option>
              </select>
              <p class="help-block">Default duration for appointments.</p>
            </div>
          </div>

          <div class="form-group">
            <label for="booking_advance_days" class="col-sm-3 control-label">Advance Booking Limit</label>
            <div class="col-sm-9">
              <select class="form-control" name="booking_advance_days" id="booking_advance_days">
                <option value="30" <?php echo ($settings['booking_advance_days'] == '30') ? 'selected' : ''; ?>>30 days</option>
                <option value="60" <?php echo ($settings['booking_advance_days'] == '60') ? 'selected' : ''; ?>>60 days</option>
                <option value="90" <?php echo ($settings['booking_advance_days'] == '90') ? 'selected' : ''; ?>>90 days</option>
                <option value="180" <?php echo ($settings['booking_advance_days'] == '180') ? 'selected' : ''; ?>>180 days</option>
                <option value="365" <?php echo ($settings['booking_advance_days'] == '365') ? 'selected' : ''; ?>>1 year</option>
              </select>
              <p class="help-block">How far in advance clients can book appointments.</p>
            </div>
          </div>

          <div class="form-group">
            <label for="email_notifications" class="col-sm-3 control-label">Email Notifications</label>
            <div class="col-sm-9">
              <div class="checkbox">
                <label>
                  <input type="checkbox" name="email_notifications" value="1" 
                         <?php echo ($settings['email_notifications'] == '1') ? 'checked' : ''; ?>>
                  Enable email notifications for appointments
                </label>
              </div>
              <p class="help-block">Send email confirmations and updates to clients.</p>
            </div>
          </div>

        </div>
        <!-- /.box-body -->
        <div class="box-footer">
          <button type="submit" class="btn btn-info pull-right">
            <i class="fa fa-save"></i> Save Settings
          </button>
        </div>
        <!-- /.box-footer -->
      </form>
    </div>
    <!-- /.box -->
  </div>

  <div class="col-md-4">
    <div class="box box-success">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-info-circle"></i> Settings Information</h3>
      </div>
      <div class="box-body">
        <h4>Clinic Logo</h4>
        <p>Your logo will appear in:</p>
        <ul>
          <li>Login page</li>
          <li>System header (if configured)</li>
          <li>Email notifications (future)</li>
          <li>Printed documents (future)</li>
        </ul>
        <p><small><strong>Tip:</strong> Use a transparent PNG for best results.</small></p>
        
        <h4>Clinic Name</h4>
        <p>This name will appear in:</p>
        <ul>
          <li>System header/logo area</li>
          <li>Email notifications</li>
          <li>Appointment confirmations</li>
          <li>Footer copyright</li>
        </ul>

        <h4>Contact Information</h4>
        <p>Used for:</p>
        <ul>
          <li>Email signatures</li>
          <li>Contact forms</li>
          <li>System notifications</li>
        </ul>

        <h4>Appointment Settings</h4>
        <p>Controls:</p>
        <ul>
          <li>Default appointment length</li>
          <li>How far ahead bookings are allowed</li>
          <li>Time slot intervals</li>
        </ul>
      </div>
    </div>

    <div class="box box-warning">
      <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-exclamation-triangle"></i> Important Notes</h3>
      </div>
      <div class="box-body">
        <ul>
          <li><strong>Backup:</strong> Settings are automatically backed up</li>
          <li><strong>Email:</strong> Changes to email settings require system restart</li>
          <li><strong>Access:</strong> Only administrators can modify these settings</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<script>
// Logo preview functionality
document.getElementById('clinic_logo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const previewDiv = document.getElementById('logo-preview');
    const previewImg = document.getElementById('logo-preview-img');
    
    if (file) {
        // Validate file type
        const validTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'];
        if (!validTypes.includes(file.type)) {
            alert('Please upload a valid image file (PNG, JPG, or GIF).');
            e.target.value = '';
            previewDiv.style.display = 'none';
            return;
        }
        
        // Validate file size (2MB max)
        const maxSize = 2 * 1024 * 1024; // 2MB in bytes
        if (file.size > maxSize) {
            alert('File size must be less than 2MB. Your file is ' + (file.size / 1024 / 1024).toFixed(2) + 'MB.');
            e.target.value = '';
            previewDiv.style.display = 'none';
            return;
        }
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(event) {
            previewImg.src = event.target.result;
            previewDiv.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        previewDiv.style.display = 'none';
    }
});

// Warn if trying to remove logo
document.querySelector('input[name="remove_logo"]')?.addEventListener('change', function(e) {
    if (e.target.checked) {
        if (!confirm('Are you sure you want to remove the current logo? This action cannot be undone.')) {
            e.target.checked = false;
        }
    }
});
</script>