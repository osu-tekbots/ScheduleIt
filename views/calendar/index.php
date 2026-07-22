<?php

require_once ABSPATH . 'config/session.php';

function date_to_tz_string($date) {
    $date = new DateTime($date);
    $date->setTimezone(new DateTimeZone($_SESSION['user_timezone']));
    return $date->format('Y-m-d H:i:s');
}

$meetings = $database->getCalendarMeetings($_SESSION['user_id']);

$merged_meetings = [];
foreach($meetings as $meeting) {
    if(isset($merged_meetings[$meeting['id'].$meeting['start_time'].$meeting['end_time']])) {
        $merged_meetings[$meeting['id'].$meeting['start_time'].$meeting['end_time']]['booker_name'] .= ', '.$meeting['booker_name'];
    } else {
        $merged_meetings[$meeting['id'].$meeting['start_time'].$meeting['end_time']] = [
            'id' => $meeting['id'],
            'meeting_hash' => $meeting['meeting_hash'],
            'name' => $meeting['name'],
            'location' => $meeting['location'],
            'creator_id' => $meeting['creator_id'],
            'start_time' => date_to_tz_string($meeting['start_time']),
            'end_time' => date_to_tz_string($meeting['end_time']),
            'creator_email' => $meeting['creator_email'],
            'creator_name' => $meeting['creator_name'],
            'booker_name' => $meeting['booker_name'],
            'creator_onid' => $meeting['creator_onid'],
        ];
    }
}

/* Need to convert back into array (from hash table) for JSON encoding */
$meetings_array = [];
foreach($merged_meetings as $meeting) {
    $meetings_array[] = $meeting;
}

echo $twig->render('calendar/index.twig', [
    'calendar_page' => true,
    'meetings_json' => json_encode($meetings_array),
    'title' => 'Calendar'
]);
