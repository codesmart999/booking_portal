<?php
	include("../config.php");
	require_once('../lib.php');
	require_once('../admin/utils.php');

	$res = [
		"status" 	=> "success",
		"message"	=> ""
	];

    $db = getDBConnection();

    switch ($_POST['action']) {
        case 'get_bookings_by_system_date':
            $systemId = $_POST['system_id'];
            $date = date('Y-m-d', strtotime(str_replace('/', '-', $_POST['date'])));

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

            $res["status"] = "success";
            $res["booking_info"] = isset($bookingInfo[$date]) ? $bookingInfo[$date] : array();
            $res['booking_periods'] = $booking_periods;
            break;
        case 'move_booking':
            $booking_code = $_POST['chk_from_booking'];
            $from_system_id = $_POST['from_system'];
            
            $stmt = $db->prepare("SELECT ServiceId, CustomerId, IsCancelled, Attended, Comments, Messages FROM bookings WHERE BookingCode=? LIMIT 1");
            $stmt->bind_param('s', $booking_code);
            $stmt->execute();
            $stmt->bind_result($ServiceId, $CustomerId, $IsCancelled, $Attended, $Comments, $Messages);
            $stmt->store_result();

            $objCurUser = $_SESSION['User'];
            $randomID = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            $currentDateTime = date('Y-m-d H:i:s');
            
            if (empty($objCurUser) || empty($objCurUser['UserId'])) {
                $res["status"] = "error";
                $res["message"] = "Unauthorized Access.";
                break;
            }

            if ($stmt->fetch()) {
                $to_system_id = $_POST['to_system'];
                $BookingDate = $date = date('Y-m-d', strtotime(str_replace('/', '-', $_POST['to_date'])));

                // Update Comment
                $arr_existing_comments = array();
                if (!empty($Comments))
                    $arr_existing_comments = json_decode($Comments, true);

                if (!empty($_POST['comment'])) {
                    $arr_existing_comments[] = [
                        'id' => str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                        'user_id' => $objCurUser['UserId'],
                        'datetime' => date('Y-m-d H:i:s'),
                        'content' => $_POST['comment']
                    ];
                }

                $Comments = json_encode($arr_existing_comments);

                // Update Message
                $arr_existing_messages = array();
                if (!empty($Messages))
                    $arr_existing_messages = json_decode($Messages, true);

                if (!empty($_POST['message'])) {
                    $arr_existing_messages[] = [
                        'id' => str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                        'user_id' => $objCurUser['UserId'],
                        'datetime' => date('Y-m-d H:i:s'),
                        'content' => $_POST['message']
                    ];
                }

                $Messages = json_encode($arr_existing_messages);
                
                $arr_target_bookingperiods = explode(',', $_POST['target_bookingperiods']);

                list($from_in_mins, $to_in_mins) = extractStartAndEndTime($arr_target_bookingperiods);
                $BookingCode = generateRandomCode($CustomerId . $to_system_id . $BookingDate . $from_in_mins . $to_in_mins);;
                
                $stmt = $db->prepare("INSERT INTO bookings (ServiceId, SystemId, CustomerId, BookingDate, BookingFrom, BookingTo, BookingCode, IsCancelled, Attended, Comments, Messages)"
                    . " VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
				foreach ($arr_target_bookingperiods as $bookingperiods) {
                    list($BookingFrom, $BookingTo) = explode('-', $bookingperiods);
					$stmt->bind_param('iiissssiiss', $ServiceId, $to_system_id, $CustomerId, $BookingDate, $BookingFrom, $BookingTo, $BookingCode, $IsCancelled, $Attended, $Comments, $Messages);
					$stmt->execute() or die($stmt->error);
				}

                $stmt = $db->prepare( 'DELETE FROM bookings WHERE SystemId = ? AND BookingCode=?' );
                $stmt->bind_param('is', $from_system_id, $booking_code);
				$stmt->execute() or die($stmt->error);

                $res["status"] = "success";
            } else {
                $res["status"] = "error";
            }
            break;
    }

    echo json_encode( $res );
    $db->close();
?>