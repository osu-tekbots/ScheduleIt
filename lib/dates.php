<?php

/**
 * Determines whether the time (in the server timezone) is on the next day (i.e. after
 * midnight) in the given timezone.
 * 
 * @param int $time The time to check (Unix timestamp like returned from `strtotime()`)
 * @param string $timezone The timezone to check whether the time is the next day in
 * 
 * @return boolean Whether the time falls on the next day in the given timezone
 */
function isNextDay($time, $timezone) {
    $tz = new DateTimeZone($timezone);

    $date = new DateTime(date("Y-m-d ") . date("H:i", $time));
    $date->setTimezone($tz);

    return $date->format('Y-m-d') != date("Y-m-d");
}


/**
 * Calculates the date prior to the given date.
 * 
 * @param string $date The date to find the one before
 * 
 * @return string The prior date
 */
function yesterday($date) {
    return date('Y-m-d', strtotime($date . ' -1 day'));
}


/**
 * Produces the labels (in server-time) for each available timeslot within one day. Any
 * times that wrap past midnight (in the specified timezone) within the time range are
 * given in the first element of the result; all times that don't wrap past midnight are
 * given in the second element of the result.
 * 
 * @param string $start_time The start of the time range (e.g. "08:00")
 * @param string $end_time The end of the time range (e.g. "17:00")
 * @param int $slot_duration The number of minutes each timeslot should be
 * @param string $timezone The timezone to use when determining whether times wrap past
 *                         midnight
 * 
 * @return array{array{int}, array{int}}
 */
function getTimeLabels($start_time, $end_time, $slot_duration, $timezone) {
    $time_labels = [[], []];

    $current = time();
    $add_time = strtotime('+' . $slot_duration . ' mins', $current);
    $diff = $add_time - $current;

    $inc_time = strtotime($start_time);
    $stop_time = strtotime($end_time);

    while ($inc_time < $stop_time) {
        if (isNextDay($inc_time, $timezone)) {
            array_push($time_labels[0], date('H:i:s', $inc_time));
        } else {
            array_push($time_labels[1], date('H:i:s', $inc_time));
        }
        $inc_time += $diff;
    }

    return $time_labels;
}


/**
 * Calculates the unique dates from a given list after localizing to the given timezone.
 * 
 * @param array{DateTime} $dates The dates (with times) to localize & find unique of
 * @param string $timezone The timezone to localize dates to
 * 
 * @return array{string} The unique dates in YYYY-MM-DD format
 */
function getUniqueDates($dates, $timezone) {
    $timezone = new DateTimeZone($timezone);
    
    $timezone_aware_list = [];
    foreach ($dates as $start_time) {
        $start_time->setTimezone($timezone);
        $timezone_aware_list[] = $start_time->format('Y-m-d');
    }

    return array_unique($timezone_aware_list);
}


/**
 * Localizes the given date(time) string for easy formatting or further use.
 * 
 * @param string $date The string to turn into a localized datetime
 * 
 * @return DateTime The localized date, ready for formatting or further use
 */
function localizeDate($date, $timezone) {
    $tz = new DateTimeZone($timezone);
    
    $dt = new DateTime($date);
    $dt->setTimezone($tz);

    return $dt;
}
