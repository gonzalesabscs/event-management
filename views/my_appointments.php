<?php 
// Check if user is logged in and is a client
$user_type = $_SESSION['calendar_fd_user']['type'];
$user_id = $_SESSION['calendar_fd_user']['id'];

if ($user_type !== 'client') {
    echo "<div class='alert alert-danger'>Access denied. This page is only for pet owners.</div>";
    return;
}

// Get client's appointments
$sql = "SELECT a.*, u.name as owner_name, u.email, u.phone 
        FROM tbl_appointments a 
        JOIN tbl_users u ON a.uid = u.id 
        WHERE a.uid = $user_id 
        ORDER BY a.appointment_date DESC";
$result = dbQuery($sql);
$appointments = array();
while($row = dbFetchAssoc($result)) {
    $appointments[] = $row;
}
?>

<!-- No-Show Warning Alert -->
<div class="col-md-12">
  <div class="alert alert-warning" style="background: #fff3cd; border-left: 4px solid #ff9800; padding: 15px; margin-bottom: 20px; border-radius: 6px;">
    <h4 style="margin-top: 0; color: #ff6f00;"><i class="fa fa-exclamation-triangle"></i> Important Attendance Policy</h4>
    <p style="margin-bottom: 0; font-size: 14px; line-height: 1.6;">
      <strong>If you do not arrive on your expected schedule, your appointment will be automatically cancelled.</strong>
      Please ensure you arrive on time for your appointments. No-shows will result in automatic cancellation without prior notice.
    </p>
  </div>
</div>

