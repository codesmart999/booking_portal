<?php 
	require_once('header.php');
	require_once('utils.php');

	$message = "";
    $db = getDBConnection();

    $current_date = date("d/m/Y");

    $arrSystems = array();

    $stmt = $db->prepare("SELECT SystemId, FullName FROM systems");
	$stmt->execute();
    $stmt->bind_result($system_id, $full_name);
    $stmt->store_result();
    while ($stmt->fetch()) {
    	$arrSystems[$system_id] = $full_name;
    }
    
    $message = '<p class="Message fst-italic fw-bold text-success d-none">'._lang('success_update').'</p>';
?>

<h4 class="page-title">Move Bookings</h4>
<?php echo $message ?>

<div class="container-fluid move-booking-container">
    <form method="post" class="form-horizontal" id="FRM_MOVE_BOOKING">
        <div class="row">
            <div class="col-md-4 panel-container">
                <div class="row">
                    <div class="col-md-12 panel-header">
                        1. Select a booking to reschedule.
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <label for="from_date" class="form-control form-control-sm">Select Date:</label>
                    </div>
                    <div class="col-md-8 input-group">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fa fa-calendar"></i></span> <!-- Date icon -->
                        </div>
                        <input type="text" id="from_date" name="from_date" required="" class="required date valid form-control form-control-sm" placeholder="dd/mm/yyyy" value="<?php echo $current_date; ?>"/>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <label for="from_system" class="form-control form-control-sm">Individual System:</label>
                    </div>
                    <div class="col-md-8 input-group">
                        <select class="custom-select" id="from_system" name="from_system">
                            <option value="0">---Select an Individual System---</option>
                        <?php
                            foreach ($arrSystems as $system_id => $full_name) {
                                echo "<option value='$system_id'>$full_name</option>";
                            }
                        ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-control form-control-sm">Select Booking:</label>
                    </div>
                    <div class="col-md-8">
                        <table id="tbl_from_booking">
                            <!-- <tr>
                                <td>
                                    <input type="checkbox" id="chk_from_booking_1" class="chk_from_booking_code" />
                                </td>
                                <td>
                                    <label for="chk_from_booking_1" class="timeslot booked">8:00am to 8:15m</label>
                                </td>
                            </tr> -->
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4 panel-container">
                <div class="row">
                    <div class="col-md-12 panel-header">
                        2. Select an available timeslot.
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <label for="to_date" class="form-control form-control-sm">Select Date:</label>
                    </div>
                    <div class="col-md-8 input-group">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fa fa-calendar"></i></span> <!-- Date icon -->
                        </div>
                        <input type="text" id="to_date" name="to_date" required="" class="required date valid form-control form-control-sm" placeholder="dd/mm/yyyy" value="<?php echo $current_date; ?>"/>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <label for="to_system" class="form-control form-control-sm">Individual System:</label>
                    </div>
                    <div class="col-md-8 input-group">
                        <select class="custom-select" id="to_system" name="to_system">
                            <option value="0">---Select an Individual System---</option>
                        <?php
                            foreach ($arrSystems as $system_id => $full_name) {
                                echo "<option value='$system_id'>$full_name</option>";
                            }
                        ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-control form-control-sm">Select Available Slot:</label>
                    </div>
                    <div class="col-md-8">
                        <table id="tbl_to_booking">
                            <!-- <tr>
                                <td>
                                    <input type="checkbox" id="chk_to_booking_1" class="chk_to_booking_code" />
                                </td>
                                <td>
                                    <label for="chk_to_booking_1" class="timeslot booked">8:00am to 8:15m</label>
                                </td>
                            </tr> -->
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4 panel-container">
                <div class="row">
                    <div class="col-md-12 panel-header">
                        3. Confirm.
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-control form-control-sm">Message (Optional):</label>
                    </div>
                    <div class="col-md-8 input-group">
                        <textarea name="message" rows=5></textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-control form-control-sm">Comment (Optional):</label>
                    </div>
                    <div class="col-md-8 input-group">
                        <textarea name="comment" rows=5></textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4"></div>
                    <div class="col-md-8">
                        <button type="button" class="btn btn-primary" name="Move" value="Move" id="btnMove">Move</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php
    $stmt->close();
    $db->close();

    require_once('footer.php');
