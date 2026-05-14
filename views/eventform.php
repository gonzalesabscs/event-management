<!-- Modern Veterinary Appointment Booking Form -->

<!-- No-Show Warning Alert -->
<div class="alert alert-warning" style="background: #fff3cd; border-left: 4px solid #ff9800; padding: 15px; margin-bottom: 20px; border-radius: 6px;">
  <h4 style="margin-top: 0; color: #ff6f00;"><i class="fa fa-exclamation-triangle"></i> Important Notice</h4>
  <p style="margin-bottom: 0; font-size: 14px; line-height: 1.6;">
    <strong>If you do not arrive on your expected schedule, your appointment will be automatically cancelled.</strong>
    Please ensure you arrive on time for your appointment. Late arrivals may result in automatic cancellation without prior notice.
  </p>
</div>

<div class="box box-primary booking-form">
  <div class="box-header with-border">
    <h3 class="box-title"><b>Book Veterinary Appointment</b></h3>
  </div>
  <!-- /.box-header -->
  <!-- form start -->
  <form role="form" action="<?php echo WEB_ROOT; ?>api/process.php?cmd=book" method="post" id="appointmentForm">
    <div class="box-body">
      <!-- Pet Owner Selection -->
      <div class="form-group">
        <label for="petOwnerSelect">Pet Owner Name *</label>
        <input type="hidden" name="userId" value="" id="userId"/>
        <select name="name" class="form-control" id="petOwnerSelect" required>
          <option value="">--select pet owner--</option>
          <?php
          $sql = "SELECT id, name FROM tbl_users WHERE type = 'client' ORDER BY name";
          $result = dbQuery($sql);
          if ($result && dbNumRows($result) > 0) {
            while($row = dbFetchAssoc($result)) {
              extract($row);
          ?>
          <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
          <?php 
            }
          } else {
            echo '<option value="">No pet owners found</option>';
          }
          ?>
        </select>
        <div class="validation-message" id="petOwnerError" style="display: none; color: #e74c3c; font-size: 12px; margin-top: 5px;">
          Pet owner name is required.
        </div>
        <div class="selected-pet-owner" id="selectedPetOwner" style="display: none; margin-top: 10px; padding: 8px 12px; background: rgba(52, 152, 219, 0.1); color: #3498db; border-radius: 4px; font-size: 13px;">
          <i class="fa fa-user"></i> <strong>Selected:</strong> <span id="selectedOwnerName"></span>
        </div>
        <div class="success-message" id="petOwnerFeedback" style="display: none; margin-top: 8px; padding: 6px 10px; background: rgba(39, 174, 96, 0.1); color: #27ae60; border-radius: 4px; font-size: 12px;">
          <i class="fa fa-check-circle"></i> Pet owner information loaded successfully
        </div>
      </div>
      
      <!-- Contact Information -->
      <div class="form-group">
        <label for="address">Address *</label>
        <textarea name="address" class="form-control" placeholder="Enter address" id="address" rows="3" required></textarea>
        <div class="validation-message" id="addressError" style="display: none; color: #e74c3c; font-size: 12px; margin-top: 5px;">
          Address is required (minimum 10 characters).
        </div>
      </div>
      
      <div class="form-group">
        <label for="phone">Phone Number *</label>
        <input type="tel" name="phone" class="form-control" placeholder="Enter phone number" id="phone" required>
        <div class="validation-message" id="phoneError" style="display: none; color: #e74c3c; font-size: 12px; margin-top: 5px;">
          Phone number is required.
        </div>
      </div>
      
      <div class="form-group">
        <label for="email">Email Address *</label>
        <input type="email" name="email" class="form-control" placeholder="Enter email address" id="email" required>
        <div class="validation-message" id="emailError" style="display: none; color: #e74c3c; font-size: 12px; margin-top: 5px;">
          Valid email address is required.
        </div>
      </div>

      <!-- Pet Information -->
      <div class="form-group">
        <label for="pet_name">Pet Name *</label>
        <input type="text" name="pet_name" class="form-control" placeholder="Enter pet's name" id="pet_name" required>
        <div class="validation-message" id="petNameError" style="display: none; color: #e74c3c; font-size: 12px; margin-top: 5px;">
          Pet name is required.
        </div>
      </div>

      <div class="form-group">
        <label for="pet_type">Pet Type *</label>
        <select name="pet_type" class="form-control" id="pet_type" required>
          <option value="">--select pet type--</option>
          <option value="Dog">Dog</option>
          <option value="Cat">Cat</option>
        </select>
        <div class="validation-message" id="petTypeError" style="display: none; color: #e74c3c; font-size: 12px; margin-top: 5px;">
          Pet type is required.
        </div>
      </div>

      <div class="form-group">
        <label for="pet_breed">Pet Breed (Optional)</label>
        <input type="text" name="pet_breed" class="form-control" placeholder="Enter pet's breed (optional)" id="pet_breed">
      </div>

      <div class="form-group">
        <label for="pet_gender">Pet Gender *</label>
        <select name="pet_gender" class="form-control" id="pet_gender" required>
          <option value="">--select gender--</option>
          <option value="Male">Male</option>
          <option value="Female">Female</option>
        </select>
        <div class="validation-message" id="petGenderError" style="display: none; color: #e74c3c; font-size: 12px; margin-top: 5px;">
          Pet gender is required.
        </div>
      </div>

      <div class="form-group">
        <label for="pet_age">Pet Age (years) *</label>
        <input type="number" name="pet_age" class="form-control" placeholder="Enter pet's age in years" id="pet_age" required min="0" max="50" step="1">
        <div class="validation-message" id="petAgeError" style="display: none; color: #e74c3c; font-size: 12px; margin-top: 5px;">
          Pet age is required (0-50 years).
        </div>
      </div>

      <div class="form-group">
        <label for="appointment_type">Appointment Type *</label>
        <select name="appointment_type" class="form-control" id="appointment_type" required>
          <option value="">--select appointment type--</option>
          <option value="General Checkup">General Checkup</option>
          <option value="Vaccination">Vaccination</option>
          <option value="Surgery">Surgery</option>
          <option value="Dental Care">Dental Care</option>
          <option value="Emergency">Emergency</option>
          <option value="Follow-up">Follow-up</option>
          <option value="Grooming">Grooming</option>
          <option value="Consultation">Consultation</option>
        </select>
        <div class="validation-message" id="appointmentTypeError" style="display: none; color: #e74c3c; font-size: 12px; margin-top: 5px;">
          Appointment type is required.
        </div>
      </div>
      
      <!-- Date and Time -->
      <div class="form-group">
        <div class="row">
          <div class="col-xs-6">
            <label for="rdate">Appointment Date *</label>
            <input type="date" name="rdate" class="form-control" id="rdate" required min="<?php echo date('Y-m-d'); ?>">
            <div class="validation-message" id="dateError" style="display: none; color: #e74c3c; font-size: 12px; margin-top: 5px;">
              Please select a valid future date.
            </div>
          </div>
          <div class="col-xs-6">
            <label for="rtime">Appointment Time *</label>
            <input type="time" name="rtime" class="form-control" id="rtime" required min="08:00" max="18:00">
            <div class="validation-message" id="timeError" style="display: none; color: #e74c3c; font-size: 12px; margin-top: 5px;">
              Please select a time between 8:00 AM and 6:00 PM.
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- /.box-body -->
    <div class="box-footer">
      <button type="submit" class="btn btn-primary" id="submitBtn">
        <i class="fa fa-calendar-plus-o"></i> Book Appointment
      </button>
      <button type="reset" class="btn btn-default" id="resetBtn">
        <i class="fa fa-refresh"></i> Reset Form
      </button>
    </div>
  </form>
