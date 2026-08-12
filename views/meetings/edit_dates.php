<?php

require_once ABSPATH . 'config/session.php';
require_once ABSPATH . 'lib/dates.php';
require_once ABSPATH . 'lib/file_upload.php';
require_once ABSPATH . 'lib/send_email.php';

$server_tz = new DateTimeZone(date_default_timezone_get());

$meeting = $database->getMeetingById($meeting_id, $_SESSION['user_onid']);

if ($meeting) {
    $dates = [];
    $dates_saved = [];
    $date_objects =  $database->getDatesByMeetingId($meeting_id);
    $timeslots = $database->getTimeslotsByMeetingId($meeting_id);
    $meeting['duration'] = count($timeslots) > 0 ? $timeslots[0]['duration'] : 60;
    $meeting['slot_capacity'] = count($timeslots) > 0 ? $timeslots[0]['slot_capacity'] : 1;
    $timeslot_times = [];
    $timeslot_hashes = [];
    $timeslot_times_saved = [];
    $timeslot_times_scheduled = [];

    foreach ($timeslots as $key => $timeslot) {
        $start_time = localizeDate($timeslot['start_time'], $meeting['creation_timezone'])->format('Y-m-d H:i:s');

        array_push($timeslot_times, $start_time);
        array_push($timeslot_times_saved, $start_time);
        if($timeslot['spaces_available'] != $timeslot['slot_capacity']) {
            array_push($timeslot_times_scheduled, $start_time);
        }
        $timeslot_hashes[$timeslot['start_time']] = $timeslot['hash'];
    }

    foreach ($date_objects as $key => $date) {
        array_push($dates, $date['date']);
        array_push($dates_saved, $date['date']);
    }

    $meeting_tz = new DateTimeZone($meeting['creation_timezone']);

    $start_time = (new DateTime($meeting['start_time']))->setTimezone($meeting_tz);
    $end_time = (new DateTime($meeting['end_time']))->setTimezone($meeting_tz);

    $time_labels = [];
    while ($start_time < $end_time) {
        array_push($time_labels, clone $start_time);
        $start_time->modify("+{$meeting['duration']} minutes");
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $slot_capacity = !empty($_POST['slot_capacity']) ? intval($_POST['slot_capacity']) : 1;
        $duration = !empty($_POST['duration']) ? intval($_POST['duration']) : 60;
        $updated_timeslots = !empty($_POST['timeslots']) ? $_POST['timeslots'] : [];
        $deleted_timeslots = [];
        $current_timeslots = [];
        $new_timeslots = [];
        $event_start_time = (new DateTimeImmutable($_POST['event_start_time']))->setTimezone($server_tz)->format('H:i:s');
        $event_end_time = (new DateTimeImmutable($_POST['event_end_time']))->setTimezone($server_tz)->format('H:i:s');

        $timeslot_times_saved_server_tz = array_map(
            fn ($t) => (new DateTime($t, $meeting_tz))->setTimezone($server_tz)->format('Y-m-d H:i:s'),
            $timeslot_times_saved
        );

        $database->updateMeetingStartEndTimes($meeting_id, $event_start_time, $event_end_time);

        // Same duration
        if ($duration == $meeting['duration']) {
            foreach ($timeslot_times_saved_server_tz as $key => $timeslot) {
                if (! empty(array_filter($updated_timeslots, fn ($t) => new DateTime($t) == new DateTime($timeslot)))) {
                    array_push($current_timeslots, $timeslot);
                } else {
                    array_push($deleted_timeslots, $timeslot);
                }
            }

            foreach ($updated_timeslots as $key => $timeslot) {
                
                if (empty(array_filter($current_timeslots, fn ($t) => new DateTime($t) == new DateTime($timeslot)))) {
                    array_push($new_timeslots, $timeslot);
                }
            }
        } else {
            // Duration changed, delete all current timeslots and create all new ones
            $deleted_timeslots = $timeslot_times_saved_server_tz;
            $new_timeslots = $updated_timeslots;
        }

        // Initialize error codes
        $insert_success = true;
        $delete_success = true;

        // Remove timeslots
        foreach ($deleted_timeslots as $key => $timeslot) {
            $timeslot_hash = $timeslot_hashes[$timeslot];
            $removed_users = $database->getAttendeesByTimeslot($timeslot_hash);
            $error_code = $database->deleteTimeslot($meeting['hash'], $timeslot_hash);

            if ($error_code != 0) {
                $delete_success = false;
            } else {
                // Notify users of removed timeslots
                foreach ($removed_users as $key => $user) {
                    $send_email->changedTimeslots($meeting, $user);
                    // delete any files the had uploaded
                    $file_name = UPLOADS_ABSPATH . $meeting['hash'] . '/' . $user['attendee_onid'] . '_upload.*';
                    $file_upload->delete($file_name);
                }
            }
        }

        // Create timeslots
        foreach ($new_timeslots as $key => $timeslot) {
            $new_timeslot['duration'] = $duration;
            $new_timeslot['capacity'] = $slot_capacity;
            $new_timeslot['start_time'] = (new DateTimeImmutable($timeslot))->setTimezone($server_tz)->format('Y-m-d H:i:s');
            $new_timeslot['end_time'] = (new DateTimeImmutable($timeslot))->modify('+' . $duration . ' mins')->setTimezone($server_tz)->format('Y-m-d H:i:s');
            $error_code = $database->addTimeslot($meeting['hash'], $new_timeslot);

            if ($error_code != 0) {
                $insert_success = false;
            }
        }

        if ($insert_success && $delete_success) {
            $msg->success('"' . $meeting['name'] . '" has been updated.', SITE_DIR . '/meetings/' . $meeting_id);
        } else {
            $msg->error('Meeting dates could not be updated.');
        }
    }

    echo $twig->render('meetings/edit_dates.twig', [
        'dates' => $dates,
        'dates_saved' => $dates_saved,
        'dates_json' => json_encode($dates),
        'dates_saved_json' => json_encode($dates_saved),
        'edit_dates' => true,
        'meeting' => $meeting,
        'meetings_end_time' => new DateTimeImmutable(MEETINGS_END_TIME, $meeting_tz),
        'meetings_start_time' => new DateTimeImmutable(MEETINGS_START_TIME, $meeting_tz),
        'meetings_max_end_time' => new DateTimeImmutable(MEETINGS_MAX_END_TIME, $meeting_tz),
        'meetings_min_start_time' => new DateTimeImmutable(MEETINGS_MIN_START_TIME, $meeting_tz),
        'time_labels' => $time_labels,
        'timeslot_times' => $timeslot_times,
        'timeslot_times_saved' => $timeslot_times_saved,
        'timeslot_times_scheduled' => $timeslot_times_scheduled, // Uncomment this to continue dev ---------------------------
        'title' => 'Edit Meeting Dates - ' . $meeting['name'],
    ]);
} else {
    http_response_code(404);
    echo $twig->render('errors/error_logged_in.twig', [
        'message' => 'Sorry, we couldn\'t find that meeting.',
        'title' => 'Meeting Not Found',
    ]);
}
