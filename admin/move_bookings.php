<?php
    define('SIDEBAR_CLASS', 'active'); // hide the sidebar

	require_once('header.php');
	require_once('utils.php');

	$message = "";
    $db = getDBConnection();

    $selected_date = isset($_POST['selected_date']) ? $_POST['selected_date'] : date("d/m/Y");

    if (isset($_POST['Refresh'])) {
        unset($_SESSION['move_bookings_info']);
    } else if (isset($_POST['selected_bookings'])) {
        $prev_selected_date = $_POST['prev_selected_date'];

        $arrSelectedBookingsByDate = isset($_SESSION['move_bookings_info']) ? $_SESSION['move_bookings_info'] : array();
        $arrSelectedBookingsByDate[$prev_selected_date] = isset($_POST['selected_bookings']) ? $_POST['selected_bookings'] : array();
        $_SESSION['move_bookings_info'] = $arrSelectedBookingsByDate;
    }

    $arrSelectedBookingsByDate = isset($_SESSION['move_bookings_info']) ? $_SESSION['move_bookings_info'] : array();

    $valuesArray = array_values($arrSelectedBookingsByDate);
    $arrSelectedBookings = call_user_func_array('array_merge', $valuesArray);

    $arrSystems = array();

    $stmt = $db->prepare("SELECT SystemId, FullName, LocationId, SystemType, MaxMultipleBookings FROM systems");
	$stmt->execute();
    $stmt->bind_result($system_id, $full_name, $location_id, $system_type, $max_multiple_bookings);
    $stmt->store_result();
    while ($stmt->fetch()) {
    	$arrSystems[$system_id] = array(
            'full_name' => $full_name,
            'location_id' => $location_id,
            'system_type' => $system_type,
            'max_multiple_bookings' => $max_multiple_bookings
        );
    }

    $date = date('Y-m-d', strtotime(str_replace('/', '-', $selected_date)));

    foreach ($arrSystems as $systemId => $objSystemInfo) {
        $bookingInfo = getBookedInfo($systemId, $date, $date);

        $weekday = date('N', strtotime($date)) % 7;
        $booking_periods =  getBookingPeriodsByWeekday($weekday, $systemId);

        $arr_availability_by_timeslot = getBookingPeriodsSpecialByDate($systemId, $date);

        $len = count($booking_periods);
        for ($i = 0; $i < $len; $i++) {
            $from_in_mins = $booking_periods[$i]['FromInMinutes'];
            $to_in_mins = $booking_periods[$i]['ToInMinutes'];

            if (isset($arr_availability_by_timeslot[$from_in_mins . '-' . $to_in_mins]))
                $booking_periods[$i]['isAvailable'] = $arr_availability_by_timeslot[$from_in_mins . '-' . $to_in_mins];
        }

        $arrSystems[$systemId]["booking_info"] = isset($bookingInfo[$date]) ? $bookingInfo[$date] : array();
        $arrSystems[$systemId]['booking_periods'] = $booking_periods;
    }

    $message = '<p class="Message fst-italic fw-bold text-success d-none">'._lang('success_update').'</p>';
?>

<h4 class="page-title">Move Bookings</h4>
<?php echo $message ?>

<input type="hidden" id="isBookingsSelected" value="<?php echo count($arrSelectedBookings); ?> "/>

<form method="post" class="form-horizontal" id="FRM_MOVE_BOOKING">
    <input type="hidden" id="target_system_id" name="target_system_id" />
    <input type="hidden" id="target_booking_period_from" name="target_booking_period_from" />
    <input type="hidden" name="prev_selected_date" value="<?php echo $selected_date; ?>" />
    <div class="container-fluid move-booking-container">
        <div class="row">
            <div class="col-md-4">
                <label for="selected_date" class="form-control form-control-sm">Select Date:</label>
            </div>
            <div class="col-md-4 input-group">
                <div class="input-group-append">
                    <span class="input-group-text"><i class="fa fa-calendar"></i></span> <!-- Date icon -->
                </div>
                <input type="text" id="selected_date" name="selected_date" class="date valid form-control form-control-sm" placeholder="dd/mm/yyyy" value="<?php echo $selected_date; ?>"/>
            </div>
            <div class="col-md-4 input-group">
                <input name="Refresh" type="submit" value="Refresh" class="btn btn-primary" />
            </div>
        </div>
    </div>
    <div class="display-flex move-booking-container">
        <?php
            foreach ($arrSystems as $systemId => $objSystemInfo) {
        ?>
            <div class="panel-container">
                <div class="panel-header panel-header-sm">
                    <?php echo '<a href="booking_access?SystemId=' . $systemId . '&startDate=' . $date . '&endDate=' . $date . '">'
                        . $objSystemInfo['full_name']
                        . '</a>';?>
                </div>
                <div class="timeslots-container">
                    <?php
                        foreach ($objSystemInfo['booking_periods'] as $objBookingPeriod) {
                            $str_booking_period = $objBookingPeriod['FromInMinutes'] . '-' . $objBookingPeriod['ToInMinutes'];

                            if (isset($objSystemInfo['booking_info'][$str_booking_period])) {
                                $arrBookingInfo = $objSystemInfo['booking_info'][$str_booking_period];

                                foreach ($arrBookingInfo as $objBookingInfo) {
                                    $checked = "";
                                    if (in_array($systemId . '_' . $objBookingInfo['booking_code'], $arrSelectedBookings))
                                        $checked = 'checked';

                                    echo '<div class="booked ' . $checked . '" data-system-id=' . $systemId . ' data-booking-code=' . $objBookingInfo['booking_code'] . '>'
                                        . $objBookingPeriod['DisplayText']
                                        . '<div><i class="fa fa-user" style="color: ' . generateTextColor($objBookingInfo['booking_code']) .'"></i> ' . $objBookingInfo['business_name'] . ' (' . $objBookingInfo['booking_code'] . ')</div>'
                                        . '<input class="d-none" type="checkbox" name="selected_bookings[]" data-system-id=' . $systemId . ' data-booking-code=' . $objBookingInfo['booking_code'] . ' value="' . $systemId . '_' . $objBookingInfo['booking_code'] . '" ' . $checked . '/>'
                                        . '</div>';
                                }
                            } else if (!empty($objBookingPeriod['isAvailable'])) {
                                echo '<div class="available" data-system-id=' . $systemId . ' data-booking-period-from=' . $objBookingPeriod['FromInMinutes'] . '>'
                                    . $objBookingPeriod['DisplayText']
                                    . '</div>';
                            }
                        }
                    ?>
                </div>
            </div>
        <?php
            }
        ?>
    </div>