<div class="col-md-12">
  <div class="box">
    <div class="box-header with-border">
      <h3 class="box-title"><i class="fa fa-calendar"></i> My Appointments</h3>
      <div class="box-tools pull-right">
        <a href="<?php echo WEB_ROOT; ?>views/?v=DB" class="btn btn-primary btn-sm">
          <i class="fa fa-plus"></i> Book New Appointment
        </a>
      </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
      <?php if (empty($appointments)) { ?>
        <div class="alert alert-info">
          <i class="fa fa-info-circle"></i> You don't have any appointments yet. 
          <a href="<?php echo WEB_ROOT; ?>views/?v=DB" class="alert-link">Book your first appointment</a>.
        </div>
      <?php } else { ?>
      <div class="table-responsive">
        <table class="table table-bordered client-appointments-table">
        <tr>
          <th style="width: 10px">#</th>
          <th>Pet Name</th>
          <th>Pet Type</th>
          <th>Appointment Date</th>
          <th>Appointment Type</th>
          <th>Status</th>
          <th>Booked Date</th>
          <th style="width: 150px">Actions</th>
        </tr>
        <?php
        $idx = 1;
        foreach($appointments as $appointment) {
            extract($appointment);
            $stat = '';
            $canCancel = false;
            
            if($status == "PENDING") {
                $stat = 'warning';
                $canCancel = true; // Can cancel pending appointments
            } else if ($status == "APPROVED") {
                $stat = 'success';
                // Can cancel within 24 hours of approval
                if ($approved_date) {
                    $approvedTime = strtotime($approved_date);
                    $currentTime = time();
                    $hoursSinceApproval = ($currentTime - $approvedTime) / 3600;
                    $canCancel = ($hoursSinceApproval <= 24);
                }
            } else if ($status == "ARRIVED") {
                $stat = 'info';
                $canCancel = false; // Cannot cancel if already arrived
            } else if($status == "DENIED") {
                $stat = 'danger';
            } else if($status == "CANCELLED") {
                $stat = 'default';
            } else if($status == "AUTO CANCELLED") {
                $stat = 'danger';
            }
            
            // Format dates
            $appointmentDateTime = date('M j, Y g:i A', strtotime($appointment_date));
            $bookedDateTime = date('M j, Y g:i A', strtotime($bdate));
            
            // Check if appointment is in the past
            $isPastAppointment = strtotime($appointment_date) < time();
        ?>
        <tr>
          <td><?php echo $idx++; ?></td>
          <td><strong><?php echo htmlspecialchars($pet_name); ?></strong></td>
          <td><?php echo htmlspecialchars($pet_type); ?></td>
          <td><?php echo $appointmentDateTime; ?></td>
          <td><?php echo htmlspecialchars($appointment_type); ?></td>
          <td><span class="label label-<?php echo $stat; ?>"><?php echo $status; ?></span></td>
          <td><?php echo $bookedDateTime; ?></td>
          <td>
            <div class="client-appointment-actions">
              <?php if ($canCancel && !$isPastAppointment && $status !== 'CANCELLED' && $status !== 'AUTO CANCELLED') { ?>
                <button onclick="cancelAppointment(<?php echo $id; ?>, '<?php echo htmlspecialchars($pet_name); ?>')" 
                        class="btn btn-danger btn-xs" title="Cancel this appointment">
                  <i class="fa fa-times"></i> Cancel
                </button>
              <?php } ?>
              
              <?php if ($status == 'APPROVED' && !$isPastAppointment) { ?>
                <span class="btn btn-info btn-xs" title="Appointment confirmed - see you soon!">
                  <i class="fa fa-check-circle"></i> Confirmed
                </span>
              <?php } ?>
              
              <?php if ($status == 'PENDING') { ?>
                <span class="btn btn-warning btn-xs" title="Waiting for clinic approval">
                  <i class="fa fa-clock-o"></i> Pending
                </span>
              <?php } ?>
              
              <?php if ($isPastAppointment && $status == 'APPROVED') { ?>
                <span class="btn btn-success btn-xs" title="Appointment completed">
                  <i class="fa fa-check"></i> Completed
                </span>
              <?php } ?>
            </div>
            
            <?php if ($status == 'APPROVED' && $approved_date && !$isPastAppointment) { 
                $approvedTime = strtotime($approved_date);
                $currentTime = time();
                $hoursSinceApproval = ($currentTime - $approvedTime) / 3600;
                $hoursLeft = 24 - $hoursSinceApproval;
                
                if ($hoursLeft > 0 && $canCancel) { ?>
                  <div class="cancellation-notice">
                    <small class="text-muted">
                      <i class="fa fa-info-circle"></i> 
                      You can cancel this appointment for <?php echo number_format($hoursLeft, 1); ?> more hours
                    </small>
                  </div>
                <?php } else if ($hoursLeft <= 0) { ?>
                  <div class="cancellation-notice">
                    <small class="text-warning">
                      <i class="fa fa-exclamation-triangle"></i> 
                      24-hour cancellation period has expired
                    </small>
                  </div>
                <?php } ?>
            <?php } ?>
            
            <?php if ($status == 'CANCELLED' && $cancelled_date) { ?>
              <div class="cancellation-info">
                <small class="text-muted">
                  <i class="fa fa-info-circle"></i> 
                  Cancelled on <?php echo date('M j, Y g:i A', strtotime($cancelled_date)); ?>
                  <?php if ($cancellation_reason) { ?>
                    <br>Reason: <?php echo htmlspecialchars($cancellation_reason); ?>
                  <?php } ?>
                </small>
              </div>
            <?php } ?>
            
            <?php if ($status == 'AUTO CANCELLED' && $auto_cancelled_date) { ?>
              <div class="cancellation-info" style="background: #ffebee; padding: 8px; border-radius: 4px; margin-top: 8px;">
                <small class="text-danger">
                  <i class="fa fa-exclamation-triangle"></i> 
                  <strong>Auto-cancelled on <?php echo date('M j, Y g:i A', strtotime($auto_cancelled_date)); ?></strong>
                  <br>Reason: No-show (did not arrive at scheduled time)
                </small>
              </div>
            <?php } ?>
          </td>
        </tr>
        <?php } ?>
        </table>
      </div>
      <?php } ?>
    </div>
    <!-- /.box-body -->
    <div class="box-footer">
      <div class="row">
        <div class="col-md-6">
          <p class="text-muted">
            <i class="fa fa-info-circle"></i> 
            <strong>Cancellation Policy:</strong> You can cancel confirmed appointments within 24 hours of approval.
          </p>
        </div>
        <div class="col-md-6 text-right">
          <a href="<?php echo WEB_ROOT; ?>views/?v=DB" class="btn btn-primary">
            <i class="fa fa-plus"></i> Book Another Appointment
          </a>
        </div>
      </div>
    </div>
  </div>
  <!-- /.box -->
