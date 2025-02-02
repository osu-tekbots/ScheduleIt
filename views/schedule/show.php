<?php

require_once ABSPATH . 'config/session.php';

$timeslot = !empty($_GET['time']) ? $_GET['time'] : null;
$user_ids_in_timeslot = NULL;
if ($timeslot) {
    $user_ids_in_timeslot = $database->getUsersByDateandTimeslot($schedule_id, $timeslot);
}

if ($user_ids_in_timeslot) {
    $number_available = count($user_ids_in_timeslot);
} else {
    $number_available = 0;
}

$available_users = [];

foreach ($user_ids_in_timeslot as $key => $user_id_in_timeslot) {
    $user = $database->getUserById($user_id_in_timeslot);
    array_push($available_users, $user['first_name'] . ' ' . $user['last_name']);
}

$schedule = $database->getScheduleById($schedule_id);
$dates = $database->getDatesByScheduleId($schedule_id);
if ($dates) {
    $schedule['dates_count'] = count($dates);
}

$users = $database->getUsersByScheduleId($schedule_id);
$not_available_users = [];
foreach ($users as $key => $user) {
    if ($user_ids_in_timeslot){ 
        if (!in_array($user, $user_ids_in_timeslot)) {
            $not_available_user = $database->getUserById($user);
            array_push($not_available_users, $not_available_user['first_name'] . ' ' . $not_available_user['last_name']);
        }
    } else {
        $not_available_user = $database->getUserById($user);
        array_push($not_available_users, $not_available_user['first_name'] . ' ' . $not_available_user['last_name']);
    }
}
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
    'timeslot_times_saved' => $timeslot_times_saved,
    'timeslot' => $timeslot,
    'number_available' => $number_available,
    'available_users' => $available_users,
    'not_available_users' => $not_available_users
]);