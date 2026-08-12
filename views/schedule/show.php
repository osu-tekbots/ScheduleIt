<?php

require_once ABSPATH . 'config/session.php';
require_once ABSPATH . 'lib/dates.php';


$schedule = $database->getScheduleById($schedule_id);

$server_dates = $database->getDatesByScheduleId($schedule_id);
if ($server_dates) {
    $server_dates = array_map(fn ($d) => $d['date'], $server_dates);

    $localized_dates = getUniqueDatesFromRange($server_dates, $schedule['start_time'], $schedule['end_time'], $_SESSION['user_timezone']);
    $schedule['dates_count'] = count($localized_dates);
    $first_date = $server_dates[0];
} else {
    $first_date = date('Y-m-d');
}

$time_labels = getTimeLabels($first_date, $schedule['start_time'], $schedule['end_time'], $schedule['slot_duration'], $_SESSION['user_timezone']);

$users = $database->getUsersByScheduleId($schedule_id);
$schedule['users_count'] = $users ? count($users) : 0;

$usernames = [];
foreach ($users as $user) {
    $user_from_table = $database->getUserById($user);
    $usernames[] = $user_from_table['first_name'] . " " . $user_from_table['last_name'];
}

$availabilities = $database->getAvailabilitiesByScheduleId($schedule_id);
$timeslot_availabilities = [];
foreach ($availabilities as $key => $availability) {
    $user = $database->getUserById($availability['fk_user_id']);
    $timeslot_availabilities[$availability['start_time']][] = $user['first_name'] . " " . $user['last_name'];
}

$timeslots = getTimeslots($server_dates, $schedule['start_time'], $schedule['end_time'], $schedule['slot_duration'], $timeslot_availabilities, $_SESSION['user_timezone']);

echo $twig->render('schedule/show.twig', [
    'title' => $schedule['name'],
    'schedule' => $schedule,
    'time_labels' => $time_labels,
    'dates' => $localized_dates,
    'timeslots' => $timeslots,
    'usernames' => $usernames
]);
