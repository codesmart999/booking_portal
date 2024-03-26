<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PHP and Bootstrap UI</title>
  <!-- Bootstrap CSS -->
  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Datepicker CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet">
  <!-- jQuery -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <!-- Bootstrap JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <!-- Bootstrap Datepicker JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
  <!-- Custom CSS -->
  <style>
    .calendar-sidebar {
      background-color: #f8f9fa;
      border-right: 1px solid #dee2e6;
    }
    .time-slots-sidebar {
      background-color: #f8f9fa;
      border-left: 1px solid #dee2e6;
    }
  </style>
</head>
<body>

<div class="container-fluid">
  <div class="row">
    <!-- Left Sidebar (Calendar) -->
    <div class="col-md-3 calendar-sidebar">
      <h3 class="mt-3 mb-3">Calendar</h3>
      
      <!-- Calendar Datepicker -->
      <div id="datepicker"></div>
    </div>
    
    <!-- Main Content (Time Slots) -->
    <div class="col-md-9 time-slots-sidebar">
      <h3 class="mt-3 mb-3">Time Slots</h3>
      <!-- Time slots with checkboxes -->
      <div class="form-group">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="" id="timeSlot1">
          <label class="form-check-label" for="timeSlot1">8:00am - 8:15am</label>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="" id="timeSlot2">
          <label class="form-check-label" for="timeSlot2">8:15am - 8:30am</label>
        </div>
        <!-- Add more time slots as needed -->
      </div>
    </div>
  </div>
</div>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!-- Initialize Datepicker -->
<script>
  $(document).ready(function(){
    // Initialize Month and Year Picker
    $('#monthYearPicker').datepicker({
      format: "MM yyyy",
      viewMode: "months",
      minViewMode: "months",
      autoclose: true,
    });

    // Initialize Calendar Datepicker
    $('#datepicker').datepicker({
      autoclose: true, // Close the datepicker when a date is selected
      todayHighlight: true // Highlight today's date
    });
  });
</script>

</body>
</html>
