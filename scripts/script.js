function getDisplayTextFromMinutes(from_in_mins, to_in_mins) {
    var start_hour = Math.floor(from_in_mins / 60);
    var start_minutes = ('0' + (from_in_mins % 60)).slice(-2);
    var start_AP = (start_hour < 12) ? 'am' : 'pm';
    start_hour -= (start_hour <= 12) ? 0 : 12;
    start_hour = ('0' + start_hour).slice(-2);

    var end_hour = Math.floor(to_in_mins / 60);
    var end_minutes = ('0' + (to_in_mins % 60)).slice(-2);
    var end_AP = (end_hour < 12) ? 'am' : 'pm';
    end_hour -= (end_hour <= 12) ? 0 : 12;
    end_hour = ('0' + end_hour).slice(-2);

    return start_hour + ':' + start_minutes + '' + start_AP + ' To ' + end_hour + ':' + end_minutes + '' + end_AP;
}