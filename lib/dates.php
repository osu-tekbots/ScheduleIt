<?php

/**
 * Determines whether the given time is on a different day (e.g. after midnight) than the
 * anchor time, once converted to the given timezone.
 * 
 * @param DateTime $anchor The time to check against
 * @param DateTime $time The time to check
 * @param string $timezone The timezone to check whether the time is the next day in
 * 
 * @return boolean Whether the time falls on the next day in the given timezone
 */
function isNextDay($anchor, $time, $timezone) {
    $tz = new DateTimeZone($timezone);

    $anchor->setTimezone($tz);
    $time->setTimezone($tz);

    return $time->format('Y-m-d') != $anchor->format("Y-m-d");
}


/**
 * Produces the labels (in the specified timezone) for each available timeslot within one
 * day. Any times that wrap past midnight within the time range are given in the first
 * element of the result; all times that don't wrap past midnight are given in the second
 * element of the result.
 * 
 * @param string $start_date The first date (server time) to produce labels for. Decreases
 *                           DST-related bugs for regions with different DST periods than
 *                           the server. For example, Sydney, AUS, has an inverted DST
 *                           schedule that otherwise causes 2-hour gaps between time
 *                           labels and timeslots.
 * @param string $start_time The start of the time range (server time) in HH:MM format
 * @param string $end_time The end of the time range (exclusive; server time) in HH:MM
 *                         format
 * @param int $slot_duration The number of minutes each timeslot should be
 * @param string $timezone The timezone to use when determining whether times wrap past
 *                         midnight
 * 
 * @return array{array{DateTime}, array{DateTime}}
 */
function getTimeLabels($start_date, $start_time, $end_time, $slot_duration, $timezone) {
    $time_labels = [[], []];

    $inc_time = localizeDate("$start_date $start_time", $timezone);
    $start_time = localizeDate("$start_date $start_time", $timezone);
    $stop_time = localizeDate("$start_date $end_time", $timezone);

    if ($inc_time > $stop_time)
        $stop_time->modify('+1 day');

    while ($inc_time < $stop_time) {
        if ($start_time->format('Y-m-d') != $inc_time->format('Y-m-d')) {
            array_push($time_labels[0], clone $inc_time);
        } else {
            array_push($time_labels[1], clone $inc_time);
        }
        $inc_time->modify("+$slot_duration mins");
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
 * Calculates the unique dates that fall within the given time range, starting on each of
 * the given dates. If the time range extends past midnight in the given timezone, the
 * following date will be included in the result, too.
 * 
 * @param array{DateTime} $dates The dates for the start of each time range
 * @param string $start_time The start of each range in HH:MM format
 * @param string $end_time The end of each range (exclusive) in HH:MM format
 * @param string $timezone The timezone to localize dates to
 * 
 * @return array{string} The unique dates in YYYY-MM-DD format
 */
function getUniqueDatesFromRange($dates, $start_time, $end_time, $timezone) {
    $result = [];
    foreach ($dates as $date) {
        $start = new DateTime("$date $start_time");
        $end = (new DateTime("$date $end_time"))->modify('-1 second');
        if ($start > $end) $end->modify('+1 day');
        
        $result[] = $start;
        $result[] = $end;
    }

    return getUniqueDates($result, $timezone);
}


/**
 * Localizes the given date(time) string for easy formatting or further use.
 * 
 * @param string $date The string to turn into a localized datetime
 * @param string $timezone The timezone to use for localization
 * 
 * @return DateTime The localized date, ready for formatting or further use
 */
function localizeDate($date, $timezone) {
    $tz = new DateTimeZone($timezone);
    
    $dt = new DateTime($date);
    $dt->setTimezone($tz);

    return $dt;
}


/**
 * Aggregates who is available and generates a properly-formatted long date string for
 * each timeslot.
 * 
 * @param array{string} $server_dates The dates that have timeslots (in YYYY-MM-DD format)
 *                      Note: if $end_time is after midnight on the day following
 *                      $start_time, only the date for $start_time should be included
 *                      unless the following day also has timeslots after $start_time.
 * @param string $start_time The start of each time block (in server time; HH:MM format)
 * @param string $end_time The end of each time block (in server time; HH:MM format)
 * @param int $slot_duration The number of minutes each timeslot takes up
 * @param array{string: array{string}} $timeslot_availabilities An associative array
 *                                     mapping timeslots (in server time) to the names of
 *                                     everyone who is available during that timeslot
 * @param string $timezone The timezone that all timeslots should be localized to
 * 
 * @return array{array{available array{string}, formatted_date array{string}}}
 */
function getTimeslots($server_dates, $start_time, $end_time, $slot_duration, $timeslot_availabilities, $timezone) {
    $server_tz = new DateTimeZone(date_default_timezone_get());
    $date_format = 'l, M j, Y \a\t g:i A T';
    $timeslots = [];

    foreach ($server_dates as $server_date) {
        $inc_time = localizeDate("$server_date $start_time", $timezone);
        $stop_time = localizeDate("$server_date $end_time", $timezone);
        if ($inc_time > $stop_time) $stop_time->modify('+1 day');

        while ($inc_time < $stop_time) {
            $server_datetime = (clone $inc_time)->setTimezone($server_tz)->format('Y-m-d H:i:s');
            $user_date = $inc_time->format('Y-m-d');
            $user_time = $inc_time->format('H:i:s');

            $timeslots[$user_date][$user_time]['available'] = $timeslot_availabilities[$server_datetime] ?? [];
            $timeslots[$user_date][$user_time]['formatted_date'] = $inc_time->format($date_format);

            $inc_time->modify("+$slot_duration mins");
        }
    }

    return $timeslots;
}
