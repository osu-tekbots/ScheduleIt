<?php

require_once ABSPATH . 'config/session.php';
require_once ABSPATH . 'lib/send_email.php';
require_once ABSPATH . 'lib/bookings_ics_file.php';
require_once ABSPATH . 'lib/attendee_csv_file.php';
require_once ABSPATH . 'lib/classes/CalLink/GoogleCalLink.php';
require_once ABSPATH . 'lib/classes/CalLink/OutlookCalLink.php';
require_once ABSPATH . 'lib/file_upload.php';

$meeting = $database->getMeetingById($meeting_id, $_SESSION['user_id']);
$timeslots = $database->getTimeslotsByMeetingId($meeting['id']);
// list of onids that were invited to the event but have not registered
$inviteList = $database->getNotRegistered($meeting['id']);

if ($meeting && $meeting['creator_id'] == $_SESSION['user_id']) {
    $meeting['dates'] = $database->getDatesByMeetingId($meeting['id'], $_SESSION['user_timezone']);
    $meeting['dates_count'] = count($meeting['dates']);
    $attendee_meetings = $database->getMeetingAttendees($meeting['id']);

    $timeslot = $timeslot_google_link = $timeslot_outlook_link = null;
    foreach ($attendee_meetings as $key => &$attendee) {
        if (is_null($timeslot) || $timeslot['start_time'] != $attendee['start_time']) {
            $timeslot_google_link = new GoogleCalLink(
                $meeting['name'], $attendee['description'], $attendee['start_time'], $attendee['end_time'], $meeting['location']
            );
            $timeslot_outlook_link = new OutlookCalLink(
                $meeting['name'], $attendee['description'], $attendee['start_time'], $attendee['end_time'], $meeting['location']
            );

            $timeslot = &$attendee;
        }

        $timeslot_google_link->addRequiredAttendee($attendee['attendee_email']);
        $timeslot_outlook_link->addRequiredAttendee($attendee['attendee_email']);

        $timeslot['google_cal_link'] = $timeslot_google_link->getOwnerLink();
        $timeslot['outlook_cal_link'] = $timeslot_outlook_link->getOwnerLink();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['deleteHash'])) {
            $meetingHash = $_POST['deleteHash'];
            $meetingHash = trim($meetingHash);

            $result = $database->deleteMeeting($meetingHash);

            if ($result > 0) {
                $file_upload->deleteEventFiles($meetingHash);
                $msg->success('"' . $meeting['name'] . '" has been deleted.', SITE_DIR . '/manage');
            } else {
                $msg->error('Could not delete the meeting.');
            }
        } else if (isset($_POST['attendeeOnid'])) {
            $attendeeOnids = $_POST['attendeeOnid'];
            $link = $_POST['link'];
            $host = $_SESSION['user_onid'];
            $hash = $meeting['hash'];
            // turn onid string into array
            $onidArray = explode(" ", $attendeeOnids);

            // create email list
            // send email in forloop so that other recipients emails are not
            // exposed
            $sentInvites = 0;
            foreach ($onidArray as $onid) {
                if (strlen($onid) > 2) {
                    $send_email->invitation($_SESSION['user_onid'], $onid, $meeting['name'], $_SESSION['user_firstname'] . ' ' . $_SESSION['user_lastname'], $link);

                // add onid to the events inivte list
                    $database->insertInviteList($onid, $meeting['id']);
                    $sentInvites += 1;
                }
            }
            if ($sentInvites > 1) {
                $successMessage = 'Sent ' . $sentInvites . ' invites.';
                $msg->success($successMessage, SITE_DIR . '/meetings/' . $meeting['id']);
            } else {
                if ($sentInvites > 0) {
                    $msg->success('Sent 1 invite.', SITE_DIR . '/meetings/' . $meeting['id']);
                }
            }
        } elseif (isset($_POST['ics'])) {
            $ics_file = new BookingsIcsFile($meeting, $timeslots);
            $ics_file->serveIcsFile();
        } else if (isset($_POST['attendee_csv'])) {
            $csv_file = new AttendeeCsvFile($meeting, $attendee_meetings);
            $csv_file->serveCsvFile();
        }
    }

    echo $twig->render('meetings/show.twig', [
        'attendee_meetings' => $attendee_meetings,
        'meeting' => $meeting,
        'title' => $meeting['name'],
        'invite_list' => $inviteList,
    ]);
} else {
    http_response_code(404);
    echo $twig->render('errors/error_logged_in.twig', [
        'message' => 'Sorry, we couldn\'t find that meeting.',
        'title' => 'Meeting Not Found',
    ]);
}
