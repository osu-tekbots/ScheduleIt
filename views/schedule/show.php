<?php

require_once ABSPATH . 'config/session.php';
require_once ABSPATH . 'lib/dates.php';


$schedule = $database->getScheduleById($schedule_id);

$server_dates = $database->getDatesByScheduleId($schedule_id);
if ($server_dates) {
    $server_dates = array_map(fn ($d) => $d['date'], $server_dates);
    $raw_dates = [];
    foreach ($server_dates as $date) {
        $raw_dates[] = new DateTime("{$date} {$schedule['start_time']}");
        $raw_dates[] = new DateTime("{$date} {$schedule['end_time']}");
    }
    $localized_dates = getUniqueDates($raw_dates, $_SESSION['user_timezone']);

    $schedule['dates_count'] = count($localized_dates);
}

$time_labels = getTimeLabels($schedule['start_time'], $schedule['end_time'], $schedule['slot_duration'], $_SESSION['user_timezone']);

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
    
    if (! isset($timeslot_availabilities[$availability['start_time']])) {
        $timeslot_availabilities[$availability['start_time']] = [];
    }

    $timeslot_availabilities[$availability['start_time']][] = $user['first_name'] . " " . $user['last_name'];
}

$timeslots = [];
foreach ($localized_dates as $date) {
    foreach ($time_labels[0] as $time) {
        $yesterday = yesterday($date);

        $timeslots[$date][$time]['is_valid'] = in_array($yesterday, $server_dates);
        $timeslots[$date][$time]['available'] = $timeslot_availabilities["$yesterday $time"] ?? [];
        $timeslots[$date][$time]['formatted_date'] = localizeDate("$yesterday $time", $_SESSION['user_timezone'])
                                                        ->format('l, M j, Y \a\t g:i A T');
    }

    foreach ($time_labels[1] as $time) {
        $timeslots[$date][$time]['is_valid'] = in_array($date, $server_dates);
        $timeslots[$date][$time]['available'] = $timeslot_availabilities["$date $time"] ?? [];
        $timeslots[$date][$time]['formatted_date'] = localizeDate("$date $time", $_SESSION['user_timezone'])
                                                        ->format('l, M j, Y \a\t g:i A T');
    }
}

echo $twig->render('schedule/show.twig', [
    'title' => $schedule['name'],
    'schedule' => $schedule,
    'time_labels' => $time_labels,
    'dates' => $localized_dates,
    'timeslots' => $timeslots,
    'usernames' => $usernames
]);
