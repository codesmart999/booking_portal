<?php
	include("../config.php");
	require_once('../lib.php');
	require_once('../admin/utils.php');

	$res = [
		"status" 	=> "success",
		"message"	=> _lang('success_update')
	];

    $db = getDBConnection();

    switch ($_POST['action']) {
        case 'get_move_booking_summary':
            $selected_date = $_POST['selected_date'];
            $target_system_id = $_POST['target_system_id'];
            $target_booking_period_from = $_POST['target_booking_period_from'];

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
        
            $arrSelectedBookingsByDate = isset($_SESSION['move_bookings_info']) ? $_SESSION['move_bookings_info'] : array();
            $arrSelectedBookingsByDate[$selected_date] = isset($_POST['selected_bookings']) ? $_POST['selected_bookings'] : array();
            $_SESSION['move_bookings_info'] = $arrSelectedBookingsByDate;
            $valuesArray = array_values($arrSelectedBookingsByDate);
            $arrSelectedBookings = call_user_func_array('array_merge', $valuesArray);
            
            $arrBookingInfo = array();

            foreach ($arrSelectedBookings as $str_selected_booking) {
                list($system_id, $booking_code) = explode('_', $str_selected_booking);

                if (!isset($arrBookingInfo[$system_id])) {
                    $arrBookingInfo[$system_id] = array(
                        'SystemInfo' => $arrSystems[$system_id],
                        'BookingInfo' => array()
                    );
                }

                if (!isset($arrBookingInfo[$system_id]['BookingInfo'][$booking_code])) {
                    $arrBookingInfo[$system_id]['BookingInfo'][$booking_code] = getBookedInfoByBookingCode($booking_code, $system_id);
                }
            }

            $res["status"] = "success";
            $res["booking_info"] = $arrBookingInfo;
            $res["target_info"] = array(
                'SystemInfo' => $arrSystems[$target_system_id],
                'BookingPeriodFrom' => format_date($selected_date) . ' ' . get_display_text_from_minutes($target_booking_period_from)
            );
            break;
        case 'get_bookings_by_system_date': // Deprecated!! (used in move_bookings_old.php)
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
            // 1) Check Authentication
            $objCurUser = $_SESSION['User'];
            $randomID = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
            $currentDateTime = date('Y-m-d H:i:s');
            
            if (empty($objCurUser) || empty($objCurUser['UserId'])) {
                $res["status"] = "error";
                $res["message"] = "Unauthorized Access.";
                break;
            }

            // 2) Load Bookings To Move
            $selected_date = $_POST['selected_date'];
            $target_system_id = $_POST['target_system_id'];
            $target_booking_period_from = $_POST['target_booking_period_from'];
        
            $arrSelectedBookingsByDate = isset($_SESSION['move_bookings_info']) ? $_SESSION['move_bookings_info'] : array();
            $valuesArray = array_values($arrSelectedBookingsByDate);
            $arrSelectedBookings = call_user_func_array('array_merge', $valuesArray);

            $arrBookingInfo = array();

            foreach ($arrSelectedBookings as $str_selected_booking) {
                list($system_id, $booking_code) = explode('_', $str_selected_booking);

                if (!isset($arrBookingInfo[$system_id][$booking_code])) {
                    $stmt = $db->prepare("SELECT ServiceId, CustomerId, StaffName, PatientName, BookingDate, BookingFrom, BookingTo, IsCancelled, Attended, Comments, Messages FROM bookings WHERE SystemId = ? AND BookingCode = ?");
                    $stmt->bind_param("is", $system_id, $booking_code);
                    $stmt->execute();
                    $stmt->bind_result($service_id, $customer_id, $staff_name, $patient_name, $booking_date, $booking_from, $booking_to, $is_cancelled, $attended, $comments, $messages);
                    $stmt->store_result();
                    
                    while ($stmt->fetch()) {
                        $arrBookingInfo[$system_id][$booking_code][] = array(
                            'service_id' => $service_id,
                            'customer_id' => $customer_id,
                            'staff_name' => $staff_name,
                            'patient_name' => $patient_name,
                            'booking_date' => $booking_date,
                            'booking_from' => $booking_from,
                            'booking_to' => $booking_to,
                            'is_cancelled' => $is_cancelled,
                            'attended' => $attended,
                            'comments' => $comments,
                            'messages' => $messages
                        );
                    }

                    $stmt = $db->prepare( 'DELETE FROM bookings WHERE SystemId = ? AND BookingCode=?' );
                    $stmt->bind_param("is", $system_id, $booking_code);
                    $stmt->execute() or die($stmt->error);
                }
            }

            // 3) Let's move
            $BookingDate = $date = date('Y-m-d', strtotime(str_replace('/', '-', $selected_date)));
            $new_booking_from = $target_booking_period_from;
            foreach ($arrBookingInfo as $system_id => $arrBookingInfoBySystemId) {
                foreach ($arrBookingInfoBySystemId as $booking_code => $arrBookingInfoByCode) {
                    if (empty($arrBookingInfoByCode)) continue;

                    $prev_from = $arrBookingInfoByCode[0]['booking_from'];
                    $prev_to = end($arrBookingInfoByCode)['booking_to'];
                    $prev_booking_date = $arrBookingInfoByCode[0]['booking_date'];

                    $Comments = $arrBookingInfoByCode[0]['comments'];
                    $Messages = $arrBookingInfoByCode[0]['messages'];

                    // Update Comment
                    $arr_existing_comments = array();
                    if (!empty($Comments)){
                        $arr_existing_comments = json_decode($Comments, true);

                        $arr_existing_comments[] = [
                            'id' => str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                            'user_id' => $objCurUser['UserId'],
                            'datetime' => date('Y-m-d H:i:s'),
                            'content' => "",
                            'type' => "MoveBooking",
                            'prevFrom' => $prev_from,
                            'prevTo' => $prev_to,
                            'prevDate' => $prev_booking_date,
                            'newFrom' => $new_booking_from,
                            'newTo' => $new_booking_from + $prev_to - $prev_from,
                            'newDate' => $BookingDate
                        ];
                    }

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

                    $BookingCode = generateRandomCode($customer_id . $target_system_id . $BookingDate . $prev_from . $prev_to);

                    $stmt = $db->prepare("INSERT INTO bookings (ServiceId, SystemId, CustomerId, StaffName, PatientName, BookingDate, BookingFrom, BookingTo, BookingCode, IsCancelled, Attended, Comments, Messages)"
                        . " VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    
                    foreach ($arrBookingInfoByCode as $bookingInfo) {
                        $new_booking_to = $new_booking_from + $bookingInfo['booking_to'] - $bookingInfo['booking_from'];

                        $stmt->bind_param('iiissssssiiss',
                            $bookingInfo['service_id'],
                            $target_system_id,
                            $bookingInfo['customer_id'],
                            $bookingInfo['staff_name'],
                            $bookingInfo['patient_name'],
                            $BookingDate,
                            $new_booking_from,
                            $new_booking_to,
                            $BookingCode,
                            $bookingInfo['is_cancelled'],
                            $bookingInfo['attended'],
                            $Comments,
                            $Messages
                        );
                        $stmt->execute() or die($stmt->error);

                        $new_booking_from = $new_booking_to;
                    }
                }
            }

            $res["status"] = "success";

            break;
        case 'apply_template':
            $template_systemId = $_POST['template_systemId'];
            $template_startDate = $_POST['template_startDate'];
            $template_endDate = $_POST['template_endDate'];
            $apply_days = $_POST['apply_days'];
            $apply_startDate = str_replace('/', '-', $_POST['apply_startDate']);
            $apply_endDate = str_replace('/', '-', $_POST['apply_endDate']);
            $bIncludeBookings = isset($_POST['include_bookings']);

            // Apply Day as Teamplte
            if ($template_startDate == $template_endDate) {
                // Iterate from start date to end date
                $apply_curDate = strtotime($apply_startDate);
                while ($apply_curDate <= strtotime($apply_endDate)) {
                    $apply_curWeekday = date('w', $apply_curDate);

                    if (in_array($apply_curWeekday, $apply_days) && $apply_curDate != strtotime($template_startDate)) {
                        apply_template(date('Y-m-d', $apply_curDate), $template_systemId, $template_startDate, $bIncludeBookings);
                    }

                    // Move to the next day
                    $apply_curDate = strtotime('+1 day', $apply_curDate);
                }
            } else {
                // Apply Week as Template
                $template_curDate = strtotime($template_startDate);
                while ($template_curDate <= strtotime($template_endDate)) {
                    $template_curWeekday = date('w', $template_curDate);

                    if (in_array($template_curWeekday, $apply_days)) {
                        // Iterate from start date to end date
                        $apply_curDate = strtotime($apply_startDate);
                        while ($apply_curDate <= strtotime($apply_endDate)) {
                            $apply_curWeekday = date('w', $apply_curDate);

                            if ($apply_curWeekday == $template_curWeekday && $template_curDate != $apply_curDate) {
                                apply_template(date('Y-m-d', $apply_curDate), $template_systemId, date('Y-m-d', $template_curDate), $bIncludeBookings);
                            }

                            // Move to the next day
                            $apply_curDate = strtotime('+1 day', $apply_curDate);
                        }
                    }

                    // Move to the next day
                    $template_curDate = strtotime('+1 day', $template_curDate);
                }
            }
            break;
        // case 'move_booking':
        //     $booking_code = $_POST['chk_from_booking'];
        //     $from_system_id = $_POST['from_system'];
            
        //     $stmt = $db->prepare("SELECT ServiceId, CustomerId, IsCancelled, Attended, Comments, Messages FROM bookings WHERE BookingCode=? LIMIT 1");
        //     $stmt->bind_param('s', $booking_code);
        //     $stmt->execute();
        //     $stmt->bind_result($ServiceId, $CustomerId, $IsCancelled, $Attended, $Comments, $Messages);
        //     $stmt->store_result();

        //     $bookingInfo = getBookedInfoByBookingCode($booking_code);

        //     $objCurUser = $_SESSION['User'];
        //     $randomID = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        //     $currentDateTime = date('Y-m-d H:i:s');
            
        //     if (empty($objCurUser) || empty($objCurUser['UserId'])) {
        //         $res["status"] = "error";
        //         $res["message"] = "Unauthorized Access.";
        //         break;
        //     }

        //     if ($stmt->fetch()) {
        //         $to_system_id = $_POST['to_system'];
        //         $BookingDate = $date = date('Y-m-d', strtotime(str_replace('/', '-', $_POST['to_date'])));

        //         $arr_target_bookingperiods = explode(',', $_POST['target_bookingperiods']);

        //         // *Added by Advik (2024-04-03)*
        //         $newBookingTimeStart = 100000; // SET MAX VALUE
        //         $newBookingTimeEnd = 0;
        //         foreach ($arr_target_bookingperiods as $bookingperiods) {
        //             list($BookingFrom, $BookingTo) = explode('-', $bookingperiods);
        //             if ($newBookingTimeStart > $BookingFrom)
        //                 $newBookingTimeStart= $BookingFrom;
        //             if ($newBookingTimeEnd < $BookingTo)
        //                 $newBookingTimeEnd = $BookingTo;
        //         }//end 


        //         // Update Comment
        //         $arr_existing_comments = array();
        //         if (!empty($Comments)){
        //             $arr_existing_comments = json_decode($Comments, true);

        //             $arr_existing_comments[] = [
        //                 'id' => str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
        //                 'user_id' => $objCurUser['UserId'],
        //                 'datetime' => date('Y-m-d H:i:s'),
        //                 'content' => "",
        //                 'type' => "MoveBooking",
        //                 'prevFrom' => $bookingInfo["startTime"],
        //                 'prevTo' => $bookingInfo["endTime"],
        //                 'prevDate' => $bookingInfo["bookingDate"],
        //                 'newFrom' => $newBookingTimeStart,
        //                 'newTo' => $newBookingTimeEnd,
        //                 'newDate' => $BookingDate
        //             ];
        //         }

        //         if (!empty($_POST['comment'])) {
        //             $arr_existing_comments[] = [
        //                 'id' => str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
        //                 'user_id' => $objCurUser['UserId'],
        //                 'datetime' => date('Y-m-d H:i:s'),
        //                 'content' => $_POST['comment']
        //             ];

        //         }

        //         $Comments = json_encode($arr_existing_comments);

        //         // Update Message
        //         $arr_existing_messages = array();
        //         if (!empty($Messages))
        //             $arr_existing_messages = json_decode($Messages, true);

        //         if (!empty($_POST['message'])) {
        //             $arr_existing_messages[] = [
        //                 'id' => str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
        //                 'user_id' => $objCurUser['UserId'],
        //                 'datetime' => date('Y-m-d H:i:s'),
        //                 'content' => $_POST['message']
        //             ];
        //         }

        //         $Messages = json_encode($arr_existing_messages);
                
                

        //         list($from_in_mins, $to_in_mins) = extractStartAndEndTime($arr_target_bookingperiods);
        //         $BookingCode = generateRandomCode($CustomerId . $to_system_id . $BookingDate . $from_in_mins . $to_in_mins);;
                
        //         $stmt = $db->prepare("INSERT INTO bookings (ServiceId, SystemId, CustomerId, BookingDate, BookingFrom, BookingTo, BookingCode, IsCancelled, Attended, Comments, Messages)"
        //             . " VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		// 		foreach ($arr_target_bookingperiods as $bookingperiods) {
        //             list($BookingFrom, $BookingTo) = explode('-', $bookingperiods);
		// 			$stmt->bind_param('iiissssiiss', $ServiceId, $to_system_id, $CustomerId, $BookingDate, $BookingFrom, $BookingTo, $BookingCode, $IsCancelled, $Attended, $Comments, $Messages);
		// 			$stmt->execute() or die($stmt->error);
		// 		}

        //         $stmt = $db->prepare( 'DELETE FROM bookings WHERE SystemId = ? AND BookingCode=?' );
        //         $stmt->bind_param('is', $from_system_id, $booking_code);
		// 		$stmt->execute() or die($stmt->error);

        //         $res["status"] = "success";
        //     } else {
        //         $res["status"] = "error";
        //     }
        //     break;
    }

    echo json_encode( $res );
    $db->close();
?>