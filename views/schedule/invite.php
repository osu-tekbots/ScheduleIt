<?php

require_once ABSPATH . 'config/session.php';
require_once ABSPATH . 'lib/dates.php';

$schedule_hash = !empty($_GET['key']) ? $_GET['key'] : null;

if(isset($schedule_hash)) {
    $schedule = $database->getScheduleByHash($schedule_hash);
}

if (!$schedule){
    echo $twig->render('schedule/invite.twig', [
        'title' => "This schedule doesn't exist",
        'exists' => false
    ]);
    die();
}

$title = $schedule['name'];


// 
// Data for showing all users' availability
// 

$server_dates_table = $database->getDatesByScheduleId($schedule['id']);
if ($server_dates_table) {
    $server_dates = array_map(fn ($d) => $d['date'], $server_dates_table);
    $raw_dates = [];
    foreach ($server_dates as $date) {
        $raw_dates[] = new DateTime("{$date} {$schedule['start_time']}");
        $raw_dates[] = new DateTime("{$date} {$schedule['end_time']}");
    }
    $localized_dates = getUniqueDates($raw_dates, $_SESSION['user_timezone']);

    $schedule['dates_count'] = count($server_dates);
}

$time_labels = getTimeLabels($schedule['start_time'], $schedule['end_time'], $schedule['slot_duration'], $_SESSION['user_timezone']);

$users = $database->getUsersByScheduleId($schedule['id']);
$schedule['users_count'] = $users ? count($users) : 0;

$usernames = [];
foreach ($users as $user) {
    $user_from_table = $database->getUserById($user);
    $usernames[] = $user_from_table['first_name'] . " " . $user_from_table['last_name'];
}

$availabilities = $database->getAvailabilitiesByScheduleId($schedule['id']);
$timeslot_availabilities = [];

foreach ($availabilities as $key => &$availability) {
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


// 
// Data for showing current user's selections
// 

$user_availabilities = $database->getAvailabilitiesByScheduleIdandUserId($schedule['id'], $_SESSION['user_id']);

$timeslot_times_scheduled = [];
foreach ($user_availabilities as $key => $user_availability) {
    array_push($timeslot_times_scheduled, $user_availability['start_time']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $availabilities_input = !empty($_POST['timeslots']) ? $_POST['timeslots'] : [];

    $database->deleteScheduleAvailabilitiesForUser($schedule['id'], $_SESSION['user_id']);

    foreach ($server_dates_table as $date) {
        if (!empty($availabilities_input)) {
            $database->addAvailabilities($_SESSION['user_id'], $date['id'], $date['date'], $availabilities_input, $schedule['slot_duration']);
        }
    }

    header("Refresh:0");
}

echo $twig->render('schedule/invite.twig', [
    'exists' => true,
    'title' => $title,
    'is_anon' => $schedule['is_anon'],
    'schedule' => $schedule,
    'time_labels' => $time_labels,
    'dates' => $localized_dates,
    'timeslots' => $timeslots,
    'usernames' => $usernames,
    'timeslot_times_scheduled' => $timeslot_times_scheduled
]);