?>

<script>
	$(document).ready(function() { 
        var apiUri = "/api/bookings.php";
        var arr_from_bookings = null;
        var arr_to_all_bookingperiods = null;

        $("#from_date").on("dateSelected", function() {
            loadFromBookingPeriods();
            
            $("#to_date").val($(this).val());
            loadToBookingPeriods();
        })
        $("#from_system").on("change", function() {
            loadFromBookingPeriods();
            
            $("#to_system").val($(this).val());
            loadToBookingPeriods();
        })
        $("#to_date").on("dateSelected", function() {
            loadToBookingPeriods();
        })
        $("#to_system").on("change", function() {
            loadToBookingPeriods();
        })
        $("#btnMove").on("click", function() {
            const chk_from_booking = $('input[name="chk_from_booking"]:checked').val(); //Booking Code
            const chk_to_booking = $('input[name="chk_to_booking"]:checked').val(); //BookingPeriod ID
            let arr_target_bookingperiods = []; //Slot to which the booking will be moved.

            if (!chk_from_booking) {
                alert("First, select a booking to reschedule.");
                return;
            }

            if (!chk_to_booking) {
                alert("Select an available timeslot in the Individual System you want to move the booking to.")
                return;
            }

            var arr_timeslots = arr_from_bookings[chk_from_booking];
            var duration_in_mins = parseInt(arr_timeslots[1]) - parseInt(arr_timeslots[0]);

            for (var i = 0; i < arr_to_all_bookingperiods.length; i++) {
                if (!arr_target_bookingperiods.length && arr_to_all_bookingperiods[i].id != chk_to_booking)
                    continue;
                else if (duration_in_mins <=0 || !arr_to_all_bookingperiods[i].isAvailable)
                    break;
                else {
                    arr_target_bookingperiods.push(arr_to_all_bookingperiods[i].FromInMinutes + "-" + arr_to_all_bookingperiods[i].ToInMinutes);
                    duration_in_mins -= parseInt(arr_to_all_bookingperiods[i].ToInMinutes - arr_to_all_bookingperiods[i].FromInMinutes);
                }
            }

            if (duration_in_mins > 0) {
                alert("The available timeslots you selected are inappropriate in duration.");
                return;
            }

            var formData = $("#FRM_MOVE_BOOKING").serializeArray();
            // Define an array to store the names of the fields you want to keep
            var fieldsToKeep = ["chk_from_booking", "to_date", "from_system", "to_system", "message", "comment"]; // Add the names of the fields you want to keep

            // Filter out the unnecessary fields
            formData = formData.filter(function(item) {
                return fieldsToKeep.includes(item.name); // Keep only the fields whose names are in the fieldsToKeep array
            });

			formData.push({ name: "action", value: "move_booking" });
            formData.push({ name: "target_bookingperiods", value: arr_target_bookingperiods })

			$.post(apiUri, formData, function (data) {
				var res = JSON.parse(data);
				
                $(".Message").removeClass('d-none');
				if (!res || res.status == "error"){
					$(".Message").removeClass("text-success");
					$(".Message").addClass("text-danger");
                    $(".Message").html("There was an error while moving booking in the server.");
				} else {
					$(".Message").removeClass("text-danger");
					$(".Message").addClass("text-success");
                    $(".Message").html("Updated Successfully.");

                    loadFromBookingPeriods();
                    loadToBookingPeriods();
				}
	        });
        });

        function loadFromBookingPeriods() {
            var from_date = $("#from_date").val();
            var from_system_id = $("#from_system").val();

            var formData = [];
			formData.push({ name: "action", value: "get_bookings_by_system_date" });
			formData.push({ name: "system_id", value: from_system_id });
            formData.push({ name: "date", value: from_date });

            const table = $('#tbl_from_booking');
            table.html("");

			$.post(apiUri, formData, function (data) {
				var res = JSON.parse(data);

                var arr_bookingperiods = res.booking_periods;
                
                arr_from_bookings = res.booking_info;

                var prev_booking_code = "";

				arr_bookingperiods.forEach(objBookingPeriod => {
                    var objBookingInfo = null

                    if (arr_from_bookings.hasOwnProperty(objBookingPeriod.FromInMinutes + "-" + objBookingPeriod.ToInMinutes)) {
                        objBookingInfo = arr_from_bookings[objBookingPeriod.FromInMinutes + "-" + objBookingPeriod.ToInMinutes];
                    }

                    // Create a new row for each element
                    const row = $('<tr>');
                    
                    // Create the first cell with checkbox
                    const cell1 = $('<td>');
                    const checkbox = $('<input>').attr({
                        type: 'radio',
                        id: 'chk_from_booking_' + (!objBookingInfo ? objBookingPeriod.id : objBookingInfo.booking_code),
                        name: 'chk_from_booking',
                        value: (!objBookingInfo ? "" : objBookingInfo.booking_code)
                    });
                    if (!objBookingInfo)
                        checkbox.prop('disabled', true);
                    
                    if (!objBookingInfo || prev_booking_code !== objBookingInfo.booking_code)
                        cell1.append(checkbox);
                    row.append(cell1);
                    
                    // Create the second cell with label
                    const cell2 = $('<td>');
                    const label = $('<label>').attr({
                        for: 'chk_from_booking_' + (!objBookingInfo ? objBookingPeriod.id : objBookingInfo.booking_code),
                        class: 'timeslot'
                    }).text(objBookingPeriod.DisplayText);
                    if (objBookingInfo)
                        label.addClass("booked");
                    if (!objBookingPeriod.isAvailable)
                        label.addClass("unavailable");
                    
                    cell2.append(label);

                    if (objBookingInfo) {
                        const span = $('<span>').text(" " + objBookingInfo.business_name);
                        cell2.append(span);

                        prev_booking_code = objBookingInfo.booking_code;
                    } else
                        prev_booking_code = "";

                    row.append(cell2);

                    // Append the row to the table
                    table.append(row);
                });
	        });
        }

        function loadToBookingPeriods() {
            var to_date = $("#to_date").val();
            var to_system_id = $("#to_system").val();

            var formData = [];
			formData.push({ name: "action", value: "get_bookings_by_system_date" });
			formData.push({ name: "system_id", value: to_system_id });
            formData.push({ name: "date", value: to_date });

            const table = $('#tbl_to_booking');
            table.html("");

			$.post(apiUri, formData, function (data) {
				var res = JSON.parse(data);

                arr_to_all_bookingperiods = res.booking_periods;
                var arr_to_bookings = res.booking_info;

				arr_to_all_bookingperiods.forEach(objBookingPeriod => {
                    var objBookingInfo = null

                    if (arr_to_bookings.hasOwnProperty(objBookingPeriod.FromInMinutes + "-" + objBookingPeriod.ToInMinutes)) {
                        objBookingInfo = arr_to_bookings[objBookingPeriod.FromInMinutes + "-" + objBookingPeriod.ToInMinutes];
                    }

                    // Create a new row for each element
                    const row = $('<tr>');
                    
                    // Create the first cell with checkbox
                    const cell1 = $('<td>');
                    const checkbox = $('<input>').attr({
                        type: 'radio',
                        id: 'chk_to_booking_' + objBookingPeriod.id,
                        name: 'chk_to_booking',
                        value: objBookingPeriod.id
                    });
                    if (objBookingInfo || !objBookingPeriod.isAvailable)
                        checkbox.prop('disabled', true);
                    
                    cell1.append(checkbox);
                    row.append(cell1);
                    
                    // Create the second cell with label
                    const cell2 = $('<td>');
                    const label = $('<label>').attr({
                        for: 'chk_to_booking_' + objBookingPeriod.id,
                        class: 'timeslot'
                    }).text(objBookingPeriod.DisplayText);
                    if (objBookingInfo)
                        label.addClass("booked");
                    if (!objBookingPeriod.isAvailable)
                        label.addClass("unavailable");
                    
                    cell2.append(label);

                    if (objBookingInfo) {
                        const span = $('<span>').text(" " + objBookingInfo.business_name);
                        cell2.append(span);

                        prev_booking_code = objBookingInfo.booking_code;
                    } else
                        prev_booking_code = "";

                    row.append(cell2);

                    // Append the row to the table
                    table.append(row);
                });
	        });
        }
    });
</script>