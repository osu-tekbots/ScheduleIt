<?php

require_once ABSPATH . 'config/session.php';

$dates = array();
array_push($dates, '2024-08-30');

$duration = 30;

$time_labels = [];
$start_time = strtotime(MEETINGS_START_TIME);
$end_time = strtotime(MEETINGS_END_TIME);
$current = time();
$add_time = strtotime('+' . $duration . ' mins', $current);
$diff = $add_time - $current;

while ($start_time < $end_time) {
    array_push($time_labels, date('H:i:s', $start_time));
    $start_time += $diff;
}

echo $twig->render('schedule/available.twig', [
    'title' => 'Schedule Times Available',
    'dates' => $dates,
    'dates_json' => json_encode($dates),
    'duration' => $duration,
    'time_labels' => $time_labels,
    'meetings_end_time' => MEETINGS_END_TIME,
    'meetings_start_time' => MEETINGS_START_TIME,
    'meetings_max_end_time' => MEETINGS_MAX_END_TIME,
    'meetings_min_start_time' => MEETINGS_MIN_START_TIME,
]);