<?php

require_once ABSPATH . 'config/session.php';
require_once ABSPATH . 'lib/dates.php';
require_once ABSPATH . 'lib/send_email.php';

$dates = [];
$schedule = $database->getScheduleById($schedule_id);

$user_tz = new DateTimeZone($_SESSION['user_timezone']);

$start_time = new DateTime(MEETINGS_START_TIME, $user_tz);
$end_time = new DateTime(MEETINGS_END_TIME, $user_tz);

$time_labels = [];
while ($start_time < $end_time) {
    array_push($time_labels, clone $start_time);
    $start_time->modify("+{$schedule['slot_duration']} minutes");
}
$time_labels = [[], $time_labels]; // Standardize format for _available_selector.twig

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $server_tz = new DateTimeZone(date_default_timezone_get());

    $schedule['id'] = $schedule_id;
    $schedule['start_time'] = (new DateTimeImmutable($_POST['start-time']))->setTimezone($server_tz)->format('H:i:s');
    $schedule['end_time'] = (new DateTimeImmutable($_POST['end-time']))->setTimezone($server_tz)->format('H:i:s');
    $schedule['slot_duration'] = $_POST['duration'];

    $user_dates = !empty($_POST['date_vals']) ? $_POST['date_vals'] : [];
    $availabilities = !empty($_POST['timeslots']) ? $_POST['timeslots'] : [];
    $notify_respondants = isset($_POST['notify_respondants']);

    $server_dates = array_map(
        fn ($d) => localizeDate("$d {$_POST['start-time']}", date_default_timezone_get())->format('Y-m-d'),
        $user_dates
    );

    if (count($server_dates) == 0) {
        $msg->error('Please fill out all required fields.');
    } else {
        if ($notify_respondants) {
            $respondants = $database->getScheduleRespondants($schedule_id);
        }

        $result = $database->replaceSchedule($schedule);

        if ($result > 0) {
            if (!empty($server_dates)) {
                $database->addDates(
                    $_SESSION['user_id'], $schedule_id, $server_dates, $schedule['start_time'],
                    $schedule['end_time'], $availabilities, $schedule['slot_duration']
                );
            }
            if ($notify_respondants) {
                $send_email->scheduleReset(
                    $schedule['name'],
                    $_SESSION['user_firstname'] . ' ' . $_SESSION['user_lastname'],
                    $_SESSION['user_email'],
                    $respondants
                );
            }

            $msg->success('"' . $schedule['name'] . '" has been replaced.', SITE_DIR . '/schedule/' . $schedule_id);
        } else {
            $msg->error('Could not replace schedule.');
        }
    }
}

echo $twig->render('schedule/retry.twig', [
    'title' => "Retry Find-A-Time: {$schedule['name']}",
    'dates' => $dates,
    'dates_json' => json_encode($dates),
    'schedule' => $schedule,
    'time_labels' => $time_labels,
    'meetings_end_time' => new DateTimeImmutable(MEETINGS_END_TIME, $user_tz),
    'meetings_start_time' => new DateTimeImmutable(MEETINGS_START_TIME, $user_tz),
    'meetings_max_end_time' => new DateTimeImmutable(MEETINGS_MAX_END_TIME, $user_tz),
    'meetings_min_start_time' => new DateTimeImmutable(MEETINGS_MIN_START_TIME, $user_tz),
]);