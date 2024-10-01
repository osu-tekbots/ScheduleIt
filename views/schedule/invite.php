<?php

require_once ABSPATH . 'config/session.php';

$schedule_hash = !empty($_GET['key']) ? $_GET['key'] : null;

if(isset($schedule_hash)) {
    $schedule = $database->getScheduleByHash($schedule_hash);
}

if ($schedule){
    $exists = true;
    $title = $schedule['name'];

    $dates = $database->getDatesByScheduleId($schedule['id']);
    $schedule['dates_count'] = count($dates);
    $users = $database->getUsersByScheduleId($schedule['id']);
    if ($users) {  
        $schedule['users_count'] = count($users);
    } else {
        $schedule['users_count'] = 0;
    }
    
    $availabilities = $database->getAvailabilitiesByScheduleId($schedule['id']);

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

    $user_availabilities = $database->getAvailabilitiesByScheduleIdandUserId($schedule['id'], $_SESSION['user_id']);

    $timeslot_times_scheduled = [];

    foreach ($user_availabilities as $key => $availability) {
        array_push($timeslot_times_scheduled, $availability['start_time']);
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $availabilities = !empty($_POST['timeslots']) ? $_POST['timeslots'] : [];

        $database->deleteScheduleAvailabilitiesForUser($schedule['id'], $_SESSION['user_id']);

        foreach ($dates as $date) {
            if (!empty($availabilities)) {
                $database->addAvailabilities($_SESSION['user_id'], $date['id'], $date['date'], $availabilities, $schedule['slot_duration']);
            }
        }

        header("Refresh:0");
    }

} else {
    $exists = false;
    $title = "This schedule doesn't exist";
}

echo $twig->render('schedule/invite.twig', [
    'title' => $title,
    'exists' => $exists,
    'is_anon' => $schedule['is_anon'],
    'schedule' => $schedule,
    'time_labels' => $time_labels,
    'dates_json' => json_encode($dates),
    'dates' => $dates,
    'timeslot_times_saved' => $timeslot_times_saved,
    'timeslot_times_scheduled' => $timeslot_times_scheduled
]);