</div>

<!-- Cancel Appointment Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><i class="fa fa-times-circle"></i> Cancel Appointment</h4>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to cancel the appointment for <strong id="petNameDisplay"></strong>?</p>
        <div class="form-group">
          <label for="cancellationReason">Reason for cancellation (optional):</label>
          <textarea class="form-control" id="cancellationReason" rows="3" 
                    placeholder="Please let us know why you're cancelling..."></textarea>
        </div>
        <div class="alert alert-warning">
          <i class="fa fa-exclamation-triangle"></i>
          <strong>Please note:</strong> Once cancelled, you'll need to book a new appointment if you change your mind.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Keep Appointment</button>
        <button type="button" class="btn btn-danger" id="confirmCancelBtn">
          <i class="fa fa-times"></i> Yes, Cancel Appointment
        </button>
      </div>
    </div>
  </div>
</div>

<style>
.client-appointment-actions {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.client-appointment-actions .btn {
  font-size: 11px;
  padding: 4px 8px;
  border-radius: 4px;
  text-align: center;
}

.cancellation-notice,
.cancellation-info {
  margin-top: 8px;
  padding: 5px 8px;
  background: rgba(0,0,0,0.05);
  border-radius: 4px;
}

.client-appointments-table {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.client-appointments-table thead th {
  background: #2d3748;
  color: white;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 15px;
  font-size: 12px;
}

.client-appointments-table tbody tr:hover {
  background: #f8f9fa;
}

@media (max-width: 768px) {
  .client-appointment-actions {
    flex-direction: row;
    flex-wrap: wrap;
    gap: 3px;
  }
  
  .client-appointment-actions .btn {
    font-size: 9px;
    padding: 2px 4px;
  }
}
</style>

<script>
let appointmentToCancel = null;

function cancelAppointment(appointmentId, petName) {
    appointmentToCancel = appointmentId;
    $('#petNameDisplay').text(petName);
    $('#cancellationReason').val('');
    $('#cancelModal').modal('show');
}

$(document).ready(function() {
    $('#confirmCancelBtn').on('click', function() {
        if (appointmentToCancel) {
            var reason = $('#cancellationReason').val().trim();
            
            // Show loading state
            $(this).html('<i class="fa fa-spinner fa-spin"></i> Cancelling...').prop('disabled', true);
            
            // Send cancellation request
            $.ajax({
                url: '<?php echo WEB_ROOT; ?>api/process.php',
                method: 'POST',
                data: {
                    cmd: 'cancelAppointment',
                    appointmentId: appointmentToCancel,
                    reason: reason
                },
                success: function(response) {
                    $('#cancelModal').modal('hide');
                    alert('Your appointment has been cancelled successfully. You will receive a confirmation email shortly.');
                    location.reload();
                },
                error: function() {
                    alert('Error cancelling appointment. Please try again or contact the clinic.');
                    $('#confirmCancelBtn').html('<i class="fa fa-times"></i> Yes, Cancel Appointment').prop('disabled', false);
                }
            });
        }
    });
    
    // Reset modal when closed
    $('#cancelModal').on('hidden.bs.modal', function() {
        appointmentToCancel = null;
        $('#confirmCancelBtn').html('<i class="fa fa-times"></i> Yes, Cancel Appointment').prop('disabled', false);
    });
});
</script>