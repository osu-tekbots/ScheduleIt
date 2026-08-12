<?php

require_once ABSPATH . 'config/session.php';
require_once ABSPATH . 'lib/file_upload.php';

$dates = [];
$meeting = [
    'slot_capacity' => 1,
    'duration' => 60,
    'creation_timezone' => $_SESSION['user_timezone']
];
$timeslot_times = [];

$user_tz = new DateTimeZone($_SESSION['user_timezone']);

$start_time = new DateTime(MEETINGS_START_TIME, $user_tz);
$end_time = new DateTime(MEETINGS_END_TIME, $user_tz);

$time_labels = [];
while ($start_time < $end_time) {
    array_push($time_labels, clone $start_time);
    $start_time->modify("+{$meeting['duration']} minutes");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $slot_capacity = !empty($_POST['slot_capacity']) ? $_POST['slot_capacity'] : 1;
    $timeslot_times = !empty($_POST['timeslots']) ? $_POST['timeslots'] : [];

    $meeting['name'] = $_POST['name'];
    $meeting['location'] = $_POST['location'];
    $meeting['description'] = $_POST['description'];
    $meeting['is_anon'] = !empty($_POST['is_anon']) ? 1 : 0;
    $meeting['enable_message'] = !empty($_POST['enable_message']) ? 1 : 0;
    $meeting['require_message'] = !empty($_POST['require_message']) ? 1 : 0;
    $meeting['message_prompt'] = $_POST['message_prompt'];
    $meeting['enable_upload'] = !empty($_POST['enable_upload']) ? 1 : 0;
    $meeting['require_upload'] = !empty($_POST['require_upload']) ? 1 : 0;
    $meeting['upload_prompt'] = $_POST['upload_prompt'];
    $meeting['slot_capacity'] = $_POST['slot_capacity'];
    $meeting['duration'] = $_POST['duration'];
    $meeting['start_time'] = $_POST['event_start_time'];
    $meeting['end_time'] = $_POST['event_end_time'];
    $meeting['creation_timezone'] = $_POST['creation_timezone'];
    $meeting['timeslots'] = $timeslot_times;

    foreach ($timeslot_times as $key => $timeslot) {
        $date = explode(' ', $timeslot);

        if (!in_array($date[0], $dates)) {
            array_push($dates, $date[0]);
        }
    }

    if (empty($_POST['name']) || empty($_POST['location'])) {
        $msg->error('Please fill out all required fields.');
    } else {
        $new_meeting_id = $database->addMeeting($_SESSION['user_id'], $meeting);

        // Check for file to upload
        if ($new_meeting_id > 0 && !empty($_FILES['file']['name'])) {
            $created_meeting = $database->getMeetingById($new_meeting_id);
            // Upload file
            $new_file_upload = $file_upload->upload($_SESSION['user_onid'], $created_meeting['hash']);

            if ($new_file_upload['error']) {
                $msg->error($new_file_upload['message']);
            } else {
                $msg->success('"' . $meeting['name'] . '" has been created.', SITE_DIR . '/meetings/' . $new_meeting_id);
            }
        // No file uploaded, just meeting creation
        } elseif ($new_meeting_id > 0) {
            $msg->success('"' . $meeting['name'] . '" has been created.', SITE_DIR . '/meetings/' . $new_meeting_id);
        } else {
            $msg->error('Could not create meeting.');
        }
    }
}

echo $twig->render('meetings/create.twig', [
    'dates' => $dates,
    'dates_json' => json_encode($dates),
    'meeting' => $meeting,
    'time_labels' => $time_labels,
    'timeslot_times' => $timeslot_times,
    'meetings_end_time' => new DateTimeImmutable(MEETINGS_END_TIME, $user_tz),
    'meetings_start_time' => new DateTimeImmutable(MEETINGS_START_TIME, $user_tz),
    'meetings_max_end_time' => new DateTimeImmutable(MEETINGS_MAX_END_TIME, $user_tz),
    'meetings_min_start_time' => new DateTimeImmutable(MEETINGS_MIN_START_TIME, $user_tz),
    'title' => 'Create Meeting',
]);
