<?php
function convert_bookingperiod_summary_into_details($arr_bookingperiod_summary_by_weekday) {
    for ($day = 0; $day < 7; $day++){
        if (empty($arr_bookingperiod_summary_by_weekday[$day])) {
            $arr_bookingperiod_summary_by_weekday[$day] = array(
                'FromInMinutes' => 8 * 60, // 8:00 AM
                'ToInMinutes' => 18 * 60, // 6:00 PM
                'DurationInMinutes' => 15
            );
        }
    }

    $regular_weekday_start_hour = [];
    $regular_weekday_start_minutes = [];
    $regular_weekday_start_AP = [];
    $regular_weekday_end_hour = [];
    $regular_weekday_end_minutes = [];
    $regular_weekday_end_AP = [];
    $regular_weekday_duration_hours = [];
    $regular_weekday_duration_minutes = [];

    // $regular_weekday_start_hour = array(
	// 	'0' => 8, '1' => 8, '2' => 8, '3' => 8, '4' => 8, '5' => 8, '6' => 8
	// );
	// $regular_weekday_start_minutes = array(
	// 	'0' => 0, '1' => 0, '2' => 0, '3' => 0, '4' => 0, '5' => 0, '6' => 0
	// );
	// $regular_weekday_start_AP = array(
	// 	'0' => 'AM', '1' => 'AM', '2' => 'AM', '3' => 'AM', '4' => 'AM', '5' => 'AM', '6' => 'AM'
	// );
	// $regular_weekday_end_hour = array(
	// 	'0' => 6, '1' => 6, '2' => 6, '3' => 6, '4' => 6, '5' => 6, '6' => 6
	// );
	// $regular_weekday_end_minutes = array(
	// 	'0' => 0, '1' => 0, '2' => 0, '3' => 0, '4' => 0, '5' => 0, '6' => 0
	// );
	// $regular_weekday_end_AP = array(
	// 	'0' => 'PM', '1' => 'PM', '2' => 'PM', '3' => 'PM', '4' => 'PM', '5' => 'PM', '6' => 'PM'
	// );

    foreach ($arr_bookingperiod_summary_by_weekday as $weekday => $workhours) {
        $start_hour = floor($workhours['FromInMinutes'] / 60);
        $start_minutes = $workhours['FromInMinutes'] % 60;
        $end_hour = floor($workhours['ToInMinutes'] / 60);
        $end_minutes = $workhours['ToInMinutes'] % 60;
        $duration_hours = floor($workhours['DurationInMinutes'] / 60);
        $duration_minutes = $workhours['DurationInMinutes'] % 60;

        $regular_weekday_start_hour[$weekday] = ($start_hour > 12) ? $start_hour - 12 : $start_hour;
        $regular_weekday_start_minutes[$weekday] = $start_minutes;
        $regular_weekday_start_AP[$weekday] = ($start_hour < 12) ? 'AM' : 'PM';
        $regular_weekday_end_hour[$weekday] = ($end_hour > 12) ? $end_hour - 12 : $end_hour;
        $regular_weekday_end_minutes[$weekday] = $end_minutes;
        $regular_weekday_end_AP[$weekday] = ($end_hour < 12) ? 'AM' : 'PM';
        $regular_weekday_duration_hours[$weekday] = $duration_hours;
        $regular_weekday_duration_minutes[$weekday] = $duration_minutes;
    }

    return compact('regular_weekday_start_hour', 'regular_weekday_start_minutes', 'regular_weekday_start_AP',
        'regular_weekday_end_hour', 'regular_weekday_end_minutes', 'regular_weekday_end_AP',
        'regular_weekday_duration_hours', 'regular_weekday_duration_minutes');
}

function summarize_bookingperiod_details($params) {
    if (!isset($params['weekday_start_hour'])) {
        // There is no weekday available.
        return null;
    }

    $regular_weekday_start_hour = $params['weekday_start_hour'];
    $regular_weekday_start_minutes = $params['weekday_start_minutes'];
    $regular_weekday_start_AP = $params['weekday_start_AP'];
    $regular_weekday_end_hour = $params['weekday_end_hour'];
    $regular_weekday_end_minutes = $params['weekday_end_minutes'];
    $regular_weekday_end_AP = $params['weekday_end_AP'];
    $regular_weekday_duration_hours = $params['weekday_duration_hours'];
    $regular_weekday_duration_minutes = $params['weekday_duration_minutes'];
    
    $arr_bookingperiod_summary_by_weekday = [];

    foreach ($regular_weekday_start_hour as $weekday => $start_hour) {
        $start_minutes = $regular_weekday_start_minutes[$weekday];
        $end_hour = $regular_weekday_end_hour[$weekday];
        $end_minutes = $regular_weekday_end_minutes[$weekday];
        $duration_hours = $regular_weekday_duration_hours[$weekday];
        $duration_minutes = $regular_weekday_duration_minutes[$weekday];

        $start_hour += ($regular_weekday_start_AP[$weekday] === 'PM' && $start_hour < 12) ? 12 : 0;
        $end_hour += ($regular_weekday_end_AP[$weekday] === 'PM' && $end_hour < 12) ? 12 : 0;

        $arr_bookingperiod_summary_by_weekday[$weekday] = [
            'FromInMinutes' => $start_hour * 60 + $start_minutes,
            'ToInMinutes' => $end_hour * 60 + $end_minutes,
            'DurationInMinutes' => $duration_hours * 60 + $duration_minutes
        ];
    }

    return $arr_bookingperiod_summary_by_weekday;
}

function get_display_text_from_minutes($from_in_mins, $to_in_mins) {
    $start_hour = floor($from_in_mins / 60);
    $start_minutes = sprintf("%02d", $from_in_mins % 60);
    $start_AP = ($start_hour < 12) ? 'am' : 'pm';
    $start_hour -= ($start_hour <= 12) ? 0 : 12;
    $start_hour = sprintf("%02d", $start_hour);

    $end_hour = floor($to_in_mins / 60);
    $end_minutes = sprintf("%02d", $to_in_mins % 60);
    $end_AP = ($end_hour < 12) ? 'am' : 'pm';
    $end_hour -= ($end_hour <= 12) ? 0 : 12;
    $end_hour = sprintf("%02d", $end_hour);

    return $start_hour . ':' . $start_minutes . '' . $start_AP . ' To ' 
        . $end_hour . ':' . $end_minutes . '' . $end_AP;
}

?>