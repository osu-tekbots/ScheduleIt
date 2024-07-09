<?php

require_once ABSPATH . 'config/session.php';
require_once ABSPATH . 'lib/send_email.php';
require_once ABSPATH . 'lib/bookings_ics_file.php';
require_once ABSPATH . 'lib/google_cal_link.php';
require_once ABSPATH . 'lib/outlook_cal_link.php';

$meeting = $database->getMeetingById($meeting_id, $_SESSION['user_id']);
$timeslots = $database->getTimeslotsByMeetingId($meeting['id']);
// list of onids that were invited to the event but have not registered
$inviteList = $database->getNotRegistered($meeting['id']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['attendeeOnid'])) {
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
    } elseif (isset($_POST['timeslot_for_ics'])) {
        $ics_file = new BookingsIcsFile($meeting, $timeslots);
        $ics_file->serveIcsFile();
    }
} 

if ($meeting && $meeting['creator_id'] == $_SESSION['user_id']) {
    $meeting['dates'] = $database->getDatesByMeetingId($meeting['id']);
    $meeting['dates_count'] = count($meeting['dates']);
    $attendee_meetings = $database->getMeetingAttendees($meeting['id']);
    foreach ($attendee_meetings as $key => $timeslot) {
        $google_cal_link = new GoogleCalLink($meeting['name'],$attendee_meetings[$key]['start_time'],$attendee_meetings[$key]['end_time'],$meeting['description'],$meeting['location']);
        $attendee_meetings[$key]['google_cal_link'] = $google_cal_link->getlink();
        $outlook_cal_link = new OutlookCalLink($meeting['name'],$attendee_meetings[$key]['start_time'],$attendee_meetings[$key]['end_time'],$meeting['description'],$meeting['location']);
        $attendee_meetings[$key]['outlook_cal_link'] = $outlook_cal_link->getlink();
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
