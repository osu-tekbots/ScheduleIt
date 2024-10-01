<?php

require_once ABSPATH . 'config/session.php';

$dates = [];
$availabilities = [];
$schedule = [
    'duration' => 60,
    'title' => 'test title',
    'description' => 'test description'
];
$duration = 60;
$timeslot_times = [];

// Create time labels
$time_labels = [];

$start_time = strtotime(MEETINGS_START_TIME);
$end_time = strtotime(MEETINGS_END_TIME);

$current = time();
$add_time = strtotime('+' . $schedule['duration'] . ' mins', $current);
$diff = $add_time - $current;

while ($start_time < $end_time) {
    array_push($time_labels, date('H:i:s', $start_time));
    $start_time += $diff;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $schedule['name'] = $_POST['name'];
    $schedule['description'] = $_POST['description'];
    $schedule['is_anon'] = !empty($_POST['is_anon']) ? 1 : 0;

    $schedule['start_time'] = $_POST['start-time'];
    $schedule['end_time'] = $_POST['end-time'];
    $schedule['slot_duration'] = $_POST['duration'];

    $dates = !empty($_POST['date_vals']) ? $_POST['date_vals'] : [];
    $availabilities = !empty($_POST['timeslots']) ? $_POST['timeslots'] : [];
    // $dates = ['2024-08-30', '2024-08-31'];

    if (empty($_POST['name']) || (count($dates) == 0)) {
        $msg->error('Please fill out all required fields.');
    } else {
        $new_schedule_id = $database->addSchedule($_SESSION['user_id'], $schedule);

        if ($new_schedule_id > 0) {
            if (!empty($dates)) {
                $database->addDates($_SESSION['user_id'], $new_schedule_id, $dates, $availabilities, $schedule['slot_duration']);
                
                $msg->success('"' . $schedule['name'] . '" has been created.', SITE_DIR . '/schedule/' . $new_schedule_id);
            }
        } else {
            $msg->error('Could not create schedule.');
        }

        
    }
}

echo $twig->render('schedule/create.twig', [
    'title' => 'Create Schedule',
    'dates' => $dates,
    'dates_json' => json_encode($dates),
    'schedule' => $schedule,
    'time_labels' => $time_labels,
    'meetings_end_time' => MEETINGS_END_TIME,
    'meetings_start_time' => MEETINGS_START_TIME,
    'meetings_max_end_time' => MEETINGS_MAX_END_TIME,
    'meetings_min_start_time' => MEETINGS_MIN_START_TIME,
]);