</form>

<div class="modal fade" id="saveModal" tabindex="-1" role="dialog" aria-labelledby="saveModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="saveModalLabel">Confirm Move</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body vertical-labels">
                <div class="form-group">
                    <label>Selected Bookings</label>
                    <table id="summaryTable">
                        <thead>
                            <tr>
                                <th>System Name</th>
                                <th>Booking Details (Booking Code)</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="form-group">
                    <label>Moving To</label>
                    <table id="summaryTable_target">
                        <thead>
                            <tr>
                                <th>System Name</th>
                                <th>Booking Periods</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="form-group">
                    <label for="message">Message (Optional)</label>
                    <textarea id="message" name="message" rows=5></textarea>
                </div>
                <div class="form-group">
                    <label for="comment">Comment (Optional)</label>
                    <textarea id="comment" name="comment" rows=5></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary btn-sm" name="Save" value="Save" id="btnSave">Save</button>
            </div>
        </div>
    </div>
</div>

<?php
    $stmt->close();
    $db->close();

    require_once('footer.php');
?>

<script>
	$(document).ready(function() { 
        var apiUri = "/api/bookings.php";

        $("#selected_date").on("dateSelected", function() {
            $("#FRM_MOVE_BOOKING").submit();
        })

        $(".booked").on("click", function(e) {
            e.preventDefault();
            
            var isSelected = $(this).hasClass('checked');
            var system_id = $(this).attr('data-system-id');
            var booking_code = $(this).attr('data-booking-code');

            if (!isSelected) {
                $(".booked[data-system-id=" + system_id + "][data-booking-code=" + booking_code + "]").addClass('checked');
                $("input[data-system-id=" + system_id + "][data-booking-code=" + booking_code + "]").attr("checked", "checked");
                
                alert('To Move this booking, select an available timeslot in the Individual System you wish to move the booking to.')
            } else {
                $(".booked[data-system-id=" + system_id + "][data-booking-code=" + booking_code + "]").removeClass('checked');
                $("input[data-system-id=" + system_id + "][data-booking-code=" + booking_code + "]").removeAttr("checked");
            }

            $("#isBookingsSelected").val($(".booked.checked").length);
        })

        $(".available").on("click", function(e) {
            e.preventDefault();

            var isBookingsSelected = $("#isBookingsSelected").val();
            if (!parseInt(isBookingsSelected)) {
                alert("First select a Booking to reschedule");
                return;
            }

            var system_id = $(this).attr('data-system-id');
            var booking_period_from = $(this).attr('data-booking-period-from');

            $(".available").removeClass('checked');
            $(this).addClass('checked');

            $("#target_system_id").val(system_id);
            $("#target_booking_period_from").val(booking_period_from);

            $('#saveModal').modal('show');

            var formData = $("#FRM_MOVE_BOOKING").serializeArray();

			formData.push({ name: "action", value: "get_move_booking_summary" });

            $("#summaryTable tbody").html("");
            $("#summaryTable_target tbody").html("");

            $.post(apiUri, formData, function (data) {
                var responseData = JSON.parse(data);

                // Generate summary text
                $.each(responseData.booking_info, function(key, value) {
                    var row = $("<tr></tr>");
                    row.append("<td>" + value.SystemInfo.full_name + "</td>");

                    var timeSlotsCell = $("<td></td>");
                    $.each(value.BookingInfo, function(bookingKey, bookingValue) {
                        timeSlotsCell.append("<div>" + bookingValue.bookingDate + " " + bookingValue.DisplayText + " - " + bookingValue.businessName + " (" + bookingValue.bookingCode + ")</div>");
                    });
                    row.append(timeSlotsCell);

                    $("#summaryTable tbody").append(row);
                });

                var row = $("<tr></tr>");
                row.append("<td>" + responseData.target_info.SystemInfo.full_name + "</td>");

                var timeSlotsCell = $("<td></td>");
                timeSlotsCell.append("<div>" + responseData.target_info.BookingPeriodFrom + "</div>");
                row.append(timeSlotsCell);

                $("#summaryTable_target tbody").append(row);
            });
        });

        $("#btnSave").on("click", function(e) {
            e.preventDefault();

            var formData = $("#FRM_MOVE_BOOKING").serializeArray();

			formData.push({ name: "action", value: "move_booking" });

            $.post(apiUri, formData, function (data) {
				var res = JSON.parse(data);
				
                $(".Message").removeClass('d-none');
				if (!res || res.status == "error"){
					$(".Message").removeClass("text-success");
					$(".Message").addClass("text-danger");
                    $(".Message").html("There was an error while moving bookings.");
				} else {
					$(".Message").removeClass("text-danger");
					$(".Message").addClass("text-success");
                    $(".Message").html("Updated Successfully.");

                    location.reload();
				}
	        });
        });
    });
</script>