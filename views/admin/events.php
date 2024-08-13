<?php

require_once ABSPATH . 'config/session.php';
require_once ABSPATH . 'lib/file_upload.php';

$isadmin = 0;
if ($_SESSION['is_admin'])
    $isadmin = 1;

$search_term = !empty($_GET['q']) ? $_GET['q'] : '';
$events = $database->getAllMeetingsBySearchTerm($search_term);

$oldEvents = array();
foreach ($events as $key => $event) {
    if ($database->getEventIsOld($event['id'])) {
        $events[$key]['isOld'] = true;
        array_push($oldEvents, $event);
    } else {
        $events[$key]['isOld'] = false;
    }
}

function deleteEventById ($eventId, $database, $file_upload) {
    if ($_SESSION['is_admin']) {
        $event = $database->getMeetingById($eventId);
        $meeting_hash = $event['hash'];
        
        $invites = $database->getNotRegistered($event['id']);
        foreach ($invites as $invite) {
            $onid = $invite['user_onid'];
            $database->deleteInvite($onid, $event['id']);
        }

        $timeslots = $database->getTimeslotsByMeetingId($event['id']);
        foreach ($timeslots as $timeslot) {
            $timeslot_hash = $timeslot['hash'];
            $bookings = $database->getBookingsByTimeslot($timeslot['id']);
            foreach ($bookings as $booking) {
                $id = $booking['fk_user_id'];
                $user = $database->getUserById($id);
                $onid = $user['onid'];
                $database->deleteBooking($onid, $timeslot_hash);
            }

            $database->deleteTimeslot($meeting_hash, $timeslot_hash);
        }
        
        $file_upload->deleteEventFiles($meeting_hash);
        $database->deleteMeeting($meeting_hash);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_SESSION['is_admin']) {
        if (isset($_POST['eventId']) && isset($_POST['deleteEvent'])) {
            deleteEventById($_POST['eventId'], $database, $file_upload);
            header("Refresh: 1");
        }
        if (isset($_POST['deleteAllOldEvents'])) {
            foreach ($oldEvents as $oldEvent) {
                deleteEventById($oldEvent['id'], $database, $file_upload);
            }
            header("Refresh: 1");
        }
    }
}

echo $twig->render('admin/events.twig', [
    'title' => 'Admininster',
    'id' => $_SESSION['user_id'],
    'isadmin' => $isadmin,
    'events' => $events
]);
