<?php

require_once ABSPATH . 'config/session.php';

$schedule = $database->getScheduleById($schedule_id);
$dates = $database->getDatesByScheduleId($schedule_id);
if ($dates) {
    $schedule['dates_count'] = count($dates);
}
$users = $database->getUsersByScheduleId($schedule_id);
if($users) {
    $schedule['users_count'] = count($users);
} else {
    $schedule['users_count'] = 0;
}
$availabilities = $database->getAvailabilitiesByScheduleId($schedule_id);

// Create time labels
$time_labels = [];

$start_time = strtotime($schedule['start_time']);
$end_time = strtotime($schedule['end_time']);

$current = time();
$add_time = strtotime('+' . $schedule['slot_duration'] . ' mins', $current);
$diff = $add_time - $current;

while ($start_time < $end_time) {
    array_push($time_labels, date('H:i:s', $start_time));
    $start_time += $diff;
}

$timeslot_times_saved = [];

foreach ($availabilities as $key => $availability) {
    array_push($timeslot_times_saved, $availability['start_time']);
}

echo $twig->render('schedule/show.twig', [
    'title' => $schedule['name'],
    'schedule' => $schedule,
    'time_labels' => $time_labels,
    'dates' => $dates,
    'timeslot_times_saved' => $timeslot_times_saved
]);