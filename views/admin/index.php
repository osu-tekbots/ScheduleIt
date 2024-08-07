<?php

require_once ABSPATH . 'config/session.php';
require_once ABSPATH . 'lib/file_upload.php';

$admins = $database->getAdmins();
$isadmin = 0;
foreach ($admins as $admin) {
    if ($admin['user_id'] == $_SESSION['user_id']) {
        $isadmin = 1;
    }
}

$users = $database->getUsers();
$events = $database->getEvents();
$currentTime = date("Y-m-d H:i:s");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($isadmin == 1) {
        if (isset($_POST['eventId']) && isset($_POST['deleteEvent'])) {
            $event = $database->getMeetingById($_POST['eventId']);
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
}

echo $twig->render('admin/index.twig', [
    'title' => 'Admininster',
    'id' => $_SESSION['user_id'],
    'isadmin' => $isadmin,
    'users' => $users,
    'events' => $events
]);
