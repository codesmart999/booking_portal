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
    }

    echo json_encode( $res );
    $db->close();
?>