</div>
<!-- /.box -->

<script type="text/javascript">
$(document).ready(function() {
    console.log('Appointment form initialized');
    console.log('jQuery version:', $.fn.jquery);
    
    // Initialize form
    initializeForm();
    
    function initializeForm() {
        // Pet owner selection handler
        $('#petOwnerSelect').on('change', function() {
            handlePetOwnerSelection();
        });
        
        // Form validation and submission
        $('#appointmentForm').on('submit', function(e) {
            return validateAndSubmitForm(e);
        });
        
        // Reset form handler
        $('#resetBtn').on('click', function() {
            resetForm();
        });
        
        // Real-time validation for select elements
        $('select.form-control').on('change', function() {
            validateSelectField($(this));
        });
        
        // Real-time validation for input elements
        $('input.form-control, textarea.form-control').on('blur', function() {
            validateInputField($(this));
        });
    }
    
    function handlePetOwnerSelection() {
        var $select = $('#petOwnerSelect');
        var selectedId = $select.val();
        var selectedText = $select.find('option:selected').text();
        
        console.log('Pet owner selected - ID:', selectedId, 'Name:', selectedText);
        
        // Clear previous states
        hideAllMessages();
        
        if (selectedId && selectedId !== '') {
            // Show selected pet owner immediately
            $('#selectedOwnerName').text(selectedText);
            $('#selectedPetOwner').show();
            
            // Mark field as valid
            markFieldValid($select);
            
            // Load pet owner details
            loadPetOwnerDetails(selectedId);
        } else {
            // Clear form and show error
            clearPetOwnerData();
            markFieldInvalid($select, 'petOwnerError');
        }
    }
    
    function loadPetOwnerDetails(userId) {
        console.log('Loading details for user ID:', userId);
        
        // Show loading state
        $('#submitBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Loading...');
        
        $.ajax({
            url: '<?php echo WEB_ROOT; ?>api/process.php',
            method: 'GET',
            data: {
                cmd: 'user',
                userId: userId
            },
            dataType: 'json',
            timeout: 10000,
            success: function(data) {
                console.log('User data received:', data);
                handleUserDataSuccess(data);
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
                handleUserDataError(xhr, status, error);
            },
            complete: function() {
                // Re-enable submit button
                $('#submitBtn').prop('disabled', false).html('<i class="fa fa-calendar-plus-o"></i> Book Appointment');
            }
        });
    }
    
    function handleUserDataSuccess(data) {
        if (data.error) {
            console.error('API returned error:', data.error);
            alert('Error: ' + data.error);
            clearPetOwnerData();
            return;
        }
        
        if (data.user_id) {
            // Populate form fields
            $('#userId').val(data.user_id);
            $('#email').val(data.email || '').addClass('auto-filled');
            $('#address').val(data.address || '').addClass('auto-filled');
            $('#phone').val(data.phone_no || '').addClass('auto-filled');
            
            // Show success message
            $('#petOwnerFeedback').show();
            
            // Remove auto-filled styling after delay
            setTimeout(function() {
                $('.auto-filled').removeClass('auto-filled');
            }, 2000);
            
            console.log('Pet owner data loaded successfully');
        } else {
            console.error('Invalid data structure received:', data);
            alert('Invalid pet owner data received from server.');
            clearPetOwnerData();
        }
    }
    
    function handleUserDataError(xhr, status, error) {
        var errorMessage = 'Failed to load pet owner information.';
        
        if (xhr.responseText) {
            try {
                var errorData = JSON.parse(xhr.responseText);
                errorMessage = errorData.error || errorMessage;
            } catch(e) {
                console.error('Could not parse error response:', xhr.responseText);
            }
        }
        
        alert(errorMessage + ' Please try again.');
        clearPetOwnerData();
    }
    
    function clearPetOwnerData() {
        $('#userId').val('');
        $('#email').val('').removeClass('auto-filled');
        $('#address').val('').removeClass('auto-filled');
        $('#phone').val('').removeClass('auto-filled');
        $('#selectedPetOwner').hide();
        $('#petOwnerFeedback').hide();
    }
    
    function validateAndSubmitForm(e) {
        console.log('Validating form submission');
        
        var isValid = true;
        hideAllMessages();
        
        // Validate pet owner selection
        if (!$('#petOwnerSelect').val()) {
            markFieldInvalid($('#petOwnerSelect'), 'petOwnerError');
            isValid = false;
        }
        
        // Validate required text fields
        var requiredFields = [
            { field: '#address', error: 'addressError', minLength: 10 },
            { field: '#phone', error: 'phoneError' },
            { field: '#email', error: 'emailError', type: 'email' },
            { field: '#pet_name', error: 'petNameError' }
        ];
        
        requiredFields.forEach(function(item) {
            var $field = $(item.field);
            var value = $field.val().trim();
            
            if (!value) {
                markFieldInvalid($field, item.error);
                isValid = false;
            } else if (item.minLength && value.length < item.minLength) {
                markFieldInvalid($field, item.error);
                isValid = false;
            } else if (item.type === 'email' && !isValidEmail(value)) {
                markFieldInvalid($field, item.error);
                isValid = false;
            } else {
                markFieldValid($field);
            }
        });
        
        // Validate select fields
        var selectFields = [
            { field: '#pet_type', error: 'petTypeError' },
            { field: '#pet_gender', error: 'petGenderError' },
            { field: '#appointment_type', error: 'appointmentTypeError' }
        ];
        
        selectFields.forEach(function(item) {
            var $field = $(item.field);
            if (!$field.val()) {
                markFieldInvalid($field, item.error);
                isValid = false;
            } else {
                markFieldValid($field);
            }
        });
        
        // Validate pet age
        var $petAge = $('#pet_age');
        var petAge = parseInt($petAge.val());
        if (!$petAge.val() || isNaN(petAge) || petAge < 0 || petAge > 50) {
            markFieldInvalid($petAge, 'petAgeError');
            isValid = false;
        } else {
            markFieldValid($petAge);
        }
        
        // Validate date and time
        var $date = $('#rdate');
        var $time = $('#rtime');
        
        if (!$date.val()) {
            markFieldInvalid($date, 'dateError');
            isValid = false;
        } else {
            var selectedDate = new Date($date.val());
            var today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                markFieldInvalid($date, 'dateError');
                isValid = false;
            } else {
                markFieldValid($date);
            }
        }
        
        if (!$time.val()) {
            markFieldInvalid($time, 'timeError');
            isValid = false;
        } else {
            markFieldValid($time);
        }
        
        if (!isValid) {
            e.preventDefault();
            
            // Scroll to first error
            var $firstError = $('.form-control.is-invalid').first();
            if ($firstError.length) {
                $('html, body').animate({
                    scrollTop: $firstError.offset().top - 100
                }, 500);
                $firstError.focus();
            }
            
            alert('Please correct the errors in the form before submitting.');
            return false;
        }
        
        console.log('Form validation passed');
        return true;
    }
    
    function validateSelectField($field) {
        var fieldId = $field.attr('id');
        var errorId = fieldId + 'Error';
        
        if ($field.val()) {
            markFieldValid($field);
        } else {
            markFieldInvalid($field, errorId);
        }
    }
    
    function validateInputField($field) {
        var fieldId = $field.attr('id');
        var errorId = fieldId + 'Error';
        var value = $field.val().trim();
        
        if ($field.prop('required') && !value) {
            markFieldInvalid($field, errorId);
        } else if (fieldId === 'email' && value && !isValidEmail(value)) {
            markFieldInvalid($field, errorId);
        } else if (fieldId === 'address' && value && value.length < 10) {
            markFieldInvalid($field, errorId);
        } else {
            markFieldValid($field);
        }
    }
    
    function markFieldValid($field) {
        $field.removeClass('is-invalid').addClass('is-valid');
        $field.css({
            'border-color': '#28a745',
            'box-shadow': '0 0 0 0.2rem rgba(40, 167, 69, 0.25)'
        });
    }
    
    function markFieldInvalid($field, errorId) {
        $field.removeClass('is-valid').addClass('is-invalid');
        $field.css({
            'border-color': '#dc3545',
            'box-shadow': '0 0 0 0.2rem rgba(220, 53, 69, 0.25)'
        });
        
        if (errorId) {
            $('#' + errorId).show();
        }
    }
    
    function hideAllMessages() {
        $('.validation-message, .success-message').hide();
        $('.form-control').removeClass('is-valid is-invalid').css({
            'border-color': '',
            'box-shadow': ''
        });
    }
    
    function resetForm() {
        $('#appointmentForm')[0].reset();
        clearPetOwnerData();
        hideAllMessages();
        console.log('Form reset');
    }
    
    function isValidEmail(email) {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
});
</script>

<style>
/* Auto-filled field styling */
.auto-filled {
    background-color: rgba(52, 152, 219, 0.1) !important;
    border-color: #3498db !important;
    transition: all 0.3s ease;
}

/* Form styling improvements */
.form-control {
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
}

/* Validation states */
.is-valid {
    border-color: #28a745 !important;
}

.is-invalid {
    border-color: #dc3545 !important;
}

/* Button styling */
#submitBtn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Message styling */
.validation-message {
    font-size: 12px;
    margin-top: 5px;
}

.success-message {
    font-size: 12px;
    margin-top: 8px;
}

.selected-pet-owner {
    margin-top: 10px;
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 13px;
}
</style>